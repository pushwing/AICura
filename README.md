# AI Cura

AI 기반 성형·토탈 광고 솔루션 — 앱/웹용 REST API + 관리자 툴

---

## 개요

| 항목 | 내용 |
|------|------|
| **스택** | PHP 8.4+ · CodeIgniter 4 · FrankenPHP |
| **Admin** | CI4 MVC · 세션 인증 · DB 직접 접근 |
| **API** | REST · JWT 인증 · JSON 응답 |
| **문서** | Swagger UI (`/api/docs`) |

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
```

### DB 생성

```bash
# MySQL root 접속 후 실행 (dev-docs/db-setup.sql 참고)
mysql -u root -p < dev-docs/db-setup.sql
```

생성되는 계정: `aicura / Aicura@2026!Dev` — `.env`에 동일하게 설정합니다.

### 마이그레이션 및 서버 실행

```bash
php spark migrate

make serve        # FrankenPHP 개발 서버 (포트 8300) — 권장
make serve-spark  # CI4 내장 서버 (포트 8300)
```

### 기본 관리자 계정

마이그레이션 후 아래 계정이 없으면 직접 INSERT합니다 (`user_type = 401`).

```
이메일:   admin@aicura.com
비밀번호: Admin@2026!
```

---

## URL 구조

| 경로 | 설명 | 인증 |
|------|------|------|
| `/admin/login` | 관리자 로그인 | 없음 |
| `/admin/*` | 관리자 패널 | 세션 |
| `/api/docs` | Swagger UI | 없음 |
| `/api/docs/spec` | OpenAPI JSON | 없음 |
| `/api/v1/auth/*` | JWT 발급·갱신 | 없음 |
| `/api/v1/*` | REST API | JWT Bearer |

---

## API 문서 (Swagger)

개발 서버 실행 후 브라우저에서 확인합니다.

```
http://localhost:8300/api/docs
```

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
│   │   ├── Admin/          — 관리자 컨트롤러 (세션 인증)
│   │   └── Api/
│   │       ├── DocsController.php   — Swagger UI & 스펙 엔드포인트
│   │       └── V1/         — REST API 컨트롤러 (JWT 인증)
│   ├── Models/             — Admin·API 공유 모델
│   ├── Filters/
│   │   ├── AdminAuthFilter.php
│   │   └── JwtAuthFilter.php
│   ├── Libraries/
│   │   └── JwtLibrary.php
│   ├── Commands/
│   │   └── SwaggerGenerate.php   — php spark swagger:generate
│   ├── Views/
│   │   ├── admin/
│   │   └── api/swagger_ui.php
│   └── Config/
│       ├── Routes.php
│       └── Filters.php
├── public/                 — 웹 루트 (index.php)
├── assets/logo/            — 브랜드 로고 SVG
├── docs/                   — 프로젝트 문서
│   ├── branding.md
│   ├── design-system.md
│   ├── ui-guide.md
│   └── architecture.md
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
php spark swagger:generate       # OpenAPI 스펙 생성
php spark routes                 # 등록된 라우트 확인
composer test                    # PHPUnit 단위·통합 테스트
composer analyse                 # PHPStan 정적 분석 (level 6)
composer check                   # PHPStan + PHPUnit 순차 실행
```

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

- Merge 방식: **Squash and merge**
- PR 머지 후 `feature/*` 브랜치 자동 삭제
- `main` · `dev` 직접 push 금지

자세한 내용: [`docs/git-workflow.md`](docs/git-workflow.md)

---

## 서버 요구사항

| 항목 | 버전 | 용도 |
|------|------|------|
| FrankenPHP | 1.12+ | 웹 서버 (PHP 8.5 내장) |
| PHP CLI | 8.4+ | composer · spark · PHPStan |
| MySQL | 8.0+ | 데이터베이스 |

**필수 PHP 확장**: `intl` `mbstring` `json` `mysqlnd` `curl` `gd` `xml` `zip`

---

## 문서

| 문서 | 경로 |
|------|------|
| 브랜딩 가이드 | `docs/branding.md` |
| 디자인 시스템 | `docs/design-system.md` |
| UI 컴포넌트 가이드 | `docs/ui-guide.md` |
| 아키텍처 설계 | `docs/architecture.md` |
| UI 쇼케이스 | `ui/components.html` |
