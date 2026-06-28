<?php

use App\Libraries\JwtLibrary;
use App\Models\BookingModel;
use App\Models\UserModel;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;

/**
 * 외부(소비자) 앱 예약 API 피처 테스트 (이슈 #101, SQLite3 인메모리 DB)
 *
 * 커버리지:
 *   [K1]  생성 → 201, status 대기
 *   [K2]  비노출 병원 → 404
 *   [K3]  상세 — 본인 200 / 타인 404
 *   [K4]  변경 — book_date·name
 *   [K5]  취소 → status=2, 재취소 409
 *   [K6]  토큰 없으면 401
 *
 * @internal
 */
final class BookingApiFeatureTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use FeatureTestTrait;

    protected $migrate   = true;
    protected $refresh   = true;
    protected $namespace = null;

    private int $userId = 0;
    private string $token = '';
    private string $otherToken = '';
    private int $hospitalId = 0;

    protected function setUp(): void
    {
        parent::setUp();
        cache()->clean();

        $db  = db_connect();
        $now = date('Y-m-d H:i:s');

        $this->userId = $this->insertUser('booker@aicura.test');
        $this->token  = (new JwtLibrary())->generateAccessToken($this->userId);
        $this->otherToken = (new JwtLibrary())->generateAccessToken($this->insertUser('other@aicura.test'));

        $db->table('hospitals')->insert(['name' => '강남병원', 'type' => 1, 'status' => 'active', 'is_deleted' => 0, 'created_at' => $now, 'updated_at' => $now]);
        $this->hospitalId = (int) $db->insertID();
    }

    private function insertUser(string $email): int
    {
        $db  = db_connect();
        $now = date('Y-m-d H:i:s');
        $db->table('users')->insert(['email' => $email, 'user_type' => UserModel::TYPE_USER, 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now]);

        return (int) $db->insertID();
    }

    /** @param array<string, mixed> $body */
    private function authReq(string $method, string $uri, array $body = []): \CodeIgniter\Test\TestResponse
    {
        return $this->withHeaders(['Authorization' => 'Bearer ' . $this->token])->withBodyFormat('json')->call($method, $uri, $body);
    }

    private function createBooking(): int
    {
        $res = $this->authReq('post', 'api/v1/bookings', [
            'hospital_id' => $this->hospitalId, 'name' => '홍길동', 'phone' => '01012345678', 'book_date' => '2026-07-01 14:00:00',
        ]);
        $res->assertStatus(201);

        return (int) json_decode($res->getJSON(), true)['data']['id'];
    }

    /** [K1] 생성 */
    public function testCreate(): void
    {
        $res = $this->authReq('post', 'api/v1/bookings', [
            'hospital_id' => $this->hospitalId, 'name' => '홍길동', 'phone' => '01012345678', 'book_date' => '2026-07-01 14:00:00',
        ]);
        $res->assertStatus(201);
        $data = json_decode($res->getJSON(), true)['data'];

        $this->assertSame('대기', $data['status_label']);
        $this->assertSame('강남병원', $data['hospital_name']);
        $this->seeInDatabase('bookings', ['id' => $data['id'], 'user_id' => $this->userId, 'status' => BookingModel::STATUS_PENDING]);
    }

    /** [K2] 비노출 병원 */
    public function testCreateOnInvalidHospital(): void
    {
        $this->authReq('post', 'api/v1/bookings', ['hospital_id' => 999999])->assertStatus(404);
    }

    /** [K3] 상세 — 소유권 */
    public function testDetailOwnership(): void
    {
        $id = $this->createBooking();
        $this->withHeaders(['Authorization' => 'Bearer ' . $this->token])->get('api/v1/bookings/' . $id)->assertStatus(200);
        $this->withHeaders(['Authorization' => 'Bearer ' . $this->otherToken])->get('api/v1/bookings/' . $id)->assertStatus(404);
    }

    /** [K4] 변경 */
    public function testUpdate(): void
    {
        $id  = $this->createBooking();
        $res = $this->authReq('patch', 'api/v1/bookings/' . $id, ['name' => '김철수']);
        $res->assertStatus(200);
        $this->assertSame('김철수', json_decode($res->getJSON(), true)['data']['name']);
    }

    /** [K5] 취소 + 재취소 409 */
    public function testCancel(): void
    {
        $id = $this->createBooking();

        $this->withHeaders(['Authorization' => 'Bearer ' . $this->token])->delete('api/v1/bookings/' . $id)->assertStatus(200);
        $this->seeInDatabase('bookings', ['id' => $id, 'status' => BookingModel::STATUS_CANCELLED]);

        $again = $this->withHeaders(['Authorization' => 'Bearer ' . $this->token])->delete('api/v1/bookings/' . $id);
        $again->assertStatus(409);
        $this->assertSame('ALREADY_CANCELLED', json_decode($again->getJSON(), true)['code']);
    }

    /** [K6] 토큰 없으면 401 */
    public function testRequiresAuth(): void
    {
        $this->get('api/v1/bookings/1')->assertStatus(401);
    }
}
