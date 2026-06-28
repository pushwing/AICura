<?php
/** @var array<int, array<string, mixed>> $payments */
/** @var int $total */
/** @var array<string, mixed> $params */
/** @var array<string, array<string, string>> $statuses */
/** @var array<int, string> $paymentTypes */
?>
<div class="page-header" style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;">
    <h1 class="page-title">결제 관리</h1>
</div>

<!-- 필터 폼 -->
<form method="GET" action="/admin/payments" style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:16px;">
    <select name="status" class="form-control" style="width:130px;">
        <option value="">전체 상태</option>
        <?php foreach ($statuses as $val => $info): ?>
            <option value="<?= esc($val) ?>" <?= $params['status'] === $val ? 'selected' : '' ?>>
                <?= esc($info['label']) ?>
            </option>
        <?php endforeach; ?>
    </select>
    <input type="text" name="hospital_name" class="form-control" style="width:200px;"
           placeholder="병원명 검색" value="<?= esc($params['hospital_name']) ?>">
    <input type="date" name="date_from" class="form-control" style="width:150px;"
           value="<?= esc($params['date_from']) ?>">
    <span style="line-height:36px;color:var(--color-text-muted);">~</span>
    <input type="date" name="date_to" class="form-control" style="width:150px;"
           value="<?= esc($params['date_to']) ?>">
    <button type="submit" class="btn btn-primary btn-sm">검색</button>
    <a href="/admin/payments" class="btn btn-outline btn-sm">초기화</a>
</form>

<p class="text-sm" style="color:var(--color-text-muted);margin-bottom:8px;">
    총 <strong><?= number_format($total) ?></strong>건
</p>

<!-- AG Grid -->
<div id="paymentGrid" style="height:600px;" class="ag-theme-alpine"></div>

<script>
const statusMap    = <?= json_encode(array_map(fn($v) => $v['label'], $statuses), JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) ?>;
const statusColors = <?= json_encode(array_map(fn($v) => $v['color'], $statuses), JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) ?>;
const typeMap      = <?= json_encode($paymentTypes, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) ?>;
const rowData      = <?= json_encode($payments, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) ?>;

const columnDefs = [
    { field: 'id', headerName: 'ID', width: 80, sortable: true },
    {
        field: 'hospital_name',
        headerName: '병원명',
        flex: 1,
        valueGetter: (p) => p.data.hospital_name || p.data.order_hospital_name || '-',
    },
    {
        field: 'type',
        headerName: '결제유형',
        width: 110,
        valueFormatter: (p) => typeMap[p.value] ?? p.value,
    },
    {
        field: 'amount',
        headerName: '금액',
        width: 130,
        valueFormatter: (p) => p.value ? Number(p.value).toLocaleString() + '원' : '-',
    },
    { field: 'result_code', headerName: '결과코드', width: 110 },
    {
        field: 'status',
        headerName: '상태',
        width: 100,
        cellRenderer: (p) => {
            const color = statusColors[p.value] ?? '#6b7280';
            const label = statusMap[p.value] ?? p.value;
            return `<span style="background:${color}20;color:${color};padding:2px 8px;border-radius:4px;font-size:12px;">${label}</span>`;
        },
    },
    {
        field: 'created_at',
        headerName: '등록일',
        width: 160,
    },
    {
        headerName: '상세',
        width: 70,
        sortable: false,
        cellRenderer: (p) => `<a href="/admin/payments/${p.data.id}" style="font-size:12px;color:var(--color-primary, #0F6E56);">보기</a>`,
    },
];

agGrid.createGrid(document.getElementById('paymentGrid'), {
    columnDefs,
    rowData,
    pagination: true,
    paginationPageSize: 20,
    defaultColDef: { resizable: true },
});
</script>
