<?php

namespace App\Models;

use CodeIgniter\Model;

class HospitalModel extends Model
{
    protected $table      = 'hospitals';
    protected $primaryKey = 'id';
    protected $useTimestamps = true;
    protected $returnType    = 'array';

    protected $allowedFields = [
        'name',
        'type',
        'phone',
        'address',
        'status',
        'is_deleted',
    ];

    // Fix #7: 병원 목록 변경 시 활성 목록 캐시 무효화
    protected $afterInsert = ['clearActiveListCache'];
    protected $afterUpdate = ['clearActiveListCache'];
    protected $afterDelete = ['clearActiveListCache'];

    /** @var array<string, string> */
    protected $validationRules = [
        'name'   => 'required|max_length[255]',
        'type'   => 'required|in_list[1,2,3]',
        'status' => 'in_list[active,inactive]',
    ];

    /**
     * @param array<string, mixed> $params
     * @return array{list: list<array<string, mixed>>, total: int}
     */
    public function getList(array $params): array
    {
        $builder = $this->db->table('hospitals')
            ->select('id, name, type, phone, status, created_at')
            ->where('is_deleted', 0);

        if (!empty($params['name'])) {
            $builder->like('name', $params['name']);
        }
        if (!empty($params['status'])) {
            $builder->where('status', $params['status']);
        }

        $total = (clone $builder)->countAllResults(false);

        $page  = max(1, (int) ($params['page'] ?? 1));
        $limit = (int) ($params['limit'] ?? 20);

        $list = $builder
            ->orderBy('id', 'DESC')
            ->limit($limit, ($page - 1) * $limit)
            ->get()
            ->getResultArray();

        return ['list' => $list, 'total' => (int) $total];
    }

    /**
     * Fix #7: 결과를 10분 캐시 — 폼 드롭다운용, 변경 빈도 낮음
     *
     * @return list<array<string, mixed>>
     */
    public function getActiveList(): array
    {
        $cacheKey = 'hospitals_active_list';
        /** @var list<array<string, mixed>>|null $cached */
        $cached = cache($cacheKey);
        if (is_array($cached)) {
            return $cached;
        }

        /** @var list<array<string, mixed>> $list */
        $list = $this->select('id, name, type')
            ->where('is_deleted', 0)
            ->where('status', 'active')
            ->orderBy('name', 'ASC')
            ->findAll();

        cache()->save($cacheKey, $list, 600);

        return $list;
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    protected function clearActiveListCache(array $data): array
    {
        cache()->delete('hospitals_active_list');
        return $data;
    }

    // ──────────────────────────────────────────────
    // 외부(소비자) 앱 — 병원 조회 (이슈 #99)
    // ──────────────────────────────────────────────

    /** boards.type — 병원 후기 (별점 요약 조인용) */
    private const REVIEW_TYPE_HOSPITAL = 2;

    /**
     * 외부 앱 병원 목록 — 활성 병원만, 이름·지역(주소) 필터, 별점 요약 조인, 페이징.
     *
     * 주의: hospitals 에는 진료과(department) 컬럼이 없어 지역은 address LIKE 로 처리한다.
     *
     * @param array<string, mixed> $params keyword·region·type·page·limit
     * @return array{list: array<int, array<string, mixed>>, total: int}
     */
    public function getConsumerList(array $params): array
    {
        $builder = $this->db->table('hospitals h')
            ->select('h.id, h.name, h.type, h.phone, h.address')
            ->select('COALESCE(bs.rate_sum, 0) AS rating', false)
            ->join(
                'board_summaries bs',
                'bs.target_id = h.id AND bs.type = ' . self::REVIEW_TYPE_HOSPITAL,
                'left'
            )
            ->where('h.is_deleted', 0)
            ->where('h.status', 'active');

        if (!empty($params['keyword'])) {
            $builder->like('h.name', (string) $params['keyword']);
        }
        if (!empty($params['region'])) {
            $builder->like('h.address', (string) $params['region']);
        }
        if (!empty($params['type'])) {
            $builder->where('h.type', (int) $params['type']);
        }

        $total = (clone $builder)->countAllResults(false);

        $page  = max(1, (int) ($params['page'] ?? 1));
        $limit = max(1, (int) ($params['limit'] ?? 20));

        $list = $builder
            ->orderBy('h.id', 'DESC')
            ->limit($limit, ($page - 1) * $limit)
            ->get()
            ->getResultArray();

        return ['list' => $list, 'total' => (int) $total];
    }

    /**
     * 외부 앱 병원 상세 — 활성 병원만, 별점 요약(rate_sum·rate1~3) 포함.
     *
     * @return array<string, mixed>|null
     */
    public function getConsumerDetail(int $id): ?array
    {
        return $this->db->table('hospitals h')
            ->select('h.id, h.name, h.type, h.phone, h.address')
            ->select('COALESCE(bs.rate_sum, 0) AS rate_sum', false)
            ->select('COALESCE(bs.rate1, 0) AS rate1, COALESCE(bs.rate2, 0) AS rate2, COALESCE(bs.rate3, 0) AS rate3', false)
            ->join(
                'board_summaries bs',
                'bs.target_id = h.id AND bs.type = ' . self::REVIEW_TYPE_HOSPITAL,
                'left'
            )
            ->where('h.id', $id)
            ->where('h.is_deleted', 0)
            ->where('h.status', 'active')
            ->get()
            ->getRowArray();
    }

    /**
     * 활성 병원 존재 여부 — 찜·하위 리소스 접근 전 검증용.
     */
    public function isVisible(int $id): bool
    {
        return $this->where('id', $id)
            ->where('is_deleted', 0)
            ->where('status', 'active')
            ->countAllResults() > 0;
    }
}
