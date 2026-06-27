<?php

namespace App\Services;

use App\Libraries\Ai\AiClientFactory;
use App\Libraries\Ai\AiClientInterface;
use App\Models\BoardModel;
use RuntimeException;

/**
 * AI 후기 신뢰성 분석 서비스 (이슈 #74)
 *
 * 후기 제목(subject)·본문(contents)을 분석해 감성·신뢰점수(0~100)·플래그(가짜/스팸/과장 등)를
 * 산출하고 boards에 저장한다. 성형·의료 도메인 특성상 허위·과장 후기는 심의·신뢰 리스크로
 * 직결되므로, 자동 비노출은 하지 않고 플래깅까지만 수행한다(최종 노출 결정은 운영자 판단).
 *
 * 즉시 응답이 필요한 작업이 아니므로 reviews:analyze 커맨드가 비동기로 호출한다.
 * 공급자는 Gemini에 고정한다(이슈 #74).
 */
class AiReviewQualityService
{
    /** 신뢰점수 허용 범위 */
    private const SCORE_MIN = 0;
    private const SCORE_MAX = 100;

    /** 근거 저장 컬럼 길이(VARCHAR 255)에 맞춘 절단 길이 */
    private const REASON_MAX = 255;

    /** 본문이 과도하게 길 때 프롬프트에 넣는 최대 길이 (토큰·비용 보호) */
    private const CONTENT_MAX = 4000;

    private AiClientInterface $ai;
    private BoardModel $boards;

    public function __construct(
        ?AiClientInterface $ai = null,
        ?BoardModel $boards = null,
    ) {
        // 공급자는 Gemini 고정 (이슈 #74 — 후기 신뢰성 분석은 Gemini 사용)
        $this->ai     = $ai     ?? AiClientFactory::make('gemini');
        $this->boards = $boards ?? model(BoardModel::class);
    }

    /**
     * 단건 후기 분석 — 감성·신뢰점수·플래그·근거를 산출해 boards에 저장.
     *
     * @throws RuntimeException 후기 없음·AI 호출 실패 시
     */
    public function analyze(int $boardId): void
    {
        $board = $this->boards
            ->select('id, subject, contents')
            ->where('is_delete', BoardModel::DELETE_NONE)
            ->find($boardId);

        if ($board === null) {
            throw new RuntimeException("분석 대상 후기를 찾을 수 없습니다: {$boardId}");
        }

        $result = $this->normalize(
            $this->ai->completeJson($this->systemPrompt(), $this->userPrompt($board))
        );

        $this->boards->saveAnalysis($boardId, $result);
    }

    /**
     * AI 원시 응답을 안전한 구조로 정규화 — 감성·플래그 화이트리스트, 점수 클램프, 근거 절단.
     *
     * @param array<string, mixed> $raw
     *
     * @return array{sentiment: string, trust_score: int, flags: list<string>, reason: string}
     */
    public function normalize(array $raw): array
    {
        $sentiment = is_string($raw['sentiment'] ?? null) ? strtolower($raw['sentiment']) : '';
        if (! in_array($sentiment, BoardModel::SENTIMENTS, true)) {
            $sentiment = 'neutral';
        }

        $score = (int) ($raw['trust_score'] ?? 0);
        $score = max(self::SCORE_MIN, min(self::SCORE_MAX, $score));

        return [
            'sentiment'   => $sentiment,
            'trust_score' => $score,
            'flags'       => $this->normalizeFlags($raw['flags'] ?? []),
            'reason'      => $this->clip($raw['reason'] ?? ''),
        ];
    }

    /**
     * 플래그를 허용 목록(BoardModel::FLAGS)으로 필터링·중복 제거해 list<string> 로 반환.
     *
     * @return list<string>
     */
    private function normalizeFlags(mixed $rawFlags): array
    {
        if (! is_array($rawFlags)) {
            return [];
        }

        $flags = [];
        foreach ($rawFlags as $flag) {
            if (! is_string($flag)) {
                continue;
            }
            $flag = strtolower(trim($flag));
            if (in_array($flag, BoardModel::FLAGS, true) && ! in_array($flag, $flags, true)) {
                $flags[] = $flag;
            }
        }

        return $flags;
    }

    /** 한 줄 텍스트로 정리 — 개행 제거 후 255자 절단 */
    private function clip(mixed $value): string
    {
        $text = trim(preg_replace('/\s+/u', ' ', (string) (is_string($value) ? $value : '')) ?? '');

        return mb_substr($text, 0, self::REASON_MAX);
    }

    private function systemPrompt(): string
    {
        $flags = implode(', ', BoardModel::FLAGS);

        return <<<PROMPT
            당신은 대한민국 성형·의료 후기 게시판의 신뢰성 평가 전문가입니다.
            후기의 제목과 본문을 분석해 감성과 신뢰성을 평가하고, 가짜·스팸·과장 후기를 플래깅합니다.

            신뢰성 평가 기준:
            - 반복/도배: 동일 문구 반복, 의미 없는 채우기 텍스트
            - 광고성(advertisement): 병원·시술 홍보, 연락처·예약 유도, 할인 강조
            - 비현실적 과장(exaggeration): "인생이 바뀌었다", "100% 만족" 등 근거 없는 극단 표현
            - 의학적 과대표현(medical_overclaim): "부작용 전혀 없음", "완벽 시술", "무조건 안전" 등 의료적으로 단정적인 주장
            - 가짜(fake): 체험 디테일이 없고 추상적이거나 작성 패턴이 부자연스러움
            - 스팸(spam): 후기와 무관한 내용, 외부 링크
            - 중복(duplicate): 다른 후기를 베낀 듯한 정형화된 문장

            출력 규칙:
            - 반드시 아래 JSON 스키마로만 응답하고 다른 문장은 출력하지 마세요.
            - sentiment: positive / neutral / negative 중 하나.
            - trust_score: 0~100 정수. 신뢰할 수 있을수록 높은 점수. 위 의심 신호가 많을수록 낮게.
            - flags: 해당되는 항목만 담은 배열. 다음 값만 사용: [{$flags}]. 없으면 빈 배열.
            - reason: 판단 근거 한 줄(한국어, 80자 이내).
            - 원문에 없는 내용을 절대 추측해 만들지 말고, 근거가 부족하면 보수적으로 평가하세요.

            JSON 스키마:
            {
              "sentiment": "neutral",
              "trust_score": 0,
              "flags": [],
              "reason": "판단 근거"
            }
            PROMPT;
    }

    /**
     * 분석 입력 프롬프트 — 제목 + 태그 제거한 본문만 전달.
     *
     * @param array{subject?: string|null, contents?: string|null} $board
     */
    private function userPrompt(array $board): string
    {
        $subject  = trim((string) ($board['subject'] ?? ''));
        $contents = trim(strip_tags((string) ($board['contents'] ?? '')));
        $contents = mb_substr($contents, 0, self::CONTENT_MAX);

        $payload = json_encode([
            '제목' => $subject !== '' ? $subject : '(없음)',
            '본문' => $contents !== '' ? $contents : '(없음)',
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

        return "다음 후기의 신뢰성을 평가하세요.\n\n{$payload}";
    }
}
