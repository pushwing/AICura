<?php
/** @var array<string, mixed> $history */

$reviewStatusLabels = [
    'pending'  => ['label' => '검수 대기', 'color' => '#f59e0b'],
    'approved' => ['label' => '승인',      'color' => '#10b981'],
    'rejected' => ['label' => '반려',      'color' => '#ef4444'],
];

$campaignStatusLabels = [
    'pending'  => ['label' => '검토중', 'color' => '#f59e0b'],
    'active'   => ['label' => '진행중', 'color' => '#10b981'],
    'rejected' => ['label' => '반려',   'color' => '#ef4444'],
    'ended'    => ['label' => '종료',   'color' => '#6b7280'],
];

$reviewStatus = $history['review_status'] ?? 'pending';
$reviewInfo   = $reviewStatusLabels[$reviewStatus] ?? ['label' => $reviewStatus, 'color' => '#888'];

/** @var list<string> $dImagesBefore */
$dImagesBefore = [];
if (!empty($history['d_images_before'])) {
    $decoded = json_decode($history['d_images_before'], true);
    $dImagesBefore = is_array($decoded) ? array_values(array_filter($decoded, 'is_string')) : [];
}

/** @var list<string> $dImagesAfter */
$dImagesAfter = [];
if (!empty($history['d_images_after'])) {
    $decoded = json_decode($history['d_images_after'], true);
    $dImagesAfter = is_array($decoded) ? array_values(array_filter($decoded, 'is_string')) : [];
}

$isPending = $reviewStatus === 'pending';
?>

<div class="page-header" style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;">
    <div style="display:flex;align-items:center;gap:12px;">
        <a href="/admin/reviews" style="color:var(--color-text-muted);text-decoration:none;">← 검수 목록</a>
        <h1 class="page-title" style="margin:0;"><?= esc($history['ad_title'] ?? '-') ?></h1>
        <span style="background:<?= esc($reviewInfo['color']) ?>20;color:<?= esc($reviewInfo['color']) ?>;padding:3px 10px;border-radius:4px;font-size:13px;">
            <?= esc($reviewInfo['label']) ?>
        </span>
    </div>
    <a href="/admin/campaigns/<?= (int) $history['campaign_id'] ?>"
       style="font-size:13px;color:var(--color-text-muted);text-decoration:none;">캠페인 상세 →</a>
</div>

<?php if (session()->getFlashdata('error')): ?>
<div style="background:#fee2e2;border:1px solid #fca5a5;color:#991b1b;padding:10px 16px;border-radius:6px;margin-bottom:16px;font-size:14px;">
    <?= esc(session()->getFlashdata('error')) ?>
</div>
<?php endif; ?>

<!-- 캠페인 기본 정보 -->
<div class="card" style="margin-bottom:20px;">
    <div class="card-body">
        <table style="width:100%;font-size:14px;border-collapse:collapse;">
            <?php
            $campStatus = $history['campaign_status'] ?? 'pending';
            $campInfo   = $campaignStatusLabels[$campStatus] ?? ['label' => $campStatus, 'color' => '#888'];
            foreach ([
                ['병원명',      esc($history['hospital_name'] ?? '-')],
                ['캠페인 상태', '<span style="background:' . $campInfo['color'] . '20;color:' . $campInfo['color'] . ';padding:2px 8px;border-radius:4px;font-size:12px;">' . esc($campInfo['label']) . '</span>'],
                ['수정자',      esc($history['created_by_name'] ?? '-')],
                ['수정일시',    esc($history['created_at'] ?? '-')],
            ] as [$label, $value]):
            ?>
            <tr>
                <td style="padding:6px 0;color:var(--color-text-muted);width:100px;"><?= $label ?></td>
                <td style="padding:6px 0;"><?= $value ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>
</div>

