<!DOCTYPE html>
<html lang="ko">
<head>
    <?= view('admin/layout/head', ['title' => $title ?? 'AICura Admin']) ?>
    <style>
        /* ── Admin Shell Layout ── */
        .admin-shell {
            display: flex;
            min-height: 100vh;
        }

        /* ── Sidebar ── */
        .admin-sidebar {
            width: 220px;
            background: var(--color-navy);
            flex-shrink: 0;
            display: flex;
            flex-direction: column;
            position: sticky;
            top: 0;
            height: 100vh;
            overflow-y: auto;
        }
        .sidebar-logo {
            padding: 18px 20px 14px;
            border-bottom: 0.5px solid rgba(255,255,255,0.08);
        }
        .sidebar-nav {
            padding: 8px 0;
            flex: 1;
        }
        .nav-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 9px 20px;
            font-size: 13.5px;
            font-weight: 500;
            color: rgba(255,255,255,0.55);
            text-decoration: none;
            transition: color 0.15s, background 0.15s;
        }
        .nav-item:hover {
            color: rgba(255,255,255,0.9);
            background: rgba(255,255,255,0.06);
        }
        .nav-item.active {
            color: #fff;
            background: rgba(15,110,86,0.22);
            border-left: 2px solid var(--color-primary);
            padding-left: 18px;
        }
        .nav-item svg { flex-shrink: 0; }
        .nav-item.active svg { stroke: #1D9E75; }
        .sidebar-footer {
            border-top: 0.5px solid rgba(255,255,255,0.06);
        }

        /* ── Main Area ── */
        .admin-main {
            flex: 1;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            background: var(--color-bg-surface);
        }

        /* ── Topbar ── */
        .admin-topbar {
            background: #fff;
            border-bottom: 0.5px solid var(--color-border);
            padding: 0 24px;
            height: 56px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 20;
            flex-shrink: 0;
        }
        .topbar-title {
            font-size: 15px;
            font-weight: 600;
            color: var(--color-text);
        }
        .topbar-actions {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .topbar-user {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
            color: var(--color-text-muted);
        }
        .topbar-logout {
            font-size: 13px;
            color: var(--color-text-hint);
            text-decoration: none;
            padding: 5px 10px;
            border-radius: var(--radius-sm);
            transition: background 0.15s, color 0.15s;
        }
        .topbar-logout:hover {
            background: var(--color-bg-muted);
            color: var(--color-text);
        }

        /* ── Page Content ── */
        .admin-content {
            flex: 1;
            padding: 28px 28px;
        }

        /* ── Flash Messages ── */
        .flash-wrap {
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
<div class="admin-shell">

    <?= view('admin/layout/sidebar') ?>

    <div class="admin-main">

        <!-- Topbar -->
        <header class="admin-topbar">
            <span class="topbar-title"><?= esc($pageTitle ?? $title ?? '') ?></span>
            <div class="topbar-actions">
                <div class="topbar-user">
                    <div class="avatar avatar-sm">
                        <?= esc(mb_substr($authUser['name'] ?? 'A', 0, 1)) ?>
                    </div>
                    <span><?= esc($authUser['name'] ?? '') ?></span>
                </div>
                <a href="<?= base_url('admin/logout') ?>" class="topbar-logout">로그아웃</a>
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
