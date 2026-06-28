<?php
/** @var array<string, mixed> $user */
/** @var array<int, string> $userTypeLabels */
/** @var array<int, string> $whereFromLabels */
/** @var array<int, string> $providerLabels */
/** @var list<array<string, mixed>> $agencyAdvertisers */
/** @var array<int, string> $adType2Labels */
/** @var list<array{id:int, campaign_title:string, status_label:string, created_at:string}> $events */
/** @var int $eventsTotal */
/** @var list<array{id:int, type_label:string, subject:string, rate:int, is_deleted:bool, created_at:string}> $reviews */
/** @var int $reviewsTotal */
/** @var int $subPerPage */

$adType2Labels = $adType2Labels ?? [];

$userType   = (int) $user['user_type'];
$typeLabel  = $userTypeLabels[$userType] ?? (string) $userType;
$isDormant  = (int) $user['is_dormant'];
$isActive   = (int) ($user['is_active'] ?? 1);
$isAgency   = (int) ($user['is_agency_account'] ?? 0) === 1;
$whereFrom  = (int) ($user['where_from'] ?? 0);
$provider   = (int) ($user['provider'] ?? 0);
$userId     = (int) $user['id'];

$agencyAdvertisers = $agencyAdvertisers ?? [];

// 상태 코드 → 라벨
$statusLabels = [1 => '활성', 2 => '중지', 3 => '해지'];

