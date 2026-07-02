<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * advertisers 포털 연동 컬럼 추가 (이슈 #32)
 *
 *   agency_user_id      소유 광고대행사 계정 (users.id, is_agency_account=1)
 *   owner_user_id       광고주 본인 로그인 계정 (users.id, 병원 유형)
 *   contract_agreed_at  광고주가 계약에 동의한 시각
 *                       — NULL 이면 "계약대기"(사용불가), 값이 있으면 "사용가능"
 */
class AddPortalColumnsToAdvertisers extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('advertisers', [
            'agency_user_id' => [
                'type'     => 'BIGINT',
                'unsigned' => true,
                'null'     => true,
                'after'    => 'network_parent_id',
            ],
            'owner_user_id' => [
                'type'     => 'BIGINT',
                'unsigned' => true,
                'null'     => true,
                'after'    => 'agency_user_id',
            ],
            'contract_agreed_at' => [
                'type'  => 'DATETIME',
                'null'  => true,
                'after' => 'owner_user_id',
            ],
        ]);

        $this->db->query('CREATE INDEX idx_advertisers_agency_user_id ON advertisers (agency_user_id)');
        // owner_user_id 는 광고주 본인 계정과 1:1 — 유니크 보장 (NULL 다중 허용: MySQL·SQLite 공통)
        $this->db->query('CREATE UNIQUE INDEX uniq_advertisers_owner_user_id ON advertisers (owner_user_id)');
    }

    public function down(): void
    {
        $this->forge->dropKey('advertisers', 'idx_advertisers_agency_user_id', false);
        $this->forge->dropKey('advertisers', 'uniq_advertisers_owner_user_id', false);
        $this->forge->dropColumn('advertisers', ['agency_user_id', 'owner_user_id', 'contract_agreed_at']);
    }
}
