<?php

namespace App\Models;

use CodeIgniter\Database\BaseBuilder;
use CodeIgniter\Model;
use RuntimeException;

class CampaignModel extends Model
{
    // 상태 전이 허용 맵: 현재 상태 → 가능한 다음 상태
    public const STATUS_TRANSITIONS = [
        'pending'  => ['active', 'rejected'],
        'active'   => ['ended'],
        'rejected' => ['pending'],
        'ended'    => [],
    ];
    public const AD_TYPES = [
        1 => 'CPA',
        2 => 'CPM',
        3 => '프로모션',
        4 => 'CPC',
        5 => '옵션',
    ];

    /**
     * 광고 타입별 과금 단가 컬럼 매핑 (이슈 #157).
     *
     * CPA는 db_cost(DB 단가), CPM은 cpm_price(1000회 노출당), CPC는 cpc_price(클릭당)를 사용한다.
     * 프로모션·옵션은 별도 과금 단가가 없다(가격 정보 카드의 text_cost로만 표시).
     *
     * @var array<int, string>
     */
    public const AD_TYPE_COST_FIELD = [
        1 => 'db_cost',
        2 => 'cpm_price',
        4 => 'cpc_price',
    ];

    public const CHANNELS = [
        1 => '굿닥',
        2 => '굿닥파트너스',
    ];

    /**
     * 병원 망 구분 (campaigns.hospital_type)
     */
    public const HOSPITAL_TYPES = [
        1 => '일반',
        2 => '네트워크 모점',
        3 => '네트워크 자점',
    ];

    // ──────────────────────────────────────────────────────────────
    // 외부(소비자) 앱 — 이벤트 조회 (이슈 #98)
    // ──────────────────────────────────────────────────────────────

    /**
     * 목록 응답용 소비자 노출 컬럼 (내부 과금·계약·심의 필드 제외)
     */
    private const string LIST_COLUMNS = 'c.id, c.ad_title, c.hospital_id, c.category, c.region, c.ad_type, '
        . 'c.cost_type, c.general_cost, c.discount_cost, c.text_cost, c.t1_image_name, '
        . 'c.ad_start_date, c.ad_end_date, c.created_at';

    /**
     * 상세 응답용 소비자 노출 컬럼
     */
    private const string DETAIL_COLUMNS = 'c.id, c.ad_title, c.hospital_id, c.category, c.region, c.ad_type, '
        . 'c.cost_type, c.general_cost, c.discount_cost, c.text_cost, c.t1_image_name, c.t2_image_name, '
        . 'c.d_image_json, c.ad_detail_info, c.ad_start_date, c.ad_end_date, c.created_at';

    // ──────────────────────────────────────────────
    // 외부(소비자) 앱 — 이벤트 캐시 무효화 (이슈 #116)
    // ──────────────────────────────────────────────

    /**
     * 소비자 캐시(목록·상세·메인·추천) 무효화용 버전 토큰 캐시 키
     */
    public const CONSUMER_CACHE_VERSION_KEY = 'events_consumer_cache_ver';

    /**
     * 버전 토큰 TTL (초) — 1일. 쓰기 발생 시 즉시 삭제되어 무효화된다.
     */
    private const int CONSUMER_CACHE_VERSION_TTL = 86400;

    protected $table         = 'campaigns';
    protected $primaryKey    = 'id';
    protected $useTimestamps = true;
    protected $returnType    = 'array';
    protected $allowedFields = [
        'ad_title',
        'hospital_id',
        'hospital_type',
        'agency_user_id',
        'user_id',
        'ad_type',
        'ad_start_date',
        'ad_end_date',
        'ad_date_extend',
        'cost_type',
        'general_cost',
        'discount_cost',
        'text_cost',
        'db_cost',
        'cpm_price',
        'cpc_price',
        'category',
        'exposure',
        'contract_id',
        'contract_order_id',
        'contract_name',
        'region',
        'cooperation',
        'sub_hospital_id',
        'keyword',
        'where_image',
        'model_image_count',
        'ad_detail_info',
        'inspect_date',
        'is_view_board',
        'custom_randing',
        'option_ad_id',
        'custom1',
        'custom2',
        'custom3',
        'deliberation_code',
        'status',
        'review_status',
        'channel',
        'is_deleted',
        'del_date',
        'delete_user_id',
        't1_image_name',
        't2_image_name',
        'd_image_json',
    ];

