<?php

namespace Tests\Support\Libraries;

use App\Libraries\RedisQueue;

/**
 * 테스트용 인메모리 RedisQueue 더블 (이슈 #115)
 *
 * 실제 Redis 연결 없이 큐 동작을 시뮬레이션한다.
 * - $available=false: Redis 미연결 환경(폴백 경로) 재현 — push 항상 false
 * - $available=true : 인메모리 FIFO 로 push/pop 동작 — Consumer 통합 테스트용
 */
class FakeRedisQueue extends RedisQueue
{
    /** @var array<string, list<string>> */
    private array $store = [];

    public function __construct(private bool $available = true)
    {
        // 부모 생성자(Config\Cache 의존)를 호출하지 않는다 — 순수 인메모리.
    }

    public function push(string $queue, string $payload): bool
    {
        if (! $this->available) {
            return false;
        }

        // LPUSH 시맨틱: 앞쪽에 적재
        $this->store[$queue] ??= [];
        array_unshift($this->store[$queue], $payload);

        return true;
    }

    public function pop(string $queue): ?string
    {
        if (! $this->available || empty($this->store[$queue])) {
            return null;
        }

        // RPOP 시맨틱: 뒤쪽에서 추출 (FIFO)
        return array_pop($this->store[$queue]);
    }

    public function length(string $queue): int
    {
        return count($this->store[$queue] ?? []);
    }

    public function isAvailable(): bool
    {
        return $this->available;
    }
}
