<?php

namespace App\Controllers\Admin;

use App\Models\RefundRequestModel;
use CodeIgniter\HTTP\RedirectResponse;

/**
 * 환불요청 관리 (이슈 #52)
 *
 * 광고주가 신청DB 상태를 중복/결번/취소로 변경하면 생성되는 환불요청을 운영자가 처리한다.
 *   승인 → CPA 차감액을 deposits에 +로 복원
 *   거부 → 사유 저장(광고주 노출) + 광고주 상태 변경 잠금 해제
 */
class RefundRequestController extends BaseAdminController
{
    private RefundRequestModel $refundRequestModel;

    public function initController(
        \CodeIgniter\HTTP\RequestInterface $request,
        \CodeIgniter\HTTP\ResponseInterface $response,
        \Psr\Log\LoggerInterface $logger
    ): void {
        parent::initController($request, $response, $logger);
        $this->refundRequestModel = model(RefundRequestModel::class);
    }

    public function index(): string
    {
        $params = [
            'status' => $this->request->getGet('status') ?? '',
            'page'   => (int) ($this->request->getGet('page') ?? 1),
            'limit'  => 20,
        ];

        $result = $this->refundRequestModel->getList($params);

        return $this->render('admin/refund-requests/index', [
            'requests'       => $result['list'],
            'total'          => $result['total'],
            'params'         => $params,
            'statusLabels'   => RefundRequestModel::STATUS_LABELS,
            'requestStatuses' => \App\Models\CallRequestModel::REFUND_STATUSES,
        ]);
    }

    public function approve(int $id): RedirectResponse
    {
        $adminId = (int) (session()->get('admin_user')['id'] ?? 0);

        try {
            $this->refundRequestModel->approve($id, $adminId);
        } catch (\RuntimeException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }

        return redirect()->to('/admin/refund-requests')
            ->with('success', '환불요청을 승인하고 차감액을 복원했습니다.');
    }

    public function reject(int $id): RedirectResponse
    {
        if (!$this->validate(['reject_reason' => 'required|max_length[500]'])) {
            return redirect()->back()->with('error', '거부 사유는 1~500자로 입력하세요.');
        }

        $adminId = (int) (session()->get('admin_user')['id'] ?? 0);

        try {
            $this->refundRequestModel->reject($id, $adminId, (string) $this->request->getPost('reject_reason'));
        } catch (\RuntimeException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }

        return redirect()->to('/admin/refund-requests')
            ->with('success', '환불요청을 거부했습니다. 광고주가 상태를 다시 변경할 수 있습니다.');
    }
}
