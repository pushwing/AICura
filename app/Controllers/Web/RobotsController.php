<?php

namespace App\Controllers\Web;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * robots.txt 동적 서빙 (이슈 #143)
 *
 * 정적 파일 대신 컨트롤러로 서빙해 `Sitemap:` 을 절대 URL(= app.baseURL, 환경변수)로 출력한다.
 * 운영 도메인 확정 시 .env 의 app.baseURL 만 바꾸면 자동 반영된다(이슈 #137 §0 결정).
 *
 * 정책: 색인 허용 + AI 검색·생성형 엔진 크롤러 전체 허용, 내부 경로(admin/portal/api) 차단.
 */
class RobotsController extends BaseController
{
    /** @var array<int, string> 전체 허용할 AI 크롤러 User-agent */
    private const array AI_CRAWLERS = [
        'GPTBot',             // OpenAI
        'OAI-SearchBot',      // ChatGPT Search
        'ChatGPT-User',
        'PerplexityBot',
        'Google-Extended',    // Gemini / AI Overviews
        'ClaudeBot',
        'Claude-Web',
        'Applebot-Extended',
        'Bingbot',            // Bing 색인 → ChatGPT Search 인용 기반 (이슈 #160)
    ];

    public function index(): ResponseInterface
    {
        $lines = [
            '# AICura robots.txt (이슈 #137·#143 — SEO/GEO)',
            '# 동적 생성 — Sitemap 은 app.baseURL 기준 절대 URL',
            '',
            'User-agent: *',
            'Allow: /',
            'Disallow: /admin/',
            'Disallow: /portal/',
            'Disallow: /api/',
            '',
            '# AI 검색·생성형 엔진 크롤러 — 전체 허용 (AI 검색 노출 목표)',
        ];

        foreach (self::AI_CRAWLERS as $agent) {
            $lines[] = 'User-agent: ' . $agent;
            $lines[] = 'Allow: /';
            $lines[] = '';
        }

        $lines[] = 'Sitemap: ' . base_url('sitemap.xml');

        return $this->response
            ->setHeader('Content-Type', 'text/plain; charset=UTF-8')
            ->setBody(implode("\n", $lines) . "\n");
    }
}
