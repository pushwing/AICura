<?php

namespace App\Controllers\Api;

use CodeIgniter\Controller;
use OpenApi\Attributes as OA;
use OpenApi\Generator;

#[OA\Info(
    version: '1.0.0',
    description: 'AI Cura 광고 솔루션 REST API',
    title: 'AI Cura API',
    contact: new OA\Contact(email: 'admin@aicura.io')
)]
#[OA\Server(url: '/api/v1', description: 'API v1')]
#[OA\SecurityScheme(
    securityScheme: 'bearerAuth',
    type: 'http',
    scheme: 'bearer',
    bearerFormat: 'JWT'
)]
class DocsController extends Controller
{
    /**
     * Swagger UI 페이지
     */
    public function index(): string
    {
        return view('api/swagger_ui');
    }

    /**
     * OpenAPI JSON 스펙 동적 생성 (개발) 또는 정적 파일 서빙 (운영)
     */
    public function spec(): \CodeIgniter\HTTP\ResponseInterface
    {
        $response = service('response');

        if (ENVIRONMENT === 'production') {
            $specPath = FCPATH . 'swagger.json';

            if (!is_file($specPath)) {
                return $response->setStatusCode(404)
                    ->setJSON(['error' => 'swagger.json not found. Run: php spark swagger:generate']);
            }

            return $response
                ->setHeader('Content-Type', 'application/json')
                ->setBody(file_get_contents($specPath));
        }

        // 개발 환경: 매 요청마다 동적 생성
        $openapi = (new Generator())->generate([APPPATH . 'Controllers/Api']);

        return $response
            ->setHeader('Content-Type', 'application/json')
            ->setBody($openapi->toJson());
    }
}
