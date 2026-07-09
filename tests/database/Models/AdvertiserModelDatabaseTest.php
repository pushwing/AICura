<?php

use App\Models\AdvertiserModel;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;

/**
 * @internal
 */
final class AdvertiserModelDatabaseTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $migrate     = true;
    protected $migrateOnce = true;
    protected $refresh     = false;
    protected $namespace;
    private int $hospitalId   = 0;
    private int $advertiserId = 0;

    protected function setUp(): void
    {
        parent::setUp();

        $db  = db_connect();
        $now = date('Y-m-d H:i:s');

        $db->table('hospitals')->insert([
            'name'       => '__adv_model_hospital__',
            'type'       => 1,
            'status'     => 'active',
            'is_deleted' => 0,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        $this->hospitalId = (int) $db->insertID();

        $db->table('advertisers')->insert([
            'hospital_id'   => $this->hospitalId,
            'hospital_name' => '__adv_model_test__',
            'is_network'    => 0,
            'status'        => 1,
            'created_at'    => $now,
            'updated_at'    => $now,
        ]);
        $this->advertiserId = (int) $db->insertID();
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

    // ── getList ────────────────────────────────────────

    public function testGetListReturnsStructureWithListAndTotal(): void
    {
        $result = model(AdvertiserModel::class)->getList(['page' => 1, 'limit' => 20]);

        $this->assertArrayHasKey('list', $result);
        $this->assertArrayHasKey('total', $result);
        $this->assertIsArray($result['list']);
        $this->assertIsInt($result['total']);
    }

    public function testGetListFindsInsertedRecord(): void
    {
        $result = model(AdvertiserModel::class)->getList([
            'hospital_name' => '__adv_model_test__',
            'page'          => 1,
            'limit'         => 20,
        ]);

        $this->assertGreaterThanOrEqual(1, $result['total']);
        $ids = array_map('intval', array_column($result['list'], 'id'));
        $this->assertContains($this->advertiserId, $ids);
    }

    public function testGetListFiltersByStatus(): void
    {
        $result = model(AdvertiserModel::class)->getList([
            'hospital_name' => '__adv_model_test__',
            'status'        => 1,
            'page'          => 1,
            'limit'         => 20,
        ]);

        $this->assertGreaterThanOrEqual(1, $result['total']);

        foreach ($result['list'] as $row) {
            $this->assertSame(1, (int) $row['status']);
        }
    }

    public function testGetListFiltersByIsNetwork(): void
    {
        $result = model(AdvertiserModel::class)->getList([
            'hospital_name' => '__adv_model_test__',
            'is_network'    => 0,
            'page'          => 1,
            'limit'         => 20,
        ]);

        $ids = array_map('intval', array_column($result['list'], 'id'));
        $this->assertContains($this->advertiserId, $ids);
    }

    public function testGetListRowsDoNotContainHospitalId(): void
    {
        // Fix #9: hospital_id는 인덱스 그리드에 불필요하므로 SELECT에서 제외됨을 검증
        $result = model(AdvertiserModel::class)->getList([
            'hospital_name' => '__adv_model_test__',
            'page'          => 1,
            'limit'         => 20,
        ]);

        $this->assertNotEmpty($result['list']);

        foreach ($result['list'] as $row) {
            $this->assertArrayNotHasKey('hospital_id', $row);
        }
    }

    // ── getDetail ──────────────────────────────────────

    public function testGetDetailReturnsNullForUnknownId(): void
    {
        $result = model(AdvertiserModel::class)->getDetail(999999);

        $this->assertNull($result);
    }

    public function testGetDetailReturnsAdvertiserWithCorrectData(): void
    {
        $result = model(AdvertiserModel::class)->getDetail($this->advertiserId);

        $this->assertIsArray($result);
        $this->assertSame($this->advertiserId, (int) $result['id']);
        $this->assertSame('__adv_model_test__', $result['hospital_name']);
    }

    public function testGetDetailIncludesContractsList(): void
    {
        $result = model(AdvertiserModel::class)->getDetail($this->advertiserId);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('contracts', $result);
        $this->assertIsArray($result['contracts']);
    }

    public function testGetDetailIncludesKpiWithRequiredKeys(): void
    {
        $result = model(AdvertiserModel::class)->getDetail($this->advertiserId);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('kpi', $result);
        $this->assertIsArray($result['kpi']);
        $this->assertArrayHasKey('total_amount', $result['kpi']);
        $this->assertArrayHasKey('balance', $result['kpi']);
        $this->assertArrayHasKey('active_campaigns', $result['kpi']);
    }

    public function testGetDetailKpiValuesAreIntegers(): void
    {
        $result = model(AdvertiserModel::class)->getDetail($this->advertiserId);

        $this->assertIsArray($result);
        $kpi = $result['kpi'];
        $this->assertIsInt($kpi['total_amount']);
        $this->assertIsInt($kpi['balance']);
        $this->assertIsInt($kpi['active_campaigns']);
    }

    // ── KPI 캐시 (Fix #1) ──────────────────────────────

    public function testGetDetailCachesKpiResult(): void
    {
        model(AdvertiserModel::class)->getDetail($this->advertiserId);

        $cached = cache('advertisers_kpi_' . $this->hospitalId);
        $this->assertNotNull($cached);
        $this->assertIsArray($cached);
    }

    public function testUpdateClearsKpiCache(): void
    {
        $model = model(AdvertiserModel::class);

        // 캐시 생성
        $model->getDetail($this->advertiserId);
        $this->assertNotNull(cache('advertisers_kpi_' . $this->hospitalId));

        // 업데이트 후 캐시 무효화
        $model->update($this->advertiserId, ['hospital_name' => '__adv_updated__']);
        $this->assertNull(cache('advertisers_kpi_' . $this->hospitalId));
    }
}
