<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

ERROR - 2019-05-08 01:19:03 --> Severity: Warning --> mysqli::query(): MySQL server has gone away /Users/blumine/works/goodoc_v2/event/adminApi/system/database/drivers/mysqli/mysqli_driver.php 305
ERROR - 2019-05-08 01:19:03 --> Severity: Warning --> mysqli::query(): Error reading result set's header /Users/blumine/works/goodoc_v2/event/adminApi/system/database/drivers/mysqli/mysqli_driver.php 305
ERROR - 2019-05-08 01:19:03 --> Query error: MySQL server has gone away - Invalid query: select p.*, cr.user_id from payments p
              left join call_requests cr on p.call_request_id=cr.id  
              where p.contract_id='101' 
              order by payment_type, created_at
              
ERROR - 2019-05-08 01:19:03 --> Severity: Warning --> Cannot modify header information - headers already sent by (output started at /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/DataMigration.php:243) /Users/blumine/works/goodoc_v2/event/adminApi/system/core/Common.php 570
ERROR - 2019-05-08 02:35:05 --> Query error: Duplicate entry '333' for key 'PRIMARY' - Invalid query: INSERT INTO `contract` (`hospitalType`, `id`, `hospitalId`, `hospitalName`, `title`, `adType`, `adType2`, `manageUserId`, `agencyUserId`, `taxChargeName`, `taxBusinessNo`, `taxChargeEmail`, `hospitalChargeName`, `hospitalChargePhone`, `hospitalChargeEmail`, `agencyCompanyId`, `agencyCompanyName`, `regDate`, `contractDate`) VALUES (1, '333', '164432', '로담한의원 천안아산점', '로담한의원 천안점', 1, 1, '5', '5', '임태경', '855-56-00085', 'hltkn1123@naver.com', '김라주 대리(이스크)', NULL, NULL, '', '김라주 대리(이스크)', '2016-03-07 08:16:14', '2016-03-07 08:16:14')
ERROR - 2019-05-08 02:35:05 --> Severity: Warning --> Cannot modify header information - headers already sent by (output started at /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/DataMigration.php:233) /Users/blumine/works/goodoc_v2/event/adminApi/system/core/Common.php 570
ERROR - 2019-05-08 03:27:27 --> Severity: Warning --> mysqli::query(): MySQL server has gone away /Users/blumine/works/goodoc_v2/event/adminApi/system/database/drivers/mysqli/mysqli_driver.php 305
ERROR - 2019-05-08 03:27:27 --> Severity: Warning --> mysqli::query(): Error reading result set's header /Users/blumine/works/goodoc_v2/event/adminApi/system/database/drivers/mysqli/mysqli_driver.php 305
ERROR - 2019-05-08 03:27:27 --> Query error: MySQL server has gone away - Invalid query: select p.*, cr.user_id from payments p
              left join call_requests cr on p.call_request_id=cr.id  
              where p.contract_id='381' 
              order by payment_type, created_at
              
ERROR - 2019-05-08 03:27:27 --> Severity: Warning --> Cannot modify header information - headers already sent by (output started at /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/DataMigration.php:304) /Users/blumine/works/goodoc_v2/event/adminApi/system/core/Common.php 570
ERROR - 2019-05-08 05:24:32 --> Query error: Duplicate entry '380' for key 'PRIMARY' - Invalid query: INSERT INTO `contract` (`hospitalType`, `id`, `hospitalId`, `hospitalName`, `title`, `adType`, `adType2`, `manageUserId`, `agencyUserId`, `taxChargeName`, `taxBusinessNo`, `taxChargeEmail`, `hospitalChargeName`, `hospitalChargePhone`, `hospitalChargeEmail`, `agencyCompanyId`, `agencyCompanyName`, `regDate`, `contractDate`) VALUES (1, '380', '164529', '예뻐진의원', '예뻐진의원', 1, 1, '8', '8', '채민호', '214-14-54761', 'david0334@naver.com', '정지만 이사', '010-3214-6699', NULL, '', '정지만 이사', '2016-04-26 02:10:30', '2016-04-26 02:10:30')
ERROR - 2019-05-08 05:57:59 --> Query error: FUNCTION c.id does not exist. Check the 'Function Name Parsing and Resolution' section in the Reference Manual - Invalid query: SELECT `c`.*, `h`.`id` `hospital_id2`, `h`.`name`, `u`.`id` `user_id`, `u`.`username`, `u`.`email`, 
        `ct`.`hospital_phone`, `ct`.`hospital_phone`, `ct`.`user_username`, `ct`.`user_phone`, `ct`.`user_email`,
        group_concat(distinct hc.hospital_id) as network_id,
        ui.agencyCompanyName as uAgencyCompanyName, ui.hospitalChargeName as uHospitalChargeName,
        ui.hospitalChargePhone as uHospitalChargePhone, ui.hospitalChargeEmail as uHospitalChargeEmail, 
        ui.taxChargeName as uTaxChargeName, ui.taxChargeEmail as uTaxChargeEmail, ui.taxBusinessNo as uTaxBusinessNo,
        ui.manageUserId as uManageUserId
        FROM `contracts` `c`
        LEFT JOIN `hospital_contracts` `hc` ON `c`.`id`=`hc`.`contract_id`
        LEFT JOIN `hospitals` `h` ON `hc`.`hospital_id`=`h`.`id`
        LEFT JOIN `user_hospital_departments` `uhd` ON `hc`.`hospital_id`=`uhd`.`hospital_id`
        LEFT JOIN `users` `u` ON `uhd`.`user_id`=`u`.`id`
        LEFT JOIN `contacts` `ct` ON `u`.`id`=`ct`.`user_id`
        LEFT JOIN `updateInfo` `ui` ON `c`.`id`=`ui`.`contractId`
        WHERE `c`.`title` NOT LIKE '%영구종료%' ESCAPE '!'
        and c.is_visible=1 
        and c.id != 1
        and c.id (27,47,55,57,60,68,71,77,81,82,85,87,95,96,97,98,101,105,108,109,115,116,117,118,122,123,136,234,238,242,267,270,284,374,380,416,527,22)
        GROUP BY `c`.`id`
        having hospital_id2 is not null
        
