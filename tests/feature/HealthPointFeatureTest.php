<?php

use App\Libraries\Social\SocialProfile;
use App\Libraries\Social\SocialVerifierInterface;
use App\Models\HealthPointLogModel;
use App\Models\UserModel;
use App\Services\AppAuthService;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;
use CodeIgniter\Test\TestResponse;
use Config\Services;

/**
 * 헬스포인트 적립/차감 트리거 피처 테스트 (이슈 #114, SQLite3 인메모리 DB)
 *
 * 커버리지:
 *   [H1] 이메일 가입 → 500 적립 + signup 로그
 *   [H2] 소셜 가입 → 500 적립
 *   [H3] 후기 작성 → 100 적립 (가입 500 + 후기 100 = 600)
 *   [H4] 후기 삭제 → 적립분 회수 (다시 500)
 *   [H5] 후기 적립 멱등 — 같은 후기 재적립 없음
 *   [H6] 차감 성공 → 잔액 감소 + balance_after 스냅샷
 *   [H7] 잔액 부족 차감 → 422 INSUFFICIENT_POINT
 *   [H8] 잘못된 금액(0) 차감 → 422 VALIDATION_ERROR
 *   [H9] 차감 인증 필요 → 401
 *
 * @internal
 */
final class HealthPointFeatureTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use FeatureTestTrait;

    protected $migrate = true;
    protected $refresh = true;
    protected $namespace;
    private int $hospitalId = 0;

    protected function setUp(): void
    {
        parent::setUp();
        cache()->clean();

        $now = date('Y-m-d H:i:s');
        $db  = db_connect();
        $db->table('hospitals')->insert(['name' => '강남병원', 'type' => 1, 'status' => 'active', 'is_deleted' => 0, 'created_at' => $now, 'updated_at' => $now]);
        $this->hospitalId = (int) $db->insertID();
    }

    /**
     * 이메일 가입 후 access_token + user id 반환.
     *
     * @return array{token: string, id: int}
     */
    private function registerUser(string $email): array
    {
        $res = $this->withBodyFormat('json')->post('api/v1/auth/register', [
            'email' => $email, 'password' => 'password1234', 'username' => '사용자', 'where_from' => 2,
        ]);
        $res->assertStatus(201);
        $token = json_decode($res->getJSON(), true)['data']['access_token'];
        $id    = (int) model(UserModel::class)->where('email', $email)->first()['id'];

        return ['token' => $token, 'id' => $id];
    }

    /**
     * @param array<string, mixed> $body
     */
    private function authPost(string $token, string $uri, array $body = []): TestResponse
    {
        return $this->withHeaders(['Authorization' => 'Bearer ' . $token])->withBodyFormat('json')->post($uri, $body);
    }

    /**
     * [H1] 이메일 가입 적립
     */
    public function testSignupAwardsPoints(): void
    {
        $this->registerUser('h1@aicura.test');

        $this->seeInDatabase('users', ['email' => 'h1@aicura.test', 'health_point' => 500]);
        $this->seeInDatabase('health_point_logs', ['type' => 'signup', 'amount' => 500, 'balance_after' => 500]);
    }

    /**
     * [H2] 소셜 가입 적립 (이슈 #187: access_token 서버 검증 — 검증기는 uid 777111 반환하도록 주입)
     */
    public function testSocialSignupAwardsPoints(): void
    {
        $fake = new class () implements SocialVerifierInterface {
            public function verify(string $provider, string $accessToken): SocialProfile
            {
                return new SocialProfile(uid: '777111', username: '카카오');
            }
        };
        Services::injectMock('socialTokenVerifier', $fake);
        Services::injectMock('appAuthService', new AppAuthService(socialVerifier: $fake));

        $res = $this->withBodyFormat('json')->post('api/v1/auth/social', [
            'provider' => 'kakao', 'access_token' => 'valid-token', 'where_from' => 3,
        ]);
        $this->assertContains($res->response()->getStatusCode(), [200, 201]);

        $user = model(UserModel::class)->where('provider', 3)->where('uid', '777111')->first();
        $this->assertSame(500, (int) $user['health_point']);
    }

    /**
     * [H3] 후기 작성 적립
     */
    public function testReviewCreateAwardsPoints(): void
    {
        $u   = $this->registerUser('h3@aicura.test');
        $res = $this->authPost($u['token'], 'api/v1/boards', [
            'type' => 2, 'target_id' => $this->hospitalId, 'subject' => '좋아요', 'contents' => '친절합니다', 'rating' => 4.5,
        ]);
        $res->assertStatus(201);

        // 가입 500 + 후기 100
        $this->seeInDatabase('users', ['id' => $u['id'], 'health_point' => 600]);
        $this->seeInDatabase('health_point_logs', ['user_id' => $u['id'], 'type' => 'review', 'amount' => 100, 'balance_after' => 600]);
    }

    /**
     * [H4] 후기 삭제 회수
     */
    public function testReviewDeleteRevokesPoints(): void
    {
        $u   = $this->registerUser('h4@aicura.test');
        $res = $this->authPost($u['token'], 'api/v1/boards', [
            'type' => 2, 'target_id' => $this->hospitalId, 'subject' => '좋아요', 'contents' => '친절합니다', 'rating' => 4.5,
        ]);
        $boardId = json_decode($res->getJSON(), true)['data']['id'];

        $this->withHeaders(['Authorization' => 'Bearer ' . $u['token']])->delete('api/v1/boards/' . $boardId)->assertStatus(200);

        // 회수되어 다시 500
        $this->seeInDatabase('users', ['id' => $u['id'], 'health_point' => 500]);
        $this->seeInDatabase('health_point_logs', ['user_id' => $u['id'], 'type' => 'review_revoke', 'amount' => -100, 'balance_after' => 500]);
    }

    /**
     * [H5] 후기 적립 멱등 — 같은 후기 재적립 없음
     */
    public function testReviewAwardIsIdempotent(): void
    {
        $u   = $this->registerUser('h5@aicura.test');
        $res = $this->authPost($u['token'], 'api/v1/boards', [
            'type' => 2, 'target_id' => $this->hospitalId, 'subject' => '좋아요', 'contents' => '친절합니다', 'rating' => 4.5,
        ]);
        $boardId = (int) json_decode($res->getJSON(), true)['data']['id'];

        // 같은 후기로 적립을 한 번 더 시도해도 잔액·로그는 그대로
        $balance = service('healthPointService')->awardReview($u['id'], $boardId);

        $this->assertSame(600, $balance);
        $this->assertSame(1, model(HealthPointLogModel::class)
            ->where('user_id', $u['id'])->where('type', 'review')->countAllResults());
    }

    /**
     * [H6] 차감 성공
     */
    public function testRedeemSucceeds(): void
    {
        $u   = $this->registerUser('h6@aicura.test');
        $res = $this->authPost($u['token'], 'api/v1/me/health-point/redeem', ['amount' => 200, 'memo' => '쿠폰 교환']);
        $res->assertStatus(200);

        $this->assertSame(300, json_decode($res->getJSON(), true)['data']['balance']);
        $this->seeInDatabase('users', ['id' => $u['id'], 'health_point' => 300]);
        $this->seeInDatabase('health_point_logs', ['user_id' => $u['id'], 'type' => 'redeem', 'amount' => -200, 'balance_after' => 300, 'memo' => '쿠폰 교환']);
    }

    /**
     * [H7] 잔액 부족 차감
     */
    public function testRedeemInsufficientBalance(): void
    {
        $u   = $this->registerUser('h7@aicura.test');
        $res = $this->authPost($u['token'], 'api/v1/me/health-point/redeem', ['amount' => 99999]);

        $res->assertStatus(422);
        $this->assertSame('INSUFFICIENT_POINT', json_decode($res->getJSON(), true)['code']);
        // 잔액 변동 없음
        $this->seeInDatabase('users', ['id' => $u['id'], 'health_point' => 500]);
    }

    /**
     * [H8] 잘못된 금액(0) 차감
     */
    public function testRedeemInvalidAmount(): void
    {
        $u   = $this->registerUser('h8@aicura.test');
        $res = $this->authPost($u['token'], 'api/v1/me/health-point/redeem', ['amount' => 0]);

        $res->assertStatus(422);
        $this->assertSame('VALIDATION_ERROR', json_decode($res->getJSON(), true)['code']);
    }

    /**
     * [H9] 차감 인증 필요
     */
    public function testRedeemRequiresAuth(): void
    {
        $this->withBodyFormat('json')->post('api/v1/me/health-point/redeem', ['amount' => 100])->assertStatus(401);
    }
}
