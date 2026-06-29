<?php
/** @var array<int, array<string, mixed>> $hospitals */
/** @var int $total */
/** @var array<string, mixed> $params */

$statusLabels = [
    'active'   => ['label' => '활성', 'color' => '#10b981'],
    'inactive' => ['label' => '비활성', 'color' => '#9ca3af'],
];
?>
<div class="page-header" style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;">
    <h1 class="page-title">병원 관리</h1>
    <a href="/admin/hospitals/new" class="btn btn-primary btn-sm">+ 병원 등록</a>
</div>

<?php if (session('success')): ?>
<div class="alert" style="margin-bottom:16px;padding:12px 16px;background:#ecfdf5;border:1px solid #a7f3d0;border-radius:6px;color:#047857;font-size:14px;">
    <?= esc(session('success')) ?>
</div>
<?php endif; ?>

<!-- 필터 폼 -->
<form method="GET" action="/admin/hospitals" style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:16px;">
    <input type="text" name="name" class="form-control" style="width:220px;"
           placeholder="병원명 검색" value="<?= esc($params['name']) ?>">
    <select name="status" class="form-control" style="width:120px;">
        <option value="">전체 상태</option>
        <?php foreach ($statusLabels as $val => $info): ?>
            <option value="<?= esc($val) ?>" <?= (string) $params['status'] === (string) $val ? 'selected' : '' ?>>
                <?= esc($info['label']) ?>
            </option>
        <?php endforeach; ?>
    </select>
    <button type="submit" class="btn btn-primary btn-sm">검색</button>
    <a href="/admin/hospitals" class="btn btn-outline btn-sm">초기화</a>
</form>

<p class="text-sm" style="color:var(--color-text-muted);margin-bottom:8px;">
    총 <strong><?= number_format($total) ?></strong>건
</p>

<div id="hospitalGrid" style="height:600px;" class="ag-theme-alpine"></div>

<script>
const statusLabels = <?= json_encode(array_map(fn($v) => $v['label'], $statusLabels)) ?>;
const statusColors = <?= json_encode(array_map(fn($v) => $v['color'], $statusLabels)) ?>;
const rowData = <?= json_encode($hospitals) ?>;

const columnDefs = [
    { field: 'id', headerName: 'ID', width: 70, sortable: true },
    {
        field: 'name',
        headerName: '병원명',
        flex: 2,
        cellRenderer: (p) => {
            const a = document.createElement('a');
            a.href = `/admin/hospitals/${p.data.id}/edit`;
            a.style.cssText = 'color:var(--color-primary,#0F6E56);text-decoration:none;';
            a.textContent = p.value;
            return a;
        },
    },
    { field: 'type_label', headerName: '유형', width: 110 },
    {
        field: 'department_names',
        headerName: '진료과',
        flex: 2,
        valueFormatter: (p) => p.value || '-',
    },
    { field: 'phone', headerName: '연락처', width: 140, valueFormatter: (p) => p.value ?? '-' },
    {
        field: 'status',
        headerName: '상태',
        width: 90,
        cellRenderer: (p) => {
            const span = document.createElement('span');
            span.textContent = statusLabels[p.value] ?? p.value;
            span.style.cssText = `color:${statusColors[p.value] ?? '#666'};font-weight:500;`;
            return span;
        },
    },
    { field: 'created_at_kst', headerName: '등록일', width: 140 },
    {
        headerName: '관리',
        width: 140,
        sortable: false,
        cellRenderer: (p) => {
            const wrap = document.createElement('div');
            wrap.style.cssText = 'display:flex;gap:6px;align-items:center;';

            const edit = document.createElement('a');
            edit.href = `/admin/hospitals/${p.data.id}/edit`;
            edit.textContent = '수정';
            edit.className = 'btn btn-outline btn-sm';

            const del = document.createElement('button');
            del.type = 'button';
            del.textContent = '삭제';
            del.className = 'btn btn-outline btn-sm';
            del.style.color = '#ef4444';
            del.addEventListener('click', () => deleteHospital(p.data.id, p.data.name));

            wrap.appendChild(edit);
            wrap.appendChild(del);
            return wrap;
        },
    },
];

agGrid.createGrid(document.getElementById('hospitalGrid'), {
    columnDefs,
    rowData,
    pagination: true,
    paginationPageSize: 20,
    defaultColDef: { resizable: true },
});

const CSRF = { name: '<?= csrf_token() ?>', hash: '<?= csrf_hash() ?>' };

function deleteHospital(id, name) {
    if (!confirm(`'${name}' 병원을 삭제하시겠습니까?`)) return;

    const form = document.createElement('form');
    form.method = 'POST';
    form.action = `/admin/hospitals/${id}/delete`;

    const token = document.createElement('input');
    token.type = 'hidden';
    token.name = CSRF.name;
    token.value = CSRF.hash;
    form.appendChild(token);

    document.body.appendChild(form);
    form.submit();
}
</script>
