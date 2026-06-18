<?php

namespace App\Controllers\Api\V1;

use OpenApi\Attributes as OA;

#[OA\Tag(name: 'Campaigns', description: '캠페인 관리')]
class CampaignController extends BaseApiController
{
    #[OA\Get(
        path: '/campaigns',
        summary: '캠페인 목록 조회',
        security: [['bearerAuth' => []]],
        tags: ['Campaigns'],
        parameters: [
            new OA\Parameter(name: 'page',   in: 'query', schema: new OA\Schema(type: 'integer', default: 1)),
            new OA\Parameter(name: 'limit',  in: 'query', schema: new OA\Schema(type: 'integer', default: 20)),
            new OA\Parameter(name: 'status', in: 'query', schema: new OA\Schema(type: 'string', enum: ['active', 'paused', 'ended', 'draft'])),
        ],
        responses: [
            new OA\Response(response: 200, description: '캠페인 목록'),
            new OA\Response(response: 401, description: '인증 필요'),
        ]
    )]
    public function index(): \CodeIgniter\HTTP\ResponseInterface
    {
        // TODO: CampaignModel 구현 후 연결
        return $this->success([], ['page' => 1, 'total' => 0]);
    }

    #[OA\Get(
        path: '/campaigns/{id}',
        summary: '캠페인 단건 조회',
        security: [['bearerAuth' => []]],
        tags: ['Campaigns'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: '캠페인 상세'),
            new OA\Response(response: 404, description: '존재하지 않는 캠페인'),
        ]
    )]
    public function show($id = null): \CodeIgniter\HTTP\ResponseInterface
    {
        return $this->success(['id' => $id]);
    }

    #[OA\Post(
        path: '/campaigns',
        summary: '캠페인 생성',
        security: [['bearerAuth' => []]],
        tags: ['Campaigns'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name', 'budget'],
                properties: [
                    new OA\Property(property: 'name',       type: 'string',  example: '강남 리프팅 이벤트'),
                    new OA\Property(property: 'budget',     type: 'integer', example: 5000000),
                    new OA\Property(property: 'start_date', type: 'string',  format: 'date', example: '2026-07-01'),
                    new OA\Property(property: 'end_date',   type: 'string',  format: 'date', example: '2026-07-31'),
                    new OA\Property(property: 'status',     type: 'string',  enum: ['active', 'paused', 'draft'], default: 'draft'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: '캠페인 생성 성공'),
            new OA\Response(response: 422, description: '유효성 검사 실패'),
        ]
    )]
    public function create(): \CodeIgniter\HTTP\ResponseInterface
    {
        return $this->success([], [], 201);
    }

    #[OA\Put(
        path: '/campaigns/{id}',
        summary: '캠페인 수정',
        security: [['bearerAuth' => []]],
        tags: ['Campaigns'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: '수정 성공'),
            new OA\Response(response: 404, description: '존재하지 않는 캠페인'),
        ]
    )]
    public function update($id = null): \CodeIgniter\HTTP\ResponseInterface
    {
        return $this->success(['id' => $id]);
    }

    #[OA\Delete(
        path: '/campaigns/{id}',
        summary: '캠페인 삭제',
        security: [['bearerAuth' => []]],
        tags: ['Campaigns'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: '삭제 성공'),
            new OA\Response(response: 404, description: '존재하지 않는 캠페인'),
        ]
    )]
    public function delete($id = null): \CodeIgniter\HTTP\ResponseInterface
    {
        return $this->success(null);
    }
}
