<?php

namespace App\Controllers\Admin;

use App\Models\CampaignModel;
use App\Models\CreativeHistoryModel;
use CodeIgniter\HTTP\ResponseInterface;

class ReviewController extends BaseAdminController
{
    private CreativeHistoryModel $historyModel;
    private CampaignModel $campaignModel;

    public function initController(
        \CodeIgniter\HTTP\RequestInterface $request,
        \CodeIgniter\HTTP\ResponseInterface $response,
        \Psr\Log\LoggerInterface $logger
    ): void {
        parent::initController($request, $response, $logger);
        $this->historyModel  = model(CreativeHistoryModel::class);
        $this->campaignModel = model(CampaignModel::class);
    }

    // ──────────────────────────────────────────────
    // 검수 대기 목록
    // ──────────────────────────────────────────────

    public function index(): string
    {
        $params = [
            'keyword' => $this->request->getGet('keyword') ?? '',
            'page'    => max(1, (int) ($this->request->getGet('page') ?? 1)),
            'limit'   => 20,
        ];

        $result = $this->historyModel->getPendingList($params);

        return $this->render('admin/reviews/index', [
            'title'    => '검수관리',
            'reviews'  => $result['list'],
            'total'    => $result['total'],
            'params'   => $params,
        ]);
    }

    // ──────────────────────────────────────────────
    // 검수 상세 (before/after 비교)
    // ──────────────────────────────────────────────

    public function show(int $id): string
    {
        $history = $this->historyModel->getDetail($id);
        if ($history === null) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        return $this->render('admin/reviews/show', [
            'title'   => '검수 상세',
            'history' => $history,
        ]);
    }

    // ──────────────────────────────────────────────
    // 검수 처리 (승인 / 반려)
    // ──────────────────────────────────────────────

    public function action(int $id): ResponseInterface
    {
        $history = $this->historyModel->getDetail($id);
        if ($history === null) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $action = $this->request->getPost('action') ?? '';
        if (!in_array($action, ['approve', 'reject'], true)) {
            return redirect()->back()->with('error', '올바르지 않은 액션입니다.');
        }

        $memo = $this->request->getPost('memo') ?? null;

        /** @var array<string, mixed> $authUser */
        $authUser = session()->get('admin_user');
        $adminId  = (int) ($authUser['id'] ?? 0);

        try {
            $this->historyModel->review($id, $action, $adminId, $memo);

            $nextReviewStatus = $action === 'approve' ? 'approved' : 'rejected';
            $this->campaignModel->update($history['campaign_id'], [
                'review_status' => $nextReviewStatus,
            ]);
        } catch (\RuntimeException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }

        $message = $action === 'approve' ? '소재가 승인되었습니다.' : '소재가 반려되었습니다.';

        return redirect()->to('/admin/reviews')
            ->with('success', $message);
    }
}
