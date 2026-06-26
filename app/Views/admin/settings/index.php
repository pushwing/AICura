<?php
/** @var array<string, mixed> $user */
/** @var array<string, string> $settings */
/** @var array<string, string> $settingKeys */
?>
<div class="page-header" style="margin-bottom:20px;">
    <h1 class="page-title">설정</h1>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;align-items:start;">

    <!-- 기본 정보 -->
    <div class="card">
        <div class="card-header"><span class="card-title">기본 정보</span></div>
        <div class="card-body">
            <form action="/admin/settings/profile" method="POST">
                <?= csrf_field() ?>
                <div class="form-group" style="margin-bottom:16px;">
                    <label class="form-label">이메일 (로그인 ID)</label>
                    <input type="email" class="form-control" value="<?= esc($user['email']) ?>" disabled>
                </div>
                <div class="form-group" style="margin-bottom:16px;">
                    <label class="form-label">이름</label>
                    <input type="text" name="username" class="form-control" required
                           value="<?= esc(old('username', $user['username'] ?? '')) ?>">
                </div>
                <div class="form-group" style="margin-bottom:20px;">
                    <label class="form-label">전화번호</label>
                    <input type="text" name="phone" class="form-control"
                           value="<?= esc(old('phone', $user['phone'] ?? '')) ?>">
                </div>
                <button type="submit" class="btn btn-primary btn-sm">정보 저장</button>
            </form>
        </div>
    </div>

    <!-- 비밀번호 변경 -->
    <div class="card">
        <div class="card-header"><span class="card-title">비밀번호 변경</span></div>
        <div class="card-body">
            <form action="/admin/settings/password" method="POST" autocomplete="off">
                <?= csrf_field() ?>
                <div class="form-group" style="margin-bottom:16px;">
                    <label class="form-label">현재 비밀번호</label>
                    <input type="password" name="current_password" class="form-control" required autocomplete="current-password">
                </div>
                <div class="form-group" style="margin-bottom:16px;">
                    <label class="form-label">새 비밀번호 (8자 이상)</label>
                    <input type="password" name="new_password" class="form-control" required autocomplete="new-password">
                </div>
                <div class="form-group" style="margin-bottom:20px;">
                    <label class="form-label">새 비밀번호 확인</label>
                    <input type="password" name="confirm_password" class="form-control" required autocomplete="new-password">
                </div>
                <button type="submit" class="btn btn-primary btn-sm">비밀번호 변경</button>
            </form>
        </div>
    </div>

</div>

<!-- 시스템 설정 -->
<div class="card" style="margin-top:20px;">
    <div class="card-header"><span class="card-title">시스템 설정</span></div>
    <div class="card-body">
        <form action="/admin/settings/system" method="POST">
            <?= csrf_field() ?>
            <?php foreach ($settingKeys as $key => $label): ?>
                <div class="form-group" style="margin-bottom:16px;max-width:520px;">
                    <label class="form-label"><?= esc($label) ?></label>
                    <input type="<?= $key === 'admin_email' ? 'email' : 'text' ?>"
                           name="<?= esc($key) ?>" class="form-control"
                           value="<?= esc(old($key, $settings[$key] ?? '')) ?>">
                </div>
            <?php endforeach; ?>
            <button type="submit" class="btn btn-primary btn-sm">설정 저장</button>
        </form>
    </div>
</div>
