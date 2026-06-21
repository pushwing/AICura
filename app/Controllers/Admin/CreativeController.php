<?php

namespace App\Controllers\Admin;

use App\Models\CampaignModel;
use App\Models\CampaignReviewRequestModel;
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
            'keyword'    => $this->request->getGet('keyword') ?? '',
            'status'     => $this->request->getGet('status') ?? '',
            'has_image'  => $this->request->getGet('has_image') ?? '',
            'page'       => max(1, (int) ($this->request->getGet('page') ?? 1)),
            'limit'      => 20,
        ];

        $result = $this->getCreativeList($params);

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
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
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
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $dImagesRaw = $this->request->getPost('d_images') ?? [];
        $dImages    = array_values(array_filter(
            is_array($dImagesRaw) ? $dImagesRaw : [],
            fn($v) => is_string($v) && trim($v) !== ''
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
            'update'
        );

        $campaignModel->update($id, ['review_status' => 'pending']);

        return redirect()->to('/admin/creatives/' . $id)
            ->with('success', '소재 변경이 검수 요청되었습니다.');
    }

    // ──────────────────────────────────────────────
    // private
    // ──────────────────────────────────────────────

    /**
     * @param array<string, mixed> $params
     * @return array<string, mixed>
     */
    private function getCreativeList(array $params): array
    {
        $db      = \Config\Database::connect();
        $builder = $db->table('campaigns c')
            ->select('c.id, c.status, c.review_status, c.created_at')
            ->select('COALESCE(c.ad_title, crr.ad_title) AS ad_title', false)
            ->select('COALESCE(c.ad_type, crr.ad_type) AS ad_type', false)
            ->select('COALESCE(c.channel, crr.channel) AS channel', false)
            ->select('COALESCE(c.t1_image_name, crr.t1_image_name) AS t1_image_name', false)
            ->select('COALESCE(c.t2_image_name, crr.t2_image_name) AS t2_image_name', false)
            ->select('COALESCE(c.d_image_json, crr.d_image_json) AS d_image_json', false)
            ->select('h.name AS hospital_name', false)
            ->join('hospitals h', 'h.id = c.hospital_id', 'left')
            ->join(
                '(SELECT campaign_id, ad_title, ad_type, channel, t1_image_name, t2_image_name, d_image_json'
                . ' FROM campaign_review_requests crr_sub'
                . ' WHERE crr_sub.id = (SELECT MAX(id) FROM campaign_review_requests WHERE campaign_id = crr_sub.campaign_id)) crr',
                'crr.campaign_id = c.id',
                'left'
            )
            ->where('c.is_deleted', 0);

        if ($params['status'] !== '') {
            $builder->where('c.status', $params['status']);
        }

        if ($params['keyword'] !== '') {
            $builder->groupStart()
                ->like('COALESCE(c.ad_title, crr.ad_title)', $params['keyword'], 'both', null, false)
                ->orLike('h.name', $params['keyword'])
                ->groupEnd();
        }

        if ($params['has_image'] === '1') {
            $builder->where('(c.t1_image_name IS NOT NULL AND c.t1_image_name != \'\') OR (crr.t1_image_name IS NOT NULL AND crr.t1_image_name != \'\')', null, false);
        } elseif ($params['has_image'] === '0') {
            $builder->where('(c.t1_image_name IS NULL OR c.t1_image_name = \'\') AND (crr.t1_image_name IS NULL OR crr.t1_image_name = \'\')', null, false);
        }

        $total = (clone $builder)->countAllResults(false);

        $list = $builder
            ->orderBy('c.id', 'DESC')
            ->limit($params['limit'], ($params['page'] - 1) * $params['limit'])
            ->get()
            ->getResultArray();

        return ['list' => $list, 'total' => $total];
    }
}
