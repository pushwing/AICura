# AICura Codex 작업 지침

AI 기반 성형·토탈 광고 솔루션이며, CodeIgniter 4 기반 Admin + REST API 단일 프로젝트다.

이 파일은 Codex가 자동으로 읽는 저장소 규칙의 단일 기준이다. Claude Code 전용 설정인 `CLAUDE.md`, `.claude/`, `@import`, `.claude-plugin`, `.lsp.json`, `/reload-plugins` 지시는 Codex에서 사용하지 않는다. 세부 배경 자료는 기존 `.claude/rules/`에 남아 있지만, Codex 작업에 필요한 규칙은 이 문서만으로 판단한다.

## 작업 원칙

- 작업 전 `git status --short --branch`로 브랜치와 기존 변경 사항을 확인한다. 사용자의 변경 사항은 되돌리거나 덮어쓰지 않는다.
- 요청 범위 밖의 리팩터링·설정 변경·파일 정리를 하지 않는다.
- 코드·설정 변경은 `feature/*` 브랜치에서 수행하고 PR로 `dev`에 반영한다. `main` 직접 push는 금지한다.
- 하나의 논리적 작업을 완료하면 독립 커밋을 만든다. 커밋 메시지는 `이모지 + Conventional Commits 접두어 + 한국어 설명` 형식이다. 예: `🐛 fix: 로그인 토큰 만료 처리`.
- 변경 위험도에 맞는 테스트와 정적 분석을 실행한다. 실패한 상태로 커밋·push하지 않는다.
- 응답과 코드 주석은 한국어로 작성한다. 기술 용어·명령어·식별자는 원문을 유지한다.

## 기술 스택과 로컬 실행

- PHP: CLI 8.4.22, FrankenPHP 내장 8.5.7, CI는 PHP 8.5. `composer.json`은 `^8.4`를 요구한다.
- 웹 서버: FrankenPHP v1.12(`make serve`, 포트 8300 권장) 또는 CI4 내장 서버(`make serve-spark`).
- 인증: Admin은 세션, API는 HMAC-SHA256 `JwtLibrary` 기반 JWT Bearer.
- API 문서: RapiDoc(`/api/docs`), `zircote/swagger-php`로 OpenAPI 생성.
- 정적 분석: PHPStan level 6(`app/`, Views 제외).
- 모바일: `app-mobile/` Flutter 앱.

```bash
cp env .env
composer install
php spark migrate

make serve
make serve-spark
php spark swagger:generate
php spark routes
composer analyse
composer test
composer check
```

`.env`에는 `app.baseURL`, DB 접속 정보, 32자 이상 `JWT_SECRET`이 필요하다. 시크릿과 실제 `.env`는 절대 커밋하지 않는다.

## 디렉터리와 아키텍처

| 경로 | 용도 |
|---|---|
| `app/Controllers/Admin/` | 세션 인증 관리자 컨트롤러 |
| `app/Controllers/Api/V1/` | JWT 인증 REST API 컨트롤러 |
| `app/Models/` | Admin·API 공유 데이터 접근 계층 |
| `app/Filters/` | `AdminAuthFilter`, `JwtAuthFilter` |
| `app/Libraries/` | `JwtLibrary` 등 공통 라이브러리 |
| `app/Commands/` | Spark 커스텀 커맨드 |
| `tests/` | PHPUnit 테스트 |
| `app-mobile/` | Flutter 앱 |
| `docs/`, `ui/`, `assets/logo/` | 문서·UI 컴포넌트·브랜드 자산 |

- Controller는 입력 검증 → Service 호출 → 응답 반환만 담당한다. 트랜잭션과 유스케이스는 Service에 둔다.
- CI4 뷰에서 Model을 직접 호출하지 않는다. 사용자 입력을 출력할 때 `esc()` 등 문맥에 맞는 이스케이프를 적용한다.
- API 인증 후 `JwtAuthFilter`가 `Auth::setUserId()`로 요청 사용자 ID를 저장한다. API 컨트롤러에서는 `$this->authUserId()`로 가져온다.
- Admin 뷰는 반드시 `BaseAdminController::render()`를 사용한다. 기본 `view()`를 직접 호출하면 세션의 `authUser`가 누락된다.

```php
return $this->render('admin/campaigns/index', ['campaigns' => $campaigns]);
```

## PHP·보안 규칙

