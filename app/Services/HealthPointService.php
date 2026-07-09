<?php

namespace App\Services;

use App\Enums\PointReason;
use App\Exceptions\PointException;
use App\Models\HealthPointLogModel;
use App\Models\UserModel;

/**
 * 헬스포인트 적립/차감 서비스 (이슈 #114)
 *
 * 잔액(users.health_point)과 내역(health_point_logs)을 트랜잭션으로 원자적 갱신한다.
 * 모든 변동은 apply() 를 거쳐 balance_after 스냅샷을 남기며, 잔액이 음수가 되는 변동은 거부한다.
 *
 * 트리거 지점:
 *   - 가입       : AppAuthService (계정 생성 직후, 1회)
 *   - 후기 작성  : BoardService::create (후기당 1회, 후기와 동일 트랜잭션)
 *   - 후기 삭제  : BoardService::delete (적립분 회수)
 *   - 차감       : POST /api/v1/me/health-point/redeem
 */
class HealthPointService
{
    private readonly UserModel $users;
    private readonly HealthPointLogModel $logs;

    public function __construct(?UserModel $users = null, ?HealthPointLogModel $logs = null)
    {
        $this->users = $users ?? model(UserModel::class);
        $this->logs  = $logs ?? model(HealthPointLogModel::class);
    }

    /**
     * 회원가입 적립 — 가입 경로에서 1회만 호출된다.
     *
     * @return int 적립 후 잔액
     */
    public function awardSignup(int $userId): int
    {
        return $this->apply($userId, PointReason::Signup->defaultAmount(), PointReason::Signup, null);
    }

    /**
     * 후기 작성 적립 — 후기(board)당 1회. 이미 적립된 후기면 현재 잔액만 반환한다(멱등).
     *
     * @return int 적립 후 잔액
     */
    public function awardReview(int $userId, int $boardId): int
    {
        if ($this->logs->existsForRef($userId, PointReason::ReviewCreate->value, $boardId)) {
            return $this->currentBalance($userId);
        }

        return $this->apply(
            $userId,
            PointReason::ReviewCreate->defaultAmount(),
            PointReason::ReviewCreate,
            $boardId,
        );
    }

    /**
     * 후기 삭제 회수 — 해당 후기로 적립된 잔여 금액을 음수로 기록한다.
     *
     * 적립 이력이 없거나 이미 회수된 경우(순합 0 이하)에는 변동이 없다(멱등).
     *
     * @return int 회수 후 잔액
     */
    public function revokeReview(int $userId, int $boardId): int
    {
        $net = $this->logs->netForRef($userId, $boardId, [
            PointReason::ReviewCreate->value,
            PointReason::ReviewRevoke->value,
        ]);

        if ($net <= 0) {
            return $this->currentBalance($userId);
        }

        return $this->apply($userId, -$net, PointReason::ReviewRevoke, $boardId);
    }

    /**
     * 포인트 차감(사용) — 잔액 부족 시 PointException.
     *
     * @return int 차감 후 잔액
     *
     * @throws PointException 금액이 0 이하이거나 잔액 부족
     */
    public function redeem(int $userId, int $amount, ?string $memo = null): int
    {
        if ($amount <= 0) {
            throw PointException::invalidAmount();
        }

        return $this->apply($userId, -$amount, PointReason::Redeem, null, $memo);
    }

    /**
     * 변동 적용 — 트랜잭션 안에서 잔액 갱신 + 내역 기록(balance_after 스냅샷).
     *
     * 잔액 조회는 행 잠금(FOR UPDATE)으로 동시 변동 경합을 막는다(SQLite 등 미지원 드라이버는 무시).
     * 결과 잔액이 음수가 되면 차감을 거부한다.
     *
     * @return int 변동 후 잔액
     *
     * @throws PointException 잔액 부족
     */
    private function apply(int $userId, int $amount, PointReason $reason, ?int $refId, ?string $memo = null): int
    {
        $db = db_connect();
        $db->transStart();

        $current      = $this->lockedBalance($userId);
        $balanceAfter = $current + $amount;

        if ($balanceAfter < 0) {
            $db->transComplete();

            throw PointException::insufficientBalance();
        }

        $db->table('users')->where('id', $userId)->update(['health_point' => $balanceAfter]);

        $this->logs->insert([
            'user_id'       => $userId,
            'amount'        => $amount,
            'balance_after' => $balanceAfter,
            'type'          => $reason->value,
            'ref_id'        => $refId,
            'memo'          => $memo ?? $reason->label(),
        ]);

        $db->transComplete();

        return $balanceAfter;
    }

    /**
     * 잠금 조회한 현재 잔액. FOR UPDATE 미지원 드라이버(SQLite)는 일반 조회로 동작한다.
     */
    private function lockedBalance(int $userId): int
    {
        $db = db_connect();

        // MySQL은 FOR UPDATE 로 행을 잠가 동시 변동 경합을 막고, SQLite 등은 일반 조회로 동작한다.
        if ($db->getPlatform() === 'MySQLi') {
            $row = $db->query('SELECT health_point FROM users WHERE id = ? FOR UPDATE', [$userId])->getRowArray();

            return (int) ($row['health_point'] ?? 0);
        }

        return $this->currentBalance($userId);
    }

    private function currentBalance(int $userId): int
    {
        $row = $this->users->select('health_point')->where('id', $userId)->first();

        return (int) ($row['health_point'] ?? 0);
    }
}