    /**
     * @var array<string, string>
     */
    protected $validationRules = [
        'hospital_id' => 'required|integer',
        'status'      => 'in_list[pending,active,rejected,ended]',
    ];

    // 이슈 #116: 캠페인 쓰기(생성·수정·상태변경·검수승인·삭제) 시 외부 앱 이벤트 캐시(events_*) 일괄 무효화.
    // 모든 어드민 쓰기 경로가 update()/insert()/delete() 를 거치므로 콜백 한 곳으로 커버된다.
    protected $afterInsert = ['clearConsumerCache'];
    protected $afterUpdate = ['clearConsumerCache'];
    protected $afterDelete = ['clearConsumerCache'];

    /**
     * 캠페인 목록 (페이징, 검색, 상태 필터)
     *
     * @param array<string, mixed> $params
     *
     * @return array<string, mixed>
     */
    public function getCampaignList(array $params): array
    {
        // 검수 대기 중인 캠페인은 review request 의 제목을 COALESCE로 표시
        $builder = $this->db->table('campaigns c')
            ->select('c.id, c.status, c.review_status, c.created_at')
            ->select('COALESCE(c.ad_title, crr.ad_title) AS ad_title', false)
            ->select('COALESCE(c.ad_type, crr.ad_type) AS ad_type', false)
            ->select('COALESCE(c.channel, crr.channel) AS channel', false)
            ->select('COALESCE(c.ad_start_date, crr.ad_start_date) AS ad_start_date', false)
            ->select('COALESCE(c.ad_end_date, crr.ad_end_date) AS ad_end_date', false)
            ->select('COALESCE(c.db_cost, crr.db_cost) AS db_cost', false)
            ->select('h.name as hospital_name', false)
            ->select('co.title as contract_name', false)
            ->join('hospitals h', 'h.id = c.hospital_id', 'left')
            ->join('contracts co', 'co.id = c.contract_id', 'left')
            ->join(
                '(SELECT campaign_id, ad_title, ad_type, channel, ad_start_date, ad_end_date, db_cost'
                . ' FROM campaign_review_requests crr_sub'
                . ' WHERE crr_sub.id = (SELECT MAX(id) FROM campaign_review_requests WHERE campaign_id = crr_sub.campaign_id)) crr',
                'crr.campaign_id = c.id',
                'left',
            )
            ->where('c.is_deleted', 0);

        if (! empty($params['status'])) {
            $builder->where('c.status', $params['status']);
        }
        if (! empty($params['review_status'])) {
            $builder->where('c.review_status', $params['review_status']);
        }
        if (! empty($params['ad_type'])) {
            $builder->where('c.ad_type', (int) $params['ad_type']);
        }
        if (! empty($params['channel'])) {
            $builder->where('c.channel', (int) $params['channel']);
        }
        if (! empty($params['keyword'])) {
            $builder->groupStart()
                ->like('c.ad_title', $params['keyword'])
                ->orLike('h.name', $params['keyword'])
                ->groupEnd();
        }

        $total = (clone $builder)->countAllResults(false);

        $page  = max(1, (int) ($params['page'] ?? 1));
        $limit = (int) ($params['limit'] ?? 20);

        $list = $builder
            ->orderBy('c.id', 'DESC')
            ->limit($limit, ($page - 1) * $limit)
            ->get()
            ->getResultArray();

        return ['list' => $list, 'total' => $total];
    }

    /**
     * 소재 관리 목록 (페이징, 검색, 상태·소재유무 필터)
     *
     * 검수 대기 중인 캠페인은 review request 의 콘텐츠를 COALESCE로 표시한다.
     *
     * @param array<string, mixed> $params
     *
     * @return array<string, mixed>
     */
    public function getCreativeList(array $params): array
    {
        $builder = $this->db->table('campaigns c')
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
                'left',
            )
            ->where('c.is_deleted', 0);

