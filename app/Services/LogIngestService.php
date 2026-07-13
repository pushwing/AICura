<?php

namespace App\Services;

use App\Libraries\RedisQueue;

/**
 * 외부(소비자) 앱 로그 수집 서비스 (이슈 #103, #115)
 *
 * CLAUDE.md 로그 파이프라인의 '큐 적재' 단계를 담당한다.
 *   앱 → API → [본 서비스] Redis 큐 적재 → Consumer → 원시 파일 / DB
 *
 * Redis 가 연결되면 log_queue 에 LPUSH 하고, 미연결(로컬·CI)이면
 * 기존과 동일하게 writable/logs/raw/YYYY-MM-DD.log 에 직접 append 하여
 * /logs 엔드포인트가 어떤 환경에서도 202 를 보장하도록 graceful 폴백한다.
 */
class LogIngestService
{
    public function __construct(private readonly ?RedisQueue $queue = null)
    {
    }

    /**
     * 큐를 지연 해석 — 공유 인스턴스가 생성 시점 큐를 고정하지 않도록 매 호출 resolve.
     */
    private function queue(): RedisQueue
    {
        return $this->queue ?? service('redisQueue');
    }

    /**
     * 로그 1건을 큐에 적재. Redis 미연결 시 원시 파일로 폴백.
     *
     * @param array<string, mixed> $payload 앱이 전송한 로그 본문
     * @param int|null             $userId  인증된 경우 사용자 id
     */
    public function ingest(array $payload, ?int $userId = null): void
    {
        $record = [
            'received_at' => date('Y-m-d H:i:s'),
            'user_id'     => $userId,
            'payload'     => $payload,
        ];

        $line = json_encode($record, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        // 1순위: Redis 큐 적재. 성공하면 Consumer 가 원시 파일·DB 저장을 담당한다.
        if ($this->queue()->push(RedisQueue::LOG_QUEUE, $line)) {
            return;
        }

        // 폴백: Redis 미연결 — 원시 파일에 직접 append (감사·재처리 보존).
        $this->writeRaw($line);
    }

    /**
     * 원시 로그 1줄을 날짜별 파일에 append.
     * 폴백 경로와 Consumer 가 공유한다.
     */
    public function writeRaw(string $line): void
    {
        $dir = rtrim(WRITEPATH, '/\\') . '/logs/raw/';
        if (! is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        file_put_contents(
            $dir . date('Y-m-d') . '.log',
            $line . PHP_EOL,
            FILE_APPEND | LOCK_EX,
        );
    }
}
