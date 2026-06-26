<?php
/**
 * AI 보고서 종류별 이전 목록 — 더보기 (이슈 #65)
 *
 * @var string $type
 * @var string $typeLabel
 * @var array<int, array<string, mixed>> $items
 * @var int $total
 * @var int $page
 * @var int $perPage
 * @var int $lastPage
 */
?>
<div class="page-header" style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;">
    <h1 class="page-title">AI <?= esc($typeLabel) ?> 보고서</h1>
    <a href="/admin/reports" class="btn btn-outline btn-sm">← 리포트로</a>
</div>

<div class="card">
    <div class="card-body">
        <?php if ($items === []): ?>
            <p style="color:var(--color-text-muted);margin:0;padding:24px 0;text-align:center;">
                생성된 보고서가 없습니다.
            </p>
        <?php else: ?>
            <table style="width:100%;border-collapse:collapse;font-size:14px;">
                <thead>
                    <tr style="border-bottom:2px solid var(--color-border);text-align:left;">
                        <th style="padding:10px 12px;">제목</th>
                        <th style="padding:10px 12px;width:140px;">기준일</th>
                        <th style="padding:10px 12px;width:170px;">생성일시</th>
                        <th style="padding:10px 12px;width:110px;"></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $item): ?>
                        <tr style="border-bottom:1px solid var(--color-border);">
                            <td style="padding:10px 12px;"><?= esc($item['title']) ?></td>
                            <td style="padding:10px 12px;"><?= esc($item['report_date']) ?></td>
                            <td style="padding:10px 12px;color:var(--color-text-muted);"><?= esc($item['created_at']) ?></td>
                            <td style="padding:10px 12px;">
                                <a href="/admin/reports/ai/<?= (int) $item['id'] ?>" target="_blank"
                                   class="btn btn-outline btn-sm">보기 ↗</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <?php if ($lastPage > 1): ?>
                <div style="display:flex;align-items:center;justify-content:center;gap:8px;margin-top:20px;">
                    <?php $base = '/admin/reports/ai-list/' . esc($type, 'url'); ?>
                    <?php if ($page > 1): ?>
                        <a href="<?= $base ?>?page=<?= $page - 1 ?>" class="btn btn-outline btn-sm">← 이전</a>
                    <?php endif; ?>
                    <span class="text-xs" style="color:var(--color-text-muted);">
                        <?= (int) $page ?> / <?= (int) $lastPage ?> 페이지 (총 <?= (int) $total ?>건)
                    </span>
                    <?php if ($page < $lastPage): ?>
                        <a href="<?= $base ?>?page=<?= $page + 1 ?>" class="btn btn-outline btn-sm">다음 →</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>