        if (! empty($params['status'])) {
            $builder->where('c.status', $params['status']);
        }

        if (! empty($params['keyword'])) {
            $builder->groupStart()
                ->like('COALESCE(c.ad_title, crr.ad_title)', $params['keyword'], 'both', null, false)
                ->orLike('h.name', $params['keyword'])
                ->groupEnd();
        }

        if (($params['has_image'] ?? '') === '1') {
            $builder->where("(c.t1_image_name IS NOT NULL AND c.t1_image_name != '') OR (crr.t1_image_name IS NOT NULL AND crr.t1_image_name != '')", null, false);
        } elseif (($params['has_image'] ?? '') === '0') {
            $builder->where("(c.t1_image_name IS NULL OR c.t1_image_name = '') AND (crr.t1_image_name IS NULL OR crr.t1_image_name = '')", null, false);
        }

        $total = (clone $builder)->countAllResults(false);

        $page  = max(1, (int) ($params['page'] ?? 1));
        $limit = (int) ($params['limit'] ?? 20);

        $list = $builder
            ->orderBy('c.id', 'DESC')
            ->limit($limit, ($page - 1) * $limit)
            ->get()
            ->getResultArray();

        return ['list' => $list, 'total' => $total];
    }

    /**
     * 캠페인 상세 (JOIN: hospital, contract, packages)
     *
     * @return array<string, mixed>|null
     */
    public function getCampaignDetail(int $id): ?array
    {
        $row = $this->db->table('campaigns c')
            ->select('c.*')
            ->select('h.name as hospital_name, h.type as hospital_type_label', false)
            ->select('co.title as contract_title', false)
            ->select('ec.title as category_title', false)
            ->join('hospitals h', 'h.id = c.hospital_id', 'left')
            ->join('contracts co', 'co.id = c.contract_id', 'left')
            ->join('event_categories ec', 'ec.id = c.category', 'left')
            ->where('c.id', $id)
            ->where('c.is_deleted', 0)
            ->get()
            ->getRowArray();

        if ($row === null) {
            return null;
        }

        // 연결된 패키지 목록
        $row['packages'] = $this->db->table('campaign_packages cp')
            ->select('cp.id, cp.title, cp.status, cp.start_date, cp.end_date')
            ->join('campaign_package_map cpm', 'cpm.campaign_package_id = cp.id')
            ->where('cpm.campaign_id', $id)
            ->where('cp.is_deleted', 0)
            ->orderBy('cpm.sort_order', 'ASC')
            ->get()
            ->getResultArray();

        return $row;
    }

    /**
     * 상태 변경 (상태 전이 검증 포함)
     *
     * @throws RuntimeException 허용되지 않는 상태 전이
     */
    public function updateStatus(int $id, string $action): string
    {
        $campaign = $this->select('id, status')->find($id);
        if ($campaign === null) {
            throw new RuntimeException('캠페인을 찾을 수 없습니다.');
        }

        $currentStatus = $campaign['status'];
        $nextStatus    = match ($action) {
            'approve' => 'active',
            'reject'  => 'rejected',
            'end'     => 'ended',
            'reopen'  => 'pending',
            default   => throw new RuntimeException('알 수 없는 액션입니다.'),
        };

        $allowed = self::STATUS_TRANSITIONS[$currentStatus] ?? [];
        if (! in_array($nextStatus, $allowed, true)) {
            throw new RuntimeException(
                sprintf('"%s" 상태에서 "%s"로 변경할 수 없습니다.', $currentStatus, $nextStatus),
            );
        }

        $this->update($id, ['status' => $nextStatus]);

        return $nextStatus;
    }

    /**
     * 히스토리 목록 (campaign_histories 테이블)
     *
     * @param array<string, mixed> $params
     *
     * @return array<string, mixed>
     */
    public function getHistoryList(int $campaignId, array $params = []): array
    {
        $builder = $this->db->table('campaign_histories ch')
            ->select('ch.id, ch.action, ch.status_from, ch.status_to, ch.memo, ch.created_at')
            ->select('u.username as admin_name', false)
            ->join('users u', 'u.id = ch.admin_user_id', 'left')
            ->where('ch.campaign_id', $campaignId);

        $total = (clone $builder)->countAllResults(false);

        $page  = max(1, (int) ($params['page'] ?? 1));
        $limit = (int) ($params['limit'] ?? 20);

        $list = $builder
            ->orderBy('ch.id', 'DESC')
            ->limit($limit, ($page - 1) * $limit)
            ->get()
            ->getResultArray();

        return ['list' => $list, 'total' => $total];
    }

    /**
     * 연결된 패키지 목록
     *
     * @return array<int, array<string, mixed>>
     */
    public function getPackageList(int $campaignId): array
    {
        return $this->db->table('campaign_packages cp')
            ->select('cp.id, cp.title, cp.banner_view_type, cp.view_type, cp.start_date, cp.end_date, cp.status, cpm.sort_order')
            ->join('campaign_package_map cpm', 'cpm.campaign_package_id = cp.id')
            ->where('cpm.campaign_id', $campaignId)
            ->where('cp.is_deleted', 0)
            ->orderBy('cpm.sort_order', 'ASC')
            ->get()
            ->getResultArray();
    }

    /**
     * 소비자 노출 조건을 빌더에 적용한다.
     *
     * 노출: status='active' · review_status='approved'(검수완료) · is_deleted=0
     *       · exposure IN(1 이벤트, 3 둘다) · 노출기간 내(기간 없으면 항상)
     *
     * 검수 미완료(pending·rejected) 이벤트는 의료광고법 저촉 가능성으로 노출을 금지한다(이슈 #137).
     * status(캠페인 게재)와 review_status(검수)는 독립 축이므로 둘 다 충족해야 노출된다.
     */
    private function applyConsumerFilters(BaseBuilder $builder, string $alias = 'c'): void
    {
        $today = date('Y-m-d');

        $builder->where("{$alias}.status", 'active')
            ->where("{$alias}.review_status", 'approved')
            ->where("{$alias}.is_deleted", 0)
            ->whereIn("{$alias}.exposure", [1, 3])
            ->groupStart()
            ->where("{$alias}.ad_start_date IS NULL", null, false)
            ->orWhere("{$alias}.ad_start_date <=", $today)
            ->groupEnd()
            ->groupStart()
            ->where("{$alias}.ad_end_date IS NULL", null, false)
            ->orWhere("{$alias}.ad_end_date >=", $today)
            ->groupEnd();
    }

    /**
     * 이벤트 목록 — 카테고리·지역·검색 필터, 정렬(latest/price_asc/price_desc/popular), 페이징.
     *
     * @param array<string, mixed> $params category·region·keyword·sort·page·limit
     *
     * @return array{list: array<int, array<string, mixed>>, total: int}
     */
    public function getEventList(array $params): array
    {
        $sort = (string) ($params['sort'] ?? 'latest');

        $builder = $this->db->table('campaigns c')
            ->select(self::LIST_COLUMNS)
            ->select('h.name AS hospital_name', false)
            ->select('ec.title AS category_title', false)
            ->join('hospitals h', 'h.id = c.hospital_id', 'left')
            ->join('event_categories ec', 'ec.id = c.category', 'left');

        $this->applyConsumerFilters($builder);

        if (! empty($params['hospital_id'])) {
            $builder->where('c.hospital_id', (int) $params['hospital_id']);
        }
        if (! empty($params['category'])) {
            $builder->where('c.category', (int) $params['category']);
        }
        if (! empty($params['region'])) {
            $builder->like('c.region', (string) $params['region']);
        }
        if (! empty($params['keyword'])) {
            $builder->groupStart()
                ->like('c.ad_title', (string) $params['keyword'])
                ->orLike('h.name', (string) $params['keyword'])
                ->groupEnd();
        }

        // 인기순 — 상담신청(call_requests) 수 기준 (A안: 인라인 집계)
        if ($sort === 'popular') {
            $builder->select('COALESCE(cr.cnt, 0) AS request_count', false)
                ->join(
                    '(SELECT campaign_id, COUNT(*) AS cnt FROM call_requests WHERE is_delete = 0 GROUP BY campaign_id) cr',
                    'cr.campaign_id = c.id',
                    'left',
                );
        }

        $total = (clone $builder)->countAllResults(false);

        match ($sort) {
            'price_asc'  => $builder->orderBy('c.discount_cost', 'ASC')->orderBy('c.id', 'DESC'),
            'price_desc' => $builder->orderBy('c.discount_cost', 'DESC')->orderBy('c.id', 'DESC'),
            'popular'    => $builder->orderBy('request_count', 'DESC')->orderBy('c.id', 'DESC'),
            default      => $builder->orderBy('c.id', 'DESC'),
        };

        $page  = max(1, (int) ($params['page'] ?? 1));
        $limit = max(1, (int) ($params['limit'] ?? 20));

        $list = $builder
            ->limit($limit, ($page - 1) * $limit)
            ->get()
            ->getResultArray();

        // 인기순 집계값(request_count) 등 내부 필드는 EventService::transformListItem 의
        // 화이트리스트 변환에서 응답에 포함되지 않는다.
        return ['list' => $list, 'total' => (int) $total];
    }

    /**
     * 이벤트 상세 — 노출 조건 충족 건만, 병원·카테고리 조인.
     *
     * @return array<string, mixed>|null
     */
    public function getEventDetail(int $id): ?array
    {
        $builder = $this->db->table('campaigns c')
            ->select(self::DETAIL_COLUMNS)
            ->select('h.name AS hospital_name, h.address AS hospital_address, h.phone AS hospital_phone', false)
            ->select('ec.title AS category_title', false)
            ->join('hospitals h', 'h.id = c.hospital_id', 'left')
            ->join('event_categories ec', 'ec.id = c.category', 'left')
            ->where('c.id', $id);

        $this->applyConsumerFilters($builder);

        return $builder->get()->getRowArray();
    }

    /**
     * 소비자 캐시 키에 끼워 넣는 버전 토큰.
     *
     * 캠페인 쓰기 시 토큰이 삭제되며, 다음 조회에서 새 토큰이 발급되어 이전 버전으로
     * 저장된 목록·상세·메인·추천 캐시는 모두 자연스럽게 무시된다(TTL 만료로 정리).
     */
    public function consumerCacheVersion(): string
    {
        /** @var string|null $ver */
        $ver = cache(self::CONSUMER_CACHE_VERSION_KEY);
        if (! is_string($ver)) {
            $ver = bin2hex(random_bytes(4));
            cache()->save(self::CONSUMER_CACHE_VERSION_KEY, $ver, self::CONSUMER_CACHE_VERSION_TTL);
        }

        return $ver;
    }

    /**
     * 소비자 캐시(목록·상세·메인·추천) 일괄 무효화.
     *
     * 캠페인 쓰기는 모델 콜백(clearConsumerCache)이 자동 처리하지만, ad_main_maps·
     * ad_recommend_maps 매핑 변경처럼 campaigns 테이블을 직접 건드리지 않는 변경은
     * 이 메서드로 명시적으로 무효화한다.
     */
    public function invalidateConsumerCache(): void
    {
        cache()->delete(self::CONSUMER_CACHE_VERSION_KEY);
    }

    /**
     * 모델 쓰기 콜백 — 버전 토큰을 삭제해 소비자 캐시를 일괄 무효화한다.
     *
     * @param array<string, mixed> $data
     *
     * @return array<string, mixed>
     */
    protected function clearConsumerCache(array $data): array
    {
        cache()->delete(self::CONSUMER_CACHE_VERSION_KEY);

        return $data;
    }

    /**
     * 메인 노출 이벤트 — ad_main_maps(is_main=1, is_inspect=1) 기준.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getMainEvents(int $limit = 10): array
    {
        $builder = $this->db->table('ad_main_maps amm')
            ->select(self::LIST_COLUMNS)
            ->select('h.name AS hospital_name', false)
            ->select('ec.title AS category_title', false)
            ->select('MAX(amm.id) AS main_order', false)
            ->join('campaigns c', 'c.id = amm.campaign_id')
            ->join('hospitals h', 'h.id = c.hospital_id', 'left')
            ->join('event_categories ec', 'ec.id = c.category', 'left')
            ->where('amm.is_main', 1)
            ->where('amm.is_inspect', 1);

        $this->applyConsumerFilters($builder);

        // ad_main_maps 는 캠페인당 1:N(버전 히스토리) 이므로 캠페인 기준으로 중복 제거.
        // 정렬은 최신 매핑(MAX(amm.id)) 기준 — ONLY_FULL_GROUP_BY 호환
        return $builder->groupBy('c.id')
            ->orderBy('main_order', 'DESC')
            ->limit($limit)
            ->get()
            ->getResultArray();
    }

    /**
     * 추천 이벤트 — ad_recommend_maps(is_delete=0), ads_order 정렬.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getRecommendEvents(int $limit = 10): array
    {
        $builder = $this->db->table('ad_recommend_maps arm')
            ->select(self::LIST_COLUMNS)
            ->select('h.name AS hospital_name', false)
            ->select('ec.title AS category_title', false)
            ->select('MIN(arm.ads_order) AS recommend_order', false)
            ->join('campaigns c', 'c.id = arm.campaign_id')
            ->join('hospitals h', 'h.id = c.hospital_id', 'left')
            ->join('event_categories ec', 'ec.id = c.category', 'left')
            ->where('arm.is_delete', 0);

        $this->applyConsumerFilters($builder);

        // ad_recommend_maps 도 캠페인당 다중 행이 가능하므로 캠페인 기준 중복 제거.
        // 정렬은 가장 앞선 노출순서(MIN(ads_order)) 기준 — ONLY_FULL_GROUP_BY 호환
        return $builder->groupBy('c.id')
            ->orderBy('recommend_order', 'ASC')
            ->limit($limit)
            ->get()
            ->getResultArray();
    }

    /**
     * 노출 조건을 충족하는 이벤트인지 확인 — 찜 토글 전 대상 유효성 검사용.
     */
    public function isVisibleEvent(int $id): bool
    {
        $builder = $this->db->table('campaigns c')->where('c.id', $id);
        $this->applyConsumerFilters($builder);

        return $builder->countAllResults() > 0;
    }

    /**
     * sitemap.xml 용 노출 이벤트 목록 — 노출 조건 충족 건의 id·갱신시각만. (이슈 #143)
     *
     * sitemaps.org 단일 파일 상한(50,000)을 기본 한도로 둔다.
     *
     * @return array<int, array{id: int, updated_at: string|null}>
     */
    public function getSitemapEvents(int $limit = 50000): array
    {
        $builder = $this->db->table('campaigns c')->select('c.id, c.updated_at');
        $this->applyConsumerFilters($builder);

        /** @var array<int, array{id: int, updated_at: string|null}> $rows */
        $rows = $builder->orderBy('c.updated_at', 'DESC')
            ->limit($limit)
            ->get()
            ->getResultArray();

        return $rows;
    }

    /**
     * 상담 신청 대상 캠페인 정보 — 노출 조건 충족 건만 (병원·CPA 단가). (이슈 #100)
     *
     * @return array{id: int, hospital_id: int, db_cost: int}|null
     */
    public function getApplyTarget(int $id): ?array
    {
        $builder = $this->db->table('campaigns c')
            ->select('c.id, c.hospital_id, c.db_cost')
            ->where('c.id', $id);
        $this->applyConsumerFilters($builder);

        $row = $builder->get()->getRowArray();
        if ($row === null) {
            return null;
        }

        return [
            'id'          => (int) $row['id'],
            'hospital_id' => (int) $row['hospital_id'],
            'db_cost'     => (int) $row['db_cost'],
        ];
    }
}
