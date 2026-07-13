<?php

namespace App\Controllers\Admin;

use App\Enums\AppLogEvent;
use App\Libraries\MarkdownRenderer;
use App\Models\AiReportModel;
use App\Models\HourlyEventStatModel;
use App\Models\ReportModel;
use App\Services\AiReportService;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Override;
use Psr\Log\LoggerInterface;
use Throwable;

/**
 * 매출·통계 리포트 컨트롤러
 *
 *   index        - 매출 리포트 (deposits 기준 월별 충전/소진) + AI 일일 보고서
 *   campaigns    - 캠페인 리포트 (call_requests 기준 신청·내원완료)
 *   aiReportShow - AI 보고서 상세 (새창)
 *   aiReportList - AI 보고서 종류별 이전 목록 (더보기)
 *   generateAi   - AI 보고서 수동 생성 (POST)
 */
class ReportController extends BaseAdminController
{
    private const int AI_LIST_PER_PAGE = 20;

    // ──────────────────────────────────────────────
    // 앱 액션 로그 통계 (이슈 #120) — 시간별/일별 추이
    // ──────────────────────────────────────────────

    private const int APP_LOG_DAILY_RANGE = 14; // 일별 모드에서 보여줄 최근 일수

    private ReportModel $reportModel;
    private AiReportModel $aiReportModel;

    #[Override]
    public function initController(
        RequestInterface $request,
        ResponseInterface $response,
        LoggerInterface $logger,
    ): void {
        parent::initController($request, $response, $logger);
        $this->reportModel   = model(ReportModel::class);
        $this->aiReportModel = model(AiReportModel::class);
    }

    // ──────────────────────────────────────────────
    // 매출 리포트 (연도별 월간 충전/소진)
    // ──────────────────────────────────────────────

    public function index(): string
    {
        $year    = (int) ($this->request->getGet('year') ?? date('Y'));
        $monthly = $this->reportModel->getMonthlyRevenue($year);

        return $this->render('admin/reports/index', [
            'year'          => $year,
            'years'         => $this->yearOptions(),
            'kpi'           => $this->reportModel->getYearKpi($year),
            'labels'        => array_map(static fn (int $m): string => $m . '월', range(1, 12)),
            'charged'       => $monthly['charged'],
            'consumed'      => $monthly['consumed'],
            'aiRevenue'     => $this->aiReportModel->latestByType(AiReportModel::TYPE_REVENUE),
            'aiConsumption' => $this->aiReportModel->latestByType(AiReportModel::TYPE_CONSUMPTION),
        ]);
    }

    // ──────────────────────────────────────────────
    // AI 일일 보고서 (이슈 #65)
    // ──────────────────────────────────────────────

