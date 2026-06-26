<?php

namespace App\Commands;

use App\Services\AiReportService;
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

    /** @var array<string, string> */
    protected $options = [
        '--date' => '보고서 기준일 (YYYY-MM-DD, 기본값: 어제)',
    ];

    /** @param array<int|string, string|null> $params */
    public function run(array $params): void
    {
        $date    = $params['date'] ?? CLI::getOption('date') ?? null;
        $service = new AiReportService();

        CLI::write('AI 일일 보고서 생성 시작' . ($date !== null ? " (기준일: {$date})" : ''), 'yellow');

        $this->generate('매출 보고서', static fn (): int => $service->generateRevenueReport($date));
        $this->generate('소진 보고서', static fn (): int => $service->generateConsumptionReport($date));
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
            log_message('error', "AI 보고서 생성 실패 [{label}]: {msg}", [
                'label' => $label,
                'msg'   => $e->getMessage(),
            ]);
        }
    }
}
