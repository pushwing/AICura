<?php

namespace App\Controllers\Admin;

use App\Models\AiReportModel;
use App\Models\ReportModel;
use App\Services\AiReportService;
use CodeIgniter\HTTP\RedirectResponse;
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
    private const AI_LIST_PER_PAGE = 20;

    private ReportModel $reportModel;
    private AiReportModel $aiReportModel;

    public function initController(
        \CodeIgniter\HTTP\RequestInterface $request,
        \CodeIgniter\HTTP\ResponseInterface $response,
        \Psr\Log\LoggerInterface $logger
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
            'year'    => $year,
            'years'   => $this->yearOptions(),
            'kpi'     => $this->reportModel->getYearKpi($year),
            'labels'  => array_map(static fn (int $m): string => $m . '월', range(1, 12)),
            'charged' => $monthly['charged'],
            'consumed' => $monthly['consumed'],
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
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        return view('reports/ai_show', ['report' => $report]);
    }

    /**
     * AI 보고서 종류별 이전 목록 (더보기)
     */
    public function aiReportList(string $type): string
    {
        if (! in_array($type, [AiReportModel::TYPE_REVENUE, AiReportModel::TYPE_CONSUMPTION], true)) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
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
