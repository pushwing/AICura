<?php
/** @var bool $hasAdvertiser */
/** @var bool $agreed */
/** @var string|null $contractName */
/** @var int $totalCount */
/** @var int $newCount */
?>
<div class="page-header">
    <h1 class="page-title">대시보드</h1>
    <span class="text-sm" style="color:var(--color-text-muted)"><?= date('Y년 n월 j일') ?></span>
</div>

<?php if (!$hasAdvertiser): ?>
    <div class="alert alert-danger">
        <span class="alert-icon">!</span>
        <div class="alert-body">연결된 광고주 정보가 없습니다. 담당 광고대행사 또는 운영팀에 문의해주세요.</div>
    </div>
<?php else: ?>

    <?php if (!$agreed): ?>
        <div class="card" style="margin-bottom:24px;border-left:3px solid #f59e0b;">
            <div class="card-body" style="display:flex;align-items:center;justify-content:space-between;gap:16px;">
                <div>
                    <p style="font-weight:600;margin-bottom:4px;">계약 동의가 필요합니다</p>
                    <p class="text-sm" style="color:var(--color-text-muted);">계약에 동의해야 광고 서비스를 이용할 수 있습니다.</p>
                </div>
                <a href="/portal/contracts" class="btn btn-primary btn-sm">계약 확인하기</a>
            </div>
        </div>
    <?php endif; ?>

    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:16px;">
        <div class="card"><div class="card-body">
            <p class="text-sm" style="color:var(--color-text-muted);margin-bottom:6px;">계약 상태</p>
            <p style="font-size:1.5rem;font-weight:700;color:<?= $agreed ? '#1D9E75' : '#f59e0b' ?>;">
                <?= $agreed ? '사용가능' : '계약대기' ?>
            </p>
            <p class="text-xs" style="color:var(--color-text-muted);margin-top:4px;"><?= esc($contractName ?? '계약 없음') ?></p>
        </div></div>
        <div class="card"><div class="card-body">
            <p class="text-sm" style="color:var(--color-text-muted);margin-bottom:6px;">전체 신청</p>
            <p style="font-size:2rem;font-weight:700;color:var(--color-text)"><?= number_format($totalCount) ?></p>
        </div></div>
        <div class="card"><div class="card-body">
            <p class="text-sm" style="color:var(--color-text-muted);margin-bottom:6px;">미확인 신청</p>
            <p style="font-size:2rem;font-weight:700;color:#ef4444;"><?= number_format($newCount) ?></p>
            <p class="text-xs" style="color:var(--color-text-muted);margin-top:4px;"><a href="/portal/call-requests?status=1" style="color:var(--color-primary);text-decoration:none;">신청DB 관리 →</a></p>
        </div></div>
    </div>

<?php endif; ?>
