<?php
/**
 * Admin 가이드 목록 (이슈 #146)
 *
 * @var array<int, array<string, mixed>> $guides
 * @var int                              $total
 */
$statusLabel = ['draft' => '임시', 'published' => '발행'];
?>
<div class="page-header" style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;">
    <h1 class="page-title" style="margin:0;">시술 가이드 <span style="font-size:14px;color:var(--color-text-muted);">(<?= esc((string) $total) ?>)</span></h1>
    <a href="/admin/guides/new" class="btn btn-primary">가이드 등록</a>
</div>

<?php if (session('success')): ?>
<div class="alert alert-success" style="margin-bottom:16px;padding:12px 16px;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:6px;color:#16a34a;font-size:14px;"><?= esc(session('success')) ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-body">
        <?php if ($guides === []): ?>
            <p style="color:var(--color-text-muted);padding:24px;text-align:center;">등록된 가이드가 없습니다.</p>
        <?php else: ?>
        <table class="table" style="width:100%;border-collapse:collapse;font-size:14px;">
            <thead>
                <tr style="text-align:left;border-bottom:2px solid var(--color-border,#e5e7eb);">
                    <th style="padding:10px 8px;">제목</th>
                    <th style="padding:10px 8px;">슬러그</th>
                    <th style="padding:10px 8px;">상태</th>
                    <th style="padding:10px 8px;">발행일</th>
                    <th style="padding:10px 8px;width:120px;">관리</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($guides as $g): ?>
                <tr style="border-bottom:1px solid var(--color-border,#f3f4f6);">
                    <td style="padding:10px 8px;"><?= esc((string) $g['title']) ?></td>
                    <td style="padding:10px 8px;color:var(--color-text-muted);font-family:monospace;font-size:12px;"><?= esc((string) $g['slug']) ?></td>
                    <td style="padding:10px 8px;"><?= esc($statusLabel[(string) $g['status']] ?? (string) $g['status']) ?></td>
                    <td style="padding:10px 8px;color:var(--color-text-muted);"><?= esc((string) ($g['published_at'] ?? '-')) ?></td>
                    <td style="padding:10px 8px;display:flex;gap:6px;">
                        <a href="/admin/guides/<?= (int) $g['id'] ?>/edit" class="btn btn-sm btn-outline">수정</a>
                        <form action="/admin/guides/<?= (int) $g['id'] ?>/delete" method="POST" onsubmit="return confirm('삭제하시겠습니까?');" style="display:inline;">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn btn-sm btn-outline" style="color:#dc2626;">삭제</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
</div>
