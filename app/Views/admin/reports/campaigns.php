<?php
/** @var array<int, array<string, mixed>> $stats */
/** @var array<string, string> $params */
?>
<div class="page-header" style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;">
    <h1 class="page-title">캠페인 리포트</h1>
    <a href="/admin/reports" class="btn btn-outline btn-sm">← 매출 리포트</a>
</div>

<!-- 필터 -->
<form method="GET" action="/admin/reports/campaigns" style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:16px;">
    <input type="date" name="date_from" class="form-control" style="width:160px;" value="<?= esc($params['date_from']) ?>">
    <span style="align-self:center;color:var(--color-text-muted);">~</span>
    <input type="date" name="date_to" class="form-control" style="width:160px;" value="<?= esc($params['date_to']) ?>">
    <input type="text" name="ad_title" class="form-control" style="width:200px;"
           placeholder="캠페인명 검색" value="<?= esc($params['ad_title']) ?>">
    <button type="submit" class="btn btn-primary btn-sm">검색</button>
    <a href="/admin/reports/campaigns" class="btn btn-outline btn-sm">초기화</a>
</form>

<p class="text-sm" style="color:var(--color-text-muted);margin-bottom:8px;">
    캠페인 <strong><?= number_format(count($stats)) ?></strong>개
</p>

<!-- 캠페인 통계 그리드 -->
<div id="campaignStatsGrid" style="height:560px;" class="ag-theme-alpine"></div>

<script>
const rowData = <?= json_encode($stats, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;

const columnDefs = [
    { field: 'campaign_id', headerName: 'ID', width: 80, sortable: true },
    {
        field: 'ad_title',
        headerName: '캠페인',
        flex: 2,
        cellRenderer: (p) => `<a href="/admin/campaigns/${Number(p.data.campaign_id)}" style="color:var(--color-primary, #0F6E56);text-decoration:none;">${escHtml(p.value)}</a>`,
    },
    { field: 'hospital_name', headerName: '병원', flex: 1 },
    {
        field: 'request_count',
        headerName: '신청 건수',
        width: 110,
        type: 'numericColumn',
        valueFormatter: (p) => Number(p.value || 0).toLocaleString(),
    },
    {
        field: 'visited_count',
        headerName: '내원완료',
        width: 110,
        type: 'numericColumn',
        valueFormatter: (p) => Number(p.value || 0).toLocaleString(),
    },
    {
        headerName: '전환율',
        width: 100,
        type: 'numericColumn',
        valueGetter: (p) => {
            const req = Number(p.data.request_count || 0);
            const vis = Number(p.data.visited_count || 0);
            return req > 0 ? Math.round((vis / req) * 1000) / 10 : 0;
        },
        valueFormatter: (p) => p.value + '%',
    },
    {
        field: 'total_cost',
        headerName: 'CPA 합계',
        width: 130,
        type: 'numericColumn',
        valueFormatter: (p) => Number(p.value || 0).toLocaleString() + '원',
    },
];

// cellRenderer가 innerHTML로 처리하므로 사용자 입력은 반드시 이스케이프
function escHtml(s) {
    return String(s ?? '').replace(/[&<>"']/g, (c) => ({
        '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;',
    }[c]));
}

agGrid.createGrid(document.getElementById('campaignStatsGrid'), {
    columnDefs,
    rowData,
    pagination: true,
    paginationPageSize: 20,
    defaultColDef: { resizable: true, sortable: true },
});
</script>
