<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */

// ── API Docs (Swagger UI) ────────────────────────────────
$routes->get('api/docs',      'Api\DocsController::index');
$routes->get('api/docs/spec', 'Api\DocsController::spec');

// ── Admin ───────────────────────────────────────────────
$routes->group('admin', static function (RouteCollection $routes): void {

    // 인증 없이 접근 가능
    $routes->get('login',  'Admin\AuthController::login');
    $routes->post('login', 'Admin\AuthController::loginProcess');
    $routes->post('logout', 'Admin\AuthController::logout');

    // 이하 admin_auth 필터 적용
    $routes->group('', ['filter' => 'admin_auth'], static function (RouteCollection $routes): void {
        $routes->get('/',           'Admin\DashboardController::index');
        $routes->get('dashboard',   'Admin\DashboardController::index');

        // 광고주 관리 — new()는 PHP 예약어이므로 GET /new 를 명시 선언 후 resource에서 제외 (Fix #4)
        // 폼의 POST + _method=PUT 오버라이드 지원: POST /advertisers/{id} → update
        $routes->get('advertisers/new', 'Admin\AdvertiserController::newForm');
        $routes->post('advertisers/(:num)', 'Admin\AdvertiserController::update/$1');
        $routes->resource('advertisers', ['controller' => 'Admin\AdvertiserController', 'except' => 'new']);

        // 캠페인 관리
        $routes->get('campaigns/temp',               'Admin\CampaignController::tempList');
        $routes->get('campaigns/(:num)/history',     'Admin\CampaignController::history/$1');
        $routes->post('campaigns/(:num)/action',     'Admin\CampaignController::action/$1');
        $routes->post('campaigns/suggest-copy',      'Admin\CampaignController::suggestCopy');
        $routes->resource('campaigns', ['controller' => 'Admin\CampaignController']);

        // 소재 관리 (POST /creatives/{id} → update, 폼 메서드 오버라이드)
        $routes->post('creatives/(:num)', 'Admin\CreativeController::update/$1');
        $routes->resource('creatives', ['controller' => 'Admin\CreativeController']);

        // 검수관리
        $routes->get('reviews',                       'Admin\ReviewController::index');
        $routes->get('reviews/(:num)',                'Admin\ReviewController::show/$1');
        $routes->post('reviews/(:num)/action',        'Admin\ReviewController::action/$1');
        $routes->post('reviews/(:num)/recheck',       'Admin\ReviewController::recheck/$1');

        // 리포트
        $routes->get('reports',           'Admin\ReportController::index');
        $routes->get('reports/campaigns', 'Admin\ReportController::campaigns');
        // AI 일일 보고서 (이슈 #65)
        $routes->post('reports/ai/generate',       'Admin\ReportController::generateAi');
        $routes->get('reports/ai/(:num)',          'Admin\ReportController::aiReportShow/$1');
        $routes->get('reports/ai-list/(:segment)', 'Admin\ReportController::aiReportList/$1');

        // 계약 관리
        // GET  contracts              → index      (메인 계약 목록)
        // GET  contracts/(:num)       → show       (메인 계약 상세 + 수주계약 목록)
        // GET  contracts/orders       → orders     (수주계약 전체 목록)
        // GET  contracts/orders/new   → orderNew   (수주계약 등록 폼)
        // POST contracts/orders       → orderCreate(수주계약 등록 처리)
        // GET  contracts/orders/(:num)       → orderShow  (수주계약 상세)
        // GET  contracts/orders/(:num)/edit  → orderEdit  (수주계약 수정 폼)
        // POST contracts/orders/(:num)       → orderUpdate(수주계약 수정 처리)
        // POST contracts/orders/(:num)/deposit-confirm → depositConfirm (입금 확인)
        $routes->get('contracts',                                     'Admin\ContractController::index');
        $routes->get('contracts/orders',                              'Admin\ContractController::orders');
        $routes->get('contracts/orders/new',                          'Admin\ContractController::orderNew');
        $routes->post('contracts/orders',                             'Admin\ContractController::orderCreate');
        $routes->get('contracts/orders/(:num)',                       'Admin\ContractController::orderShow/$1');
        $routes->get('contracts/orders/(:num)/edit',                  'Admin\ContractController::orderEdit/$1');
        $routes->post('contracts/orders/(:num)',                      'Admin\ContractController::orderUpdate/$1');
        $routes->post('contracts/orders/(:num)/deposit-confirm',      'Admin\ContractController::depositConfirm/$1');
        $routes->get('contracts/(:num)',                              'Admin\ContractController::show/$1');

        // 이벤트 신청 DB 관리
        // GET  call-requests                       → index       (신청 목록)
        // GET  call-requests/(:num)                → show        (신청 상세 + 메모)
        // POST call-requests/(:num)/status         → changeStatus(상태 변경, JSON)
        // POST call-requests/(:num)/memos          → memoStore   (메모 등록)
        // POST call-requests/(:num)/memos/(:num)/delete → memoDelete (메모 삭제)
        $routes->get('call-requests',                            'Admin\CallRequestController::index');
        $routes->get('call-requests/(:num)',                     'Admin\CallRequestController::show/$1');
        $routes->post('call-requests/(:num)/status',             'Admin\CallRequestController::changeStatus/$1');
        $routes->post('call-requests/(:num)/memos',              'Admin\CallRequestController::memoStore/$1');
        $routes->post('call-requests/(:num)/memos/(:num)/delete', 'Admin\CallRequestController::memoDelete/$1/$2');

        // 후기 운영
        // GET  boards                  → index    (후기 목록)
        // GET  boards/(:num)           → show     (후기 상세 + 신고 내역)
        // POST boards/(:num)/delete    → delete   (임시/완전 삭제)
        // POST boards/(:num)/restore   → restore  (삭제 복구)
        // POST boards/(:num)/recalc    → recalcSummary (별점 집계 갱신)
        $routes->get('boards',                  'Admin\BoardController::index');
        $routes->get('boards/(:num)',           'Admin\BoardController::show/$1');
        $routes->post('boards/(:num)/delete',   'Admin\BoardController::delete/$1');
        $routes->post('boards/(:num)/restore',  'Admin\BoardController::restore/$1');
        $routes->post('boards/(:num)/recalc',   'Admin\BoardController::recalcSummary/$1');

        // 결제 관리
        $routes->get('payments',                    'Admin\PaymentController::index');
        $routes->get('payments/(:num)',             'Admin\PaymentController::show/$1');
        $routes->post('payments/(:num)/refund',     'Admin\PaymentController::refund/$1');

        // 환불요청 관리 (이슈 #52) — 신청DB 상태변경(중복/결번/취소)에 따른 차감 복원 처리
        $routes->get('refund-requests',                  'Admin\RefundRequestController::index');
        $routes->post('refund-requests/(:num)/approve',  'Admin\RefundRequestController::approve/$1');
        $routes->post('refund-requests/(:num)/reject',   'Admin\RefundRequestController::reject/$1');

        // 사용자 관리
        $routes->get('users',        'Admin\UserController::index');
        $routes->get('users/(:num)', 'Admin\UserController::show/$1');

        // 설정 (이슈 #63) — 내 계정(프로필·비밀번호) + 시스템 설정
        $routes->get('settings',           'Admin\SettingController::index');
        $routes->post('settings/profile',  'Admin\SettingController::updateProfile');
        $routes->post('settings/password', 'Admin\SettingController::updatePassword');
        $routes->post('settings/system',   'Admin\SettingController::updateSystem');
    });
});


