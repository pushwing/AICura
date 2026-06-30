<?php

namespace App\Controllers\Web;

use App\Exceptions\NotFoundException;
use App\Services\HospitalService;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Services;
use Psr\Log\LoggerInterface;

/**
 * 공개 병원 SSR 페이지 (이슈 #144 — SEO/GEO Phase 1 ③)
 *
 * 소비자 앱 JSON API(Api\V1\HospitalController)와 동일한 HospitalService 를 재사용해
 * 크롤링 가능한 HTML 을 제공한다. 데이터 캐시는 HospitalService 내부에서 처리한다.
 */
class HospitalPageController extends BaseWebController
{
    private HospitalService $hospitals;

    public function initController(
        RequestInterface $request,
        ResponseInterface $response,
        LoggerInterface $logger
    ): void {
        parent::initController($request, $response, $logger);
        $this->hospitals = Services::hospitalService();
    }

    /**
     * 병원 목록 페이지.
     */
    public function index(): string
    {
        $result = $this->hospitals->list(self::GUEST_USER_ID, $this->listParams());

        return $this->render('web/hospitals/index', [
            'hospitals' => $result['items'],
            'total'     => $result['total'],
        ], [
            'title'       => '성형·시술 병원 찾기 | AICura',
            'description' => '지역·진료과별 성형·시술 병원을 찾아보고 이벤트·후기를 확인하세요.',
        ]);
    }

    /**
     * 병원 상세 페이지 — 병원 정보 + 진행 이벤트.
     */
    public function show(?string $id = null): string
    {
        $hospitalId = (int) $id;

        try {
            $hospital = $this->hospitals->detail(self::GUEST_USER_ID, $hospitalId);
            $events   = $this->hospitals->events(self::GUEST_USER_ID, $hospitalId, ['limit' => 20]);
        } catch (NotFoundException $e) {
            throw PageNotFoundException::forPageNotFound($e->getMessage());
        }

        $name = (string) $hospital['name'];

        return $this->render('web/hospitals/show', [
            'hospital' => $hospital,
            'events'   => $events['items'],
        ], [
            'title'       => $name . ' | AICura',
            'description' => $name . '의 위치·진료과·진행 중인 성형·시술 이벤트와 후기 평점을 확인하세요.',
            'og_type'     => 'website',
        ]);
    }

    /**
     * 목록 쿼리 파라미터 정규화 — keyword·filter[region]·filter[department]·filter[type]·page·per_page.
     *
     * @return array<string, mixed>
     */
    private function listParams(): array
    {
        $filter = $this->request->getGet('filter');
        $filter = is_array($filter) ? $filter : [];

        return [
            'keyword'    => (string) ($this->request->getGet('keyword') ?? ''),
            'region'     => isset($filter['region']) ? (string) $filter['region'] : '',
            'department' => isset($filter['department']) ? (string) $filter['department'] : '',
            'type'       => isset($filter['type']) ? (int) $filter['type'] : 0,
            'page'       => max(1, (int) ($this->request->getGet('page') ?? 1)),
            'limit'      => max(1, min(100, (int) ($this->request->getGet('per_page') ?? 20))),
        ];
    }
}
