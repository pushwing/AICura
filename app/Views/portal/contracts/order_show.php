<?php
/** @var array<string, mixed> $order */
/** @var array<int, array<string, mixed>> $history */
/** @var array<int, string> $adTypeLabels */
/** @var array<int, string> $statusLabels */
/** @var array<int, string> $depositLabels */

$balance = (int) ($order['balance'] ?? 0);
?>
<div class="page-header" style="display:flex;align-items:center;gap:12px;margin-bottom:20px;">
    <a href="/portal/contracts" style="color:var(--color-text-muted);text-decoration:none;">← 계약 관리</a>
    <h1 class="page-title" style="margin:0;">수주계약 #<?= (int) $order['id'] ?></h1>
    <span class="badge badge-success"><?= esc($statusLabels[(int) $order['contract_status']] ?? '-') ?></span>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">

    <!-- 계약 정보 -->
    <div class="card">
        <div class="card-body">
            <h3 style="font-size:15px;margin-bottom:16px;">계약 정보</h3>
            <table style="width:100%;font-size:14px;border-collapse:collapse;">
                <?php foreach ([
                    ['계약명',   esc($order['contract_title'] ?? '-')],
                    ['상품종류', esc($adTypeLabels[(int) $order['ad_type2']] ?? '-')],
                    ['계약금액', number_format((int) $order['ad_price']) . '원'],
                    ['결제방식', isset($order['pay_type']) ? ((int) $order['pay_type'] === 1 ? '선불' : '후불') : '-'],
                    ['입금일',   empty($order['deposit_date']) ? '미입금' : esc((string) $order['deposit_date'])],
                ] as [$label, $value]): ?>
                <tr>
                    <td style="padding:8px 0;color:var(--color-text-muted);width:110px;"><?= $label ?></td>
                    <td style="padding:8px 0;"><?= $value ?></td>
                </tr>
                <?php endforeach; ?>
            </table>
        </div>
    </div>

    <!-- 잔액 요약 -->
    <div class="card">
        <div class="card-body">
            <h3 style="font-size:15px;margin-bottom:16px;">잔액</h3>
            <p style="font-size:2rem;font-weight:700;color:var(--color-primary);">
                <?= number_format($balance) ?><span style="font-size:1rem;font-weight:500;">원</span>
            </p>
            <p class="text-sm" style="color:var(--color-text-muted);margin-top:8px;">
                입금 상태:
                <?= empty($order['deposit_date'])
                    ? '<span class="badge badge-warning">입금대기</span>'
                    : '<span class="badge badge-success">입금완료</span>' ?>
            </p>
        </div>
    </div>

</div>

<!-- 거래내역 -->
<div class="card" style="margin-top:20px;">
    <div class="card-header"><span class="card-title">거래내역</span></div>
    <div class="card-body">
        <?php if (empty($history)): ?>
            <p class="text-sm" style="color:var(--color-text-muted);">거래내역이 없습니다.</p>
        <?php else: ?>
            <div class="table-wrap">
                <table class="table">
                    <thead><tr><th>구분</th><th style="text-align:right;">금액</th><th>일시</th></tr></thead>
                    <tbody>
                    <?php foreach ($history as $h):
                        $isMinus = (int) $h['is_minus'] === 1;
                        $amount  = (int) $h['price'];
                    ?>
                        <tr>
                            <td><?= esc($depositLabels[(int) $h['status']] ?? '-') ?></td>
                            <td style="text-align:right;color:<?= $isMinus ? '#ef4444' : '#1D9E75' ?>;">
                                <?= $isMinus ? '-' : '+' ?><?= number_format(abs($amount)) ?>원
                            </td>
                            <td class="text-sm"><?= esc($h['created_at_kst']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
