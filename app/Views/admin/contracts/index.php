<?php
/** @var array<int, array<string, mixed>> $contracts */
/** @var int $total */
/** @var array<string, mixed> $params */
?>
<div class="page-header" style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;">
    <h1 class="page-title">계약 관리</h1>
    <a href="/admin/contracts/orders/new" class="btn btn-primary btn-sm">+ 수주계약 등록</a>
</div>

<form method="GET" action="/admin/contracts" style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:16px;">
    <input type="text" name="hospital_name" class="form-control" style="width:220px;"
           placeholder="병원명 검색" value="<?= esc($params['hospital_name']) ?>">
    <button type="submit" class="btn btn-primary btn-sm">검색</button>
    <a href="/admin/contracts" class="btn btn-outline btn-sm">초기화</a>
    <a href="/admin/contracts/orders" class="btn btn-outline btn-sm" style="margin-left:auto;">수주계약 전체 목록 →</a>
</form>

<p class="text-sm" style="color:var(--color-text-muted);margin-bottom:8px;">
    총 <strong><?= number_format($total) ?></strong>건
</p>

<div id="contractGrid" style="height:600px;" class="ag-theme-alpine"></div>

<script>
const rowData = <?= json_encode($contracts, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) ?>;

const columnDefs = [
    { field: 'id',            headerName: 'ID',      width: 70, sortable: true },
    {
        field: 'hospital_name',
        headerName: '병원명',
        flex: 2,
        cellRenderer: (p) => `<a href="/admin/contracts/${p.data.id}" style="color:var(--color-primary,#0F6E56);text-decoration:none;">${p.value}</a>`,
    },
    { field: 'title',         headerName: '계약명',  flex: 2 },
    {
        field: 'pay_type',
        headerName: '결제방식',
        width: 110,
        valueFormatter: (p) => p.value === 1 ? '선불' : '후불',
    },
    {
        field: 'order_count',
        headerName: '수주건수',
        width: 100,
        type: 'numericColumn',
    },
    {
        field: 'total_price',
        headerName: '총 계약금액',
        width: 140,
        type: 'numericColumn',
        valueFormatter: (p) => p.value ? Number(p.value).toLocaleString() + '원' : '0원',
    },
    { field: 'created_at',    headerName: '계약일',  width: 160 },
    {
        headerName: '상세',
        width: 70,
        sortable: false,
        cellRenderer: (p) => `<a href="/admin/contracts/${p.data.id}" style="font-size:12px;color:var(--color-primary,#0F6E56);">보기</a>`,
    },
];

agGrid.createGrid(document.getElementById('contractGrid'), {
    columnDefs,
    rowData,
    pagination: true,
    paginationPageSize: 20,
    defaultColDef: { resizable: true },
});
</script>
