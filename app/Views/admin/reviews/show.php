<?php
/**
 * @var array<string, mixed> $detail
 * @var array<string, mixed>|null $compliance
 * @var array<int, string> $adTypes
 * @var array<int, string> $channels
 */

// 의료광고 심의 사전검사 결과 (이슈 #71)
$complianceLevels = [
    'safe'      => ['label' => '안전',     'color' => '#10b981', 'bg' => '#d1fae5'],
    'warning'   => ['label' => '주의',     'color' => '#f59e0b', 'bg' => '#fef3c7'],
    'violation' => ['label' => '위반 의심', 'color' => '#ef4444', 'bg' => '#fee2e2'],
];
$complianceFlags = [];
if (!empty($compliance['flags'])) {
    $dec = json_decode((string) $compliance['flags'], true);
    $complianceFlags = is_array($dec) ? $dec : [];
}
$complianceLevel = (string) ($compliance['risk_level'] ?? '');
$complianceInfo  = $complianceLevels[$complianceLevel] ?? ['label' => $complianceLevel, 'color' => '#888', 'bg' => '#f3f4f6'];

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

$reviewStatus = $detail['review_status'] ?? 'pending';
$reviewInfo   = $reviewStatusLabels[$reviewStatus] ?? ['label' => $reviewStatus, 'color' => '#888'];
$isCreate     = ($detail['request_type'] ?? 'update') === 'create';
$isPending    = $reviewStatus === 'pending';

// before: 현재 campaigns 승인 데이터 (최초 등록이면 없음)
$before = [
    'ad_title'         => $detail['approved_title'] ?? null,
    'ad_detail_info'   => $detail['approved_detail'] ?? null,
    'ad_type'          => $detail['approved_ad_type'] ?? null,
    'ad_start_date'    => $detail['approved_start'] ?? null,
    'ad_end_date'      => $detail['approved_end'] ?? null,
    'cost_type'        => $detail['approved_cost_type'] ?? null,
    'general_cost'     => $detail['approved_general_cost'] ?? null,
    'discount_cost'    => $detail['approved_discount_cost'] ?? null,
    'text_cost'        => $detail['approved_text_cost'] ?? null,
    'db_cost'          => $detail['approved_db_cost'] ?? null,
    'category'         => $detail['approved_category'] ?? null,
    'exposure'         => $detail['approved_exposure'] ?? null,
    'region'           => $detail['approved_region'] ?? null,
    'keyword'          => $detail['approved_keyword'] ?? null,
    'deliberation_code'=> $detail['approved_deliberation_code'] ?? null,
    'channel'          => $detail['approved_channel'] ?? null,
    't1_image_name'    => $detail['approved_t1'] ?? null,
    't2_image_name'    => $detail['approved_t2'] ?? null,
    'd_image_json'     => $detail['approved_d_json'] ?? null,
];

// after: 검수 요청 데이터
$after = [];
foreach (\App\Models\CampaignReviewRequestModel::CONTENT_FIELDS as $f) {
    $after[$f] = $detail[$f] ?? null;
}

/** @param mixed $a @param mixed $b */
if (!function_exists('isChanged')) {
    function isChanged(mixed $a, mixed $b): bool {
        return (string) $a !== (string) $b;
    }
}

// 업로드 이미지 경로 → 실제 URL 변환 (campaigns/xxx.png → /uploads/campaigns/xxx.png)
if (!function_exists('reviewImageUrl')) {
    function reviewImageUrl(string $path): string {
        return base_url('uploads/campaigns/' . basename($path));
    }
}

$dImagesBefore = [];
if (!empty($before['d_image_json'])) {
    $dec = json_decode($before['d_image_json'], true);
    $dImagesBefore = is_array($dec) ? array_values(array_filter($dec, 'is_string')) : [];
}
$dImagesAfter = [];
if (!empty($after['d_image_json'])) {
    $dec = json_decode($after['d_image_json'], true);
    $dImagesAfter = is_array($dec) ? array_values(array_filter($dec, 'is_string')) : [];
}

$exposureLabels = [1 => '이벤트', 2 => '병원상세', 3 => '둘다'];
$costTypeLabels = [1 => '숫자', 2 => '텍스트'];
?>

