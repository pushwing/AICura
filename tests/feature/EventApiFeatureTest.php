<?php

use App\Libraries\JwtLibrary;
use App\Models\UserModel;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;

/**
 * 외부(소비자) 앱 이벤트 API 피처 테스트 (이슈 #98, SQLite3 인메모리 DB)
 *
 * 커버리지:
 *   [E1]  목록 — 노출 조건 충족 건만(active·exposure∈{1,3}·기간내·미삭제), 내부 필드 미노출
 *   [E2]  목록 — 카테고리 필터
 *   [E3]  목록 — 가격 오름차순 정렬
 *   [E4]  목록 — 인기순(상담신청 수) 정렬
 *   [E5]  상세 — 노출 건 200, 비노출 건 404
 *   [E6]  카테고리 — 노출 항목만
 *   [E7]  메인 노출 이벤트
 *   [E8]  추천 이벤트
 *   [E9]  찜 토글 — 추가/해제, 비노출 대상 404
 *   [E10] 찜 후 목록 is_liked 반영
 *   [E11] 토큰 없으면 401
 *
 * @internal
 */
final class EventApiFeatureTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use FeatureTestTrait;

    protected $migrate   = true;
    protected $refresh   = true;
    protected $namespace = null;

    private int $userId   = 0;
    private string $token = '';
    private int $hospitalId = 0;
    private int $catA = 0;
    private int $catB = 0;
    private int $c1 = 0;
    private int $c2 = 0;
    private int $c3Hidden = 0;

    protected function setUp(): void
    {
        parent::setUp();
        cache()->clean(); // 테스트 간 캐시 격리

        $db  = db_connect();
        $now = date('Y-m-d H:i:s');

        $db->table('users')->insert([
            'email' => 'eventuser@aicura.test', 'user_type' => UserModel::TYPE_USER,
            'is_active' => 1, 'created_at' => $now, 'updated_at' => $now,
        ]);
        $this->userId = (int) $db->insertID();
        $this->token  = (new JwtLibrary())->generateAccessToken($this->userId);

        $db->table('hospitals')->insert([
            'name' => '강남병원', 'type' => 1, 'status' => 'active', 'is_deleted' => 0,
            'address' => '서울 강남구', 'phone' => '02-123-4567', 'created_at' => $now, 'updated_at' => $now,
        ]);
        $this->hospitalId = (int) $db->insertID();

        $this->catA = $this->insertCategory('성형', 1, 1);
        $this->catB = $this->insertCategory('피부', 1, 2);
        $this->insertCategory('숨김카테고리', 0, 3); // 비노출

        $yesterday = date('Y-m-d', strtotime('-1 day'));
        $tomorrow  = date('Y-m-d', strtotime('+1 day'));

        // 노출 대상
        $this->c1 = $this->insertCampaign([
            'ad_title' => '강남 리프팅', 'exposure' => 1, 'category' => $this->catA, 'region' => '서울 강남',
            'discount_cost' => 10000, 'general_cost' => 20000, 'ad_type' => 1,
            'ad_start_date' => $yesterday, 'ad_end_date' => $tomorrow,
            't1_image_name' => 'c1.jpg', 'd_image_json' => '["d1.jpg","d2.jpg"]', 'ad_detail_info' => '상세설명',
        ]);
        $this->c2 = $this->insertCampaign([
            'ad_title' => '부산 보톡스', 'exposure' => 3, 'category' => $this->catB, 'region' => '부산',
            'discount_cost' => 5000, 'general_cost' => 8000, 'ad_type' => 3,
            'ad_start_date' => null, 'ad_end_date' => null, 't1_image_name' => 'c2.jpg',
        ]);
        // 비노출: exposure=2(병원상세 전용)
        $this->c3Hidden = $this->insertCampaign(['ad_title' => '병원상세전용', 'exposure' => 2, 'category' => $this->catA]);
        // 비노출: pending
        $this->insertCampaign(['ad_title' => '검수대기', 'exposure' => 1, 'status' => 'pending']);
        // 비노출: 기간 종료
        $this->insertCampaign(['ad_title' => '종료', 'exposure' => 1, 'ad_end_date' => $yesterday]);
        // 비노출: 삭제됨
        $this->insertCampaign(['ad_title' => '삭제됨', 'exposure' => 1, 'is_deleted' => 1]);

        // 인기순: C2 가 상담신청 더 많음
        $this->insertCallRequest($this->c1, 1);
        $this->insertCallRequest($this->c2, 3);

        // 메인/추천
        $db->table('ad_main_maps')->insert([
            'ad_main_id' => 1, 'campaign_id' => $this->c1, 'is_main' => 1, 'is_inspect' => 1,
            'created_at' => $now, 'updated_at' => $now,
        ]);
        $db->table('ad_recommend_maps')->insert([
            'campaign_id' => $this->c2, 'ads_order' => 1, 'is_delete' => 0,
            'created_at' => $now, 'updated_at' => $now,
        ]);
    }

    private function insertCategory(string $title, int $visible, int $sort): int
    {
        $db = db_connect();
        $db->table('event_categories')->insert([
            'title' => $title, 'is_visible' => $visible, 'sort' => $sort, 'category_type' => 0,
        ]);

        return (int) $db->insertID();
    }

    /** @param array<string, mixed> $overrides */
    private function insertCampaign(array $overrides): int
    {
        $db  = db_connect();
        $now = date('Y-m-d H:i:s');
        $row = array_merge([
            'ad_title' => '이벤트', 'hospital_id' => $this->hospitalId, 'status' => 'active',
            'exposure' => 1, 'is_deleted' => 0, 'category' => 0, 'region' => '서울',
            'ad_type' => 1, 'cost_type' => 1, 'general_cost' => 0, 'discount_cost' => 0,
            'created_at' => $now, 'updated_at' => $now,
        ], $overrides);
        $db->table('campaigns')->insert($row);

        return (int) $db->insertID();
    }

    private function insertCallRequest(int $campaignId, int $count): void
    {
        $db  = db_connect();
        $now = date('Y-m-d H:i:s');
        for ($i = 0; $i < $count; $i++) {
            $db->table('call_requests')->insert([
                'hospital_id' => $this->hospitalId, 'campaign_id' => $campaignId,
                'is_delete' => 0, 'status' => 1, 'created_at' => $now, 'updated_at' => $now,
            ]);
        }
    }

    /** @return array<string, mixed> */
    private function authGet(string $uri): array
    {
        $result = $this->withHeaders(['Authorization' => 'Bearer ' . $this->token])->get($uri);
        $result->assertStatus(200);

        return json_decode($result->getJSON(), true);
    }

    /** [E1] 목록 — 노출 조건 + 내부 필드 미노출 */
    public function testListReturnsOnlyVisibleEvents(): void
    {
        $body = $this->authGet('api/v1/campaigns');

        $this->assertSame(2, $body['meta']['total']);
        $ids = array_column($body['data'], 'id');
        sort($ids);
        $this->assertSame([$this->c1, $this->c2], $ids);

        $item = $body['data'][0];
        $this->assertArrayHasKey('thumbnail_url', $item);
        $this->assertArrayHasKey('is_liked', $item);
        $this->assertFalse($item['is_liked']);
        // 내부 과금·계약 필드는 노출되지 않아야 함
        $this->assertArrayNotHasKey('db_cost', $item);
        $this->assertArrayNotHasKey('contract_id', $item);
        $this->assertArrayNotHasKey('agency_user_id', $item);
    }

    /** [E2] 카테고리 필터 */
    public function testListFilterByCategory(): void
    {
        $body = $this->authGet('api/v1/campaigns?filter[category]=' . $this->catA);

        $this->assertSame(1, $body['meta']['total']);
        $this->assertSame($this->c1, $body['data'][0]['id']);
    }

    /** [E3] 가격 오름차순 */
    public function testListSortByPriceAsc(): void
    {
        $body = $this->authGet('api/v1/campaigns?sort=price_asc');

        $this->assertSame($this->c2, $body['data'][0]['id']); // 5000
        $this->assertSame($this->c1, $body['data'][1]['id']); // 10000
    }

    /** [E4] 인기순 (상담신청 수) */
    public function testListSortByPopular(): void
    {
        $body = $this->authGet('api/v1/campaigns?sort=popular');

        $this->assertSame($this->c2, $body['data'][0]['id']); // 신청 3건
        $this->assertArrayNotHasKey('request_count', $body['data'][0]); // 내부 집계값 미노출
    }

    /** [E5] 상세 — 노출 200 / 비노출 404 */
    public function testDetailVisibleAndHidden(): void
    {
        $body = $this->authGet('api/v1/campaigns/' . $this->c1);
        $this->assertSame($this->c1, $body['data']['id']);
        $this->assertSame('강남병원', $body['data']['hospital_name']);
        $this->assertCount(2, $body['data']['detail_images']);
        $this->assertStringContainsString('uploads/campaigns/c1.jpg', $body['data']['thumbnail_url']);

        $hidden = $this->withHeaders(['Authorization' => 'Bearer ' . $this->token])
            ->get('api/v1/campaigns/' . $this->c3Hidden);
        $hidden->assertStatus(404);
        $this->assertSame('NOT_FOUND', json_decode($hidden->getJSON(), true)['code']);
    }

    /** [E6] 카테고리 — 노출만 */
    public function testCategoriesReturnsVisibleOnly(): void
    {
        $body = $this->authGet('api/v1/campaigns/categories');

        $this->assertCount(2, $body['data']);
        $titles = array_column($body['data'], 'title');
        $this->assertNotContains('숨김카테고리', $titles);
    }

    /** [E7] 메인 노출 */
    public function testMainEvents(): void
    {
        $body = $this->authGet('api/v1/campaigns/main');

        $this->assertCount(1, $body['data']);
        $this->assertSame($this->c1, $body['data'][0]['id']);
    }

    /** [E8] 추천 */
    public function testRecommendEvents(): void
    {
        $body = $this->authGet('api/v1/campaigns/recommend');

        $this->assertCount(1, $body['data']);
        $this->assertSame($this->c2, $body['data'][0]['id']);
    }

    /** [E9] 찜 토글 + 비노출 대상 404 */
    public function testLikeToggle(): void
    {
        $first = $this->withHeaders(['Authorization' => 'Bearer ' . $this->token])
            ->post('api/v1/campaigns/' . $this->c1 . '/like');
        $first->assertStatus(200);
        $this->assertTrue(json_decode($first->getJSON(), true)['data']['liked']);

        $this->seeInDatabase('favorites', [
            'user_id' => $this->userId, 'target_type' => 'campaign', 'target_id' => $this->c1,
        ]);

        $second = $this->withHeaders(['Authorization' => 'Bearer ' . $this->token])
            ->post('api/v1/campaigns/' . $this->c1 . '/like');
        $this->assertFalse(json_decode($second->getJSON(), true)['data']['liked']);

        $hidden = $this->withHeaders(['Authorization' => 'Bearer ' . $this->token])
            ->post('api/v1/campaigns/' . $this->c3Hidden . '/like');
        $hidden->assertStatus(404);
    }

    /** [E10] 찜 후 목록 is_liked 반영 */
    public function testListReflectsLike(): void
    {
        $this->withHeaders(['Authorization' => 'Bearer ' . $this->token])
            ->post('api/v1/campaigns/' . $this->c1 . '/like');

        $body  = $this->authGet('api/v1/campaigns');
        $liked = [];
        foreach ($body['data'] as $item) {
            $liked[$item['id']] = $item['is_liked'];
        }
        $this->assertTrue($liked[$this->c1]);
        $this->assertFalse($liked[$this->c2]);
    }

    /** [E11] 토큰 없으면 401 */
    public function testRequiresAuth(): void
    {
        $this->get('api/v1/campaigns')->assertStatus(401);
    }
}
