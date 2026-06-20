<?php
/** @var array<int, string> $adTypeLabels */

/** @var array<string, string> $errors */
$errors = session('errors') ?? [];
?>
<div class="page-header" style="display:flex;align-items:center;gap:12px;margin-bottom:24px;">
    <a href="/portal/contracts" style="color:var(--color-text-muted);text-decoration:none;">← 계약 관리</a>
    <h1 class="page-title" style="margin:0;">광고 충전 신청</h1>
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
        <p class="text-sm" style="color:var(--color-text-muted);margin-bottom:20px;">
            충전 금액은 CPA 광고비로 사용되며, 앱에서 신청(DB)이 들어올 때마다 광고 단가만큼 차감됩니다.
            신청 후 <strong>입금이 확인되면</strong> 광고 잔액에 반영됩니다.
        </p>
        <form action="/portal/contracts/orders" method="POST">
            <?= csrf_field() ?>

            <div style="margin-bottom:20px;">
                <label style="display:block;font-size:14px;font-weight:500;margin-bottom:6px;">광고 상품 <span style="color:#ef4444">*</span></label>
                <select name="ad_type2" class="form-control" required>
                    <option value="">선택</option>
                    <?php foreach ($adTypeLabels as $val => $label): ?>
                        <option value="<?= (int) $val ?>" <?= (string) old('ad_type2') === (string) $val ? 'selected' : '' ?>><?= esc($label) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div style="margin-bottom:20px;">
                <label style="display:block;font-size:14px;font-weight:500;margin-bottom:6px;">충전 금액(원) <span style="color:#ef4444">*</span></label>
                <input type="number" name="ad_price" class="form-control" min="1" step="1"
                       value="<?= esc((string) old('ad_price')) ?>" placeholder="예: 1000000" required>
            </div>

            <div style="margin-bottom:28px;">
                <label style="display:block;font-size:14px;font-weight:500;margin-bottom:6px;">결제 방법 <span style="color:#ef4444">*</span></label>
                <select name="pay_type" class="form-control" required>
                    <option value="1" <?= (string) old('pay_type') === '1' ? 'selected' : '' ?>>무통장입금(가상계좌)</option>
                    <option value="2" <?= (string) old('pay_type') === '2' ? 'selected' : '' ?>>신용카드</option>
                </select>
            </div>

            <div style="display:flex;gap:8px;">
                <button type="submit" class="btn btn-primary">충전 신청</button>
                <a href="/portal/contracts" class="btn btn-outline">취소</a>
            </div>
        </form>
    </div>
</div>