<div class="page-header" style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;">
    <div style="display:flex;align-items:center;gap:12px;">
        <a href="/admin/reviews" style="color:var(--color-text-muted);text-decoration:none;">← 검수 목록</a>
        <h1 class="page-title" style="margin:0;">
            <?= esc($detail['ad_title'] ?? $detail['approved_title'] ?? '(제목 없음)') ?>
        </h1>
        <span style="background:<?= esc($reviewInfo['color']) ?>20;color:<?= esc($reviewInfo['color']) ?>;padding:3px 10px;border-radius:4px;font-size:13px;">
            <?= esc($reviewInfo['label']) ?>
        </span>
        <?php if ($isCreate): ?>
        <span style="background:#ede9fe;color:#6d28d9;padding:3px 10px;border-radius:4px;font-size:12px;">최초 등록</span>
        <?php endif; ?>
    </div>
    <a href="/admin/campaigns/<?= (int) $detail['campaign_id'] ?>"
       style="font-size:13px;color:var(--color-text-muted);text-decoration:none;">캠페인 상세 →</a>
</div>

<?php if (session()->getFlashdata('error')): ?>
<div style="background:#fee2e2;border:1px solid #fca5a5;color:#991b1b;padding:10px 16px;border-radius:6px;margin-bottom:16px;font-size:14px;">
    <?= esc(session()->getFlashdata('error')) ?>
</div>
<?php endif; ?>

