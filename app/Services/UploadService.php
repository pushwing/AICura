<?php

namespace App\Services;

use CodeIgniter\HTTP\Files\UploadedFile;

/**
 * 외부(소비자) 앱 이미지 업로드 서비스 (이슈 #102)
 *
 * 업로드 파일은 공개 경로 밖(writable/uploads/boards)에 랜덤 파일명으로 저장한다.
 * 파일명 자체가 추측 불가능한 capability 역할을 하므로, 서빙 라우트는 인증 없이 접근 가능하다.
 * MIME·확장자 검증은 컨트롤러의 Validation(uploaded·is_image·mime_in·max_size)에서 선행한다.
 */
class UploadService
{
    /**
     * 저장 하위 디렉터리 (writable/uploads/ 기준)
     */
    private const string SUBDIR = 'boards';

    /**
     * 허용 확장자 → 정규화
     */
    private const array EXT_MAP = [
        'jpg'  => 'jpg',
        'jpeg' => 'jpg',
        'png'  => 'png',
        'webp' => 'webp',
        'gif'  => 'gif',
    ];

    /**
     * 이미지 저장 — 저장된 파일명과 서빙 URL을 반환.
     *
     * @return array{file: string, url: string}
     */
    public function storeImage(UploadedFile $file): array
    {
        $ext  = self::EXT_MAP[strtolower($file->getExtension())] ?? 'jpg';
        $name = bin2hex(random_bytes(16)) . '.' . $ext;

        $dir = $this->baseDir();
        if (! is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        $file->move($dir, $name);

        return [
            'file' => $name,
            'url'  => $this->urlFor($name),
        ];
    }

    /**
     * 서빙용 파일 경로·MIME 반환 — 미존재·잘못된 이름이면 null.
     *
     * @return array{path: string, mime: string}|null
     */
    public function locate(string $name): ?array
    {
        // 경로 조작 방지 — 파일명만 허용
        if ($name !== basename($name)) {
            return null;
        }

        $path = $this->baseDir() . $name;
        if (! is_file($path)) {
            return null;
        }

        $ext  = strtolower(pathinfo($name, PATHINFO_EXTENSION));
        $mime = match ($ext) {
            'png'   => 'image/png',
            'webp'  => 'image/webp',
            'gif'   => 'image/gif',
            default => 'image/jpeg',
        };

        return ['path' => $path, 'mime' => $mime];
    }

    /**
     * 저장된 파일명 → 절대 서빙 URL.
     */
    public function urlFor(string $name): string
    {
        return (string) base_url('api/v1/uploads/images/' . $name);
    }

    private function baseDir(): string
    {
        return rtrim(WRITEPATH, '/\\') . '/uploads/' . self::SUBDIR . '/';
    }
}
