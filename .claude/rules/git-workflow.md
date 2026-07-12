# Git 워크플로우

```
feature/* → (PR) → dev → (PR) → main
```

- **PR 대상**: `feature/*` → `dev`
- **배포**: `dev` → `main` PR
- **머지 방식**:
  - `feature/*` → `dev`: **Squash and merge**
  - `dev` → `main`(배포): **Merge commit** (⚠️ Squash 금지 — 아래 주의)
- **머지 후**: `feature/*` 브랜치 자동 삭제
- `main`과 `dev`에 직접 push 금지 (아래 **문서 전용 작업 예외** 제외)

## 문서 전용 작업 예외

코드 변경 없이 **문서만** 수정하는 경우(`docs:` 커밋 — `.md` 파일, `docs/`, `CLAUDE.md`·`.claude/rules/` 등)는
feature 브랜치·PR 절차를 생략하고 **`dev` 에 직접 커밋·푸시**한다.

- 적용 조건: 변경 파일이 전부 문서(코드·설정·마이그레이션 변경 없음)일 때만
- 커밋 접두어는 `docs:` 사용
- 코드가 한 줄이라도 섞이면 예외 아님 → 반드시 `feature/*` → PR 절차를 따른다
- `main` 직접 push 금지는 예외 없이 유지 (배포는 항상 `dev → main` PR)

> ⚠️ **`dev → main` 배포 PR 을 Squash 로 머지하면 안 된다.** Squash 는 `dev` 커밋들을
> 새 커밋 하나로 눌러 `main` 을 `dev` 의 조상에서 이탈시킨다. 그러면 다음 배포마다
> `deploy.yml` 등에서 3-way 충돌이 재발한다. **반드시 merge commit** 으로 머지해
> `main` 이 `dev` 의 조상으로 유지되게 한다(배포 = fast-forward → 무충돌).

## 기능 개발 시작

```bash
git checkout dev
git pull origin dev
git checkout -b feature/기능명   # 예: feature/campaign-crud
```

## dev가 앞서간 경우 rebase

```bash
git rebase origin/dev
git push --force-with-lease origin feature/기능명
```

## 커밋 메시지 (Conventional Commits)

| 접두어 | 용도 |
|--------|------|
| `feat` | 새 기능 |
| `fix` | 버그 수정 |
| `refactor` | 리팩토링 |
| `docs` | 문서 |
| `chore` | 설정·빌드 |
| `test` | 테스트 |

자세한 내용: `docs/git-workflow.md`
