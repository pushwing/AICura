<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

ERROR - 2019-11-20 07:46:24 --> 404 Page Not Found: Faviconico/index
ERROR - 2019-11-20 07:53:06 --> Severity: Notice --> Undefined index: contractId /Users/blumine/works/goodoc_v2/event/adminApi/application/models/Common_m.php 443
ERROR - 2019-11-20 07:53:06 --> Severity: Notice --> Undefined index: dImages /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/MartinTest.php 1620
ERROR - 2019-11-20 08:01:06 --> Severity: Notice --> Undefined index: dImages /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/MartinTest.php 1621
ERROR - 2019-11-20 08:06:33 --> Severity: Notice --> Undefined index: dImageJson /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/MartinTest.php 1623
ERROR - 2019-11-20 08:09:04 --> 
        Exception of type \'Error\' occurred with Message: Call to undefined method MartinTest::setHistory() in File /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/MartinTest.php at Line 1627
        
 Backtrace 
.#0 /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/MartinTest.php(1525): MartinTest->updateAds(Array)
#1 /Users/blumine/works/goodoc_v2/event/adminApi/system/core/CodeIgniter.php(532): MartinTest->updateDbCost()
#2 /Users/blumine/works/goodoc_v2/event/adminApi/index.php(341): require_once('/Users/blumine/...')
#3 {main}
    
ERROR - 2019-11-20 08:09:04 --> Severity: error --> Exception: Call to undefined method MartinTest::setHistory() /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/MartinTest.php 1627
ERROR - 2019-11-20 08:09:04 --> Severity: Warning --> Cannot modify header information - headers already sent by (output started at /Users/blumine/works/goodoc_v2/event/adminApi/application/helpers/common_helper.php:72) /Users/blumine/works/goodoc_v2/event/adminApi/system/core/Common.php 570
ERROR - 2019-11-20 08:19:16 --> Severity: Notice --> Undefined index: contractId /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/MartinTest.php 1587
ERROR - 2019-11-20 08:19:16 --> Query error: Invalid JSON text: "The document is empty." at position 0 in value for column 'ads_history.dImageJson'. - Invalid query: INSERT INTO `ads_history` (`adsId`, `dbCost`, `adDetailInfo`, `contractId`, `agencyUserId`, `userId`, `regDate`, `deletejson`, `dImageJson`) VALUES ('15055', '35000', '[null,null,null,null,null,null]', NULL, NULL, 1, '2019-11-20', '{\"adsId\":\"15055\",\"dbCost\":\"35000\",\"adDetailInfo\":\"[null,null,null,null,null,null]\",\"contractId\":null,\"agencyUserId\":null,\"userId\":1,\"regDate\":\"2019-11-20\"}', '')
ERROR - 2019-11-20 08:19:16 --> Severity: Warning --> Cannot modify header information - headers already sent by (output started at /Users/blumine/works/goodoc_v2/event/adminApi/application/helpers/common_helper.php:72) /Users/blumine/works/goodoc_v2/event/adminApi/system/core/Common.php 570
