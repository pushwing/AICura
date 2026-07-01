<?php

namespace App\Services;

use CodeIgniter\HTTP\CURLRequest;
use Throwable;

/**
 * IndexNow 즉시 색인 제출 서비스 (이슈 #152)
 *
 * 콘텐츠 발행·수정 시 변경 URL 을 IndexNow 엔드포인트에 제출해 Bing·Naver·Yandex 등에서
 * 즉시 재크롤링되도록 한다. 키는 .env(INDEXNOW_KEY)에 두고 keyLocation 으로 검증한다.
 *
 * 안전장치:
 *  - 키 미설정·로컬 호스트(localhost/*.test 등)에서는 제출하지 않는다.
 *  - 외부 호출 실패가 요청 흐름을 막지 않도록 예외를 삼키고 로깅만 한다(타임아웃 필수).
 */
class IndexNowService
{
    private const ENDPOINT = 'https://api.indexnow.org/indexnow';

    private string $key;
    private int $timeout;
    private ?CURLRequest $client;

    public function __construct(?CURLRequest $client = null)
    {
        $this->key     = (string) env('INDEXNOW_KEY', '');
        $this->timeout = max(3, (int) env('INDEXNOW_TIMEOUT', 5));
        $this->client  = $client;
    }

    /** 제출 가능 여부 — 키 설정 + 운영(비로컬) 호스트 */
    public function isEnabled(): bool
    {
        return $this->key !== '' && !$this->isLocalHost();
    }

    /** 키 검증 파일 위치 (keyLocation) */
    public function keyLocation(): string
    {
        return base_url('indexnow-key.txt');
    }

    public function key(): string
    {
        return $this->key;
    }

    /**
     * 변경 URL 제출. 비활성(키 없음·로컬)·빈 목록이면 아무 것도 하지 않고 false.
     */
    public function submit(string ...$urls): bool
    {
        $urls = array_values(array_filter(
            array_map(static fn (string $u): string => trim($u), $urls),
            static fn (string $u): bool => $u !== '',
        ));

        if (!$this->isEnabled() || $urls === []) {
            return false;
        }

        try {
            $client = $this->client ?? service('curlrequest', [
                'timeout'     => $this->timeout,
                'http_errors' => false,
            ]);
            $client->post(self::ENDPOINT, ['json' => $this->payload($urls)]);

            return true;
        } catch (Throwable $e) {
            log_message('error', 'IndexNow 제출 실패: {msg}', ['msg' => $e->getMessage()]);

            return false;
        }
    }

    /**
     * IndexNow POST 페이로드.
     *
     * @param list<string> $urls
     * @return array<string, mixed>
     */
    public function payload(array $urls): array
    {
        return [
            'host'        => (string) (parse_url(base_url(), PHP_URL_HOST) ?? ''),
            'key'         => $this->key,
            'keyLocation' => $this->keyLocation(),
            'urlList'     => $urls,
        ];
    }

    /** 로컬·테스트 호스트 여부 — 실서비스 도메인이 아닐 때 제출 방지 */
    private function isLocalHost(): bool
    {
        $host = (string) (parse_url(base_url(), PHP_URL_HOST) ?? '');

        return $host === ''
            || $host === 'localhost'
            || $host === '127.0.0.1'
            || str_ends_with($host, '.local')
            || str_ends_with($host, '.test')
            || str_ends_with($host, '.example.com')
            || $host === 'example.com';
    }
}
