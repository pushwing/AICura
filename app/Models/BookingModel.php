<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * 예약 모델 (bookings) — 외부(소비자) 앱 (이슈 #97에서 조회만 우선 사용, 전체 CRUD는 #101)
 *
 * status: 0 대기 · 1 확정 · 2 취소 (운영 정의에 따름)
 */
class BookingModel extends Model
{
    protected $table      = 'bookings';
    protected $primaryKey = 'id';
    protected $useTimestamps = true;
    protected $returnType    = 'array';

    protected $allowedFields = [
        'user_id',
        'hospital_id',
        'call_request_id',
        'status',
        'name',
        'phone',
        'book_date',
        'confirm_date',
    ];

    /**
     * 사용자 예약 목록 (병원명 포함, 최신순, 페이징)
     *
     * @return array{list: array<int, array<string, mixed>>, total: int}
     */
    public function getByUser(int $userId, int $page, int $limit): array
    {
        $builder = $this->db->table('bookings b')
            ->select('b.id, b.hospital_id, b.status, b.book_date, b.confirm_date, b.created_at')
            ->select('h.name AS hospital_name', false)
            ->join('hospitals h', 'h.id = b.hospital_id', 'left')
            ->where('b.user_id', $userId);

        $total = (clone $builder)->countAllResults(false);

        $list = $builder
            ->orderBy('b.id', 'DESC')
            ->limit($limit, ($page - 1) * $limit)
            ->get()
            ->getResultArray();

        return ['list' => $list, 'total' => (int) $total];
    }
}
