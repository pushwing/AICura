<?php
/** @var array<string, mixed> $campaign */
/** @var array<int, array<string, mixed>> $histories */
/** @var array<int, string> $adTypes */
/** @var array<int, string> $channels */
/** @var array<string, array<string, string>> $transitions */

$statusLabels = [
    'pending'  => ['label' => '검토중', 'color' => '#f59e0b'],
    'active'   => ['label' => '진행중', 'color' => '#10b981'],
    'rejected' => ['label' => '반려',   'color' => '#ef4444'],
    'ended'    => ['label' => '종료',   'color' => '#6b7280'],
];

$actionLabels = [
    'approve' => ['label' => '승인',    'class' => 'btn-success'],
    'reject'  => ['label' => '반려',    'class' => 'btn-danger'],
    'end'     => ['label' => '종료처리', 'class' => 'btn-secondary'],
    'reopen'  => ['label' => '재검토',  'class' => 'btn-warning'],
];

$currentStatus = $campaign['status'];
$nextStatuses  = \App\Models\CampaignModel::STATUS_TRANSITIONS[$currentStatus] ?? [];

$actionMap = [
    'active'   => 'approve',
    'rejected' => 'reject',
    'ended'    => 'end',
    'pending'  => 'reopen',
];
?>

<div class="page-header" style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;">
    <div style="display:flex;align-items:center;gap:12px;">
        <a href="/admin/campaigns" style="color:var(--color-text-muted);text-decoration:none;">← 목록</a>
        <h1 class="page-title" style="margin:0;"><?= esc($campaign['ad_title']) ?></h1>
        <?php
        $s = $statusLabels[$currentStatus] ?? ['label' => $currentStatus, 'color' => '#888'];
        ?>
        <span style="background:<?= $s['color'] ?>20;color:<?= $s['color'] ?>;padding:3px 10px;border-radius:4px;font-size:13px;">
            <?= esc($s['label']) ?>
        </span>
    </div>
    <div style="display:flex;gap:8px;">
        <a href="/admin/campaigns/<?= (int) $campaign['id'] ?>/edit" class="btn btn-outline btn-sm">수정</a>
        <!-- 상태 변경 버튼 -->
        <?php foreach ($nextStatuses as $nextStatus):
            $act = $actionMap[$nextStatus] ?? null;
            if ($act === null) continue;
            $al = $actionLabels[$act] ?? ['label' => $act, 'class' => 'btn-outline'];
        ?>
            <button type="button"
                    class="btn btn-sm <?= $al['class'] ?>"
                    onclick="changeStatus('<?= esc($act) ?>')">
                <?= esc($al['label']) ?>
            </button>
        <?php endforeach; ?>
    </div>
</div>

<div style="display:grid;grid-template-columns:2fr 1fr;gap:20px;">

    <!-- 캠페인 정보 -->
    <div class="card">
        <div class="card-body">
            <h3 style="margin-bottom:16px;font-size:15px;">기본 정보</h3>
            <table style="width:100%;font-size:14px;border-collapse:collapse;">
                <?php
                $rows = [
                    ['병원',      esc($campaign['hospital_name'] ?? '-')],
                    ['광고 타입',  esc($adTypes[(int)$campaign['ad_type']] ?? '-')],
                    ['채널',      esc($channels[(int)$campaign['channel']] ?? '-')],
                    ['광고 기간',  esc($campaign['ad_start_date']) . ' ~ ' . esc($campaign['ad_end_date'])],
                    ['노출 유형',  match((int)$campaign['exposure']) { 1 => '이벤트', 2 => '병원상세', 3 => '전체', default => '-' }],
                    ['가격 유형',  (int)$campaign['cost_type'] === 1 ? '숫자' : '텍스트'],
                    ['정상가',    number_format((int)$campaign['general_cost']) . '원'],
                    ['할인가',    number_format((int)$campaign['discount_cost']) . '원'],
                    ['DB 단가',   number_format((int)$campaign['db_cost']) . '원'],
                    ['지역',      esc($campaign['region'] ?? '-')],
                    ['키워드',    esc($campaign['keyword'] ?? '-')],
                    ['심의번호',  esc($campaign['deliberation_code'] ?? '-')],
                    ['계약 ID',   esc((string)($campaign['contract_id'] ?? '-'))],
                ];
                foreach ($rows as [$label, $value]):
                ?>
                <tr>
                    <td style="padding:8px 0;color:var(--color-text-muted);width:120px;"><?= $label ?></td>
                    <td style="padding:8px 0;"><?= $value ?></td>
                </tr>
                <?php endforeach; ?>
            </table>
        </div>
    </div>

    <!-- 우측 패널 -->
    <div style="display:flex;flex-direction:column;gap:16px;">

        <!-- 이미지 -->
        <div class="card">
            <div class="card-body">
                <h3 style="margin-bottom:12px;font-size:15px;">이미지</h3>
                <?php foreach (['t1_image_name' => '썸네일1', 't2_image_name' => '썸네일2'] as $field => $label): ?>
                    <?php if (!empty($campaign[$field])): ?>
                        <p style="font-size:12px;color:var(--color-text-muted);margin-bottom:4px;"><?= $label ?></p>
                        <img src="<?= base_url('uploads/campaigns/' . esc(basename($campaign[$field]))) ?>"
                             alt="<?= $label ?>"
                             style="max-width:100%;border-radius:4px;margin-bottom:8px;">
                    <?php endif; ?>
                <?php endforeach; ?>
                <?php if (!empty($campaign['d_image_json'])):
                    $dImages = json_decode($campaign['d_image_json'], true) ?: [];
                ?>
                    <p style="font-size:12px;color:var(--color-text-muted);margin-bottom:4px;">상세 이미지</p>
                    <div style="display:flex;flex-wrap:wrap;gap:6px;">
                        <?php foreach ($dImages as $img): ?>
                            <img src="<?= base_url('uploads/campaigns/' . esc(basename($img))) ?>"
                                 style="width:60px;height:60px;object-fit:cover;border-radius:4px;">
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                <?php if (empty($campaign['t1_image_name']) && empty($campaign['t2_image_name']) && empty($campaign['d_image_json'])): ?>
                    <p style="color:var(--color-text-muted);font-size:13px;">이미지 없음</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- 패키지 목록 -->
        <?php if (!empty($campaign['packages'])): ?>
        <div class="card">
            <div class="card-body">
                <h3 style="margin-bottom:12px;font-size:15px;">연결 패키지</h3>
                <?php foreach ($campaign['packages'] as $pkg): ?>
                    <div style="padding:8px 0;border-bottom:1px solid var(--color-border);font-size:13px;">
                        <strong><?= esc($pkg['title']) ?></strong>
                        <span style="color:var(--color-text-muted);margin-left:8px;"><?= esc($pkg['status']) ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

    </div>
