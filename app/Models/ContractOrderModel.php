<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * 추가계약건 모델 (병원당 N개)
 *
 * contract_status:
 *  1 정상
 *  2 발행환불
 *  3 발행취소
 *  4 계약취소
 *  5 계약환불
 *  6 이월종료 (재계약으로 이월 처리됨)
 *
 * deposit status:
 *  2  계약충전 / 3  DB소진 / 4  기타+ / 5  기타-
 *  6  발행환불 / 7  계약환불 / 9  발행취소 / 10 계약취소
 *  11 이월소진 / 12 이월충전
 */
class ContractOrderModel extends Model
{
    protected $table      = 'contract_orders';
    protected $primaryKey = 'id';
    protected $useTimestamps = true;
    protected $returnType    = 'array';

    protected $allowedFields = [
        'hospital_id',
        'hospital_name',
        'contract_type',
        'ad_type',
        'ad_type2',
        'ad_price',
        'contract_status',
        'deposit_date',
        'parent_id',
        'title',
        'contract_date',
        'contract_agree_date',
        'agree',
        'deposit_check_id',
        'main_contract',
        'is_delete',
        'purchase_owner_id',
        'is_network',
        'hospital_type',
        'hospital_charge_name',
        'hospital_charge_phone',
        'hospital_charge_email',
        'agency_user_id',
        'manage_user_id',
        'agency_company_name',
        'agency_company_fee_rate',
        'agency_company_charge_name',
        'agency_company_charge_phone',
        'agency_company_charge_email',
        'tax_charge_name',
        'tax_charge_email',
        'tax_business_no',
        'tax_issue_date',
        'tax_issue_request_date',
        'ads_count',
        'ads_count_bonus',
        'pay_method',
        'memo',
    ];

    /** @var array<string, string> */
    protected $validationRules = [
        'hospital_id'    => 'required|integer',
        'hospital_name'  => 'required|max_length[255]',
        'contract_type'  => 'required|in_list[1,2]',
        'ad_type'        => 'required|in_list[1,2]',
        'ad_type2'       => 'required|in_list[1,2,3,4,5]',
        'ad_price'       => 'required|integer|greater_than[0]',
    ];

    /**
     * 수주계약 목록 (검색·페이징)
     *
     * @param array<string, mixed> $params
     * @return array<string, mixed>
     */
    public function getList(array $params): array
    {
        $builder = $this->db->table('contract_orders co')
            ->select('co.*, c.id AS contract_id, c.title AS contract_title, c.pay_type')
            ->join('contract_order_connects coc', 'coc.contract_order_id = co.id')
            ->join('contracts c', 'c.id = coc.contract_id');

        if (!empty($params['hospital_name'])) {
            $builder->like('co.hospital_name', $params['hospital_name']);
        }
        if (!empty($params['contract_status'])) {
            $builder->where('co.contract_status', $params['contract_status']);
        }
        if (!empty($params['ad_type2'])) {
            $builder->where('co.ad_type2', $params['ad_type2']);
        }

        $total = (clone $builder)->countAllResults(false);

        $page  = max(1, (int) ($params['page'] ?? 1));
        $limit = (int) ($params['limit'] ?? 20);

        $list = $builder
            ->orderBy('co.id', 'DESC')
            ->limit($limit, ($page - 1) * $limit)
            ->get()
            ->getResultArray();

        return ['list' => $list, 'total' => $total];
    }

    /**
     * 수주계약 상세 (잔액 집계 포함)
     *
     * @return array<string, mixed>|null
     */
    public function getDetail(int $id): ?array
    {
        $row = $this->db->table('contract_orders co')
            ->select('co.*, c.id AS contract_id, c.title AS contract_title, c.pay_type')
            ->join('contract_order_connects coc', 'coc.contract_order_id = co.id')
            ->join('contracts c', 'c.id = coc.contract_id')
            ->where('co.id', $id)
            ->get()
            ->getRowArray();

        if ($row === null) {
            return null;
        }

        $row['balance'] = $this->getBalance((int) $row['contract_id'], $id);

        return $row;
    }

    /**
     * 잔액 계산 = 충전금액 - 소진금액
     * 충전: status IN (2, 4, 12)
     * 소진: status IN (3, 5, 6, 7, 8, 9, 10, 11)
     */
    public function getBalance(int $contractId, int $contractOrderId): int
    {
        $charged = (int) $this->db->table('deposits')
            ->selectSum('price')
            ->where('contract_id', $contractId)
            ->where('contract_order_id', $contractOrderId)
            ->whereIn('status', [2, 4, 12])
            ->get()
            ->getRowArray()['price'];

        $used = (int) $this->db->table('deposits')
            ->selectSum('price')
            ->where('contract_id', $contractId)
            ->where('contract_order_id', $contractOrderId)
            ->whereIn('status', [3, 5, 6, 7, 8, 9, 10, 11])
            ->get()
            ->getRowArray()['price'];

        return $charged - $used;
    }

