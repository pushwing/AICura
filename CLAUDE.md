# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

AI 기반 성형·토탈 광고 솔루션. CodeIgniter 4 기반 Admin + REST API 단일 프로젝트.

## 기술 스택

- **언어**: PHP 8.2+
- **프레임워크**: CodeIgniter 4
- **인증**: 세션(Admin) / JWT Bearer(API) — JWT는 외부 라이브러리 없이 `JwtLibrary`(HMAC-SHA256)로 직접 구현
- **API 문서**: Swagger UI (`/api/docs`) — `zircote/swagger-php`

## 로컬 환경 설정

```bash
cp env .env          # env 파일을 .env로 복사 후 DB·JWT_SECRET 등 설정
composer install
php spark migrate
```

## 커맨드

```bash
php spark serve               # 개발 서버
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

## Admin 뷰 개발

Admin 뷰 작성 시 아래를 반드시 참고한다.

- **UI 컴포넌트·CSS 클래스**: `docs/ui-guide.md`
- **브랜드 컬러·로고·타이포**: `assets/logo/`, `docs/design-system.md`
- CSS 파일: `ui/aicura.css` 를 레이아웃에 포함
- 컴포넌트 실물 확인: `ui/components.html`

### 데이터 그리드

목록성 화면(테이블)은 **AG Grid Community** 를 사용한다.

```html
<!-- CDN -->
<script src="https://cdn.jsdelivr.net/npm/ag-grid-community/dist/ag-grid-community.min.js"></script>

<!-- 기본 사용 패턴 -->
<div id="myGrid" style="height:500px;" class="ag-theme-alpine"></div>
<script>
const gridOptions = {
    columnDefs: [
        { field: 'name', headerName: '캠페인명' },
        { field: 'status', headerName: '상태' },
    ],
    rowData: <?= json_encode($rows) ?>,
    pagination: true,
    paginationPageSize: 20,
};
agGrid.createGrid(document.getElementById('myGrid'), gridOptions);
</script>
```

- 테마: `ag-theme-alpine` 기본 사용
- 서버사이드 페이징이 필요한 경우 `serverSideDatasource` 적용
- `html` 셀 렌더링 시 `cellRenderer` 사용 (`innerHTML` 직접 조작 금지)

### 에디터

리치 텍스트 입력이 필요한 경우 **TinyMCE** 를 사용한다.

```html
<script src="https://cdn.tiny.cloud/1/{API_KEY}/tinymce/7/tinymce.min.js" referrerpolicy="origin"></script>
<script>
tinymce.init({
    selector: 'textarea.editor',
    language: 'ko_KR',
    plugins: 'link image lists table code',
    toolbar: 'undo redo | bold italic | alignleft aligncenter alignright | bullist numlist | link image | code',
    images_upload_url: '/admin/media/upload',
});
</script>
```

- API Key는 `.env`의 `TINYMCE_API_KEY`에서 관리
- 저장 시 출력은 반드시 `esc($content, 'html')` 또는 허용된 태그 화이트리스트 필터 적용

### 차트

통계·리포트 화면의 차트는 **Chart.js** 를 사용한다.

```html
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<canvas id="myChart"></canvas>
<script>
new Chart(document.getElementById('myChart'), {
    type: 'bar',
    data: {
        labels: <?= json_encode($labels) ?>,
        datasets: [{
            label: '광고비',
            data: <?= json_encode($values) ?>,
            backgroundColor: '#1D9E75',
        }],
    },
    options: { responsive: true },
});
</script>
```

- 브랜드 Primary 컬러 `#0F6E56` / Secondary `#1D9E75` 우선 사용
- 데이터는 컨트롤러에서 `$labels`, `$values` 형태로 분리해 전달
- 민감한 집계 데이터는 뷰에 직접 노출하지 않고 API 엔드포인트로 분리 고려

### 엑셀

엑셀 내보내기·읽기는 **PhpSpreadsheet** 를 사용한다.

```bash
composer require phpoffice/phpspreadsheet
```

```php
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// 내보내기
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->fromArray($rows, null, 'A1');

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="export.xlsx"');
(new Xlsx($spreadsheet))->save('php://output');
exit;

// 읽기
$spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($filePath);
$rows = $spreadsheet->getActiveSheet()->toArray();
```

- 대용량(1만 행 이상)은 `ChunkReadFilter` 또는 청크 단위 처리 적용
- 업로드된 파일은 `public/` 외부 경로(`writable/uploads/`)에 저장 후 처리
- 처리 완료 후 임시 파일 즉시 삭제

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

## API 부하 분산 원칙

API 개발 시 부하 분산을 최우선으로 고려한다. 아래 원칙을 기본으로 적용한다.

