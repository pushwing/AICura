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

    public const STATUS_PENDING   = 0; // 예약 대기
    public const STATUS_CONFIRMED = 1; // 확정
    public const STATUS_CANCELLED = 2; // 취소

    /** @var array<int, string> 상태 라벨 */
    public const STATUSES = [
        0 => '대기',
        1 => '확정',
        2 => '취소',
    ];

    /**
     * 예약 생성 — 생성된 id 반환.
     *
     * @param array<string, mixed> $data
     */
    public function createBooking(array $data): int
    {
        return (int) $this->insert($data + ['status' => self::STATUS_PENDING], true);
    }

    /**
     * 본인 예약 상세 (병원명 포함) — 소유자 검증 포함.
     *
     * @return array<string, mixed>|null
     */
    public function getOwnedDetail(int $id, int $userId): ?array
    {
        return $this->db->table('bookings b')
            ->select('b.id, b.hospital_id, b.call_request_id, b.status, b.name, b.phone, b.book_date, b.confirm_date, b.created_at')
            ->select('h.name AS hospital_name', false)
            ->join('hospitals h', 'h.id = b.hospital_id', 'left')
            ->where('b.id', $id)
            ->where('b.user_id', $userId)
            ->get()
            ->getRowArray();
    }

    /**
     * 본인 예약 최소 조회 (권한·상태 확인용).
     *
     * @return array<string, mixed>|null
     */
    public function findOwned(int $id, int $userId): ?array
    {
        return $this->select('id, user_id, status')
            ->where('id', $id)
            ->where('user_id', $userId)
            ->first();
    }

    /**
     * 본인 예약 수정 (허용 필드만).
     *
     * @param array<string, mixed> $data
     */
    public function updateOwned(int $id, array $data): void
    {
        $allowed = ['name', 'phone', 'book_date'];
        $update  = array_intersect_key($data, array_flip($allowed));

        if ($update !== []) {
            $this->update($id, $update);
        }
    }

    /**
     * 예약 취소 — status=2.
     */
    public function cancel(int $id): void
    {
        $this->update($id, ['status' => self::STATUS_CANCELLED]);
    }

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
