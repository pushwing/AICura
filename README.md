# AI Cura

AI 기반 성형·토탈 광고 솔루션 — 소비자 앱 REST API + 운영자/광고주 관리 툴 + 공개 웹(SEO/GEO)

---

## 개요

CodeIgniter 4 단일 프로젝트에 네 개의 진입면(surface)이 공존한다.

| 진입면 | 대상 | 인증 | 컨트롤러 |
|--------|------|------|----------|
| **Admin** | 운영자 | 세션 | `app/Controllers/Admin/` |
| **Portal** | 광고주·대행사 | 세션 | `app/Controllers/Portal/` |
| **API v1** | 소비자 앱(Flutter) | JWT Bearer | `app/Controllers/Api/V1/` |
| **공개 웹** | 검색·AI 크롤러 (SEO/GEO) | 없음 | `app/Controllers/Web/` |

| 항목 | 내용 |
|------|------|
| **스택** | PHP 8.4+ (CLI) / 8.5 (FrankenPHP) · CodeIgniter 4 |
| **웹 서버** | FrankenPHP(로컬 `make serve`) / 아파치 mod_php(프로덕션) |
| **API 문서** | RapiDoc (`/api/docs`) — `zircote/swagger-php` 로 스펙 생성 |
| **AI** | Groq(`llama-3.3-70b-versatile`) — 일일 보고서·리드 분석·후기 품질·카피·컴플라이언스 |
| **모바일 앱** | Flutter (`app-mobile/`) |

---

## 시작하기

### 설치

```bash
git clone https://github.com/pushwing/AICura.git
cd AICura
composer install
cp env .env
```

### 환경 설정

`.env` 파일을 열어 아래 항목을 수정합니다.

```ini
CI_ENVIRONMENT = development

app.baseURL = 'http://localhost:8300/'

database.default.hostname = localhost
database.default.database = aicura
database.default.username = root
database.default.password =
database.default.DBDriver = MySQLi

JWT_SECRET = your-secret-key-here   # 32자 이상 랜덤 문자열 권장

# AI 일일 보고서 (이슈 #65) — 미설정 시 보고서 생성만 비활성, 그 외 기능 정상
AI_PROVIDER = groq                     # 공급자 선택 (기본 groq)
GROQ_API_KEY = your-groq-api-key
GROQ_MODEL = llama-3.3-70b-versatile   # 선택 (기본값 동일)
```

### DB 생성

MySQL에 접속해 데이터베이스와 계정을 생성합니다.

```sql
CREATE DATABASE aicura CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
CREATE USER 'aicura'@'localhost' IDENTIFIED BY 'Aicura@2026!Dev';
GRANT ALL PRIVILEGES ON aicura.* TO 'aicura'@'localhost';
FLUSH PRIVILEGES;
```

생성한 계정(`aicura / Aicura@2026!Dev`)을 위 `.env`의 `database.default.*`에 동일하게 설정합니다.
로컬에서 root 계정을 그대로 쓴다면 이 단계는 생략하고 데이터베이스만 만들어도 됩니다.

### 마이그레이션 및 서버 실행

```bash
php spark migrate

make serve        # FrankenPHP 개발 서버 (포트 8300) — 권장
make serve-spark  # CI4 내장 서버 (포트 8300)
```

### 기본 관리자 계정

마이그레이션 후 시더로 기본 관리자 계정(`user_type = 401`)을 생성한다.

```bash
php spark db:seed AdminUserSeeder
```

| 항목 | 값 |
|------|-----|
| 이메일 | `admin@aicura.com` |
| 비밀번호 | `Admin@2026!` |
| user_type | `401` (관리자) |

- 재실행 안전 — 이미 있으면 건너뛴다(비밀번호 덮어쓰기 없음).
- **운영 환경에서는 최초 로그인 후 반드시 비밀번호를 변경**한다.

---

## URL 구조

