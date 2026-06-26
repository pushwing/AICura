<?php

namespace Tests\Unit;

use App\Libraries\Ai\AiClientInterface;
use App\Services\AiCopyService;
use CodeIgniter\Cache\CacheInterface;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * AiCopyService 단위 테스트 (이슈 #73)
 *
 * 제목 후보 정규화·HTML 화이트리스트 필터·캐시 단락을 검증한다.
 * 외부 의존성(AI·캐시)은 Fake/Mock으로 대체한다.
 *
 * @internal
 */
final class AiCopyServiceTest extends CIUnitTestCase
{
    public function testNormalizeLimitsTitlesToThreeAndDropsEmpty(): void
    {
        $service = new AiCopyService($this->fakeAi([]), $this->createMock(CacheInterface::class));

        $result = $service->normalize([
            'titles' => ['A', '', 'B', 'C', 'D'],
            'detail' => '<p>본문</p>',
        ]);

        $this->assertSame(['A', 'B', 'C'], $result['titles']);
        $this->assertSame('<p>본문</p>', $result['detail']);
    }

    public function testNormalizeStripsDisallowedTagsAndAttributes(): void
    {
        $service = new AiCopyService($this->fakeAi([]), $this->createMock(CacheInterface::class));

        $result = $service->normalize([
            'titles' => [],
            'detail' => '<p onclick="x()">안녕</p><script>alert(1)</script><strong class="a">강조</strong>',
        ]);

        $this->assertStringNotContainsString('<script', $result['detail']);
        $this->assertStringNotContainsString('onclick', $result['detail']);
        $this->assertStringNotContainsString('class=', $result['detail']);
        $this->assertStringContainsString('<p>안녕</p>', $result['detail']);
        $this->assertStringContainsString('<strong>강조</strong>', $result['detail']);
    }

    public function testSuggestCallsAiOnCacheMissAndSaves(): void
    {
        $cache = $this->createMock(CacheInterface::class);
        $cache->method('get')->willReturn(null);
        $cache->expects($this->once())->method('save');

        $ai = $this->fakeAi(['titles' => ['제목1', '제목2', '제목3'], 'detail' => '<p>상세</p>']);

        $service = new AiCopyService($ai, $cache);
        $result  = $service->suggest(['keyword' => '쌍꺼풀', 'hospital_type' => '일반', 'category' => '눈성형']);

        $this->assertCount(3, $result['titles']);
        $this->assertSame('<p>상세</p>', $result['detail']);
    }

    public function testSuggestReturnsCachedWithoutCallingAi(): void
    {
        $cached = ['titles' => ['캐시제목'], 'detail' => '<p>캐시</p>'];

        $cache = $this->createMock(CacheInterface::class);
        $cache->method('get')->willReturn($cached);
        $cache->expects($this->never())->method('save');

        // AI를 호출하면 실패하는 Fake — 캐시 히트 시 호출되지 않아야 한다
        $ai = new class implements AiClientInterface {
            public function isConfigured(): bool { return true; }
            public function complete(string $s, string $u): string { return ''; }
            public function completeJson(string $s, string $u): array { throw new \RuntimeException('AI를 호출하면 안 됩니다'); }
        };

        $service = new AiCopyService($ai, $cache);
        $result  = $service->suggest(['keyword' => '쌍꺼풀']);

        $this->assertSame($cached, $result);
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
