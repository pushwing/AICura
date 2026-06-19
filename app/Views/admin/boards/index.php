<?php
/** @var array<int, array<string, mixed>> $boards */
/** @var int $total */
/** @var array<string, mixed> $params */
/** @var array<int, string> $types */
/** @var array<int, string> $deleteStates */
?>
<div class="page-header" style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;">
    <h1 class="page-title">후기 운영</h1>
</div>

<!-- 필터 -->
<form method="GET" action="/admin/boards" style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:16px;">
    <select name="type" class="form-control" style="width:120px;">
        <option value="">전체 유형</option>
        <?php foreach ($types as $val => $label): ?>
            <option value="<?= esc((string) $val) ?>" <?= (string) $params['type'] === (string) $val ? 'selected' : '' ?>>
                <?= esc($label) ?>
            </option>
        <?php endforeach; ?>
    </select>
    <select name="is_delete" class="form-control" style="width:130px;">
        <option value="">전체 상태</option>
        <?php foreach ($deleteStates as $val => $label): ?>
            <option value="<?= esc((string) $val) ?>" <?= (string) $params['is_delete'] === (string) $val ? 'selected' : '' ?>>
                <?= esc($label) ?>
            </option>
        <?php endforeach; ?>
    </select>
    <label style="display:flex;align-items:center;gap:6px;font-size:13px;color:var(--color-text-muted);">
        <input type="checkbox" name="reported" value="1" <?= !empty($params['reported']) ? 'checked' : '' ?>>
        신고만 보기
    </label>
    <input type="text" name="keyword" class="form-control" style="width:200px;"
           placeholder="제목, 작성자 검색" value="<?= esc((string) $params['keyword']) ?>">
    <button type="submit" class="btn btn-primary btn-sm">검색</button>
    <a href="/admin/boards" class="btn btn-outline btn-sm">초기화</a>
</form>

<p class="text-sm" style="color:var(--color-text-muted);margin-bottom:8px;">
    총 <strong><?= number_format($total) ?></strong>건
</p>

<!-- AG Grid -->
<div id="boardGrid" style="height:600px;" class="ag-theme-alpine"></div>

<script>
const types        = <?= json_encode($types, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
const deleteStates = <?= json_encode($deleteStates, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
const rowData      = <?= json_encode($boards, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;

function escHtml(s) {
    return String(s ?? '').replace(/[&<>"']/g, (c) => ({
        '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;',
    }[c]));
}

const deleteColors = { 0: '#10b981', 1: '#f59e0b', 2: '#ef4444' };

const columnDefs = [
    { field: 'id', headerName: 'ID', width: 80, sortable: true },
    {
        field: 'type',
        headerName: '유형',
        width: 90,
        valueFormatter: (p) => types[p.value] ?? p.value,
    },
    {
        field: 'subject',
        headerName: '제목',
        flex: 2,
        cellRenderer: (p) => `<a href="/admin/boards/${Number(p.data.id)}" style="color:var(--color-primary, #0F6E56);text-decoration:none;">${escHtml(p.value) || '(제목없음)'}</a>`,
    },
    { field: 'user_name', headerName: '작성자', flex: 1 },
    {
        field: 'rate_sum',
        headerName: '별점',
        width: 90,
        valueFormatter: (p) => p.value ? Number(p.value).toFixed(1) : '-',
    },
    {
        field: 'like_count',
        headerName: '좋아요',
        width: 90,
        type: 'numericColumn',
    },
    {
        field: 'complain_count',
        headerName: '신고',
        width: 90,
        cellRenderer: (p) => {
            const n = Number(p.value || 0);
            return n > 0
                ? `<span style="color:#ef4444;font-weight:600;">${n}</span>`
                : `<span style="color:#9ca3af;">0</span>`;
        },
    },
    {
        field: 'is_delete',
        headerName: '상태',
        width: 110,
        cellRenderer: (p) => {
            const color = deleteColors[p.value] ?? '#6b7280';
            const label = deleteStates[p.value] ?? p.value;
            return `<span style="background:${color}20;color:${color};padding:2px 8px;border-radius:4px;font-size:12px;">${escHtml(label)}</span>`;
        },
    },
    { field: 'created_at', headerName: '작성일시', width: 150 },
];

agGrid.createGrid(document.getElementById('boardGrid'), {
    columnDefs,
    rowData,
    pagination: true,
    paginationPageSize: 20,
    defaultColDef: { resizable: true, sortable: true },
});
</script>
