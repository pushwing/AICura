<?php

use CodeIgniter\Security\Security;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;
use Config\Services;

/**
 * Admin 캠페인 컨트롤러 테스트 (이슈 #157)
 *
 * 커버리지:
 *   - 광고 타입별 필수 단가 필드 검증 (CPA=db_cost, CPM=cpm_price, CPC=cpc_price)
 *   - 프로모션·옵션은 별도 과금 단가 없이 등록 가능
 *   - 종료(end) 처리 시 메모(사유) 필수, 미입력 시 422
 *   - 승인 등 다른 액션은 메모 없어도 정상 처리
 *
 * @internal
 */
final class CampaignAdminControllerTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use FeatureTestTrait;

    protected $migrate = true;
    protected $refresh = true;
    protected $namespace;

    /**
     * @var array<string, mixed>
     */
    private array $authSession = [];

    private int $hospitalId = 0;

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
        $db->table('hospitals')->insert([
            'name'       => '__test_hospital__', 'type' => 1, 'status' => 'active',
            'is_deleted' => 0, 'created_at' => $now, 'updated_at' => $now,
        ]);
        $this->hospitalId = (int) $db->insertID();
    }

    /**
     * @return array<string, mixed>
     */
    private function baseCampaignPayload(): array
    {
        return [
            'ad_title'      => '테스트 캠페인',
            'hospital_id'   => $this->hospitalId,
            'ad_start_date' => '2026-01-01',
            'ad_end_date'   => '2026-12-31',
            'cost_type'     => 1,
            'channel'       => 1,
        ];
    }

    // ── 광고 타입별 단가 검증 ──────────────────────────

    public function testCreateCpaRequiresDbCost(): void
    {
        $payload = $this->baseCampaignPayload() + ['ad_type' => 1];

        $this->withSession($this->authSession)->post('admin/campaigns', $payload)
            ->assertRedirect();
        $this->assertSame(0, db_connect()->table('campaigns')->countAllResults());

        $payload['db_cost'] = 10000;
        $this->withSession($this->authSession)->post('admin/campaigns', $payload)
            ->assertRedirectTo('/admin/campaigns/1');
        $this->seeInDatabase('campaigns', ['hospital_id' => $this->hospitalId]);
    }

    public function testCreateCpmRequiresCpmPrice(): void
    {
        $payload = $this->baseCampaignPayload() + ['ad_type' => 2];

        $this->withSession($this->authSession)->post('admin/campaigns', $payload)
            ->assertRedirect();
        $this->assertSame(0, db_connect()->table('campaigns')->countAllResults());

        $payload['cpm_price'] = 5000;
        $this->withSession($this->authSession)->post('admin/campaigns', $payload)
            ->assertRedirectTo('/admin/campaigns/1');

        $reviewRequest = db_connect()->table('campaign_review_requests')
            ->where('campaign_id', 1)->get()->getRowArray();
        $this->assertSame(5000, (int) $reviewRequest['cpm_price']);
    }

    public function testCreateCpcRequiresCpcPrice(): void
    {
        $payload = $this->baseCampaignPayload() + ['ad_type' => 4];

        $this->withSession($this->authSession)->post('admin/campaigns', $payload)
            ->assertRedirect();
        $this->assertSame(0, db_connect()->table('campaigns')->countAllResults());

        $payload['cpc_price'] = 300;
        $this->withSession($this->authSession)->post('admin/campaigns', $payload)
            ->assertRedirectTo('/admin/campaigns/1');

        $reviewRequest = db_connect()->table('campaign_review_requests')
            ->where('campaign_id', 1)->get()->getRowArray();
        $this->assertSame(300, (int) $reviewRequest['cpc_price']);
    }

    public function testCreatePromotionDoesNotRequireAnyUnitCost(): void
    {
        $payload = $this->baseCampaignPayload() + ['ad_type' => 3];

        $this->withSession($this->authSession)->post('admin/campaigns', $payload)
            ->assertRedirectTo('/admin/campaigns/1');
        $this->assertSame(1, db_connect()->table('campaigns')->countAllResults());
    }

    // ── 종료(end) 메모 필수 ──────────────────────────

    private function createActiveCampaign(): int
    {
        $now = date('Y-m-d H:i:s');
        $db  = db_connect();
        $db->table('campaigns')->insert([
            'ad_title'   => '__active__', 'hospital_id' => $this->hospitalId, 'hospital_type' => 1,
            'ad_type'    => 1, 'ad_start_date' => '2026-01-01', 'ad_end_date' => '2026-12-31',
            'cost_type'  => 1, 'status' => 'active', 'channel' => 1, 'is_deleted' => 0,
            'created_at' => $now, 'updated_at' => $now,
        ]);

        return (int) $db->insertID();
    }

    public function testEndActionWithoutMemoReturns422(): void
    {
        $id = $this->createActiveCampaign();

        $result = $this->withSession($this->authSession)
            ->withBodyFormat('json')
            ->post('admin/campaigns/' . $id . '/action', ['action' => 'end', 'memo' => '']);

        $result->assertStatus(422);
        $this->seeInDatabase('campaigns', ['id' => $id, 'status' => 'active']);
    }

    public function testEndActionWithMemoSucceeds(): void
    {
        $id = $this->createActiveCampaign();

        $result = $this->withSession($this->authSession)
            ->withBodyFormat('json')
            ->post('admin/campaigns/' . $id . '/action', ['action' => 'end', 'memo' => '계약 만료로 종료']);

        $result->assertOK();
        $this->seeInDatabase('campaigns', ['id' => $id, 'status' => 'ended']);
        $this->seeInDatabase('campaign_histories', ['campaign_id' => $id, 'action' => 'end']);
    }

    public function testApproveActionWithoutMemoStillSucceeds(): void
    {
        $now = date('Y-m-d H:i:s');
        $db  = db_connect();
        $db->table('campaigns')->insert([
            'ad_title'   => '__pending__', 'hospital_id' => $this->hospitalId, 'hospital_type' => 1,
            'ad_type'    => 1, 'ad_start_date' => '2026-01-01', 'ad_end_date' => '2026-12-31',
            'cost_type'  => 1, 'status' => 'pending', 'channel' => 1, 'is_deleted' => 0,
            'created_at' => $now, 'updated_at' => $now,
        ]);
        $id = (int) $db->insertID();

        $result = $this->withSession($this->authSession)
            ->withBodyFormat('json')
            ->post('admin/campaigns/' . $id . '/action', ['action' => 'approve', 'memo' => '']);

        $result->assertOK();
        $this->seeInDatabase('campaigns', ['id' => $id, 'status' => 'active']);
    }
}
