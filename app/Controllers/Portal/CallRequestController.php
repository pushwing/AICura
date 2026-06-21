<?php

namespace App\Controllers\Portal;

use App\Models\CallMemoModel;
use App\Models\CallRequestModel;
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

    public function initController(
        \CodeIgniter\HTTP\RequestInterface $request,
        \CodeIgniter\HTTP\ResponseInterface $response,
        \Psr\Log\LoggerInterface $logger
    ): void {
        parent::initController($request, $response, $logger);
        $this->callRequestModel = model(CallRequestModel::class);
        $this->callMemoModel    = model(CallMemoModel::class);
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

        return $this->render('portal/call-requests/show', [
            'pageTitle' => '신청 상세',
            'request'   => $request,
            'statuses'  => CallRequestModel::STATUSES,
            'devices'   => CallRequestModel::DEVICES,
        ]);
    }

    public function changeStatus(int $id): ResponseInterface
    {
        $hospitalId = $this->requireHospitalId();

        $request = $this->callRequestModel->find($id);
        if ($request === null || (int) $request['hospital_id'] !== $hospitalId) {
            throw PageNotFoundException::forPageNotFound();
        }

        $body   = $this->request->getJSON(true);
        $status = (int) ($body['status'] ?? 0);

        try {
            $this->callRequestModel->changeStatus($id, $status);
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
