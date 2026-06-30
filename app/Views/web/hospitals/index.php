<?php
/**
 * 공개 병원 목록 (이슈 #144)
 *
 * @var array<int, array<string, mixed>> $hospitals
 * @var int                              $total
 */
?>
<section class="web-section">
    <h1>성형·시술 병원</h1>
    <p class="web-sub">등록 병원 <?= esc((string) $total) ?>곳</p>

    <?php if ($hospitals === []): ?>
        <p class="web-empty">등록된 병원이 없습니다.</p>
    <?php else: ?>
        <ul class="web-card-grid">
            <?php foreach ($hospitals as $hospital): ?>
                <li class="web-card">
                    <a href="<?= base_url('hospitals/' . (int) $hospital['id']) ?>">
                        <h2><?= esc((string) $hospital['name']) ?></h2>
                        <?php if (!empty($hospital['type_label'])): ?>
                            <p class="web-card-meta"><?= esc((string) $hospital['type_label']) ?></p>
                        <?php endif; ?>
                        <?php if (!empty($hospital['address'])): ?>
                            <p class="web-card-meta"><?= esc((string) $hospital['address']) ?></p>
                        <?php endif; ?>
                        <?php if ((float) ($hospital['rating'] ?? 0) > 0): ?>
                            <p class="web-card-meta">평점 <?= esc((string) $hospital['rating']) ?></p>
                        <?php endif; ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</section>
