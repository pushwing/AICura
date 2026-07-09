<?php

use App\Models\BoardModel;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;

/**
 * 공개 상세 페이지 JSON-LD 주입 피처 테스트 (이슈 #145)
 *
 * JSON-LD 는 비ASCII를 \uXXXX 로 인코딩하므로, 스크립트 블록을 추출해 JSON 파싱 후 검증한다
 * (파싱 성공 자체가 유효한 구조화 데이터임을 보장 — Rich Results 적격성).
 *
 * @internal
 */
final class WebJsonLdFeatureTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use FeatureTestTrait;

    protected $migrate = true;
    protected $refresh = true;
    protected $namespace;
    private int $hospitalId = 0;
    private int $eventId    = 0;
    private int $reviewId   = 0;

    protected function setUp(): void
    {
        parent::setUp();
        cache()->clean();

        $db  = db_connect();
        $now = date('Y-m-d H:i:s');

        $db->table('hospitals')->insert([
            'name'    => '강남웹병원', 'type' => 1, 'status' => 'active', 'is_deleted' => 0,
            'address' => '서울 강남구', 'phone' => '02-123-4567', 'created_at' => $now, 'updated_at' => $now,
        ]);
        $this->hospitalId = (int) $db->insertID();

        $db->table('board_summaries')->insert([
            'type'     => BoardModel::TYPE_HOSPITAL, 'target_id' => $this->hospitalId,
            'rate_sum' => 4.5, 'rate1' => 4.5, 'rate2' => 4.5, 'rate3' => 4.5,
        ]);

        $db->table('campaigns')->insert([
            'ad_title'       => '강남 리프팅', 'hospital_id' => $this->hospitalId, 'status' => 'active',
            'review_status'  => 'approved', 'exposure' => 1, 'is_deleted' => 0, 'category' => 0,
            'ad_type'        => 1, 'cost_type' => 1, 'discount_cost' => 10000, 'general_cost' => 20000,
            'ad_detail_info' => '리프팅 상세', 'created_at' => $now, 'updated_at' => $now,
        ]);
        $this->eventId = (int) $db->insertID();

        $db->table('boards')->insert([
            'user_id'    => 1, 'user_name' => '홍길동', 'type' => BoardModel::TYPE_HOSPITAL,
            'target_id'  => $this->hospitalId, 'subject' => '만족 후기', 'contents' => '시술 만족합니다',
            'rate_sum'   => 5, 'is_secret' => 0, 'is_list' => 1, 'is_delete' => 0,
            'ai_status'  => BoardModel::AI_STATUS_IDLE, 'complain_count' => 0,
            'created_at' => $now, 'updated_at' => $now,
        ]);
        $this->reviewId = (int) $db->insertID();
    }

    /**
     * 페이지 본문에서 첫 JSON-LD 블록을 파싱해 반환.
     *
     * @return array<string, mixed>
     */
    private function jsonLd(string $uri): array
    {
        $body = $this->get($uri)->getBody();
        $this->assertMatchesRegularExpression('#<script type="application/ld\+json">#', $body, 'JSON-LD 누락');

        preg_match('#<script type="application/ld\+json">(.*?)</script>#s', $body, $m);
        $decoded = json_decode(trim($m[1] ?? ''), true);
        $this->assertIsArray($decoded, 'JSON-LD 파싱 실패(유효하지 않은 JSON)');

        return $decoded;
    }

    /**
     * [J1] 이벤트 상세 — Offer + MedicalProcedure + seller, 한글 정상 디코드
     */
    public function testEventDetailHasOfferJsonLd(): void
    {
        $ld = $this->jsonLd('events/' . $this->eventId);

        $this->assertSame('Offer', $ld['@type']);
        $this->assertSame('KRW', $ld['priceCurrency']);
        $this->assertSame(10000, $ld['price']);
        $this->assertSame('강남 리프팅', $ld['name']); // \uXXXX → 한글 정상 복원
        $this->assertSame('MedicalProcedure', $ld['itemOffered']['@type']);
        $this->assertSame('MedicalClinic', $ld['seller']['@type']);
        $this->assertSame('강남웹병원', $ld['seller']['name']);
    }

    /**
     * [J2] 병원 상세 — MedicalClinic + AggregateRating
     */
    public function testHospitalDetailHasMedicalClinicJsonLd(): void
    {
        $ld = $this->jsonLd('hospitals/' . $this->hospitalId);

        $this->assertSame('MedicalClinic', $ld['@type']);
        $this->assertSame('강남웹병원', $ld['name']);
        $this->assertSame('PostalAddress', $ld['address']['@type']);
        $this->assertSame('KR', $ld['address']['addressCountry']);
        $this->assertSame(4.5, $ld['aggregateRating']['ratingValue']);
        $this->assertGreaterThan(0, $ld['aggregateRating']['reviewCount']);
    }

    /**
     * [J3] 후기 상세 — Review + itemReviewed(병원명) + 마스킹된 author
     */
    public function testReviewDetailHasReviewJsonLd(): void
    {
        $ld = $this->jsonLd('reviews/' . $this->reviewId);

        $this->assertSame('Review', $ld['@type']);
        $this->assertSame('홍*동', $ld['author']['name']);   // 마스킹된 작성자
        $this->assertNotSame('홍길동', $ld['author']['name']); // 실명 미노출
        $this->assertSame('MedicalClinic', $ld['itemReviewed']['@type']);
        $this->assertSame('강남웹병원', $ld['itemReviewed']['name']);
        $this->assertEquals(5.0, $ld['reviewRating']['ratingValue']); // JSON 은 5.0 을 5 로 직렬화
    }

    /**
     * [J4] 목록 페이지 — JSON-LD 미출력
     */
    public function testListPageHasNoJsonLd(): void
    {
        $body = $this->get('events')->getBody();
        $this->assertStringNotContainsString('application/ld+json', $body);
    }
}
