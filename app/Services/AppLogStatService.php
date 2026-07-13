<?php

namespace App\Services;

use App\Models\AppLogModel;
use App\Models\HourlyEventStatModel;

/**
 * 소비자 앱 액션 로그 시간별 집계 서비스 (이슈 #120)
 *
 * app_logs(원시 가공 로그)를 1시간 버킷으로 롤업해 hourly_event_stats 에 적재한다.
 * 무거운 집계는 요청 사이클이 아니라 배치(logs:aggregate)에서 수행한다는
 * CLAUDE.md 부하 분산 원칙을 따른다.
 *
 * 집계 키는 (event, campaign_id) 이며, campaign_id 는 로그 context 에서 추출한다.
 * context 가 JSON 문자열로 저장되므로 DB JSON 함수 종속을 피해 PHP 에서 그룹핑한다.
 * created_at 인덱스로 1시간치만 청크 조회하므로 풀스캔이 발생하지 않는다.
 */
class AppLogStatService
{
    /**
     * 한 번에 메모리로 읽을 로그 행 수 — 메모리 사용을 제한한다.
     */
    private const int CHUNK = 1000;

    public function __construct(
        private ?AppLogModel $logs = null,
        private ?HourlyEventStatModel $stats = null,
    ) {
        $this->logs ??= model(AppLogModel::class);
        $this->stats ??= model(HourlyEventStatModel::class);
    }

    /**
     * 특정 날짜·시각(0~23)의 로그를 집계해 hourly_event_stats 에 멱등 적재.
     *
     * @return array{buckets:int, logs:int} 적재한 버킷 수와 읽은 로그 수
     */
    public function aggregateHour(string $date, int $hour): array
    {
        $from = sprintf('%s %02d:00:00', $date, $hour);
        $to   = sprintf('%s %02d:59:59', $date, $hour);

        // (event|campaign_id) => ['count' => int, 'users' => array<int,true>]
        $grouped = [];
        $logRows = 0;

        $offset = 0;

        while (true) {
            $rows = $this->logs
                ->select('event, user_id, context')
                ->where('created_at >=', $from)
                ->where('created_at <=', $to)
                ->orderBy('id', 'ASC')
                ->findAll(self::CHUNK, $offset);

            if ($rows === []) {
                break;
            }

            foreach ($rows as $row) {
                $this->accumulate($grouped, $row);
            }

            $logRows += count($rows);
            $offset += self::CHUNK;

            if (count($rows) < self::CHUNK) {
                break;
            }
        }

        foreach ($this->toBuckets($grouped) as $bucket) {
            $this->stats->upsertBucket([
                'stat_date'   => $date,
                'stat_hour'   => $hour,
                'event'       => $bucket['event'],
                'campaign_id' => $bucket['campaign_id'],
                'count'       => $bucket['count'],
                'uniq_users'  => $bucket['uniq_users'],
            ]);
        }

        return ['buckets' => count($grouped), 'logs' => $logRows];
    }

    /**
     * 로그 행 목록을 (event, campaign_id)별 집계 버킷으로 변환 — DB 없이 순수 계산.
     * 집계 정확성(카운트·distinct user·campaign_id 추출) 검증의 단위 테스트 진입점이다.
     *
     * @param list<array<string, mixed>> $rows
     *
     * @return list<array{event:string, campaign_id:int, count:int, uniq_users:int}>
     */
    public function summarize(array $rows): array
    {
        $grouped = [];

        foreach ($rows as $row) {
            $this->accumulate($grouped, $row);
        }

        return $this->toBuckets($grouped);
    }

    /**
     * 내부 누적 맵을 upsert 가능한 평탄 버킷 목록으로 변환.
     *
     * @param array<string, array{count:int, users:array<int, true>}> $grouped
     *
     * @return list<array{event:string, campaign_id:int, count:int, uniq_users:int}>
     */
    private function toBuckets(array $grouped): array
    {
        $buckets = [];

        foreach ($grouped as $key => $agg) {
            [$event, $campaignId] = explode('|', $key, 2);
            $buckets[]            = [
                'event'       => $event,
                'campaign_id' => (int) $campaignId,
                'count'       => $agg['count'],
                'uniq_users'  => count($agg['users']),
            ];
        }

        return $buckets;
    }

    /**
     * 로그 1행을 (event, campaign_id) 버킷에 누적.
     * event 가 비면 'unknown' 으로, campaign_id 가 없으면 0(캠페인 무관)으로 집계한다.
     *
     * @param array<string, array{count:int, users:array<int, true>}> $grouped
     * @param array<string, mixed>                                    $row
     */
    private function accumulate(array &$grouped, array $row): void
    {
        $event      = isset($row['event']) && $row['event'] !== '' ? (string) $row['event'] : 'unknown';
        $campaignId = $this->extractCampaignId($row['context'] ?? null);
        $key        = $event . '|' . $campaignId;

        if (! isset($grouped[$key])) {
            $grouped[$key] = ['count' => 0, 'users' => []];
        }

        $grouped[$key]['count']++;

        // isset 은 null 도 걸러내므로 익명(user_id 없음) 로그는 distinct 에서 제외된다.
        if (isset($row['user_id'])) {
            $grouped[$key]['users'][(int) $row['user_id']] = true;
        }
    }

    /**
     * context(JSON 문자열)에서 campaign_id 를 정수로 추출. 없으면 0.
     */
    private function extractCampaignId(mixed $context): int
    {
        if (! is_string($context) || $context === '') {
            return 0;
        }

        $decoded = json_decode($context, true);
        if (! is_array($decoded) || ! isset($decoded['campaign_id'])) {
            return 0;
        }

        return (int) $decoded['campaign_id'];
    }
}
