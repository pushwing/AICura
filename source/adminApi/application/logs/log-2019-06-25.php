<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

ERROR - 2019-06-25 00:38:21 --> Severity: Warning --> mysqli::query(): MySQL server has gone away /Users/blumine/works/goodoc_v2/event/adminApi/system/database/drivers/mysqli/mysqli_driver.php 305
ERROR - 2019-06-25 00:38:21 --> Severity: Warning --> mysqli::query(): Error reading result set's header /Users/blumine/works/goodoc_v2/event/adminApi/system/database/drivers/mysqli/mysqli_driver.php 305
ERROR - 2019-06-25 00:38:21 --> Query error: MySQL server has gone away - Invalid query: UPDATE `call_request` SET `status` = 1
WHERE `callRequestId` = '1055970'
ERROR - 2019-06-25 00:38:21 --> Severity: Warning --> Cannot modify header information - headers already sent by (output started at /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/MartinTest.php:82) /Users/blumine/works/goodoc_v2/event/adminApi/system/core/Common.php 570
ERROR - 2019-06-25 02:03:53 --> Severity: Notice --> Undefined index: result /Users/blumine/works/goodoc_v2/event/adminApi/application/libraries/ApiSrc/HospitalApi.php 209
ERROR - 2019-06-25 02:03:53 --> Severity: Notice --> Undefined index: hospitals /Users/blumine/works/goodoc_v2/event/adminApi/application/libraries/ApiSrc/HospitalApi.php 118
ERROR - 2019-06-25 02:03:53 --> Query error: You have an error in your SQL syntax; check the manual that corresponds to your MySQL server version for the right syntax to use near ') IS NULL
AND  `ias`.`isAdmin` = 2 and `ias`.`status` = 1
GROUP BY `ias`.`id`' at line 4 - Invalid query: SELECT *
FROM `inspecting_ads` `ias`
JOIN `ads` ON `ias`.`adsId`=`ads`.`id`
WHERE () IS NULL
AND  `ias`.`isAdmin` = 2 and `ias`.`status` = 1
GROUP BY `ias`.`id`
ERROR - 2019-06-25 02:08:21 --> Severity: Notice --> Undefined index: result /Users/blumine/works/goodoc_v2/event/adminApi/application/libraries/ApiSrc/HospitalApi.php 209
ERROR - 2019-06-25 02:08:48 --> Severity: Notice --> Undefined index: result /Users/blumine/works/goodoc_v2/event/adminApi/application/libraries/ApiSrc/HospitalApi.php 209
ERROR - 2019-06-25 02:08:52 --> Severity: Notice --> Undefined index: result /Users/blumine/works/goodoc_v2/event/adminApi/application/libraries/ApiSrc/HospitalApi.php 209
