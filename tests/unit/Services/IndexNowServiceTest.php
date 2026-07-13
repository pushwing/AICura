<?php

use App\Services\IndexNowService;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * IndexNow 제출 서비스 단위 테스트 (이슈 #152)
 *
 * @internal
 */
final class IndexNowServiceTest extends CIUnitTestCase
{
    protected function tearDown(): void
    {
        parent::tearDown();
        putenv('INDEXNOW_KEY');
        unset($_ENV['INDEXNOW_KEY'], $_SERVER['INDEXNOW_KEY']);
    }

    /**
     * 키 미설정이면 비활성 → 제출하지 않음
     */
    public function testDisabledWhenKeyMissing(): void
    {
        $service = new IndexNowService();

        $this->assertFalse($service->isEnabled());
        $this->assertFalse($service->submit('https://x.test/a'));
    }

    /**
     * 로컬/테스트 호스트(example.com)면 키가 있어도 비활성
     */
    public function testDisabledOnLocalHost(): void
    {
        putenv('INDEXNOW_KEY=abc123');
        $_ENV['INDEXNOW_KEY'] = 'abc123';

        $service = new IndexNowService();
        // 테스트 base_url 은 example.com → 로컬로 간주되어 제출 안 함
        $this->assertFalse($service->isEnabled());
    }

    /**
     * 페이로드 구조 — host·key·keyLocation·urlList
     */
    public function testPayloadShape(): void
    {
        putenv('INDEXNOW_KEY=mykey');
        $_ENV['INDEXNOW_KEY'] = 'mykey';

        $service = new IndexNowService();
        $payload = $service->payload(['https://x.test/a', 'https://x.test/b']);

        $this->assertSame('mykey', $payload['key']);
        $this->assertArrayHasKey('host', $payload);
        $this->assertStringContainsString('indexnow-key.txt', (string) $payload['keyLocation']);
        $this->assertSame(['https://x.test/a', 'https://x.test/b'], $payload['urlList']);
    }

    /**
     * 빈 URL 은 제출하지 않음
     */
    public function testSubmitEmptyUrlsReturnsFalse(): void
    {
        putenv('INDEXNOW_KEY=mykey');
        $_ENV['INDEXNOW_KEY'] = 'mykey';

        $this->assertFalse((new IndexNowService())->submit('', '   '));
    }
}
