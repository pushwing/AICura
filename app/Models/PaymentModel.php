<?php

namespace App\Models;

use CodeIgniter\Model;
use RuntimeException;
use Throwable;

/**
 * contract_orders.contract_status 환불 유형:
 *  2 발행환불 → deposits.status = 6
 *  5 계약환불 → deposits.status = 7
 */
class PaymentModel extends Model
{
    public const PAYMENT_TYPES = [
        1 => '가상계좌',
        2 => '신용카드',
    ];
    public const STATUSES = [
        'pending'          => ['label' => '입금대기', 'color' => '#f59e0b'],
        'paid'             => ['label' => '결제완료', 'color' => '#10b981'],
        'partial_refunded' => ['label' => '부분환불', 'color' => '#a855f7'],
        'refunded'         => ['label' => '환불', 'color' => '#6366f1'],
        'failed'           => ['label' => '실패', 'color' => '#ef4444'],
    ];

    // contract_orders.contract_status → deposits.status 매핑
    private const array REFUND_DEPOSIT_STATUS_MAP = [
        2 => 6, // 발행환불 → deposits 발행환불
        5 => 7, // 계약환불 → deposits 계약환불
    ];

    protected $table         = 'payments';
    protected $primaryKey    = 'id';
    protected $useTimestamps = true;
    protected $returnType    = 'array';
    protected $allowedFields = [
        'user_id',
        'hospital_id',
        'contract_id',
        'contract_order_id',
        'type',
        'amount',
        'result_code',
        'trans_no',
        'auth_date',
        'auth_no',
        'fn_name',
        'vbank_no',
        'vbank_expire',
        'status',
    ];

    /**
     * 결제 목록 (페이징, 검색)
     *
     * @param array<string, mixed> $params
     *
     * @return array<string, mixed>
     */
    public function getPaymentList(array $params): array
    {
        $builder = $this->db->table('payments p')
            ->select('p.id, p.type, p.amount, p.result_code, p.trans_no, p.status, p.created_at')
            ->select('h.name as hospital_name', false)
            ->select('co.hospital_name as order_hospital_name', false)
            ->join('hospitals h', 'h.id = p.hospital_id', 'left')
            ->join('contract_orders co', 'co.id = p.contract_order_id', 'left');

        if (! empty($params['status'])) {
            $builder->where('p.status', $params['status']);
        }
        if (! empty($params['hospital_name'])) {
            $builder->groupStart()
                ->like('h.name', $params['hospital_name'])
                ->orLike('co.hospital_name', $params['hospital_name'])
                ->groupEnd();
        }
        if (! empty($params['date_from'])) {
            $builder->where('DATE(p.created_at) >=', $params['date_from']);
        }
        if (! empty($params['date_to'])) {
            $builder->where('DATE(p.created_at) <=', $params['date_to']);
        }

        $total = (clone $builder)->countAllResults(false);

        $page  = max(1, (int) ($params['page'] ?? 1));
        $limit = (int) ($params['limit'] ?? 20);

        $list = $builder
            ->orderBy('p.id', 'DESC')
            ->limit($limit, ($page - 1) * $limit)
            ->get()
            ->getResultArray();

        return ['list' => $list, 'total' => $total];
    }

    /**
     * 결제 상세 (병원, 수주계약 JOIN 포함)
     *
     * @return array<string, mixed>|null
     */
    public function getPaymentDetail(int $id): ?array
    {
        $row = $this->db->table('payments p')
            ->select('p.*')
            ->select('h.name as hospital_name', false)
            ->select('co.hospital_name as order_hospital_name, co.ad_price, co.contract_status', false)
            ->join('hospitals h', 'h.id = p.hospital_id', 'left')
            ->join('contract_orders co', 'co.id = p.contract_order_id', 'left')
            ->where('p.id', $id)
            ->get()
            ->getRowArray();

        return $row ?? null;
    }

    /**
     * 환불 처리 트랜잭션 (부분 환불 다회 허용)
     *  ① deposits INSERT (status=6 or 7, is_minus=1) — 원장
     *  ② refunds  INSERT — PG 환불 결과 기록 (감사용)
     *  ③ contract_orders.contract_status 업데이트 (2 or 5)
     *  ④ payments.status — 누적 환불액이 결제액 미만이면 'partial_refunded',
     *     도달하면 'refunded'
     *
     * 환불 가능액은 (결제액 − 성공한 기존 환불액 누계) 로 산정하며,
     * 잔여액에 도달할 때까지 부분 환불을 여러 번 처리할 수 있다.
     *
     * @throws RuntimeException 유효하지 않은 환불 유형·금액
     * @throws Throwable        DB 트랜잭션 오류
     */
    public function processRefund(int $paymentId, int $amount, int $refundType, int $userId): void
    {
        $depositStatus = self::REFUND_DEPOSIT_STATUS_MAP[$refundType] ?? null;
        if ($depositStatus === null) {
            throw new RuntimeException('유효하지 않은 환불 유형입니다.');
        }

        /** @var array<string, mixed>|null $payment */
        $payment = $this->select('id, user_id, contract_id, contract_order_id, type, trans_no, amount')->find($paymentId);
        if ($payment === null) {
            throw new RuntimeException('결제 정보를 찾을 수 없습니다.');
        }

        $total           = (int) $payment['amount'];
        $alreadyRefunded = model(RefundModel::class)->getRefundedTotal($paymentId);
        $remaining       = $total - $alreadyRefunded;

        if ($amount <= 0 || $amount > $remaining) {
            throw new RuntimeException('환불 금액이 유효하지 않습니다.');
        }

        // 이번 환불로 누적 환불액이 결제액에 도달하면 전체 환불
        $isFullyRefunded = ($alreadyRefunded + $amount) >= $total;

        $db  = $this->db;
        $now = date('Y-m-d H:i:s');

        $db->transBegin();

        try {
            $db->table('deposits')->insert([
                'status'            => $depositStatus,
                'is_minus'          => 1,
                'contract_id'       => $payment['contract_id'],
                'contract_order_id' => $payment['contract_order_id'],
                'users_id'          => $userId,
                'price'             => $amount,
                'created_at'        => $now,
                'updated_at'        => $now,
            ]);

            // 환불 내역 기록 (전체/부분 구분 — 결제 전액 환불이면 전체)
            $db->table('refunds')->insert([
                'user_id'    => (int) ($payment['user_id'] ?? 0),
                'payment_id' => $paymentId,
                'trans_no'   => (string) ($payment['trans_no'] ?? ''),
                'term_type'  => $amount >= (int) $payment['amount']
                    ? RefundModel::TERM_FULL
                    : RefundModel::TERM_PARTIAL,
                'contract_id'       => (int) $payment['contract_id'],
                'contract_order_id' => (int) $payment['contract_order_id'],
                'type'              => (int) $payment['type'],
                'amount'            => $amount,
                'result_code'       => RefundModel::RESULT_SUCCESS,
                'created_at'        => $now,
                'updated_at'        => $now,
            ]);

            $db->table('contract_orders')
                ->where('id', $payment['contract_order_id'])
                ->update(['contract_status' => $refundType, 'updated_at' => $now]);

            $db->table('payments')
                ->where('id', $paymentId)
                ->update([
                    'status'     => $isFullyRefunded ? 'refunded' : 'partial_refunded',
                    'updated_at' => $now,
                ]);

            $db->transCommit();
        } catch (Throwable $e) {
            $db->transRollback();

            throw $e;
        }
    }
}
