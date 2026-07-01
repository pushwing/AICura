<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * refund_requests — 신청DB 상태 변경(중복/결번/취소)에 따른 환불요청 (이슈 #52)
 *
 * 광고주가 신청 건을 중복(8)/결번(9)/취소(3)로 변경하면 운영자에게 환불요청이 생성된다.
 * 운영자가 승인하면 CPA 차감액을 deposits에 +로 복원하고, 거부하면 사유를 남겨 광고주에게 노출한다.
 *
 *   requested_status : 환불요청을 유발한 신청 상태 (3 취소, 8 중복, 9 결번)
 *   status           : 1 대기, 2 승인, 3 거부
 */
class CreateRefundRequestsTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'              => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'call_request_id' => ['type' => 'INT'],
            'hospital_id'     => ['type' => 'INT'],
            'requested_status' => ['type' => 'TINYINT'],
            'reason'          => ['type' => 'VARCHAR', 'constraint' => 500, 'null' => true],
            'status'          => ['type' => 'TINYINT', 'default' => 1],
            'reject_reason'   => ['type' => 'VARCHAR', 'constraint' => 500, 'null' => true],
            'deposit_id'      => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true],
            'requested_by'    => ['type' => 'INT', 'null' => true],
            'processed_by'    => ['type' => 'INT', 'null' => true],
            'processed_at'    => ['type' => 'DATETIME', 'null' => true],
            'created_at'      => ['type' => 'DATETIME', 'null' => true],
            'updated_at'      => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('call_request_id', false, false, 'idx_refund_requests_call_request_id');
        $this->forge->addKey('hospital_id', false, false, 'idx_refund_requests_hospital_id');
        $this->forge->addKey('status', false, false, 'idx_refund_requests_status');
        $this->forge->createTable('refund_requests');
    }

    public function down(): void
    {
        $this->forge->dropTable('refund_requests', true);
    }
}
