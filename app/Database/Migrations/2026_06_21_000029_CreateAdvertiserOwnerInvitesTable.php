<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\MySQLi\Connection;
use CodeIgniter\Database\Migration;

/**
 * advertiser_owner_invites 테이블 — owner 연결 초대/승인 플로우 (이슈 #38)
 *
 * 대행사가 광고주 레코드에 owner(광고주 본인 로그인 계정)를 즉시 바인딩하던 방식을
 * "초대 → 당사자 승인" 기반으로 전환한다. 수락 시에만 advertisers.owner_user_id 가 확정된다.
 *
 * status:
 *   1  pending   초대 발송, 응답 대기
 *   2  accepted  광고주가 수락 → owner_user_id 확정
 *   3  rejected  광고주가 거절
 *   4  expired   TTL(7일) 경과 또는 다른 초대 수락으로 자동 만료
 *   5  cancelled 대행사가 취소
 */
class CreateAdvertiserOwnerInvitesTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'BIGINT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'advertiser_id' => [
                'type'     => 'BIGINT',
                'unsigned' => true,
            ],
            // agency_user_id / invitee_user_id 는 users.id 참조. 기존 advertisers(agency_user_id,
            // owner_user_id)와 동일하게 users 테이블에는 의도적으로 FK를 걸지 않는다(기존 컨벤션 일치).
            'agency_user_id' => [
                'type'     => 'BIGINT',
                'unsigned' => true,
            ],
            // 초대 대상은 반드시 기존 병원유형 계정(resolveInvitee에서 검증) — NOT NULL 의도.
            'invitee_user_id' => [
                'type'     => 'BIGINT',
                'unsigned' => true,
            ],
            'invitee_email' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'status' => [
                'type'    => 'TINYINT',
                'default' => 1,
            ],
            'expires_at' => [
                'type' => 'DATETIME',
            ],
            'responded_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('advertiser_id', false, false, 'idx_advertiser_owner_invites_advertiser_id');
        // 로그인 시 수신 초대 조회용 — invitee_user_id + status 복합
        $this->forge->addKey(['invitee_user_id', 'status'], false, false, 'idx_advertiser_owner_invites_invitee');
        $this->forge->addForeignKey('advertiser_id', 'advertisers', 'id', 'CASCADE', 'CASCADE');

        $this->forge->createTable('advertiser_owner_invites');
    }

    public function down(): void
    {
        $isMySQLi = $this->db instanceof Connection;
        if ($isMySQLi) {
            $this->db->query('SET FOREIGN_KEY_CHECKS=0');
        }
        $this->forge->dropTable('advertiser_owner_invites');
        if ($isMySQLi) {
            $this->db->query('SET FOREIGN_KEY_CHECKS=1');
        }
    }
}
