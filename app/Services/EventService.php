<?php

namespace App\Services;

use App\Exceptions\NotFoundException;
use App\Models\CampaignModel;
use App\Models\EventCategoryModel;
use App\Models\FavoriteModel;

/**
 * 외부(소비자) 앱 이벤트 서비스 (이슈 #98)
 *
 * 캠페인을 '이벤트'로 노출하는 조회 유스케이스를 담당한다.
 * - 노출/필터/정렬은 CampaignModel 이 책임지고, 본 서비스는 이미지 URL 조립·is_liked 오버레이·캐시를 담당한다.
 * - 캐시는 사용자 무관한 기본 결과만 저장하고, 찜 여부(is_liked)는 캐시 조회 후 사용자별로 덧입힌다.
 */
class EventService
{
    /** 이미지 서빙 경로 prefix (admin 뷰와 동일) */
    private const IMAGE_PATH = 'uploads/campaigns/';

    /** 목록·집계 캐시 TTL (초) */
    private const LIST_TTL = 300;

    /** 메인·추천 캐시 TTL (초) */
    private const FEED_TTL = 600;

    private CampaignModel $campaigns;
    private EventCategoryModel $categories;
    private FavoriteModel $favorites;

    public function __construct(
        ?CampaignModel $campaigns = null,
        ?EventCategoryModel $categories = null,
        ?FavoriteModel $favorites = null,
    ) {
        $this->campaigns  = $campaigns  ?? model(CampaignModel::class);
        $this->categories = $categories ?? model(EventCategoryModel::class);
        $this->favorites  = $favorites  ?? model(FavoriteModel::class);
    }

    /**
     * 이벤트 목록 — 캐시된 기본 결과에 사용자 찜 여부를 덧입혀 반환한다.
     *
     * @param array<string, mixed> $params
     * @return array{items: array<int, array<string, mixed>>, total: int}
     */
    public function list(int $userId, array $params): array
    {
        $cacheKey = 'events_list_' . md5(serialize($this->normalizeListParams($params)));

        /** @var array{list: array<int, array<string, mixed>>, total: int}|null $base */
        $base = cache($cacheKey);
        if (!is_array($base)) {
            $base = $this->campaigns->getEventList($params);
            cache()->save($cacheKey, $base, self::LIST_TTL);
        }

        $items = array_map([$this, 'transformListItem'], $base['list']);
        $items = $this->overlayLikes($userId, $items);

        return ['items' => $items, 'total' => $base['total']];
    }

    /**
     * 이벤트 상세
     *
     * @return array<string, mixed>
     * @throws NotFoundException 노출 조건 미충족·미존재
     */
    public function detail(int $userId, int $id): array
    {
        $cacheKey = 'events_detail_' . $id;

        /** @var array<string, mixed>|null $row */
        $row = cache($cacheKey);
        if (!is_array($row)) {
            $row = $this->campaigns->getEventDetail($id);
            if ($row === null) {
                throw NotFoundException::of('이벤트를 찾을 수 없습니다.');
            }
            cache()->save($cacheKey, $row, self::LIST_TTL);
        }

        $item = $this->transformDetailItem($row);
        $item['is_liked'] = $this->favorites->likedTargetIds($userId, FavoriteModel::TYPE_CAMPAIGN, [(int) $item['id']]) !== [];

        return $item;
    }

    /**
     * 노출 카테고리 목록 (모델에서 1시간 캐시)
     *
     * @return array<int, array<string, mixed>>
     */
    public function categories(): array
    {
        return $this->categories->getVisibleList();
    }

    /**
     * 메인 노출 이벤트
     *
     * @return array<int, array<string, mixed>>
     */
    public function main(int $userId, int $limit = 10): array
    {
        return $this->feed($userId, 'events_main_' . $limit, fn (): array => $this->campaigns->getMainEvents($limit));
    }

    /**
     * 추천 이벤트
     *
     * @return array<int, array<string, mixed>>
     */
    public function recommend(int $userId, int $limit = 10): array
    {
        return $this->feed($userId, 'events_recommend_' . $limit, fn (): array => $this->campaigns->getRecommendEvents($limit));
    }

    /**
     * 이벤트 찜 토글
     *
     * @return array{liked: bool}
     * @throws NotFoundException 노출 조건 미충족·미존재
     */
    public function toggleFavorite(int $userId, int $id): array
    {
        if (!$this->campaigns->isVisibleEvent($id)) {
            throw NotFoundException::of('이벤트를 찾을 수 없습니다.');
        }

        $liked = $this->favorites->toggle($userId, FavoriteModel::TYPE_CAMPAIGN, $id);

        return ['liked' => $liked];
    }

