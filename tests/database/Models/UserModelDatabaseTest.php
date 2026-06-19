<?php

use App\Models\UserModel;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;

/**
 * UserModel DB 통합 테스트
 *
 * @internal
 */
final class UserModelDatabaseTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $migrate     = true;
    protected $migrateOnce = true;
    protected $refresh     = false;
    protected $namespace   = null;

    /** @var list<int> */
    private array $insertedIds = [];

    protected function setUp(): void
    {
        parent::setUp();

        $db  = db_connect();
        $now = date('Y-m-d H:i:s');

        $fixtures = [
            [
                'email'     => '__user_general__@test.invalid',
                'user_type' => UserModel::TYPE_USER,
                'is_dormant'=> 1,  // 활성
                'is_active' => 1,
                'username'  => '__일반사용자__',
                'phone'     => '010-1111-0001',
            ],
            [
                'email'     => '__user_admin__@test.invalid',
                'user_type' => UserModel::TYPE_ADMIN,
                'is_dormant'=> 1,
                'is_active' => 1,
                'username'  => '__관리자__',
                'phone'     => '010-1111-0002',
            ],
            [
                'email'     => '__user_dormant__@test.invalid',
                'user_type' => UserModel::TYPE_USER,
                'is_dormant'=> 0,  // 휴면
                'is_active' => 1,
                'username'  => '__휴면사용자__',
            ],
            [
                'email'     => '__user_deleted__@test.invalid',
                'user_type' => UserModel::TYPE_USER,
                'is_dormant'=> 1,
                'is_active' => 0,
                'username'  => '__탈퇴사용자__',
                'deleted_at'=> $now,
            ],
        ];

        foreach ($fixtures as $row) {
            $row['created_at'] = $now;
            $row['updated_at'] = $now;
            $db->table('users')->insert($row);
            $this->insertedIds[] = (int) $db->insertID();
        }
    }

    protected function tearDown(): void
    {
        if (!empty($this->insertedIds)) {
            db_connect()->table('users')
                ->whereIn('id', $this->insertedIds)
                ->delete();
        }

        parent::tearDown();
    }

    // ── getList() 기본 구조 ────────────────────────────

    public function testGetListReturnsRequiredKeys(): void
    {
        $result = model(UserModel::class)->getList([]);

        $this->assertArrayHasKey('list', $result);
        $this->assertArrayHasKey('total', $result);
    }

    public function testGetListListIsArray(): void
    {
        $result = model(UserModel::class)->getList([]);

        $this->assertIsArray($result['list']);
    }

    public function testGetListTotalIsInteger(): void
    {
        $result = model(UserModel::class)->getList([]);

        $this->assertIsInt($result['total']);
    }

    // ── soft delete 필터 ──────────────────────────────

    public function testGetListExcludesSoftDeletedUsers(): void
    {
        $result = model(UserModel::class)->getList([]);

        $emails = array_column($result['list'], 'email');
        $this->assertNotContains('__user_deleted__@test.invalid', $emails);
    }

    public function testGetListIncludesNonDeletedUsers(): void
    {
        $result = model(UserModel::class)->getList([]);

        $emails = array_column($result['list'], 'email');
        $this->assertContains('__user_general__@test.invalid', $emails);
    }

    // ── user_types 필터 ───────────────────────────────

    public function testGetListFiltersByUserTypesArray(): void
    {
        $result = model(UserModel::class)->getList([
            'user_types' => [UserModel::TYPE_ADMIN],
        ]);

        $this->assertNotEmpty($result['list']);
        foreach ($result['list'] as $row) {
            $this->assertSame(UserModel::TYPE_ADMIN, (int) $row['user_type']);
        }
    }

    public function testGetListFiltersBySingleUserType(): void
    {
        $result = model(UserModel::class)->getList([
            'user_type' => UserModel::TYPE_USER,
        ]);

        foreach ($result['list'] as $row) {
            $this->assertSame(UserModel::TYPE_USER, (int) $row['user_type']);
        }
    }

    // ── is_dormant 필터 ───────────────────────────────

    public function testGetListFiltersByIsDormantActive(): void
    {
        $result = model(UserModel::class)->getList([
            'is_dormant' => '1',  // 활성
        ]);

        foreach ($result['list'] as $row) {
            $this->assertSame(1, (int) $row['is_dormant']);
        }
    }

    public function testGetListFiltersByIsDormantDormant(): void
    {
        $result = model(UserModel::class)->getList([
            'is_dormant' => '0',  // 휴면
        ]);

        $this->assertNotEmpty($result['list']);
        foreach ($result['list'] as $row) {
            $this->assertSame(0, (int) $row['is_dormant']);
        }
    }

    public function testGetListIgnoresEmptyIsDormant(): void
    {
        $resultAll    = model(UserModel::class)->getList([]);
        $resultFilter = model(UserModel::class)->getList(['is_dormant' => '']);

        $this->assertSame($resultAll['total'], $resultFilter['total']);
    }

    // ── search_word 필터 ──────────────────────────────

    public function testGetListSearchByEmail(): void
    {
        $result = model(UserModel::class)->getList([
            'search_word' => '__user_general__',
        ]);

        $this->assertNotEmpty($result['list']);
        $emails = array_column($result['list'], 'email');
        $this->assertContains('__user_general__@test.invalid', $emails);
    }

    public function testGetListSearchByUsername(): void
    {
        $result = model(UserModel::class)->getList([
            'search_word' => '__관리자__',
        ]);

        $this->assertNotEmpty($result['list']);
        $usernames = array_column($result['list'], 'username');
        $this->assertContains('__관리자__', $usernames);
    }

    public function testGetListSearchByPhone(): void
    {
        $result = model(UserModel::class)->getList([
            'search_word' => '010-1111-0001',
        ]);

        $this->assertNotEmpty($result['list']);
        $phones = array_column($result['list'], 'phone');
        $this->assertContains('010-1111-0001', $phones);
    }

    public function testGetListSearchNoMatchReturnsEmpty(): void
    {
        $result = model(UserModel::class)->getList([
            'search_word' => '__존재하지않는검색어_xyz__',
        ]);

        $this->assertEmpty($result['list']);
        $this->assertSame(0, $result['total']);
    }

    // ── total 정합성 ─────────────────────────────────

    public function testGetListTotalMatchesListCountForSmallDataset(): void
    {
        $result = model(UserModel::class)->getList([
            'search_word' => '__user_',
        ]);

        // soft deleted 제외 3건이 검색됨
        $this->assertSame(count($result['list']), $result['total']);
    }
}
