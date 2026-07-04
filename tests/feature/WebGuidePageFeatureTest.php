<?php

use App\Models\GuideModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;

/**
 * 공개 시술 가이드 SSR 페이지 피처 테스트 (이슈 #146)
 *
 * 커버리지:
 *   [G1] 목록 — 발행 가이드만 노출
 *   [G2] 상세 — 슬러그 200·본문·FAQ 렌더
 *   [G3] 상세 — Article(MedicalWebPage) + FAQPage JSON-LD
 *   [G4] 임시저장(draft)·미존재 슬러그 404
 *
 * @internal
 */
final class WebGuidePageFeatureTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use FeatureTestTrait;

    protected $migrate   = true;
    protected $refresh   = true;
    protected $namespace = null;

    protected function setUp(): void
    {
        parent::setUp();
        cache()->clean();

        $now = date('Y-m-d H:i:s');
        $db  = db_connect();

        $db->table('guides')->insert([
            'title' => '쌍꺼풀 수술 가이드', 'slug' => 'double-eyelid', 'summary' => '쌍꺼풀 정보 요약',
            // script + 속성 기반 XSS(javascript: 링크)를 함께 심어 정화를 검증한다.
            'content' => '<p>쌍꺼풀 본문<script>bad()</script></p>'
                . '<a href="javascript:alert(1)">위험링크</a>',
            'procedure_name' => '쌍꺼풀 수술',
            'faq_json' => json_encode([['q' => '비용은?', 'a' => '병원마다 다릅니다']], JSON_UNESCAPED_UNICODE),
            'status' => 'published', 'published_at' => $now, 'created_at' => $now, 'updated_at' => $now,
        ]);
        $db->table('guides')->insert([
            'title' => '임시 가이드', 'slug' => 'draft-guide', 'status' => 'draft',
            'created_at' => $now, 'updated_at' => $now,
        ]);
    }

    private function decode(string $body): string
    {
        return html_entity_decode($body, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    /**
     * @return array<int, array<string, mixed>> 본문의 모든 JSON-LD 블록을 파싱해 반환
     */
    private function jsonLdBlocks(string $body): array
    {
        preg_match_all('#<script type="application/ld\+json">(.*?)</script>#s', $body, $m);
        $out = [];
        foreach ($m[1] as $json) {
            $decoded = json_decode(trim($json), true);
            $this->assertIsArray($decoded, 'JSON-LD 파싱 실패');
            $out[] = $decoded;
        }

        return $out;
    }

    /** [G1] 목록 — 발행 가이드만 */
    public function testIndexShowsPublishedOnly(): void
    {
        $body = $this->decode($this->get('guides')->getBody());

        $this->assertStringContainsString('쌍꺼풀 수술 가이드', $body);
        $this->assertStringNotContainsString('임시 가이드', $body);
    }

    /** [G2] 상세 — 본문·FAQ 렌더, script 제거 */
    public function testShowRendersBodyAndFaq(): void
    {
        $result = $this->get('guides/double-eyelid');
        $result->assertStatus(200);
        $raw  = $result->getBody();
        $body = $this->decode($raw);

        $this->assertStringContainsString('쌍꺼풀 본문', $body);
        $this->assertStringContainsString('비용은?', $body);       // FAQ 질문
        $this->assertStringContainsString('병원마다 다릅니다', $body); // FAQ 답변
        $this->assertStringNotContainsString('<script>bad()', $raw); // 화이트리스트 제거
        // 이슈 #187: 허용 태그(a)의 javascript: 스킴은 제거되고 링크 텍스트는 보존된다.
        $this->assertStringNotContainsString('javascript:', $raw);
        $this->assertStringContainsString('위험링크', $body);
    }

    /** [G3] 상세 — Article(MedicalWebPage) + FAQPage JSON-LD */
    public function testShowHasArticleAndFaqJsonLd(): void
    {
        $blocks = $this->jsonLdBlocks($this->get('guides/double-eyelid')->getBody());

        $types = array_column($blocks, '@type');
        $this->assertContains('MedicalWebPage', $types);
        $this->assertContains('FAQPage', $types);

        $article = $blocks[array_search('MedicalWebPage', $types, true)];
        $this->assertSame('쌍꺼풀 수술 가이드', $article['name']);
        $this->assertSame('MedicalProcedure', $article['about']['@type']);
        $this->assertSame('쌍꺼풀 수술', $article['about']['name']);

        $faq = $blocks[array_search('FAQPage', $types, true)];
        $this->assertSame('Question', $faq['mainEntity'][0]['@type']);
        $this->assertSame('비용은?', $faq['mainEntity'][0]['name']);
    }

    /** [G4] draft·미존재 슬러그 404 */
    public function testDraftAndMissingReturn404(): void
    {
        $this->expectException(PageNotFoundException::class);
        $this->get('guides/draft-guide');
    }

    /** [G4b] 미존재 슬러그 404 */
    public function testMissingSlugReturns404(): void
    {
        $this->expectException(PageNotFoundException::class);
        $this->get('guides/nope');
    }

    /** STATUS 상수 노출 확인 (회귀 방지) */
    public function testStatusConstants(): void
    {
        $this->assertSame('published', GuideModel::STATUS_PUBLISHED);
        $this->assertSame('draft', GuideModel::STATUS_DRAFT);
    }
}
