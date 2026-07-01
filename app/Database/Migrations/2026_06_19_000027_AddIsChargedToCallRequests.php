<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * call_requests.is_charged — CPA 과금(소진) 처리 여부
 *
 * 이벤트 신청이 API로 들어오는 순간 CPA 과금이 1회 발생하며,
 * 이중과금을 방지하기 위해 과금 완료 시 1로 표시한다.
 */
class AddIsChargedToCallRequests extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('call_requests', [
            // 0 미과금, 1 과금완료
            'is_charged' => [
                'type'    => 'TINYINT',
                'default' => 0,
                'after'   => 'status',
            ],
        ]);

        $this->db->query('CREATE INDEX idx_call_requests_is_charged ON call_requests (is_charged)');
    }

    public function down(): void
    {
        $this->forge->dropColumn('call_requests', 'is_charged');
    }
}
