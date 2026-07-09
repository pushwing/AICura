<?php

namespace App\Controllers\Api\V1;

use App\Models\CodeModel;
use App\Models\SettingModel;
use CodeIgniter\HTTP\ResponseInterface;
use OpenApi\Attributes as OA;
use Throwable;

/**
 * 외부(소비자) 앱 공통·운영 컨트롤러 (이슈 #103)
 *
 * 앱 부트스트랩·운영 엔드포인트(설정·코드·로그·헬스체크). 인증 없이 접근 가능하다
 * (버전 게이트·약관은 로그인 전 필요, 헬스체크는 LB용, 로그는 익명 텔레메트리 허용).
 */
#[OA\Tag(name: 'System', description: '공통·운영 — 소비자 앱')]
class SystemController extends BaseApiController
{
    /**
     * 로그 본문 최대 크기(bytes) — 비인증 엔드포인트 디스크·큐 남용 방지 (이슈 #187)
     */
    private const int MAX_LOG_BYTES = 8192;

    /**
     * 허용 로그 레벨
     */
    private const string ALLOWED_LEVELS = 'debug,info,warn,error';

    #[OA\Get(
        path: '/settings',
        summary: '앱 설정·최소버전·약관 링크',
        tags: ['System'],
        responses: [new OA\Response(response: 200, description: '공개 설정 맵')],
    )]
    public function settings(): ResponseInterface
    {
        return $this->success(model(SettingModel::class)->getPublicMap());
    }

    #[OA\Get(
        path: '/codes',
        summary: '코드성 데이터',
        tags: ['System'],
        parameters: [
            new OA\Parameter(name: 'type', description: '코드 유형 필터', in: 'query', schema: new OA\Schema(type: 'string')),
        ],
        responses: [new OA\Response(response: 200, description: '코드 목록')],
    )]
    public function codes(): ResponseInterface
    {
        $type = $this->request->getGet('type');
        $type = is_string($type) && $type !== '' ? $type : null;

        return $this->success(model(CodeModel::class)->getActive($type));
    }

    #[OA\Post(path: '/logs', summary: '앱 로그 수집 (큐 적재 후 202)', requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(properties: [
            new OA\Property(property: 'level', type: 'string', example: 'info'),
            new OA\Property(property: 'event', type: 'string', example: 'screen_view'),
            new OA\Property(property: 'message', type: 'string', example: '이벤트 상세 진입'),
        ]),
    ), tags: ['System'], responses: [
        new OA\Response(response: 202, description: '수집 접수'),
        new OA\Response(response: 413, description: '본문 크기 초과'),
        new OA\Response(response: 422, description: '본문 형식 오류'),
    ])]
    public function logs(): ResponseInterface
    {
        // 비인증·익명 엔드포인트이므로 디코드 전에 원시 본문 크기부터 제한한다(디스크·큐 남용 방지).
        if (strlen((string) $this->request->getBody()) > self::MAX_LOG_BYTES) {
            return $this->error('PAYLOAD_TOO_LARGE', '로그 본문이 너무 큽니다.', 413);
        }

        $payload = $this->request->getJSON(true);
        if (! is_array($payload) || $payload === []) {
            return $this->error('VALIDATION_ERROR', '로그 본문(JSON 객체)이 필요합니다.', 422);
        }

        // 알려진 필드는 형식·길이를 제한한다(그 외 컨텍스트 키는 크기 상한으로만 통제).
        if (! $this->validate([
            'level'   => 'permit_empty|in_list[' . self::ALLOWED_LEVELS . ']',
            'event'   => 'permit_empty|max_length[100]',
            'message' => 'permit_empty|max_length[2000]',
        ])) {
            return $this->error('VALIDATION_ERROR', implode(' ', $this->validator->getErrors()), 422);
        }

        service('logIngestService')->ingest($payload);

        // 즉시 접수 응답 (DB 직접 쓰기 금지 — 원시 파일 append)
        return $this->respond(['status' => 'accepted'], 202);
    }

    #[OA\Get(
        path: '/health',
        summary: '헬스체크 (DB·캐시 상태)',
        tags: ['System'],
        responses: [
            new OA\Response(response: 200, description: '정상'),
            new OA\Response(response: 503, description: '일부 구성요소 장애'),
        ],
    )]
    public function health(): ResponseInterface
    {
        $db    = $this->checkDb();
        $cache = $this->checkCache();
        $ok    = $db && $cache;

        return $this->respond([
            'status' => $ok ? 'ok' : 'degraded',
            'db'     => $db ? 'up' : 'down',
            'cache'  => $cache ? 'up' : 'down',
        ], $ok ? 200 : 503);
    }

    private function checkDb(): bool
    {
        try {
            db_connect()->query('SELECT 1');

            return true;
        } catch (Throwable) {
            return false;
        }
    }

    private function checkCache(): bool
    {
        try {
            cache()->save('health_ping', '1', 10);

            return cache('health_ping') === '1';
        } catch (Throwable) {
            return false;
        }
    }
}
