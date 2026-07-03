<?php

use App\Libraries\Html\HtmlSanitizer;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * 화이트리스트 HTML 새니타이저 단위 테스트 (이슈 #187 저장형 XSS)
 *
 * 핵심: strip_tags 가 놓치던 "허용 태그의 위험 속성"을 제거하는지 검증한다.
 *
 * @internal
 */
final class HtmlSanitizerTest extends CIUnitTestCase
{
    private HtmlSanitizer $sanitizer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->sanitizer = new HtmlSanitizer();
    }

    /** img 의 onerror 이벤트 핸들러 제거 — 대표적 XSS 벡터 */
    public function testStripsImgOnerror(): void
    {
        $out = $this->sanitizer->sanitize('<img src="x" onerror="alert(document.cookie)">', ['img']);

        $this->assertStringNotContainsString('onerror', $out);
        $this->assertStringNotContainsString('alert', $out);
        $this->assertStringContainsString('<img', $out);
    }

    /** a href 의 javascript: 스킴 제거 */
    public function testStripsJavascriptHref(): void
    {
        $out = $this->sanitizer->sanitize('<a href="javascript:alert(1)">클릭</a>', ['a']);

        $this->assertStringNotContainsString('javascript', $out);
        $this->assertStringContainsString('클릭', $out);
    }

    /** 제어문자로 위장한 스킴(java\tscript:)도 차단 */
    public function testStripsObfuscatedJavascriptScheme(): void
    {
        $out = $this->sanitizer->sanitize("<a href=\"java\tscript:alert(1)\">x</a>", ['a']);

        $this->assertStringNotContainsString('script:', strtolower($out));
    }

    /** data: URL 도 차단 (스킴 화이트리스트에 없음) */
    public function testStripsDataUrl(): void
    {
        $out = $this->sanitizer->sanitize('<a href="data:text/html;base64,PHNjcmlwdD4=">x</a>', ['a']);

        $this->assertStringNotContainsString('data:', $out);
    }

    /** script 태그는 내용째 제거 */
    public function testRemovesScriptTagAndContent(): void
    {
        $out = $this->sanitizer->sanitize('<p>안녕</p><script>alert(1)</script>', ['p']);

        $this->assertStringNotContainsString('alert', $out);
        $this->assertStringNotContainsString('<script', $out);
        $this->assertStringContainsString('안녕', $out);
    }

    /** 허용 안 된 태그(div)는 언랩 — 내부 텍스트는 보존 */
    public function testUnwrapsDisallowedTagKeepingText(): void
    {
        $out = $this->sanitizer->sanitize('<div><p>본문</p></div>', ['p']);

        $this->assertStringNotContainsString('<div', $out);
        $this->assertStringContainsString('<p>본문</p>', $out);
    }

    /** style 속성 제거 (CSS 기반 공격 차단) */
    public function testStripsStyleAttribute(): void
    {
        $out = $this->sanitizer->sanitize('<p style="background:url(javascript:alert(1))">x</p>', ['p']);

        $this->assertStringNotContainsString('style', $out);
        $this->assertStringContainsString('x', $out);
    }

    /** 안전한 태그·속성·http 링크는 보존 */
    public function testKeepsSafeContent(): void
    {
        $html = '<p>안녕 <strong>세계</strong> <a href="https://a.test/page" title="링크">이동</a></p>';
        $out  = $this->sanitizer->sanitize($html, ['p', 'strong', 'a']);

        $this->assertStringContainsString('<strong>세계</strong>', $out);
        $this->assertStringContainsString('href="https://a.test/page"', $out);
        $this->assertStringContainsString('title="링크"', $out);
    }

    /** 상대경로·앵커 링크는 허용 */
    public function testKeepsRelativeAndAnchorHref(): void
    {
        $out = $this->sanitizer->sanitize('<a href="/events/1">a</a><a href="#top">b</a>', ['a']);

        $this->assertStringContainsString('href="/events/1"', $out);
        $this->assertStringContainsString('href="#top"', $out);
    }

    /** target=_blank 링크에 rel=noopener 강제 (탭내빙 방지) */
    public function testForcesRelOnBlankTarget(): void
    {
        $out = $this->sanitizer->sanitize('<a href="https://a.test" target="_blank">x</a>', ['a']);

        $this->assertStringContainsString('rel="noopener noreferrer"', $out);
    }

    /** 한글·이모지 등 멀티바이트 텍스트 보존 */
    public function testPreservesMultibyteText(): void
    {
        $out = $this->sanitizer->sanitize('<p>성형외과 이벤트 🎉 안내</p>', ['p']);

        $this->assertStringContainsString('성형외과 이벤트 🎉 안내', $out);
    }

    /** 빈 입력 → 빈 문자열 */
    public function testEmptyInput(): void
    {
        $this->assertSame('', $this->sanitizer->sanitize('   ', ['p']));
    }

    /** svg 내부 스크립트 벡터 제거 */
    public function testRemovesSvg(): void
    {
        $out = $this->sanitizer->sanitize('<svg><script>alert(1)</script></svg><p>ok</p>', ['p']);

        $this->assertStringNotContainsString('svg', $out);
        $this->assertStringNotContainsString('alert', $out);
        $this->assertStringContainsString('ok', $out);
    }

    /** iframe 제거 */
    public function testRemovesIframe(): void
    {
        $out = $this->sanitizer->sanitize('<p>a</p><iframe src="https://evil.test"></iframe>', ['p', 'iframe']);

        // iframe 은 위험 태그이므로 허용 목록에 넣어도 제거된다.
        $this->assertStringNotContainsString('iframe', $out);
        $this->assertStringContainsString('a', $out);
    }
}
