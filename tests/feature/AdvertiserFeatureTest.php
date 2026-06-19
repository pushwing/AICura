<?php

use App\Models\AdvertiserModel;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;
use Tests\Support\Database\Seeds\AdvertiserSeeder;

/**
 * 광고주 관리 피처 테스트 (SQLite3 인메모리 DB)
 *
 * 테스트 플랜 커버리지:
 *   [plan-2] 목록 페이지 + 검색 필터 동작
 *   [plan-4] 광고주 등록 후 상세 페이지 이동
 *   [plan-5] 상세 페이지 KPI 구조 확인
 *   [plan-6] 계약 목록 / 모병원·자병원 관계 표시
 *   [plan-7] 수정 폼 — 선택적 필드 null 클리어
 *
 * JS 행동([plan-3]: 병원 select 자동입력, 모병원 select 토글)은
 * 브라우저 테스트 도구(Playwright/Dusk) 영역이므로 PHPUnit 범위 외.
 *
 * @internal
 */
final class AdvertiserFeatureTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use FeatureTestTrait;

    protected $seed      = AdvertiserSeeder::class;
    protected $migrate   = true;
    protected $refresh   = true;
    protected $namespace = null; // App 마이그레이션까지 모두 실행

    /** 관리자 세션 데이터 (AdminAuthFilter 통과용) */
    private const ADMIN_SESSION = ['admin_user' => ['id' => 1, 'email' => 'admin@test.com', 'username' => 'admin']];

    // ── [plan-2] 목록 페이지 ──────────────────────────

    public function testIndexRedirectsWhenNotAuthenticated(): void
    {
        $result = $this->get('/admin/advertisers');

        $result->assertRedirectTo('/admin/login');
    }

    public function testIndexReturns200WhenAuthenticated(): void
    {
        $result = $this->withSession(self::ADMIN_SESSION)
                       ->get('/admin/advertisers');

        $result->assertStatus(200);
        $result->assertSee('광고주 관리');
        $result->assertSee('advertiserGrid');
    }

    public function testIndexContainsSeededAdvertiserInRowData(): void
    {
        $result = $this->withSession(self::ADMIN_SESSION)
                       ->get('/admin/advertisers');

        $result->assertStatus(200);
        $result->assertSee('강남성형외과');
    }

    public function testIndexFilterByHospitalName(): void
    {
        $result = $this->withSession(self::ADMIN_SESSION)
                       ->get('/admin/advertisers?hospital_name=강남');

        $result->assertStatus(200);
        // 강남성형외과는 포함, 서울네트워크는 미포함
        $result->assertSee('강남성형외과');
        $result->assertDontSee('서울네트워크모병원');
    }

    public function testIndexFilterByStatus(): void
    {
        // status=1(활성)만 조회
        $result = $this->withSession(self::ADMIN_SESSION)
                       ->get('/admin/advertisers?status=1');

        $result->assertStatus(200);
        $result->assertSee('강남성형외과');
    }

    public function testIndexFilterByIsNetwork(): void
    {
        // is_network=1(모병원)만 조회
        $result = $this->withSession(self::ADMIN_SESSION)
                       ->get('/admin/advertisers?is_network=1');

        $result->assertStatus(200);
        $result->assertSee('서울네트워크모병원');
        $result->assertDontSee('강남성형외과');
    }

    // ── [plan-4] 광고주 등록 ──────────────────────────

    public function testCreateValidationFailsWithoutHospitalId(): void
    {
        $result = $this->withSession(self::ADMIN_SESSION)
                       ->post('/admin/advertisers', [
                           csrf_token() => csrf_hash(),
                           'hospital_name' => '테스트병원',
                           'is_network'    => '0',
                           'status'        => '1',
                       ]);

        // 유효성 실패 → 이전 폼으로 리다이렉트
        $result->assertRedirect();
        $this->assertNotEmpty(session('errors'));
    }

    public function testCreateAdvertiserSuccessAndRedirectsToShow(): void
    {
        $result = $this->withSession(self::ADMIN_SESSION)
                       ->post('/admin/advertisers', [
                           csrf_token()      => csrf_hash(),
                           'hospital_id'     => '99',
                           'hospital_name'   => '신규병원',
                           'is_network'      => '0',
                           'status'          => '1',
                       ]);

        $result->assertRedirect();
        // DB에 신규 광고주 존재 확인
        $this->seeInDatabase('advertisers', ['hospital_id' => 99, 'hospital_name' => '신규병원']);
    }

    // ── [plan-5] 상세 페이지 KPI ──────────────────────

    public function testShowReturns404ForNonExistentAdvertiser(): void
    {
        $this->expectException(\CodeIgniter\Exceptions\PageNotFoundException::class);

        $this->withSession(self::ADMIN_SESSION)
             ->get('/admin/advertisers/9999');
    }

    public function testShowDisplaysKpiCards(): void
    {
        $result = $this->withSession(self::ADMIN_SESSION)
                       ->get('/admin/advertisers/1');

        $result->assertStatus(200);
        $result->assertSee('계약 총금액');
        $result->assertSee('잔여 충전금');
        $result->assertSee('진행중 캠페인');
    }

    public function testShowDisplaysAdvertiserBasicInfo(): void
    {
        $result = $this->withSession(self::ADMIN_SESSION)
                       ->get('/admin/advertisers/1');

        $result->assertStatus(200);
        $result->assertSee('강남성형외과');
        $result->assertSee('123-45-67890');   // business_no
        $result->assertSee('kim@gannam.com'); // contact_email
    }

    public function testShowDisplaysEmptyContractMessage(): void
    {
        // 계약 없는 광고주 상세 — "등록된 계약이 없습니다." 표시
        $result = $this->withSession(self::ADMIN_SESSION)
                       ->get('/admin/advertisers/1');

        $result->assertStatus(200);
        $result->assertSee('등록된 계약이 없습니다.');
    }

    // ── [plan-6] 네트워크 관계 표시 ──────────────────

    public function testShowDisplaysChildrenForNetworkParent(): void
    {
        // is_network=1(모병원) — 자병원 목록 표시
        $result = $this->withSession(self::ADMIN_SESSION)
                       ->get('/admin/advertisers/2');

        $result->assertStatus(200);
        $result->assertSee('자병원 목록');
        $result->assertSee('분당자병원');
    }

    public function testShowDisplaysParentLinkForNetworkChild(): void
    {
        // is_network=2(자병원) — 모병원 링크 표시
        $result = $this->withSession(self::ADMIN_SESSION)
                       ->get('/admin/advertisers/3');

        $result->assertStatus(200);
        $result->assertSee('모병원');
        $result->assertSee('서울네트워크모병원');
    }

    // ── [plan-7] 수정 — 선택적 필드 null 클리어 ─────

    public function testUpdateClearsOptionalFieldsWhenBlank(): void
    {
        // contact_name, contact_phone 을 빈 문자열로 전송 → DB에 null 저장
        $result = $this->withSession(self::ADMIN_SESSION)
                       ->put('/admin/advertisers/1', [
                           csrf_token()    => csrf_hash(),
                           'hospital_name' => '강남성형외과',
                           'contact_name'  => '',
                           'contact_phone' => '',
                           'contact_email' => '',
                           'business_no'   => '',
                           'is_network'    => '0',
                           'status'        => '1',
                       ]);

        $result->assertRedirect();

        $model      = new AdvertiserModel();
        $advertiser = $model->find(1);

        $this->assertNull($advertiser['contact_name'],  'contact_name이 null 이어야 함');
        $this->assertNull($advertiser['contact_phone'], 'contact_phone이 null 이어야 함');
        $this->assertNull($advertiser['contact_email'], 'contact_email이 null 이어야 함');
    }

    public function testUpdateValidationFailsWithBadEmail(): void
    {
        $result = $this->withSession(self::ADMIN_SESSION)
                       ->put('/admin/advertisers/1', [
                           csrf_token()     => csrf_hash(),
                           'hospital_name'  => '강남성형외과',
                           'contact_email'  => 'not-an-email',
                           'is_network'     => '0',
                           'status'         => '1',
                       ]);

        $result->assertRedirect();
        $this->assertNotEmpty(session('errors'));
    }

    // ── 수정 폼 페이지 ────────────────────────────────

    public function testEditFormReturns200AndShowsCurrentValues(): void
    {
        $result = $this->withSession(self::ADMIN_SESSION)
                       ->get('/admin/advertisers/1/edit');

        $result->assertStatus(200);
        $result->assertSee('광고주 수정');
        $result->assertSee('강남성형외과');
    }
}
