<?php

namespace App\Controllers\Admin;

use App\Models\CallMemoModel;
use App\Models\CallRequestModel;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * 이벤트 신청 DB 관리 컨트롤러
 *
 *   call_requests - 이벤트 신청 (CPA 과금 핵심)
 *   call_memos    - 신청 건당 운영 메모
 */
class CallRequestController extends BaseAdminController
{
    private CallRequestModel $callRequestModel;
    private CallMemoModel $callMemoModel;

    public function initController(
        \CodeIgniter\HTTP\RequestInterface $request,
        \CodeIgniter\HTTP\ResponseInterface $response,
        \Psr\Log\LoggerInterface $logger
    ): void {
        parent::initController($request, $response, $logger);
        $this->callRequestModel = model(CallRequestModel::class);
        $this->callMemoModel    = model(CallMemoModel::class);
    }

    // ──────────────────────────────────────────────
    // 신청 목록 (병원별·캠페인별·상태별 필터)
    // ──────────────────────────────────────────────

    public function index(): string
    {
        $params = [
            'hospital_id' => $this->request->getGet('hospital_id') ?? '',
            'campaign_id' => $this->request->getGet('campaign_id') ?? '',
            'status'      => $this->request->getGet('status') ?? '',
            'keyword'     => $this->request->getGet('keyword') ?? '',
            'sort'        => $this->request->getGet('sort') ?? '',
            'page'        => (int) ($this->request->getGet('page') ?? 1),
            'limit'       => 20,
        ];

        $result = $this->callRequestModel->getList($params);

        return $this->render('admin/call-requests/index', [
            'requests' => $result['list'],
            'total'    => $result['total'],
            'params'   => $params,
            'statuses' => CallRequestModel::STATUSES,
        ]);
    }

    // ──────────────────────────────────────────────
    // 신청 상세 (메모 목록 포함)
    // ──────────────────────────────────────────────

    public function show(int $id): string
    {
        $request = $this->callRequestModel->getDetail($id);
        if ($request === null) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        return $this->render('admin/call-requests/show', [
            'request'  => $request,
            'statuses' => CallRequestModel::STATUSES,
            'devices'  => CallRequestModel::DEVICES,
        ]);
    }

    // ──────────────────────────────────────────────
    // 상태 변경 (9단계) — AJAX(JSON)
    // ──────────────────────────────────────────────

    public function changeStatus(int $id): ResponseInterface
    {
        $body       = $this->request->getJSON(true);
        $status     = (int) ($body['status'] ?? 0);
        $reservedAt = isset($body['reserved_at']) ? (string) $body['reserved_at'] : null;

        /** @var array<string, mixed> $authUser */
        $authUser = session()->get('admin_user');
        $adminId  = (int) ($authUser['id'] ?? 0) ?: null;

        try {
            $this->callRequestModel->changeStatus($id, $status, $reservedAt, $adminId);
        } catch (\RuntimeException $e) {
            return $this->response->setStatusCode(422)
                ->setJSON(['success' => false, 'message' => $e->getMessage()]);
        }

        return $this->response->setJSON([
            'success' => true,
            'status'  => $status,
            'label'   => CallRequestModel::STATUSES[$status] ?? '',
            'message' => '상태가 변경되었습니다.',
        ]);
    }

    // ──────────────────────────────────────────────
    // 메모 등록
    // ──────────────────────────────────────────────

    public function memoStore(int $id): ResponseInterface
    {
        $request = $this->callRequestModel->find($id);
        if ($request === null) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        if (!$this->validate(['memo' => 'required|max_length[500]'])) {
            return redirect()->back()->with('error', '메모는 1~500자로 입력하세요.');
        }

        /** @var array<string, mixed> $authUser */
        $authUser = session()->get('admin_user');

        $this->callMemoModel->insert([
            'call_request_id' => $id,
            'user_id'         => (int) ($authUser['id'] ?? 0) ?: null,
            'memo'            => $this->request->getPost('memo'),
        ]);

        // 새 메모 반영해 재분석 — 큐 적재만 하고 즉시 응답 (leads:analyze가 소비)
        $this->callRequestModel->enqueueAnalysis($id);

        return redirect()->to('/admin/call-requests/' . $id)
            ->with('success', '메모가 등록되었습니다.');
    }

    // ──────────────────────────────────────────────
    // 메모 삭제
    // ──────────────────────────────────────────────

    public function memoDelete(int $id, int $memoId): ResponseInterface
    {
        $memo = $this->callMemoModel->find($memoId);
        if ($memo === null || (int) $memo['call_request_id'] !== $id) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $this->callMemoModel->delete($memoId);

        return redirect()->to('/admin/call-requests/' . $id)
            ->with('success', '메모가 삭제되었습니다.');
    }
}
