# Git 워크플로우

## 브랜치 구조

```
main   ─── 운영 배포 (Production)
  └── dev  ─── 통합 개발 (Staging)
        └── feature/* ─── 기능 개발
```

| 브랜치 | 역할 | PR 대상 |
|--------|------|---------|
| `main` | 운영 배포 | — |
| `dev` | 통합·스테이징 | `main` |
| `feature/*` | 기능 개발 | `dev` |

---

## 개발 흐름

### 1. 기능 개발 시작

```bash
git checkout dev
git pull origin dev
git checkout -b feature/기능명
```

**브랜치명 예시**
- `feature/campaign-crud`
- `feature/jwt-auth`
- `feature/swagger-setup`
- `feature/admin-dashboard`

### 2. 개발 및 커밋

```bash
git add .
git commit -m "feat: 캠페인 CRUD API 구현"
git push origin feature/기능명
```

커밋 메시지는 Conventional Commits를 따릅니다.

| 접두어 | 용도 |
|--------|------|
| `feat` | 새 기능 |
| `fix` | 버그 수정 |
| `refactor` | 리팩토링 |
| `docs` | 문서 |
| `chore` | 설정·빌드 |
| `test` | 테스트 |

### 3. feature → dev PR

```bash
gh pr create --base dev --head feature/기능명 \
  --title "feat: 기능명" \
  --body "## 작업 내용\n- ...\n\n## 체크리스트\n- [ ] 테스트 확인\n- [ ] 문서 업데이트"
```

- PR 머지 방식: **Squash and merge**
- 머지 완료 시 `feature/*` 브랜치 자동 삭제

### 4. dev → main PR (배포)

```bash
git checkout dev
git pull origin dev
gh pr create --base main --head dev \
  --title "release: YYYY-MM-DD 배포" \
  --body "## 변경 사항\n- ..."
```

- PR 머지 후 `main` 태그 생성 권장: `git tag v1.x.x`

> ⚠️ **`dev → main` 배포 PR 은 반드시 "Create a merge commit" 로 머지한다 (Squash 금지).**
> Squash 로 머지하면 `dev` 의 커밋들이 하나로 눌려 `main` 이 `dev` 의 조상에서 이탈한다.
> 그러면 공통 조상이 과거에 멈춰, 다음 배포마다 그동안 수정된 파일(특히 `deploy.yml`)에서
> 3-way 충돌이 재발한다. merge commit 으로 머지하면 `main` 이 `dev` 의 조상으로 유지돼
> 배포가 fast-forward 가 되고 충돌이 발생하지 않는다.
> (`feature/* → dev` PR 은 기존대로 Squash and merge 를 사용한다.)

---

## GitHub 설정 현황

| 항목 | 설정값 |
|------|--------|
| Default branch | `dev` |
| Merge 방식 | `feature/* → dev`: Squash and merge / `dev → main`: **Merge commit** |
| 머지 후 브랜치 자동 삭제 | 활성화 |
| 브랜치 보호 | 수동 관리 (GitHub Free) |

---

## 주의사항

- `main`에 직접 push 금지 — 반드시 PR을 통해 반영
- `dev`에 직접 push 금지 — `feature/*` 브랜치에서 PR
- `feature/*` 브랜치는 항상 최신 `dev`에서 분기
- `dev`가 많이 앞서간 경우 rebase 후 PR

```bash
git checkout feature/기능명
git rebase origin/dev
git push --force-with-lease origin feature/기능명
```
