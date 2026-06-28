<?php

namespace App\Models;

use CodeIgniter\Model;

class EventCategoryModel extends Model
{
    protected $table         = 'event_categories';
    protected $primaryKey    = 'id';
    protected $useTimestamps = true;
    protected $returnType    = 'array';

    protected $allowedFields = [
        'parent_id',
        'title',
        'category_type',
        'sort',
        'is_visible',
        'image',
        'coocha_tags',
        'coocha_category',
    ];

    /**
     * 광고 카테고리 선택 옵션 — 노출 가능(is_visible=1) 항목만, 정렬 순.
     *
     * @return array<int, array{id: int, title: string}>
     */
    public function getSelectOptions(): array
    {
        return $this->select('id, title')
            ->where('is_visible', 1)
            ->orderBy('sort', 'ASC')
            ->orderBy('id', 'ASC')
            ->findAll();
    }

    /**
     * 카테고리 ID로 제목 조회 (없으면 빈 문자열) — AI 카피 프롬프트 입력용
     */
    public function titleById(int $id): string
    {
        $row = $this->select('title')->where('id', $id)->first();

        return (string) ($row['title'] ?? '');
    }

    /**
     * 외부 앱 노출용 카테고리 목록 — 노출 가능 항목만, 정렬 순. 1시간 캐시(변경 빈도 낮음). (이슈 #98)
     *
     * @return array<int, array<string, mixed>>
     */
    public function getVisibleList(): array
    {
        $cacheKey = 'event_categories_visible';
        /** @var array<int, array<string, mixed>>|null $cached */
        $cached = cache($cacheKey);
        if (is_array($cached)) {
            return $cached;
        }

        $list = $this->select('id, parent_id, title, image, sort')
            ->where('is_visible', 1)
            ->orderBy('sort', 'ASC')
            ->orderBy('id', 'ASC')
            ->findAll();

        cache()->save($cacheKey, $list, 3600);

        return $list;
    }
}
