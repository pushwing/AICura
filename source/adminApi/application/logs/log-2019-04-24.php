<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

ERROR - 2019-04-24 09:18:03 --> Severity: Notice --> Undefined property: CronDaily::$replicator_m /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/CronDaily.php 354
ERROR - 2019-04-24 09:18:03 --> 
        Exception of type \'Error\' occurred with Message: Call to a member function send() on null in File /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/CronDaily.php at Line 354
        
 Backtrace 
.#0 /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/CronDaily.php(80): CronDaily->repli('2655')
#1 /Users/blumine/works/goodoc_v2/event/adminApi/system/core/CodeIgniter.php(532): CronDaily->extendEventDate()
#2 /Users/blumine/works/goodoc_v2/event/adminApi/index.php(327): require_once('/Users/blumine/...')
#3 {main}
    
ERROR - 2019-04-24 09:18:03 --> Severity: error --> Exception: Call to a member function send() on null /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/CronDaily.php 354
ERROR - 2019-04-24 09:18:03 --> Severity: Warning --> Cannot modify header information - headers already sent by (output started at /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/CronDaily.php:65) /Users/blumine/works/goodoc_v2/event/adminApi/system/core/Common.php 570
ERROR - 2019-04-24 09:18:04 --> 404 Page Not Found: Faviconico/index
ERROR - 2019-04-24 09:24:19 --> Severity: Notice --> Undefined property: CronDaily::$common_m /Users/blumine/works/goodoc_v2/event/adminApi/system/core/Model.php 73
ERROR - 2019-04-24 09:24:19 --> 
        Exception of type \'Error\' occurred with Message: Call to a member function isInTrans() on null in File /Users/blumine/works/goodoc_v2/event/adminApi/application/models/Ads_m.php at Line 2929
        
 Backtrace 
.#0 /Users/blumine/works/goodoc_v2/event/adminApi/application/models/Ads_m.php(2788): Ads_m->getHistoryListSec(Array)
#1 /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/CronDaily.php(106): Ads_m->gethistoryMerge(Array)
#2 /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/CronDaily.php(113): CronDaily->{closure}(Array, '2019-04-24 09:2...')
#3 /Users/blumine/works/goodoc_v2/event/adminApi/system/core/CodeIgniter.php(532): CronDaily->extendEventDate()
#4 /Users/blumine/works/goodoc_v2/event/adminApi/index.php(327): require_once('/Users/blumine/...')
#5 {main}
    
ERROR - 2019-04-24 09:24:20 --> Severity: error --> Exception: Call to a member function isInTrans() on null /Users/blumine/works/goodoc_v2/event/adminApi/application/models/Ads_m.php 2929
ERROR - 2019-04-24 09:24:20 --> Severity: Warning --> Cannot modify header information - headers already sent by (output started at /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/CronDaily.php:66) /Users/blumine/works/goodoc_v2/event/adminApi/system/core/Common.php 570
ERROR - 2019-04-24 09:52:03 --> Severity: Notice --> Undefined index: users_id /Users/blumine/works/goodoc_v2/event/adminApi/application/models/Ads_m.php 1515
ERROR - 2019-04-24 09:52:04 --> Query error: Column 'userId' cannot be null - Invalid query: INSERT INTO `inspecting_ads` (`status`, `adStatus`, `regDate`, `hospitalId`, `historyId`, `adsId`, `userId`, `prevAdStatus`, `prevSubAdStatus`, `agencyUserId`, `adsMainMapId`, `isAdmin`, `inspectUserId`, `inspectDate`) VALUES (2, 2, '2019-04-24 09:52:03', '33928', '20095', '12912', NULL, '3', '', NULL, 20371, 1, NULL, '2019-04-24 09:52:03')
ERROR - 2019-04-24 09:52:04 --> Severity: Warning --> Cannot modify header information - headers already sent by (output started at /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/CronDaily.php:134) /Users/blumine/works/goodoc_v2/event/adminApi/system/core/Common.php 570
