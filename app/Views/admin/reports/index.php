<?php
/** @var int $year */
/** @var array<int, int> $years */
/** @var array{charged: int, consumed: int, refunded: int, balance: int} $kpi */
/** @var array<int, string> $labels */
/** @var array<int, int> $charged */
/** @var array<int, int> $consumed */

$kpiCards = [
    ['label' => '충전 합계', 'value' => $kpi['charged'],  'color' => '#0F6E56'],
    ['label' => '소진 합계', 'value' => $kpi['consumed'], 'color' => '#1D9E75'],
    ['label' => '환불 합계', 'value' => $kpi['refunded'], 'color' => '#ef4444'],
    ['label' => '잔액',     'value' => $kpi['balance'],  'color' => '#6366f1'],
];
?>
<div class="page-header" style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;">
    <h1 class="page-title">매출 리포트</h1>
    <div style="display:flex;gap:8px;">
        <a href="/admin/reports/campaigns" class="btn btn-outline btn-sm">캠페인 리포트 →</a>
    </div>
</div>

<!-- 연도 선택 -->
<form method="GET" action="/admin/reports" style="display:flex;gap:8px;margin-bottom:16px;">
    <select name="year" class="form-control" style="width:120px;" onchange="this.form.submit()">
        <?php foreach ($years as $y): ?>
            <option value="<?= (int) $y ?>" <?= $year === $y ? 'selected' : '' ?>><?= (int) $y ?>년</option>
        <?php endforeach; ?>
    </select>
</form>

<!-- KPI 카드 -->
<div style="display:grid;grid-template-columns:repeat(4, 1fr);gap:16px;margin-bottom:20px;">
    <?php foreach ($kpiCards as $card): ?>
        <div class="card">
            <div class="card-body">
                <p style="font-size:13px;color:var(--color-text-muted);margin:0 0 8px;"><?= esc($card['label']) ?></p>
                <p style="font-size:22px;font-weight:700;margin:0;color:<?= esc($card['color']) ?>;">
                    <?= number_format($card['value']) ?><span style="font-size:14px;font-weight:400;">원</span>
                </p>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<!-- 월별 충전/소진 차트 -->
<div class="card">
    <div class="card-body">
        <h3 style="margin:0 0 16px;font-size:15px;"><?= (int) $year ?>년 월별 충전/소진</h3>
        <canvas id="revenueChart" height="90"></canvas>
    </div>
</div>

<script>
const labels   = <?= json_encode($labels, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
const charged  = <?= json_encode($charged) ?>;
const consumed = <?= json_encode($consumed) ?>;

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
        scales: {
            y: {
                beginAtZero: true,
                ticks: { callback: (v) => Number(v).toLocaleString() },
            },
        },
        plugins: {
            tooltip: {
                callbacks: {
                    label: (ctx) => `${ctx.dataset.label}: ${Number(ctx.parsed.y).toLocaleString()}원`,
                },
            },
        },
    },
});
</script>
