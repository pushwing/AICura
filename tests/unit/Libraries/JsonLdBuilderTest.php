<?php

use App\Libraries\Seo\JsonLdBuilder;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * schema.org JSON-LD 빌더 단위 테스트 (이슈 #145)
 *
 * @internal
 */
final class JsonLdBuilderTest extends CIUnitTestCase
{
    /** 이벤트 → Offer + MedicalProcedure + MedicalClinic seller */
    public function testEventSchema(): void
    {
        $schema = JsonLdBuilder::event([
            'id' => 7, 'ad_title' => '강남 리프팅', 'category_title' => '리프팅',
            'discount_cost' => 10000, 'general_cost' => 20000, 'region' => '서울',
            'ad_detail_info' => '<p>상세</p>', 'thumbnail_url' => 'https://x/t.jpg',
            'ad_start_date' => '2026-06-01', 'ad_end_date' => '2026-07-01',
            'hospital_name' => '강남병원', 'hospital_address' => '서울 강남', 'hospital_phone' => '02-1',
        ], 'https://aicura.test/events/7');

        $this->assertSame('Offer', $schema['@type']);
        $this->assertSame(10000, $schema['price']);
        $this->assertSame('KRW', $schema['priceCurrency']);
        $this->assertSame('MedicalProcedure', $schema['itemOffered']['@type']);
        $this->assertSame('MedicalClinic', $schema['seller']['@type']);
        $this->assertSame('강남병원', $schema['seller']['name']);
        $this->assertSame('서울 강남', $schema['seller']['address']);
    }

    /** 할인가 0이면 정상가를 price 로 사용 */
    public function testEventFallsBackToGeneralCost(): void
    {
        $schema = JsonLdBuilder::event([
            'ad_title' => 'x', 'discount_cost' => 0, 'general_cost' => 5000,
        ], 'https://aicura.test/events/1');

        $this->assertSame(5000, $schema['price']);
    }

    /** 병원 → MedicalClinic + AggregateRating(평점·건수 있을 때만) */
    public function testHospitalSchemaWithRating(): void
    {
        $schema = JsonLdBuilder::hospital([
            'name' => '강남병원', 'phone' => '02-1', 'address' => '서울 강남',
            'departments' => ['성형외과', '피부과'],
            'review_summary' => ['rating' => 4.5, 'count' => 12],
        ], 'https://aicura.test/hospitals/3');

        $this->assertSame('MedicalClinic', $schema['@type']);
        $this->assertSame('PostalAddress', $schema['address']['@type']);
        $this->assertSame('KR', $schema['address']['addressCountry']);
        $this->assertSame(['성형외과', '피부과'], $schema['medicalSpecialty']);
        $this->assertSame(4.5, $schema['aggregateRating']['ratingValue']);
        $this->assertSame(12, $schema['aggregateRating']['reviewCount']);
    }

    /** 평점·건수 없으면 aggregateRating 미포함 (Google 요구사항) */
    public function testHospitalWithoutRatingOmitsAggregate(): void
    {
        $schema = JsonLdBuilder::hospital([
            'name' => '무후기병원', 'review_summary' => ['rating' => 0, 'count' => 0],
        ], 'https://aicura.test/hospitals/9');

        $this->assertArrayNotHasKey('aggregateRating', $schema);
    }

    /** 후기 → Review + Rating + itemReviewed(대상명 있을 때) */
    public function testReviewSchema(): void
    {
        $schema = JsonLdBuilder::review([
            'subject' => '만족', 'contents' => '시술 좋았어요', 'author' => '홍*동',
            'rating' => 5, 'created_at' => '2026-06-01 10:00:00', 'type' => 2,
        ], 'https://aicura.test/reviews/4', '강남병원');

        $this->assertSame('Review', $schema['@type']);
        $this->assertSame('홍*동', $schema['author']['name']); // 마스킹된 값 그대로
        $this->assertSame(5.0, $schema['reviewRating']['ratingValue']);
        $this->assertSame('MedicalClinic', $schema['itemReviewed']['@type']);
        $this->assertSame('강남병원', $schema['itemReviewed']['name']);
    }

    /** 대상명 없으면 itemReviewed 생략 */
    public function testReviewWithoutTargetOmitsItemReviewed(): void
    {
        $schema = JsonLdBuilder::review([
            'subject' => 's', 'rating' => 0, 'type' => 1,
        ], 'https://aicura.test/reviews/5', null);

        $this->assertArrayNotHasKey('itemReviewed', $schema);
        $this->assertArrayNotHasKey('reviewRating', $schema); // rating 0 → 미포함
    }

    /** render() — script 블록 + </script> 탈출 방지(JSON_HEX_TAG) */
    public function testRenderEscapesScriptInjection(): void
    {
        $html = JsonLdBuilder::render([
            ['@context' => 'https://schema.org', '@type' => 'Thing', 'name' => 'x</script><script>alert(1)'],
        ]);

        $this->assertStringContainsString('<script type="application/ld+json">', $html);
        // 데이터의 <,> 는 </> 로 이스케이프 → 주입된 <script> 리터럴 미출현, 닫는 태그 1개뿐
        $this->assertStringContainsString('<script', $html);
        $this->assertStringNotContainsString('<script>alert', $html);
        $this->assertSame(1, substr_count($html, '</script>'));
    }

    /** render([]) — 빈 입력은 빈 문자열 */
    public function testRenderEmpty(): void
    {
        $this->assertSame('', JsonLdBuilder::render([]));
    }
}
