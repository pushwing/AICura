# TRD (Technical Requirements Document)
# AICura Admin — CI3 adminApi → CI4 뷰 렌더링 구조 전환

> 분석 대상: `source/adminApi` (CodeIgniter 3 REST API)  
> 전환 목표: `app/Controllers/Admin/` 기반 CI4 뷰 렌더링 어드민

---

## 1. 소스 분석 개요

### 1.1 CI3 adminApi 구조

```
source/adminApi/
├── application/
│   ├── controllers/v10/       # REST API 컨트롤러
│   ├── models/                # CI3 모델
│   ├── helpers/               # common, db, password 헬퍼
│   ├── hooks/                 # 인증 전처리 (preProcess.php)
│   └── config/
│       ├── routes.php         # 기본 라우트만 (REST 자동 라우팅)
│       └── constants.php      # 환경별 상수 (API URL, PG 키 등)
```

### 1.2 인증 방식 (CI3)

- REST 헤더 기반: `x-api-key`, `x-api-token`, `x-api-refreshtoken`, `x-api-userid`
- `preProcess.php` 훅에서 토큰 검증 후 컨트롤러에 `token`, `users_id`, `refreshToken` 주입
- 컨트롤러에서 `$this->common_m->checkToken($data)`로 권한 체크

### 1.3 광고 핵심 상수

| 분류 | 값 | 의미 |
|------|-----|------|
| adType | 1 | CPA (이벤트 신청) |
| adType | 2 | CPM (기간 노출) |
| adType | 3 | 프로모션 |
| adType | 4 | CPC |
| adType | 5 | 옵션 |
| user_type | 1 | 일반 사용자 |
| user_type | 2 | 운영자 |
| user_type | 3 | 광고주 (병원) |
| hospitalType | 1 | 일반 병원 |
| hospitalType | 2 | 네트워크 모병원 |
| hospitalType | 3 | 네트워크 자병원 |
| contractType | 1 | 신규 계약 |
| contractType | 2 | 재계약 |
| payType | 1 | 선불 |
| payType | 2 | 후불 |
| payment type | 1 | 가상계좌 |
| payment type | 2 | 신용카드 |
| costType | 1 | 숫자 가격 |
| costType | 2 | 텍스트 가격 |

---

## 2. 도메인 엔티티 매핑

### CI3 → CI4 컨트롤러 매핑

| CI3 컨트롤러 | 핵심 메서드 | CI4 Admin 컨트롤러 | 상태 |
|---|---|---|---|
| `Ads` | register, update, view, eventList, listCount, listAction, historyList, addPackage, packageList, viewPackage, updatePackage, historyMemo, tempList, delete, tempUpdate, tempDelete, tempView, history_view | `Admin\CampaignController` | Routes 정의됨, 구현 필요 |
| `ContractOrder` | register, getInfo, update, list, taxIssue, depositConfirmData, depositConfirm | `Admin\ContractController` | **신규 추가** |
| `Contract` | update, getContractInfo, getContractList, getContractOrderList, getDepositList, register, dashBoard, requestInspect | `Admin\ContractController` (통합) | **신규 추가** |
| `Advertiser` | update, getContractInfo, getContractList, getContractOrderList, getDepositList, register, dashBoard, requestInspect | `Admin\AdvertiserController` | Routes 정의됨, 구현 필요 |
| `Payment` | check, register, update, refund, process, cancel | `Admin\PaymentController` | **신규 추가** |
| `User` | list, getUserInfo | `Admin\UserController` | Routes 정의됨, 구현 필요 |
| `DashBoardEvent` | 집계 쿼리 | `Admin\DashboardController` | Routes 정의됨, 구현 필요 |
| `Sales` | list (스텁) | `Admin\ReportController` | Routes 정의됨, 구현 필요 |
| `Inspection` | 광고 심의 | CampaignController 하위 액션 | 통합 |
| `Board` / `AdvertiserBoard` | 게시판 | `Admin\BoardController` | 2차 대응 |
| `Screen` / `Service` | 미구현 스텁 | — | 추후 결정 |

---

## 3. CI4 Admin 디렉토리 구조

