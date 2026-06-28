<?php
/** @var array<int, array<string, mixed>> $campaigns */
/** @var int $total */
/** @var array<string, mixed> $params */
/** @var array<int, string> $adTypes */
/** @var array<int, string> $channels */

$statusLabels = [
    'pending'  => ['label' => '검토중',  'class' => 'badge-warning'],
    'active'   => ['label' => '진행중',  'class' => 'badge-success'],
    'rejected' => ['label' => '반려',    'class' => 'badge-danger'],
    'ended'    => ['label' => '종료',    'class' => 'badge-secondary'],
];

$reviewStatusLabels = [
    'pending'  => '검수중',
    'approved' => '검수완료',
    'rejected' => '검수반려',
];
?>
<div class="page-header" style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;">
    <h1 class="page-title">캠페인 관리</h1>
    <div style="display:flex;gap:8px;">
        <a href="/admin/campaigns/temp" class="btn btn-outline btn-sm">임시저장 목록</a>
        <a href="/admin/campaigns/new" class="btn btn-primary btn-sm">+ 캠페인 등록</a>
    </div>
</div>

<!-- 필터 폼 -->
<form method="GET" action="/admin/campaigns" style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:16px;">
    <select name="status" class="form-control" style="width:120px;">
        <option value="">전체 상태</option>
        <?php foreach ($statusLabels as $val => $info): ?>
            <option value="<?= esc($val) ?>" <?= $params['status'] === $val ? 'selected' : '' ?>>
                <?= esc($info['label']) ?>
            </option>
        <?php endforeach; ?>
    </select>
    <select name="review_status" class="form-control" style="width:120px;">
        <option value="">전체 검수</option>
        <?php foreach ($reviewStatusLabels as $val => $label): ?>
            <option value="<?= esc($val) ?>" <?= ($params['review_status'] ?? '') === $val ? 'selected' : '' ?>>
                <?= esc($label) ?>
            </option>
        <?php endforeach; ?>
    </select>
    <select name="ad_type" class="form-control" style="width:130px;">
        <option value="">전체 타입</option>
        <?php foreach ($adTypes as $val => $label): ?>
            <option value="<?= esc((string) $val) ?>" <?= $params['ad_type'] == $val ? 'selected' : '' ?>>
                <?= esc($label) ?>
            </option>
        <?php endforeach; ?>
    </select>
    <select name="channel" class="form-control" style="width:140px;">
        <option value="">전체 채널</option>
        <?php foreach ($channels as $val => $label): ?>
            <option value="<?= esc((string) $val) ?>" <?= $params['channel'] == $val ? 'selected' : '' ?>>
                <?= esc($label) ?>
            </option>
        <?php endforeach; ?>
    </select>
    <input type="text" name="keyword" class="form-control" style="width:220px;"
           placeholder="캠페인명, 병원명 검색" value="<?= esc($params['keyword']) ?>">
    <button type="submit" class="btn btn-primary btn-sm">검색</button>
    <a href="/admin/campaigns" class="btn btn-outline btn-sm">초기화</a>
</form>

<p class="text-sm" style="color:var(--color-text-muted);margin-bottom:8px;">
    총 <strong><?= number_format($total) ?></strong>건
</p>

<!-- AG Grid -->
<div id="campaignGrid" style="height:600px;" class="ag-theme-alpine"></div>

<script>
const statusLabels = <?= json_encode(array_map(fn($v) => $v['label'], $statusLabels)) ?>;
const reviewStatusLabels = <?= json_encode($reviewStatusLabels) ?>;
const adTypes = <?= json_encode($adTypes) ?>;
const channels = <?= json_encode($channels) ?>;
const rowData = <?= json_encode($campaigns) ?>;

const columnDefs = [
    { field: 'id',            headerName: 'ID',     width: 70,  sortable: true },
    {
        field: 'ad_title',
        headerName: '캠페인명',
        flex: 2,
        cellRenderer: (p) => `<a href="/admin/campaigns/${p.data.id}" style="color:var(--color-primary, #0F6E56);text-decoration:none;">${p.value}</a>`,
    },
    { field: 'hospital_name',  headerName: '병원',   flex: 1 },
    {
        field: 'ad_type',
        headerName: '타입',
        width: 100,
        valueFormatter: (p) => adTypes[p.value] ?? p.value,
    },
    {
        field: 'channel',
        headerName: '채널',
        width: 120,
        valueFormatter: (p) => channels[p.value] ?? p.value,
    },
    {
        field: 'status',
        headerName: '상태',
        width: 90,
        cellRenderer: (p) => {
            const map = { pending: '#f59e0b', active: '#10b981', rejected: '#ef4444', ended: '#6b7280' };
            const color = map[p.value] ?? '#6b7280';
            const label = statusLabels[p.value] ?? p.value;
            return `<span style="background:${color}20;color:${color};padding:2px 8px;border-radius:4px;font-size:12px;">${label}</span>`;
        },
    },
    {
        field: 'review_status',
        headerName: '검수',
        width: 90,
        cellRenderer: (p) => {
            if (!p.value) return '';
            const map = { pending: '#f59e0b', approved: '#10b981', rejected: '#ef4444' };
            const color = map[p.value] ?? '#6b7280';
            const label = reviewStatusLabels[p.value] ?? p.value;
            return `<span style="background:${color}20;color:${color};padding:2px 8px;border-radius:4px;font-size:12px;">${label}</span>`;
        },
    },
    { field: 'ad_start_date',  headerName: '시작일', width: 110 },
    { field: 'ad_end_date',    headerName: '종료일', width: 110 },
    {
        field: 'db_cost',
        headerName: 'DB단가',
        width: 100,
        valueFormatter: (p) => p.value ? Number(p.value).toLocaleString() + '원' : '-',
    },
    {
        headerName: '액션',
        width: 70,
        sortable: false,
        cellRenderer: (p) => `<a href="/admin/campaigns/${p.data.id}/edit" style="font-size:12px;color:var(--color-text-muted, #888)">수정</a>`,
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
