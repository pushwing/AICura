<?php

namespace App\Services;

use App\Libraries\GroqLibrary;
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
    private const LOW_BALANCE_RATIO = 0.05;

    private ReportModel $reportModel;
    private AiReportModel $aiReportModel;
    private GroqLibrary $groq;

    public function __construct(
        ?ReportModel $reportModel = null,
        ?AiReportModel $aiReportModel = null,
        ?GroqLibrary $groq = null
    ) {
        $this->reportModel   = $reportModel   ?? model(ReportModel::class);
        $this->aiReportModel = $aiReportModel ?? model(AiReportModel::class);
        $this->groq          = $groq          ?? new GroqLibrary();
    }

    /**
     * 매출보고서 생성 — 전일 1일치 + 당월 누계 현황·특이점
     *
     * @return int 저장된 보고서 ID
     */
    public function generateRevenueReport(?string $reportDate = null): int
    {
        $date      = $reportDate ?? date('Y-m-d', strtotime('-1 day'));
        $monthFrom = date('Y-m-01', strtotime($date));

        $daily      = $this->reportModel->getDailyStats($date);
        $monthToDate = $this->reportModel->getMonthToDateStats($monthFrom, $date);

        $meta = [
            'report_date'   => $date,
            'month_from'     => $monthFrom,
            'daily'          => $daily,
            'month_to_date'  => $monthToDate,
        ];

        $content = $this->groq->complete(
            $this->revenueSystemPrompt(),
            $this->revenueUserPrompt($date, $monthFrom, $daily, $monthToDate)
        );

        return (int) $this->aiReportModel->insert([
            'type'        => AiReportModel::TYPE_REVENUE,
            'title'       => $date . ' 매출 현황 보고서',
            'content'     => $content,
            'report_date' => $date,
            'meta'        => json_encode($meta, JSON_UNESCAPED_UNICODE),
        ], true);
    }

    /**
     * 소진보고서 생성 — 충전금의 5% 이하만 남은 광고주(병원) 분석
     *
     * @return int 저장된 보고서 ID
     */
    public function generateConsumptionReport(?string $reportDate = null): int
    {
        $date      = $reportDate ?? date('Y-m-d', strtotime('-1 day'));
        $lowBalance = $this->reportModel->getLowBalanceHospitals(self::LOW_BALANCE_RATIO);

        $meta = [
            'report_date'    => $date,
            'threshold_ratio' => self::LOW_BALANCE_RATIO,
            'low_balance'    => $lowBalance,
        ];

        $content = $this->groq->complete(
            $this->consumptionSystemPrompt(),
            $this->consumptionUserPrompt($date, $lowBalance)
        );

        return (int) $this->aiReportModel->insert([
            'type'        => AiReportModel::TYPE_CONSUMPTION,
            'title'       => $date . ' 소진 보고서',
            'content'     => $content,
            'report_date' => $date,
            'meta'        => json_encode($meta, JSON_UNESCAPED_UNICODE),
        ], true);
    }

    // ──────────────────────────────────────────────
    // 프롬프트
    // ──────────────────────────────────────────────

    private function revenueSystemPrompt(): string
    {
        return <<<'PROMPT'
            당신은 성형·토탈 광고 솔루션의 재무 분석가입니다.
            전달받은 충전·소진·환불 집계 수치를 바탕으로 한국어 매출 현황 보고서를 작성합니다.
            반드시 마크다운 형식으로 작성하고, 다음 구성을 따릅니다.
            1. ## 요약 — 전일 및 당월 핵심 수치 2~3문장
            2. ## 매출 현황 — 표로 충전/소진/환불/잔액 정리
            3. ## 특이점 및 분석 — 전일 대비 흐름, 주목할 점, 주의가 필요한 부분
            금액은 원 단위로 천 단위 콤마를 사용합니다. 수치를 지어내지 말고 전달받은 값만 사용합니다.
            PROMPT;
    }

    /**
     * @param array{charged: int, consumed: int, refunded: int} $daily
     * @param array{charged: int, consumed: int, refunded: int, balance: int} $monthToDate
     */
    private function revenueUserPrompt(string $date, string $monthFrom, array $daily, array $monthToDate): string
    {
        $payload = json_encode([
            '기준일'      => $date,
            '당월_시작일' => $monthFrom,
            '전일_집계'   => [
                '충전' => $daily['charged'],
                '소진' => $daily['consumed'],
                '환불' => $daily['refunded'],
            ],
            '당월_누계'   => [
                '충전' => $monthToDate['charged'],
                '소진' => $monthToDate['consumed'],
                '환불' => $monthToDate['refunded'],
                '잔액' => $monthToDate['balance'],
            ],
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

        return "다음 집계 데이터로 매출 현황 보고서를 작성하세요. (단위: 원)\n\n{$payload}";
    }

    private function consumptionSystemPrompt(): string
    {
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
    private function consumptionUserPrompt(string $date, array $lowBalance): string
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

        return "다음은 충전금의 5% 이하만 남은 광고주 목록입니다. 소진 보고서를 작성하세요. (단위: 원)\n\n{$payload}";
    }
}
