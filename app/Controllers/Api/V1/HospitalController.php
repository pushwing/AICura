<?php

namespace App\Controllers\Api\V1;

use App\Exceptions\DomainException;
use App\Services\HospitalService;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Services;
use OpenApi\Attributes as OA;
use Override;

/**
 * 외부(소비자) 앱 병원 컨트롤러 (이슈 #99)
 *
 * 병원 목록·상세·소속 이벤트·후기·찜. 모두 jwt_auth(로그인 필수) 하위.
 * 비즈니스 로직은 HospitalService 가 담당한다.
 */
#[OA\Tag(name: 'Hospitals', description: '병원 조회 — 소비자 앱')]
class HospitalController extends BaseApiController
{
    private readonly HospitalService $hospitals;

    public function __construct()
    {
        $this->hospitals = Services::hospitalService();
    }

    #[OA\Get(
        path: '/hospitals',
        summary: '병원 목록 조회',
        security: [['bearerAuth' => []]],
        tags: ['Hospitals'],
        parameters: [
            new OA\Parameter(name: 'keyword', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[region]', description: '주소 부분일치', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[department]', description: '진료과 코드 일치 (예: plastic_surgery)', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[type]', description: '1 일반·2 네트워크모·3 네트워크자', in: 'query', schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'page', in: 'query', schema: new OA\Schema(type: 'integer', default: 1)),
            new OA\Parameter(name: 'per_page', in: 'query', schema: new OA\Schema(type: 'integer', default: 20)),
        ],
        responses: [
            new OA\Response(response: 200, description: '병원 목록'),
            new OA\Response(response: 401, description: '인증 필요'),
        ],
    )]
    #[Override]
    public function index(): ResponseInterface
    {
        $params = $this->listParams();
        $result = $this->hospitals->list($this->authUserId(), $params);

        return $this->success($result['items'], $this->meta($params, $result['total']));
    }

    #[OA\Get(
        path: '/hospitals/{id}',
        summary: '병원 상세 (별점 요약 포함)',
        security: [['bearerAuth' => []]],
        tags: ['Hospitals'],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(response: 200, description: '병원 상세'),
            new OA\Response(response: 404, description: '존재하지 않는 병원'),
        ],
    )]
    #[Override]
    public function show($id = null): ResponseInterface
    {
        try {
            $item = $this->hospitals->detail($this->authUserId(), (int) $id);
        } catch (DomainException $e) {
            return $this->error($e->errorCode(), $e->getMessage(), $e->httpStatusCode());
        }

        return $this->success($item);
    }

    #[OA\Get(
        path: '/hospitals/{id}/campaigns',
        summary: '병원 소속 이벤트',
        security: [['bearerAuth' => []]],
        tags: ['Hospitals'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'page', in: 'query', schema: new OA\Schema(type: 'integer', default: 1)),
            new OA\Parameter(name: 'per_page', in: 'query', schema: new OA\Schema(type: 'integer', default: 20)),
        ],
        responses: [
            new OA\Response(response: 200, description: '병원 이벤트 목록'),
            new OA\Response(response: 404, description: '존재하지 않는 병원'),
        ],
    )]
    public function campaigns(?string $id = null): ResponseInterface
    {
        $params = $this->pageParams();

        try {
            $result = $this->hospitals->events($this->authUserId(), (int) $id, $params);
        } catch (DomainException $e) {
            return $this->error($e->errorCode(), $e->getMessage(), $e->httpStatusCode());
        }

        return $this->success($result['items'], $this->meta($params, $result['total']));
    }

    #[OA\Get(
        path: '/hospitals/{id}/reviews',
        summary: '병원 후기 목록',
        security: [['bearerAuth' => []]],
        tags: ['Hospitals'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'page', in: 'query', schema: new OA\Schema(type: 'integer', default: 1)),
            new OA\Parameter(name: 'per_page', in: 'query', schema: new OA\Schema(type: 'integer', default: 20)),
        ],
        responses: [
            new OA\Response(response: 200, description: '병원 후기 목록'),
            new OA\Response(response: 404, description: '존재하지 않는 병원'),
        ],
    )]
    public function reviews(?string $id = null): ResponseInterface
    {
        $params = $this->pageParams();

        try {
            $result = $this->hospitals->reviews((int) $id, $params);
        } catch (DomainException $e) {
            return $this->error($e->errorCode(), $e->getMessage(), $e->httpStatusCode());
        }

        return $this->success($result['items'], $this->meta($params, $result['total']));
    }

    #[OA\Post(
        path: '/hospitals/{id}/like',
        summary: '병원 찜 토글',
        security: [['bearerAuth' => []]],
        tags: ['Hospitals'],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(response: 200, description: '토글 결과 (liked: 찜 여부)'),
            new OA\Response(response: 404, description: '존재하지 않는 병원'),
        ],
    )]
    public function like(?string $id = null): ResponseInterface
    {
        try {
            $result = $this->hospitals->toggleFavorite($this->authUserId(), (int) $id);
        } catch (DomainException $e) {
            return $this->error($e->errorCode(), $e->getMessage(), $e->httpStatusCode());
        }

        return $this->success($result);
    }

    /**
     * 목록 쿼리 파라미터 — keyword·filter[region]·filter[department]·filter[type]·page·per_page
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

    /**
     * 페이지 파라미터만 — 하위 리소스(이벤트·후기)용
     *
     * @return array<string, mixed>
     */
    private function pageParams(): array
    {
        return [
            'page'  => max(1, (int) ($this->request->getGet('page') ?? 1)),
            'limit' => max(1, min(100, (int) ($this->request->getGet('per_page') ?? 20))),
        ];
    }

    /**
     * 페이지네이션 meta 표준 4필드
     *
     * @param array<string, mixed> $params
     *
     * @return array<string, int>
     */
    private function meta(array $params, int $total): array
    {
        $limit = max(1, (int) $params['limit']);

        return [
            'page'      => (int) $params['page'],
            'per_page'  => $limit,
            'total'     => $total,
            'last_page' => (int) ceil($total / $limit),
        ];
    }
}
