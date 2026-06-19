<?php
/** @var int                $year */
/** @var array<int, int>    $years */
/** @var array<string, int> $kpi */
/** @var array<int, string> $chartLabels */
/** @var array<int, int>    $chartAmounts */
?>
<div class="page-header" style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;">
    <h1 class="page-title">매출 리포트</h1>
    <div style="display:flex;gap:8px;align-items:center;">
        <a href="/admin/reports/campaigns" class="btn btn-outline btn-sm">캠페인별 소진</a>
        <form method="GET" action="/admin/reports" style="display:flex;gap:6px;align-items:center;">
            <select name="year" class="form-control" style="width:100px;" onchange="this.form.submit()">
                <?php foreach ($years as $y): ?>
                    <option value="<?= esc((string) $y) ?>" <?= $year === $y ? 'selected' : '' ?>>
                        <?= esc((string) $y) ?>년
                    </option>
                <?php endforeach; ?>
            </select>
        </form>
    </div>
</div>

<!-- KPI 카드 -->
<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:28px;">

    <div class="card">
        <div class="card-body">
            <p class="text-sm" style="color:var(--color-text-muted);margin-bottom:6px;">계약 충전액</p>
            <p style="font-size:1.6rem;font-weight:700;color:var(--color-text);">
                <?= number_format($kpi['charged']) ?>원
            </p>
            <p class="text-xs" style="color:var(--color-text-muted);margin-top:4px;">입금 확인 기준</p>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <p class="text-sm" style="color:var(--color-text-muted);margin-bottom:6px;">DB 소진액</p>
            <p style="font-size:1.6rem;font-weight:700;color:#1D9E75;">
                <?= number_format($kpi['consumed']) ?>원
            </p>
            <p class="text-xs" style="color:var(--color-text-muted);margin-top:4px;">광고 집행 기준</p>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <p class="text-sm" style="color:var(--color-text-muted);margin-bottom:6px;">환불액</p>
            <p style="font-size:1.6rem;font-weight:700;color:#EF4444;">
                <?= number_format($kpi['refunded']) ?>원
            </p>
            <p class="text-xs" style="color:var(--color-text-muted);margin-top:4px;">발행·계약 환불 합산</p>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <p class="text-sm" style="color:var(--color-text-muted);margin-bottom:6px;">잔액</p>
            <p style="font-size:1.6rem;font-weight:700;color:#0F6E56;">
                <?= number_format($kpi['balance']) ?>원
            </p>
            <p class="text-xs" style="color:var(--color-text-muted);margin-top:4px;">충전 − 소진 − 환불</p>
        </div>
    </div>

</div>

<!-- 월별 계약충전 바 차트 -->
<div class="card">
    <div class="card-body">
        <h2 class="text-base font-semibold" style="margin-bottom:16px;">
            <?= esc((string) $year) ?>년 월별 계약충전 (입금 확인 기준)
        </h2>
        <canvas id="chartRevenue" style="max-height:320px;"></canvas>
    </div>
</div>

<script>
(function () {
    const labels  = <?= json_encode($chartLabels, JSON_UNESCAPED_UNICODE) ?>;
    const amounts = <?= json_encode($chartAmounts) ?>;

    new Chart(document.getElementById('chartRevenue'), {
        type: 'bar',
        data: {
            labels,
            datasets: [{
                label: '계약충전액',
                data: amounts,
                backgroundColor: '#1D9E75',
                borderColor: '#0F6E56',
                borderWidth: 1,
                borderRadius: 4,
            }],
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: (ctx) => ' ' + Number(ctx.raw).toLocaleString() + '원',
                    },
                },
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        color: '#6B7280',
                        callback: (v) => (Number(v) / 10000).toLocaleString() + '만',
                    },
                    grid: { color: 'rgba(0,0,0,0.06)' },
                },
                x: {
                    ticks: { color: '#6B7280' },
                    grid: { display: false },
                },
            },
        },
    });
})();
</script>
