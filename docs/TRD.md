# TRD (Technical Requirements Document)
# AICura Admin — CI3 adminApi → CI4 뷰 렌더링 구조 전환

> 분석 대상: `source/adminApi` (CodeIgniter 3 REST API) + `event_v2_ERD.mwb` (MySQL Workbench ERD)
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
| `Ads` | register, update, view, eventList, listCount, listAction, historyList, addPackage, packageList, viewPackage, updatePackage, historyMemo, tempList, delete, tempUpdate, tempDelete, tempView, history_view | `Admin\CampaignController` | 구현 중 |
| `ContractOrder` | register, getInfo, update, list, taxIssue, depositConfirmData, depositConfirm | `Admin\ContractController` | 구현 중 |
| `Contract` | update, getContractInfo, getContractList, getContractOrderList, getDepositList, register, dashBoard, requestInspect | `Admin\ContractController` (통합) | 구현 중 |
| `Advertiser` | update, getContractInfo, getContractList, getContractOrderList, getDepositList, register, dashBoard, requestInspect | `Admin\AdvertiserController` | 구현 중 |
| `Payment` | check, register, update, refund, process, cancel | `Admin\PaymentController` | 구현 중 |
| `User` | list, getUserInfo | `Admin\UserController` | 구현 중 |
| `DashBoardEvent` | 집계 쿼리 | `Admin\DashboardController` | 구현 중 |
| `Sales` | list (스텁) | `Admin\ReportController` | 구현 중 |
| `Inspection` | 광고 심의 | `Admin\ReviewController` | 구현 완료 |
| `Board` / `AdvertiserBoard` | 게시판 | `Admin\BoardController` | 2차 대응 |

---

## 3. CI4 Admin 디렉토리 구조

```
app/
├── Controllers/
│   └── Admin/
│       ├── BaseAdminController.php      # render() 공통 제공
│       ├── AuthController.php           # 세션 로그인/로그아웃
│       ├── DashboardController.php      # KPI 집계
│       ├── CampaignController.php       # 광고/캠페인 CRUD + 액션
│       ├── AdvertiserController.php     # 광고주 CRUD
│       ├── ContractController.php       # 수주계약·계약 CRUD
│       ├── PaymentController.php        # 결제·환불
│       ├── ReviewController.php         # 캠페인 검수 (구현 완료)
│       ├── CreativeController.php       # 소재(이미지) 관리
│       ├── ReportController.php         # 매출·통계 리포트
│       └── UserController.php           # 회원·운영자·광고주 조회
│
├── Models/
│   ├── UserModel.php
│   ├── CampaignModel.php
│   ├── CampaignReviewRequestModel.php   # 검수 요청
│   ├── AdvertiserModel.php
│   ├── ContractModel.php
│   └── PaymentModel.php
│
└── Database/Migrations/
    ├── 2026_06_18_000001~000012  # 핵심 도메인 테이블
    ├── 2026_06_19_000013~000016  # 소재·검수 테이블
    └── 2026_06_19_000017~000026  # ERD 기반 확장 (보강)
```

---

## 4. 라우트 설계

```php
// 광고주
$routes->resource('advertisers', ['controller' => 'Admin\AdvertiserController']);

// 캠페인 (광고)
$routes->resource('campaigns', ['controller' => 'Admin\CampaignController']);
$routes->post('campaigns/(:num)/action',  'Admin\CampaignController::action/$1');
$routes->get('campaigns/(:num)/history',  'Admin\CampaignController::history/$1');
$routes->get('campaigns/temp',            'Admin\CampaignController::tempList');

// 검수
$routes->get('reviews',              'Admin\ReviewController::index');
$routes->get('reviews/(:num)',       'Admin\ReviewController::show/$1');
$routes->post('reviews/(:num)/approve', 'Admin\ReviewController::approve/$1');
$routes->post('reviews/(:num)/reject',  'Admin\ReviewController::reject/$1');

// 계약
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
$routes->get('payments',                   'Admin\PaymentController::index');
$routes->get('payments/(:num)',             'Admin\PaymentController::show/$1');
$routes->post('payments/(:num)/refund',     'Admin\PaymentController::refund/$1');

// 리포트
$routes->get('reports',           'Admin\ReportController::index');
$routes->get('reports/campaigns', 'Admin\ReportController::campaigns');
```