<!-- 기본 정보 -->
<div class="card" style="margin-bottom:20px;">
    <div class="card-body">
        <table style="width:100%;font-size:14px;border-collapse:collapse;">
            <?php
            $campStatus = $detail['campaign_status'] ?? 'pending';
            $campInfo   = $campaignStatusLabels[$campStatus] ?? ['label' => $campStatus, 'color' => '#888'];
            foreach ([
                ['병원명',   esc($detail['hospital_name'] ?? '-')],
                ['요청 유형', $isCreate ? '<span style="color:#6d28d9;font-weight:600;">최초 등록 요청</span>' : '수정 요청'],
                ['캠페인 상태', '<span style="background:' . $campInfo['color'] . '20;color:' . $campInfo['color'] . ';padding:2px 8px;border-radius:4px;font-size:12px;">' . esc($campInfo['label']) . '</span>'],
                ['요청자',  esc($detail['created_by_name'] ?? '-')],
                ['요청일시', esc($detail['created_at'] ?? '-')],
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

<!-- AI 의료광고 심의 사전검사 -->
<div class="card" style="margin-bottom:20px;">
    <div class="card-body">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;">
            <h3 style="font-size:15px;margin:0;display:flex;align-items:center;gap:10px;">
                AI 의료광고 심의 사전검사
                <?php if ($compliance !== null): ?>
                <span style="background:<?= esc($complianceInfo['bg']) ?>;color:<?= esc($complianceInfo['color']) ?>;padding:3px 10px;border-radius:4px;font-size:13px;font-weight:600;">
                    <?= esc($complianceInfo['label']) ?>
                </span>
                <?php else: ?>
                <span style="background:#f3f4f6;color:#6b7280;padding:3px 10px;border-radius:4px;font-size:12px;">검사 대기중</span>
                <?php endif; ?>
            </h3>
            <form method="POST" action="/admin/reviews/<?= (int) $detail['id'] ?>/recheck" style="margin:0;"
                  onsubmit="return confirm('AI 심의 사전검사를 다시 실행하시겠습니까?');">
                <?= csrf_field() ?>
                <button type="submit" class="btn btn-outline btn-sm"><?= $compliance === null ? '검사 실행' : '검사 재요청' ?></button>
            </form>
        </div>

        <?php if ($compliance === null): ?>
        <p style="font-size:13px;color:var(--color-text-muted);margin:0;">
            아직 심의 사전검사 결과가 없습니다. '검사 실행'을 눌러 AI 검토를 요청하세요.
        </p>
        <?php elseif ($complianceFlags === []): ?>
        <p style="font-size:13px;color:#065f46;margin:0;">
            검토 결과 위반 소지가 발견되지 않았습니다. (모델: <?= esc((string) ($compliance['model'] ?? '-')) ?>)
        </p>
        <?php else: ?>
        <div style="display:flex;flex-direction:column;gap:12px;">
            <?php foreach ($complianceFlags as $flag): ?>
                <?php
                $sev      = (string) ($flag['severity'] ?? 'low');
                $sevColor = $sev === 'high' ? '#ef4444' : '#f59e0b';
                $sevLabel = $sev === 'high' ? '높음' : '낮음';
                ?>
            <div style="border:1px solid var(--color-border,#e5e7eb);border-left:4px solid <?= $sevColor ?>;border-radius:6px;padding:12px 14px;">
                <div style="display:flex;align-items:center;gap:8px;margin-bottom:6px;">
                    <span style="font-weight:600;font-size:13px;"><?= esc((string) ($flag['rule'] ?? '위반 항목')) ?></span>
                    <span style="background:<?= $sevColor ?>20;color:<?= $sevColor ?>;padding:1px 8px;border-radius:10px;font-size:11px;">심각도 <?= esc($sevLabel) ?></span>
                </div>
                <?php if (!empty($flag['quote'])): ?>
                <div style="font-size:13px;margin-bottom:4px;">
                    <span style="color:var(--color-text-muted);">문제 표현:</span>
                    <span style="background:#fffbeb;padding:1px 4px;border-radius:3px;">"<?= esc((string) $flag['quote']) ?>"</span>
                </div>
                <?php endif; ?>
                <?php if (!empty($flag['reason'])): ?>
                <div style="font-size:13px;color:var(--color-text-muted);margin-bottom:4px;">사유: <?= esc((string) $flag['reason']) ?></div>
                <?php endif; ?>
                <?php if (!empty($flag['suggestion'])): ?>
                <div style="font-size:13px;color:#065f46;">수정안: <?= esc((string) $flag['suggestion']) ?></div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
        <div style="font-size:11px;color:var(--color-text-muted);margin-top:10px;">
            검사 모델: <?= esc((string) ($compliance['model'] ?? '-')) ?> · 검사 시각: <?= esc((string) ($compliance['created_at'] ?? '-')) ?>
            <br>※ AI 1차 스크리닝 결과이며 최종 판단은 검수자가 합니다.
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- 필드 비교 테이블 -->
<div class="card" style="margin-bottom:20px;">
    <div class="card-body">
        <h3 style="font-size:15px;margin-bottom:16px;">캠페인 정보 변경 비교</h3>
        <table style="width:100%;font-size:13px;border-collapse:collapse;">
            <thead>
                <tr style="border-bottom:2px solid var(--color-border,#e5e7eb);">
                    <th style="text-align:left;padding:8px 0;width:130px;color:var(--color-text-muted);">항목</th>
                    <?php if (!$isCreate): ?>
                    <th style="text-align:left;padding:8px 12px;color:var(--color-text-muted);">현재 (승인됨)</th>
                    <?php endif; ?>
                    <th style="text-align:left;padding:8px 12px;color:var(--color-text-muted);"><?= $isCreate ? '등록 요청 값' : '변경 요청 값' ?></th>
                    <?php if (!$isCreate): ?>
                    <th style="text-align:center;padding:8px 0;width:60px;">변경</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
            <?php
            $textFields = [
                'ad_title'         => '광고 제목',
                'ad_type'          => '광고 유형',
                'channel'          => '채널',
                'ad_start_date'    => '시작일',
                'ad_end_date'      => '종료일',
                'cost_type'        => '비용 유형',
                'general_cost'     => '일반 비용',
                'discount_cost'    => '할인 비용',
                'text_cost'        => '텍스트 비용',
                'db_cost'          => 'DB 비용',
                'category'         => '카테고리',
                'exposure'         => '노출 위치',
                'region'           => '지역',
                'keyword'          => '키워드',
                'deliberation_code'=> '심의 코드',
            ];

            foreach ($textFields as $field => $label):
                $bVal = $before[$field] ?? null;
                $aVal = $after[$field] ?? null;
                $changed = isChanged($bVal, $aVal);

                // 코드 → 레이블 변환
                $displayBefore = match($field) {
                    'ad_type'   => $adTypes[(int) $bVal] ?? $bVal,
                    'channel'   => $channels[(int) $bVal] ?? $bVal,
                    'cost_type' => $costTypeLabels[(int) $bVal] ?? $bVal,
                    'exposure'  => $exposureLabels[(int) $bVal] ?? $bVal,
                    default     => $bVal,
                };
                $displayAfter = match($field) {
                    'ad_type'   => $adTypes[(int) $aVal] ?? $aVal,
                    'channel'   => $channels[(int) $aVal] ?? $aVal,
                    'cost_type' => $costTypeLabels[(int) $aVal] ?? $aVal,
                    'exposure'  => $exposureLabels[(int) $aVal] ?? $aVal,
                    default     => $aVal,
                };
            ?>
            <tr style="border-bottom:1px solid var(--color-border,#f3f4f6);<?= (!$isCreate && $changed) ? 'background:#fffbeb;' : '' ?>">
                <td style="padding:8px 0;color:var(--color-text-muted);"><?= esc($label) ?></td>
                <?php if (!$isCreate): ?>
                <td style="padding:8px 12px;"><?= esc((string) ($displayBefore ?? '-')) ?></td>
                <?php endif; ?>
                <td style="padding:8px 12px;font-weight:<?= (!$isCreate && $changed) ? '600' : 'normal' ?>;">
                    <?= esc((string) ($displayAfter ?? '-')) ?>
                </td>
                <?php if (!$isCreate): ?>
                <td style="text-align:center;padding:8px 0;">
                    <?php if ($changed): ?>
                    <span style="background:#fef3c7;color:#92400e;padding:2px 6px;border-radius:4px;font-size:11px;">변경</span>
                    <?php endif; ?>
                </td>
                <?php endif; ?>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- 광고 상세문구 비교 (이슈 #73) -->
<?php
$detailBefore = (string) ($before['ad_detail_info'] ?? '');
$detailAfter  = (string) ($after['ad_detail_info'] ?? '');
$detailChanged = isChanged($detailBefore, $detailAfter);
?>
<?php if ($detailBefore !== '' || $detailAfter !== ''): ?>
<div class="card" style="margin-bottom:20px;">
    <div class="card-body">
        <h3 style="font-size:15px;margin-bottom:16px;">광고 상세문구
            <?php if (!$isCreate && $detailChanged): ?>
            <span style="background:#fef3c7;color:#92400e;padding:2px 6px;border-radius:4px;font-size:11px;">변경됨</span>
            <?php endif; ?>
        </h3>
        <div style="display:grid;grid-template-columns:<?= $isCreate ? '1fr' : '1fr 1fr' ?>;gap:16px;">
            <?php if (!$isCreate): ?>
            <div>
                <div style="font-size:12px;color:var(--color-text-muted);margin-bottom:6px;">현재 (승인됨)</div>
                <div style="border:1px solid var(--color-border,#e5e7eb);border-radius:6px;padding:12px;font-size:13px;line-height:1.6;">
                    <?= $detailBefore !== '' ? clean_html($detailBefore) : '<span style="color:#9ca3af;">없음</span>' ?>
                </div>
            </div>
            <?php endif; ?>
            <div>
                <div style="font-size:12px;color:var(--color-text-muted);margin-bottom:6px;"><?= $isCreate ? '등록 요청' : '변경 요청' ?></div>
                <div style="border:2px solid <?= (!$isCreate && $detailChanged) ? '#10b981' : 'var(--color-border,#e5e7eb)' ?>;border-radius:6px;padding:12px;font-size:13px;line-height:1.6;">
                    <?= $detailAfter !== '' ? clean_html($detailAfter) : '<span style="color:#9ca3af;">없음</span>' ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- 썸네일 이미지 비교 -->
<div class="card" style="margin-bottom:20px;">
    <div class="card-body">
        <h3 style="font-size:15px;margin-bottom:16px;">썸네일 이미지</h3>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;">
            <?php foreach ([
                ['썸네일 1', 't1_image_name'],
                ['썸네일 2', 't2_image_name'],
            ] as [$label, $field]):
                $bImg = (string) ($before[$field] ?? '');
                $aImg = (string) ($after[$field] ?? '');
                $changed = isChanged($bImg, $aImg);
            ?>
            <div>
                <div style="font-size:13px;font-weight:600;margin-bottom:12px;display:flex;align-items:center;gap:8px;">
                    <?= esc($label) ?>
                    <?php if (!$isCreate && $changed): ?>
                    <span style="background:#fef3c7;color:#92400e;padding:2px 6px;border-radius:4px;font-size:11px;">변경됨</span>
                    <?php endif; ?>
                </div>
                <div style="display:grid;grid-template-columns:<?= $isCreate ? '1fr' : '1fr 1fr' ?>;gap:12px;">
                    <?php if (!$isCreate): ?>
                    <div>
                        <div style="font-size:12px;color:var(--color-text-muted);margin-bottom:6px;">현재</div>
                        <?php if ($bImg !== ''): ?>
                        <div style="border:1px solid var(--color-border,#e5e7eb);border-radius:6px;overflow:hidden;">
                            <img src="<?= esc(reviewImageUrl($bImg)) ?>" alt="현재"
                                 style="width:100%;display:block;height:auto;"
                                 onerror="this.style.display='none'">
                        </div>
                        <div style="font-size:11px;color:var(--color-text-muted);margin-top:4px;word-break:break-all;"><?= esc($bImg) ?></div>
                        <?php else: ?>
                        <div style="border:1px dashed var(--color-border,#e5e7eb);border-radius:6px;padding:20px;text-align:center;font-size:12px;color:var(--color-text-muted);">없음</div>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                    <div>
                        <div style="font-size:12px;color:var(--color-text-muted);margin-bottom:6px;"><?= $isCreate ? '등록 요청' : '요청' ?></div>
                        <?php if ($aImg !== ''): ?>
                        <div style="border:2px solid <?= (!$isCreate && $changed) ? '#10b981' : 'var(--color-border,#e5e7eb)' ?>;border-radius:6px;overflow:hidden;">
                            <img src="<?= esc(reviewImageUrl($aImg)) ?>" alt="요청"
                                 style="width:100%;display:block;height:auto;"
                                 onerror="this.style.display='none'">
                        </div>
                        <div style="font-size:11px;color:var(--color-text-muted);margin-top:4px;word-break:break-all;"><?= esc($aImg) ?></div>
                        <?php else: ?>
                        <div style="border:1px dashed var(--color-border,#e5e7eb);border-radius:6px;padding:20px;text-align:center;font-size:12px;color:var(--color-text-muted);">없음</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- 상세 이미지 비교 -->
<?php $maxCount = max(1, count($dImagesBefore), count($dImagesAfter)); ?>
<div class="card" style="margin-bottom:20px;">
    <div class="card-body">
        <h3 style="font-size:15px;margin-bottom:16px;">상세 이미지</h3>
        <?php for ($i = 0; $i < $maxCount; $i++):
            $bImg    = $dImagesBefore[$i] ?? '';
            $aImg    = $dImagesAfter[$i] ?? '';
            $changed = isChanged($bImg, $aImg);
        ?>
        <div style="margin-bottom:16px;<?= $i < $maxCount - 1 ? 'padding-bottom:16px;border-bottom:1px solid var(--color-border,#f3f4f6);' : '' ?>">
            <div style="font-size:13px;font-weight:600;margin-bottom:8px;display:flex;gap:8px;align-items:center;">
                <?= $i + 1 ?>번 이미지
                <?php if (!$isCreate && $changed): ?>
                    <?php if ($bImg !== '' && $aImg === ''): ?>
                    <span style="background:#fee2e2;color:#991b1b;padding:2px 6px;border-radius:4px;font-size:11px;">삭제됨</span>
                    <?php elseif ($bImg === '' && $aImg !== ''): ?>
                    <span style="background:#d1fae5;color:#065f46;padding:2px 6px;border-radius:4px;font-size:11px;">추가됨</span>
                    <?php else: ?>
                    <span style="background:#fef3c7;color:#92400e;padding:2px 6px;border-radius:4px;font-size:11px;">변경됨</span>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
            <div style="display:grid;grid-template-columns:<?= $isCreate ? '1fr' : '1fr 1fr' ?>;gap:12px;max-width:500px;">
                <?php if (!$isCreate): ?>
                <div>
                    <div style="font-size:12px;color:var(--color-text-muted);margin-bottom:6px;">현재</div>
                    <?php if ($bImg !== ''): ?>
                    <img src="<?= esc(reviewImageUrl($bImg)) ?>" alt="현재 <?= $i + 1 ?>"
                         style="width:100%;height:auto;display:block;border-radius:6px;border:1px solid var(--color-border,#e5e7eb);"
                         onerror="this.style.display='none'">
                    <div style="font-size:11px;color:var(--color-text-muted);margin-top:4px;word-break:break-all;"><?= esc($bImg) ?></div>
                    <?php else: ?>
                    <div style="border:1px dashed var(--color-border,#e5e7eb);border-radius:6px;padding:20px;text-align:center;font-size:12px;color:var(--color-text-muted);">없음</div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
                <div>
                    <div style="font-size:12px;color:var(--color-text-muted);margin-bottom:6px;"><?= $isCreate ? '등록 요청' : '요청' ?></div>
                    <?php if ($aImg !== ''): ?>
                    <img src="<?= esc(reviewImageUrl($aImg)) ?>" alt="요청 <?= $i + 1 ?>"
                         style="width:100%;height:auto;display:block;border-radius:6px;border:2px solid <?= (!$isCreate && $changed) ? '#10b981' : 'var(--color-border,#e5e7eb)' ?>;"
                         onerror="this.style.display='none'">
                    <div style="font-size:11px;color:var(--color-text-muted);margin-top:4px;word-break:break-all;"><?= esc($aImg) ?></div>
                    <?php else: ?>
                    <div style="border:1px dashed var(--color-border,#e5e7eb);border-radius:6px;padding:20px;text-align:center;font-size:12px;color:var(--color-text-muted);">없음</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endfor; ?>
    </div>
</div>

<!-- 검수 처리 폼 또는 결과 -->
<?php if ($isPending): ?>
<div class="card" style="margin-bottom:20px;">
    <div class="card-body">
        <h3 style="font-size:15px;margin-bottom:16px;">검수 처리</h3>
        <form method="POST" action="/admin/reviews/<?= (int) $detail['id'] ?>/action" id="reviewForm">
            <?= csrf_field() ?>
            <input type="hidden" name="action" id="actionInput" value="">
            <div style="margin-bottom:12px;">
                <label style="display:block;font-size:13px;margin-bottom:6px;color:var(--color-text-muted);">메모 (선택)</label>
                <textarea name="memo" class="form-control" rows="3"
                          placeholder="반려 사유 또는 승인 메모를 입력하세요."></textarea>
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
<div class="card" style="margin-bottom:20px;">
    <div class="card-body">
        <h3 style="font-size:15px;margin-bottom:12px;">검수 결과</h3>
        <table style="width:100%;font-size:14px;border-collapse:collapse;">
            <tr>
                <td style="padding:6px 0;color:var(--color-text-muted);width:100px;">결과</td>
                <td style="padding:6px 0;">
                    <span style="background:<?= esc($reviewInfo['color']) ?>20;color:<?= esc($reviewInfo['color']) ?>;padding:2px 8px;border-radius:4px;font-size:13px;">
                        <?= esc($reviewInfo['label']) ?>
                    </span>
                </td>
            </tr>
            <tr>
                <td style="padding:6px 0;color:var(--color-text-muted);">검수자</td>
                <td style="padding:6px 0;"><?= esc($detail['reviewed_by_name'] ?? '-') ?></td>
            </tr>
            <tr>
                <td style="padding:6px 0;color:var(--color-text-muted);">처리일시</td>
                <td style="padding:6px 0;"><?= esc($detail['reviewed_at'] ?? '-') ?></td>
            </tr>
            <?php if (!empty($detail['review_memo'])): ?>
            <tr>
                <td style="padding:6px 0;color:var(--color-text-muted);">메모</td>
                <td style="padding:6px 0;"><?= esc($detail['review_memo']) ?></td>
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
    if (!confirm(`검수를 ${labels[action]}하시겠습니까?`)) return;
    document.getElementById('actionInput').value = action;
    document.getElementById('reviewForm').submit();
}
</script>
