<?php

namespace App\Services;

use App\Models\GuideModel;

/**
 * 시술 가이드 서비스 (이슈 #146)
 *
 * 어드민 쓰기(슬러그 생성·FAQ 정규화·발행 시각)와 공개 읽기(발행 목록·슬러그 단건, 캐시)를 담당한다.
 */
class GuideService
{
    /** 공개 조회 캐시 TTL (초) — 10분 */
    private const int PUBLIC_TTL = 600;

    private readonly GuideModel $guides;

    public function __construct(?GuideModel $guides = null)
    {
        $this->guides = $guides ?? model(GuideModel::class);
    }

    // ── 어드민 ────────────────────────────────────────

    /**
     * @param array<string, mixed> $params
     * @return array{list: list<array<string, mixed>>, total: int}
     */
    public function adminList(array $params): array
    {
        return $this->guides->getAdminList($params);
    }

    /**
     * @return array<string, mixed>|null
     */
    public function find(int $id): ?array
    {
        return $this->guides->find($id);
    }

    /**
     * 가이드 생성 — 생성된 id 반환.
     *
     * @param array<string, mixed> $input title·slug·summary·content·faq_q[]·faq_a[]·procedure_name·status
     */
    public function create(array $input): int
    {
        $data = $this->normalize($input);
        $data['slug'] = $this->guides->generateUniqueSlug(
            (string) ($input['slug'] ?? '') !== '' ? (string) $input['slug'] : (string) ($input['title'] ?? ''),
        );

        $id = $this->guides->insert($data, true);

        if ($data['status'] === GuideModel::STATUS_PUBLISHED) {
            $this->submitIndexNow($data['slug']);
        }

        return (int) $id;
    }

    /**
     * 가이드 수정.
     *
     * @param array<string, mixed> $input
     */
    public function update(int $id, array $input): bool
    {
        $data = $this->normalize($input);
        $data['slug'] = $this->guides->generateUniqueSlug(
            (string) ($input['slug'] ?? '') !== '' ? (string) $input['slug'] : (string) ($input['title'] ?? ''),
            $id,
        );

        $result = $this->guides->update($id, $data);
        $this->invalidate();

        if ($result && $data['status'] === GuideModel::STATUS_PUBLISHED) {
            $this->submitIndexNow($data['slug']);
        }

        return $result;
    }

    public function delete(int $id): void
    {
        $this->guides->delete($id);
        $this->invalidate();
    }

    // ── 공개 ──────────────────────────────────────────

    /**
     * 공개 목록 (발행 건, 캐시).
     *
     * @param array<string, mixed> $params
     * @return array{items: list<array<string, mixed>>, total: int}
     */
    public function publishedList(array $params): array
    {
        $page  = max(1, (int) ($params['page'] ?? 1));
        $limit = max(1, min(100, (int) ($params['limit'] ?? 20)));

        $cacheKey = 'web_guides_list_' . $page . '_' . $limit;
        /** @var array{list: list<array<string, mixed>>, total: int}|null $base */
        $base = cache($cacheKey);
        if (!is_array($base)) {
            $base = $this->guides->getPublishedList(['page' => $page, 'limit' => $limit]);
            cache()->save($cacheKey, $base, self::PUBLIC_TTL);
        }

        return ['items' => $base['list'], 'total' => $base['total']];
    }

    /**
     * 슬러그로 발행 가이드 단건 (FAQ 디코드 포함). 없으면 null.
     *
     * @return array<string, mixed>|null
     */
    public function findPublishedBySlug(string $slug): ?array
    {
        $cacheKey = 'web_guide_' . md5($slug);
        /** @var array<string, mixed>|null|false $cached */
        $cached = cache($cacheKey);
        if (is_array($cached)) {
            return $cached;
        }

        $guide = $this->guides->findPublishedBySlug($slug);
        if ($guide === null) {
            return null;
        }

        $guide['faq'] = $this->decodeFaq($guide['faq_json'] ?? null);
        cache()->save($cacheKey, $guide, self::PUBLIC_TTL);

        return $guide;
    }

    // ── 내부 ──────────────────────────────────────────

    /**
     * 폼 입력 → 저장 데이터 정규화 (FAQ 직렬화·발행 시각).
     *
     * @param array<string, mixed> $input
     * @return array<string, mixed>
     */
    private function normalize(array $input): array
    {
        $status = ($input['status'] ?? '') === GuideModel::STATUS_PUBLISHED
            ? GuideModel::STATUS_PUBLISHED
            : GuideModel::STATUS_DRAFT;

        $data = [
            'title'          => trim((string) ($input['title'] ?? '')),
            'summary'        => trim((string) ($input['summary'] ?? '')) ?: null,
            'content'        => (string) ($input['content'] ?? ''),
            'procedure_name' => trim((string) ($input['procedure_name'] ?? '')) ?: null,
            'faq_json'       => $this->encodeFaq($input['faq_q'] ?? [], $input['faq_a'] ?? []),
            'status'         => $status,
        ];

        if ($status === GuideModel::STATUS_PUBLISHED) {
            $data['published_at'] = date('Y-m-d H:i:s');
        }

        return $data;
    }

    /**
     * 폼의 faq_q[]·faq_a[] → [{q,a}] JSON (빈 행 제외). 없으면 null.
     *
     * @param mixed $questions
     * @param mixed $answers
     */
    private function encodeFaq($questions, $answers): ?string
    {
        $questions = is_array($questions) ? array_values($questions) : [];
        $answers   = is_array($answers) ? array_values($answers) : [];

        $items = [];
        foreach ($questions as $i => $q) {
            $q = trim((string) $q);
            $a = trim((string) ($answers[$i] ?? ''));
            if ($q !== '' && $a !== '') {
                $items[] = ['q' => $q, 'a' => $a];
            }
        }

        return $items === [] ? null : json_encode($items, JSON_UNESCAPED_UNICODE);
    }

    /**
     * faq_json → [{q,a}] 배열.
     *
     * @return array<int, array{q: string, a: string}>
     */
    private function decodeFaq(?string $json): array
    {
        if ($json === null || trim($json) === '') {
            return [];
        }

        $decoded = json_decode($json, true);
        if (!is_array($decoded)) {
            return [];
        }

        $items = [];
        foreach ($decoded as $row) {
            if (is_array($row) && isset($row['q'], $row['a'])) {
                $items[] = ['q' => (string) $row['q'], 'a' => (string) $row['a']];
            }
        }

        return $items;
    }

    /** 발행 가이드 공개 URL 을 IndexNow 에 제출 (이슈 #152). */
    private function submitIndexNow(string $slug): void
    {
        service('indexNowService')->submit(base_url('guides/' . rawurlencode($slug)));
    }

    private function invalidate(): void
    {
        // 목록·단건 캐시는 짧은 TTL 로 자연 만료. 즉시성이 필요하면 키 패턴 삭제를 고려.
        cache()->deleteMatching('web_guide*');
    }
}
