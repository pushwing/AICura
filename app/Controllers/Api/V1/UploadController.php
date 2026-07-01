<?php

namespace App\Controllers\Api\V1;

use App\Services\UploadService;
use CodeIgniter\HTTP\Files\UploadedFile;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Services;
use OpenApi\Attributes as OA;

/**
 * 외부(소비자) 앱 이미지 업로드 컨트롤러 (이슈 #102)
 *
 * - 업로드(images)는 jwt_auth 하위(로그인 필수).
 * - 서빙(serve)은 인증 없이 접근 — 파일명이 추측 불가능한 capability 역할을 한다.
 */
#[OA\Tag(name: 'Uploads', description: '이미지 업로드 — 소비자 앱')]
class UploadController extends BaseApiController
{
    private readonly UploadService $uploads;

    public function __construct()
    {
        $this->uploads = Services::uploadService();
    }

    #[OA\Post(path: '/uploads/images', summary: '이미지 업로드 (후기/프로필 공용)', security: [['bearerAuth' => []]], requestBody: new OA\RequestBody(
        required: true,
        content: new OA\MediaType(
            mediaType: 'multipart/form-data',
            schema: new OA\Schema(properties: [
                new OA\Property(property: 'image', type: 'string', format: 'binary'),
            ])
        )
    ), tags: ['Uploads'], responses: [
        new OA\Response(response: 201, description: '업로드 성공 (file, url)'),
        new OA\Response(response: 422, description: '유효성 검사 실패'),
    ])]
    public function images(): ResponseInterface
    {
        $rules = [
            'image' => 'uploaded[image]|is_image[image]|max_size[image,5120]|mime_in[image,image/png,image/jpeg,image/webp,image/gif]',
        ];

        if (!$this->validate($rules)) {
            return $this->error('VALIDATION_ERROR', implode(' ', $this->validator->getErrors()), 422);
        }

        $file = $this->request->getFile('image');
        if (!$file instanceof UploadedFile || !$file->isValid()) {
            return $this->error('VALIDATION_ERROR', '업로드된 이미지가 올바르지 않습니다.', 422);
        }

        return $this->success($this->uploads->storeImage($file), [], 201);
    }

    #[OA\Get(
        path: '/uploads/images/{name}',
        summary: '업로드 이미지 서빙',
        tags: ['Uploads'],
        parameters: [new OA\Parameter(name: 'name', in: 'path', required: true, schema: new OA\Schema(type: 'string'))],
        responses: [
            new OA\Response(response: 200, description: '이미지 바이너리'),
            new OA\Response(response: 404, description: '존재하지 않는 파일'),
        ]
    )]
    public function serve(?string $name = null): ResponseInterface
    {
        $located = $this->uploads->locate((string) $name);
        if ($located === null) {
            return $this->error('NOT_FOUND', '파일을 찾을 수 없습니다.', 404);
        }

        return $this->response
            ->setHeader('Content-Type', $located['mime'])
            ->setHeader('Cache-Control', 'public, max-age=86400')
            ->setBody((string) file_get_contents($located['path']));
    }
}
