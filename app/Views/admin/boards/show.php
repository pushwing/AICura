<?php
/** @var array<string, mixed> $board */
/** @var array<int, string> $types */
/** @var array<int, string> $deleteStates */

$deleteColors = [0 => '#10b981', 1 => '#f59e0b', 2 => '#ef4444'];
$state        = (int) $board['is_delete'];
$sc           = $deleteColors[$state] ?? '#6b7280';
$isDeleted    = $state !== 0;
$reports      = $board['reports'] ?? [];
?>

<div class="page-header" style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;">
    <div style="display:flex;align-items:center;gap:12px;">
        <a href="/admin/boards" style="color:var(--color-text-muted);text-decoration:none;">← 목록</a>
        <h1 class="page-title" style="margin:0;"><?= esc($board['subject'] ?: '(제목없음)') ?></h1>
        <span style="background:<?= $sc ?>20;color:<?= $sc ?>;padding:3px 10px;border-radius:4px;font-size:13px;">
            <?= esc($deleteStates[$state] ?? (string) $state) ?>
        </span>
    </div>
    <div style="display:flex;gap:8px;">
        <form method="POST" action="/admin/boards/<?= (int) $board['id'] ?>/reanalyze" style="margin:0;">
            <?= csrf_field() ?>
            <button type="submit" class="btn btn-outline btn-sm">AI 재분석</button>
        </form>
        <form method="POST" action="/admin/boards/<?= (int) $board['id'] ?>/recalc" style="margin:0;">
            <?= csrf_field() ?>
            <button type="submit" class="btn btn-outline btn-sm">별점 집계 갱신</button>
        </form>
        <?php if ($isDeleted): ?>
            <form method="POST" action="/admin/boards/<?= (int) $board['id'] ?>/restore" style="margin:0;"
                  onsubmit="return confirm('이 후기를 복구하시겠습니까?');">
                <?= csrf_field() ?>
                <button type="submit" class="btn btn-success btn-sm">복구</button>
            </form>
        <?php else: ?>
            <button type="button" class="btn btn-warning btn-sm" onclick="openDeleteModal(1)">임시삭제</button>
            <button type="button" class="btn btn-danger btn-sm" onclick="openDeleteModal(2)">완전삭제</button>
        <?php endif; ?>
    </div>
</div>

