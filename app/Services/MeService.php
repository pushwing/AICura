<?php

namespace App\Services;

use App\Exceptions\NotFoundException;
use App\Exceptions\PointException;
use App\Models\BoardModel;
use App\Models\BookingModel;
use App\Models\CallRequestModel;
use App\Models\FavoriteModel;
use App\Models\HealthPointLogModel;
use App\Models\UserDeviceModel;
use App\Models\UserModel;

/**
 * 외부(소비자) 앱 마이페이지 서비스 (이슈 #97)
 *
 * 프로필·회원탈퇴·기기등록 + 내 활동(상담·후기·예약·찜·헬스포인트) 조회를 한데 모은다.
 * 각 하위 목록은 해당 도메인 모델에 위임하고, 본 서비스는 소비자 응답으로 정규화한다.
 */
class MeService
{
    /**
     * 이미지 서빙 경로 prefix (캠페인 썸네일)
     */
    private const string IMAGE_PATH = 'uploads/campaigns/';

    private readonly UserModel $users;
    private readonly UserDeviceModel $devices;
    private readonly CallRequestModel $callRequests;
    private readonly BoardModel $boards;
    private readonly BookingModel $bookings;
    private readonly FavoriteModel $favorites;
    private readonly HealthPointLogModel $pointLogs;
    private readonly HealthPointService $points;

    public function __construct(
        ?UserModel $users = null,
        ?UserDeviceModel $devices = null,
        ?CallRequestModel $callRequests = null,
        ?BoardModel $boards = null,
        ?BookingModel $bookings = null,
        ?FavoriteModel $favorites = null,
        ?HealthPointLogModel $pointLogs = null,
        ?HealthPointService $points = null,
    ) {
        $this->users        = $users ?? model(UserModel::class);
        $this->devices      = $devices ?? model(UserDeviceModel::class);
        $this->callRequests = $callRequests ?? model(CallRequestModel::class);
        $this->boards       = $boards ?? model(BoardModel::class);
        $this->bookings     = $bookings ?? model(BookingModel::class);
        $this->favorites    = $favorites ?? model(FavoriteModel::class);
        $this->pointLogs    = $pointLogs ?? model(HealthPointLogModel::class);
        $this->points       = $points ?? service('healthPointService');
    }

    /**
     * 내 프로필
     *
     * @return array<string, mixed>
     *
     * @throws NotFoundException 탈퇴·미존재
     */
    public function profile(int $userId): array
    {
        $row = $this->users->getProfile($userId);
        if ($row === null) {
            throw NotFoundException::of('사용자를 찾을 수 없습니다.');
        }

        return $this->transformProfile($row);
    }

    /**
     * 프로필 수정 후 최신 프로필 반환
     *
     * @param array<string, mixed> $input
     *
     * @return array<string, mixed>
     */
    public function updateProfile(int $userId, array $input): array
    {
        $this->users->updateProfile($userId, $input);

        return $this->profile($userId);
    }

    /**
     * 회원 탈퇴 — soft delete.
     */
    public function withdraw(int $userId): void
    {
        $this->users->revokeAppTokens($userId);
        $this->users->delete($userId);
    }

    /**
     * 푸시 토큰·기기 등록
     */
    public function registerDevice(int $userId, string $pushToken, int $platform): void
    {
        $this->devices->register($userId, $pushToken, $platform);
    }

    /**
     * 내 상담 신청 내역
     *
     * @return array{items: array<int, array<string, mixed>>, total: int}
     */
    public function callRequests(int $userId, int $page, int $limit): array
    {
        $base  = $this->callRequests->getByUser($userId, $limit, ($page - 1) * $limit);
        $items = array_map(static function (array $r): array {
            $status = (int) $r['status'];

            return [
                'id'             => (int) $r['id'],
                'campaign_id'    => (int) $r['campaign_id'],
                'campaign_title' => $r['campaign_title'],
                'status'         => $status,
                'status_label'   => CallRequestModel::STATUSES[$status] ?? null,
                'created_at'     => $r['created_at'],
            ];
        }, $base['list']);

        return ['items' => $items, 'total' => $base['total']];
    }

    /**
     * 내가 쓴 후기
     *
     * @return array{items: array<int, array<string, mixed>>, total: int}
     */
    public function boards(int $userId, int $page, int $limit): array
    {
        // 소비자 마이페이지는 삭제(임시·완전)한 후기를 제외하고 본인이 노출 중인 후기만 보여준다.
        $base  = $this->boards->getByUser($userId, $limit, ($page - 1) * $limit, true);
        $items = array_map(static function (array $r): array {
            $type = (int) $r['type'];

            return [
                'id'         => (int) $r['id'],
                'type'       => $type,
                'type_label' => BoardModel::TYPES[$type] ?? null,
                'target_id'  => (int) $r['target_id'],
                'subject'    => $r['subject'],
                'rating'     => round((float) $r['rate_sum'], 2),
                'like_count' => (int) $r['like_count'],
                'created_at' => $r['created_at'],
            ];
        }, $base['list']);

        return ['items' => $items, 'total' => $base['total']];
    }