<!-- 썸네일 비교 -->
<div class="card" style="margin-bottom:20px;">
    <div class="card-body">
        <h3 style="font-size:15px;margin-bottom:16px;">썸네일 비교</h3>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;">
            <?php foreach ([
                ['썸네일 1 (t1)', $history['t1_before'] ?? '', $history['t1_after'] ?? ''],
                ['썸네일 2 (t2)', $history['t2_before'] ?? '', $history['t2_after'] ?? ''],
            ] as [$label, $before, $after]):
                $changed = $before !== $after;
            ?>
            <div>
                <div style="font-size:13px;font-weight:600;margin-bottom:12px;display:flex;align-items:center;gap:8px;">
                    <?= esc($label) ?>
                    <?php if ($changed): ?>
                    <span style="background:#fef3c7;color:#92400e;padding:2px 6px;border-radius:4px;font-size:11px;">변경됨</span>
                    <?php endif; ?>
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                    <div>
                        <div style="font-size:12px;color:var(--color-text-muted);margin-bottom:6px;">이전</div>
                        <?php if ($before !== ''): ?>
                        <div style="border:1px solid var(--color-border,#e5e7eb);border-radius:6px;overflow:hidden;">
                            <img src="<?= esc($before) ?>" alt="이전"
                                 style="width:100%;display:block;max-height:120px;object-fit:cover;"
                                 onerror="this.style.display='none';this.nextElementSibling.style.display='block'">
                            <div style="display:none;padding:8px;font-size:11px;color:var(--color-text-muted);text-align:center;">이미지 없음</div>
                        </div>
                        <div style="font-size:11px;color:var(--color-text-muted);margin-top:4px;word-break:break-all;"><?= esc($before) ?></div>
                        <?php else: ?>
                        <div style="border:1px solid var(--color-border,#e5e7eb);border-radius:6px;padding:20px;text-align:center;font-size:12px;color:var(--color-text-muted);">없음</div>
                        <?php endif; ?>
                    </div>
                    <div>
                        <div style="font-size:12px;color:var(--color-text-muted);margin-bottom:6px;">변경 후</div>
                        <?php if ($after !== ''): ?>
                        <div style="border:2px solid <?= $changed ? '#10b981' : 'var(--color-border,#e5e7eb)' ?>;border-radius:6px;overflow:hidden;">
                            <img src="<?= esc($after) ?>" alt="변경 후"
                                 style="width:100%;display:block;max-height:120px;object-fit:cover;"
                                 onerror="this.style.display='none';this.nextElementSibling.style.display='block'">
                            <div style="display:none;padding:8px;font-size:11px;color:var(--color-text-muted);text-align:center;">이미지 없음</div>
                        </div>
                        <div style="font-size:11px;color:var(--color-text-muted);margin-top:4px;word-break:break-all;"><?= esc($after) ?></div>
                        <?php else: ?>
                        <div style="border:1px solid var(--color-border,#e5e7eb);border-radius:6px;padding:20px;text-align:center;font-size:12px;color:var(--color-text-muted);">없음</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- 상세 이미지 비교 -->
<?php
$maxCount = max(count($dImagesBefore), count($dImagesAfter));
if ($maxCount > 0):
?>
<div class="card" style="margin-bottom:20px;">
    <div class="card-body">
        <h3 style="font-size:15px;margin-bottom:16px;">상세 이미지 비교</h3>
        <?php for ($i = 0; $i < $maxCount; $i++):
            $bImg = $dImagesBefore[$i] ?? '';
            $aImg = $dImagesAfter[$i] ?? '';
            $changed = $bImg !== $aImg;
        ?>
        <div style="margin-bottom:20px;padding-bottom:20px;<?= $i < $maxCount - 1 ? 'border-bottom:1px solid var(--color-border,#e5e7eb);' : '' ?>">
            <div style="font-size:13px;font-weight:600;margin-bottom:12px;display:flex;align-items:center;gap:8px;">
                상세 이미지 <?= $i + 1 ?>
                <?php if ($changed): ?>
                <span style="background:#fef3c7;color:#92400e;padding:2px 6px;border-radius:4px;font-size:11px;">변경됨</span>
                <?php endif; ?>
                <?php if ($bImg !== '' && $aImg === ''): ?>
                <span style="background:#fee2e2;color:#991b1b;padding:2px 6px;border-radius:4px;font-size:11px;">삭제됨</span>
                <?php elseif ($bImg === '' && $aImg !== ''): ?>
                <span style="background:#d1fae5;color:#065f46;padding:2px 6px;border-radius:4px;font-size:11px;">추가됨</span>
                <?php endif; ?>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;max-width:600px;">
                <div>
                    <div style="font-size:12px;color:var(--color-text-muted);margin-bottom:6px;">이전</div>
                    <?php if ($bImg !== ''): ?>
                    <img src="<?= esc($bImg) ?>" alt="이전 <?= $i + 1 ?>"
                         style="width:100%;border-radius:6px;border:1px solid var(--color-border,#e5e7eb);max-height:180px;object-fit:cover;"
                         onerror="this.style.display='none'">
                    <div style="font-size:11px;color:var(--color-text-muted);margin-top:4px;word-break:break-all;"><?= esc($bImg) ?></div>
                    <?php else: ?>
                    <div style="border:1px solid var(--color-border,#e5e7eb);border-radius:6px;padding:20px;text-align:center;font-size:12px;color:var(--color-text-muted);">없음</div>
                    <?php endif; ?>
                </div>
                <div>
                    <div style="font-size:12px;color:var(--color-text-muted);margin-bottom:6px;">변경 후</div>
                    <?php if ($aImg !== ''): ?>
                    <img src="<?= esc($aImg) ?>" alt="변경 후 <?= $i + 1 ?>"
                         style="width:100%;border-radius:6px;border:2px solid <?= $changed ? '#10b981' : 'var(--color-border,#e5e7eb)' ?>;max-height:180px;object-fit:cover;"
                         onerror="this.style.display='none'">
                    <div style="font-size:11px;color:var(--color-text-muted);margin-top:4px;word-break:break-all;"><?= esc($aImg) ?></div>
                    <?php else: ?>
                    <div style="border:1px solid var(--color-border,#e5e7eb);border-radius:6px;padding:20px;text-align:center;font-size:12px;color:var(--color-text-muted);">없음</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endfor; ?>
    </div>
