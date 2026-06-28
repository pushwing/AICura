<?php
/** @var array<string, mixed> $advertiser */
/** @var bool $hasInvite */
/** @var array<string, mixed>|null $agencyInfo 갑(광고대행사) 정보 */
/** @var list<array<string, mixed>> $orders 건별 수주내역 */
/** @var array<int, string> $adType2Labels */

$statusLabels = [1 => '활성', 2 => '정지', 3 => '탈퇴'];
$agreed    = !empty($advertiser['contract_agreed_at']);
$hasOwner  = !empty($advertiser['owner_user_id']);
$hasInvite = $hasInvite ?? false;
$agencyInfo = $agencyInfo ?? null;

$orders        = $orders ?? [];
$adType2Labels = $adType2Labels ?? [];
$orderTotal    = array_sum(array_map(static fn (array $o): int => (int) ($o['ad_price'] ?? 0), $orders));
?>
<div class="page-header" style="display:flex;align-items:center;gap:12px;margin-bottom:24px;">
    <a href="/portal/advertisers" style="color:var(--color-text-muted);text-decoration:none;">← 목록</a>
    <h1 class="page-title" style="margin:0;"><?= esc($advertiser['hospital_name']) ?></h1>
    <?= $agreed ? '<span class="badge badge-success">사용가능</span>' : '<span class="badge badge-warning">계약대기</span>' ?>
</div>

<div class="card">
    <div class="card-header"><span class="card-title">기본 정보</span></div>
    <div class="card-body">
        <div class="table-wrap">
            <table class="table">
                <tbody>
                    <tr><th style="width:160px;">병원명</th><td><?= esc($advertiser['hospital_name']) ?></td></tr>
                    <tr><th>담당자</th><td><?= esc($advertiser['contact_name'] ?? '-') ?></td></tr>
                    <tr><th>연락처</th><td><?= esc($advertiser['contact_phone'] ?? '-') ?></td></tr>
                    <tr><th>이메일</th><td><?= esc($advertiser['contact_email'] ?? '-') ?></td></tr>
                    <tr><th>사업자등록번호</th><td><?= esc($advertiser['business_no'] ?? '-') ?></td></tr>
                    <tr><th>상태</th><td><?= esc($statusLabels[(int) $advertiser['status']] ?? '-') ?></td></tr>
                    <tr><th>계약 동의</th><td><?= $agreed ? esc($advertiser['contract_agreed_at_kst']) . ' 동의 완료' : '미동의 (광고주 동의 대기)' ?></td></tr>
                    <tr><th>광고주 계정 연결</th><td>
                        <?php if ($hasOwner): ?>
                            <span class="badge badge-success">연결됨</span> (user #<?= (int) $advertiser['owner_user_id'] ?>)
                        <?php elseif ($hasInvite): ?>
                            <span class="badge badge-warning">초대 응답 대기</span>
                        <?php else: ?>
                            미연결
                        <?php endif; ?>
                    </td></tr>
                    <tr><th>등록일</th><td><?= esc($advertiser['created_at_kst']) ?></td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- 건별 수주내역 (이슈 #59) -->
<div class="card" style="margin-top:24px;">
    <div class="card-header" style="display:flex;align-items:center;justify-content:space-between;">
        <span class="card-title">건별 수주내역</span>
        <span class="text-sm" style="color:var(--color-text-muted);">
            계약 <strong style="color:var(--color-text);"><?= number_format(count($orders)) ?></strong>건
            · 총 <strong style="color:var(--color-primary,#0F6E56);">₩<?= number_format($orderTotal) ?></strong>
        </span>
    </div>
    <div class="card-body">
        <?php if ($orders === []): ?>
        <p class="text-sm" style="color:var(--color-text-muted);margin:0;">건별 수주내역이 없습니다.</p>
        <?php else: ?>
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>계약명</th>
                        <th>광고상품</th>
                        <th>상태</th>
                        <th>등록일</th>
                        <th style="text-align:right;">금액</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $o): ?>
                    <tr>
                        <td><?= esc($o['title'] ?? '-') ?></td>
                        <td><?= esc($adType2Labels[(int) ($o['ad_type2'] ?? 0)] ?? '-') ?></td>
                        <td>
                            <?php $isUnpaid = ($o['status_label'] ?? '') === '미입금'; ?>
                            <span style="<?= $isUnpaid ? 'color:#dc2626;font-weight:600;' : '' ?>"><?= esc($o['status_label'] ?? '-') ?></span>
                        </td>
                        <td><?= esc(substr((string) ($o['created_at'] ?? ''), 0, 10) ?: '-') ?></td>
                        <td style="text-align:right;">₩<?= number_format((int) ($o['ad_price'] ?? 0)) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="4" style="text-align:right;">총금액</th>
                        <td style="text-align:right;color:var(--color-primary,#0F6E56);font-weight:600;">₩<?= number_format($orderTotal) ?></td>
                    </tr>
                </tfoot>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- 표준 광고대행 계약서 (광고주 화면과 동일, 조회 전용) -->
<div style="margin-top:24px;">
    <?= view('portal/contracts/_standard_contract', [
        'agreed'      => $agreed,
        'agreedAtKst' => $advertiser['contract_agreed_at_kst'] ?: null,
        'gabName'     => $agencyInfo['agency_company_name'] ?? null,
        'gabCharge'   => $agencyInfo['agency_company_charge_name'] ?? null,
        'eulName'     => $advertiser['hospital_name'] ?? '',
        'eulBizNo'    => $advertiser['business_no'] ?? null,
        'eulCharge'   => $advertiser['contact_name'] ?? null,
        'readonly'    => true,
    ]) ?>
</div>

<?php if (!$hasOwner && !$hasInvite): ?>
<div class="card" style="margin-top:24px;">
    <div class="card-header"><span class="card-title">광고주 계정 연결 초대</span></div>
    <div class="card-body">
        <p class="text-sm" style="color:var(--color-text-muted);margin-bottom:12px;">
            광고주 본인의 로그인 계정 이메일로 연결 초대를 보냅니다. 당사자가 수락해야 연결이 확정됩니다.
        </p>
        <form action="/portal/advertisers/<?= (int) $advertiser['id'] ?>/invite" method="POST"
              style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
            <?= csrf_field() ?>
            <input type="email" name="owner_email" class="form-control" style="max-width:320px;"
                   placeholder="advertiser@hospital.com" maxlength="255" required>
            <button type="submit" class="btn btn-primary">초대 보내기</button>
        </form>
    </div>
</div>
<?php endif; ?>
