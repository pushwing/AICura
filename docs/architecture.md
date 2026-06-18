# AI Cura — 아키텍처 설계

## 전체 구조

```
외부 클라이언트 (앱/웹)
        │
        │ HTTPS · REST · JWT
        ▼
┌───────────────┐        ┌───────────────┐
│   API Server  │        │  Admin Panel  │
│  (CI4 → PHP)  │        │    (CI4)      │
└───────┬───────┘        └───────┬───────┘
        │                        │
        └──────────┬─────────────┘
                   │
            ┌──────▼──────┐
            │   Database  │
            │   (MySQL)   │
            └─────────────┘
```

---

## 컴포넌트별 역할

### API Server (`api/`)
- **대상**: 외부 앱·웹 클라이언트
- **방식**: REST API
- **인증**: JWT (Bearer Token)
- **초기 구현**: CodeIgniter 4
- **향후**: 트래픽·성능 이슈 발생 시 Pure PHP로 분리

### Admin Panel (`admin/`)
- **대상**: 내부 운영자
- **방식**: CI4 MVC (서버사이드 렌더링)
- **인증**: 세션 기반 (CI4 AuthFilter)
- **DB 접근**: 직접 쿼리 (API를 거치지 않음)

---

## 기술 스택

| 영역 | 기술 | 비고 |
|------|------|------|
| Admin | CodeIgniter 4 | MVC, 세션 인증 |
| API (초기) | CodeIgniter 4 | REST, JWT |
| API (분리 시) | Pure PHP | 성능 최적화 |
| DB | MySQL 8.0+ | |
| 인증 (API) | JWT (Bearer Token) | Access + Refresh Token |
| 서버 | PHP 8.1+ | |

---

## 디렉토리 구조

단일 CI4 설치로 Admin과 API를 함께 운영합니다.

```
AICura/
├── app/
│   ├── Controllers/
│   │   ├── Admin/          — 관리자 컨트롤러 (세션 인증)
│   │   │   ├── BaseAdminController.php
│   │   │   ├── AuthController.php
│   │   │   ├── DashboardController.php
│   │   │   ├── AdvertiserController.php
│   │   │   ├── CampaignController.php
│   │   │   ├── CreativeController.php
│   │   │   ├── ReportController.php
│   │   │   ├── UserController.php
│   │   │   └── SettingController.php
│   │   └── Api/
│   │       └── V1/         — REST API 컨트롤러 (JWT 인증)
│   │           ├── BaseApiController.php
│   │           ├── AuthController.php
│   │           ├── CampaignController.php
│   │           ├── CreativeController.php
│   │           ├── ReportController.php
│   │           └── UserController.php
│   ├── Models/             — Admin·API 공유 모델
│   ├── Filters/
│   │   ├── AdminAuthFilter.php   — 세션 인증 필터
│   │   └── JwtAuthFilter.php     — JWT 인증 필터
│   ├── Libraries/
│   │   └── JwtLibrary.php        — JWT 생성·검증
│   ├── Views/
│   │   └── admin/          — 관리자 뷰 (API는 JSON 응답, 뷰 없음)
│   └── Config/
│       ├── Routes.php      — /admin/* 및 /api/v1/* 라우트 분리
│       └── Filters.php     — admin_auth / jwt_auth 필터 등록
├── public/                 — 단일 진입점 (index.php)
├── assets/                 — 브랜드 에셋 (로고 등)
├── docs/                   — 프로젝트 문서
└── ui/                     — UI 컴포넌트
```

### 라우트 구조

| 경로 | 인증 | 처리 |
|------|------|------|
| `GET  /admin/login` | 없음 | 로그인 페이지 |
| `POST /admin/login` | 없음 | 로그인 처리 |
| `*    /admin/*` | `admin_auth` (세션) | 관리자 기능 |
| `POST /api/v1/auth/login` | 없음 | JWT 발급 |
| `POST /api/v1/auth/refresh` | 없음 | JWT 갱신 |
| `*    /api/v1/*` | `jwt_auth` (Bearer) | REST API |

---

## API 설계 원칙

### 인증 흐름

```
클라이언트                        API Server
    │                                 │
    │── POST /auth/login ────────────▶│
    │                                 │  credentials 검증
    │◀── { access_token,              │
    │       refresh_token } ──────────│
    │                                 │
    │── GET /campaigns ──────────────▶│
    │   Authorization: Bearer <token> │  JWT 검증
    │◀── 200 { data } ───────────────│
    │                                 │
    │── POST /auth/refresh ──────────▶│
    │   { refresh_token }             │  토큰 갱신
    │◀── { access_token } ───────────│
```

### 응답 포맷

```json
// 성공
{
  "status": "success",
  "data": { ... },
  "meta": { "page": 1, "total": 100 }
}

// 실패
{
  "status": "error",
  "code": "UNAUTHORIZED",
  "message": "인증이 필요합니다."
}
```

### 엔드포인트 네이밍

```
GET    /api/v1/campaigns          — 목록
GET    /api/v1/campaigns/{id}     — 단건
POST   /api/v1/campaigns          — 생성
PUT    /api/v1/campaigns/{id}     — 전체 수정
PATCH  /api/v1/campaigns/{id}     — 부분 수정
DELETE /api/v1/campaigns/{id}     — 삭제
```

---

## API Pure PHP 분리 기준 (ADR)

**현재**: CI4로 API·Admin 통합 개발 — 초기 속도 우선

**분리 트리거** (아래 중 하나 충족 시):
- API 평균 응답시간 > 200ms 지속
- 일 요청량 > 100만 건
- CI4 프레임워크 오버헤드가 병목으로 확인

**분리 전략**:
- DB 스키마·모델 로직은 동일하게 유지
- CI4 Controller → Pure PHP Router로 1:1 교체
- Admin은 CI4 유지 (성능 요구 낮음)
- API URL 구조 변경 없이 마이그레이션

---

## 보안 원칙

- JWT Secret은 `.env`에서만 관리, 코드에 하드코딩 금지
- Access Token 유효기간: 1시간
- Refresh Token 유효기간: 30일 (DB 저장, 폐기 가능)
- Admin은 CSRF 필터 활성화
- API는 CORS 허용 도메인 명시적 설정
- 모든 입력값 CI4 Validation 또는 PHP 자체 필터링 적용
