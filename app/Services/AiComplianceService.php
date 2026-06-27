<?php

namespace App\Services;

use App\Libraries\Ai\AiClientFactory;
use App\Libraries\Ai\AiClientInterface;
use App\Models\CampaignModel;
use App\Models\CampaignReviewRequestModel;
use App\Models\ComplianceCheckModel;
use RuntimeException;

/**
 * AI 의료광고 심의 사전검사 서비스 (이슈 #71)
 *
 * 소재 텍스트(광고 제목 + 상세 설명)를 의료법 제56조 위반 유형 기준으로
 * Groq AI에 1차 스크리닝시켜 위반 플래그(JSON)를 받아 저장한다.
 * 캠페인 등록/수정 시점(CampaignController)과 검수 화면의 재검사 액션에서 호출한다.
 */
class AiComplianceService
{
    private AiClientInterface $ai;
    private ComplianceCheckModel $checkModel;
    private CampaignModel $campaignModel;
    private CampaignReviewRequestModel $reviewModel;
    private string $modelName;

    public function __construct(
        ?AiClientInterface $ai = null,
        ?ComplianceCheckModel $checkModel = null,
        ?CampaignModel $campaignModel = null,
        ?CampaignReviewRequestModel $reviewModel = null
    ) {
        // 이슈 #71·#73은 Gemini 사용 — 공급자 명시 고정 (#65 보고서는 Groq 유지)
        $this->ai            = $ai            ?? AiClientFactory::make('gemini');
        $this->checkModel    = $checkModel    ?? model(ComplianceCheckModel::class);
        $this->campaignModel = $campaignModel ?? model(CampaignModel::class);
        $this->reviewModel   = $reviewModel   ?? model(CampaignReviewRequestModel::class);
        $this->modelName     = (string) env('GEMINI_MODEL', 'gemini-2.0-flash');
    }

    /**
     * 소재 심의 사전검사 수행 → 결과 저장 → 저장된 검사 ID 반환
     *
     * @param int      $campaignId      검사 대상 캠페인 ID
     * @param int|null $reviewRequestId 연관 검수 요청 ID (제목 출처·추적용)
     *
     * @return int 저장된 creative_compliance_checks ID
     *
     * @throws RuntimeException 캠페인 없음·AI 호출/파싱 실패 시
     */
    public function check(int $campaignId, ?int $reviewRequestId = null): int
    {
        $campaign = $this->campaignModel->find($campaignId);
        if ($campaign === null) {
            throw new RuntimeException("캠페인을 찾을 수 없습니다: {$campaignId}");
        }

        $text = $this->gatherText($campaign, $reviewRequestId);

        // 검사할 텍스트가 없으면 AI 호출 없이 안전으로 기록
        $result = $text === ''
            ? ['risk_level' => ComplianceCheckModel::RISK_SAFE, 'flags' => []]
            : $this->normalize($this->ai->completeJson($this->systemPrompt(), $text));

        return (int) $this->checkModel->insert([
            'campaign_id'       => $campaignId,
            'review_request_id' => $reviewRequestId,
            'risk_level'        => $result['risk_level'],
            'flags'             => json_encode($result['flags'], JSON_UNESCAPED_UNICODE),
            'checked_text'      => $text,
            'model'             => $this->modelName,
        ], true);
    }

    /**
     * 검사 대상 텍스트 조합 — 제목(검수 요청 우선) + 상세 설명(태그 제거)
     *
     * 콘텐츠는 승인 전까지 campaigns 가 아닌 검수 요청에 있으므로,
     * 제목·상세문구 모두 검수 요청 값을 우선 사용한다(이슈 #73으로 상세문구도 검수 요청에 포함).
     *
     * @param array<string, mixed> $campaign
     */
    private function gatherText(array $campaign, ?int $reviewRequestId): string
    {
        $adTitle = (string) ($campaign['ad_title'] ?? '');
        $detail  = (string) ($campaign['ad_detail_info'] ?? '');

        if ($reviewRequestId !== null) {
            $request = $this->reviewModel->find($reviewRequestId);
            if ($request !== null) {
                if (($request['ad_title'] ?? '') !== '') {
                    $adTitle = (string) $request['ad_title'];
                }
                if (($request['ad_detail_info'] ?? '') !== '') {
                    $detail = (string) $request['ad_detail_info'];
                }
            }
        }

        return trim($adTitle . "\n\n" . trim(strip_tags($detail)));
    }

