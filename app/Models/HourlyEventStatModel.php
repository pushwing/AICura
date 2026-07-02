<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * 소비자 앱 액션 로그 시간별 집계 모델 (이슈 #120)
 *
 * logs:aggregate 배치가 멱등 upsert 로 적재하고, 어드민 통계 화면이 조회한다.
 */
class HourlyEventStatModel extends Model
{
    protected $table         = 'hourly_event_stats';
    protected $primaryKey    = 'id';
    protected $useTimestamps = true;
    protected $returnType    = 'array';

    protected $allowedFields = [
        'stat_date',
        'stat_hour',
        'event',
        'campaign_id',
        'count',
        'uniq_users',
    ];

    /**
     * 버킷 1건을 멱등 upsert. (stat_date, stat_hour, event, campaign_id) 유니크 키로
     * 충돌 시 count·uniq_users·updated_at 만 갱신해 재집계가 안전하게 반복되도록 한다.
     *
     * @param array{stat_date:string, stat_hour:int, event:string, campaign_id:int, count:int, uniq_users:int} $row
     */
    public function upsertBucket(array $row): void
    {
        $now = date('Y-m-d H:i:s');

        $this->db->table($this->table)
            // 충돌 시 집계값·updated_at 만 덮어쓰고 created_at 은 보존
            ->updateFields(['count', 'uniq_users', 'updated_at'])
            ->upsert($row + ['created_at' => $now, 'updated_at' => $now]);
    }

    /**
     * 시간별 추이 — 특정 날짜의 0~23시 버킷을 이벤트별로 반환.
     *
     * @return list<array<string, mixed>>
     */
    public function hourlyByDate(string $date): array
    {
        return $this->select('stat_hour, event, SUM(`count`) AS total, SUM(uniq_users) AS users')
            ->where('stat_date', $date)
            ->groupBy(['stat_hour', 'event'])
            ->orderBy('stat_hour', 'ASC')
            ->findAll();
    }

    /**
     * 일별 추이 — 날짜 범위를 일·이벤트 단위로 합산해 반환.
     *
     * @return list<array<string, mixed>>
     */
    public function dailyBetween(string $from, string $to): array
    {
        return $this->select('stat_date, event, SUM(`count`) AS total, SUM(uniq_users) AS users')
            ->where('stat_date >=', $from)
            ->where('stat_date <=', $to)
            ->groupBy(['stat_date', 'event'])
            ->orderBy('stat_date', 'ASC')
            ->findAll();
    }
}
