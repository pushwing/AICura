<?php
/** @var array<string, mixed> $campaign */
/** @var array<int, array<string, mixed>> $histories */
/** @var int $total */
/** @var array<string, mixed> $params */

$actionLabels = [
    'create' => '등록',
    'update' => '수정',
    'approve' => '승인',
    'reject'  => '반려',
    'end'     => '종료',
    'reopen'  => '재검토',
];
?>

<div class="page-header" style="display:flex;align-items:center;gap:12px;margin-bottom:20px;">
    <a href="/admin/campaigns/<?= (int)$campaign['id'] ?>" style="color:var(--color-text-muted);text-decoration:none;">← 상세</a>
    <h1 class="page-title" style="margin:0;">변경 이력 — <?= esc($campaign['ad_title']) ?></h1>
</div>

<div class="card">
    <div class="card-body">
        <p class="text-sm" style="color:var(--color-text-muted);margin-bottom:12px;">총 <?= number_format($total) ?>건</p>

        <table style="width:100%;font-size:14px;border-collapse:collapse;">
            <thead>
                <tr style="border-bottom:2px solid var(--color-border);">
                    <th style="padding:10px 0;text-align:left;font-weight:500;color:var(--color-text-muted);">액션</th>
                    <th style="padding:10px 0;text-align:left;font-weight:500;color:var(--color-text-muted);">상태 변경</th>
                    <th style="padding:10px 0;text-align:left;font-weight:500;color:var(--color-text-muted);">메모</th>
                    <th style="padding:10px 0;text-align:left;font-weight:500;color:var(--color-text-muted);">처리자</th>
                    <th style="padding:10px 0;text-align:left;font-weight:500;color:var(--color-text-muted);">일시</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($histories)): ?>
                <tr>
                    <td colspan="5" style="padding:32px 0;text-align:center;color:var(--color-text-muted);">이력이 없습니다.</td>
                </tr>
                <?php else: ?>
                <?php foreach ($histories as $h): ?>
                <tr style="border-bottom:1px solid var(--color-border);">
                    <td style="padding:12px 0;">
                        <span style="font-size:13px;padding:2px 8px;border-radius:4px;background:var(--color-bg-secondary, #f5f5f5);">
                            <?= esc($actionLabels[$h['action']] ?? $h['action']) ?>
                        </span>
                    </td>
                    <td style="padding:12px 0;">
                        <?php if ($h['status_from'] !== $h['status_to'] && $h['status_from'] !== ''): ?>
                            <span style="color:var(--color-text-muted);"><?= esc($h['status_from']) ?></span>
                            <span style="margin:0 6px;">→</span>
                            <strong><?= esc($h['status_to']) ?></strong>
                        <?php else: ?>
                            <span style="color:var(--color-text-muted);">—</span>
                        <?php endif; ?>
                    </td>
                    <td style="padding:12px 0;color:var(--color-text-muted);max-width:200px;">
                        <?php // memo는 저장 시 CampaignController::sanitizeDetailHtml()로 화이트리스트 필터링되어 안전한 태그만 남음 — esc() 없이 서식 그대로 출력 ?>
                        <?= $h['memo'] ?? '—' ?>
                    </td>
                    <td style="padding:12px 0;"><?= esc($h['admin_name'] ?? '—') ?></td>
                    <td style="padding:12px 0;color:var(--color-text-muted);font-size:13px;"><?= esc($h['created_at']) ?></td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- 페이지네이션 -->
        <?php
        $lastPage = (int) ceil($total / $params['limit']);
        if ($lastPage > 1):
            $currentPage = $params['page'];
        ?>
        <div style="display:flex;justify-content:center;gap:4px;margin-top:20px;">
            <?php for ($p = 1; $p <= $lastPage; $p++): ?>
                <a href="?page=<?= $p ?>"
                   style="padding:6px 12px;border-radius:4px;font-size:13px;text-decoration:none;
                          background:<?= $p === $currentPage ? 'var(--color-primary, #0F6E56)' : 'transparent' ?>;
                          color:<?= $p === $currentPage ? '#fff' : 'var(--color-text-muted)' ?>;
                          border:1px solid <?= $p === $currentPage ? 'var(--color-primary, #0F6E56)' : 'var(--color-border)' ?>;">
                    <?= $p ?>
                </a>
            <?php endfor; ?>
        </div>
        <?php endif; ?>
    </div>
</div>
