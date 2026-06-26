<script
        src="https://code.jquery.com/jquery-3.3.1.min.js"
        integrity="sha256-FgpCb/KJQlLNfOu91ta32o/NMZxltwRo8QtmkMRdAu8="
        crossorigin="anonymous"></script>
<?php
//폼검증 에러 표출
if(@validation_errors())
{
    echo '<div id = "error"><h1>'.@validation_errors().'</h1></div>';
}


//var_dump( $this->session->userdata());
?>
삭제된 후기 포함한 후기 리스트<br><br>
<form name = "form" id = "form" method = "post" action = "">
    검색어 : <input type = "text" id="searchName" name = "searchName" value = "<?php echo (set_value('searchName'))?set_value('searchName'):''; ?>" />
후기번호, 이벤트번호, 병원번호, 이름, 이메일, 내용 중에서 검색<br>
    <input type = "submit" id="checkVal" value = "검색" />
</form>

<?php
if(isset($result) == TRUE)
{
    echo '후기 '.count($result) .' 건, 사진 : '.$cnt.' 건<br><br>';
    echo "<table width='98%' border='1'  cellpadding=\"5\" cellspacing=\"0\"  style=\"border-collapse:collapse; border:1px gray solid;\">
        <tr>
            <td width='80px'>후기번호<br>삭제여부</td>
            <td width='80px'>구분</td>
            <td width='150px'>작성자<br>이메일<br>날짜</td>
            <td>내용<br>점수<br>설문내용</td>
            <td>이미지</td>
        </tr>  
        ";
    $i=1;
    foreach ($result as $item)
    {
        if($item['imgName'] == null)
        {
            $expCnt = 0;
        }
        else
        {
            $exp = explode(',', $item['imgName']);
            $expCnt = count($exp);
        }

?>
        <tr>
            <td><?php echo $item['id'];?><br><?php echo ($item['isDelete'] == '1')?'<font color=red>삭제</font>':'<a href="#" nums="'.$item['id'].'" id="blindAction">삭제하기</a>';?></td>
            <td><?php echo ($item['type'] == '1')?'이벤트':'병원';?><br><?php echo $item['hName']?>(<?php echo $item['targetId']?>)</td>
            <td><?php echo $item['userName']?><br><?php echo $item['userEmail']?><br><?php echo $item['regDate']?></td>
            <td>내용 : <?php echo urldecode($item['contents'])?><br>진료 : <?php echo $item['rate1']?>, 의료진 : <?php echo $item['rate2']?>, 시설 : <?php echo $item['rate3']?><br>설문내용 : <?php echo $item['surveyType']?>/<?php echo $item['surveyAll']?></td>
            <td>
                <?php
                if($expCnt > 0)
                {
                    foreach ($exp as $it)
                    {
                        echo '<img src="'.$it.'" width="400px"><br>';
                    }
                }
                ?>
            </td>
        </tr>
<?php
        $i++;
    }

    echo "</table>";

}
?>

<script>
    var userID = "<?php echo $this->session->userdata('users_id')?>";
    $("#blindAction").click(
        function () {
            //alert($(this).attr('nums'));
            $.ajax({
                type: "POST",
                url : '<?php echo SITE_URL?>/dashBoardEvent/blindAction',
                data: {"users_id":userID, "blindId":$(this).attr('nums')},
                success : function (data) {
                    if (data) {
                        alert("임시삭제처리하였습니다.");
                    } else {
                        alert("임시삭제처리 실패");
                    }
                }
            });
        }
    )

</script>
