<!DOCTYPE html>
<html lang="ko">
<head>
    <?= view('admin/layout/head', ['title' => $title ?? 'AICura Admin']) ?>
</head>
<body>
<div class="admin-shell">

    <?= view('admin/layout/sidebar') ?>

    <div class="admin-main">

        <!-- Topbar -->
        <?php
        // $authUser가 null(미인증)인 경우 안전하게 처리
        $userName    = is_array($authUser) ? ($authUser['username'] ?? '') : '';
        $userInitial = mb_substr($userName, 0, 1) ?: 'A';
        ?>
        <header class="admin-topbar">
            <span class="topbar-title"><?= esc($pageTitle ?? $title ?? '') ?></span>
            <div class="topbar-actions">
                <div class="topbar-user">
                    <div class="avatar avatar-sm"><?= esc($userInitial) ?></div>
                    <span><?= esc($userName) ?></span>
                </div>
                <form action="<?= base_url('admin/logout') ?>" method="POST" style="display:inline">
                    <?= csrf_field() ?>
                    <button type="submit" class="topbar-logout">로그아웃</button>
                </form>
            </div>
        </header>

        <!-- Page Content -->
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
