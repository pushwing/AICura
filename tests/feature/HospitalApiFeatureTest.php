<?php

use App\Libraries\JwtLibrary;
use App\Models\UserModel;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;

/**
 * 외부(소비자) 앱 병원 API 피처 테스트 (이슈 #99, SQLite3 인메모리 DB)
 *
 * 커버리지:
 *   [H1]  목록 — 활성 병원만, 별점·is_liked 포함
 *   [H2]  목록 — 지역(주소) 필터
 *   [H3]  상세 — 별점 요약·후기 수, 비활성/미존재 404
 *   [H4]  소속 이벤트 — 해당 병원 노출 이벤트만
 *   [H5]  후기 — 병원(type=2) 공개 후기만, 비밀글 제외
 *   [H6]  찜 토글 — 추가/해제 + 목록 is_liked 반영, 미존재 병원 404
 *   [H7]  토큰 없으면 401
 *
 * @internal
 */
final class HospitalApiFeatureTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use FeatureTestTrait;

    protected $migrate   = true;
    protected $refresh   = true;
    protected $namespace = null;

    private int $userId = 0;
    private string $token = '';
    private int $h1 = 0; // 활성 (서울)
    private int $h2 = 0; // 활성 (부산)
    private int $hInactive = 0;

    protected function setUp(): void
    {
        parent::setUp();
        cache()->clean();

        $db  = db_connect();
        $now = date('Y-m-d H:i:s');

        $db->table('users')->insert([
            'email' => 'huser@aicura.test', 'user_type' => UserModel::TYPE_USER, 'is_active' => 1,
            'created_at' => $now, 'updated_at' => $now,
        ]);
        $this->userId = (int) $db->insertID();
        $this->token  = (new JwtLibrary())->generateAccessToken($this->userId);

        $this->h1        = $this->insertHospital('강남성형외과', '서울 강남구', 'active');
        $this->h2        = $this->insertHospital('부산피부과', '부산 해운대구', 'active');
        $this->hInactive = $this->insertHospital('폐업의원', '서울 종로구', 'inactive');

        // 진료과 마스터 + 매핑: h1 = 성형외과·피부과(다진료과), h2 = 피부과
        $dPlastic = $this->insertDepartment('plastic_surgery', '성형외과');
        $dDerma   = $this->insertDepartment('dermatology', '피부과');
        $this->mapDepartment($this->h1, $dPlastic);
        $this->mapDepartment($this->h1, $dDerma);
        $this->mapDepartment($this->h2, $dDerma);

        // h1 별점 요약 (board_summaries type=2)
        $db->table('board_summaries')->insert([
            'type' => 2, 'target_id' => $this->h1, 'rate_sum' => 4.5, 'rate1' => 4.0, 'rate2' => 5.0, 'rate3' => 4.5,
        ]);

        // h1 소속 노출 이벤트 + 다른 병원 이벤트
        $this->insertCampaign($this->h1, '강남 리프팅');
        $this->insertCampaign($this->h2, '부산 보톡스');

        // h1 후기 (type=2): 공개 1 + 비밀 1
        $this->insertReview($this->h1, '만족해요', 0);
        $this->insertReview($this->h1, '비밀후기', 1);
        // 이벤트 후기(type=1)는 병원 후기에 섞이면 안 됨
        $db->table('boards')->insert([
            'user_id' => $this->userId, 'type' => 1, 'target_id' => $this->h1, 'subject' => '이벤트후기',
            'is_delete' => 0, 'is_secret' => 0, 'is_list' => 1, 'created_at' => $now, 'updated_at' => $now,
        ]);
    }

    private function insertHospital(string $name, string $address, string $status): int
    {
        $db  = db_connect();
        $now = date('Y-m-d H:i:s');
        $db->table('hospitals')->insert([
            'name' => $name, 'type' => 1, 'address' => $address, 'phone' => '02-000-0000',
            'status' => $status, 'is_deleted' => 0, 'created_at' => $now, 'updated_at' => $now,
        ]);

        return (int) $db->insertID();
    }

    private function insertDepartment(string $code, string $name): int
    {
        $db  = db_connect();
        $now = date('Y-m-d H:i:s');
        $db->table('departments')->insert([
            'code' => $code, 'name' => $name, 'sort' => 0, 'is_active' => 1,
            'created_at' => $now, 'updated_at' => $now,
        ]);

        return (int) $db->insertID();
    }

    private function mapDepartment(int $hospitalId, int $departmentId): void
    {
        db_connect()->table('department_hospital')->insert([
            'hospital_id' => $hospitalId, 'department_id' => $departmentId,
        ]);
    }

    private function insertCampaign(int $hospitalId, string $title): int
    {
        $db  = db_connect();
        $now = date('Y-m-d H:i:s');
        $db->table('campaigns')->insert([
            'ad_title' => $title, 'hospital_id' => $hospitalId, 'status' => 'active',
            'review_status' => 'approved', // 검수완료 — 노출 조건 (이슈 #137)
            'exposure' => 1, 'is_deleted' => 0, 'ad_type' => 1, 'cost_type' => 1,
            'created_at' => $now, 'updated_at' => $now,
        ]);

        return (int) $db->insertID();
    }

    private function insertReview(int $hospitalId, string $subject, int $secret): void
    {
        $db  = db_connect();
        $now = date('Y-m-d H:i:s');
        $db->table('boards')->insert([
            'user_id' => $this->userId, 'type' => 2, 'target_id' => $hospitalId, 'subject' => $subject,
            'contents' => '후기 내용', 'rate_sum' => 4.5, 'like_count' => 3,
            'is_delete' => 0, 'is_secret' => $secret, 'is_list' => 1, 'created_at' => $now, 'updated_at' => $now,
        ]);
    }

    /** @return array<string, mixed> */
    private function authGet(string $uri): array
    {
        $result = $this->withHeaders(['Authorization' => 'Bearer ' . $this->token])->get($uri);
        $result->assertStatus(200);

        return json_decode($result->getJSON(), true);
    }

    /** [H1] 목록 */
    public function testListReturnsActiveHospitals(): void
    {
        $body = $this->authGet('api/v1/hospitals');

        $this->assertSame(2, $body['meta']['total']); // 비활성 제외
        $byId = [];
        foreach ($body['data'] as $h) {
            $byId[$h['id']] = $h;
        }
        $this->assertEqualsWithDelta(4.5, $byId[$this->h1]['rating'], 0.001);
        $this->assertEqualsWithDelta(0.0, $byId[$this->h2]['rating'], 0.001); // 요약 없음 → 0
        $this->assertFalse($byId[$this->h1]['is_liked']);
        $this->assertArrayNotHasKey('is_deleted', $byId[$this->h1]);
    }

    /** [H2] 지역 필터 */
    public function testListFilterByRegion(): void
    {
        $body = $this->authGet('api/v1/hospitals?filter[region]=부산');

        $this->assertSame(1, $body['meta']['total']);
        $this->assertSame($this->h2, $body['data'][0]['id']);
    }

    /** [H8] 진료과 필터 + 응답 departments 배열 */
    public function testListFilterByDepartment(): void
    {
        // 성형외과 → h1 만 (다진료과 병원도 단일 코드로 매칭, 행 중복 없음)
        $body = $this->authGet('api/v1/hospitals?filter[department]=plastic_surgery');
        $this->assertSame(1, $body['meta']['total']);
        $this->assertSame($this->h1, $body['data'][0]['id']);

        // 피부과 → h1·h2 둘 다
        $body = $this->authGet('api/v1/hospitals?filter[department]=dermatology');
        $this->assertSame(2, $body['meta']['total']);

        // 응답에 병원별 진료과 배열 포함 (h1 = 2개)
        $byId = [];
        foreach ($body['data'] as $h) {
            $byId[$h['id']] = $h;
        }
        $codes = array_column($byId[$this->h1]['departments'], 'code');
        sort($codes);
        $this->assertSame(['dermatology', 'plastic_surgery'], $codes);

        // 미존재 코드 → 빈 결과
        $body = $this->authGet('api/v1/hospitals?filter[department]=unknown_code');
        $this->assertSame(0, $body['meta']['total']);
    }

    /** [H3] 상세 */
    public function testDetail(): void
    {
        $body = $this->authGet('api/v1/hospitals/' . $this->h1);
        $this->assertSame('강남성형외과', $body['data']['name']);
        $this->assertEqualsWithDelta(4.5, $body['data']['review_summary']['rating'], 0.001);
        $this->assertSame(1, $body['data']['review_summary']['count']); // 공개 후기 1건
        $this->assertCount(2, $body['data']['departments']); // 성형외과·피부과

        // 비활성 404
        $this->withHeaders(['Authorization' => 'Bearer ' . $this->token])
            ->get('api/v1/hospitals/' . $this->hInactive)
            ->assertStatus(404);
        // 미존재 404
        $this->withHeaders(['Authorization' => 'Bearer ' . $this->token])
            ->get('api/v1/hospitals/999999')
            ->assertStatus(404);
    }

    /** [H4] 소속 이벤트 */
    public function testHospitalCampaigns(): void
    {
        $body = $this->authGet('api/v1/hospitals/' . $this->h1 . '/campaigns');

        $this->assertSame(1, $body['meta']['total']);
        $this->assertSame('강남 리프팅', $body['data'][0]['ad_title']);
        $this->assertSame($this->h1, $body['data'][0]['hospital_id']);
    }

    /** [H5] 후기 — 병원 공개 후기만 */
    public function testHospitalReviews(): void
    {
        $body = $this->authGet('api/v1/hospitals/' . $this->h1 . '/reviews');

        $this->assertSame(1, $body['meta']['total']); // 비밀글·이벤트후기 제외
        $this->assertSame('만족해요', $body['data'][0]['subject']);
        $this->assertEqualsWithDelta(4.5, $body['data'][0]['rating'], 0.001);
    }

    /** [H6] 찜 토글 + 목록 반영 */
    public function testLikeToggle(): void
    {
        $first = $this->withHeaders(['Authorization' => 'Bearer ' . $this->token])
            ->post('api/v1/hospitals/' . $this->h1 . '/like');
        $first->assertStatus(200);
        $this->assertTrue(json_decode($first->getJSON(), true)['data']['liked']);

        $this->seeInDatabase('favorites', [
            'user_id' => $this->userId, 'target_type' => 'hospital', 'target_id' => $this->h1,
        ]);

        // 목록 is_liked 반영
        $body = $this->authGet('api/v1/hospitals');
        $liked = [];
        foreach ($body['data'] as $h) {
            $liked[$h['id']] = $h['is_liked'];
        }
        $this->assertTrue($liked[$this->h1]);
        $this->assertFalse($liked[$this->h2]);

        // 해제
        $second = $this->withHeaders(['Authorization' => 'Bearer ' . $this->token])
            ->post('api/v1/hospitals/' . $this->h1 . '/like');
        $this->assertFalse(json_decode($second->getJSON(), true)['data']['liked']);

        // 미존재 병원 404
        $this->withHeaders(['Authorization' => 'Bearer ' . $this->token])
            ->post('api/v1/hospitals/999999/like')
            ->assertStatus(404);
    }

    /** [H7] 조회는 비로그인 허용(200), 찜은 로그인 필요(401) */
    public function testAuthPolicy(): void
    {
        $this->get('api/v1/hospitals')->assertStatus(200);
        $this->get('api/v1/hospitals/' . $this->h1)->assertStatus(200);
        $this->post('api/v1/hospitals/' . $this->h1 . '/like')->assertStatus(401);
    }
}
