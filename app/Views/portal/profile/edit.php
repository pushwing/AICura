<?php
/** @var array<string, mixed> $user */
/** @var array<string, mixed>|null $agency */
/** @var string $role */

$errors = session()->getFlashdata('errors') ?? [];
?>
<div class="page-header" style="margin-bottom:20px;">
    <h1 class="page-title">내 정보</h1>
</div>

<?php if (!empty($errors)): ?>
    <div class="alert alert-danger" style="margin-bottom:16px;">
        <span class="alert-icon">!</span>
        <div class="alert-body">
            <?php foreach ($errors as $err): ?>
                <div><?= esc($err) ?></div>
            <?php endforeach; ?>
        </div>
    </div>
<?php endif; ?>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;align-items:start;">

    <!-- 기본 정보 -->
    <div class="card">
        <div class="card-header"><span class="card-title">기본 정보</span></div>
        <div class="card-body">
            <form action="/portal/profile" method="POST">
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
            <form action="/portal/profile/password" method="POST" autocomplete="off">
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

<?php if ($role !== 'agency'): ?>
    <!-- 내 대행사 (광고주 전용) -->
    <div class="card" style="margin-top:20px;">
        <div class="card-header"><span class="card-title">내 대행사</span></div>
        <div class="card-body">
            <?php if ($agency === null): ?>
                <p class="text-sm" style="color:var(--color-text-muted);">
                    연결된 대행사 정보가 없습니다. 계약이 등록되면 담당 대행사 정보가 표시됩니다.
                </p>
            <?php else: ?>
                <table style="width:100%;font-size:14px;border-collapse:collapse;max-width:520px;">
                    <?php foreach ([
                        ['대행사명',  $agency['agency_company_name'] ?? '-'],
                        ['담당자',    $agency['agency_company_charge_name'] ?? '-'],
                        ['전화번호',  $agency['agency_company_charge_phone'] ?? '-'],
                        ['이메일',    $agency['agency_company_charge_email'] ?? '-'],
                    ] as [$label, $value]): ?>
                    <tr>
                        <td style="padding:8px 0;color:var(--color-text-muted);width:110px;"><?= $label ?></td>
                        <td style="padding:8px 0;"><?= esc((string) ($value ?: '-')) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </table>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>
