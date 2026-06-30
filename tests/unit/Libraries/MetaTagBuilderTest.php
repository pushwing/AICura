<?php

use App\Libraries\Seo\MetaTagBuilder;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * SEO 메타태그 빌더 단위 테스트 (이슈 #143)
 *
 * @internal
 */
final class MetaTagBuilderTest extends CIUnitTestCase
{
    /** 기본 메타 — title·description·robots·canonical·OG·Twitter 출력 */
    public function testRendersCoreTags(): void
    {
        $html = MetaTagBuilder::fromArray([
            'title'       => 'AICura Events',
            'description' => 'Plastic surgery events',
            'canonical'   => 'https://aicura.test/events',
            'robots'      => 'index, follow',
            'og_type'     => 'website',
        ])->render();

        $this->assertStringContainsString('<title>AICura Events</title>', $html);
        $this->assertStringContainsString('<meta name="description" content="Plastic surgery events">', $html);
        $this->assertStringContainsString('<meta name="robots" content="index, follow">', $html);
        $this->assertStringContainsString('<link rel="canonical" href="https://aicura.test/events">', $html);
        $this->assertStringContainsString('<meta property="og:type" content="website">', $html);
        $this->assertStringContainsString('<meta property="og:title" content="AICura Events">', $html);
        $this->assertStringContainsString('<meta property="og:url" content="https://aicura.test/events">', $html);
    }

    /** og:image 없으면 image 태그 없음 + twitter:card=summary */
    public function testWithoutImageUsesSummaryCard(): void
    {
        $html = MetaTagBuilder::fromArray([
            'title'       => 'No Image',
            'description' => 'desc',
            'canonical'   => 'https://aicura.test/x',
        ])->render();

        $this->assertStringContainsString('<meta name="twitter:card" content="summary">', $html);
        $this->assertStringNotContainsString('og:image', $html);
        $this->assertStringNotContainsString('twitter:image', $html);
    }

    /** og:image 있으면 image 태그 + twitter:card=summary_large_image */
    public function testWithImageUsesLargeCard(): void
    {
        $html = MetaTagBuilder::fromArray([
            'title'       => 'With Image',
            'description' => 'desc',
            'canonical'   => 'https://aicura.test/y',
            'og_image'    => 'https://aicura.test/img.jpg',
        ])->render();

        $this->assertStringContainsString('<meta name="twitter:card" content="summary_large_image">', $html);
        $this->assertStringContainsString('<meta property="og:image" content="https://aicura.test/img.jpg">', $html);
        $this->assertStringContainsString('<meta name="twitter:image" content="https://aicura.test/img.jpg">', $html);
    }

    /** 누락 키는 안전한 기본값으로 보정 */
    public function testMissingKeysFallBackToDefaults(): void
    {
        $html = MetaTagBuilder::fromArray([])->render();

        $this->assertStringContainsString('<title>AICura</title>', $html);
        $this->assertStringContainsString('<meta name="robots" content="index, follow">', $html);
        $this->assertStringContainsString('<meta property="og:type" content="website">', $html);
    }
}
