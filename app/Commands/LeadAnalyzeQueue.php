<?php

namespace App\Commands;

use App\Models\CallRequestModel;
use App\Models\SettingModel;
use App\Services\AiLeadService;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Throwable;

/**
 * AI 리드(콜) 분석 큐 소비 커맨드 (이슈 #72)
 *
 *   php spark leads:analyze            # 대기(PENDING) 신청을 최대 50건 분석
 *   php spark leads:analyze --limit=100
 *
 * Redis 등 별도 큐 인프라 없이 call_requests.ai_status 컬럼을 큐로 사용한다.
 * 신청·메모 저장 시 PENDING(1)으로 적재되고, 이 커맨드가 비동기로 소비한다.
 *
 * 서버 crontab 등록 예시 (5분마다):
 *   매 5분  cd /path/to/app && php spark leads:analyze >> writable/logs/lead-analyze.log 2>&1
 */
class LeadAnalyzeQueue extends BaseCommand
{
    protected $group       = 'AICura';
    protected $name        = 'leads:analyze';
    protected $description = '대기 중인 이벤트 신청을 AI로 분석해 전환점수·요약·다음액션을 채웁니다.';
    protected $usage       = 'leads:analyze [options]';

    /** @var array<string, string> */
    protected $options = [
        '--limit' => '한 번에 처리할 최대 건수 (기본값: 50)',
    ];

    private const DEFAULT_LIMIT = 50;

    /** @param array<int|string, string|null> $params */
    public function run(array $params): void
    {
        // 기능 토글 — settings에서 꺼져 있으면 대기 건을 건드리지 않고 종료
        if (! model(SettingModel::class)->enabled('lead_analysis_enabled')) {
            CLI::write('AI 리드 분석 기능이 비활성화되어 있습니다 (settings: lead_analysis_enabled).', 'yellow');

            return;
        }

        $limit = (int) ($params['limit'] ?? CLI::getOption('limit') ?? self::DEFAULT_LIMIT);
        $limit = max(1, $limit);

        $callRequests = model(CallRequestModel::class);
        $pending      = $callRequests->getPendingAnalysis($limit);

        if ($pending === []) {
            CLI::write('분석 대기 중인 신청이 없습니다.', 'green');

            return;
        }

        CLI::write(sprintf('AI 리드 분석 시작 — 대기 %d건', count($pending)), 'yellow');

        $service = new AiLeadService();
        $ok      = 0;
        $failed  = 0;

        foreach ($pending as $row) {
            $id = (int) $row['id'];
            try {
                $service->analyze($id);
                $ok++;
                CLI::write("  ✓ #{$id} 분석 완료", 'green');
            } catch (Throwable $e) {
                $failed++;
                $callRequests->markAnalysisFailed($id);
                CLI::error("  ✗ #{$id} 분석 실패: " . $e->getMessage());
                log_message('error', 'AI 리드 분석 실패 [call_request:{id}]: {msg}', [
                    'id'  => $id,
                    'msg' => $e->getMessage(),
                ]);
            }
        }

        CLI::write(sprintf('완료 — 성공 %d건, 실패 %d건', $ok, $failed), 'green');
    }
}