| 경로 | 설명 | 인증 |
|------|------|------|
| `/admin/login` | 운영자 로그인 | 없음 |
| `/admin/*` | 운영자 패널 | 세션 |
| `/portal/login` | 광고주·대행사 로그인 | 없음 |
| `/portal/*` | 광고주·대행사 포털 | 세션(portal_auth) |
| `/api/docs` · `/api/docs/spec` | RapiDoc UI · OpenAPI JSON | 없음 |
| `/api/v1/auth/*` | JWT 발급·갱신·소셜 로그인 | 없음 |
| `/api/v1/campaigns` · `/hospitals` · `/boards` 등 조회 | 소비자 앱 열람 | 선택적(jwt_optional) |
| `/api/v1/*` (찜·신청·예약 등) | 소비자 앱 쓰기 | JWT Bearer |
| `/events` · `/hospitals` · `/reviews` · `/guides` | 공개 랜딩 페이지 (SEO/GEO) | 없음 |
| `/sitemap.xml` · `/robots.txt` · `/llms.txt` | 크롤러 진입점(동적 생성) | 없음 |

---

## API 문서 (RapiDoc)

OpenAPI 스펙은 `zircote/swagger-php`로 생성하고, **RapiDoc**이 이를 렌더링합니다.
개발 서버 실행 후 브라우저에서 확인합니다.

```
http://localhost:8300/api/docs
```

- `/api/docs` — RapiDoc UI (`app/Views/api/rapidoc.php`)
- `/api/docs/spec` — OpenAPI JSON (개발: 동적 생성 / 운영: `public/swagger.json` 서빙)

### 정적 스펙 파일 생성 (운영 배포 시)

```bash
php spark swagger:generate
# → public/swagger.json 생성
```

운영 환경에서는 `public/swagger.json` 정적 파일을 서빙합니다.  
배포 스크립트에 `php spark swagger:generate`를 포함하세요.

### 새 엔드포인트 문서 추가

`app/Controllers/Api/V1/` 컨트롤러 메서드에 PHP 어트리뷰트를 추가하면 자동 반영됩니다.

```php
use OpenApi\Attributes as OA;

#[OA\Get(
    path: '/campaigns',
    summary: '캠페인 목록 조회',
    security: [['bearerAuth' => []]],
    tags: ['Campaigns'],
    responses: [
        new OA\Response(response: 200, description: '성공'),
    ]
)]
public function index(): \CodeIgniter\HTTP\ResponseInterface
{
    // ...
}
```

---

## 인증

### Admin (세션)

```
POST /admin/login  →  세션 발급
```

### API (JWT)

```
POST /api/v1/auth/login
{
  "email": "user@aicura.io",
  "password": "secret"
}

→ { "access_token": "...", "refresh_token": "...", "expires_in": 3600 }
```

이후 요청 헤더에 포함합니다.

```
Authorization: Bearer <access_token>
```

Access Token 만료(1시간) 시 Refresh Token으로 갱신합니다.

```
POST /api/v1/auth/refresh
{ "refresh_token": "..." }
```

---

## 디렉토리 구조

