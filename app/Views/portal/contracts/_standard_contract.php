<?php
/**
 * 광고대행 표준계약서 본문 (공용 파셜)
 *
 * 광고주 계약 화면과 대행사 광고주 상세 화면에서 동일하게 사용한다.
 *
 * @var bool        $agreed      동의 여부
 * @var string|null $agreedAtKst 동의 일시 (KST)
 * @var string|null $gabName     갑(광고대행사) 회사명
 * @var string|null $gabCharge   갑 담당자명
 * @var string      $eulName     을(광고주) 상호
 * @var string|null $eulBizNo    을 사업자번호
 * @var string|null $eulCharge   을 담당자명
 * @var bool        $readonly    true=조회 전용(동의 버튼 미노출)
 */
$readonly = $readonly ?? false;
?>
<div class="card" style="margin-bottom:24px;border-left:3px solid <?= $agreed ? '#1D9E75' : '#f59e0b' ?>;">
    <div class="card-header" style="display:flex;align-items:center;justify-content:space-between;">
        <span class="card-title">광고대행 표준계약서</span>
        <?php if ($agreed): ?>
            <span class="badge badge-success">동의 완료<?= $agreedAtKst !== null ? ' · ' . esc($agreedAtKst) : '' ?></span>
        <?php else: ?>
            <span class="badge badge-warning">동의 대기</span>
        <?php endif; ?>
    </div>
    <div class="card-body">
        <!-- 당사자 (갑/을) -->
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:20px;">
            <div style="border:1px solid var(--color-border);border-radius:var(--radius-sm);padding:14px;">
                <p style="font-weight:600;margin-bottom:8px;">갑 (광고대행사)</p>
                <p class="text-sm">회사명: <strong><?= esc($gabName ?: '담당 광고대행사') ?></strong></p>
                <?php if (!empty($gabCharge)): ?>
                    <p class="text-sm" style="color:var(--color-text-muted);">담당자: <?= esc($gabCharge) ?></p>
                <?php endif; ?>
            </div>
            <div style="border:1px solid var(--color-border);border-radius:var(--radius-sm);padding:14px;">
                <p style="font-weight:600;margin-bottom:8px;">을 (광고주)</p>
                <p class="text-sm">상호: <strong><?= esc($eulName) ?></strong></p>
                <?php if (!empty($eulBizNo)): ?>
                    <p class="text-sm" style="color:var(--color-text-muted);">사업자번호: <?= esc($eulBizNo) ?></p>
                <?php endif; ?>
                <?php if (!empty($eulCharge)): ?>
                    <p class="text-sm" style="color:var(--color-text-muted);">담당자: <?= esc($eulCharge) ?></p>
                <?php endif; ?>
            </div>
        </div>

        <!-- 계약 본문 -->
        <div style="max-height:320px;overflow-y:auto;border:1px solid var(--color-border);border-radius:var(--radius-sm);padding:18px;font-size:13px;line-height:1.8;color:var(--color-text);">
            <p style="font-weight:600;margin-bottom:8px;">제1조 (목적)</p>
            <p style="margin-bottom:14px;">본 계약은 갑(광고대행사)이 을(광고주)의 의료광고 대행 업무를 수행함에 있어 양 당사자의 권리와 의무를 정함을 목적으로 한다.</p>

            <p style="font-weight:600;margin-bottom:8px;">제2조 (광고 대행의 범위)</p>
            <p style="margin-bottom:14px;">갑은 을이 충전한 광고비 범위 내에서 캠페인 등록·운영·소재 관리 및 신청(DB) 수집 등 광고 대행 업무를 수행한다. 구체적인 광고 상품과 단가는 수주계약(충전) 건별로 정한다.</p>

            <p style="font-weight:600;margin-bottom:8px;">제3조 (광고비 및 정산)</p>
            <p style="margin-bottom:14px;">을은 광고 집행에 필요한 비용을 사전 충전 방식으로 예치하며, 유효한 이벤트 신청(DB) 발생 시 약정 단가가 예치금에서 차감(CPA)된다. 중복·결번 신청 또는 정당한 취소 건은 운영자 검토를 거쳐 차감액을 복원(환불)할 수 있다.</p>

            <p style="font-weight:600;margin-bottom:8px;">제4조 (환불)</p>
            <p style="margin-bottom:14px;">을이 신청 건을 중복·결번·취소로 처리하면 운영자에게 환불요청이 접수되며, 운영자가 승인할 경우 해당 차감액이 예치금 잔액으로 복원된다. 운영자가 거부할 경우 그 사유를 을에게 통지한다.</p>

            <p style="font-weight:600;margin-bottom:8px;">제5조 (개인정보 보호)</p>
            <p style="margin-bottom:14px;">양 당사자는 광고 집행 과정에서 수집된 신청자의 개인정보를 관련 법령에 따라 보호하며, 광고 대행 목적 외로 이용하지 아니한다.</p>

            <p style="font-weight:600;margin-bottom:8px;">제6조 (계약의 효력)</p>
            <p style="margin-bottom:0;">본 계약은 을이 본 계약서에 동의한 날로부터 효력이 발생하며, 동의 일시는 시스템에 기록된다.</p>
        </div>

        <div style="display:flex;align-items:center;justify-content:space-between;gap:16px;margin-top:18px;">
            <p class="text-sm" style="color:var(--color-text-muted);margin:0;">
                <?php if ($agreed): ?>
                    을(<?= esc($eulName) ?>)은(는) <strong><?= esc($agreedAtKst ?? '-') ?></strong>에 본 계약서에 동의하였습니다.
                <?php elseif ($readonly): ?>
                    광고주가 아직 계약서에 동의하지 않았습니다. 동의 후 광고 충전(수주계약)이 가능합니다.
                <?php else: ?>
                    계약에 동의해야 광고 충전(수주계약)을 신청할 수 있습니다.
                <?php endif; ?>
            </p>
            <?php if (!$readonly): ?>
                <?php if (!$agreed): ?>
                    <form action="/portal/contracts/agree" method="POST" onsubmit="return confirm('표준계약서 내용에 동의하시겠습니까?');" style="margin:0;">
                        <?= csrf_field() ?>
                        <button type="submit" class="btn btn-primary btn-sm" style="white-space:nowrap;">계약서에 동의</button>
                    </form>
                <?php else: ?>
                    <a href="/portal/contracts/orders/new" class="btn btn-primary btn-sm" style="white-space:nowrap;">+ 광고 충전 신청</a>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>
