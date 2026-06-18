<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */

// ── Admin ───────────────────────────────────────────────
$routes->group('admin', static function (RouteCollection $routes): void {

    // 인증 없이 접근 가능
    $routes->get('login',  'Admin\AuthController::login');
    $routes->post('login', 'Admin\AuthController::loginProcess');
    $routes->get('logout', 'Admin\AuthController::logout');

    // 이하 admin_auth 필터 적용
    $routes->group('', ['filter' => 'admin_auth'], static function (RouteCollection $routes): void {
        $routes->get('/',           'Admin\DashboardController::index');
        $routes->get('dashboard',   'Admin\DashboardController::index');

        // 광고주 관리
        $routes->resource('advertisers', ['controller' => 'Admin\AdvertiserController']);

        // 캠페인 관리
        $routes->resource('campaigns', ['controller' => 'Admin\CampaignController']);

        // 소재 관리
        $routes->resource('creatives', ['controller' => 'Admin\CreativeController']);

        // 리포트
        $routes->get('reports',           'Admin\ReportController::index');
        $routes->get('reports/campaigns', 'Admin\ReportController::campaigns');

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