    /**
     * AI 보고서 상세 — 새창 standalone 페이지 (마크다운→HTML)
     */
    public function aiReportShow(int $id): string
    {
        $report = $this->aiReportModel->find($id);

        if ($report === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        return view('reports/ai_show', [
            'report'      => $report,
            'contentHtml' => (new MarkdownRenderer())->toSafeHtml((string) $report['content']),
        ]);
    }

    /**
     * AI 보고서 종류별 이전 목록 (더보기)
     */
    public function aiReportList(string $type): string
    {
        if (! in_array($type, [AiReportModel::TYPE_REVENUE, AiReportModel::TYPE_CONSUMPTION], true)) {
            throw PageNotFoundException::forPageNotFound();
        }

        $page    = max(1, (int) ($this->request->getGet('page') ?? 1));
        $history = $this->aiReportModel->historyByType($type, $page, self::AI_LIST_PER_PAGE);

        return $this->render('admin/reports/ai_list', [
            'type'      => $type,
            'typeLabel' => $type === AiReportModel::TYPE_REVENUE ? '매출' : '소진',
            'items'     => $history['items'],
            'total'     => $history['total'],
            'page'      => $page,
            'perPage'   => self::AI_LIST_PER_PAGE,
            'lastPage'  => (int) ceil($history['total'] / self::AI_LIST_PER_PAGE),
        ]);
    }

    /**
     * AI 보고서 수동 생성 (POST) — 매출·소진 즉시 생성 후 리포트로 복귀
     */
    public function generateAi(): RedirectResponse
    {
        try {
            $service = new AiReportService();
            $service->generateRevenueReport();
            $service->generateConsumptionReport();

            return redirect()->to('/admin/reports')
                ->with('success', 'AI 일일 보고서가 생성되었습니다.');
        } catch (Throwable $e) {
            log_message('error', 'AI 보고서 수동 생성 실패: {msg}', ['msg' => $e->getMessage()]);

            return redirect()->to('/admin/reports')
                ->with('error', 'AI 보고서 생성에 실패했습니다: ' . $e->getMessage());
        }
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
     * 앱 액션 로그 통계 화면.
     *   ?mode=hourly&date=YYYY-MM-DD  — 해당 날짜의 0~23시 추이 (기본, 1시간 전까지 반영)
     *   ?mode=daily&date=YYYY-MM-DD   — date 기준 최근 14일 일별 추이
     */
    public function appLogs(): string
    {
        $mode = $this->request->getGet('mode') === 'daily' ? 'daily' : 'hourly';
        $date = $this->request->getGet('date');
        $date = is_string($date) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) === 1 ? $date : date('Y-m-d');

        $stats = model(HourlyEventStatModel::class);

        if ($mode === 'hourly') {
            $rows   = $stats->hourlyByDate($date);
            $labels = array_map(static fn (int $h): string => $h . '시', range(0, 23));
            $chart  = $this->pivotSeries($rows, 'stat_hour', range(0, 23));
        } else {
            $from   = date('Y-m-d', strtotime($date . ' -' . (self::APP_LOG_DAILY_RANGE - 1) . ' days'));
            $rows   = $stats->dailyBetween($from, $date);
            $days   = $this->dateRange($from, $date);
            $labels = $days;
            $chart  = $this->pivotSeries($rows, 'stat_date', $days);
        }

        return $this->render('admin/reports/app_logs', [
            'mode'     => $mode,
            'date'     => $date,
            'labels'   => $labels,
            'datasets' => $chart['datasets'],
            'totals'   => $chart['totals'],
        ]);
    }

    /**
     * 집계 행을 Chart.js 데이터셋으로 피벗.
     * 행: {<bucketKey>, event, total} → 이벤트별 series + 이벤트별 합계.
     *
     * @param list<array<string, mixed>> $rows
     * @param 'stat_date'|'stat_hour'    $bucketKey
     * @param list<int|string>           $buckets   라벨 순서를 고정하는 버킷 목록
     *
     * @return array{datasets: list<array{label: string, event: string, data: list<int>}>, totals: array<string, int>}
     */
    private function pivotSeries(array $rows, string $bucketKey, array $buckets): array
    {
        // bucket 인덱스 맵 — 라벨 순서대로 0 채움
        $index  = array_flip(array_map(strval(...), $buckets));
        $series = []; // event => list<int> (버킷별 합계)
        $totals = []; // event => int

        foreach ($rows as $row) {
            $event  = (string) ($row['event'] ?? 'unknown');
            $bucket = (string) ($row[$bucketKey] ?? '');
            $total  = (int) ($row['total'] ?? 0);

            if (! isset($index[$bucket])) {
                continue;
            }
            if (! isset($series[$event])) {
                $series[$event] = array_fill(0, count($buckets), 0);
                $totals[$event] = 0;
            }

            $series[$event][$index[$bucket]] += $total;
            $totals[$event] += $total;
        }

        // 합계 내림차순으로 정렬해 주요 이벤트가 위로 오게 한다
        arsort($totals);

        $datasets = [];

        foreach (array_keys($totals) as $event) {
            $datasets[] = [
                'label' => AppLogEvent::labelFor($event),
                'event' => $event,
                'data'  => $series[$event],
            ];
        }

        return ['datasets' => $datasets, 'totals' => $totals];
    }

    /**
     * from~to(포함) 날짜 문자열 목록.
     *
     * @return list<string>
     */
    private function dateRange(string $from, string $to): array
    {
        $days  = [];
        $start = strtotime($from);
        $end   = strtotime($to);

        for ($t = $start; $t <= $end; $t += 86400) {
            $days[] = date('Y-m-d', $t);
        }

        return $days;
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