```
app/
├── Controllers/
│   └── Admin/
│       ├── BaseAdminController.php      # 기존 (render() 공통 제공)
│       ├── AuthController.php           # 세션 로그인/로그아웃
│       ├── DashboardController.php      # 기존 (KPI 집계 구현 필요)
│       ├── CampaignController.php       # 광고/캠페인 CRUD + 액션
│       ├── AdvertiserController.php     # 광고주 CRUD
│       ├── ContractController.php       # 수주계약·계약 CRUD  [신규]
│       ├── PaymentController.php        # 결제·환불             [신규]
│       ├── UserController.php           # 회원·운영자·광고주 조회
│       └── ReportController.php        # 매출·통계 리포트
│
├── Models/
│   ├── UserModel.php                    # 기존
│   ├── CampaignModel.php                # 광고 모델              [신규]
│   ├── AdvertiserModel.php              # 광고주 모델            [신규]
│   ├── ContractModel.php                # 계약·수주계약 모델     [신규]
│   └── PaymentModel.php                 # 결제 모델              [신규]
│
├── Views/
│   └── admin/
│       ├── layout/
│       │   ├── main.php                 # 공통 레이아웃 (사이드바, 헤더)
│       │   ├── head.php
│       │   └── sidebar.php
│       ├── dashboard/
│       │   └── index.php
│       ├── campaigns/
│       │   ├── index.php               # 목록 (AG Grid)
│       │   ├── show.php                # 상세
│       │   ├── form.php                # 등록/수정 폼
│       │   └── history.php            # 히스토리
│       ├── advertisers/
│       │   ├── index.php
│       │   ├── show.php
│       │   └── form.php
│       ├── contracts/
│       │   ├── index.php
│       │   ├── show.php
│       │   └── form.php
│       ├── payments/
│       │   ├── index.php
│       │   └── show.php
│       ├── users/
│       │   ├── index.php
│       │   └── show.php
│       └── reports/
│           ├── index.php
│           └── campaigns.php
│
└── Database/Migrations/
    ├── YYYY_MM_DD_000001_CreateCampaignsTable.php      [신규]
    ├── YYYY_MM_DD_000002_CreateAdvertisersTable.php    [신규]
    ├── YYYY_MM_DD_000003_CreateContractsTable.php      [신규]
    ├── YYYY_MM_DD_000004_CreateContractOrdersTable.php [신규]
    └── YYYY_MM_DD_000005_CreatePaymentsTable.php       [신규]
```

---

## 4. 라우트 설계

`app/Config/Routes.php` — `admin_auth` 필터 그룹 내

### 현재 정의된 라우트 (구현 필요)

```php
// 광고주
$routes->resource('advertisers', ['controller' => 'Admin\AdvertiserController']);

// 캠페인 (광고)
$routes->resource('campaigns', ['controller' => 'Admin\CampaignController']);

// 리포트
$routes->get('reports',           'Admin\ReportController::index');
$routes->get('reports/campaigns', 'Admin\ReportController::campaigns');

// 사용자
$routes->resource('users', ['controller' => 'Admin\UserController']);
```

### 신규 추가된 라우트 (구현 완료)

```php
// 계약 관리 — contracts(메인 1개) + contract_orders(추가계약건 N개)
$routes->get('contracts',                                'Admin\ContractController::index');
$routes->get('contracts/(:num)',                         'Admin\ContractController::show/$1');
$routes->get('contracts/orders',                         'Admin\ContractController::orders');
$routes->get('contracts/orders/new',                     'Admin\ContractController::orderNew');
$routes->post('contracts/orders',                        'Admin\ContractController::orderCreate');
$routes->get('contracts/orders/(:num)',                  'Admin\ContractController::orderShow/$1');
$routes->get('contracts/orders/(:num)/edit',             'Admin\ContractController::orderEdit/$1');
$routes->post('contracts/orders/(:num)',                 'Admin\ContractController::orderUpdate/$1');
$routes->post('contracts/orders/(:num)/deposit-confirm', 'Admin\ContractController::depositConfirm/$1');

// 결제
$routes->get('payments',          'Admin\PaymentController::index');
$routes->get('payments/(:num)',   'Admin\PaymentController::show/$1');
$routes->post('payments/(:num)/refund', 'Admin\PaymentController::refund/$1');

// 캠페인 추가 액션
$routes->post('campaigns/(:num)/action',  'Admin\CampaignController::action/$1');
$routes->get('campaigns/(:num)/history',  'Admin\CampaignController::history/$1');
$routes->get('campaigns/temp',            'Admin\CampaignController::tempList');
```

---

## 5. 모델 설계

### 5.1 CampaignModel

```php
// 테이블: campaigns (= 기존 ads 테이블 구조 기반)
protected $allowedFields = [
    'ad_title', 'hospital_id', 'hospital_type', 'ad_type',
    'ad_start_date', 'ad_end_date', 'cost_type', 'general_cost',
    'discount_cost', 'text_cost', 'db_cost', 'category',
    'exposure', 'contract_id', 'contract_order_id', 'region',
    'cooperation', 'keyword', 'button_name', 'button_link',
    'deliberation_code', 'status', 'channel', 'is_deleted',
];
```

