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
    <label style="display:flex;align-items:center;gap:6px;font-size:13px;color:var(--color-text-muted);">
        <input type="checkbox" name="suspicious" value="1" <?= !empty($params['suspicious']) ? 'checked' : '' ?>>
        의심 후기만
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

// AI 분석 상태/감성/플래그 (이슈 #74)
const aiStatusLabels = { 0: '미분석', 1: '분석 대기중', 2: '완료', 3: '실패' };
const sentimentMeta = {
    positive: { label: '긍정', color: '#10b981' },
    neutral:  { label: '중립', color: '#6b7280' },
    negative: { label: '부정', color: '#ef4444' },
};
const flagLabels = {
    spam: '스팸', fake: '가짜', exaggeration: '과장',
    medical_overclaim: '의학적과장', advertisement: '광고성', duplicate: '중복',
};

// 신뢰점수 색상 — 낮을수록 위험
function trustColor(score) {
    if (score < 40) return '#ef4444';
    if (score < 70) return '#f59e0b';
    return '#10b981';
}

function badge(label, color) {
    return `<span style="background:${color}20;color:${color};padding:2px 8px;border-radius:4px;font-size:12px;">${escHtml(label)}</span>`;
}

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
        field: 'ai_trust_score',
        headerName: '신뢰점수',
        width: 110,
        sortable: true,
        cellRenderer: (p) => {
            if (Number(p.data.ai_status) !== 2 || p.value === null || p.value === undefined) {
                return `<span style="color:#9ca3af;font-size:12px;">${escHtml(aiStatusLabels[p.data.ai_status] ?? '미분석')}</span>`;
            }
            const score = Number(p.value);
            return badge(score + '점', trustColor(score));
        },
    },
    {
        field: 'ai_sentiment',
        headerName: '감성',
        width: 90,
        cellRenderer: (p) => {
            const m = sentimentMeta[p.value];
            return m ? badge(m.label, m.color) : `<span style="color:#9ca3af;">-</span>`;
        },
    },
    {
        field: 'ai_flags',
        headerName: '플래그',
        flex: 1,
        sortable: false,
        cellRenderer: (p) => {
            const flags = Array.isArray(p.value) ? p.value : [];
            if (flags.length === 0) return `<span style="color:#9ca3af;">-</span>`;
            return flags.map((f) => badge(flagLabels[f] ?? f, '#ef4444')).join(' ');
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
