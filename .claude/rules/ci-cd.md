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

Linux 러너 전환 이후 `services:`(서비스 컨테이너)로 `mysql:8.0` 을 기동하고 다음 순서로 검증한다.

1. `services.mysql` 이 헬스체크(`mysqladmin ping`) 통과까지 대기한 뒤에야 스텝이 시작됨 — 호스트 포트는 고정하지 않고(`ports: [3306]`) 조직 공용 러너에서 동시에 도는 다른 저장소 CI 와의 충돌을 피한다. `${{ job.services.mysql.ports['3306'] }}` 로 실제 할당된 포트를 읽어 `$MYSQL_PORT` 에 저장
2. setup-php `8.5` (확장: `mbstring intl mysqli curl dom xml tokenizer`, 커버리지 `pcov`)
   - `phpunit.dist.xml` 이 `failOnWarning` + `<coverage>` 를 켜 두어 커버리지 드라이버 없으면 경고→실패 → `pcov` 필수
3. Composer 캐시 → `composer install`
4. `env` → `.env` 복사 후 CI용 DB(동적 포트 포함)·`JWT_SECRET` 주입
5. `writable/` 하위 디렉토리 생성 (git 미추적, `WRITEPATH` 보장)
6. `composer analyse` (PHPStan level 6)
7. `phpunit.dist.xml` 의 `database.tests.hostname`(`localhost`→`127.0.0.1`)·`database.tests.port`(`3306`→`$MYSQL_PORT`) 를 sed 로 치환
8. `composer test` (PHPUnit 단위·DB 통합)

서비스 컨테이너는 잡이 끝나면 GitHub Actions 가 자동으로 정리하므로 별도 cleanup 스텝이 없다.

### `app` 잡 — Flutter · analyze · test

`app-mobile/` 작업 디렉토리에서 실행한다.

1. Flutter stable 채널 설치 → `flutter pub get`
2. `dart format --set-exit-if-changed lib test` (포맷 검사)
3. `flutter analyze`
4. `flutter test`

> 새 PHP 코드는 PHPStan level 6 통과 + 관련 PHPUnit 테스트가 그린이어야 배포 PR 의 CI 를 통과한다. 새 기능에는 `tests/` 테스트를 함께 작성한다. 로컬에서 동일 검증은 위 `.githooks/pre-push` 또는 `composer check` 로 직접 수행한다.

### self-hosted 러너에서 돈다

