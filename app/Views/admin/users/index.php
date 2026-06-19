<?php
/** @var array<int, array<string, mixed>> $users */
/** @var int $total */
/** @var int $typeGroup */
/** @var int $subType */
/** @var string $isDormant */
/** @var string $searchWord */
/** @var int $page */
/** @var array<int, string> $tabLabels */
/** @var array<int, list<int>> $typeGroups */
/** @var array<int, string> $userTypeLabels */
?>
<div class="page-header" style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;">
    <h1 class="page-title">사용자 관리</h1>
</div>

<!-- 탭 -->
<div style="display:flex;gap:4px;margin-bottom:20px;border-bottom:2px solid var(--color-border,#e5e7eb);">
    <?php foreach ($tabLabels as $groupId => $label): ?>
    <a href="/admin/users?type=<?= $groupId ?>"
       style="padding:8px 18px;font-size:14px;text-decoration:none;border-bottom:2px solid transparent;margin-bottom:-2px;color:var(--color-text-muted);
              <?= $typeGroup === $groupId ? 'border-bottom-color:var(--color-primary,#0F6E56);color:var(--color-primary,#0F6E56);font-weight:600;' : '' ?>">
        <?= esc($label) ?>
    </a>
    <?php endforeach; ?>
</div>

<!-- 필터 폼 -->
<form method="GET" action="/admin/users" style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:16px;">
    <input type="hidden" name="type" value="<?= $typeGroup ?>">

    <?php if ($typeGroup > 1): ?>
    <select name="sub_type" class="form-control" style="width:160px;">
        <option value="">전체 유형</option>
        <?php foreach ($typeGroups[$typeGroup] as $typeVal): ?>
            <option value="<?= $typeVal ?>" <?= $subType === $typeVal ? 'selected' : '' ?>>
                <?= esc($userTypeLabels[$typeVal] ?? (string) $typeVal) ?>
            </option>
        <?php endforeach; ?>
    </select>
    <?php endif; ?>

    <select name="is_dormant" class="form-control" style="width:120px;">
        <option value="">전체</option>
        <option value="1" <?= $isDormant === '1' ? 'selected' : '' ?>>활성</option>
        <option value="0" <?= $isDormant === '0' ? 'selected' : '' ?>>휴면</option>
    </select>

    <input type="text" name="search_word" class="form-control" style="width:220px;"
           placeholder="이메일 / 이름 / 전화번호" value="<?= esc($searchWord) ?>">

    <button type="submit" class="btn btn-primary btn-sm">검색</button>
    <a href="/admin/users?type=<?= $typeGroup ?>" class="btn btn-outline btn-sm">초기화</a>
</form>

<p class="text-sm" style="color:var(--color-text-muted);margin-bottom:8px;">
    총 <strong><?= number_format($total) ?></strong>건
</p>

<!-- AG Grid -->
<div id="userGrid" style="height:600px;" class="ag-theme-alpine"></div>

<script>
const typeLabels = <?= json_encode($userTypeLabels, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) ?>;
const rowData    = <?= json_encode($users, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) ?>;

const columnDefs = [
    { field: 'id', headerName: 'ID', width: 80, sortable: true },
    { field: 'email', headerName: '이메일', flex: 1, minWidth: 180 },
    { field: 'username', headerName: '이름', width: 120 },
    {
        field: 'user_type',
        headerName: '유형',
        width: 130,
        valueFormatter: (p) => typeLabels[p.value] ?? p.value,
    },
    { field: 'phone', headerName: '전화번호', width: 130 },
    {
        field: 'is_dormant',
        headerName: '상태',
        width: 90,
        cellRenderer: (p) => {
            const active = p.value == 1;
            const color  = active ? '#0F6E56' : '#9ca3af';
            const label  = active ? '활성' : '휴면';
            return `<span style="background:${color}20;color:${color};padding:2px 8px;border-radius:4px;font-size:12px;">${label}</span>`;
        },
    },
    { field: 'last_login_at', headerName: '최근 로그인', width: 160 },
    { field: 'created_at', headerName: '가입일', width: 160 },
    {
        headerName: '상세',
        width: 70,
        sortable: false,
        cellRenderer: (p) => `<a href="/admin/users/${p.data.id}" style="font-size:12px;color:var(--color-primary,#0F6E56);">보기</a>`,
    },
];

agGrid.createGrid(document.getElementById('userGrid'), {
    columnDefs,
    rowData,
    pagination: true,
    paginationPageSize: 20,
    defaultColDef: { resizable: true },
});
</script>