</div>

<!-- 최근 히스토리 -->
<?php if (!empty($histories)): ?>
<div class="card" style="margin-top:20px;">
    <div class="card-body">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px;">
            <h3 style="font-size:15px;margin:0;">최근 변경 이력</h3>
            <a href="/admin/campaigns/<?= (int)$campaign['id'] ?>/history" style="font-size:13px;color:var(--color-primary, #0F6E56);">전체 보기</a>
        </div>
        <table style="width:100%;font-size:13px;border-collapse:collapse;">
            <thead>
                <tr style="border-bottom:1px solid var(--color-border);">
                    <th style="padding:8px 0;text-align:left;font-weight:500;color:var(--color-text-muted);">액션</th>
                    <th style="padding:8px 0;text-align:left;font-weight:500;color:var(--color-text-muted);">상태 변경</th>
                    <th style="padding:8px 0;text-align:left;font-weight:500;color:var(--color-text-muted);">처리자</th>
                    <th style="padding:8px 0;text-align:left;font-weight:500;color:var(--color-text-muted);">일시</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($histories as $h): ?>
                <tr style="border-bottom:1px solid var(--color-border);">
                    <td style="padding:8px 0;"><?= esc($h['action']) ?></td>
                    <td style="padding:8px 0;"><?= esc($h['status_from']) ?> → <?= esc($h['status_to']) ?></td>
                    <td style="padding:8px 0;"><?= esc($h['admin_name'] ?? '-') ?></td>
                    <td style="padding:8px 0;color:var(--color-text-muted);"><?= esc($h['created_at']) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<!-- 상태 변경 모달 -->
<div id="actionModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:1000;align-items:center;justify-content:center;">
    <div style="background:var(--color-bg,#fff);border-radius:12px;padding:24px;min-width:400px;max-width:520px;width:100%;">
        <h3 id="modalTitle" style="margin-bottom:16px;font-size:16px;"></h3>

        <!-- Tiptap 에디터 래퍼 -->
        <div style="border:1px solid var(--color-border);border-radius:var(--radius-sm);overflow:hidden;">
            <!-- 툴바 -->
            <div style="display:flex;gap:2px;padding:5px 6px;border-bottom:1px solid var(--color-border);background:var(--color-bg-surface);">
                <button type="button" id="tipBold"
                        title="굵게 (Ctrl+B)"
                        style="width:28px;height:28px;border:none;background:transparent;border-radius:4px;cursor:pointer;font-weight:700;font-size:13px;color:var(--color-text);">B</button>
                <button type="button" id="tipItalic"
                        title="기울임 (Ctrl+I)"
                        style="width:28px;height:28px;border:none;background:transparent;border-radius:4px;cursor:pointer;font-style:italic;font-size:13px;color:var(--color-text);">I</button>
                <button type="button" id="tipBullet"
                        title="글머리 목록"
                        style="width:28px;height:28px;border:none;background:transparent;border-radius:4px;cursor:pointer;font-size:15px;color:var(--color-text);">≡</button>
            </div>
            <!-- 편집 영역 -->
            <div id="tiptapMemoEditor"
                 style="padding:8px 10px;min-height:80px;font-size:14px;line-height:1.6;color:var(--color-text);background:var(--color-bg);"></div>
        </div>

        <div style="display:flex;justify-content:flex-end;gap:8px;margin-top:16px;">
            <button type="button" onclick="closeModal()" class="btn btn-outline btn-sm">취소</button>
            <button type="button" id="modalConfirm" class="btn btn-primary btn-sm">확인</button>
        </div>
    </div>
