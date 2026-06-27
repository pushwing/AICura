<?php
/** @var array<string, mixed> $request */
/** @var array<int, string> $statuses */
/** @var array<int, string> $devices */
/** @var array<int, string> $refundStatuses 환불요청 유발 상태 (3/8/9) */
/** @var int $reservedStatus 예약 상태값 (5) */
/** @var array<string, mixed>|null $refund 최신 환불요청 */
/** @var array<int, string> $refundLabels 환불요청 상태 라벨 */
/** @var bool $locked 환불요청 처리 중 잠금 여부 */

$statusColors = [
    1 => '#f59e0b', 2 => '#6b7280', 3 => '#ef4444', 4 => '#6b7280', 5 => '#3b82f6',
    6 => '#ef4444', 7 => '#10b981', 8 => '#a855f7', 9 => '#6b7280',
];

$currentStatus = (int) $request['status'];
$sc = $statusColors[$currentStatus] ?? '#6b7280';
$sexLabel = match ((int) $request['sex']) { 1 => '남', 2 => '여', default => '-' };

$refundStatus = $refund !== null ? (int) $refund['status'] : 0;
?>

<div class="page-header" style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;">
    <div style="display:flex;align-items:center;gap:12px;">
        <a href="/portal/call-requests" style="color:var(--color-text-muted);text-decoration:none;">← 목록</a>
        <h1 class="page-title" style="margin:0;"><?= esc($request['name'] ?: '(이름없음)') ?></h1>
        <span style="background:<?= $sc ?>20;color:<?= $sc ?>;padding:3px 10px;border-radius:4px;font-size:13px;">
            <?= esc($statuses[$currentStatus] ?? (string) $currentStatus) ?>
        </span>
    </div>
</div>

<?php if ($refund !== null && $refundStatus === 1): ?>
    <div class="alert alert-warning" style="margin-bottom:16px;">
        <span class="alert-icon">!</span>
        <div class="alert-body">
            <strong><?= esc($refundStatuses[(int) $refund['requested_status']] ?? '환불') ?></strong> 사유로 환불요청이 접수되어 운영자 검토 중입니다.
            처리 완료 전까지 상태를 변경할 수 없습니다.
            <?php if (!empty($refund['reason'])): ?>
                <div style="margin-top:4px;font-size:13px;color:var(--color-text-muted);">요청 사유: <?= esc($refund['reason']) ?></div>
            <?php endif; ?>
        </div>
    </div>
<?php elseif ($refund !== null && $refundStatus === 2): ?>
    <div class="alert alert-success" style="margin-bottom:16px;">
        <span class="alert-icon">✓</span>
        <div class="alert-body">환불요청이 승인되어 차감액이 복원되었습니다.</div>
    </div>
<?php elseif ($refund !== null && $refundStatus === 3): ?>
    <div class="alert alert-danger" style="margin-bottom:16px;">
        <span class="alert-icon">!</span>
        <div class="alert-body">
            환불요청이 거부되었습니다. 상태를 다시 변경할 수 있습니다.
            <?php if (!empty($refund['reject_reason'])): ?>
                <div style="margin-top:4px;font-size:13px;">거부 사유: <strong><?= esc($refund['reject_reason']) ?></strong></div>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>

