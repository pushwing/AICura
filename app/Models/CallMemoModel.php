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
    protected $table      = 'call_memos';
    protected $primaryKey = 'id';
    protected $useTimestamps = true;
    protected $returnType    = 'array';

    protected $allowedFields = [
        'call_request_id',
        'user_id',
        'memo',
    ];

    /** @var array<string, string> */
    protected $validationRules = [
        'call_request_id' => 'required|integer',
        'memo'            => 'required|max_length[500]',
    ];

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
