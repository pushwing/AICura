<?php

namespace App\Models;

use CodeIgniter\Model;
use Throwable;

/**
 * 광고주 owner 연결 초대 (이슈 #38)
 *
 * 대행사가 광고주(advertiser) 레코드에 owner 계정을 연결할 때 즉시 바인딩하지 않고
 * 초대를 생성한다. 당사자(병원유형 계정)가 로그인 후 수락해야 owner_user_id 가 확정된다.
 */
class AdvertiserOwnerInviteModel extends Model
{
    public const STATUS_PENDING   = 1;
    public const STATUS_ACCEPTED  = 2;
    public const STATUS_REJECTED  = 3;
    public const STATUS_EXPIRED   = 4;
    public const STATUS_CANCELLED = 5;

    /**
     * 초대 유효 기간(일)
     */
    public const TTL_DAYS = 7;

    protected $table         = 'advertiser_owner_invites';
    protected $primaryKey    = 'id';
    protected $useTimestamps = true;
    protected $returnType    = 'array';
    protected $allowedFields = [
        'advertiser_id',
        'agency_user_id',
        'invitee_user_id',
        'invitee_email',
        'status',
        'expires_at',
        'responded_at',
    ];

    /**
     * 초대 생성 — 만료 시각은 생성 +7일로 자동 설정
     *
     * @return false|int 생성된 초대 id, 실패 시 false
     */
    public function createInvite(int $advertiserId, int $agencyUserId, int $inviteeUserId, string $inviteeEmail): false|int
    {
        $id = $this->insert([
            'advertiser_id'   => $advertiserId,
            'agency_user_id'  => $agencyUserId,
            'invitee_user_id' => $inviteeUserId,
            'invitee_email'   => $inviteeEmail,
            'status'          => self::STATUS_PENDING,
            'expires_at'      => date('Y-m-d H:i:s', strtotime('+' . self::TTL_DAYS . ' days')),
        ]);

        return $id === false ? false : (int) $id;
    }

    /**
     * 해당 광고주에 응답 대기(pending·미만료) 초대가 있는지
     */
    public function hasPendingForAdvertiser(int $advertiserId): bool
    {
        return $this->where('advertiser_id', $advertiserId)
            ->where('status', self::STATUS_PENDING)
            ->where('expires_at >', date('Y-m-d H:i:s'))
            ->countAllResults() > 0;
    }

    /**
     * 특정 계정이 수신한 응답 대기(pending·미만료) 초대 목록 (광고주명 포함)
     *
     * @return list<array<string, mixed>>
     */
    public function findPendingForInvitee(int $inviteeUserId): array
    {
        /** @var list<array<string, mixed>> $rows */
        $rows = $this->db->table('advertiser_owner_invites i')
            ->select('i.id, i.advertiser_id, i.expires_at, i.created_at, a.hospital_name')
            ->join('advertisers a', 'a.id = i.advertiser_id')
            ->where('i.invitee_user_id', $inviteeUserId)
            ->where('i.status', self::STATUS_PENDING)
            ->where('i.expires_at >', date('Y-m-d H:i:s'))
            ->orderBy('i.id', 'DESC')
            ->get()
            ->getResultArray();

        return $rows;
    }

