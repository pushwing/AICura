<?php
/** @var array<string, mixed>|null $campaign */
/** @var array<int, array<string, mixed>> $hospitals */
/** @var array<int, array<string, mixed>> $contracts */
/** @var array<int, string> $adTypes */
/** @var array<int, string> $channels */
/** @var array<int, array{id: int, title: string}> $categories */

$isEdit   = $campaign !== null;
$formAction = $isEdit
    ? '/admin/campaigns/' . (int)$campaign['id']
    : '/admin/campaigns';
$title = $isEdit ? '캠페인 수정' : '캠페인 등록';
$old   = fn(string $key, mixed $default = '') => old($key, $campaign[$key] ?? $default);
?>

<div class="page-header" style="display:flex;align-items:center;gap:12px;margin-bottom:20px;">
    <a href="<?= $isEdit ? '/admin/campaigns/' . (int)$campaign['id'] : '/admin/campaigns' ?>"
       style="color:var(--color-text-muted);text-decoration:none;">←</a>
    <h1 class="page-title" style="margin:0;"><?= $title ?></h1>
</div>

<?php if (!empty(session()->getFlashdata('errors'))): ?>
<div class="alert alert-danger" style="margin-bottom:16px;">
    <ul style="margin:0;padding-left:20px;">
        <?php foreach ((array)session()->getFlashdata('errors') as $error): ?>
            <li><?= esc($error) ?></li>
        <?php endforeach; ?>
    </ul>
</div>
<?php endif; ?>

