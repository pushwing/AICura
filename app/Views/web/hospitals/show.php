<?php
/**
 * 공개 병원 상세 (이슈 #144)
 *
 * @var array<string, mixed>             $hospital
 * @var array<int, array<string, mixed>> $events
 */
$summary = $hospital['review_summary'] ?? [];
?>
<article class="web-detail">
    <nav class="web-breadcrumb">
        <a href="<?= base_url('hospitals') ?>">병원</a> ›
        <span><?= esc((string) $hospital['name']) ?></span>
    </nav>

    <h1><?= esc((string) $hospital['name']) ?></h1>

    <dl class="web-detail-meta">
        <?php if (!empty($hospital['type_label'])): ?>
            <dt>유형</dt>
            <dd><?= esc((string) $hospital['type_label']) ?></dd>
        <?php endif; ?>
        <?php if (!empty($hospital['address'])): ?>
            <dt>주소</dt>
            <dd><?= esc((string) $hospital['address']) ?></dd>
        <?php endif; ?>
        <?php if (!empty($hospital['phone'])): ?>
            <dt>전화</dt>
            <dd><?= esc((string) $hospital['phone']) ?></dd>
        <?php endif; ?>
        <?php if (!empty($hospital['departments'])): ?>
            <dt>진료과</dt>
            <dd><?= esc(implode(', ', array_map('strval', (array) $hospital['departments']))) ?></dd>
        <?php endif; ?>
        <?php if ((float) ($summary['rating'] ?? 0) > 0): ?>
            <dt>평점</dt>
            <dd><?= esc((string) $summary['rating']) ?> (<?= esc((string) ($summary['count'] ?? 0)) ?>건)</dd>
        <?php endif; ?>
    </dl>

    <?php if ($events !== []): ?>
        <h2>진행 중인 이벤트</h2>
        <ul class="web-card-grid">
            <?php foreach ($events as $event): ?>
                <li class="web-card">
                    <a href="<?= base_url('events/' . (int) $event['id']) ?>">
                        <h3><?= esc((string) $event['ad_title']) ?></h3>
                        <?php if ((int) ($event['discount_cost'] ?? 0) > 0): ?>
                            <p class="web-card-price"><?= esc(number_format((int) $event['discount_cost'])) ?>원</p>
                        <?php endif; ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</article>