```
AICura/
├── app/
│   ├── Controllers/
│   │   ├── Admin/          — 운영자 컨트롤러 (세션 인증)
│   │   ├── Portal/         — 광고주·대행사 포털 (세션 인증)
│   │   ├── Web/            — 공개 웹 페이지·크롤러 진입점 (SEO/GEO)
│   │   └── Api/
│   │       ├── DocsController.php   — RapiDoc & 스펙 엔드포인트
│   │       └── V1/         — REST API 컨트롤러 (JWT 인증)
│   ├── Models/             — Admin·Portal·API 공유 모델
│   ├── Services/           — 비즈니스 로직 (AI 보고서·리드·후기·헬스포인트 등)
│   ├── Enums/              — Backed Enum (AppLogEvent, PointReason)
│   ├── Exceptions/         — 도메인 예외
│   ├── Filters/            — AdminAuth / PortalAuth / JwtAuth / OptionalJwtAuth / RateLimit
│   ├── Libraries/
│   │   ├── JwtLibrary.php
│   │   ├── MarkdownRenderer.php
│   │   └── Ai/             — AiClientInterface · GroqClient · AiClientFactory
│   ├── Commands/           — swagger:generate · reports:generate-ai · logs:* · leads:analyze · reviews:analyze
│   ├── Database/
│   │   ├── Migrations/
│   │   └── Seeds/          — AdminUserSeeder 등
│   ├── Views/              — admin / portal / web / reports / api
│   └── Config/             — Routes.php · Filters.php 등
├── app-mobile/             — Flutter 소비자 앱
├── public/                 — 웹 루트 (index.php)
├── assets/logo/            — 브랜드 로고 SVG
├── docs/                   — 프로젝트 문서 (아래 문서 표 참고)
├── .githooks/              — pre-commit (php-cs-fixer 자동 적용)
└── ui/                     — UI 컴포넌트 (aicura.css, components.html)
```

---

## Spark 커맨드

```bash
make serve                       # FrankenPHP 개발 서버 (포트 8300) — 권장
make serve-spark                 # CI4 내장 개발 서버 (포트 8300)
php spark migrate                # DB 마이그레이션 (default 그룹)
php spark migrate --group tests  # 테스트 DB 마이그레이션
php spark migrate:rollback       # 마이그레이션 롤백
php spark routes                 # 등록된 라우트 확인
php spark swagger:generate       # OpenAPI 스펙 생성 (public/swagger.json)

# AI·큐 배치 (crontab/데몬으로 주기 실행)
php spark reports:generate-ai    # Groq AI 일일 매출·소진 보고서 생성 (이슈 #65)
php spark leads:analyze          # 대기 중 이벤트 신청 AI 분석 (전환점수·요약·다음액션)
php spark reviews:analyze        # 대기 중 후기 AI 분석 (감성·신뢰점수·플래그)
php spark logs:consume           # 로그 큐 소비 → app_logs 적재 (이슈 #115)
php spark logs:aggregate         # app_logs 시간별 집계 → hourly_event_stats (이슈 #120)

# 품질 검증 (composer)
composer test                    # PHPUnit 단위·통합 테스트
composer analyse                 # PHPStan 정적 분석 (level 6)
composer check                   # PHPStan + PHPUnit 순차 실행
composer cs                      # PHP CS Fixer 검사 (dry-run)
composer cs:fix                  # PHP CS Fixer 자동 수정 (커밋 시 pre-commit 훅으로도 적용)
```

---

## AI 일일 보고서 (이슈 #65)

Groq AI(`llama-3.3-70b-versatile`)로 매일 1회 **매출 현황 보고서**와 **소진 보고서**를 자동 생성한다.

- **매출 보고서**: 전일 1일치 + 당월 누계 충전/소진/환불/잔액을 분석해 현황·특이점을 문서화
- **소진 보고서**: 충전금의 **5% 이하**만 남은 광고주(병원)를 추려 재충전 권고 등을 문서화
- **노출**: 리포트 화면 상단에 종류별 최신 보고서 2건 + `더보기`(이전 목록), 보고서는 **새창**으로 표시
- **수동 생성**: 운영자 화면(`/admin/reports`)의 `지금 생성` 버튼으로 즉시 생성 가능

### 스코프 (노출 주체별 분리)

`ai_reports.scope_type` / `scope_id`로 주체별 보고서를 분리 저장한다. 야간 배치가 아래를 모두 생성한다.

| scope_type | 대상 | 노출 위치 |
|-----------|------|-----------|
| `global`   | 전체(운영자 관점) | `/admin/reports` |
| `hospital` | 광고주 단일 병원 (자기 데이터만) | `/portal/reports` (광고주 로그인) |
| `agency`   | 대행사 소속 광고주 합산 | `/portal/reports` (대행사 로그인) |

