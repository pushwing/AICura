<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * app_logs 테이블 — 소비자 앱 텔레메트리 가공 로그 (이슈 #115)
 *
 * 원시 로그는 writable/logs/raw/ 에 보존되고, Consumer(logs:consume)가
 * 큐에서 꺼낸 항목을 가공해 본 테이블에 적재한다.
 *
 *   level              : 로그 레벨 (info/warn/error 등)
 *   event              : 이벤트 키 (예: screen_view)
 *   message            : 사람이 읽는 메시지
 *   context            : 부가 컨텍스트 JSON (문자열 저장 — 크로스 DB 호환)
 *   client_received_at : API 가 수신한 시각(앱→서버)
 */
class CreateAppLogsTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'BIGINT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'user_id' => [
                'type'     => 'BIGINT',
                'unsigned' => true,
                'null'     => true,
            ],
            'level' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'default'    => 'info',
            ],
            'event' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'message' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'context' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'client_received_at' => ['type' => 'DATETIME', 'null' => true],
            'created_at'         => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('level', false, false, 'idx_app_logs_level');
        $this->forge->addKey('event', false, false, 'idx_app_logs_event');
        $this->forge->addKey('created_at', false, false, 'idx_app_logs_created_at');

        $this->forge->createTable('app_logs');
    }

    public function down(): void
    {
        $this->forge->dropTable('app_logs', true);
    }
}
