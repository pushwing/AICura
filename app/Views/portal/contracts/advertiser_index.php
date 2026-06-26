<?php
/** @var bool $hasAdvertiser */
/** @var array<string, mixed>|null $advertiser */
/** @var bool $agreed */
/** @var string|null $agreedAtKst 동의 일시 (KST) */
/** @var array<string, mixed>|null $agencyInfo 갑(광고대행사) 정보 */
/** @var array<string, mixed>|null $contract */
/** @var array<int, array<string, mixed>> $orders */
/** @var array<int, string> $adTypeLabels */
/** @var array<int, string> $statusLabels */

// 갑(광고대행사) / 을(광고주) 당사자 표기
$gabName  = $agencyInfo['agency_company_name'] ?? null;
$gabCharge = $agencyInfo['agency_company_charge_name'] ?? null;
$eulName  = $advertiser['hospital_name'] ?? '';
$eulBizNo = $advertiser['business_no'] ?? null;
$eulCharge = $advertiser['contact_name'] ?? null;
?>
<div class="page-header" style="margin-bottom:20px;">
    <h1 class="page-title">계약 관리</h1>
</div>

<?php if (!$hasAdvertiser): ?>
    <div class="alert alert-danger">
        <span class="alert-icon">!</span>
        <div class="alert-body">연결된 광고주 정보가 없습니다. 담당 광고대행사 또는 운영팀에 문의해주세요.</div>
    </div>
<?php else: ?>

    <!-- 표준 광고대행 계약서 (공용 파셜) -->
    <?= view('portal/contracts/_standard_contract', [
        'agreed'      => $agreed,
        'agreedAtKst' => $agreedAtKst,
        'gabName'     => $gabName,
        'gabCharge'   => $gabCharge,
        'eulName'     => $eulName,
        'eulBizNo'    => $eulBizNo,
        'eulCharge'   => $eulCharge,
        'readonly'    => false,
    ]) ?>

    <!-- 수주계약 목록 -->
    <div class="card">
        <div class="card-header"><span class="card-title">수주계약 (충전) 내역</span></div>
        <div class="card-body">
            <?php if (empty($orders)): ?>
                <p class="text-sm" style="color:var(--color-text-muted);">
                    <?= $agreed ? '충전 내역이 없습니다. 광고 충전을 신청해보세요.' : '계약 동의 후 충전을 신청할 수 있습니다.' ?>
                </p>
            <?php else: ?>
                <div class="table-wrap">
                    <table class="table">
                        <thead><tr><th>상품</th><th style="text-align:right;">계약금액</th><th style="text-align:right;">잔액</th><th>상태</th><th>입금</th><th>신청일</th><th></th></tr></thead>
                        <tbody>
                        <?php foreach ($orders as $o): ?>
                            <tr>
                                <td><?= esc($adTypeLabels[(int) $o['ad_type2']] ?? '-') ?></td>
                                <td style="text-align:right;"><?= number_format((int) $o['ad_price']) ?>원</td>
                                <td style="text-align:right;"><?= number_format((int) $o['balance']) ?>원</td>
                                <td><?= esc($statusLabels[(int) $o['contract_status']] ?? '-') ?></td>
                                <td><?= empty($o['deposit_date']) ? '<span class="badge badge-warning">입금대기</span>' : '<span class="badge badge-success">입금완료</span>' ?></td>
                                <td class="text-sm"><?= esc($o['created_at_kst']) ?></td>
                                <td style="text-align:right;"><a href="/portal/contracts/orders/<?= (int) $o['id'] ?>" class="btn btn-outline btn-sm">상세</a></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

<?php endif; ?>
