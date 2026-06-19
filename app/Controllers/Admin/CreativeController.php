<?php

namespace App\Controllers\Admin;

use App\Models\CampaignModel;
use App\Models\CreativeHistoryModel;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * 소재 관리 컨트롤러
 *
 * campaigns 테이블의 t1_image_name, t2_image_name, d_image_json 관리.
 * 캠페인별 광고 크리에이티브(이미지 소재) 등록·수정·삭제.
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

        return $this->render('admin/creatives/show', [
            'title'    => '소재 상세',
            'campaign' => $campaign,
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

        $afterData = [
            't1_image_name' => $this->request->getPost('t1_image_name') ?? '',
            't2_image_name' => $this->request->getPost('t2_image_name') ?? '',
            'd_image_json'  => count($dImages) > 0 ? json_encode($dImages, JSON_UNESCAPED_UNICODE) : null,
        ];

        $campaignModel->update($id, array_merge($afterData, ['review_status' => 'pending']));

        /** @var array<string, mixed> $authUser */
        $authUser = session()->get('admin_user');

        model(CreativeHistoryModel::class)->record(
            $id,
            [
                't1_image_name' => $campaign['t1_image_name'] ?? null,
                't2_image_name' => $campaign['t2_image_name'] ?? null,
                'd_image_json'  => $campaign['d_image_json'] ?? null,
            ],
            $afterData,
            (int) ($authUser['id'] ?? 0)
        );

        return redirect()->to('/admin/creatives/' . $id)
            ->with('success', '소재가 업데이트되었습니다. 검수 대기 상태로 변경되었습니다.');
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
            ->select('c.id, c.ad_title, c.status, c.ad_type, c.channel, c.t1_image_name, c.t2_image_name, c.d_image_json, c.created_at')
            ->select('h.name AS hospital_name', false)
            ->join('hospitals h', 'h.id = c.hospital_id', 'left')
            ->where('c.is_deleted', 0);

        if ($params['status'] !== '') {
            $builder->where('c.status', $params['status']);
        }

        if ($params['keyword'] !== '') {
            $builder->groupStart()
                ->like('c.ad_title', $params['keyword'])
                ->orLike('h.name', $params['keyword'])
                ->groupEnd();
        }

        if ($params['has_image'] === '1') {
            $builder->where('c.t1_image_name IS NOT NULL AND c.t1_image_name != \'\'', null, false);
        } elseif ($params['has_image'] === '0') {
            $builder->groupStart()
                ->where('c.t1_image_name IS NULL', null, false)
                ->orWhere('c.t1_image_name', '')
                ->groupEnd();
        }

        $total = (clone $builder)->countAllResults(false);

        $page  = $params['page'];
        $limit = $params['limit'];

        $list = $builder
            ->orderBy('c.id', 'DESC')
            ->limit($limit, ($page - 1) * $limit)
            ->get()
            ->getResultArray();

        return ['list' => $list, 'total' => $total];
    }
}
