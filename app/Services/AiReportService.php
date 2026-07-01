<?php

namespace App\Services;

use App\Libraries\Ai\AiClientFactory;
use App\Libraries\Ai\AiClientInterface;
use App\Models\AiReportModel;
use App\Models\ReportModel;

/**
 * AI 일일 보고서 생성 서비스 (이슈 #65)
 *
 * 집계 데이터 수집 → Groq AI 산문 생성 → ai_reports 저장을 오케스트레이션한다.
 * Spark 커맨드(정기 실행)와 Admin 컨트롤러(수동 버튼)가 공용으로 호출한다.
 */
class AiReportService
{
    /** 소진보고서 잔액 임계 비율 — 충전금의 5% 이하 */
    private const float LOW_BALANCE_RATIO = 0.05;

    private readonly ReportModel $reportModel;
    private readonly AiReportModel $aiReportModel;
    private readonly AiClientInterface $ai;

    public function __construct(
        ?ReportModel $reportModel = null,
        ?AiReportModel $aiReportModel = null,
        ?AiClientInterface $ai = null
    ) {
        $this->reportModel   = $reportModel   ?? model(ReportModel::class);
        $this->aiReportModel = $aiReportModel ?? model(AiReportModel::class);
        $this->ai            = $ai            ?? AiClientFactory::make();
    }

    /**
     * 매출보고서 생성 — 전일 1일치 + 당월 누계 현황·특이점
     *
     * @param ReportScope|null $scope 생성 범위 (null이면 전체)
     * @return int 저장된 보고서 ID
     */
    public function generateRevenueReport(?ReportScope $scope = null, ?string $reportDate = null): int
    {
        $scope ??= ReportScope::global();
        $date      = $reportDate ?? date('Y-m-d', strtotime('-1 day'));
        $monthFrom = date('Y-m-01', strtotime($date));

        $daily       = $this->reportModel->getDailyStats($date, $scope->hospitalIds);
        $monthToDate = $this->reportModel->getMonthToDateStats($monthFrom, $date, $scope->hospitalIds);

        $meta = [
            'scope_type'    => $scope->type,
            'scope_id'      => $scope->id,
            'report_date'   => $date,
            'month_from'    => $monthFrom,
            'daily'         => $daily,
            'month_to_date' => $monthToDate,
        ];

        $content = $this->ai->complete(
            $this->revenueSystemPrompt(),
            $this->revenueUserPrompt($scope, $date, $monthFrom, $daily, $monthToDate)
        );

        return (int) $this->aiReportModel->insert([
            'scope_type'  => $scope->type,
            'scope_id'    => $scope->id,
            'type'        => AiReportModel::TYPE_REVENUE,
            'title'       => $this->titlePrefix($scope) . $date . ' 매출 현황 보고서',
            'content'     => $content,
            'report_date' => $date,
            'meta'        => json_encode($meta, JSON_UNESCAPED_UNICODE),
        ], true);
    }

    /**
     * 소진보고서 생성 — 충전금의 5% 이하만 남은 광고주(병원) 분석
     *
     * @param ReportScope|null $scope 생성 범위 (null이면 전체)
     * @return int 저장된 보고서 ID
     */
    public function generateConsumptionReport(?ReportScope $scope = null, ?string $reportDate = null): int
    {
        $scope ??= ReportScope::global();
        $date       = $reportDate ?? date('Y-m-d', strtotime('-1 day'));
        $lowBalance = $this->reportModel->getLowBalanceHospitals(self::LOW_BALANCE_RATIO, $scope->hospitalIds);

        $meta = [
            'scope_type'      => $scope->type,
            'scope_id'        => $scope->id,
            'report_date'     => $date,
            'threshold_ratio' => self::LOW_BALANCE_RATIO,
            'low_balance'     => $lowBalance,
        ];

        $content = $this->ai->complete(
            $this->consumptionSystemPrompt($scope),
            $this->consumptionUserPrompt($scope, $date, $lowBalance)
        );

        return (int) $this->aiReportModel->insert([
            'scope_type'  => $scope->type,
            'scope_id'    => $scope->id,
            'type'        => AiReportModel::TYPE_CONSUMPTION,
            'title'       => $this->titlePrefix($scope) . $date . ' 소진 보고서',
            'content'     => $content,
            'report_date' => $date,
            'meta'        => json_encode($meta, JSON_UNESCAPED_UNICODE),
        ], true);
    }

    /** 비-전체 스코프일 때 제목 앞에 주체명 표기 */
    private function titlePrefix(ReportScope $scope): string
    {
        return $scope->type === AiReportModel::SCOPE_GLOBAL ? '' : '[' . $scope->label . '] ';
    }

    // ──────────────────────────────────────────────
    // 프롬프트
    // ──────────────────────────────────────────────

    /** 스코프별 보고서 대상 설명 (프롬프트 문맥) */
    private function scopeContext(ReportScope $scope): string
    {
        return match ($scope->type) {
            AiReportModel::SCOPE_HOSPITAL => "보고 대상: '{$scope->label}' 광고주(병원) 단일",
            AiReportModel::SCOPE_AGENCY   => "보고 대상: '{$scope->label}' 대행사 소속 광고주 전체 합산",
            default                       => '보고 대상: 전체(운영자 관점)',
        };
    }