---

## 5. DB 마이그레이션 — 테이블 전체 목록

### 5.1 ERD → CI4 테이블명 매핑

| ERD 원본 (legacy) | CI4 마이그레이션 테이블 | 설명 |
|---|---|---|
| `ads` | `campaigns` | 광고/캠페인 현행 데이터 |
| `ads_temporary` | `campaign_temps` | 임시저장 |
| `ads_history` | `campaign_histories` | 상태 변경 이력 |
| `ads_main` | `ad_mains` | 병원-캠페인 단위 메인 |
| `ads_main_map` | `ad_main_maps` | 소재 버전 매핑 |
| `ads_package` | `campaign_packages` | 기획전 |
| `ads_package_map` | — | campaign_packages에 통합 |
| `ads_recommend_map` | `ad_recommend_maps` | 추천 이벤트 |
| `inspecting_ads` | `campaign_review_requests` | 검수 요청 |
| `contract` | `contracts` | 병원 메인 계약 |
| `contract_order` | `contract_orders` | 수주 계약 (N개) |
| `contract_order_connect` | `contract_order_connects` | 계약-수주 매핑 |
| `deposit` | `deposits` | 잔액 원장 |
| `payment` | `payments` | 결제 |
| `refund` | `refunds` | 환불 |
| `call_request` | `call_requests` | 이벤트 신청 DB |
| `call_memo` | `call_memos` | 신청 메모 |
| `booking` | `bookings` | 예약 |
| `board` | `boards` | 후기/게시판 |
| `board_comments` | `board_comments` | 댓글 |
| `board_files` | `board_files` | 첨부파일 |
| `board_estimation` | `board_estimations` | 좋아요/신고 |
| `board_rank` | `board_ranks` | 정렬 차수 |
| `board_summary` | `board_summaries` | 설문·별점 집계 |
| `board_tags` | `board_tags` | 태그 |
| `advertiser_board` | `advertiser_boards` | 광고주 게시판 |
| `advertiser_board_comments` | `advertiser_board_comments` | |
| `advertiser_board_files` | `advertiser_board_files` | |
| `black_list` | `black_lists` | 사용자 블랙리스트 |
| `event_categories` | `event_categories` | 광고 카테고리 |
| `mapping_category` | `mapping_categories` | v1→v2 카테고리 매핑 |
| `code` | `codes` | 공통 코드 |
| `memo` | `memos` | 영업·원장 메모 |
| `user_action` | `user_actions` | 앱 사용자 포인트 |
| `users` (admin) | `users` | 어드민/영업 사용자 |
| `hospitals` | `hospitals` | 병원 |
| `advertisers` | `advertisers` | 광고주 |

---

### 5.2 핵심 도메인 테이블 스키마

#### campaigns (광고/캠페인)

| 컬럼 | 타입 | 설명 |
|------|------|------|
| id | bigint PK | |
| ad_title | varchar(255) | 광고 제목 |
| hospital_id | bigint | 병원 번호 |
| hospital_type | tinyint | 1 일반, 2 네트워크모, 3 네트워크자 |
| agency_user_id | int | 영업 담당자 번호 |
| user_id | int | 등록자 번호 |
| ad_type | tinyint | 1 CPA, 2 CPM, 3 프로모션, 4 CPC, 5 옵션 |
| ad_start_date | date | |
| ad_end_date | date | |
| ad_date_extend | tinyint | 기간 연장 여부 |
| cost_type | tinyint | 1 숫자, 2 텍스트 |
| general_cost | int | 정상가 |
| discount_cost | int | 할인가 |
| text_cost | varchar(100) | 텍스트 가격 |
| db_cost | int | DB 단가 (CPA) |
| category | int | 소 카테고리 번호 |
| exposure | tinyint | 1 이벤트존, 2 병원상세, 3 둘다 |
| where_image | varchar(20) | 이미지 노출 유형 (다중: 1,2,3) |
| model_image_count | tinyint | 0~2 |
| ad_detail_info | text | 신청버튼 설정 JSON |
| inspect_date | date | 검수일 |
| is_view_board | tinyint | 1 후기노출, 2 미노출 |
| deliberation_code | varchar(100) | 의료심의번호 |
| custom_randing | varchar(10) | 커스텀랜딩 (다중) |
| option_ad_id | varchar(50) | 옵션이벤트번호 (다중) |
| custom1/2/3 | varchar(50) | 커스텀랜딩 병원사업자정보 |
| region | varchar(100) | 노출지역 |
| cooperation | varchar(50) | 제휴매체 (다중) |
| keyword | varchar(500) | 검색키워드 |
| sub_hospital_id | varchar(100) | 네트워크 자병원 ID |
| contract_id | bigint | 계약 번호 |
| contract_order_id | bigint | 수주계약 번호 |
| contract_name | varchar(200) | 계약명 (캐시) |
| t1_image_name | varchar(500) | 썸네일1 |
| t2_image_name | varchar(500) | 썸네일2 |
| d1~d6_image_name | varchar(500) | 상세이미지 |
| status | varchar(20) | pending/active/rejected/ended |
| channel | tinyint | 1 굿닥, 2 굿닥파트너스 |
| is_deleted | tinyint | 소프트 삭제 |
| del_date | datetime | 삭제 일시 |
| delete_user_id | int | 삭제 처리자 |
| created_at / updated_at | datetime | |

