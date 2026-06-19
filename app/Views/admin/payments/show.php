<?php
/** @var array<string, mixed> $payment */
/** @var array<string, array<string, string>> $statuses */
/** @var array<int, string> $paymentTypes */

$currentStatus = $payment['status'];
$statusInfo    = $statuses[$currentStatus] ?? ['label' => $currentStatus, 'color' => '#888'];

$refundTypes = [
    2 => '발행환불',
    5 => '계약환불',
];
?>

<div class="page-header" style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;">
    <div style="display:flex;align-items:center;gap:12px;">
        <a href="/admin/payments" style="color:var(--color-text-muted);text-decoration:none;">← 목록</a>
        <h1 class="page-title" style="margin:0;">결제 #<?= (int) $payment['id'] ?></h1>
        <span style="background:<?= esc($statusInfo['color']) ?>20;color:<?= esc($statusInfo['color']) ?>;padding:3px 10px;border-radius:4px;font-size:13px;">
            <?= esc($statusInfo['label']) ?>
        </span>
    </div>
    <button type="button" class="btn btn-danger btn-sm" onclick="openRefundModal()">환불 처리</button>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">

    <!-- 결제 정보 -->
    <div class="card">
        <div class="card-body">
            <h3 style="margin-bottom:16px;font-size:15px;">결제 정보</h3>
            <table style="width:100%;font-size:14px;border-collapse:collapse;">
                <?php
                $rows = [
                    ['병원명',      esc($payment['hospital_name'] ?? $payment['order_hospital_name'] ?? '-')],
                    ['결제 유형',   esc($paymentTypes[(int) $payment['type']] ?? '-')],
                    ['결제 금액',   number_format((int) $payment['amount']) . '원'],
                    ['결과 코드',   esc($payment['result_code'] ?? '-')],
                    ['거래 번호',   esc($payment['trans_no'] ?? '-')],
                    ['승인 일시',   esc($payment['auth_date'] ?? '-')],
                    ['승인 번호',   esc($payment['auth_no'] ?? '-')],
                    ['금융사',      esc($payment['fn_name'] ?? '-')],
                ];
                foreach ($rows as [$label, $value]):
                ?>
                <tr>
                    <td style="padding:8px 0;color:var(--color-text-muted);width:110px;"><?= $label ?></td>
                    <td style="padding:8px 0;"><?= $value ?></td>
                </tr>
                <?php endforeach; ?>
            </table>
        </div>
    </div>

    <!-- 가상계좌 / 계약 정보 -->
    <div style="display:flex;flex-direction:column;gap:16px;">

        <?php if (!empty($payment['vbank_no'])): ?>
        <div class="card">
            <div class="card-body">
                <h3 style="margin-bottom:12px;font-size:15px;">가상계좌 정보</h3>
                <table style="width:100%;font-size:14px;border-collapse:collapse;">
                    <?php
                    $vrows = [
                        ['계좌번호', esc($payment['vbank_no'])],
                        ['만료일시', esc($payment['vbank_expire'] ?? '-')],
                    ];
                    foreach ($vrows as [$label, $value]):
                    ?>
                    <tr>
                        <td style="padding:8px 0;color:var(--color-text-muted);width:80px;"><?= $label ?></td>
                        <td style="padding:8px 0;"><?= $value ?></td>
                    </tr>
                    <?php endforeach; ?>
                </table>
            </div>
        </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-body">
                <h3 style="margin-bottom:12px;font-size:15px;">계약 정보</h3>
                <table style="width:100%;font-size:14px;border-collapse:collapse;">
                    <?php
                    $crows = [
                        ['계약 ID',   esc((string) ($payment['contract_id'] ?? '-'))],
                        ['수주계약 ID', esc((string) ($payment['contract_order_id'] ?? '-'))],
                        ['계약금액',   number_format((int) ($payment['ad_price'] ?? 0)) . '원'],
                    ];
                    foreach ($crows as [$label, $value]):
                    ?>
                    <tr>
                        <td style="padding:8px 0;color:var(--color-text-muted);width:100px;"><?= $label ?></td>
                        <td style="padding:8px 0;"><?= $value ?></td>
                    </tr>
                    <?php endforeach; ?>
                </table>
                <?php if (!empty($payment['contract_order_id'])): ?>
                <div style="margin-top:12px;">
                    <a href="/admin/contracts/orders/<?= (int) $payment['contract_order_id'] ?>"
                       style="font-size:13px;color:var(--color-primary, #0F6E56);">수주계약 상세 보기 →</a>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <p style="font-size:12px;color:var(--color-text-muted);margin:0 0 4px;">등록일</p>
                <p style="font-size:14px;margin:0;"><?= esc($payment['created_at']) ?></p>
                <p style="font-size:12px;color:var(--color-text-muted);margin:8px 0 4px;">최종 수정</p>
                <p style="font-size:14px;margin:0;"><?= esc($payment['updated_at']) ?></p>
            </div>
        </div>
    </div>
</div>

<!-- 환불 모달 -->
<div id="refundModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:1000;align-items:center;justify-content:center;">
    <div style="background:var(--color-bg,#fff);border-radius:12px;padding:24px;min-width:380px;max-width:480px;">
        <h3 style="margin-bottom:16px;font-size:16px;">환불 처리</h3>
        <form method="POST" action="/admin/payments/<?= (int) $payment['id'] ?>/refund">
            <?= csrf_field() ?>
            <div style="margin-bottom:14px;">
                <label style="display:block;font-size:13px;margin-bottom:6px;color:var(--color-text-muted);">환불 유형</label>
                <select name="refund_type" class="form-control" required>
                    <option value="">선택하세요</option>
                    <?php foreach ($refundTypes as $val => $label): ?>
                        <option value="<?= (int) $val ?>"><?= esc($label) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div style="margin-bottom:20px;">
                <label style="display:block;font-size:13px;margin-bottom:6px;color:var(--color-text-muted);">
                    환불 금액 (최대 <?= number_format((int) $payment['amount']) ?>원)
                </label>
                <input type="number" name="refund_amount" class="form-control"
                       min="1" max="<?= (int) $payment['amount'] ?>"
                       placeholder="환불할 금액 입력" required>
            </div>
            <div style="display:flex;justify-content:flex-end;gap:8px;">
                <button type="button" onclick="closeRefundModal()" class="btn btn-outline btn-sm">취소</button>
                <button type="submit" class="btn btn-danger btn-sm">환불 처리</button>
            </div>
        </form>
    </div>
</div>

<script>
function openRefundModal() {
    document.getElementById('refundModal').style.display = 'flex';
}

function closeRefundModal() {
    document.getElementById('refundModal').style.display = 'none';
}

document.getElementById('refundModal').addEventListener('click', (e) => {
    if (e.target === e.currentTarget) closeRefundModal();
});
</script>
