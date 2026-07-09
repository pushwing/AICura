<?php

namespace App\Libraries\Html;

use DOMComment;
use DOMDocument;
use DOMElement;
use DOMNode;
use DOMText;

/**
 * 화이트리스트 기반 HTML 새니타이저 (이슈 #187)
 *
 * Tiptap 등 리치 에디터가 생성한 사용자 작성 HTML 을 공개 페이지에 출력하기 전에 정화한다.
 * `strip_tags` 는 허용 태그의 "속성"을 제거하지 못해 `<img onerror=...>` ·
 * `<a href="javascript:...">` 같은 XSS 가 통과한다. 본 클래스는 DOMDocument 로 파싱해
 * 허용 태그·허용 속성만 남기고, href/src 는 안전한 스킴만 통과시킨다.
 *
 * 정책:
 *   - 허용 목록에 없는 태그: 위험 태그(script 등)는 내용째 제거, 그 외는 언랩(자식·텍스트는 보존)
 *   - 허용 태그의 속성: 태그별 허용 속성만 유지, on* 이벤트 핸들러·style 등은 제거
 *   - href/src: http·https·mailto·tel·상대경로·앵커(#)만 허용 (javascript:·data: 등 차단)
 *   - target="_blank" 링크: rel="noopener noreferrer" 강제(탭내빙 방지)
 */
class HtmlSanitizer
{
    /**
     * 기본 허용 태그 (호출부에서 재정의 가능)
     */
    public const array DEFAULT_TAGS = [
        'p', 'br', 'strong', 'em', 'b', 'i', 's', 'u',
        'ul', 'ol', 'li', 'h2', 'h3', 'h4', 'blockquote', 'a', 'img',
    ];

    /**
     * 내용까지 통째로 제거할 위험 태그 (언랩 대상이 아님)
     */
    private const array DANGEROUS_TAGS = [
        'script', 'style', 'iframe', 'object', 'embed', 'form', 'input',
        'button', 'textarea', 'select', 'option', 'link', 'meta', 'base',
        'svg', 'math', 'template', 'noscript', 'title', 'head',
    ];

    /**
     * 태그별 허용 속성 — 여기에 없는 태그의 속성은 모두 제거
     */
    private const array ATTRIBUTE_POLICY = [
        'a'   => ['href', 'title', 'target', 'rel'],
        'img' => ['src', 'alt', 'title', 'width', 'height'],
    ];

    /**
     * href/src 에 허용하는 URL 스킴
     */
    private const array SAFE_SCHEMES = ['http', 'https', 'mailto', 'tel'];

    /**
     * HTML 정화.
     *
     * @param string            $html        정화 대상 HTML
     * @param list<string>|null $allowedTags 허용 태그 목록(소문자). null 이면 DEFAULT_TAGS 사용.
     */
    public function sanitize(string $html, ?array $allowedTags = null): string
    {
        if (trim($html) === '') {
            return '';
        }

        $allowed = array_map('strtolower', $allowedTags ?? self::DEFAULT_TAGS);

        $dom = new DOMDocument();

        // UTF-8 보존을 위해 인코딩 선언을 앞에 붙이고, 파서 경고는 억제한다.
        $prev = libxml_use_internal_errors(true);
        $dom->loadHTML(
            '<?xml encoding="utf-8"?><html><body>' . $html . '</body></html>',
            LIBXML_HTML_NODEFDTD | LIBXML_NONET,
        );
        libxml_clear_errors();
        libxml_use_internal_errors($prev);

        $body = $dom->getElementsByTagName('body')->item(0);
        if (! $body instanceof DOMElement) {
            return '';
        }

        $this->cleanChildren($body, $allowed);

        $out = '';

        foreach (iterator_to_array($body->childNodes) as $child) {
            $out .= (string) $dom->saveHTML($child);
        }

        return trim($out);
    }

    /**
     * 자식 노드들을 정책에 따라 정화한다(재귀).
     *
     * @param list<string> $allowed
     */
    private function cleanChildren(DOMNode $node, array $allowed): void
    {
        // 순회 중 노드를 제거·이동하므로 스냅샷을 떠서 반복한다.
        foreach (iterator_to_array($node->childNodes) as $child) {
            if ($child instanceof DOMText) {
                continue; // 텍스트는 안전(직렬화 시 이스케이프됨)
            }

            if ($child instanceof DOMComment || ! $child instanceof DOMElement) {
                $node->removeChild($child); // 주석·PI 등 제거

                continue;
            }

            $tag = strtolower($child->nodeName);

            if (in_array($tag, self::DANGEROUS_TAGS, true)) {
                $node->removeChild($child); // 서브트리째 제거

                continue;
            }

            if (! in_array($tag, $allowed, true)) {
                // 허용되지 않은 일반 태그: 자식을 정화한 뒤 언랩(태그만 벗기고 내용 보존)
                $this->cleanChildren($child, $allowed);
                $this->unwrap($child);

                continue;
            }

            $this->cleanAttributes($child, $tag);
            $this->cleanChildren($child, $allowed);
        }
    }

    /**
     * 허용 속성만 남기고 나머지 제거, href/src 스킴 검증.
     */
    private function cleanAttributes(DOMElement $el, string $tag): void
    {
        $allowedAttrs = self::ATTRIBUTE_POLICY[$tag] ?? [];

        foreach (iterator_to_array($el->attributes) as $attr) {
            $name = strtolower($attr->nodeName);

            if (! in_array($name, $allowedAttrs, true)) {
                $el->removeAttribute($attr->nodeName);

                continue;
            }

            if (($name === 'href' || $name === 'src') && ! $this->isSafeUrl((string) $attr->nodeValue)) {
                $el->removeAttribute($attr->nodeName);
            }
        }

        // 새 탭 링크는 탭내빙(reverse tabnabbing) 방지를 위해 rel 을 강제한다.
        if ($tag === 'a' && strtolower($el->getAttribute('target')) === '_blank') {
            $el->setAttribute('rel', 'noopener noreferrer');
        }
    }

    /**
     * 노드를 제거하되 자식들은 부모로 끌어올려 보존한다.
     */
    private function unwrap(DOMElement $el): void
    {
        $parent = $el->parentNode;
        if ($parent === null) {
            return;
        }

        while ($el->firstChild !== null) {
            $parent->insertBefore($el->firstChild, $el);
        }

        $parent->removeChild($el);
    }

    /**
     * href/src URL 이 안전한지 검사.
     * 스킴이 있으면 화이트리스트만 허용하고, 상대경로·앵커는 허용한다.
     * 스킴 위장(예: "java\tscript:")을 막기 위해 제어문자를 제거한 뒤 판정한다.
     */
    private function isSafeUrl(string $url): bool
    {
        $normalized = (string) preg_replace('/[\x00-\x20]+/', '', $url);

        if ($normalized === '') {
            return false;
        }

        // 앵커·루트 상대경로·프로토콜 상대(//host)는 상대 URL 로 간주해 허용
        if (str_starts_with($normalized, '#') || str_starts_with($normalized, '/')) {
            return true;
        }

        // 스킴이 명시된 경우: 화이트리스트만 허용
        if (preg_match('#^([a-z][a-z0-9+.\-]*):#i', $normalized, $m) === 1) {
            return in_array(strtolower($m[1]), self::SAFE_SCHEMES, true);
        }

        // 스킴 없음 → 상대경로(예: foo/bar, ?q=1)로 간주해 허용
        return true;
    }
}