    private function revenueSystemPrompt(): string
    {
        return <<<'PROMPT'
            당신은 성형·토탈 광고 솔루션의 재무 분석가입니다.
            전달받은 충전·소진·환불 집계 수치를 바탕으로 한국어 매출 현황 보고서를 작성합니다.
            반드시 마크다운 형식으로 작성하고, 다음 구성을 따릅니다.
            1. ## 요약 — 전일 및 당월 핵심 수치 2~3문장
            2. ## 매출 현황 — 표로 충전/소진/환불/CPA환불복원/잔액 정리
            3. ## 특이점 및 분석 — 전일 대비 흐름, 주목할 점, 주의가 필요한 부분
            '환불'은 충전금 자체 환불(발행환불·계약환불)이고, 'CPA환불복원'은 신청DB 환불 승인으로 소진을 되돌린 금액으로 서로 다른 항목입니다. '소진'은 CPA환불복원이 이미 차감된 순소진 값이므로 이중으로 빼지 마십시오.
            금액은 원 단위로 천 단위 콤마를 사용합니다. 수치를 지어내지 말고 전달받은 값만 사용합니다.
            제공된 '보고 대상' 관점에 맞춰 서술합니다.
            PROMPT;
    }

    /**
     * @param array{charged: int, consumed: int, refunded: int, cpa_refunded: int} $daily
     * @param array{charged: int, consumed: int, refunded: int, cpa_refunded: int, balance: int} $monthToDate
     */
    private function revenueUserPrompt(ReportScope $scope, string $date, string $monthFrom, array $daily, array $monthToDate): string
    {
        $payload = json_encode([
            '기준일'      => $date,
            '당월_시작일' => $monthFrom,
            '전일_집계'   => [
                '충전'        => $daily['charged'],
                '소진'        => $daily['consumed'],
                '환불'        => $daily['refunded'],
                'CPA환불복원' => $daily['cpa_refunded'],
            ],
            '당월_누계'   => [
                '충전'        => $monthToDate['charged'],
                '소진'        => $monthToDate['consumed'],
                '환불'        => $monthToDate['refunded'],
                'CPA환불복원' => $monthToDate['cpa_refunded'],
                '잔액'        => $monthToDate['balance'],
            ],
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

        return $this->scopeContext($scope) . "\n\n다음 집계 데이터로 매출 현황 보고서를 작성하세요. (단위: 원)\n\n{$payload}";
    }

    private function consumptionSystemPrompt(ReportScope $scope): string
    {
        if ($scope->type === AiReportModel::SCOPE_HOSPITAL) {
            return <<<'PROMPT'
                당신은 성형·토탈 광고 솔루션의 운영 분석가입니다.
                특정 광고주(병원) 본인의 충전금 소진 현황을 받아 한국어 소진 보고서를 작성합니다.
                반드시 마크다운으로 작성하고 다음 구성을 따릅니다.
                1. ## 요약 — 현재 잔액 비율과 소진 임박 여부(잔액 5% 이하면 경고)
                2. ## 충전금 현황 — 표로 충전금/소진/잔액/잔액비율 정리
                3. ## 권고 사항 — 잔액이 부족하면 재충전 안내, 충분하면 안심 안내
                잔액비율은 백분율, 금액은 천 단위 콤마를 사용하고 수치를 지어내지 않습니다.
                PROMPT;
        }

        return <<<'PROMPT'
            당신은 성형·토탈 광고 솔루션의 운영 분석가입니다.
            충전금의 5% 이하만 남은 광고주(병원) 목록을 받아 한국어 소진 보고서를 작성합니다.
            반드시 마크다운 형식으로 작성하고, 다음 구성을 따릅니다.
            1. ## 요약 — 충전금 소진 임박(잔액 5% 이하) 광고주 수와 전반적 상황
            2. ## 소진 임박 광고주 — 표로 병원명/충전금/소진/잔액/잔액비율 정리(잔액비율 낮은 순)
            3. ## 권고 사항 — 재충전 안내·연락이 시급한 광고주, 운영상 유의점
            잔액비율은 백분율로 표기하고 금액은 천 단위 콤마를 사용합니다.
            대상이 없으면 소진 임박 광고주가 없다는 점을 명확히 안내합니다. 수치를 지어내지 않습니다.
            PROMPT;
    }

    /**
     * @param array<int, array{hospital_id: int, hospital_name: string, charged: int, used: int, balance: int, ratio: float}> $lowBalance
     */
    private function consumptionUserPrompt(ReportScope $scope, string $date, array $lowBalance): string
    {
        $rows = array_map(static fn (array $h): array => [
            '병원명'    => $h['hospital_name'],
            '충전금'    => $h['charged'],
            '소진'      => $h['used'],
            '잔액'      => $h['balance'],
            '잔액비율'  => round($h['ratio'] * 100, 1) . '%',
        ], $lowBalance);

        $payload = json_encode([
            '기준일'         => $date,
            '임계_잔액비율'  => '5%',
            '대상_광고주수'  => count($lowBalance),
            '대상_목록'      => $rows,
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

        return $this->scopeContext($scope) . "\n\n다음은 충전금의 5% 이하만 남은 광고주 목록입니다. 소진 보고서를 작성하세요. (단위: 원)\n\n{$payload}";
    }
}
