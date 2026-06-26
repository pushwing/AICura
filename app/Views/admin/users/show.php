<?php
/** @var array<string, mixed> $user */
/** @var array<int, string> $userTypeLabels */
/** @var list<array<string, mixed>> $agencyAdvertisers */

$userType  = (int) $user['user_type'];
$typeLabel = $userTypeLabels[$userType] ?? (string) $userType;
$isDormant = (int) $user['is_dormant'];
$isAgency  = (int) ($user['is_agency_account'] ?? 0) === 1;

$agencyAdvertisers = $agencyAdvertisers ?? [];

// 상태 코드 → 라벨
$statusLabels = [1 => '활성', 2 => '중지', 3 => '해지'];

$backType = match (true) {
    $isAgency                                                => 4,
    in_array($userType, [1], true)                           => 1,
    in_array($userType, [2, 401, 402, 403, 404, 405], true) => 2,
    default                                                  => 3,
};
?>

<div class="page-header" style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;">
    <div style="display:flex;align-items:center;gap:12px;">
        <a href="/admin/users?type=<?= $backType ?>" style="color:var(--color-text-muted);text-decoration:none;">← 목록</a>
        <h1 class="page-title" style="margin:0;">사용자 #<?= (int) $user['id'] ?></h1>
        <span style="background:#0F6E5620;color:var(--color-primary,#0F6E56);padding:3px 10px;border-radius:4px;font-size:13px;">
            <?= esc($typeLabel) ?>
        </span>
        <?php if ($isAgency): ?>
        <span style="background:#1D9E7520;color:var(--color-secondary,#1D9E75);padding:3px 10px;border-radius:4px;font-size:13px;font-weight:600;">대행사</span>
        <?php endif; ?>
        <?php if ($isDormant === 0): ?>
        <span style="background:#f3f4f620;color:#9ca3af;padding:3px 10px;border-radius:4px;font-size:13px;">휴면</span>
        <?php endif; ?>
    </div>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">

    <!-- 기본 정보 -->
    <div class="card">
        <div class="card-body">
            <h3 style="margin-bottom:16px;font-size:15px;">기본 정보</h3>
            <table style="width:100%;font-size:14px;border-collapse:collapse;">
                <?php
                $rows = [
                    ['ID',        (string) (int) $user['id']],
                    ['이메일',    esc($user['email'] ?? '-')],
                    ['이름',      esc($user['username'] ?? '-')],
                    ['유형',      esc($typeLabel)],
                    ['전화번호',  esc($user['phone'] ?? '-')],
                    ['성별',      match($user['sex'] ?? null) { 'M' => '남성', 'F' => '여성', default => '-' }],
                    ['나이',      isset($user['age']) ? (string) (int) $user['age'] . '세' : '-'],
                    ['상태',      $isDormant === 1 ? '활성' : '휴면'],
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

    <!-- 계정 활동 정보 -->
    <div style="display:flex;flex-direction:column;gap:16px;">

        <div class="card">
            <div class="card-body">
                <h3 style="margin-bottom:16px;font-size:15px;">계정 정보</h3>
                <table style="width:100%;font-size:14px;border-collapse:collapse;">
                    <?php
                    $arows = [
                        ['가입 경로',    esc($user['where_from'] ?? '-')],
                        ['로그인 방식',  esc($user['provider'] ?? '-')],
                        ['에이전시 계정', (int) ($user['is_agency_account'] ?? 0) === 1 ? '예' : '아니오'],
                        ['헬스 포인트', isset($user['health_point']) ? number_format((int) $user['health_point']) : '-'],
                    ];
                    foreach ($arows as [$label, $value]):
                    ?>
                    <tr>
                        <td style="padding:8px 0;color:var(--color-text-muted);width:110px;"><?= $label ?></td>
                        <td style="padding:8px 0;"><?= $value ?></td>
                    </tr>
                    <?php endforeach; ?>
                </table>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <p style="font-size:12px;color:var(--color-text-muted);margin:0 0 4px;">최근 로그인</p>
                <p style="font-size:14px;margin:0;"><?= esc($user['last_login_at'] ?? '-') ?></p>
                <p style="font-size:12px;color:var(--color-text-muted);margin:12px 0 4px;">가입일</p>
                <p style="font-size:14px;margin:0;"><?= esc($user['created_at'] ?? '-') ?></p>
            </div>
        </div>

    </div>
</div>

<?php if ($isAgency): ?>
<?php
$advCount   = count($agencyAdvertisers);
$orderTotal = array_sum(array_map(static fn (array $a): int => (int) $a['order_count'], $agencyAdvertisers));
$priceTotal = array_sum(array_map(static fn (array $a): int => (int) $a['total_price'], $agencyAdvertisers));
?>
<div class="card" style="margin-top:20px;">
    <div class="card-body">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;">
            <h3 style="margin:0;font-size:15px;">소유 광고주 · 계약 요약</h3>
            <div style="display:flex;gap:16px;font-size:13px;color:var(--color-text-muted);">
                <span>광고주 <strong style="color:var(--color-text);"><?= number_format($advCount) ?></strong>개</span>
                <span>계약 <strong style="color:var(--color-text);"><?= number_format($orderTotal) ?></strong>건</span>
                <span>총 <strong style="color:var(--color-primary,#0F6E56);">₩<?= number_format($priceTotal) ?></strong></span>
            </div>
        </div>

        <?php if ($advCount === 0): ?>
        <p style="font-size:14px;color:var(--color-text-muted);margin:0;">소유한 광고주가 없습니다.</p>
        <?php else: ?>
        <table style="width:100%;font-size:14px;border-collapse:collapse;">
            <thead>
                <tr style="border-bottom:2px solid var(--color-border,#e5e7eb);text-align:left;color:var(--color-text-muted);">
                    <th style="padding:8px 4px;">광고주명</th>
                    <th style="padding:8px 4px;">담당자</th>
                    <th style="padding:8px 4px;">상태</th>
                    <th style="padding:8px 4px;">계약 동의</th>
                    <th style="padding:8px 4px;text-align:right;">계약 건수</th>
                    <th style="padding:8px 4px;text-align:right;">계약 금액</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($agencyAdvertisers as $a): ?>
                <?php $advUrl = '/admin/advertisers/' . (int) ($a['id'] ?? 0); ?>
                <tr style="border-bottom:1px solid var(--color-border,#f3f4f6);cursor:pointer;"
                    onclick="location.href='<?= esc($advUrl, 'attr') ?>'"
                    onmouseover="this.style.background='var(--color-bg-subtle,#f9fafb)'"
                    onmouseout="this.style.background=''">
                    <td style="padding:8px 4px;">
                        <a href="<?= esc($advUrl, 'attr') ?>" style="color:var(--color-primary,#0F6E56);text-decoration:none;font-weight:500;">
                            <?= esc($a['hospital_name'] ?? '-') ?>
                        </a>
                    </td>
                    <td style="padding:8px 4px;"><?= esc($a['contact_name'] ?? '-') ?></td>
                    <td style="padding:8px 4px;"><?= esc($statusLabels[(int) ($a['status'] ?? 0)] ?? '-') ?></td>
                    <td style="padding:8px 4px;">
                        <?php if (!empty($a['agreed'])): ?>
                        <span style="color:var(--color-primary,#0F6E56);">동의</span>
                        <?php else: ?>
                        <span style="color:#9ca3af;">대기</span>
                        <?php endif; ?>
                    </td>
                    <td style="padding:8px 4px;text-align:right;"><?= number_format((int) ($a['order_count'] ?? 0)) ?></td>
                    <td style="padding:8px 4px;text-align:right;">₩<?= number_format((int) ($a['total_price'] ?? 0)) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>
