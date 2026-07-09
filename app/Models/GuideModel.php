<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * 시술 가이드 모델 (이슈 #146)
 *
 * 발행(published) 상태만 공개되며 슬러그로 단건 조회한다. 소프트 삭제 사용.
 */
class GuideModel extends Model
{
    public const STATUS_DRAFT     = 'draft';
    public const STATUS_PUBLISHED = 'published';

    protected $table          = 'guides';
    protected $primaryKey     = 'id';
    protected $useTimestamps  = true;
    protected $useSoftDeletes = true;
    protected $returnType     = 'array';
    protected $allowedFields  = [
        'title',
        'slug',
        'summary',
        'content',
        'faq_json',
        'procedure_name',
        'status',
        'published_at',
    ];

    // 슬러그 유일성은 GuideService::generateUniqueSlug 가 보장한다(모델 is_unique 의 {id}
    // 플레이스홀더가 update 시 자기 자신과 충돌하는 문제를 피하기 위해 모델 규칙에서는 제외).
    /**
     * @var array<string, string>
     */
    protected $validationRules = [
        'title'  => 'required|max_length[200]',
        'slug'   => 'required|max_length[220]',
        'status' => 'in_list[draft,published]',
    ];

    /**
     * 어드민 목록 — 상태·키워드 필터, 페이징(삭제 제외).
     *
     * @param array<string, mixed> $params
     *
     * @return array{list: list<array<string, mixed>>, total: int}
     */
    public function getAdminList(array $params): array
    {
        $builder = $this->builder()
            ->select('id, title, slug, status, published_at, updated_at')
            ->where('deleted_at');

        if (($params['status'] ?? '') !== '') {
            $builder->where('status', (string) $params['status']);
        }
        if (($params['keyword'] ?? '') !== '') {
            $builder->like('title', (string) $params['keyword']);
        }

        $total = (clone $builder)->countAllResults(false);

        $page  = max(1, (int) ($params['page'] ?? 1));
        $limit = max(1, (int) ($params['limit'] ?? 20));

        /** @var list<array<string, mixed>> $list */
        $list = $builder->orderBy('id', 'DESC')
            ->limit($limit, ($page - 1) * $limit)
            ->get()
            ->getResultArray();

        return ['list' => $list, 'total' => $total];
    }

    /**
     * 공개 목록 — 발행 건만, 최신순 페이징.
     *
     * @param array<string, mixed> $params
     *
     * @return array{list: list<array<string, mixed>>, total: int}
     */
    public function getPublishedList(array $params): array
    {
        $builder = $this->builder()
            ->select('id, title, slug, summary, published_at')
            ->where('deleted_at')
            ->where('status', self::STATUS_PUBLISHED);

        $total = (clone $builder)->countAllResults(false);

        $page  = max(1, (int) ($params['page'] ?? 1));
        $limit = max(1, (int) ($params['limit'] ?? 20));

        /** @var list<array<string, mixed>> $list */
        $list = $builder->orderBy('published_at', 'DESC')
            ->orderBy('id', 'DESC')
            ->limit($limit, ($page - 1) * $limit)
            ->get()
            ->getResultArray();

        return ['list' => $list, 'total' => $total];
    }

    /**
     * 슬러그로 발행 가이드 단건 조회 (없으면 null).
     *
     * @return array<string, mixed>|null
     */
    public function findPublishedBySlug(string $slug): ?array
    {
        return $this->where('slug', $slug)
            ->where('status', self::STATUS_PUBLISHED)
            ->first();
    }

    /**
     * sitemap.xml 용 발행 가이드 — slug·갱신시각.
     *
     * @return array<int, array{slug: string, updated_at: string|null}>
     */
    public function getSitemapGuides(int $limit = 50000): array
    {
        /** @var array<int, array{slug: string, updated_at: string|null}> $rows */
        $rows = $this->builder()
            ->select('slug, updated_at')
            ->where('deleted_at')
            ->where('status', self::STATUS_PUBLISHED)
            ->orderBy('published_at', 'DESC')
            ->limit($limit)
            ->get()
            ->getResultArray();

        return $rows;
    }

    /**
     * 고유 슬러그 생성 — base 를 슬러그화하고, 중복 시 -2·-3… 접미사로 유일화.
     * (한글 등 ASCII 외 문자만 있으면 'guide' 로 대체)
     */
    public function generateUniqueSlug(string $base, ?int $excludeId = null): string
    {
        helper('text');
        $slug = url_title($base, '-', true);
        if ($slug === '') {
            $slug = 'guide';
        }

        $candidate = $slug;
        $suffix    = 2;

        while ($this->slugExists($candidate, $excludeId)) {
            $candidate = $slug . '-' . $suffix;
            $suffix++;
        }

        return $candidate;
    }

    private function slugExists(string $slug, ?int $excludeId): bool
    {
        $builder = $this->where('slug', $slug);
        if ($excludeId !== null) {
            $builder->where('id !=', $excludeId);
        }

        return $builder->countAllResults() > 0;
    }
}
