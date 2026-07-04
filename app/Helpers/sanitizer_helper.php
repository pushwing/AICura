<?php

/**
 * HTML 새니타이저 헬퍼 (이슈 #187)
 *
 * 뷰·컨트롤러에서 사용자 작성 리치 HTML 을 출력 전에 정화한다.
 * `esc()` 가 일반 텍스트용이라면, `clean_html()` 은 태그를 살려야 하는
 * 리치 HTML 전용이다. 허용 태그·속성만 남기고 위험 요소를 제거한다.
 */

use App\Libraries\Html\HtmlSanitizer;

if (! function_exists('clean_html')) {
    /**
     * 화이트리스트 기반으로 HTML 을 정화해 반환한다.
     *
     * @param string            $html        정화 대상 HTML
     * @param list<string>|null $allowedTags 허용 태그(소문자). null 이면 기본 허용 목록 사용.
     */
    function clean_html(string $html, ?array $allowedTags = null): string
    {
        /** @var HtmlSanitizer $sanitizer */
        $sanitizer = service('htmlSanitizer');

        return $sanitizer->sanitize($html, $allowedTags);
    }
}
