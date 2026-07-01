<?php

namespace App\Services;

use App\Exceptions\NotFoundException;
use App\Models\BoardModel;
use App\Models\DepartmentModel;
use App\Models\FavoriteModel;
use App\Models\HospitalModel;

/**
 * 외부(소비자) 앱 병원 서비스 (이슈 #99)
 *
 * 병원 목록·상세·소속 이벤트·후기·찜을 담당한다.
 * - 소속 이벤트 조회는 EventService 를 재사용해 노출조건·is_liked·캐시를 그대로 활용한다.
 * - 병원 후기는 boards(type=2) 를, 별점 요약은 board_summaries(type=2) 를 사용한다.
 */
class HospitalService
{
    /** 병원 망 구분 라벨 (hospitals.type) */
    private const array TYPE_LABELS = [
        1 => '일반',
        2 => '네트워크 모점',
        3 => '네트워크 자점',
    ];

    /** 목록 캐시 TTL (초) */
    private const int LIST_TTL = 300;

    /** 단건 상세 캐시 TTL (초) */
    private const int DETAIL_TTL = 300;

    private readonly HospitalModel $hospitals;
    private readonly BoardModel $boards;
    private readonly FavoriteModel $favorites;
    private readonly DepartmentModel $departments;
    private readonly EventService $events;

    public function __construct(
        ?HospitalModel $hospitals = null,
        ?BoardModel $boards = null,
        ?FavoriteModel $favorites = null,
        ?DepartmentModel $departments = null,
        ?EventService $events = null,
    ) {
        $this->hospitals   = $hospitals   ?? model(HospitalModel::class);
        $this->boards      = $boards      ?? model(BoardModel::class);
        $this->favorites   = $favorites   ?? model(FavoriteModel::class);
        $this->departments = $departments ?? model(DepartmentModel::class);
        $this->events      = $events      ?? service('eventService');
    }

    /**
     * 병원 목록 — 캐시된 기본 결과에 사용자 찜 여부를 덧입혀 반환한다.
     *
     * @param array<string, mixed> $params
     * @return array{items: array<int, array<string, mixed>>, total: int}
     */
    public function list(int $userId, array $params): array
    {
        $cacheKey = 'hospitals_consumer_list_' . $this->hospitals->consumerCacheVersion()
            . '_' . md5(serialize($this->normalizeListParams($params)));

        /** @var array{list: array<int, array<string, mixed>>, total: int}|null $base */
        $base = cache($cacheKey);
        if (!is_array($base)) {
            $base = $this->hospitals->getConsumerList($params);
            cache()->save($cacheKey, $base, self::LIST_TTL);
        }

        $items = array_map($this->transformListItem(...), $base['list']);
        $items = $this->overlayLikes($userId, $items);
        $items = $this->overlayDepartments($items);

        return ['items' => $items, 'total' => $base['total']];
    }

    /**
     * 병원 상세 — 별점 요약·후기 수·is_liked 포함.
     *
     * @return array<string, mixed>
     * @throws NotFoundException 미존재·비활성
     */
    public function detail(int $userId, int $id): array
    {
        // 사용자 무관한 기본 상세를 버전 키로 캐시하고(목록과 동일하게 쓰기 시 일괄 무효화),
        // 사용자별 is_liked 만 캐시 조회 후 덧입힌다.
        $cacheKey = 'hospitals_consumer_detail_' . $this->hospitals->consumerCacheVersion() . '_' . $id;

        /** @var array<string, mixed>|null $base */
        $base = cache($cacheKey);
        if (!is_array($base)) {
            $row = $this->hospitals->getConsumerDetail($id);
            if ($row === null) {
                throw NotFoundException::of('병원을 찾을 수 없습니다.');
            }

            $type = (int) $row['type'];
            $base = [
                'id'         => (int) $row['id'],
                'name'       => $row['name'],
                'type'       => $type,
                'type_label' => self::TYPE_LABELS[$type] ?? null,
                'phone'      => $row['phone'],
                'address'    => $row['address'],
                'review_summary' => [
                    'rating' => round((float) $row['rate_sum'], 2),
                    'rate1'  => round((float) $row['rate1'], 2),
                    'rate2'  => round((float) $row['rate2'], 2),
                    'rate3'  => round((float) $row['rate3'], 2),
                    'count'  => $this->boards->countReviewsByTarget(BoardModel::TYPE_HOSPITAL, $id),
                ],
            ];
            cache()->save($cacheKey, $base, self::DETAIL_TTL);
        }

        $base['is_liked'] = $this->favorites->likedTargetIds($userId, FavoriteModel::TYPE_HOSPITAL, [$id]) !== [];
        // 진료과는 캐시된 기본 상세와 별개로 매번 최신을 덧입힌다(매핑 변경 즉시 반영).
        $base['departments'] = $this->departments->byHospitalIds([$id])[$id] ?? [];

        return $base;
    }