- `declare(strict_types=1)`, PSR-12, 명시적 타입을 따른다. PHP 8.4 기능(typed property, `readonly`, enum, `match`)을 적절히 사용한다.
- Request 객체로 입력을 받고 검증한다. 원시 `$_GET`, `$_POST`, `$_REQUEST`, `$_FILES`를 직접 사용하지 않는다.
- SQL은 Query Builder/ORM 또는 바인딩만 사용한다. 문자열 조합 SQL은 금지한다.
- 상태 변경 요청에 CSRF 보호를 적용한다. 비밀번호는 `password_hash()`로 저장하며 `md5()`·`sha1()`은 사용하지 않는다.
- JWT·서명·암호화 로직을 새로 구현하지 않는다. 기존 `JwtLibrary` 계약을 유지하고 허용 알고리즘·서명·만료를 모두 검증한다.
- 업로드 파일은 확장자·MIME·크기를 검증하고 `writable/uploads/`에 저장한다. 임시 파일은 처리 후 삭제한다.
- `@`, `extract()`, `global`, 비즈니스 로직의 `die()`/`exit()`, `SELECT *`, N+1 쿼리, 의미 없는 변수명, 커밋되는 디버그 출력은 사용하지 않는다.
- API 오류는 기존 오류 코드와 공용 responder 계약을 재사용한다. 새 REST 엔드포인트는 OpenAPI 문서도 추가한다.

## Admin 뷰 규칙

- UI 컴포넌트와 CSS 클래스는 `docs/ui-guide.md`, 브랜드는 `docs/design-system.md`와 `assets/logo/`, 실물 컴포넌트는 `ui/components.html`을 먼저 확인한다.
- 레이아웃에 `ui/aicura.css`를 포함한다.
- 목록성 화면은 AG Grid Community와 `ag-theme-alpine`을 기본 사용한다. HTML 셀은 `cellRenderer`로 처리하고 `innerHTML` 직접 조작은 하지 않는다.
- 리치 텍스트는 Tiptap을 사용한다. 저장 전 허용 태그 화이트리스트를 적용하거나 `esc($content, 'html')`로 출력한다.
- 차트는 Chart.js를 사용하며 Primary `#0F6E56`, Secondary `#1D9E75`를 우선한다. 데이터는 컨트롤러에서 분리해 전달한다.
- 엑셀은 PhpSpreadsheet를 사용한다. 1만 행 이상은 청크 처리하고 업로드·임시 파일은 `public/` 밖에 둔다.

## 검증·CI·배포

- `.githooks/`를 사용한다. 필요하면 `git config core.hooksPath .githooks`로 활성화한다.
- `feature/*` push는 빠른 반복을 위해 훅 검사를 생략한다. `dev` 대상의 PHP 변경은 `composer check`, Flutter 변경은 `dart format --output=none --set-exit-if-changed lib test && flutter analyze && flutter test`가 필요하다.
- GitHub Actions는 `dev → main` PR과 `main` push에서 실행된다. 변경 경로에 따라 backend(PHPStan + PHPUnit)와 app(Flutter)을 선택 실행하며 self-hosted macOS runner를 사용한다.
- CI backend의 MySQL은 Docker로 동적 포트에 기동한다. CI 설정의 macOS `sed -i ''` 문법과 컨테이너 정리 단계를 훼손하지 않는다.
- `main` push 시 SSH 자동 배포된다. 배포는 `writable/` 생성 → `composer install --no-dev` → `php spark migrate --all -f` 결과 검사 → 캐시 정리 → Apache reload 순서다.
- `php spark migrate`는 예외에도 종료 코드 0을 낼 수 있으므로, 배포 스크립트의 예외 출력 검사 로직을 유지한다.
- 배포 서버의 `writable/` 권한 변경은 Apache 소유 파일 때문에 실패할 수 있으므로 best-effort 처리와 setgid 구성을 유지한다.

## Flutter 도구 지원

`app-mobile/`의 Dart 분석 서버는 Flutter/Dart SDK에 포함되어 있다. Codex에서는 Claude 플러그인 파일을 만들지 말고, 필요 시 프로젝트에 설치된 `dart`, `flutter` 명령으로 포맷·분석·테스트를 실행한다.

## 작업 전 확인 목록

- API/Controller 변경: 입력 검증, 인증·권한, 오류 응답, OpenAPI, PHPUnit을 확인한다.
- Model/Service 변경: SQL 바인딩, 트랜잭션, N+1, 회귀 테스트와 PHPStan을 확인한다.
- Admin 뷰 변경: `render()`, `esc()`, UI 가이드와 모바일/브라우저 동작을 확인한다.
- 배포·CI 변경: self-hosted macOS runner, 동적 MySQL 포트, 비대화형 SSH와 마이그레이션 실패 감지를 확인한다.
