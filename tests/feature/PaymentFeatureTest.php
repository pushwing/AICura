<?php

use App\Models\PaymentModel;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;
use Tests\Support\Database\Seeds\PaymentSeeder;

/**
 * 결제 관리 피처 테스트 (SQLite3 인메모리 DB)
 *
 * 테스트 플랜 커버리지:
 *   [F1] 미인증 목록 접근 → /admin/login 리다이렉트
 *   [F2] 인증 후 목록 200 + AG Grid div
 *   [F3] 목록 status 필터
 *   [F4] 목록 hospital_name 필터
 *   [F5] 상세 404 — 존재하지 않는 ID
 *   [F6] 상세 200 + 결제 정보 표시
 *   [F7] 상세 환불 버튼 포함
 *   [F8] 이미 환불된 결제 재환불 시도 → 에러 리다이렉트
 *   [F9] 환불 — refund_type 누락 → 유효성 실패 리다이렉트
 *   [F10] 환불 — amount > payment.amount → 에러 리다이렉트
 *   [F11] 발행환불(type=2) 정상 처리 → deposits.status=6, payments.status=refunded, contract_status=2
 *   [F12] 계약환불(type=5) 정상 처리 → deposits.status=7, contract_status=5
 *
 * @internal
 */
final class PaymentFeatureTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use FeatureTestTrait;

    protected $seed      = PaymentSeeder::class;
    protected $migrate   = true;
    protected $refresh   = true;
    protected $namespace = null;

    private const ADMIN_SESSION = ['admin_user' => ['id' => 1, 'email' => 'admin@test.com', 'username' => 'admin']];

    // ── [F1] 미인증 목록 접근 ─────────────────────────

    public function testIndexRedirectsWhenNotAuthenticated(): void
    {
        $result = $this->get('/admin/payments');

        $result->assertRedirectTo('/admin/login');
    }

    // ── [F2] 목록 200 + AG Grid ───────────────────────

    public function testIndexReturns200WithAuth(): void
    {
        $result = $this->withSession(self::ADMIN_SESSION)
                       ->get('/admin/payments');

        $result->assertStatus(200);
        $result->assertSee('결제 관리');
        $result->assertSee('paymentGrid');
    }

    public function testIndexContainsSeededPaymentInRowData(): void
    {
        $result = $this->withSession(self::ADMIN_SESSION)
                       ->get('/admin/payments');

        $result->assertStatus(200);
        $result->assertSee('TRANS001');
    }

    // ── [F3] status 필터 ──────────────────────────────

    public function testIndexFilterByStatus(): void
    {
        $result = $this->withSession(self::ADMIN_SESSION)
                       ->get('/admin/payments?status=paid');

        $result->assertStatus(200);
        $result->assertSee('TRANS001');
        $result->assertDontSee('TRANS003'); // pending은 미포함
    }

    public function testIndexFilterByStatusPending(): void
    {
        $result = $this->withSession(self::ADMIN_SESSION)
                       ->get('/admin/payments?status=pending');

        $result->assertStatus(200);
        $result->assertSee('TRANS003');
        $result->assertDontSee('TRANS001');
    }

    // ── [F4] 병원명 필터 ──────────────────────────────

    public function testIndexFilterByHospitalName(): void
    {
        $result = $this->withSession(self::ADMIN_SESSION)
                       ->get('/admin/payments?hospital_name=강남');

        $result->assertStatus(200);
        $result->assertSee('TRANS001');
    }

    public function testIndexFilterByHospitalNameNoMatch(): void
    {
        $result = $this->withSession(self::ADMIN_SESSION)
                       ->get('/admin/payments?hospital_name=존재하지않는병원');

        $result->assertStatus(200);
        $result->assertDontSee('TRANS001');
    }

    // ── [F5] 상세 404 ─────────────────────────────────

    public function testShowReturns404ForNonExistent(): void
    {
        $this->expectException(\CodeIgniter\Exceptions\PageNotFoundException::class);

        $this->withSession(self::ADMIN_SESSION)
             ->get('/admin/payments/9999');
    }

    // ── [F6] 상세 결제 정보 표시 ──────────────────────

    public function testShowDisplaysPaymentInfo(): void
    {
        $result = $this->withSession(self::ADMIN_SESSION)
                       ->get('/admin/payments/1');

        $result->assertStatus(200);
        $result->assertSee('결제 #1');
        $result->assertSee('TRANS001');
        $result->assertSee('1,100,000');  // amount 포맷
        $result->assertSee('신용카드');
    }

    // ── [F7] 환불 버튼 포함 ───────────────────────────

    public function testShowDisplaysRefundButton(): void
    {
        $result = $this->withSession(self::ADMIN_SESSION)
                       ->get('/admin/payments/1');

        $result->assertStatus(200);
        $result->assertSee('환불 처리');
    }

    // ── [F8] 이미 환불된 결제 재환불 시도 ────────────

    public function testRefundBlocksAlreadyRefunded(): void
    {
        $result = $this->withSession(self::ADMIN_SESSION)
                       ->post('/admin/payments/2/refund', [
                           csrf_token()     => csrf_hash(),
                           'refund_type'   => '2',
                           'refund_amount' => '100000',
                       ]);

        $result->assertRedirect();
        $this->assertSame('이미 환불 처리된 결제입니다.', session('error'));
    }

    // ── [F9] refund_type 누락 → 유효성 실패 ──────────

    public function testRefundValidationFailsWithoutType(): void
    {
        $result = $this->withSession(self::ADMIN_SESSION)
                       ->post('/admin/payments/1/refund', [
                           csrf_token()     => csrf_hash(),
                           'refund_amount' => '100000',
                           // refund_type 누락
                       ]);

        $result->assertRedirect();
    }

    public function testRefundValidationFailsWithoutAmount(): void
    {
        $result = $this->withSession(self::ADMIN_SESSION)
                       ->post('/admin/payments/1/refund', [
                           csrf_token()    => csrf_hash(),
                           'refund_type'  => '2',
                           // refund_amount 누락
                       ]);

        $result->assertRedirect();
    }

    // ── [F10] amount > payment.amount ─────────────────

    public function testRefundFailsWhenAmountExceedsPayment(): void
    {
        $result = $this->withSession(self::ADMIN_SESSION)
                       ->post('/admin/payments/1/refund', [
                           csrf_token()     => csrf_hash(),
                           'refund_type'   => '2',
                           'refund_amount' => '9999999', // 1100000 초과
                       ]);

        $result->assertRedirect();
        $this->assertSame('환불 금액이 결제 금액을 초과할 수 없습니다.', session('error'));
    }

    // ── [F11] 발행환불(type=2) 정상 처리 ─────────────

    public function testRefundProcessesHappyPath(): void
    {
        $result = $this->withSession(self::ADMIN_SESSION)
                       ->post('/admin/payments/1/refund', [
                           csrf_token()     => csrf_hash(),
                           'refund_type'   => '2',   // 발행환불
                           'refund_amount' => '500000',
                       ]);

        $result->assertRedirectTo('/admin/payments/1');

        // payments.status = refunded
        $this->seeInDatabase('payments', ['id' => 1, 'status' => 'refunded']);

        // deposits INSERT: status=6, is_minus=1, price=500000
        $this->seeInDatabase('deposits', [
            'contract_id'       => 1,
            'contract_order_id' => 1,
            'status'            => 6,
            'is_minus'          => 1,
            'price'             => 500000,
        ]);

        // contract_orders.contract_status = 2 (발행환불)
        $this->seeInDatabase('contract_orders', ['id' => 1, 'contract_status' => 2]);
    }

    // ── [F12] 계약환불(type=5) 정상 처리 ─────────────

    public function testRefundProcessesKeiyakuRefund(): void
    {
        $result = $this->withSession(self::ADMIN_SESSION)
                       ->post('/admin/payments/1/refund', [
                           csrf_token()     => csrf_hash(),
                           'refund_type'   => '5',   // 계약환불
                           'refund_amount' => '1100000',
                       ]);

        $result->assertRedirectTo('/admin/payments/1');

        // deposits.status = 7 (계약환불)
        $this->seeInDatabase('deposits', [
            'contract_order_id' => 1,
            'status'            => 7,
            'price'             => 1100000,
        ]);

        // contract_orders.contract_status = 5 (계약환불)
        $this->seeInDatabase('contract_orders', ['id' => 1, 'contract_status' => 5]);
    }

    // ── [F13] 환불 404 — 존재하지 않는 결제 ID ────────

    public function testRefundThrowsPageNotFoundForNonExistentPayment(): void
    {
        // CI4 FeatureTestTrait는 PageNotFoundException을 HTTP 응답으로 변환하지 않고 전파
        $this->expectException(\CodeIgniter\Exceptions\PageNotFoundException::class);

        $this->withSession(self::ADMIN_SESSION)
             ->post('/admin/payments/9999/refund', [
                 csrf_token()     => csrf_hash(),
                 'refund_type'   => '2',
                 'refund_amount' => '100000',
             ]);
    }

    // ── [F14] date_from 날짜 필터 ─────────────────────

    public function testIndexFilterByDateFrom(): void
    {
        // 시더 데이터는 오늘 날짜로 생성되므로 today 기준으로 필터하면 포함
        $today  = date('Y-m-d');
        $result = $this->withSession(self::ADMIN_SESSION)
                       ->get('/admin/payments?date_from=' . $today);

        $result->assertStatus(200);
        $result->assertSee('TRANS001');
    }

    public function testIndexFilterByDateToExcludesFutureFilter(): void
    {
        // date_to를 어제로 설정하면 오늘 생성된 시더 데이터가 제외됨
        $yesterday = date('Y-m-d', strtotime('-1 day'));
        $result    = $this->withSession(self::ADMIN_SESSION)
                          ->get('/admin/payments?date_to=' . $yesterday);

        $result->assertStatus(200);
        $result->assertDontSee('TRANS001');
    }

    // ── [F15] 잘못된 refund_type 값 ───────────────────

    public function testRefundFailsWithInvalidRefundType(): void
    {
        // in_list[2,5] 위반 — 유효하지 않은 type 값
        $result = $this->withSession(self::ADMIN_SESSION)
                       ->post('/admin/payments/1/refund', [
                           csrf_token()     => csrf_hash(),
                           'refund_type'   => '3',
                           'refund_amount' => '100000',
                       ]);

        $result->assertRedirect();
        // payments.status는 변경되지 않아야 함
        $this->seeInDatabase('payments', ['id' => 1, 'status' => 'paid']);
    }
}
