<?php

namespace App\Services;

use App\Libraries\Ai\AiClientFactory;
use App\Libraries\Ai\AiClientInterface;
use App\Models\CallMemoModel;
use App\Models\CallRequestModel;
use RuntimeException;

/**
 * AI 콜(리드) 분석 서비스 (이슈 #72)
 *
 * 이벤트 신청 내용(content)과 상담 메모(call_memos)를 분석해
 * 전환 가능성 점수(0~100) · 한 줄 요약 · 다음 상담 액션을 산출하고
 * call_requests에 저장한다.
 *
 * 즉시 응답이 필요한 작업이 아니므로 leads:analyze 커맨드가 비동기로 호출한다.
 * 공급자는 Groq에 고정한다(사전검사와 동일한 배치/비용 정책).
 *
 * 개인정보 보호: phone·name 등 PII는 프롬프트에 절대 포함하지 않는다.
 * 입력은 content·메모·funnel·age·sex 로 한정한다.
 */
class AiLeadService
{
    /**
     * 점수 허용 범위
     */
    private const int SCORE_MIN = 0;

    private const int SCORE_MAX = 100;

    /**
     * 요약·다음액션 저장 컬럼 길이(VARCHAR 255)에 맞춘 절단 길이
     */
    private const int TEXT_MAX = 255;

    /**
     * @var array<int, string> 성별 라벨 (0 미지정 / 1 남 / 2 여)
     */
    private const array SEX_LABELS = [0 => '미지정', 1 => '남성', 2 => '여성'];

    private readonly AiClientInterface $ai;
    private readonly CallRequestModel $callRequests;
    private readonly CallMemoModel $callMemos;

    public function __construct(
        ?AiClientInterface $ai = null,
        ?CallRequestModel $callRequests = null,
        ?CallMemoModel $callMemos = null,
    ) {
        // 공급자는 Groq 고정 — 사전검사(AiComplianceService)와 동일 정책
        $this->ai           = $ai ?? AiClientFactory::make('groq');
        $this->callRequests = $callRequests ?? model(CallRequestModel::class);
        $this->callMemos    = $callMemos ?? model(CallMemoModel::class);
    }

    /**
     * 단건 리드 분석 — 점수·요약·다음액션을 산출해 call_requests에 저장.
     *
     * @throws RuntimeException 신청 건 없음·AI 호출 실패 시
     */
    public function analyze(int $callRequestId): void
    {
        $request = $this->callRequests
            ->select('id, content, funnel, age, sex')
            ->where('is_delete', 0)
            ->find($callRequestId);

        if ($request === null) {
            throw new RuntimeException("분석 대상 신청 건을 찾을 수 없습니다: {$callRequestId}");
        }

        $memos  = $this->callMemos->getMemosForAnalysis($callRequestId);
        $result = $this->normalize(
            $this->ai->completeJson($this->systemPrompt(), $this->userPrompt($request, $memos)),
        );

        $this->callRequests->saveAnalysis($callRequestId, $result);
    }

    /**
     * AI 원시 응답을 안전한 구조로 정규화 — 점수 클램프 + 문자열 절단.
     *
     * @param array<string, mixed> $raw
     *
     * @return array{score: int, summary: string, next_action: string}
     */
    public function normalize(array $raw): array
    {
        $score = (int) ($raw['score'] ?? 0);
        $score = max(self::SCORE_MIN, min(self::SCORE_MAX, $score));

        return [
            'score'       => $score,
            'summary'     => $this->clip($raw['summary'] ?? ''),
            'next_action' => $this->clip($raw['next_action'] ?? ''),
        ];
    }

    /**
     * 한 줄 텍스트로 정리 — 개행 제거 후 255자 절단
     */
    private function clip(mixed $value): string
    {
        $text = trim(preg_replace('/\s+/u', ' ', is_string($value) ? $value : '') ?? '');

        return mb_substr($text, 0, self::TEXT_MAX);
    }

    private function systemPrompt(): string
    {
        return <<<'PROMPT'
            당신은 대한민국 성형·의료 광고 대행사의 콜(리드) 분석 전문가입니다.
            이벤트 신청 내용과 상담 메모를 분석해 이 리드의 "내원 전환 가능성"을 평가합니다.

            평가 기준:
            - 신청 내용의 구체성·진정성 (시술/부위/시기 언급, 비용·예약 의향)
            - 상담 메모에 드러난 반응(긍정/보류/거부, 재상담 약속, 연락 두절 등)
            - 유입 경로·연령·성별로 추정되는 관심도

            출력 규칙:
            - 반드시 아래 JSON 스키마로만 응답하고 다른 문장은 출력하지 마세요.
            - score: 0~100 정수. 전환 가능성이 높을수록 높은 점수.
            - summary: 이 리드의 핵심을 담은 한 줄 요약(한국어, 80자 이내).
            - next_action: 상담사가 취할 가장 효과적인 다음 액션 한 가지(한국어, 80자 이내).
            - 정보가 부족하면 무리하게 추정하지 말고 보수적으로 평가하세요.

            JSON 스키마:
            {
              "score": 0,
              "summary": "한 줄 요약",
              "next_action": "다음 상담 액션"
            }
            PROMPT;
    }

    /**
     * 분석 입력 프롬프트 — PII(name·phone) 제외, content·메모·funnel·age·sex 만 전달.
     *
     * @param array{content?: string|null, funnel?: string|null, age?: int|string|null, sex?: int|string|null} $request
     * @param array<int, string>                                                                               $memos
     */
    private function userPrompt(array $request, array $memos): string
    {
        $content = trim((string) ($request['content'] ?? ''));
        $funnel  = trim((string) ($request['funnel'] ?? ''));
        $age     = $request['age'] ?? null;
        $sex     = (int) ($request['sex'] ?? 0);

        $payload = json_encode([
            '신청내용' => $content !== '' ? $content : '(없음)',
            '유입경로' => $funnel !== '' ? $funnel : '(미지정)',
            '연령'     => ($age !== null && $age !== '') ? (int) $age . '세' : '(미지정)',
            '성별'     => self::SEX_LABELS[$sex] ?? '미지정',
            '상담메모' => $memos !== [] ? $memos : ['(없음)'],
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

        return "다음 리드를 분석해 전환 가능성을 평가하세요.\n\n{$payload}";
    }
}
