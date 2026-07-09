<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * call_requests AI 리드 분석 컬럼 (이슈 #72)
 *
 * AI가 신청 내용·상담 메모를 분석해 채우는 결과 컬럼과
 * 비동기 분석 큐 상태 컬럼을 추가한다.
 *
 *   ai_score        전환 가능성 점수 (0~100)
 *   ai_summary      한 줄 요약
 *   ai_next_action  추천 다음 상담 액션
 *   ai_status       분석 큐 상태 (0 미분석 / 1 대기 / 2 완료 / 3 실패)
 *
 * Redis 등 별도 큐 인프라 없이 ai_status 컬럼을 큐로 사용한다 —
 * 신청·메모 저장 시 1(대기)로 표시하고, leads:analyze 커맨드가 소비한다.
 */
class AddAiFieldsToCallRequests extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('call_requests', [
            'ai_score' => [
                'type'     => 'TINYINT',
                'unsigned' => true,
                'null'     => true,
                'after'    => 'content',
            ],
            'ai_summary' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'after'      => 'ai_score',
            ],
            'ai_next_action' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'after'      => 'ai_summary',
            ],
            // 0 미분석, 1 대기(pending), 2 완료, 3 실패
            'ai_status' => [
                'type'    => 'TINYINT',
                'default' => 0,
                'after'   => 'ai_next_action',
            ],
        ]);

        // leads:analyze 커맨드가 대기(1) 건을 스캔하므로 인덱스 필수
        $this->db->query('CREATE INDEX idx_call_requests_ai_status ON call_requests (ai_status)');
    }

    public function down(): void
    {
        $this->forge->dropColumn('call_requests', ['ai_score', 'ai_summary', 'ai_next_action', 'ai_status']);
    }
}
