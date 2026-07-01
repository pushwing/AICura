<?php

use CodeIgniter\Security\Security;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;
use Config\Services;

/**
 * Admin 캠페인 상태 변경(action) 컨트롤러 테스트
 *
 * 커버리지: 상태 변경 메모(memo)가 저장 전 화이트리스트 HTML 필터를 거치는지 확인 (표시 버그 수정 회귀 방지)
 *
 * @internal
 */
final class CampaignControllerActionTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use FeatureTestTrait;

    protected $migrate   = true;
    protected $refresh   = true;
    protected $namespace = null;

    /** @var array<string, mixed> */
    private array $authSession = [];

    protected function setUp(): void
    {
        parent::setUp();
        cache()->clean();

        $security = $this->getMockBuilder(Security::class)
            ->setConstructorArgs([config('Security')])
            ->onlyMethods(['verify'])
            ->getMock();
        $security->method('verify')->willReturn(true);
        Services::injectMock('security', $security);

        $this->authSession = ['admin_user' => ['id' => 1, 'username' => 'admin', 'email' => 'admin@aicura.com']];
    }

    private function createPendingCampaign(): int
    {
        $now = date('Y-m-d H:i:s');
        db_connect()->table('campaigns')->insert([
            'ad_title'    => '테스트 캠페인',
            'hospital_id' => 1,
            'status'      => 'pending',
            'created_at'  => $now,
            'updated_at'  => $now,
        ]);

        return (int) db_connect()->insertID();
    }

    public function testActionSanitizesMemoBeforeSaving(): void
    {
        $id = $this->createPendingCampaign();

        $rawMemo = '<script>alert(1)</script><strong onclick="x()">중요</strong><img src=x onerror=alert(1)>';

        $this->withSession($this->authSession)
            ->withBodyFormat('json')
            ->post('admin/campaigns/' . $id . '/action', [
                'action' => 'approve',
                'memo'   => $rawMemo,
            ])
            ->assertStatus(200);

        $row = db_connect()->table('campaign_histories')->where('campaign_id', $id)->get()->getRowArray();

        $this->assertNotNull($row);
        $this->assertStringNotContainsString('<script', $row['memo']);
        $this->assertStringNotContainsString('onclick', $row['memo']);
        $this->assertStringNotContainsString('<img', $row['memo']);
        $this->assertStringContainsString('<strong>중요</strong>', $row['memo']);
    }

    public function testActionAllowsNullMemo(): void
    {
        $id = $this->createPendingCampaign();

        $this->withSession($this->authSession)
            ->withBodyFormat('json')
            ->post('admin/campaigns/' . $id . '/action', ['action' => 'approve'])
            ->assertStatus(200);

        $row = db_connect()->table('campaign_histories')->where('campaign_id', $id)->get()->getRowArray();
        $this->assertNull($row['memo']);
    }
}
