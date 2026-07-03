<?php
/**
 * 공개 가이드 상세 (이슈 #146)
 *
 * @var array<string, mixed> $guide faq 키에 [{q,a}] 포함
 */
// 본문은 어드민 작성 리치 HTML.
// clean_html 이 허용 태그 + 허용 속성만 남기고 href 스킴까지 검증한다(이슈 #187 저장형 XSS 방지).
$bodyHtml = clean_html(
    (string) ($guide['content'] ?? ''),
    ['p', 'br', 'strong', 'em', 'b', 'i', 'ul', 'ol', 'li', 'h2', 'h3', 'h4', 'blockquote', 'a'],
);

/** @var array<int, array{q: string, a: string}> $faq */
$faq = $guide['faq'] ?? [];
?>
<article class="web-detail">
    <nav class="web-breadcrumb">
        <a href="<?= base_url('guides') ?>">가이드</a> ›
        <span><?= esc((string) $guide['title']) ?></span>
    </nav>

    <h1><?= esc((string) $guide['title']) ?></h1>

    <?php if (!empty($guide['published_at'])): ?>
        <p class="web-card-meta">발행일 <?= esc((string) $guide['published_at']) ?></p>
    <?php endif; ?>

    <?php if (!empty($guide['summary'])): ?>
        <p class="web-lead"><?= esc((string) $guide['summary']) ?></p>
    <?php endif; ?>

    <?php if ($bodyHtml !== ''): ?>
        <div class="web-detail-body"><?= $bodyHtml ?></div>
    <?php endif; ?>

    <?php if ($faq !== []): ?>
        <section class="web-faq">
            <h2>자주 묻는 질문</h2>
            <?php foreach ($faq as $item): ?>
                <details class="web-faq-item">
                    <summary><?= esc((string) $item['q']) ?></summary>
                    <p><?= esc((string) $item['a']) ?></p>
                </details>
            <?php endforeach; ?>
        </section>
    <?php endif; ?>
</article>
