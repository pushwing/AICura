<?php

use App\Services\SitemapService;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;

/**
 * sitemap.xml · robots.txt 동적 서빙 피처 테스트 (이슈 #143)
 *
 * 커버리지:
 *   [S1] sitemap.xml — 200·XML, urlset·이벤트 인덱스·노출 이벤트 상세 URL 포함
 *   [S2] sitemap.xml — 비노출(검수 미완료 등) 이벤트 URL 미포함
 *   [R1] robots.txt — 200·plain, 색인 허용·내부경로 차단·AI 크롤러·절대 Sitemap URL
 *
 * @internal
 */
final class SeoEndpointsFeatureTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use FeatureTestTrait;

    protected $migrate   = true;
    protected $refresh   = true;
    protected $namespace = null;

    private int $hospitalId = 0;
    private int $visibleId  = 0;

    protected function setUp(): void
    {
        parent::setUp();
        cache()->clean(); // sitemap 캐시 격리

        $db  = db_connect();
        $now = date('Y-m-d H:i:s');

        $db->table('hospitals')->insert([
            'name' => '강남병원', 'type' => 1, 'status' => 'active', 'is_deleted' => 0,
            'address' => '서울', 'phone' => '02-1', 'created_at' => $now, 'updated_at' => $now,
        ]);
        $this->hospitalId = (int) $db->insertID();

        $this->visibleId = $this->insertCampaign(['ad_title' => '노출이벤트', 'exposure' => 1]);
        // 비노출: 검수 미완료
        $this->insertCampaign(['ad_title' => '검수미완료', 'exposure' => 1, 'review_status' => 'pending']);
    }

    /** @param array<string, mixed> $overrides */
    private function insertCampaign(array $overrides): int
    {
        $db  = db_connect();
        $now = date('Y-m-d H:i:s');
        $db->table('campaigns')->insert(array_merge([
            'ad_title' => '이벤트', 'hospital_id' => $this->hospitalId, 'status' => 'active',
            'review_status' => 'approved', 'exposure' => 1, 'is_deleted' => 0,
            'category' => 0, 'ad_type' => 1, 'cost_type' => 1,
            'created_at' => $now, 'updated_at' => $now,
        ], $overrides));

        return (int) $db->insertID();
    }

    /** [S1] sitemap.xml — 200·XML, 이벤트 URL 포함 */
    public function testSitemapServesXmlWithEvents(): void
    {
        $result = $this->get('sitemap.xml');

        $result->assertStatus(200);
        $result->assertHeader('Content-Type', 'application/xml; charset=UTF-8');

        $body = $result->getBody();
        $this->assertStringContainsString('<urlset', $body);
        $this->assertStringContainsString('/events</loc>', $body);
        $this->assertStringContainsString('/events/' . $this->visibleId . '</loc>', $body);
        $this->assertStringContainsString('<lastmod>', $body);
    }

    /** [S2] sitemap.xml — 검수 미완료 이벤트 미포함 */
    public function testSitemapExcludesHiddenEvents(): void
    {
        $body = $this->get('sitemap.xml')->getBody();

        // 노출 이벤트 1건만 상세 URL 로 포함, 검수 미완료 이벤트는 제외
        $this->assertStringContainsString('/events/' . $this->visibleId . '</loc>', $body);
        $this->assertSame(1, substr_count($body, '/events/')); // 이벤트 상세는 1건뿐
    }

    /** [S3] sitemap 결과는 캐시된다 */
    public function testSitemapIsCached(): void
    {
        $this->get('sitemap.xml');
        $this->assertIsString(cache(SitemapService::CACHE_KEY));
    }

    /** [R1] robots.txt — 색인 허용·내부 차단·AI 크롤러·절대 Sitemap URL */
    public function testRobotsServesPolicy(): void
    {
        $result = $this->get('robots.txt');

        $result->assertStatus(200);
        $result->assertHeader('Content-Type', 'text/plain; charset=UTF-8');

        $body = $result->getBody();
        $this->assertStringContainsString('User-agent: *', $body);
        $this->assertStringContainsString('Disallow: /admin/', $body);
        $this->assertStringContainsString('User-agent: GPTBot', $body);
        $this->assertStringContainsString('User-agent: PerplexityBot', $body);
        // 절대 URL (base_url 기준) 로 sitemap 제시
        $this->assertMatchesRegularExpression('#Sitemap: https?://[^/]+/.*sitemap\.xml#', $body);
    }
}