**주요 쿼리 메서드:**
- `getCampaignList(array $params)` — 목록 (페이징, 검색, 상태 필터)
- `getCampaignDetail(int $id)` — 상세 (JOIN: hospital, contract, package)
- `updateStatus(int $id, string $action)` — 상태 변경 (승인/반려/종료)
- `getHistoryList(int $campaignId)` — 히스토리 목록
- `getPackageList(int $campaignId)` — 패키지 목록

### 5.2 ContractModel + ContractOrderModel (4-테이블 구조)

```
contracts (병원당 1개 메인 계약)
    └── contract_order_connects (매핑)
            └── contract_orders (추가계약건 N개: 100만원, 200만원 등)
                        └── deposits (원장: 충전/소진/이월 이력)
```

**ContractModel** (`app/Models/ContractModel.php`)
```php
// 테이블: contracts
protected $allowedFields = ['hospital_id', 'hospital_name', 'title', 'pay_type'];

// 주요 메서드
getListWithOrders(array $params)  // 계약 목록 + 수주건 집계
getDetail(int $id)                // 계약 상세 + 수주계약 목록
findByHospital(int $hospitalId)   // 병원 기준 메인 계약 조회
```

**ContractOrderModel** (`app/Models/ContractOrderModel.php`)
```php
// 테이블: contract_orders
protected $allowedFields = [
    'hospital_id', 'hospital_name', 'contract_type', 'ad_type', 'ad_type2',
    'ad_price', 'contract_status', 'deposit_date', 'parent_id',
    'agency_user_id', 'manage_user_id',
    'tax_charge_name', 'tax_charge_email', 'tax_business_no', 'tax_issue_date',
    'agency_company_name', 'agency_company_fee_rate', 'memo',
];

// 주요 메서드
getList(array $params)                              // 수주계약 목록 (검색·페이징)
getDetail(int $id)                                  // 상세 + 잔액 집계
getBalance(int $contractId, int $orderId): int      // 잔액 = 충전 - 소진
registerWithContract(array $data): int              // 신규/재계약 트랜잭션 등록
confirmDeposit(int $orderId, int $userId): bool     // 입금 확인 + deposit 입력
```

**재계약 흐름 (registerWithContract)**
1. `contract_orders` INSERT (parent_id = 이전 수주계약 id)
2. `contract_order_connects` INSERT (같은 contract_id에 매핑)
3. 이전 잔액 조회 → `deposits` status=11(이월소진) INSERT
4. 신규 계약에 → `deposits` status=12(이월충전) INSERT
5. 이전 수주계약 `contract_status` → 6(이월종료) UPDATE

### 5.3 AdvertiserModel

```php
// 테이블: advertisers (= 기존 hospital/advertiser 구조)
protected $allowedFields = [
    'hospital_id', 'hospital_name', 'contact_name',
    'contact_email', 'contact_phone', 'business_no',
    'is_network', 'network_parent_id', 'status',
];
```

### 5.4 PaymentModel

```php
// 테이블: payments (= 기존 payment 테이블 구조)
protected $allowedFields = [
    'user_id', 'hospital_id', 'contract_id', 'contract_order_id',
    'type', 'amount', 'result_code', 'trans_no', 'auth_date',
    'auth_no', 'fn_name', 'vbank_no', 'status',
];
```

---

## 6. DB 마이그레이션 설계

### campaigns (광고/이벤트)

| 컬럼 | 타입 | 설명 |
|------|------|------|
| id | bigint PK | |
| ad_title | varchar(255) | 광고 제목 |
| hospital_id | bigint FK | 병원 번호 |
| hospital_type | tinyint | 1 일반, 2 네트워크모, 3 네트워크자 |
| ad_type | tinyint | 1 CPA, 2 CPM, 3 프로모션, 4 CPC, 5 옵션 |
| ad_start_date | date | |
| ad_end_date | date | |
| cost_type | tinyint | 1 숫자, 2 텍스트 |
| general_cost | int | 정상가 |
| discount_cost | int | 할인가 |
| text_cost | varchar(100) | 텍스트 가격 |
| db_cost | int | DB 단가 |
| category | int | 소 카테고리 번호 |
| exposure | tinyint | 1 이벤트, 2 병원상세, 3 둘다 |
| contract_id | bigint FK | 계약 번호 |
| contract_order_id | bigint FK | 수주계약 번호 |
| region | varchar(100) | 노출지역 (1,2,3 형태) |
| keyword | varchar(500) | 검색키워드 |
| deliberation_code | varchar(100) | 의료심의번호 |
| status | varchar(20) | pending/active/rejected/ended |
| channel | tinyint | 1 굿닥, 2 굿닥파트너스 |
| is_deleted | tinyint | 소프트 삭제 |
| created_at | datetime | |
| updated_at | datetime | |

