<?php

namespace App\Controllers\Api\V1;

use Override;
use App\Exceptions\DomainException;
use App\Services\EventService;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Services;
use OpenApi\Attributes as OA;

/**
 * 외부(소비자) 앱 이벤트 컨트롤러 (이슈 #98)
 *
 * 캠페인을 '이벤트'로 노출하는 조회 전용 API + 찜 토글.
 * 비즈니스 로직은 EventService 가 담당하고, 컨트롤러는 입력 정규화·응답 변환만 수행한다.
 * 모든 엔드포인트는 jwt_auth 필터(로그인 필수) 하위에 둔다.
 */
#[OA\Tag(name: 'Campaigns', description: '이벤트(캠페인) 조회 — 소비자 앱')]
class CampaignController extends BaseApiController
{
    private readonly EventService $events;

    public function __construct()
    {
        $this->events = Services::eventService();
    }

    #[OA\Get(
        path: '/campaigns',
        summary: '이벤트 목록 조회',
        security: [['bearerAuth' => []]],
        tags: ['Campaigns'],
        parameters: [
            new OA\Parameter(name: 'filter[category]', in: 'query', schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'filter[region]', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'keyword', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'sort', in: 'query', schema: new OA\Schema(type: 'string', default: 'latest', enum: ['latest', 'price_asc', 'price_desc', 'popular'])),
            new OA\Parameter(name: 'page', in: 'query', schema: new OA\Schema(type: 'integer', default: 1)),
            new OA\Parameter(name: 'per_page', in: 'query', schema: new OA\Schema(type: 'integer', default: 20)),
        ],
        responses: [
            new OA\Response(response: 200, description: '이벤트 목록'),
            new OA\Response(response: 401, description: '인증 필요'),
        ]
    )]
    #[Override]
    public function index(): ResponseInterface
    {
        $params = $this->listParams();
        $result = $this->events->list($this->authUserId(), $params);

        return $this->success($result['items'], [
            'page'      => (int) $params['page'],
            'per_page'  => (int) $params['limit'],
            'total'     => $result['total'],
            'last_page' => (int) ceil($result['total'] / max(1, (int) $params['limit'])),
        ]);
    }

    #[OA\Get(
        path: '/campaigns/categories',
        summary: '이벤트 카테고리 목록',
        security: [['bearerAuth' => []]],
        tags: ['Campaigns'],
        responses: [new OA\Response(response: 200, description: '카테고리 목록')]
    )]
    public function categories(): ResponseInterface
    {
        return $this->success($this->events->categories());
    }

    #[OA\Get(
        path: '/campaigns/main',
        summary: '메인 노출 이벤트',
        security: [['bearerAuth' => []]],
        tags: ['Campaigns'],
        responses: [new OA\Response(response: 200, description: '메인 이벤트 목록')]
    )]
    public function main(): ResponseInterface
    {
        return $this->success($this->events->main($this->authUserId()));
    }

    #[OA\Get(
        path: '/campaigns/recommend',
        summary: '추천 이벤트',
        security: [['bearerAuth' => []]],
        tags: ['Campaigns'],
        responses: [new OA\Response(response: 200, description: '추천 이벤트 목록')]
    )]
    public function recommend(): ResponseInterface
    {
        return $this->success($this->events->recommend($this->authUserId()));
    }

    #[OA\Get(
        path: '/campaigns/{id}',
        summary: '이벤트 상세 조회',
        security: [['bearerAuth' => []]],
        tags: ['Campaigns'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: '이벤트 상세'),
            new OA\Response(response: 404, description: '존재하지 않는 이벤트'),
        ]
    )]
    #[Override]
    public function show($id = null): ResponseInterface
    {
        try {
            $item = $this->events->detail($this->authUserId(), (int) $id);
        } catch (DomainException $e) {
            return $this->error($e->errorCode(), $e->getMessage(), $e->httpStatusCode());
        }

        return $this->success($item);
    }

    #[OA\Post(
        path: '/campaigns/{id}/like',
        summary: '이벤트 찜 토글',
        security: [['bearerAuth' => []]],
        tags: ['Campaigns'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: '토글 결과 (liked: 찜 여부)'),
            new OA\Response(response: 404, description: '존재하지 않는 이벤트'),
        ]
    )]
    public function like(?string $id = null): ResponseInterface
    {
        try {
            $result = $this->events->toggleFavorite($this->authUserId(), (int) $id);
        } catch (DomainException $e) {
            return $this->error($e->errorCode(), $e->getMessage(), $e->httpStatusCode());
        }

        return $this->success($result);
    }

    /**
     * 목록 쿼리 파라미터 정규화 — filter[*]·sort·page·per_page
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
