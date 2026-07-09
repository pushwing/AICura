<?php

use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;

/**
 * Bing Webmaster Tools 사이트 인증 파일 피처 테스트 (이슈 #160)
 *
 * @internal
 */
final class BingSiteVerificationFeatureTest extends CIUnitTestCase
{
    use FeatureTestTrait;

    protected $namespace;

    protected function tearDown(): void
    {
        parent::tearDown();
        putenv('BING_SITE_VERIFICATION');
        unset($_ENV['BING_SITE_VERIFICATION'], $_SERVER['BING_SITE_VERIFICATION']);
    }

    /**
     * 인증 코드 미설정이면 /BingSiteAuth.xml 404
     */
    public function testEndpoint404WhenUnset(): void
    {
        $this->expectException(PageNotFoundException::class);
        $this->get('BingSiteAuth.xml');
    }

    /**
     * 인증 코드 설정 시 Bing 인증 XML 반환
     */
    public function testEndpointServesVerificationXml(): void
    {
        putenv('BING_SITE_VERIFICATION=abc123code');
        $_ENV['BING_SITE_VERIFICATION'] = 'abc123code';

        $result = $this->get('BingSiteAuth.xml');
        $result->assertStatus(200);
        $result->assertHeader('Content-Type', 'application/xml; charset=UTF-8');
        $body = (string) $result->response()->getBody();
        $this->assertStringContainsString('<user>abc123code</user>', $body);
    }
}
