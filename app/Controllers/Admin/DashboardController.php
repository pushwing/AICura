<?php

namespace App\Controllers\Admin;

use App\Models\DashboardModel;

class DashboardController extends BaseAdminController
{
    private DashboardModel $dashboardModel;

    public function initController(
        \CodeIgniter\HTTP\RequestInterface $request,
        \CodeIgniter\HTTP\ResponseInterface $response,
        \Psr\Log\LoggerInterface $logger
    ): void {
        parent::initController($request, $response, $logger);
        $this->dashboardModel = model(DashboardModel::class);
    }

    public function index(): string
    {
        $year  = (int) date('Y');
        $month = (int) date('m');

        $kpi = $this->dashboardModel->getMonthlyKpi($year, $month);

        // 최근 6개월 목록 구성 (오래된 순)
        $months      = [];
        $chartLabels = [];
        for ($i = 5; $i >= 0; $i--) {
            $ts       = mktime(0, 0, 0, $month - $i, 1, $year);
            $months[] = ['y' => (int) date('Y', $ts), 'm' => (int) date('n', $ts)];
            $chartLabels[] = date('Y년 n월', $ts);
        }

        $trend = $this->dashboardModel->getMonthlyTrend($months);

        $contractMap = [];
        foreach ($trend['contracts'] as $row) {
            $key = ((string) $row['y']) . '-' . ((string) $row['m']);
            $contractMap[$key] = $row;
        }

        $revenueMap = [];
        foreach ($trend['revenues'] as $row) {
            $key = ((string) $row['y']) . '-' . ((string) $row['m']);
            $revenueMap[$key] = (int) $row['total_price'];
        }

        $chartContractCounts  = [];
        $chartContractAmounts = [];
        $chartRevenues        = [];

        foreach ($months as $mo) {
            $key = $mo['y'] . '-' . $mo['m'];
            $chartContractCounts[]  = (int) ($contractMap[$key]['cnt'] ?? 0);
            $chartContractAmounts[] = (int) ($contractMap[$key]['total_price'] ?? 0);
            $chartRevenues[]        = $revenueMap[$key] ?? 0;
        }

        return $this->render('admin/dashboard/index', [
            'title'                  => '대시보드',
            'totalCampaigns'         => $kpi['total_campaigns'],
            'activeCampaigns'        => $kpi['active_campaigns'],
            'monthlyContractCount'   => $kpi['monthly_contract_count'],
            'monthlyContractAmount'  => $kpi['monthly_contract_amount'],
            'monthlyRevenue'         => $kpi['monthly_revenue'],
            'chartLabels'            => $chartLabels,
            'chartContractCounts'    => $chartContractCounts,
            'chartContractAmounts'   => $chartContractAmounts,
            'chartRevenues'          => $chartRevenues,
        ]);
    }
}