</div>

<!-- Tiptap 에디터 CSS (헤드리스 기본 스타일) -->
<style>
#tiptapMemoEditor .tiptap { outline: none; }
#tiptapMemoEditor .tiptap p { margin: 0 0 4px; }
#tiptapMemoEditor .tiptap ul { padding-left: 20px; margin: 4px 0; }
#tiptapMemoEditor .tiptap li { margin: 2px 0; }
#tiptapMemoEditor .tiptap p.is-editor-empty:first-child::before {
    content: attr(data-placeholder);
    color: var(--color-text-muted, #aaa);
    pointer-events: none;
    float: left;
    height: 0;
}
.tiptap-btn-active { background: var(--color-bg-muted) !important; color: var(--color-primary, #0F6E56) !important; }
</style>

<script>
let pendingAction = null;

const actionTitles = {
    approve: '승인하시겠습니까?',
    reject:  '반려 처리하시겠습니까?',
    end:     '종료 처리하시겠습니까?',
    reopen:  '재검토 상태로 변경하시겠습니까?',
};

function changeStatus(action) {
    pendingAction = action;
    document.getElementById('modalTitle').textContent = actionTitles[action] ?? action;
    window.__memoEditor?.commands.clearContent(true);
    document.getElementById('actionModal').style.display = 'flex';
    setTimeout(() => window.__memoEditor?.commands.focus(), 50);
}

function closeModal() {
    document.getElementById('actionModal').style.display = 'none';
    pendingAction = null;
}

document.getElementById('modalConfirm').addEventListener('click', async () => {
    if (!pendingAction) return;

    const memo = window.__memoEditor ? window.__memoEditor.getHTML() : '';
    const btn  = document.getElementById('modalConfirm');
    btn.disabled = true;
    btn.textContent = '처리 중...';

    try {
        const res = await fetch('/admin/campaigns/<?= (int)$campaign['id'] ?>/action', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.cookie.match(/csrf_cookie_name=([^;]+)/)?.[1] ?? '',
            },
            body: JSON.stringify({ action: pendingAction, memo }),
        });

        const data = await res.json();
        if (data.success) {
            location.reload();
        } else {
            alert(data.message ?? '처리 실패');
        }
    } catch (e) {
        alert('오류가 발생했습니다.');
    } finally {
        btn.disabled = false;
        btn.textContent = '확인';
    }
});

document.getElementById('actionModal').addEventListener('click', (e) => {
    if (e.target === e.currentTarget) closeModal();
});
</script>

<!-- Tiptap 에디터 초기화 (ES Module CDN) -->
<script type="module">
import { Editor } from 'https://esm.sh/@tiptap/core@2'
import StarterKit from 'https://esm.sh/@tiptap/starter-kit@2'
import Placeholder from 'https://esm.sh/@tiptap/extension-placeholder@2'

const editor = new Editor({
    element: document.getElementById('tiptapMemoEditor'),
    extensions: [
        StarterKit,
        Placeholder.configure({ placeholder: '메모를 입력하세요 (선택)' }),
    ],
    content: '',
})

// 툴바 버튼 연결
document.getElementById('tipBold').addEventListener('click', () =>
    editor.chain().focus().toggleBold().run()
)
document.getElementById('tipItalic').addEventListener('click', () =>
    editor.chain().focus().toggleItalic().run()
)
document.getElementById('tipBullet').addEventListener('click', () =>
    editor.chain().focus().toggleBulletList().run()
)

// 활성 상태 표시
const updateToolbar = () => {
    document.getElementById('tipBold').classList.toggle('tiptap-btn-active', editor.isActive('bold'))
    document.getElementById('tipItalic').classList.toggle('tiptap-btn-active', editor.isActive('italic'))
    document.getElementById('tipBullet').classList.toggle('tiptap-btn-active', editor.isActive('bulletList'))
}
editor.on('transaction', updateToolbar)

// 전역 노출 (일반 script에서 참조)
window.__memoEditor = editor
</script>
