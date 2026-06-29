<?php

use CodeIgniter\Security\Security;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;
use Config\Services;

/**
 * Admin 병원·진료과 관리 컨트롤러 테스트 (이슈 #113)
 *
 * 커버리지:
 *   - 인증 가드 (비로그인 redirect)
 *   - 병원 등록/수정 + 진료과 매핑 동기화
 *   - 병원 soft delete + 매핑 정리
 *   - 진료과 마스터 추가/수정/삭제
 *   - 매핑된 진료과 삭제 차단
 *   - 진료과 코드 유효성(형식·중복)
 *
 * @internal
 */
final class HospitalAdminControllerTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use FeatureTestTrait;

    protected $migrate   = true;
    protected $refresh   = true;
    protected $namespace = null;

    /** @var array<string, mixed> */
    private array $authSession = [];

    private int $dPlastic = 0;
    private int $dDerma   = 0;

    protected function setUp(): void
    {
        parent::setUp();
        cache()->clean();

        // CSRF verify 우회
        $security = $this->getMockBuilder(Security::class)
            ->setConstructorArgs([config('Security')])
            ->onlyMethods(['verify'])
            ->getMock();
        $security->method('verify')->willReturn(true);
        Services::injectMock('security', $security);

        $this->authSession = ['admin_user' => ['id' => 1, 'username' => 'admin', 'email' => 'admin@aicura.com']];

        $db  = db_connect();
        $now = date('Y-m-d H:i:s');
        $db->table('departments')->insert(['code' => 'plastic_surgery', 'name' => '성형외과', 'sort' => 1, 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now]);
        $this->dPlastic = (int) $db->insertID();
        $db->table('departments')->insert(['code' => 'dermatology', 'name' => '피부과', 'sort' => 2, 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now]);
        $this->dDerma = (int) $db->insertID();
    }

    // ── 인증 가드 ──────────────────────────────────────

    public function testIndexRedirectsWhenUnauthenticated(): void
    {
        $this->get('admin/hospitals')->assertRedirect();
        $this->get('admin/departments')->assertRedirect();
    }

    public function testIndexReturns200WithAuth(): void
    {
        $this->withSession($this->authSession)->get('admin/hospitals')->assertStatus(200);
        $this->withSession($this->authSession)->get('admin/departments')->assertStatus(200);
        $this->withSession($this->authSession)->get('admin/hospitals/new')->assertStatus(200);
    }

    // ── 병원 등록 + 진료과 매핑 ─────────────────────────

    public function testCreateHospitalWithDepartments(): void
    {
        $result = $this->withSession($this->authSession)->post('admin/hospitals', [
            'name'        => '강남성형외과',
            'type'        => 1,
            'status'      => 'active',
            'phone'       => '02-111-2222',
            'address'     => '서울 강남구',
            'departments' => [$this->dPlastic, $this->dDerma],
        ]);
        $result->assertRedirectTo('/admin/hospitals');

        $db  = db_connect();
        $row = $db->table('hospitals')->where('name', '강남성형외과')->get()->getRowArray();
        $this->assertIsArray($row);

        $count = $db->table('department_hospital')->where('hospital_id', (int) $row['id'])->countAllResults();
        $this->assertSame(2, $count);
    }

    public function testCreateHospitalValidationFails(): void
    {
        // name 누락 → redirect(검증 실패), 병원 미생성
        $this->withSession($this->authSession)->post('admin/hospitals', [
            'type' => 1, 'status' => 'active',
        ])->assertRedirect();

        $this->assertSame(0, db_connect()->table('hospitals')->countAllResults());
    }

    // ── 병원 수정 — 매핑 재동기화 ───────────────────────

    public function testUpdateHospitalResyncsDepartments(): void
    {
        $db  = db_connect();
        $now = date('Y-m-d H:i:s');
        $db->table('hospitals')->insert(['name' => '병원A', 'type' => 1, 'status' => 'active', 'is_deleted' => 0, 'created_at' => $now, 'updated_at' => $now]);
        $hid = (int) $db->insertID();
        $db->table('department_hospital')->insert(['hospital_id' => $hid, 'department_id' => $this->dPlastic]);

        // 성형외과 → 피부과 로 교체
        $this->withSession($this->authSession)->post('admin/hospitals/' . $hid, [
            'name'        => '병원A',
            'type'        => 1,
            'status'      => 'active',
            'departments' => [$this->dDerma],
        ])->assertRedirectTo('/admin/hospitals');

        $ids = $db->table('department_hospital')->where('hospital_id', $hid)
            ->get()->getResultArray();
        $this->assertCount(1, $ids);
        $this->assertSame($this->dDerma, (int) $ids[0]['department_id']);
    }

    // ── 병원 삭제 — soft delete + 매핑 정리 ─────────────

    public function testDeleteHospitalSoftDeletesAndClearsMappings(): void
    {
        $db  = db_connect();
        $now = date('Y-m-d H:i:s');
        $db->table('hospitals')->insert(['name' => '삭제대상', 'type' => 1, 'status' => 'active', 'is_deleted' => 0, 'created_at' => $now, 'updated_at' => $now]);
        $hid = (int) $db->insertID();
        $db->table('department_hospital')->insert(['hospital_id' => $hid, 'department_id' => $this->dPlastic]);

        $this->withSession($this->authSession)->post('admin/hospitals/' . $hid . '/delete')
            ->assertRedirectTo('/admin/hospitals');

        $row = $db->table('hospitals')->where('id', $hid)->get()->getRowArray();
        $this->assertSame(1, (int) $row['is_deleted']);
        $this->assertSame(0, $db->table('department_hospital')->where('hospital_id', $hid)->countAllResults());
    }

    // ── 진료과 마스터 CRUD ──────────────────────────────

    public function testCreateDepartment(): void
    {
        $this->withSession($this->authSession)->post('admin/departments', [
            'code' => 'dental', 'name' => '치과', 'sort' => 3, 'is_active' => '1',
        ])->assertRedirectTo('/admin/departments');

        $this->seeInDatabase('departments', ['code' => 'dental', 'name' => '치과']);
    }

    public function testCreateDepartmentRejectsInvalidCode(): void
    {
        // 대문자·공백 포함 → 형식 위반
        $this->withSession($this->authSession)->post('admin/departments', [
            'code' => 'Invalid Code', 'name' => '엑스',
        ])->assertRedirect();

        $this->dontSeeInDatabase('departments', ['name' => '엑스']);
    }

    public function testCreateDepartmentRejectsDuplicateCode(): void
    {
        $this->withSession($this->authSession)->post('admin/departments', [
            'code' => 'plastic_surgery', 'name' => '중복', 'is_active' => '1',
        ])->assertRedirect();

        $this->dontSeeInDatabase('departments', ['name' => '중복']);
    }

    public function testUpdateDepartment(): void
    {
        $this->withSession($this->authSession)->post('admin/departments/' . $this->dDerma, [
            'code' => 'dermatology', 'name' => '피부·미용', 'sort' => 5, 'is_active' => '0',
        ])->assertRedirectTo('/admin/departments');

        $this->seeInDatabase('departments', ['id' => $this->dDerma, 'name' => '피부·미용', 'is_active' => 0]);
    }

    public function testDeleteUnmappedDepartment(): void
    {
        $this->withSession($this->authSession)->post('admin/departments/' . $this->dDerma . '/delete')
            ->assertRedirectTo('/admin/departments');

        $this->dontSeeInDatabase('departments', ['id' => $this->dDerma]);
    }

    public function testDeleteMappedDepartmentBlocked(): void
    {
        $db  = db_connect();
        $now = date('Y-m-d H:i:s');
        $db->table('hospitals')->insert(['name' => '매핑병원', 'type' => 1, 'status' => 'active', 'is_deleted' => 0, 'created_at' => $now, 'updated_at' => $now]);
        $hid = (int) $db->insertID();
        $db->table('department_hospital')->insert(['hospital_id' => $hid, 'department_id' => $this->dPlastic]);

        $this->withSession($this->authSession)->post('admin/departments/' . $this->dPlastic . '/delete')
            ->assertRedirectTo('/admin/departments');

        // 매핑 존재 → 삭제되지 않음
        $this->seeInDatabase('departments', ['id' => $this->dPlastic]);
    }
}
