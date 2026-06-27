<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * boards AI 후기 신뢰성 분석 컬럼 (이슈 #74)
 *
 * AI가 후기의 감성·신뢰성을 분석해 채우는 결과 컬럼과
 * 비동기 분석 큐 상태 컬럼을 추가한다.
 *
 *   ai_sentiment    감성 (positive / neutral / negative)
 *   ai_trust_score  신뢰점수 (0~100)
 *   ai_flags        플래그 목록 JSON (spam / fake / exaggeration / medical_overclaim ...)
 *   ai_reason       판단 근거 한 줄
 *   ai_status       분석 큐 상태 (0 미분석 / 1 대기 / 2 완료 / 3 실패)
 *
 * Redis 등 별도 큐 인프라 없이 ai_status 컬럼을 큐로 사용한다(이슈 #72와 동일 정책).
 * boards는 이 앱이 아니라 외부 시스템이 직접 INSERT하므로, 신규 행이 자동으로 큐에
 * 들어오도록 ai_status 의 DEFAULT 를 1(대기)로 둔다. 마이그레이션 시점의 기존 행은
 * 첫 실행 대량 분석을 피하기 위해 0(미분석)으로 되돌린다 — 필요 시 운영자가 재분석한다.
 */
class AddAiFieldsToBoards extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('boards', [
            'ai_sentiment' => [
                'type'       => 'VARCHAR',
                'constraint' => 10,
                'null'       => true,
                'after'      => 'contents',
            ],
            'ai_trust_score' => [
                'type'     => 'TINYINT',
                'unsigned' => true,
                'null'     => true,
                'after'    => 'ai_sentiment',
            ],
            'ai_flags' => [
                'type'  => 'JSON',
                'null'  => true,
                'after' => 'ai_trust_score',
            ],
            'ai_reason' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'after'      => 'ai_flags',
            ],
            // 0 미분석, 1 대기(pending), 2 완료, 3 실패 — 신규 INSERT는 자동으로 대기
            'ai_status' => [
                'type'    => 'TINYINT',
                'default' => 1,
                'after'   => 'ai_reason',
            ],
        ]);

        // 컬럼 추가로 기존 행이 모두 default(1)로 채워지므로, 기존 후기는 0(미분석)으로 되돌린다.
        $this->db->table('boards')->update(['ai_status' => 0], ['ai_status' => 1]);

        // reviews:analyze 커맨드가 대기(1) 건을 스캔하고, 목록에서 점수·감성 정렬/필터에 사용
        $this->db->query('CREATE INDEX idx_boards_ai_status ON boards (ai_status)');
        $this->db->query('CREATE INDEX idx_boards_ai_trust_score ON boards (ai_trust_score)');
        $this->db->query('CREATE INDEX idx_boards_ai_sentiment ON boards (ai_sentiment)');
    }

    public function down(): void
    {
        $this->forge->dropColumn('boards', ['ai_sentiment', 'ai_trust_score', 'ai_flags', 'ai_reason', 'ai_status']);
    }
}
