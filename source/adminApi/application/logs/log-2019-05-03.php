<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

ERROR - 2019-05-03 07:25:03 --> Severity: Warning --> mysqli::real_connect(): (HY000/2002): Operation timed out /Users/blumine/works/goodoc_v2/event/adminApi/system/database/drivers/mysqli/mysqli_driver.php 201
ERROR - 2019-05-03 07:25:03 --> Unable to connect to the database
ERROR - 2019-05-03 07:25:03 --> 404 Page Not Found: Faviconico/index
ERROR - 2019-05-03 07:25:53 --> Query error: Table 'goodoc_staging.updateInfo' doesn't exist - Invalid query: SELECT `c`.*, `h`.`id` `hospital_id2`, `h`.`name`, `u`.`id` `user_id`, `u`.`username`, `u`.`email`, 
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
         and c.id = 3
        GROUP BY `c`.`id`
        having hospital_id2 is not null
        
ERROR - 2019-05-03 12:17:31 --> Severity: Warning --> mysqli::real_connect(): (HY000/2002): Operation timed out /Users/blumine/works/goodoc_v2/event/adminApi/system/database/drivers/mysqli/mysqli_driver.php 201
ERROR - 2019-05-03 12:17:31 --> Unable to connect to the database
ERROR - 2019-05-03 14:01:05 --> Severity: Warning --> mysqli::query(): MySQL server has gone away /Users/blumine/works/goodoc_v2/event/adminApi/system/database/drivers/mysqli/mysqli_driver.php 305
ERROR - 2019-05-03 14:01:05 --> Severity: Warning --> mysqli::query(): Error reading result set's header /Users/blumine/works/goodoc_v2/event/adminApi/system/database/drivers/mysqli/mysqli_driver.php 305
ERROR - 2019-05-03 14:01:05 --> Query error: MySQL server has gone away - Invalid query: select p.*, cr.user_id from payments p
              left join call_requests cr on p.call_request_id=cr.id  
              where p.contract_id='100' 
              order by created_at
              
ERROR - 2019-05-03 14:01:05 --> Severity: Warning --> Cannot modify header information - headers already sent by (output started at /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/DataMigration.php:243) /Users/blumine/works/goodoc_v2/event/adminApi/system/core/Common.php 570
ERROR - 2019-05-03 16:50:16 --> Query error: Duplicate entry '256' for key 'PRIMARY' - Invalid query: INSERT INTO `contract` (`hospitalType`, `id`, `hospitalId`, `hospitalName`, `title`, `adType`, `adType2`, `manageUserId`, `agencyUserId`, `taxChargeName`, `taxBusinessNo`, `taxChargeEmail`, `hospitalChargeName`, `hospitalChargePhone`, `hospitalChargeEmail`, `agencyCompanyId`, `agencyCompanyName`, `regDate`, `contractDate`) VALUES (1, '256', '155509', '퍼스티지치과의원', '퍼스티지치과의원', 1, 1, '8', '8', NULL, '', NULL, NULL, NULL, NULL, '', '', '2015-08-13 09:28:43', '2015-08-13 09:28:43')
