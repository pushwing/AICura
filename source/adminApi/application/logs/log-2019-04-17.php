<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

ERROR - 2019-04-17 10:42:43 --> Query error: Duplicate entry '31' for key 'PRIMARY' - Invalid query: INSERT INTO `contract` (`hospitalType`, `id`, `hospitalId`, `hospitalName`, `contractDate`, `title`, `adType`, `adType2`, `agencyUserId`, `manageUserId`, `taxChargeName`, `taxBusinessNo`, `taxChargeEmail`, `hospitalChargeName`, `hospitalChargePhone`, `hospitalChargeEmail`, `agencyCompanyId`, `agencyCompanyName`, `regDate`) VALUES (1, '31', '85809', '하늘안과의원 부산점', '2015-05-06 18:41:14', '하늘안과의원', 1, 1, '5', '', NULL, '', NULL, NULL, NULL, NULL, '', '', '2015-05-06 18:41:14')
ERROR - 2019-04-17 10:42:43 --> Severity: Warning --> Cannot modify header information - headers already sent by (output started at /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/DataMigration.php:467) /Users/blumine/works/goodoc_v2/event/adminApi/system/core/Common.php 570
ERROR - 2019-04-17 13:11:18 --> Severity: Warning --> mysqli::query(): MySQL server has gone away /Users/blumine/works/goodoc_v2/event/adminApi/system/database/drivers/mysqli/mysqli_driver.php 305
ERROR - 2019-04-17 13:11:18 --> Severity: Warning --> mysqli::query(): Error reading result set's header /Users/blumine/works/goodoc_v2/event/adminApi/system/database/drivers/mysqli/mysqli_driver.php 305
ERROR - 2019-04-17 13:11:18 --> Query error: MySQL server has gone away - Invalid query: select p.*, cr.user_id from payments p
              left join call_requests cr on p.call_request_id=cr.id  
              where p.contract_id='100' 
              order by created_at
              
ERROR - 2019-04-17 13:11:18 --> Severity: Warning --> Cannot modify header information - headers already sent by (output started at /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/DataMigration.php:467) /Users/blumine/works/goodoc_v2/event/adminApi/system/core/Common.php 570
ERROR - 2019-04-17 13:26:30 --> Severity: Warning --> mysqli::query(): MySQL server has gone away /Users/blumine/works/goodoc_v2/event/adminApi/system/database/drivers/mysqli/mysqli_driver.php 305
ERROR - 2019-04-17 13:26:30 --> Severity: Warning --> mysqli::query(): Error reading result set's header /Users/blumine/works/goodoc_v2/event/adminApi/system/database/drivers/mysqli/mysqli_driver.php 305
ERROR - 2019-04-17 13:26:30 --> Query error: MySQL server has gone away - Invalid query: select p.*, cr.user_id from payments p
              left join call_requests cr on p.call_request_id=cr.id  
              where p.contract_id='101' 
              order by created_at
              
ERROR - 2019-04-17 13:26:30 --> Severity: Warning --> Cannot modify header information - headers already sent by (output started at /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/DataMigration.php:468) /Users/blumine/works/goodoc_v2/event/adminApi/system/core/Common.php 570
