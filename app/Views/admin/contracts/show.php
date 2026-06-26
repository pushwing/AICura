<?php
/** @var array<string, mixed> $contract */
/** @var array<int, array<string, mixed>> $contract['orders'] */

$contractStatusLabels = [
    1 => ['label' => '정상',    'color' => '#10b981'],
    2 => ['label' => '발행환불', 'color' => '#f59e0b'],
    3 => ['label' => '발행취소', 'color' => '#ef4444'],
    4 => ['label' => '계약취소', 'color' => '#6b7280'],
    5 => ['label' => '계약환불', 'color' => '#ef4444'],
    6 => ['label' => '이월종료', 'color' => '#6b7280'],
];

$adTypeLabels  = [1 => 'CPA', 2 => 'CPM'];
$adType2Labels = [1 => '이벤트', 2 => '메인배너', 3 => '이벤트존메인배너', 4 => 'CPM', 5 => '기타'];
?>
<div class="page-header" style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;">
    <div style="display:flex;align-items:center;gap:12px;">
        <a href="/admin/contracts" style="color:var(--color-text-muted);text-decoration:none;">← 목록</a>
        <h1 class="page-title" style="margin:0;"><?= esc($contract['hospital_name']) ?> 계약</h1>
    </div>
    <a href="/admin/contracts/orders/new?contract_id=<?= (int) $contract['id'] ?>"
       class="btn btn-primary btn-sm">+ 수주계약 추가</a>
</div>

<!-- 메인 계약 정보 -->
<div class="card" style="margin-bottom:20px;">
    <div class="card-body">
        <h3 style="font-size:15px;margin-bottom:16px;">계약 정보</h3>
        <table style="width:100%;font-size:14px;border-collapse:collapse;">
            <?php foreach ([
                ['계약 ID',   (string) $contract['id']],
                ['병원명',    esc($contract['hospital_name'])],
                ['계약명',    esc($contract['title'])],
                ['결제방식',  $contract['pay_type'] === 1 ? '선불' : '후불'],
                ['등록일',    esc($contract['created_at'])],
            ] as [$label, $value]): ?>
            <tr>
                <td style="padding:8px 0;color:var(--color-text-muted);width:120px;"><?= $label ?></td>
                <td style="padding:8px 0;"><?= $value ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>
</div>

<!-- 수주계약 목록 -->
<h2 style="font-size:16px;margin-bottom:12px;">수주계약 목록</h2>
<div id="orderGrid" style="height:400px;" class="ag-theme-alpine"></div>

<script>
const contractStatusLabels = <?= json_encode(array_map(fn($v) => $v['label'], $contractStatusLabels), JSON_UNESCAPED_UNICODE) ?>;
const contractStatusColors = <?= json_encode(array_map(fn($v) => $v['color'], $contractStatusLabels), JSON_UNESCAPED_UNICODE) ?>;
const adTypeLabels  = <?= json_encode($adTypeLabels, JSON_UNESCAPED_UNICODE) ?>;
const adType2Labels = <?= json_encode($adType2Labels, JSON_UNESCAPED_UNICODE) ?>;
const rowData = <?= json_encode($contract['orders'] ?? [], JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) ?>;

const columnDefs = [
    { field: 'id', headerName: 'ID', width: 70, sortable: true },
    {
        field: 'ad_type',
        headerName: '계약방식',
        width: 100,
        valueFormatter: (p) => adTypeLabels[p.value] ?? p.value,
    },
    {
        field: 'ad_type2',
        headerName: '상품종류',
        width: 130,
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
        width: 160,
        valueFormatter: (p) => p.value ?? '미입금',
    },
    {
        field: 'tax_issue_date',
        headerName: '세금계산서',
        width: 130,
        valueFormatter: (p) => p.value ?? '미발행',
    },
    { field: 'created_at', headerName: '등록일', width: 160 },
    {
        headerName: '상세',
        width: 70,
        sortable: false,
        cellRenderer: (p) => `<a href="/admin/contracts/orders/${p.data.id}" style="font-size:12px;color:var(--color-primary,#0F6E56);">보기</a>`,
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
