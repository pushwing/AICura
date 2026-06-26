<?php
// PHP_URL_PATH는 string|null — null 반환 시 str_starts_with()가 TypeError 유발
$currentPath = (string) parse_url(current_url(), PHP_URL_PATH);

$isActive = static function (string $prefix, string $current): bool {
    return str_starts_with($current, $prefix);
};

$role = $role ?? '';

$icons = [
    'dashboard'   => '<svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>',
    'advertisers' => '<svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M3 21h18M9 21V7l6-4v18M9 11h6M9 15h6"/></svg>',
    'contracts'   => '<svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>',
    'calls'       => '<svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.13.81.36 1.6.7 2.34a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.74-1.27a2 2 0 0 1 2.11-.45c.74.34 1.53.57 2.34.7A2 2 0 0 1 22 16.92z"/></svg>',
    'profile'     => '<svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>',
];

if ($role === 'agency') {
    $nav = [
        ['label' => '대시보드',    'href' => '/portal/dashboard',   'exact' => true, 'icon' => $icons['dashboard']],
        ['label' => '광고주 관리',  'href' => '/portal/advertisers', 'icon' => $icons['advertisers']],
        ['label' => '계약 관리',    'href' => '/portal/contracts',   'icon' => $icons['contracts']],
        ['label' => '내 정보',      'href' => '/portal/profile',     'icon' => $icons['profile']],
    ];
} else {
    $nav = [
        ['label' => '대시보드',     'href' => '/portal/dashboard',    'exact' => true, 'icon' => $icons['dashboard']],
        ['label' => '계약 관리',     'href' => '/portal/contracts',    'icon' => $icons['contracts']],
        ['label' => '신청DB 관리',   'href' => '/portal/call-requests', 'icon' => $icons['calls']],
        ['label' => '내 정보',       'href' => '/portal/profile',       'icon' => $icons['profile']],
    ];
}
?>
<nav class="admin-sidebar">
    <div class="sidebar-logo">
        <img src="<?= base_url('assets/img/logo-dark.svg') ?>" alt="AICura" height="32">
    </div>

    <div class="sidebar-nav">
        <?php foreach ($nav as $item):
            $active = isset($item['exact'])
                ? $currentPath === $item['href']
                : $isActive($item['href'], $currentPath);
        ?>
            <a href="<?= esc($item['href']) ?>"
               class="nav-item<?= $active ? ' active' : '' ?>">
                <?= $item['icon'] /* 하드코딩된 SVG */ ?>
                <?= esc($item['label']) ?>
            </a>
        <?php endforeach; ?>
    </div>

    <div class="sidebar-footer">
        <div class="sidebar-version text-xs" style="color:rgba(255,255,255,0.2); padding: 16px 20px;">
            AICura <?= $role === 'agency' ? '대행사' : '광고주' ?> 포털
        </div>
    </div>
</nav>
