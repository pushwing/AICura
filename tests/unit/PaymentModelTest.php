<?php

use App\Models\PaymentModel;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * PaymentModel 단위 테스트 (DB 불필요)
 *
 * 테스트 플랜:
 *   [U1] table = 'payments'
 *   [U2] returnType = 'array'
 *   [U3] useTimestamps = true
 *   [U4] allowedFields 필수 컬럼 포함
 *   [U5] PAYMENT_TYPES 상수 값 검증
 *   [U6] STATUSES 상수 4개 키 존재
 *   [U7] processRefund — 유효하지 않은 refund_type → RuntimeException
 *   [U8] CreatePaymentsTable 마이그레이션 파일 존재
 *
 * @internal
 */
final class PaymentModelTest extends CIUnitTestCase
{
    private PaymentModel $model;

    protected function setUp(): void
    {
        parent::setUp();
        $this->model = new PaymentModel();
    }

    // ── [U1] 테이블명 ──────────────────────────────────

    public function testTableIsPayments(): void
    {
        $this->assertSame('payments', $this->model->getTable());
    }

    // ── [U2] returnType ────────────────────────────────

    public function testReturnTypeIsArray(): void
    {
        $prop = (new ReflectionClass($this->model))->getProperty('returnType');
        $prop->setAccessible(true);

        $this->assertSame('array', $prop->getValue($this->model));
    }

    // ── [U3] useTimestamps ─────────────────────────────

    public function testUseTimestampsIsTrue(): void
    {
        $prop = (new ReflectionClass($this->model))->getProperty('useTimestamps');
        $prop->setAccessible(true);

        $this->assertTrue($prop->getValue($this->model));
    }

    // ── [U4] allowedFields ─────────────────────────────

    public function testAllowedFieldsContainRequiredColumns(): void
    {
        $prop = (new ReflectionClass($this->model))->getProperty('allowedFields');
        $prop->setAccessible(true);
        $fields = $prop->getValue($this->model);

        $required = [
            'user_id', 'hospital_id', 'contract_id', 'contract_order_id',
            'type', 'amount', 'result_code', 'trans_no', 'auth_date',
            'auth_no', 'fn_name', 'vbank_no', 'vbank_expire', 'status',
        ];

        foreach ($required as $col) {
            $this->assertContains($col, $fields, "allowedFields에 '{$col}' 누락");
        }
    }

    // ── [U5] PAYMENT_TYPES 상수 ────────────────────────

    public function testPaymentTypesConstant(): void
    {
        $this->assertSame('가상계좌', PaymentModel::PAYMENT_TYPES[1]);
        $this->assertSame('신용카드', PaymentModel::PAYMENT_TYPES[2]);
        $this->assertCount(2, PaymentModel::PAYMENT_TYPES);
    }

    // ── [U6] STATUSES 상수 ─────────────────────────────

    public function testStatusesConstantHasFourKeys(): void
    {
        $statuses = PaymentModel::STATUSES;

        $this->assertArrayHasKey('pending', $statuses);
        $this->assertArrayHasKey('paid', $statuses);
        $this->assertArrayHasKey('refunded', $statuses);
        $this->assertArrayHasKey('failed', $statuses);
    }

    public function testStatusesHaveLabelAndColorKeys(): void
    {
        foreach (PaymentModel::STATUSES as $key => $info) {
            $this->assertArrayHasKey('label', $info, "STATUSES[{$key}]에 'label' 누락");
            $this->assertArrayHasKey('color', $info, "STATUSES[{$key}]에 'color' 누락");
        }
    }

    // ── [U7] processRefund — 유효하지 않은 refund_type ──

    public function testProcessRefundThrowsForInvalidRefundType(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('유효하지 않은 환불 유형');

        // DB 접근 없이 refundType 검증 단계에서 예외 발생
        $this->model->processRefund(1, 1000, 9, 1);
    }

    // ── [U8] 마이그레이션 파일 존재 ───────────────────

    public function testMigrationFileExists(): void
    {
        $pattern = APPPATH . 'Database/Migrations/*_CreatePaymentsTable.php';
        $files   = glob($pattern);

        $this->assertNotEmpty($files, 'CreatePaymentsTable 마이그레이션 파일이 없습니다.');
    }

    public function testMigrationDefinesRequiredColumns(): void
    {
        $pattern = APPPATH . 'Database/Migrations/*_CreatePaymentsTable.php';
        $files   = glob($pattern);
        $this->assertNotEmpty($files);

        $content = (string) file_get_contents($files[0]);

        foreach (['trans_no', 'vbank_no', 'vbank_expire', 'result_code', 'status'] as $col) {
            $this->assertStringContainsString($col, $content, "마이그레이션에 '{$col}' 컬럼 정의 없음");
        }
    }
}
