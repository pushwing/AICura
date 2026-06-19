<?php

namespace App\Controllers\Admin;

use App\Models\CampaignModel;
use App\Models\CampaignReviewRequestModel;
use CodeIgniter\HTTP\ResponseInterface;

class ReviewController extends BaseAdminController
{
    private CampaignReviewRequestModel $reviewModel;
    private CampaignModel $campaignModel;

    public function initController(
        \CodeIgniter\HTTP\RequestInterface $request,
        \CodeIgniter\HTTP\ResponseInterface $response,
        \Psr\Log\LoggerInterface $logger
    ): void {
        parent::initController($request, $response, $logger);
        $this->reviewModel   = model(CampaignReviewRequestModel::class);
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

        $result = $this->reviewModel->getPendingList($params);

        return $this->render('admin/reviews/index', [
            'title'   => '검수관리',
            'reviews' => $result['list'],
            'total'   => $result['total'],
            'params'  => $params,
        ]);
    }

    // ──────────────────────────────────────────────
    // 검수 상세 (before/after 비교)
    // ──────────────────────────────────────────────

    public function show(int $id): string
    {
        $detail = $this->reviewModel->getDetail($id);
        if ($detail === null) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        return $this->render('admin/reviews/show', [
            'title'  => '검수 상세',
            'detail' => $detail,
            'adTypes'  => CampaignModel::AD_TYPES,
            'channels' => CampaignModel::CHANNELS,
        ]);
    }

    // ──────────────────────────────────────────────
    // 검수 처리 (승인 / 반려)
    // ──────────────────────────────────────────────

    public function action(int $id): ResponseInterface
    {
        $detail = $this->reviewModel->getDetail($id);
        if ($detail === null) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $action = $this->request->getPost('action') ?? '';
        if (!in_array($action, ['approve', 'reject'], true)) {
            return redirect()->back()->with('error', '올바르지 않은 액션입니다.');
        }

        $memo    = $this->request->getPost('memo') ?? null;

        /** @var array<string, mixed> $authUser */
        $authUser = session()->get('admin_user');
        $adminId  = (int) ($authUser['id'] ?? 0);

        try {
            if ($action === 'approve') {
                $contentFields = $this->reviewModel->approve($id, $adminId, $memo);

                // 검수 승인: review request 의 콘텐츠를 campaigns 에 복사
                $this->campaignModel->update($detail['campaign_id'], array_merge(
                    $contentFields,
                    ['review_status' => 'approved']
                ));
            } else {
                $this->reviewModel->reject($id, $adminId, $memo);

                $this->campaignModel->update($detail['campaign_id'], [
                    'review_status' => 'rejected',
                ]);
            }
        } catch (\RuntimeException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }

        $message = $action === 'approve' ? '검수가 승인되었습니다.' : '검수가 반려되었습니다.';

        return redirect()->to('/admin/reviews')
            ->with('success', $message);
    }
}
