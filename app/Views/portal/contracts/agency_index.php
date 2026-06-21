<?php
/** @var array<int, array<string, mixed>> $rows */

$statusLabels = [1 => '활성', 2 => '정지', 3 => '탈퇴'];
?>
<div class="page-header" style="margin-bottom:20px;">
    <h1 class="page-title">계약 관리</h1>
</div>

<p class="text-sm" style="color:var(--color-text-muted);margin-bottom:12px;">
    소유 광고주들의 계약 현황입니다. (정상 수주계약 기준)
</p>

<div class="card">
    <div class="card-body">
        <?php if (empty($rows)): ?>
            <p class="text-sm" style="color:var(--color-text-muted);">등록된 광고주가 없습니다.</p>
        <?php else: ?>
            <div class="table-wrap">
                <table class="table">
                    <thead><tr><th>병원명</th><th>상태</th><th>계약 동의</th><th>수주계약 수</th><th style="text-align:right;">총 계약금액</th></tr></thead>
                    <tbody>
                    <?php foreach ($rows as $row): ?>
                        <tr>
                            <td><a href="/portal/advertisers/<?= (int) $row['id'] ?>" style="color:var(--color-primary);text-decoration:none;"><?= esc($row['hospital_name']) ?></a></td>
                            <td><?= esc($statusLabels[(int) $row['status']] ?? '-') ?></td>
                            <td><?= !empty($row['agreed']) ? '<span class="badge badge-success">동의</span>' : '<span class="badge badge-warning">대기</span>' ?></td>
                            <td><?= number_format((int) $row['order_count']) ?>건</td>
                            <td style="text-align:right;"><?= number_format((int) $row['total_price']) ?>원</td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
