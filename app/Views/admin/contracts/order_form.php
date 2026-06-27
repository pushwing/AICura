<?php
/** @var array<string, mixed>|null $order */
/** @var int|string|null $contractId */

$isEdit     = $order !== null;
$title      = $isEdit ? '수주계약 수정' : '수주계약 등록';
$formAction = $isEdit
    ? '/admin/contracts/orders/' . (int) $order['id']
    : '/admin/contracts/orders';

$canEditPrice = !$isEdit
    || (empty($order['deposit_date']) && empty($order['tax_issue_date']));
?>
<div class="page-header" style="display:flex;align-items:center;gap:12px;margin-bottom:20px;">
    <a href="<?= $isEdit ? '/admin/contracts/orders/' . (int) $order['id'] : '/admin/contracts/orders' ?>"
       style="color:var(--color-text-muted);text-decoration:none;">← 뒤로</a>
    <h1 class="page-title" style="margin:0;"><?= $title ?></h1>
</div>

<?php if (!empty(session()->getFlashdata('errors'))): ?>
<div class="alert alert-danger" style="margin-bottom:16px;">
    <ul style="margin:0;padding-left:18px;">
        <?php foreach ((array) session()->getFlashdata('errors') as $err): ?>
            <li><?= esc($err) ?></li>
        <?php endforeach; ?>
    </ul>
</div>
<?php endif; ?>

