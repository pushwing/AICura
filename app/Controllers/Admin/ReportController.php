<?php

namespace App\Controllers\Admin;

use App\Models\ReportModel;

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

    public function index(): string
    {
        $currentYear = (int) date('Y');
        $year        = (int) ($this->request->getGet('year') ?? $currentYear);

        if ($year < 2020 || $year > $currentYear) {
            $year = $currentYear;
        }

        $kpi           = $this->reportModel->getYearKpi($year);
        $monthlyAmounts = $this->reportModel->getMonthlyRevenue($year);

        $chartLabels = [];
        for ($m = 1; $m <= 12; $m++) {
            $chartLabels[] = $m . '월';
        }

        $years = [];
        for ($y = $currentYear; $y >= 2020; $y--) {
            $years[] = $y;
        }

        return $this->render('admin/reports/index', [
            'year'           => $year,
            'years'          => $years,
            'kpi'            => $kpi,
            'chartLabels'    => $chartLabels,
            'chartAmounts'   => $monthlyAmounts,
        ]);
    }

    public function campaigns(): string
    {
        $params = [
            'date_from' => (string) ($this->request->getGet('date_from') ?? ''),
            'date_to'   => (string) ($this->request->getGet('date_to') ?? ''),
            'ad_title'  => (string) ($this->request->getGet('ad_title') ?? ''),
        ];

        $rows = $this->reportModel->getCampaignConsumption($params);

        return $this->render('admin/reports/campaigns', [
            'rows'   => $rows,
            'params' => $params,
        ]);
    }
}
