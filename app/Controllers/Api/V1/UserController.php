<?php

namespace App\Controllers\Api\V1;

use App\Exceptions\DomainException;
use App\Services\MeService;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Services;
use OpenApi\Attributes as OA;

/**
 * 외부(소비자) 앱 마이페이지 컨트롤러 (이슈 #97)
 *
 * 내 프로필·회원탈퇴·기기등록 + 내 활동(상담·후기·예약·찜·헬스포인트) 조회.
 * 모두 jwt_auth(로그인 필수) 하위. 비즈니스 로직은 MeService 가 담당한다.
 */
#[OA\Tag(name: 'Me', description: '내 정보·마이페이지 — 소비자 앱')]
class UserController extends BaseApiController
{
    private MeService $me;

    public function __construct()
    {
        $this->me = Services::meService();
    }

    #[OA\Get(
        path: '/me',
        summary: '내 프로필',
        security: [['bearerAuth' => []]],
        tags: ['Me'],
        responses: [
            new OA\Response(response: 200, description: '내 프로필'),
            new OA\Response(response: 401, description: '인증 필요'),
        ]
    )]
    public function me(): ResponseInterface
    {
        try {
            return $this->success($this->me->profile($this->authUserId()));
        } catch (DomainException $e) {
            return $this->error($e->errorCode(), $e->getMessage(), $e->httpStatusCode());
        }
    }

    #[OA\Patch(
        path: '/me',
        summary: '프로필 수정',
        security: [['bearerAuth' => []]],
        tags: ['Me'],
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'username', type: 'string', example: '홍길동'),
                    new OA\Property(property: 'phone', type: 'string', example: '01012345678'),
                    new OA\Property(property: 'age', type: 'integer', example: 29),
                    new OA\Property(property: 'sex', type: 'string', example: 'F'),
                    new OA\Property(property: 'job', type: 'string', example: '회사원'),
                    new OA\Property(property: 'picture', type: 'string', example: 'https://...'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: '수정된 프로필'),
            new OA\Response(response: 422, description: '유효성 검사 실패'),
        ]
    )]
    public function updateProfile(): ResponseInterface
    {
        $rules = [
            'username' => 'permit_empty|max_length[100]',
            'phone'    => 'permit_empty|max_length[30]',
            'age'      => 'permit_empty|is_natural_no_zero|less_than[150]',
            'sex'      => 'permit_empty|max_length[10]',
            'job'      => 'permit_empty|max_length[100]',
            'picture'  => 'permit_empty|max_length[500]',
        ];

        if (!$this->validate($rules)) {
            return $this->error('VALIDATION_ERROR', implode(' ', $this->validator->getErrors()), 422);
        }

        try {
            return $this->success($this->me->updateProfile($this->authUserId(), $this->json()));
        } catch (DomainException $e) {
            return $this->error($e->errorCode(), $e->getMessage(), $e->httpStatusCode());
        }
    }

    #[OA\Delete(
        path: '/me',
        summary: '회원 탈퇴',
        security: [['bearerAuth' => []]],
        tags: ['Me'],
        responses: [new OA\Response(response: 200, description: '탈퇴 완료')]
    )]
    public function withdraw(): ResponseInterface
    {
        $this->me->withdraw($this->authUserId());

        return $this->success(null);
    }

    #[OA\Post(
        path: '/me/device',
        summary: '푸시 토큰·기기 등록',
        security: [['bearerAuth' => []]],
        tags: ['Me'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['push_token'],
                properties: [
                    new OA\Property(property: 'push_token', type: 'string', example: 'fcm-token-xxxx'),
                    new OA\Property(property: 'platform', type: 'integer', description: '2 iOS · 3 Android', example: 2),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: '등록 완료'),
            new OA\Response(response: 422, description: '유효성 검사 실패'),
        ]
    )]
    public function registerDevice(): ResponseInterface
    {
        $rules = [
            'push_token' => 'required|max_length[512]',
            'platform'   => 'permit_empty|in_list[2,3]',
        ];

        if (!$this->validate($rules)) {
            return $this->error('VALIDATION_ERROR', implode(' ', $this->validator->getErrors()), 422);
        }

        $input    = $this->json();
        $platform = in_array((int) ($input['platform'] ?? 0), [2, 3], true) ? (int) $input['platform'] : 2;

        $this->me->registerDevice($this->authUserId(), (string) $input['push_token'], $platform);

        return $this->success(null);
    }

    #[OA\Get(
        path: '/me/call-requests',
        summary: '내 상담 신청 내역',
        security: [['bearerAuth' => []]],
        tags: ['Me'],
        responses: [new OA\Response(response: 200, description: '상담 신청 목록')]
    )]
    public function callRequests(): ResponseInterface
    {
        $p = $this->pageParams();
        $r = $this->me->callRequests($this->authUserId(), $p['page'], $p['limit']);

        return $this->success($r['items'], $this->meta($p, $r['total']));
    }

    #[OA\Get(
        path: '/me/boards',
        summary: '내가 쓴 후기',
        security: [['bearerAuth' => []]],
        tags: ['Me'],
        responses: [new OA\Response(response: 200, description: '내 후기 목록')]
    )]
    public function boards(): ResponseInterface
    {
        $p = $this->pageParams();
        $r = $this->me->boards($this->authUserId(), $p['page'], $p['limit']);

        return $this->success($r['items'], $this->meta($p, $r['total']));
    }

    #[OA\Get(
        path: '/me/bookings',
        summary: '내 예약',
        security: [['bearerAuth' => []]],
        tags: ['Me'],
        responses: [new OA\Response(response: 200, description: '내 예약 목록')]
    )]
    public function bookings(): ResponseInterface
    {
        $p = $this->pageParams();
        $r = $this->me->bookings($this->authUserId(), $p['page'], $p['limit']);

        return $this->success($r['items'], $this->meta($p, $r['total']));
    }

    #[OA\Get(
        path: '/me/likes',
        summary: '찜한 이벤트/병원',
        security: [['bearerAuth' => []]],
        tags: ['Me'],
        parameters: [
            new OA\Parameter(name: 'type', in: 'query', schema: new OA\Schema(type: 'string', enum: ['campaign', 'hospital'], default: 'campaign')),
        ],
        responses: [new OA\Response(response: 200, description: '찜 목록')]
    )]
    public function likes(): ResponseInterface
    {
        $p    = $this->pageParams();
        $type = (string) ($this->request->getGet('type') ?? 'campaign');
        $r    = $this->me->likes($this->authUserId(), $type, $p['page'], $p['limit']);

        return $this->success($r['items'], $this->meta($p, $r['total']));
    }

    #[OA\Get(
        path: '/me/health-point',
        summary: '헬스포인트 잔액·내역',
        security: [['bearerAuth' => []]],
        tags: ['Me'],
        responses: [new OA\Response(response: 200, description: '잔액 + 변동 내역')]
    )]
    public function healthPoint(): ResponseInterface
    {
        $p = $this->pageParams();

        try {
            $r = $this->me->healthPoint($this->authUserId(), $p['page'], $p['limit']);
        } catch (DomainException $e) {
            return $this->error($e->errorCode(), $e->getMessage(), $e->httpStatusCode());
        }

        return $this->success(
            ['balance' => $r['balance'], 'logs' => $r['logs']],
            $this->meta($p, $r['total']),
        );
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
     * @param array{page: int, limit: int} $p
     * @return array<string, int>
     */
    private function meta(array $p, int $total): array
    {
        return [
            'page'      => $p['page'],
            'per_page'  => $p['limit'],
            'total'     => $total,
            'last_page' => (int) ceil($total / max(1, $p['limit'])),
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
