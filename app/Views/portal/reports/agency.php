<?php
/** @var int $year */
/** @var list<int> $years */
/** @var array{advertiser_count:int, charged:int, consumed:int, refunded:int, cpa_refunded:int, balance:int, requested:int, visited:int} $summary */
/** @var list<array<string, mixed>> $breakdown */
/** @var array<string, mixed>|null $aiRevenue */
/** @var array<string, mixed>|null $aiConsumption */

$kpiCards = [
    ['label' => '광고주 수', 'value' => $summary['advertiser_count'], 'unit' => '개', 'color' => 'var(--color-text)'],
    ['label' => '충전 합계', 'value' => $summary['charged'],  'unit' => '원', 'color' => '#0F6E56'],
    ['label' => '소진 합계', 'value' => $summary['consumed'], 'unit' => '원', 'color' => '#1D9E75'],
    ['label' => 'CPA 환불', 'value' => $summary['cpa_refunded'], 'unit' => '원', 'color' => '#f59e0b'],
    ['label' => '잔액 합계', 'value' => $summary['balance'],  'unit' => '원', 'color' => '#6366f1'],
    ['label' => '신청 건수', 'value' => $summary['requested'], 'unit' => '건', 'color' => 'var(--color-text)'],
    ['label' => '내원완료', 'value' => $summary['visited'],    'unit' => '건', 'color' => '#0ea5e9'],
];
?>
<div class="page-header" style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;">
    <h1 class="page-title">리포트</h1>
    <form method="GET" action="/portal/reports" style="margin:0;">
        <select name="year" class="form-control" style="width:120px;" onchange="this.form.submit()">
            <?php foreach ($years as $y): ?>
                <option value="<?= (int) $y ?>" <?= $year === $y ? 'selected' : '' ?>><?= (int) $y ?>년</option>
            <?php endforeach; ?>
        </select>
    </form>
</div>

<!-- AI 일일 보고서 (이슈 #65) — 소속 광고주 합산 -->
<?= view('reports/_ai_section', [
    'aiRevenue'     => $aiRevenue,
    'aiConsumption' => $aiConsumption,
    'aiBasePath'    => '/portal/reports',
    'aiCanGenerate' => false,
]) ?>

<!-- 전체 합계 KPI -->
<div style="display:grid;grid-template-columns:repeat(7, 1fr);gap:12px;margin-bottom:20px;">
    <?php foreach ($kpiCards as $card): ?>
        <div class="card"><div class="card-body">
            <p style="font-size:13px;color:var(--color-text-muted);margin:0 0 8px;"><?= esc($card['label']) ?></p>
            <p style="font-size:20px;font-weight:700;margin:0;color:<?= esc($card['color']) ?>;">
                <?= number_format((int) $card['value']) ?><span style="font-size:13px;font-weight:400;"><?= esc($card['unit']) ?></span>
            </p>
        </div></div>
    <?php endforeach; ?>
</div>

<?php if ($breakdown === []): ?>
    <div class="alert alert-danger">
        <span class="alert-icon">!</span>
        <div class="alert-body">소속 광고주가 없습니다. 광고주를 등록하면 매출 리포트가 집계됩니다.</div>
    </div>
<?php else: ?>

    <!-- 광고주별 충전/소진 차트 -->
    <div class="card" style="margin-bottom:20px;">
        <div class="card-body">
            <h3 style="margin:0 0 16px;font-size:15px;"><?= (int) $year ?>년 광고주별 충전/소진</h3>
            <canvas id="advertiserChart" height="80"></canvas>
        </div>
    </div>

    <!-- 광고주별 개별 표 -->
    <div class="card">
        <div class="card-body">
            <h3 style="margin:0 0 12px;font-size:15px;">광고주별 매출</h3>
            <div id="advertiserGrid" style="height:480px;" class="ag-theme-alpine"></div>
        </div>
    </div>

    <script>
    const rows = <?= json_encode($breakdown, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;

    function escHtml(s) {
        return String(s ?? '').replace(/[&<>"']/g, (c) => ({
            '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;',
        }[c]));
    }

    // 광고주별 충전/소진 막대 차트
    new Chart(document.getElementById('advertiserChart'), {
        type: 'bar',
        data: {
            labels: rows.map((r) => r.hospital_name || '(이름없음)'),
            datasets: [
                { label: '충전', data: rows.map((r) => Number(r.charged || 0)),  backgroundColor: '#0F6E56' },
                { label: '소진', data: rows.map((r) => Number(r.consumed || 0)), backgroundColor: '#1D9E75' },
            ],
        },
        options: {
            responsive: true,
            scales: { y: { beginAtZero: true, ticks: { callback: (v) => Number(v).toLocaleString() } } },
            plugins: {
                tooltip: {
                    callbacks: { label: (ctx) => `${ctx.dataset.label}: ${Number(ctx.parsed.y).toLocaleString()}원` },
                },
            },
        },
    });

    agGrid.createGrid(document.getElementById('advertiserGrid'), {
        columnDefs: [
            {
                field: 'hospital_name', headerName: '광고주(병원)', flex: 2,
                cellRenderer: (p) => `<a href="/portal/advertisers/${Number(p.data.advertiser_id)}" style="color:var(--color-primary, #0F6E56);text-decoration:none;">${escHtml(p.value)}</a>`,
            },
            { field: 'charged', headerName: '충전', width: 130, type: 'numericColumn', valueFormatter: (p) => Number(p.value || 0).toLocaleString() + '원' },
            { field: 'consumed', headerName: '소진', width: 130, type: 'numericColumn', valueFormatter: (p) => Number(p.value || 0).toLocaleString() + '원' },
            { field: 'refunded', headerName: '환불', width: 120, type: 'numericColumn', valueFormatter: (p) => Number(p.value || 0).toLocaleString() + '원' },
            { field: 'cpa_refunded', headerName: 'CPA환불', width: 120, type: 'numericColumn', valueFormatter: (p) => Number(p.value || 0).toLocaleString() + '원' },
            { field: 'balance', headerName: '잔액', width: 130, type: 'numericColumn', valueFormatter: (p) => Number(p.value || 0).toLocaleString() + '원' },
            { field: 'requested', headerName: '신청', width: 90, type: 'numericColumn', valueFormatter: (p) => Number(p.value || 0).toLocaleString() },
            { field: 'visited', headerName: '내원완료', width: 100, type: 'numericColumn', valueFormatter: (p) => Number(p.value || 0).toLocaleString() },
        ],
        rowData: rows,
        pagination: true,
        paginationPageSize: 20,
        defaultColDef: { resizable: true, sortable: true },
        localeText: { noRowsToShow: '집계할 광고주가 없습니다.' },
    });
    </script>

<?php endif; ?>