- 포털(광고주·대행사)에는 **수동 생성 버튼이 없으며**, 본인 스코프 보고서만 조회·열람 가능(권한 검증)

### 정기 실행 (crontab)

`.env`에 `GROQ_API_KEY`를 설정한 뒤, 서버 crontab에 매일 1회 등록한다.

```cron
# 매일 06:00 KST 보고서 생성
0 6 * * * cd /path/to/AICura && php spark reports:generate-ai >> writable/logs/ai-report.log 2>&1
```

특정 날짜 기준으로 생성하려면 `--date` 옵션을 사용한다.

```bash
php spark reports:generate-ai --date=2026-06-25
```

### 다른 AI로 교체

AI 호출은 `AiClientInterface`로 추상화되어 있어 호출부 수정 없이 공급자를 교체할 수 있다.

```
app/Libraries/Ai/
  ├─ AiClientInterface.php   # complete(system, user): string
  ├─ GroqClient.php          # 기본 구현 (Groq)
  └─ AiClientFactory.php     # env('AI_PROVIDER')로 구현체 선택
```

1. `AiClientInterface`를 구현하는 새 클라이언트 클래스 추가 (예: `OpenAiClient`)
2. `AiClientFactory::make()`의 `match`에 한 줄 등록
3. `.env`의 `AI_PROVIDER` 값 변경

프롬프트·도메인 로직은 `AiReportService`에 있으므로 공급자와 무관하게 그대로 재사용된다.

### 처리 흐름

```
서버 crontab (야간 1회)  ──┐
관리자 '지금 생성' 버튼     ──┴─▶  AiReportService
                                    ├─ ReportModel: 스코프별 집계 수집
                                    ├─ AiClient(Groq): 마크다운 보고서 생성
                                    └─ AiReportModel: ai_reports 저장 (scope 포함)
                                                          │
리포트 화면 ◀── 최신 매출1 + 소진1 카드 + 더보기 ─────────┘
   └─ 전체 보기 → 새창 상세 (MarkdownRenderer로 서버 HTML 변환)
```

### 구성 요소

| 경로 | 역할 |
|------|------|
| `app/Models/AiReportModel.php` | 보고서 저장·스코프 한정 조회 (`latestByType`/`historyByType`/`findScoped`) |
| `app/Models/ReportModel.php` | 스코프별 집계 (전일·당월 누계 매출 / 잔액 5% 이하 광고주) |
| `app/Libraries/Ai/` | `AiClientInterface` · `GroqClient` · `AiClientFactory` (공급자 교체 추상화) |
| `app/Services/AiReportService.php` | 집계→AI 생성→저장 오케스트레이션 + 프롬프트 |
| `app/Services/ReportScope.php` | 생성 범위 값 객체 (global / hospital / agency) |
| `app/Libraries/MarkdownRenderer.php` | 마크다운→안전 HTML 변환 (서버 사이드) |
| `app/Commands/ReportGenerateAi.php` | `reports:generate-ai` 배치 커맨드 |
| `app/Views/reports/ai_show.php` | 새창 상세 (admin·portal 공용) |
| `app/Views/reports/_ai_section.php` | 리포트 화면 AI 카드 섹션 파셜 (공용) |

### 마크다운 렌더링

AI가 생성한 마크다운 본문은 **서버에서** `league/commonmark`(GFM, 표 지원)로 HTML 변환한다.
외부 CDN(marked/DOMPurify) 의존을 제거해 네트워크 환경과 무관하게 렌더된다.

- 보안 설정: `html_input=escape`(본문 내 원시 HTML 이스케이프) · `allow_unsafe_links=false`(`javascript:` 등 위험 링크 차단)으로 XSS 방어
- 변환 진입점: `App\Libraries\MarkdownRenderer::toSafeHtml()`

---

## 앱 로그 수집·집계 (이슈 #115·#120)

