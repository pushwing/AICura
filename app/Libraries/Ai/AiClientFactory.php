<?php

namespace App\Libraries\Ai;

use RuntimeException;

/**
 * AI 클라이언트 팩토리 (이슈 #65)
 *
 * env('AI_PROVIDER')로 구현체를 선택한다. 다른 AI로 교체하려면
 * 새 클라이언트 클래스(AiClientInterface 구현)를 추가하고 여기에 한 줄만 등록한 뒤
 * .env의 AI_PROVIDER 값을 바꾸면 된다. 호출부(Service·Command·Controller)는 무수정.
 */
class AiClientFactory
{
    public const DEFAULT_PROVIDER = 'groq';

    /**
     * @param string|null $provider null이면 env('AI_PROVIDER') 사용 (기본 groq)
     *
     * @throws RuntimeException 지원하지 않는 공급자일 때
     */
    public static function make(?string $provider = null): AiClientInterface
    {
        $provider = strtolower($provider ?? (string) env('AI_PROVIDER', self::DEFAULT_PROVIDER));

        return match ($provider) {
            'groq'   => new GroqClient(),
            'gemini' => new GeminiClient(),
            default  => throw new RuntimeException("지원하지 않는 AI 공급자입니다: {$provider}"),
        };
    }
}
