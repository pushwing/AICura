<?php

namespace App\Controllers\Admin;

use Override;
use CodeIgniter\HTTP\RequestInterface;
use Psr\Log\LoggerInterface;
use CodeIgniter\Exceptions\PageNotFoundException;
use RuntimeException;
use App\Models\CampaignModel;
use App\Models\CampaignReviewRequestModel;
use App\Models\ComplianceCheckModel;
use App\Services\AiComplianceService;
use CodeIgniter\HTTP\ResponseInterface;
use Throwable;

class ReviewController extends BaseAdminController
{
    private CampaignReviewRequestModel $reviewModel;
    private CampaignModel $campaignModel;

    #[Override]
    public function initController(
        RequestInterface $request,
        ResponseInterface $response,
        LoggerInterface $logger
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
            throw PageNotFoundException::forPageNotFound();
        }

        $compliance = model(ComplianceCheckModel::class)
            ->latestByCampaign((int) $detail['campaign_id']);

        return $this->render('admin/reviews/show', [
            'title'  => '검수 상세',
            'detail' => $detail,
            'compliance' => $compliance,
            'adTypes'  => CampaignModel::AD_TYPES,
            'channels' => CampaignModel::CHANNELS,
        ]);
    }

    // ──────────────────────────────────────────────
    // 심의 사전검사 재요청 (수동)
    // ──────────────────────────────────────────────

    public function recheck(int $id): ResponseInterface
    {
        $detail = $this->reviewModel->getDetail($id);
        if ($detail === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        try {
            new AiComplianceService()->check((int) $detail['campaign_id'], $id);
        } catch (Throwable $e) {
            log_message('error', '심의 사전검사 재요청 실패 [review {id}]: {msg}', [
                'id'  => $id,
                'msg' => $e->getMessage(),
            ]);

            return redirect()->to('/admin/reviews/' . $id)
                ->with('error', '심의 사전검사에 실패했습니다: ' . $e->getMessage());
        }

        return redirect()->to('/admin/reviews/' . $id)
            ->with('success', '심의 사전검사를 완료했습니다.');
    }

    // ──────────────────────────────────────────────
    // 검수 처리 (승인 / 반려)
    // ──────────────────────────────────────────────

    public function action(int $id): ResponseInterface
    {
        $detail = $this->reviewModel->getDetail($id);
        if ($detail === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        $action = $this->request->getPost('action') ?? '';
        if (!in_array($action, ['approve', 'reject'], true)) {
            return redirect()->back()->with('error', '올바르지 않은 액션입니다.');
        }

        $memo    = $this->request->getPost('memo') ?? null;

        /** @var array<string, mixed> $authUser */
        $authUser = session()->get('admin_user');
        $adminId  = (int) ($authUser['id'] ?? 0);

        $campaignId = (int) $detail['campaign_id'];

        try {
            if ($action === 'approve') {
                $contentFields = $this->reviewModel->approve($id, $adminId, $memo);

                // 이미지 필드가 비어있으면 캠페인의 기존 이미지를 보존 (빈 값 덮어쓰기 방지).
                // 이미지 삭제 기능이 없어 빈 값은 항상 '변경 없음'을 의미하므로,
                // 이미지 없이 만든 검수요청을 승인할 때 기존 썸네일이 지워지는 것을 막는다.
                foreach (['t1_image_name', 't2_image_name', 'd_image_json'] as $imageField) {
                    if (empty($contentFields[$imageField])) {
                        unset($contentFields[$imageField]);
                    }
                }

                // 다른 pending 요청이 남아있으면 캐시는 pending 유지
                $cacheStatus = $this->reviewModel->hasPending($campaignId) ? 'pending' : 'approved';

                // 검수 승인: review request 의 콘텐츠를 campaigns 에 복사
                $this->campaignModel->update($campaignId, array_merge(
                    $contentFields,
                    ['review_status' => $cacheStatus]
                ));
            } else {
                $this->reviewModel->reject($id, $adminId, $memo);

                // 다른 pending 요청이 남아있으면 캐시는 pending 유지
                $cacheStatus = $this->reviewModel->hasPending($campaignId) ? 'pending' : 'rejected';

                $this->campaignModel->update($campaignId, [
                    'review_status' => $cacheStatus,
                ]);
            }
        } catch (RuntimeException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }

        // 검수 승인으로 공개된 이벤트 URL 을 IndexNow 에 제출 (이슈 #152)
        if ($action === 'approve' && $this->campaignModel->isVisibleEvent($campaignId)) {
            service('indexNowService')->submit(base_url('events/' . $campaignId));
        }

        $message = $action === 'approve' ? '검수가 승인되었습니다.' : '검수가 반려되었습니다.';

        return redirect()->to('/admin/reviews')
            ->with('success', $message);
    }
}
