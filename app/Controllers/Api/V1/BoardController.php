<?php

namespace App\Controllers\Api\V1;

use Override;
use App\Exceptions\DomainException;
use App\Services\BoardService;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Services;
use OpenApi\Attributes as OA;

/**
 * 외부(소비자) 앱 후기 커뮤니티 컨트롤러 (이슈 #102)
 *
 * 후기 목록·상세·작성·수정·삭제·좋아요·신고·댓글. 모두 jwt_auth(로그인 필수) 하위.
 * 비즈니스 로직은 BoardService 가 담당한다.
 */
#[OA\Tag(name: 'Boards', description: '후기 커뮤니티 — 소비자 앱')]
class BoardController extends BaseApiController
{
    private readonly BoardService $boards;

    public function __construct()
    {
        $this->boards = Services::boardService();
    }

    #[OA\Get(
        path: '/boards',
        summary: '후기 목록',
        security: [['bearerAuth' => []]],
        tags: ['Boards'],
        parameters: [
            new OA\Parameter(name: 'filter[type]', description: '1 이벤트 · 2 병원', in: 'query', schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'filter[target_id]', in: 'query', schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'sort', in: 'query', schema: new OA\Schema(type: 'string', default: 'latest', enum: ['latest', 'rating', 'likes'])),
            new OA\Parameter(name: 'page', in: 'query', schema: new OA\Schema(type: 'integer', default: 1)),
            new OA\Parameter(name: 'per_page', in: 'query', schema: new OA\Schema(type: 'integer', default: 20)),
        ],
        responses: [new OA\Response(response: 200, description: '후기 목록')]
    )]
    #[Override]
    public function index(): ResponseInterface
    {
        $p = $this->listParams();
        $r = $this->boards->list($this->authUserId(), $p);

        return $this->success($r['items'], $this->meta($p, $r['total']));
    }

    #[OA\Get(
        path: '/boards/{id}',
        summary: '후기 상세 (이미지·댓글·평점)',
        security: [['bearerAuth' => []]],
        tags: ['Boards'],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(response: 200, description: '후기 상세'),
            new OA\Response(response: 404, description: '존재하지 않는 후기'),
        ]
    )]
    #[Override]
    public function show($id = null): ResponseInterface
    {
        try {
            return $this->success($this->boards->detail($this->authUserId(), (int) $id));
        } catch (DomainException $e) {
            return $this->error($e->errorCode(), $e->getMessage(), $e->httpStatusCode());
        }
    }

    #[OA\Post(path: '/boards', summary: '후기 작성', security: [['bearerAuth' => []]], requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['type', 'target_id'],
            properties: [
                new OA\Property(property: 'type', description: '1 이벤트 · 2 병원', type: 'integer', example: 2),
                new OA\Property(property: 'target_id', type: 'integer', example: 5),
                new OA\Property(property: 'subject', type: 'string', example: '만족스러운 시술'),
                new OA\Property(property: 'contents', type: 'string', example: '친절하고 좋았어요'),
                new OA\Property(property: 'rating', type: 'number', format: 'float', example: 4.5),
                new OA\Property(property: 'images', description: '업로드 파일명 목록', type: 'array', items: new OA\Items(type: 'string')),
            ]
        )
    ), tags: ['Boards'], responses: [
        new OA\Response(response: 201, description: '작성 성공'),
        new OA\Response(response: 404, description: '대상 없음'),
        new OA\Response(response: 422, description: '유효성 검사 실패'),
    ])]
    #[Override]
    public function create(): ResponseInterface
    {
        $rules = [
            'type'      => 'required|in_list[1,2]',
            'target_id' => 'required|is_natural_no_zero',
            'subject'   => 'permit_empty|max_length[200]',
            'contents'  => 'permit_empty|max_length[5000]',
            'rating'    => 'permit_empty|decimal|greater_than_equal_to[0]|less_than_equal_to[5]',
        ];

        if (!$this->validate($rules)) {
            return $this->error('VALIDATION_ERROR', implode(' ', $this->validator->getErrors()), 422);
        }

        try {
            return $this->success($this->boards->create($this->authUserId(), $this->json()), [], 201);
        } catch (DomainException $e) {
            return $this->error($e->errorCode(), $e->getMessage(), $e->httpStatusCode());
        }
    }

    #[OA\Patch(
        path: '/boards/{id}',
        summary: '후기 수정 (본인)',
        security: [['bearerAuth' => []]],
        tags: ['Boards'],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(response: 200, description: '수정 성공'),
            new OA\Response(response: 404, description: '존재하지 않거나 권한 없는 후기'),
        ]
    )]
    #[Override]
    public function update($id = null): ResponseInterface
    {
        $rules = [
            'subject'  => 'permit_empty|max_length[200]',
            'contents' => 'permit_empty|max_length[5000]',
            'rating'   => 'permit_empty|decimal|greater_than_equal_to[0]|less_than_equal_to[5]',
        ];

        if (!$this->validate($rules)) {
            return $this->error('VALIDATION_ERROR', implode(' ', $this->validator->getErrors()), 422);
        }

        try {
            return $this->success($this->boards->update($this->authUserId(), (int) $id, $this->json()));
        } catch (DomainException $e) {
            return $this->error($e->errorCode(), $e->getMessage(), $e->httpStatusCode());
        }
    }

    #[OA\Delete(
        path: '/boards/{id}',
        summary: '후기 삭제 (본인)',
        security: [['bearerAuth' => []]],
        tags: ['Boards'],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(response: 200, description: '삭제 성공'),
            new OA\Response(response: 404, description: '존재하지 않거나 권한 없는 후기'),
        ]
    )]
    #[Override]
    public function delete($id = null): ResponseInterface
    {
        try {
            $this->boards->delete($this->authUserId(), (int) $id);

            return $this->success(null);
        } catch (DomainException $e) {
            return $this->error($e->errorCode(), $e->getMessage(), $e->httpStatusCode());
        }
    }

    #[OA\Post(
        path: '/boards/{id}/like',
        summary: '후기 좋아요 토글',
        security: [['bearerAuth' => []]],
        tags: ['Boards'],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(response: 200, description: '토글 결과'),
            new OA\Response(response: 404, description: '존재하지 않는 후기'),
        ]
    )]
    public function like(?string $id = null): ResponseInterface
    {
        try {
            return $this->success($this->boards->toggleLike($this->authUserId(), (int) $id));
        } catch (DomainException $e) {
            return $this->error($e->errorCode(), $e->getMessage(), $e->httpStatusCode());
        }
    }

    #[OA\Post(
        path: '/boards/{id}/report',
        summary: '후기 신고',
        security: [['bearerAuth' => []]],
        tags: ['Boards'],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(response: 200, description: '신고 결과 (reported: 신규 여부)'),
            new OA\Response(response: 404, description: '존재하지 않는 후기'),
        ]
    )]
    public function report(?string $id = null): ResponseInterface
    {
        try {
            return $this->success($this->boards->report($this->authUserId(), (int) $id));
        } catch (DomainException $e) {
            return $this->error($e->errorCode(), $e->getMessage(), $e->httpStatusCode());
        }
    }

    #[OA\Get(
        path: '/boards/{id}/comments',
        summary: '댓글 목록',
        security: [['bearerAuth' => []]],
        tags: ['Boards'],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(response: 200, description: '댓글 목록'),
            new OA\Response(response: 404, description: '존재하지 않는 후기'),
        ]
    )]
    public function comments(?string $id = null): ResponseInterface
    {
        $p = $this->pageParams();

        try {
            $r = $this->boards->comments((int) $id, $p['page'], $p['limit']);

            return $this->success($r['items'], $this->meta($p, $r['total']));
        } catch (DomainException $e) {
            return $this->error($e->errorCode(), $e->getMessage(), $e->httpStatusCode());
        }
    }

    #[OA\Post(path: '/boards/{id}/comments', summary: '댓글 작성', security: [['bearerAuth' => []]], requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(required: ['contents'], properties: [
            new OA\Property(property: 'contents', type: 'string', example: '저도 다녀왔어요!'),
        ])
    ), tags: ['Boards'], parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))], responses: [
        new OA\Response(response: 201, description: '작성 성공'),
        new OA\Response(response: 404, description: '존재하지 않는 후기'),
        new OA\Response(response: 422, description: '유효성 검사 실패'),
    ])]
    public function commentCreate(?string $id = null): ResponseInterface
    {
        if (!$this->validate(['contents' => 'required|max_length[1000]'])) {
            return $this->error('VALIDATION_ERROR', implode(' ', $this->validator->getErrors()), 422);
        }

        try {
            $comment = $this->boards->addComment($this->authUserId(), (int) $id, (string) $this->json()['contents']);

            return $this->success($comment, [], 201);
        } catch (DomainException $e) {
            return $this->error($e->errorCode(), $e->getMessage(), $e->httpStatusCode());
        }
    }

    #[OA\Delete(
        path: '/boards/{id}/comments/{commentId}',
        summary: '댓글 삭제 (본인)',
        security: [['bearerAuth' => []]],
        tags: ['Boards'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'commentId', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: '삭제 성공'),
            new OA\Response(response: 404, description: '존재하지 않거나 권한 없는 댓글'),
        ]
    )]
    public function commentDelete(?string $id = null, ?string $commentId = null): ResponseInterface
    {
        try {
            $this->boards->deleteComment($this->authUserId(), (int) $id, (int) $commentId);

            return $this->success(null);
        } catch (DomainException $e) {
            return $this->error($e->errorCode(), $e->getMessage(), $e->httpStatusCode());
        }
    }

    /**
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

    /**
     * @return array{page: int, limit: int}
     */
    private function pageParams(): array
    {
        return [
            'page'  => max(1, (int) ($this->request->getGet('page') ?? 1)),
            'limit' => max(1, min(100, (int) ($this->request->getGet('per_page') ?? 20))),
        ];
    }

    /**
     * @param array<string, mixed> $p
     * @return array<string, int>
     */
    private function meta(array $p, int $total): array
    {
        $limit = max(1, (int) $p['limit']);

        return [
            'page'      => (int) $p['page'],
            'per_page'  => $limit,
            'total'     => $total,
            'last_page' => (int) ceil($total / $limit),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function json(): array
    {
        $data = $this->request->getJSON(true);

        return is_array($data) ? $data : [];
    }
}
