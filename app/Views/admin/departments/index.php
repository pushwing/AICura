<?php
/** @var array<int, array<string, mixed>> $departments */

/** @var array<string, string> $errors */
$errors = session('errors') ?? [];
?>
<div class="page-header" style="margin-bottom:20px;">
    <h1 class="page-title">진료과 관리</h1>
    <p class="text-sm" style="color:var(--color-text-muted);margin-top:4px;">
        병원 진료과 필터(<code>filter[department]</code>)의 기준 코드 집합입니다.
    </p>
</div>

<?php if (session('success')): ?>
<div class="alert" style="margin-bottom:16px;padding:12px 16px;background:#ecfdf5;border:1px solid #a7f3d0;border-radius:6px;color:#047857;font-size:14px;">
    <?= esc(session('success')) ?>
</div>
<?php endif; ?>
<?php if (session('error')): ?>
<div class="alert" style="margin-bottom:16px;padding:12px 16px;background:#fef2f2;border:1px solid #fecaca;border-radius:6px;color:#dc2626;font-size:14px;">
    <?= esc(session('error')) ?>
</div>
<?php endif; ?>
<?php if (!empty($errors)): ?>
<div class="alert alert-danger" style="margin-bottom:16px;padding:12px 16px;background:#fef2f2;border:1px solid #fecaca;border-radius:6px;color:#dc2626;font-size:14px;">
    <ul style="margin:0;padding-left:16px;">
        <?php foreach ($errors as $err): ?><li><?= esc($err) ?></li><?php endforeach; ?>
    </ul>
</div>
<?php endif; ?>

<!-- 진료과 추가 -->
<div class="card" style="margin-bottom:24px;">
    <div class="card-body">
        <h2 style="font-size:15px;font-weight:600;margin:0 0 16px;">진료과 추가</h2>
        <form action="/admin/departments" method="POST"
              style="display:grid;grid-template-columns:1.2fr 1.2fr 80px 90px auto;gap:12px;align-items:end;">
            <?= csrf_field() ?>
            <div>
                <label style="display:block;font-size:13px;margin-bottom:6px;">코드 <span style="color:#ef4444">*</span></label>
                <input type="text" name="code" class="form-control" placeholder="plastic_surgery"
                       value="<?= esc((string) old('code', '')) ?>" maxlength="30" required>
            </div>
            <div>
                <label style="display:block;font-size:13px;margin-bottom:6px;">표시명 <span style="color:#ef4444">*</span></label>
                <input type="text" name="name" class="form-control" placeholder="성형외과"
                       value="<?= esc((string) old('name', '')) ?>" maxlength="100" required>
            </div>
            <div>
                <label style="display:block;font-size:13px;margin-bottom:6px;">정렬</label>
                <input type="number" name="sort" class="form-control" value="<?= esc((string) old('sort', '0')) ?>" min="0">
            </div>
            <div>
                <label style="display:flex;align-items:center;gap:6px;font-size:13px;margin-bottom:10px;">
                    <input type="checkbox" name="is_active" value="1" checked> 활성
                </label>
            </div>
            <button type="submit" class="btn btn-primary btn-sm">추가</button>
        </form>
        <p style="font-size:12px;color:var(--color-text-muted);margin:10px 0 0;">
            코드는 영소문자·숫자·언더스코어(_)만 사용하며 첫 글자는 영소문자여야 합니다. 한번 정한 코드는 앱 필터에서 사용되므로 가급적 변경하지 마세요.
        </p>
    </div>
</div>

<!-- 진료과 목록 -->
<div class="card">
    <div class="card-body">
        <h2 style="font-size:15px;font-weight:600;margin:0 0 16px;">진료과 목록 (<?= count($departments) ?>)</h2>

        <?php if ($departments === []): ?>
            <p style="font-size:14px;color:var(--color-text-muted);">등록된 진료과가 없습니다.</p>
        <?php else: ?>
            <div style="display:flex;flex-direction:column;gap:8px;">
                <?php foreach ($departments as $d): $did = (int) $d['id']; ?>
                <div style="display:grid;grid-template-columns:1.2fr 1.2fr 80px 90px auto auto;gap:12px;align-items:end;padding:12px;border:1px solid var(--color-border,#e5e7eb);border-radius:8px;">
                    <form action="/admin/departments/<?= $did ?>" method="POST"
                          style="display:contents;">
                        <?= csrf_field() ?>
                        <div>
                            <input type="text" name="code" class="form-control" value="<?= esc($d['code']) ?>" maxlength="30" required>
                        </div>
                        <div>
                            <input type="text" name="name" class="form-control" value="<?= esc($d['name']) ?>" maxlength="100" required>
                        </div>
                        <div>
                            <input type="number" name="sort" class="form-control" value="<?= (int) $d['sort'] ?>" min="0">
                        </div>
                        <div>
                            <label style="display:flex;align-items:center;gap:6px;font-size:13px;">
                                <input type="checkbox" name="is_active" value="1" <?= (int) $d['is_active'] === 1 ? 'checked' : '' ?>> 활성
                            </label>
                        </div>
                        <button type="submit" class="btn btn-outline btn-sm">저장</button>
                    </form>
                    <button type="button" class="btn btn-outline btn-sm" style="color:#ef4444;"
                            onclick="deleteDept(<?= $did ?>, <?= (int) $d['hospital_count'] ?>, '<?= esc($d['name'], 'js') ?>')">
                        삭제<?= (int) $d['hospital_count'] > 0 ? ' (' . (int) $d['hospital_count'] . ')' : '' ?>
                    </button>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
const CSRF = { name: '<?= csrf_token() ?>', hash: '<?= csrf_hash() ?>' };

function deleteDept(id, count, name) {
    if (count > 0) {
        alert(`'${name}' 진료과는 ${count}개 병원에 매핑되어 있어 삭제할 수 없습니다.\n비활성으로 변경하세요.`);
        return;
    }
    if (!confirm(`'${name}' 진료과를 삭제하시겠습니까?`)) return;

    const form = document.createElement('form');
    form.method = 'POST';
    form.action = `/admin/departments/${id}/delete`;
    const token = document.createElement('input');
    token.type = 'hidden';
    token.name = CSRF.name;
    token.value = CSRF.hash;
    form.appendChild(token);
    document.body.appendChild(form);
    form.submit();
}
</script>
