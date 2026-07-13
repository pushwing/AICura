<?php

namespace App\Controllers\Portal;

use App\Libraries\MarkdownRenderer;
use App\Models\AiReportModel;
use App\Models\PortalReportModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Override;
use Psr\Log\LoggerInterface;

/**
 * 포털 리포트 컨트롤러 (이슈 #56, #65)
 *
 * 운영자 매출 리포트를 참고해 범위를 좁혀 노출한다.
 *   - 광고주: 자기 병원 매출만 (+ 자기 병원 AI 보고서)
 *   - 대행사: 소속 광고주 매출 합계 + 광고주별 개별 (+ 소속 광고주 합산 AI 보고서)
 *
 * AI 보고서(이슈 #65)는 scope_type/scope_id로 본인 것만 조회·열람한다.
 */
class ReportController extends BasePortalController
{
    private const int AI_LIST_PER_PAGE = 20;

    private PortalReportModel $reportModel;
    private AiReportModel $aiReportModel;

    #[Override]
    public function initController(
        RequestInterface $request,
        ResponseInterface $response,
        LoggerInterface $logger,
    ): void {
        parent::initController($request, $response, $logger);
        $this->reportModel   = model(PortalReportModel::class);
        $this->aiReportModel = model(AiReportModel::class);
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
                'pageTitle'     => '리포트',
                'year'          => $year,
                'years'         => $this->yearOptions(),
                'hasHospital'   => false,
                'kpi'           => ['charged' => 0, 'consumed' => 0, 'refunded' => 0, 'cpa_refunded' => 0, 'balance' => 0],
                'call'          => ['requested' => 0, 'visited' => 0],
                'labels'        => $this->monthLabels(),
                'charged'       => array_fill(0, 12, 0),
                'consumed'      => array_fill(0, 12, 0),
                'campaigns'     => [],
                'aiRevenue'     => null,
                'aiConsumption' => null,
            ]);
        }

        $monthly = $this->reportModel->getHospitalMonthlyRevenue($hospitalId, $year);

        return $this->render('portal/reports/advertiser', array_merge([
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
        ], $this->latestAiReports()));
    }

    // ──────────────────────────────────────────────
    // 대행사 — 소속 광고주 합계 + 개별
    // ──────────────────────────────────────────────

    private function agencyReport(int $year): string
    {
        $breakdown = $this->reportModel->getAgencyAdvertiserBreakdown($this->userId(), $year);
        $summary   = $this->reportModel->summarizeAgency($breakdown);

        return $this->render('portal/reports/agency', array_merge([
            'pageTitle' => '리포트',
            'year'      => $year,
            'years'     => $this->yearOptions(),
            'summary'   => $summary,
            'breakdown' => $breakdown,
        ], $this->latestAiReports()));
    }

    // ──────────────────────────────────────────────
    // AI 일일 보고서 (이슈 #65) — 본인 스코프만
    // ──────────────────────────────────────────────

    /**
     * AI 보고서 상세 — 새창 standalone. 본인 스코프가 아니면 404.
     */
    public function aiReportShow(int $id): string
    {
        [$scopeType, $scopeId] = $this->aiScope();
        $report                = $this->aiReportModel->findScoped($id, $scopeType, $scopeId);

        if ($report === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        return view('reports/ai_show', [
            'report'      => $report,
            'contentHtml' => (new MarkdownRenderer())->toSafeHtml((string) $report['content']),
        ]);
    }

    /**
     * AI 보고서 종류별 이전 목록 (더보기) — 본인 스코프만
     */
    public function aiReportList(string $type): string
    {
        if (! in_array($type, [AiReportModel::TYPE_REVENUE, AiReportModel::TYPE_CONSUMPTION], true)) {
            throw PageNotFoundException::forPageNotFound();
        }

        [$scopeType, $scopeId] = $this->aiScope();
        $page                  = max(1, (int) ($this->request->getGet('page') ?? 1));
        $history               = $this->aiReportModel->historyByType($type, $page, self::AI_LIST_PER_PAGE, $scopeType, $scopeId);

        return $this->render('portal/reports/ai_list', [
            'pageTitle' => '리포트',
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
     * 로그인 주체의 AI 보고서 스코프 — 광고주: 자기 병원 / 대행사: 자기 사용자
     *
     * @return array{0: string, 1: int|null}
     */
    private function aiScope(): array
    {
        return $this->isAgency()
            ? [AiReportModel::SCOPE_AGENCY, $this->userId()]
            : [AiReportModel::SCOPE_HOSPITAL, $this->hospitalId()];
    }

    /**
     * 본인 스코프의 최신 매출·소진 보고서
     *
     * @return array{aiRevenue: array<string, mixed>|null, aiConsumption: array<string, mixed>|null}
     */
    private function latestAiReports(): array
    {
        [$scopeType, $scopeId] = $this->aiScope();

        return [
            'aiRevenue'     => $this->aiReportModel->latestByType(AiReportModel::TYPE_REVENUE, $scopeType, $scopeId),
            'aiConsumption' => $this->aiReportModel->latestByType(AiReportModel::TYPE_CONSUMPTION, $scopeType, $scopeId),
        ];
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
