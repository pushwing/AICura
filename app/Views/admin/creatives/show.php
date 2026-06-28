<?php
/** @var array<string, mixed> $campaign */

$statusLabels = [
    'pending'  => ['label' => '검토중', 'color' => '#f59e0b'],
    'active'   => ['label' => '진행중', 'color' => '#10b981'],
    'rejected' => ['label' => '반려',   'color' => '#ef4444'],
    'ended'    => ['label' => '종료',   'color' => '#6b7280'],
];

$currentStatus = $campaign['status'] ?? 'pending';
$statusInfo    = $statusLabels[$currentStatus] ?? ['label' => $currentStatus, 'color' => '#888'];

/** @var list<string> $dImages */
$dImages = [];
if (!empty($campaign['d_image_json'])) {
    $decoded = json_decode($campaign['d_image_json'], true);
    $dImages = is_array($decoded) ? array_values(array_filter($decoded, 'is_string')) : [];
}
?>
<div class="page-header" style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;">
    <div style="display:flex;align-items:center;gap:12px;">
        <a href="/admin/creatives" style="color:var(--color-text-muted);text-decoration:none;">← 소재 목록</a>
        <h1 class="page-title" style="margin:0;"><?= esc($campaign['ad_title']) ?></h1>
        <span style="background:<?= esc($statusInfo['color']) ?>20;color:<?= esc($statusInfo['color']) ?>;padding:3px 10px;border-radius:4px;font-size:13px;">
            <?= esc($statusInfo['label']) ?>
        </span>
    </div>
    <a href="/admin/campaigns/<?= (int) $campaign['id'] ?>"
       style="font-size:13px;color:var(--color-text-muted);text-decoration:none;">캠페인 상세 →</a>
</div>

<!-- 캠페인 기본 정보 요약 -->
<div class="card" style="margin-bottom:20px;">
    <div class="card-body">
        <table style="width:100%;font-size:14px;border-collapse:collapse;">
            <?php foreach ([
                ['병원명',   esc($campaign['hospital_name'] ?? '-')],
                ['광고유형', \App\Models\CampaignModel::AD_TYPES[(int) ($campaign['ad_type'] ?? 0)] ?? '-'],
                ['기간',     esc($campaign['ad_start_date'] ?? '-') . ' ~ ' . esc($campaign['ad_end_date'] ?? '-')],
            ] as [$label, $value]): ?>
            <tr>
                <td style="padding:6px 0;color:var(--color-text-muted);width:100px;"><?= $label ?></td>
                <td style="padding:6px 0;"><?= $value ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>
</div>

