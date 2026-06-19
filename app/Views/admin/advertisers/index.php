<?php
/** @var array<int, array<string, mixed>> $advertisers */
/** @var int $total */
/** @var array<string, mixed> $params */

$statusLabels = [
    1 => ['label' => '활성', 'color' => '#10b981'],
    2 => ['label' => '정지', 'color' => '#f59e0b'],
    3 => ['label' => '탈퇴', 'color' => '#ef4444'],
];

$networkLabels = [
    0 => '일반',
    1 => '모병원',
    2 => '자병원',
];
?>
<div class="page-header" style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;">
    <h1 class="page-title">광고주 관리</h1>
    <a href="/admin/advertisers/new" class="btn btn-primary btn-sm">+ 광고주 등록</a>
</div>

<!-- 필터 폼 -->
<form method="GET" action="/admin/advertisers" style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:16px;">
    <input type="text" name="hospital_name" class="form-control" style="width:220px;"
           placeholder="병원명 검색" value="<?= esc($params['hospital_name']) ?>">
    <select name="status" class="form-control" style="width:110px;">
        <option value="">전체 상태</option>
        <?php foreach ($statusLabels as $val => $info): ?>
            <option value="<?= $val ?>" <?= (string) $params['status'] === (string) $val ? 'selected' : '' ?>>
                <?= esc($info['label']) ?>
            </option>
        <?php endforeach; ?>
    </select>
    <select name="is_network" class="form-control" style="width:110px;">
        <option value="">전체 유형</option>
        <?php foreach ($networkLabels as $val => $label): ?>
            <option value="<?= $val ?>" <?= (string) $params['is_network'] === (string) $val ? 'selected' : '' ?>>
                <?= esc($label) ?>
            </option>
        <?php endforeach; ?>
    </select>
    <button type="submit" class="btn btn-primary btn-sm">검색</button>
    <a href="/admin/advertisers" class="btn btn-outline btn-sm">초기화</a>
</form>

<p class="text-sm" style="color:var(--color-text-muted);margin-bottom:8px;">
    총 <strong><?= number_format($total) ?></strong>건
</p>

<!-- AG Grid -->
<div id="advertiserGrid" style="height:600px;" class="ag-theme-alpine"></div>

<script>
const statusLabels = <?= json_encode(array_map(fn($v) => $v['label'], $statusLabels)) ?>;
const statusColors = <?= json_encode(array_map(fn($v) => $v['color'], $statusLabels)) ?>;
const networkLabels = <?= json_encode($networkLabels) ?>;
const rowData = <?= json_encode($advertisers) ?>;

const columnDefs = [
    { field: 'id', headerName: 'ID', width: 70, sortable: true },
    {
        field: 'hospital_name',
        headerName: '병원명',
        flex: 2,
        cellRenderer: (p) => {
            const a = document.createElement('a');
            a.href = `/admin/advertisers/${p.data.id}`;
            a.style.cssText = 'color:var(--color-primary,#0F6E56);text-decoration:none;';
            a.textContent = p.value;
            return a;
        },
    },
    { field: 'contact_name',  headerName: '담당자', flex: 1 },
    { field: 'contact_phone', headerName: '연락처', width: 130 },
    {
        field: 'is_network',
        headerName: '네트워크',
        width: 100,
        valueFormatter: (p) => networkLabels[p.value] ?? '-',
    },
    {
        field: 'parent_name',
        headerName: '모병원',
        flex: 1,
        valueFormatter: (p) => p.value ?? '-',
    },
    {
        field: 'status',
        headerName: '상태',
        width: 80,
        cellRenderer: (p) => {
            const color = statusColors[p.value] ?? '#6b7280';
            const label = statusLabels[p.value] ?? p.value;
            return `<span style="background:${color}20;color:${color};padding:2px 8px;border-radius:4px;font-size:12px;">${label}</span>`;
        },
    },
    { field: 'created_at', headerName: '등록일', width: 160 },
    {
        headerName: '액션',
        width: 70,
        sortable: false,
        cellRenderer: (p) => {
            const a = document.createElement('a');
            a.href = `/admin/advertisers/${p.data.id}/edit`;
            a.style.cssText = 'font-size:12px;color:var(--color-text-muted,#888);text-decoration:none;';
            a.textContent = '수정';
            return a;
        },
    },
];

agGrid.createGrid(document.getElementById('advertiserGrid'), {
    columnDefs,
    rowData,
    pagination: true,
    paginationPageSize: 20,
    defaultColDef: { resizable: true },
});
</script>
