<?php
/** @var array<int, array<string, mixed>> $requests */
/** @var int $total */
/** @var array<string, mixed> $params */
/** @var array<int, string> $statuses */
?>
<div class="page-header" style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;">
    <h1 class="page-title">신청DB 관리</h1>
</div>

<form method="GET" action="/portal/call-requests" style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:16px;">
    <select name="status" class="form-control" style="width:130px;">
        <option value="">전체 상태</option>
        <?php foreach ($statuses as $val => $label): ?>
            <option value="<?= esc((string) $val) ?>" <?= (string) $params['status'] === (string) $val ? 'selected' : '' ?>>
                <?= esc($label) ?>
            </option>
        <?php endforeach; ?>
    </select>
    <input type="text" name="keyword" class="form-control" style="width:220px;"
           placeholder="이름, 연락처 검색" value="<?= esc((string) $params['keyword']) ?>">
    <button type="submit" class="btn btn-primary btn-sm">검색</button>
    <a href="/portal/call-requests" class="btn btn-outline btn-sm">초기화</a>
</form>

<p class="text-sm" style="color:var(--color-text-muted);margin-bottom:8px;">
    총 <strong><?= number_format($total) ?></strong>건
</p>

<div id="callRequestGrid" style="height:600px;" class="ag-theme-alpine"></div>

<script>
const statuses = <?= json_encode($statuses, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
const rowData  = <?= json_encode($requests, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;

const esc = (s) => String(s ?? '').replace(/[&<>"']/g, (c) => ({
    '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;',
}[c]));

const statusColors = {
    1: '#f59e0b', 2: '#6b7280', 3: '#ef4444', 4: '#6b7280', 5: '#3b82f6',
    6: '#ef4444', 7: '#10b981', 8: '#a855f7', 9: '#6b7280',
};

const columnDefs = [
    { field: 'id', headerName: 'ID', width: 80, sortable: true },
    {
        field: 'name',
        headerName: '신청자',
        flex: 1,
        cellRenderer: (p) => `<a href="/portal/call-requests/${Number(p.data.id)}" style="color:var(--color-primary, #0F6E56);text-decoration:none;">${esc(p.value) || '(이름없음)'}</a>`,
    },
    { field: 'phone', headerName: '연락처', width: 140 },
    { field: 'campaign_title', headerName: '캠페인', flex: 1 },
    {
        field: 'status',
        headerName: '상태',
        width: 110,
        cellRenderer: (p) => {
            const color = statusColors[p.value] ?? '#6b7280';
            const label = statuses[p.value] ?? p.value;
            return `<span style="background:${color}20;color:${color};padding:2px 8px;border-radius:4px;font-size:12px;">${label}</span>`;
        },
    },
    {
        field: 'event_cost',
        headerName: '광고 단가',
        width: 110,
        valueFormatter: (p) => p.value ? Number(p.value).toLocaleString() + '원' : '-',
    },
    { field: 'confirm_date', headerName: '확인일시', width: 150 },
    { field: 'created_at', headerName: '신청일시', width: 150 },
];

agGrid.createGrid(document.getElementById('callRequestGrid'), {
    columnDefs,
    rowData,
    pagination: true,
    paginationPageSize: 20,
    defaultColDef: { resizable: true },
});
</script>
