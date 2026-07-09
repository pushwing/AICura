<?php

use App\Models\BoardModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;

/**
 * 공개 병원·후기 SSR 페이지 피처 테스트 (이슈 #144)
 *
 * 커버리지:
 *   [H1] 병원 목록 — 비로그인 200·병원 렌더
 *   [H2] 병원 상세 — 200·이름/진행 이벤트, 비활성 404
 *   [R1] 후기 목록 — 작성자 마스킹(실명 미노출)
 *   [R2] 후기 상세 — 마스킹 + 정상 후기 index
 *   [R3] 후기 상세 — 신고/저신뢰 후기 noindex
 *   [R4] 후기 상세 — 비밀/삭제 404
 *
 * @internal
 */
final class WebHospitalReviewPageFeatureTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use FeatureTestTrait;

    protected $migrate = true;
    protected $refresh = true;
    protected $namespace;
    private int $hospitalId       = 0;
    private int $cleanReviewId    = 0;
    private int $reportedReviewId = 0;
    private int $lowTrustReviewId = 0;
    private int $secretReviewId   = 0;

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

        // 병원 별점 요약
        $db->table('board_summaries')->insert([
            'type'     => BoardModel::TYPE_HOSPITAL, 'target_id' => $this->hospitalId,
            'rate_sum' => 4.5, 'rate1' => 4.5, 'rate2' => 4.5, 'rate3' => 4.5,
        ]);

        // 병원 진행 이벤트 (검수완료·노출)
        $db->table('campaigns')->insert([
            'ad_title'      => '강남 리프팅', 'hospital_id' => $this->hospitalId, 'status' => 'active',
            'review_status' => 'approved', 'exposure' => 1, 'is_deleted' => 0, 'category' => 0,
            'ad_type'       => 1, 'cost_type' => 1, 'discount_cost' => 10000, 'general_cost' => 20000,
            'created_at'    => $now, 'updated_at' => $now,
        ]);

        $this->cleanReviewId    = $this->insertReview('정상후기', '홍길동', []);
        $this->reportedReviewId = $this->insertReview('신고후기', '김철수', ['complain_count' => 1]);
        $this->lowTrustReviewId = $this->insertReview('저신뢰후기', '이영희', [
            'ai_status' => BoardModel::AI_STATUS_DONE, 'ai_trust_score' => 10,
        ]);
        $this->secretReviewId = $this->insertReview('비밀후기', '박비밀', ['is_secret' => 1]);
    }

    /**
     * @param array<string, mixed> $overrides
     */
    private function insertReview(string $subject, string $userName, array $overrides): int
    {
        $db  = db_connect();
        $now = date('Y-m-d H:i:s');
        $db->table('boards')->insert(array_merge([
            'user_id'    => 1, 'user_name' => $userName, 'type' => BoardModel::TYPE_HOSPITAL,
            'target_id'  => $this->hospitalId, 'subject' => $subject, 'contents' => '시술 만족합니다',
            'rate_sum'   => 5, 'is_secret' => 0, 'is_list' => 1, 'is_delete' => 0,
            'ai_status'  => BoardModel::AI_STATUS_IDLE, 'complain_count' => 0,
            'created_at' => $now, 'updated_at' => $now,
        ], $overrides));

        return (int) $db->insertID();
    }

    private function decode(string $body): string
    {
        return html_entity_decode($body, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    // ── 병원 ──────────────────────────────────────────

    /**
     * [H1] 병원 목록 — 200 + 병원 렌더
     */
    public function testHospitalIndex(): void
    {
        $result = $this->get('hospitals');
        $result->assertStatus(200);
        $this->assertStringContainsString('강남웹병원', $this->decode($result->getBody()));
    }

    /**
     * [H2] 병원 상세 — 200 + 이름·진행 이벤트
     */
    public function testHospitalShow(): void
    {
        $result = $this->get('hospitals/' . $this->hospitalId);
        $result->assertStatus(200);
        $body = $this->decode($result->getBody());
        $this->assertStringContainsString('강남웹병원', $body);
        $this->assertStringContainsString('강남 리프팅', $body); // 진행 이벤트
    }

    /**
     * [H2] 병원 상세 — 비활성 404
     */
    public function testHospitalShowInactiveReturns404(): void
    {
        db_connect()->table('hospitals')->update(['status' => 'inactive'], ['id' => $this->hospitalId]);

        $this->expectException(PageNotFoundException::class);
        $this->get('hospitals/' . $this->hospitalId);
    }

    // ── 후기 ──────────────────────────────────────────

    /**
     * [R1] 후기 목록 — 작성자 마스킹(실명 미노출)
     */
    public function testReviewIndexMasksAuthor(): void
    {
        $body = $this->decode($this->get('reviews')->getBody());

        $this->assertStringContainsString('홍*동', $body);     // 마스킹된 형태
        $this->assertStringNotContainsString('홍길동', $body); // 실명 미노출
    }

    /**
     * [R2] 후기 상세 — 마스킹 + 정상 후기 index
     */
    public function testReviewShowIndexableAndMasked(): void
    {
        $result = $this->get('reviews/' . $this->cleanReviewId);
        $result->assertStatus(200);
        $raw  = $result->getBody();
        $body = $this->decode($raw);

        $this->assertStringContainsString('홍*동', $body);
        $this->assertStringNotContainsString('홍길동', $body);
        $this->assertStringContainsString('content="index, follow"', $raw);
    }

    /**
     * [R3] 후기 상세 — 신고 후기 noindex
     */
    public function testReportedReviewIsNoindex(): void
    {
        $raw = $this->get('reviews/' . $this->reportedReviewId)->getBody();
        $this->assertStringContainsString('content="noindex, follow"', $raw);
    }

    /**
     * [R3] 후기 상세 — 저신뢰 후기 noindex
     */
    public function testLowTrustReviewIsNoindex(): void
    {
        $raw = $this->get('reviews/' . $this->lowTrustReviewId)->getBody();
        $this->assertStringContainsString('content="noindex, follow"', $raw);
    }

    /**
     * [R4] 후기 상세 — 비밀글 404
     */
    public function testSecretReviewReturns404(): void
    {
        $this->expectException(PageNotFoundException::class);
        $this->get('reviews/' . $this->secretReviewId);
    }
}
