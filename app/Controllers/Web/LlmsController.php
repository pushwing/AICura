<?php

namespace App\Controllers\Web;

use App\Controllers\BaseController;
use App\Services\EventService;
use App\Services\GuideService;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Services;

/**
 * llms.txt 동적 서빙 (이슈 #147 — GEO / AI 크롤러용 사이트 요약)
 *
 * llmstxt.org 규격의 마크다운으로 사이트 개요·핵심 페이지·발행 가이드·이벤트 카테고리를 제공한다.
 * 절대 URL 은 base_url()(= app.baseURL, 환경변수) 기준 — 운영 도메인 확정 시 .env 만 바꾸면 반영된다.
 */
class LlmsController extends BaseController
{
    /** 나열할 발행 가이드 최대 수 */
    private const GUIDE_LIMIT = 100;

    public function index(): ResponseInterface
    {
        /** @var GuideService $guideService */
        $guideService = Services::guideService();
        /** @var EventService $eventService */
        $eventService = Services::eventService();

        $lines = [
            '# AICura',
            '',
            '> AI 기반 성형·시술 정보 플랫폼. 검증(검수 완료)된 성형·시술 이벤트, 병원, 실사용 후기, '
                . '시술 정보 가이드를 제공합니다.',
            '',
            '아래는 AI 검색·생성형 엔진이 참고할 주요 콘텐츠 목록입니다. 모든 링크는 크롤링 가능한 공개 페이지입니다.',
            '',
            '## 주요 페이지',
            '- [성형·시술 이벤트](' . base_url('events') . '): 진행 중인 성형·시술 이벤트 목록',
            '- [병원 찾기](' . base_url('hospitals') . '): 지역·진료과별 성형·시술 병원',
            '- [실사용 후기](' . base_url('reviews') . '): 실제 이용자 후기와 평점',
            '- [시술 정보 가이드](' . base_url('guides') . '): 시술 비용·과정·주의사항 정보',
        ];

        $guideLines = $this->guideLines($guideService);
        if ($guideLines !== []) {
            $lines[] = '';
            $lines[] = '## 시술 가이드';
            $lines   = array_merge($lines, $guideLines);
        }

        $categoryLines = $this->categoryLines($eventService);
        if ($categoryLines !== []) {
            $lines[] = '';
            $lines[] = '## 이벤트 카테고리';
            $lines   = array_merge($lines, $categoryLines);
        }

        $lines[] = '';
        $lines[] = '## 참고';
        $lines[] = '- [sitemap.xml](' . base_url('sitemap.xml') . '): 전체 공개 URL 목록';
        $lines[] = '';

        return $this->response
            ->setHeader('Content-Type', 'text/plain; charset=UTF-8')
            ->setBody(implode("\n", $lines));
    }

    /**
     * 발행 가이드 → `- [제목](url): 요약` 목록.
     *
     * @return list<string>
     */
    private function guideLines(GuideService $guideService): array
    {
        $result = $guideService->publishedList(['page' => 1, 'limit' => self::GUIDE_LIMIT]);

        $lines = [];
        foreach ($result['items'] as $guide) {
            $url     = base_url('guides/' . rawurlencode((string) $guide['slug']));
            $summary = trim((string) ($guide['summary'] ?? ''));
            $line    = '- [' . (string) $guide['title'] . '](' . $url . ')';
            if ($summary !== '') {
                $line .= ': ' . $summary;
            }
            $lines[] = $line;
        }

        return $lines;
    }

    /**
     * 노출 이벤트 카테고리 → `- 이름` 목록.
     *
     * @return list<string>
     */
    private function categoryLines(EventService $eventService): array
    {
        $lines = [];
        foreach ($eventService->categories() as $category) {
            $title = trim((string) ($category['title'] ?? ''));
            if ($title !== '') {
                $lines[] = '- ' . $title;
            }
        }

        return $lines;
    }
}
