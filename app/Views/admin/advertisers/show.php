<?php
/** @var array<string, mixed> $advertiser */

$statusLabels = [
    1 => ['label' => '활성', 'color' => '#10b981'],
    2 => ['label' => '정지', 'color' => '#f59e0b'],
    3 => ['label' => '탈퇴', 'color' => '#ef4444'],
];

$networkLabels = [0 => '일반', 1 => '네트워크(모)', 2 => '네트워크(자)'];
$payTypeLabels = [1 => '선불', 2 => '후불'];

$status = (int) $advertiser['status'];
$sl     = $statusLabels[$status] ?? ['label' => '-', 'color' => '#888'];

/** @var array<string, mixed> $kpi */
$kpi = $advertiser['kpi'] ?? ['total_amount' => 0, 'balance' => 0, 'active_campaigns' => 0];
?>

<div class="page-header" style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;">
    <div style="display:flex;align-items:center;gap:12px;">
        <a href="/admin/advertisers" style="color:var(--color-text-muted);text-decoration:none;">← 목록</a>
        <h1 class="page-title" style="margin:0;"><?= esc($advertiser['hospital_name']) ?></h1>
        <span style="background:<?= $sl['color'] ?>20;color:<?= $sl['color'] ?>;padding:3px 10px;border-radius:4px;font-size:13px;">
            <?= esc($sl['label']) ?>
        </span>
    </div>
    <a href="/admin/advertisers/<?= (int) $advertiser['id'] ?>/edit" class="btn btn-outline btn-sm">수정</a>
</div>

<!-- KPI 카드 -->
<div style="display:grid;grid-template-columns:repeat(3,1fr);gap:16px;margin-bottom:24px;">
    <div class="card">
        <div class="card-body" style="text-align:center;">
            <div style="font-size:12px;color:var(--color-text-muted);margin-bottom:6px;">계약 총금액</div>
            <div style="font-size:22px;font-weight:700;color:var(--color-primary,#0F6E56);">
                <?= number_format((int) $kpi['total_amount']) ?>원
            </div>
        </div>
    </div>
    <div class="card">
        <div class="card-body" style="text-align:center;">
            <div style="font-size:12px;color:var(--color-text-muted);margin-bottom:6px;">잔여 충전금</div>
            <div style="font-size:22px;font-weight:700;color:var(--color-primary,#0F6E56);">
                <?= number_format((int) $kpi['balance']) ?>원
            </div>
        </div>
    </div>
    <div class="card">
        <div class="card-body" style="text-align:center;">
            <div style="font-size:12px;color:var(--color-text-muted);margin-bottom:6px;">진행중 캠페인</div>
            <div style="font-size:22px;font-weight:700;color:var(--color-primary,#0F6E56);">
                <?= (int) $kpi['active_campaigns'] ?>건
            </div>
        </div>
    </div>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:24px;">

    <!-- 기본 정보 -->
    <div class="card">
        <div class="card-body">
            <h3 style="margin-bottom:16px;font-size:15px;">기본 정보</h3>
            <table style="width:100%;font-size:14px;border-collapse:collapse;">
                <tr style="border-bottom:1px solid var(--color-border,#eee);">
                    <th style="width:130px;padding:10px 0;font-weight:500;color:var(--color-text-muted);">병원 ID</th>
                    <td style="padding:10px 0;"><?= esc((string) $advertiser['hospital_id']) ?></td>
                </tr>
                <tr style="border-bottom:1px solid var(--color-border,#eee);">
                    <th style="width:130px;padding:10px 0;font-weight:500;color:var(--color-text-muted);">사업자등록번호</th>
                    <td style="padding:10px 0;"><?= esc($advertiser['business_no'] ?? '-') ?></td>
                </tr>
                <tr style="border-bottom:1px solid var(--color-border,#eee);">
                    <th style="width:130px;padding:10px 0;font-weight:500;color:var(--color-text-muted);">네트워크 유형</th>
                    <td style="padding:10px 0;"><?= esc($networkLabels[(int) $advertiser['is_network']] ?? '-') ?></td>
                </tr>
                <?php if ((int) $advertiser['is_network'] === 2 && !empty($advertiser['parent'])):
                    /** @var array<string, mixed> $parent */
                    $parent = $advertiser['parent'];
                ?>
                <tr style="border-bottom:1px solid var(--color-border,#eee);">
                    <th style="width:130px;padding:10px 0;font-weight:500;color:var(--color-text-muted);">모병원</th>
                    <td style="padding:10px 0;">
                        <a href="/admin/advertisers/<?= (int) $parent['id'] ?>" style="color:var(--color-primary,#0F6E56);text-decoration:none;">
                            <?= esc($parent['hospital_name']) ?>
                        </a>
                    </td>
                </tr>
                <?php endif; ?>
            </table>
        </div>
    </div>

    <!-- 담당자 정보 -->
    <div class="card">
        <div class="card-body">
            <h3 style="margin-bottom:16px;font-size:15px;">담당자 정보</h3>
            <table style="width:100%;font-size:14px;border-collapse:collapse;">
                <?php
                $contactRows = [
                    ['담당자',  esc($advertiser['contact_name'] ?? '-')],
                    ['이메일',  esc($advertiser['contact_email'] ?? '-')],
                    ['연락처',  esc($advertiser['contact_phone'] ?? '-')],
                    ['등록일',  esc($advertiser['created_at'] ?? '-')],
                ];
                foreach ($contactRows as [$label, $value]):
                ?>
                    <tr style="border-bottom:1px solid var(--color-border,#eee);">
                        <th style="width:80px;padding:10px 0;font-weight:500;color:var(--color-text-muted);"><?= esc($label) ?></th>
                        <td style="padding:10px 0;"><?= $value ?></td>
                    </tr>
                <?php endforeach; ?>
            </table>
        </div>
    </div>
