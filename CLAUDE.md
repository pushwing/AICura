# CLAUDE.md — AI Cura

AI 기반 성형·토탈 광고 솔루션. CodeIgniter 4 기반 Admin + REST API 단일 프로젝트.

## 기술 스택

- **언어**: PHP 8.1+
- **프레임워크**: CodeIgniter 4
- **인증**: 세션(Admin) / JWT Bearer(API)
- **API 문서**: Swagger UI (`/api/docs`) — `zircote/swagger-php`

## 커맨드

```bash
php spark serve               # 개발 서버
php spark migrate             # DB 마이그레이션
php spark swagger:generate    # OpenAPI 스펙 생성 (public/swagger.json)
php spark routes              # 라우트 목록
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

## API 응답 포맷

```php
// 성공
$this->success($data, $meta);
// → { "status": "success", "data": {...}, "meta": {...} }

// 실패
$this->error('ERROR_CODE', '메시지', $statusCode);
// → { "status": "error", "code": "...", "message": "..." }
```

## Swagger 어트리뷰트 규칙

새 API 엔드포인트마다 PHP 어트리뷰트 추가 필수.

```php
use OpenApi\Attributes as OA;

#[OA\Get(path: '/campaigns', summary: '...', security: [['bearerAuth' => []]], tags: ['Campaigns'], responses: [...])]
public function index() { ... }
```

## Git 워크플로우

```
feature/* → (PR) → dev → (PR) → main
```

- **기능 개발**: `dev`에서 `feature/기능명` 브랜치 생성
- **PR 대상**: `feature/*` → `dev`
- **배포**: `dev` → `main` PR
- **머지 방식**: Squash and merge
- **머지 후**: `feature/*` 브랜치 자동 삭제
- `main`과 `dev`에 직접 push 금지

자세한 내용: `docs/git-workflow.md`

## 정적 분석 (PHPStan)

코드 작성 후 반드시 정적 분석을 통과해야 한다.

```bash
composer analyse          # PHPStan 단독 실행
composer check            # PHPStan + PHPUnit 순차 실행
```

- 분석 레벨: **6** (`phpstan.neon`)
- 분석 대상: `app/` (Views 제외)
- 새 클래스·메서드 작성 시 `array<string, mixed>` 등 제네릭 타입 명시 필수
- `@phpstan-ignore` 주석으로 억제 금지 — 원인을 찾아 코드를 수정할 것

## 네이밍 규칙

### PHP

| 대상 | 규칙 | 예시 |
|------|------|------|
| 클래스 | PascalCase | `CampaignController`, `JwtLibrary` |
| 인터페이스 | PascalCase + `Interface` | `PGInterface`, `AdapterInterface` |
| 추상 클래스 | `Base` 접두어 | `BaseApiController`, `BaseAdminController` |
| 메서드 | camelCase | `getAccessToken()`, `buildPaymentParams()` |
| 변수 | camelCase | `$accessToken`, `$campaignId` |
| 프로퍼티 | camelCase | `$authUserId`, `$refreshTtl` |
| 상수 | UPPER_SNAKE_CASE | `MAX_RETRY`, `DEFAULT_TTL` |
| 배열 키 | snake_case | `$data['access_token']`, `$payload['user_id']` |
| 파일명 | 클래스와 동일 | `CampaignController.php` |

### DB

| 대상 | 규칙 | 예시 |
|------|------|------|
| 테이블 | snake_case · 복수형 | `campaigns`, `ad_creatives`, `stock_logs` |
| 컬럼 | snake_case | `created_at`, `discount_price` |
| PK | `id` | `id` |
| FK | `{단수테이블명}_id` | `campaign_id`, `user_id` |
| 불리언 | `is_` 접두어 | `is_active`, `is_deleted` |
| 타임스탬프 | CI4 표준 | `created_at`, `updated_at`, `deleted_at` |
| 일반 인덱스 | `idx_{테이블}_{컬럼}` | `idx_campaigns_status` |
| 유니크 인덱스 | `uniq_{테이블}_{컬럼}` | `uniq_users_email` |
| Pivot 테이블 | 두 테이블 알파벳순 · 단수 | `campaign_tag` |

## 코딩 규칙

- PSR-12 준수
- 입력값은 반드시 CI4 Validation 또는 `esc()` 처리
- SQL은 CI4 Query Builder만 사용 (raw query 금지)
- 시크릿은 `.env`에서만 관리 (`env('KEY')`)
- POST 폼에는 `<?= csrf_field() ?>` 필수 (Admin 뷰)

## PHP 절대 금지

### 보안

| 금지 | 이유 | 대신 |
|------|------|------|
| `$_GET`·`$_POST` 직접 사용 | 필터링 없는 원시 입력 | `$this->request->getPost()` |
| SQL 문자열 직접 조합 | SQL Injection | Query Builder / 바인딩 |
| `echo $변수` (뷰에서) | XSS | `echo esc($변수)` |
| `eval()` 사용 | 코드 인젝션 | 사용 이유 자체를 제거 |
| `md5()` / `sha1()`로 비밀번호 저장 | 취약한 해시 | `password_hash()` |
| 시크릿·API키 코드에 하드코딩 | 노출 위험 | `.env` + `env('KEY')` |
| CSRF 토큰 없이 POST 처리 | CSRF 공격 | `csrf_field()` |
| `$_FILES` 직접 처리 후 저장 | 악성 파일 업로드 | 확장자·MIME 검증 필수 |
| 에러 메시지에 스택 트레이스 노출 | 내부 구조 노출 | 운영 환경 `CI_ENVIRONMENT=production` |

### 코드 품질

| 금지 | 이유 |
|------|------|
| `@` 에러 억제 연산자 | 에러를 숨겨 디버깅 불가 |
| `extract($array)` | 변수 충돌·추적 불가 |
| `global $변수` | 상태 추적 불가, 테스트 불가 |
| `die()` / `exit()` 비즈니스 로직 안에 | 응답 흐름 단절, 테스트 불가 |
| 함수 하나에 100줄 이상 | 단일 책임 원칙 위반 |
| 의미 없는 변수명 (`$a`, `$tmp`, `$data2`) | 가독성 저하 |
| 주석으로 코드 비활성화 후 방치 | 죽은 코드 |
| `var_dump()` / `print_r()` 커밋 | 디버그 코드 노출 |

### PHP 특성 함정

| 금지 | 이유 | 대신 |
|------|------|------|
| `==` 타입 비교 | `0 == "a"` → true | `===` 사용 |
| `intval()` 없이 문자열을 숫자로 연산 | 타입 오염 | 명시적 형변환 또는 타입 선언 |
| 타입 선언 없는 함수 파라미터 | PHPStan 레벨 6 통과 불가 | `string $id`, `int $count` 명시 |
| `null` 반환과 `false` 반환 혼용 | 호출부 처리 혼란 | 반환 타입 통일 |
| `catch` 후 예외 무시 | 버그가 조용히 삼켜짐 | 최소한 로깅 |

### CI4 한정

| 금지 | 이유 |
|------|------|
| Controller에 비즈니스 로직 작성 | Model/Service로 위임 |
| `$db->query("... WHERE id = $id")` | SQL Injection |
| `allowedFields` 없는 Model | 의도치 않은 mass assignment |
| CSRF 예외 라우트 무분별 추가 | 보호 구멍 |
| `env()` 없이 Config에 직접 시크릿 작성 | `.env` 관리 원칙 위반 |
