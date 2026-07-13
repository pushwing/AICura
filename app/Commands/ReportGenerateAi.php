<?php

namespace App\Commands;

use App\Models\ReportModel;
use App\Services\AiReportService;
use App\Services\ReportScope;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Throwable;

/**
 * 일일 AI 보고서 생성 커맨드 (이슈 #65)
 *
 *   php spark reports:generate-ai           # 전일 기준 매출·소진 보고서 생성
 *   php spark reports:generate-ai --date=2026-06-25
 *
 * 서버 crontab 등록 예시:
 *   0 6 * * * cd /path/to/app && php spark reports:generate-ai >> writable/logs/ai-report.log 2>&1
 */
class ReportGenerateAi extends BaseCommand
{
    protected $group       = 'AICura';
    protected $name        = 'reports:generate-ai';
    protected $description = 'Groq AI로 일일 매출·소진 보고서를 생성합니다.';
    protected $usage       = 'reports:generate-ai [options]';

    /**
     * @var array<string, string>
     */
    protected $options = [
        '--date' => '보고서 기준일 (YYYY-MM-DD, 기본값: 어제)',
    ];

    /**
     * @param array<int|string, string|null> $params
     */
    public function run(array $params): void
    {
        $date    = $params['date'] ?? CLI::getOption('date') ?? null;
        $service = new AiReportService();
        $reports = model(ReportModel::class);

        CLI::write('AI 일일 보고서 생성 시작' . ($date !== null ? " (기준일: {$date})" : ''), 'yellow');

        // 1) 전체(운영자) 보고서
        CLI::write('[전체]', 'cyan');
        $this->generateScope($service, ReportScope::global(), $date);

        // 2) 광고주(병원) 단위 보고서
        foreach ($reports->getReportableHospitals() as $h) {
            CLI::write("[광고주] {$h['hospital_name']}", 'cyan');
            $this->generateScope($service, ReportScope::hospital($h['hospital_id'], $h['hospital_name']), $date);
        }

        // 3) 대행사 단위 보고서 (소속 광고주 합산)
        foreach ($reports->getReportableAgencies() as $a) {
            $hospitalIds = $reports->hospitalIdsForAgency($a['agency_user_id']);
            CLI::write("[대행사] {$a['agency_name']}", 'cyan');
            $this->generateScope(
                $service,
                ReportScope::agency($a['agency_user_id'], $hospitalIds, $a['agency_name']),
                $date,
            );
        }

        CLI::write('완료', 'green');
    }

    /**
     * 한 스코프의 매출·소진 보고서를 생성 (개별 실패는 로깅 후 계속)
     */
    private function generateScope(AiReportService $service, ReportScope $scope, ?string $date): void
    {
        $this->generate('  매출', static fn (): int => $service->generateRevenueReport($scope, $date));
        $this->generate('  소진', static fn (): int => $service->generateConsumptionReport($scope, $date));
    }

    /**
     * @param callable(): int $task
     */
    private function generate(string $label, callable $task): void
    {
        try {
            $id = $task();
            CLI::write("  ✓ {$label} 생성 완료 (ID: {$id})", 'green');
        } catch (Throwable $e) {
            CLI::error("  ✗ {$label} 생성 실패: " . $e->getMessage());
            log_message('error', 'AI 보고서 생성 실패 [{label}]: {msg}', [
                'label' => $label,
                'msg'   => $e->getMessage(),
            ]);
        }
    }
}
