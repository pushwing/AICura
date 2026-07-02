<?php
/**
 * 공개 가이드 목록 (이슈 #146)
 *
 * @var array<int, array<string, mixed>> $guides
 * @var int                              $total
 */
?>
<section class="web-section">
    <h1>성형·시술 정보 가이드</h1>
    <p class="web-sub">가이드 <?= esc((string) $total) ?>건</p>

    <?php if ($guides === []): ?>
        <p class="web-empty">등록된 가이드가 없습니다.</p>
    <?php else: ?>
        <ul class="web-list">
            <?php foreach ($guides as $guide): ?>
                <li class="web-list-item">
                    <a href="<?= base_url('guides/' . rawurlencode((string) $guide['slug'])) ?>">
                        <h2><?= esc((string) $guide['title']) ?></h2>
                    </a>
                    <?php if (!empty($guide['summary'])): ?>
                        <p class="web-excerpt"><?= esc((string) $guide['summary']) ?></p>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</section>
