<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * AI 일일 보고서 모델 (이슈 #65)
 *
 * type: revenue(매출현황) / consumption(소진보고서)
 */
class AiReportModel extends Model
{
    public const TYPE_REVENUE     = 'revenue';
    public const TYPE_CONSUMPTION = 'consumption';
    public const SCOPE_GLOBAL     = 'global';
    public const SCOPE_HOSPITAL   = 'hospital';
    public const SCOPE_AGENCY     = 'agency';

    protected $table         = 'ai_reports';
    protected $primaryKey    = 'id';
    protected $useTimestamps = true;
    protected $returnType    = 'array';

    /**
     * @var list<string>
     */
    protected $allowedFields = [
        'scope_type',
        'scope_id',
        'type',
        'title',
        'content',
        'report_date',
        'meta',
    ];

    /**
     * 종류별 최신 보고서 1건 (스코프 한정)
     *
     * @return array<string, mixed>|null
     */
    public function latestByType(string $type, string $scopeType = self::SCOPE_GLOBAL, ?int $scopeId = null): ?array
    {
        return $this->applyScope($scopeType, $scopeId)
            ->where('type', $type)
            ->orderBy('report_date', 'DESC')
            ->orderBy('id', 'DESC')
            ->first();
    }

    /**
     * 종류별 보고서 목록 (페이징, 스코프 한정) — '더보기' 리스트용
     *
     * @return array{items: array<int, array<string, mixed>>, total: int}
     */
    public function historyByType(
        string $type,
        int $page,
        int $limit,
        string $scopeType = self::SCOPE_GLOBAL,
        ?int $scopeId = null,
    ): array {
        $offset = ($page - 1) * $limit;

        $total = $this->applyScope($scopeType, $scopeId)->where('type', $type)->countAllResults(false);

        $items = $this->applyScope($scopeType, $scopeId)
            ->where('type', $type)
            ->orderBy('report_date', 'DESC')
            ->orderBy('id', 'DESC')
            ->findAll($limit, $offset);

        return [
            'items' => $items,
            'total' => $total,
        ];
    }

    /**
     * 스코프 일치 단건 조회 — 포털 권한 검증용 (다른 주체 보고서 접근 차단)
     *
     * @return array<string, mixed>|null
     */
    public function findScoped(int $id, string $scopeType, ?int $scopeId): ?array
    {
        return $this->applyScope($scopeType, $scopeId)->where('id', $id)->first();
    }

    /**
     * scope_type / scope_id WHERE 조건 적용 (scope_id null이면 IS NULL)
     *
     * @return $this
     */
    private function applyScope(string $scopeType, ?int $scopeId): static
    {
        $this->where('scope_type', $scopeType);
        $scopeId === null ? $this->where('scope_id') : $this->where('scope_id', $scopeId);

        return $this;
    }
}
