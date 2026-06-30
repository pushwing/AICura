<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * health_point_logs.ref_id 추가 — 적립/차감 로그를 발생원과 연결 (이슈 #114)
 *
 * 후기 적립처럼 "대상당 1회" 멱등 보장과 후기 삭제 시 회수 판단을 위해
 * 로그가 어떤 리소스(예: board id)에서 발생했는지 식별한다.
 *
 *   ref_id : 발생원 식별자 (가입·차감 등 무관한 사유는 NULL)
 *
 * 멱등 조회는 (type, ref_id) 복합 인덱스로 처리한다.
 */
class AddRefIdToHealthPointLogs extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('health_point_logs', [
            'ref_id' => [
                'type'     => 'BIGINT',
                'unsigned' => true,
                'null'     => true,
                'after'    => 'type',
            ],
        ]);

        // CREATE INDEX 구문은 MySQL·SQLite 공통이다.
        $this->db->query('CREATE INDEX idx_health_point_logs_type_ref ON health_point_logs (type, ref_id)');
    }

    public function down(): void
    {
        // DROP INDEX 구문은 드라이버마다 다르다 (MySQL은 테이블 지정 필요, SQLite는 인덱스명만).
        $sql = $this->db->getPlatform() === 'MySQLi'
            ? 'DROP INDEX idx_health_point_logs_type_ref ON health_point_logs'
            : 'DROP INDEX idx_health_point_logs_type_ref';
        $this->db->query($sql);

        $this->forge->dropColumn('health_point_logs', 'ref_id');
    }
}
