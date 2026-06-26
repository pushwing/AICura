<?php
/** @var array<string, mixed> $request */
/** @var array<int, string> $statuses */
/** @var array<int, string> $devices */

$statusColors = [
    1 => '#f59e0b', 2 => '#6b7280', 3 => '#ef4444', 4 => '#6b7280', 5 => '#3b82f6',
    6 => '#ef4444', 7 => '#10b981', 8 => '#a855f7', 9 => '#6b7280',
];

$currentStatus = (int) $request['status'];
$sc = $statusColors[$currentStatus] ?? '#6b7280';
$sexLabel = match ((int) $request['sex']) { 1 => '남', 2 => '여', default => '-' };
?>

<div class="page-header" style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;">
    <div style="display:flex;align-items:center;gap:12px;">
        <a href="/admin/call-requests" style="color:var(--color-text-muted);text-decoration:none;">← 목록</a>
        <h1 class="page-title" style="margin:0;"><?= esc($request['name'] ?: '(이름없음)') ?></h1>
        <span style="background:<?= $sc ?>20;color:<?= $sc ?>;padding:3px 10px;border-radius:4px;font-size:13px;">
            <?= esc($statuses[$currentStatus] ?? (string) $currentStatus) ?>
        </span>
        <?php if ((int) $request['is_charged'] === 1): ?>
            <span style="background:#10b98120;color:#10b981;padding:3px 10px;border-radius:4px;font-size:13px;">과금완료</span>
        <?php endif; ?>
    </div>
</div>

<div style="display:grid;grid-template-columns:2fr 1fr;gap:20px;">

    <!-- 신청 정보 -->
    <div class="card">
        <div class="card-body">
            <h3 style="margin-bottom:16px;font-size:15px;">신청 정보</h3>
            <table style="width:100%;font-size:14px;border-collapse:collapse;">
                <?php
                $rows = [
                    ['병원',      esc($request['hospital_name'] ?? '-')],
                    ['캠페인',    esc($request['campaign_title'] ?? '-')],
                    ['신청자',    esc($request['name'] ?: '-')],
                    ['연락처',    esc($request['phone'] ?: '-')],
                    ['나이',      $request['age'] !== null ? esc((string) $request['age']) . '세' : '-'],
                    ['성별',      $sexLabel],
                    ['통화 가능 시간', esc($request['call_time'] ?: '-')],
                    ['디바이스',  esc($devices[(int) $request['device']] ?? '-')],
                    ['지역',      esc($request['region'] ?? '-')],
                    ['유입 경로', esc($request['funnel'] ?? '-')],
                    ['CPA 단가',  number_format((int) $request['event_cost']) . '원'],
                    ['문의 내용', nl2br(esc($request['content'] ?? '-'))],
                    ['예약 일시', !empty($request['reserved_at']) ? esc((string) $request['reserved_at']) : '-'],
                    ['확인일시',  esc($request['confirm_date'] ?? '-')],
                    ['신청일시',  esc($request['created_at'] ?? '-')],
                ];
                foreach ($rows as [$label, $value]):
                ?>
                <tr>
                    <td style="padding:8px 0;color:var(--color-text-muted);width:130px;vertical-align:top;"><?= $label ?></td>
                    <td style="padding:8px 0;"><?= $value ?></td>
                </tr>
                <?php endforeach; ?>
            </table>
        </div>
    </div>

    <!-- 우측: 상태 변경 + 메모 -->
    <div style="display:flex;flex-direction:column;gap:16px;">

        <!-- 상태 변경 -->
        <div class="card">
            <div class="card-body">
                <h3 style="margin-bottom:12px;font-size:15px;">상태 변경</h3>
                <div style="display:flex;flex-direction:column;gap:8px;">
                    <select id="statusSelect" class="form-control">
                        <?php foreach ($statuses as $val => $label): ?>
                            <option value="<?= esc((string) $val) ?>" <?= $currentStatus === $val ? 'selected' : '' ?>>
                                <?= esc($label) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div id="reservedWrap" style="display:none;">
                        <label style="font-size:12px;color:var(--color-text-muted);display:block;margin-bottom:4px;">예약 일시</label>
                        <input type="datetime-local" id="reservedAt" class="form-control"
                               value="<?= !empty($request['reserved_at']) ? esc(date('Y-m-d\TH:i', strtotime((string) $request['reserved_at']))) : '' ?>">
                    </div>
                    <button type="button" id="statusApply" class="btn btn-primary btn-sm">변경</button>
                </div>
            </div>
        </div>

        <!-- 메모 -->
        <div class="card">
            <div class="card-body">
                <h3 style="margin-bottom:12px;font-size:15px;">메모</h3>

                <form method="POST" action="/admin/call-requests/<?= (int) $request['id'] ?>/memos"
                      style="display:flex;flex-direction:column;gap:8px;margin-bottom:16px;">
                    <?= csrf_field() ?>
                    <textarea name="memo" class="form-control" rows="2" maxlength="500"
                              placeholder="메모 입력 (최대 500자)" required></textarea>
                    <button type="submit" class="btn btn-outline btn-sm" style="align-self:flex-end;">등록</button>
                </form>

                <?php if (empty($request['memos'])): ?>
                    <p style="color:var(--color-text-muted);font-size:13px;">등록된 메모가 없습니다.</p>
                <?php else: ?>
                    <?php foreach ($request['memos'] as $memo): ?>
                        <div style="padding:10px 0;border-bottom:1px solid var(--color-border);">
                            <div style="font-size:13px;white-space:pre-wrap;"><?= esc($memo['memo']) ?></div>
                            <div style="display:flex;align-items:center;justify-content:space-between;margin-top:6px;">
                                <span style="font-size:11px;color:var(--color-text-muted);">
                                    <?= esc($memo['admin_name'] ?? '-') ?> · <?= esc($memo['created_at']) ?>
                                </span>
                                <form method="POST"
                                      action="/admin/call-requests/<?= (int) $request['id'] ?>/memos/<?= (int) $memo['id'] ?>/delete"
                                      onsubmit="return confirm('메모를 삭제하시겠습니까?');" style="margin:0;">
                                    <?= csrf_field() ?>
                                    <button type="submit" style="border:none;background:none;color:#ef4444;font-size:11px;cursor:pointer;">삭제</button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

    </div>
</div>

<script>
const RESERVED_STATUS = 5;
const statusSelect = document.getElementById('statusSelect');
const reservedWrap = document.getElementById('reservedWrap');

function toggleReserved() {
    reservedWrap.style.display = Number(statusSelect.value) === RESERVED_STATUS ? 'block' : 'none';
}
statusSelect.addEventListener('change', toggleReserved);
toggleReserved();

document.getElementById('statusApply').addEventListener('click', async (e) => {
    const btn    = e.currentTarget;
    const status = Number(statusSelect.value);
    const payload = { status };

    if (status === RESERVED_STATUS) {
        const val = document.getElementById('reservedAt').value;
        if (!val) { alert('예약 일시를 입력해주세요.'); return; }
        payload.reserved_at = val.replace('T', ' ');
    }

    btn.disabled = true;
    btn.textContent = '처리 중...';

    try {
        const res = await fetch('/admin/call-requests/<?= (int) $request['id'] ?>/status', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                ...csrfHeaders(),
            },
            body: JSON.stringify(payload),
        });
        const data = await res.json();
        if (data.success) {
            location.reload();
        } else {
            alert(data.message ?? '처리 실패');
            btn.disabled = false;
            btn.textContent = '변경';
        }
    } catch (err) {
        alert('오류가 발생했습니다.');
        btn.disabled = false;
        btn.textContent = '변경';
    }
});
</script>
