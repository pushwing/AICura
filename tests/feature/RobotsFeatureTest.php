<?php

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;

/**
 * robots.txt 동적 생성 피처 테스트 (이슈 #143·#160)
 *
 * @internal
 */
final class RobotsFeatureTest extends CIUnitTestCase
{
    use FeatureTestTrait;

    protected $namespace = null;

    public function testRobotsListsAiAndSearchCrawlers(): void
    {
        $result = $this->get('robots.txt');
        $result->assertStatus(200);
        $result->assertHeader('Content-Type', 'text/plain; charset=UTF-8');

        $body = (string) $result->response()->getBody();
        $this->assertStringContainsString('User-agent: *', $body);
        $this->assertStringContainsString('Disallow: /admin/', $body);
        $this->assertStringContainsString('User-agent: GPTBot', $body);
        $this->assertStringContainsString('User-agent: ClaudeBot', $body);
        $this->assertStringContainsString('User-agent: Bingbot', $body);
        $this->assertStringContainsString('Sitemap: ', $body);
    }
}
