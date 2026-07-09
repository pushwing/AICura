<?php

namespace App\Libraries;

use Config\Cache;
use Predis\Client;
use Throwable;

/**
 * Redis 리스트 기반 경량 큐 (이슈 #115)
 *
 * CLAUDE.md 로그 수집 파이프라인의 큐 계층을 담당한다.
 * predis 클라이언트를 `Config\Cache`의 `$redis` 설정으로 지연 연결하며,
 * 생산자는 LPUSH, 소비자는 RPOP 으로 FIFO 처리한다.
 *
 * Redis 미연결 환경(로컬·CI)에서도 절대 예외를 전파하지 않고
 * isAvailable()/pop() 으로 가용성만 노출 — 호출부가 폴백을 판단한다.
 */
class RedisQueue
{
    /**
     * 로그 수집 큐 키
     */
    public const LOG_QUEUE = 'log_queue';

    private ?Client $client = null;

    /**
     * 연결 시도 후 실패가 확정된 경우 재시도하지 않도록 표시
     */
    private bool $unavailable = false;

    /**
     * @var array<string, mixed>
     */
    private array $config;

    public function __construct()
    {
        // Config\Cache 의 redis 설정(host·port·password·database)을 재사용한다.
        $this->config = (new Cache())->redis;
    }

    /**
     * 큐에 페이로드 1건 적재 (LPUSH).
     *
     * @return bool 적재 성공 여부 (Redis 미연결 시 false)
     */
    public function push(string $queue, string $payload): bool
    {
        $client = $this->client();
        if (! $client instanceof Client) {
            return false;
        }

        try {
            $client->lpush($queue, [$payload]);

            return true;
        } catch (Throwable $e) {
            $this->unavailable = true;
            log_message('error', '[RedisQueue] push 실패: {msg}', ['msg' => $e->getMessage()]);

            return false;
        }
    }

    /**
     * 큐에서 페이로드 1건 추출 (RPOP).
     *
     * @return string|null 추출값, 비었거나 미연결이면 null
     */
    public function pop(string $queue): ?string
    {
        $client = $this->client();
        if (! $client instanceof Client) {
            return null;
        }

        try {
            $value = $client->rpop($queue);

            return is_string($value) ? $value : null;
        } catch (Throwable $e) {
            $this->unavailable = true;
            log_message('error', '[RedisQueue] pop 실패: {msg}', ['msg' => $e->getMessage()]);

            return null;
        }
    }

    /**
     * 현재 큐에 남은 항목 수 (미연결 시 0).
     */
    public function length(string $queue): int
    {
        $client = $this->client();
        if (! $client instanceof Client) {
            return 0;
        }

        try {
            return (int) $client->llen($queue);
        } catch (Throwable) {
            return 0;
        }
    }

    /**
     * Redis 연결 가용 여부.
     */
    public function isAvailable(): bool
    {
        return $this->client() instanceof Client;
    }

    /**
     * 지연 연결 — 최초 호출 시 PING 으로 연결을 확인한다.
     * 실패가 확정되면 이후 호출은 즉시 null 을 반환한다.
     */
    private function client(): ?Client
    {
        if ($this->unavailable) {
            return null;
        }

        if ($this->client instanceof Client) {
            return $this->client;
        }

        try {
            $client = new Client([
                'scheme'   => 'tcp',
                'host'     => (string) ($this->config['host'] ?? '127.0.0.1'),
                'port'     => (int) ($this->config['port'] ?? 6379),
                'password' => $this->config['password'] ?? null,
                'database' => (int) ($this->config['database'] ?? 0),
            ], [
                // 연결 지연을 막기 위한 짧은 타임아웃 (외부 호출 5초 원칙보다 보수적)
                'parameters' => ['timeout' => 1.0, 'read_write_timeout' => 1.0],
            ]);
            $client->ping();

            return $this->client = $client;
        } catch (Throwable $e) {
            $this->unavailable = true;
            log_message('info', '[RedisQueue] 연결 불가 — 폴백 사용: {msg}', ['msg' => $e->getMessage()]);

            return null;
        }
    }
}
