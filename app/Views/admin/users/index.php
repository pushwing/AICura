<?php
/** @var array<int, array<string, mixed>> $users */
/** @var int $total */
/** @var int $page */
/** @var int $lastPage */
/** @var int $typeGroup */
/** @var int $subType */
/** @var string $isDormant */
/** @var string $searchWord */
/** @var bool $isAgency */
/** @var array<int, string> $tabLabels */
/** @var array<int, list<int>> $typeGroups */
/** @var array<int, string> $userTypeLabels */

// 필터를 유지한 채 page 만 교체하는 링크 헬퍼
$pageLink = static function (int $p) use ($typeGroup, $subType, $isDormant, $searchWord): string {
    $query = array_filter([
        'type'        => $typeGroup,
        'sub_type'    => $subType > 0 ? $subType : null,
        'is_dormant'  => $isDormant !== '' ? $isDormant : null,
        'search_word' => $searchWord !== '' ? $searchWord : null,
        'page'        => $p,
    ], static fn ($v) => $v !== null);

    return '/admin/users?' . http_build_query($query);
};
?>
<div class="page-header" style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;">
    <h1 class="page-title">사용자 관리</h1>
</div>

<!-- 탭 -->
<div style="display:flex;gap:4px;margin-bottom:20px;border-bottom:2px solid var(--color-border,#e5e7eb);">
    <?php foreach ($tabLabels as $groupId => $label): ?>
    <a href="/admin/users?type=<?= $groupId ?>"
       style="padding:8px 18px;font-size:14px;text-decoration:none;border-bottom:2px solid transparent;margin-bottom:-2px;color:var(--color-text-muted);
              <?= $typeGroup === $groupId ? 'border-bottom-color:var(--color-primary,#0F6E56);color:var(--color-primary,#0F6E56);font-weight:600;' : '' ?>">
        <?= esc($label) ?>
    </a>
    <?php endforeach; ?>
</div>

<!-- 필터 폼 -->
<form method="GET" action="/admin/users" style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:16px;">
    <input type="hidden" name="type" value="<?= $typeGroup ?>">

    <?php if ($typeGroup > 1 && isset($typeGroups[$typeGroup])): ?>
    <select name="sub_type" class="form-control" style="width:160px;">
        <option value="">전체 유형</option>
        <?php foreach ($typeGroups[$typeGroup] as $typeVal): ?>
            <option value="<?= $typeVal ?>" <?= $subType === $typeVal ? 'selected' : '' ?>>
                <?= esc($userTypeLabels[$typeVal] ?? (string) $typeVal) ?>
            </option>
        <?php endforeach; ?>
    </select>
    <?php endif; ?>

    <select name="is_dormant" class="form-control" style="width:120px;">
        <option value="">전체</option>
        <option value="1" <?= $isDormant === '1' ? 'selected' : '' ?>>활성</option>
        <option value="0" <?= $isDormant === '0' ? 'selected' : '' ?>>휴면</option>
    </select>

    <input type="text" name="search_word" class="form-control" style="width:220px;"
           placeholder="이메일 / 이름 / 전화번호" value="<?= esc($searchWord) ?>">

    <button type="submit" class="btn btn-primary btn-sm">검색</button>
    <a href="/admin/users?type=<?= $typeGroup ?>" class="btn btn-outline btn-sm">초기화</a>
</form>

<p class="text-sm" style="color:var(--color-text-muted);margin-bottom:8px;">
    총 <strong><?= number_format($total) ?></strong>건
</p>

<!-- AG Grid (서버사이드 페이징 — 현재 페이지만 표시) -->
<div id="userGrid" style="height:600px;" class="ag-theme-alpine"></div>

<!-- 서버사이드 페이지네이션 -->
<?php if ($lastPage > 1): ?>
<nav style="display:flex;justify-content:center;align-items:center;gap:6px;margin-top:16px;">
    <?php if ($page > 1): ?>
        <a href="<?= esc($pageLink($page - 1), 'attr') ?>" class="btn btn-outline btn-sm">이전</a>
    <?php endif; ?>

    <?php
    $start = max(1, $page - 2);
    $end   = min($lastPage, $page + 2);
    for ($p = $start; $p <= $end; $p++):
    ?>
        <?php if ($p === $page): ?>
            <span class="btn btn-primary btn-sm"><?= $p ?></span>
        <?php else: ?>
            <a href="<?= esc($pageLink($p), 'attr') ?>" class="btn btn-outline btn-sm"><?= $p ?></a>
        <?php endif; ?>
    <?php endfor; ?>

    <?php if ($page < $lastPage): ?>
        <a href="<?= esc($pageLink($page + 1), 'attr') ?>" class="btn btn-outline btn-sm">다음</a>
    <?php endif; ?>
</nav>
<p class="text-sm" style="text-align:center;color:var(--color-text-muted);margin-top:8px;">
    <?= $page ?> / <?= $lastPage ?> 페이지
</p>
<?php endif; ?>

<script>
const typeLabels = <?= json_encode($userTypeLabels, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) ?>;
const rowData    = <?= json_encode($users, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) ?>;
const isAgency   = <?= $isAgency ? 'true' : 'false' ?>;

const won = (v) => '₩' + Number(v || 0).toLocaleString();

const agencyColumns = [
    { field: 'advertiser_count', headerName: '소유 광고주', width: 110, valueFormatter: (p) => Number(p.value || 0).toLocaleString() + '개' },
    { field: 'order_count', headerName: '계약 건수', width: 100, valueFormatter: (p) => Number(p.value || 0).toLocaleString() + '건' },
    { field: 'total_price', headerName: '총 계약금액', width: 150, valueFormatter: (p) => won(p.value) },
];

const columnDefs = [
    { field: 'id', headerName: 'ID', width: 80, sortable: true },
    { field: 'email', headerName: '이메일', flex: 1, minWidth: 180 },
    { field: 'username', headerName: '이름', width: 120 },
    ...(isAgency ? agencyColumns : [{
        field: 'user_type',
        headerName: '유형',
        width: 130,
        valueFormatter: (p) => typeLabels[p.value] ?? p.value,
    }]),
    { field: 'phone', headerName: '전화번호', width: 130 },
    {
        field: 'is_dormant',
        headerName: '상태',
        width: 90,
        cellRenderer: (p) => {
            const active = p.value == 1;
            const color  = active ? '#0F6E56' : '#9ca3af';
            const label  = active ? '활성' : '휴면';
            return `<span style="background:${color}20;color:${color};padding:2px 8px;border-radius:4px;font-size:12px;">${label}</span>`;
        },
    },
    { field: 'last_login_at', headerName: '최근 로그인', width: 160 },
    { field: 'created_at', headerName: '가입일', width: 160 },
    {
        headerName: '상세',
        width: 70,
        sortable: false,
        cellRenderer: (p) => `<a href="/admin/users/${p.data.id}" style="font-size:12px;color:var(--color-primary,#0F6E56);">보기</a>`,
    },
];

// 페이징은 서버사이드(아래 페이지네이션)로 처리 — 그리드는 현재 페이지만 표시
agGrid.createGrid(document.getElementById('userGrid'), {
    columnDefs,
    rowData,
    defaultColDef: { resizable: true },
});
</script>
