<?php
/**
 * Created by PhpStorm.
 * User: blumine
 * Date: 10/10/2018
 * Time: 7:01 PM
 */
?>
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-type" content="text/html; charset=utf-8"/>
    <meta http-equiv="X-UA-Compatible" content="IE=Edge">
    <meta name="viewport" content="width=device-width,initial-scale=1.0,minimum-scale=1.0,maximum-scale=1.0">
    <title>S'Pay 결제페이지</title>
    <?php
        if($_SERVER['HTTP_HOST'] == 'ev.com' or $_SERVER['HTTP_HOST'] == 'devevent.goodoc.kr') {
            echo '<link rel="stylesheet" type="text/css" href="https://asset-dev.goodoc.kr/coffee/v_bank_success/v_bank_success.min.css" />';
        } else if($_SERVER['HTTP_HOST'] == 'stagingevent.goodoc.kr') {
            echo '<link rel="stylesheet" type="text/css" href="https://asset-staging.goodoc.kr/coffee/v_bank_success/v_bank_success.min.css" />';
        } else if($_SERVER['HTTP_HOST'] == 'event.goodoc.kr') {
            echo '<link rel="stylesheet" type="text/css" href="https://asset.goodoc.kr/coffee/v_bank_success/v_bank_success.min.css" />';
        }
    ?>
</head>
<body>
<div id="spay_container"></div>
<?php
    if($_SERVER['HTTP_HOST'] == 'ev.com' or $_SERVER['HTTP_HOST'] == 'devevent.goodoc.kr') {
        echo '<script src="https://asset-dev.goodoc.kr/coffee/v_bank_success/v_bank_success.min.js"></script>';
    } else if($_SERVER['HTTP_HOST'] == 'stagingevent.goodoc.kr') {
        echo '<script src="https://asset-staging.goodoc.kr/coffee/v_bank_success/v_bank_success.min.js"></script>';
    } else if($_SERVER['HTTP_HOST'] == 'event.goodoc.kr') {
        echo '<script src="https://asset.goodoc.kr/coffee/v_bank_success/v_bank_success.min.js"></script>';
    }
?>
</body>
</html>