</div>

<?php if (!empty($advertiser['children'])): ?>
<!-- 자병원 목록 (모병원인 경우) -->
<div class="card" style="margin-bottom:24px;">
    <div class="card-body">
        <h3 style="margin-bottom:16px;font-size:15px;">자병원 목록</h3>
        <table style="width:100%;font-size:14px;border-collapse:collapse;">
            <thead>
                <tr style="border-bottom:2px solid var(--color-border,#eee);">
                    <th style="padding:8px 0;text-align:left;">병원명</th>
                    <th style="padding:8px 0;text-align:left;width:80px;">상태</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($advertiser['children'] as $child):
                /** @var array<string, mixed> $child */
                $cs = $statusLabels[(int) $child['status']] ?? ['label' => '-', 'color' => '#888'];
            ?>
                <tr style="border-bottom:1px solid var(--color-border,#eee);">
                    <td style="padding:10px 0;">
                        <a href="/admin/advertisers/<?= (int) $child['id'] ?>" style="color:var(--color-primary,#0F6E56);text-decoration:none;">
                            <?= esc($child['hospital_name']) ?>
                        </a>
                    </td>
                    <td style="padding:10px 0;">
                        <span style="background:<?= $cs['color'] ?>20;color:<?= $cs['color'] ?>;padding:2px 8px;border-radius:4px;font-size:12px;">
                            <?= esc($cs['label']) ?>
                        </span>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<!-- 계약 목록 -->
<div class="card">
    <div class="card-body">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;">
            <h3 style="font-size:15px;margin:0;">계약 목록</h3>
            <a href="/admin/contracts/orders/new?hospital_id=<?= (int) $advertiser['hospital_id'] ?>&hospital_name=<?= urlencode((string) $advertiser['hospital_name']) ?>"
               class="btn btn-outline btn-sm">+ 수주계약 등록</a>
        </div>
        <?php if (empty($advertiser['contracts'])): ?>
            <p style="color:var(--color-text-muted);font-size:14px;">등록된 계약이 없습니다.</p>
        <?php else: ?>
            <table style="width:100%;font-size:14px;border-collapse:collapse;">
                <thead>
                    <tr style="border-bottom:2px solid var(--color-border,#eee);">
                        <th style="padding:8px 0;text-align:left;">ID</th>
                        <th style="padding:8px 0;text-align:left;">계약명</th>
                        <th style="padding:8px 0;text-align:left;width:80px;">결제방식</th>
                        <th style="padding:8px 0;text-align:left;width:160px;">등록일</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($advertiser['contracts'] as $contract):
                    /** @var array<string, mixed> $contract */
                ?>
                    <tr style="border-bottom:1px solid var(--color-border,#eee);">
                        <td style="padding:10px 0;">
                            <a href="/admin/contracts/<?= (int) $contract['id'] ?>" style="color:var(--color-primary,#0F6E56);text-decoration:none;">
                                #<?= (int) $contract['id'] ?>
                            </a>
                        </td>
                        <td style="padding:10px 0;"><?= esc($contract['title']) ?></td>
                        <td style="padding:10px 0;"><?= esc($payTypeLabels[(int) $contract['pay_type']] ?? '-') ?></td>
                        <td style="padding:10px 0;"><?= esc($contract['created_at']) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>