    /**
     * 신규 계약 등록 트랜잭션
     * - contracts 테이블에 메인 계약 없으면 생성
     * - contract_orders에 추가계약건 insert
     * - contract_order_connects에 매핑 insert
     *
     * @param array<string, mixed> $data
     * @return int 생성된 contract_order id
     */
    public function registerWithContract(array $data): int
    {
        $db = $this->db;
        $db->transBegin();

        try {
            $contractId = (int) ($data['contract_id'] ?? 0);

            if ($data['contract_type'] === 1 || $contractId === 0) {
                // 신규: contracts 테이블에 메인 계약 생성
                $db->table('contracts')->insert([
                    'hospital_id'   => $data['hospital_id'],
                    'hospital_name' => $data['hospital_name'],
                    'title'         => $data['title'] ?? $data['hospital_name'],
                    'pay_type'      => $data['pay_type'] ?? 1,
                    'created_at'    => date('Y-m-d H:i:s'),
                    'updated_at'    => date('Y-m-d H:i:s'),
                ]);
                $contractId = $db->insertID();
            }

            $now = date('Y-m-d H:i:s');
            $orderFields = array_intersect_key($data, array_flip($this->allowedFields));
            $orderFields['created_at'] = $now;
            $orderFields['updated_at'] = $now;

            // 재계약: parent_id 설정
            if ((int) $data['contract_type'] === 2 && !empty($data['parent_order_id'])) {
                $orderFields['parent_id'] = $data['parent_order_id'];
            }

            $db->table('contract_orders')->insert($orderFields);
            $orderId = $db->insertID();

            // 매핑 테이블 insert
            $db->table('contract_order_connects')->insert([
                'contract_id'       => $contractId,
                'contract_order_id' => $orderId,
                'created_at'        => $now,
            ]);

            // 재계약: 이전 수주계약 잔액 이월 처리
            if ((int) $data['contract_type'] === 2 && !empty($data['parent_order_id'])) {
                $this->carryOverBalance(
                    $contractId,
                    (int) $data['parent_order_id'],
                    $orderId,
                    (int) ($data['agency_user_id'] ?? 0)
                );
            }

            $db->transCommit();

            return $orderId;
        } catch (\Throwable $e) {
            $db->transRollback();
            throw $e;
        }
    }

    /**
     * 재계약 시 이전 수주계약 잔액 이월
     * - 이전 계약에 이월소진(status=11) deposit 입력
     * - 신규 계약에 이월충전(status=12) deposit 입력
     * - 이전 수주계약 status → 6(이월종료)
     */
    private function carryOverBalance(
        int $contractId,
        int $prevOrderId,
        int $newOrderId,
        int $userId
    ): void {
        $balance = $this->getBalance($contractId, $prevOrderId);
        $now     = date('Y-m-d H:i:s');
        $isMinus = $balance < 0 ? 1 : 0;

        $this->db->table('deposits')->insert([
            'status'            => 11, // 이월소진
            'is_minus'          => $isMinus,
            'contract_id'       => $contractId,
            'contract_order_id' => $prevOrderId,
            'users_id'          => $userId,
            'price'             => $balance,
            'created_at'        => $now,
            'updated_at'        => $now,
        ]);

        $this->db->table('deposits')->insert([
            'status'            => 12, // 이월충전
            'is_minus'          => $isMinus,
            'contract_id'       => $contractId,
            'contract_order_id' => $newOrderId,
            'users_id'          => $userId,
            'price'             => $balance,
            'created_at'        => $now,
            'updated_at'        => $now,
        ]);

        // 이전 수주계약 이월종료 처리
        $this->db->table('contract_orders')
            ->where('id', $prevOrderId)
            ->update(['contract_status' => 6, 'updated_at' => $now]);
    }

    /**
     * 입금 확인 처리
     */
    public function confirmDeposit(int $orderId, int $userId): bool
    {
        $now = date('Y-m-d H:i:s');

        // 수주계약 입금일 업데이트
        $this->db->table('contract_orders')
            ->where('id', $orderId)
            ->update(['deposit_date' => $now, 'updated_at' => $now]);

        // 연결된 contract_id 조회
        $connect = $this->db->table('contract_order_connects')
            ->where('contract_order_id', $orderId)
            ->get()
            ->getRowArray();

        if ($connect === null) {
            return false;
        }

        // 수주계약 정보 조회
        $order = $this->find($orderId);
        if ($order === null) {
            return false;
        }

        // 계약충전(status=2) deposit 입력
        $this->db->table('deposits')->insert([
            'status'            => 2,
            'is_minus'          => 0,
            'contract_id'       => $connect['contract_id'],
            'contract_order_id' => $orderId,
            'users_id'          => $userId,
            'price'             => $order['ad_price'],
            'created_at'        => $now,
            'updated_at'        => $now,
        ]);

        return true;
    }
}
