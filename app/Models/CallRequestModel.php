<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * 이벤트 신청 DB (call_requests)
 *
 * 앱/웹에서 이벤트 신청 시 생성되는 CPA 과금의 핵심 테이블.
 *
 * status (9단계):
 *   1 미확인, 2 부재중, 3 취소, 4 기타, 5 예약,
 *   6 예약취소, 7 내원완료, 8 중복, 9 결번
 */
class CallRequestModel extends Model
{
    protected $table      = 'call_requests';
    protected $primaryKey = 'id';
    protected $useTimestamps = true;
    protected $returnType    = 'array';

    protected $allowedFields = [
        'hospital_id',
        'campaign_id',
        'user_id',
        'device',
        'status',
        'is_charged',
        'confirm_date',
        'name',
        'phone',
        'content',
        'call_time',
        'age',
        'sex',
        'privacy_agree',
        'supply_third_party_agree',
        'event_cost',
        'funnel',
        'region',
        'finger_print',
        'is_delete',
        'parent_id',
        'is_save_phone',
    ];

    /** @var array<int, string> 신청 상태 라벨 (1~9) */
    public const STATUSES = [
        1 => '미확인',
        2 => '부재중',
        3 => '취소',
        4 => '기타',
        5 => '예약',
        6 => '예약취소',
        7 => '내원완료',
        8 => '중복',
        9 => '결번',
    ];

    /** @var array<int, string> 디바이스 라벨 */
    public const DEVICES = [
        1 => '안드로이드',
        2 => 'iOS',
        3 => '웹',
    ];

    /**
     * 신청 목록 (병원별·캠페인별·상태별 필터, 페이징)
     *
     * @param array<string, mixed> $params
     * @return array{list: array<int, array<string, mixed>>, total: int}
     */
    public function getList(array $params): array
    {
        $builder = $this->db->table('call_requests cr')
            ->select('cr.id, cr.hospital_id, cr.campaign_id, cr.status, cr.is_charged, cr.name, cr.phone, cr.event_cost, cr.confirm_date, cr.created_at')
            ->select('h.name AS hospital_name', false)
            ->select('c.ad_title AS campaign_title', false)
            ->join('hospitals h', 'h.id = cr.hospital_id', 'left')
            ->join('campaigns c', 'c.id = cr.campaign_id', 'left')
            ->where('cr.is_delete', 0);

        if (!empty($params['hospital_id'])) {
            $builder->where('cr.hospital_id', (int) $params['hospital_id']);
        }
        if (!empty($params['campaign_id'])) {
            $builder->where('cr.campaign_id', (int) $params['campaign_id']);
        }
        if (!empty($params['status'])) {
            $builder->where('cr.status', (int) $params['status']);
        }
        if (!empty($params['keyword'])) {
            $builder->groupStart()
                ->like('cr.name', $params['keyword'])
                ->orLike('cr.phone', $params['keyword'])
                ->groupEnd();
        }

        $total = (clone $builder)->countAllResults(false);

        $page  = max(1, (int) ($params['page'] ?? 1));
        $limit = (int) ($params['limit'] ?? 20);

        $list = $builder
            ->orderBy('cr.id', 'DESC')
            ->limit($limit, ($page - 1) * $limit)
            ->get()
            ->getResultArray();

        return ['list' => $list, 'total' => $total];
    }

    /**
     * 신청 상세 (병원·캠페인명 + 메모 목록 포함)
     *
     * @return array<string, mixed>|null
     */
    public function getDetail(int $id): ?array
    {
        $row = $this->db->table('call_requests cr')
            ->select('cr.*')
            ->select('h.name AS hospital_name', false)
            ->select('c.ad_title AS campaign_title', false)
            ->join('hospitals h', 'h.id = cr.hospital_id', 'left')
            ->join('campaigns c', 'c.id = cr.campaign_id', 'left')
            ->where('cr.id', $id)
            ->where('cr.is_delete', 0)
            ->get()
            ->getRowArray();

        if ($row === null) {
            return null;
        }

        $row['memos'] = model(CallMemoModel::class)->getListByRequest($id);

        return $row;
    }

