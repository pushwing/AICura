<?php
/** @var array{total:int,pending:int,agreed:int} $stats */
/** @var array<int, array<string, mixed>> $recent */

$statusLabels = [1 => '활성', 2 => '정지', 3 => '탈퇴'];
?>
<div class="page-header">
    <h1 class="page-title">대시보드</h1>
    <span class="text-sm" style="color:var(--color-text-muted)"><?= date('Y년 n월 j일') ?></span>
</div>

<div style="display:grid;grid-template-columns:repeat(3,1fr);gap:16px;margin-bottom:28px;">
    <div class="card"><div class="card-body">
        <p class="text-sm" style="color:var(--color-text-muted);margin-bottom:6px;">전체 광고주</p>
        <p style="font-size:2rem;font-weight:700;color:var(--color-text)"><?= number_format($stats['total']) ?></p>
    </div></div>
    <div class="card"><div class="card-body">
        <p class="text-sm" style="color:var(--color-text-muted);margin-bottom:6px;">계약대기</p>
        <p style="font-size:2rem;font-weight:700;color:#f59e0b;"><?= number_format($stats['pending']) ?></p>
        <p class="text-xs" style="color:var(--color-text-muted);margin-top:4px;">광고주 계약 동의 대기</p>
    </div></div>
    <div class="card"><div class="card-body">
        <p class="text-sm" style="color:var(--color-text-muted);margin-bottom:6px;">사용중 광고주</p>
        <p style="font-size:2rem;font-weight:700;color:#1D9E75;"><?= number_format($stats['agreed']) ?></p>
        <p class="text-xs" style="color:var(--color-text-muted);margin-top:4px;">계약 동의 완료</p>
    </div></div>
</div>

<div class="card">
    <div class="card-header" style="display:flex;align-items:center;justify-content:space-between;">
        <span class="card-title">최근 등록 광고주</span>
        <a href="/portal/advertisers" class="btn btn-outline btn-sm">전체 보기</a>
    </div>
    <div class="card-body">
        <?php if (empty($recent)): ?>
            <p class="text-sm" style="color:var(--color-text-muted);">등록된 광고주가 없습니다.</p>
        <?php else: ?>
            <div class="table-wrap">
                <table class="table">
                    <thead><tr><th>병원명</th><th>담당자</th><th>상태</th><th>계약</th><th>등록일</th></tr></thead>
                    <tbody>
                    <?php foreach ($recent as $row): ?>
                        <tr>
                            <td><a href="/portal/advertisers/<?= (int) $row['id'] ?>" style="color:var(--color-primary);text-decoration:none;"><?= esc($row['hospital_name']) ?></a></td>
                            <td><?= esc($row['contact_name'] ?? '-') ?></td>
                            <td><?= esc($statusLabels[(int) $row['status']] ?? '-') ?></td>
                            <td><?= empty($row['contract_agreed_at']) ? '<span class="badge badge-warning">대기</span>' : '<span class="badge badge-success">동의</span>' ?></td>
                            <td class="text-sm"><?= esc($row['created_at_kst']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
