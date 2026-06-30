<?php

namespace App\Services;

use App\Models\CampaignModel;

/**
 * sitemap.xml 생성 서비스 (이슈 #143)
 *
 * 노출 조건을 충족하는 공개 URL 을 모아 sitemaps.org 규격 XML 을 생성한다.
 * 절대 URL 은 `base_url()`(= app.baseURL, 환경변수) 기준으로 만들어 운영 도메인에 자동 대응한다.
 * 결과는 Redis 캐시(1시간)에 저장하며, 캠페인 쓰기 시 무효화 대상은 아니다(주기 갱신으로 충분).
 */
final class SitemapService
{
    /** 캐시 키 (CI4 캐시 키는 ':' 등 예약문자 금지 — 언더스코어 사용) */
    public const CACHE_KEY = 'web_sitemap';

    /** 캐시 TTL (초) — 1시간 (CLAUDE.md §부하분산: sitemap 1h) */
    private const CACHE_TTL = 3600;

    private CampaignModel $campaigns;

    public function __construct()
    {
        $this->campaigns = model(CampaignModel::class);
    }

    /**
     * sitemap.xml 문자열 반환 (캐시 우선).
     */
    public function xml(): string
    {
        /** @var string|null $cached */
        $cached = cache(self::CACHE_KEY);
        if (is_string($cached)) {
            return $cached;
        }

        $xml = $this->build();
        cache()->save(self::CACHE_KEY, $xml, self::CACHE_TTL);

        return $xml;
    }

    /**
     * sitemap XML 생성.
     */
    private function build(): string
    {
        $urls = [];

        // 정적 진입 페이지
        $urls[] = $this->url(base_url('events'), null, 'daily', '0.8');

        // 노출 이벤트 상세
        // TODO(#144·#146): 병원·후기·가이드 공개 페이지 추가 시 여기에 포함
        foreach ($this->campaigns->getSitemapEvents() as $event) {
            $urls[] = $this->url(
                base_url('events/' . (int) $event['id']),
                $event['updated_at'] ?? null,
                'weekly',
                '0.6',
            );
        }

        return '<?xml version="1.0" encoding="UTF-8"?>' . "\n"
            . '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n"
            . implode("\n", $urls) . "\n"
            . '</urlset>' . "\n";
    }

    /**
     * 단일 `<url>` 요소 생성. lastmod 는 W3C(ISO 8601) 포맷.
     */
    private function url(string $loc, ?string $updatedAt, string $changefreq, string $priority): string
    {
        $parts = ['  <url>', '    <loc>' . esc($loc) . '</loc>'];

        if ($updatedAt !== null && trim($updatedAt) !== '') {
            $ts = strtotime($updatedAt);
            if ($ts !== false) {
                $parts[] = '    <lastmod>' . date('c', $ts) . '</lastmod>';
            }
        }

        $parts[] = '    <changefreq>' . $changefreq . '</changefreq>';
        $parts[] = '    <priority>' . $priority . '</priority>';
        $parts[] = '  </url>';

        return implode("\n", $parts);
    }
}
