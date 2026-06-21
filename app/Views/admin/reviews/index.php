<?php
/** @var list<array<string, mixed>> $reviews */
/** @var int $total */
/** @var array<string, mixed> $params */

$campaignStatusLabels = [
    'pending'  => ['label' => '검토중', 'color' => '#f59e0b'],
    'active'   => ['label' => '진행중', 'color' => '#10b981'],
    'rejected' => ['label' => '반려',   'color' => '#ef4444'],
    'ended'    => ['label' => '종료',   'color' => '#6b7280'],
];
?>

<div class="page-header" style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;">
    <h1 class="page-title" style="margin:0;">검수관리</h1>
    <span style="font-size:13px;color:var(--color-text-muted);">검수 대기 <?= $total ?>건</span>
</div>

<?php if (session()->getFlashdata('success')): ?>
<div style="background:#d1fae5;border:1px solid #6ee7b7;color:#065f46;padding:10px 16px;border-radius:6px;margin-bottom:16px;font-size:14px;">
    <?= esc(session()->getFlashdata('success')) ?>
</div>
<?php endif; ?>

<?php if (session()->getFlashdata('error')): ?>
<div style="background:#fee2e2;border:1px solid #fca5a5;color:#991b1b;padding:10px 16px;border-radius:6px;margin-bottom:16px;font-size:14px;">
    <?= esc(session()->getFlashdata('error')) ?>
</div>
<?php endif; ?>

<!-- 검색 -->
<form method="GET" action="/admin/reviews" style="margin-bottom:16px;display:flex;gap:8px;">
    <input type="text" name="keyword" class="form-control" style="max-width:280px;"
           value="<?= esc($params['keyword']) ?>" placeholder="캠페인명 또는 병원명 검색">
    <button type="submit" class="btn btn-primary btn-sm">검색</button>
    <?php if ($params['keyword'] !== ''): ?>
    <a href="/admin/reviews" class="btn btn-outline btn-sm">초기화</a>
    <?php endif; ?>
</form>

<div class="card">
    <div class="card-body" style="padding:0;">
        <div id="reviewGrid" style="height:500px;" class="ag-theme-alpine"></div>
    </div>
</div>

<!-- 페이징 -->
<?php
$totalPages = (int) ceil($total / $params['limit']);
$page       = $params['page'];
if ($totalPages > 1):
?>
<div style="display:flex;justify-content:center;gap:4px;margin-top:16px;">
    <?php for ($p = 1; $p <= $totalPages; $p++): ?>
    <a href="?page=<?= $p ?>&keyword=<?= urlencode($params['keyword']) ?>"
       style="padding:4px 10px;border-radius:4px;font-size:13px;text-decoration:none;
              <?= $p === $page ? 'background:var(--color-primary);color:#fff;' : 'color:var(--color-text-muted);border:1px solid var(--color-border);' ?>">
        <?= $p ?>
    </a>
    <?php endfor; ?>
</div>
<?php endif; ?>

<script src="https://cdn.jsdelivr.net/npm/ag-grid-community/dist/ag-grid-community.min.js"></script>
<script>
const rows = <?= json_encode(array_map(function (array $r) use ($campaignStatusLabels): array {
    return [
        'id'              => $r['id'],
        'campaign_id'     => $r['campaign_id'],
        'ad_title'        => $r['ad_title'] ?? '-',
        'hospital_name'   => $r['hospital_name'] ?? '-',
        'campaign_status' => $r['campaign_status'] ?? '-',
        'created_by_name' => $r['created_by_name'] ?? '-',
        'created_at'      => $r['created_at'] ?? '-',
    ];
}, $reviews), JSON_UNESCAPED_UNICODE) ?>;

const campaignStatusLabels = <?= json_encode(array_map(fn($v) => $v['label'], $campaignStatusLabels), JSON_UNESCAPED_UNICODE) ?>;

agGrid.createGrid(document.getElementById('reviewGrid'), {
    columnDefs: [
        { field: 'id',            headerName: '#',       width: 70 },
        { field: 'ad_title',      headerName: '캠페인명', flex: 1 },
        { field: 'hospital_name', headerName: '병원명',   width: 160 },
        {
            field: 'campaign_status', headerName: '캠페인 상태', width: 120,
            cellRenderer: params => {
                const labels = { pending: '검토중', active: '진행중', rejected: '반려', ended: '종료' };
                const colors = { pending: '#f59e0b', active: '#10b981', rejected: '#ef4444', ended: '#6b7280' };
                const v = params.value;
                return `<span style="background:${colors[v] ?? '#888'}20;color:${colors[v] ?? '#888'};padding:2px 8px;border-radius:4px;font-size:12px;">${labels[v] ?? v}</span>`;
            },
        },
        { field: 'created_by_name', headerName: '수정자',    width: 120 },
        { field: 'created_at',      headerName: '수정일시',  width: 160 },
        {
            headerName: '검수',
            width: 90,
            cellRenderer: params => `<a href="/admin/reviews/${params.data.id}" style="color:var(--color-primary);text-decoration:none;font-size:13px;">상세 →</a>`,
        },
    ],
    rowData: rows,
    pagination: false,
    rowHeight: 44,
    defaultColDef: { resizable: true, sortable: true },
});
</script>
