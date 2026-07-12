# CI · CD · 클라우드 인프라

## CI (GitHub Actions)

`dev` · `main` 으로의 **push / PR** 마다 자동 검증된다. 정의: `.github/workflows/ci.yml` (단일 파일, 두 잡 병렬).

- **동시성**: 같은 ref 새 푸시 시 진행 중 실행 취소 (`concurrency.cancel-in-progress`)

### `backend` 잡 — PHP 8.5 · PHPStan · PHPUnit

`mysql:8.0` 서비스 컨테이너를 띄우고 다음 순서로 검증한다.

1. setup-php `8.5` (확장: `mbstring intl mysqli curl dom xml tokenizer`, 커버리지 `pcov`)
   - `phpunit.dist.xml` 이 `failOnWarning` + `<coverage>` 를 켜 두어 커버리지 드라이버 없으면 경고→실패 → `pcov` 필수
2. Composer 캐시 → `composer install`
3. `env` → `.env` 복사 후 CI용 DB·`JWT_SECRET` 주입
4. `writable/` 하위 디렉토리 생성 (git 미추적, `WRITEPATH` 보장)
5. `composer analyse` (PHPStan level 6)
6. MySQL 헬스 대기 → `phpunit.dist.xml` 의 `database.tests.hostname` 을 `localhost` → `127.0.0.1` 로 sed 치환 (MySQLi TCP 강제)
7. `composer test` (PHPUnit 단위·DB 통합)

### `app` 잡 — Flutter · analyze · test

`app-mobile/` 작업 디렉토리에서 실행한다.

1. Flutter stable 채널 설치 → `flutter pub get`
2. `dart format --set-exit-if-changed lib test` (포맷 검사)
3. `flutter analyze`
4. `flutter test`

### 푸시 전 로컬 사전 검증

CI 실패를 줄이기 위해 푸시 전 동일 검증을 로컬에서 수행한다.

```bash
composer check   # = analyse + test (백엔드)
# 앱: cd app-mobile && dart format --output=none --set-exit-if-changed lib test && flutter analyze && flutter test
```

> 새 PHP 코드는 PHPStan level 6 통과 + 관련 PHPUnit 테스트가 그린이어야 CI를 통과한다. 새 기능에는 `tests/` 테스트를 함께 작성한다.

## CD (배포)

`main` push(= `dev → main` PR 머지) 시 프로덕션 서버로 **SSH 자동 배포**된다. 정의: `.github/workflows/deploy.yml`.

- **트리거**: `main` push + `workflow_dispatch`(수동·롤백)
- **동시성**: `deploy-production` 그룹 — 배포 동시 실행 1개, `cancel-in-progress: false`
- **대상**: Ubuntu + mod_php 아파치 단일 서버 (appleboy/ssh-action)

### 배포 절차 (서버 SSH 실행)

1. `git reset --hard origin/main` — 최신 main 반영
2. `writable/` 디렉토리 생성 — **반드시 composer/migrate 이전** (없으면 spark 부팅 실패 `WRITEPATH is not set correctly`)
3. `composer install --no-dev --optimize-autoloader`
4. `php spark migrate --all -f` — 출력을 grep 검사해 예외 감지 시 `exit 1` 로 배포 중단
5. `php spark cache:clear`
6. `sudo -n systemctl reload apache2` — OPcache 갱신(무중단)

> **spark migrate 함정**: DB 연결 실패·마이그레이션 예외가 나도 종료코드 0 을 반환한다. `set -e` 로 못 잡으므로 출력을 캡처해 예외 패턴(`[...Exception]`·`Unable to connect`·`Access denied`)을 직접 검사하고 실패 시 배포를 중단한다.

> **writable chmod 함정**: 런타임에 아파치(`www-data`)가 만든 `writable/cache`·`session` 파일은 배포 계정 소유가 아니라 `chmod -R 775 writable` 가 `Operation not permitted` 로 실패한다. `set -e` 로 배포가 중단되지 않도록 이 `chmod` 는 best-effort(`2>/dev/null || echo …`)로 처리한다. 근본 해결은 아래 서버 준비의 setgid 구성이다.

### 필요한 GitHub Secrets (`production` 환경)

`DEPLOY_HOST` · `DEPLOY_USER` · `DEPLOY_SSH_KEY` · `DEPLOY_PORT` · `DEPLOY_PATH`

### 서버 사전 준비 (한 번만)

- **GitHub 읽기전용 deploy key** — 서버 저장소 리모트를 SSH(`git@github.com:...`)로 설정 (HTTPS면 `could not read Username` 실패)
- **프로덕션 `.env`** 에 실제 DB 접속정보 (없으면 migrate 시 `Access denied`)
- **비밀번호 없는 sudo**: `/etc/sudoers.d/aicura-deploy` 에 `<DEPLOY_USER> ALL=(ALL) NOPASSWD: /usr/bin/systemctl reload apache2` (없으면 `sudo: a password is required` 로 실패)
- 아파치 `DocumentRoot` 는 `public/`, `writable/` 는 아파치 유저(`www-data`) 쓰기 가능
- **writable setgid 구성(권장)** — 소유권 충돌로 인한 chmod 실패를 근본 제거:
  ```bash
  sudo chown -R <DEPLOY_USER>:www-data <DEPLOY_PATH>/writable
  sudo chmod -R 2775 <DEPLOY_PATH>/writable   # setgid: 새 파일이 www-data 그룹 상속
  ```

### 배포 후 — 기본 관리자 계정 (최초 1회)

배포에는 마이그레이션만 포함되고 **시더는 자동 실행되지 않는다.** 관리자 계정(`admin@aicura.com` / `user_type=401`)이 없으면 서버에서 한 번 실행한다(재실행 안전).

```bash
cd <DEPLOY_PATH> && php spark db:seed AdminUserSeeder
```

### 브랜치 삭제 방지

저장소가 프라이빗+무료 플랜이라 GitHub 브랜치 보호·Ruleset API 는 사용 불가(Pro 필요). 대신 저장소 설정 `delete_branch_on_merge=false` 로 `dev → main` 머지 시 `dev` 자동삭제를 막는다. `main` 은 기본 브랜치라 삭제 불가.

## 클라우드·인프라 (참고)

- **AWS 기본 스택**: ECS(Fargate) + RDS + ElastiCache(Redis) + SQS
- **시크릿 관리**: `.env` 커밋 금지 — AWS SSM Parameter Store / Secrets Manager 사용
- **로그**: 구조화 로그(JSON) 지향
- **헬스체크**: `GET /health` 엔드포인트 (DB·캐시 연결 상태 포함) 제공 권장
