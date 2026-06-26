<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

ERROR - 2019-05-15 02:33:15 --> Severity: Notice --> Undefined index: SERVER_NAME /Users/blumine/works/goodoc_v2/event/adminApi/application/hooks/preProcess.php 14
ERROR - 2019-05-15 02:33:15 --> Severity: Notice --> Undefined index: SERVER_NAME /Users/blumine/works/goodoc_v2/event/adminApi/application/hooks/preProcess.php 14
ERROR - 2019-05-15 02:33:15 --> Severity: Notice --> Undefined index: SERVER_NAME /Users/blumine/works/goodoc_v2/event/adminApi/application/hooks/preProcess.php 14
ERROR - 2019-05-15 02:33:15 --> Severity: Notice --> Undefined index: SERVER_NAME /Users/blumine/works/goodoc_v2/event/adminApi/application/hooks/preProcess.php 14
ERROR - 2019-05-15 02:33:15 --> Severity: Notice --> Undefined index: SERVER_NAME /Users/blumine/works/goodoc_v2/event/adminApi/application/hooks/preProcess.php 15
ERROR - 2019-05-15 02:33:15 --> Severity: Notice --> Undefined index: REQUEST_URI /Users/blumine/works/goodoc_v2/event/adminApi/application/hooks/preProcess.php 22
ERROR - 2019-05-15 02:33:15 --> Severity: Notice --> Undefined index: SERVER_NAME /Users/blumine/works/goodoc_v2/event/adminApi/application/hooks/preProcess.php 26
ERROR - 2019-05-15 02:33:15 --> Severity: Notice --> Undefined index: SERVER_NAME /Users/blumine/works/goodoc_v2/event/adminApi/application/hooks/preProcess.php 26
ERROR - 2019-05-15 02:33:15 --> Severity: Notice --> Undefined index: SERVER_NAME /Users/blumine/works/goodoc_v2/event/adminApi/application/hooks/preProcess.php 26
ERROR - 2019-05-15 02:33:15 --> Severity: Notice --> Undefined index: SERVER_NAME /Users/blumine/works/goodoc_v2/event/adminApi/application/hooks/preProcess.php 26
ERROR - 2019-05-15 02:33:15 --> Severity: Notice --> Undefined index: x-api-key /Users/blumine/works/goodoc_v2/event/adminApi/application/hooks/preProcess.php 27
ERROR - 2019-05-15 02:35:22 --> Severity: Notice --> Undefined index: REQUEST_URI /Users/blumine/works/goodoc_v2/event/adminApi/application/hooks/preProcess.php 22
ERROR - 2019-05-15 02:35:22 --> Severity: Notice --> Undefined index: REQUEST_URI /Users/blumine/works/goodoc_v2/event/adminApi/application/hooks/preProcess.php 22
ERROR - 2019-05-15 02:41:33 --> Query error: Table 'neo_goodoc_production.call_requests_back' doesn't exist - Invalid query: select p.*, cr.user_id from payments p
              left join call_requests_back cr on p.call_request_id=cr.id  
              where p.contract_id='55' 
              order by created_at
              
ERROR - 2019-05-15 02:42:44 --> Query error: Duplicate entry '55' for key 'PRIMARY' - Invalid query: INSERT INTO `contract` (`hospitalType`, `id`, `hospitalId`, `hospitalName`, `title`, `adType`, `adType2`, `manageUserId`, `agencyUserId`, `taxChargeName`, `taxBusinessNo`, `taxChargeEmail`, `hospitalChargeName`, `hospitalChargePhone`, `hospitalChargeEmail`, `agencyCompanyId`, `agencyCompanyName`, `regDate`, `contractDate`) VALUES (1, '55', '84391', '볼륨성형외과의원', '볼륨성형외과의원', 1, 1, '8', '8', '박선봉', '508-24-51289', 'sunnroses@naver.com', '박선봉 원장', '010-3418-4684', 'sunnroses@naver.com', '', '박선봉 원장', '2015-04-28 04:28:19', '2015-04-28 04:28:19')
