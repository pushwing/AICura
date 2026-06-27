<?php
/** @var array<int, array<string, mixed>> $orders */
/** @var int $total */
/** @var array<string, mixed> $params */

$contractStatusLabels = [
    1 => ['label' => '정상',    'color' => '#10b981'],
    2 => ['label' => '발행환불', 'color' => '#f59e0b'],
    3 => ['label' => '발행취소', 'color' => '#ef4444'],
    4 => ['label' => '계약취소', 'color' => '#6b7280'],
    5 => ['label' => '계약환불', 'color' => '#ef4444'],
    6 => ['label' => '이월종료', 'color' => '#6b7280'],
];

$adType2Labels = [1 => '이벤트', 2 => '메인배너', 3 => '이벤트존메인배너', 4 => 'CPM', 5 => '기타'];
?>
<div class="page-header" style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;">
    <div style="display:flex;align-items:center;gap:12px;">
        <a href="/admin/contracts" style="color:var(--color-text-muted);text-decoration:none;">← 계약 목록</a>
        <h1 class="page-title" style="margin:0;">수주계약 목록</h1>
    </div>
    <a href="/admin/contracts/orders/new" class="btn btn-primary btn-sm">+ 수주계약 등록</a>
</div>

<form method="GET" action="/admin/contracts/orders" style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:16px;">
    <input type="text" name="hospital_name" class="form-control" style="width:220px;"
           placeholder="병원명 검색" value="<?= esc($params['hospital_name']) ?>">
    <select name="contract_status" class="form-control" style="width:130px;">
        <option value="">전체 상태</option>
        <?php foreach ($contractStatusLabels as $val => $info): ?>
            <option value="<?= $val ?>" <?= (string) $params['contract_status'] === (string) $val ? 'selected' : '' ?>>
                <?= esc($info['label']) ?>
            </option>
        <?php endforeach; ?>
    </select>
    <select name="ad_type2" class="form-control" style="width:160px;">
        <option value="">전체 상품</option>
        <?php foreach ($adType2Labels as $val => $label): ?>
            <option value="<?= $val ?>" <?= (string) $params['ad_type2'] === (string) $val ? 'selected' : '' ?>>
                <?= esc($label) ?>
            </option>
        <?php endforeach; ?>
    </select>
    <button type="submit" class="btn btn-primary btn-sm">검색</button>
    <a href="/admin/contracts/orders" class="btn btn-outline btn-sm">초기화</a>
</form>

<p class="text-sm" style="color:var(--color-text-muted);margin-bottom:8px;">
    총 <strong><?= number_format($total) ?></strong>건
</p>

<div id="orderGrid" style="height:600px;" class="ag-theme-alpine"></div>

<script>
const contractStatusLabels = <?= json_encode(array_map(fn($v) => $v['label'], $contractStatusLabels), JSON_UNESCAPED_UNICODE) ?>;
const contractStatusColors = <?= json_encode(array_map(fn($v) => $v['color'], $contractStatusLabels), JSON_UNESCAPED_UNICODE) ?>;
const adType2Labels = <?= json_encode($adType2Labels, JSON_UNESCAPED_UNICODE) ?>;
const rowData = <?= json_encode($orders, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) ?>;

const columnDefs = [
    { field: 'id', headerName: 'ID', width: 70, sortable: true },
    {
        field: 'hospital_name',
        headerName: '병원명',
        flex: 1,
        cellRenderer: (p) => `<a href="/admin/contracts/orders/${p.data.id}" style="color:var(--color-primary,#0F6E56);text-decoration:none;">${p.value}</a>`,
    },
    {
        field: 'contract_title',
        headerName: '계약명',
        flex: 1,
        cellRenderer: (p) => p.data.contract_id
            ? `<a href="/admin/contracts/${p.data.contract_id}" style="color:var(--color-text-muted,#888);text-decoration:none;">${p.value}</a>`
            : (p.value ?? '-'),
    },
    {
        field: 'ad_type2',
        headerName: '상품종류',
        width: 140,
        valueFormatter: (p) => adType2Labels[p.value] ?? p.value,
    },
    {
        field: 'ad_price',
        headerName: '계약금액',
        width: 130,
        type: 'numericColumn',
        valueFormatter: (p) => Number(p.value).toLocaleString() + '원',
    },
    {
        field: 'contract_status',
        headerName: '상태',
        width: 100,
        cellRenderer: (p) => {
            const color = contractStatusColors[p.value] ?? '#6b7280';
            const label = contractStatusLabels[p.value] ?? p.value;
            return `<span style="background:${color}20;color:${color};padding:2px 8px;border-radius:4px;font-size:12px;">${label}</span>`;
        },
    },
    {
        field: 'deposit_date',
        headerName: '입금일',
        width: 140,
        valueFormatter: (p) => p.value ?? '미입금',
    },
    { field: 'created_at', headerName: '등록일', width: 160 },
    {
        headerName: '액션',
        width: 70,
        sortable: false,
        cellRenderer: (p) => `<a href="/admin/contracts/orders/${p.data.id}/edit" style="font-size:12px;color:var(--color-text-muted,#888);">수정</a>`,
    },
];

agGrid.createGrid(document.getElementById('orderGrid'), {
    columnDefs,
    rowData,
    pagination: true,
    paginationPageSize: 20,
    defaultColDef: { resizable: true },
});
</script>
