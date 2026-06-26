<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

ERROR - 2019-07-03 01:11:28 --> 404 Page Not Found: Faviconico/index
ERROR - 2019-07-03 05:41:55 --> Severity: Notice --> Undefined property: MartinTest::$common_m /Users/blumine/works/goodoc_v2/event/adminApi/system/core/Model.php 73
ERROR - 2019-07-03 05:41:55 --> 
        Exception of type \'Error\' occurred with Message: Call to a member function isInTrans() on null in File /Users/blumine/works/goodoc_v2/event/adminApi/application/models/Ads_m.php at Line 2993
        
 Backtrace 
.#0 /Users/blumine/works/goodoc_v2/event/adminApi/application/models/Ads_m.php(2843): Ads_m->getHistoryListSec(Array)
#1 /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/MartinTest.php(787): Ads_m->gethistoryMerge(Array)
#2 /Users/blumine/works/goodoc_v2/event/adminApi/system/core/CodeIgniter.php(532): MartinTest->checkAds()
#3 /Users/blumine/works/goodoc_v2/event/adminApi/index.php(341): require_once('/Users/blumine/...')
#4 {main}
    
ERROR - 2019-07-03 05:41:56 --> Severity: error --> Exception: Call to a member function isInTrans() on null /Users/blumine/works/goodoc_v2/event/adminApi/application/models/Ads_m.php 2993
ERROR - 2019-07-03 05:42:28 --> Severity: Warning --> array_merge(): Argument #2 is not an array /Users/blumine/works/goodoc_v2/event/adminApi/application/models/Ads_m.php 2856
ERROR - 2019-07-03 05:43:57 --> Severity: Warning --> array_merge(): Argument #2 is not an array /Users/blumine/works/goodoc_v2/event/adminApi/application/models/Ads_m.php 2856
ERROR - 2019-07-03 05:44:31 --> Severity: Warning --> array_merge(): Argument #2 is not an array /Users/blumine/works/goodoc_v2/event/adminApi/application/models/Ads_m.php 2856
ERROR - 2019-07-03 09:33:33 --> 
        Exception of type \'ParseError\' occurred with Message: syntax error, unexpected ',' in File /Users/blumine/works/goodoc_v2/event/adminApi/application/hooks/preProcess.php at Line 45
        
 Backtrace 
.#0 /Users/blumine/works/goodoc_v2/event/adminApi/system/core/Loader.php(1285): Goodocapi->__construct()
#1 /Users/blumine/works/goodoc_v2/event/adminApi/system/core/Loader.php(1083): CI_Loader->_ci_init_library('Goodocapi', '', NULL, 'goodocapi')
#2 /Users/blumine/works/goodoc_v2/event/adminApi/system/core/Loader.php(218): CI_Loader->_ci_load_library('Goodocapi', NULL, NULL)
#3 /Users/blumine/works/goodoc_v2/event/adminApi/system/core/Loader.php(202): CI_Loader->library('goodocapi', NULL)
#4 /Users/blumine/works/goodoc_v2/event/adminApi/system/core/Loader.php(1359): CI_Loader->library(Array)
#5 /Users/blumine/works/goodoc_v2/event/adminApi/system/core/Loader.php(157): CI_Loader->_ci_autoloader()
#6 /Users/blumine/works/goodoc_v2/event/adminApi/system/core/Controller.php(79): CI_Loader->initialize()
#7 /Users/blumine/works/goodoc_v2/event/adminApi/application/libraries/REST_Controller.php(392): CI_Controller->__construct()
#8 /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/v10/Ads.php(19): REST_Controller->__construct()
#9 /Users/blumine/works/goodoc_v2/event/adminApi/system/core/CodeIgniter.php(518): Ads->__construct()
#10 /Users/blumine/works/goodoc_v2/event/adminApi/index.php(341): require_once('/Users/blumine/...')
#11 {main}
    
ERROR - 2019-07-03 09:33:34 --> Severity: error --> Exception: syntax error, unexpected ',' /Users/blumine/works/goodoc_v2/event/adminApi/application/hooks/preProcess.php 45
ERROR - 2019-07-03 09:33:46 --> 
        Exception of type \'ParseError\' occurred with Message: syntax error, unexpected ',' in File /Users/blumine/works/goodoc_v2/event/adminApi/application/hooks/preProcess.php at Line 45
        
 Backtrace 
.#0 /Users/blumine/works/goodoc_v2/event/adminApi/system/core/Loader.php(1285): Goodocapi->__construct()
#1 /Users/blumine/works/goodoc_v2/event/adminApi/system/core/Loader.php(1083): CI_Loader->_ci_init_library('Goodocapi', '', NULL, 'goodocapi')
#2 /Users/blumine/works/goodoc_v2/event/adminApi/system/core/Loader.php(218): CI_Loader->_ci_load_library('Goodocapi', NULL, NULL)
#3 /Users/blumine/works/goodoc_v2/event/adminApi/system/core/Loader.php(202): CI_Loader->library('goodocapi', NULL)
#4 /Users/blumine/works/goodoc_v2/event/adminApi/system/core/Loader.php(1359): CI_Loader->library(Array)
#5 /Users/blumine/works/goodoc_v2/event/adminApi/system/core/Loader.php(157): CI_Loader->_ci_autoloader()
#6 /Users/blumine/works/goodoc_v2/event/adminApi/system/core/Controller.php(79): CI_Loader->initialize()
#7 /Users/blumine/works/goodoc_v2/event/adminApi/application/libraries/REST_Controller.php(392): CI_Controller->__construct()
#8 /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/v10/Ads.php(19): REST_Controller->__construct()
#9 /Users/blumine/works/goodoc_v2/event/adminApi/system/core/CodeIgniter.php(518): Ads->__construct()
#10 /Users/blumine/works/goodoc_v2/event/adminApi/index.php(341): require_once('/Users/blumine/...')
#11 {main}
    
ERROR - 2019-07-03 09:33:47 --> Severity: error --> Exception: syntax error, unexpected ',' /Users/blumine/works/goodoc_v2/event/adminApi/application/hooks/preProcess.php 45
