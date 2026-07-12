# PHP 절대 금지 (보안·코드 품질)

## 보안

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

## 코드 품질

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

## PHP 특성 함정

| 금지 | 이유 | 대신 |
|------|------|------|
| `==` 타입 비교 | `0 == "a"` → true | `===` 사용 |
| `intval()` 없이 문자열을 숫자로 연산 | 타입 오염 | 명시적 형변환 또는 타입 선언 |
| 타입 선언 없는 함수 파라미터 | PHPStan 레벨 6 통과 불가 | `string $id`, `int $count` 명시 |
| `null` 반환과 `false` 반환 혼용 | 호출부 처리 혼란 | 반환 타입 통일 |
| `catch` 후 예외 무시 | 버그가 조용히 삼켜짐 | 최소한 로깅 |

## CI4 한정

| 금지 | 이유 |
|------|------|
| Controller에 비즈니스 로직 작성 | Model/Service로 위임 |
| `$db->query("... WHERE id = $id")` | SQL Injection |
| `allowedFields` 없는 Model | 의도치 않은 mass assignment |
| CSRF 예외 라우트 무분별 추가 | 보호 구멍 |
| `env()` 없이 Config에 직접 시크릿 작성 | `.env` 관리 원칙 위반 |
| 뷰에서 Model을 직접 호출해 데이터 조회 | MVC 책임 분리 위반, 테스트·유지보수 불가 |
| `new UserModel()` 직접 인스턴스화 | `model()` 헬퍼 우회 — `model(UserModel::class)` 사용 |

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