<div class="card">
    <div class="card-body">
        <form method="POST" action="<?= $formAction ?>">
            <?= csrf_field() ?>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">

                <div>
                    <label style="display:block;font-size:13px;margin-bottom:6px;color:var(--color-text-muted);">병원 ID <span style="color:#ef4444;">*</span></label>
                    <input type="number" name="hospital_id" class="form-control"
                           value="<?= esc((string) ($order['hospital_id'] ?? '')) ?>" required>
                </div>

                <div>
                    <label style="display:block;font-size:13px;margin-bottom:6px;color:var(--color-text-muted);">병원명 <span style="color:#ef4444;">*</span></label>
                    <input type="text" name="hospital_name" class="form-control"
                           value="<?= esc($order['hospital_name'] ?? '') ?>" required>
                </div>

                <div>
                    <label style="display:block;font-size:13px;margin-bottom:6px;color:var(--color-text-muted);">계약 유형 <span style="color:#ef4444;">*</span></label>
                    <select name="contract_type" class="form-control" <?= $isEdit ? 'disabled' : '' ?>>
                        <option value="1" <?= ($order['contract_type'] ?? 1) == 1 ? 'selected' : '' ?>>신규</option>
                        <option value="2" <?= ($order['contract_type'] ?? 1) == 2 ? 'selected' : '' ?>>재계약</option>
                    </select>
                </div>

                <div>
                    <label style="display:block;font-size:13px;margin-bottom:6px;color:var(--color-text-muted);">계약 방식 <span style="color:#ef4444;">*</span></label>
                    <select name="ad_type" class="form-control" <?= $isEdit ? 'disabled' : '' ?>>
                        <option value="1" <?= ($order['ad_type'] ?? 1) == 1 ? 'selected' : '' ?>>CPA (이벤트신청)</option>
                        <option value="2" <?= ($order['ad_type'] ?? 1) == 2 ? 'selected' : '' ?>>CPM (기간노출)</option>
                    </select>
                </div>

                <div>
                    <label style="display:block;font-size:13px;margin-bottom:6px;color:var(--color-text-muted);">상품 종류 <span style="color:#ef4444;">*</span></label>
                    <select name="ad_type2" class="form-control" <?= $isEdit ? 'disabled' : '' ?>>
                        <?php foreach ([1 => '이벤트', 2 => '메인배너', 3 => '이벤트존메인배너', 4 => 'CPM', 5 => '기타'] as $v => $l): ?>
                        <option value="<?= $v ?>" <?= ($order['ad_type2'] ?? 1) == $v ? 'selected' : '' ?>><?= esc($l) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label style="display:block;font-size:13px;margin-bottom:6px;color:var(--color-text-muted);">계약금액 (원) <span style="color:#ef4444;">*</span></label>
                    <input type="number" name="ad_price" class="form-control" min="1"
                           value="<?= esc((string) ($order['ad_price'] ?? '')) ?>"
                           <?= !$canEditPrice ? 'disabled title="입금 또는 세금계산서 발행 후 수정 불가"' : '' ?> required>
                    <?php if (!$canEditPrice): ?>
                    <p style="font-size:12px;color:var(--color-text-muted);margin-top:4px;">입금 또는 세금계산서 발행 후에는 금액을 수정할 수 없습니다.</p>
                    <?php endif; ?>
                </div>

                <?php if (!$isEdit): ?>
                <div>
                    <label style="display:block;font-size:13px;margin-bottom:6px;color:var(--color-text-muted);">결제방식</label>
                    <select name="pay_type" class="form-control">
                        <option value="1">선불</option>
                        <option value="2">후불</option>
                    </select>
                </div>

                <div>
                    <label style="display:block;font-size:13px;margin-bottom:6px;color:var(--color-text-muted);">계약명 (미입력 시 병원명)</label>
                    <input type="text" name="title" class="form-control"
                           value="<?= esc($order['contract_title'] ?? '') ?>">
                </div>

                <input type="hidden" name="contract_id" value="<?= (int) ($contractId ?? $order['contract_id'] ?? 0) ?>">
                <?php endif; ?>

            </div>

            <!-- 세금계산서 정보 -->
            <hr style="margin:20px 0;border:none;border-top:1px solid var(--color-border,#e5e7eb);">
            <h3 style="font-size:14px;margin-bottom:14px;color:var(--color-text-muted);">세금계산서 정보 (선택)</h3>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                <div>
                    <label style="display:block;font-size:13px;margin-bottom:6px;color:var(--color-text-muted);">담당자명</label>
                    <input type="text" name="tax_charge_name" class="form-control"
                           value="<?= esc($order['tax_charge_name'] ?? '') ?>">
                </div>
                <div>
                    <label style="display:block;font-size:13px;margin-bottom:6px;color:var(--color-text-muted);">담당자 이메일</label>
                    <input type="email" name="tax_charge_email" class="form-control"
                           value="<?= esc($order['tax_charge_email'] ?? '') ?>">
                </div>
                <div>
                    <label style="display:block;font-size:13px;margin-bottom:6px;color:var(--color-text-muted);">사업자번호</label>
                    <input type="text" name="tax_business_no" class="form-control"
                           value="<?= esc($order['tax_business_no'] ?? '') ?>">
                </div>
            </div>

            <!-- 에이전시 정보 -->
            <hr style="margin:20px 0;border:none;border-top:1px solid var(--color-border,#e5e7eb);">
            <h3 style="font-size:14px;margin-bottom:14px;color:var(--color-text-muted);">에이전시 정보 (선택)</h3>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                <div>
                    <label style="display:block;font-size:13px;margin-bottom:6px;color:var(--color-text-muted);">에이전시 유저 ID</label>
                    <input type="number" name="agency_user_id" class="form-control"
                           value="<?= esc((string) ($order['agency_user_id'] ?? '')) ?>">
                </div>
                <div>
                    <label style="display:block;font-size:13px;margin-bottom:6px;color:var(--color-text-muted);">담당 운영자 ID</label>
                    <input type="number" name="manage_user_id" class="form-control"
                           value="<?= esc((string) ($order['manage_user_id'] ?? '')) ?>">
                </div>
                <div>
                    <label style="display:block;font-size:13px;margin-bottom:6px;color:var(--color-text-muted);">에이전시 회사명</label>
                    <input type="text" name="agency_company_name" class="form-control"
                           value="<?= esc($order['agency_company_name'] ?? '') ?>">
                </div>
                <div>
                    <label style="display:block;font-size:13px;margin-bottom:6px;color:var(--color-text-muted);">에이전시 수수료율 (%)</label>
                    <input type="number" name="agency_company_fee_rate" class="form-control" step="0.01" min="0" max="100"
                           value="<?= esc((string) ($order['agency_company_fee_rate'] ?? '')) ?>">
                </div>
            </div>

            <!-- 메모 -->
            <hr style="margin:20px 0;border:none;border-top:1px solid var(--color-border,#e5e7eb);">
            <div>
                <label style="display:block;font-size:13px;margin-bottom:6px;color:var(--color-text-muted);">메모</label>
                <textarea name="memo" class="form-control" rows="4"><?= esc($order['memo'] ?? '') ?></textarea>
            </div>

            <div style="display:flex;justify-content:flex-end;gap:8px;margin-top:24px;">
                <a href="<?= $isEdit ? '/admin/contracts/orders/' . (int) $order['id'] : '/admin/contracts/orders' ?>"
                   class="btn btn-outline btn-sm">취소</a>
                <button type="submit" class="btn btn-primary btn-sm">
                    <?= $isEdit ? '저장' : '등록' ?>
                </button>
            </div>

        </form>
    </div>
</div>
