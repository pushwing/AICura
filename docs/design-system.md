# AI Cura 디자인 시스템

## 로고

### 파일 구성

| 파일 | 용도 |
|------|------|
| `assets/logo/logo.svg` | 기본형 — 라이트 배경용 |
| `assets/logo/logo-dark.svg` | 다크 배경용 |
| `assets/logo/logo-color-bg.svg` | 컬러(그린) 배경용 |
| `assets/logo/icon.svg` | 앱 아이콘 / 파비콘 원본 (64×64) |
| `public/favicon.ico` | 브라우저 탭 파비콘 (16/32/48 멀티 해상도) |
| `public/assets/favicon/*.png` | apple-touch-icon(180)·android-chrome(192/512) 등 파생 아이콘 |
| `public/site.webmanifest` | PWA 매니페스트 (android-chrome 아이콘 참조) |

### 로고 구조

- **마크**: 다이아몬드(Diamond) — 정제·정확성을 상징하며 의료·케어 아이덴티티와 연결
- **워드마크**: `AI` (Bold 700) + `Cura` (Regular 400)
- **마크 모서리**: `rx="8"` (rounded square)

### 사용 규칙

- 로고 최소 크기: 높이 24px 이상
- 마크와 워드마크 사이 여백: 마크 너비의 약 37%
- 배경색에 따라 버전 선택 (라이트/다크/컬러)
- 로고 색상 임의 변경 금지

---

## 컬러 시스템

### 브랜드 컬러

| 이름 | Hex | 용도 |
|------|-----|------|
| Cura Green | `#0F6E56` | Primary — 마크, 주요 버튼, 강조 |
| Cura Teal | `#1D9E75` | Secondary — 다크모드 마크, 호버 |
| Mint | `#E1F5EE` | Background tint, 배지 배경 |
| Deep Navy | `#0F1923` | 다크 배경, 워드마크(라이트) |
| Cura Blue | `#185FA5` | 데이터·분석 Accent |
| Blush | `#F4C0D1` | 성형·뷰티 포인트 |
| Warm White | `#F5F5F3` | Surface, 카드 배경 |

### 시맨틱 컬러

| 상태 | 색상 |
|------|------|
| Success | `#0F6E56` (Cura Green) |
| Info | `#185FA5` (Cura Blue) |
| Warning | `#BA7517` |
| Danger | `#A32D2D` |

---

## 타이포그래피

### 폰트 패밀리

- **한글**: Pretendard
- **영문**: Inter
- **Fallback**: `system-ui, sans-serif`

### 스케일

| 용도 | 크기 | 굵기 |
|------|------|------|
| 헤드라인 | 28–32px | 700 |
| 서브헤드 | 18–22px | 500 |
| 본문 | 14–16px | 400 |
| 캡션 | 12–13px | 400 |
| 배지·라벨 | 11–12px | 500 |

---

## 컴포넌트 원칙

- **Flat UI**: 그라디언트·그림자 없음
- **테두리**: `0.5px solid` (강조 시 `2px solid`)
- **모서리**: `border-radius: 8px` (기본) / `12px` (카드)
- **간격**: 8 · 12 · 16 · 24 · 32px 기준
- **배지**: `border-radius: 999px` (pill)

---

## UI 스타일 키워드

- **Clean Medical** — 의료 신뢰감, 여백 중심
- **Data-driven** — 숫자·지표 중심 대시보드
- **Beauty Premium** — 성형·뷰티 감성 포인트
- **Trust & Care** — 케어 중심 브랜드 톤

---

## 슬로건

> **"광고, 처방하다"**

| 슬로건 | 톤 |
|--------|-----|
| 광고, 처방하다 | 강렬·심플 (메인) |
| AI가 진단하는 당신의 광고 | 설명형 |
| The Ad Platform That Cares | 영문·글로벌 |
| Cura for Every Click | 퍼포먼스 마케팅 |
