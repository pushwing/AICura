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
        $routes->resource('campaigns', ['controller' => 'Admin\CampaignController']);

        // 소재 관리
        $routes->resource('creatives', ['controller' => 'Admin\CreativeController']);

        // 리포트
        $routes->get('reports',           'Admin\ReportController::index');
        $routes->get('reports/campaigns', 'Admin\ReportController::campaigns');

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

        // 결제 관리
        $routes->get('payments',                    'Admin\PaymentController::index');
        $routes->get('payments/(:num)',             'Admin\PaymentController::show/$1');
        $routes->post('payments/(:num)/refund',     'Admin\PaymentController::refund/$1');

        // 사용자 관리
        $routes->resource('users', ['controller' => 'Admin\UserController']);

        // 설정
        $routes->get('settings',  'Admin\SettingController::index');
        $routes->post('settings', 'Admin\SettingController::update');
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

        // 광고 소재
        $routes->resource('creatives', ['controller' => 'CreativeController']);

        // 리포트
        $routes->get('reports/summary',  'ReportController::summary');
        $routes->get('reports/campaigns/(:num)', 'ReportController::campaign/$1');

        // 사용자 프로필
        $routes->get('me',        'UserController::me');
        $routes->patch('me',      'UserController::update');
    });
});