$backType = match (true) {
    $isAgency                                   => 4,
    in_array($userType, [201, 202, 203], true) => 3, // 광고주/병원
    default                                     => 2, // 운영자(및 기타)
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
                        ['가입 경로',    esc($whereFromLabels[$whereFrom] ?? '-')],
                        ['로그인 방식',  esc($providerLabels[$provider] ?? '-')],
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

        <!-- 상태 관리 -->
        <div class="card">
            <div class="card-body">
                <h3 style="margin-bottom:16px;font-size:15px;">상태 관리</h3>
                <div style="display:flex;flex-direction:column;gap:12px;">
                    <div style="display:flex;align-items:center;gap:12px;">
                        <label for="dormantSelect" style="width:90px;font-size:14px;color:var(--color-text-muted);">휴면 상태</label>
                        <select id="dormantSelect" class="form-control" style="flex:1;">
                            <option value="1" <?= $isDormant === 1 ? 'selected' : '' ?>>활성</option>
                            <option value="0" <?= $isDormant === 0 ? 'selected' : '' ?>>휴면</option>
                        </select>
                    </div>
                    <div style="display:flex;align-items:center;gap:12px;">
                        <label for="activeSelect" style="width:90px;font-size:14px;color:var(--color-text-muted);">계정 활성</label>
                        <select id="activeSelect" class="form-control" style="flex:1;">
                            <option value="1" <?= $isActive === 1 ? 'selected' : '' ?>>활성</option>
                            <option value="0" <?= $isActive === 0 ? 'selected' : '' ?>>비활성</option>
                        </select>
                    </div>
                    <button type="button" id="statusApply" class="btn btn-primary" style="align-self:flex-end;">상태 변경</button>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- 신청한 이벤트 -->
<div class="card" style="margin-top:20px;">
    <div class="card-body">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;">
            <h3 style="margin:0;font-size:15px;">신청한 이벤트</h3>
            <span style="font-size:13px;color:var(--color-text-muted);">총 <strong style="color:var(--color-text);"><?= number_format($eventsTotal) ?></strong>건</span>
        </div>
        <?php if ($eventsTotal === 0): ?>
        <p style="font-size:14px;color:var(--color-text-muted);margin:0;">신청한 이벤트가 없습니다.</p>
        <?php else: ?>
        <table style="width:100%;font-size:14px;border-collapse:collapse;">
            <thead>
                <tr style="border-bottom:2px solid var(--color-border,#e5e7eb);text-align:left;color:var(--color-text-muted);">
                    <th style="padding:8px 4px;">캠페인</th>
                    <th style="padding:8px 4px;width:100px;">상태</th>
                    <th style="padding:8px 4px;width:120px;">신청일</th>
                </tr>
            </thead>
            <tbody id="eventsBody">
                <?php foreach ($events as $ev): ?>
                <tr style="border-bottom:1px solid var(--color-border,#f3f4f6);">
                    <td style="padding:8px 4px;">
                        <a href="/admin/call-requests/<?= (int) $ev['id'] ?>" style="color:var(--color-primary,#0F6E56);text-decoration:none;">
                            <?= esc($ev['campaign_title']) ?>
                        </a>
                    </td>
                    <td style="padding:8px 4px;"><?= esc($ev['status_label']) ?></td>
                    <td style="padding:8px 4px;"><?= esc($ev['created_at']) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php if ($eventsTotal > $subPerPage): ?>
        <div style="text-align:center;margin-top:12px;">
            <button type="button" id="eventsMore" class="btn btn-outline" data-page="1">더보기</button>
        </div>
        <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<!-- 작성한 후기 -->
<div class="card" style="margin-top:20px;">
    <div class="card-body">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;">
            <h3 style="margin:0;font-size:15px;">작성한 후기</h3>
            <span style="font-size:13px;color:var(--color-text-muted);">총 <strong style="color:var(--color-text);"><?= number_format($reviewsTotal) ?></strong>건</span>
        </div>
        <?php if ($reviewsTotal === 0): ?>
        <p style="font-size:14px;color:var(--color-text-muted);margin:0;">작성한 후기가 없습니다.</p>
        <?php else: ?>
        <table style="width:100%;font-size:14px;border-collapse:collapse;">
            <thead>
                <tr style="border-bottom:2px solid var(--color-border,#e5e7eb);text-align:left;color:var(--color-text-muted);">
                    <th style="padding:8px 4px;width:80px;">유형</th>
                    <th style="padding:8px 4px;">제목</th>
                    <th style="padding:8px 4px;width:70px;text-align:right;">평점</th>
                    <th style="padding:8px 4px;width:120px;">작성일</th>
                </tr>
            </thead>
            <tbody id="reviewsBody">
                <?php foreach ($reviews as $rv): ?>
                <tr style="border-bottom:1px solid var(--color-border,#f3f4f6);">
                    <td style="padding:8px 4px;"><?= esc($rv['type_label']) ?></td>
                    <td style="padding:8px 4px;">
                        <a href="/admin/reviews/<?= (int) $rv['id'] ?>" style="color:var(--color-primary,#0F6E56);text-decoration:none;<?= $rv['is_deleted'] ? 'text-decoration:line-through;color:#9ca3af;' : '' ?>">
                            <?= esc($rv['subject']) ?>
                        </a>
                    </td>
                    <td style="padding:8px 4px;text-align:right;"><?= (int) $rv['rate'] ?></td>
                    <td style="padding:8px 4px;"><?= esc($rv['created_at']) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php if ($reviewsTotal > $subPerPage): ?>
        <div style="text-align:center;margin-top:12px;">
            <button type="button" id="reviewsMore" class="btn btn-outline" data-page="1">더보기</button>
        </div>
        <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<script>
(function () {
    const userId = <?= $userId ?>;

    // ── 상태 변경 ──
    const statusApply = document.getElementById('statusApply');
    if (statusApply) {
        statusApply.addEventListener('click', async () => {
            statusApply.disabled = true;
            const original = statusApply.textContent;
            statusApply.textContent = '처리 중...';
            try {
                const res = await fetch('/admin/users/' + userId + '/status', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        ...csrfHeaders(),
                    },
                    body: JSON.stringify({
                        is_dormant: Number(document.getElementById('dormantSelect').value),
                        is_active: Number(document.getElementById('activeSelect').value),
                    }),
                });
                const data = await res.json();
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.message ?? '처리 실패');
                    statusApply.disabled = false;
                    statusApply.textContent = original;
                }
            } catch (err) {
                alert('오류가 발생했습니다.');
                statusApply.disabled = false;
                statusApply.textContent = original;
            }
        });
    }

    // ── 더보기 (신청 이벤트·작성 후기 공통) ──
    function setupMore(btnId, bodyId, endpoint, renderRow) {
        const btn = document.getElementById(btnId);
        if (!btn) return;
        btn.addEventListener('click', async () => {
            const nextPage = Number(btn.dataset.page) + 1;
            btn.disabled = true;
            btn.textContent = '불러오는 중...';
            try {
                const res = await fetch('/admin/users/' + userId + '/' + endpoint + '?page=' + nextPage, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                });
                const data = await res.json();
                const tbody = document.getElementById(bodyId);
                data.items.forEach((item) => tbody.appendChild(renderRow(item)));
                btn.dataset.page = String(nextPage);
                if (data.has_more) {
                    btn.disabled = false;
                    btn.textContent = '더보기';
                } else {
                    btn.remove();
                }
            } catch (err) {
                alert('오류가 발생했습니다.');
                btn.disabled = false;
                btn.textContent = '더보기';
            }
        });
    }

    function cell(text, opts) {
        const td = document.createElement('td');
        td.style.padding = '8px 4px';
        if (opts && opts.align) td.style.textAlign = opts.align;
        if (opts && opts.link) {
            const a = document.createElement('a');
            a.href = opts.link;
            a.style.color = 'var(--color-primary,#0F6E56)';
            a.style.textDecoration = 'none';
            if (opts.strike) { a.style.textDecoration = 'line-through'; a.style.color = '#9ca3af'; }
            a.textContent = text;
            td.appendChild(a);
        } else {
            td.textContent = text;
        }
        return td;
    }

    function rowBase() {
        const tr = document.createElement('tr');
        tr.style.borderBottom = '1px solid var(--color-border,#f3f4f6)';
        return tr;
    }

    setupMore('eventsMore', 'eventsBody', 'events', (it) => {
        const tr = rowBase();
        tr.appendChild(cell(it.campaign_title, { link: '/admin/call-requests/' + it.id }));
        tr.appendChild(cell(it.status_label));
        tr.appendChild(cell(it.created_at));
        return tr;
    });

    setupMore('reviewsMore', 'reviewsBody', 'reviews', (it) => {
        const tr = rowBase();
        tr.appendChild(cell(it.type_label));
        tr.appendChild(cell(it.subject, { link: '/admin/reviews/' + it.id, strike: it.is_deleted }));
        tr.appendChild(cell(String(it.rate), { align: 'right' }));
        tr.appendChild(cell(it.created_at));
        return tr;
    });
})();
</script>

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
        <p style="font-size:12px;color:var(--color-text-muted);margin:0 0 8px;">광고주 행을 클릭하면 건별 수주내역이 펼쳐집니다.</p>
        <table style="width:100%;font-size:14px;border-collapse:collapse;">
            <thead>
                <tr style="border-bottom:2px solid var(--color-border,#e5e7eb);text-align:left;color:var(--color-text-muted);">
                    <th style="padding:8px 4px;width:24px;"></th>
                    <th style="padding:8px 4px;">광고주명</th>
                    <th style="padding:8px 4px;">담당자</th>
                    <th style="padding:8px 4px;">상태</th>
                    <th style="padding:8px 4px;">계약 동의</th>
                    <th style="padding:8px 4px;text-align:right;">계약 건수</th>
                    <th style="padding:8px 4px;text-align:right;">계약 금액</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($agencyAdvertisers as $i => $a): ?>
                <?php
                $advUrl = '/admin/advertisers/' . (int) ($a['id'] ?? 0);
                /** @var list<array<string, mixed>> $orders */
                $orders     = $a['orders'] ?? [];
                $orderTotal = array_sum(array_map(static fn (array $o): int => (int) ($o['ad_price'] ?? 0), $orders));
                ?>
                <tr style="border-bottom:1px solid var(--color-border,#f3f4f6);cursor:pointer;"
                    onclick="toggleOrders(<?= (int) $i ?>)"
                    onmouseover="this.style.background='var(--color-bg-subtle,#f9fafb)'"
                    onmouseout="this.style.background=''">
                    <td style="padding:8px 4px;text-align:center;color:var(--color-text-muted);">
                        <span id="adv-caret-<?= (int) $i ?>" style="display:inline-block;transition:transform .15s;">▸</span>
                    </td>
                    <td style="padding:8px 4px;">
                        <a href="<?= esc($advUrl, 'attr') ?>" onclick="event.stopPropagation();"
                           style="color:var(--color-primary,#0F6E56);text-decoration:none;font-weight:500;">
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
                <tr id="adv-orders-<?= (int) $i ?>" style="display:none;background:var(--color-bg-subtle,#f9fafb);">
                    <td></td>
                    <td colspan="6" style="padding:12px 4px;">
                        <?php if ($orders === []): ?>
                        <p style="font-size:13px;color:var(--color-text-muted);margin:0;">건별 수주내역이 없습니다.</p>
                        <?php else: ?>
                        <table style="width:100%;font-size:13px;border-collapse:collapse;">
                            <thead>
                                <tr style="border-bottom:1px solid var(--color-border,#e5e7eb);text-align:left;color:var(--color-text-muted);">
                                    <th style="padding:6px 4px;">계약명</th>
                                    <th style="padding:6px 4px;">광고상품</th>
                                    <th style="padding:6px 4px;">상태</th>
                                    <th style="padding:6px 4px;">등록일</th>
                                    <th style="padding:6px 4px;text-align:right;">금액</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($orders as $o): ?>
                                <?php $orderUrl = '/admin/contracts/orders/' . (int) ($o['id'] ?? 0); ?>
                                <tr style="border-bottom:1px solid var(--color-border,#f3f4f6);">
                                    <td style="padding:6px 4px;">
                                        <a href="<?= esc($orderUrl, 'attr') ?>" style="color:var(--color-primary,#0F6E56);text-decoration:none;">
                                            <?= esc($o['title'] ?? '-') ?>
                                        </a>
                                    </td>
                                    <td style="padding:6px 4px;"><?= esc($adType2Labels[(int) ($o['ad_type2'] ?? 0)] ?? '-') ?></td>
                                    <td style="padding:6px 4px;">
                                        <?php $isUnpaid = ($o['status_label'] ?? '') === '미입금'; ?>
                                        <span style="<?= $isUnpaid ? 'color:#dc2626;font-weight:600;' : '' ?>"><?= esc($o['status_label'] ?? '-') ?></span>
                                    </td>
                                    <td style="padding:6px 4px;"><?= esc(substr((string) ($o['created_at'] ?? ''), 0, 10) ?: '-') ?></td>
                                    <td style="padding:6px 4px;text-align:right;">₩<?= number_format((int) ($o['ad_price'] ?? 0)) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr style="border-top:2px solid var(--color-border,#e5e7eb);font-weight:600;">
                                    <td colspan="4" style="padding:6px 4px;text-align:right;">총금액</td>
                                    <td style="padding:6px 4px;text-align:right;color:var(--color-primary,#0F6E56);">₩<?= number_format($orderTotal) ?></td>
                                </tr>
                            </tfoot>
                        </table>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <script>
        function toggleOrders(i) {
            var row = document.getElementById('adv-orders-' + i);
            var caret = document.getElementById('adv-caret-' + i);
            if (!row) return;
            var open = row.style.display === 'none';
            row.style.display = open ? 'table-row' : 'none';
            if (caret) caret.style.transform = open ? 'rotate(90deg)' : '';
        }
        </script>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>
