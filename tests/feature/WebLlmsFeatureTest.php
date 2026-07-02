<?php

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;

/**
 * llms.txt 동적 서빙 피처 테스트 (이슈 #147)
 *
 * 주의: TestResponse::getBody() 는 DOMParser 를 거쳐 한글이 HTML 엔티티로 인코딩되므로,
 * 원문(raw UTF-8) 검증은 response()->getBody() 로 실제 응답 본문을 직접 읽는다(운영 출력과 동일).
 *
 * @internal
 */
final class WebLlmsFeatureTest extends CIUnitTestCase
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

        $db->table('event_categories')->insert(['title' => '눈성형', 'is_visible' => 1, 'sort' => 1, 'category_type' => 0]);

        $db->table('guides')->insert([
            'title' => '쌍꺼풀 수술 가이드', 'slug' => 'double-eyelid', 'summary' => '쌍꺼풀 정보 요약',
            'status' => 'published', 'published_at' => $now, 'created_at' => $now, 'updated_at' => $now,
        ]);
        $db->table('guides')->insert([
            'title' => '임시 가이드', 'slug' => 'draft-guide', 'status' => 'draft',
            'created_at' => $now, 'updated_at' => $now,
        ]);
    }

    /** 실제 응답 본문(raw UTF-8) */
    private function rawBody(string $uri): string
    {
        return (string) $this->get($uri)->response()->getBody();
    }

    /** 200 · text/plain · 마크다운 헤더·주요 페이지 링크 */
    public function testServesMarkdownWithMainPages(): void
    {
        $result = $this->get('llms.txt');
        $result->assertStatus(200);
        $result->assertHeader('Content-Type', 'text/plain; charset=UTF-8');

        $body = $this->rawBody('llms.txt');
        $this->assertStringContainsString('# AICura', $body);
        $this->assertStringContainsString('## 주요 페이지', $body);
        $this->assertStringContainsString('/events)', $body);
        $this->assertStringContainsString('/hospitals)', $body);
        $this->assertStringContainsString('/reviews)', $body);
        $this->assertStringContainsString('/guides)', $body);
        $this->assertStringContainsString('/sitemap.xml)', $body);
    }

    /** 발행 가이드는 나열, 임시 가이드는 제외 */
    public function testListsPublishedGuidesOnly(): void
    {
        $body = $this->rawBody('llms.txt');

        $this->assertStringContainsString('## 시술 가이드', $body);
        $this->assertStringContainsString('쌍꺼풀 수술 가이드', $body);
        $this->assertStringContainsString('/guides/double-eyelid)', $body);
        $this->assertStringNotContainsString('임시 가이드', $body);
    }

    /** 이벤트 카테고리 나열 */
    public function testListsEventCategories(): void
    {
        $body = $this->rawBody('llms.txt');

        $this->assertStringContainsString('## 이벤트 카테고리', $body);
        $this->assertStringContainsString('- 눈성형', $body);
    }

    /** 한글 원문이 raw UTF-8 로 출력된다(엔티티 아님) */
    public function testKoreanIsRawUtf8(): void
    {
        $body = $this->rawBody('llms.txt');

        $this->assertStringContainsString('성형', $body);
        $this->assertStringNotContainsString('&#', $body);
    }
}
