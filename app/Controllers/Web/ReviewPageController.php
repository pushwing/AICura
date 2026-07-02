<?php

namespace App\Controllers\Web;

use Override;
use App\Exceptions\NotFoundException;
use App\Libraries\Seo\JsonLdBuilder;
use App\Libraries\Seo\NameMasker;
use App\Services\BoardService;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Services;
use Psr\Log\LoggerInterface;

/**
 * 공개 후기 SSR 페이지 (이슈 #144 — SEO/GEO Phase 1 ③)
 *
 * 소비자 앱 JSON API(Api\V1\BoardController)와 동일한 BoardService 를 재사용한다.
 * - 작성자명은 NameMasker 로 마스킹해 노출(개인정보 보호).
 * - 신고·의심 후기 상세는 noindex 처리(§4.3) — 검색·AI 인용에서 제외.
 */
class ReviewPageController extends BaseWebController
{
    private BoardService $boards;

    #[Override]
    public function initController(
        RequestInterface $request,
        ResponseInterface $response,
        LoggerInterface $logger
    ): void {
        parent::initController($request, $response, $logger);
        $this->boards = Services::boardService();
    }

    /**
     * 후기 목록 페이지.
     */
    public function index(): string
    {
        $result = $this->boards->list(self::GUEST_USER_ID, $this->listParams());

        $reviews = array_map(function (array $item): array {
            $item['author'] = NameMasker::mask((string) ($item['user_name'] ?? ''));

            return $item;
        }, $result['items']);

        return $this->render('web/reviews/index', [
            'reviews' => $reviews,
            'total'   => $result['total'],
        ], [
            'title'       => '성형·시술 실사용 후기 | AICura',
            'description' => '실제 이용자가 남긴 성형·시술 후기와 평점을 확인하세요.',
        ]);
    }

    /**
     * 후기 상세 페이지 — 신고·의심 건은 noindex.
     */
    public function show(?string $id = null): string
    {
        $reviewId = (int) $id;

        try {
            $review = $this->boards->detail(self::GUEST_USER_ID, $reviewId);
        } catch (NotFoundException $e) {
            throw PageNotFoundException::forPageNotFound($e->getMessage());
        }

        // 작성자·댓글 작성자 마스킹
        $review['author']   = NameMasker::mask((string) ($review['user_name'] ?? ''));
        $review['comments'] = array_map(static function (array $comment): array {
            $comment['author'] = NameMasker::mask((string) ($comment['user_name'] ?? ''));

            return $comment;
        }, $review['comments'] ?? []);

        $indexable  = $this->boards->isIndexable($reviewId);
        $targetName = $this->boards->resolveTargetName((int) ($review['type'] ?? 0), (int) ($review['target_id'] ?? 0));
        $summary    = mb_substr(trim(strip_tags((string) ($review['contents'] ?? ''))), 0, 150);

        return $this->render('web/reviews/show', [
            'review'     => $review,
            'targetName' => $targetName, // 내부 링크: 리뷰 대상(병원/이벤트)명 (이슈 #152)
            'jsonLd'     => [JsonLdBuilder::review($review, base_url('reviews/' . $reviewId), $targetName)],
        ], [
            'title'       => $review['subject'] . ' | AICura 후기',
            'description' => $summary !== '' ? $summary : '성형·시술 실사용 후기 — AICura',
            'og_type'     => 'article',
            // 신고·의심 후기는 색인 제외(§4.3). 링크는 따라가도록 follow 유지
            'robots'      => $indexable ? 'index, follow' : 'noindex, follow',
        ]);
    }

    /**
     * 목록 쿼리 파라미터 정규화 — filter[type]·filter[target_id]·sort·page·per_page.
     *
     * @return array<string, mixed>
     */
    private function listParams(): array
    {
        $filter = $this->request->getGet('filter');
        $filter = is_array($filter) ? $filter : [];

        return [
            'type'      => isset($filter['type']) ? (int) $filter['type'] : 0,
            'target_id' => isset($filter['target_id']) ? (int) $filter['target_id'] : 0,
            'sort'      => (string) ($this->request->getGet('sort') ?? 'latest'),
            'page'      => max(1, (int) ($this->request->getGet('page') ?? 1)),
            'limit'     => max(1, min(100, (int) ($this->request->getGet('per_page') ?? 20))),
        ];
    }
}
