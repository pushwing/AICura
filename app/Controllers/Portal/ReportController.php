<?php

namespace App\Controllers\Portal;

use App\Models\PortalReportModel;

/**
 * 포털 리포트 컨트롤러 (이슈 #56)
 *
 * 운영자 매출 리포트를 참고해 범위를 좁혀 노출한다.
 *   - 광고주: 자기 병원 매출만
 *   - 대행사: 소속 광고주 매출 합계 + 광고주별 개별
 */
class ReportController extends BasePortalController
{
    private PortalReportModel $reportModel;

    public function initController(
        \CodeIgniter\HTTP\RequestInterface $request,
        \CodeIgniter\HTTP\ResponseInterface $response,
        \Psr\Log\LoggerInterface $logger
    ): void {
        parent::initController($request, $response, $logger);
        $this->reportModel = model(PortalReportModel::class);
    }

    public function index(): string
    {
        $year = (int) ($this->request->getGet('year') ?? date('Y'));

        return $this->isAgency()
            ? $this->agencyReport($year)
            : $this->advertiserReport($year);
    }

    // ──────────────────────────────────────────────
    // 광고주 — 자기 병원 매출
    // ──────────────────────────────────────────────

    private function advertiserReport(int $year): string
    {
        $hospitalId = $this->hospitalId();

        // 병원 미연결(계약 전) 광고주는 빈 상태 안내
        if ($hospitalId === null) {
            return $this->render('portal/reports/advertiser', [
                'pageTitle'    => '리포트',
                'year'         => $year,
                'years'        => $this->yearOptions(),
                'hasHospital'  => false,
                'kpi'          => ['charged' => 0, 'consumed' => 0, 'refunded' => 0, 'balance' => 0],
                'call'         => ['requested' => 0, 'visited' => 0],
                'labels'       => $this->monthLabels(),
                'charged'      => array_fill(0, 12, 0),
                'consumed'     => array_fill(0, 12, 0),
                'campaigns'    => [],
            ]);
        }

        $monthly = $this->reportModel->getHospitalMonthlyRevenue($hospitalId, $year);

        return $this->render('portal/reports/advertiser', [
            'pageTitle'   => '리포트',
            'year'        => $year,
            'years'       => $this->yearOptions(),
            'hasHospital' => true,
            'kpi'         => $this->reportModel->getHospitalYearKpi($hospitalId, $year),
            'call'        => $this->reportModel->getHospitalCallSummary($hospitalId, $year),
            'labels'      => $this->monthLabels(),
            'charged'     => $monthly['charged'],
            'consumed'    => $monthly['consumed'],
            'campaigns'   => $this->reportModel->getHospitalCampaignStats($hospitalId, $year),
        ]);
    }

    // ──────────────────────────────────────────────
    // 대행사 — 소속 광고주 합계 + 개별
    // ──────────────────────────────────────────────

    private function agencyReport(int $year): string
    {
        $breakdown = $this->reportModel->getAgencyAdvertiserBreakdown($this->userId(), $year);
        $summary   = $this->reportModel->summarizeAgency($breakdown);

        return $this->render('portal/reports/agency', [
            'pageTitle' => '리포트',
            'year'      => $year,
            'years'     => $this->yearOptions(),
            'summary'   => $summary,
            'breakdown' => $breakdown,
        ]);
    }

    /**
     * 연도 셀렉트 옵션 (최근 5년)
     *
     * @return list<int>
     */
    private function yearOptions(): array
    {
        $current = (int) date('Y');

        return range($current, $current - 4);
    }

    /**
     * 월 라벨 (1월 ~ 12월)
     *
     * @return list<string>
     */
    private function monthLabels(): array
    {
        return array_map(static fn (int $m): string => $m . '월', range(1, 12));
    }
}