    /**
     * 내 예약
     *
     * @return array{items: array<int, array<string, mixed>>, total: int}
     */
    public function bookings(int $userId, int $page, int $limit): array
    {
        $base  = $this->bookings->getByUser($userId, $page, $limit);
        $items = array_map(static fn (array $r): array => [
            'id'            => (int) $r['id'],
            'hospital_id'   => (int) $r['hospital_id'],
            'hospital_name' => $r['hospital_name'],
            'status'        => (int) $r['status'],
            'book_date'     => $r['book_date'],
            'confirm_date'  => $r['confirm_date'],
            'created_at'    => $r['created_at'],
        ], $base['list']);

        return ['items' => $items, 'total' => $base['total']];
    }

    /**
     * 찜 목록 (유형별: campaign | hospital)
     *
     * @return array{items: array<int, array<string, mixed>>, total: int}
     */
    public function likes(int $userId, string $type, int $page, int $limit): array
    {
        $targetType = $type === FavoriteModel::TYPE_HOSPITAL ? FavoriteModel::TYPE_HOSPITAL : FavoriteModel::TYPE_CAMPAIGN;
        $base       = $this->favorites->listByUser($userId, $targetType, $page, $limit);

        if ($targetType === FavoriteModel::TYPE_CAMPAIGN) {
            $items = array_map(fn (array $r): array => [
                'type'          => 'campaign',
                'id'            => (int) $r['target_id'],
                'title'         => $r['ad_title'],
                'hospital_name' => $r['hospital_name'],
                'thumbnail_url' => $this->imageUrl($r['t1_image_name'] ?? null),
                'liked_at'      => $r['liked_at'],
            ], $base['list']);
        } else {
            $items = array_map(static fn (array $r): array => [
                'type'     => 'hospital',
                'id'       => (int) $r['target_id'],
                'name'     => $r['hospital_name'],
                'address'  => $r['hospital_address'],
                'liked_at' => $r['liked_at'],
            ], $base['list']);
        }

        return ['items' => $items, 'total' => $base['total']];
    }

    /**
     * 헬스포인트 잔액 + 변동 내역
     *
     * @return array{balance: int, logs: array<int, array<string, mixed>>, total: int}
     */
    public function healthPoint(int $userId, int $page, int $limit): array
    {
        $profile = $this->users->getProfile($userId);
        if ($profile === null) {
            throw NotFoundException::of('사용자를 찾을 수 없습니다.');
        }

        $base = $this->pointLogs->getByUser($userId, $page, $limit);
        $logs = array_map(static fn (array $r): array => [
            'id'            => (int) $r['id'],
            'amount'        => (int) $r['amount'],
            'balance_after' => (int) $r['balance_after'],
            'type'          => $r['type'],
            'memo'          => $r['memo'],
            'created_at'    => $r['created_at'],
        ], $base['list']);

        return [
            'balance' => (int) $profile['health_point'],
            'logs'    => $logs,
            'total'   => $base['total'],
        ];
    }

    /**
     * 헬스포인트 차감(사용) — 차감 후 잔액 반환 (이슈 #114)
     *
     * @return array{balance: int}
     *
     * @throws PointException 금액 오류·잔액 부족
     */
    public function redeemHealthPoint(int $userId, int $amount, ?string $memo = null): array
    {
        return ['balance' => $this->points->redeem($userId, $amount, $memo)];
    }

    /**
     * @param array<string, mixed> $row
     *
     * @return array<string, mixed>
     */
    private function transformProfile(array $row): array
    {
        return [
            'id'           => (int) $row['id'],
            'email'        => $row['email'],
            'username'     => $row['username'],
            'picture'      => $row['picture'],
            'phone'        => $row['phone'],
            'age'          => $row['age'] !== null ? (int) $row['age'] : null,
            'sex'          => $row['sex'],
            'job'          => $row['job'],
            'health_point' => (int) $row['health_point'],
            'provider'     => UserModel::PROVIDER_LABELS[(int) $row['provider']] ?? null,
            'created_at'   => $row['created_at'],
        ];
    }

    private function imageUrl(?string $name): ?string
    {
        if ($name === null || trim($name) === '') {
            return null;
        }

        return base_url(self::IMAGE_PATH . basename($name));
    }
}
