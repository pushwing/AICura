<?php
/** @var array<int, array<string, mixed>> $advertisers */
/** @var int $total */
/** @var array<string, mixed> $params */

$statusLabels = [1 => '활성', 2 => '정지', 3 => '탈퇴'];
?>
<div class="page-header" style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;">
    <h1 class="page-title">광고주 관리</h1>
    <a href="/portal/advertisers/new" class="btn btn-primary btn-sm">+ 광고주 등록</a>
</div>

<form method="GET" action="/portal/advertisers" style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:16px;">
    <input type="text" name="hospital_name" class="form-control" style="width:220px;"
           placeholder="병원명 검색" value="<?= esc($params['hospital_name']) ?>">
    <select name="status" class="form-control" style="width:120px;">
        <option value="">전체 상태</option>
        <?php foreach ($statusLabels as $val => $label): ?>
            <option value="<?= $val ?>" <?= (string) $params['status'] === (string) $val ? 'selected' : '' ?>><?= esc($label) ?></option>
        <?php endforeach; ?>
    </select>
    <button type="submit" class="btn btn-primary btn-sm">검색</button>
    <a href="/portal/advertisers" class="btn btn-outline btn-sm">초기화</a>
</form>

<p class="text-sm" style="color:var(--color-text-muted);margin-bottom:8px;">총 <strong><?= number_format($total) ?></strong>건</p>

<div class="card">
    <div class="card-body">
        <?php if (empty($advertisers)): ?>
            <p class="text-sm" style="color:var(--color-text-muted);">등록된 광고주가 없습니다.</p>
        <?php else: ?>
            <div class="table-wrap">
                <table class="table">
                    <thead><tr><th>ID</th><th>병원명</th><th>담당자</th><th>연락처</th><th>상태</th><th>계약</th><th>등록일</th></tr></thead>
                    <tbody>
                    <?php foreach ($advertisers as $row): ?>
                        <tr>
                            <td><?= (int) $row['id'] ?></td>
                            <td><a href="/portal/advertisers/<?= (int) $row['id'] ?>" style="color:var(--color-primary);text-decoration:none;"><?= esc($row['hospital_name']) ?></a></td>
                            <td><?= esc($row['contact_name'] ?? '-') ?></td>
                            <td><?= esc($row['contact_phone'] ?? '-') ?></td>
                            <td><?= esc($statusLabels[(int) $row['status']] ?? '-') ?></td>
                            <td><?= empty($row['contract_agreed_at']) ? '<span class="badge badge-warning">계약대기</span>' : '<span class="badge badge-success">사용가능</span>' ?></td>
                            <td class="text-sm"><?= esc($row['created_at_kst']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
