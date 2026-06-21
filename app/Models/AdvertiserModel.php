<?php

namespace App\Models;

use CodeIgniter\Model;

class AdvertiserModel extends Model
{
    protected $table      = 'advertisers';
    protected $primaryKey = 'id';
    protected $useTimestamps = true;
    protected $returnType    = 'array';

    protected $allowedFields = [
        'hospital_id',
        'hospital_name',
        'contact_name',
        'contact_email',
        'contact_phone',
        'business_no',
        'is_network',
        'network_parent_id',
        'status',
        'agency_user_id',
        'owner_user_id',
        'contract_agreed_at',
    ];

    // Fix #1: 광고주 저장 시 KPI 캐시 무효화
    protected $afterInsert = ['clearKpiCache'];
    protected $afterUpdate = ['clearKpiCache'];

    /** @var array<string, string> */
    protected $validationRules = [
        'hospital_id'   => 'required|integer|greater_than[0]',
        'hospital_name' => 'required|max_length[255]',
        'contact_email' => 'permit_empty|valid_email|max_length[255]',
        'contact_phone' => 'permit_empty|max_length[30]',
        'business_no'   => 'permit_empty|max_length[50]',
        'is_network'    => 'required|in_list[0,1,2]',
        'status'        => 'required|in_list[1,2,3]',
    ];

    /**
     * @param array<string, mixed> $params
     * @return array{list: list<array<string, mixed>>, total: int}
     */
    public function getList(array $params): array
    {
        // Fix #9: hospital_id는 인덱스 그리드에서 불필요 — 제외하여 노출 최소화
        $builder = $this->db->table('advertisers a')
            ->select('a.id, a.hospital_name, a.contact_name, a.contact_phone, a.is_network, a.status, a.created_at')
            ->select('pa.hospital_name AS parent_name', false)
            ->join('advertisers pa', 'pa.id = a.network_parent_id', 'left');

        if (!empty($params['hospital_name'])) {
            $builder->like('a.hospital_name', $params['hospital_name']);
        }
        if (isset($params['status']) && $params['status'] !== '') {
            $builder->where('a.status', (int) $params['status']);
        }
        if (isset($params['is_network']) && $params['is_network'] !== '') {
            $builder->where('a.is_network', (int) $params['is_network']);
        }

        $total = (clone $builder)->countAllResults(false);

        $page  = max(1, (int) ($params['page'] ?? 1));
        $limit = (int) ($params['limit'] ?? 20);

        /** @var list<array<string, mixed>> $list */
        $list = $builder
            ->orderBy('a.id', 'DESC')
            ->limit($limit, ($page - 1) * $limit)
            ->get()
            ->getResultArray();

        return ['list' => $list, 'total' => (int) $total];
    }

    /**
     * 광고주 상세 + 네트워크 관계 + 계약 목록 + KPI
     *
     * Fix #5: 의존 테이블 — contracts(000001), contract_orders(000002),
     *         contract_order_connects(000003), deposits(000004), campaigns(000006)
     *
     * @return array<string, mixed>|null
     */
    public function getDetail(int $id): ?array
    {
        $advertiser = $this->find($id);
        if ($advertiser === null) {
            return null;
        }

        $isNetwork = (int) $advertiser['is_network'];

        if ($isNetwork === 1) {
            $advertiser['children'] = $this->db->table('advertisers')
                ->select('id, hospital_name, status')
                ->where('network_parent_id', $id)
                ->get()
                ->getResultArray();
        }

        if ($isNetwork === 2 && !empty($advertiser['network_parent_id'])) {
            $advertiser['parent'] = $this->db->table('advertisers')
                ->select('id, hospital_name, status')
                ->where('id', (int) $advertiser['network_parent_id'])
                ->get()
                ->getRowArray();
        }

        $advertiser['contracts'] = $this->db->table('contracts')
            ->select('id, title, pay_type, created_at')
            ->where('hospital_id', (int) $advertiser['hospital_id'])
            ->orderBy('id', 'DESC')
            ->get()
            ->getResultArray();

        $advertiser['kpi'] = $this->getKpi((int) $advertiser['hospital_id']);

        return $advertiser;
    }