**인덱스:** hospital_id, agency_user_id, contract_order_id, (is_deleted, status), ad_type

#### contracts (메인 계약)

| 컬럼 | 타입 | 설명 |
|------|------|------|
| id | bigint PK | |
| hospital_id | bigint | 병원 번호 |
| hospital_name | varchar(255) | |
| hospital_type | tinyint | 1 병원, 2 약국, 3 업체 |
| title | varchar(255) | 계약명 |
| contract_date | date | 계약일 |
| ad_type / ad_type2 | tinyint | 광고 유형 |
| main_contract | text | 계약 본문 |
| pay_type | tinyint | 1 선불, 2 후불 |
| use_status | tinyint | 1 진행, 2 종료 |
| is_deleted | tinyint | |
| agency_user_id | int | 영업 담당자 |
| manage_user_id | int | 관리 담당자 |
| agency_company_id | int | 대행사 번호 |
| agency_company_name | varchar(100) | 대행사명 |
| hospital_charge_name/phone/email | varchar | 병원 담당자 연락처 |
| tax_charge_name / tax_business_no / tax_charge_email | varchar | 세금계산서 정보 |
| created_at / updated_at | datetime | |

#### contract_orders (수주계약)

| 컬럼 | 타입 | 설명 |
|------|------|------|
| id | bigint PK | |
| parent_id | bigint | 이전 수주계약 ID (재계약) |
| title | varchar(100) | 수주계약명 |
| contract_date | date | 계약일 |
| contract_agree_date | int | 광고주 동의 일시 (Unix ts) |
| agree | tinyint | 0 동의전, 1 동의, 2 취소 |
| contract_type | tinyint | 1 신규, 2 재계약 |
| contract_status | tinyint | 1 정상, 2 발행환불, 3 발행취소, 4 계약취소, 5 계약환불, 6 이월종료 |
| ad_type / ad_type2 | tinyint | |
| ad_price | bigint | 계약금액 |
| agency_company_fee_rate | decimal(5,2) | 대행수수료 % |
| ads_count / ads_count_bonus | smallint/tinyint | 이벤트 수량 |
| pay_method | tinyint | 지불방법 |
| main_contract | text | 계약 본문 |
| deposit_date | datetime | 입금 확인 일시 |
| deposit_check_id | int | 입금확인 ID |
| is_delete | tinyint | |
| purchase_owner_id | int | 차감 병원 (네트워크) |
| is_network | tinyint | 0 일반, 1 모병원, 2 자병원 |
| hospital_id | bigint | |
| hospital_name / hospital_type | | |
| hospital_charge_name/phone/email | varchar | |
| agency_user_id / manage_user_id | bigint | |
| agency_company_id / agency_company_name | | |
| agency_company_charge_name/phone/email | varchar | 대행사 담당자 |
| tax_charge_name / tax_business_no / tax_charge_email | varchar | |
| tax_issue_date / tax_issue_request_date | date | 세금계산서 발행 |
| memo | text | |
| created_at / updated_at | datetime | |

