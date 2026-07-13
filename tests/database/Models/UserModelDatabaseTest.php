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
    protected $namespace;

    /**
     * @var list<int>
     */
    private array $insertedIds = [];

    protected function setUp(): void
    {
        parent::setUp();

        $db  = db_connect();
        $now = date('Y-m-d H:i:s');

        $fixtures = [
            [
                'email'      => '__user_general__@test.invalid',
                'user_type'  => UserModel::TYPE_USER,
                'is_dormant' => 1,  // 활성
                'is_active'  => 1,
                'username'   => '__일반사용자__',
                'phone'      => '010-1111-0001',
            ],
            [
                'email'      => '__user_admin__@test.invalid',
                'user_type'  => UserModel::TYPE_ADMIN,
                'is_dormant' => 1,
                'is_active'  => 1,
                'username'   => '__관리자__',
                'phone'      => '010-1111-0002',
            ],
            [
                'email'      => '__user_dormant__@test.invalid',
                'user_type'  => UserModel::TYPE_USER,
                'is_dormant' => 0,  // 휴면
                'is_active'  => 1,
                'username'   => '__휴면사용자__',
            ],
            [
                'email'      => '__user_deleted__@test.invalid',
                'user_type'  => UserModel::TYPE_USER,
                'is_dormant' => 1,
                'is_active'  => 0,
                'username'   => '__탈퇴사용자__',
                'deleted_at' => $now,
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
        if (! empty($this->insertedIds)) {
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

    // ── is_agency 필터 (대행사 탭) ─────────────────────

    public function testGetListIsAgencyFilterReturnsOnlyAgencyAccounts(): void
    {
        $db  = db_connect();
        $now = date('Y-m-d H:i:s');
        $db->table('users')->insert([
            'email'             => '__user_agency__@test.invalid',
            'user_type'         => UserModel::TYPE_USER,
            'is_agency_account' => 1,
            'is_dormant'        => 1,
            'is_active'         => 1,
            'username'          => '__대행사__',
            'created_at'        => $now,
            'updated_at'        => $now,
        ]);
        $this->insertedIds[] = (int) $db->insertID();

        $result = model(UserModel::class)->getList(['is_agency' => 1]);

        $emails = array_column($result['list'], 'email');
        $this->assertContains('__user_agency__@test.invalid', $emails);
        // 일반/관리자 계정은 제외
        $this->assertNotContains('__user_general__@test.invalid', $emails);
    }

    public function testGetListGeneralTabExcludesAgencyAccounts(): void
    {
        $db  = db_connect();
        $now = date('Y-m-d H:i:s');
        // user_type=1(일반)이지만 대행사 플래그가 켜진 계정
        $db->table('users')->insert([
            'email'             => '__user_type1_agency__@test.invalid',
            'user_type'         => UserModel::TYPE_USER,
            'is_agency_account' => 1,
            'is_dormant'        => 1,
            'is_active'         => 1,
            'username'          => '__타입1대행사__',
            'created_at'        => $now,
            'updated_at'        => $now,
        ]);
        $this->insertedIds[] = (int) $db->insertID();

        // 일반 사용자 탭 = user_type IN [1], 대행사 제외
        $result = model(UserModel::class)->getList(['user_types' => [UserModel::TYPE_USER]]);

        $emails = array_column($result['list'], 'email');
        $this->assertNotContains('__user_type1_agency__@test.invalid', $emails);
        // 순수 일반 사용자는 그대로 노출
        $this->assertContains('__user_general__@test.invalid', $emails);
    }

    // ── createHospitalOwner / emailExists ─────────────

    public function testCreateHospitalOwnerCreatesActiveHospitalAccount(): void
    {
        $model = model(UserModel::class);

        $id = $model->createHospitalOwner('__owner_new__@test.invalid', 'secret12345', '__오너__', '010-2222-3333');
        $this->assertIsInt($id);
        $this->insertedIds[] = $id;

        $user = $model->find($id);
        $this->assertNotNull($user);
        $this->assertSame(UserModel::TYPE_HOSPITAL_AD, (int) $user['user_type']);
        $this->assertSame(1, (int) $user['is_active']);
    }

    public function testCreateHospitalOwnerHashesPassword(): void
    {
        $model = model(UserModel::class);

        $id = $model->createHospitalOwner('__owner_hash__@test.invalid', 'secret12345', null, null);
        $this->assertIsInt($id);
        $this->insertedIds[] = $id;

        // $hidden 우회 조회로 password 해시 확인
        $row = $model->findPortalForAuth('__owner_hash__@test.invalid');
        $this->assertNotNull($row);
        $this->assertTrue(password_verify('secret12345', (string) $row['password']));
    }

    public function testEmailExistsTrueForInsertedUser(): void
    {
        $this->assertTrue(model(UserModel::class)->emailExists('__user_general__@test.invalid'));
    }

    public function testEmailExistsFalseForUnknownEmail(): void
    {
        $this->assertFalse(model(UserModel::class)->emailExists('__nobody_xyz__@test.invalid'));
    }
}
