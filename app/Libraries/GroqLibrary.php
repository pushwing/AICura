<?php

namespace App\Libraries;

use RuntimeException;

/**
 * Groq AI Chat Completions 클라이언트 (이슈 #65)
 *
 * OpenAI 호환 엔드포인트(/openai/v1/chat/completions)를 호출한다.
 * 외부 라이브러리 없이 CI4 CURLRequest 서비스로 직접 구현한다.
 *
 *   GROQ_API_KEY  필수 — Groq 콘솔에서 발급
 *   GROQ_MODEL    선택 — 기본 llama-3.3-70b-versatile
 */
class GroqLibrary
{
    private const ENDPOINT      = 'https://api.groq.com/openai/v1/chat/completions';
    private const DEFAULT_MODEL = 'llama-3.3-70b-versatile';

    private string $apiKey;
    private string $model;

    public function __construct()
    {
        $this->apiKey = (string) env('GROQ_API_KEY', '');
        $this->model  = (string) env('GROQ_MODEL', self::DEFAULT_MODEL);
    }

    public function isConfigured(): bool
    {
        return $this->apiKey !== '';
    }

    /**
     * 시스템·사용자 프롬프트로 단일 응답 생성
     *
     * @throws RuntimeException API 키 미설정·HTTP 오류·응답 파싱 실패 시
     */
    public function complete(string $systemPrompt, string $userPrompt): string
    {
        if (! $this->isConfigured()) {
            throw new RuntimeException('GROQ_API_KEY가 설정되지 않았습니다.');
        }

        $client = service('curlrequest', [
            'baseURI'     => '',
            'timeout'     => 60,
            'http_errors' => false,
        ]);

        $response = $client->post(self::ENDPOINT, [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type'  => 'application/json',
            ],
            'json' => [
                'model'       => $this->model,
                'temperature' => 0.4,
                'messages'    => [
                    ['role' => 'system', 'content' => $systemPrompt],
                    ['role' => 'user',   'content' => $userPrompt],
                ],
            ],
        ]);

        $status = $response->getStatusCode();
        $body   = (string) $response->getBody();

        if ($status < 200 || $status >= 300) {
            log_message('error', 'Groq API 오류 [{status}]: {body}', [
                'status' => $status,
                'body'   => $body,
            ]);
            throw new RuntimeException("Groq API 응답 오류 (HTTP {$status})");
        }

        /** @var array<string, mixed>|null $decoded */
        $decoded = json_decode($body, true);
        $content = $decoded['choices'][0]['message']['content'] ?? null;

        if (! is_string($content) || $content === '') {
            throw new RuntimeException('Groq API 응답에서 본문을 추출하지 못했습니다.');
        }

        return trim($content);
    }
}
