<?php
/** @var array<int, array<string, mixed>> $requests */
/** @var int $total */
/** @var array<string, mixed> $params */
/** @var array<int, string> $statusLabels 환불요청 상태 (1대기/2승인/3거부) */
/** @var array<int, string> $requestStatuses 요청 유형 (3취소/8중복/9결번) */

$badge = [
    1 => ['#f59e0b', '대기'],
    2 => ['#10b981', '승인'],
    3 => ['#ef4444', '거부'],
];
?>
<div class="page-header" style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;">
    <h1 class="page-title">환불요청 관리</h1>
</div>

<!-- 상태 필터 -->
<form method="GET" action="/admin/refund-requests" style="display:flex;gap:8px;margin-bottom:16px;">
    <select name="status" class="form-control" style="width:150px;">
        <option value="">전체 상태</option>
        <?php foreach ($statusLabels as $val => $label): ?>
            <option value="<?= esc((string) $val) ?>" <?= (string) $params['status'] === (string) $val ? 'selected' : '' ?>>
                <?= esc($label) ?>
            </option>
        <?php endforeach; ?>
    </select>
    <button type="submit" class="btn btn-primary btn-sm">검색</button>
    <a href="/admin/refund-requests" class="btn btn-outline btn-sm">초기화</a>
</form>

<p class="text-sm" style="color:var(--color-text-muted);margin-bottom:8px;">
    총 <strong><?= number_format($total) ?></strong>건
</p>

<div class="card">
    <div class="card-body">
        <?php if (empty($requests)): ?>
            <p class="text-sm" style="color:var(--color-text-muted);">환불요청이 없습니다.</p>
        <?php else: ?>
            <div class="table-wrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th>신청자</th>
                            <th>캠페인 / 병원</th>
                            <th>요청 유형</th>
                            <th>사유</th>
                            <th style="text-align:right;">단가</th>
                            <th>과금</th>
                            <th>상태</th>
                            <th>요청일</th>
                            <th style="width:200px;">처리</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($requests as $r):
                        $st = (int) $r['status'];
                        [$color, $label] = $badge[$st] ?? ['#6b7280', (string) $st];
                        $charged = (int) ($r['is_charged'] ?? 0) === 1;
                    ?>
                        <tr>
                            <td>
                                <a href="/admin/call-requests/<?= (int) $r['call_request_id'] ?>" style="color:var(--color-primary, #0F6E56);text-decoration:none;">
                                    <?= esc($r['applicant_name'] ?: '(이름없음)') ?>
                                </a>
                                <div class="text-sm" style="color:var(--color-text-muted);"><?= esc($r['applicant_phone'] ?? '') ?></div>
                            </td>
                            <td>
                                <div><?= esc($r['campaign_title'] ?? '-') ?></div>
                                <div class="text-sm" style="color:var(--color-text-muted);"><?= esc($r['hospital_name'] ?? '-') ?></div>
                            </td>
                            <td><?= esc($requestStatuses[(int) $r['requested_status']] ?? '-') ?></td>
                            <td class="text-sm"><?= esc($r['reason'] ?? '-') ?: '-' ?></td>
                            <td style="text-align:right;"><?= number_format((int) ($r['event_cost'] ?? 0)) ?>원</td>
                            <td>
                                <?= $charged
                                    ? '<span style="color:#10b981;font-size:12px;">과금완료</span>'
                                    : '<span style="color:#9ca3af;font-size:12px;">미과금</span>' ?>
                            </td>
                            <td>
                                <span style="background:<?= $color ?>20;color:<?= $color ?>;padding:2px 8px;border-radius:4px;font-size:12px;"><?= esc($label) ?></span>
                                <?php if ($st === 3 && !empty($r['reject_reason'])): ?>
                                    <div class="text-sm" style="color:var(--color-text-muted);margin-top:4px;">거부: <?= esc($r['reject_reason']) ?></div>
                                <?php endif; ?>
                            </td>
                            <td class="text-sm"><?= esc($r['created_at'] ?? '-') ?></td>
                            <td>
                                <?php if ($st === 1): ?>
                                    <div style="display:flex;flex-direction:column;gap:6px;">
                                        <form method="POST" action="/admin/refund-requests/<?= (int) $r['id'] ?>/approve"
                                              onsubmit="return confirm('<?= $charged ? '차감액을 복원하고 ' : '' ?>승인하시겠습니까?');" style="margin:0;">
                                            <?= csrf_field() ?>
                                            <button type="submit" class="btn btn-primary btn-sm" style="width:100%;">승인</button>
                                        </form>
                                        <button type="button" class="btn btn-outline btn-sm"
                                                onclick="document.getElementById('reject-<?= (int) $r['id'] ?>').style.display='block';this.style.display='none';">
                                            거부
                                        </button>
                                        <form id="reject-<?= (int) $r['id'] ?>" method="POST"
                                              action="/admin/refund-requests/<?= (int) $r['id'] ?>/reject"
                                              style="display:none;flex-direction:column;gap:6px;margin:0;">
                                            <?= csrf_field() ?>
                                            <textarea name="reject_reason" class="form-control" rows="2" maxlength="500"
                                                      placeholder="거부 사유 (광고주에게 노출)" required></textarea>
                                            <button type="submit" class="btn btn-danger btn-sm">거부 확정</button>
                                        </form>
                                    </div>
                                <?php else: ?>
                                    <span class="text-sm" style="color:var(--color-text-muted);"><?= esc($r['processed_at'] ?? '처리됨') ?></span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
