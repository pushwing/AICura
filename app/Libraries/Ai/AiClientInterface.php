<?php

namespace App\Libraries\Ai;

use RuntimeException;

/**
 * AI 챗 클라이언트 공통 인터페이스 (이슈 #65)
 *
 * 공급자(Groq·OpenAI·Claude 등)를 교체해도 호출부가 바뀌지 않도록
 * "시스템·사용자 프롬프트 → 텍스트 응답" 형태로 통일한다.
 */
interface AiClientInterface
{
    /**
     * 사용 가능 여부 (API 키 등 필수 설정 충족 여부)
     */
    public function isConfigured(): bool;

    /**
     * 시스템·사용자 프롬프트로 단일 응답 생성
     *
     * @throws RuntimeException 미설정·HTTP 오류·응답 파싱 실패 시
     */
    public function complete(string $systemPrompt, string $userPrompt): string;
}
