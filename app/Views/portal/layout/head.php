<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= esc($title ?? 'AICura 포털') ?></title>
<link rel="icon" href="<?= base_url('favicon.ico') ?>">
<link rel="icon" type="image/png" sizes="32x32" href="<?= base_url('assets/favicon/favicon-32x32.png') ?>">
<link rel="apple-touch-icon" sizes="180x180" href="<?= base_url('assets/favicon/apple-touch-icon.png') ?>">
<link rel="manifest" href="<?= base_url('site.webmanifest') ?>">

<!-- CSRF 토큰 (JS fetch 용) -->
<meta name="csrf-token" content="<?= csrf_hash() ?>">
<meta name="csrf-header" content="<?= csrf_header() ?>">
<script src="<?= base_url('assets/js/csrf.js') ?>"></script>

<!-- Pretendard (Korean + Latin) -->
<link rel="preconnect" href="https://cdn.jsdelivr.net">
<link href="https://cdn.jsdelivr.net/gh/orioncactus/pretendard@v1.3.9/dist/web/static/pretendard.min.css" rel="stylesheet">

<!-- AICura Design System -->
<link rel="stylesheet" href="<?= base_url('assets/css/aicura.css') ?>">
<link rel="stylesheet" href="<?= base_url('assets/css/admin-layout.css') ?>">

<!-- AG Grid Community -->
<script src="https://cdn.jsdelivr.net/npm/ag-grid-community@31/dist/ag-grid-community.min.js"></script>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>
