<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * 후기 별점·설문 요약 통계 (board_summaries)
 *
 * type + target_id 단위로 1건. boards의 별점을 집계해 갱신한다.
 */
class BoardSummaryModel extends Model
{
    protected $table         = 'board_summaries';
    protected $primaryKey    = 'id';
    protected $useTimestamps = false;
    protected $returnType    = 'array';
    protected $allowedFields = [
        'type',
        'target_id',
        'rate_sum',
        'rate1',
        'rate2',
        'rate3',
        'survey_type',
        'reg_date',
        'mod_date',
    ];

    /**
     * type + target_id 의 별점 집계를 boards에서 재계산해 upsert.
     *
     * 정상(is_delete=0) 후기들의 평균 별점(rate_sum, rate1~3)을 산출한다.
     *
     * @return array{rate_sum: float, rate1: float, rate2: float, rate3: float, count: int}
     */
    public function recalculate(int $type, int $targetId): array
    {
        $agg = $this->db->table('boards')
            ->select('COUNT(*) AS cnt', false)
            ->select('IFNULL(AVG(rate_sum), 0) AS rate_sum', false)
            ->select('IFNULL(AVG(rate1), 0) AS rate1', false)
            ->select('IFNULL(AVG(rate2), 0) AS rate2', false)
            ->select('IFNULL(AVG(rate3), 0) AS rate3', false)
            ->where('type', $type)
            ->where('target_id', $targetId)
            ->where('is_delete', BoardModel::DELETE_NONE)
            ->get()
            ->getRowArray();

        $rates = [
            'rate_sum' => round((float) ($agg['rate_sum'] ?? 0), 2),
            'rate1'    => round((float) ($agg['rate1'] ?? 0), 2),
            'rate2'    => round((float) ($agg['rate2'] ?? 0), 2),
            'rate3'    => round((float) ($agg['rate3'] ?? 0), 2),
        ];
        $count = (int) ($agg['cnt'] ?? 0);

        $today    = date('Y-m-d');
        $existing = $this->where('type', $type)->where('target_id', $targetId)->first();

        if ($existing === null) {
            $this->insert($rates + [
                'type'      => $type,
                'target_id' => $targetId,
                'reg_date'  => $today,
                'mod_date'  => $today,
            ]);
        } else {
            $this->update((int) $existing['id'], $rates + ['mod_date' => $today]);
        }

        return $rates + ['count' => $count];
    }
}
