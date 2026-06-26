<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

ERROR - 2019-05-04 03:04:46 --> Severity: Warning --> mysqli::query(): MySQL server has gone away /Users/blumine/works/goodoc_v2/event/adminApi/system/database/drivers/mysqli/mysqli_driver.php 305
ERROR - 2019-05-04 03:04:46 --> Severity: Warning --> mysqli::query(): Error reading result set's header /Users/blumine/works/goodoc_v2/event/adminApi/system/database/drivers/mysqli/mysqli_driver.php 305
ERROR - 2019-05-04 03:04:46 --> Query error: MySQL server has gone away - Invalid query: select p.*, cr.user_id from payments p
              left join call_requests cr on p.call_request_id=cr.id  
              where p.contract_id='100' 
              order by created_at
              
ERROR - 2019-05-04 03:04:46 --> Severity: Warning --> Cannot modify header information - headers already sent by (output started at /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/DataMigration.php:243) /Users/blumine/works/goodoc_v2/event/adminApi/system/core/Common.php 570
ERROR - 2019-05-04 03:30:30 --> Severity: Warning --> mysqli::query(): MySQL server has gone away /Users/blumine/works/goodoc_v2/event/adminApi/system/database/drivers/mysqli/mysqli_driver.php 305
ERROR - 2019-05-04 03:30:30 --> Severity: Warning --> mysqli::query(): Error reading result set's header /Users/blumine/works/goodoc_v2/event/adminApi/system/database/drivers/mysqli/mysqli_driver.php 305
ERROR - 2019-05-04 03:30:30 --> Query error: MySQL server has gone away - Invalid query: select p.*, cr.user_id from payments p
              left join call_requests cr on p.call_request_id=cr.id  
              where p.contract_id='101' 
              order by created_at
              
ERROR - 2019-05-04 03:30:30 --> Severity: Warning --> Cannot modify header information - headers already sent by (output started at /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/DataMigration.php:243) /Users/blumine/works/goodoc_v2/event/adminApi/system/core/Common.php 570
