<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * 결제관리 노출 정합성 백필 (이슈 #59)
 *
 * payments 레코드가 없는 기존 수주계약(contract_orders)을 결제관리에 노출하기 위해
 * 1수주건당 1payments 레코드를 생성한다.
 *   - 입금일(deposit_date) 있음 → status='paid'
 *   - 입금일 없음              → status='pending' (미입금/입금대기)
 */
class BackfillPaymentsFromContractOrders extends Migration
{
    public function up(): void
    {
        $now = date('Y-m-d H:i:s');

        // payments에 아직 없는 수주건 + 연결된 contract_id 조회
        /** @var list<array<string, mixed>> $orders */
        $orders = $this->db->table('contract_orders co')
            ->select('co.id, co.hospital_id, co.ad_price, co.deposit_date, co.created_at, coc.contract_id')
            ->join('contract_order_connects coc', 'coc.contract_order_id = co.id', 'left')
            ->join('payments p', 'p.contract_order_id = co.id', 'left')
            ->where('p.id')
            ->get()
            ->getResultArray();

        if ($orders === []) {
            return;
        }

        $rows = [];
        foreach ($orders as $o) {
            $paid    = !empty($o['deposit_date']);
            $created = $o['created_at'] ?: $now;

            $rows[] = [
                'user_id'           => null,
                'hospital_id'       => (int) $o['hospital_id'],
                'contract_id'       => (int) ($o['contract_id'] ?? 0) ?: null,
                'contract_order_id' => (int) $o['id'],
                'type'              => 1, // 가상계좌
                'amount'            => (int) $o['ad_price'],
                'status'            => $paid ? 'paid' : 'pending',
                'auth_date'         => $paid ? $o['deposit_date'] : null,
                'created_at'        => $created,
                'updated_at'        => $now,
            ];
        }

        $this->db->table('payments')->insertBatch($rows);
    }

    public function down(): void
    {
        // 백필로 생성된 레코드는 PG 결과값(trans_no 등)이 비어 있어 식별 가능하나,
        // 운영 데이터 손실 위험이 있어 자동 롤백은 수행하지 않는다.
    }
}
