<?php

namespace App\Controllers\Api\V1;

use App\Exceptions\DomainException;
use App\Services\BookingService;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Services;
use OpenApi\Attributes as OA;

/**
 * 외부(소비자) 앱 예약 컨트롤러 (이슈 #101)
 *
 * 병원 예약 생성·조회·변경·취소. 모두 jwt_auth(로그인 필수) 하위·본인 소유 한정.
 */
#[OA\Tag(name: 'Bookings', description: '예약 — 소비자 앱')]
class BookingController extends BaseApiController
{
    private BookingService $bookings;

    public function __construct()
    {
        $this->bookings = Services::bookingService();
    }

    #[OA\Post(
        path: '/bookings',
        summary: '예약 생성',
        security: [['bearerAuth' => []]],
        tags: ['Bookings'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['hospital_id'],
                properties: [
                    new OA\Property(property: 'hospital_id', type: 'integer', example: 5),
                    new OA\Property(property: 'name', type: 'string', example: '홍길동'),
                    new OA\Property(property: 'phone', type: 'string', example: '01012345678'),
                    new OA\Property(property: 'book_date', type: 'string', format: 'date-time', example: '2026-07-01 14:00:00'),
                    new OA\Property(property: 'call_request_id', type: 'integer', description: '연결할 상담 신청(선택)', example: 12),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: '예약 생성'),
            new OA\Response(response: 404, description: '병원/상담건 없음'),
            new OA\Response(response: 422, description: '유효성 검사 실패'),
        ]
    )]
    public function create(): ResponseInterface
    {
        $rules = [
            'hospital_id'     => 'required|is_natural_no_zero',
            'name'            => 'permit_empty|max_length[255]',
            'phone'           => 'permit_empty|max_length[255]',
            'call_request_id' => 'permit_empty|is_natural_no_zero',
        ];

        if (!$this->validate($rules)) {
            return $this->error('VALIDATION_ERROR', implode(' ', $this->validator->getErrors()), 422);
        }

        try {
            return $this->success($this->bookings->create($this->authUserId(), $this->json()), [], 201);
        } catch (DomainException $e) {
            return $this->error($e->errorCode(), $e->getMessage(), $e->httpStatusCode());
        }
    }

    #[OA\Get(
        path: '/bookings/{id}',
        summary: '예약 상세',
        security: [['bearerAuth' => []]],
        tags: ['Bookings'],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(response: 200, description: '예약 상세'),
            new OA\Response(response: 404, description: '존재하지 않거나 권한 없는 예약'),
        ]
    )]
    public function show($id = null): ResponseInterface
    {
        try {
            return $this->success($this->bookings->detail($this->authUserId(), (int) $id));
        } catch (DomainException $e) {
            return $this->error($e->errorCode(), $e->getMessage(), $e->httpStatusCode());
        }
    }

    #[OA\Patch(
        path: '/bookings/{id}',
        summary: '예약 변경',
        security: [['bearerAuth' => []]],
        tags: ['Bookings'],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(response: 200, description: '변경 성공'),
            new OA\Response(response: 404, description: '존재하지 않거나 권한 없는 예약'),
            new OA\Response(response: 409, description: '이미 취소된 예약'),
        ]
    )]
    public function update($id = null): ResponseInterface
    {
        $rules = [
            'name'      => 'permit_empty|max_length[255]',
            'phone'     => 'permit_empty|max_length[255]',
        ];

        if (!$this->validate($rules)) {
            return $this->error('VALIDATION_ERROR', implode(' ', $this->validator->getErrors()), 422);
        }

        try {
            return $this->success($this->bookings->update($this->authUserId(), (int) $id, $this->json()));
        } catch (DomainException $e) {
            return $this->error($e->errorCode(), $e->getMessage(), $e->httpStatusCode());
        }
    }

    #[OA\Delete(
        path: '/bookings/{id}',
        summary: '예약 취소',
        security: [['bearerAuth' => []]],
        tags: ['Bookings'],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(response: 200, description: '취소 성공'),
            new OA\Response(response: 404, description: '존재하지 않거나 권한 없는 예약'),
            new OA\Response(response: 409, description: '이미 취소된 예약'),
        ]
    )]
    public function delete($id = null): ResponseInterface
    {
        try {
            $this->bookings->cancel($this->authUserId(), (int) $id);

            return $this->success(null);
        } catch (DomainException $e) {
            return $this->error($e->errorCode(), $e->getMessage(), $e->httpStatusCode());
        }
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
