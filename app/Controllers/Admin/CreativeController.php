<?php

namespace App\Controllers\Admin;

use App\Models\CampaignModel;
use App\Models\CampaignReviewRequestModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * 소재 관리 컨트롤러
 *
 * 이미지 소재(t1, t2, 상세) 수정 — 수정 시 campaign_review_requests 에 기록되고
 * 기존 campaigns 콘텐츠는 검수 승인 전까지 변경되지 않는다.
 */
class CreativeController extends BaseAdminController
{
    // ──────────────────────────────────────────────
    // 소재 목록
    // ──────────────────────────────────────────────

    public function index(): string
    {
        $params = [
            'keyword'   => $this->request->getGet('keyword') ?? '',
            'status'    => $this->request->getGet('status') ?? '',
            'has_image' => $this->request->getGet('has_image') ?? '',
            'page'      => max(1, (int) ($this->request->getGet('page') ?? 1)),
            'limit'     => 20,
        ];

        $result = model(CampaignModel::class)->getCreativeList($params);

        return $this->render('admin/creatives/index', [
            'title'     => '소재 관리',
            'creatives' => $result['list'],
            'total'     => $result['total'],
            'params'    => $params,
        ]);
    }

    // ──────────────────────────────────────────────
    // 소재 상세 (캠페인의 이미지 소재 보기/수정)
    // ──────────────────────────────────────────────

    public function show(int $id): string
    {
        $campaign = model(CampaignModel::class)->getCampaignDetail($id);
        if ($campaign === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        // 검수 대기 중인 이미지 데이터가 있으면 폼에 pre-populate
        $pending = model(CampaignReviewRequestModel::class)->getLatestPending($id);

        return $this->render('admin/creatives/show', [
            'title'    => '소재 상세',
            'campaign' => $campaign,
            'pending'  => $pending,
        ]);
    }

    // ──────────────────────────────────────────────
    // 소재 수정 (이미지 URL 직접 입력)
    // ──────────────────────────────────────────────

    public function update(int $id): ResponseInterface
    {
        $campaignModel = model(CampaignModel::class);

        $campaign = $campaignModel->find($id);
        if ($campaign === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        // 이미지 필드는 URL 또는 파일명 — 컬럼 길이(VARCHAR 500) 기준 검증
        $rules = [
            't1_image_name' => 'permit_empty|max_length[500]',
            't2_image_name' => 'permit_empty|max_length[500]',
            'd_images.*'    => 'permit_empty|max_length[500]',
        ];
        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $dImagesRaw = $this->request->getPost('d_images') ?? [];
        $dImages    = array_values(array_filter(
            is_array($dImagesRaw) ? $dImagesRaw : [],
            static fn ($v): bool => is_string($v) && trim($v) !== '',
        ));

        // 이미지 변경 데이터 — campaigns 에는 직접 쓰지 않고 review request 로 기록
        $imageData = [
            't1_image_name' => $this->request->getPost('t1_image_name') ?? '',
            't2_image_name' => $this->request->getPost('t2_image_name') ?? '',
            'd_image_json'  => count($dImages) > 0 ? json_encode($dImages, JSON_UNESCAPED_UNICODE) : null,
        ];

        // 나머지 콘텐츠 필드는 기존 approved 데이터 유지 (이미지만 변경하는 경우)
        $contentData = [];

        foreach (CampaignReviewRequestModel::CONTENT_FIELDS as $field) {
            $contentData[$field] = $imageData[$field] ?? $campaign[$field] ?? null;
        }

        /** @var array<string, mixed> $authUser */
        $authUser = session()->get('admin_user');

        model(CampaignReviewRequestModel::class)->record(
            $id,
            $contentData,
            (int) ($authUser['id'] ?? 0),
            'update',
        );

        $campaignModel->update($id, ['review_status' => 'pending']);

        return redirect()->to('/admin/creatives/' . $id)
            ->with('success', '소재 변경이 검수 요청되었습니다.');
    }
}
