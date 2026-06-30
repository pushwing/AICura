<?php

namespace App\Libraries\Seo;

/**
 * schema.org JSON-LD 빌더 (이슈 #145 — GEO 구조화 데이터)
 *
 * 공개 상세 페이지에 검색·생성형 엔진이 기계 판독 가능한 구조화 데이터를 주입한다.
 * 각 정적 메서드는 엔티티 배열(컨트롤러가 이미 조회한 뷰 데이터)을 schema.org 배열로 변환하고,
 * render() 가 `<script type="application/ld+json">` 블록(들)으로 직렬화한다.
 *
 * 보안: 인라인 JSON-LD 의 `</script>` 탈출을 막기 위해 JSON_HEX_TAG 로 `<`·`>` 를 이스케이프한다.
 */
final class JsonLdBuilder
{
    public const CONTEXT = 'https://schema.org';

    /** 평점 척도 */
    private const BEST_RATING  = 5;
    private const WORST_RATING = 1;

    /**
     * 이벤트(캠페인) → Offer + MedicalProcedure (§4.1)
     *
     * @param array<string, mixed> $event EventService::detail 결과
     * @return array<string, mixed>
     */
    public static function event(array $event, string $url): array
    {
        $price = (int) ($event['discount_cost'] ?? 0);
        if ($price <= 0) {
            $price = (int) ($event['general_cost'] ?? 0);
        }

        $schema = [
            '@context'      => self::CONTEXT,
            '@type'         => 'Offer',
            'name'          => (string) ($event['ad_title'] ?? ''),
            'url'           => $url,
            'priceCurrency' => 'KRW',
            'price'         => $price,
            'itemOffered'   => [
                '@type' => 'MedicalProcedure',
                'name'  => (string) ($event['category_title'] ?? $event['ad_title'] ?? ''),
            ],
        ];

        $desc = trim(strip_tags((string) ($event['ad_detail_info'] ?? '')));
        if ($desc !== '') {
            $schema['description'] = mb_substr($desc, 0, 300);
        }
        if (!empty($event['thumbnail_url'])) {
            $schema['image'] = (string) $event['thumbnail_url'];
        }
        if (!empty($event['ad_start_date'])) {
            $schema['availabilityStarts'] = (string) $event['ad_start_date'];
        }
        if (!empty($event['ad_end_date'])) {
            $schema['availabilityEnds'] = (string) $event['ad_end_date'];
        }
        if (!empty($event['region'])) {
            $schema['areaServed'] = (string) $event['region'];
        }
        if (!empty($event['hospital_name'])) {
            $seller = ['@type' => 'MedicalClinic', 'name' => (string) $event['hospital_name']];
            if (!empty($event['hospital_address'])) {
                $seller['address'] = (string) $event['hospital_address'];
            }
            if (!empty($event['hospital_phone'])) {
                $seller['telephone'] = (string) $event['hospital_phone'];
            }
            $schema['seller'] = $seller;
        }

        return $schema;
    }

    /**
     * 병원 → MedicalClinic + AggregateRating (§4.2)
     *
     * @param array<string, mixed> $hospital HospitalService::detail 결과
     * @return array<string, mixed>
     */
    public static function hospital(array $hospital, string $url): array
    {
        $schema = [
            '@context' => self::CONTEXT,
            '@type'    => 'MedicalClinic',
            'name'     => (string) ($hospital['name'] ?? ''),
            'url'      => $url,
        ];

        if (!empty($hospital['address'])) {
            $schema['address'] = [
                '@type'          => 'PostalAddress',
                'streetAddress'  => (string) $hospital['address'],
                'addressCountry' => 'KR',
            ];
        }
        if (!empty($hospital['phone'])) {
            $schema['telephone'] = (string) $hospital['phone'];
        }

        /** @var array<int, mixed> $departments */
        $departments = (array) ($hospital['departments'] ?? []);
        if ($departments !== []) {
            $schema['medicalSpecialty'] = array_values(array_map('strval', $departments));
        }

        /** @var array<string, mixed> $summary */
        $summary = (array) ($hospital['review_summary'] ?? []);
        $rating  = (float) ($summary['rating'] ?? 0);
        $count   = (int) ($summary['count'] ?? 0);
        if ($rating > 0 && $count > 0) {
            $schema['aggregateRating'] = [
                '@type'       => 'AggregateRating',
                'ratingValue' => $rating,
                'reviewCount' => $count,
                'bestRating'  => self::BEST_RATING,
                'worstRating' => self::WORST_RATING,
            ];
        }

        return $schema;
    }

    /**
     * 후기 → Review + Rating (§4.3)
     *
     * @param array<string, mixed> $review     BoardService::detail 결과 (author 는 마스킹된 값)
     * @param string|null          $targetName 후기 대상(병원/이벤트) 이름 — itemReviewed 용
     * @return array<string, mixed>
     */
    public static function review(array $review, string $url, ?string $targetName = null): array
    {
        $schema = [
            '@context' => self::CONTEXT,
            '@type'    => 'Review',
            'name'     => (string) ($review['subject'] ?? ''),
            'url'      => $url,
            'author'   => [
                '@type' => 'Person',
                'name'  => (string) ($review['author'] ?? '익명'),
            ],
        ];

        $body = trim(strip_tags((string) ($review['contents'] ?? '')));
        if ($body !== '') {
            $schema['reviewBody'] = mb_substr($body, 0, 500);
        }
        if (!empty($review['created_at'])) {
            $schema['datePublished'] = (string) $review['created_at'];
        }

        $rating = (float) ($review['rating'] ?? 0);
        if ($rating > 0) {
            $schema['reviewRating'] = [
                '@type'       => 'Rating',
                'ratingValue' => $rating,
                'bestRating'  => self::BEST_RATING,
                'worstRating' => self::WORST_RATING,
            ];
        }

        if ($targetName !== null && $targetName !== '') {
            // 병원(2) 후기는 MedicalClinic, 그 외(이벤트 등)는 MedicalProcedure 로 본다
            $type = (int) ($review['type'] ?? 0);
            $schema['itemReviewed'] = [
                '@type' => $type === 2 ? 'MedicalClinic' : 'MedicalProcedure',
                'name'  => $targetName,
            ];
        }

        return $schema;
    }

    /**
     * schema 배열(들)을 `<script type="application/ld+json">` 블록으로 직렬화.
     *
     * @param array<int, array<string, mixed>> $schemas
     */
    public static function render(array $schemas): string
    {
        $blocks = [];
        foreach ($schemas as $schema) {
            if ($schema === []) {
                continue;
            }
            // 비ASCII는 \uXXXX 로 인코딩한다(JSON_UNESCAPED_UNICODE 미사용).
            // 전역 출력 핸들러가 멀티바이트를 HTML 엔티티로 바꿔 <script ld+json> 안에서 깨지는 것을 방지.
            $json = json_encode(
                $schema,
                JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_PRETTY_PRINT,
            );
            if ($json === false) {
                continue;
            }
            $blocks[] = '<script type="application/ld+json">' . "\n" . $json . "\n" . '</script>';
        }

        return implode("\n", $blocks);
    }
}