    /**
     * 상태 변경 (9단계 중 유효한 값으로만)
     *
     * 미확인(1)에서 다른 상태로 처음 전환되는 시점에 confirm_date를 기록한다.
     *
     * @throws \RuntimeException 신청 건 없음 또는 유효하지 않은 상태
     */
    public function changeStatus(int $id, int $status): void
    {
        if (!isset(self::STATUSES[$status])) {
            throw new \RuntimeException('유효하지 않은 상태입니다.');
        }

        $request = $this->select('id, status, confirm_date')
            ->where('is_delete', 0)
            ->find($id);
        if ($request === null) {
            throw new \RuntimeException('신청 건을 찾을 수 없습니다.');
        }

        $update = ['status' => $status];

        // 최초 확인 시점 기록 (미확인 → 확인 계열)
        if ((int) $request['status'] === 1 && $status !== 1 && empty($request['confirm_date'])) {
            $update['confirm_date'] = date('Y-m-d H:i:s');
        }

        $this->update($id, $update);
    }

    /**
     * CPA 과금 (deposits status=3 소진 1회 기록) — 멱등 처리.
     *
     * 이벤트 신청이 API로 들어오는 순간 호출되도록 설계되었으며,
     * is_charged 플래그로 이중과금을 방지한다. (Admin에서는 자동 호출하지 않음)
     *
     * 동시 호출 경쟁 조건은 트랜잭션 내 조건부 UPDATE(`WHERE is_charged = 0`)로
     * 선점하여 차단한다 — 선점에 성공한 호출만 deposits에 1건을 기록한다.
     *
     * @return bool 실제 과금 발생 여부 (이미 과금/금액 없음/계약 미연결 시 false)
     */
    public function chargeCpa(int $id, int $userId): bool
    {
        // 사전 검증 (빠른 종료 — 실제 멱등 보장은 아래 조건부 UPDATE)
        $request = $this->find($id);
        if ($request === null || (int) $request['is_charged'] === 1) {
            return false;
        }

        $eventCost = (int) $request['event_cost'];
        if ($eventCost <= 0) {
            return false;
        }

        // 캠페인 → 계약 연결 정보 조회
        $campaign = $this->db->table('campaigns')
            ->select('contract_id, contract_order_id')
            ->where('id', (int) $request['campaign_id'])
            ->get()
            ->getRowArray();

        if ($campaign === null
            || empty($campaign['contract_id'])
            || empty($campaign['contract_order_id'])
        ) {
            return false;
        }

        $db = $this->db;
        $db->transBegin();

        try {
            // 원자적 선점: is_charged 0 → 1. 동시 호출 중 한 건만 성공한다.
            $db->table('call_requests')
                ->where('id', $id)
                ->where('is_charged', 0)
                ->update(['is_charged' => 1, 'updated_at' => date('Y-m-d H:i:s')]);

            if ($db->affectedRows() === 0) {
                // 이미 다른 호출이 과금을 선점함 → 이중과금 방지
                $db->transRollback();

                return false;
            }

            $now = date('Y-m-d H:i:s');

            $db->table('deposits')->insert([
                'status'            => 3, // DB소진
                'is_minus'          => 1,
                'contract_id'       => (int) $campaign['contract_id'],
                'contract_order_id' => (int) $campaign['contract_order_id'],
                'users_id'          => $userId ?: null,
                'price'             => $eventCost,
                'note'              => 'CPA 소진 (call_request:' . $id . ')',
                'created_at'        => $now,
                'updated_at'        => $now,
            ]);

            $db->transCommit();

            return true;
        } catch (\Throwable $e) {
            $db->transRollback();
            throw $e;
        }
    }
}
