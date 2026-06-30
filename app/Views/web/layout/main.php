<?php
/**
 * 공개 SSR 레이아웃 (이슈 #137 — SEO/GEO)
 *
 * @var array<string, mixed> $meta    SEO 메타 (BaseWebController::defaultMeta 기준)
 * @var string               $content 본문 슬롯
 */
$meta = $meta ?? [];
$title       = (string) ($meta['title'] ?? 'AICura');
$description = (string) ($meta['description'] ?? '');
$canonical   = (string) ($meta['canonical'] ?? current_url());
$robots      = (string) ($meta['robots'] ?? 'index, follow');
$ogType      = (string) ($meta['og_type'] ?? 'website');
$ogImage     = $meta['og_image'] ?? null;
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title><?= esc($title) ?></title>
    <meta name="description" content="<?= esc($description) ?>">
    <meta name="robots" content="<?= esc($robots) ?>">
    <link rel="canonical" href="<?= esc($canonical, 'attr') ?>">

    <!-- Open Graph -->
    <meta property="og:site_name" content="AICura">
    <meta property="og:locale" content="ko_KR">
    <meta property="og:type" content="<?= esc($ogType, 'attr') ?>">
    <meta property="og:title" content="<?= esc($title, 'attr') ?>">
    <meta property="og:description" content="<?= esc($description, 'attr') ?>">
    <meta property="og:url" content="<?= esc($canonical, 'attr') ?>">
    <?php if ($ogImage !== null): ?>
    <meta property="og:image" content="<?= esc((string) $ogImage, 'attr') ?>">
    <?php endif; ?>

    <!-- Twitter Card -->
    <meta name="twitter:card" content="<?= $ogImage !== null ? 'summary_large_image' : 'summary' ?>">
    <meta name="twitter:title" content="<?= esc($title, 'attr') ?>">
    <meta name="twitter:description" content="<?= esc($description, 'attr') ?>">
    <?php if ($ogImage !== null): ?>
    <meta name="twitter:image" content="<?= esc((string) $ogImage, 'attr') ?>">
    <?php endif; ?>

    <link rel="icon" href="<?= base_url('favicon.ico') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/aicura.css') ?>">

    <!-- JSON-LD 구조화 데이터는 Phase 2(이슈 ④)에서 web/partials/json_ld 로 주입 -->
</head>
<body>
    <header class="web-header">
        <a href="<?= base_url('events') ?>" class="web-logo">AICura</a>
        <nav class="web-nav">
            <a href="<?= base_url('events') ?>">이벤트</a>
            <a href="<?= base_url('hospitals') ?>">병원</a>
            <a href="<?= base_url('reviews') ?>">후기</a>
        </nav>
    </header>

    <main class="web-main">
        <?= $content ?>
    </main>

    <footer class="web-footer">
        <p>&copy; <?= date('Y') ?> AICura. 성형·토탈 광고 솔루션.</p>
    </footer>
</body>
</html>
