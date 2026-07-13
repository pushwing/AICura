<?php

use App\Models\PaymentModel;
use App\Models\RefundModel;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;

/**
 * @internal
 */
final class PaymentModelDatabaseTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $migrate     = true;
    protected $migrateOnce = true;
    protected $refresh     = false;
    protected $namespace;
    private int $hospitalId      = 0;
    private int $contractId      = 0;
    private int $contractOrderId = 0;
    private int $paymentId       = 0;

    protected function setUp(): void
    {
        parent::setUp();

        $db  = db_connect();
        $now = date('Y-m-d H:i:s');

        $db->table('hospitals')->insert([
            'name'       => '__pay_model_hospital__',
            'type'       => 1,
            'status'     => 'active',
            'is_deleted' => 0,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        $this->hospitalId = (int) $db->insertID();

        $db->table('contracts')->insert([
            'hospital_id'   => $this->hospitalId,
            'hospital_name' => '__pay_model_hospital__',
            'title'         => '__pay_model_contract__',
            'pay_type'      => 1,
            'created_at'    => $now,
            'updated_at'    => $now,
        ]);
        $this->contractId = (int) $db->insertID();

        $db->table('contract_orders')->insert([
            'hospital_id'     => $this->hospitalId,
            'hospital_name'   => '__pay_model_hospital__',
            'contract_type'   => 1,
            'ad_type'         => 1,
            'ad_type2'        => 1,
            'ad_price'        => 1100000,
            'contract_status' => 1,
            'created_at'      => $now,
            'updated_at'      => $now,
        ]);
        $this->contractOrderId = (int) $db->insertID();

        $db->table('payments')->insert([
            'hospital_id'       => $this->hospitalId,
            'contract_id'       => $this->contractId,
            'contract_order_id' => $this->contractOrderId,
            'type'              => 2,
            'amount'            => 1100000,
            'result_code'       => '0021',
            'trans_no'          => '__PAY_MODEL_TRANS__',
            'status'            => 'paid',
            'created_at'        => $now,
            'updated_at'        => $now,
        ]);
        $this->paymentId = (int) $db->insertID();
    }

    protected function tearDown(): void
    {
        $db = db_connect();
        $db->table('refunds')->where('payment_id', $this->paymentId)->delete();
        $db->table('deposits')->where('contract_id', $this->contractId)->delete();
        $db->table('payments')->where('id', $this->paymentId)->delete();
        $db->table('contract_orders')->where('id', $this->contractOrderId)->delete();
        $db->table('contracts')->where('id', $this->contractId)->delete();
        $db->table('hospitals')->where('id', $this->hospitalId)->delete();

        parent::tearDown();
    }

    // ── getPaymentList ─────────────────────────────────

    public function testGetPaymentListReturnsStructureWithListAndTotal(): void
    {
        $result = model(PaymentModel::class)->getPaymentList(['page' => 1, 'limit' => 20]);

        $this->assertArrayHasKey('list', $result);
        $this->assertArrayHasKey('total', $result);
        $this->assertIsArray($result['list']);
        $this->assertIsInt($result['total']);
    }

    public function testGetPaymentListContainsInsertedRecord(): void
    {
        $result = model(PaymentModel::class)->getPaymentList([
            'page'  => 1,
            'limit' => 20,
        ]);

        $transNos = array_column($result['list'], 'trans_no');
        $this->assertContains('__PAY_MODEL_TRANS__', $transNos);
    }

    public function testGetPaymentListFiltersByStatus(): void
    {
        $result = model(PaymentModel::class)->getPaymentList([
            'status' => 'paid',
            'page'   => 1,
            'limit'  => 20,
        ]);

        $this->assertGreaterThanOrEqual(1, $result['total']);

        foreach ($result['list'] as $row) {
            $this->assertSame('paid', $row['status']);
        }
    }

    public function testGetPaymentListFiltersByHospitalName(): void
    {
        $result = model(PaymentModel::class)->getPaymentList([
            'hospital_name' => '__pay_model_hospital__',
            'page'          => 1,
            'limit'         => 20,
        ]);

        $this->assertGreaterThanOrEqual(1, $result['total']);
        $ids = array_map('intval', array_column($result['list'], 'id'));
        $this->assertContains($this->paymentId, $ids);
    }

    public function testGetPaymentListFiltersByDateFrom(): void
    {
        $today = date('Y-m-d');

        $result = model(PaymentModel::class)->getPaymentList([
            'date_from' => $today,
            'page'      => 1,
            'limit'     => 20,
        ]);

        // 오늘 생성한 결제가 포함되어야 함
        $ids = array_map('intval', array_column($result['list'], 'id'));
        $this->assertContains($this->paymentId, $ids);
    }

    public function testGetPaymentListExcludesRecordsBeyondDateTo(): void
    {
        // date_to를 어제로 설정하면 오늘 생성한 결제가 제외됨
        $yesterday = date('Y-m-d', strtotime('-1 day'));

        $result = model(PaymentModel::class)->getPaymentList([
            'date_to' => $yesterday,
            'page'    => 1,
            'limit'   => 20,
        ]);

        $ids = array_map('intval', array_column($result['list'], 'id'));
        $this->assertNotContains($this->paymentId, $ids);
    }

    // ── getPaymentDetail ───────────────────────────────

    public function testGetPaymentDetailReturnsNullForUnknownId(): void
    {
        $result = model(PaymentModel::class)->getPaymentDetail(999999);

        $this->assertNull($result);
    }

    public function testGetPaymentDetailReturnsCorrectData(): void
    {
        $result = model(PaymentModel::class)->getPaymentDetail($this->paymentId);

        $this->assertIsArray($result);
        $this->assertSame($this->paymentId, (int) $result['id']);
        $this->assertSame('__PAY_MODEL_TRANS__', $result['trans_no']);
        $this->assertSame(1100000, (int) $result['amount']);
    }

    public function testGetPaymentDetailIncludesHospitalNameFromJoin(): void
    {
        $result = model(PaymentModel::class)->getPaymentDetail($this->paymentId);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('hospital_name', $result);
        $this->assertSame('__pay_model_hospital__', $result['hospital_name']);
    }

    // ── processRefund ──────────────────────────────────

    public function testProcessRefundThrowsForUnknownPaymentId(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('결제 정보를 찾을 수 없습니다.');

        model(PaymentModel::class)->processRefund(999999, 100000, 2, 1);
    }

    public function testProcessRefundPartialSetsPartialRefundedStatus(): void
    {
        // 부분 환불 (1,100,000 중 500,000) → 'partial_refunded'
        model(PaymentModel::class)->processRefund($this->paymentId, 500000, 2, 1);

        $row = db_connect()->table('payments')->where('id', $this->paymentId)->get()->getRowArray();
        $this->assertIsArray($row);
        $this->assertSame('partial_refunded', $row['status']);
    }

    public function testProcessRefundFullSetsRefundedStatus(): void
    {
        // 전액 환불 → 'refunded'
        model(PaymentModel::class)->processRefund($this->paymentId, 1100000, 5, 1);

        $row = db_connect()->table('payments')->where('id', $this->paymentId)->get()->getRowArray();
        $this->assertIsArray($row);
        $this->assertSame('refunded', $row['status']);
    }

    public function testProcessRefundInsertsDepositRecord(): void
    {
        model(PaymentModel::class)->processRefund($this->paymentId, 500000, 2, 1);

        $this->seeInDatabase('deposits', [
            'contract_id'       => $this->contractId,
            'contract_order_id' => $this->contractOrderId,
            'status'            => 6,
            'is_minus'          => 1,
            'price'             => 500000,
        ]);
    }

    public function testProcessRefundUpdatesContractOrderStatus(): void
    {
        model(PaymentModel::class)->processRefund($this->paymentId, 500000, 5, 1);

        $row = db_connect()->table('contract_orders')->where('id', $this->contractOrderId)->get()->getRowArray();
        $this->assertIsArray($row);
        $this->assertSame(5, (int) $row['contract_status']);
    }

    public function testProcessRefundInsertsPartialRefundRecord(): void
    {
        // 부분 환불 (결제액 1,100,000 중 500,000)
        model(PaymentModel::class)->processRefund($this->paymentId, 500000, 2, 1);

        $this->seeInDatabase('refunds', [
            'payment_id'        => $this->paymentId,
            'contract_order_id' => $this->contractOrderId,
            'amount'            => 500000,
            'term_type'         => 2, // 부분
            'result_code'       => 1, // 성공
        ]);
    }

    public function testProcessRefundRecordsFullRefundTermType(): void
    {
        // 전액 환불 → term_type = 1 (전체)
        model(PaymentModel::class)->processRefund($this->paymentId, 1100000, 5, 1);

        $this->seeInDatabase('refunds', [
            'payment_id' => $this->paymentId,
            'amount'     => 1100000,
            'term_type'  => 1,
        ]);
    }

    public function testProcessRefundAllowsMultiplePartialsUntilFull(): void
    {
        $payments = model(PaymentModel::class);
        $db       = db_connect();

        // 1차 부분 환불 (잔여 700,000) → 부분환불 유지
        $payments->processRefund($this->paymentId, 400000, 2, 1);
        $row = $db->table('payments')->where('id', $this->paymentId)->get()->getRowArray();
        $this->assertSame('partial_refunded', $row['status']);

        // 2차 부분 환불 (잔여 0) → 누적 1,100,000 = 결제액 → 전액 환불
        $payments->processRefund($this->paymentId, 700000, 5, 1);
        $row = $db->table('payments')->where('id', $this->paymentId)->get()->getRowArray();
        $this->assertSame('refunded', $row['status']);

        // 환불 내역 2건 기록 + 누적액 검증
        $this->assertSame(2, $db->table('refunds')->where('payment_id', $this->paymentId)->countAllResults());
        $this->assertSame(1100000, model(RefundModel::class)->getRefundedTotal($this->paymentId));
    }

    public function testProcessRefundRejectsAmountExceedingRemaining(): void
    {
        // 1차 환불 후 잔여 600,000
        model(PaymentModel::class)->processRefund($this->paymentId, 500000, 2, 1);

        // 잔여액(600,000) 초과 요청은 거부
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('환불 금액이 유효하지 않습니다.');
        model(PaymentModel::class)->processRefund($this->paymentId, 700000, 5, 1);
    }
}
