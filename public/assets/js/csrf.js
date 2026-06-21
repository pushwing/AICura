/**
 * CSRF 토큰 헬퍼 — 어드민/포털 공통.
 *
 * 토큰과 헤더 이름은 레이아웃 head 의 메타 태그에서 읽는다.
 * (document.cookie 직접 파싱을 대체 — CSRF 쿠키가 HttpOnly 여도 동작)
 *
 *   <meta name="csrf-token"  content="<?= csrf_hash() ?>">
 *   <meta name="csrf-header" content="<?= csrf_header() ?>">
 */
(function () {
    'use strict';

    function metaContent(name, fallback) {
        return document.querySelector('meta[name="' + name + '"]')?.content ?? fallback;
    }

    /** 현재 페이지의 CSRF 토큰 값. */
    window.getCsrfToken = function () {
        return metaContent('csrf-token', '');
    };

    /** CSRF 헤더 이름 (기본 X-CSRF-TOKEN). */
    window.getCsrfHeaderName = function () {
        return metaContent('csrf-header', 'X-CSRF-TOKEN');
    };

    /** fetch headers 에 펼쳐 쓰는 { [헤더명]: 토큰 } 객체. */
    window.csrfHeaders = function () {
        return { [window.getCsrfHeaderName()]: window.getCsrfToken() };
    };
})();
