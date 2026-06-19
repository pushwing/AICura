<?php
// PHP_URL_PATH는 string|null — null 반환 시 str_starts_with()가 TypeError 유발
$currentPath = (string) parse_url(current_url(), PHP_URL_PATH);

/**
 * @param string $prefix  URL 접두어 (예: '/admin/campaigns')
 * @param string $current 현재 요청 경로
 */
$isActive = static function (string $prefix, string $current): bool {
    return str_starts_with($current, $prefix);
};

$nav = [
    [
        'label' => '대시보드',
        'href'  => '/admin/dashboard',
        'exact' => true,
        'icon'  => '<svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>',
    ],
    [
        'label' => '광고주 관리',
        'href'  => '/admin/advertisers',
        'icon'  => '<svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M3 21h18M9 21V7l6-4v18M9 11h6M9 15h6"/></svg>',
    ],
    [
        'label' => '캠페인 관리',
        'href'  => '/admin/campaigns',
        'icon'  => '<svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M3 11l19-9-9 19-2-8-8-2z"/></svg>',
    ],
    [
        'label' => '이벤트 신청 DB',
        'href'  => '/admin/call-requests',
        'icon'  => '<svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.13.96.36 1.9.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.91.34 1.85.57 2.81.7A2 2 0 0 1 22 16.92z"/></svg>',
    ],
    [
        'label' => '소재 관리',
        'href'  => '/admin/creatives',
        'icon'  => '<svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="m21 15-5-5L5 21"/></svg>',
    ],
    [
        'label' => '계약 관리',
        'href'  => '/admin/contracts',
        'icon'  => '<svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>',
    ],
    [
        'label' => '결제 관리',
        'href'  => '/admin/payments',
        'icon'  => '<svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>',
    ],
    [
        'label' => '리포트',
        'href'  => '/admin/reports',
        'icon'  => '<svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>',
    ],
    [
        'label' => '사용자 관리',
        'href'  => '/admin/users',
        'icon'  => '<svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg>',
    ],
    [
        'label' => '설정',
        'href'  => '/admin/settings',
        'icon'  => '<svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>',
    ],
];
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
                <?= $item['icon'] /* 하드코딩된 SVG — 동적 입력이 아니므로 esc() 불필요 */ ?>
                <?= esc($item['label']) ?>
            </a>
        <?php endforeach; ?>
    </div>

    <div class="sidebar-footer">
        <div class="sidebar-version text-xs" style="color:rgba(255,255,255,0.2); padding: 16px 20px;">
            AICura Admin
        </div>
    </div>
</nav>
