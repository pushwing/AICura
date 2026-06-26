<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

ERROR - 2019-07-17 02:05:40 --> Query error: You have an error in your SQL syntax; check the manual that corresponds to your MySQL server version for the right syntax to use near ')' at line 3 - Invalid query: 
            select * from ads 
             where isLive='Y' 
)
        
ERROR - 2019-07-17 02:05:41 --> 404 Page Not Found: Faviconico/index
ERROR - 2019-07-17 02:06:32 --> Query error: Unknown column 'event_id' in 'where clause' - Invalid query: select * from events  
                where event_id='1110'
ERROR - 2019-07-17 06:36:35 --> Severity: Notice --> Undefined property: MartinTest::$ads_m /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/MartinTest.php 1092
ERROR - 2019-07-17 06:36:35 --> 
        Exception of type \'Error\' occurred with Message: Call to a member function gethistoryMerge() on null in File /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/MartinTest.php at Line 1092
        
 Backtrace 
.#0 /Users/blumine/works/goodoc_v2/event/adminApi/system/core/CodeIgniter.php(532): MartinTest->vDog()
#1 /Users/blumine/works/goodoc_v2/event/adminApi/index.php(341): require_once('/Users/blumine/...')
#2 {main}
    
ERROR - 2019-07-17 06:36:36 --> Severity: error --> Exception: Call to a member function gethistoryMerge() on null /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/MartinTest.php 1092
ERROR - 2019-07-17 06:36:55 --> Severity: Notice --> Undefined property: MartinTest::$common_m /Users/blumine/works/goodoc_v2/event/adminApi/system/core/Model.php 73
ERROR - 2019-07-17 06:36:55 --> 
        Exception of type \'Error\' occurred with Message: Call to a member function isInTrans() on null in File /Users/blumine/works/goodoc_v2/event/adminApi/application/models/Ads_m.php at Line 2995
        
 Backtrace 
.#0 /Users/blumine/works/goodoc_v2/event/adminApi/application/models/Ads_m.php(2845): Ads_m->getHistoryListSec(Array)
#1 /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/MartinTest.php(1093): Ads_m->gethistoryMerge(Array)
#2 /Users/blumine/works/goodoc_v2/event/adminApi/system/core/CodeIgniter.php(532): MartinTest->vDog()
#3 /Users/blumine/works/goodoc_v2/event/adminApi/index.php(341): require_once('/Users/blumine/...')
#4 {main}
    
ERROR - 2019-07-17 06:36:55 --> Severity: error --> Exception: Call to a member function isInTrans() on null /Users/blumine/works/goodoc_v2/event/adminApi/application/models/Ads_m.php 2995
ERROR - 2019-07-17 06:37:10 --> Severity: Notice --> Undefined property: MartinTest::$ads_m /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/MartinTest.php 1093
ERROR - 2019-07-17 06:37:10 --> 
        Exception of type \'Error\' occurred with Message: Call to a member function gethistoryMerge() on null in File /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/MartinTest.php at Line 1093
        
 Backtrace 
.#0 /Users/blumine/works/goodoc_v2/event/adminApi/system/core/CodeIgniter.php(532): MartinTest->vDog()
#1 /Users/blumine/works/goodoc_v2/event/adminApi/index.php(341): require_once('/Users/blumine/...')
#2 {main}
    
ERROR - 2019-07-17 06:37:10 --> Severity: error --> Exception: Call to a member function gethistoryMerge() on null /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/MartinTest.php 1093
ERROR - 2019-07-17 06:37:23 --> 
        Exception of type \'Error\' occurred with Message: Call to a member function query() on null in File /Users/blumine/works/goodoc_v2/event/adminApi/application/models/Common_m.php at Line 33
        
 Backtrace 
.#0 /Users/blumine/works/goodoc_v2/event/adminApi/application/models/Ads_m.php(2995): Common_m->isInTrans()
#1 /Users/blumine/works/goodoc_v2/event/adminApi/application/models/Ads_m.php(2845): Ads_m->getHistoryListSec(Array)
#2 /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/MartinTest.php(1093): Ads_m->gethistoryMerge(Array)
#3 /Users/blumine/works/goodoc_v2/event/adminApi/system/core/CodeIgniter.php(532): MartinTest->vDog()
#4 /Users/blumine/works/goodoc_v2/event/adminApi/index.php(341): require_once('/Users/blumine/...')
#5 {main}
    
ERROR - 2019-07-17 06:37:24 --> Severity: error --> Exception: Call to a member function query() on null /Users/blumine/works/goodoc_v2/event/adminApi/application/models/Common_m.php 33
