<?php

namespace App\Controllers\Web;

use App\Libraries\Seo\JsonLdBuilder;
use App\Services\GuideService;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Services;
use Override;
use Psr\Log\LoggerInterface;

/**
 * 공개 시술 가이드 SSR 페이지 (이슈 #146 — GEO 콘텐츠 자산)
 *
 * 발행된 가이드를 슬러그 URL 로 노출하고, Article(MedicalWebPage)·FAQPage 구조화 데이터를 주입한다.
 * 가이드는 GEO 인용률이 가장 높은 자산이므로 명료한 본문·FAQ 를 우선한다.
 */
class GuidePageController extends BaseWebController
{
    private GuideService $guides;

    #[Override]
    public function initController(
        RequestInterface $request,
        ResponseInterface $response,
        LoggerInterface $logger,
    ): void {
        parent::initController($request, $response, $logger);
        $this->guides = Services::guideService();
    }

    /**
     * 가이드 목록 페이지.
     */
    public function index(): string
    {
        $result = $this->guides->publishedList([
            'page'  => max(1, (int) ($this->request->getGet('page') ?? 1)),
            'limit' => 20,
        ]);

        return $this->render('web/guides/index', [
            'guides' => $result['items'],
            'total'  => $result['total'],
        ], [
            'title'       => '성형·시술 정보 가이드 | AICura',
            'description' => '성형·시술의 비용·과정·주의사항을 전문 정보로 정리한 가이드입니다.',
        ]);
    }

    /**
     * 가이드 상세 페이지 (슬러그).
     */
    public function show(?string $slug = null): string
    {
        $guide = $this->guides->findPublishedBySlug((string) $slug);
        if ($guide === null) {
            throw PageNotFoundException::forPageNotFound('가이드를 찾을 수 없습니다.');
        }

        $url = base_url('guides/' . rawurlencode((string) $guide['slug']));

        /** @var array<int, array{q: string, a: string}> $faq */
        $faq     = $guide['faq'] ?? [];
        $summary = trim((string) ($guide['summary'] ?? ''));

        return $this->render('web/guides/show', [
            'guide' => $guide,
            // faqPage 는 FAQ 없으면 [] 반환 → render 에서 자동 무시
            'jsonLd' => [
                JsonLdBuilder::guide($guide, $url),
                JsonLdBuilder::faqPage($faq, $url),
            ],
        ], [
            'title'       => $guide['title'] . ' | AICura',
            'description' => $summary !== '' ? $summary : '성형·시술 정보 가이드 — AICura',
            'og_type'     => 'article',
        ]);
    }
}
