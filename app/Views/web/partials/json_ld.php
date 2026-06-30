<?php

use App\Libraries\Seo\JsonLdBuilder;

/**
 * JSON-LD 구조화 데이터 주입 파셜 (이슈 #145)
 *
 * 컨트롤러가 전달한 schema 배열 목록($jsonLd)을 `<script type="application/ld+json">` 로 출력한다.
 * JsonLdBuilder::render() 는 비ASCII를 \uXXXX 로 인코딩하므로 전역 출력 핸들러(멀티바이트→HTML 엔티티)에
 * 영향받지 않고 유효한 JSON-LD 를 유지한다. 값이 없으면 아무것도 출력하지 않는다(목록 페이지 등).
 *
 * @var array<int, array<string, mixed>> $jsonLd
 */
echo JsonLdBuilder::render($jsonLd ?? []);
