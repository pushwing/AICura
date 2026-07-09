<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Throwable;

/**
 * 앱 액션 로그 시간별 집계 커맨드 (이슈 #120)
 *
 *   php spark logs:aggregate                       # 직전 1시간 집계 (기본 — 매시 크론)
 *   php spark logs:aggregate --date=2026-06-30 --hour=14   # 특정 시각 재집계
 *   php spark logs:aggregate --date=2026-06-30             # 해당 날짜 0~23시 전체 재집계 (--hour 없으면 하루 전체)
 *   php spark logs:aggregate --date=2026-06-30 --backfill  # 해당 날짜 0~23시 전체 재집계 (명시)
 *
 * app_logs 를 (event, campaign_id) 기준 1시간 버킷으로 롤업해 hourly_event_stats 에 멱등 적재한다.
 * 멱등하므로 같은 시각을 여러 번 돌려도 결과가 누적되지 않고 갱신된다.
 *
 * 서버 crontab 등록 예시 (매시 5분 — 직전 1시간 집계):
 *   5 * * * * cd /path/to/app && php spark logs:aggregate >> writable/logs/log-aggregate.log 2>&1
 */
class AppLogAggregate extends BaseCommand
{
    protected $group       = 'AICura';
    protected $name        = 'logs:aggregate';
    protected $description = 'app_logs 를 시간 단위로 집계해 hourly_event_stats 에 적재합니다.';
    protected $usage       = 'logs:aggregate [options]';

    /**
     * @var array<string, string>
     */
    protected $options = [
        '--date'     => '집계 날짜 (YYYY-MM-DD, 기본: 직전 1시간의 날짜). --hour 없으면 하루 전체 재집계',
        '--hour'     => '집계 시각 (0~23, 기본: 직전 1시간)',
        '--backfill' => '--date 의 0~23시 전체를 재집계',
    ];

    /**
     * @param array<int|string, string|null> $params
     */
    public function run(array $params): void
    {
        $dateOpt = $params['date'] ?? CLI::getOption('date');
        $hourOpt = $params['hour'] ?? CLI::getOption('hour');

        // 기본 대상은 "직전 1시간" — 매시 크론이 막 끝난 직전 시간대를 집계한다.
        $prev = strtotime('-1 hour');
        $date = (string) ($dateOpt ?? date('Y-m-d', $prev));

        // --date 만 주고 --hour 가 없으면 그 날짜 하루 전체를 집계한다.
        // (시각을 "직전 1시간"으로 채우면 과거 날짜에서 무관한 한 시간만 집계되는 함정을 막는다)
        $backfill = array_key_exists('backfill', $params)
            || CLI::getOption('backfill')
            || ($dateOpt !== null && $hourOpt === null);

        $service = service('appLogStatService');

        try {
            if ($backfill) {
                $totalBuckets = 0;
                $totalLogs    = 0;

                for ($h = 0; $h < 24; $h++) {
                    $result = $service->aggregateHour($date, $h);
                    $totalBuckets += $result['buckets'];
                    $totalLogs += $result['logs'];
                }
                CLI::write(sprintf('백필 완료 — %s 전체: 로그 %d건 → 버킷 %d개', $date, $totalLogs, $totalBuckets), 'green');

                return;
            }

            $hour = $hourOpt !== null ? max(0, min(23, (int) $hourOpt)) : (int) date('G', $prev);

            $result = $service->aggregateHour($date, $hour);
            CLI::write(
                sprintf('집계 완료 — %s %02d시: 로그 %d건 → 버킷 %d개', $date, $hour, $result['logs'], $result['buckets']),
                'green',
            );
        } catch (Throwable $e) {
            CLI::error('집계 실패: ' . $e->getMessage());
            log_message('error', '[logs:aggregate] {msg}', ['msg' => $e->getMessage()]);
        }
    }
}
