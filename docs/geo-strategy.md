# GEO 적용 전략 (SEO → GEO)

> 이슈 [#137](https://github.com/) — AICura에 SEO를 먼저 적용한 뒤 GEO(Generative Engine Optimization)를 적용해
> **AI 검색(ChatGPT Search·Perplexity·Google AI Overviews·Gemini 등) 노출도**를 높이는 전략 문서.
>
> 본 문서는 **전략·로드맵·아키텍처 설계**까지를 범위로 한다. 실제 코드 구현은 후속 이슈로 분할한다(§9).

---

## 0. 확정된 선행 결정사항 (2026-06-30)

| # | 항목 | 결정 | 반영 |
|---|------|------|------|
| 1 | 운영 도메인·HTTPS | **환경변수로 추상화** — 도메인 미확정. `app.baseURL`(.env) 기준으로 구축하고, 확정 시 값만 주입. canonical·OG·sitemap 모두 `base_url()` 경유 | §3.1, §5 |
| 2 | 공개 페이지 색인 정책 | **색인 허용 전제** — 공개 페이지 기본 `robots: index, follow`. 단, 미심의·저신뢰 콘텐츠는 개별 `noindex`(§4.3, §8) | §5, §8 |
| 3 | AI 크롤러 정책 | **전체 허용** — 검색 인용·학습 크롤러(GPTBot·OAI-SearchBot·PerplexityBot·Google-Extended·ClaudeBot 등) 모두 허용. AI 검색 노출 극대화가 이슈 목표 | §6 |

> 의료광고법(§8)은 기술 외 **사업·법무 결정**으로, 색인 허용 전제이되 미심의 콘텐츠는 `noindex` 처리하는 것을 기본 방어선으로 한다.

---

## 1. 배경 — 현재 AICura의 공개 크롤링 표면은 0

AICura는 성형·토탈 광고 솔루션으로, 코드베이스는 세 가지 표면으로 구성된다.

| 영역 | 인증 | 출력 | 크롤링/색인 가능? |
|------|------|------|------------------|
| `app/Controllers/Admin/*` | 세션 | HTML | ❌ 내부 운영용 |
| `app/Controllers/Portal/*` (광고주) | 세션 | HTML | ❌ 내부용 |
| `app/Controllers/Api/V1/*` (소비자 앱) | JWT optional | **JSON** | ❌ JSON 응답은 검색엔진이 의미 있게 색인하지 못함 |
| 소비자 앱 (`app-mobile/`) | — | Flutter 모바일 | ❌ 웹 페이지 없음 |

**핵심 제약:** 소비자 콘텐츠(이벤트/캠페인·병원·후기·가이드)는 현재 **JWT-optional JSON API로만** 존재한다.
검색엔진과 AI 크롤러는 사람이 보는 **렌더링된 HTML**을 색인하므로, 지금 상태로는 SEO/GEO를 적용할 **대상 페이지 자체가 없다.**

추가 현황:
- `public/robots.txt` 는 전체 허용(`Disallow:` 빈 값)이나 **sitemap.xml 없음**
- **SSR HTML 페이지 없음**, **JSON-LD 구조화 데이터 없음**, **OG/Twitter/canonical 메타 없음**
- `app/Config/App.php` 의 `baseURL` 이 아직 `http://localhost:8080/` (운영 도메인 미확정)

> **결론:** SEO/GEO의 1순위 전제 조건은 **소비자 콘텐츠를 공개·서버사이드 렌더링(SSR) HTML로 노출하는 웹 레이어**다.
> 이게 선행되지 않으면 메타태그·구조화데이터·sitemap을 붙일 페이지가 존재하지 않는다.

---

## 2. SEO vs GEO — 무엇이 다른가

| 구분 | SEO (전통 검색 최적화) | GEO (생성형 엔진 최적화) |
|------|----------------------|------------------------|
| 목표 | 검색결과 **순위** 상승 → 클릭 유도 | LLM 답변 안에 **인용·언급**되기 |
| 소비 주체 | 사람(클릭) | LLM(요약·인용 후 사람에게 전달) |
| 평가 단위 | 페이지 단위 랭킹 | 문장·사실(fact) 단위 추출 가능성 |
| 핵심 신호 | 키워드, 백링크, Core Web Vitals, 메타 | **명확한 사실 진술**, 구조화 데이터, 인용 가능성, 출처 신뢰도, 엔티티 명료성 |
| 대표 채널 | Google·Naver 검색 | ChatGPT Search, Perplexity, Google AI Overviews, Gemini, Copilot |

**관계:** GEO는 SEO를 대체하지 않고 **위에 쌓인다.** AI 크롤러도 결국 크롤링 가능한 HTML·구조화 데이터·sitemap에 의존한다.
따라서 이슈 요구대로 **SEO 기반(Phase 1)을 먼저 갖춘 뒤 GEO 레이어(Phase 2)를 올린다.**

---

## 3. 아키텍처 설계 — CI4 프로젝트 내 공개 SSR 레이어

> 결정사항: 별도 프론트엔드(Next.js 등) 없이 **이 CI4 프로젝트 안에 SSR 웹 레이어**를 신설한다.
> 기존 모델·서비스(`EventService`, `HospitalModel`, `BoardModel` 등)를 그대로 재사용하므로 추가 인프라가 없다.

### 3.1 디렉토리·라우트 규칙 (신규)

| 경로 | 용도 |
|------|------|
| `app/Controllers/Web/` | **공개 SSR 컨트롤러** (인증 없음, HTML 반환) |
| `app/Views/web/` | 공개 페이지 뷰 (SEO 메타 + JSON-LD 포함 레이아웃) |
| `app/Views/web/layout/` | 공개용 공통 레이아웃 — `<head>` 메타/구조화데이터 블록 |
| `app/Libraries/Seo/` | 메타태그·JSON-LD 빌더 (`MetaTagBuilder`, `JsonLdBuilder`) |
| `public/robots.txt` | 갱신 (Sitemap 지시자 추가) |
| `public/sitemap.xml` | **동적 생성** 라우트로 서빙 (정적 파일 아님) |
| `public/llms.txt` | GEO용 — 사이트 요약·핵심 링크 (§5.2) |

라우트 예시 (`app/Config/Routes.php`):

```php
// ── 공개 웹 (SSR · SEO/GEO) ───────────────────────────────
$routes->group('', ['namespace' => 'App\Controllers\Web'], static function ($routes): void {
    $routes->get('events',            'EventPageController::index');   // 이벤트 목록
    $routes->get('events/(:num)',     'EventPageController::show/$1'); // 이벤트 상세
    $routes->get('hospitals',         'HospitalPageController::index');
    $routes->get('hospitals/(:num)',  'HospitalPageController::show/$1');
    $routes->get('reviews',           'ReviewPageController::index');
    $routes->get('reviews/(:num)',    'ReviewPageController::show/$1');
    $routes->get('guides',            'GuidePageController::index');   // 시술 가이드(신규 콘텐츠)
    $routes->get('guides/(:segment)', 'GuidePageController::show/$1');
});

// 크롤러 진입점
$routes->get('sitemap.xml', 'Web\SitemapController::index');
```

> **URI 설계:** 사람이 읽고 AI가 엔티티를 파악하기 쉽도록 가능하면 슬러그 포함을 권장
> (예: `/events/123-eye-double-fold`). 초기엔 `(:num)`만으로 시작하고 슬러그는 점진 도입.

### 3.2 레이어 책임 (CLAUDE.md 준수)

- **Controller(Web)는 얇게:** 캐시 조회 → Service 호출 → 뷰 렌더. 비즈니스 로직 금지.
- 데이터 접근은 기존 **Service/Model 재사용** (`service('eventService')` 등). 신규 쿼리 중복 작성 금지.
- 출력은 전부 `esc()`. 후기 등 사용자 HTML은 **허용 태그 화이트리스트** 적용.

### 3.3 부하 분산 (CLAUDE.md 「API 부하 분산 원칙」 적용)

공개 페이지는 크롤러 트래픽이 몰릴 수 있으므로 캐시를 1순위로 설계한다.

> CI4 캐시 키는 `:` 등 예약문자를 금지하므로 실제 키는 언더스코어를 쓴다(예: `web_sitemap`).

| 페이지 | 캐시 키 | TTL |
|--------|---------|-----|
| 이벤트 목록 | `web_events_list_{파라미터해시}` | 5–15분 |
| 이벤트 상세 | `web_events_{id}` | 5–10분 |
| 병원 상세 | `web_hospitals_{id}` | 30–60분 |
| 후기 상세 | `web_reviews_{id}` | 10–30분 |
| sitemap.xml | `web_sitemap` (구현됨, #143) | 1시간 |

- 캐시 미스 시에만 DB 조회, 쓰기(승인·종료·삭제) 발생 시 해당 키 즉시 무효화
  (기존 `HospitalModel::clearActiveListCache` 패턴 재사용).
- 전체 HTML 페이지 캐시(풀 페이지 캐시) 또는 데이터 캐시 중 데이터 캐시 우선.

---

## 4. 노출 콘텐츠 → schema.org 매핑 (4종 전부)

GEO의 핵심은 **구조화 데이터로 사실을 기계 판독 가능하게** 만드는 것이다. 실제 모델 필드 기준 매핑:

> ⚠️ **구현 주의(#145):** `JsonLdBuilder` 는 `JSON_UNESCAPED_UNICODE` 를 쓰지 않고 한글을 `\uXXXX`(ASCII)로
> 인코딩한다. 이는 인라인 JSON-LD 의 이식성 베스트프랙티스이며(운영 출력은 raw UTF-8 로도 유효),
> 동시에 **PHPUnit 테스트 하네스가 `DOMParser::getBody()`(mb_encode_numericentity)로 응답 본문의 비ASCII를
> HTML 엔티티(`&#44053;`)로 변환**하는 아티팩트를 우회한다(운영에는 이런 전역 변환이 없음 — 테스트에서
> 원문 검증은 `response()->getBody()` 사용). `</script>` 탈출 방지는 `JSON_HEX_TAG`.
> 또한 `Config\View::$saveData=true` 로 공유 렌더러가 직전 요청의 `jsonLd` 를 유지(워커 모드 누출)하므로,
> `BaseWebController::render` 가 `jsonLd` 기본값을 항상 `[]` 로 명시한다. (가이드 Article #146 도 동일 규칙 준수)

### 4.1 이벤트/캠페인 — `Offer` + `MedicalProcedure`
소스: `campaigns` 테이블 / `CampaignModel`, `EventService::detail()`

| 필드(소스) | schema.org 속성 |
|------------|----------------|
| `ad_title` | `name` |
| `ad_detail_info` | `description` |
| `discount_cost` / `general_cost` | `Offer.price` / `priceSpecification` (정상가·할인가) |
| `ad_start_date`·`ad_end_date` | `Offer.availabilityStarts`·`availabilityEnds` |
| `hospital_id` → 병원 | `Offer.seller` (= `MedicalClinic`) |
| `category` | `MedicalProcedure.procedureType` 또는 카테고리 라벨 |
| `deliberation_code` | 본문 노출(의료광고 심의번호) — §8 규제 대응 |

### 4.2 병원 — `MedicalClinic` / `LocalBusiness`
소스: `hospitals` 테이블 / `HospitalModel`

| 필드 | schema.org 속성 |
|------|----------------|
| `name` | `name` |
| `address` | `address` (PostalAddress) |
| `phone` | `telephone` |
| `type` | `medicalSpecialty` (1/2/3 코드 → 라벨 매핑 필요) |
| (집계) 후기 별점 | `aggregateRating` |

### 4.3 후기/리뷰 — `Review` + `AggregateRating`
소스: `boards` 테이블 / `BoardModel` (`type` 1=이벤트·2=병원·3=접수, `rate_sum`, `ai_trust_score`)

| 필드 | schema.org 속성 |
|------|----------------|
| `subject`·`contents` | `Review.reviewBody` |
| `rate_sum` | `Review.reviewRating` |
| `user_name` | `Review.author` (개인정보 — 닉네임/마스킹) |
| `target_id`+`type` | `itemReviewed` (이벤트/병원) |
| `ai_trust_score` | 내부 노출 제어용(저신뢰 후기 색인 제외 판단) |

> **신뢰도 활용:** `ai_status`/`ai_trust_score`(이슈 #102 AI 신뢰성 분석)를 이용해 **저신뢰·신고 후기는 색인 제외**.
> GEO에서 신뢰 신호는 인용 채택률에 직결된다.

### 4.4 콘텐츠/가이드 — `Article` + `FAQPage`
소스: **신규 테이블 필요** (`guides` — 시술 정보성 아티클). GEO에서 가장 인용되기 좋은 자산.

| 항목 | schema.org |
|------|-----------|
| 제목·본문 | `Article` (`headline`, `articleBody`, `author`, `datePublished`) |
| Q&A 섹션 | `FAQPage` / `Question`·`Answer` |
| 시술 설명 | `MedicalWebPage` + `MedicalProcedure` |

---

## 5. 단계별 로드맵

### Phase 1 — SEO 기반 (선행 · 필수)

1. **공개 SSR 레이어 신설** (§3) — 이벤트/병원/후기 목록·상세 페이지
2. **메타태그 표준화** (`app/Libraries/Seo/MetaTagBuilder`)
   - `<title>`, `meta description`, `canonical`, `robots`
   - Open Graph(`og:title/description/image/type/url`), Twitter Card
3. **sitemap.xml 동적 생성** — 활성 이벤트/병원/후기/가이드 URL, `lastmod` 포함
4. **robots.txt 갱신** — `Sitemap: https://{도메인}/sitemap.xml` 추가
5. **운영 도메인·HTTPS** → `app.baseURL` 환경변수화 (현재 localhost) — ✅ **환경변수 추상화로 확정**(§0). 도메인 값만 추후 주입
6. **성능(Core Web Vitals)** — 이미지 최적화/지연로딩, 서버 응답 캐시(§3.3), 모바일 우선
7. **한국 시장 대응** — Naver(웹마스터도구·사이트맵 제출), Google Search Console 등록

### Phase 2 — GEO 레이어 (Phase 1 위에 적층)

1. **JSON-LD 구조화 데이터** (`app/Libraries/Seo/JsonLdBuilder`) — §4 매핑 전 타입 출력
2. **`llms.txt` 발행** (`public/llms.txt`) — 사이트 개요, 핵심 카테고리, 주요 URL 목록
   (AI 크롤러용 사이트맵 격 요약. robots.txt와 별개)
3. **인용 가능한 콘텐츠 구조화**
   - 핵심 사실을 **명료한 단문**으로 (가격·기간·시술 효과·주의사항)
   - **FAQ 섹션**(FAQPage 스키마) — "쌍꺼풀 수술 비용은?" 형식 질문-답변
   - 통계·수치·날짜를 본문에 명시(LLM이 인용하기 좋은 형태)
4. **엔티티 명료화** — 병원/시술/카테고리를 일관된 명칭+구조화데이터로 묶어 지식그래프 신호 강화
5. **신뢰 신호** — 의료광고 심의번호, 작성일/갱신일, 출처, 후기 신뢰도 필터(§4.3)
6. **가이드 콘텐츠 자산화** — 시술별 `Article`/`MedicalWebPage` 신규 작성(GEO 인용률 최상위 자산)

---

## 6. AI 크롤러 접근 정책 (robots.txt)

GEO를 원한다면 주요 AI 크롤러를 **명시적으로 허용**해야 한다. 차단 시 인용 대상에서 제외된다.

```
User-agent: *
Allow: /

# AI 검색/학습 크롤러 (노출 원하면 허용)
User-agent: GPTBot            # OpenAI
Allow: /
User-agent: OAI-SearchBot     # ChatGPT Search
Allow: /
User-agent: PerplexityBot
Allow: /
User-agent: Google-Extended   # Gemini/AI Overviews
Allow: /
User-agent: ClaudeBot
Allow: /

Sitemap: https://{운영도메인}/sitemap.xml
```

> ✅ **결정(§0): 전체 허용.** 검색 인용 크롤러와 LLM 학습 크롤러를 모두 허용한다.
> (선별 차단이 필요해지면 학습 크롤러(GPTBot 등)만 `Disallow`로 전환할 수 있으나, 현재는 노출 극대화 우선.)
> 실제 적용본은 `public/robots.txt` 참조 — 이슈 #137 골격에서 이미 반영됨.

---

## 7. 측정 지표

| 단계 | 지표 | 도구 |
|------|------|------|
| SEO | 색인된 페이지 수, 노출수, 클릭수, 평균순위 | Google Search Console, Naver 웹마스터 |
| SEO | Core Web Vitals(LCP/INP/CLS) | PageSpeed Insights |
| GEO | AI 검색 인용·언급 빈도 | 주요 질의를 ChatGPT Search/Perplexity에 주기 점검(수동·자동) |
| GEO | AI 크롤러 유입 | 서버 로그 User-agent 분석(GPTBot·PerplexityBot 등) |
| 공통 | 구조화 데이터 유효성 | Google Rich Results Test, Schema Markup Validator |

### 7.1 배포 후 측정·검증 체크리스트 (이슈 #148)

> #148 은 **배포 시점까지 열어 둔다**(코드 구현이 아닌 운영 활동). 운영 도메인·HTTPS 확정 후 아래 순서로 수행한다.
> 현재 구현된 크롤러 진입점: `GET /robots.txt` · `GET /sitemap.xml` · `GET /llms.txt` (모두 `base_url()` 기준 절대 URL).

**A. 배포 전 확인**
- [ ] `.env` 의 `app.baseURL` 을 운영 도메인(HTTPS)으로 설정 → canonical·OG·sitemap·robots·llms 절대 URL 자동 반영
- [ ] `https://{도메인}/robots.txt` · `/sitemap.xml` · `/llms.txt` 200 응답·내용 확인
- [ ] 미심의·저신뢰 콘텐츠 `noindex` 동작 확인(신고·의심 후기 상세)
- [ ] 의료광고법 검토(§8) 완료 — 가격 표기·후기 공개 범위

**B. 검색엔진 등록**
- [ ] Google Search Console 속성 등록 → `sitemap.xml` 제출
- [ ] Naver 서치어드바이저 사이트 등록 → 사이트맵 제출·수집 요청
- [ ] (선택) Bing Webmaster Tools 등록

**C. 구조화 데이터 검증** — [Rich Results Test](https://search.google.com/test/rich-results) / [Schema Validator](https://validator.schema.org)
- [ ] 이벤트 상세(`/events/{id}`) — `Offer` 오류 0
- [ ] 병원 상세(`/hospitals/{id}`) — `MedicalClinic`/`AggregateRating` 오류 0
- [ ] 후기 상세(`/reviews/{id}`) — `Review` 오류 0
- [ ] 가이드 상세(`/guides/{slug}`) — `MedicalWebPage`+`FAQPage` 오류 0

**D. 성능(Core Web Vitals)**
- [ ] 주요 페이지 PageSpeed Insights LCP/INP/CLS 확인 → 이미지 지연로딩·캐시 점검

**E. AI 크롤러·GEO 모니터링**
- [ ] 서버 로그 User-agent 로 AI 크롤러 유입 확인: `GPTBot`·`OAI-SearchBot`·`ChatGPT-User`·`PerplexityBot`·`Google-Extended`·`ClaudeBot`
- [ ] 대표 질의(예: "강남 쌍꺼풀 이벤트", "쌍꺼풀 수술 비용")를 ChatGPT Search·Perplexity·Google AI Overviews 에 넣어 **인용·언급 여부** 주기 점검
- [ ] GSC 색인 페이지 수·노출·클릭·평균순위 추이 기록(월 단위)

---

## 8. 리스크 — 의료광고법 (성형 도메인 특수성) ⚠️

성형/의료 콘텐츠를 **공개 웹에 노출**하는 순간 의료법상 **의료광고 심의 대상**이 된다.
모바일 앱(폐쇄형)과 공개 웹은 규제 강도가 다를 수 있으므로 반드시 사전 검토한다.

- **심의번호 표기:** `deliberation_code`(캠페인)·`ComplianceCheckModel`(이슈 #71 사전검사) 활용 → 공개 페이지에 심의번호 노출
- **가격 표시 규제:** 할인가·이벤트가 직접 노출은 의료법상 제한될 수 있음 → 표기 방식 법무 검토
- **후기 노출:** 환자 후기의 공개는 별도 규제·개인정보 이슈 → 작성자 마스킹, 동의 범위 확인
- **검수완료 이벤트만 노출 (구현됨):** 공개 노출 이벤트는 **검수가 완료(`review_status='approved'`)되어 의료광고법 저촉이 없는** 건으로 한정한다. 검수 미완료(`pending`·`rejected`)는 `status='active'`라도 노출 금지. 캠페인 게재 상태(`status`)와 검수(`review_status`)는 독립 축이므로 **둘 다** 충족해야 한다. → `CampaignModel::applyConsumerFilters()`에 강제(모바일 앱 API·공개 웹 SSR 공유, 이슈 #137)
- **미심의 콘텐츠 색인 차단:** 심의 미통과/대기 콘텐츠는 `noindex` 처리 — 색인 허용 전제(§0)의 **기본 방어선**

> 이 리스크는 기술이 아닌 **사업·법무 결정**이다. 색인은 허용(§0)하되, 미심의 콘텐츠 `noindex`·가격 표기·후기 공개는 법무 검토를 병행한다.

---

## 9. 후속 이슈 분할 제안

`feature/* → dev → main` 워크플로우(CLAUDE.md)에 맞춰 다음 단위로 분할 제안한다.

아래 항목을 #137의 **서브이슈**로 등록한다.

| 순서 | 이슈(제안) | 범위 | 의존 | 상태 |
|------|-----------|------|------|------|
| 1 | 공개 SSR 웹 레이어 골격 | `Controllers/Web/*` + `Views/web/layout` + 라우트 + 이벤트 목록/상세 | — | ✅ 완료 (커밋 `7c82f0f`, AI 크롤러 robots 반영) |
| 2 | SEO 메타 + sitemap + robots | `MetaTagBuilder`, `SitemapController`, robots 보강, baseURL 환경변수 명문화 | 1 | ✅ 완료 (#143) — 동적 sitemap.xml·robots.txt(절대 URL), MetaTagBuilder 라이브러리화 |
| 3 | 병원·후기 공개 페이지 | 병원/후기 SSR + 후기 신뢰도 필터(noindex) | 1 | ✅ 완료 (#144) — 병원/후기 SSR, 작성자 마스킹, 신고·저신뢰 noindex, sitemap 확장 |
| 4 | JSON-LD 구조화 데이터 | `JsonLdBuilder` — Offer/MedicalClinic/Review/Article | 2,3 | ✅ 완료 (#145) — 이벤트 Offer·병원 MedicalClinic·후기 Review, web/partials/json_ld 주입 (가이드 Article 은 #146) |
| 5 | GEO 콘텐츠 자산 — 가이드 | `guides` 테이블·CRUD(Admin)·공개 페이지·Article/FAQ 스키마 | 4 | ✅ 완료 (#146) — guides 마이그레이션·Admin CRUD(Tiptap·FAQ)·공개 슬러그 페이지·MedicalWebPage+FAQPage JSON-LD·sitemap |
| 6 | llms.txt + AI 크롤러 정책 | `public/llms.txt`, robots AI 크롤러 섹션 점검 | 2 | ✅ 완료 (#147) — 동적 llms.txt(사이트 요약·주요 페이지·발행 가이드·카테고리, 절대 URL), robots AI 크롤러 확인 |
| 7 | 측정·검증 | GSC/Naver 등록, Rich Results 검증, 크롤러 로그 분석 | 전체 | ⏸ 배포 시점까지 보류(#148) — 운영 활동, 체크리스트 §7.1 참조 |

선행 결정사항(비기술) — **모두 확정**(§0):
1. ✅ **운영 도메인·HTTPS** — 환경변수로 추상화 (도메인 값만 추후 주입)
2. ✅ **AI 크롤러 정책** — 전체 허용 (검색 인용·학습 모두)
3. ⚠️ **의료광고법 검토** (§8) — 색인 허용 전제이되 미심의 콘텐츠 `noindex` 기본 방어선. 가격·후기 공개 표기는 법무 병행

---

## 부록 A. 참고 — 기존 재사용 자산

- `app/Services/EventService`(`detail()`/목록) — 이벤트 데이터 진입점
- `app/Models/HospitalModel`·`BoardModel`·`CampaignModel` — 매핑 소스
- `app/Models/ComplianceCheckModel`(#71) — 의료광고 사전검사
- `HospitalModel::clearActiveListCache` — 캐시 무효화 패턴 참고
- Redis 캐시·큐 인프라 — 이미 도입됨(로그 파이프라인 #115)