#### deposits (잔액 원장)

| 컬럼 | 타입 | 설명 |
|------|------|------|
| id | bigint PK | |
| contract_id | bigint | |
| contract_order_id | bigint | |
| status | tinyint | 2 계약충전, 3 DB소진, 4 기타충전, 5 기타차감, 6 발행환불, 7 계약환불, 9 발행취소, 10 계약취소, 11 이월소진, 12 이월충전 |
| is_minus | tinyint | 0 양수, 1 음수 |
| price | bigint | 금액 |
| users_id | bigint | 등록자 |
| note | text | 메모 |
| created_at / updated_at | datetime | |

#### call_requests (이벤트 신청 DB)

| 컬럼 | 타입 | 설명 |
|------|------|------|
| id | bigint PK | |
| hospital_id | int | |
| campaign_id | int | |
| user_id | int | |
| device | tinyint | 1 안드, 2 iOS, 3 웹 |
| status | tinyint | 1 미확인, 2 부재중, 3 취소, 4 기타, 5 예약, 6 예약취소, 7 내원완료, 8 중복, 9 결번 |
| confirm_date | datetime | 확인 일시 |
| name / phone / content | varchar/text | 신청자 정보 |
| call_time | varchar(100) | 통화 가능 시간 |
| age / sex | int/tinyint | 생년/성별 |
| privacy_agree / supply_third_party_agree | tinyint | 동의 여부 |
| event_cost | int | 이벤트 단가 (CPA 과금 기준) |
| funnel / region / finger_print | varchar | |
| is_delete | tinyint | |
| parent_id | int | 중복 신청 연결 |
| created_at / updated_at | datetime | |

**인덱스:** hospital_id, campaign_id, user_id, status, created_at

#### boards (후기/게시판)

| 컬럼 | 타입 | 설명 |
|------|------|------|
| id | bigint PK | |
| user_id | bigint | (웹 비회원: 2로 시작 11자리) |
| type | tinyint | 1 이벤트, 2 병원, 3 접수 |
| target_id | int | 이벤트/병원 번호 |
| subject / contents | varchar/text | |
| rate_sum / rate1~3 | float | 별점 |
| survey_type / survey1~6 | tinyint/varchar | 설문 |
| is_delete | tinyint | 0 미삭제, 1 임시삭제, 2 완전삭제 |
| call_request_id | int | 신청 DB 연결 |
| not_event_user | tinyint | 1 비신청 후기 |
| device | tinyint | |
| created_at / updated_at | datetime | |

---

### 5.3 광고 소재 구조 (ad_mains → ad_main_maps → campaigns)

```
ad_mains (병원-캠페인 단위, 1개)
  └── ad_main_maps (소재 버전 히스토리, N개)
          └── campaigns (각 버전 콘텐츠)
                  └── campaign_review_requests (검수 요청)
```

- `ad_mains.campaign_id` → 최초 생성된 캠페인 참조
- `ad_main_maps.is_main` → 현재 노출 중인 소재 버전 식별
- `ad_main_maps.is_inspect` → 1 검수완료, 2 미검수
- 검수 승인 시 `campaigns.status = 'active'` 전환

---

### 5.4 계약-수주계약-잔액 흐름

```
contracts (병원당 메인 1개)
  └── contract_order_connects (1:N 매핑)
          └── contract_orders (수주계약 N개: 100만, 200만 등)
                      └── deposits (원장: 충전/소진/이월 이력)
```

**재계약 트랜잭션 순서:**
1. `contract_orders` INSERT (parent_id = 이전 수주계약 id)
2. `contract_order_connects` INSERT (동일 contract_id 매핑)
3. 이전 잔액 조회 → `deposits` status=11(이월소진) INSERT
4. 신규 계약 → `deposits` status=12(이월충전) INSERT
5. 이전 수주계약 `contract_status` → 6(이월종료) UPDATE

---

## 6. 모델 설계

### CampaignModel (`app/Models/CampaignModel.php`)