<div style="display:grid;grid-template-columns:2fr 1fr;gap:20px;">

    <!-- 후기 본문 -->
    <div class="card">
        <div class="card-body">
            <table style="width:100%;font-size:14px;border-collapse:collapse;margin-bottom:16px;">
                <?php
                $rows = [
                    ['유형',    esc($types[(int) $board['type']] ?? '-')],
                    ['대상 ID', esc((string) $board['target_id'])],
                    ['작성자',  esc($board['user_name'] ?: '-')],
                    ['별점',    number_format((float) $board['rate_sum'], 1) . '점'],
                    ['좋아요',  number_format((int) $board['like_count']) . '개'],
                    ['신고',    number_format((int) $board['complain_count']) . '건'],
                    ['작성일시', esc($board['created_at'] ?? '-')],
                ];
                if ($isDeleted) {
                    $rows[] = ['삭제 사유', esc($board['delete_memo'] ?? '-')];
                    $rows[] = ['삭제일시', esc($board['delete_date'] ?? '-')];
                }
                foreach ($rows as [$label, $value]):
                ?>
                <tr>
                    <td style="padding:8px 0;color:var(--color-text-muted);width:110px;vertical-align:top;"><?= $label ?></td>
                    <td style="padding:8px 0;"><?= $value ?></td>
                </tr>
                <?php endforeach; ?>
            </table>

            <h3 style="font-size:14px;margin:0 0 8px;color:var(--color-text-muted);">본문</h3>
            <div style="font-size:14px;line-height:1.7;white-space:pre-wrap;border-top:1px solid var(--color-border);padding-top:12px;">
                <?= nl2br(esc($board['contents'] ?? '')) ?>
            </div>

            <?php
            // AI 후기 신뢰성 분석 (이슈 #74)
            $aiStatus    = (int) ($board['ai_status'] ?? 0);
            $aiStatusMap = [0 => '미분석', 1 => '분석 대기중', 2 => '완료', 3 => '실패'];
            $sentimentMap = [
                'positive' => ['긍정', '#10b981'],
                'neutral'  => ['중립', '#6b7280'],
                'negative' => ['부정', '#ef4444'],
            ];
            $flagMap = [
                'spam' => '스팸', 'fake' => '가짜', 'exaggeration' => '과장',
                'medical_overclaim' => '의학적과장', 'advertisement' => '광고성', 'duplicate' => '중복',
            ];
            $aiFlags = is_array($board['ai_flags'] ?? null) ? $board['ai_flags'] : [];
            $trust   = $board['ai_trust_score'] ?? null;
            $trustColor = $trust === null ? '#6b7280' : ((int) $trust < 40 ? '#ef4444' : ((int) $trust < 70 ? '#f59e0b' : '#10b981'));
            ?>
            <h3 style="font-size:14px;margin:20px 0 8px;color:var(--color-text-muted);border-top:1px solid var(--color-border);padding-top:16px;">AI 신뢰성 분석</h3>
            <?php if ($aiStatus === 2): ?>
                <div style="display:flex;flex-wrap:wrap;gap:8px;align-items:center;margin-bottom:10px;">
                    <span style="background:<?= $trustColor ?>20;color:<?= $trustColor ?>;padding:3px 10px;border-radius:4px;font-size:13px;font-weight:600;">
                        신뢰점수 <?= esc((string) (int) $trust) ?>점
                    </span>
                    <?php if (isset($sentimentMap[$board['ai_sentiment'] ?? ''])): [$sLabel, $sColor] = $sentimentMap[$board['ai_sentiment']]; ?>
                        <span style="background:<?= $sColor ?>20;color:<?= $sColor ?>;padding:3px 10px;border-radius:4px;font-size:13px;"><?= esc($sLabel) ?></span>
                    <?php endif; ?>
                    <?php foreach ($aiFlags as $flag): ?>
                        <span style="background:#ef444420;color:#ef4444;padding:3px 10px;border-radius:4px;font-size:13px;"><?= esc($flagMap[$flag] ?? $flag) ?></span>
                    <?php endforeach; ?>
                </div>
                <?php if (!empty($board['ai_reason'])): ?>
                    <p style="font-size:13px;color:var(--color-text-muted);margin:0;">근거: <?= esc($board['ai_reason']) ?></p>
                <?php endif; ?>
            <?php else: ?>
                <p style="font-size:13px;color:var(--color-text-muted);margin:0;"><?= esc($aiStatusMap[$aiStatus] ?? '미분석') ?><?= $aiStatus === 0 || $aiStatus === 3 ? ' — 상단 [AI 재분석] 버튼으로 분석을 요청할 수 있습니다.' : '' ?></p>
            <?php endif; ?>
        </div>
    </div>

    <!-- 신고 내역 -->
    <div class="card">
        <div class="card-body">
            <h3 style="margin:0 0 12px;font-size:15px;">신고 내역 (<?= count($reports) ?>)</h3>
            <?php if (empty($reports)): ?>
                <p style="color:var(--color-text-muted);font-size:13px;">신고 내역이 없습니다.</p>
            <?php else: ?>
                <?php foreach ($reports as $r): ?>
                    <div style="padding:8px 0;border-bottom:1px solid var(--color-border);font-size:13px;">
                        <div><?= esc($r['reporter_name'] ?? ('사용자 #' . (int) $r['user_id'])) ?></div>
                        <div style="color:var(--color-text-muted);font-size:11px;margin-top:2px;"><?= esc($r['created_at'] ?? '-') ?></div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php if (!$isDeleted): ?>
<!-- 삭제 모달 -->
<div id="deleteModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:1000;align-items:center;justify-content:center;">
    <div style="background:var(--color-bg,#fff);border-radius:12px;padding:24px;min-width:380px;max-width:480px;">
        <h3 id="deleteModalTitle" style="margin-bottom:16px;font-size:16px;"></h3>
        <form method="POST" action="/admin/boards/<?= (int) $board['id'] ?>/delete">
            <?= csrf_field() ?>
            <input type="hidden" name="state" id="deleteState" value="1">
            <div style="margin-bottom:20px;">
                <label style="display:block;font-size:13px;margin-bottom:6px;color:var(--color-text-muted);">삭제 사유 (선택)</label>
                <input type="text" name="delete_memo" class="form-control" maxlength="200" placeholder="사유 입력">
            </div>
            <div style="display:flex;justify-content:flex-end;gap:8px;">
                <button type="button" onclick="closeDeleteModal()" class="btn btn-outline btn-sm">취소</button>
                <button type="submit" class="btn btn-danger btn-sm">삭제</button>
            </div>
        </form>
    </div>
</div>

<script>
function openDeleteModal(state) {
    document.getElementById('deleteState').value = state;
    document.getElementById('deleteModalTitle').textContent =
        state === 2 ? '완전삭제 처리' : '임시삭제 처리';
    document.getElementById('deleteModal').style.display = 'flex';
}

function closeDeleteModal() {
    document.getElementById('deleteModal').style.display = 'none';
}

document.getElementById('deleteModal').addEventListener('click', (e) => {
    if (e.target === e.currentTarget) closeDeleteModal();
});
</script>
<?php endif; ?>
