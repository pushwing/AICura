<?php

namespace App\Libraries\Ai;

use CodeIgniter\HTTP\ResponseInterface;
use RuntimeException;

/**
 * Google Gemini generateContent 클라이언트 (이슈 #71)
 *
 * Generative Language API(/v1beta/models/{model}:generateContent)를 호출한다.
 * 외부 라이브러리 없이 CI4 CURLRequest 서비스로 직접 구현한다.
 *
 *   GEMINI_API_KEY  필수 — Google AI Studio에서 발급
 *   GEMINI_MODEL    선택 — 기본 gemini-2.0-flash
 *   GEMINI_TIMEOUT  선택 — 호출 타임아웃(초), 기본 30
 *
 * 시스템 프롬프트는 systemInstruction, JSON 모드는 generationConfig.responseMimeType
 * 으로 전달한다. 레이트리밋(429)·일시 서버 오류(5xx)는 지수 백오프로 재시도한다.
 */
class GeminiClient implements AiClientInterface
{
    private const ENDPOINT_TEMPLATE = 'https://generativelanguage.googleapis.com/v1beta/models/%s:generateContent';
    private const DEFAULT_MODEL      = 'gemini-2.0-flash';

    /** 응답 토큰 상한 — 위반 플래그 JSON이 잘리지 않을 만큼 충분, 과금 폭주 방지 */
    private const MAX_TOKENS = 2048;

    /** 429·5xx 재시도 횟수 (총 시도 = MAX_RETRIES + 1) */
    private const MAX_RETRIES = 2;

    /** 백오프 상한(초) — Retry-After가 비정상적으로 길어도 이 값으로 캡 */
    private const MAX_BACKOFF = 10;

    private string $apiKey;
    private string $model;
    private int $timeout;

    public function __construct()
    {
        $this->apiKey  = (string) env('GEMINI_API_KEY', '');
        $this->model   = (string) env('GEMINI_MODEL', self::DEFAULT_MODEL);
        $this->timeout = max(5, (int) env('GEMINI_TIMEOUT', 30));
    }

    public function isConfigured(): bool
    {
        return $this->apiKey !== '';
    }

    public function complete(string $systemPrompt, string $userPrompt): string
    {
        return $this->request($systemPrompt, $userPrompt, false);
    }

    public function completeJson(string $systemPrompt, string $userPrompt): array
    {
        $content = $this->request($systemPrompt, $userPrompt, true);

        /** @var array<string, mixed>|null $decoded */
        $decoded = json_decode($content, true);
        if (! is_array($decoded)) {
            throw new RuntimeException('Gemini API JSON 응답을 파싱하지 못했습니다.');
        }

        return $decoded;
    }

    /** 공통 호출 루프 — $json=true면 JSON 모드로 요청하고 본문 문자열을 반환 */
    private function request(string $systemPrompt, string $userPrompt, bool $json): string
    {
        if (! $this->isConfigured()) {
            throw new RuntimeException('GEMINI_API_KEY가 설정되지 않았습니다.');
        }

        for ($attempt = 0; ; $attempt++) {
            $response = $this->send($systemPrompt, $userPrompt, $json);
            $status   = $response->getStatusCode();
            $body     = (string) $response->getBody();

            if ($status >= 200 && $status < 300) {
                return $this->extractContent($body);
            }

            // 429(레이트리밋)·5xx(서버 일시 오류)는 백오프 후 재시도, 그 외 4xx는 즉시 실패
            $retryable = $status === 429 || $status >= 500;
            if ($retryable && $attempt < self::MAX_RETRIES) {
                $wait = $this->backoffSeconds($response, $attempt);
                log_message('warning', 'Gemini API {status} — {wait}초 후 재시도 ({n}/{max})', [
                    'status' => $status,
                    'wait'   => $wait,
                    'n'      => $attempt + 1,
                    'max'    => self::MAX_RETRIES,
                ]);
                sleep($wait);

                continue;
            }

            log_message('error', 'Gemini API 오류 [{status}]: {body}', [
                'status' => $status,
                'body'   => $body,
            ]);
            throw new RuntimeException("Gemini API 응답 오류 (HTTP {$status})");
        }
    }

    private function send(string $systemPrompt, string $userPrompt, bool $json): ResponseInterface
    {
        $client = service('curlrequest', [
            'baseURI'     => '',
            'timeout'     => $this->timeout,
            'http_errors' => false,
        ]);

        /** @var array<string, mixed> $generationConfig */
        $generationConfig = [
            'temperature'     => 0.4,
            'maxOutputTokens' => self::MAX_TOKENS,
        ];
        if ($json) {
            $generationConfig['responseMimeType'] = 'application/json';
        }

        return $client->post(sprintf(self::ENDPOINT_TEMPLATE, $this->model), [
            'headers' => [
                'Content-Type'    => 'application/json',
                'x-goog-api-key'  => $this->apiKey,
            ],
            'json' => [
                'systemInstruction' => [
                    'parts' => [['text' => $systemPrompt]],
                ],
                'contents' => [
                    ['role' => 'user', 'parts' => [['text' => $userPrompt]]],
                ],
                'generationConfig' => $generationConfig,
            ],
        ]);
    }

    /** 재시도 대기 시간 — Retry-After 헤더 우선, 없으면 지수 백오프(2,4,8…), MAX_BACKOFF로 캡 */
    private function backoffSeconds(ResponseInterface $response, int $attempt): int
    {
        $retryAfter = (int) $response->getHeaderLine('Retry-After');
        if ($retryAfter > 0) {
            return min($retryAfter, self::MAX_BACKOFF);
        }

        return min(2 ** ($attempt + 1), self::MAX_BACKOFF);
    }

    private function extractContent(string $body): string
    {
        /** @var array<string, mixed>|null $decoded */
        $decoded = json_decode($body, true);
        $content = $decoded['candidates'][0]['content']['parts'][0]['text'] ?? null;

        if (! is_string($content) || $content === '') {
            throw new RuntimeException('Gemini API 응답에서 본문을 추출하지 못했습니다.');
        }

        return trim($content);
    }
}
