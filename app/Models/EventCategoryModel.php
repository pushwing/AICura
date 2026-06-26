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
}
