<?php
/** @var array<string, mixed> $advertiser */

$statusLabels = [1 => '활성', 2 => '정지', 3 => '탈퇴'];
$agreed = !empty($advertiser['contract_agreed_at']);
?>
<div class="page-header" style="display:flex;align-items:center;gap:12px;margin-bottom:24px;">
    <a href="/portal/advertisers" style="color:var(--color-text-muted);text-decoration:none;">← 목록</a>
    <h1 class="page-title" style="margin:0;"><?= esc($advertiser['hospital_name']) ?></h1>
    <?= $agreed ? '<span class="badge badge-success">사용가능</span>' : '<span class="badge badge-warning">계약대기</span>' ?>
</div>

<div class="card">
    <div class="card-header"><span class="card-title">기본 정보</span></div>
    <div class="card-body">
        <div class="table-wrap">
            <table class="table">
                <tbody>
                    <tr><th style="width:160px;">병원명</th><td><?= esc($advertiser['hospital_name']) ?></td></tr>
                    <tr><th>담당자</th><td><?= esc($advertiser['contact_name'] ?? '-') ?></td></tr>
                    <tr><th>연락처</th><td><?= esc($advertiser['contact_phone'] ?? '-') ?></td></tr>
                    <tr><th>이메일</th><td><?= esc($advertiser['contact_email'] ?? '-') ?></td></tr>
                    <tr><th>사업자등록번호</th><td><?= esc($advertiser['business_no'] ?? '-') ?></td></tr>
                    <tr><th>상태</th><td><?= esc($statusLabels[(int) $advertiser['status']] ?? '-') ?></td></tr>
                    <tr><th>계약 동의</th><td><?= $agreed ? esc($advertiser['contract_agreed_at_kst']) . ' 동의 완료' : '미동의 (광고주 동의 대기)' ?></td></tr>
                    <tr><th>광고주 계정 연결</th><td><?= !empty($advertiser['owner_user_id']) ? '연결됨 (user #' . (int) $advertiser['owner_user_id'] . ')' : '미연결' ?></td></tr>
                    <tr><th>등록일</th><td><?= esc($advertiser['created_at_kst']) ?></td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
