<?php
/** @var array<string, mixed>|null $hospital */
/** @var array<int, array<string, mixed>> $departments */
/** @var list<int> $selectedDept */

$isEdit = $hospital !== null;
$title  = $isEdit ? '병원 수정' : '병원 등록';
$action = $isEdit ? '/admin/hospitals/' . (int) $hospital['id'] : '/admin/hospitals';

/** @var array<string, string> $errors */
$errors = session('errors') ?? [];

// 체크 상태: 검증 실패 후엔 old() 우선, 그 외엔 기존 매핑(selectedDept)
$oldDept = old('departments');
$checked = is_array($oldDept) ? array_map('intval', $oldDept) : $selectedDept;

$currentType   = (string) old('type', $isEdit ? ($hospital['type'] ?? 1) : 1);
$currentStatus = (string) old('status', $isEdit ? ($hospital['status'] ?? 'active') : 'active');
?>

<div class="page-header" style="display:flex;align-items:center;gap:12px;margin-bottom:24px;">
    <a href="/admin/hospitals" style="color:var(--color-text-muted);text-decoration:none;">← 목록</a>
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

            <!-- 병원명 -->
            <div style="margin-bottom:20px;">
                <label style="display:block;font-size:14px;font-weight:500;margin-bottom:6px;">병원명 <span style="color:#ef4444">*</span></label>
                <input type="text" name="name" class="form-control"
                       value="<?= esc((string) old('name', $isEdit ? ($hospital['name'] ?? '') : '')) ?>"
                       placeholder="병원명" maxlength="255" required>
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:20px;">
                <div>
                    <label style="display:block;font-size:14px;font-weight:500;margin-bottom:6px;">유형 <span style="color:#ef4444">*</span></label>
                    <select name="type" class="form-control">
                        <option value="1" <?= $currentType === '1' ? 'selected' : '' ?>>일반</option>
                        <option value="2" <?= $currentType === '2' ? 'selected' : '' ?>>네트워크(모)</option>
                        <option value="3" <?= $currentType === '3' ? 'selected' : '' ?>>네트워크(자)</option>
                    </select>
                </div>
                <div>
                    <label style="display:block;font-size:14px;font-weight:500;margin-bottom:6px;">상태 <span style="color:#ef4444">*</span></label>
                    <select name="status" class="form-control">
                        <option value="active"   <?= $currentStatus === 'active' ? 'selected' : '' ?>>활성</option>
                        <option value="inactive" <?= $currentStatus === 'inactive' ? 'selected' : '' ?>>비활성</option>
                    </select>
                </div>
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:20px;">
                <div>
                    <label style="display:block;font-size:14px;font-weight:500;margin-bottom:6px;">연락처</label>
                    <input type="text" name="phone" class="form-control"
                           value="<?= esc((string) old('phone', $isEdit ? ($hospital['phone'] ?? '') : '')) ?>"
                           placeholder="02-0000-0000" maxlength="20">
                </div>
                <div>
                    <label style="display:block;font-size:14px;font-weight:500;margin-bottom:6px;">주소</label>
                    <input type="text" name="address" class="form-control"
                           value="<?= esc((string) old('address', $isEdit ? ($hospital['address'] ?? '') : '')) ?>"
                           placeholder="서울 강남구 ..." maxlength="500">
                </div>
            </div>

            <!-- 진료과 매핑 -->
            <div style="margin-bottom:28px;">
                <label style="display:block;font-size:14px;font-weight:500;margin-bottom:8px;">진료과</label>
                <?php if ($departments === []): ?>
                    <p style="font-size:13px;color:var(--color-text-muted);">
                        등록된 진료과가 없습니다. <a href="/admin/departments">진료과 관리</a>에서 먼저 추가하세요.
                    </p>
                <?php else: ?>
                    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:8px;padding:16px;border:1px solid var(--color-border,#e5e7eb);border-radius:8px;background:var(--color-bg-subtle,#f9fafb);">
                        <?php foreach ($departments as $d): ?>
                            <label style="display:flex;align-items:center;gap:8px;font-size:14px;cursor:pointer;<?= (int) $d['is_active'] === 0 ? 'opacity:.5;' : '' ?>">
                                <input type="checkbox" name="departments[]" value="<?= (int) $d['id'] ?>"
                                       <?= in_array((int) $d['id'], $checked, true) ? 'checked' : '' ?>>
                                <?= esc($d['name']) ?>
                                <?php if ((int) $d['is_active'] === 0): ?><span style="font-size:11px;color:#9ca3af;">(비활성)</span><?php endif; ?>
                            </label>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <div style="display:flex;gap:8px;">
                <button type="submit" class="btn btn-primary"><?= $isEdit ? '수정' : '등록' ?></button>
                <a href="/admin/hospitals" class="btn btn-outline">취소</a>
            </div>
        </form>
    </div>
</div>
