<?php
/**
 * 공개 이벤트 목록 (이슈 #137)
 *
 * @var array<int, array<string, mixed>> $events
 * @var int                              $total
 */
?>
<section class="web-section">
    <h1>성형·시술 이벤트</h1>
    <p class="web-sub">진행 중인 이벤트 <?= esc((string) $total) ?>건</p>

    <?php if ($events === []): ?>
        <p class="web-empty">진행 중인 이벤트가 없습니다.</p>
    <?php else: ?>
        <ul class="web-card-grid">
            <?php foreach ($events as $event): ?>
                <li class="web-card">
                    <a href="<?= base_url('events/' . (int) $event['id']) ?>">
                        <?php if (!empty($event['thumbnail_url'])): ?>
                            <img src="<?= esc((string) $event['thumbnail_url'], 'attr') ?>"
                                 alt="<?= esc((string) $event['ad_title'], 'attr') ?>" loading="lazy">
                        <?php endif; ?>
                        <h2><?= esc((string) $event['ad_title']) ?></h2>
                        <p class="web-card-meta">
                            <?= esc((string) ($event['hospital_name'] ?? '')) ?>
                            <?php if (!empty($event['region'])): ?>
                                · <?= esc((string) $event['region']) ?>
                            <?php endif; ?>
                        </p>
                        <?php if ((int) $event['discount_cost'] > 0): ?>
                            <p class="web-card-price"><?= esc(number_format((int) $event['discount_cost'])) ?>원</p>
                        <?php endif; ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</section>