```php
protected $allowedFields = [
    'ad_title', 'hospital_id', 'hospital_type', 'agency_user_id', 'user_id',
    'ad_type', 'ad_start_date', 'ad_end_date', 'ad_date_extend',
    'cost_type', 'general_cost', 'discount_cost', 'text_cost', 'db_cost',
    'category', 'exposure', 'where_image', 'model_image_count', 'ad_detail_info',
    'inspect_date', 'is_view_board', 'deliberation_code',
    'custom_randing', 'option_ad_id', 'custom1', 'custom2', 'custom3',
    'region', 'cooperation', 'keyword', 'sub_hospital_id',
    'contract_id', 'contract_order_id', 'contract_name',
    't1_image_name', 't2_image_name',
    'd1_image_name', 'd2_image_name', 'd3_image_name',
    'd4_image_name', 'd5_image_name', 'd6_image_name',
    'status', 'channel', 'is_deleted', 'del_date', 'delete_user_id',
];
```

**주요 메서드:**
- `getList(array $params)` — 목록 (페이징, 검색, 상태 필터)
- `getDetail(int $id)` — 상세 (JOIN: hospitals, contracts)
- `updateStatus(int $id, string $action)` — 상태 변경
- `getHistories(int $campaignId)` — 이력

### ContractModel + ContractOrderModel

```php
// ContractModel → 테이블: contracts
// ContractOrderModel → 테이블: contract_orders
// 핵심: getBalance(int $contractId, int $orderId): int
//   잔액 = SUM(price WHERE is_minus=0) - SUM(price WHERE is_minus=1)
```

### CampaignReviewRequestModel (`app/Models/CampaignReviewRequestModel.php`)

```php
protected $allowedFields = [
    'campaign_id', 'request_type', 'ad_title', 'ad_type', ...
    'review_status', 'review_memo', 'reviewed_by', 'reviewed_at', 'created_by',
];
// review_status: pending | approved | rejected
```

---

## 7. 뷰 렌더링 패턴

```php
// CI3 방식 (API 응답)
$this->response(['status' => 'success', 'result' => $data], 200);

// CI4 방식 (뷰 렌더링) — authUser 자동 주입
return $this->render('admin/campaigns/index', [
    'campaigns' => $campaigns,
    'pager'     => $pager,
    'filters'   => $filters,
]);
```

- 목록 화면: **AG Grid** (`ag-theme-alpine`)
- 차트/통계: **Chart.js** (Primary `#0F6E56`, Secondary `#1D9E75`)
- 리치 텍스트: **Tiptap** (빌드 없이 ESM CDN)
- 레이아웃: `$this->render()` 반드시 사용 (view() 직접 호출 금지)

---

## 8. 구현 우선순위

| 순서 | 작업 | 상태 |
|------|------|------|
| ✅ 1 | DB 마이그레이션 (핵심 도메인) | 완료 |
| ✅ 2 | Admin 공통 레이아웃 뷰 | 완료 |
| ✅ 3 | AuthController (로그인) | 완료 |
| ✅ 4 | DashboardController | 완료 |
| ✅ 5 | CampaignController + 기본 CRUD | 완료 |
| ✅ 6 | AdvertiserController | 완료 |
| ✅ 7 | ContractController + ContractOrderModel | 완료 |
| ✅ 8 | ReviewController (검수 승인/반려) | 완료 |
| ✅ 9 | ERD 기반 마이그레이션 보강 (000017~000026) | 완료 |
| 🔲 10 | PaymentController + 환불 | 미완 |
| 🔲 11 | ReportController + Chart.js | 미완 |
| 🔲 12 | CallRequest 목록 (신청 DB 관리) | 미완 |
| 🔲 13 | Board 관리 (후기 운영) | 미완 |

---

## 9. 보안 체크리스트 (CI3 → CI4 전환 시)

- [x] `$_GET` / `$_POST` 직접 사용 → `$this->request->getPost()` 교체
- [x] `common_m->checkToken()` → `AdminAuthFilter` 세션 인증 교체
- [x] 뷰 출력 시 `esc()` 적용
- [x] 모든 폼에 `csrf_field()` 추가
- [x] SQL Query Builder 사용 (raw query 금지)
- [ ] PG 키, S3 키 등 하드코딩 상수 → `.env` 이관 확인

---

*최초 작성: 2026-06-18 | ERD 기반 보강: 2026-06-19*
