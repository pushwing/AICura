<?php

namespace App\Controllers\Api\V1;

use App\Exceptions\DomainException;
use App\Services\CallRequestService;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Services;
use OpenApi\Attributes as OA;

/**
 * 외부(소비자) 앱 상담 신청 컨트롤러 (이슈 #100)
 *
 * 이벤트(캠페인)에 대한 상담/전화 신청 생성·조회·취소. 모두 jwt_auth(로그인 필수) 하위.
 * 비즈니스 로직은 CallRequestService 가 담당한다.
 */
#[OA\Tag(name: 'CallRequests', description: '상담 신청 — 소비자 앱')]
class CallRequestController extends BaseApiController
{
    private CallRequestService $callRequests;

    public function __construct()
    {
        $this->callRequests = Services::callRequestService();
    }

    #[OA\Post(
        path: '/call-requests',
        summary: '상담/전화 신청',
        security: [['bearerAuth' => []]],
        tags: ['CallRequests'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['campaign_id', 'name', 'phone', 'privacy_agree'],
                properties: [
                    new OA\Property(property: 'campaign_id', type: 'integer', example: 12),
                    new OA\Property(property: 'name', type: 'string', example: '홍길동'),
                    new OA\Property(property: 'phone', type: 'string', example: '01012345678'),
                    new OA\Property(property: 'privacy_agree', type: 'boolean', description: '개인정보 수집·이용 동의 (필수)', example: true),
                    new OA\Property(property: 'supply_third_party_agree', type: 'boolean', description: '제3자(병원) 제공 동의', example: true),
                    new OA\Property(property: 'content', type: 'string', example: '리프팅 상담 원해요'),
                    new OA\Property(property: 'call_time', type: 'string', example: '오후 2시 이후'),
                    new OA\Property(property: 'age', type: 'integer', example: 29),
                    new OA\Property(property: 'sex', type: 'integer', description: '1 남 · 2 여', example: 2),
                    new OA\Property(property: 'funnel', type: 'string', example: 'event_detail'),
                    new OA\Property(property: 'region', type: 'string', example: '서울'),
                    new OA\Property(property: 'device', type: 'integer', description: '1 Android · 2 iOS', example: 2),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: '신청 성공'),
            new OA\Response(response: 404, description: '신청할 수 없는 이벤트'),
            new OA\Response(response: 422, description: '유효성 검사 실패'),
        ]
    )]
    public function create(): ResponseInterface
    {
        $rules = [
            'campaign_id' => 'required|is_natural_no_zero',
            'name'        => 'required|max_length[100]',
            'phone'       => 'required|max_length[30]',
            'content'     => 'permit_empty|max_length[2000]',
            'call_time'   => 'permit_empty|max_length[100]',
            'age'         => 'permit_empty|is_natural_no_zero|less_than[150]',
            'sex'         => 'permit_empty|in_list[0,1,2]',
            'device'      => 'permit_empty|in_list[1,2]',
            'funnel'      => 'permit_empty|max_length[500]',
            'region'      => 'permit_empty|max_length[100]',
        ];

        if (!$this->validate($rules)) {
            return $this->error('VALIDATION_ERROR', implode(' ', $this->validator->getErrors()), 422);
        }

        $input = $this->json();

        // 개인정보 수집·이용 동의는 필수 (boolean/1 모두 허용)
        if (empty($input['privacy_agree'])) {
            return $this->error('VALIDATION_ERROR', '개인정보 수집·이용에 동의해야 신청할 수 있습니다.', 422);
        }

        try {
            $detail = $this->callRequests->apply($this->authUserId(), $input);
        } catch (DomainException $e) {
            return $this->error($e->errorCode(), $e->getMessage(), $e->httpStatusCode());
        }

        return $this->success($detail, [], 201);
    }

    #[OA\Get(
        path: '/call-requests/{id}',
        summary: '상담 신청 상세·진행상태',
        security: [['bearerAuth' => []]],
        tags: ['CallRequests'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: '신청 상세'),
            new OA\Response(response: 404, description: '존재하지 않거나 권한 없는 신청'),
        ]
    )]
    public function show($id = null): ResponseInterface
    {
        try {
            $detail = $this->callRequests->detail($this->authUserId(), (int) $id);
        } catch (DomainException $e) {
            return $this->error($e->errorCode(), $e->getMessage(), $e->httpStatusCode());
        }

        return $this->success($detail);
    }

    #[OA\Delete(
        path: '/call-requests/{id}',
        summary: '상담 신청 취소 (미확인 건만)',
        security: [['bearerAuth' => []]],
        tags: ['CallRequests'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: '취소 성공'),
            new OA\Response(response: 404, description: '존재하지 않거나 권한 없는 신청'),
            new OA\Response(response: 409, description: '이미 처리되어 취소 불가'),
        ]
    )]
    public function delete($id = null): ResponseInterface
    {
        try {
            $this->callRequests->cancel($this->authUserId(), (int) $id);
        } catch (DomainException $e) {
            return $this->error($e->errorCode(), $e->getMessage(), $e->httpStatusCode());
        }

        return $this->success(null);
    }

    /**
     * 요청 JSON 본문을 연관 배열로 반환
     *
     * @return array<string, mixed>
     */
    private function json(): array
    {
        $data = $this->request->getJSON(true);

        return is_array($data) ? $data : [];
    }
}
