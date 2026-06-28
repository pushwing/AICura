<?php

use App\Libraries\JwtLibrary;
use App\Models\BoardModel;
use App\Models\UserModel;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;

/**
 * 외부(소비자) 앱 후기 커뮤니티 API 피처 테스트 (이슈 #102, SQLite3 인메모리 DB)
 *
 * 커버리지:
 *   [B1]  작성 → 201, 별점 요약 재집계·AI 큐 PENDING
 *   [B2]  비노출 대상 작성 → 404
 *   [B3]  목록 — 공개글, is_liked
 *   [B4]  상세 — 이미지·댓글·is_liked
 *   [B5]  수정 — 본인 200 / 타인 404
 *   [B6]  삭제 — 본인 soft delete + 요약 재집계
 *   [B7]  좋아요 토글 — like_count·is_liked
 *   [B8]  신고 — 1인 1회(멱등) complain_count
 *   [B9]  댓글 작성/목록/삭제 + comment_count
 *   [B10] 댓글 삭제 — 타인 404
 *   [B11] 이미지 서빙 라우트 (공개)
 *   [B12] 토큰 없으면 401
 *
 * @internal
 */
final class BoardApiFeatureTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use FeatureTestTrait;

    protected $migrate   = true;
    protected $refresh   = true;
    protected $namespace = null;

    private int $userId = 0;
    private int $otherId = 0;
    private string $token = '';
    private string $otherToken = '';
    private int $hospitalId = 0;

    protected function setUp(): void
    {
        parent::setUp();
        cache()->clean();

        $db  = db_connect();
        $now = date('Y-m-d H:i:s');

        $this->userId  = $this->insertUser('writer@aicura.test', '작성자');
        $this->otherId = $this->insertUser('other@aicura.test', '다른이');
        $this->token      = (new JwtLibrary())->generateAccessToken($this->userId);
        $this->otherToken = (new JwtLibrary())->generateAccessToken($this->otherId);

        $db->table('hospitals')->insert(['name' => '강남병원', 'type' => 1, 'status' => 'active', 'is_deleted' => 0, 'created_at' => $now, 'updated_at' => $now]);
        $this->hospitalId = (int) $db->insertID();
    }

    private function insertUser(string $email, string $name): int
    {
        $db  = db_connect();
        $now = date('Y-m-d H:i:s');
        $db->table('users')->insert(['email' => $email, 'username' => $name, 'user_type' => UserModel::TYPE_USER, 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now]);

        return (int) $db->insertID();
    }

    /** @param array<string, mixed> $body */
    private function authPost(string $token, string $uri, array $body = []): \CodeIgniter\Test\TestResponse
    {
        return $this->withHeaders(['Authorization' => 'Bearer ' . $token])->withBodyFormat('json')->post($uri, $body);
    }

    /** 후기 1건 작성 후 id 반환 */
    private function createReview(string $token = null): int
    {
        $res = $this->authPost($token ?? $this->token, 'api/v1/boards', [
            'type' => 2, 'target_id' => $this->hospitalId, 'subject' => '좋아요', 'contents' => '<b>친절</b>합니다', 'rating' => 4.5,
        ]);
        $res->assertStatus(201);

        return (int) json_decode($res->getJSON(), true)['data']['id'];
    }

    /** [B1] 작성 */
    public function testCreate(): void
    {
        $res = $this->authPost($this->token, 'api/v1/boards', [
            'type' => 2, 'target_id' => $this->hospitalId, 'subject' => '좋아요', 'contents' => '<b>친절</b>합니다', 'rating' => 4.5,
        ]);
        $res->assertStatus(201);
        $data = json_decode($res->getJSON(), true)['data'];

        $this->assertSame('친절합니다', $data['contents']); // 태그 제거
        $this->assertEqualsWithDelta(4.5, $data['rating'], 0.001);

        $this->seeInDatabase('boards', ['id' => $data['id'], 'user_id' => $this->userId, 'type' => 2, 'target_id' => $this->hospitalId, 'ai_status' => BoardModel::AI_STATUS_PENDING]);
        $this->seeInDatabase('board_summaries', ['type' => 2, 'target_id' => $this->hospitalId]);
    }

    /** [B2] 비노출 대상 */
    public function testCreateOnInvalidTarget(): void
    {
        $res = $this->authPost($this->token, 'api/v1/boards', ['type' => 2, 'target_id' => 999999, 'contents' => 'x', 'rating' => 3]);
        $res->assertStatus(404);
    }

    /** [B3] 목록 */
    public function testList(): void
    {
        $this->createReview();
        $res = $this->withHeaders(['Authorization' => 'Bearer ' . $this->token])->get('api/v1/boards?filter[type]=2&sort=latest');
        $res->assertStatus(200);
        $body = json_decode($res->getJSON(), true);
        $this->assertSame(1, $body['meta']['total']);
        $this->assertFalse($body['data'][0]['is_liked']);

        // 목록은 본문 전체 대신 발췌(excerpt)만 노출 (페이로드 최소화)
        $this->assertArrayHasKey('excerpt', $body['data'][0]);
        $this->assertArrayNotHasKey('contents', $body['data'][0]);
        $this->assertSame('친절합니다', $body['data'][0]['excerpt']);
    }

    /** [B4] 상세 */
    public function testDetail(): void
    {
        $id  = $this->createReview();
        $res = $this->withHeaders(['Authorization' => 'Bearer ' . $this->token])->get('api/v1/boards/' . $id);
        $res->assertStatus(200);
        $data = json_decode($res->getJSON(), true)['data'];
        $this->assertArrayHasKey('images', $data);
        $this->assertArrayHasKey('comments', $data);
        $this->assertFalse($data['is_liked']);
    }

    /** [B5] 수정 */
    public function testUpdate(): void
    {
        $id = $this->createReview();

        $ok = $this->withHeaders(['Authorization' => 'Bearer ' . $this->token])->withBodyFormat('json')
            ->patch('api/v1/boards/' . $id, ['subject' => '수정됨', 'rating' => 3.0]);
        $ok->assertStatus(200);
        $this->assertSame('수정됨', json_decode($ok->getJSON(), true)['data']['subject']);

        // 타인 수정 → 404
        $this->withHeaders(['Authorization' => 'Bearer ' . $this->otherToken])->withBodyFormat('json')
            ->patch('api/v1/boards/' . $id, ['subject' => '침범'])->assertStatus(404);
    }

    /** [B6] 삭제 */
    public function testDelete(): void
    {
        $id = $this->createReview();
        $this->withHeaders(['Authorization' => 'Bearer ' . $this->token])->delete('api/v1/boards/' . $id)->assertStatus(200);
        $this->seeInDatabase('boards', ['id' => $id, 'is_delete' => BoardModel::DELETE_FULL]);
    }

    /** [B7] 좋아요 토글 */
    public function testLikeToggle(): void
    {
        $id = $this->createReview();

        $on = $this->authPost($this->otherToken, 'api/v1/boards/' . $id . '/like');
        $on->assertStatus(200);
        $this->assertTrue(json_decode($on->getJSON(), true)['data']['liked']);
        $this->seeInDatabase('boards', ['id' => $id, 'like_count' => 1]);

        $off = $this->authPost($this->otherToken, 'api/v1/boards/' . $id . '/like');
        $this->assertFalse(json_decode($off->getJSON(), true)['data']['liked']);
        $this->seeInDatabase('boards', ['id' => $id, 'like_count' => 0]);
    }

    /** [B8] 신고 멱등 */
    public function testReportIdempotent(): void
    {
        $id = $this->createReview();

        $first = $this->authPost($this->otherToken, 'api/v1/boards/' . $id . '/report');
        $this->assertTrue(json_decode($first->getJSON(), true)['data']['reported']);
        $this->seeInDatabase('boards', ['id' => $id, 'complain_count' => 1]);

        $second = $this->authPost($this->otherToken, 'api/v1/boards/' . $id . '/report');
        $this->assertFalse(json_decode($second->getJSON(), true)['data']['reported']);
        $this->seeInDatabase('boards', ['id' => $id, 'complain_count' => 1]); // 증가 안 함
    }

    /** [B9] 댓글 작성/목록/삭제 */
    public function testComments(): void
    {
        $id = $this->createReview();

        $c = $this->authPost($this->otherToken, 'api/v1/boards/' . $id . '/comments', ['contents' => '저도요']);
        $c->assertStatus(201);
        $cid = (int) json_decode($c->getJSON(), true)['data']['id'];
        $this->seeInDatabase('boards', ['id' => $id, 'comment_count' => 1]);

        $list = $this->withHeaders(['Authorization' => 'Bearer ' . $this->token])->get('api/v1/boards/' . $id . '/comments');
        $list->assertStatus(200);
        $this->assertSame(1, json_decode($list->getJSON(), true)['meta']['total']);

        // 본인 삭제
        $this->withHeaders(['Authorization' => 'Bearer ' . $this->otherToken])->delete('api/v1/boards/' . $id . '/comments/' . $cid)->assertStatus(200);
        $this->seeInDatabase('boards', ['id' => $id, 'comment_count' => 0]);
    }

    /** [B10] 댓글 타인 삭제 404 */
    public function testCommentDeleteByOther(): void
    {
        $id  = $this->createReview();
        $c   = $this->authPost($this->otherToken, 'api/v1/boards/' . $id . '/comments', ['contents' => '저도요']);
        $cid = (int) json_decode($c->getJSON(), true)['data']['id'];

        // 작성자(다른 사람)가 삭제 시도 → 404
        $this->withHeaders(['Authorization' => 'Bearer ' . $this->token])->delete('api/v1/boards/' . $id . '/comments/' . $cid)->assertStatus(404);
    }

    /** [B11] 이미지 서빙 (공개) */
    public function testServeImage(): void
    {
        $dir = rtrim(WRITEPATH, '/\\') . '/uploads/boards/';
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }
        // 1x1 PNG
        $png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+M8AAAMBAQDJ/pLvAAAAAElFTkSuQmCC');
        file_put_contents($dir . 'serve_test.png', $png);

        $res = $this->get('api/v1/uploads/images/serve_test.png');
        $res->assertStatus(200);
        $res->assertHeader('Content-Type', 'image/png');

        // 미존재 404
        $this->get('api/v1/uploads/images/nope.png')->assertStatus(404);

        @unlink($dir . 'serve_test.png');
    }

    /** [B12] 토큰 없으면 401 */
    public function testRequiresAuth(): void
    {
        $this->get('api/v1/boards')->assertStatus(401);
    }
}
