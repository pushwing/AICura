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
     * Fix #7 + 이슈 #99: 병원 쓰기 시 활성 목록 캐시와 소비자 캐시(목록·상세)를 함께 무효화.
     *
     * 소비자 캐시는 파라미터 해시(md5) 기반이라 개별 키를 열거할 수 없으므로,
     * 캐시 키에 포함되는 버전 토큰을 삭제해 한 번에 무효화한다(다음 조회 시 새 토큰 발급).
     *
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    protected function clearActiveListCache(array $data): array
    {
        cache()->delete('hospitals_active_list');
        cache()->delete(self::CONSUMER_CACHE_VERSION_KEY);
        return $data;
    }

    // ──────────────────────────────────────────────
    // 외부(소비자) 앱 — 병원 조회 (이슈 #99)
    // ──────────────────────────────────────────────

    /** boards.type — 병원 후기 (별점 요약 조인용) */
    private const int REVIEW_TYPE_HOSPITAL = 2;

    /** 소비자 캐시(목록·상세) 무효화용 버전 토큰 캐시 키 */
    public const CONSUMER_CACHE_VERSION_KEY = 'hospitals_consumer_cache_ver';

    /** 버전 토큰 TTL (초) — 1일. 쓰기 발생 시 즉시 삭제되어 무효화된다. */
    private const int CONSUMER_CACHE_VERSION_TTL = 86400;

    /**
     * 소비자 캐시 키에 끼워 넣는 버전 토큰.
     *
     * 병원 쓰기(Insert·Update·Delete) 시 토큰이 삭제되며, 다음 조회에서 새 토큰이
     * 발급되어 이전 버전으로 저장된 목록·상세 캐시는 모두 자연스럽게 무시된다(TTL 만료로 정리).
     */
    public function consumerCacheVersion(): string
    {
        /** @var string|null $ver */
        $ver = cache(self::CONSUMER_CACHE_VERSION_KEY);
        if (!is_string($ver)) {
            $ver = bin2hex(random_bytes(4));
            cache()->save(self::CONSUMER_CACHE_VERSION_KEY, $ver, self::CONSUMER_CACHE_VERSION_TTL);
        }

        return $ver;
    }

    /**
     * 외부 앱 병원 목록 — 활성 병원만, 이름·지역(주소)·진료과 필터, 별점 요약 조인, 페이징.
     *
     * 지역은 address LIKE, 진료과는 department_hospital 피벗 조인(코드 일치)으로 처리한다.
     * 단일 진료과 코드로만 필터링하므로 INNER JOIN 시 병원 행 중복은 발생하지 않는다.
     *
     * @param array<string, mixed> $params keyword·region·type·department·page·limit
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
        if (!empty($params['department'])) {
            $builder->join('department_hospital dh', 'dh.hospital_id = h.id', 'inner')
                ->join('departments d', 'd.id = dh.department_id', 'inner')
                ->where('d.code', (string) $params['department'])
                ->where('d.is_active', 1);
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
     * 소비자 캐시(목록·상세) 일괄 무효화.
     *
     * 병원 쓰기는 모델 콜백(clearActiveListCache)이 처리하지만, 진료과 매핑 변경처럼
     * hospitals 테이블을 직접 건드리지 않는 변경은 이 메서드로 명시적으로 무효화한다.
     */
    public function invalidateConsumerCache(): void
    {
        cache()->delete('hospitals_active_list');
        cache()->delete(self::CONSUMER_CACHE_VERSION_KEY);
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

    /**
     * sitemap.xml 용 노출 병원 목록 — 활성·미삭제 병원의 id·갱신시각. (이슈 #144)
     *
     * @return array<int, array{id: int, updated_at: string|null}>
     */
    public function getSitemapHospitals(int $limit = 50000): array
    {
        /** @var array<int, array{id: int, updated_at: string|null}> $rows */
        $rows = $this->db->table('hospitals')
            ->select('id, updated_at')
            ->where('is_deleted', 0)
            ->where('status', 'active')
            ->orderBy('updated_at', 'DESC')
            ->limit($limit)
            ->get()
            ->getResultArray();

        return $rows;
    }
}
