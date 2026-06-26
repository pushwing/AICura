<?php
/** @var int $year */
/** @var array<int, int> $years */
/** @var array{charged: int, consumed: int, refunded: int, cpa_refunded: int, balance: int} $kpi */
/** @var array<int, string> $labels */
/** @var array<int, int> $charged */
/** @var array<int, int> $consumed */
/** @var array<string, mixed>|null $aiRevenue */
/** @var array<string, mixed>|null $aiConsumption */

/**
 * AI 보고서 마크다운 본문에서 미리보기 텍스트 추출 (마크다운 기호 제거 후 절단)
 */
$aiPreview = static function (string $content, int $limit = 160): string {
    $plain = preg_replace('/[#>*`\-\|\r\n]+/u', ' ', $content) ?? '';
    $plain = trim((string) preg_replace('/\s+/u', ' ', $plain));
    return mb_strlen($plain) > $limit ? mb_substr($plain, 0, $limit) . '…' : $plain;
};

$aiCards = [
    ['label' => '매출 현황 보고서', 'type' => 'revenue',     'report' => $aiRevenue],
    ['label' => '소진 보고서',     'type' => 'consumption', 'report' => $aiConsumption],
];

$kpiCards = [
    ['label' => '충전 합계', 'value' => $kpi['charged'],      'color' => '#0F6E56'],
    ['label' => '소진 합계', 'value' => $kpi['consumed'],     'color' => '#1D9E75'],
    ['label' => '환불 합계', 'value' => $kpi['refunded'],     'color' => '#ef4444'],
    ['label' => 'CPA 환불', 'value' => $kpi['cpa_refunded'], 'color' => '#f59e0b'],
    ['label' => '잔액',     'value' => $kpi['balance'],      'color' => '#6366f1'],
];
?>
<div class="page-header" style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;">
    <h1 class="page-title">매출 리포트</h1>
    <div style="display:flex;gap:8px;">
        <a href="/admin/reports/campaigns" class="btn btn-outline btn-sm">캠페인 리포트 →</a>
    </div>
</div>

<!-- AI 일일 보고서 (이슈 #65) -->
<div class="card" style="margin-bottom:20px;">
    <div class="card-body">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px;">
            <h3 style="margin:0;font-size:15px;">🤖 AI 일일 보고서</h3>
            <form method="POST" action="/admin/reports/ai/generate" style="margin:0;"
                  onsubmit="this.querySelector('button').disabled=true;this.querySelector('button').textContent='생성 중…';">
                <?= csrf_field() ?>
                <button type="submit" class="btn btn-primary btn-sm">지금 생성</button>
            </form>
        </div>

        <div style="display:grid;grid-template-columns:repeat(2, 1fr);gap:16px;">
            <?php foreach ($aiCards as $ai): ?>
                <div style="border:1px solid var(--color-border);border-radius:var(--radius-sm);padding:16px;">
                    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px;">
                        <strong style="font-size:14px;"><?= esc($ai['label']) ?></strong>
                        <a href="/admin/reports/ai-list/<?= esc($ai['type'], 'url') ?>"
                           class="text-xs" style="color:var(--color-text-muted);">더보기 →</a>
                    </div>
                    <?php if ($ai['report'] !== null): ?>
                        <p class="text-xs" style="color:var(--color-text-muted);margin:0 0 6px;">
                            <?= esc($ai['report']['report_date']) ?> 기준
                        </p>
                        <p style="font-size:13px;line-height:1.6;margin:0 0 12px;color:var(--color-text);">
                            <?= esc($aiPreview((string) $ai['report']['content'])) ?>
                        </p>
                        <a href="/admin/reports/ai/<?= (int) $ai['report']['id'] ?>" target="_blank"
                           class="btn btn-outline btn-sm">전체 보기 ↗</a>
                    <?php else: ?>
                        <p style="font-size:13px;color:var(--color-text-muted);margin:8px 0 0;">
                            아직 생성된 보고서가 없습니다. '지금 생성'을 눌러 보고서를 만들어 보세요.
                        </p>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
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
<div style="display:grid;grid-template-columns:repeat(5, 1fr);gap:16px;margin-bottom:20px;">
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
