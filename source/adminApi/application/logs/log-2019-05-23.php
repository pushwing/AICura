<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

ERROR - 2019-05-23 07:31:03 --> Query error: Unknown column 'ui.agencyCompanyChargeName' in 'field list' - Invalid query: SELECT `c`.*, `h`.`id` `hospital_id2`, `h`.`name`, `u`.`id` `user_id`, `u`.`username`, `u`.`email`, 
        `ct`.`hospital_phone`, `ct`.`hospital_phone`, `ct`.`user_username`, `ct`.`user_phone`, `ct`.`user_email`,
        group_concat(distinct hc.hospital_id) as network_id,
        ui.agencyCompanyName as uAgencyCompanyName, ui.agencyCompanyChargeName as uAgencyCompanyChargeName
        , ui.agencyCompanyChargePhone as uAgencyCompanyChargePhone, ui.agencyCompanyChargeEmail as uAgencyCompanyChargeEmail 
        , ui.agencyCompanyFeeRate as uAgencyCompanyFeeRate,  
        ui.hospitalChargeName as uHospitalChargeName,
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
         and c.id in(137,144)
        
        GROUP BY `c`.`id`
        having hospital_id2 is not null
        
ERROR - 2019-05-23 07:31:03 --> 404 Page Not Found: Faviconico/index
ERROR - 2019-05-23 07:32:14 --> Query error: Unknown column 'ui.agencyCompanyFeeRate' in 'field list' - Invalid query: SELECT `c`.*, `h`.`id` `hospital_id2`, `h`.`name`, `u`.`id` `user_id`, `u`.`username`, `u`.`email`, 
        `ct`.`hospital_phone`, `ct`.`hospital_phone`, `ct`.`user_username`, `ct`.`user_phone`, `ct`.`user_email`,
        group_concat(distinct hc.hospital_id) as network_id,
        ui.agencyCompanyName as uAgencyCompanyName, ui.agencyCompanyChargeName as uAgencyCompanyChargeName
        , ui.agencyCompanyChargePhone as uAgencyCompanyChargePhone, ui.agencyCompanyChargeEmail as uAgencyCompanyChargeEmail 
        , ui.agencyCompanyFeeRate as uAgencyCompanyFeeRate,  
        ui.hospitalChargeName as uHospitalChargeName,
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
         and c.id in(137,144)
        
        GROUP BY `c`.`id`
        having hospital_id2 is not null
        
ERROR - 2019-05-23 07:32:35 --> Query error: Table 'neo_goodoc_production.call_requests_back' doesn't exist - Invalid query: select p.*, cr.user_id from payments p
              left join call_requests_back cr on p.call_request_id=cr.id  
              where p.contract_id='137' 
              order by created_at
              