<div style="display:grid;grid-template-columns:2fr 1fr;gap:20px;">

    <!-- 신청 정보 -->
    <div class="card">
        <div class="card-body">
            <h3 style="margin-bottom:16px;font-size:15px;">신청 정보</h3>
            <table style="width:100%;font-size:14px;border-collapse:collapse;">
                <?php
                $rows = [
                    ['캠페인',    esc($request['campaign_title'] ?? '-')],
                    ['신청자',    esc($request['name'] ?: '-')],
                    ['연락처',    esc($request['phone'] ?: '-')],
                    ['나이',      $request['age'] !== null ? esc((string) $request['age']) . '세' : '-'],
                    ['성별',      $sexLabel],
                    ['통화 가능 시간', esc($request['call_time'] ?: '-')],
                    ['디바이스',  esc($devices[(int) $request['device']] ?? '-')],
                    ['지역',      esc($request['region'] ?? '-')],
                    ['유입 경로', esc($request['funnel'] ?? '-')],
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

        <div class="card">
            <div class="card-body">
                <h3 style="margin-bottom:12px;font-size:15px;">상태 변경</h3>
                <?php if ($locked): ?>
                    <p style="color:var(--color-text-muted);font-size:13px;">
                        환불요청 처리 중에는 상태를 변경할 수 없습니다.
                    </p>
                <?php else: ?>
                    <div style="display:flex;flex-direction:column;gap:8px;">
                        <select id="statusSelect" class="form-control">
                            <?php foreach ($statuses as $val => $label): ?>
                                <option value="<?= esc((string) $val) ?>" <?= $currentStatus === $val ? 'selected' : '' ?>>
                                    <?= esc($label) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>

                        <!-- 예약 일시 입력 (예약 선택 시 노출) -->
                        <div id="reservedWrap" style="display:none;">
                            <label style="font-size:12px;color:var(--color-text-muted);display:block;margin-bottom:4px;">예약 일시</label>
                            <input type="datetime-local" id="reservedAt" class="form-control">
                        </div>

                        <!-- 취소 사유 입력 (취소 선택 시 노출) -->
                        <div id="reasonWrap" style="display:none;">
                            <label style="font-size:12px;color:var(--color-text-muted);display:block;margin-bottom:4px;">취소 사유</label>
                            <textarea id="cancelReason" class="form-control" rows="2" maxlength="500"
                                      placeholder="취소 사유를 입력하세요 (필수)"></textarea>
                            <p style="font-size:11px;color:var(--color-text-muted);margin-top:4px;">
                                중복·결번·취소로 변경하면 운영자에게 환불요청이 접수되며 이후 상태를 변경할 수 없습니다.
                            </p>
                        </div>

                        <button type="button" id="statusApply" class="btn btn-primary btn-sm">변경</button>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <h3 style="margin-bottom:12px;font-size:15px;">메모 · 히스토리</h3>

                <form method="POST" action="/portal/call-requests/<?= (int) $request['id'] ?>/memos"
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
                        <?php $isSystem = str_starts_with((string) $memo['memo'], '[상태변경]'); ?>
                        <div style="padding:10px 0;border-bottom:1px solid var(--color-border);">
                            <div style="font-size:13px;white-space:pre-wrap;<?= $isSystem ? 'color:var(--color-text-muted);' : '' ?>"><?= esc($memo['memo']) ?></div>
                            <div style="display:flex;align-items:center;justify-content:space-between;margin-top:6px;">
                                <span style="font-size:11px;color:var(--color-text-muted);">
                                    <?= esc($memo['created_at']) ?>
                                </span>
                                <?php if (!$isSystem): ?>
                                <form method="POST"
                                      action="/portal/call-requests/<?= (int) $request['id'] ?>/memos/<?= (int) $memo['id'] ?>/delete"
                                      onsubmit="return confirm('메모를 삭제하시겠습니까?');" style="margin:0;">
                                    <?= csrf_field() ?>
                                    <button type="submit" style="border:none;background:none;color:#ef4444;font-size:11px;cursor:pointer;">삭제</button>
                                </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

    </div>
</div>

<?php if (!$locked): ?>
<script>
const RESERVED_STATUS = <?= (int) $reservedStatus ?>;
const CANCEL_STATUS   = 3;
const statusSelect = document.getElementById('statusSelect');
const reservedWrap = document.getElementById('reservedWrap');
const reasonWrap   = document.getElementById('reasonWrap');

function toggleExtraInputs() {
    const v = Number(statusSelect.value);
    reservedWrap.style.display = v === RESERVED_STATUS ? 'block' : 'none';
    reasonWrap.style.display   = v === CANCEL_STATUS ? 'block' : 'none';
}
statusSelect.addEventListener('change', toggleExtraInputs);
toggleExtraInputs();

document.getElementById('statusApply').addEventListener('click', async (e) => {
    const btn    = e.currentTarget;
    const status = Number(statusSelect.value);
    const payload = { status };

    if (status === RESERVED_STATUS) {
        const val = document.getElementById('reservedAt').value;
        if (!val) { alert('예약 일시를 입력해주세요.'); return; }
        payload.reserved_at = val.replace('T', ' ');
    }
    if (status === CANCEL_STATUS) {
        const reason = document.getElementById('cancelReason').value.trim();
        if (!reason) { alert('취소 사유를 입력해주세요.'); return; }
        payload.reason = reason;
    }

    btn.disabled = true;
    btn.textContent = '처리 중...';

    try {
        const res = await fetch('/portal/call-requests/<?= (int) $request['id'] ?>/status', {
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
            if (data.refund_created && data.message) alert(data.message);
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
<?php endif; ?>
