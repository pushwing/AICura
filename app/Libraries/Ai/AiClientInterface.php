<?php

declare(strict_types=1);

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
     * 시스템·사용자 프롬프트로 단일 텍스트(산문) 응답 생성
     *
     * @throws RuntimeException 미설정·HTTP 오류·응답 파싱 실패 시
     */
    public function complete(string $systemPrompt, string $userPrompt): string;

    /**
     * 시스템·사용자 프롬프트로 단일 JSON 응답 생성·디코드
     *
     * 공급자의 JSON 모드(response_format / responseMimeType)를 사용해
     * 구조화 응답을 받아 연관 배열로 반환한다. 위반 플래그처럼
     * 기계 판독이 필요한 출력에 사용한다.
     *
     * @return array<string, mixed>
     *
     * @throws RuntimeException 미설정·HTTP 오류·JSON 파싱 실패 시
     */
    public function completeJson(string $systemPrompt, string $userPrompt): array;
}
