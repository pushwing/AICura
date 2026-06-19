<?php
/** @var array<int, array<string, mixed>> $rows */
/** @var array<string, string>            $params */
?>
<div class="page-header" style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;">
    <h1 class="page-title">캠페인별 DB 소진</h1>
    <a href="/admin/reports" class="btn btn-outline btn-sm">매출 리포트</a>
</div>

<!-- 필터 폼 -->
<form method="GET" action="/admin/reports/campaigns"
      style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:16px;">
    <input type="text" name="ad_title" class="form-control" style="width:200px;"
           placeholder="캠페인명 검색" value="<?= esc($params['ad_title']) ?>">
    <input type="date" name="date_from" class="form-control" style="width:150px;"
           value="<?= esc($params['date_from']) ?>">
    <span style="line-height:36px;color:var(--color-text-muted);">~</span>
    <input type="date" name="date_to" class="form-control" style="width:150px;"
           value="<?= esc($params['date_to']) ?>">
    <button type="submit" class="btn btn-primary btn-sm">검색</button>
    <a href="/admin/reports/campaigns" class="btn btn-outline btn-sm">초기화</a>
</form>

<p class="text-sm" style="color:var(--color-text-muted);margin-bottom:8px;">
    총 <strong><?= number_format(count($rows)) ?></strong>개 캠페인
</p>

<!-- AG Grid -->
<div id="campaignGrid" style="height:600px;" class="ag-theme-alpine"></div>

<script>
const rowData = <?= json_encode($rows, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) ?>;

const columnDefs = [
    { field: 'campaign_id', headerName: 'ID', width: 80, sortable: true },
    {
        field: 'ad_title',
        headerName: '캠페인명',
        flex: 1,
        sortable: true,
        filter: true,
    },
    {
        field: 'hospital_name',
        headerName: '병원명',
        width: 180,
        sortable: true,
        filter: true,
    },
    {
        field: 'ad_start_date',
        headerName: '광고 시작일',
        width: 130,
        sortable: true,
    },
    {
        field: 'ad_end_date',
        headerName: '광고 종료일',
        width: 130,
        sortable: true,
    },
    {
        field: 'consumed',
        headerName: 'DB 소진액',
        width: 150,
        sortable: true,
        sort: 'desc',
        valueFormatter: (p) => p.value ? Number(p.value).toLocaleString() + '원' : '0원',
        cellStyle: { color: '#1D9E75', fontWeight: '600' },
    },
];

agGrid.createGrid(document.getElementById('campaignGrid'), {
    columnDefs,
    rowData,
    pagination: true,
    paginationPageSize: 20,
    defaultColDef: { resizable: true },
});
</script>
