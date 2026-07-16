<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'AICura 포털 로그인') ?></title>
    <link rel="icon" href="<?= base_url('favicon.ico') ?>">
    <link rel="icon" type="image/png" sizes="32x32" href="<?= base_url('assets/favicon/favicon-32x32.png') ?>">
    <link rel="apple-touch-icon" sizes="180x180" href="<?= base_url('assets/favicon/apple-touch-icon.png') ?>">
    <link rel="manifest" href="<?= base_url('site.webmanifest') ?>">
    <link rel="preconnect" href="https://cdn.jsdelivr.net">
    <link href="https://cdn.jsdelivr.net/gh/orioncactus/pretendard@v1.3.9/dist/web/static/pretendard.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= base_url('assets/css/aicura.css') ?>">
    <style>
        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--color-bg, #F5F5F3);
            font-family: 'Pretendard', sans-serif;
        }
        .login-wrap { width: 100%; max-width: 420px; padding: 1.5rem; }
        .login-logo {
            display: flex; flex-direction: column; align-items: center;
            gap: 0.5rem; margin-bottom: 2rem;
        }
        .login-logo-mark { width: 48px; height: 48px; }
        .login-logo-wordmark {
            font-size: 1.375rem; font-weight: 700;
            color: var(--color-text, #0F1923); letter-spacing: -0.02em;
        }
        .login-logo-wordmark span { color: var(--color-primary, #0F6E56); }
        .login-card {
            background: #fff; border-radius: 1rem;
            box-shadow: 0 1px 3px rgba(0,0,0,.08), 0 4px 16px rgba(0,0,0,.06);
            padding: 2rem;
        }
        .login-card h1 {
            font-size: 1.125rem; font-weight: 600;
            color: var(--color-text, #0F1923); margin: 0 0 0.375rem;
        }
        .login-card .login-sub {
            font-size: 0.8125rem; color: var(--color-text-muted, #6b7280);
            margin: 0 0 1.5rem;
        }
        .login-error {
            background: #fef2f2; border: 1px solid #fca5a5; border-radius: 0.5rem;
            color: #991b1b; font-size: 0.875rem; padding: 0.75rem 1rem; margin-bottom: 1.25rem;
        }
        .form-group { margin-bottom: 1rem; }
        .form-label {
            display: block; font-size: 0.875rem; font-weight: 500;
            color: var(--color-text, #0F1923); margin-bottom: 0.375rem;
        }
    </style>
</head>
<body>
<div class="login-wrap">

    <div class="login-logo">
        <svg class="login-logo-mark" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg" role="img">
            <title>AICura</title>
            <rect width="48" height="48" rx="12" fill="#0F6E56"/>
            <polygon points="24,7.5 40.5,24 24,40.5 7.5,24" fill="#fff"/>
        </svg>
        <span class="login-logo-wordmark"><span>AI</span>Cura</span>
    </div>

    <div class="login-card">
        <h1>광고주 · 대행사 포털</h1>
        <p class="login-sub">광고주 또는 광고대행사 계정으로 로그인하세요.</p>

        <?php if (!empty($error)): ?>
            <div class="login-error"><?= esc($error) ?></div>
        <?php endif; ?>

        <form action="<?= base_url('portal/login') ?>" method="POST" novalidate>
            <?= csrf_field() ?>

            <div class="form-group">
                <label class="form-label" for="email">이메일</label>
                <input class="input" type="email" id="email" name="email"
                       value="<?= esc($old_email ?? '') ?>"
                       placeholder="you@example.com" autocomplete="email" autofocus required>
            </div>

            <div class="form-group" style="margin-bottom: 1.5rem;">
                <label class="form-label" for="password">비밀번호</label>
                <input class="input" type="password" id="password" name="password"
                       placeholder="비밀번호를 입력하세요" autocomplete="current-password" required>
            </div>

            <button type="submit" class="btn btn-primary btn-block">로그인</button>
        </form>
    </div>

</div>
</body>
</html>
