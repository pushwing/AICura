<?php

use App\Libraries\JwtLibrary;
use App\Models\CallRequestModel;
use App\Models\UserModel;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;

/**
 * 외부(소비자) 앱 상담 신청 API 피처 테스트 (이슈 #100, SQLite3 인메모리 DB)
 *
 * 커버리지:
 *   [C1]  신청 성공 → 201, event_cost=db_cost·status=미확인·AI큐 PENDING 저장
 *   [C2]  개인정보 미동의 → 422
 *   [C3]  필수값 누락 → 422
 *   [C4]  비노출 캠페인 신청 → 404
 *   [C5]  상세 — 본인 200 / 타인 404 / 미존재 404
 *   [C6]  상세 — 내부 필드(ai_*·event_cost·is_charged) 미노출
 *   [C7]  취소 — 미확인 건 soft delete
 *   [C8]  취소 — 미확인 아님 → 409 CANNOT_CANCEL
 *   [C9]  취소 — 타인 건 404
 *   [C10] 토큰 없으면 401
 *
 * @internal
 */
final class CallRequestApiFeatureTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use FeatureTestTrait;

    protected $migrate   = true;
    protected $refresh   = true;
    protected $namespace = null;

    private int $userId = 0;
    private int $otherUserId = 0;
    private string $token = '';
    private int $hospitalId = 0;
    private int $campaignId = 0;
    private int $hiddenCampaignId = 0;

    protected function setUp(): void
    {
        parent::setUp();
        cache()->clean();

        $db  = db_connect();
        $now = date('Y-m-d H:i:s');

        $this->userId      = $this->insertUser('caller@aicura.test');
        $this->otherUserId = $this->insertUser('other@aicura.test');
        $this->token       = (new JwtLibrary())->generateAccessToken($this->userId);

        $db->table('hospitals')->insert([
            'name' => '강남병원', 'type' => 1, 'status' => 'active', 'is_deleted' => 0,
            'created_at' => $now, 'updated_at' => $now,
        ]);
        $this->hospitalId = (int) $db->insertID();

        // 노출 캠페인 (CPA 단가 db_cost=3000)
        $this->campaignId = $this->insertCampaign(['exposure' => 1, 'db_cost' => 3000]);
        // 비노출 (exposure=2 병원상세 전용)
        $this->hiddenCampaignId = $this->insertCampaign(['exposure' => 2, 'db_cost' => 3000]);
    }

    private function insertUser(string $email): int
    {
        $db  = db_connect();
        $now = date('Y-m-d H:i:s');
        $db->table('users')->insert([
            'email' => $email, 'user_type' => UserModel::TYPE_USER, 'is_active' => 1,
            'created_at' => $now, 'updated_at' => $now,
        ]);

        return (int) $db->insertID();
    }

    /** @param array<string, mixed> $overrides */
    private function insertCampaign(array $overrides): int
    {
        $db  = db_connect();
        $now = date('Y-m-d H:i:s');
        $db->table('campaigns')->insert(array_merge([
            'ad_title' => '리프팅 이벤트', 'hospital_id' => $this->hospitalId, 'status' => 'active',
            'review_status' => 'approved', // 검수완료 — 노출 조건 (이슈 #137)
            'exposure' => 1, 'is_deleted' => 0, 'ad_type' => 1, 'cost_type' => 1,
            'created_at' => $now, 'updated_at' => $now,
        ], $overrides));

        return (int) $db->insertID();
    }

    /** @param array<string, mixed> $body */
    private function authPost(string $uri, array $body): \CodeIgniter\Test\TestResponse
    {
        return $this->withHeaders(['Authorization' => 'Bearer ' . $this->token])
            ->withBodyFormat('json')
            ->post($uri, $body);
    }

    private function validPayload(): array
    {
        return [
            'campaign_id' => $this->campaignId,
            'name' => '홍길동', 'phone' => '01012345678',
            'privacy_agree' => true, 'supply_third_party_agree' => true,
            'content' => '상담 원해요', 'age' => 29, 'sex' => 2, 'device' => 2,
        ];
    }

    /** [C1] 신청 성공 */
    public function testCreateSucceeds(): void
    {
        $result = $this->authPost('api/v1/call-requests', $this->validPayload());
        $result->assertStatus(201);

        $data = json_decode($result->getJSON(), true)['data'];
        $this->assertSame('미확인', $data['status_label']);
        $this->assertSame($this->campaignId, $data['campaign_id']);

        $this->seeInDatabase('call_requests', [
            'id' => $data['id'], 'user_id' => $this->userId, 'hospital_id' => $this->hospitalId,
            'event_cost' => 3000, 'status' => CallRequestModel::STATUS_UNCONFIRMED,
            'privacy_agree' => 1, 'ai_status' => CallRequestModel::AI_STATUS_PENDING,
        ]);
    }

    /** [C2] 개인정보 미동의 */
    public function testCreateRequiresPrivacyAgree(): void
    {
        $payload = $this->validPayload();
        $payload['privacy_agree'] = false;

        $result = $this->authPost('api/v1/call-requests', $payload);
        $result->assertStatus(422);
        $this->assertSame('VALIDATION_ERROR', json_decode($result->getJSON(), true)['code']);
    }

    /** [C3] 필수값 누락 */
    public function testCreateValidatesRequired(): void
    {
        $payload = $this->validPayload();
        unset($payload['name']);

        $this->authPost('api/v1/call-requests', $payload)->assertStatus(422);
    }

    /** [C3-1] 컬럼 길이 초과(region) → DB 오류(500)가 아닌 422 */
    public function testCreateValidatesFieldLength(): void
    {
        $payload           = $this->validPayload();
        $payload['region'] = str_repeat('가', 101); // region VARCHAR(100) 초과

        $result = $this->authPost('api/v1/call-requests', $payload);
        $result->assertStatus(422);
        $this->assertSame('VALIDATION_ERROR', json_decode($result->getJSON(), true)['code']);
    }

    /** [C4] 비노출 캠페인 */
    public function testCreateOnHiddenCampaignReturns404(): void
    {
        $payload = $this->validPayload();
        $payload['campaign_id'] = $this->hiddenCampaignId;

        $result = $this->authPost('api/v1/call-requests', $payload);
        $result->assertStatus(404);
        $this->assertSame('NOT_FOUND', json_decode($result->getJSON(), true)['code']);
    }

    /** [C5][C6] 상세 — 소유권 + 내부 필드 미노출 */
    public function testDetailOwnershipAndFieldExposure(): void
    {
        $id = json_decode($this->authPost('api/v1/call-requests', $this->validPayload())->getJSON(), true)['data']['id'];

        $own = $this->withHeaders(['Authorization' => 'Bearer ' . $this->token])->get('api/v1/call-requests/' . $id);
        $own->assertStatus(200);
        $data = json_decode($own->getJSON(), true)['data'];
        $this->assertArrayNotHasKey('ai_score', $data);
        $this->assertArrayNotHasKey('event_cost', $data);
        $this->assertArrayNotHasKey('is_charged', $data);

        // 타인 토큰으로 조회 → 404
        $otherToken = (new JwtLibrary())->generateAccessToken($this->otherUserId);
        $this->withHeaders(['Authorization' => 'Bearer ' . $otherToken])
            ->get('api/v1/call-requests/' . $id)
            ->assertStatus(404);

        // 미존재 → 404
        $this->withHeaders(['Authorization' => 'Bearer ' . $this->token])
            ->get('api/v1/call-requests/999999')
            ->assertStatus(404);
    }

    /** [C7] 취소 — 미확인 건 */
    public function testCancelUnconfirmed(): void
    {
        $id = json_decode($this->authPost('api/v1/call-requests', $this->validPayload())->getJSON(), true)['data']['id'];

        $result = $this->withHeaders(['Authorization' => 'Bearer ' . $this->token])
            ->delete('api/v1/call-requests/' . $id);
        $result->assertStatus(200);

        $this->seeInDatabase('call_requests', ['id' => $id, 'is_delete' => 1]);
    }

    /** [C8] 취소 — 미확인 아님 → 409 */
    public function testCancelNonUnconfirmedConflicts(): void
    {
        $id = json_decode($this->authPost('api/v1/call-requests', $this->validPayload())->getJSON(), true)['data']['id'];
        // 병원이 확인(부재중=2)으로 상태 변경한 상황
        db_connect()->table('call_requests')->where('id', $id)->update(['status' => 2]);

        $result = $this->withHeaders(['Authorization' => 'Bearer ' . $this->token])
            ->delete('api/v1/call-requests/' . $id);
        $result->assertStatus(409);
        $this->assertSame('CANNOT_CANCEL', json_decode($result->getJSON(), true)['code']);
    }

    /** [C9] 취소 — 타인 건 404 */
    public function testCancelOthersReturns404(): void
    {
        $id = json_decode($this->authPost('api/v1/call-requests', $this->validPayload())->getJSON(), true)['data']['id'];

        $otherToken = (new JwtLibrary())->generateAccessToken($this->otherUserId);
        $this->withHeaders(['Authorization' => 'Bearer ' . $otherToken])
            ->delete('api/v1/call-requests/' . $id)
            ->assertStatus(404);
    }

    /** [C10] 토큰 없으면 401 */
    public function testRequiresAuth(): void
    {
        $this->withBodyFormat('json')
            ->post('api/v1/call-requests', $this->validPayload())
            ->assertStatus(401);
    }
}
