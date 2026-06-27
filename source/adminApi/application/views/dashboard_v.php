<?php
//폼검증 에러 표출
if(validation_errors())
{
    echo '<div id = "error"><h1>'.validation_errors().'</h1></div>';
}
?>

<form name = "form" id = "form" method = "post" action = "">
    날짜 : <input type = "text" name = "date" id = "date" value = "<?php echo (set_value('date'))?set_value('date'):date("Y-m-d"); ?>" /> 2017-07-24 형태

    <input type = "submit" value = "조회" />
</form>

<?php
if(isset($result) == TRUE)
{
    echo '후기 '.count($result) .' 건, 사진 : '.$cnt.' 건<br><br>';
    echo "<table width='98%' border='1'  cellpadding=\"5\" cellspacing=\"0\"  style=\"border-collapse:collapse; border:1px gray solid;\">
        <tr>
            <td width='80px'>후기번호</td>
            <td width='80px'>구분</td>
            <td width='150px'>작성자<br>날짜</td>
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
            <td><a href="/dashBoardEvent/blindList2/<?php echo $item['id'];?>" target="_blank"><?php echo $item['id'];?></a></td>
            <td><?php echo ($item['type'] == '1')?'이벤트':'병원';?><br><?php echo $item['hName']?>(<?php echo $item['targetId']?>)</td>
            <td><?php echo $item['userName']?><br><?php echo $item['regDate']?></td>
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