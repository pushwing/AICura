<?php
/** @var array<int, array<string, mixed>> $hospitals */

/** @var array<string, string> $errors */
$errors = session('errors') ?? [];
?>
<div class="page-header" style="display:flex;align-items:center;gap:12px;margin-bottom:24px;">
    <a href="/portal/advertisers" style="color:var(--color-text-muted);text-decoration:none;">← 목록</a>
    <h1 class="page-title" style="margin:0;">광고주 등록</h1>
</div>

<?php if (!empty($errors)): ?>
<div class="alert alert-danger" style="margin-bottom:16px;">
    <ul style="margin:0;padding-left:16px;">
        <?php foreach ($errors as $err): ?><li><?= esc($err) ?></li><?php endforeach; ?>
    </ul>
</div>
<?php endif; ?>

<div class="card">
    <div class="card-body">
        <form action="/portal/advertisers" method="POST">
            <?= csrf_field() ?>

            <div style="margin-bottom:20px;">
                <label style="display:block;font-size:14px;font-weight:500;margin-bottom:6px;">병원 <span style="color:#ef4444">*</span></label>
                <select name="hospital_id" class="form-control" id="hospitalSelect" onchange="onHospitalChange(this)">
                    <option value="">병원 선택</option>
                    <?php foreach ($hospitals as $h): ?>
                        <option value="<?= (int) $h['id'] ?>" data-name="<?= esc($h['name']) ?>"
                            <?= (string) old('hospital_id') === (string) $h['id'] ? 'selected' : '' ?>>
                            <?= esc($h['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div style="margin-bottom:20px;">
                <label style="display:block;font-size:14px;font-weight:500;margin-bottom:6px;">병원명 <span style="color:#ef4444">*</span></label>
                <input type="text" name="hospital_name" class="form-control" id="hospitalName"
                       value="<?= esc((string) old('hospital_name')) ?>" placeholder="병원명" maxlength="255" required>
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:20px;">
                <div>
                    <label style="display:block;font-size:14px;font-weight:500;margin-bottom:6px;">담당자명</label>
                    <input type="text" name="contact_name" class="form-control" value="<?= esc((string) old('contact_name')) ?>" maxlength="100">
                </div>
                <div>
                    <label style="display:block;font-size:14px;font-weight:500;margin-bottom:6px;">연락처</label>
                    <input type="text" name="contact_phone" class="form-control" value="<?= esc((string) old('contact_phone')) ?>" placeholder="010-0000-0000" maxlength="30">
                </div>
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:20px;">
                <div>
                    <label style="display:block;font-size:14px;font-weight:500;margin-bottom:6px;">이메일</label>
                    <input type="email" name="contact_email" class="form-control" value="<?= esc((string) old('contact_email')) ?>" maxlength="255">
                </div>
                <div>
                    <label style="display:block;font-size:14px;font-weight:500;margin-bottom:6px;">사업자등록번호</label>
                    <input type="text" name="business_no" class="form-control" value="<?= esc((string) old('business_no')) ?>" placeholder="000-00-00000" maxlength="50">
                </div>
            </div>

            <div style="margin-bottom:28px;">
                <label style="display:block;font-size:14px;font-weight:500;margin-bottom:6px;">광고주 로그인 계정 이메일 <span class="text-xs" style="color:var(--color-text-muted);">(선택 — 입력 시 해당 병원 계정과 연결)</span></label>
                <input type="email" name="owner_email" class="form-control" value="<?= esc((string) old('owner_email')) ?>" placeholder="advertiser@hospital.com" maxlength="255">
            </div>

            <div style="display:flex;gap:8px;">
                <button type="submit" class="btn btn-primary">등록</button>
                <a href="/portal/advertisers" class="btn btn-outline">취소</a>
            </div>
        </form>
    </div>
</div>

<script>
function onHospitalChange(select) {
    const opt = select.options[select.selectedIndex];
    const nameInput = document.getElementById('hospitalName');
    if (opt && opt.dataset.name) { nameInput.value = opt.dataset.name; }
}
</script>
