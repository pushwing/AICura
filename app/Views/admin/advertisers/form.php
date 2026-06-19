<?php
/** @var array<string, mixed>|null $advertiser */
/** @var array<int, array<string, mixed>> $hospitals */
/** @var array<int, array<string, mixed>> $parentAdvertisers */

$isEdit  = $advertiser !== null;
$title   = $isEdit ? '광고주 수정' : '광고주 등록';
$action  = $isEdit ? '/admin/advertisers/' . (int) $advertiser['id'] : '/admin/advertisers';

/** @var array<string, string> $errors */
$errors = session('errors') ?? [];

$old = fn(string $key, mixed $default = ''): mixed => old($key, $isEdit ? ($advertiser[$key] ?? $default) : $default);
?>

<div class="page-header" style="display:flex;align-items:center;gap:12px;margin-bottom:24px;">
    <a href="<?= $isEdit ? '/admin/advertisers/' . (int) $advertiser['id'] : '/admin/advertisers' ?>"
       style="color:var(--color-text-muted);text-decoration:none;">← <?= $isEdit ? '상세' : '목록' ?></a>
    <h1 class="page-title" style="margin:0;"><?= esc($title) ?></h1>
</div>

<?php if (!empty($errors)): ?>
<div class="alert alert-danger" style="margin-bottom:16px;padding:12px 16px;background:#fef2f2;border:1px solid #fecaca;border-radius:6px;color:#dc2626;font-size:14px;">
    <ul style="margin:0;padding-left:16px;">
        <?php foreach ($errors as $err): ?>
            <li><?= esc($err) ?></li>
        <?php endforeach; ?>
    </ul>
</div>
<?php endif; ?>

<div class="card">
    <div class="card-body">
        <form action="<?= esc($action) ?>" method="POST">
            <?= csrf_field() ?>
            <?php if ($isEdit): ?>
                <input type="hidden" name="_method" value="PUT">
            <?php endif; ?>

            <!-- 병원 선택 -->
            <div style="margin-bottom:20px;">
                <label style="display:block;font-size:14px;font-weight:500;margin-bottom:6px;">병원 <span style="color:#ef4444">*</span></label>
                <?php if ($isEdit): ?>
                    <input type="hidden" name="hospital_id" value="<?= (int) $advertiser['hospital_id'] ?>">
                    <input type="text" class="form-control" value="<?= esc($advertiser['hospital_name']) ?>" disabled style="background:var(--color-bg-subtle,#f9fafb);">
                <?php else: ?>
                    <select name="hospital_id" class="form-control" id="hospitalSelect" onchange="onHospitalChange(this)">
                        <option value="">병원 선택</option>
                        <?php foreach ($hospitals as $h): ?>
                            <option value="<?= (int) $h['id'] ?>"
                                    data-name="<?= esc($h['name']) ?>"
                                <?= (string) $old('hospital_id') === (string) $h['id'] ? 'selected' : '' ?>>
                                <?= esc($h['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                <?php endif; ?>
            </div>

            <!-- 병원명 (표시용 / 자동입력) -->
            <div style="margin-bottom:20px;">
                <label style="display:block;font-size:14px;font-weight:500;margin-bottom:6px;">병원명 <span style="color:#ef4444">*</span></label>
                <input type="text" name="hospital_name" class="form-control" id="hospitalName"
                       value="<?= esc((string) $old('hospital_name')) ?>"
                       placeholder="병원명" maxlength="255" required>
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:20px;">
                <!-- 담당자명 -->
                <div>
                    <label style="display:block;font-size:14px;font-weight:500;margin-bottom:6px;">담당자명</label>
                    <input type="text" name="contact_name" class="form-control"
                           value="<?= esc((string) $old('contact_name')) ?>"
                           placeholder="담당자명" maxlength="100">
                </div>
                <!-- 담당자 전화 -->
                <div>
                    <label style="display:block;font-size:14px;font-weight:500;margin-bottom:6px;">연락처</label>
                    <input type="text" name="contact_phone" class="form-control"
                           value="<?= esc((string) $old('contact_phone')) ?>"
                           placeholder="010-0000-0000" maxlength="30">
                </div>
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:20px;">
                <!-- 담당자 이메일 -->
                <div>
                    <label style="display:block;font-size:14px;font-weight:500;margin-bottom:6px;">이메일</label>
                    <input type="email" name="contact_email" class="form-control"
                           value="<?= esc((string) $old('contact_email')) ?>"
                           placeholder="example@hospital.com" maxlength="255">
                </div>
                <!-- 사업자등록번호 -->
                <div>
                    <label style="display:block;font-size:14px;font-weight:500;margin-bottom:6px;">사업자등록번호</label>
                    <input type="text" name="business_no" class="form-control"
                           value="<?= esc((string) $old('business_no')) ?>"
                           placeholder="000-00-00000" maxlength="50">
                </div>
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px;margin-bottom:28px;">
                <!-- 네트워크 유형 -->
                <div>
                    <label style="display:block;font-size:14px;font-weight:500;margin-bottom:6px;">네트워크 유형 <span style="color:#ef4444">*</span></label>
                    <select name="is_network" class="form-control" id="networkType" onchange="onNetworkChange(this.value)">
                        <option value="0" <?= (string) $old('is_network', 0) === '0' ? 'selected' : '' ?>>일반</option>
                        <option value="1" <?= (string) $old('is_network', 0) === '1' ? 'selected' : '' ?>>네트워크(모)</option>
                        <option value="2" <?= (string) $old('is_network', 0) === '2' ? 'selected' : '' ?>>네트워크(자)</option>
                    </select>
                </div>

                <!-- 모병원 선택 (자병원인 경우) -->
                <div id="parentWrap" style="display:<?= (string) $old('is_network', 0) === '2' ? 'block' : 'none' ?>;">
                    <label style="display:block;font-size:14px;font-weight:500;margin-bottom:6px;">모병원</label>
                    <select name="network_parent_id" class="form-control">
                        <option value="">모병원 선택</option>
                        <?php foreach ($parentAdvertisers as $p): ?>
                            <option value="<?= (int) $p['id'] ?>"
                                <?= (string) $old('network_parent_id') === (string) $p['id'] ? 'selected' : '' ?>>
                                <?= esc($p['hospital_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- 상태 -->
                <div>
                    <label style="display:block;font-size:14px;font-weight:500;margin-bottom:6px;">상태 <span style="color:#ef4444">*</span></label>
                    <select name="status" class="form-control">
                        <option value="1" <?= (string) $old('status', 1) === '1' ? 'selected' : '' ?>>활성</option>
                        <option value="2" <?= (string) $old('status', 1) === '2' ? 'selected' : '' ?>>정지</option>
                        <option value="3" <?= (string) $old('status', 1) === '3' ? 'selected' : '' ?>>탈퇴</option>
                    </select>
                </div>
            </div>

            <div style="display:flex;gap:8px;">
                <button type="submit" class="btn btn-primary"><?= $isEdit ? '수정' : '등록' ?></button>
                <a href="<?= $isEdit ? '/admin/advertisers/' . (int) $advertiser['id'] : '/admin/advertisers' ?>"
                   class="btn btn-outline">취소</a>
            </div>
        </form>
    </div>
</div>

<script>
function onHospitalChange(select) {
    const opt = select.options[select.selectedIndex];
    const nameInput = document.getElementById('hospitalName');
    if (opt && opt.dataset.name) {
        nameInput.value = opt.dataset.name;
    }
}

function onNetworkChange(val) {
    const wrap = document.getElementById('parentWrap');
    wrap.style.display = val === '2' ? 'block' : 'none';
}
</script>
