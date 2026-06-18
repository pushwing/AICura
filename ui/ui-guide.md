# AI Cura — UI 컴포넌트 가이드

> `aicura.css` 한 파일로 모든 컴포넌트를 사용할 수 있습니다.  
> 쇼케이스 페이지: `ui/components.html`

---

## 시작하기

```html
<link rel="stylesheet" href="aicura.css"/>
```

폰트는 별도 로드가 필요합니다.

```html
<!-- Pretendard (한글) -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/orioncactus/pretendard/dist/web/static/pretendard.css"/>
<!-- Inter (영문) -->
<link rel="preconnect" href="https://fonts.googleapis.com"/>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700&display=swap" rel="stylesheet"/>
```

---

## 버튼 (Button)

### 종류

```html
<button class="btn btn-primary">기본</button>
<button class="btn btn-secondary">세컨더리</button>
<button class="btn btn-outline">아웃라인</button>
<button class="btn btn-ghost">고스트</button>
<button class="btn btn-danger">삭제</button>
```

### 크기

```html
<button class="btn btn-primary btn-sm">소형</button>
<button class="btn btn-primary">기본</button>
<button class="btn btn-primary btn-lg">대형</button>
```

### 아이콘 포함 / 전체 너비 / 비활성

```html
<!-- 아이콘 포함 -->
<button class="btn btn-primary">
  <svg ...></svg>
  캠페인 추가
</button>

<!-- 전체 너비 -->
<button class="btn btn-primary btn-block">저장하기</button>

<!-- 비활성 -->
<button class="btn btn-primary" disabled>비활성</button>
```

| 클래스 | 설명 |
|--------|------|
| `btn` | 기본 (필수) |
| `btn-primary` | 메인 액션 |
| `btn-secondary` | 보조 액션 (연한 그린) |
| `btn-outline` | 테두리형 |
| `btn-ghost` | 약한 강조 |
| `btn-danger` | 삭제·위험 액션 |
| `btn-sm` | 소형 |
| `btn-lg` | 대형 |
| `btn-block` | 전체 너비 |

---

## 배지 (Badge)

```html
<span class="badge badge-success">활성</span>
<span class="badge badge-info">분석중</span>
<span class="badge badge-warning">검토 필요</span>
<span class="badge badge-danger">중단</span>
<span class="badge badge-neutral">초안</span>
```

### 점 인디케이터 포함

```html
<span class="badge badge-success badge-dot">활성</span>
```

| 클래스 | 색상 |
|--------|------|
| `badge-success` | 그린 |
| `badge-info` | 블루 |
| `badge-warning` | 앰버 |
| `badge-danger` | 레드 |
| `badge-neutral` | 그레이 |
| `badge-dot` | 왼쪽 점 인디케이터 추가 |

---

## 폼 (Form)

### 기본 구조

```html
<div class="form-group">
  <label class="form-label">
    레이블 <span class="required">*</span>
  </label>
  <input class="input" type="text" placeholder="입력하세요"/>
  <span class="form-hint">보조 설명 텍스트</span>
</div>
```

### Select

```html
<select class="select">
  <option value="">선택하세요</option>
  <option>옵션 1</option>
</select>
```

### Textarea

```html
<textarea class="textarea" placeholder="내용을 입력하세요."></textarea>
```

### 에러 상태

```html
<input class="input error" type="text" value="잘못된 값"/>
<span class="form-error">올바른 형식으로 입력해주세요.</span>
```

### 체크박스 / 라디오

```html
<div class="checkbox-group">
  <label class="checkbox-item">
    <input type="checkbox" checked/> 네이버 검색광고
  </label>
  <label class="checkbox-item">
    <input type="checkbox"/> 카카오 디스플레이
  </label>
</div>

<div class="radio-group">
  <label class="radio-item">
    <input type="radio" name="channel"/> CPC
  </label>
  <label class="radio-item">
    <input type="radio" name="channel"/> CPM
  </label>
</div>
```

---

## 카드 (Card)

### 기본 카드

```html
<div class="card">
  카드 내용
</div>
```