    /**
     * AI 원시 응답을 저장 가능한 구조로 정규화 — 등급 검증·플래그 형태 통일
     *
     * @param array<string, mixed> $raw
     *
     * @return array{risk_level: string, flags: array<int, array{rule: string, severity: string, quote: string, reason: string, suggestion: string}>}
     */
    public function normalize(array $raw): array
    {
        $flags = [];
        $rawFlags = $raw['flags'] ?? [];
        if (is_array($rawFlags)) {
            foreach ($rawFlags as $flag) {
                if (! is_array($flag)) {
                    continue;
                }

                $severity = is_string($flag['severity'] ?? null) ? strtolower((string) $flag['severity']) : 'low';

                $flags[] = [
                    'rule'       => $this->str($flag['rule'] ?? ''),
                    'severity'   => in_array($severity, ['low', 'high'], true) ? $severity : 'low',
                    'quote'      => $this->str($flag['quote'] ?? ''),
                    'reason'     => $this->str($flag['reason'] ?? ''),
                    'suggestion' => $this->str($flag['suggestion'] ?? ''),
                ];
            }
        }

        $level = is_string($raw['risk_level'] ?? null) ? strtolower((string) $raw['risk_level']) : '';
        if (! in_array($level, ComplianceCheckModel::RISK_LEVELS, true)) {
            $level = $this->inferLevel($flags);
        }

        return ['risk_level' => $level, 'flags' => $flags];
    }

    /**
     * 등급 누락·이상 시 플래그로부터 위험 등급 추론
     *
     * @param array<int, array{rule: string, severity: string, quote: string, reason: string, suggestion: string}> $flags
     */
    private function inferLevel(array $flags): string
    {
        if ($flags === []) {
            return ComplianceCheckModel::RISK_SAFE;
        }

        foreach ($flags as $flag) {
            if ($flag['severity'] === 'high') {
                return ComplianceCheckModel::RISK_VIOLATION;
            }
        }

        return ComplianceCheckModel::RISK_WARNING;
    }

    /** @param mixed $value */
    private function str(mixed $value): string
    {
        return is_string($value) ? trim($value) : '';
    }

    private function systemPrompt(): string
    {
        return <<<'PROMPT'
            당신은 대한민국 의료법 제56조(의료광고의 금지 등) 기준으로 성형·의료 광고 소재를
            사전 검토하는 심의 보조 전문가입니다. 전달받은 광고 소재 텍스트를 아래 위반 유형으로 검토하세요.

            검토 유형:
            1) 거짓·과장 광고 (효과를 보장하거나 사실과 다른 표현)
            2) 최상급·배타성 표현 ("최고", "1위", "유일", "100%", "완벽" 등)
            3) 부작용·통증 등 중요 정보 미고지
            4) 비급여 진료비 할인·유인 (과도한 가격 할인, 이벤트성 유인)
            5) 치료경험담·후기 (환자의 치료 경험을 광고에 활용)
            6) 다른 의료기관·의료인 비교·비방
            7) 사전심의 대상 표현인데 심의번호가 없는 경우

            규칙:
            - 반드시 아래 JSON 스키마로만 응답하고 다른 문장은 출력하지 마세요.
            - 각 필드 값(reason·suggestion 등) 안에서는 큰따옴표(") 문자를 절대 사용하지 마세요.
              강조가 필요하면 작은따옴표(') 또는 낫표(「」)를 사용하세요. (JSON 파싱 오류 방지)
            - quote 에는 반드시 원문에 실제로 존재하는 표현만 그대로 인용하세요. 원문에 없는 표현을 지어내지 마세요.
            - 위반 소지가 없으면 flags 는 빈 배열, risk_level 은 "safe" 로 합니다.
            - severity 는 경미하면 "low", 명백·중대하면 "high" 로 합니다.
            - risk_level 은 위반이 명백하면 "violation", 주의가 필요하면 "warning", 문제없으면 "safe" 입니다.
            - suggestion 에는 의료법에 부합하도록 고친 수정안을 한국어로 제시합니다.

            JSON 스키마:
            {
              "risk_level": "safe|warning|violation",
              "flags": [
                {"rule":"위반 유형","severity":"low|high","quote":"문제 원문","reason":"위반 사유","suggestion":"수정안"}
              ]
            }
            PROMPT;
    }
}
