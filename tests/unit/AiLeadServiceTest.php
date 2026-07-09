<?php

namespace Tests\Unit;

use App\Libraries\Ai\AiClientInterface;
use App\Models\CallMemoModel;
use App\Models\CallRequestModel;
use App\Services\AiLeadService;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * AiLeadService 단위 테스트 (이슈 #72)
 *
 * AI 원시 응답의 방어 정규화(점수 클램프·문자열 절단·공백 정리)를 검증한다.
 * 외부 의존성(AI·모델)은 Fake/Mock으로 대체한다.
 *
 * @internal
 */
final class AiLeadServiceTest extends CIUnitTestCase
{
    public function testNormalizeClampsScoreIntoRange(): void
    {
        $service = $this->service();

        $this->assertSame(100, $service->normalize(['score' => 250])['score']);
        $this->assertSame(0, $service->normalize(['score' => -10])['score']);
        $this->assertSame(73, $service->normalize(['score' => 73])['score']);
    }

    public function testNormalizeDefaultsMissingFields(): void
    {
        $result = $this->service()->normalize([]);

        $this->assertSame(0, $result['score']);
        $this->assertSame('', $result['summary']);
        $this->assertSame('', $result['next_action']);
    }

    public function testNormalizeCollapsesWhitespaceAndTruncates(): void
    {
        $long = str_repeat('가', 300);

        $result = $this->service()->normalize([
            'score'       => 50,
            'summary'     => "여러   줄\n요약\t정리",
            'next_action' => $long,
        ]);

        $this->assertSame('여러 줄 요약 정리', $result['summary']);
        $this->assertSame(255, mb_strlen($result['next_action']));
    }

    public function testNormalizeCastsNonStringSafely(): void
    {
        $result = $this->service()->normalize([
            'score'       => '88',
            'summary'     => ['배열은무시'],
            'next_action' => null,
        ]);

        $this->assertSame(88, $result['score']);
        $this->assertSame('', $result['summary']);
        $this->assertSame('', $result['next_action']);
    }

    private function service(): AiLeadService
    {
        $ai = new class () implements AiClientInterface {
            public function isConfigured(): bool
            {
                return true;
            }

            public function complete(string $systemPrompt, string $userPrompt): string
            {
                return '';
            }

            /**
             * @return array<string, mixed>
             */
            public function completeJson(string $systemPrompt, string $userPrompt): array
            {
                return [];
            }
        };

        return new AiLeadService(
            $ai,
            $this->createMock(CallRequestModel::class),
            $this->createMock(CallMemoModel::class),
        );
    }
}
