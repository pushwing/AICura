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

`.env`에는 아래 값이 필요하다. 시크릿과 실제 `.env`는 절대 커밋하지 않는다.

```env
app.baseURL = http://localhost:8300/
database.default.hostname = localhost
database.default.database = aicura
database.default.username = root
database.default.password =
JWT_SECRET = 32자-이상-랜덤-문자열
TINYMCE_API_KEY = # 리치 에디터 사용 시
```

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
- API 인증 후 `JwtAuthFilter`가 `Auth::setUserId((int) $payload['sub'])`로 요청 사용자 ID를 저장한다. API 컨트롤러에서는 `$this->authUserId()`로 가져오며, 이 정적 홀더는 요청 컨텍스트 안에서만 사용한다.
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
- 서버 사이드 페이지네이션이 필요하면 AG Grid `serverSideDatasource`를 적용한다.
- 리치 텍스트는 빌드 단계 없이 ES module CDN의 Tiptap을 사용한다. 폼 제출은 숨김 input에 `editor.getHTML()`을 동기화하고, 저장된 HTML은 `editor.commands.setContent(savedHtml)`로 불러온다. 구현은 `app/Views/admin/campaigns/show.php`의 메모 에디터를 참고한다. 출력 전에는 허용 태그 화이트리스트를 적용하거나 `esc($content, 'html')`로 이스케이프한다.
- 차트는 Chart.js를 사용하며 Primary `#0F6E56`, Secondary `#1D9E75`를 우선한다. 데이터는 컨트롤러에서 `$labels`, `$values`로 분리해 전달한다. 민감한 집계 데이터는 뷰에 직접 넣지 않고 API 엔드포인트로 분리한다.
- 엑셀은 PhpSpreadsheet를 사용한다. 1만 행 이상은 청크 처리하고 업로드·임시 파일은 `public/` 밖에 둔다.

## 검증·CI·배포

- `.githooks/`를 사용한다. 클론 뒤 `git config core.hooksPath .githooks`로 활성화한다.
- `feature/*` push와 `feature → dev` PR에는 검사·CI가 없으며 코드 리뷰만 게이트다. `dev` 직접 push는 문서 전용 예외에만 허용한다.
- `pre-commit`은 스테이징된 PHP/Dart를 CS Fixer·`dart format`으로 자동 수정 후 재스테이징한다. `pre-push`는 `main` 직접 push를 차단하고, `feature/*`는 검사 없이 통과하며, `dev` 대상의 PHP 변경은 `composer check`, Flutter 변경은 `dart format --output=none --set-exit-if-changed lib test && flutter analyze && flutter test`를 실행한다. 문서 전용 push는 검사 대상이 없어 통과한다. 긴급 우회는 `git push --no-verify`다.
- GitHub Actions는 `dev → main` PR과 `main` push에서만 실행된다. `dev`를 트리거에 추가하지 않는다. 변경 경로에 따라 backend(PHPStan + PHPUnit)와 app(Flutter)을 선택 실행하며, 같은 ref의 새 실행은 이전 실행을 취소한다.
- backend CI는 Docker `mysql:8.0`을 `-p 127.0.0.1::3306` 동적 포트로 기동하고, PHP 8.5 확장과 `pcov`를 준비한다. `phpunit.dist.xml`은 coverage·`failOnWarning`을 사용하므로 `pcov`를 제거하면 안 된다. CI용 `.env`와 `writable/`을 만든 뒤, 테스트 DB host/port를 동적 포트로 치환하고 `composer analyse`, `composer test`를 실행한다. MySQL 컨테이너는 항상 정리한다.
- Flutter CI는 `app-mobile/`에서 `flutter pub get` → `dart format --set-exit-if-changed lib test` → `flutter analyze` → `flutter test` 순서다. 새 기능은 관련 `tests/`를 추가하고 PHPStan level 6과 PHPUnit을 통과해야 한다.

### self-hosted macOS runner

