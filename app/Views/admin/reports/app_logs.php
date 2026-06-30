<?php
/**
 * 앱 액션 로그 통계 (이슈 #120)
 *
 * @var string                                                       $mode     'hourly' | 'daily'
 * @var string                                                       $date     기준 날짜 (YYYY-MM-DD)
 * @var list<string>                                                 $labels   x축 라벨 (0~23시 또는 날짜들)
 * @var list<array{label: string, event: string, data: list<int>}>  $datasets 이벤트별 시리즈
 * @var array<string, int>                                           $totals   이벤트별 합계
 */

// 이벤트 시리즈용 브랜드 팔레트 (Primary/Secondary 우선 → 보조색)
$palette = ['#0F6E56', '#1D9E75', '#6366f1', '#f59e0b', '#ef4444', '#0ea5e9', '#a855f7', '#14b8a6'];
$grandTotal = array_sum($totals);
?>
<div class="page-header" style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;">
    <h1 class="page-title">앱 로그 통계</h1>
    <div style="display:flex;gap:8px;">
        <a href="/admin/reports" class="btn btn-outline btn-sm">매출 리포트 →</a>
    </div>
</div>

<!-- 모드 토글 + 날짜 선택 -->
<form method="GET" action="/admin/reports/app-logs" style="display:flex;gap:8px;align-items:center;margin-bottom:16px;flex-wrap:wrap;">
    <div class="btn-group" role="group" style="display:inline-flex;gap:4px;">
        <a href="/admin/reports/app-logs?mode=hourly&date=<?= esc($date, 'attr') ?>"
           class="btn btn-sm <?= $mode === 'hourly' ? 'btn-primary' : 'btn-outline' ?>">시간별</a>
        <a href="/admin/reports/app-logs?mode=daily&date=<?= esc($date, 'attr') ?>"
           class="btn btn-sm <?= $mode === 'daily' ? 'btn-primary' : 'btn-outline' ?>">일별</a>
    </div>
    <input type="hidden" name="mode" value="<?= esc($mode, 'attr') ?>">
    <input type="date" name="date" value="<?= esc($date, 'attr') ?>" class="form-control" style="width:170px;"
           onchange="this.form.submit()">
    <span class="text-muted" style="font-size:13px;">
        <?= $mode === 'hourly' ? esc($date) . ' 시간별 추이 (직전 1시간까지 반영)' : '최근 14일 일별 추이' ?>
    </span>
</form>

<!-- 추이 차트 -->
<div class="card">
    <div class="card-body">
        <h3 style="margin:0 0 16px;font-size:15px;">이벤트별 <?= $mode === 'hourly' ? '시간별' : '일별' ?> 발생 추이</h3>
        <?php if ($datasets === []): ?>
            <p class="text-muted" style="margin:24px 0;text-align:center;">집계된 로그가 없습니다. (배치: <code>php spark logs:aggregate</code>)</p>
        <?php else: ?>
            <canvas id="appLogChart" height="90"></canvas>
        <?php endif; ?>
    </div>
</div>

<?php if ($datasets !== []): ?>
<!-- 이벤트별 합계 -->
<div class="card" style="margin-top:16px;">
    <div class="card-body">
        <h3 style="margin:0 0 16px;font-size:15px;">이벤트별 합계 <span class="text-muted" style="font-weight:400;">(총 <?= number_format($grandTotal) ?>건)</span></h3>
        <table class="table" style="width:100%;">
            <thead>
                <tr>
                    <th style="text-align:left;">이벤트</th>
                    <th style="text-align:right;">발생 수</th>
                    <th style="text-align:right;">비중</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($datasets as $i => $ds): ?>
                    <?php $count = $totals[$ds['event']] ?? 0; ?>
                    <tr>
                        <td>
                            <span style="display:inline-block;width:10px;height:10px;border-radius:2px;background:<?= $palette[$i % count($palette)] ?>;margin-right:8px;"></span>
                            <?= esc($ds['label']) ?>
                            <span class="text-muted" style="font-size:12px;">(<?= esc($ds['event']) ?>)</span>
                        </td>
                        <td style="text-align:right;"><?= number_format($count) ?></td>
                        <td style="text-align:right;"><?= $grandTotal > 0 ? round($count / $grandTotal * 100, 1) : 0 ?>%</td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
const labels  = <?= json_encode($labels, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
const palette = <?= json_encode($palette) ?>;
const series  = <?= json_encode($datasets, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;

new Chart(document.getElementById('appLogChart'), {
    type: 'line',
    data: {
        labels,
        datasets: series.map((s, i) => ({
            label: s.label,
            data: s.data,
            borderColor: palette[i % palette.length],
            backgroundColor: palette[i % palette.length],
            tension: 0.3,
            fill: false,
            pointRadius: 2,
        })),
    },
    options: {
        responsive: true,
        interaction: { mode: 'index', intersect: false },
        scales: {
            y: {
                beginAtZero: true,
                ticks: { precision: 0, callback: (v) => Number(v).toLocaleString() },
            },
        },
        plugins: {
            tooltip: {
                callbacks: {
                    label: (ctx) => `${ctx.dataset.label}: ${Number(ctx.parsed.y).toLocaleString()}건`,
                },
            },
        },
    },
});
</script>
<?php endif; ?>