<form action="<?= $formAction ?>" method="POST" enctype="multipart/form-data">
    <?= csrf_field() ?>
    <?php if ($isEdit): ?>
        <input type="hidden" name="_method" value="PUT">
    <?php endif; ?>

    <div style="display:grid;grid-template-columns:2fr 1fr;gap:20px;">

        <!-- 좌측: 기본 정보 -->
        <div style="display:flex;flex-direction:column;gap:16px;">

            <div class="card">
                <div class="card-body">
                    <h3 style="font-size:15px;margin-bottom:16px;">기본 정보</h3>

                    <div class="form-group">
                        <label class="form-label">캠페인명 <span style="color:#ef4444">*</span></label>
                        <input type="text" name="ad_title" class="form-control"
                               value="<?= esc($old('ad_title')) ?>" required>
                    </div>

                    <div class="form-group" style="margin-top:12px;">
                        <label class="form-label">병원 <span style="color:#ef4444">*</span></label>
                        <select name="hospital_id" class="form-control" required>
                            <option value="">병원 선택</option>
                            <?php foreach ($hospitals as $h): ?>
                                <option value="<?= (int)$h['id'] ?>"
                                    <?= (int)$old('hospital_id', 0) === (int)$h['id'] ? 'selected' : '' ?>>
                                    <?= esc($h['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-top:12px;">
                        <div class="form-group">
                            <label class="form-label">광고 타입 <span style="color:#ef4444">*</span></label>
                            <select name="ad_type" class="form-control" required>
                                <?php foreach ($adTypes as $val => $label): ?>
                                    <option value="<?= $val ?>"
                                        <?= (int)$old('ad_type', 1) === $val ? 'selected' : '' ?>>
                                        <?= esc($label) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">채널 <span style="color:#ef4444">*</span></label>
                            <select name="channel" class="form-control" required>
                                <?php foreach ($channels as $val => $label): ?>
                                    <option value="<?= $val ?>"
                                        <?= (int)$old('channel', 1) === $val ? 'selected' : '' ?>>
                                        <?= esc($label) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-top:12px;">
                        <div class="form-group">
                            <label class="form-label">광고 시작일 <span style="color:#ef4444">*</span></label>
                            <input type="date" name="ad_start_date" class="form-control"
                                   value="<?= esc($old('ad_start_date')) ?>" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">광고 종료일 <span style="color:#ef4444">*</span></label>
                            <input type="date" name="ad_end_date" class="form-control"
                                   value="<?= esc($old('ad_end_date')) ?>" required>
                        </div>
                    </div>

                    <div class="form-group" style="margin-top:12px;">
                        <label class="form-label">노출 유형</label>
                        <select name="exposure" class="form-control">
                            <option value="1" <?= (int)$old('exposure', 1) === 1 ? 'selected' : '' ?>>이벤트</option>
                            <option value="2" <?= (int)$old('exposure', 1) === 2 ? 'selected' : '' ?>>병원상세</option>
                            <option value="3" <?= (int)$old('exposure', 1) === 3 ? 'selected' : '' ?>>전체</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- 가격 정보 -->
            <div class="card">
                <div class="card-body">
                    <h3 style="font-size:15px;margin-bottom:16px;">가격 정보</h3>

                    <div class="form-group">
                        <label class="form-label">가격 유형 <span style="color:#ef4444">*</span></label>
                        <div style="display:flex;gap:16px;">
                            <label style="display:flex;align-items:center;gap:6px;cursor:pointer;">
                                <input type="radio" name="cost_type" value="1"
                                       <?= (int)$old('cost_type', 1) === 1 ? 'checked' : '' ?>
                                       onchange="toggleCostType(1)">
                                숫자 가격
                            </label>
                            <label style="display:flex;align-items:center;gap:6px;cursor:pointer;">
                                <input type="radio" name="cost_type" value="2"
                                       <?= (int)$old('cost_type', 1) === 2 ? 'checked' : '' ?>
                                       onchange="toggleCostType(2)">
                                텍스트 가격
                            </label>
                        </div>
                    </div>

                    <div id="numericCostFields" style="margin-top:12px;">
                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                            <div class="form-group">
                                <label class="form-label">정상가 (원)</label>
                                <input type="number" name="general_cost" class="form-control"
                                       value="<?= esc($old('general_cost', 0)) ?>" min="0">
                            </div>
                            <div class="form-group">
                                <label class="form-label">할인가 (원)</label>
                                <input type="number" name="discount_cost" class="form-control"
                                       value="<?= esc($old('discount_cost', 0)) ?>" min="0">
                            </div>
                        </div>
                    </div>

                    <div id="textCostField" style="display:none;margin-top:12px;">
                        <div class="form-group">
                            <label class="form-label">가격 텍스트</label>
                            <input type="text" name="text_cost" class="form-control"
                                   value="<?= esc($old('text_cost')) ?>" placeholder="예: 상담 후 결정">
                        </div>
                    </div>

                    <div class="form-group" style="margin-top:12px;">
                        <label class="form-label">DB 단가 (원)</label>
                        <input type="number" name="db_cost" class="form-control"
                               value="<?= esc($old('db_cost', 0)) ?>" min="0">
                    </div>
                </div>
            </div>

            <!-- 추가 정보 -->
            <div class="card">
                <div class="card-body">
                    <h3 style="font-size:15px;margin-bottom:16px;">추가 정보</h3>

                    <div class="form-group">
                        <label class="form-label">지역</label>
                        <input type="text" name="region" class="form-control"
                               value="<?= esc($old('region')) ?>" placeholder="예: 서울,경기">
                    </div>

                    <div class="form-group" style="margin-top:12px;">
                        <label class="form-label">키워드</label>
                        <input type="text" name="keyword" class="form-control"
                               value="<?= esc($old('keyword')) ?>" placeholder="콤마로 구분">
                    </div>

                    <div class="form-group" style="margin-top:12px;">
                        <label class="form-label">의료심의번호</label>
                        <input type="text" name="deliberation_code" class="form-control"
                               value="<?= esc($old('deliberation_code')) ?>">
                    </div>

                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-top:12px;">
                        <div class="form-group">
                            <label class="form-label">계약 ID</label>
                            <select name="contract_id" class="form-control">
                                <option value="">선택 안 함</option>
                                <?php foreach ($contracts as $c): ?>
                                    <option value="<?= (int)$c['id'] ?>"
                                        <?= (int)$old('contract_id', 0) === (int)$c['id'] ? 'selected' : '' ?>>
                                        <?= esc($c['title'] ?? $c['hospital_name'] ?? '#' . $c['id']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">카테고리</label>
                            <select name="category" class="form-control">
                                <option value="0">미분류</option>
                                <?php foreach (($categories ?? []) as $cat): ?>
                                    <option value="<?= (int) $cat['id'] ?>"
                                        <?= (int) $old('category', 0) === (int) $cat['id'] ? 'selected' : '' ?>>
                                        <?= esc($cat['title']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <!-- 우측: 이미지 업로드 -->
        <div style="display:flex;flex-direction:column;gap:16px;">
            <div class="card">
                <div class="card-body">
                    <h3 style="font-size:15px;margin-bottom:16px;">이미지</h3>

                    <?php foreach (['t1_image_name' => '썸네일 1', 't2_image_name' => '썸네일 2'] as $field => $label): ?>
                    <div class="form-group" style="margin-bottom:16px;">
                        <label class="form-label"><?= $label ?></label>
                        <?php if ($isEdit && !empty($campaign[$field])): ?>
                            <div style="margin-bottom:6px;">
                                <img src="<?= base_url('uploads/campaigns/' . esc(basename($campaign[$field]))) ?>"
                                     style="max-width:100%;max-height:120px;border-radius:4px;display:block;">
                                <p style="font-size:11px;color:var(--color-text-muted);margin-top:4px;">현재 이미지 (새 파일 업로드 시 교체)</p>
                            </div>
                        <?php endif; ?>
                        <input type="file" name="<?= $field ?>" class="form-control"
                               accept="image/jpeg,image/png,image/gif,image/webp"
                               onchange="previewImage(this, '<?= $field ?>_preview')">
                        <img id="<?= $field ?>_preview" style="display:none;max-width:100%;margin-top:6px;border-radius:4px;">
                    </div>
                    <?php endforeach; ?>

                    <div class="form-group" style="margin-top:4px;">
                        <label class="form-label">상세 이미지 (다중)</label>
                        <?php if ($isEdit && !empty($campaign['d_image_json'])):
                            $dImages = json_decode($campaign['d_image_json'], true) ?: [];
                        ?>
                            <div style="display:flex;flex-wrap:wrap;gap:6px;margin-bottom:8px;">
                                <?php foreach ($dImages as $img): ?>
                                    <img src="<?= base_url('uploads/campaigns/' . esc(basename($img))) ?>"
                                         style="width:60px;height:60px;object-fit:cover;border-radius:4px;">
                                <?php endforeach; ?>
                            </div>
                            <p style="font-size:11px;color:var(--color-text-muted);margin-bottom:6px;">새 파일 업로드 시 전체 교체</p>
                        <?php endif; ?>
                        <input type="file" name="d_images[]" class="form-control"
                               accept="image/jpeg,image/png,image/gif,image/webp" multiple>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <div style="display:flex;justify-content:flex-end;gap:8px;margin-top:20px;">
        <a href="<?= $isEdit ? '/admin/campaigns/' . (int)$campaign['id'] : '/admin/campaigns' ?>"
           class="btn btn-outline">취소</a>
        <button type="submit" class="btn btn-primary">
            <?= $isEdit ? '수정 저장' : '등록' ?>
        </button>
    </div>
</form>

<script>
function toggleCostType(type) {
    document.getElementById('numericCostFields').style.display = type === 1 ? 'block' : 'none';
    document.getElementById('textCostField').style.display     = type === 2 ? 'block' : 'none';
}

function previewImage(input, previewId) {
    const preview = document.getElementById(previewId);
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = (e) => {
            preview.src = e.target.result;
            preview.style.display = 'block';
        };
        reader.readAsDataURL(input.files[0]);
    }
}

// 초기 가격 유형 적용
(function () {
    const checked = document.querySelector('input[name="cost_type"]:checked');
    if (checked) toggleCostType(Number(checked.value));
})();
</script>