### 헤더·푸터 포함

```html
<div class="card">
  <div class="card-header">
    <span class="card-title">제목</span>
    <span class="badge badge-success">활성</span>
  </div>
  <p>본문 내용</p>
  <div class="card-footer flex justify-between items-center">
    <span class="text-sm text-muted">2026.06.01</span>
    <button class="btn btn-outline btn-sm">상세보기</button>
  </div>
</div>
```

### 지표 카드 (Metric Card)

```html
<div class="metric-card">
  <p class="metric-label">총 광고비</p>
  <p class="metric-value">₩2,840만</p>
  <p class="metric-delta up">▲ 18.4% 전월 대비</p>
</div>
```

`metric-delta`에 `up` 또는 `down` 클래스로 색상 자동 적용.

---

## 알림 (Alert)

```html
<div class="alert alert-success">
  <span class="alert-icon">✓</span>
  <div class="alert-body">
    <p class="alert-title">성공 제목</p>
    <p>설명 텍스트</p>
  </div>
</div>
```

| 클래스 | 색상 |
|--------|------|
| `alert-success` | 그린 |
| `alert-info` | 블루 |
| `alert-warning` | 앰버 |
| `alert-danger` | 레드 |

---

## 테이블 (Table)

```html
<div class="table-wrap">
  <table>
    <thead>
      <tr>
        <th>캠페인명</th>
        <th>상태</th>
        <th>광고비</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td>강남 리프팅 이벤트</td>
        <td><span class="badge badge-success badge-dot">활성</span></td>
        <td>₩4,200,000</td>
      </tr>
    </tbody>
  </table>
</div>
```

`table-wrap`이 overflow 처리 및 테두리·모서리를 담당합니다.

---

## 탭 (Tabs)

```html
<div class="tabs">
  <button class="tab active">전체</button>
  <button class="tab">활성 캠페인</button>
  <button class="tab">리포트</button>
</div>
```

활성 탭에 `active` 클래스를 JS로 토글합니다.

---

## 아바타 (Avatar)

```html
<div class="avatar avatar-sm">AI</div>
<div class="avatar avatar-md">김과</div>
<div class="avatar avatar-lg">이수</div>

<!-- 색상 변형 -->
<div class="avatar avatar-lg" style="background:#E6F1FB; color:#185FA5;">박부</div>
```

| 클래스 | 크기 |
|--------|------|
| `avatar-sm` | 28px |
| `avatar-md` | 36px |
| `avatar-lg` | 48px |

---

## 유틸리티 클래스

| 클래스 | 속성 |
|--------|------|
| `flex` | `display: flex` |
| `flex-col` | `flex-direction: column` |
| `items-center` | `align-items: center` |
| `justify-between` | `justify-content: space-between` |
| `gap-2` / `gap-3` / `gap-4` / `gap-6` | gap 8 / 12 / 16 / 24px |
| `mt-1` / `mt-2` / `mt-4` | margin-top 4 / 8 / 16px |
| `mb-4` | `margin-bottom: 16px` |
| `w-full` | `width: 100%` |
| `text-muted` | 보조 텍스트 색상 |
| `text-hint` | 힌트 텍스트 색상 |
| `text-primary` | 브랜드 그린 |
| `text-sm` | 13px |
| `text-xs` | 11px |

---

## CSS 변수 참조

```css
/* 브랜드 컬러 */
--color-primary:       #0F6E56;
--color-primary-hover: #0a5442;
--color-primary-light: #E1F5EE;
--color-secondary:     #1D9E75;
--color-accent-blue:   #185FA5;
--color-accent-blush:  #F4C0D1;
--color-navy:          #0F1923;

/* 배경 */
--color-bg:            #ffffff;
--color-bg-surface:    #F5F5F3;
--color-bg-muted:      #F0F0EE;

/* 텍스트 */
--color-text:          #0F1923;
--color-text-muted:    #6B7280;
--color-text-hint:     #9CA3AF;

/* 레이아웃 */
--radius-sm:   6px;
--radius-md:   8px;
--radius-lg:   12px;
--radius-full: 9999px;
```