소비자 앱의 액션 로그를 큐로 비동기 수집하고, 시간 단위로 집계해 어드민에서 추이를 본다.

```
앱 액션 → POST /api/v1/logs → Redis 큐 → logs:consume → ① raw 파일 ② app_logs
                                                              │
                                          logs:aggregate (매시) → hourly_event_stats
                                                              │
                                   /admin/reports/app-logs (시간별·일별 Chart.js)
```

- **수집 이벤트**: `event_list_view` · `event_detail_view` · `apply_form_view` · `apply_submit` · `event_search` · `event_like` · `hospital_detail_view` · `app_open` (`App\Enums\AppLogEvent`)
- **집계 단위**: `(event, campaign_id)` × 1시간 버킷. 멱등 upsert이므로 같은 시각을 재집계해도 누적되지 않는다.
- **조회**: 어드민 사이드바 *앱 로그 통계* → 시간별(직전 1시간까지 반영)·일별(최근 14일) 토글

### 정기 실행 (crontab)

서버 crontab에 **두 커맨드**를 등록한다. 소비는 1분마다, 집계는 매시 5분에 직전 1시간을 처리한다.

```cron
# 로그 큐 소비 → app_logs 적재 (1분마다)
* * * * * cd /path/to/AICura && php spark logs:consume >> writable/logs/log-consume.log 2>&1

# 시간별 집계 → hourly_event_stats (매시 5분, 직전 1시간)
5 * * * * cd /path/to/AICura && php spark logs:aggregate >> writable/logs/log-aggregate.log 2>&1
```

- `logs:consume`는 상시 데몬(`--daemon`, systemd/supervisor)으로도 운용 가능하다.
- 특정 시각 재집계: `php spark logs:aggregate --date=2026-06-30 --hour=14`
- 하루 전체 백필: `php spark logs:aggregate --date=2026-06-30` (`--hour` 생략 시 0~23시 전체, `--backfill` 명시도 동일)

> Redis 미연결(로컬·CI) 시 수집은 원시 파일로 폴백되며, 집계는 `app_logs` 기준으로 동작한다.

---

## Git 워크플로우

```
feature/* → PR → dev → PR → main
```

```bash
# 기능 개발 시작
git checkout dev && git pull origin dev
git checkout -b feature/기능명

# 개발 후 PR
git push origin feature/기능명
gh pr create --base dev --head feature/기능명

# 배포 (dev → main)
gh pr create --base main --head dev --title "release: YYYY-MM-DD"
```

- Merge 방식: `feature/* → dev` 는 **Squash and merge**, `dev → main` 배포는 **Merge commit** (Squash 금지 — 조상 관계 유지)
- PR 머지 후 `feature/*` 브랜치 자동 삭제
- `main` · `dev` 직접 push 금지
- 커밋 시 `.githooks/pre-commit` 이 스테이징된 PHP 파일에 **php-cs-fixer(CI4 표준)** 를 자동 적용한다 (`composer install` 이 훅 경로를 등록)

자세한 내용: [`docs/git-workflow.md`](docs/git-workflow.md)

---

## CI (GitHub Actions)

`dev` · `main` 브랜치로의 **push** 와 **PR** 마다 자동 검증이 실행된다.
정의 파일은 [`.github/workflows/ci.yml`](.github/workflows/ci.yml) 단일 파일이며, 백엔드·앱 두 잡으로 병렬 실행된다.

| 항목 | 내용 |
|------|------|
| **트리거** | `dev` · `main` 으로의 push / pull_request |
| **동시성 제어** | 같은 ref 에 새 푸시가 오면 진행 중 실행 자동 취소 (`cancel-in-progress`) |

### Backend 잡 (PHP 8.5 · PHPStan · PHPUnit)

`mysql:8.0` 서비스 컨테이너를 띄운 뒤 아래 순서로 검증한다.

