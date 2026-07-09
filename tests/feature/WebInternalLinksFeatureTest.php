<?php

use App\Models\BoardModel;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;

/**
 * 공개 페이지 내부 링크 강화 피처 테스트 (이슈 #152 — 블로그 백링크=내부 링크)
 *
 * 링크 URL 은 ASCII 이지만 `&`·`[]` 가 DOMParser 로 정규화되므로 response()->getBody()(원문)로 검증한다.
 *
 * @internal
 */
final class WebInternalLinksFeatureTest extends CIUnitTestCase
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

        $now = date('Y-m-d H:i:s');
        $db  = db_connect();

        $db->table('hospitals')->insert([
            'name'    => '강남웹병원', 'type' => 1, 'status' => 'active', 'is_deleted' => 0,
            'address' => '서울', 'phone' => '02-1', 'created_at' => $now, 'updated_at' => $now,
        ]);
        $this->hospitalId = (int) $db->insertID();

        $db->table('board_summaries')->insert([
            'type'     => BoardModel::TYPE_HOSPITAL, 'target_id' => $this->hospitalId,
            'rate_sum' => 4.5, 'rate1' => 4.5, 'rate2' => 4.5, 'rate3' => 4.5,
        ]);

        $db->table('campaigns')->insert([
            'ad_title'      => '강남 리프팅', 'hospital_id' => $this->hospitalId, 'status' => 'active',
            'review_status' => 'approved', 'exposure' => 1, 'is_deleted' => 0, 'category' => 0,
            'ad_type'       => 1, 'cost_type' => 1, 'discount_cost' => 10000, 'created_at' => $now, 'updated_at' => $now,
        ]);
        $this->eventId = (int) $db->insertID();

        $db->table('boards')->insert([
            'user_id'    => 1, 'user_name' => '홍길동', 'type' => BoardModel::TYPE_HOSPITAL,
            'target_id'  => $this->hospitalId, 'subject' => '만족 후기', 'contents' => '좋아요',
            'rate_sum'   => 5, 'is_secret' => 0, 'is_list' => 1, 'is_delete' => 0,
            'ai_status'  => BoardModel::AI_STATUS_IDLE, 'complain_count' => 0,
            'created_at' => $now, 'updated_at' => $now,
        ]);
        $this->reviewId = (int) $db->insertID();
    }

    private function rawBody(string $uri): string
    {
        return (string) $this->get($uri)->response()->getBody();
    }

    /**
     * 헤더 nav 에 가이드 링크
     */
    public function testHeaderHasGuidesLink(): void
    {
        $this->assertStringContainsString('/guides"', $this->rawBody('events'));
    }

    /**
     * 이벤트 상세 → 이 이벤트 후기 링크 + 병원 링크
     */
    public function testEventLinksToReviewsAndHospital(): void
    {
        $body = $this->rawBody('events/' . $this->eventId);

        // base_url 이 대괄호를 %5B%5D 로 인코딩 (CI4 가 디코드해 filter[] 로 파싱)
        $this->assertStringContainsString('reviews?filter%5Btype%5D=1&filter%5Btarget_id%5D=' . $this->eventId, $body);
        $this->assertStringContainsString('hospitals/' . $this->hospitalId, $body);
    }

    /**
     * 병원 상세 → 후기 전체 보기 링크
     */
    public function testHospitalLinksToReviews(): void
    {
        $body = $this->rawBody('hospitals/' . $this->hospitalId);

        $this->assertStringContainsString('reviews?filter%5Btype%5D=2&filter%5Btarget_id%5D=' . $this->hospitalId, $body);
    }

    /**
     * 후기 상세 → 리뷰 대상(병원) 링크
     */
    public function testReviewLinksToTargetHospital(): void
    {
        $body = $this->rawBody('reviews/' . $this->reviewId);

        $this->assertStringContainsString('hospitals/' . $this->hospitalId, $body);
    }
}