    /**
     * 병원 소속 이벤트 — EventService 재사용(hospital_id 필터).
     *
     * @param array<string, mixed> $params
     * @return array{items: array<int, array<string, mixed>>, total: int}
     * @throws NotFoundException 미존재·비활성 병원
     */
    public function events(int $userId, int $id, array $params): array
    {
        $this->assertVisible($id);

        $params['hospital_id'] = $id;

        return $this->events->list($userId, $params);
    }

    /**
     * 병원 후기 목록 (boards type=2)
     *
     * @param array<string, mixed> $params
     * @return array{items: array<int, array<string, mixed>>, total: int}
     * @throws NotFoundException 미존재·비활성 병원
     */
    public function reviews(int $id, array $params): array
    {
        $this->assertVisible($id);

        $page  = max(1, (int) ($params['page'] ?? 1));
        $limit = max(1, (int) ($params['limit'] ?? 20));

        $base  = $this->boards->getReviewsByTarget(BoardModel::TYPE_HOSPITAL, $id, $page, $limit);
        $items = array_map($this->transformReview(...), $base['list']);

        return ['items' => $items, 'total' => $base['total']];
    }

    /**
     * 병원 찜 토글
     *
     * @return array{liked: bool}
     * @throws NotFoundException 미존재·비활성 병원
     */
    public function toggleFavorite(int $userId, int $id): array
    {
        $this->assertVisible($id);

        $liked = $this->favorites->toggle($userId, FavoriteModel::TYPE_HOSPITAL, $id);

        return ['liked' => $liked];
    }

    /**
     * @throws NotFoundException 미존재·비활성 병원
     */
    private function assertVisible(int $id): void
    {
        if (!$this->hospitals->isVisible($id)) {
            throw NotFoundException::of('병원을 찾을 수 없습니다.');
        }
    }

    /**
     * is_liked 일괄 오버레이 (N+1 방지)
     *
     * @param array<int, array<string, mixed>> $items
     * @return array<int, array<string, mixed>>
     */
    private function overlayLikes(int $userId, array $items): array
    {
        if ($items === []) {
            return $items;
        }

        $ids   = array_map(static fn (array $i): int => (int) $i['id'], $items);
        $liked = array_flip($this->favorites->likedTargetIds($userId, FavoriteModel::TYPE_HOSPITAL, $ids));

        foreach ($items as &$item) {
            $item['is_liked'] = isset($liked[(int) $item['id']]);
        }
        unset($item);

        return $items;
    }

    /**
     * 진료과 일괄 오버레이 (N+1 방지) — 병원별 departments 배열 부착.
     *
     * @param array<int, array<string, mixed>> $items
     * @return array<int, array<string, mixed>>
     */
    private function overlayDepartments(array $items): array
    {
        if ($items === []) {
            return $items;
        }

        $ids = array_map(static fn (array $i): int => (int) $i['id'], $items);
        $map = $this->departments->byHospitalIds($ids);

        foreach ($items as &$item) {
            $item['departments'] = $map[(int) $item['id']] ?? [];
        }
        unset($item);

        return $items;
    }

    /**
     * 병원 목록 행 → 소비자 응답
     *
     * @param array<string, mixed> $row
     * @return array<string, mixed>
     */
    private function transformListItem(array $row): array
    {
        $type = (int) $row['type'];

        return [
            'id'         => (int) $row['id'],
            'name'       => $row['name'],
            'type'       => $type,
            'type_label' => self::TYPE_LABELS[$type] ?? null,
            'phone'      => $row['phone'],
            'address'    => $row['address'],
            'rating'     => round((float) $row['rating'], 2),
        ];
    }

    /**
     * 후기 행 → 소비자 응답
     *
     * @param array<string, mixed> $row
     * @return array<string, mixed>
     */
    private function transformReview(array $row): array
    {
        return [
            'id'            => (int) $row['id'],
            'user_name'     => $row['user_name'],
            'subject'       => $row['subject'],
            'contents'      => $row['contents'],
            'rating'        => round((float) $row['rate_sum'], 2),
            'like_count'    => (int) $row['like_count'],
            'comment_count' => (int) $row['comment_count'],
            'files_count'   => (int) $row['files_count'],
            'created_at'    => $row['created_at'],
        ];
    }

    /**
     * 목록 캐시 키 정규화.
     *
     * @param array<string, mixed> $params
     * @return array<string, mixed>
     */
    private function normalizeListParams(array $params): array
    {
        return [
            'keyword'    => (string) ($params['keyword'] ?? ''),
            'region'     => (string) ($params['region'] ?? ''),
            'department' => (string) ($params['department'] ?? ''),
            'type'       => (int) ($params['type'] ?? 0),
            'page'       => max(1, (int) ($params['page'] ?? 1)),
            'limit'      => max(1, (int) ($params['limit'] ?? 20)),
        ];
    }
}
