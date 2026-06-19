<?php

namespace App\Controllers\Admin;

use App\Models\ReportModel;

/**
 * 매출·통계 리포트 컨트롤러
 *
 *   index     - 매출 리포트 (deposits 기준 월별 충전/소진)
 *   campaigns - 캠페인 리포트 (call_requests 기준 신청·내원완료)
 */
class ReportController extends BaseAdminController
{
    private ReportModel $reportModel;

    public function initController(
        \CodeIgniter\HTTP\RequestInterface $request,
        \CodeIgniter\HTTP\ResponseInterface $response,
        \Psr\Log\LoggerInterface $logger
    ): void {
        parent::initController($request, $response, $logger);
        $this->reportModel = model(ReportModel::class);
    }

    // ──────────────────────────────────────────────
    // 매출 리포트 (연도별 월간 충전/소진)
    // ──────────────────────────────────────────────

    public function index(): string
    {
        $year    = (int) ($this->request->getGet('year') ?? date('Y'));
        $monthly = $this->reportModel->getMonthlyRevenue($year);

        return $this->render('admin/reports/index', [
            'year'    => $year,
            'years'   => $this->yearOptions(),
            'kpi'     => $this->reportModel->getYearKpi($year),
            'labels'  => array_map(static fn (int $m): string => $m . '월', range(1, 12)),
            'charged' => $monthly['charged'],
            'consumed' => $monthly['consumed'],
        ]);
    }

    // ──────────────────────────────────────────────
    // 캠페인 리포트 (신청·내원완료 통계)
    // ──────────────────────────────────────────────

    public function campaigns(): string
    {
        $params = [
            'date_from' => $this->request->getGet('date_from') ?? '',
            'date_to'   => $this->request->getGet('date_to') ?? '',
            'ad_title'  => $this->request->getGet('ad_title') ?? '',
        ];

        $stats = $this->reportModel->getCampaignStats($params);

        return $this->render('admin/reports/campaigns', [
            'stats'  => $stats,
            'params' => $params,
        ]);
    }

    /**
     * 연도 셀렉트 옵션 (최근 5년)
     *
     * @return array<int, int>
     */
    private function yearOptions(): array
    {
        $current = (int) date('Y');

        return range($current, $current - 4);
    }
}
