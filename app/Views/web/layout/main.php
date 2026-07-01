<?php

use App\Libraries\Seo\MetaTagBuilder;

/**
 * 공개 SSR 레이아웃 (이슈 #137 — SEO/GEO)
 *
 * @var array<string, mixed> $meta    SEO 메타 (BaseWebController::defaultMeta 기준)
 * @var string               $content 본문 슬롯
 */
$meta = $meta ?? [];
// canonical 미지정 시 현재 URL 보정 (이슈 #143 — MetaTagBuilder 로 메타 렌더 일원화)
$meta['canonical'] = (string) ($meta['canonical'] ?? current_url());
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <?= MetaTagBuilder::fromArray($meta)->render() ?>

    <link rel="icon" href="<?= base_url('favicon.ico') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/aicura.css') ?>">

    <!-- JSON-LD 구조화 데이터 (이슈 #145) — 상세 페이지에서 $jsonLd 전달 시 주입 -->
    <?= $this->include('web/partials/json_ld') ?>
</head>
<body>
    <header class="web-header">
        <a href="<?= base_url('events') ?>" class="web-logo">AICura</a>
        <nav class="web-nav">
            <a href="<?= base_url('events') ?>">이벤트</a>
            <a href="<?= base_url('hospitals') ?>">병원</a>
            <a href="<?= base_url('reviews') ?>">후기</a>
            <a href="<?= base_url('guides') ?>">가이드</a>
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
