<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * 이벤트 신청 메모 (call_memos)
 *
 * 신청 건(call_request)당 N개의 운영 메모.
 */
class CallMemoModel extends Model
{
    /**
     * 시스템 자동 메모(상태 변경 히스토리)의 접두사 — 분석 입력에서 제외
     */
    private const string SYSTEM_MEMO_PREFIX = '[상태변경]';

    protected $table         = 'call_memos';
    protected $primaryKey    = 'id';
    protected $useTimestamps = true;
    protected $returnType    = 'array';
    protected $allowedFields = [
        'call_request_id',
        'user_id',
        'memo',
    ];

    /**
     * @var array<string, string>
     */
    protected $validationRules = [
        'call_request_id' => 'required|integer',
        'memo'            => 'required|max_length[500]',
    ];

    /**
     * AI 분석 입력용 메모 텍스트 목록 (오래된 순, 시스템 메모 제외)
     *
     * 상담사가 직접 작성한 운영 메모만 추려 반환한다. 상태 변경 히스토리
     * (`[상태변경] …`)는 분석에 불필요한 노이즈이므로 걸러낸다.
     *
     * @return array<int, string>
     */
    public function getMemosForAnalysis(int $callRequestId): array
    {
        $rows = $this->select('memo')
            ->where('call_request_id', $callRequestId)
            ->orderBy('id', 'ASC')
            ->findAll();

        $memos = [];

        foreach ($rows as $row) {
            $memo = trim((string) ($row['memo'] ?? ''));
            if ($memo === '' || str_starts_with($memo, self::SYSTEM_MEMO_PREFIX)) {
                continue;
            }
            $memos[] = $memo;
        }

        return $memos;
    }

    /**
     * 신청 건의 메모 목록 (작성자명 포함, 최신순)
     *
     * @return array<int, array<string, mixed>>
     */
    public function getListByRequest(int $callRequestId): array
    {
        return $this->db->table('call_memos cm')
            ->select('cm.id, cm.memo, cm.user_id, cm.created_at')
            ->select('u.username AS admin_name', false)
            ->join('users u', 'u.id = cm.user_id', 'left')
            ->where('cm.call_request_id', $callRequestId)
            ->orderBy('cm.id', 'DESC')
            ->get()
            ->getResultArray();
    }
}
