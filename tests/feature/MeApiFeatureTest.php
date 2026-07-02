<?php

use App\Libraries\JwtLibrary;
use App\Models\UserModel;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;

/**
 * 외부(소비자) 앱 마이페이지 API 피처 테스트 (이슈 #97, SQLite3 인메모리 DB)
 *
 * 커버리지:
 *   [M1]  프로필 — 민감 필드(password) 미노출
 *   [M2]  프로필 수정
 *   [M3]  회원 탈퇴 — soft delete
 *   [M4]  기기 등록 — push_token upsert
 *   [M5]  내 상담 신청 내역 (본인 것만)
 *   [M6]  내가 쓴 후기 (본인 것만)
 *   [M7]  내 예약
 *   [M8]  찜 — campaign / hospital 유형별
 *   [M9]  헬스포인트 — 잔액 + 내역
 *   [M10] 토큰 없으면 401
 *
 * @internal
 */
final class MeApiFeatureTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use FeatureTestTrait;

    protected $migrate   = true;
    protected $refresh   = true;
    protected $namespace = null;

    private int $userId = 0;
    private int $otherId = 0;
    private string $token = '';
    private int $hospitalId = 0;
    private int $campaignId = 0;

    protected function setUp(): void
    {
        parent::setUp();
        cache()->clean();

        $db  = db_connect();
        $now = date('Y-m-d H:i:s');

        $db->table('users')->insert([
            'email' => 'me@aicura.test', 'username' => '나', 'user_type' => UserModel::TYPE_USER,
            'is_active' => 1, 'health_point' => 500, 'provider' => 9, 'created_at' => $now, 'updated_at' => $now,
        ]);
        $this->userId = (int) $db->insertID();
        $this->token  = (new JwtLibrary())->generateAccessToken($this->userId);

        $db->table('users')->insert([
            'email' => 'other@aicura.test', 'user_type' => UserModel::TYPE_USER, 'is_active' => 1,
            'created_at' => $now, 'updated_at' => $now,
        ]);
        $this->otherId = (int) $db->insertID();

        $db->table('hospitals')->insert(['name' => '강남병원', 'type' => 1, 'status' => 'active', 'is_deleted' => 0, 'created_at' => $now, 'updated_at' => $now]);
        $this->hospitalId = (int) $db->insertID();
        $db->table('campaigns')->insert([
            'ad_title' => '리프팅', 'hospital_id' => $this->hospitalId, 'status' => 'active', 'exposure' => 1,
            'is_deleted' => 0, 'ad_type' => 1, 'cost_type' => 1, 'created_at' => $now, 'updated_at' => $now,
        ]);
        $this->campaignId = (int) $db->insertID();

        // 내 활동 + 타인 활동(섞이면 안 됨)
        $db->table('call_requests')->insert(['hospital_id' => $this->hospitalId, 'campaign_id' => $this->campaignId, 'user_id' => $this->userId, 'status' => 1, 'is_delete' => 0, 'created_at' => $now, 'updated_at' => $now]);
        $db->table('call_requests')->insert(['hospital_id' => $this->hospitalId, 'campaign_id' => $this->campaignId, 'user_id' => $this->otherId, 'status' => 1, 'is_delete' => 0, 'created_at' => $now, 'updated_at' => $now]);

        $db->table('boards')->insert(['user_id' => $this->userId, 'type' => 2, 'target_id' => $this->hospitalId, 'subject' => '내 후기', 'rate_sum' => 4.0, 'like_count' => 1, 'is_delete' => 0, 'is_secret' => 0, 'is_list' => 1, 'created_at' => $now, 'updated_at' => $now]);
        // 삭제(임시)한 후기 — 소비자 마이페이지에는 노출되면 안 됨
        $db->table('boards')->insert(['user_id' => $this->userId, 'type' => 2, 'target_id' => $this->hospitalId, 'subject' => '삭제한 후기', 'rate_sum' => 3.0, 'like_count' => 0, 'is_delete' => 1, 'is_secret' => 0, 'is_list' => 1, 'created_at' => $now, 'updated_at' => $now]);

        $db->table('bookings')->insert(['user_id' => $this->userId, 'hospital_id' => $this->hospitalId, 'status' => 1, 'book_date' => $now, 'created_at' => $now, 'updated_at' => $now]);

        $db->table('favorites')->insert(['user_id' => $this->userId, 'target_type' => 'campaign', 'target_id' => $this->campaignId, 'created_at' => $now]);
        $db->table('favorites')->insert(['user_id' => $this->userId, 'target_type' => 'hospital', 'target_id' => $this->hospitalId, 'created_at' => $now]);

        $db->table('health_point_logs')->insert(['user_id' => $this->userId, 'amount' => 500, 'balance_after' => 500, 'type' => 'signup', 'memo' => '가입 적립', 'created_at' => $now]);
    }

    private function authHeaders(): array
    {
        return ['Authorization' => 'Bearer ' . $this->token];
    }

    /** @return array<string, mixed> */
    private function authGet(string $uri): array
    {
        $result = $this->withHeaders($this->authHeaders())->get($uri);
        $result->assertStatus(200);

        return json_decode($result->getJSON(), true);
    }

    /** [M1] 프로필 */
    public function testProfile(): void
    {
        $body = $this->authGet('api/v1/me');
        $this->assertSame('me@aicura.test', $body['data']['email']);
        $this->assertSame(500, $body['data']['health_point']);
        $this->assertArrayNotHasKey('password', $body['data']);
    }

    /** [M2] 프로필 수정 */
    public function testUpdateProfile(): void
    {
        $result = $this->withHeaders($this->authHeaders())->withBodyFormat('json')
            ->patch('api/v1/me', ['username' => '새이름', 'phone' => '01099998888']);
        $result->assertStatus(200);
        $this->assertSame('새이름', json_decode($result->getJSON(), true)['data']['username']);
        $this->seeInDatabase('users', ['id' => $this->userId, 'username' => '새이름', 'phone' => '01099998888']);
    }

    /** [M3] 회원 탈퇴 */
    public function testWithdraw(): void
    {
        $this->withHeaders($this->authHeaders())->delete('api/v1/me')->assertStatus(200);
        $this->dontSeeInDatabase('users', ['id' => $this->userId, 'deleted_at' => null]);
    }

    /** [M4] 기기 등록 (upsert) */
    public function testRegisterDevice(): void
    {
        $payload = ['push_token' => 'fcm-abc', 'platform' => 2];
        $this->withHeaders($this->authHeaders())->withBodyFormat('json')->post('api/v1/me/device', $payload)->assertStatus(200);
        $this->seeInDatabase('user_devices', ['user_id' => $this->userId, 'push_token' => 'fcm-abc', 'platform' => 2]);

        // 같은 토큰 재등록 → 행 1개 유지
        $this->withHeaders($this->authHeaders())->withBodyFormat('json')->post('api/v1/me/device', $payload)->assertStatus(200);
        $this->assertSame(1, model(\App\Models\UserDeviceModel::class)->where('push_token', 'fcm-abc')->countAllResults());
    }

    /** [M5] 내 상담 내역 */
    public function testMyCallRequests(): void
    {
        $body = $this->authGet('api/v1/me/call-requests');
        $this->assertSame(1, $body['meta']['total']); // 타인 것 제외
        $this->assertSame('미확인', $body['data'][0]['status_label']);
    }

    /** [M6] 내 후기 */
    public function testMyBoards(): void
    {
        $body = $this->authGet('api/v1/me/boards');
        $this->assertSame(1, $body['meta']['total']);
        $this->assertSame('내 후기', $body['data'][0]['subject']);
    }

    /** [M7] 내 예약 */
    public function testMyBookings(): void
    {
        $body = $this->authGet('api/v1/me/bookings');
        $this->assertSame(1, $body['meta']['total']);
        $this->assertSame('강남병원', $body['data'][0]['hospital_name']);
    }

    /** [M8] 찜 — 유형별 */
    public function testMyLikes(): void
    {
        $camp = $this->authGet('api/v1/me/likes?type=campaign');
        $this->assertSame(1, $camp['meta']['total']);
        $this->assertSame('campaign', $camp['data'][0]['type']);
        $this->assertSame('리프팅', $camp['data'][0]['title']);

        $hosp = $this->authGet('api/v1/me/likes?type=hospital');
        $this->assertSame(1, $hosp['meta']['total']);
        $this->assertSame('hospital', $hosp['data'][0]['type']);
        $this->assertSame('강남병원', $hosp['data'][0]['name']);
    }

    /** [M9] 헬스포인트 */
    public function testHealthPoint(): void
    {
        $body = $this->authGet('api/v1/me/health-point');
        $this->assertSame(500, $body['data']['balance']);
        $this->assertCount(1, $body['data']['logs']);
        $this->assertSame('signup', $body['data']['logs'][0]['type']);
    }

    /** [M10] 토큰 없으면 401 */
    public function testRequiresAuth(): void
    {
        $this->get('api/v1/me')->assertStatus(401);
    }
}
