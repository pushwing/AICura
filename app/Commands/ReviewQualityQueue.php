<?php

namespace App\Commands;

use App\Models\BoardModel;
use App\Models\SettingModel;
use App\Services\AiReviewQualityService;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Throwable;

/**
 * AI 후기 신뢰성 분석 큐 소비 커맨드 (이슈 #74)
 *
 *   php spark reviews:analyze            # 대기(PENDING) 후기를 최대 50건 분석
 *   php spark reviews:analyze --limit=100
 *
 * Redis 등 별도 큐 인프라 없이 boards.ai_status 컬럼을 큐로 사용한다.
 * boards는 외부 시스템이 직접 INSERT하므로 신규 행은 DEFAULT로 PENDING이 되어
 * 자동으로 큐에 들어오고, 이 커맨드가 비동기로 소비한다(운영자 재분석도 동일 경로).
 *
 * 서버 crontab 등록 예시 (5분마다):
 *   매 5분  cd /path/to/app && php spark reviews:analyze >> writable/logs/review-analyze.log 2>&1
 */
class ReviewQualityQueue extends BaseCommand
{
    protected $group       = 'AICura';
    protected $name        = 'reviews:analyze';
    protected $description = '대기 중인 후기를 AI로 분석해 감성·신뢰점수·플래그를 채웁니다.';
    protected $usage       = 'reviews:analyze [options]';

    /** @var array<string, string> */
    protected $options = [
        '--limit' => '한 번에 처리할 최대 건수 (기본값: 50)',
    ];

    private const DEFAULT_LIMIT = 50;

    /** @param array<int|string, string|null> $params */
    public function run(array $params): void
    {
        // 기능 토글 — settings에서 꺼져 있으면 대기 건을 건드리지 않고 종료
        if (! model(SettingModel::class)->enabled('review_quality_enabled')) {
            CLI::write('AI 후기 신뢰성 분석 기능이 비활성화되어 있습니다 (settings: review_quality_enabled).', 'yellow');

            return;
        }

        $limit = (int) ($params['limit'] ?? CLI::getOption('limit') ?? self::DEFAULT_LIMIT);
        $limit = max(1, $limit);

        $boards  = model(BoardModel::class);
        $pending = $boards->getPendingAnalysis($limit);

        if ($pending === []) {
            CLI::write('분석 대기 중인 후기가 없습니다.', 'green');

            return;
        }

        CLI::write(sprintf('AI 후기 신뢰성 분석 시작 — 대기 %d건', count($pending)), 'yellow');

        $service = new AiReviewQualityService();
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
                $boards->markAnalysisFailed($id);
                CLI::error("  ✗ #{$id} 분석 실패: " . $e->getMessage());
                log_message('error', 'AI 후기 분석 실패 [board:{id}]: {msg}', [
                    'id'  => $id,
                    'msg' => $e->getMessage(),
                ]);
            }
        }

        CLI::write(sprintf('완료 — 성공 %d건, 실패 %d건', $ok, $failed), 'green');
    }
}