    /**
     * 메인·추천 공통 — 캐시된 기본 피드에 사용자 찜 여부 오버레이.
     *
     * @param callable():array<int, array<string, mixed>> $fetch
     * @return array<int, array<string, mixed>>
     */
    private function feed(int $userId, string $cacheKey, callable $fetch): array
    {
        /** @var array<int, array<string, mixed>>|null $rows */
        $rows = cache($cacheKey);
        if (!is_array($rows)) {
            $rows = $fetch();
            cache()->save($cacheKey, $rows, self::FEED_TTL);
        }

        $items = array_map([$this, 'transformListItem'], $rows);

        return $this->overlayLikes($userId, $items);
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
        $liked = array_flip($this->favorites->likedTargetIds($userId, FavoriteModel::TYPE_CAMPAIGN, $ids));

        foreach ($items as &$item) {
            $item['is_liked'] = isset($liked[(int) $item['id']]);
        }
        unset($item);

        return $items;
    }

    /**
     * 목록 행 → 소비자 응답 변환 (썸네일 URL 조립, 타입 라벨)
     *
     * @param array<string, mixed> $row
     * @return array<string, mixed>
     */
    private function transformListItem(array $row): array
    {
        $adType = (int) $row['ad_type'];

        return [
            'id'             => (int) $row['id'],
            'ad_title'       => $row['ad_title'],
            'hospital_id'    => (int) $row['hospital_id'],
            'hospital_name'  => $row['hospital_name'],
            'category_id'    => (int) $row['category'],
            'category_title' => $row['category_title'],
            'region'         => $row['region'],
            'ad_type'        => $adType,
            'ad_type_label'  => CampaignModel::AD_TYPES[$adType] ?? null,
            'cost_type'      => (int) $row['cost_type'],
            'general_cost'   => (int) $row['general_cost'],
            'discount_cost'  => (int) $row['discount_cost'],
            'text_cost'      => $row['text_cost'],
            'thumbnail_url'  => $this->imageUrl($row['t1_image_name'] ?? null),
            'ad_start_date'  => $row['ad_start_date'],
            'ad_end_date'    => $row['ad_end_date'],
        ];
    }

    /**
     * 상세 행 → 소비자 응답 변환
     *
     * @param array<string, mixed> $row
     * @return array<string, mixed>
     */
    private function transformDetailItem(array $row): array
    {
        $item = $this->transformListItem($row);

        $item['sub_thumbnail_url'] = $this->imageUrl($row['t2_image_name'] ?? null);
        $item['detail_images']     = $this->detailImageUrls($row['d_image_json'] ?? null);
        $item['ad_detail_info']    = $row['ad_detail_info'];
        $item['hospital_address']  = $row['hospital_address'] ?? null;
        $item['hospital_phone']    = $row['hospital_phone'] ?? null;

        return $item;
    }

    /**
     * 파일명 → 절대 이미지 URL (없으면 null)
     */
    private function imageUrl(?string $name): ?string
    {
        if ($name === null || trim($name) === '') {
            return null;
        }

        return base_url(self::IMAGE_PATH . basename($name));
    }

    /**
     * d_image_json(파일명 배열 JSON) → 절대 URL 목록
     *
     * @return array<int, string>
     */
    private function detailImageUrls(?string $json): array
    {
        if ($json === null || trim($json) === '') {
            return [];
        }

        $decoded = json_decode($json, true);
        if (!is_array($decoded)) {
            return [];
        }

        $urls = [];
        foreach ($decoded as $name) {
            if (is_string($name) && trim($name) !== '') {
                $urls[] = (string) base_url(self::IMAGE_PATH . basename($name));
            }
        }

        return $urls;
    }

    /**
     * 목록 캐시 키 정규화 — 캐시 적중률을 위해 의미 있는 파라미터만 추린다.
     *
     * @param array<string, mixed> $params
     * @return array<string, mixed>
     */
    private function normalizeListParams(array $params): array
    {
        return [
            'category' => (int) ($params['category'] ?? 0),
            'region'   => (string) ($params['region'] ?? ''),
            'keyword'  => (string) ($params['keyword'] ?? ''),
            'sort'     => (string) ($params['sort'] ?? 'latest'),
            'page'     => max(1, (int) ($params['page'] ?? 1)),
            'limit'    => max(1, (int) ($params['limit'] ?? 20)),
        ];
    }
}