GitHub 호스팅 러너(`ubuntu-latest`)가 아니라 **조직(`aivance-kr`) 공용 self-hosted 러너 1대**(Linux, X64)로 돈다. `ci.yml`·`deploy.yml` 모두 `runs-on: [self-hosted, Linux, X64]` 로 같은 러너를 공유한다. 계기는 GitHub 계정 결제 문제로 호스팅 러너 잡이 아예 시작되지 못하는 상태(`The job was not started because recent account payments have failed or your spending limit needs to be increased.`)를 실제 배포 PR(#216)에서 겪었기 때문이다.

> 이전에는 저장소별로 로컬 Mac 을 self-hosted 러너로 등록해 썼으나(`AIFid`·`AIPicto`·AICura 각자 launchd LaunchAgent), 이후 조직 계정(`aivance-kr`)의 **Linux 러너 1대를 여러 저장소가 공유**하는 구성으로 전환했다. 저장소 단위 등록이 아니라 **조직 단위 등록**이므로, 새 저장소를 추가해도 러너를 또 만들 필요는 없다 — 워크플로의 `runs-on` 라벨만 `[self-hosted, Linux, X64]` 로 맞추면 된다.

- **러너 관리**: GitHub Actions 러너 배포 스크립트의 `svc.sh` 는 OS를 자동 감지해 Linux 에서는 systemd 서비스로 등록한다(macOS 의 launchd 와 동일 인터페이스) — `./svc.sh status|stop|start` 로 제어. 정확한 설치 경로·서비스명은 러너를 등록한 인프라 담당자에게 확인한다(**확인 필요 — 문서에 반영 안 됨**).
- **MySQL**: Linux self-hosted 러너는 GitHub Actions `services:`(서비스 컨테이너)를 지원해 `backend` 잡에서 `docker run`/수동 정리 스텝을 걷어내고 `services.mysql` 로 옮겼다. 호스트 포트는 고정하지 않고(`ports: [3306]`) Docker가 빈 포트를 임의 할당하게 해, 러너 1대를 공유하는 다른 저장소 CI 컨테이너와 충돌하지 않는다 — 실제 포트는 `${{ job.services.mysql.ports['3306'] }}` 로 읽는다. 헬스체크(`mysqladmin ping`)가 끝나야 다음 스텝이 시작되므로 별도 polling 루프가 필요 없다.
- **sed 문법**: 러너가 Linux 라 `sed` 는 GNU sed 다. `sed -i -e '...' file` 형태를 쓴다(macOS BSD sed 의 `sed -i '' -e '...' file` 문법은 쓰지 않는다).
- **SSH 배포 스텝은 그대로 유지**: Linux 러너에서는 `appleboy/ssh-action`(Docker 컨테이너 액션) 도 쓸 수 있게 됐지만, 얻는 이득이 없고 `deploy.yml` 의 수동 ssh 방식은 pty 미할당 등 과거 사고(AIPicto)로 다져진 검증된 구현이라 **의도적으로 바꾸지 않았다**. 바꾸려면 pty·타임아웃 동작을 새 러너에서 별도로 검증해야 한다.
- **`run:` 안에서 heredoc 쓸 때 주의**: YAML block scalar(`|`)는 첫 내용 줄의 들여쓰기를 기준으로 "공통 들여쓰기만" 제거한다. `<<'EOF'` 같은 heredoc 종료 마커는 실제 실행되는 스크립트에서 다른 본문 줄과 정확히 같은 들여쓰기(따라서 YAML 이 벗겨낸 뒤엔 flush-left)여야 한다 — 한 칸이라도 어긋나면 heredoc 이 닫히지 않고 다음 스텝 내용까지 표준입력으로 삼켜 배포가 멈춘다.
- **재등록**: 조직 러너 재등록이 필요하면 `gh api -X POST orgs/aivance-kr/actions/runners/registration-token --jq .token` 으로 토큰을 발급받아 `./config.sh --url https://github.com/aivance-kr --token <TOKEN> --replace` 실행(저장소 단위가 아닌 **조직 단위 URL**).
- **호스팅 러너로 되돌리려면**: 결제 문제가 해결됐다면 `ci.yml`·`deploy.yml` 의 `runs-on` 을 `ubuntu-latest` 로 되돌린다(MySQL `services:`·SSH 스텝은 이미 호스팅 Linux 러너와도 호환되므로 그대로 둬도 된다).

## CD (배포)

`main` push(= `dev → main` PR 머지) 시 프로덕션 서버로 **SSH 자동 배포**된다. 정의: `.github/workflows/deploy.yml`.

- **트리거**: `main` push + `workflow_dispatch`(수동·롤백)
- **동시성**: `deploy-production` 그룹 — 배포 동시 실행 1개, `cancel-in-progress: false`
- **러너**: `[self-hosted, Linux, X64]` (ci.yml 과 동일 이유·동일 조직 러너). `appleboy/ssh-action` 을 쓸 수도 있지만, 검증된 기존 방식대로 러너 내장 `ssh` 클라이언트로 직접 접속하는 `run:` 스텝을 유지한다. `sudo -n systemctl reload apache2` 가 상시 프로세스라 pty 를 할당하면 SSH 세션이 끝나지 않으므로 `-t`/`-tt` 없이 접속한다.
- **대상**: Ubuntu + mod_php 아파치 단일 서버

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
