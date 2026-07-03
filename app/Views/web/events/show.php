<?php
/**
 * 공개 이벤트 상세 (이슈 #137)
 *
 * @var array<string, mixed> $event
 */
// ad_detail_info 는 어드민/대행사 작성 리치 HTML.
// clean_html 이 허용 태그 + 허용 속성만 남기고 href/src 스킴까지 검증한다(이슈 #187 저장형 XSS 방지).
$detailHtml = clean_html(
    (string) ($event['ad_detail_info'] ?? ''),
    ['p', 'br', 'strong', 'em', 'b', 'i', 'ul', 'ol', 'li', 'h2', 'h3', 'h4', 'img'],
);
?>
<article class="web-detail">
    <nav class="web-breadcrumb">
        <a href="<?= base_url('events') ?>">이벤트</a> ›
        <span><?= esc((string) $event['ad_title']) ?></span>
    </nav>

    <h1><?= esc((string) $event['ad_title']) ?></h1>

    <dl class="web-detail-meta">
        <?php if (!empty($event['hospital_name'])): ?>
            <dt>병원</dt>
            <dd>
                <a href="<?= base_url('hospitals/' . (int) ($event['hospital_id'] ?? 0)) ?>">
                    <?= esc((string) $event['hospital_name']) ?>
                </a>
            </dd>
        <?php endif; ?>
        <?php if (!empty($event['region'])): ?>
            <dt>지역</dt>
            <dd><?= esc((string) $event['region']) ?></dd>
        <?php endif; ?>
        <?php if ((int) $event['discount_cost'] > 0): ?>
            <dt>이벤트가</dt>
            <dd><?= esc(number_format((int) $event['discount_cost'])) ?>원</dd>
        <?php endif; ?>
        <?php if (!empty($event['ad_start_date']) && !empty($event['ad_end_date'])): ?>
            <dt>기간</dt>
            <dd><?= esc((string) $event['ad_start_date']) ?> ~ <?= esc((string) $event['ad_end_date']) ?></dd>
        <?php endif; ?>
    </dl>

    <p class="web-inline-links">
        <a href="<?= base_url('reviews?filter[type]=1&filter[target_id]=' . (int) $event['id']) ?>">이 이벤트 후기 보기</a>
    </p>

    <?php if (!empty($event['thumbnail_url'])): ?>
        <img class="web-detail-hero" src="<?= esc((string) $event['thumbnail_url'], 'attr') ?>"
             alt="<?= esc((string) $event['ad_title'], 'attr') ?>">
    <?php endif; ?>

    <?php if ($detailHtml !== ''): ?>
        <div class="web-detail-body"><?= $detailHtml ?></div>
    <?php endif; ?>

    <?php foreach (($event['detail_images'] ?? []) as $img): ?>
        <img class="web-detail-image" src="<?= esc((string) $img, 'attr') ?>" alt="" loading="lazy">
    <?php endforeach; ?>
</article>
