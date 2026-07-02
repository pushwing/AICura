<?php

namespace App\Controllers\Portal;

use Override;
use CodeIgniter\HTTP\RequestInterface;
use Psr\Log\LoggerInterface;
use RuntimeException;
use App\Models\CallMemoModel;
use App\Models\CallRequestModel;
use App\Models\RefundRequestModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * 광고주 신청 DB 관리 (이슈 #32)
 *
 * 광고주는 본인 병원(hospital_id)에 들어온 이벤트 신청(call_requests)만 조회·관리한다.
 * 모든 단건 작업은 신청 건의 hospital_id가 로그인 광고주의 병원과 일치하는지 검증한다.
 */
class CallRequestController extends BasePortalController
{
    private CallRequestModel $callRequestModel;
    private CallMemoModel $callMemoModel;
    private RefundRequestModel $refundRequestModel;

    #[Override]
    public function initController(
        RequestInterface $request,
        ResponseInterface $response,
        LoggerInterface $logger
    ): void {
        parent::initController($request, $response, $logger);
        $this->callRequestModel   = model(CallRequestModel::class);
        $this->callMemoModel      = model(CallMemoModel::class);
        $this->refundRequestModel = model(RefundRequestModel::class);
    }

    /** 로그인 광고주의 병원 id를 반환하거나 404 (미연결 시) */
    private function requireHospitalId(): int
    {
        $this->requireAdvertiser();

        $hospitalId = $this->hospitalId();
        if ($hospitalId === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        return $hospitalId;
    }

    public function index(): string
    {
        $hospitalId = $this->requireHospitalId();

        $params = [
            'hospital_id' => $hospitalId, // 병원 스코프 강제
            'campaign_id' => $this->request->getGet('campaign_id') ?? '',
            'status'      => $this->request->getGet('status') ?? '',
            'keyword'     => $this->request->getGet('keyword') ?? '',
            'page'        => (int) ($this->request->getGet('page') ?? 1),
            'limit'       => 20,
        ];

        $result = $this->callRequestModel->getList($params);

        return $this->render('portal/call-requests/index', [
            'pageTitle' => '신청DB 관리',
            'requests'  => $result['list'],
            'total'     => $result['total'],
            'params'    => $params,
            'statuses'  => CallRequestModel::STATUSES,
        ]);
    }

    public function show(int $id): string
    {
        $hospitalId = $this->requireHospitalId();

        $request = $this->callRequestModel->getDetail($id);
        if ($request === null || (int) $request['hospital_id'] !== $hospitalId) {
            throw PageNotFoundException::forPageNotFound();
        }

        $refund = $this->refundRequestModel->latestByCallRequest($id);
        $locked = $this->refundRequestModel->isLocked($id);

        return $this->render('portal/call-requests/show', [
            'pageTitle'      => '신청 상세',
            'request'        => $request,
            'statuses'       => CallRequestModel::STATUSES,
            'devices'        => CallRequestModel::DEVICES,
            'refundStatuses' => CallRequestModel::REFUND_STATUSES,
            'reservedStatus' => CallRequestModel::STATUS_RESERVED,
            'refund'         => $refund,
            'refundLabels'   => RefundRequestModel::STATUS_LABELS,
            'locked'         => $locked,
        ]);
    }

    public function changeStatus(int $id): ResponseInterface
    {
        $hospitalId = $this->requireHospitalId();

        $request = $this->callRequestModel->find($id);
        if ($request === null || (int) $request['hospital_id'] !== $hospitalId) {
            throw PageNotFoundException::forPageNotFound();
        }

        // 환불요청 대기·승인 중이면 광고주의 상태 변경을 잠근다 (이슈 #52)
        if ($this->refundRequestModel->isLocked($id)) {
            return $this->response->setStatusCode(422)->setJSON([
                'success' => false,
                'message' => '환불요청 처리 중에는 상태를 변경할 수 없습니다.',
            ]);
        }

        $body       = $this->request->getJSON(true);
        $status     = (int) ($body['status'] ?? 0);
        $reservedAt = isset($body['reserved_at']) ? (string) $body['reserved_at'] : null;
        $reason     = isset($body['reason']) ? trim((string) $body['reason']) : '';

        // 취소(3)는 사유 입력 필수
        if ($status === 3 && $reason === '') {
            return $this->response->setStatusCode(422)
                ->setJSON(['success' => false, 'message' => '취소 사유를 입력해주세요.']);
        }

        try {
            $this->callRequestModel->changeStatus($id, $status, $reservedAt, $this->userId() ?: null);
        } catch (RuntimeException $e) {
            return $this->response->setStatusCode(422)
                ->setJSON(['success' => false, 'message' => $e->getMessage()]);
        }

        // 중복/결번/취소 → 운영자 환불요청 생성 + 이후 상태 변경 잠금
        $refundCreated = false;
        if (isset(CallRequestModel::REFUND_STATUSES[$status])) {
            $refundCreated = $this->refundRequestModel->createRequest(
                $id,
                $hospitalId,
                $status,
                $reason !== '' ? $reason : null,
                $this->userId() ?: null,
            ) > 0;
        }

        return $this->response->setJSON([
            'success'        => true,
            'status'         => $status,
            'label'          => CallRequestModel::STATUSES[$status] ?? '',
            'refund_created' => $refundCreated,
            'message'        => $refundCreated
                ? '상태가 변경되어 환불요청이 접수되었습니다. 운영자 처리 전까지 상태를 변경할 수 없습니다.'
                : '상태가 변경되었습니다.',
        ]);
    }

    public function memoStore(int $id): ResponseInterface
    {
        $hospitalId = $this->requireHospitalId();

        $request = $this->callRequestModel->find($id);
        if ($request === null || (int) $request['hospital_id'] !== $hospitalId) {
            throw PageNotFoundException::forPageNotFound();
        }

        if (!$this->validate(['memo' => 'required|max_length[500]'])) {
            return redirect()->back()->with('error', '메모는 1~500자로 입력하세요.');
        }

        $this->callMemoModel->insert([
            'call_request_id' => $id,
            'user_id'         => $this->userId() ?: null,
            'memo'            => $this->request->getPost('memo'),
        ]);

        // 새 메모 반영해 재분석 — 큐 적재만 하고 즉시 응답 (leads:analyze가 소비)
        $this->callRequestModel->enqueueAnalysis($id);

        return redirect()->to('/portal/call-requests/' . $id)
            ->with('success', '메모가 등록되었습니다.');
    }

    public function memoDelete(int $id, int $memoId): ResponseInterface
    {
        $hospitalId = $this->requireHospitalId();

        $request = $this->callRequestModel->find($id);
        if ($request === null || (int) $request['hospital_id'] !== $hospitalId) {
            throw PageNotFoundException::forPageNotFound();
        }

        $memo = $this->callMemoModel->find($memoId);
        if ($memo === null || (int) $memo['call_request_id'] !== $id) {
            throw PageNotFoundException::forPageNotFound();
        }

        $this->callMemoModel->delete($memoId);

        return redirect()->to('/portal/call-requests/' . $id)
            ->with('success', '메모가 삭제되었습니다.');
    }
}
