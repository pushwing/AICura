<?php

namespace Tests\Unit;

use App\Libraries\Ai\AiClientInterface;
use App\Models\CampaignModel;
use App\Models\CampaignReviewRequestModel;
use App\Models\ComplianceCheckModel;
use App\Services\AiComplianceService;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * AiComplianceService 단위 테스트 (이슈 #71)
 *
 * AI 응답 정규화·등급 추론 로직과 빈 텍스트 단락(short-circuit)을 검증한다.
 * 외부 의존성(AI·모델)은 Fake/Mock으로 대체한다.
 *
 * @internal
 */
final class AiComplianceServiceTest extends CIUnitTestCase
{
    public function testNormalizeKeepsValidLevelAndMapsFlags(): void
    {
        $service = new AiComplianceService($this->fakeAi([]));

        $result = $service->normalize([
            'risk_level' => 'violation',
            'flags'      => [[
                'rule'       => '최상급 표현',
                'severity'   => 'HIGH',
                'quote'      => '최고의 시술',
                'reason'     => '배타성 표현 금지',
                'suggestion' => '전문적인 시술',
            ]],
        ]);

        $this->assertSame('violation', $result['risk_level']);
        $this->assertCount(1, $result['flags']);
        $this->assertSame('high', $result['flags'][0]['severity']);
        $this->assertSame('최상급 표현', $result['flags'][0]['rule']);
    }

    public function testNormalizeInfersViolationFromHighSeverityWhenLevelInvalid(): void
    {
        $service = new AiComplianceService($this->fakeAi([]));

        $result = $service->normalize([
            'risk_level' => 'unknown',
            'flags'      => [['severity' => 'high', 'rule' => 'x', 'quote' => 'q', 'reason' => 'r', 'suggestion' => 's']],
        ]);

        $this->assertSame('violation', $result['risk_level']);
    }

    public function testNormalizeInfersWarningFromLowSeverity(): void
    {
        $service = new AiComplianceService($this->fakeAi([]));

        $result = $service->normalize([
            'flags' => [['severity' => 'low', 'rule' => 'x', 'quote' => 'q', 'reason' => 'r', 'suggestion' => 's']],
        ]);

        $this->assertSame('warning', $result['risk_level']);
    }

    public function testNormalizeEmptyFlagsIsSafe(): void
    {
        $service = new AiComplianceService($this->fakeAi([]));

        $result = $service->normalize(['flags' => []]);

        $this->assertSame('safe', $result['risk_level']);
        $this->assertSame([], $result['flags']);
    }

    public function testNormalizeDropsNonArrayFlags(): void
    {
        $service = new AiComplianceService($this->fakeAi([]));

        $result = $service->normalize(['risk_level' => 'warning', 'flags' => ['oops', 123]]);

        $this->assertSame([], $result['flags']);
    }

    public function testCheckWithEmptyTextSkipsAiAndStoresSafe(): void
    {
        // AI를 호출하면 실패하는 Fake — 빈 텍스트면 호출되지 않아야 한다
        $ai = new class implements AiClientInterface {
            public function isConfigured(): bool { return true; }
            public function complete(string $s, string $u): string { throw new \RuntimeException('called'); }
            public function completeJson(string $s, string $u): array { throw new \RuntimeException('AI를 호출하면 안 됩니다'); }
        };

        $campaignModel = $this->createMock(CampaignModel::class);
        $campaignModel->method('find')->willReturn(['ad_title' => '', 'ad_detail_info' => '']);

        $captured = null;
        $checkModel = $this->createMock(ComplianceCheckModel::class);
        $checkModel->method('insert')->willReturnCallback(static function ($data) use (&$captured) {
            $captured = $data;

            return 7;
        });

        $reviewModel = $this->createMock(CampaignReviewRequestModel::class);

        $service = new AiComplianceService($ai, $checkModel, $campaignModel, $reviewModel);
        $id      = $service->check(10);

        $this->assertSame(7, $id);
        $this->assertSame('safe', $captured['risk_level']);
        $this->assertSame('[]', $captured['flags']);
    }

    public function testCheckStoresNormalizedViolation(): void
    {
        $ai = $this->fakeAi([
            'risk_level' => 'violation',
            'flags'      => [['rule' => '과장', 'severity' => 'high', 'quote' => '100% 효과', 'reason' => 'r', 'suggestion' => 's']],
        ]);

        $campaignModel = $this->createMock(CampaignModel::class);
        $campaignModel->method('find')->willReturn(['ad_title' => '100% 효과 보장', 'ad_detail_info' => '<p>설명</p>']);

        $captured = null;
        $checkModel = $this->createMock(ComplianceCheckModel::class);
        $checkModel->method('insert')->willReturnCallback(static function ($data) use (&$captured) {
            $captured = $data;

            return 99;
        });

        $reviewModel = $this->createMock(CampaignReviewRequestModel::class);

        $service = new AiComplianceService($ai, $checkModel, $campaignModel, $reviewModel);
        $id      = $service->check(10);

        $this->assertSame(99, $id);
        $this->assertSame('violation', $captured['risk_level']);
        $this->assertSame(10, $captured['campaign_id']);
        $this->assertStringContainsString('100% 효과 보장', (string) $captured['checked_text']);
        $this->assertStringNotContainsString('<p>', (string) $captured['checked_text']);
    }

    /**
     * @param array<string, mixed> $jsonResponse
     */
    private function fakeAi(array $jsonResponse): AiClientInterface
    {
        return new class ($jsonResponse) implements AiClientInterface {
            /** @param array<string, mixed> $jsonResponse */
            public function __construct(private array $jsonResponse) {}

            public function isConfigured(): bool { return true; }

            public function complete(string $systemPrompt, string $userPrompt): string { return ''; }

            /** @return array<string, mixed> */
            public function completeJson(string $systemPrompt, string $userPrompt): array { return $this->jsonResponse; }
        };
    }
}