    /**
     * Fix #1: 집계 쿼리 결과 5분 캐시
     *
     * @return array<string, int>
     */
    private function getKpi(int $hospitalId): array
    {
        $cacheKey = 'advertisers_kpi_' . $hospitalId;
        /** @var array<string, int>|null $cached */
        $cached = cache($cacheKey);
        if (is_array($cached)) {
            return $cached;
        }

        $totalRow = $this->db->table('contract_orders co')
            ->select('IFNULL(SUM(co.ad_price), 0) AS total', false)
            ->join('contract_order_connects coc', 'coc.contract_order_id = co.id')
            ->join('contracts c', 'c.id = coc.contract_id')
            ->where('c.hospital_id', $hospitalId)
            ->where('co.contract_status', 1)
            ->get()
            ->getRow();
        $totalAmount = (int) ($totalRow->total ?? 0);

        $balanceRow = $this->db->table('deposits d')
            ->select('IFNULL(SUM(CASE WHEN d.is_minus = 0 THEN d.price ELSE -d.price END), 0) AS balance', false)
            ->join('contracts c', 'c.id = d.contract_id')
            ->where('c.hospital_id', $hospitalId)
            ->get()
            ->getRow();
        $balance = (int) ($balanceRow->balance ?? 0);

        $activeCampaigns = (int) $this->db->table('campaigns')
            ->where('hospital_id', $hospitalId)
            ->where('status', 'active')
            ->where('is_deleted', 0)
            ->countAllResults();

        $result = [
            'total_amount'     => $totalAmount,
            'balance'          => $balance,
            'active_campaigns' => $activeCampaigns,
        ];

        cache()->save($cacheKey, $result, 300);

        return $result;
    }

    /**
     * afterInsert/afterUpdate 콜백 — KPI 캐시 무효화
     *
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    protected function clearKpiCache(array $data): array
    {
        $hospitalId = null;

        /** @var array<string, mixed> $payload */
        $payload = is_array($data['data'] ?? null) ? $data['data'] : [];
        if (!empty($payload['hospital_id'])) {
            $hospitalId = (int) $payload['hospital_id'];
        } else {
            $rawId = $data['id'] ?? null;
            if (is_array($rawId)) {
                $rawId = $rawId[0] ?? null;
            }
            $rowId = is_numeric($rawId) ? (int) $rawId : 0;
            if ($rowId > 0) {
                $row = $this->select('hospital_id')->find($rowId);
                if (is_array($row)) {
                    $hospitalId = (int) $row['hospital_id'];
                }
            }
        }

        if ($hospitalId !== null) {
            cache()->delete('advertisers_kpi_' . $hospitalId);
        }

        return $data;
    }

    // ──────────────────────────────────────────────
    // 포털 스코프 조회 (이슈 #32)
    // ──────────────────────────────────────────────

    /**
     * 특정 대행사가 소유한 광고주 목록 (검색·페이징)
     *
     * @param array<string, mixed> $params
     * @return array{list: list<array<string, mixed>>, total: int}
     */
    public function getListByAgency(int $agencyUserId, array $params): array
    {
        $builder = $this->db->table('advertisers a')
            ->select('a.id, a.hospital_id, a.hospital_name, a.contact_name, a.contact_phone, a.status, a.contract_agreed_at, a.created_at')
            ->where('a.agency_user_id', $agencyUserId);

        if (!empty($params['hospital_name'])) {
            $builder->like('a.hospital_name', $params['hospital_name']);
        }
        if (isset($params['status']) && $params['status'] !== '') {
            $builder->where('a.status', (int) $params['status']);
        }

        $total = (clone $builder)->countAllResults(false);

        $page  = max(1, (int) ($params['page'] ?? 1));
        $limit = max(1, (int) ($params['limit'] ?? 20));

        /** @var list<array<string, mixed>> $list */
        $list = $builder
            ->orderBy('a.id', 'DESC')
            ->limit($limit, ($page - 1) * $limit)
            ->get()
            ->getResultArray();

        return ['list' => $list, 'total' => (int) $total];
    }

    /**
     * 광고주 본인 로그인 계정으로 광고주 레코드 조회 (없으면 null)
     *
     * @return array<string, mixed>|null
     */
    public function findByOwner(int $ownerUserId): ?array
    {
        return $this->where('owner_user_id', $ownerUserId)->first();
    }

    /**
     * 대행사 소유 + 단건 조회 (소유권 검증용)
     *
     * @return array<string, mixed>|null
     */
    public function findOwnedByAgency(int $id, int $agencyUserId): ?array
    {
        return $this->where('id', $id)
            ->where('agency_user_id', $agencyUserId)
            ->first();
    }
}
