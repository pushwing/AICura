<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

ERROR - 2019-09-19 01:37:22 --> Severity: Notice --> Undefined index: region /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/MakeEventS3Data.php 83
ERROR - 2019-09-19 01:37:22 --> Severity: Notice --> Undefined index: hospitalId /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/MakeEventS3Data.php 155
ERROR - 2019-09-19 01:37:23 --> Severity: Notice --> Undefined index: hospitalId /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/MakeEventS3Data.php 199
ERROR - 2019-09-19 01:37:23 --> Severity: Notice --> Undefined index: hospitalId /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/MakeEventS3Data.php 201
ERROR - 2019-09-19 01:37:23 --> Severity: Notice --> Undefined index: hospitalId /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/MakeEventS3Data.php 216
ERROR - 2019-09-19 01:37:23 --> Severity: Notice --> Undefined index: addr /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/MakeEventS3Data.php 218
ERROR - 2019-09-19 01:41:11 --> Query error: Column 'id' in where clause is ambiguous - Invalid query: SELECT `ads`.`id`, `ads`.`isLive`, `ads`.`vHospitalId` as `hospitalId`, `ec`.`title` `categoryName`
FROM `ads`
LEFT JOIN `event_categories` `ec` ON `ads`.`vCategory`=`ec`.`id`
WHERE `channel` = 1
AND `id` = 14626
ERROR - 2019-09-19 01:43:04 --> Severity: Notice --> Undefined index: region /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/MakeEventS3Data.php 83
ERROR - 2019-09-19 01:43:04 --> Severity: Notice --> Undefined index: hospitalId /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/MakeEventS3Data.php 155