1. **PHP 8.5** 설치 (`mbstring · intl · mysqli · curl · dom · xml · tokenizer`, 커버리지 드라이버 `pcov`)
2. Composer 캐시 복원 후 `composer install`
3. `env` → `.env` 복사 + CI용 DB·JWT 값 주입
4. `writable/` 하위 디렉토리 생성 (git 미추적 — CI4 `WRITEPATH` 보장)
5. **정적 분석**: `composer analyse` (PHPStan level 6)
6. MySQL 헬스 체크 대기 → 테스트 호스트 `localhost` → `127.0.0.1` 보정 (MySQLi TCP 강제)
7. **테스트**: `composer test` (PHPUnit 단위·DB 통합)

> CI DB 계정은 워크플로우 내부 전용(`aicura / Aicura@2026!Dev`)이며 운영 시크릿과 무관하다.

### App 잡 (Flutter · analyze · test)

`app-mobile/` 디렉토리에서 실행된다.

1. Flutter **stable** 채널 설치 (pub 캐시 사용)
2. `flutter pub get`
3. **포맷 검사**: `dart format --set-exit-if-changed lib test`
4. **정적 분석**: `flutter analyze`
5. **테스트**: `flutter test`

### 로컬에서 동일하게 검증

푸시 전에 CI와 같은 검증을 로컬에서 미리 돌릴 수 있다.

```bash
composer check                 # PHPStan + PHPUnit (백엔드)

cd app-mobile
dart format --output=none --set-exit-if-changed lib test
flutter analyze && flutter test
```

---

## CD (배포)

`main` 브랜치에 push(= `dev → main` PR 머지)되면 프로덕션 서버로 **SSH 자동 배포**가 실행된다.
정의 파일은 [`.github/workflows/deploy.yml`](.github/workflows/deploy.yml).

| 항목 | 내용 |
|------|------|
| **트리거** | `main` push + `workflow_dispatch`(수동 실행·롤백) |
| **동시성 제어** | `deploy-production` 그룹으로 배포 동시 실행 1개만 (`cancel-in-progress: false`) |
| **대상** | Ubuntu + mod_php 아파치 단일 서버 |

### 배포 절차 (서버에서 SSH로 실행)

1. `git fetch --prune origin main` → `git reset --hard origin/main` (최신 main 반영)
2. `writable/` 디렉토리·권한 보정 — **composer/migrate 이전**에 생성 (없으면 `WRITEPATH` 오류)
3. `composer install --no-dev --optimize-autoloader`
4. `php spark migrate --all -f` — 출력을 검사해 예외 감지 시 배포 중단(spark 는 실패해도 exit 0)
5. `php spark cache:clear`
6. `sudo -n systemctl reload apache2` — OPcache 갱신(무중단)

### 필요한 GitHub Secrets (`production` 환경)

| 시크릿 | 용도 |
|--------|------|
| `DEPLOY_HOST` | 서버 IP/도메인 |
| `DEPLOY_USER` | SSH 계정 |
| `DEPLOY_SSH_KEY` | 배포용 개인키 (전체 내용) |
| `DEPLOY_PORT` | SSH 포트 |
| `DEPLOY_PATH` | 서버 프로젝트 경로 (예: `/var/www/aicura`) |

### 서버 사전 준비 (한 번만)

```bash
# 1) GitHub 읽기전용 deploy key — 서버 저장소 리모트는 SSH 여야 함 (HTTPS면 인증 실패)
git remote set-url origin git@github.com:pushwing/AICura.git

# 2) 프로덕션 .env 에 실제 DB 접속 정보 (없으면 migrate 시 Access denied)

# 3) 아파치 리로드용 비밀번호 없는 sudo
echo '<DEPLOY_USER> ALL=(ALL) NOPASSWD: /usr/bin/systemctl reload apache2' \
  | sudo tee /etc/sudoers.d/aicura-deploy
sudo chmod 440 /etc/sudoers.d/aicura-deploy && sudo visudo -c

# 4) writable/ 를 배포계정·아파치(www-data) 공용 그룹 + setgid 로 구성 (권장)
#    없어도 배포는 되지만(chmod best-effort), 구성해두면 소유권 충돌이 사라진다.
sudo chown -R <DEPLOY_USER>:www-data <DEPLOY_PATH>/writable
sudo chmod -R 2775 <DEPLOY_PATH>/writable   # 2xxx = setgid: 새 파일이 www-data 그룹 상속
```

