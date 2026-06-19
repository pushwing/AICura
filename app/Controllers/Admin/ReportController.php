<?php

namespace App\Controllers\Admin;

use App\Models\ReportModel;

class ReportController extends BaseAdminController
{
    private const MIN_YEAR = 2020; // Fix #6: 연도 하한 상수화

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

        if ($year < self::MIN_YEAR || $year > $currentYear) {
            $year = $currentYear;
        }

        $kpi            = $this->reportModel->getYearKpi($year);
        $monthlyAmounts = $this->reportModel->getMonthlyRevenue($year);

        $chartLabels = [];
        for ($m = 1; $m <= 12; $m++) {
            $chartLabels[] = $m . '월';
        }

        $years = [];
        for ($y = $currentYear; $y >= self::MIN_YEAR; $y--) {
            $years[] = $y;
        }

        return $this->render('admin/reports/index', [
            'year'         => $year,
            'years'        => $years,
            'kpi'          => $kpi,
            'chartLabels'  => $chartLabels,
            'chartAmounts' => $monthlyAmounts,
        ]);
    }

    public function campaigns(): string
    {
        $dateFrom = (string) ($this->request->getGet('date_from') ?? '');
        $dateTo   = (string) ($this->request->getGet('date_to') ?? '');
        $adTitle  = (string) ($this->request->getGet('ad_title') ?? '');

        // Fix #3: 날짜 형식 검증 (YYYY-MM-DD)
        $dateFrom = $this->sanitizeDate($dateFrom);
        $dateTo   = $this->sanitizeDate($dateTo);

        // Fix #4: 역방향 날짜 범위 교정
        if ($dateFrom !== '' && $dateTo !== '' && $dateFrom > $dateTo) {
            [$dateFrom, $dateTo] = [$dateTo, $dateFrom];
        }

        $params = [
            'date_from' => $dateFrom,
            'date_to'   => $dateTo,
            'ad_title'  => $adTitle,
        ];

        $rows = $this->reportModel->getCampaignConsumption($params);

        return $this->render('admin/reports/campaigns', [
            'rows'   => $rows,
            'params' => $params,
        ]);
    }

    private function sanitizeDate(string $value): string
    {
        if ($value === '') {
            return '';
        }
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $value) !== 1) {
            return '';
        }
        return strtotime($value) !== false ? $value : '';
    }
}