<!-- 소재 수정 폼 -->
<form method="POST" action="/admin/creatives/<?= (int) $campaign['id'] ?>">
    <?= csrf_field() ?>

    <!-- 썸네일 -->
    <div class="card" style="margin-bottom:16px;">
        <div class="card-body">
            <h3 style="font-size:15px;margin-bottom:16px;">썸네일 이미지</h3>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">

                <div>
                    <label style="display:block;font-size:13px;margin-bottom:6px;color:var(--color-text-muted);">썸네일 1 (t1)</label>
                    <input type="text" name="t1_image_name" class="form-control"
                           value="<?= esc($campaign['t1_image_name'] ?? '') ?>"
                           placeholder="이미지 URL 또는 파일명">
                    <?php if (!empty($campaign['t1_image_name'])): ?>
                    <div style="margin-top:8px;border:1px solid var(--color-border,#e5e7eb);border-radius:6px;overflow:hidden;max-width:200px;">
                        <img src="<?= esc($campaign['t1_image_name']) ?>" alt="썸네일1"
                             style="width:100%;display:block;"
                             onerror="this.style.display='none';this.nextElementSibling.style.display='block'">
                        <div style="display:none;padding:12px;font-size:12px;color:var(--color-text-muted);text-align:center;">이미지를 불러올 수 없습니다</div>
                    </div>
                    <?php endif; ?>
                </div>

                <div>
                    <label style="display:block;font-size:13px;margin-bottom:6px;color:var(--color-text-muted);">썸네일 2 (t2)</label>
                    <input type="text" name="t2_image_name" class="form-control"
                           value="<?= esc($campaign['t2_image_name'] ?? '') ?>"
                           placeholder="이미지 URL 또는 파일명">
                    <?php if (!empty($campaign['t2_image_name'])): ?>
                    <div style="margin-top:8px;border:1px solid var(--color-border,#e5e7eb);border-radius:6px;overflow:hidden;max-width:200px;">
                        <img src="<?= esc($campaign['t2_image_name']) ?>" alt="썸네일2"
                             style="width:100%;display:block;"
                             onerror="this.style.display='none';this.nextElementSibling.style.display='block'">
                        <div style="display:none;padding:12px;font-size:12px;color:var(--color-text-muted);text-align:center;">이미지를 불러올 수 없습니다</div>
                    </div>
                    <?php endif; ?>
                </div>

            </div>
        </div>
    </div>

    <!-- 상세 이미지 -->
    <div class="card" style="margin-bottom:16px;">
        <div class="card-body">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;">
                <h3 style="font-size:15px;margin:0;">상세 이미지</h3>
                <button type="button" class="btn btn-outline btn-sm" onclick="addImageRow()">+ 이미지 추가</button>
            </div>

            <div id="dImageList">
                <?php foreach ($dImages as $i => $imgUrl): ?>
                <div class="d-image-row" style="display:flex;align-items:center;gap:8px;margin-bottom:8px;">
                    <input type="text" name="d_images[]" class="form-control"
                           value="<?= esc($imgUrl) ?>" placeholder="이미지 URL 또는 파일명">
                    <?php if ($imgUrl !== ''): ?>
                    <img src="<?= esc($imgUrl) ?>" alt="상세<?= $i + 1 ?>"
                         style="width:48px;height:48px;object-fit:cover;border-radius:4px;border:1px solid var(--color-border,#e5e7eb);"
                         onerror="this.style.display='none'">
                    <?php endif; ?>
                    <button type="button" class="btn btn-outline btn-sm"
                            style="flex-shrink:0;color:#ef4444;border-color:#ef4444;"
                            onclick="removeRow(this)">삭제</button>
                </div>
                <?php endforeach; ?>
                <?php if (count($dImages) === 0): ?>
                <div class="d-image-row" style="display:flex;align-items:center;gap:8px;margin-bottom:8px;">
                    <input type="text" name="d_images[]" class="form-control" placeholder="이미지 URL 또는 파일명">
                    <button type="button" class="btn btn-outline btn-sm"
                            style="flex-shrink:0;color:#ef4444;border-color:#ef4444;"
                            onclick="removeRow(this)">삭제</button>
                </div>
                <?php endif; ?>
            </div>

            <p style="font-size:12px;color:var(--color-text-muted);margin-top:8px;">
                순서대로 저장됩니다. 빈 항목은 저장 시 자동으로 제외됩니다.
            </p>
        </div>
    </div>

    <div style="display:flex;justify-content:flex-end;gap:8px;">
        <a href="/admin/creatives" class="btn btn-outline btn-sm">취소</a>
        <button type="submit" class="btn btn-primary btn-sm">저장</button>
    </div>

</form>

<script>
function addImageRow() {
    const list = document.getElementById('dImageList');
    const row  = document.createElement('div');
    row.className = 'd-image-row';
    row.style.cssText = 'display:flex;align-items:center;gap:8px;margin-bottom:8px;';
    row.innerHTML = `
        <input type="text" name="d_images[]" class="form-control" placeholder="이미지 URL 또는 파일명">
        <button type="button" class="btn btn-outline btn-sm"
                style="flex-shrink:0;color:#ef4444;border-color:#ef4444;"
                onclick="removeRow(this)">삭제</button>
    `;
    list.appendChild(row);
}

function removeRow(btn) {
    const rows = document.querySelectorAll('.d-image-row');
    if (rows.length <= 1) {
        btn.closest('.d-image-row').querySelector('input').value = '';
        return;
    }
    btn.closest('.d-image-row').remove();
}
</script>
