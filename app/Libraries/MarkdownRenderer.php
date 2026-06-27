<?php

namespace App\Libraries;

use League\CommonMark\GithubFlavoredMarkdownConverter;

/**
 * 마크다운 → 안전 HTML 렌더러 (이슈 #65)
 *
 * AI 보고서 본문(마크다운)을 서버에서 GFM(표 포함) HTML로 변환한다.
 * 외부 CDN(marked/DOMPurify) 의존을 제거해 네트워크 환경과 무관하게 렌더된다.
 *
 * 보안: html_input=escape 로 본문 내 원시 HTML을 이스케이프하고
 *       allow_unsafe_links=false 로 위험 링크 스킴을 차단한다(XSS 방어).
 */
class MarkdownRenderer
{
    private GithubFlavoredMarkdownConverter $converter;

    public function __construct()
    {
        $this->converter = new GithubFlavoredMarkdownConverter([
            'html_input'         => 'escape',
            'allow_unsafe_links' => false,
        ]);
    }

    public function toSafeHtml(string $markdown): string
    {
        return $this->converter->convert($markdown)->getContent();
    }
}
