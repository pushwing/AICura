<?php
/** @var array<int, array<string, mixed>> $temps */
/** @var int $total */
/** @var array<string, mixed> $params */

$adTypeLabels = \App\Models\CampaignModel::AD_TYPES;
$channelLabels = \App\Models\CampaignModel::CHANNELS;
?>

<div class="page-header" style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;">
    <div style="display:flex;align-items:center;gap:12px;">
        <a href="/admin/campaigns" style="color:var(--color-text-muted);text-decoration:none;">← 목록</a>
        <h1 class="page-title" style="margin:0;">임시저장 목록</h1>
    </div>
</div>

<form method="GET" action="/admin/campaigns/temp" style="display:flex;gap:8px;margin-bottom:16px;">
    <input type="text" name="keyword" class="form-control" style="width:240px;"
           placeholder="캠페인명 검색" value="<?= esc($params['keyword']) ?>">
    <button type="submit" class="btn btn-primary btn-sm">검색</button>
    <a href="/admin/campaigns/temp" class="btn btn-outline btn-sm">초기화</a>
</form>

<p class="text-sm" style="color:var(--color-text-muted);margin-bottom:8px;">
    총 <strong><?= number_format($total) ?></strong>건
</p>

<div id="tempGrid" style="height:500px;" class="ag-theme-alpine"></div>

<script>
const adTypeLabels = <?= json_encode($adTypeLabels) ?>;
const channelLabels = <?= json_encode($channelLabels) ?>;
const rowData = <?= json_encode($temps) ?>;

agGrid.createGrid(document.getElementById('tempGrid'), {
    columnDefs: [
        { field: 'id',            headerName: 'ID',       width: 70 },
        {
            field: 'ad_title',
            headerName: '캠페인명',
            flex: 2,
            cellRenderer: (p) => p.value
                ? `<span>${p.value}</span>`
                : '<span style="color:#aaa">제목 없음</span>',
        },
        { field: 'hospital_name', headerName: '병원',     flex: 1 },
        {
            field: 'ad_type',
            headerName: '타입',
            width: 100,
            valueFormatter: (p) => adTypeLabels[p.value] ?? '-',
        },
        {
            field: 'channel',
            headerName: '채널',
            width: 120,
            valueFormatter: (p) => channelLabels[p.value] ?? '-',
        },
        { field: 'created_at', headerName: '저장일시', width: 160 },
        {
            headerName: '액션',
            width: 120,
            sortable: false,
            cellRenderer: (p) => `
                <a href="/admin/campaigns/new?from_temp=${p.data.id}"
                   style="font-size:12px;color:var(--color-primary, #0F6E56);margin-right:8px;">불러오기</a>
            `,
        },
    ],
    rowData,
    pagination: true,
    paginationPageSize: 20,
    defaultColDef: { resizable: true },
});
</script>
