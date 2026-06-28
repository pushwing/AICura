<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * 공통 코드 모델 (codes) — 외부(소비자) 앱 코드성 데이터 (이슈 #103)
 */
class CodeModel extends Model
{
    protected $table      = 'codes';
    protected $primaryKey = 'id';
    protected $useTimestamps = false;
    protected $returnType    = 'array';

    protected $allowedFields = [
        'code',
        'name',
        'description',
        'type',
        'is_use',
        'sort',
    ];

    /**
     * 사용 중인 코드 목록 (type 필터 선택) — type·sort 순. 1시간 캐시.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getActive(?string $type = null): array
    {
        $cacheKey = 'codes_active_' . ($type !== null && $type !== '' ? $type : 'all');
        /** @var array<int, array<string, mixed>>|null $cached */
        $cached = cache($cacheKey);
        if (is_array($cached)) {
            return $cached;
        }

        $builder = $this->select('code, name, description, type, sort')
            ->where('is_use', 1);

        if ($type !== null && $type !== '') {
            $builder->where('type', $type);
        }

        $list = $builder->orderBy('type', 'ASC')->orderBy('sort', 'ASC')->findAll();

        cache()->save($cacheKey, $list, 3600);

        return $list;
    }
}