</div>
<?php endif; ?>

<!-- 검수 처리 폼 -->
<?php if ($isPending): ?>
<div class="card" style="margin-bottom:20px;">
    <div class="card-body">
        <h3 style="font-size:15px;margin-bottom:16px;">검수 처리</h3>
        <form method="POST" action="/admin/reviews/<?= (int) $history['id'] ?>/action" id="reviewForm">
            <?= csrf_field() ?>
            <input type="hidden" name="action" id="actionInput" value="">
            <div style="margin-bottom:12px;">
                <label style="display:block;font-size:13px;margin-bottom:6px;color:var(--color-text-muted);">메모 (선택)</label>
                <textarea name="memo" class="form-control" rows="3" placeholder="반려 사유 또는 승인 메모를 입력하세요."></textarea>
            </div>
            <div style="display:flex;gap:8px;justify-content:flex-end;">
                <a href="/admin/reviews" class="btn btn-outline btn-sm">목록으로</a>
                <button type="button" class="btn btn-sm"
                        style="background:#ef4444;color:#fff;border:none;"
                        onclick="submitAction('reject')">반려</button>
                <button type="button" class="btn btn-primary btn-sm"
                        onclick="submitAction('approve')">승인</button>
            </div>
        </form>
    </div>
</div>
<?php else: ?>
<!-- 처리 완료 표시 -->
<div class="card" style="margin-bottom:20px;">
    <div class="card-body">
        <h3 style="font-size:15px;margin-bottom:12px;">검수 결과</h3>
        <table style="width:100%;font-size:14px;border-collapse:collapse;">
            <tr>
                <td style="padding:6px 0;color:var(--color-text-muted);width:100px;">검수 결과</td>
                <td style="padding:6px 0;">
                    <span style="background:<?= esc($reviewInfo['color']) ?>20;color:<?= esc($reviewInfo['color']) ?>;padding:2px 8px;border-radius:4px;font-size:13px;">
                        <?= esc($reviewInfo['label']) ?>
                    </span>
                </td>
            </tr>
            <tr>
                <td style="padding:6px 0;color:var(--color-text-muted);">검수자</td>
                <td style="padding:6px 0;"><?= esc($history['reviewed_by_name'] ?? '-') ?></td>
            </tr>
            <tr>
                <td style="padding:6px 0;color:var(--color-text-muted);">처리일시</td>
                <td style="padding:6px 0;"><?= esc($history['reviewed_at'] ?? '-') ?></td>
            </tr>
            <?php if (!empty($history['review_memo'])): ?>
            <tr>
                <td style="padding:6px 0;color:var(--color-text-muted);">메모</td>
                <td style="padding:6px 0;"><?= esc($history['review_memo']) ?></td>
            </tr>
            <?php endif; ?>
        </table>
        <div style="margin-top:12px;">
            <a href="/admin/reviews" class="btn btn-outline btn-sm">목록으로</a>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
function submitAction(action) {
    const labels = { approve: '승인', reject: '반려' };
    if (!confirm(`소재를 ${labels[action]}하시겠습니까?`)) return;
    document.getElementById('actionInput').value = action;
    document.getElementById('reviewForm').submit();
}
</script>