    /**
     * 초대 수락 — 원자적으로 owner_user_id 를 확정한다 (이슈 #38)
     *
     * 경쟁 조건·무단 선점 방어:
     *   ① 조건부 UPDATE 로 owner_user_id 가 비어 있을 때만 선점 (advertisers 측)
     *   ② invitee 가 이미 다른 광고주에 연결됐다면 uniq 인덱스 위반 → 실패 처리
     *   ③ 수락된 초대 외 같은 광고주의 다른 pending 초대는 일괄 만료
     *
     * @return array{ok: bool, advertiser_id?: int, hospital_id?: int, reason?: string}
     */
    public function acceptInvite(int $inviteId, int $inviteeUserId): array
    {
        $db  = $this->db;
        $now = date('Y-m-d H:i:s');

        // 본인 수신 + pending + 미만료 초대만 대상
        $invite = $this->where('id', $inviteId)
            ->where('invitee_user_id', $inviteeUserId)
            ->where('status', self::STATUS_PENDING)
            ->where('expires_at >', $now)
            ->first();

        if ($invite === null) {
            return ['ok' => false, 'reason' => 'not_found'];
        }

        $advertiserId = (int) $invite['advertiser_id'];

        // 빠른 경로: 초대 생성~수락 사이에 해당 계정이 이미 다른 광고주에 연결됐다면 즉시 차단
        // (서로 다른 광고주가 같은 이메일로 각각 초대 → 한쪽을 먼저 수락한 순차 케이스).
        $linkedElsewhere = $this->db->table('advertisers')
            ->where('owner_user_id', $inviteeUserId)
            ->countAllResults() > 0;
        if ($linkedElsewhere) {
            return ['ok' => false, 'reason' => 'already_linked'];
        }

        $db->transBegin();

        try {
            // ① owner_user_id 가 비어 있을 때만 선점 — 동시 수락 중 한 건만 성공
            $db->table('advertisers')
                ->where('id', $advertiserId)
                ->where('owner_user_id')
                ->update([
                    'owner_user_id' => $inviteeUserId,
                    'updated_at'    => $now,
                ]);

            if ($db->affectedRows() === 0) {
                // 이미 owner 가 연결됨 (다른 수락이 선점했거나 기존 바인딩 존재)
                $db->transRollback();

                return ['ok' => false, 'reason' => 'already_linked'];
            }

            // 수락 초대 확정
            $db->table('advertiser_owner_invites')
                ->where('id', $inviteId)
                ->update([
                    'status'       => self::STATUS_ACCEPTED,
                    'responded_at' => $now,
                    'updated_at'   => $now,
                ]);

            // 같은 광고주의 다른 pending 초대 일괄 만료
            $db->table('advertiser_owner_invites')
                ->where('advertiser_id', $advertiserId)
                ->where('id !=', $inviteId)
                ->where('status', self::STATUS_PENDING)
                ->update([
                    'status'     => self::STATUS_EXPIRED,
                    'updated_at' => $now,
                ]);

            // 확정된 광고주의 hospital_id 조회 (세션 갱신용)
            $advertiser = $db->table('advertisers')
                ->select('hospital_id')
                ->where('id', $advertiserId)
                ->get()
                ->getRowArray();

            $db->transCommit();

            return [
                'ok'            => true,
                'advertiser_id' => $advertiserId,
                'hospital_id'   => (int) ($advertiser['hospital_id'] ?? 0),
            ];
        } catch (Throwable $e) {
            $db->transRollback();

            // 동시 수락 경합: 위 빠른 경로를 통과한 두 요청이 같은 계정을 서로 다른 광고주에
            // UPDATE하면 uniq_advertisers_owner_user_id 위반으로 예외가 난다. 롤백 후 실제로
            // 다른 광고주에 연결됐는지 재확인해 500 대신 already_linked로 우아하게 처리한다.
            $linkedNow = $this->db->table('advertisers')
                ->where('owner_user_id', $inviteeUserId)
                ->countAllResults() > 0;
            if ($linkedNow) {
                return ['ok' => false, 'reason' => 'already_linked'];
            }

            throw $e;
        }
    }

    /**
     * 초대 거절 — 본인 수신 pending 초대만 거절 처리
     *
     * @return bool 거절 성공 여부
     */
    public function rejectInvite(int $inviteId, int $inviteeUserId): bool
    {
        $now = date('Y-m-d H:i:s');

        $this->where('id', $inviteId)
            ->where('invitee_user_id', $inviteeUserId)
            ->where('status', self::STATUS_PENDING)
            ->set([
                'status'       => self::STATUS_REJECTED,
                'responded_at' => $now,
                'updated_at'   => $now,
            ])
            ->update();

        return $this->db->affectedRows() > 0;
    }
}
