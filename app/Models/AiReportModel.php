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
    protected $table         = 'ai_reports';
    protected $primaryKey     = 'id';
    protected $useTimestamps  = true;
    protected $returnType     = 'array';

    /** @var list<string> */
    protected $allowedFields = [
        'type',
        'title',
        'content',
        'report_date',
        'meta',
    ];

    public const TYPE_REVENUE     = 'revenue';
    public const TYPE_CONSUMPTION = 'consumption';

    /**
     * 종류별 최신 보고서 1건
     *
     * @return array<string, mixed>|null
     */
    public function latestByType(string $type): ?array
    {
        return $this->where('type', $type)
            ->orderBy('report_date', 'DESC')
            ->orderBy('id', 'DESC')
            ->first();
    }

    /**
     * 종류별 보고서 목록 (페이징) — '더보기' 리스트용
     *
     * @return array{items: array<int, array<string, mixed>>, total: int}
     */
    public function historyByType(string $type, int $page, int $limit): array
    {
        $offset = ($page - 1) * $limit;

        $total = $this->where('type', $type)->countAllResults(false);

        $items = $this->where('type', $type)
            ->orderBy('report_date', 'DESC')
            ->orderBy('id', 'DESC')
            ->findAll($limit, $offset);

        return [
            'items' => $items,
            'total' => $total,
        ];
    }
}
