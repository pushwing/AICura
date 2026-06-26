<?php
/**
 * AI 보고서 상세 — 새창 standalone 페이지 (이슈 #65)
 *
 * @var array<string, mixed> $report
 *
 * 본문(content)은 AI가 생성한 마크다운. 클라이언트에서 marked로 파싱하고
 * DOMPurify로 새니타이즈한 뒤 렌더한다(XSS 방어).
 */
$typeLabel = ($report['type'] ?? '') === 'consumption' ? '소진 보고서' : '매출 현황 보고서';
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($report['title']) ?> | AICura</title>
    <link rel="icon" href="<?= base_url('favicon.ico') ?>">
    <link href="https://cdn.jsdelivr.net/gh/orioncactus/pretendard@v1.3.9/dist/web/static/pretendard.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= base_url('assets/css/aicura.css') ?>">
    <script src="https://cdn.jsdelivr.net/npm/marked@12/marked.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/dompurify@3/dist/purify.min.js"></script>
    <style>
        body { font-family: 'Pretendard', sans-serif; background: #f7f8fa; margin: 0; padding: 32px 16px; color: #1a1a1a; }
        .report-wrap { max-width: 820px; margin: 0 auto; background: #fff; border-radius: 12px; padding: 40px 48px; box-shadow: 0 1px 4px rgba(0,0,0,0.06); }
        .report-head { border-bottom: 2px solid #0F6E56; padding-bottom: 16px; margin-bottom: 24px; }
        .report-badge { display: inline-block; font-size: 12px; font-weight: 600; color: #0F6E56; background: #e6f4ef; padding: 3px 10px; border-radius: 999px; margin-bottom: 10px; }
        .report-head h1 { font-size: 22px; margin: 0 0 6px; }
        .report-head .meta { font-size: 13px; color: #888; }
        .report-body { font-size: 15px; line-height: 1.75; }
        .report-body h2 { font-size: 18px; margin: 28px 0 12px; padding-bottom: 6px; border-bottom: 1px solid #eee; }
        .report-body h3 { font-size: 16px; margin: 20px 0 10px; }
        .report-body table { width: 100%; border-collapse: collapse; margin: 14px 0; font-size: 14px; }
        .report-body th, .report-body td { border: 1px solid #e3e6ea; padding: 8px 12px; text-align: left; }
        .report-body th { background: #f2f5f7; font-weight: 600; }
        .report-body code { background: #f2f5f7; padding: 2px 5px; border-radius: 4px; font-size: 13px; }
        .report-actions { max-width: 820px; margin: 16px auto 0; text-align: right; }
        .report-actions button { font-family: inherit; font-size: 13px; padding: 8px 16px; border: 1px solid #ccc; background: #fff; border-radius: 6px; cursor: pointer; }
        @media print { .report-actions { display: none; } body { background: #fff; padding: 0; } .report-wrap { box-shadow: none; } }
    </style>
</head>
<body>
    <div class="report-wrap">
        <div class="report-head">
            <span class="report-badge"><?= esc($typeLabel) ?></span>
            <h1><?= esc($report['title']) ?></h1>
            <p class="meta"><?= esc($report['report_date']) ?> 기준 · 생성 <?= esc($report['created_at']) ?></p>
        </div>
        <div class="report-body" id="reportBody"></div>
    </div>
    <div class="report-actions">
        <button type="button" onclick="window.print()">인쇄 / PDF 저장</button>
    </div>

    <script>
        (function () {
            // JSON_HEX_TAG로 </script> 브레이크아웃 차단, marked 파싱 후 DOMPurify로 새니타이즈
            const raw = <?= json_encode((string) $report['content'], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE) ?>;
            const html = DOMPurify.sanitize(marked.parse(raw));
            document.getElementById('reportBody').innerHTML = html;
        })();
    </script>
</body>
</html>
