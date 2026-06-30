<?php
/**
 * 공개 후기 목록 (이슈 #144)
 *
 * @var array<int, array<string, mixed>> $reviews 작성자명은 마스킹된 author 키 포함
 * @var int                              $total
 */
?>
<section class="web-section">
    <h1>실사용 후기</h1>
    <p class="web-sub">후기 <?= esc((string) $total) ?>건</p>

    <?php if ($reviews === []): ?>
        <p class="web-empty">등록된 후기가 없습니다.</p>
    <?php else: ?>
        <ul class="web-list">
            <?php foreach ($reviews as $review): ?>
                <li class="web-list-item">
                    <a href="<?= base_url('reviews/' . (int) $review['id']) ?>">
                        <h2><?= esc((string) $review['subject']) ?></h2>
                    </a>
                    <p class="web-card-meta">
                        <?= esc((string) ($review['author'] ?? '익명')) ?>
                        <?php if ((float) ($review['rating'] ?? 0) > 0): ?>
                            · 평점 <?= esc((string) $review['rating']) ?>
                        <?php endif; ?>
                        <?php if (!empty($review['type_label'])): ?>
                            · <?= esc((string) $review['type_label']) ?>
                        <?php endif; ?>
                    </p>
                    <?php if (!empty($review['excerpt'])): ?>
                        <p class="web-excerpt"><?= esc((string) $review['excerpt']) ?></p>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</section>
