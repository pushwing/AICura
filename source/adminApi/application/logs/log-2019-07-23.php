<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

ERROR - 2019-07-23 07:55:43 --> Severity: Notice --> Undefined property: MartinTest::$common_m /Users/blumine/works/goodoc_v2/event/adminApi/system/core/Model.php 73
ERROR - 2019-07-23 07:55:43 --> 
        Exception of type \'Error\' occurred with Message: Call to a member function isInTrans() on null in File /Users/blumine/works/goodoc_v2/event/adminApi/application/models/Ads_m.php at Line 2995
        
 Backtrace 
.#0 /Users/blumine/works/goodoc_v2/event/adminApi/application/models/Ads_m.php(2845): Ads_m->getHistoryListSec(Array)
#1 /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/MartinTest.php(488): Ads_m->gethistoryMerge(Array)
#2 /Users/blumine/works/goodoc_v2/event/adminApi/system/core/CodeIgniter.php(532): MartinTest->checkV8()
#3 /Users/blumine/works/goodoc_v2/event/adminApi/index.php(341): require_once('/Users/blumine/...')
#4 {main}
    
ERROR - 2019-07-23 07:55:43 --> Severity: error --> Exception: Call to a member function isInTrans() on null /Users/blumine/works/goodoc_v2/event/adminApi/application/models/Ads_m.php 2995
ERROR - 2019-07-23 07:56:20 --> 
        Exception of type \'Error\' occurred with Message: Call to a member function query() on null in File /Users/blumine/works/goodoc_v2/event/adminApi/application/models/Common_m.php at Line 33
        
 Backtrace 
.#0 /Users/blumine/works/goodoc_v2/event/adminApi/application/models/Ads_m.php(2995): Common_m->isInTrans()
#1 /Users/blumine/works/goodoc_v2/event/adminApi/application/models/Ads_m.php(2845): Ads_m->getHistoryListSec(Array)
#2 /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/MartinTest.php(488): Ads_m->gethistoryMerge(Array)
#3 /Users/blumine/works/goodoc_v2/event/adminApi/system/core/CodeIgniter.php(532): MartinTest->checkV8()
#4 /Users/blumine/works/goodoc_v2/event/adminApi/index.php(341): require_once('/Users/blumine/...')
#5 {main}
    
ERROR - 2019-07-23 07:56:21 --> Severity: error --> Exception: Call to a member function query() on null /Users/blumine/works/goodoc_v2/event/adminApi/application/models/Common_m.php 33
ERROR - 2019-07-23 08:44:35 --> Query error: Unknown column 'tags' in 'where clause' - Invalid query: select * from client_search_tags where tags = '지방분해'
ERROR - 2019-07-23 08:45:15 --> Query error: Table 'neo_goodoc_production.event_client_search_tag' doesn't exist - Invalid query: INSERT INTO `event_client_search_tag` (`event_id`, `client_search_tag_id`, `created_at`, `updated_at`) VALUES ('1682', '34', '2019-07-23 17:45:15', '2019-07-23 17:45:15')
ERROR - 2019-07-23 08:53:46 --> Query error: Unknown column 'tags' in 'field list' - Invalid query: INSERT INTO `client_search_tags` (`tags`, `created_at`, `updated_at`) VALUES ('여성의원', '2019-07-23 17:48:02', '2019-07-23 17:48:02')
ERROR - 2019-07-23 08:53:46 --> Severity: Warning --> Cannot modify header information - headers already sent by (output started at /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/MartinTest.php:504) /Users/blumine/works/goodoc_v2/event/adminApi/system/core/Common.php 570
ERROR - 2019-07-23 09:33:29 --> 404 Page Not Found: Faviconico/index
ERROR - 2019-07-23 10:40:43 --> Severity: Warning --> Illegal string offset 'id' /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/MartinTest.php 496
ERROR - 2019-07-23 10:40:43 --> Severity: Notice --> Undefined index: keyword /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/MartinTest.php 499
ERROR - 2019-07-23 10:40:43 --> Severity: Warning --> Illegal string offset 'id' /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/MartinTest.php 496
ERROR - 2019-07-23 10:40:43 --> Severity: Notice --> Undefined index: keyword /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/MartinTest.php 499
ERROR - 2019-07-23 10:40:43 --> Severity: Warning --> Illegal string offset 'id' /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/MartinTest.php 496
ERROR - 2019-07-23 10:40:43 --> Severity: Notice --> Undefined index: keyword /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/MartinTest.php 499
ERROR - 2019-07-23 10:40:43 --> Severity: Warning --> Illegal string offset 'id' /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/MartinTest.php 496
ERROR - 2019-07-23 10:40:43 --> Severity: Notice --> Undefined index: keyword /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/MartinTest.php 499
ERROR - 2019-07-23 10:40:43 --> Severity: Warning --> Illegal string offset 'id' /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/MartinTest.php 496
ERROR - 2019-07-23 10:40:43 --> Severity: Notice --> Undefined index: keyword /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/MartinTest.php 499
ERROR - 2019-07-23 10:40:43 --> Severity: Warning --> Illegal string offset 'id' /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/MartinTest.php 496
ERROR - 2019-07-23 10:40:43 --> Severity: Notice --> Undefined index: keyword /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/MartinTest.php 499
ERROR - 2019-07-23 10:40:43 --> Severity: Warning --> Illegal string offset 'id' /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/MartinTest.php 496
ERROR - 2019-07-23 10:40:43 --> Severity: Notice --> Undefined index: keyword /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/MartinTest.php 499
ERROR - 2019-07-23 10:40:43 --> Severity: Warning --> Illegal string offset 'id' /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/MartinTest.php 496
ERROR - 2019-07-23 10:40:43 --> Severity: Notice --> Undefined index: keyword /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/MartinTest.php 499
ERROR - 2019-07-23 10:40:43 --> Severity: Warning --> Illegal string offset 'id' /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/MartinTest.php 496
ERROR - 2019-07-23 10:40:43 --> Severity: Notice --> Undefined index: keyword /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/MartinTest.php 499
ERROR - 2019-07-23 10:40:43 --> Severity: Warning --> Illegal string offset 'id' /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/MartinTest.php 496
ERROR - 2019-07-23 10:40:43 --> Severity: Notice --> Undefined index: keyword /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/MartinTest.php 499
ERROR - 2019-07-23 10:40:43 --> Severity: Warning --> Illegal string offset 'id' /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/MartinTest.php 496
ERROR - 2019-07-23 10:40:43 --> Severity: Notice --> Undefined index: keyword /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/MartinTest.php 499
ERROR - 2019-07-23 10:40:43 --> Severity: Warning --> Illegal string offset 'id' /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/MartinTest.php 496
ERROR - 2019-07-23 10:40:43 --> Severity: Notice --> Undefined index: keyword /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/MartinTest.php 499
