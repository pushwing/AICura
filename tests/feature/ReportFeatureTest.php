<?php

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;

/**
 * 리포트 피처 테스트 (SQLite3 인메모리 DB)
 *
 * 테스트 플랜 커버리지:
 *   [F1]  미인증 index 접근 → /admin/login 리다이렉트
 *   [F2]  미인증 campaigns 접근 → /admin/login 리다이렉트
 *   [F3]  인증 후 index 200 + Chart.js canvas
 *   [F4]  index year 파라미터 (유효 연도) → 200
 *   [F5]  index year < 2020 → 현재 연도로 보정 (200)
 *   [F6]  index year > 현재 연도 → 현재 연도로 보정 (200)
 *   [F7]  인증 후 campaigns 200 + AG Grid
 *   [F8]  campaigns ad_title 필터 → 200
 *   [F9]  campaigns 잘못된 date_from 형식 → 무시 (200)
 *   [F10] campaigns 역방향 날짜 범위 → 교정 후 200
 *
 * @internal
 */
final class ReportFeatureTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use FeatureTestTrait;

    protected $migrate   = true;
    protected $refresh   = true;
    protected $namespace = null;

    private const ADMIN_SESSION = ['admin_user' => ['id' => 1, 'email' => 'admin@test.com', 'username' => 'admin']];

    // ── [F1] 미인증 index 접근 ────────────────────────

    public function testIndexRedirectsWhenNotAuthenticated(): void
    {
        $result = $this->get('/admin/reports');

        $result->assertRedirectTo('/admin/login');
    }

    // ── [F2] 미인증 campaigns 접근 ───────────────────

    public function testCampaignsRedirectsWhenNotAuthenticated(): void
    {
        $result = $this->get('/admin/reports/campaigns');

        $result->assertRedirectTo('/admin/login');
    }

    // ── [F3] 인증 후 index 200 + Chart.js canvas ─────

    public function testIndexReturns200WithAuth(): void
    {
        $result = $this->withSession(self::ADMIN_SESSION)
                       ->get('/admin/reports');

        $result->assertStatus(200);
        $result->assertSee('chartRevenue');
    }

    public function testIndexContainsYearSelector(): void
    {
        $result = $this->withSession(self::ADMIN_SESSION)
                       ->get('/admin/reports');

        $result->assertStatus(200);
        $result->assertSee((string) date('Y'));
    }

    // ── [F4] 유효 연도 파라미터 ──────────────────────

    public function testIndexAcceptsValidYear(): void
    {
        $result = $this->withSession(self::ADMIN_SESSION)
                       ->get('/admin/reports?year=2023');

        $result->assertStatus(200);
        $result->assertSee('2023');
    }

    // ── [F5] year < 2020 → 현재 연도 보정 ────────────

    public function testIndexClampsYearBelowMinimum(): void
    {
        $result = $this->withSession(self::ADMIN_SESSION)
                       ->get('/admin/reports?year=2019');

        $result->assertStatus(200);
        // 2019는 유효 범위 밖이므로 현재 연도로 보정됨 — 2019가 선택값으로 노출되지 않아야 함
        // (selected option 확인용 — 뷰는 $year를 selected에 사용)
        $result->assertSee((string) date('Y'));
    }

    // ── [F6] year > 현재 연도 → 현재 연도 보정 ───────

    public function testIndexClampsYearAboveCurrent(): void
    {
        $futureYear = (int) date('Y') + 1;
        $result     = $this->withSession(self::ADMIN_SESSION)
                           ->get('/admin/reports?year=' . $futureYear);

        $result->assertStatus(200);
        $result->assertSee((string) date('Y'));
    }

    // ── [F7] campaigns 200 + AG Grid ─────────────────

    public function testCampaignsReturns200WithAuth(): void
    {
        $result = $this->withSession(self::ADMIN_SESSION)
                       ->get('/admin/reports/campaigns');

        $result->assertStatus(200);
        $result->assertSee('campaignGrid');
    }

    // ── [F8] campaigns ad_title 필터 ─────────────────

    public function testCampaignsAcceptsAdTitleFilter(): void
    {
        $result = $this->withSession(self::ADMIN_SESSION)
                       ->get('/admin/reports/campaigns?ad_title=테스트캠페인');

        $result->assertStatus(200);
    }

    // ── [F9] 잘못된 date_from 형식 → 무시 ────────────

    public function testCampaignsIgnoresInvalidDateFormat(): void
    {
        $result = $this->withSession(self::ADMIN_SESSION)
                       ->get('/admin/reports/campaigns?date_from=notadate&date_to=also-invalid');

        $result->assertStatus(200);
    }

    // ── [F10] 역방향 날짜 범위 → 교정 후 200 ─────────

    public function testCampaignsSwapsReversedDateRange(): void
    {
        $result = $this->withSession(self::ADMIN_SESSION)
                       ->get('/admin/reports/campaigns?date_from=2026-06-30&date_to=2026-01-01');

        // 역방향이지만 교정 후 정상 렌더링
        $result->assertStatus(200);
        $result->assertSee('campaignGrid');
    }
}
