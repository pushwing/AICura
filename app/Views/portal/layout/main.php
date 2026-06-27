<!DOCTYPE html>
<html lang="ko">
<head>
    <?= view('portal/layout/head', ['title' => $title ?? 'AICura 포털']) ?>
</head>
<body>
<div class="admin-shell">

    <?= view('portal/layout/sidebar', ['role' => $role ?? '']) ?>

    <div class="admin-main">

        <?php
        $userName    = is_array($portalUser ?? null) ? ($portalUser['username'] ?? '') : '';
        $userInitial = mb_substr((string) $userName, 0, 1) ?: 'U';
        $roleLabel   = ($role ?? '') === 'agency' ? '광고대행사' : '광고주';
        ?>
        <header class="admin-topbar">
            <span class="topbar-title"><?= esc($pageTitle ?? $title ?? '') ?></span>
            <div class="topbar-actions">
                <div class="topbar-user">
                    <div class="avatar avatar-sm"><?= esc($userInitial) ?></div>
                    <span><?= esc($userName) ?> · <?= esc($roleLabel) ?></span>
                </div>
                <form action="<?= base_url('portal/logout') ?>" method="POST" style="display:inline">
                    <?= csrf_field() ?>
                    <button type="submit" class="topbar-logout">로그아웃</button>
                </form>
            </div>
        </header>

        <main class="admin-content">
            <?php if (session()->getFlashdata('success')): ?>
                <div class="flash-wrap">
                    <div class="alert alert-success">
                        <span class="alert-icon">✓</span>
                        <div class="alert-body"><?= esc(session()->getFlashdata('success')) ?></div>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (session()->getFlashdata('error')): ?>
                <div class="flash-wrap">
                    <div class="alert alert-danger">
                        <span class="alert-icon">✕</span>
                        <div class="alert-body"><?= esc(session()->getFlashdata('error')) ?></div>
                    </div>
                </div>
            <?php endif; ?>

            <?= $content ?? '' ?>
        </main>

    </div><!-- /.admin-main -->

</div><!-- /.admin-shell -->
</body>
</html>
