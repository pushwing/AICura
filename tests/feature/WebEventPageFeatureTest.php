<?php

use App\Models\UserModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;

/**
 * 공개 이벤트 SSR 페이지 피처 테스트 (이슈 #137 — SEO/GEO Phase 1 골격)
 *
 * 커버리지:
 *   [W1] 목록 — 노출 이벤트가 HTML 로 렌더되고 비로그인(인증 헤더 없이) 200
 *   [W2] 목록 — SEO 메타(title·description·canonical·robots index) 출력
 *   [W3] 상세 — 노출 건 200 + 제목/병원 렌더, og:type=article
 *   [W4] 상세 — 비노출 건 404
 *
 * @internal
 */
final class WebEventPageFeatureTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use FeatureTestTrait;

    protected $migrate   = true;
    protected $refresh   = true;
    protected $namespace = null;

    private int $hospitalId = 0;
    private int $visibleId  = 0;
    private int $hiddenId   = 0;

    protected function setUp(): void
    {
        parent::setUp();
        cache()->clean(); // 테스트 간 캐시 격리

        $db  = db_connect();
        $now = date('Y-m-d H:i:s');

        $db->table('users')->insert([
            'email' => 'webguest@aicura.test', 'user_type' => UserModel::TYPE_USER,
            'is_active' => 1, 'created_at' => $now, 'updated_at' => $now,
        ]);

        $db->table('hospitals')->insert([
            'name' => '강남웹병원', 'type' => 1, 'status' => 'active', 'is_deleted' => 0,
            'address' => '서울 강남구', 'phone' => '02-123-4567', 'created_at' => $now, 'updated_at' => $now,
        ]);
        $this->hospitalId = (int) $db->insertID();

        $yesterday = date('Y-m-d', strtotime('-1 day'));
        $tomorrow  = date('Y-m-d', strtotime('+1 day'));

        $this->visibleId = $this->insertCampaign([
            'ad_title' => '강남 리프팅 이벤트', 'exposure' => 1, 'region' => '서울 강남',
            'discount_cost' => 10000, 'general_cost' => 20000,
            'ad_start_date' => $yesterday, 'ad_end_date' => $tomorrow,
            't1_image_name' => 'w1.jpg', 'ad_detail_info' => '<p>리프팅 상세 설명</p><script>alert(1)</script>',
        ]);
        // 비노출: exposure=2 (병원상세 전용)
        $this->hiddenId = $this->insertCampaign(['ad_title' => '비노출이벤트', 'exposure' => 2]);
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

    /**
     * esc() 가 한글을 숫자 HTML 엔티티로 출력하므로(프로젝트 표준), 한글 단언 전 디코드한다.
     */
    private function decode(string $body): string
    {
        return html_entity_decode($body, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    /** [W1] 목록 — 비로그인 200 + 노출 이벤트 렌더 */
    public function testIndexRendersVisibleEvent(): void
    {
        $result = $this->get('events');

        $result->assertStatus(200);
        $body = $this->decode($result->getBody());
        $this->assertStringContainsString('강남 리프팅 이벤트', $body);
        $this->assertStringContainsString('events/' . $this->visibleId, $body);
        $this->assertStringNotContainsString('비노출이벤트', $body);
    }

    /** [W2] 목록 — SEO 메타 출력 (색인 허용 전제) */
    public function testIndexEmitsSeoMeta(): void
    {
        $raw  = $this->get('events')->getBody();
        $body = $this->decode($raw);

        $this->assertStringContainsString('<title>성형·시술 이벤트 모음 | AICura</title>', $body);
        $this->assertStringContainsString('name="description"', $raw);
        $this->assertStringContainsString('content="index, follow"', $raw);
        $this->assertStringContainsString('rel="canonical"', $raw);
        $this->assertStringContainsString('property="og:title"', $raw);
    }

    /** [W3] 상세 — 노출 건 200 + 제목/병원 렌더, 스크립트 태그 제거 */
    public function testShowRendersVisibleEvent(): void
    {
        $result = $this->get('events/' . $this->visibleId);

        $result->assertStatus(200);
        $raw  = $result->getBody();
        $body = $this->decode($raw);
        $this->assertStringContainsString('강남 리프팅 이벤트', $body);
        $this->assertStringContainsString('강남웹병원', $body);
        $this->assertStringContainsString('property="og:type" content="article"', $raw);
        // 리치 본문의 허용 태그는 유지, script 는 화이트리스트로 제거
        $this->assertStringContainsString('리프팅 상세 설명', $body);
        $this->assertStringNotContainsString('<script>alert(1)</script>', $raw);
    }

    /** [W4] 상세 — 비노출 건은 404(PageNotFoundException) */
    public function testShowHiddenReturns404(): void
    {
        $this->expectException(PageNotFoundException::class);
        $this->get('events/' . $this->hiddenId);
    }
}
