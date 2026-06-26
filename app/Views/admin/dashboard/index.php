<?php
/** @var int    $totalCampaigns */
/** @var int    $activeCampaigns */
/** @var int    $monthlyContractCount */
/** @var int    $monthlyContractAmount */
/** @var int    $monthlyRevenue */
/** @var int    $totalBalance */
/** @var array<int, string> $chartLabels */
/** @var array<int, int>    $chartContractCounts */
/** @var array<int, int>    $chartContractAmounts */
/** @var array<int, int>    $chartRevenues */
?>
<div class="page-header">
    <h1 class="page-title">대시보드</h1>
    <span class="text-sm" style="color:var(--color-text-muted)"><?= date('Y년 n월 j일') ?></span>
</div>

<!-- KPI 카드 -->
<div class="grid grid-cols-5 gap-4" style="display:grid;grid-template-columns:repeat(5,1fr);gap:16px;margin-bottom:28px;">

    <div class="card">
        <div class="card-body">
            <p class="text-sm" style="color:var(--color-text-muted);margin-bottom:6px;">총 광고수</p>
            <p class="text-3xl font-bold" style="font-size:2rem;font-weight:700;color:var(--color-text)">
                <?= number_format($totalCampaigns) ?>
            </p>
            <p class="text-xs" style="color:var(--color-text-muted);margin-top:4px;">전체 등록 광고</p>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <p class="text-sm" style="color:var(--color-text-muted);margin-bottom:6px;">진행중 광고</p>
            <p class="text-3xl font-bold" style="font-size:2rem;font-weight:700;color:#1D9E75;">
                <?= number_format($activeCampaigns) ?>
            </p>
            <p class="text-xs" style="color:var(--color-text-muted);margin-top:4px;">status = active</p>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <p class="text-sm" style="color:var(--color-text-muted);margin-bottom:6px;">이달 신규 계약</p>
            <p class="text-3xl font-bold" style="font-size:2rem;font-weight:700;color:var(--color-text)">
                <?= number_format($monthlyContractCount) ?>건
            </p>
            <p class="text-xs" style="color:var(--color-text-muted);margin-top:4px;">
                <?= number_format($monthlyContractAmount) ?>원
            </p>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <p class="text-sm" style="color:var(--color-text-muted);margin-bottom:6px;">이달 매출</p>
            <p class="text-3xl font-bold" style="font-size:2rem;font-weight:700;color:#0F6E56;">
                <?= number_format($monthlyRevenue) ?>원
            </p>
            <p class="text-xs" style="color:var(--color-text-muted);margin-top:4px;">입금 확인 기준</p>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <p class="text-sm" style="color:var(--color-text-muted);margin-bottom:6px;">전체 잔액</p>
            <p class="text-3xl font-bold" style="font-size:2rem;font-weight:700;color:#0F6E56;">
                <?= number_format($totalBalance) ?>원
            </p>
            <p class="text-xs" style="color:var(--color-text-muted);margin-top:4px;">충전 − 소진(차감)</p>
        </div>
    </div>

</div>

<!-- 월별 추이 차트 -->
<div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">

    <div class="card">
        <div class="card-body">
            <h2 class="text-base font-semibold" style="margin-bottom:16px;">월별 신규 계약 건수</h2>
            <canvas id="chartContracts" style="max-height:260px;"></canvas>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <h2 class="text-base font-semibold" style="margin-bottom:16px;">월별 매출 (입금 확인)</h2>
            <canvas id="chartRevenue" style="max-height:260px;"></canvas>
        </div>
    </div>

</div>

<script>
(function () {
    const labels  = <?= json_encode($chartLabels) ?>;
    const counts  = <?= json_encode($chartContractCounts) ?>;
    const amounts = <?= json_encode($chartContractAmounts) ?>;
    const revenues = <?= json_encode($chartRevenues) ?>;

    const commonOptions = {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: {
            y: {
                beginAtZero: true,
                ticks: { color: '#6B7280' },
                grid:  { color: 'rgba(0,0,0,0.06)' },
            },
            x: {
                ticks: { color: '#6B7280' },
                grid:  { display: false },
            },
        },
    };

    // 계약 건수 차트
    new Chart(document.getElementById('chartContracts'), {
        type: 'bar',
        data: {
            labels,
            datasets: [{
                label: '계약 건수',
                data: counts,
                backgroundColor: '#1D9E75',
                borderRadius: 4,
            }],
        },
        options: {
            ...commonOptions,
            scales: {
                ...commonOptions.scales,
                y: { ...commonOptions.scales.y, ticks: { ...commonOptions.scales.y.ticks, stepSize: 1 } },
            },
        },
    });

    // 매출 차트
    new Chart(document.getElementById('chartRevenue'), {
        type: 'line',
        data: {
            labels,
            datasets: [{
                label: '매출',
                data: revenues,
                borderColor: '#0F6E56',
                backgroundColor: 'rgba(15,110,86,0.08)',
                pointBackgroundColor: '#0F6E56',
                tension: 0.3,
                fill: true,
            }],
        },
        options: {
            ...commonOptions,
            scales: {
                ...commonOptions.scales,
                y: {
                    ...commonOptions.scales.y,
                    ticks: {
                        ...commonOptions.scales.y.ticks,
                        callback: (v) => (v / 10000).toLocaleString() + '만',
                    },
                },
            },
        },
    });
})();
</script>
