<?php
/** @var array<int, array<string, mixed>> $creatives */
/** @var int $total */
/** @var array<string, mixed> $params */

$statusLabels = [
    'pending'  => ['label' => '검토중', 'color' => '#f59e0b'],
    'active'   => ['label' => '진행중', 'color' => '#10b981'],
    'rejected' => ['label' => '반려',   'color' => '#ef4444'],
    'ended'    => ['label' => '종료',   'color' => '#6b7280'],
];

$adTypeLabels = [1 => 'CPA', 2 => 'CPM', 3 => '프로모션', 4 => 'CPC', 5 => '옵션'];
?>
<div class="page-header" style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;">
    <h1 class="page-title">소재 관리</h1>
</div>

<form method="GET" action="/admin/creatives" style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:16px;">
    <select name="status" class="form-control" style="width:120px;">
        <option value="">전체 상태</option>
        <?php foreach ($statusLabels as $val => $info): ?>
            <option value="<?= esc($val) ?>" <?= $params['status'] === $val ? 'selected' : '' ?>>
                <?= esc($info['label']) ?>
            </option>
        <?php endforeach; ?>
    </select>
    <select name="has_image" class="form-control" style="width:130px;">
        <option value="">소재 전체</option>
        <option value="1" <?= $params['has_image'] === '1' ? 'selected' : '' ?>>소재 등록됨</option>
        <option value="0" <?= $params['has_image'] === '0' ? 'selected' : '' ?>>소재 없음</option>
    </select>
    <input type="text" name="keyword" class="form-control" style="width:220px;"
           placeholder="캠페인명, 병원명 검색" value="<?= esc($params['keyword']) ?>">
    <button type="submit" class="btn btn-primary btn-sm">검색</button>
    <a href="/admin/creatives" class="btn btn-outline btn-sm">초기화</a>
</form>

<p class="text-sm" style="color:var(--color-text-muted);margin-bottom:8px;">
    총 <strong><?= number_format($total) ?></strong>건
</p>

<div id="creativeGrid" style="height:600px;" class="ag-theme-alpine"></div>

<script>
const statusLabels = <?= json_encode(array_map(fn($v) => $v['label'], $statusLabels), JSON_UNESCAPED_UNICODE) ?>;
const statusColors = <?= json_encode(array_map(fn($v) => $v['color'], $statusLabels), JSON_UNESCAPED_UNICODE) ?>;
const adTypeLabels = <?= json_encode($adTypeLabels, JSON_UNESCAPED_UNICODE) ?>;
const rowData = <?= json_encode($creatives, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) ?>;

const columnDefs = [
    { field: 'id', headerName: 'ID', width: 70, sortable: true },
    {
        field: 'ad_title',
        headerName: '캠페인명',
        flex: 2,
        cellRenderer: (p) => `<a href="/admin/creatives/${p.data.id}" style="color:var(--color-primary,#0F6E56);text-decoration:none;">${p.value}</a>`,
    },
    { field: 'hospital_name', headerName: '병원', flex: 1 },
    {
        field: 'ad_type',
        headerName: '유형',
        width: 90,
        valueFormatter: (p) => adTypeLabels[p.value] ?? p.value,
    },
    {
        field: 'status',
        headerName: '상태',
        width: 90,
        cellRenderer: (p) => {
            const color = statusColors[p.value] ?? '#6b7280';
            const label = statusLabels[p.value] ?? p.value;
            return `<span style="background:${color}20;color:${color};padding:2px 8px;border-radius:4px;font-size:12px;">${label}</span>`;
        },
    },
    {
        field: 't1_image_name',
        headerName: '썸네일1',
        width: 90,
        cellRenderer: (p) => p.value
            ? `<span style="color:#10b981;font-size:12px;">✓ 등록</span>`
            : `<span style="color:#ef4444;font-size:12px;">✗ 없음</span>`,
    },
    {
        field: 't2_image_name',
        headerName: '썸네일2',
        width: 90,
        cellRenderer: (p) => p.value
            ? `<span style="color:#10b981;font-size:12px;">✓ 등록</span>`
            : `<span style="color:#ef4444;font-size:12px;">✗ 없음</span>`,
    },
    {
        field: 'd_image_json',
        headerName: '상세이미지',
        width: 100,
        cellRenderer: (p) => {
            if (!p.value) return `<span style="color:#ef4444;font-size:12px;">✗ 없음</span>`;
            try {
                const arr = JSON.parse(p.value);
                return `<span style="color:#10b981;font-size:12px;">✓ ${arr.length}장</span>`;
            } catch { return `<span style="color:#f59e0b;font-size:12px;">? 파싱오류</span>`; }
        },
    },
    { field: 'created_at', headerName: '등록일', width: 160 },
    {
        headerName: '소재 관리',
        width: 90,
        sortable: false,
        cellRenderer: (p) => `<a href="/admin/creatives/${p.data.id}" style="font-size:12px;color:var(--color-primary,#0F6E56);">관리</a>`,
    },
];

agGrid.createGrid(document.getElementById('creativeGrid'), {
    columnDefs,
    rowData,
    pagination: true,
    paginationPageSize: 20,
    defaultColDef: { resizable: true },
});
</script>