> **참고**: 아파치 `DocumentRoot` 는 프로젝트 루트가 아니라 `public/` 을 가리켜야 하며,
> `writable/` 는 아파치 실행 유저(`www-data`)가 쓸 수 있어야 한다.
> PHP-FPM 구성이면 리로드 대상을 `apache2` 대신 `php8.4-fpm` 으로 바꾼다.

> **writable 소유권 주의**: 런타임에 아파치(`www-data`)가 `writable/cache`·`session` 에
> 파일을 만들면 배포 계정이 소유하지 않아 `chmod` 가 실패할 수 있다. 배포 워크플로우는
> 이 `chmod` 를 best-effort(실패 무시)로 처리하므로 배포가 중단되지는 않지만,
> 위 4) 의 setgid 구성으로 근본적으로 해소하는 것을 권장한다.

### 배포 후 — 기본 관리자 계정 생성 (최초 1회)

배포에는 마이그레이션만 포함되고 **시더는 자동 실행되지 않는다.** 최초 배포 후 관리자
계정이 없으면 서버에서 한 번 실행한다(재실행 안전 — 이미 있으면 건너뜀).

```bash
cd <DEPLOY_PATH> && php spark db:seed AdminUserSeeder
```

---

## 서버 요구사항

### 프로덕션 (아파치)

| 항목 | 버전 | 용도 |
|------|------|------|
| Apache HTTP Server | 2.4+ | 웹 서버 (mod_php) — `DocumentRoot` 는 `public/` |
| PHP (mod_php) | 8.4+ | 웹 요청 처리 (`php-mysql` 확장 포함) |
| PHP CLI | 8.4+ | composer · spark · PHPStan (배포 스크립트) |
| MySQL | 8.0+ | 데이터베이스 |

- 배포 자동화(SSH)는 위 [CD (배포)](#cd-배포) 참고 — deploy key · `.env` · NOPASSWD sudo 필요
- OPcache 갱신은 `sudo systemctl reload apache2`(무중단). PHP-FPM 구성이면 `php8.4-fpm` 리로드

### 로컬 개발

| 항목 | 버전 | 용도 |
|------|------|------|
| FrankenPHP | 1.12+ | 개발 서버 (PHP 8.5 내장) — `make serve` |
| PHP CLI | 8.4+ | composer · spark · 테스트 |
| MySQL | 8.0+ | 데이터베이스 |

> 로컬은 FrankenPHP, 프로덕션은 아파치(mod_php)로 서로 다르다. CI4 는 두 환경 모두 `public/` 을 문서 루트로 사용한다.

**필수 PHP 확장**: `intl` `mbstring` `json` `mysqlnd` `curl` `gd` `xml` `zip`

---

## 문서

| 문서 | 경로 |
|------|------|
| 아키텍처 설계 | `docs/architecture.md` |
| 기술 요구사항 정의(TRD) | `docs/TRD.md` |
| Git 워크플로우 | `docs/git-workflow.md` |
| 운영자 매뉴얼 | `docs/admin-manual.md` (`.pdf`) |
| 광고주·대행사 포털 매뉴얼 | `docs/portal-manual.md` (`.pdf`) |
| 브랜딩 가이드 | `docs/branding.md` |
| 디자인 시스템 | `docs/design-system.md` |
| UI 컴포넌트 가이드 | `docs/ui-guide.md` |
| GEO 적용 전략 (SEO→GEO) | `docs/geo-strategy.md` |
| UI 쇼케이스 | `ui/components.html` |
