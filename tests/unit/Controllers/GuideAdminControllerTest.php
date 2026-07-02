<?php

use CodeIgniter\Security\Security;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;
use Config\Services;

/**
 * Admin 시술 가이드 관리 컨트롤러 테스트 (이슈 #146)
 *
 * 커버리지: 인증 가드 · 등록(슬러그 자동·FAQ 직렬화·발행) · 수정 · 소프트 삭제 · 슬러그 중복 유일화
 *
 * @internal
 */
final class GuideAdminControllerTest extends CIUnitTestCase
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

    public function testIndexRedirectsWhenUnauthenticated(): void
    {
        $this->get('admin/guides')->assertRedirect();
    }

    public function testIndexReturns200WithAuth(): void
    {
        $this->withSession($this->authSession)->get('admin/guides')->assertStatus(200);
    }

    public function testCreateGeneratesSlugAndSerializesFaq(): void
    {
        $this->withSession($this->authSession)->post('admin/guides', [
            'title'          => 'Double Eyelid Guide',
            'slug'           => '',
            'summary'        => '쌍꺼풀 수술 정보',
            'content'        => '<p>본문</p>',
            'procedure_name' => '쌍꺼풀 수술',
            'status'         => 'published',
            'faq_q'          => ['비용은?', '회복 기간은?'],
            'faq_a'          => ['병원마다 다릅니다', '약 2주'],
        ])->assertRedirectTo('/admin/guides');

        $this->seeInDatabase('guides', ['slug' => 'double-eyelid-guide', 'status' => 'published']);

        $row = db_connect()->table('guides')->where('slug', 'double-eyelid-guide')->get()->getRowArray();
        $this->assertNotNull($row['published_at']);
        $faq = json_decode((string) $row['faq_json'], true);
        $this->assertCount(2, $faq);
        $this->assertSame('비용은?', $faq[0]['q']);
    }

    public function testCreateRequiresTitle(): void
    {
        $this->withSession($this->authSession)->post('admin/guides', ['title' => ''])
            ->assertRedirect(); // 검증 실패 → back
        $this->dontSeeInDatabase('guides', ['summary' => 'x']);
    }

    public function testDuplicateSlugIsMadeUnique(): void
    {
        $now = date('Y-m-d H:i:s');
        db_connect()->table('guides')->insert([
            'title' => '기존', 'slug' => 'guide-x', 'status' => 'draft', 'created_at' => $now, 'updated_at' => $now,
        ]);

        $this->withSession($this->authSession)->post('admin/guides', [
            'title' => '신규', 'slug' => 'guide-x', 'status' => 'draft',
        ])->assertRedirectTo('/admin/guides');

        $this->seeInDatabase('guides', ['slug' => 'guide-x-2']);
    }

    public function testUpdateAndSoftDelete(): void
    {
        $now = date('Y-m-d H:i:s');
        db_connect()->table('guides')->insert([
            'title' => '원본', 'slug' => 'orig', 'status' => 'draft', 'created_at' => $now, 'updated_at' => $now,
        ]);
        $id = (int) db_connect()->insertID();

        $this->withSession($this->authSession)->post('admin/guides/' . $id . '/update', [
            'title' => '수정됨', 'slug' => 'orig', 'status' => 'published',
        ])->assertRedirectTo('/admin/guides');
        $this->seeInDatabase('guides', ['id' => $id, 'title' => '수정됨', 'status' => 'published']);

        $this->withSession($this->authSession)->post('admin/guides/' . $id . '/delete')
            ->assertRedirectTo('/admin/guides');
        // 소프트 삭제 — deleted_at 채워짐
        $row = db_connect()->table('guides')->where('id', $id)->get()->getRowArray();
        $this->assertNotNull($row['deleted_at']);
    }
}
