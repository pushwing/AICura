<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * 환불 내역 (refunds)
 *
 * 세틀뱅크 환불 API 결과를 보존하는 감사용 기록.
 * deposits(원장)와 별개로 PG 환불 트랜잭션 단위로 1건 저장한다.
 *
 * term_type:  1 전체, 2 부분
 * type:       1 가상계좌, 2 카드
 * result_code: 1 성공, 2 실패
 */
class RefundModel extends Model
{
    protected $table      = 'refunds';
    protected $primaryKey = 'id';
    protected $useTimestamps = true;
    protected $returnType    = 'array';

    protected $allowedFields = [
        'user_id',
        'payment_id',
        'trans_no',
        'term_type',
        'contract_id',
        'contract_order_id',
        'type',
        'amount',
        'result_code',
        'result1',
        'result2',
    ];

    public const TERM_FULL    = 1;
    public const TERM_PARTIAL = 2;

    public const RESULT_SUCCESS = 1;
    public const RESULT_FAIL    = 2;

    /**
     * 결제 건의 환불 내역 (최신순)
     *
     * @return array<int, array<string, mixed>>
     */
    public function getListByPayment(int $paymentId): array
    {
        return $this->where('payment_id', $paymentId)
            ->orderBy('id', 'DESC')
            ->findAll();
    }
}
