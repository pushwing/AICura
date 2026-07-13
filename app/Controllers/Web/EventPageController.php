<?php

namespace App\Controllers\Web;

use App\Exceptions\NotFoundException;
use App\Libraries\Seo\JsonLdBuilder;
use App\Services\EventService;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Services;
use Override;
use Psr\Log\LoggerInterface;

/**
 * 공개 이벤트 SSR 페이지 (이슈 #137 — SEO/GEO Phase 1 골격)
 *
 * 소비자 앱 JSON API(Api\V1\CampaignController)와 동일한 EventService 를 재사용해
 * 크롤링 가능한 HTML 을 제공한다. 데이터 캐시는 EventService 내부에서 처리한다.
 */
class EventPageController extends BaseWebController
{
    private EventService $events;

    #[Override]
    public function initController(
        RequestInterface $request,
        ResponseInterface $response,
        LoggerInterface $logger,
    ): void {
        parent::initController($request, $response, $logger);
        $this->events = Services::eventService();
    }

    /**
     * 이벤트 목록 페이지.
     */
    public function index(): string
    {
        $result = $this->events->list(self::GUEST_USER_ID, $this->listParams());

        return $this->render('web/events/index', [
            'events' => $result['items'],
            'total'  => $result['total'],
        ], [
            'title'       => '성형·시술 이벤트 모음 | AICura',
            'description' => '진행 중인 성형·시술 이벤트를 한눈에. 가격·병원·기간을 비교하고 신청하세요.',
        ]);
    }

    /**
     * 이벤트 상세 페이지.
     */
    public function show(?string $id = null): string
    {
        try {
            $event = $this->events->detail(self::GUEST_USER_ID, (int) $id);
        } catch (NotFoundException $e) {
            throw PageNotFoundException::forPageNotFound($e->getMessage());
        }

        $summary = mb_substr(trim(strip_tags((string) ($event['ad_detail_info'] ?? ''))), 0, 150);

        return $this->render('web/events/show', [
            'event'  => $event,
            'jsonLd' => [JsonLdBuilder::event($event, base_url('events/' . (int) $event['id']))],
        ], [
            'title'       => $event['ad_title'] . ' | AICura',
            'description' => $summary !== '' ? $summary : '성형·시술 이벤트 상세 — AICura',
            'og_type'     => 'article',
            'og_image'    => $event['thumbnail_url'] ?? null,
        ]);
    }

    /**
     * 목록 쿼리 파라미터 정규화 — filter[*]·sort·page·per_page.
     *
     * @return array<string, mixed>
     */
    private function listParams(): array
    {
        $filter = $this->request->getGet('filter');
        $filter = is_array($filter) ? $filter : [];

        return [
            'category' => isset($filter['category']) ? (int) $filter['category'] : 0,
            'region'   => isset($filter['region']) ? (string) $filter['region'] : '',
            'keyword'  => (string) ($this->request->getGet('keyword') ?? ''),
            'sort'     => (string) ($this->request->getGet('sort') ?? 'latest'),
            'page'     => max(1, (int) ($this->request->getGet('page') ?? 1)),
            'limit'    => max(1, min(100, (int) ($this->request->getGet('per_page') ?? 20))),
        ];
    }
}
