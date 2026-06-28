<?php

namespace Tests\Unit;

use App\Libraries\Ai\AiClientInterface;
use App\Models\BoardModel;
use App\Services\AiReviewQualityService;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * AiReviewQualityService 단위 테스트 (이슈 #74)
 *
 * AI 원시 응답의 방어 정규화(감성·플래그 화이트리스트, 점수 클램프, 근거 절단)를 검증한다.
 * 외부 의존성(AI·모델)은 Fake/Mock으로 대체한다.
 *
 * @internal
 */
final class AiReviewQualityServiceTest extends CIUnitTestCase
{
    public function testNormalizeClampsScoreIntoRange(): void
    {
        $service = $this->service();

        $this->assertSame(100, $service->normalize(['trust_score' => 250])['trust_score']);
        $this->assertSame(0, $service->normalize(['trust_score' => -10])['trust_score']);
        $this->assertSame(73, $service->normalize(['trust_score' => 73])['trust_score']);
    }

    public function testNormalizeDefaultsMissingFields(): void
    {
        $result = $this->service()->normalize([]);

        $this->assertSame('neutral', $result['sentiment']);
        $this->assertSame(0, $result['trust_score']);
        $this->assertSame([], $result['flags']);
        $this->assertSame('', $result['reason']);
    }

    public function testNormalizeRejectsUnknownSentiment(): void
    {
        $this->assertSame('positive', $this->service()->normalize(['sentiment' => 'POSITIVE'])['sentiment']);
        $this->assertSame('neutral', $this->service()->normalize(['sentiment' => '최고'])['sentiment']);
    }

    public function testNormalizeFiltersFlagsToWhitelistAndDedupes(): void
    {
        $result = $this->service()->normalize([
            'flags' => ['fake', 'FAKE', 'spam', '존재하지않는플래그', 123, 'medical_overclaim'],
        ]);

        $this->assertSame(['fake', 'spam', 'medical_overclaim'], $result['flags']);
    }

    public function testNormalizeIgnoresNonArrayFlags(): void
    {
        $this->assertSame([], $this->service()->normalize(['flags' => 'fake'])['flags']);
    }

    public function testNormalizeCollapsesWhitespaceAndTruncatesReason(): void
    {
        $long = str_repeat('가', 300);

        $this->assertSame('여러 줄 근거', $this->service()->normalize(['reason' => "여러   줄\n근거"])['reason']);
        $this->assertSame(255, mb_strlen($this->service()->normalize(['reason' => $long])['reason']));
    }

    private function service(): AiReviewQualityService
    {
        $ai = new class implements AiClientInterface {
            public function isConfigured(): bool { return true; }

            public function complete(string $systemPrompt, string $userPrompt): string { return ''; }

            /** @return array<string, mixed> */
            public function completeJson(string $systemPrompt, string $userPrompt): array { return []; }
        };

        return new AiReviewQualityService(
            $ai,
            $this->createMock(BoardModel::class),
        );
    }
}