- CI와 배포는 GitHub-hosted runner가 아닌 self-hosted macOS runner를 사용한다. runner는 `~/actions-runners/AICura`에 있고 LaunchAgent `actions.runner.pushwing-AICura.aicura-mac-local-runner.plist`로 실행된다. 상태 관리는 `./svc.sh status|stop|start`를 사용한다.
- macOS self-hosted runner에서는 GitHub Actions `services:`와 Docker 컨테이너 액션을 사용하지 않는다. MySQL은 `docker run`으로 직접 기동하고, 배포는 `appleboy/ssh-action` 대신 macOS 내장 `ssh`를 `run:` 단계에서 사용한다.
- macOS에서는 BSD sed 문법 `sed -i '' -e '...'`를 사용한다. GNU `sed -i` 문법을 넣지 않는다.
- YAML `run: |` 안의 heredoc 종료 마커는 실행 스크립트에서 flush-left가 되도록 본문과 정확히 같은 들여쓰기를 유지한다. 어긋나면 heredoc이 닫히지 않아 배포가 멈춘다.
- runner를 재등록할 때는 GitHub registration token을 새로 발급하고 `./config.sh --replace`를 사용한다. 호스팅 runner로 되돌릴 때는 `runs-on`, MySQL 서비스 구성, SSH 구현을 함께 되돌린다.

### 배포

- `main` push와 `workflow_dispatch`(롤백·수동 실행)로 Ubuntu + mod_php Apache 서버에 SSH 배포한다. `deploy-production` 동시성 그룹은 한 번에 하나만 실행하며 진행 중 배포를 취소하지 않는다.
- SSH에는 TTY를 할당하지 않는다. `sudo -n systemctl reload apache2`와 Apache 자식 프로세스 때문에 `-t`/`-tt`를 쓰면 세션이 끝나지 않을 수 있다.
- 배포는 `git reset --hard origin/main` → `writable/` 생성 → `composer install --no-dev --optimize-autoloader` → `php spark migrate --all -f` 출력 검사 → `AdminUserSeeder` 실행(재실행 안전) → 캐시 정리 → `sudo -n systemctl reload apache2` 순서다.
- `php spark migrate`는 예외에도 종료 코드 0을 낼 수 있으므로, 배포 스크립트의 예외 출력 검사 로직을 유지한다.
- 배포 서버의 `writable/` 권한 변경은 Apache 소유 파일 때문에 실패할 수 있으므로 best-effort 처리와 setgid 구성을 유지한다.
- production GitHub Secrets는 `DEPLOY_HOST`, `DEPLOY_USER`, `DEPLOY_SSH_KEY`, `DEPLOY_PORT`, `DEPLOY_PATH`다. 서버는 GitHub 읽기 전용 deploy key, 실제 DB `.env`, `apache2 reload`에 대한 NOPASSWD sudo, `public/` DocumentRoot, `www-data`가 쓰기 가능한 `writable/` 및 setgid 구성이 필요하다.
- 저장소 설정 `delete_branch_on_merge=false`를 유지해 `dev`가 배포 PR 머지 뒤 자동 삭제되지 않게 한다.

### 클라우드·인프라 참고

- 기본 AWS 구성은 ECS(Fargate) + RDS + ElastiCache(Redis) + SQS다.
- 운영 시크릿은 AWS SSM Parameter Store 또는 Secrets Manager에 두고, 로그는 구조화 JSON으로 남긴다.
- `GET /health`는 DB·캐시 연결 상태를 포함하도록 제공한다.

## Flutter 도구 지원

`app-mobile/`의 Dart 분석 서버는 Flutter/Dart SDK에 포함되어 있다. Codex에서는 Claude 플러그인 파일을 만들지 말고, 필요 시 프로젝트에 설치된 `dart`, `flutter` 명령으로 포맷·분석·테스트를 실행한다.

## 작업 전 확인 목록

- API/Controller 변경: 입력 검증, 인증·권한, 오류 응답, OpenAPI, PHPUnit을 확인한다.
- Model/Service 변경: SQL 바인딩, 트랜잭션, N+1, 회귀 테스트와 PHPStan을 확인한다.
- Admin 뷰 변경: `render()`, `esc()`, UI 가이드와 모바일/브라우저 동작을 확인한다.
- 배포·CI 변경: self-hosted macOS runner, 동적 MySQL 포트, 비대화형 SSH와 마이그레이션 실패 감지를 확인한다.
