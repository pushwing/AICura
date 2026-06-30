<?php
/**
 * Admin 가이드 등록/수정 폼 (이슈 #146) — Tiptap 본문 + FAQ 반복 입력
 *
 * @var array<string, mixed>|null $guide
 */
$isEdit = $guide !== null;
$title  = $isEdit ? '가이드 수정' : '가이드 등록';
$action = $isEdit ? '/admin/guides/' . (int) $guide['id'] . '/update' : '/admin/guides';

/** @var array<string, string> $errors */
$errors = session('errors') ?? [];

$curStatus    = (string) old('status', $isEdit ? ($guide['status'] ?? 'draft') : 'draft');
$curContent   = (string) old('content', $isEdit ? ($guide['content'] ?? '') : '');
$faqDecoded   = [];
if ($isEdit && !empty($guide['faq_json'])) {
    $tmp = json_decode((string) $guide['faq_json'], true);
    $faqDecoded = is_array($tmp) ? $tmp : [];
}
?>
<div class="page-header" style="display:flex;align-items:center;gap:12px;margin-bottom:24px;">
    <a href="/admin/guides" style="color:var(--color-text-muted);text-decoration:none;">← 목록</a>
    <h1 class="page-title" style="margin:0;"><?= esc($title) ?></h1>
</div>

<?php if (!empty($errors)): ?>
<div class="alert alert-danger" style="margin-bottom:16px;padding:12px 16px;background:#fef2f2;border:1px solid #fecaca;border-radius:6px;color:#dc2626;font-size:14px;">
    <ul style="margin:0;padding-left:16px;"><?php foreach ($errors as $err): ?><li><?= esc($err) ?></li><?php endforeach; ?></ul>
</div>
<?php endif; ?>

<div class="card"><div class="card-body">
<form action="<?= esc($action) ?>" method="POST" id="guideForm">
    <?= csrf_field() ?>

    <div style="margin-bottom:20px;">
        <label style="display:block;font-size:14px;font-weight:500;margin-bottom:6px;">제목 <span style="color:#ef4444">*</span></label>
        <input type="text" name="title" class="form-control" maxlength="200" required
               value="<?= esc((string) old('title', $isEdit ? ($guide['title'] ?? '') : '')) ?>">
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:20px;">
        <div>
            <label style="display:block;font-size:14px;font-weight:500;margin-bottom:6px;">슬러그 <span style="font-size:12px;color:var(--color-text-muted);">(비우면 제목에서 자동 생성)</span></label>
            <input type="text" name="slug" class="form-control" maxlength="220" placeholder="double-eyelid-guide"
                   value="<?= esc((string) old('slug', $isEdit ? ($guide['slug'] ?? '') : '')) ?>">
        </div>
        <div>
            <label style="display:block;font-size:14px;font-weight:500;margin-bottom:6px;">상태</label>
            <select name="status" class="form-control">
                <option value="draft" <?= $curStatus === 'draft' ? 'selected' : '' ?>>임시저장</option>
                <option value="published" <?= $curStatus === 'published' ? 'selected' : '' ?>>발행</option>
            </select>
        </div>
    </div>

    <div style="display:grid;grid-template-columns:2fr 1fr;gap:16px;margin-bottom:20px;">
        <div>
            <label style="display:block;font-size:14px;font-weight:500;margin-bottom:6px;">요약 <span style="font-size:12px;color:var(--color-text-muted);">(검색 설명·150자 내외 권장)</span></label>
            <input type="text" name="summary" class="form-control" maxlength="500"
                   value="<?= esc((string) old('summary', $isEdit ? ($guide['summary'] ?? '') : '')) ?>">
        </div>
        <div>
            <label style="display:block;font-size:14px;font-weight:500;margin-bottom:6px;">관련 시술명</label>
            <input type="text" name="procedure_name" class="form-control" maxlength="100" placeholder="쌍꺼풀 수술"
                   value="<?= esc((string) old('procedure_name', $isEdit ? ($guide['procedure_name'] ?? '') : '')) ?>">
        </div>
    </div>

    <!-- 본문 (Tiptap) -->
    <div style="margin-bottom:20px;">
        <label style="display:block;font-size:14px;font-weight:500;margin-bottom:6px;">본문</label>
        <div style="display:flex;gap:4px;margin-bottom:6px;">
            <button type="button" id="tipBold" class="btn btn-sm btn-outline"><b>B</b></button>
            <button type="button" id="tipItalic" class="btn btn-sm btn-outline"><i>I</i></button>
            <button type="button" id="tipH2" class="btn btn-sm btn-outline">H2</button>
            <button type="button" id="tipBullet" class="btn btn-sm btn-outline">• 목록</button>
        </div>
        <div id="tiptapEditor" style="min-height:240px;border:1px solid var(--color-border,#e5e7eb);border-radius:6px;padding:12px;outline:none;"></div>
        <input type="hidden" name="content" id="contentInput">
    </div>

    <!-- FAQ -->
    <div style="margin-bottom:24px;">
        <label style="display:block;font-size:14px;font-weight:500;margin-bottom:6px;">FAQ <span style="font-size:12px;color:var(--color-text-muted);">(질문·답변 — GEO 인용에 유리)</span></label>
        <div id="faqList"></div>
        <button type="button" id="faqAdd" class="btn btn-sm btn-outline" style="margin-top:8px;">+ FAQ 추가</button>
    </div>

    <div style="display:flex;gap:8px;">
        <button type="submit" class="btn btn-primary"><?= $isEdit ? '수정' : '등록' ?></button>
        <a href="/admin/guides" class="btn btn-outline">취소</a>
    </div>
