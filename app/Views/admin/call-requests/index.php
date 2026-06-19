<?php
/** @var array<int, array<string, mixed>> $requests */
/** @var int $total */
/** @var array<string, mixed> $params */
/** @var array<int, string> $statuses */
?>
<div class="page-header" style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;">
    <h1 class="page-title">이벤트 신청 DB</h1>
</div>

<!-- 필터 폼 -->
<form method="GET" action="/admin/call-requests" style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:16px;">
    <input type="number" name="hospital_id" class="form-control" style="width:120px;"
           placeholder="병원 ID" value="<?= esc((string) $params['hospital_id']) ?>">
    <input type="number" name="campaign_id" class="form-control" style="width:130px;"
           placeholder="캠페인 ID" value="<?= esc((string) $params['campaign_id']) ?>">
    <select name="status" class="form-control" style="width:130px;">
        <option value="">전체 상태</option>
        <?php foreach ($statuses as $val => $label): ?>
            <option value="<?= esc((string) $val) ?>" <?= (string) $params['status'] === (string) $val ? 'selected' : '' ?>>
                <?= esc($label) ?>
            </option>
        <?php endforeach; ?>
    </select>
    <input type="text" name="keyword" class="form-control" style="width:200px;"
           placeholder="이름, 연락처 검색" value="<?= esc((string) $params['keyword']) ?>">
    <button type="submit" class="btn btn-primary btn-sm">검색</button>
    <a href="/admin/call-requests" class="btn btn-outline btn-sm">초기화</a>
</form>

<p class="text-sm" style="color:var(--color-text-muted);margin-bottom:8px;">
    총 <strong><?= number_format($total) ?></strong>건
</p>

<!-- AG Grid (라이브러리는 layout/head.php 에서 로드) -->
<div id="callRequestGrid" style="height:600px;" class="ag-theme-alpine"></div>

<script>
const statuses = <?= json_encode($statuses, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
const rowData  = <?= json_encode($requests, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;

// HTML 이스케이프 (cellRenderer가 innerHTML로 처리하므로 사용자 입력은 반드시 이스케이프)
const esc = (s) => String(s ?? '').replace(/[&<>"']/g, (c) => ({
    '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;',
}[c]));

// 상태별 색상 (1~9)
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
        cellRenderer: (p) => `<a href="/admin/call-requests/${Number(p.data.id)}" style="color:var(--color-primary, #0F6E56);text-decoration:none;">${esc(p.value) || '(이름없음)'}</a>`,
    },
    { field: 'phone', headerName: '연락처', width: 140 },
    { field: 'hospital_name', headerName: '병원', flex: 1 },
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
        field: 'is_charged',
        headerName: '과금',
        width: 90,
        cellRenderer: (p) => Number(p.value) === 1
            ? `<span style="color:#10b981;font-size:12px;">과금완료</span>`
            : `<span style="color:#9ca3af;font-size:12px;">미과금</span>`,
    },
    {
        field: 'event_cost',
        headerName: '단가',
        width: 100,
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