**인덱스:** `idx_campaigns_status`, `idx_campaigns_hospital_id`, `idx_campaigns_ad_type`

### contract_orders (수주계약)

| 컬럼 | 타입 | 설명 |
|------|------|------|
| id | bigint PK | |
| hospital_id | bigint | 병원 번호 |
| hospital_name | varchar(255) | |
| hospital_type | tinyint | 1 병원, 2 약국, 3 업체 |
| is_network | tinyint | 0 일반, 1 모병원, 2 자병원 |
| contract_date | date | 계약일 |
| contract_type | tinyint | 1 신규, 2 재계약 |
| title | varchar(255) | 수주계약 제목 |
| ad_type | tinyint | 계약방식 |
| start_date | date | |
| end_date | date | |
| amount | bigint | 계약금액 |
| pay_type | tinyint | 1 선불, 2 후불 |
| status | varchar(20) | |
| user_id | bigint | 담당 운영자 |
| created_at | datetime | |
| updated_at | datetime | |

### payments (결제)

| 컬럼 | 타입 | 설명 |
|------|------|------|
| id | bigint PK | |
| user_id | bigint | 운영자 번호 |
| hospital_id | bigint | 병원 번호 |
| contract_id | bigint | 계약 번호 |
| contract_order_id | bigint | 수주계약 번호 |
| type | tinyint | 1 가상계좌, 2 신용카드 |
| amount | bigint | 결제금액 |
| result_code | varchar(10) | 세틀뱅크 결과코드 |
| trans_no | varchar(100) | 세틀뱅크 거래번호 |
| auth_date | varchar(20) | 승인일시 |
| auth_no | varchar(50) | 승인번호 |
| fn_name | varchar(100) | 금융사명 |
| vbank_no | varchar(50) | 가상계좌번호 |
| vbank_expire | varchar(20) | 가상계좌 만료일 |
| status | varchar(20) | pending/paid/refunded/failed |
| created_at | datetime | |
| updated_at | datetime | |

---

## 7. 뷰 렌더링 패턴

CI3 API 응답 → CI4 뷰 렌더링 변환 원칙:

```php
// CI3 방식 (API 응답)
$this->response([
    'status' => 'success',
    'code'   => '200',
    'result' => $data,
], 200);

// CI4 방식 (뷰 렌더링)
return $this->render('admin/campaigns/index', [
    'campaigns' => $campaigns,
    'pager'     => $pager,
    'filters'   => $filters,
]);
```

- 목록 화면: **AG Grid** (`ag-theme-alpine`)
- 차트/통계: **Chart.js** (Primary `#0F6E56`, Secondary `#1D9E75`)
- 리치 텍스트: **TinyMCE** (광고 상세 편집)
- 레이아웃: `$this->render()` 반드시 사용 (`authUser` 자동 주입)

---

## 8. 구현 우선순위

| 순서 | 작업 | 비고 |
|------|------|------|
| 1 | DB 마이그레이션 생성 | campaigns, advertisers, contract_orders, payments |
| 2 | Admin 공통 레이아웃 뷰 | sidebar, header, aicura.css 적용 |
| 3 | AuthController (로그인) | 세션 기반, AdminAuthFilter 연동 |
| 4 | DashboardController | KPI 집계 (광고수, 계약수, 매출) |
| 5 | CampaignController + CampaignModel | 핵심 도메인, Ads.php 로직 이식 |
| 6 | AdvertiserController + AdvertiserModel | |
| 7 | ContractController + ContractModel | ContractOrder + Contract 통합 |
| 8 | PaymentController + PaymentModel | 결제 목록·환불 (PG 연동 별도) |
| 9 | UserController | 회원/운영자/광고주 조회 |
| 10 | ReportController | 매출 집계, Chart.js 시각화 |

---

## 9. 보안 체크리스트 (CI3 → CI4 전환 시)

- [ ] `$_GET` / `$_POST` 직접 사용 → `$this->request->getPost()` 로 교체
- [ ] `common_m->checkToken()` JWT 인증 → `AdminAuthFilter` 세션 인증으로 교체
- [ ] 뷰 출력 시 `esc()` 누락 없이 적용
- [ ] 모든 폼에 `csrf_field()` 추가
- [ ] SQL Query Builder 사용 (raw query 금지)
- [ ] 비밀번호 `md5()` → `password_hash()` 교체
- [ ] PG 키, S3 키 등 하드코딩 상수 → `.env` 이관

---

*작성일: 2026-06-18*