// ── Portal (광고주·광고대행사) ───────────────────────────
$routes->group('portal', static function (RouteCollection $routes): void {

    // 인증 없이 접근 가능
    $routes->get('login',   'Portal\AuthController::login');
    $routes->post('login',  'Portal\AuthController::loginProcess');
    $routes->post('logout', 'Portal\AuthController::logout');

    // 이하 portal_auth 필터 적용
    $routes->group('', ['filter' => 'portal_auth'], static function (RouteCollection $routes): void {
        $routes->get('/',         'Portal\DashboardController::index');
        $routes->get('dashboard', 'Portal\DashboardController::index');

        // 광고주 관리 (대행사) — new는 (:num)보다 먼저 선언
        $routes->get('advertisers',          'Portal\AdvertiserController::index');
        $routes->get('advertisers/new',      'Portal\AdvertiserController::newForm');
        $routes->post('advertisers',         'Portal\AdvertiserController::create');
        $routes->get('advertisers/(:num)',   'Portal\AdvertiserController::show/$1');
        $routes->post('advertisers/(:num)/invite', 'Portal\AdvertiserController::invite/$1');

        // owner 연결 초대 응답 (광고주)
        $routes->post('invites/(:num)/accept', 'Portal\InviteController::accept/$1');
        $routes->post('invites/(:num)/reject', 'Portal\InviteController::reject/$1');

        // 계약 관리 (광고주 동의·충전 / 대행사 조회)
        $routes->get('contracts',              'Portal\ContractController::index');
        $routes->post('contracts/agree',       'Portal\ContractController::agree');
        $routes->get('contracts/orders/new',     'Portal\ContractController::orderNew');
        $routes->post('contracts/orders',        'Portal\ContractController::orderCreate');
        $routes->get('contracts/orders/(:num)',  'Portal\ContractController::orderShow/$1');

        // 내 정보 (이름·전화번호·비밀번호 / 광고주는 대행사 정보 표시)
        $routes->get('profile',           'Portal\ProfileController::index');
        $routes->post('profile',          'Portal\ProfileController::update');
        $routes->post('profile/password', 'Portal\ProfileController::updatePassword');

        // 리포트 (광고주: 자기 병원 매출 / 대행사: 소속 광고주 합계+개별) — 이슈 #56
        $routes->get('reports', 'Portal\ReportController::index');
        // AI 일일 보고서 (이슈 #65) — 스코프 검증으로 본인 것만 조회
        $routes->get('reports/ai/(:num)',          'Portal\ReportController::aiReportShow/$1');
        $routes->get('reports/ai-list/(:segment)', 'Portal\ReportController::aiReportList/$1');

        // 신청DB 관리 (광고주)
        $routes->get('call-requests',                              'Portal\CallRequestController::index');
        $routes->get('call-requests/(:num)',                       'Portal\CallRequestController::show/$1');
        $routes->post('call-requests/(:num)/status',              'Portal\CallRequestController::changeStatus/$1');
        $routes->post('call-requests/(:num)/memos',               'Portal\CallRequestController::memoStore/$1');
        $routes->post('call-requests/(:num)/memos/(:num)/delete', 'Portal\CallRequestController::memoDelete/$1/$2');
    });
});


// ── API v1 ──────────────────────────────────────────────
$routes->group('api/v1', ['namespace' => 'App\Controllers\Api\V1'], static function (RouteCollection $routes): void {

    // 인증 (JWT 불필요)
    $routes->post('auth/login',   'AuthController::login');
    $routes->post('auth/refresh', 'AuthController::refresh');
    $routes->post('auth/logout',  'AuthController::logout');

    // 이하 jwt_auth 필터 적용
    $routes->group('', ['filter' => 'jwt_auth'], static function (RouteCollection $routes): void {

        // 캠페인
        $routes->resource('campaigns', ['controller' => 'CampaignController']);

        // 리포트
        $routes->get('reports/summary',  'ReportController::summary');
        $routes->get('reports/campaigns/(:num)', 'ReportController::campaign/$1');

        // 사용자 프로필
        $routes->get('me',        'UserController::me');
        $routes->patch('me',      'UserController::update');
    });
});
