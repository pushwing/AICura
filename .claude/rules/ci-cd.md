# CI · CD · 클라우드 인프라

## 검증 게이트 — 어디서 무엇을 돌리는가

검증은 로컬에서 끝낸다. `feature/* → dev` PR 에는 CI 를 걸지 않고, CI 는 `dev → main` 배포 PR 에서만 돈다.

```
feature/*  ──[검사 없음, 코드 리뷰만]──▶  dev  ──[PR + CI]──▶  main
                                            ↑
                                     여기서만 CI 가 돈다
```

| 시점 | 무엇을 | 누가 |
|------|--------|------|
| 개발 중 | 필요할 때 수시로 `composer analyse` / `composer test:unit`(DB 불필요) | 사람 / Claude |
| `feature/*` 푸시 | 검사 없음 — 작업 중 빠른 반복을 막지 않기 위해 | `.githooks/pre-push` 가 스킵 |
| `feature → dev` PR | CI 없음. 코드 리뷰만 | — |
| `dev` 직접 푸시(문서 전용 예외 등 드문 경우) | `composer check`(analyse + test) 전체 필수. 실패하면 푸시되지 않는다 | `.githooks/pre-push` |
| `dev → main` PR | GitHub Actions 전체 (PHPStan + PHPUnit, backend/app 잡) | CI |

> `feature/* → dev` 는 전역 [`git-workflow.md`](~/.claude/rules/git-workflow.md) 규칙에 따라 GitHub **Squash and merge**(`gh pr merge --squash`)로만 반영되고 로컬에서 `dev` 로 직접 push 하지 않는다. 즉 `feature → dev` 단계는 pre-push 훅도 CI 도 거치지 않으며, **코드 리뷰가 유일한 게이트**다. `dev` 로의 직접 push 는 문서 전용 변경 예외처럼 드문 경우에만 발생하고, 그때는 pre-push 훅이 실질적 검증 게이트로 동작한다.
>
> Claude 가 작업할 때도 동일하다 — `dev → main` 배포 PR 을 만들기 전에 `composer check` 를 실제로 실행하고 출력을 확인한 뒤 진행한다. "통과할 것 같다"로 넘어가지 않는다.

### 훅으로 강제한다 (`.githooks/`)

로컬 검증을 사람 기억에 의존하면 반드시 빠진다. 훅이 저장소에 커밋돼 있으니 클론 직후 1회 활성화한다.

```bash
git config core.hooksPath .githooks
```

| 훅 | 동작 |
|----|------|
| `pre-commit` | 스테이징된 `*.php`/`*.dart` 를 CS Fixer/`dart format` 으로 자동 수정 후 재-스테이징. 커밋을 막지는 않는다 |
| `pre-push` | 푸시 대상이 `main` 이면 무조건 차단(배포는 `dev → main` PR 로만). `feature/*` 는 검사 없이 통과. `dev` 대상일 때만 변경 파일 종류에 따라 `composer check` 또는 `dart format`/`flutter analyze`/`flutter test` 실행, 실패 시 푸시 중단 |

- 문서 전용 변경(`*.md`, `docs/**`, `.claude/rules/**` 만 바뀐 푸시)은 `dev` 대상이어도 검사할 PHP/Dart 파일이 없어 자동으로 통과한다.
- 긴급 우회: `git push --no-verify` (로컬 DB·flutter 미비 등)

## CI (GitHub Actions)

`dev → main` PR 과 `main` push(= 배포 PR 머지) 시에만 자동 검증된다. 정의: `.github/workflows/ci.yml`.

```yaml
on:
  push:
    branches: [main]
  pull_request:
    branches: [main]
```

`branches` 에 `dev` 를 넣지 않는 것이 핵심이다 — `feature → dev` PR 이나 `dev` 로의 push 에서는 CI 가 돌지 않는다. `dev → main` 배포 PR 은 merge commit 으로 머지하므로(전역 규칙), CI 가 통과한 커밋 조합이 그대로 `main` 에 올라간다.

- **동시성**: 같은 ref 새 푸시 시 진행 중 실행 취소 (`concurrency.cancel-in-progress`)

### `backend` 잡 — PHP 8.5 · PHPStan · PHPUnit

Docker 로 `mysql:8.0` 컨테이너를 직접 기동하고 다음 순서로 검증한다(왜 `services:` 대신 Docker 인지는 아래 "self-hosted 러너에서 돈다" 참고).

