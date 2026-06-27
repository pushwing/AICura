<?php
/**
 * AI 일일 보고서 카드 섹션 (이슈 #65) — admin·portal 공용 파셜
 *
 * @var array<string, mixed>|null $aiRevenue     매출 보고서 최신 1건
 * @var array<string, mixed>|null $aiConsumption 소진 보고서 최신 1건
 * @var string $aiBasePath   링크 베이스 (예: '/admin/reports', '/portal/reports')
 * @var bool   $aiCanGenerate 수동 생성 버튼 노출 여부 (포털은 false)
 */
$aiBasePath    = $aiBasePath ?? '/admin/reports';
$aiCanGenerate = $aiCanGenerate ?? false;

$aiPreview = static function (string $content, int $limit = 160): string {
    $plain = preg_replace('/[#>*`\-\|\r\n]+/u', ' ', $content) ?? '';
    $plain = trim((string) preg_replace('/\s+/u', ' ', $plain));
    return mb_strlen($plain) > $limit ? mb_substr($plain, 0, $limit) . '…' : $plain;
};

$aiCards = [
    ['label' => '매출 현황 보고서', 'type' => 'revenue',     'report' => $aiRevenue ?? null],
    ['label' => '소진 보고서',     'type' => 'consumption', 'report' => $aiConsumption ?? null],
];
?>
<div class="card" style="margin-bottom:20px;">
    <div class="card-body">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px;">
            <h3 style="margin:0;font-size:15px;">🤖 AI 일일 보고서</h3>
            <?php if ($aiCanGenerate): ?>
                <form method="POST" action="<?= esc($aiBasePath, 'attr') ?>/ai/generate" style="margin:0;"
                      onsubmit="this.querySelector('button').disabled=true;this.querySelector('button').textContent='생성 중…';">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-primary btn-sm">지금 생성</button>
                </form>
            <?php endif; ?>
        </div>

        <div style="display:grid;grid-template-columns:repeat(2, 1fr);gap:16px;">
            <?php foreach ($aiCards as $ai): ?>
                <div style="border:1px solid var(--color-border);border-radius:var(--radius-sm);padding:16px;">
                    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px;">
                        <strong style="font-size:14px;"><?= esc($ai['label']) ?></strong>
                        <a href="<?= esc($aiBasePath, 'attr') ?>/ai-list/<?= esc($ai['type'], 'url') ?>"
                           class="text-xs" style="color:var(--color-text-muted);">더보기 →</a>
                    </div>
                    <?php if ($ai['report'] !== null): ?>
                        <p class="text-xs" style="color:var(--color-text-muted);margin:0 0 6px;">
                            <?= esc($ai['report']['report_date']) ?> 기준
                        </p>
                        <p style="font-size:13px;line-height:1.6;margin:0 0 12px;color:var(--color-text);">
                            <?= esc($aiPreview((string) $ai['report']['content'])) ?>
                        </p>
                        <a href="<?= esc($aiBasePath, 'attr') ?>/ai/<?= (int) $ai['report']['id'] ?>" target="_blank"
                           class="btn btn-outline btn-sm">전체 보기 ↗</a>
                    <?php else: ?>
                        <p style="font-size:13px;color:var(--color-text-muted);margin:8px 0 0;">
                            아직 생성된 보고서가 없습니다.
                        </p>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
