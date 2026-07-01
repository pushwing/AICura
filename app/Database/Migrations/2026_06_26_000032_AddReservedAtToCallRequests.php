<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * call_requests.reserved_at — 예약(status=5) 상태로 변경 시 입력받는 예약 일시 (이슈 #52)
 */
class AddReservedAtToCallRequests extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('call_requests', [
            'reserved_at' => [
                'type' => 'DATETIME',
                'null' => true,
                'after' => 'confirm_date',
            ],
        ]);
    }

    public function down(): void
    {
        $this->forge->dropColumn('call_requests', 'reserved_at');
    }
}
