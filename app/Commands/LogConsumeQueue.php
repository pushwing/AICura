<?php

namespace App\Commands;

use App\Libraries\RedisQueue;
use App\Models\AppLogModel;
use App\Services\LogIngestService;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Throwable;

/**
 * 로그 수집 큐 소비 커맨드 (이슈 #115)
 *
 *   php spark logs:consume                 # 큐를 비울 때까지 최대 100건 처리 후 종료
 *   php spark logs:consume --limit=500
 *   php spark logs:consume --daemon        # 상시 소비 (빈 큐면 --sleep 초 대기 후 재시도)
 *   php spark logs:consume --daemon --sleep=3
 *
 * CLAUDE.md 로그 파이프라인의 Consumer 단계.
 *   log_queue RPOP → ① 원시 파일 저장(writable/logs/raw/) ② 가공 후 app_logs INSERT
 * 가공·저장 실패 건은 dead-letter(writable/logs/queue-failed/)로 보존하고 계속 진행한다.
 *
 * Redis 미연결 시: 폴백으로 원시 파일에 이미 적재되므로 안내 후 정상 종료한다.
 *
 * 서버 crontab 등록 예시 (1분마다):
 *   * * * * * cd /path/to/app && php spark logs:consume >> writable/logs/log-consume.log 2>&1
 * 또는 상시 데몬(systemd/supervisor):
 *   php spark logs:consume --daemon
 */
class LogConsumeQueue extends BaseCommand
{
    protected $group       = 'AICura';
    protected $name        = 'logs:consume';
    protected $description = 'Redis 로그 큐를 소비해 원시 파일 저장 + app_logs 적재합니다.';
    protected $usage       = 'logs:consume [options]';

    /** @var array<string, string> */
    protected $options = [
        '--limit'  => '한 번에 처리할 최대 건수 (기본값: 100, --daemon 시 무시)',
        '--daemon' => '상시 소비 모드 — 빈 큐면 대기 후 재시도',
        '--sleep'  => '데몬 모드에서 빈 큐일 때 대기 초 (기본값: 2)',
    ];

    private const DEFAULT_LIMIT = 100;

    /** 데몬 모드 실행 플래그 — SIGTERM/SIGINT 수신 시 false 로 전환해 graceful 종료. */
    private bool $running = true;

    /** @param array<int|string, string|null> $params */
    public function run(array $params): void
    {
        $queue = service('redisQueue');

        if (! $queue->isAvailable()) {
            CLI::write('Redis 미연결 — 큐 소비를 건너뜁니다 (로그는 원시 파일 폴백으로 보존됨).', 'yellow');

            return;
        }

        $daemon = array_key_exists('daemon', $params) || CLI::getOption('daemon');
        $sleep  = max(1, (int) ($params['sleep'] ?? CLI::getOption('sleep') ?? 2));
        $limit  = max(1, (int) ($params['limit'] ?? CLI::getOption('limit') ?? self::DEFAULT_LIMIT));

        if ($daemon) {
            CLI::write('로그 큐 데몬 소비 시작 (Ctrl+C 로 종료)', 'yellow');
            $this->consumeForever($queue, $sleep);

            return;
        }

        $processed = $this->consumeBatch($queue, $limit);
        CLI::write(sprintf('완료 — %d건 처리', $processed), 'green');
    }

    /** 큐가 비거나 limit 도달까지 처리. 처리 건수 반환. */
    private function consumeBatch(RedisQueue $queue, int $limit): int
    {
        $count = 0;

        while ($count < $limit) {
            $raw = $queue->pop(RedisQueue::LOG_QUEUE);
            if ($raw === null) {
                break;
            }

            $this->handle($raw);
            $count++;
        }

        return $count;
    }

    /** 상시 소비 — 빈 큐면 sleep 후 재시도. SIGTERM/SIGINT 로 graceful 종료. */
    private function consumeForever(RedisQueue $queue, int $sleep): void
    {
        $this->registerSignalHandlers();

        while ($this->running) {
            $raw = $queue->pop(RedisQueue::LOG_QUEUE);
            if ($raw === null) {
                sleep($sleep);

                continue;
            }

            $this->handle($raw);
        }

        CLI::write('종료 신호 수신 — 데몬을 정상 종료합니다.', 'yellow');
    }

    /** 데몬 graceful 종료를 위한 시그널 핸들러 등록 (pcntl 가용 시). */
    private function registerSignalHandlers(): void
    {
        if (! function_exists('pcntl_async_signals')) {
            return;
        }

        pcntl_async_signals(true);
        $stop = function (): void {
            $this->running = false;
        };
        pcntl_signal(SIGTERM, $stop);
        pcntl_signal(SIGINT, $stop);
    }

    /**
     * 큐 항목 1건 처리 — 원시 파일 저장 + 가공 DB 적재.
     * 실패 시 dead-letter 로 보존하고 예외를 전파하지 않는다.
     */
    private function handle(string $raw): void
    {
        try {
            // ① 원시 로그 보존 (감사·재처리)
            service('logIngestService')->writeRaw($raw);

            // ② 가공 후 DB 적재
            $record = json_decode($raw, true);
            if (! is_array($record)) {
                throw new \RuntimeException('JSON 디코드 실패');
            }

            model(AppLogModel::class)->insert($this->transform($record));
        } catch (Throwable $e) {
            $this->deadLetter($raw, $e->getMessage());
            CLI::error('  ✗ 처리 실패 → dead-letter: ' . $e->getMessage());
        }
    }

    /**
     * 큐 레코드({received_at, user_id, payload})를 app_logs 행으로 가공.
     * payload 의 level/event/message 를 분리하고 나머지는 context(JSON)로 보존한다.
     *
     * @param array<string, mixed> $record
     * @return array<string, mixed>
     */
    public function transform(array $record): array
    {
        /** @var array<string, mixed> $payload */
        $payload = is_array($record['payload'] ?? null) ? $record['payload'] : [];

        $level   = isset($payload['level']) ? (string) $payload['level'] : 'info';
        $event   = isset($payload['event']) ? (string) $payload['event'] : null;
        $message = isset($payload['message']) ? (string) $payload['message'] : null;

        // 표준 필드를 제외한 나머지를 context 로 보존
        $context = array_diff_key($payload, array_flip(['level', 'event', 'message']));

        return [
            'user_id'            => isset($record['user_id']) ? (int) $record['user_id'] : null,
            'level'              => $level,
            'event'              => $event,
            'message'            => $message,
            'context'            => $context === [] ? null : json_encode($context, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'client_received_at' => isset($record['received_at']) ? (string) $record['received_at'] : null,
        ];
    }

    /** 처리 실패 건을 날짜별 dead-letter 파일에 보존. */
    private function deadLetter(string $raw, string $reason): void
    {
        $dir = rtrim(WRITEPATH, '/\\') . '/logs/queue-failed/';
        if (! is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        $entry = json_encode([
            'failed_at' => date('Y-m-d H:i:s'),
            'reason'    => $reason,
            'raw'       => $raw,
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        file_put_contents(
            $dir . date('Y-m-d') . '.log',
            $entry . PHP_EOL,
            FILE_APPEND | LOCK_EX,
        );

        log_message('error', '[logs:consume] dead-letter: {reason}', ['reason' => $reason]);
    }
}
