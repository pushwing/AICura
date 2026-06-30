<?php

namespace App\Libraries\Seo;

/**
 * SEO 메타태그 빌더 (이슈 #143)
 *
 * 공개 웹 레이아웃의 `<head>` 인라인 메타를 라이브러리로 추출한 것.
 * title·description·canonical·robots·Open Graph·Twitter Card 태그를 일관되게 생성한다.
 * 컨트롤러가 전달한 메타 배열을 `fromArray()` 로 받아 `render()` 로 HTML 문자열을 반환한다.
 *
 * 출력 값은 모두 `esc()` 로 이스케이프한다(한글은 프로젝트 표준대로 숫자 엔티티로 출력 — 크롤러 정상 디코드).
 */
final readonly class MetaTagBuilder
{
    public function __construct(
        private string $title,
        private string $description,
        private string $canonical,
        private string $robots = 'index, follow',
        private string $ogType = 'website',
        private ?string $ogImage = null,
        private string $siteName = 'AICura',
        private string $locale = 'ko_KR',
    ) {
    }

    /**
     * 메타 배열 → 빌더. 누락 키는 안전한 기본값으로 보정한다.
     *
     * @param array<string, mixed> $meta
     */
    public static function fromArray(array $meta): self
    {
        return new self(
            title: (string) ($meta['title'] ?? 'AICura'),
            description: (string) ($meta['description'] ?? ''),
            canonical: (string) ($meta['canonical'] ?? ''),
            robots: (string) ($meta['robots'] ?? 'index, follow'),
            ogType: (string) ($meta['og_type'] ?? 'website'),
            ogImage: isset($meta['og_image']) ? (string) $meta['og_image'] : null,
            siteName: (string) ($meta['site_name'] ?? 'AICura'),
            locale: (string) ($meta['locale'] ?? 'ko_KR'),
        );
    }

    /**
     * `<head>` 에 삽입할 메타태그 블록(HTML) 반환.
     */
    public function render(): string
    {
        $hasImage   = $this->ogImage !== null && trim($this->ogImage) !== '';
        $twitterCard = $hasImage ? 'summary_large_image' : 'summary';

        $lines = [
            '<title>' . esc($this->title) . '</title>',
            $this->meta('name', 'description', $this->description),
            $this->meta('name', 'robots', $this->robots),
            '<link rel="canonical" href="' . esc($this->canonical) . '">',

            // Open Graph
            $this->meta('property', 'og:site_name', $this->siteName),
            $this->meta('property', 'og:locale', $this->locale),
            $this->meta('property', 'og:type', $this->ogType),
            $this->meta('property', 'og:title', $this->title),
            $this->meta('property', 'og:description', $this->description),
            $this->meta('property', 'og:url', $this->canonical),

            // Twitter Card
            $this->meta('name', 'twitter:card', $twitterCard),
            $this->meta('name', 'twitter:title', $this->title),
            $this->meta('name', 'twitter:description', $this->description),
        ];

        if ($hasImage) {
            /** @var string $image */
            $image   = $this->ogImage;
            $lines[] = $this->meta('property', 'og:image', $image);
            $lines[] = $this->meta('name', 'twitter:image', $image);
        }

        return implode("\n    ", $lines);
    }

    /**
     * 단일 `<meta>` 태그 생성.
     *
     * $keyAttr·$key 는 코드 내 고정 리터럴이므로 이스케이프하지 않는다.
     * $content 만 html 컨텍스트로 이스케이프 — ASCII 는 그대로, 한글은 엔티티(크롤러 정상 디코드).
     * 큰따옴표 속성값에는 htmlspecialchars(ENT_QUOTES) 로 충분하다(attr 컨텍스트의 과도한 인코딩 회피).
     */
    private function meta(string $keyAttr, string $key, string $content): string
    {
        return sprintf('<meta %s="%s" content="%s">', $keyAttr, $key, esc($content));
    }
}