</form>
</div></div>

<style>
#tiptapEditor .tiptap { outline:none; }
.tiptap-btn-active { background: var(--color-bg-muted) !important; color: var(--color-primary,#0F6E56) !important; }
.faq-row { display:grid; grid-template-columns:1fr 2fr auto; gap:8px; margin-bottom:8px; align-items:start; }
</style>

<script>
// FAQ 반복 입력 (기존 데이터로 초기화)
const faqList = document.getElementById('faqList');
function addFaqRow(q = '', a = '') {
    const row = document.createElement('div');
    row.className = 'faq-row';
    const qi = document.createElement('input');
    qi.type = 'text'; qi.name = 'faq_q[]'; qi.className = 'form-control'; qi.placeholder = '질문'; qi.value = q;
    const ai = document.createElement('input');
    ai.type = 'text'; ai.name = 'faq_a[]'; ai.className = 'form-control'; ai.placeholder = '답변'; ai.value = a;
    const del = document.createElement('button');
    del.type = 'button'; del.className = 'btn btn-sm btn-outline'; del.textContent = '×';
    del.addEventListener('click', () => row.remove());
    row.append(qi, ai, del);
    faqList.appendChild(row);
}
document.getElementById('faqAdd').addEventListener('click', () => addFaqRow());
(<?= json_encode($faqDecoded) ?> || []).forEach(it => addFaqRow(it.q || '', it.a || ''));

// 제출 시 본문 HTML 주입
document.getElementById('guideForm').addEventListener('submit', () => {
    document.getElementById('contentInput').value = window.__guideEditor ? window.__guideEditor.getHTML() : '';
});
</script>

<script type="module">
import { Editor } from 'https://esm.sh/@tiptap/core@2'
import StarterKit from 'https://esm.sh/@tiptap/starter-kit@2'

const editor = new Editor({
    element: document.getElementById('tiptapEditor'),
    extensions: [StarterKit],
    content: <?= json_encode($curContent) ?>,
})
document.getElementById('tipBold').addEventListener('click', () => editor.chain().focus().toggleBold().run())
document.getElementById('tipItalic').addEventListener('click', () => editor.chain().focus().toggleItalic().run())
document.getElementById('tipH2').addEventListener('click', () => editor.chain().focus().toggleHeading({ level: 2 }).run())
document.getElementById('tipBullet').addEventListener('click', () => editor.chain().focus().toggleBulletList().run())
editor.on('transaction', () => {
    document.getElementById('tipBold').classList.toggle('tiptap-btn-active', editor.isActive('bold'))
    document.getElementById('tipItalic').classList.toggle('tiptap-btn-active', editor.isActive('italic'))
    document.getElementById('tipH2').classList.toggle('tiptap-btn-active', editor.isActive('heading', { level: 2 }))
    document.getElementById('tipBullet').classList.toggle('tiptap-btn-active', editor.isActive('bulletList'))
})
window.__guideEditor = editor
</script>
