<?php
/** @var int $year */
/** @var list<int> $years */
/** @var bool $hasHospital */
/** @var array{charged:int, consumed:int, refunded:int, cpa_refunded:int, balance:int} $kpi */
/** @var array{requested:int, visited:int} $call */
/** @var list<string> $labels */
/** @var array<int, int> $charged */
/** @var array<int, int> $consumed */
/** @var list<array<string, mixed>> $campaigns */
/** @var array<string, mixed>|null $aiRevenue */
/** @var array<string, mixed>|null $aiConsumption */

$kpiCards = [
    ['label' => '충전 합계', 'value' => $kpi['charged'],  'unit' => '원', 'color' => '#0F6E56'],
    ['label' => '소진 합계', 'value' => $kpi['consumed'], 'unit' => '원', 'color' => '#1D9E75'],
    ['label' => '환불 합계', 'value' => $kpi['refunded'], 'unit' => '원', 'color' => '#ef4444'],
    ['label' => 'CPA 환불', 'value' => $kpi['cpa_refunded'], 'unit' => '원', 'color' => '#f59e0b'],
    ['label' => '잔액',     'value' => $kpi['balance'],  'unit' => '원', 'color' => '#6366f1'],
    ['label' => '신청 건수', 'value' => $call['requested'], 'unit' => '건', 'color' => 'var(--color-text)'],
    ['label' => '내원완료', 'value' => $call['visited'],    'unit' => '건', 'color' => '#0ea5e9'],
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

<!-- AI 일일 보고서 (이슈 #65) — 자기 병원 -->
<?= view('reports/_ai_section', [
    'aiRevenue'     => $aiRevenue,
    'aiConsumption' => $aiConsumption,
    'aiBasePath'    => '/portal/reports',
    'aiCanGenerate' => false,
]) ?>

<?php if (!$hasHospital): ?>
    <div class="alert alert-danger">
        <span class="alert-icon">!</span>
        <div class="alert-body">연결된 광고주(병원) 정보가 없어 리포트를 표시할 수 없습니다. 담당 광고대행사 또는 운영팀에 문의해주세요.</div>
    </div>
<?php else: ?>

    <!-- KPI 카드 -->
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

    <!-- 월별 충전/소진 차트 -->
    <div class="card" style="margin-bottom:20px;">
        <div class="card-body">
            <h3 style="margin:0 0 16px;font-size:15px;"><?= (int) $year ?>년 월별 충전/소진</h3>
            <canvas id="revenueChart" height="90"></canvas>
        </div>
    </div>

    <!-- 캠페인별 통계 -->
    <div class="card">
        <div class="card-body">
            <h3 style="margin:0 0 12px;font-size:15px;">캠페인별 신청·내원완료</h3>
            <p class="text-sm" style="color:var(--color-text-muted);margin:0 0 12px;">
                캠페인 <strong><?= number_format(count($campaigns)) ?></strong>개
            </p>
            <div id="campaignStatsGrid" style="height:420px;" class="ag-theme-alpine"></div>
        </div>
    </div>

    <script>
    const labels   = <?= json_encode($labels, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
    const charged  = <?= json_encode(array_map('intval', $charged)) ?>;
    const consumed = <?= json_encode(array_map('intval', $consumed)) ?>;

    new Chart(document.getElementById('revenueChart'), {
        type: 'bar',
        data: {
            labels,
            datasets: [
                { label: '충전', data: charged,  backgroundColor: '#0F6E56' },
                { label: '소진', data: consumed, backgroundColor: '#1D9E75' },
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

    const rowData = <?= json_encode($campaigns, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;

    function escHtml(s) {
        return String(s ?? '').replace(/[&<>"']/g, (c) => ({
            '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;',
        }[c]));
    }

    agGrid.createGrid(document.getElementById('campaignStatsGrid'), {
        columnDefs: [
            { field: 'ad_title', headerName: '캠페인', flex: 2, cellRenderer: (p) => escHtml(p.value) },
            { field: 'request_count', headerName: '신청 건수', width: 120, type: 'numericColumn', valueFormatter: (p) => Number(p.value || 0).toLocaleString() },
            { field: 'visited_count', headerName: '내원완료', width: 110, type: 'numericColumn', valueFormatter: (p) => Number(p.value || 0).toLocaleString() },
            {
                headerName: '전환율', width: 100, type: 'numericColumn',
                valueGetter: (p) => {
                    const req = Number(p.data.request_count || 0);
                    const vis = Number(p.data.visited_count || 0);
                    return req > 0 ? Math.round((vis / req) * 1000) / 10 : 0;
                },
                valueFormatter: (p) => p.value + '%',
            },
            { field: 'total_cost', headerName: 'CPA 합계', width: 140, type: 'numericColumn', valueFormatter: (p) => Number(p.value || 0).toLocaleString() + '원' },
        ],
        rowData,
        pagination: true,
        paginationPageSize: 20,
        defaultColDef: { resizable: true, sortable: true },
        localeText: { noRowsToShow: '해당 연도의 신청 내역이 없습니다.' },
    });
    </script>

<?php endif; ?>