### 캐시

- 변경 빈도가 낮은 조회 응답은 **Redis 캐시** 적용
- 캐시 키 규칙: `{리소스}:{식별자}:{파라미터해시}` (예: `campaigns:list:abc123`)
- TTL 기준

| 데이터 성격 | TTL |
|------------|-----|
| 설정·코드성 데이터 | 1시간 이상 |
| 목록·집계 | 5–60분 |
| 단건 상세 | 5–10분 |
| 실시간 필요 데이터 | 캐시 적용 금지 |

- 쓰기(INSERT·UPDATE·DELETE) 발생 시 관련 캐시 즉시 무효화
- 캐시 미스 시 DB 조회 후 캐시 저장 — 로직은 Service 레이어에서 처리

### 큐

- 즉시 응답이 불필요한 작업은 큐로 위임 (이메일·알림·로그·리포트 생성 등)
- API는 큐 적재 후 즉시 `202 Accepted` 반환
- 무거운 연산(배치 집계·엑셀 생성 등)은 절대 요청 사이클 안에서 처리 금지

### DB 쿼리

- `SELECT *` 금지 — 필요한 컬럼만 명시
- N+1 쿼리 금지 — 관계 데이터는 JOIN 또는 eager load
- 목록 API는 반드시 페이징 적용 (`limit` / `offset` 또는 커서 기반)
- 인덱스 없는 컬럼 `WHERE` 조건 금지 — 마이그레이션에 인덱스 함께 정의
- 집계 쿼리(`COUNT`, `SUM` 등)는 캐시 우선 적용

### API 응답

- 불필요한 필드 제거 — 응답 페이로드 최소화
- 목록 응답에 `meta.total`, `meta.page` 포함
- 대용량 데이터 응답은 스트리밍 또는 청크 분할 고려

### 기타

- 외부 API 호출은 타임아웃 설정 필수 (기본 5초)
- 외부 API 실패 시 재시도는 큐로 처리 (즉시 재시도 금지)
- 동일 엔드포인트 반복 호출 방어: Rate Limit 필터 적용 검토

## 로그 수집 파이프라인

프론트(앱/웹)에서 API로 전송되는 로그는 큐를 통해 비동기 처리한다.

### 흐름

```
앱/웹
  │
  │ POST /api/v1/logs
  ▼
API Server
  │ 큐에 적재 (즉시 응답)
  ▼
Queue (Redis)
  │
  ▼
Queue Consumer (Spark Command / Scheduler)
  ├── 원시 로그 → 파일 저장 (writable/logs/raw/YYYY-MM-DD.log)
  └── 가공 데이터 → DB INSERT
```

### 규칙

- API는 로그를 받는 즉시 큐에 넣고 `202 Accepted` 응답 — DB 직접 쓰기 금지
- 큐 드라이버: **Redis** (`predis/predis`)
- 원시(raw) 로그는 `writable/logs/raw/` 에 날짜별 파일로 append
- 가공 후 DB 저장 — 원시 파일은 보존 (감사·재처리 용도)
- Consumer는 Spark Command로 구현, CI4 Scheduler로 주기 실행
- 큐 처리 실패 시 dead-letter 로깅 필수 (`writable/logs/queue-failed/`)

### 기본 패턴

```php
// API Controller — 큐에 적재
public function store(): ResponseInterface
{
    $payload = $this->request->getJSON(true);
    // 유효성 검사 후
    Redis::lpush('log_queue', json_encode($payload));
    return $this->respond(null, 202);
}

// Spark Command — Consumer
class ProcessLogQueue extends BaseCommand
{
    public function run(array $params): void
    {
        while ($raw = Redis::rpop('log_queue')) {
            // 1. 원시 파일 저장
            file_put_contents(
                WRITEPATH . 'logs/raw/' . date('Y-m-d') . '.log',
                $raw . PHP_EOL,
                FILE_APPEND
            );
            // 2. 가공 후 DB 저장
            $data = $this->transform(json_decode($raw, true));
            model(LogModel::class)->insert($data);
        }
    }
}
```

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
| 뷰에서 Model을 직접 호출해 데이터 조회 | MVC 책임 분리 위반, 테스트·유지보수 불가 |

뷰는 컨트롤러가 전달한 데이터만 렌더링한다.

```php
// ❌ 금지 — 뷰에서 직접 조회
$campaigns = new \App\Models\CampaignModel();
foreach ($campaigns->findAll() as $item) { ... }

// ✅ 올바른 방식 — 컨트롤러에서 전달
// Controller
return $this->render('admin/campaigns/index', [
    'campaigns' => model(CampaignModel::class)->findAll(),
]);

// View
foreach ($campaigns as $item) { ... }
```
