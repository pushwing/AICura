<?php

namespace App\Services;

use App\Libraries\Ai\AiClientFactory;
use App\Libraries\Ai\AiClientInterface;
use CodeIgniter\Cache\CacheInterface;

/**
 * AI 광고 카피 생성 어시스턴트 서비스 (이슈 #73)
 *
 * 키워드·병원유형·카테고리를 받아 의료법 심의를 통과할 수 있는
 * 광고 제목 후보 3개와 상세문구(HTML)를 Gemini AI로 생성한다.
 * 사용자가 버튼을 눌러 즉시 결과를 봐야 하므로 동기 호출이며,
 * 동일 입력 해시 키로 file 캐시(1시간)를 적용해 반복 호출을 방어한다.
 */
class AiCopyService
{
    /** 캐시 TTL(초) — 동일 입력 반복 생성 방어 */
    private const int CACHE_TTL = 3600;

    // CI4 캐시 키는 콜론(:) 등을 예약 문자로 금지(app/Config/Cache.php)하므로 언더스코어 사용
    private const string CACHE_PREFIX = 'ai_copy_';

    /** 생성 제목 후보 개수 */
    private const int TITLE_COUNT = 3;

    /** 상세문구에 허용하는 HTML 태그 (Tiptap StarterKit 출력 호환) */
    private const string ALLOWED_TAGS = '<p><br><strong><em><s><ul><ol><li><h3><h4><blockquote>';

    private readonly AiClientInterface $ai;
    private readonly CacheInterface $cache;

    public function __construct(?AiClientInterface $ai = null, ?CacheInterface $cache = null)
    {
        // 이슈 #71·#73은 Gemini 사용 — 공급자 명시 고정 (#65 보고서는 Groq 유지)
        $this->ai    = $ai    ?? AiClientFactory::make('gemini');
        $this->cache = $cache ?? cache();
    }

    /**
     * 광고 카피 생성 — 제목 후보 3개 + 상세문구(HTML)
     *
     * @param array{keyword?: string, hospital_type?: string, category?: string} $input
     *
     * @return array{titles: array<int, string>, detail: string}
     */
    public function suggest(array $input): array
    {
        $normalized = [
            'keyword'       => trim((string) ($input['keyword'] ?? '')),
            'hospital_type' => trim((string) ($input['hospital_type'] ?? '')),
            'category'      => trim((string) ($input['category'] ?? '')),
        ];

        $cacheKey = self::CACHE_PREFIX . md5(json_encode($normalized, JSON_UNESCAPED_UNICODE) ?: '');

        /** @var array{titles: array<int, string>, detail: string}|null $cached */
        $cached = $this->cache->get($cacheKey);
        if (is_array($cached)) {
            return $cached;
        }

        $result = $this->normalize($this->ai->completeJson($this->systemPrompt(), $this->userPrompt($normalized)));

        $this->cache->save($cacheKey, $result, self::CACHE_TTL);

        return $result;
    }

    /**
     * AI 원시 응답을 안전한 구조로 정규화 — 제목 3개 + 화이트리스트 필터된 HTML
     *
     * @param array<string, mixed> $raw
     *
     * @return array{titles: array<int, string>, detail: string}
     */
    public function normalize(array $raw): array
    {
        $titles   = [];
        $rawTitles = $raw['titles'] ?? [];
        if (is_array($rawTitles)) {
            foreach ($rawTitles as $title) {
                $value = trim(is_string($title) ? $title : '');
                if ($value !== '') {
                    $titles[] = $value;
                }
                if (count($titles) >= self::TITLE_COUNT) {
                    break;
                }
            }
        }

        $detail = is_string($raw['detail'] ?? null) ? $this->sanitizeHtml($raw['detail']) : '';

        return ['titles' => $titles, 'detail' => $detail];
    }

    /**
     * 상세문구 HTML 정화 — 허용 태그만 남기고 모든 속성 제거 (XSS 방지)
     */
    private function sanitizeHtml(string $html): string
    {
        $stripped = strip_tags($html, self::ALLOWED_TAGS);

        // 여는 태그의 속성 제거: <p class="x"> → <p>, <br/> → <br> (닫는 태그는 보존)
        $clean = preg_replace('/<(\w+)[^>]*>/', '<$1>', $stripped);

        return trim($clean ?? $stripped);
    }

    private function systemPrompt(): string
    {
        return <<<'PROMPT'
            당신은 대한민국 성형·의료 광고의 카피라이터입니다. 입력된 키워드·병원유형·카테고리를 바탕으로
            의료법 제56조(의료광고 금지) 심의를 통과할 수 있는 광고 카피를 생성합니다.

            반드시 지킬 심의 규칙(금지 표현 회피):
            - 거짓·과장 금지 (효과 보장·단정 금지)
            - 최상급·배타성 표현 금지 ("최고", "1위", "유일", "100%", "완벽" 등)
            - 부작용·통증 등 중요 정보를 은폐하는 표현 금지
            - 비급여 진료비 과도한 할인·유인 표현 금지
            - 환자 치료경험담·후기 활용 금지
            - 다른 의료기관·의료인 비교·비방 금지

            출력 형식 규칙:
            - 반드시 아래 JSON 스키마로만 응답하고 다른 문장은 출력하지 마세요.
            - titles 는 서로 다른 광고 제목 후보 3개(각 30자 이내)입니다.
            - detail 은 상세문구를 HTML 로 작성하되 <p>, <strong>, <em>, <ul>, <ol>, <li>, <h3>, <br> 태그만 사용합니다.
            - detail 에는 style/class/script 등 속성이나 다른 태그를 넣지 마세요.

            JSON 스키마:
            {
              "titles": ["제목1", "제목2", "제목3"],
              "detail": "<p>상세 문구</p>"
            }
            PROMPT;
    }

    /**
     * @param array{keyword: string, hospital_type: string, category: string} $input
     */
    private function userPrompt(array $input): string
    {
        $payload = json_encode([
            '키워드'    => $input['keyword'] !== '' ? $input['keyword'] : '(미지정)',
            '병원유형'  => $input['hospital_type'] !== '' ? $input['hospital_type'] : '(미지정)',
            '카테고리'  => $input['category'] !== '' ? $input['category'] : '(미지정)',
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

        return "다음 조건으로 의료광고 카피를 생성하세요.\n\n{$payload}";
    }
}
