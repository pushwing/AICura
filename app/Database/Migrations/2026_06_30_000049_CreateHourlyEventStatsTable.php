<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * hourly_event_stats 테이블 — 소비자 앱 액션 로그 시간별 집계 (이슈 #120)
 *
 * logs:aggregate 배치가 app_logs 를 1시간 단위로 롤업해 적재한다.
 *   - 시간별 조회: (stat_date, stat_hour) 단위 행을 그대로 사용
 *   - 일별 조회: stat_date 로 묶어 count 합산
 *
 *   stat_date   : 집계 날짜 (앱→서버 수신 시각 기준)
 *   stat_hour   : 0~23 시각 버킷
 *   event       : 이벤트 키 (AppLogEvent)
 *   campaign_id : 이벤트별 분해 키. 캠페인 무관 로그는 0 (NULL 은 uniq 인덱스에서 중복 허용되므로 0 센티넬 사용)
 *   count       : 해당 버킷 발생 건수
 *   uniq_users  : 해당 버킷 distinct user_id 수 (시간 버킷 내 기준 — 일별 합산 시 중복 가능)
 */
class CreateHourlyEventStatsTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'BIGINT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'stat_date' => ['type' => 'DATE'],
            'stat_hour' => [
                'type'       => 'TINYINT',
                'unsigned'   => true,
            ],
            'event' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'campaign_id' => [
                'type'     => 'BIGINT',
                'unsigned' => true,
                'default'  => 0,
            ],
            'count' => [
                'type'     => 'INT',
                'unsigned' => true,
                'default'  => 0,
            ],
            'uniq_users' => [
                'type'     => 'INT',
                'unsigned' => true,
                'default'  => 0,
            ],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addKey('id', true);
        // 멱등 upsert 키 — 재집계 시 동일 버킷을 갱신
        $this->forge->addUniqueKey(
            ['stat_date', 'stat_hour', 'event', 'campaign_id'],
            'uniq_hourly_event_stats_bucket',
        );
        // 일별 조회용 — stat_date 범위 스캔
        $this->forge->addKey('stat_date', false, false, 'idx_hourly_event_stats_date');

        $this->forge->createTable('hourly_event_stats');
    }

    public function down(): void
    {
        $this->forge->dropTable('hourly_event_stats', true);
    }
}
