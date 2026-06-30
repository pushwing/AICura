<?php
/**
 * 공개 후기 상세 (이슈 #144)
 *
 * @var array<string, mixed> $review 작성자·댓글 작성자는 마스킹된 author 키 포함
 */
?>
<article class="web-detail">
    <nav class="web-breadcrumb">
        <a href="<?= base_url('reviews') ?>">후기</a> ›
        <span><?= esc((string) $review['subject']) ?></span>
    </nav>

    <h1><?= esc((string) $review['subject']) ?></h1>

    <dl class="web-detail-meta">
        <dt>작성자</dt>
        <dd><?= esc((string) ($review['author'] ?? '익명')) ?></dd>
        <?php if ((float) ($review['rating'] ?? 0) > 0): ?>
            <dt>평점</dt>
            <dd><?= esc((string) $review['rating']) ?></dd>
        <?php endif; ?>
        <?php if (!empty($review['created_at'])): ?>
            <dt>작성일</dt>
            <dd><?= esc((string) $review['created_at']) ?></dd>
        <?php endif; ?>
    </dl>

    <div class="web-detail-body"><?= esc((string) ($review['contents'] ?? '')) ?></div>

    <?php foreach (($review['images'] ?? []) as $img): ?>
        <img class="web-detail-image" src="<?= esc((string) $img, 'attr') ?>" alt="" loading="lazy">
    <?php endforeach; ?>

    <?php if (!empty($review['comments'])): ?>
        <section class="web-comments">
            <h2>댓글 <?= esc((string) count($review['comments'])) ?></h2>
            <ul class="web-list">
                <?php foreach ($review['comments'] as $comment): ?>
                    <li class="web-list-item">
                        <p class="web-card-meta"><?= esc((string) ($comment['author'] ?? '익명')) ?></p>
                        <p><?= esc((string) ($comment['contents'] ?? '')) ?></p>
                    </li>
                <?php endforeach; ?>
            </ul>
        </section>
    <?php endif; ?>
</article>
