# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

AI 기반 성형·토탈 광고 솔루션. CodeIgniter 4 기반 Admin + REST API 단일 프로젝트.

> 세부 규칙은 `.claude/rules/` 로 분리되어 있으며 아래 `@import` 로 항상 로드된다.
> 주제별로 수정할 때는 해당 rule 파일을 직접 편집한다.

## 언어 규칙

- 모든 응답은 반드시 한국어로 작성할 것
- 코드 주석도 한국어로 작성할 것
- 영어 응답은 절대 금지

## 기술 스택

- **언어**: PHP 8.4+ (시스템 CLI) / PHP 8.5 (FrankenPHP 내장)
- **웹 서버**: FrankenPHP v1.12 — `make serve` (포트 8300, 권장) / CI4 내장 — `make serve-spark`
- **프레임워크**: CodeIgniter 4
- **인증**: 세션(Admin) / JWT Bearer(API) — JWT는 외부 라이브러리 없이 `JwtLibrary`(HMAC-SHA256)로 직접 구현
- **API 문서**: RapiDoc (`/api/docs`) — OpenAPI 스펙은 `zircote/swagger-php` 로 생성

> **PHP 버전 구분**  
> - 웹 요청 처리: FrankenPHP 내장 PHP 8.5.7  
> - CLI (composer/spark/PHPStan/PHPUnit): 시스템 PHP 8.4.22  
> - CI (GitHub Actions `backend` 잡): PHP 8.5 (setup-php)  
> - `composer.json` 요구사항은 `^8.4` (8.5 포함)

## 로컬 환경 설정

```bash
cp env .env          # env 파일을 .env로 복사 후 아래 필수 키 설정
composer install
php spark migrate
```

`.env` 필수 키:

```env
# 앱
app.baseURL = http://localhost:8300/

# DB
database.default.hostname = localhost
database.default.database = aicura
database.default.username = root
database.default.password =

# JWT (필수 — 32자 이상 랜덤 문자열)
JWT_SECRET = your-secret-key-here

# 기능별 선택
TINYMCE_API_KEY =    # 리치 에디터 사용 시
```

## 커맨드

```bash
make serve                    # 개발 서버 — FrankenPHP (포트 8300, 권장)
make serve-spark              # 개발 서버 — CI4 내장 (포트 8300)
php spark migrate             # DB 마이그레이션
php spark swagger:generate    # OpenAPI 스펙 생성 (public/swagger.json)
php spark routes              # 라우트 목록
composer test                 # PHPUnit 단독 실행
composer analyse              # PHPStan 단독 실행
composer check                # PHPStan + PHPUnit 순차 실행
```

## 디렉토리 규칙

| 경로 | 용도 |
|------|------|
| `app/Controllers/Admin/` | 관리자 컨트롤러 (세션 인증) |
| `app/Controllers/Api/V1/` | REST API 컨트롤러 (JWT 인증) |
| `app/Models/` | Admin·API 공유 모델 |
| `app/Filters/` | AdminAuthFilter / JwtAuthFilter |
| `app/Libraries/` | JwtLibrary 등 공통 라이브러리 |
| `app/Commands/` | Spark 커스텀 커맨드 |
| `docs/` | 프로젝트 문서 |
| `assets/logo/` | 브랜드 로고 SVG |
| `ui/` | UI 컴포넌트 (aicura.css, components.html) |

## 아키텍처 핵심 패턴

### JWT 인증 흐름

`JwtAuthFilter`가 토큰을 검증한 뒤 `Auth::setUserId()`로 사용자 ID를 정적 홀더에 저장하고, 컨트롤러는 `$this->authUserId()`(= `Auth::userId()`)로 꺼내 쓴다. 별도 의존성 주입 없이 요청 컨텍스트 안에서만 유효하다.

```php
// JwtAuthFilter → Auth 홀더에 저장
Auth::setUserId((int) $payload['sub']);

// BaseApiController 상속 컨트롤러에서 사용
$userId = $this->authUserId();
```

### Admin 뷰 렌더링

`BaseAdminController::render()`는 `$viewData`(세션의 `authUser` 포함)를 자동으로 병합한다. CI4 기본 `view()` 함수를 직접 호출하면 `authUser`가 누락되므로 반드시 `$this->render()`를 사용한다.

```php
// ✅ 올바른 방식
return $this->render('admin/campaigns/index', ['campaigns' => $campaigns]);

// ❌ 금지 — authUser 등 공통 데이터 누락
return view('admin/campaigns/index', ['campaigns' => $campaigns]);
```

## 세부 규칙 (`.claude/rules/`)

주제별 상세 규칙은 아래 파일에 있으며 `@import` 로 항상 함께 로드된다.

| 파일 | 내용 |
|------|------|
| [코드 스타일·네이밍·아키텍처](.claude/rules/code-style.md) | 코딩 규칙, PHP/DB 네이밍, 모던 스타일(DTO·Enum), 레이어 책임, 도메인 예외 |
| [PHP 절대 금지 (보안·품질)](.claude/rules/security.md) | 보안·코드품질·PHP 함정·CI4 한정 금지 목록 |
| [API 설계](.claude/rules/api-design.md) | 응답 포맷, 에러 코드, REST URI, HTTP 상태, OpenAPI, 부하 분산, 로그 파이프라인 |
| [Admin 뷰 개발](.claude/rules/admin-views.md) | AG Grid, Tiptap, Chart.js, PhpSpreadsheet |
| [정적 분석·테스트](.claude/rules/testing.md) | PHPStan, PHPUnit |
| [Git 워크플로우](.claude/rules/git-workflow.md) | 브랜치 전략, 머지 방식, 커밋 규칙 |
| [CI·CD·인프라](.claude/rules/ci-cd.md) | GitHub Actions, SSH 배포, AWS 참고 |
| [LSP 설정](.claude/rules/tooling.md) | Intelephense(PHP), Dart/Flutter LSP |

@.claude/rules/code-style.md
@.claude/rules/security.md
@.claude/rules/api-design.md
@.claude/rules/admin-views.md
@.claude/rules/testing.md
@.claude/rules/git-workflow.md
@.claude/rules/ci-cd.md
@.claude/rules/tooling.md