ERROR - 2019-05-08 06:14:48 --> Severity: Warning --> mysqli::query(): MySQL server has gone away /Users/blumine/works/goodoc_v2/event/adminApi/system/database/drivers/mysqli/mysqli_driver.php 305
ERROR - 2019-05-08 06:14:48 --> Severity: Warning --> mysqli::query(): Error reading result set's header /Users/blumine/works/goodoc_v2/event/adminApi/system/database/drivers/mysqli/mysqli_driver.php 305
ERROR - 2019-05-08 06:14:48 --> Query error: MySQL server has gone away - Invalid query: SELECT `c`.*, `h`.`id` `hospital_id2`, `h`.`name`, `u`.`id` `user_id`, `u`.`username`, `u`.`email`, 
        `ct`.`hospital_phone`, `ct`.`hospital_phone`, `ct`.`user_username`, `ct`.`user_phone`, `ct`.`user_email`,
        group_concat(distinct hc.hospital_id) as network_id,
        ui.agencyCompanyName as uAgencyCompanyName, ui.hospitalChargeName as uHospitalChargeName,
        ui.hospitalChargePhone as uHospitalChargePhone, ui.hospitalChargeEmail as uHospitalChargeEmail, 
        ui.taxChargeName as uTaxChargeName, ui.taxChargeEmail as uTaxChargeEmail, ui.taxBusinessNo as uTaxBusinessNo,
        ui.manageUserId as uManageUserId
        FROM `contracts` `c`
        LEFT JOIN `hospital_contracts` `hc` ON `c`.`id`=`hc`.`contract_id`
        LEFT JOIN `hospitals` `h` ON `hc`.`hospital_id`=`h`.`id`
        LEFT JOIN `user_hospital_departments` `uhd` ON `hc`.`hospital_id`=`uhd`.`hospital_id`
        LEFT JOIN `users` `u` ON `uhd`.`user_id`=`u`.`id`
        LEFT JOIN `contacts` `ct` ON `u`.`id`=`ct`.`user_id`
        LEFT JOIN `updateInfo` `ui` ON `c`.`id`=`ui`.`contractId`
        WHERE `c`.`title` NOT LIKE '%영구종료%' ESCAPE '!'
        and c.is_visible=1 
        and c.id != 1
        and c.id in (27,47,55,57,60,68,71,77,81,82,85,87,95,96,97,98,101,105,108,109,115,116,117,118,122,123,136,234,238,242,267,270,284,374,380,416,527,22)
        GROUP BY `c`.`id`
        having hospital_id2 is not null
        
ERROR - 2019-05-08 09:31:25 --> Severity: Notice --> Undefined index: adsId /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/v10/Advertiser.php 234
ERROR - 2019-05-08 09:31:32 --> Severity: Notice --> Undefined index: adsId /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/v10/Advertiser.php 234
ERROR - 2019-05-08 09:34:33 --> Query error: Unknown column 'pt.resultCode' in 'field list' - Invalid query: SELECT `c`.*, `co`.`contractDate` as `contractOrderDate`, `co`.`contractType`, `co`.`adPrice`, `co`.`depositDate`, `pt`.`resultCode` `paymentStatus`, `co`.`taxIssueRequestDate`, `co`.`taxIssueDate`, (select group_concat(distinct id, '|', adTitle separator '>>>') from ads where contractId=c.id) adsId
FROM `contract` `c`
JOIN `contract_order_connect` `coc` ON `c`.`id`=`coc`.`contractId`
JOIN `contract_order` `co` ON `coc`.`contractOrderId`=`co`.`id`
WHERE `co`.`id` = '1833'
GROUP BY `c`.`id`
