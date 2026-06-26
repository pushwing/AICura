

    <form name="order_form" id="order_form" method="post" action="<?php echo $url?>">
        <input type="hidden" name="PHash" value="">
        <input type="hidden" name="PData" value="">
        <input type="hidden" name="PStateCd" value="">
        <input type="hidden" name="POrderId" value="">
        <!-- 결과처리를 위한 파라미터 -->

        <input type="hidden" name="PNoteUrl" value="<?php echo SITE_URL?>/api/v1.0/payment/update"> <!--db처리 url 예)http://www.***.com/rnoti.jsp -->
        <input type="hidden" name="PNextPUrl" value="<?php echo SITE_URL?>/api/v1.0/payment/process"> <!--성공,실패 화면처리 예)http://www.***.com/pay_rcv.jsp -->
        <input type="hidden" name="PCancPUrl" value="<?php echo SITE_URL?>/api/v1.0/payment/cancel"> <!-- 결제창을 닫은 경우 화면처리 예)http://www.***.com/pay_rcv.jsp -->

        <input type="hidden" name="PEmail" value="<?php echo $hospitalEmail?>"> <!-- 결제자 e-mail -->
        <input type="hidden" name="PPhone" value="<?php echo $hospitalPhone?>"> <!-- 결제자 연락처 -->
        <input type="hidden" name="POid" value="<?php echo $paymentId?>">
        <input type="hidden" name="PGoods" value="<?php echo urlencode($contractTitle)?>"> <!-- 상품명 -->
        <input type="hidden" name="PNoti" value="<?php echo $paymentId?>"> <!-- 회원사에서 이용할 수 있는 여유필드 -->
        <input type="hidden" name="PMname" value="<?php echo urlencode('케어랩스')?>"> <!-- 회원사 한글명 -->
        <input type="hidden" name="PUname" value="<?php echo urlencode($hospitalName)?>"> <!-- 결제자 이름-->
        <input type="hidden" name="PBname" value="<?php echo urlencode('주식회사케어랩스')?>"> <!-- 계좌이체/가상계좌입금시 고객통장에 찍힐 통장인자명 -->
        <input type="hidden" name="PVtransDt" value="<?php echo $transDate?>"> <!-- 가상계좌입금마감일 : 가상계좌에서만 사용합니다 예)20120101235959  -->
        <input type="hidden" name="PMid" value="<?php echo ORDER_ID?>"> <!-- 가맹점 사용자 ID -->
        <input type="hidden" name="PAmt" value="<?php echo $amount?>"> <!-- 가맹점 사용자 ID -->
    </form>

    <script type="text/javascript">
        this.document.getElementById("order_form").submit();
    </script>