1. `docker run` 으로 MySQL 컨테이너 기동 — 호스트 포트는 고정하지 않고 `-p 127.0.0.1::3306` 로 동적 할당, `docker port` 로 읽어 `$MYSQL_PORT` 에 저장
2. setup-php `8.5` (확장: `mbstring intl mysqli curl dom xml tokenizer`, 커버리지 `pcov`)
   - `phpunit.dist.xml` 이 `failOnWarning` + `<coverage>` 를 켜 두어 커버리지 드라이버 없으면 경고→실패 → `pcov` 필수
3. Composer 캐시 → `composer install`
4. `env` → `.env` 복사 후 CI용 DB(동적 포트 포함)·`JWT_SECRET` 주입
5. `writable/` 하위 디렉토리 생성 (git 미추적, `WRITEPATH` 보장)
6. `composer analyse` (PHPStan level 6)
7. `phpunit.dist.xml` 의 `database.tests.hostname`(`localhost`→`127.0.0.1`)·`database.tests.port`(`3306`→`$MYSQL_PORT`) 를 sed 로 치환
8. `composer test` (PHPUnit 단위·DB 통합)
9. `if: always()` 로 MySQL 컨테이너 정리

### `app` 잡 — Flutter · analyze · test

`app-mobile/` 작업 디렉토리에서 실행한다.

1. Flutter stable 채널 설치 → `flutter pub get`
2. `dart format --set-exit-if-changed lib test` (포맷 검사)
3. `flutter analyze`
4. `flutter test`

> 새 PHP 코드는 PHPStan level 6 통과 + 관련 PHPUnit 테스트가 그린이어야 배포 PR 의 CI 를 통과한다. 새 기능에는 `tests/` 테스트를 함께 작성한다. 로컬에서 동일 검증은 위 `.githooks/pre-push` 또는 `composer check` 로 직접 수행한다.

### self-hosted 러너에서 돈다

GitHub 호스팅 러너(`ubuntu-latest`)가 아니라 이 저장소 개발자의 로컬 Mac 을 self-hosted 러너로 등록해서 돈다(`AIFid`·`AIPicto` 저장소에서 이미 쓰던 패턴을 AICura 에도 적용).

- **러너 위치**: `~/actions-runners/AICura` (저장소 밖). `aicura-mac-local-runner` 라는 이름으로 launchd **LaunchAgent**(`~/Library/LaunchAgents/actions.runner.pushwing-AICura.aicura-mac-local-runner.plist`)로 상시 등록돼 있다 — sudo 불필요, Mac 로그인 세션이 켜져 있으면 자동으로 리스닝한다.
- **MySQL**: self-hosted macOS 러너는 `services:` 도커 컨테이너를 지원하지 않는다(Linux 러너 전용 기능). 대신 `backend` 잡에서 `docker run` 으로 직접 기동하고 `if: always()` 스텝으로 정리한다.
- **포트**: 호스트 포트를 고정하지 않고 `-p 127.0.0.1::3306` 로 Docker 가 빈 포트를 임의 할당하게 한다 — 같은 Mac 에서 상시 켜둔 로컬 개발용 MySQL, 그리고 `AIFid`·`AIPicto` 등 다른 저장소의 CI 컨테이너와 동시에 실행돼도 포트 충돌이 없다. 컨테이너명(`aicura-ci-mysql`)도 저장소별로 구분해 컨테이너 자체가 충돌하지 않게 한다.
- **macOS sed 문법**: 러너가 macOS 라 `sed` 가 BSD sed 다. `sed -i 's#...#...#' file`(GNU 문법)은 macOS 에서 에러가 나므로 `sed -i '' -e '...' file` 형태를 쓴다.
- **runner 관리**: `cd ~/actions-runners/AICura && ./svc.sh status|stop|start` 로 서비스 제어. 재등록이 필요하면 `gh api -X POST repos/pushwing/AICura/actions/runners/registration-token --jq .token` 으로 토큰을 발급받아 `./config.sh --url https://github.com/pushwing/AICura --token <TOKEN> --replace` 실행.
- **호스팅 러너로 되돌리려면**: `ci.yml` 의 `runs-on` 을 `ubuntu-latest` 로 바꾸고, `backend` 잡의 Docker 기동/정리 스텝을 `services: mysql: ...`(고정 포트 3306) 블록으로 되돌리면 된다.

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
