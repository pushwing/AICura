<?php

use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\Security\Security;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;
use Config\Services;

/**
 * @internal
 */
final class AdvertiserControllerTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use FeatureTestTrait;

    protected $migrate     = true;
    protected $migrateOnce = true;
    protected $refresh     = false;
    protected $namespace;
    private int $hospitalId   = 0;
    private int $advertiserId = 0;

    /**
     * @var array<string, mixed>
     */
    private array $authSession = [];

    protected function setUp(): void
    {
        parent::setUp();

        $security = $this->getMockBuilder(Security::class)
            ->setConstructorArgs([config('Security')])
            ->onlyMethods(['verify'])
            ->getMock();
        $security->method('verify')->willReturn(true);
        Services::injectMock('security', $security);

        $db  = db_connect();
        $now = date('Y-m-d H:i:s');

        $db->table('hospitals')->insert([
            'name'       => '__ctrl_test_hospital__',
            'type'       => 1,
            'status'     => 'active',
            'is_deleted' => 0,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        $this->hospitalId = (int) $db->insertID();

        $db->table('advertisers')->insert([
            'hospital_id'   => $this->hospitalId,
            'hospital_name' => '__ctrl_test_advertiser__',
            'is_network'    => 0,
            'status'        => 1,
            'created_at'    => $now,
            'updated_at'    => $now,
        ]);
        $this->advertiserId = (int) $db->insertID();

        $this->authSession = ['admin_user' => ['id' => 1, 'username' => 'admin', 'email' => 'admin@aicura.com']];
    }

    protected function tearDown(): void
    {
        $db = db_connect();
        $db->table('advertisers')->where('hospital_id', $this->hospitalId)->delete();
        $db->table('hospitals')->where('id', $this->hospitalId)->delete();
        cache()->delete('advertisers_kpi_' . $this->hospitalId);
        cache()->delete('hospitals_active_list');

        parent::tearDown();
    }

    // ── 인증 ────────────────────────────────────────────

    public function testIndexRedirectsWhenUnauthenticated(): void
    {
        $result = $this->get('admin/advertisers');

        $result->assertRedirect();
    }

    public function testIndexReturns200WithAuth(): void
    {
        $result = $this->withSession($this->authSession)->get('admin/advertisers');

        $result->assertStatus(200);
    }

    // ── 등록 폼 (Fix #4: newForm) ────────────────────────

    public function testNewFormRedirectsWhenUnauthenticated(): void
    {
        $result = $this->get('admin/advertisers/new');

        $result->assertRedirect();
    }

    public function testNewFormReturns200WithAuth(): void
    {
        $result = $this->withSession($this->authSession)->get('admin/advertisers/new');

        $result->assertStatus(200);
    }

    // ── 상세 ─────────────────────────────────────────────

    public function testShowThrowsPageNotFoundForMissingAdvertiser(): void
    {
        // CI4 FeatureTestTrait는 PageNotFoundException을 HTTP 응답으로 변환하지 않고 전파
        $this->expectException(PageNotFoundException::class);
        $this->withSession($this->authSession)->get('admin/advertisers/999999');
    }

    public function testShowReturns200ForExistingAdvertiser(): void
    {
        $result = $this->withSession($this->authSession)->get('admin/advertisers/' . $this->advertiserId);

        $result->assertStatus(200);
    }

    // ── 수정 폼 ───────────────────────────────────────────

    public function testEditReturns200ForExistingAdvertiser(): void
    {
        $result = $this->withSession($this->authSession)
            ->get('admin/advertisers/' . $this->advertiserId . '/edit');

        $result->assertStatus(200);
    }

    // ── 등록 처리 ─────────────────────────────────────────

    public function testCreateRedirectsWithEmptyData(): void
    {
        $result = $this->withSession($this->authSession)->post('admin/advertisers', []);

        $result->assertRedirect();
    }

    public function testCreateRedirectsWithNonExistentHospital(): void
    {
        // Fix #3: hospital_id 가 hospitals 테이블에 없으면 유효성 검사 실패 → redirect
        $result = $this->withSession($this->authSession)->post('admin/advertisers', [
            'hospital_id'   => 999999,
            'hospital_name' => 'Ghost Hospital',
            'is_network'    => 0,
            'status'        => 1,
        ]);

        $result->assertRedirect();
    }

    public function testCreateInsertsAndRedirects(): void
    {
        $db = db_connect();
        $db->table('hospitals')->insert([
            'name'       => '__create_hosp__',
            'type'       => 1,
            'status'     => 'active',
            'is_deleted' => 0,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        $newHospId = (int) $db->insertID();

        $result = $this->withSession($this->authSession)->post('admin/advertisers', [
            'hospital_id'   => $newHospId,
            'hospital_name' => '__created_advertiser__',
            'is_network'    => 0,
            'status'        => 1,
        ]);

        $result->assertRedirect();

        $db->table('advertisers')->where('hospital_id', $newHospId)->delete();
        $db->table('hospitals')->where('id', $newHospId)->delete();
        cache()->delete('hospitals_active_list');
    }

    public function testCreateSetsNetworkParentIdNullForNonChildNetwork(): void
    {
        // Fix #2: is_network !== 2 이면 network_parent_id 는 null 로 저장
        $db = db_connect();
        $db->table('hospitals')->insert([
            'name'       => '__net_hosp__',
            'type'       => 1,
            'status'     => 'active',
            'is_deleted' => 0,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        $newHospId = (int) $db->insertID();

        $this->withSession($this->authSession)->post('admin/advertisers', [
            'hospital_id'       => $newHospId,
            'hospital_name'     => '__net_advertiser__',
            'is_network'        => 0,
            'network_parent_id' => $this->advertiserId, // 일반 병원이므로 무시되어야 함
            'status'            => 1,
        ]);

        $row = $db->table('advertisers')->where('hospital_id', $newHospId)->get()->getRowArray();
        $this->assertIsArray($row);
        $this->assertNull($row['network_parent_id']);

        $db->table('advertisers')->where('hospital_id', $newHospId)->delete();
        $db->table('hospitals')->where('id', $newHospId)->delete();
        cache()->delete('hospitals_active_list');
    }

    // ── 수정 처리 ─────────────────────────────────────────

    public function testUpdateRedirectsWithInvalidData(): void
    {
        $result = $this->withSession($this->authSession)
            ->post('admin/advertisers/' . $this->advertiserId, [
                'hospital_name' => '', // required 필드 비워서 실패 유도
                'is_network'    => 0,
                'status'        => 1,
            ]);

        $result->assertRedirect();
    }

    public function testUpdateSucceedsAndRedirects(): void
    {
        $result = $this->withSession($this->authSession)
            ->post('admin/advertisers/' . $this->advertiserId, [
                'hospital_name' => '__updated_advertiser__',
                'is_network'    => 0,
                'status'        => 2,
            ]);

        $result->assertRedirect();
    }

    public function testUpdatePersistsChangesToDatabase(): void
    {
        $this->withSession($this->authSession)
            ->post('admin/advertisers/' . $this->advertiserId, [
                'hospital_name' => '__persisted_name__',
                'is_network'    => 0,
                'status'        => 2,
            ]);

        $row = db_connect()->table('advertisers')->where('id', $this->advertiserId)->get()->getRowArray();
        $this->assertIsArray($row);
        $this->assertSame('__persisted_name__', $row['hospital_name']);
        $this->assertSame(2, (int) $row['status']);
    }
}
