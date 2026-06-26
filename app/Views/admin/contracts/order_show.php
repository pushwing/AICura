<?php
/** @var array<string, mixed> $order */

$contractStatusLabels = [
    1 => ['label' => '정상',    'color' => '#10b981'],
    2 => ['label' => '발행환불', 'color' => '#f59e0b'],
    3 => ['label' => '발행취소', 'color' => '#ef4444'],
    4 => ['label' => '계약취소', 'color' => '#6b7280'],
    5 => ['label' => '계약환불', 'color' => '#ef4444'],
    6 => ['label' => '이월종료', 'color' => '#6b7280'],
];

$adTypeLabels  = [1 => 'CPA (이벤트신청)', 2 => 'CPM (기간노출)'];
$adType2Labels = [1 => '이벤트', 2 => '메인배너', 3 => '이벤트존메인배너', 4 => 'CPM', 5 => '기타'];

$currentStatus = (int) ($order['contract_status'] ?? 1);
$statusInfo    = $contractStatusLabels[$currentStatus] ?? ['label' => '-', 'color' => '#6b7280'];
$canConfirm    = empty($order['deposit_date']) && $currentStatus === 1;
?>
<div class="page-header" style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;">
    <div style="display:flex;align-items:center;gap:12px;">
        <a href="/admin/contracts/orders" style="color:var(--color-text-muted);text-decoration:none;">← 수주계약 목록</a>
        <h1 class="page-title" style="margin:0;">수주계약 #<?= (int) $order['id'] ?></h1>
        <span style="background:<?= esc($statusInfo['color']) ?>20;color:<?= esc($statusInfo['color']) ?>;padding:3px 10px;border-radius:4px;font-size:13px;">
            <?= esc($statusInfo['label']) ?>
        </span>
    </div>
    <div style="display:flex;gap:8px;">
        <a href="/admin/contracts/orders/<?= (int) $order['id'] ?>/edit" class="btn btn-outline btn-sm">수정</a>
        <?php if ($canConfirm): ?>
        <button type="button" class="btn btn-primary btn-sm" onclick="openConfirmModal()">입금 확인</button>
        <?php endif; ?>
    </div>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">

    <!-- 기본 정보 -->
    <div class="card">
        <div class="card-body">
            <h3 style="font-size:15px;margin-bottom:16px;">계약 정보</h3>
            <table style="width:100%;font-size:14px;border-collapse:collapse;">
                <?php foreach ([
                    ['병원명',    esc($order['hospital_name'])],
                    ['계약명',    esc($order['contract_title'] ?? '-')],
                    ['계약방식',  $adTypeLabels[(int) ($order['ad_type'] ?? 0)] ?? '-'],
                    ['상품종류',  $adType2Labels[(int) ($order['ad_type2'] ?? 0)] ?? '-'],
                    ['계약금액',  number_format((int) $order['ad_price']) . '원'],
                    ['결제방식',  isset($order['pay_type']) ? ($order['pay_type'] == 1 ? '선불' : '후불') : '-'],
                    ['잔액',     number_format((int) ($order['balance'] ?? 0)) . '원'],
                ] as [$label, $value]): ?>
                <tr>
                    <td style="padding:8px 0;color:var(--color-text-muted);width:110px;"><?= $label ?></td>
                    <td style="padding:8px 0;"><?= $value ?></td>
                </tr>
                <?php endforeach; ?>
            </table>
        </div>
    </div>

    <!-- 세금계산서 / 입금 정보 -->
    <div style="display:flex;flex-direction:column;gap:16px;">

        <div class="card">
            <div class="card-body">
                <h3 style="font-size:15px;margin-bottom:12px;">입금 정보</h3>
                <table style="width:100%;font-size:14px;border-collapse:collapse;">
                    <?php foreach ([
                        ['입금일',         esc($order['deposit_date'] ?? '미입금')],
                        ['담당 영업자 ID',  esc((string) ($order['manage_user_id'] ?? '-'))],
                        ['에이전시 ID',     esc((string) ($order['agency_user_id'] ?? '-'))],
                        ['에이전시명',      esc($order['agency_company_name'] ?? '-')],
                        ['에이전시 수수료', isset($order['agency_company_fee_rate']) ? $order['agency_company_fee_rate'] . '%' : '-'],
                    ] as [$label, $value]): ?>
                    <tr>
                        <td style="padding:8px 0;color:var(--color-text-muted);width:130px;"><?= $label ?></td>
                        <td style="padding:8px 0;"><?= $value ?></td>
                    </tr>
                    <?php endforeach; ?>
                </table>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <h3 style="font-size:15px;margin-bottom:12px;">세금계산서</h3>
                <table style="width:100%;font-size:14px;border-collapse:collapse;">
                    <?php foreach ([
                        ['담당자명',   esc($order['tax_charge_name'] ?? '-')],
                        ['담당자 이메일', esc($order['tax_charge_email'] ?? '-')],
                        ['사업자번호', esc($order['tax_business_no'] ?? '-')],
                        ['발행일',     esc($order['tax_issue_date'] ?? '미발행')],
                    ] as [$label, $value]): ?>
                    <tr>
                        <td style="padding:8px 0;color:var(--color-text-muted);width:130px;"><?= $label ?></td>
                        <td style="padding:8px 0;"><?= $value ?></td>
                    </tr>
                    <?php endforeach; ?>
                </table>
            </div>
        </div>

    </div>
</div>

<?php if (!empty($order['memo'])): ?>
<div class="card" style="margin-top:16px;">
    <div class="card-body">
        <h3 style="font-size:15px;margin-bottom:8px;">메모</h3>
        <p style="font-size:14px;white-space:pre-wrap;margin:0;"><?= esc($order['memo']) ?></p>
    </div>
</div>
<?php endif; ?>

<div style="margin-top:12px;">
    <span style="font-size:12px;color:var(--color-text-muted);">
        등록일: <?= esc($order['created_at']) ?>
        &nbsp;·&nbsp;
        수정일: <?= esc($order['updated_at']) ?>
    </span>
</div>

<?php if ($canConfirm): ?>
<!-- 입금 확인 모달 -->
<div id="confirmModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:1000;align-items:center;justify-content:center;">
    <div style="background:var(--color-bg,#fff);border-radius:12px;padding:24px;min-width:360px;">
        <h3 style="margin-bottom:12px;font-size:16px;">입금 확인</h3>
        <p style="font-size:14px;color:var(--color-text-muted);margin-bottom:20px;">
            <?= esc($order['hospital_name']) ?>의 계약금액
            <strong><?= number_format((int) $order['ad_price']) ?>원</strong> 입금을 확인하시겠습니까?
        </p>
        <form method="POST" action="/admin/contracts/orders/<?= (int) $order['id'] ?>/deposit-confirm">
            <?= csrf_field() ?>
            <div style="display:flex;justify-content:flex-end;gap:8px;">
                <button type="button" onclick="closeConfirmModal()" class="btn btn-outline btn-sm">취소</button>
                <button type="submit" class="btn btn-primary btn-sm">확인</button>
            </div>
        </form>
    </div>
</div>
<script>
function openConfirmModal()  { document.getElementById('confirmModal').style.display = 'flex'; }
function closeConfirmModal() { document.getElementById('confirmModal').style.display = 'none'; }
document.getElementById('confirmModal').addEventListener('click', (e) => {
    if (e.target === e.currentTarget) closeConfirmModal();
});
</script>
<?php endif; ?>
