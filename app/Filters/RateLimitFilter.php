<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * IP 기준 Rate Limit 필터 (이슈 #115)
 *
 * 인증 없는 공개 POST 엔드포인트(예: /logs)의 남용·텔레메트리 폭주를 막는다.
 * CI4 Throttler 로 IP 당 분당 호출을 제한하고, 초과 시 429 + Retry-After 를 반환한다.
 *
 * 임계값은 필터 인자로 조정한다 (기본 60회/60초):
 *   $filters['ratelimit']['before'] = ['api/v1/logs'];               // 기본 60/분
 *   route('...', ..., ['filter' => 'ratelimit:120,60']);            // 120/60초
 */
class RateLimitFilter implements FilterInterface
{
    /**
     * 기본 허용 횟수
     */
    private const int DEFAULT_CAPACITY = 60;

    /**
     * 기본 시간창(초)
     */
    private const int DEFAULT_SECONDS = 60;

    public function before(RequestInterface $request, $arguments = null): mixed
    {
        [$capacity, $seconds] = $this->limits($arguments);

        $throttler = service('throttler');
        // 캐시 키 예약문자(:,/,@ 등) 회피를 위해 md5 해시(16진)만 사용한다.
        $key = 'ratelimit_' . md5($request->getIPAddress() . '|' . $request->getUri()->getPath());

        // 토큰이 없으면 false 반환 — 호출 거부
        if ($throttler->check($key, $capacity, $seconds) === false) {
            return service('response')
                ->setStatusCode(429)
                ->setHeader('Retry-After', (string) $throttler->getTokenTime())
                ->setJSON([
                    'status'  => 'error',
                    'code'    => 'RATE_LIMITED',
                    'message' => '요청이 너무 많습니다. 잠시 후 다시 시도해 주세요.',
                ]);
        }

        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null): mixed
    {
        return null;
    }

    /**
     * 필터 인자에서 [허용횟수, 시간창초]를 해석한다.
     *
     * @param list<string>|null $arguments
     *
     * @return array{0: int, 1: int}
     */
    private function limits(?array $arguments): array
    {
        $capacity = isset($arguments[0]) ? max(1, (int) $arguments[0]) : self::DEFAULT_CAPACITY;
        $seconds  = isset($arguments[1]) ? max(1, (int) $arguments[1]) : self::DEFAULT_SECONDS;

        return [$capacity, $seconds];
    }
}
