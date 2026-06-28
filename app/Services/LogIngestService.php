<?php

namespace App\Services;

/**
 * 외부(소비자) 앱 로그 수집 서비스 (이슈 #103)
 *
 * CLAUDE.md 로그 파이프라인의 '원시 로그 파일 저장' 단계를 담당한다.
 * Redis 큐 인프라(predis)가 도입되면 이 지점에서 큐 적재로 교체할 수 있다.
 * 현재는 writable/logs/raw/YYYY-MM-DD.log 에 JSON 라인으로 append 한다.
 */
class LogIngestService
{
    /**
     * 로그 1건을 원시 파일에 append.
     *
     * @param array<string, mixed> $payload 앱이 전송한 로그 본문
     * @param int|null $userId 인증된 경우 사용자 id
     */
    public function ingest(array $payload, ?int $userId = null): void
    {
        $dir = rtrim(WRITEPATH, '/\\') . '/logs/raw/';
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        $record = [
            'received_at' => date('Y-m-d H:i:s'),
            'user_id'     => $userId,
            'payload'     => $payload,
        ];

        $line = json_encode($record, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        file_put_contents(
            $dir . date('Y-m-d') . '.log',
            $line . PHP_EOL,
            FILE_APPEND | LOCK_EX,
        );
    }
}
