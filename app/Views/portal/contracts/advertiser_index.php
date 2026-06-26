<?php
/** @var bool $hasAdvertiser */
/** @var array<string, mixed>|null $advertiser */
/** @var bool $agreed */
/** @var array<string, mixed>|null $contract */
/** @var array<int, array<string, mixed>> $orders */
/** @var array<int, string> $adTypeLabels */
/** @var array<int, string> $statusLabels */
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

    <!-- 계약 동의 카드 -->
    <div class="card" style="margin-bottom:24px;border-left:3px solid <?= $agreed ? '#1D9E75' : '#f59e0b' ?>;">
        <div class="card-body" style="display:flex;align-items:center;justify-content:space-between;gap:16px;">
            <div>
                <p style="font-weight:600;margin-bottom:4px;">
                    <?= $agreed ? '계약 동의 완료 — 사용가능' : '계약 동의 대기' ?>
                </p>
                <p class="text-sm" style="color:var(--color-text-muted);">
                    <?= $agreed
                        ? '광고 충전(수주계약)을 신청할 수 있습니다.'
                        : '계약에 동의해야 광고 서비스를 이용할 수 있습니다.' ?>
                </p>
            </div>
            <?php if (!$agreed): ?>
                <form action="/portal/contracts/agree" method="POST" onsubmit="return confirm('계약에 동의하시겠습니까?');">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-primary btn-sm">계약 동의</button>
                </form>
            <?php else: ?>
                <a href="/portal/contracts/orders/new" class="btn btn-primary btn-sm">+ 광고 충전 신청</a>
            <?php endif; ?>
        </div>
    </div>

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
