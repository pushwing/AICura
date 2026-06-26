<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

INFO - 2019-03-22 01:08:35 --> Config Class Initialized
INFO - 2019-03-22 01:08:35 --> Hooks Class Initialized
DEBUG - 2019-03-22 01:08:35 --> UTF-8 Support Enabled
INFO - 2019-03-22 01:08:35 --> Utf8 Class Initialized
INFO - 2019-03-22 01:08:35 --> URI Class Initialized
INFO - 2019-03-22 01:08:35 --> Router Class Initialized
INFO - 2019-03-22 01:08:35 --> Output Class Initialized
INFO - 2019-03-22 01:08:35 --> Security Class Initialized
DEBUG - 2019-03-22 01:08:35 --> Global POST, GET and COOKIE data sanitized
INFO - 2019-03-22 01:08:35 --> Input Class Initialized
INFO - 2019-03-22 01:08:35 --> Language Class Initialized
INFO - 2019-03-22 01:08:35 --> Loader Class Initialized
INFO - 2019-03-22 01:08:35 --> Helper loaded: common_helper
INFO - 2019-03-22 01:08:35 --> Database Driver Class Initialized
INFO - 2019-03-22 01:08:35 --> Controller Class Initialized
INFO - 2019-03-22 01:08:35 --> Database Driver Class Initialized
INFO - 2019-03-22 01:08:35 --> Database Driver Class Initialized
INFO - 2019-03-22 01:08:35 --> Model "contractOrder_m" initialized
INFO - 2019-03-22 01:08:35 --> Model "Ads_m" initialized
INFO - 2019-03-22 01:08:35 --> Model "dataMigration_m" initialized
INFO - 2019-03-22 01:08:35 --> Model "Common_m" initialized
ERROR - 2019-03-22 01:08:35 --> Query error: You have an error in your SQL syntax; check the manual that corresponds to your MariaDB server version for the right syntax to use near 'FROM `contracts` `c`
        LEFT JOIN `hospital_contracts` `hc` ON `c`.`id`=`hc' at line 4 - Invalid query: SELECT `c`.*, `h`.`id` `hospital_id2`, `h`.`name`, `u`.`id` `user_id`, `u`.`username`, `u`.`email`, 
        `ct`.`hospital_phone`, `ct`.`hospital_phone`, `ct`.`user_username`, `ct`.`user_phone`, `ct`.`user_email`,
        group_concat(distinct hc.hospital_id) as network_id,
        FROM `contracts` `c`
        LEFT JOIN `hospital_contracts` `hc` ON `c`.`id`=`hc`.`contract_id`
        LEFT JOIN `hospitals` `h` ON `hc`.`hospital_id`=`h`.`id`
        LEFT JOIN `user_hospital_departments` `uhd` ON `hc`.`hospital_id`=`uhd`.`hospital_id`
        LEFT JOIN `users` `u` ON `uhd`.`user_id`=`u`.`id`
        LEFT JOIN `contacts` `ct` ON `u`.`id`=`ct`.`user_id`
        WHERE `c`.`title` NOT LIKE '%영구종료%' ESCAPE '!'
        and c.is_visible=1 -- 2가 종료
        and c.id != 1
         and c.id in(3)
        GROUP BY `c`.`id`
        having hospital_id2 is not null
        
INFO - 2019-03-22 01:08:35 --> Language file loaded: language/english/db_lang.php
INFO - 2019-03-22 01:09:15 --> Config Class Initialized
INFO - 2019-03-22 01:09:15 --> Hooks Class Initialized
DEBUG - 2019-03-22 01:09:15 --> UTF-8 Support Enabled
INFO - 2019-03-22 01:09:15 --> Utf8 Class Initialized
INFO - 2019-03-22 01:09:15 --> URI Class Initialized
INFO - 2019-03-22 01:09:15 --> Router Class Initialized
INFO - 2019-03-22 01:09:15 --> Output Class Initialized
INFO - 2019-03-22 01:09:15 --> Security Class Initialized
DEBUG - 2019-03-22 01:09:15 --> Global POST, GET and COOKIE data sanitized
INFO - 2019-03-22 01:09:15 --> Input Class Initialized
INFO - 2019-03-22 01:09:15 --> Language Class Initialized
INFO - 2019-03-22 01:09:15 --> Loader Class Initialized
INFO - 2019-03-22 01:09:15 --> Helper loaded: common_helper
INFO - 2019-03-22 01:09:15 --> Database Driver Class Initialized
INFO - 2019-03-22 01:09:15 --> Controller Class Initialized
INFO - 2019-03-22 01:09:15 --> Database Driver Class Initialized
INFO - 2019-03-22 01:09:16 --> Database Driver Class Initialized
INFO - 2019-03-22 01:09:16 --> Model "contractOrder_m" initialized
INFO - 2019-03-22 01:09:16 --> Model "Ads_m" initialized
INFO - 2019-03-22 01:09:16 --> Model "dataMigration_m" initialized
INFO - 2019-03-22 01:09:16 --> Model "Common_m" initialized
ERROR - 2019-03-22 01:09:17 --> Query error: Unknown column 'agencyCompanyChargeName' in 'field list' - Invalid query: INSERT INTO `contract_order` (`hospitalType`, `hospitalId`, `hospitalName`, `contractDate`, `contractType`, `title`, `adType`, `adType2`, `agencyUserId`, `manageUserId`, `taxChargeName`, `taxBusinessNo`, `taxChargeEmail`, `hospitalChargeName`, `hospitalChargePhone`, `hospitalChargeEmail`, `taxIssueRequestDate`, `agencyCompanyChargeName`, `agencyCompanyChargePhone`, `agencyCompanyChargeEmail`, `agencyCompanyId`, `agencyCompanyName`, `agencyCompanyFeeRate`, `regDate`, `taxIssueDate`, `isNetwork`, `adPrice`) VALUES (1, '35467', '아이디치과의원', '2015-05-06 18:36:12', 1, '아이디치과의원', 1, 1, '8', '', NULL, '', NULL, NULL, NULL, NULL, '2015-05-06 18:36:12', '', '', '', '', '', '', '2015-05-06 18:36:12', '2015-05-06 18:36:12', 0, '10000000')
INFO - 2019-03-22 01:09:17 --> Language file loaded: language/english/db_lang.php
INFO - 2019-03-22 01:12:33 --> Config Class Initialized
INFO - 2019-03-22 01:12:33 --> Hooks Class Initialized
DEBUG - 2019-03-22 01:12:33 --> UTF-8 Support Enabled
INFO - 2019-03-22 01:12:33 --> Utf8 Class Initialized
INFO - 2019-03-22 01:12:33 --> URI Class Initialized
INFO - 2019-03-22 01:12:33 --> Router Class Initialized
INFO - 2019-03-22 01:12:33 --> Output Class Initialized
INFO - 2019-03-22 01:12:33 --> Security Class Initialized
DEBUG - 2019-03-22 01:12:33 --> Global POST, GET and COOKIE data sanitized
INFO - 2019-03-22 01:12:33 --> Input Class Initialized
INFO - 2019-03-22 01:12:33 --> Language Class Initialized
INFO - 2019-03-22 01:12:33 --> Loader Class Initialized
INFO - 2019-03-22 01:12:33 --> Helper loaded: common_helper
INFO - 2019-03-22 01:12:33 --> Database Driver Class Initialized
INFO - 2019-03-22 01:12:33 --> Controller Class Initialized
INFO - 2019-03-22 01:12:33 --> Database Driver Class Initialized
INFO - 2019-03-22 01:12:34 --> Database Driver Class Initialized
INFO - 2019-03-22 01:12:34 --> Model "contractOrder_m" initialized
INFO - 2019-03-22 01:12:34 --> Model "Ads_m" initialized
INFO - 2019-03-22 01:12:34 --> Model "dataMigration_m" initialized
INFO - 2019-03-22 01:12:34 --> Model "Common_m" initialized
ERROR - 2019-03-22 01:12:34 --> Severity: Notice --> Undefined property: DataMigration::$session /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/DataMigration.php 379
ERROR - 2019-03-22 01:12:34 --> 
        Exception of type \'Error\' occurred with Message: Call to a member function set_flashdata() on null in File /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/DataMigration.php at Line 379
        
 Backtrace 
.#0 /Users/blumine/works/goodoc_v2/event/adminApi/system/core/CodeIgniter.php(532): DataMigration->contractProcess2()
#1 /Users/blumine/works/goodoc_v2/event/adminApi/index.php(327): require_once('/Users/blumine/...')
#2 {main}
    
ERROR - 2019-03-22 01:12:35 --> Severity: error --> Exception: Call to a member function set_flashdata() on null /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/DataMigration.php 379
INFO - 2019-03-22 01:13:28 --> Config Class Initialized
INFO - 2019-03-22 01:13:28 --> Hooks Class Initialized
DEBUG - 2019-03-22 01:13:28 --> UTF-8 Support Enabled
INFO - 2019-03-22 01:13:28 --> Utf8 Class Initialized
INFO - 2019-03-22 01:13:28 --> URI Class Initialized
INFO - 2019-03-22 01:13:28 --> Router Class Initialized
INFO - 2019-03-22 01:13:28 --> Output Class Initialized
INFO - 2019-03-22 01:13:28 --> Security Class Initialized
DEBUG - 2019-03-22 01:13:28 --> Global POST, GET and COOKIE data sanitized
INFO - 2019-03-22 01:13:28 --> Input Class Initialized
INFO - 2019-03-22 01:13:28 --> Language Class Initialized
INFO - 2019-03-22 01:13:28 --> Loader Class Initialized
INFO - 2019-03-22 01:13:28 --> Helper loaded: common_helper
INFO - 2019-03-22 01:13:28 --> Database Driver Class Initialized
INFO - 2019-03-22 01:13:28 --> Controller Class Initialized
INFO - 2019-03-22 01:13:28 --> Database Driver Class Initialized
INFO - 2019-03-22 01:13:29 --> Database Driver Class Initialized
INFO - 2019-03-22 01:13:29 --> Model "contractOrder_m" initialized
INFO - 2019-03-22 01:13:29 --> Model "Ads_m" initialized
INFO - 2019-03-22 01:13:29 --> Model "dataMigration_m" initialized
INFO - 2019-03-22 01:13:29 --> Model "Common_m" initialized
DEBUG - 2019-03-22 01:13:29 --> Session: "sess_save_path" is empty; using "session.save_path" value from php.ini.
INFO - 2019-03-22 01:13:29 --> Session: Class initialized using 'files' driver.
ERROR - 2019-03-22 01:13:29 --> Query error: Duplicate entry '3-0' for key 'PRIMARY' - Invalid query: INSERT INTO `contract` (`hospitalType`, `id`, `hospitalId`, `hospitalName`, `contractDate`, `title`, `adType`, `adType2`, `agencyUserId`, `manageUserId`, `taxChargeName`, `taxBusinessNo`, `taxChargeEmail`, `hospitalChargeName`, `hospitalChargePhone`, `hospitalChargeEmail`, `agencyCompanyId`, `agencyCompanyName`, `regDate`) VALUES (1, '3', '35467', '아이디치과의원', '2015-05-06 18:36:12', '아이디치과의원', 1, 1, '8', '', NULL, '', NULL, NULL, NULL, NULL, '', '', '2015-05-06 18:36:12')
INFO - 2019-03-22 01:13:29 --> Language file loaded: language/english/db_lang.php
INFO - 2019-03-22 01:13:59 --> Config Class Initialized
INFO - 2019-03-22 01:13:59 --> Hooks Class Initialized
DEBUG - 2019-03-22 01:13:59 --> UTF-8 Support Enabled
INFO - 2019-03-22 01:13:59 --> Utf8 Class Initialized
INFO - 2019-03-22 01:13:59 --> URI Class Initialized
INFO - 2019-03-22 01:13:59 --> Router Class Initialized
INFO - 2019-03-22 01:13:59 --> Output Class Initialized
INFO - 2019-03-22 01:13:59 --> Security Class Initialized
DEBUG - 2019-03-22 01:13:59 --> Global POST, GET and COOKIE data sanitized
INFO - 2019-03-22 01:13:59 --> Input Class Initialized
INFO - 2019-03-22 01:13:59 --> Language Class Initialized
INFO - 2019-03-22 01:13:59 --> Loader Class Initialized
INFO - 2019-03-22 01:13:59 --> Helper loaded: common_helper
INFO - 2019-03-22 01:13:59 --> Database Driver Class Initialized
INFO - 2019-03-22 01:13:59 --> Controller Class Initialized
INFO - 2019-03-22 01:13:59 --> Database Driver Class Initialized
INFO - 2019-03-22 01:13:59 --> Database Driver Class Initialized
INFO - 2019-03-22 01:13:59 --> Model "contractOrder_m" initialized
INFO - 2019-03-22 01:13:59 --> Model "Ads_m" initialized
INFO - 2019-03-22 01:13:59 --> Model "dataMigration_m" initialized
INFO - 2019-03-22 01:13:59 --> Model "Common_m" initialized
DEBUG - 2019-03-22 01:13:59 --> Session: "sess_save_path" is empty; using "session.save_path" value from php.ini.
INFO - 2019-03-22 01:13:59 --> Session: Class initialized using 'files' driver.
ERROR - 2019-03-22 01:14:00 --> Severity: Notice --> Undefined index: price /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/DataMigration.php 389
ERROR - 2019-03-22 01:14:00 --> Severity: Notice --> Undefined index: price /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/DataMigration.php 405
ERROR - 2019-03-22 01:14:03 --> Severity: Notice --> Undefined index: users_id /Users/blumine/works/goodoc_v2/event/adminApi/application/models/DataMigration_m.php 1345
INFO - 2019-03-22 01:14:03 --> UPDATE `ads` SET `contractOrderId` = 3
WHERE `contractOrderId` = 1
ERROR - 2019-03-22 01:14:03 --> 
        Exception of type \'Error\' occurred with Message: Call to undefined method dataMigration_m::setVColumn() in File /Users/blumine/works/goodoc_v2/event/adminApi/application/models/DataMigration_m.php at Line 1433
        
 Backtrace 
.#0 /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/DataMigration.php(420): dataMigration_m->setContractInfo(Array)
#1 /Users/blumine/works/goodoc_v2/event/adminApi/system/core/CodeIgniter.php(532): DataMigration->contractProcess2()
#2 /Users/blumine/works/goodoc_v2/event/adminApi/index.php(327): require_once('/Users/blumine/...')
#3 {main}
    
ERROR - 2019-03-22 01:14:05 --> Severity: error --> Exception: Call to undefined method dataMigration_m::setVColumn() /Users/blumine/works/goodoc_v2/event/adminApi/application/models/DataMigration_m.php 1433
INFO - 2019-03-22 01:18:24 --> Config Class Initialized
INFO - 2019-03-22 01:18:24 --> Hooks Class Initialized
DEBUG - 2019-03-22 01:18:24 --> UTF-8 Support Enabled
INFO - 2019-03-22 01:18:24 --> Utf8 Class Initialized
INFO - 2019-03-22 01:18:24 --> URI Class Initialized
INFO - 2019-03-22 01:18:24 --> Router Class Initialized
INFO - 2019-03-22 01:18:24 --> Output Class Initialized
INFO - 2019-03-22 01:18:24 --> Security Class Initialized
DEBUG - 2019-03-22 01:18:24 --> Global POST, GET and COOKIE data sanitized
INFO - 2019-03-22 01:18:24 --> Input Class Initialized
INFO - 2019-03-22 01:18:24 --> Language Class Initialized
INFO - 2019-03-22 01:18:24 --> Loader Class Initialized
INFO - 2019-03-22 01:18:24 --> Helper loaded: common_helper
INFO - 2019-03-22 01:18:24 --> Database Driver Class Initialized
INFO - 2019-03-22 01:18:24 --> Controller Class Initialized
INFO - 2019-03-22 01:18:24 --> Database Driver Class Initialized
INFO - 2019-03-22 01:18:24 --> Database Driver Class Initialized
INFO - 2019-03-22 01:18:25 --> Model "contractOrder_m" initialized
INFO - 2019-03-22 01:18:25 --> Model "Ads_m" initialized
INFO - 2019-03-22 01:18:25 --> Model "dataMigration_m" initialized
INFO - 2019-03-22 01:18:25 --> Model "Common_m" initialized
DEBUG - 2019-03-22 01:18:25 --> Session: "sess_save_path" is empty; using "session.save_path" value from php.ini.
INFO - 2019-03-22 01:18:25 --> Session: Class initialized using 'files' driver.
ERROR - 2019-03-22 01:18:29 --> Severity: Notice --> Undefined index: users_id /Users/blumine/works/goodoc_v2/event/adminApi/application/models/DataMigration_m.php 1345
INFO - 2019-03-22 01:18:29 --> UPDATE `ads` SET `contractOrderId` = 3
WHERE `contractOrderId` = 1
ERROR - 2019-03-22 01:18:29 --> 
        Exception of type \'Error\' occurred with Message: Call to undefined method dataMigration_m::setVColumn() in File /Users/blumine/works/goodoc_v2/event/adminApi/application/models/DataMigration_m.php at Line 1433
        
 Backtrace 
.#0 /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/DataMigration.php(420): dataMigration_m->setContractInfo(Array)
#1 /Users/blumine/works/goodoc_v2/event/adminApi/system/core/CodeIgniter.php(532): DataMigration->contractProcess2()
#2 /Users/blumine/works/goodoc_v2/event/adminApi/index.php(327): require_once('/Users/blumine/...')
#3 {main}
    
ERROR - 2019-03-22 01:18:30 --> Severity: error --> Exception: Call to undefined method dataMigration_m::setVColumn() /Users/blumine/works/goodoc_v2/event/adminApi/application/models/DataMigration_m.php 1433
INFO - 2019-03-22 01:23:03 --> Config Class Initialized
INFO - 2019-03-22 01:23:03 --> Hooks Class Initialized
DEBUG - 2019-03-22 01:23:03 --> UTF-8 Support Enabled
INFO - 2019-03-22 01:23:03 --> Utf8 Class Initialized
INFO - 2019-03-22 01:23:03 --> URI Class Initialized
INFO - 2019-03-22 01:23:03 --> Router Class Initialized
INFO - 2019-03-22 01:23:03 --> Output Class Initialized
INFO - 2019-03-22 01:23:03 --> Security Class Initialized
DEBUG - 2019-03-22 01:23:03 --> Global POST, GET and COOKIE data sanitized
INFO - 2019-03-22 01:23:03 --> Input Class Initialized
INFO - 2019-03-22 01:23:03 --> Language Class Initialized
INFO - 2019-03-22 01:23:03 --> Loader Class Initialized
INFO - 2019-03-22 01:23:03 --> Helper loaded: common_helper
INFO - 2019-03-22 01:23:03 --> Database Driver Class Initialized
INFO - 2019-03-22 01:23:03 --> Controller Class Initialized
INFO - 2019-03-22 01:23:03 --> Database Driver Class Initialized
INFO - 2019-03-22 01:23:03 --> Database Driver Class Initialized
INFO - 2019-03-22 01:23:04 --> Model "contractOrder_m" initialized
INFO - 2019-03-22 01:23:04 --> Model "Ads_m" initialized
INFO - 2019-03-22 01:23:04 --> Model "dataMigration_m" initialized
INFO - 2019-03-22 01:23:04 --> Model "Common_m" initialized
DEBUG - 2019-03-22 01:23:04 --> Session: "sess_save_path" is empty; using "session.save_path" value from php.ini.
INFO - 2019-03-22 01:23:04 --> Session: Class initialized using 'files' driver.
INFO - 2019-03-22 01:23:07 --> UPDATE `ads` SET `contractOrderId` = 3
WHERE `contractOrderId` = 1
INFO - 2019-03-22 01:23:07 --> []
ERROR - 2019-03-22 01:23:12 --> Query error: Duplicate entry '3' for key 'PRIMARY' - Invalid query: INSERT INTO `contract_order` (`hospitalType`, `id`, `hospitalId`, `hospitalName`, `contractDate`, `contractType`, `title`, `adType`, `adType2`, `agencyUserId`, `manageUserId`, `taxChargeName`, `taxBusinessNo`, `taxChargeEmail`, `hospitalChargeName`, `hospitalChargePhone`, `hospitalChargeEmail`, `taxIssueRequestDate`, `agencyCompanyChargeName`, `agencyCompanyChargePhone`, `agencyCompanyChargeEmail`, `agencyCompanyId`, `agencyCompanyName`, `agencyCompanyFeeRate`, `regDate`, `taxIssueDate`, `isNetwork`, `adPrice`, `parentId`) VALUES (1, '3', '35467', '아이디치과의원', '2015-05-06 18:36:12', 1, '아이디치과의원', 1, 1, '8', '', NULL, '', NULL, NULL, NULL, NULL, '2015-09-25 16:00:27', '', '', '', '', '', '', '2015-09-25 16:00:27', '2015-09-25 16:00:27', 0, '5000000', 3)
INFO - 2019-03-22 01:23:12 --> Language file loaded: language/english/db_lang.php
INFO - 2019-03-22 01:23:23 --> Config Class Initialized
INFO - 2019-03-22 01:23:23 --> Hooks Class Initialized
DEBUG - 2019-03-22 01:23:23 --> UTF-8 Support Enabled
INFO - 2019-03-22 01:23:23 --> Utf8 Class Initialized
INFO - 2019-03-22 01:23:23 --> URI Class Initialized
INFO - 2019-03-22 01:23:23 --> Router Class Initialized
INFO - 2019-03-22 01:23:23 --> Output Class Initialized
INFO - 2019-03-22 01:23:23 --> Security Class Initialized
DEBUG - 2019-03-22 01:23:23 --> Global POST, GET and COOKIE data sanitized
INFO - 2019-03-22 01:23:23 --> Input Class Initialized
INFO - 2019-03-22 01:23:23 --> Language Class Initialized
INFO - 2019-03-22 01:23:23 --> Loader Class Initialized
INFO - 2019-03-22 01:23:23 --> Helper loaded: common_helper
INFO - 2019-03-22 01:23:23 --> Database Driver Class Initialized
INFO - 2019-03-22 01:23:24 --> Controller Class Initialized
INFO - 2019-03-22 01:23:24 --> Database Driver Class Initialized
INFO - 2019-03-22 01:23:24 --> Database Driver Class Initialized
INFO - 2019-03-22 01:23:24 --> Model "contractOrder_m" initialized
INFO - 2019-03-22 01:23:24 --> Model "Ads_m" initialized
INFO - 2019-03-22 01:23:24 --> Model "dataMigration_m" initialized
INFO - 2019-03-22 01:23:24 --> Model "Common_m" initialized
DEBUG - 2019-03-22 01:23:24 --> Session: "sess_save_path" is empty; using "session.save_path" value from php.ini.
INFO - 2019-03-22 01:23:24 --> Session: Class initialized using 'files' driver.
INFO - 2019-03-22 01:23:28 --> UPDATE `ads` SET `contractOrderId` = 3
WHERE `contractOrderId` = 1
INFO - 2019-03-22 01:23:28 --> []
ERROR - 2019-03-22 01:23:32 --> Query error: Duplicate entry '3' for key 'PRIMARY' - Invalid query: INSERT INTO `contract_order` (`hospitalType`, `id`, `hospitalId`, `hospitalName`, `contractDate`, `contractType`, `title`, `adType`, `adType2`, `agencyUserId`, `manageUserId`, `taxChargeName`, `taxBusinessNo`, `taxChargeEmail`, `hospitalChargeName`, `hospitalChargePhone`, `hospitalChargeEmail`, `taxIssueRequestDate`, `agencyCompanyChargeName`, `agencyCompanyChargePhone`, `agencyCompanyChargeEmail`, `agencyCompanyId`, `agencyCompanyName`, `agencyCompanyFeeRate`, `regDate`, `taxIssueDate`, `isNetwork`, `adPrice`, `parentId`) VALUES (1, '3', '35467', '아이디치과의원', '2015-05-06 18:36:12', 1, '아이디치과의원', 1, 1, '8', '', NULL, '', NULL, NULL, NULL, NULL, '2015-09-25 16:00:27', '', '', '', '', '', '', '2015-09-25 16:00:27', '2015-09-25 16:00:27', 0, '5000000', 3)
INFO - 2019-03-22 01:23:32 --> Language file loaded: language/english/db_lang.php
INFO - 2019-03-22 01:29:50 --> Config Class Initialized
INFO - 2019-03-22 01:29:50 --> Hooks Class Initialized
DEBUG - 2019-03-22 01:29:50 --> UTF-8 Support Enabled
INFO - 2019-03-22 01:29:50 --> Utf8 Class Initialized
INFO - 2019-03-22 01:29:50 --> URI Class Initialized
INFO - 2019-03-22 01:29:50 --> Router Class Initialized
INFO - 2019-03-22 01:29:50 --> Output Class Initialized
INFO - 2019-03-22 01:29:50 --> Security Class Initialized
DEBUG - 2019-03-22 01:29:50 --> Global POST, GET and COOKIE data sanitized
INFO - 2019-03-22 01:29:50 --> Input Class Initialized
INFO - 2019-03-22 01:29:50 --> Language Class Initialized
INFO - 2019-03-22 01:29:50 --> Loader Class Initialized
INFO - 2019-03-22 01:29:50 --> Helper loaded: common_helper
INFO - 2019-03-22 01:29:50 --> Database Driver Class Initialized
INFO - 2019-03-22 01:29:50 --> Controller Class Initialized
INFO - 2019-03-22 01:29:50 --> Database Driver Class Initialized
INFO - 2019-03-22 01:29:50 --> Database Driver Class Initialized
INFO - 2019-03-22 01:29:50 --> Model "contractOrder_m" initialized
INFO - 2019-03-22 01:29:50 --> Model "Ads_m" initialized
INFO - 2019-03-22 01:29:50 --> Model "dataMigration_m" initialized
INFO - 2019-03-22 01:29:50 --> Model "Common_m" initialized
DEBUG - 2019-03-22 01:29:50 --> Session: "sess_save_path" is empty; using "session.save_path" value from php.ini.
INFO - 2019-03-22 01:29:50 --> Session: Class initialized using 'files' driver.
INFO - 2019-03-22 01:29:54 --> UPDATE `ads` SET `contractOrderId` = 3
WHERE `contractOrderId` = 1
INFO - 2019-03-22 01:29:54 --> []
ERROR - 2019-03-22 01:29:58 --> Query error: Duplicate entry '3' for key 'PRIMARY' - Invalid query: INSERT INTO `contract_order` (`hospitalType`, `id`, `hospitalId`, `hospitalName`, `contractDate`, `contractType`, `title`, `adType`, `adType2`, `agencyUserId`, `manageUserId`, `taxChargeName`, `taxBusinessNo`, `taxChargeEmail`, `hospitalChargeName`, `hospitalChargePhone`, `hospitalChargeEmail`, `taxIssueRequestDate`, `agencyCompanyChargeName`, `agencyCompanyChargePhone`, `agencyCompanyChargeEmail`, `agencyCompanyId`, `agencyCompanyName`, `agencyCompanyFeeRate`, `regDate`, `taxIssueDate`, `isNetwork`, `adPrice`, `parentId`) VALUES (1, '3', '35467', '아이디치과의원', '2015-09-25 16:00:27', 1, '아이디치과의원', 1, 1, '8', '', NULL, '', NULL, NULL, NULL, NULL, '2015-09-25 16:00:27', '', '', '', '', '', '', '2015-09-25 16:00:27', '2015-09-25 16:00:27', 0, '5000000', 3)
INFO - 2019-03-22 01:29:58 --> Language file loaded: language/english/db_lang.php
ERROR - 2019-03-22 01:29:59 --> Severity: Warning --> Cannot modify header information - headers already sent by (output started at /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/DataMigration.php:462) /Users/blumine/works/goodoc_v2/event/adminApi/system/core/Common.php 570
INFO - 2019-03-22 01:32:38 --> Config Class Initialized
INFO - 2019-03-22 01:32:38 --> Hooks Class Initialized
DEBUG - 2019-03-22 01:32:38 --> UTF-8 Support Enabled
INFO - 2019-03-22 01:32:38 --> Utf8 Class Initialized
INFO - 2019-03-22 01:32:38 --> URI Class Initialized
INFO - 2019-03-22 01:32:38 --> Router Class Initialized
INFO - 2019-03-22 01:32:38 --> Output Class Initialized
INFO - 2019-03-22 01:32:38 --> Security Class Initialized
DEBUG - 2019-03-22 01:32:38 --> Global POST, GET and COOKIE data sanitized
INFO - 2019-03-22 01:32:38 --> Input Class Initialized
INFO - 2019-03-22 01:32:38 --> Language Class Initialized
INFO - 2019-03-22 01:32:38 --> Loader Class Initialized
INFO - 2019-03-22 01:32:38 --> Helper loaded: common_helper
INFO - 2019-03-22 01:32:38 --> Database Driver Class Initialized
INFO - 2019-03-22 01:32:38 --> Controller Class Initialized
INFO - 2019-03-22 01:32:38 --> Database Driver Class Initialized
INFO - 2019-03-22 01:32:38 --> Database Driver Class Initialized
INFO - 2019-03-22 01:32:38 --> Model "contractOrder_m" initialized
INFO - 2019-03-22 01:32:38 --> Model "Ads_m" initialized
INFO - 2019-03-22 01:32:38 --> Model "dataMigration_m" initialized
INFO - 2019-03-22 01:32:38 --> Model "Common_m" initialized
DEBUG - 2019-03-22 01:32:38 --> Session: "sess_save_path" is empty; using "session.save_path" value from php.ini.
INFO - 2019-03-22 01:32:38 --> Session: Class initialized using 'files' driver.
INFO - 2019-03-22 01:32:42 --> UPDATE `ads` SET `contractOrderId` = 3
WHERE `contractOrderId` = 1
INFO - 2019-03-22 01:32:42 --> []
ERROR - 2019-03-22 01:32:46 --> Query error: Duplicate entry '3' for key 'PRIMARY' - Invalid query: INSERT INTO `contract_order` (`hospitalType`, `id`, `hospitalId`, `hospitalName`, `contractDate`, `contractType`, `title`, `adType`, `adType2`, `agencyUserId`, `manageUserId`, `taxChargeName`, `taxBusinessNo`, `taxChargeEmail`, `hospitalChargeName`, `hospitalChargePhone`, `hospitalChargeEmail`, `taxIssueRequestDate`, `agencyCompanyChargeName`, `agencyCompanyChargePhone`, `agencyCompanyChargeEmail`, `agencyCompanyId`, `agencyCompanyName`, `agencyCompanyFeeRate`, `regDate`, `taxIssueDate`, `isNetwork`, `adPrice`, `parentId`) VALUES (1, '3', '35467', '아이디치과의원', '2015-09-25 16:00:27', 1, '아이디치과의원', 1, 1, '8', '', NULL, '', NULL, NULL, NULL, NULL, '2015-09-25 16:00:27', '', '', '', '', '', '', '2015-09-25 16:00:27', '2015-09-25 16:00:27', 0, '5000000', 3)
INFO - 2019-03-22 01:32:46 --> Language file loaded: language/english/db_lang.php
ERROR - 2019-03-22 01:32:46 --> Severity: Warning --> Cannot modify header information - headers already sent by (output started at /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/DataMigration.php:464) /Users/blumine/works/goodoc_v2/event/adminApi/system/core/Common.php 570
INFO - 2019-03-22 01:41:18 --> Config Class Initialized
INFO - 2019-03-22 01:41:18 --> Hooks Class Initialized
DEBUG - 2019-03-22 01:41:18 --> UTF-8 Support Enabled
INFO - 2019-03-22 01:41:18 --> Utf8 Class Initialized
INFO - 2019-03-22 01:41:18 --> URI Class Initialized
INFO - 2019-03-22 01:41:18 --> Router Class Initialized
INFO - 2019-03-22 01:41:18 --> Output Class Initialized
INFO - 2019-03-22 01:41:18 --> Security Class Initialized
DEBUG - 2019-03-22 01:41:18 --> Global POST, GET and COOKIE data sanitized
INFO - 2019-03-22 01:41:18 --> Input Class Initialized
INFO - 2019-03-22 01:41:18 --> Language Class Initialized
INFO - 2019-03-22 01:41:18 --> Loader Class Initialized
INFO - 2019-03-22 01:41:18 --> Helper loaded: common_helper
INFO - 2019-03-22 01:41:18 --> Database Driver Class Initialized
INFO - 2019-03-22 01:41:18 --> Controller Class Initialized
INFO - 2019-03-22 01:41:18 --> Database Driver Class Initialized
INFO - 2019-03-22 01:41:18 --> Database Driver Class Initialized
INFO - 2019-03-22 01:41:18 --> Model "contractOrder_m" initialized
INFO - 2019-03-22 01:41:18 --> Model "Ads_m" initialized
INFO - 2019-03-22 01:41:18 --> Model "dataMigration_m" initialized
INFO - 2019-03-22 01:41:18 --> Model "Common_m" initialized
DEBUG - 2019-03-22 01:41:18 --> Session: "sess_save_path" is empty; using "session.save_path" value from php.ini.
INFO - 2019-03-22 01:41:18 --> Session: Class initialized using 'files' driver.
INFO - 2019-03-22 01:41:22 --> UPDATE `ads` SET `contractOrderId` = 3
WHERE `contractOrderId` = 1
INFO - 2019-03-22 01:41:22 --> []
ERROR - 2019-03-22 01:41:25 --> Query error: Duplicate entry '3' for key 'PRIMARY' - Invalid query: INSERT INTO `contract_order` (`hospitalType`, `id`, `hospitalId`, `hospitalName`, `contractDate`, `contractType`, `title`, `adType`, `adType2`, `agencyUserId`, `manageUserId`, `taxChargeName`, `taxBusinessNo`, `taxChargeEmail`, `hospitalChargeName`, `hospitalChargePhone`, `hospitalChargeEmail`, `taxIssueRequestDate`, `agencyCompanyChargeName`, `agencyCompanyChargePhone`, `agencyCompanyChargeEmail`, `agencyCompanyId`, `agencyCompanyName`, `agencyCompanyFeeRate`, `regDate`, `taxIssueDate`, `isNetwork`, `adPrice`, `parentId`) VALUES (1, '3', '35467', '아이디치과의원', '2015-09-25 16:00:27', 1, '아이디치과의원', 1, 1, '8', '', NULL, '', NULL, NULL, NULL, NULL, '2015-09-25 16:00:27', '', '', '', '', '', '', '2015-09-25 16:00:27', '2015-09-25 16:00:27', 0, '5000000', 3)
INFO - 2019-03-22 01:41:25 --> Language file loaded: language/english/db_lang.php
ERROR - 2019-03-22 01:41:25 --> Severity: Warning --> Cannot modify header information - headers already sent by (output started at /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/DataMigration.php:465) /Users/blumine/works/goodoc_v2/event/adminApi/system/core/Common.php 570
INFO - 2019-03-22 01:42:21 --> Config Class Initialized
INFO - 2019-03-22 01:42:21 --> Hooks Class Initialized
DEBUG - 2019-03-22 01:42:21 --> UTF-8 Support Enabled
INFO - 2019-03-22 01:42:21 --> Utf8 Class Initialized
INFO - 2019-03-22 01:42:21 --> URI Class Initialized
INFO - 2019-03-22 01:42:21 --> Router Class Initialized
INFO - 2019-03-22 01:42:21 --> Output Class Initialized
INFO - 2019-03-22 01:42:21 --> Security Class Initialized
DEBUG - 2019-03-22 01:42:21 --> Global POST, GET and COOKIE data sanitized
INFO - 2019-03-22 01:42:21 --> Input Class Initialized
INFO - 2019-03-22 01:42:21 --> Language Class Initialized
INFO - 2019-03-22 01:42:21 --> Loader Class Initialized
INFO - 2019-03-22 01:42:21 --> Helper loaded: common_helper
INFO - 2019-03-22 01:42:21 --> Database Driver Class Initialized
INFO - 2019-03-22 01:42:21 --> Controller Class Initialized
INFO - 2019-03-22 01:42:21 --> Database Driver Class Initialized
INFO - 2019-03-22 01:42:21 --> Database Driver Class Initialized
INFO - 2019-03-22 01:42:21 --> Model "contractOrder_m" initialized
INFO - 2019-03-22 01:42:21 --> Model "Ads_m" initialized
INFO - 2019-03-22 01:42:21 --> Model "dataMigration_m" initialized
INFO - 2019-03-22 01:42:21 --> Model "Common_m" initialized
DEBUG - 2019-03-22 01:42:21 --> Session: "sess_save_path" is empty; using "session.save_path" value from php.ini.
INFO - 2019-03-22 01:42:21 --> Session: Class initialized using 'files' driver.
INFO - 2019-03-22 01:42:25 --> UPDATE `ads` SET `contractOrderId` = 2
WHERE `contractOrderId` = 1
INFO - 2019-03-22 01:42:25 --> []
INFO - 2019-03-22 01:42:29 --> UPDATE `ads` SET `contractOrderId` = 3
WHERE `contractOrderId` = 2
INFO - 2019-03-22 01:42:29 --> []
INFO - 2019-03-22 01:42:30 --> UPDATE `ads` SET `contractOrderId` = 4
WHERE `contractOrderId` = 3
INFO - 2019-03-22 01:42:30 --> []
INFO - 2019-03-22 01:42:30 --> UPDATE `ads` SET `contractOrderId` = 5
WHERE `contractOrderId` = 4
INFO - 2019-03-22 01:42:30 --> []
INFO - 2019-03-22 01:42:32 --> UPDATE `ads` SET `contractOrderId` = 6
WHERE `contractOrderId` = 5
INFO - 2019-03-22 01:42:32 --> []
INFO - 2019-03-22 01:42:34 --> UPDATE `ads` SET `contractOrderId` = 7
WHERE `contractOrderId` = 6
INFO - 2019-03-22 01:42:34 --> []
INFO - 2019-03-22 01:42:38 --> UPDATE `ads` SET `contractOrderId` = 8
WHERE `contractOrderId` = 7
INFO - 2019-03-22 01:42:38 --> []
INFO - 2019-03-22 01:42:38 --> UPDATE `ads` SET `contractOrderId` = 9
WHERE `contractOrderId` = 8
INFO - 2019-03-22 01:42:38 --> []
INFO - 2019-03-22 01:42:38 --> UPDATE `ads` SET `contractOrderId` = 10
WHERE `contractOrderId` = 9
INFO - 2019-03-22 01:42:38 --> []
INFO - 2019-03-22 01:42:38 --> UPDATE `ads` SET `contractOrderId` = 11
WHERE `contractOrderId` = 10
INFO - 2019-03-22 01:42:38 --> []
INFO - 2019-03-22 01:42:42 --> Final output sent to browser
DEBUG - 2019-03-22 01:42:42 --> Total execution time: 20.7107
INFO - 2019-03-22 01:44:33 --> Config Class Initialized
INFO - 2019-03-22 01:44:33 --> Hooks Class Initialized
DEBUG - 2019-03-22 01:44:33 --> UTF-8 Support Enabled
INFO - 2019-03-22 01:44:33 --> Utf8 Class Initialized
INFO - 2019-03-22 01:44:33 --> URI Class Initialized
INFO - 2019-03-22 01:44:33 --> Router Class Initialized
INFO - 2019-03-22 01:44:33 --> Output Class Initialized
INFO - 2019-03-22 01:44:33 --> Security Class Initialized
DEBUG - 2019-03-22 01:44:33 --> Global POST, GET and COOKIE data sanitized
INFO - 2019-03-22 01:44:33 --> Input Class Initialized
INFO - 2019-03-22 01:44:33 --> Language Class Initialized
INFO - 2019-03-22 01:44:33 --> Loader Class Initialized
INFO - 2019-03-22 01:44:33 --> Helper loaded: common_helper
INFO - 2019-03-22 01:44:33 --> Database Driver Class Initialized
INFO - 2019-03-22 01:44:33 --> Controller Class Initialized
INFO - 2019-03-22 01:44:33 --> Database Driver Class Initialized
INFO - 2019-03-22 01:44:33 --> Database Driver Class Initialized
INFO - 2019-03-22 01:44:33 --> Model "contractOrder_m" initialized
INFO - 2019-03-22 01:44:33 --> Model "Ads_m" initialized
INFO - 2019-03-22 01:44:33 --> Model "dataMigration_m" initialized
INFO - 2019-03-22 01:44:33 --> Model "Common_m" initialized
DEBUG - 2019-03-22 01:44:33 --> Session: "sess_save_path" is empty; using "session.save_path" value from php.ini.
INFO - 2019-03-22 01:44:33 --> Session: Class initialized using 'files' driver.
INFO - 2019-03-22 01:44:37 --> UPDATE `ads` SET `contractOrderId` = 2
WHERE `contractOrderId` = 1
INFO - 2019-03-22 01:44:37 --> []
INFO - 2019-03-22 01:44:41 --> UPDATE `ads` SET `contractOrderId` = 3
WHERE `contractOrderId` = 2
INFO - 2019-03-22 01:44:41 --> []
INFO - 2019-03-22 01:44:42 --> UPDATE `ads` SET `contractOrderId` = 4
WHERE `contractOrderId` = 3
INFO - 2019-03-22 01:44:42 --> []
INFO - 2019-03-22 01:44:42 --> UPDATE `ads` SET `contractOrderId` = 5
WHERE `contractOrderId` = 4
INFO - 2019-03-22 01:44:42 --> []
INFO - 2019-03-22 01:44:44 --> UPDATE `ads` SET `contractOrderId` = 6
WHERE `contractOrderId` = 5
INFO - 2019-03-22 01:44:44 --> []
INFO - 2019-03-22 01:44:46 --> UPDATE `ads` SET `contractOrderId` = 7
WHERE `contractOrderId` = 6
INFO - 2019-03-22 01:44:46 --> []
INFO - 2019-03-22 01:44:49 --> UPDATE `ads` SET `contractOrderId` = 8
WHERE `contractOrderId` = 7
INFO - 2019-03-22 01:44:49 --> []
INFO - 2019-03-22 01:44:49 --> UPDATE `ads` SET `contractOrderId` = 9
WHERE `contractOrderId` = 8
INFO - 2019-03-22 01:44:49 --> []
INFO - 2019-03-22 01:44:50 --> UPDATE `ads` SET `contractOrderId` = 10
WHERE `contractOrderId` = 9
INFO - 2019-03-22 01:44:50 --> []
INFO - 2019-03-22 01:44:50 --> UPDATE `ads` SET `contractOrderId` = 11
WHERE `contractOrderId` = 10
INFO - 2019-03-22 01:44:50 --> []
INFO - 2019-03-22 01:44:53 --> Final output sent to browser
DEBUG - 2019-03-22 01:44:53 --> Total execution time: 19.5599
INFO - 2019-03-22 01:55:28 --> Config Class Initialized
INFO - 2019-03-22 01:55:28 --> Hooks Class Initialized
DEBUG - 2019-03-22 01:55:28 --> UTF-8 Support Enabled
INFO - 2019-03-22 01:55:28 --> Utf8 Class Initialized
INFO - 2019-03-22 01:55:28 --> URI Class Initialized
INFO - 2019-03-22 01:55:28 --> Router Class Initialized
INFO - 2019-03-22 01:55:28 --> Output Class Initialized
INFO - 2019-03-22 01:55:28 --> Security Class Initialized
DEBUG - 2019-03-22 01:55:28 --> Global POST, GET and COOKIE data sanitized
INFO - 2019-03-22 01:55:28 --> Input Class Initialized
INFO - 2019-03-22 01:55:28 --> Language Class Initialized
INFO - 2019-03-22 01:55:28 --> Loader Class Initialized
INFO - 2019-03-22 01:55:28 --> Helper loaded: common_helper
INFO - 2019-03-22 01:55:28 --> Database Driver Class Initialized
INFO - 2019-03-22 01:55:28 --> Controller Class Initialized
INFO - 2019-03-22 01:55:28 --> Database Driver Class Initialized
INFO - 2019-03-22 01:55:28 --> Database Driver Class Initialized
INFO - 2019-03-22 01:55:28 --> Model "contractOrder_m" initialized
INFO - 2019-03-22 01:55:28 --> Model "Ads_m" initialized
INFO - 2019-03-22 01:55:28 --> Model "dataMigration_m" initialized
INFO - 2019-03-22 01:55:28 --> Model "Common_m" initialized
DEBUG - 2019-03-22 01:55:28 --> Session: "sess_save_path" is empty; using "session.save_path" value from php.ini.
INFO - 2019-03-22 01:55:28 --> Session: Class initialized using 'files' driver.
INFO - 2019-03-22 01:55:34 --> UPDATE `ads` SET `contractOrderId` = 2
WHERE `contractOrderId` = 1
INFO - 2019-03-22 01:55:34 --> []
INFO - 2019-03-22 01:55:39 --> UPDATE `ads` SET `contractOrderId` = 3
WHERE `contractOrderId` = 2
INFO - 2019-03-22 01:55:39 --> []
INFO - 2019-03-22 01:55:42 --> UPDATE `ads` SET `contractOrderId` = 4
WHERE `contractOrderId` = 3
INFO - 2019-03-22 01:55:42 --> []
INFO - 2019-03-22 01:55:43 --> UPDATE `ads` SET `contractOrderId` = 5
WHERE `contractOrderId` = 4
INFO - 2019-03-22 01:55:43 --> []
INFO - 2019-03-22 01:55:45 --> UPDATE `ads` SET `contractOrderId` = 6
WHERE `contractOrderId` = 5
INFO - 2019-03-22 01:55:45 --> []
INFO - 2019-03-22 01:55:48 --> UPDATE `ads` SET `contractOrderId` = 7
WHERE `contractOrderId` = 6
INFO - 2019-03-22 01:55:48 --> []
INFO - 2019-03-22 01:55:53 --> UPDATE `ads` SET `contractOrderId` = 8
WHERE `contractOrderId` = 7
INFO - 2019-03-22 01:55:53 --> []
INFO - 2019-03-22 01:55:53 --> UPDATE `ads` SET `contractOrderId` = 9
WHERE `contractOrderId` = 8
INFO - 2019-03-22 01:55:53 --> []
INFO - 2019-03-22 01:55:54 --> UPDATE `ads` SET `contractOrderId` = 10
WHERE `contractOrderId` = 9
INFO - 2019-03-22 01:55:54 --> []
INFO - 2019-03-22 01:55:56 --> UPDATE `ads` SET `contractOrderId` = 11
WHERE `contractOrderId` = 10
INFO - 2019-03-22 01:55:56 --> []
INFO - 2019-03-22 01:56:00 --> Final output sent to browser
DEBUG - 2019-03-22 01:56:00 --> Total execution time: 32.2905
INFO - 2019-03-22 02:09:11 --> Config Class Initialized
INFO - 2019-03-22 02:09:11 --> Hooks Class Initialized
DEBUG - 2019-03-22 02:09:11 --> UTF-8 Support Enabled
INFO - 2019-03-22 02:09:11 --> Utf8 Class Initialized
INFO - 2019-03-22 02:09:11 --> URI Class Initialized
INFO - 2019-03-22 02:09:11 --> Router Class Initialized
INFO - 2019-03-22 02:09:11 --> Output Class Initialized
INFO - 2019-03-22 02:09:11 --> Security Class Initialized
DEBUG - 2019-03-22 02:09:11 --> Global POST, GET and COOKIE data sanitized
INFO - 2019-03-22 02:09:11 --> Input Class Initialized
INFO - 2019-03-22 02:09:11 --> Language Class Initialized
INFO - 2019-03-22 02:09:11 --> Loader Class Initialized
INFO - 2019-03-22 02:09:11 --> Helper loaded: common_helper
INFO - 2019-03-22 02:09:11 --> Database Driver Class Initialized
INFO - 2019-03-22 02:09:11 --> Controller Class Initialized
INFO - 2019-03-22 02:09:11 --> Database Driver Class Initialized
INFO - 2019-03-22 02:09:11 --> Database Driver Class Initialized
INFO - 2019-03-22 02:09:11 --> Model "contractOrder_m" initialized
INFO - 2019-03-22 02:09:11 --> Model "Ads_m" initialized
INFO - 2019-03-22 02:09:11 --> Model "dataMigration_m" initialized
INFO - 2019-03-22 02:09:11 --> Model "Common_m" initialized
DEBUG - 2019-03-22 02:09:11 --> Session: "sess_save_path" is empty; using "session.save_path" value from php.ini.
INFO - 2019-03-22 02:09:11 --> Session: Class initialized using 'files' driver.
INFO - 2019-03-22 02:09:18 --> UPDATE `ads` SET `contractOrderId` = 2
WHERE `contractOrderId` = 1
INFO - 2019-03-22 02:09:18 --> []
INFO - 2019-03-22 02:09:27 --> UPDATE `ads` SET `contractOrderId` = 3
WHERE `contractOrderId` = 2
INFO - 2019-03-22 02:09:27 --> []
INFO - 2019-03-22 02:09:30 --> UPDATE `ads` SET `contractOrderId` = 4
WHERE `contractOrderId` = 3
INFO - 2019-03-22 02:09:30 --> []
INFO - 2019-03-22 02:09:31 --> UPDATE `ads` SET `contractOrderId` = 5
WHERE `contractOrderId` = 4
INFO - 2019-03-22 02:09:31 --> []
INFO - 2019-03-22 02:09:34 --> UPDATE `ads` SET `contractOrderId` = 6
WHERE `contractOrderId` = 5
INFO - 2019-03-22 02:09:34 --> []
INFO - 2019-03-22 02:09:37 --> UPDATE `ads` SET `contractOrderId` = 7
WHERE `contractOrderId` = 6
INFO - 2019-03-22 02:09:37 --> []
INFO - 2019-03-22 02:09:46 --> UPDATE `ads` SET `contractOrderId` = 8
WHERE `contractOrderId` = 7
INFO - 2019-03-22 02:09:46 --> []
INFO - 2019-03-22 02:09:46 --> UPDATE `ads` SET `contractOrderId` = 9
WHERE `contractOrderId` = 8
INFO - 2019-03-22 02:09:46 --> []
INFO - 2019-03-22 02:09:47 --> UPDATE `ads` SET `contractOrderId` = 10
WHERE `contractOrderId` = 9
INFO - 2019-03-22 02:09:47 --> []
INFO - 2019-03-22 02:09:47 --> UPDATE `ads` SET `contractOrderId` = 11
WHERE `contractOrderId` = 10
INFO - 2019-03-22 02:09:47 --> []
INFO - 2019-03-22 02:09:53 --> Final output sent to browser
DEBUG - 2019-03-22 02:09:53 --> Total execution time: 41.7614
INFO - 2019-03-22 02:11:09 --> Config Class Initialized
INFO - 2019-03-22 02:11:09 --> Hooks Class Initialized
DEBUG - 2019-03-22 02:11:09 --> UTF-8 Support Enabled
INFO - 2019-03-22 02:11:09 --> Utf8 Class Initialized
INFO - 2019-03-22 02:11:09 --> URI Class Initialized
INFO - 2019-03-22 02:11:09 --> Router Class Initialized
INFO - 2019-03-22 02:11:09 --> Output Class Initialized
INFO - 2019-03-22 02:11:09 --> Security Class Initialized
DEBUG - 2019-03-22 02:11:09 --> Global POST, GET and COOKIE data sanitized
INFO - 2019-03-22 02:11:09 --> Input Class Initialized
INFO - 2019-03-22 02:11:09 --> Language Class Initialized
INFO - 2019-03-22 02:11:09 --> Loader Class Initialized
INFO - 2019-03-22 02:11:09 --> Helper loaded: common_helper
INFO - 2019-03-22 02:11:09 --> Database Driver Class Initialized
INFO - 2019-03-22 02:11:09 --> Controller Class Initialized
INFO - 2019-03-22 02:11:09 --> Database Driver Class Initialized
INFO - 2019-03-22 02:11:09 --> Database Driver Class Initialized
INFO - 2019-03-22 02:11:09 --> Model "contractOrder_m" initialized
INFO - 2019-03-22 02:11:09 --> Model "Ads_m" initialized
INFO - 2019-03-22 02:11:09 --> Model "dataMigration_m" initialized
INFO - 2019-03-22 02:11:09 --> Model "Common_m" initialized
DEBUG - 2019-03-22 02:11:09 --> Session: "sess_save_path" is empty; using "session.save_path" value from php.ini.
INFO - 2019-03-22 02:11:09 --> Session: Class initialized using 'files' driver.
INFO - 2019-03-22 02:12:05 --> Config Class Initialized
INFO - 2019-03-22 02:12:05 --> Hooks Class Initialized
DEBUG - 2019-03-22 02:12:05 --> UTF-8 Support Enabled
INFO - 2019-03-22 02:12:05 --> Utf8 Class Initialized
INFO - 2019-03-22 02:12:05 --> URI Class Initialized
INFO - 2019-03-22 02:12:05 --> Router Class Initialized
INFO - 2019-03-22 02:12:05 --> Output Class Initialized
INFO - 2019-03-22 02:12:05 --> Security Class Initialized
DEBUG - 2019-03-22 02:12:05 --> Global POST, GET and COOKIE data sanitized
INFO - 2019-03-22 02:12:05 --> Input Class Initialized
INFO - 2019-03-22 02:12:05 --> Language Class Initialized
INFO - 2019-03-22 02:12:05 --> Loader Class Initialized
INFO - 2019-03-22 02:12:05 --> Helper loaded: common_helper
INFO - 2019-03-22 02:12:05 --> Database Driver Class Initialized
INFO - 2019-03-22 02:12:05 --> Controller Class Initialized
INFO - 2019-03-22 02:12:05 --> Database Driver Class Initialized
INFO - 2019-03-22 02:12:05 --> Database Driver Class Initialized
INFO - 2019-03-22 02:12:05 --> Model "contractOrder_m" initialized
INFO - 2019-03-22 02:12:05 --> Model "Ads_m" initialized
INFO - 2019-03-22 02:12:05 --> Model "dataMigration_m" initialized
INFO - 2019-03-22 02:12:05 --> Model "Common_m" initialized
DEBUG - 2019-03-22 02:12:05 --> Session: "sess_save_path" is empty; using "session.save_path" value from php.ini.
INFO - 2019-03-22 02:12:05 --> Session: Class initialized using 'files' driver.
INFO - 2019-03-22 02:12:53 --> Config Class Initialized
INFO - 2019-03-22 02:12:53 --> Hooks Class Initialized
DEBUG - 2019-03-22 02:12:54 --> UTF-8 Support Enabled
INFO - 2019-03-22 02:12:54 --> Utf8 Class Initialized
INFO - 2019-03-22 02:12:54 --> URI Class Initialized
INFO - 2019-03-22 02:12:54 --> Router Class Initialized
INFO - 2019-03-22 02:12:54 --> Output Class Initialized
INFO - 2019-03-22 02:12:54 --> Security Class Initialized
DEBUG - 2019-03-22 02:12:54 --> Global POST, GET and COOKIE data sanitized
INFO - 2019-03-22 02:12:54 --> Input Class Initialized
INFO - 2019-03-22 02:12:54 --> Language Class Initialized
INFO - 2019-03-22 02:12:54 --> Loader Class Initialized
INFO - 2019-03-22 02:12:54 --> Helper loaded: common_helper
INFO - 2019-03-22 02:12:54 --> Database Driver Class Initialized
INFO - 2019-03-22 02:12:54 --> Controller Class Initialized
INFO - 2019-03-22 02:12:54 --> Database Driver Class Initialized
INFO - 2019-03-22 02:12:54 --> Database Driver Class Initialized
INFO - 2019-03-22 02:12:54 --> Model "contractOrder_m" initialized
INFO - 2019-03-22 02:12:54 --> Model "Ads_m" initialized
INFO - 2019-03-22 02:12:54 --> Model "dataMigration_m" initialized
INFO - 2019-03-22 02:12:54 --> Model "Common_m" initialized
DEBUG - 2019-03-22 02:12:54 --> Session: "sess_save_path" is empty; using "session.save_path" value from php.ini.
INFO - 2019-03-22 02:12:54 --> Session: Class initialized using 'files' driver.
INFO - 2019-03-22 02:19:45 --> Config Class Initialized
INFO - 2019-03-22 02:19:45 --> Hooks Class Initialized
DEBUG - 2019-03-22 02:19:45 --> UTF-8 Support Enabled
INFO - 2019-03-22 02:19:45 --> Utf8 Class Initialized
INFO - 2019-03-22 02:19:45 --> URI Class Initialized
INFO - 2019-03-22 02:19:45 --> Router Class Initialized
INFO - 2019-03-22 02:19:45 --> Output Class Initialized
INFO - 2019-03-22 02:19:45 --> Security Class Initialized
DEBUG - 2019-03-22 02:19:45 --> Global POST, GET and COOKIE data sanitized
INFO - 2019-03-22 02:19:45 --> Input Class Initialized
INFO - 2019-03-22 02:19:45 --> Language Class Initialized
INFO - 2019-03-22 02:19:45 --> Loader Class Initialized
INFO - 2019-03-22 02:19:45 --> Helper loaded: common_helper
INFO - 2019-03-22 02:19:45 --> Database Driver Class Initialized
INFO - 2019-03-22 02:19:45 --> Controller Class Initialized
INFO - 2019-03-22 02:19:45 --> Database Driver Class Initialized
INFO - 2019-03-22 02:19:45 --> Database Driver Class Initialized
INFO - 2019-03-22 02:19:46 --> Model "contractOrder_m" initialized
INFO - 2019-03-22 02:19:46 --> Model "Ads_m" initialized
INFO - 2019-03-22 02:19:46 --> Model "dataMigration_m" initialized
INFO - 2019-03-22 02:19:46 --> Model "Common_m" initialized
DEBUG - 2019-03-22 02:19:46 --> Session: "sess_save_path" is empty; using "session.save_path" value from php.ini.
INFO - 2019-03-22 02:19:46 --> Session: Class initialized using 'files' driver.
INFO - 2019-03-22 02:21:43 --> Config Class Initialized
INFO - 2019-03-22 02:21:43 --> Hooks Class Initialized
DEBUG - 2019-03-22 02:21:44 --> UTF-8 Support Enabled
INFO - 2019-03-22 02:21:44 --> Utf8 Class Initialized
INFO - 2019-03-22 02:21:44 --> URI Class Initialized
INFO - 2019-03-22 02:21:44 --> Router Class Initialized
INFO - 2019-03-22 02:21:44 --> Output Class Initialized
INFO - 2019-03-22 02:21:44 --> Security Class Initialized
DEBUG - 2019-03-22 02:21:44 --> Global POST, GET and COOKIE data sanitized
INFO - 2019-03-22 02:21:44 --> Input Class Initialized
INFO - 2019-03-22 02:21:44 --> Language Class Initialized
INFO - 2019-03-22 02:21:44 --> Loader Class Initialized
INFO - 2019-03-22 02:21:44 --> Helper loaded: common_helper
INFO - 2019-03-22 02:21:44 --> Database Driver Class Initialized
INFO - 2019-03-22 02:21:44 --> Controller Class Initialized
INFO - 2019-03-22 02:21:44 --> Database Driver Class Initialized
INFO - 2019-03-22 02:21:44 --> Database Driver Class Initialized
INFO - 2019-03-22 02:21:44 --> Model "contractOrder_m" initialized
INFO - 2019-03-22 02:21:44 --> Model "Ads_m" initialized
INFO - 2019-03-22 02:21:44 --> Model "dataMigration_m" initialized
INFO - 2019-03-22 02:21:44 --> Model "Common_m" initialized
DEBUG - 2019-03-22 02:21:44 --> Session: "sess_save_path" is empty; using "session.save_path" value from php.ini.
INFO - 2019-03-22 02:21:44 --> Session: Class initialized using 'files' driver.
INFO - 2019-03-22 02:21:49 --> UPDATE `ads` SET `contractOrderId` = 2
WHERE `contractOrderId` = 1
INFO - 2019-03-22 02:21:49 --> []
INFO - 2019-03-22 02:21:55 --> UPDATE `ads` SET `contractOrderId` = 3
WHERE `contractOrderId` = 2
INFO - 2019-03-22 02:21:55 --> []
INFO - 2019-03-22 02:21:57 --> UPDATE `ads` SET `contractOrderId` = 4
WHERE `contractOrderId` = 3
INFO - 2019-03-22 02:21:57 --> []
INFO - 2019-03-22 02:21:58 --> UPDATE `ads` SET `contractOrderId` = 5
WHERE `contractOrderId` = 4
INFO - 2019-03-22 02:21:58 --> []
INFO - 2019-03-22 02:22:01 --> UPDATE `ads` SET `contractOrderId` = 6
WHERE `contractOrderId` = 5
INFO - 2019-03-22 02:22:01 --> []
INFO - 2019-03-22 02:22:03 --> UPDATE `ads` SET `contractOrderId` = 7
WHERE `contractOrderId` = 6
INFO - 2019-03-22 02:22:03 --> []
INFO - 2019-03-22 02:22:08 --> UPDATE `ads` SET `contractOrderId` = 8
WHERE `contractOrderId` = 7
INFO - 2019-03-22 02:22:08 --> []
INFO - 2019-03-22 02:22:09 --> UPDATE `ads` SET `contractOrderId` = 9
WHERE `contractOrderId` = 8
INFO - 2019-03-22 02:22:09 --> []
INFO - 2019-03-22 02:22:09 --> UPDATE `ads` SET `contractOrderId` = 10
WHERE `contractOrderId` = 9
INFO - 2019-03-22 02:22:09 --> []
INFO - 2019-03-22 02:22:09 --> UPDATE `ads` SET `contractOrderId` = 11
WHERE `contractOrderId` = 10
INFO - 2019-03-22 02:22:09 --> []
INFO - 2019-03-22 02:22:33 --> UPDATE `ads` SET `contractOrderId` = 12
WHERE `contractOrderId` = 11
INFO - 2019-03-22 02:22:33 --> []
INFO - 2019-03-22 02:22:36 --> UPDATE `ads` SET `contractOrderId` = 13
WHERE `contractOrderId` = 12
INFO - 2019-03-22 02:22:36 --> []
INFO - 2019-03-22 02:22:38 --> UPDATE `ads` SET `contractOrderId` = 14
WHERE `contractOrderId` = 13
INFO - 2019-03-22 02:22:38 --> []
INFO - 2019-03-22 02:22:46 --> UPDATE `ads` SET `contractOrderId` = 15
WHERE `contractOrderId` = 14
INFO - 2019-03-22 02:22:46 --> []
INFO - 2019-03-22 02:22:51 --> UPDATE `ads` SET `contractOrderId` = 16
WHERE `contractOrderId` = 15
INFO - 2019-03-22 02:22:51 --> []
INFO - 2019-03-22 02:22:54 --> UPDATE `ads` SET `contractOrderId` = 17
WHERE `contractOrderId` = 16
INFO - 2019-03-22 02:22:54 --> []
INFO - 2019-03-22 02:23:02 --> UPDATE `ads` SET `contractOrderId` = 18
WHERE `contractOrderId` = 17
INFO - 2019-03-22 02:23:02 --> []
INFO - 2019-03-22 02:23:04 --> UPDATE `ads` SET `contractOrderId` = 19
WHERE `contractOrderId` = 18
INFO - 2019-03-22 02:23:04 --> []
INFO - 2019-03-22 02:23:07 --> UPDATE `ads` SET `contractOrderId` = 20
WHERE `contractOrderId` = 19
INFO - 2019-03-22 02:23:07 --> []
INFO - 2019-03-22 02:23:09 --> UPDATE `ads` SET `contractOrderId` = 21
WHERE `contractOrderId` = 20
INFO - 2019-03-22 02:23:09 --> []
INFO - 2019-03-22 02:23:12 --> UPDATE `ads` SET `contractOrderId` = 22
WHERE `contractOrderId` = 21
INFO - 2019-03-22 02:23:12 --> []
INFO - 2019-03-22 02:23:14 --> UPDATE `ads` SET `contractOrderId` = 23
WHERE `contractOrderId` = 22
INFO - 2019-03-22 02:23:14 --> []
INFO - 2019-03-22 02:23:15 --> UPDATE `ads` SET `contractOrderId` = 24
WHERE `contractOrderId` = 23
INFO - 2019-03-22 02:23:15 --> []
INFO - 2019-03-22 02:23:16 --> UPDATE `ads` SET `contractOrderId` = 25
WHERE `contractOrderId` = 24
INFO - 2019-03-22 02:23:16 --> []
INFO - 2019-03-22 02:23:18 --> UPDATE `ads` SET `contractOrderId` = 26
WHERE `contractOrderId` = 25
INFO - 2019-03-22 02:23:18 --> []
INFO - 2019-03-22 02:23:20 --> UPDATE `ads` SET `contractOrderId` = 27
WHERE `contractOrderId` = 26
INFO - 2019-03-22 02:23:20 --> []
INFO - 2019-03-22 02:23:22 --> UPDATE `ads` SET `contractOrderId` = 28
WHERE `contractOrderId` = 27
INFO - 2019-03-22 02:23:22 --> []
INFO - 2019-03-22 02:23:23 --> UPDATE `ads` SET `contractOrderId` = 29
WHERE `contractOrderId` = 28
INFO - 2019-03-22 02:23:23 --> []
INFO - 2019-03-22 02:23:24 --> UPDATE `ads` SET `contractOrderId` = 30
WHERE `contractOrderId` = 29
INFO - 2019-03-22 02:23:24 --> []
INFO - 2019-03-22 02:23:28 --> UPDATE `ads` SET `contractOrderId` = 31
WHERE `contractOrderId` = 30
INFO - 2019-03-22 02:23:28 --> []
INFO - 2019-03-22 02:23:30 --> UPDATE `ads` SET `contractOrderId` = 32
WHERE `contractOrderId` = 31
INFO - 2019-03-22 02:23:30 --> []
INFO - 2019-03-22 02:23:32 --> UPDATE `ads` SET `contractOrderId` = 33
WHERE `contractOrderId` = 32
INFO - 2019-03-22 02:23:32 --> []
INFO - 2019-03-22 02:23:34 --> UPDATE `ads` SET `contractOrderId` = 34
WHERE `contractOrderId` = 33
INFO - 2019-03-22 02:23:34 --> []
INFO - 2019-03-22 02:23:37 --> UPDATE `ads` SET `contractOrderId` = 35
WHERE `contractOrderId` = 34
INFO - 2019-03-22 02:23:37 --> []
INFO - 2019-03-22 02:23:39 --> UPDATE `ads` SET `contractOrderId` = 36
WHERE `contractOrderId` = 35
INFO - 2019-03-22 02:23:39 --> []
INFO - 2019-03-22 02:23:41 --> UPDATE `ads` SET `contractOrderId` = 37
WHERE `contractOrderId` = 36
INFO - 2019-03-22 02:23:41 --> []
INFO - 2019-03-22 02:23:42 --> UPDATE `ads` SET `contractOrderId` = 38
WHERE `contractOrderId` = 37
INFO - 2019-03-22 02:23:42 --> []
INFO - 2019-03-22 02:23:43 --> UPDATE `ads` SET `contractOrderId` = 39
WHERE `contractOrderId` = 38
INFO - 2019-03-22 02:23:43 --> []
INFO - 2019-03-22 02:23:45 --> UPDATE `ads` SET `contractOrderId` = 40
WHERE `contractOrderId` = 39
INFO - 2019-03-22 02:23:45 --> []
INFO - 2019-03-22 02:23:47 --> UPDATE `ads` SET `contractOrderId` = 41
WHERE `contractOrderId` = 40
INFO - 2019-03-22 02:23:47 --> []
INFO - 2019-03-22 02:23:49 --> UPDATE `ads` SET `contractOrderId` = 42
WHERE `contractOrderId` = 41
INFO - 2019-03-22 02:23:49 --> []
INFO - 2019-03-22 02:23:50 --> UPDATE `ads` SET `contractOrderId` = 43
WHERE `contractOrderId` = 42
INFO - 2019-03-22 02:23:50 --> []
INFO - 2019-03-22 02:23:52 --> UPDATE `ads` SET `contractOrderId` = 44
WHERE `contractOrderId` = 43
INFO - 2019-03-22 02:23:52 --> []
INFO - 2019-03-22 02:23:54 --> UPDATE `ads` SET `contractOrderId` = 45
WHERE `contractOrderId` = 44
INFO - 2019-03-22 02:23:54 --> []
INFO - 2019-03-22 02:23:57 --> UPDATE `ads` SET `contractOrderId` = 46
WHERE `contractOrderId` = 45
INFO - 2019-03-22 02:23:57 --> []
INFO - 2019-03-22 02:23:58 --> UPDATE `ads` SET `contractOrderId` = 47
WHERE `contractOrderId` = 46
INFO - 2019-03-22 02:23:58 --> []
INFO - 2019-03-22 02:23:59 --> Final output sent to browser
DEBUG - 2019-03-22 02:23:59 --> Total execution time: 135.6046
INFO - 2019-03-22 02:25:15 --> Config Class Initialized
INFO - 2019-03-22 02:25:15 --> Hooks Class Initialized
DEBUG - 2019-03-22 02:25:15 --> UTF-8 Support Enabled
INFO - 2019-03-22 02:25:15 --> Utf8 Class Initialized
INFO - 2019-03-22 02:25:15 --> URI Class Initialized
INFO - 2019-03-22 02:25:15 --> Router Class Initialized
INFO - 2019-03-22 02:25:15 --> Output Class Initialized
INFO - 2019-03-22 02:25:15 --> Security Class Initialized
DEBUG - 2019-03-22 02:25:15 --> Global POST, GET and COOKIE data sanitized
INFO - 2019-03-22 02:25:15 --> Input Class Initialized
INFO - 2019-03-22 02:25:15 --> Language Class Initialized
INFO - 2019-03-22 02:25:15 --> Loader Class Initialized
INFO - 2019-03-22 02:25:15 --> Helper loaded: common_helper
INFO - 2019-03-22 02:25:15 --> Database Driver Class Initialized
INFO - 2019-03-22 02:25:15 --> Controller Class Initialized
INFO - 2019-03-22 02:25:15 --> Database Driver Class Initialized
INFO - 2019-03-22 02:25:15 --> Database Driver Class Initialized
INFO - 2019-03-22 02:25:15 --> Model "contractOrder_m" initialized
INFO - 2019-03-22 02:25:15 --> Model "Ads_m" initialized
INFO - 2019-03-22 02:25:15 --> Model "dataMigration_m" initialized
INFO - 2019-03-22 02:25:15 --> Model "Common_m" initialized
DEBUG - 2019-03-22 02:25:15 --> Session: "sess_save_path" is empty; using "session.save_path" value from php.ini.
INFO - 2019-03-22 02:25:15 --> Session: Class initialized using 'files' driver.
INFO - 2019-03-22 02:25:21 --> UPDATE `ads` SET `contractOrderId` = 2
WHERE `contractOrderId` = 1
INFO - 2019-03-22 02:25:21 --> []
INFO - 2019-03-22 02:25:27 --> UPDATE `ads` SET `contractOrderId` = 3
WHERE `contractOrderId` = 2
INFO - 2019-03-22 02:25:27 --> []
INFO - 2019-03-22 02:25:28 --> UPDATE `ads` SET `contractOrderId` = 4
WHERE `contractOrderId` = 3
INFO - 2019-03-22 02:25:28 --> []
INFO - 2019-03-22 02:25:29 --> UPDATE `ads` SET `contractOrderId` = 5
WHERE `contractOrderId` = 4
INFO - 2019-03-22 02:25:29 --> []
INFO - 2019-03-22 02:25:32 --> UPDATE `ads` SET `contractOrderId` = 6
WHERE `contractOrderId` = 5
INFO - 2019-03-22 02:25:32 --> []
INFO - 2019-03-22 02:25:35 --> UPDATE `ads` SET `contractOrderId` = 7
WHERE `contractOrderId` = 6
INFO - 2019-03-22 02:25:35 --> []
INFO - 2019-03-22 02:25:40 --> UPDATE `ads` SET `contractOrderId` = 8
WHERE `contractOrderId` = 7
INFO - 2019-03-22 02:25:40 --> []
INFO - 2019-03-22 02:25:41 --> UPDATE `ads` SET `contractOrderId` = 9
WHERE `contractOrderId` = 8
INFO - 2019-03-22 02:25:41 --> []
INFO - 2019-03-22 02:25:41 --> UPDATE `ads` SET `contractOrderId` = 10
WHERE `contractOrderId` = 9
INFO - 2019-03-22 02:25:41 --> []
INFO - 2019-03-22 02:25:41 --> UPDATE `ads` SET `contractOrderId` = 11
WHERE `contractOrderId` = 10
INFO - 2019-03-22 02:25:41 --> []
INFO - 2019-03-22 02:26:06 --> UPDATE `ads` SET `contractOrderId` = 12
WHERE `contractOrderId` = 11
INFO - 2019-03-22 02:26:06 --> []
INFO - 2019-03-22 02:26:09 --> UPDATE `ads` SET `contractOrderId` = 13
WHERE `contractOrderId` = 12
INFO - 2019-03-22 02:26:09 --> []
INFO - 2019-03-22 02:26:10 --> UPDATE `ads` SET `contractOrderId` = 14
WHERE `contractOrderId` = 13
INFO - 2019-03-22 02:26:10 --> []
INFO - 2019-03-22 02:26:19 --> UPDATE `ads` SET `contractOrderId` = 15
WHERE `contractOrderId` = 14
INFO - 2019-03-22 02:26:19 --> []
INFO - 2019-03-22 02:26:24 --> UPDATE `ads` SET `contractOrderId` = 16
WHERE `contractOrderId` = 15
INFO - 2019-03-22 02:26:24 --> []
INFO - 2019-03-22 02:26:27 --> UPDATE `ads` SET `contractOrderId` = 17
WHERE `contractOrderId` = 16
INFO - 2019-03-22 02:26:27 --> []
INFO - 2019-03-22 02:26:34 --> UPDATE `ads` SET `contractOrderId` = 18
WHERE `contractOrderId` = 17
INFO - 2019-03-22 02:26:34 --> []
INFO - 2019-03-22 02:26:34 --> UPDATE `ads` SET `contractOrderId` = 19
WHERE `contractOrderId` = 18
INFO - 2019-03-22 02:26:34 --> []
INFO - 2019-03-22 02:26:37 --> UPDATE `ads` SET `contractOrderId` = 20
WHERE `contractOrderId` = 19
INFO - 2019-03-22 02:26:37 --> []
INFO - 2019-03-22 02:26:39 --> UPDATE `ads` SET `contractOrderId` = 21
WHERE `contractOrderId` = 20
INFO - 2019-03-22 02:26:39 --> []
INFO - 2019-03-22 02:26:41 --> UPDATE `ads` SET `contractOrderId` = 22
WHERE `contractOrderId` = 21
INFO - 2019-03-22 02:26:41 --> []
INFO - 2019-03-22 02:26:43 --> UPDATE `ads` SET `contractOrderId` = 23
WHERE `contractOrderId` = 22
INFO - 2019-03-22 02:26:43 --> []
INFO - 2019-03-22 02:26:44 --> UPDATE `ads` SET `contractOrderId` = 24
WHERE `contractOrderId` = 23
INFO - 2019-03-22 02:26:44 --> []
INFO - 2019-03-22 02:26:45 --> UPDATE `ads` SET `contractOrderId` = 25
WHERE `contractOrderId` = 24
INFO - 2019-03-22 02:26:45 --> []
INFO - 2019-03-22 02:26:46 --> UPDATE `ads` SET `contractOrderId` = 26
WHERE `contractOrderId` = 25
INFO - 2019-03-22 02:26:46 --> []
INFO - 2019-03-22 02:26:49 --> UPDATE `ads` SET `contractOrderId` = 27
WHERE `contractOrderId` = 26
INFO - 2019-03-22 02:26:49 --> []
INFO - 2019-03-22 02:26:50 --> UPDATE `ads` SET `contractOrderId` = 28
WHERE `contractOrderId` = 27
INFO - 2019-03-22 02:26:50 --> []
INFO - 2019-03-22 02:26:51 --> UPDATE `ads` SET `contractOrderId` = 29
WHERE `contractOrderId` = 28
INFO - 2019-03-22 02:26:51 --> []
INFO - 2019-03-22 02:26:52 --> UPDATE `ads` SET `contractOrderId` = 30
WHERE `contractOrderId` = 29
INFO - 2019-03-22 02:26:52 --> []
INFO - 2019-03-22 02:26:55 --> UPDATE `ads` SET `contractOrderId` = 31
WHERE `contractOrderId` = 30
INFO - 2019-03-22 02:26:55 --> []
INFO - 2019-03-22 02:26:57 --> UPDATE `ads` SET `contractOrderId` = 32
WHERE `contractOrderId` = 31
INFO - 2019-03-22 02:26:57 --> []
INFO - 2019-03-22 02:26:59 --> UPDATE `ads` SET `contractOrderId` = 33
WHERE `contractOrderId` = 32
INFO - 2019-03-22 02:26:59 --> []
INFO - 2019-03-22 02:27:01 --> UPDATE `ads` SET `contractOrderId` = 34
WHERE `contractOrderId` = 33
INFO - 2019-03-22 02:27:01 --> []
INFO - 2019-03-22 02:27:03 --> UPDATE `ads` SET `contractOrderId` = 35
WHERE `contractOrderId` = 34
INFO - 2019-03-22 02:27:03 --> []
INFO - 2019-03-22 02:27:05 --> UPDATE `ads` SET `contractOrderId` = 36
WHERE `contractOrderId` = 35
INFO - 2019-03-22 02:27:05 --> []
INFO - 2019-03-22 02:27:07 --> UPDATE `ads` SET `contractOrderId` = 37
WHERE `contractOrderId` = 36
INFO - 2019-03-22 02:27:07 --> []
INFO - 2019-03-22 02:27:07 --> UPDATE `ads` SET `contractOrderId` = 38
WHERE `contractOrderId` = 37
INFO - 2019-03-22 02:27:07 --> []
INFO - 2019-03-22 02:27:08 --> UPDATE `ads` SET `contractOrderId` = 39
WHERE `contractOrderId` = 38
INFO - 2019-03-22 02:27:08 --> []
INFO - 2019-03-22 02:27:10 --> UPDATE `ads` SET `contractOrderId` = 40
WHERE `contractOrderId` = 39
INFO - 2019-03-22 02:27:10 --> []
INFO - 2019-03-22 02:27:11 --> UPDATE `ads` SET `contractOrderId` = 41
WHERE `contractOrderId` = 40
INFO - 2019-03-22 02:27:11 --> []
INFO - 2019-03-22 02:27:12 --> UPDATE `ads` SET `contractOrderId` = 42
WHERE `contractOrderId` = 41
INFO - 2019-03-22 02:27:12 --> []
INFO - 2019-03-22 02:27:13 --> UPDATE `ads` SET `contractOrderId` = 43
WHERE `contractOrderId` = 42
INFO - 2019-03-22 02:27:13 --> []
INFO - 2019-03-22 02:27:14 --> UPDATE `ads` SET `contractOrderId` = 44
WHERE `contractOrderId` = 43
INFO - 2019-03-22 02:27:14 --> []
INFO - 2019-03-22 02:27:16 --> UPDATE `ads` SET `contractOrderId` = 45
WHERE `contractOrderId` = 44
INFO - 2019-03-22 02:27:16 --> []
INFO - 2019-03-22 02:27:17 --> UPDATE `ads` SET `contractOrderId` = 46
WHERE `contractOrderId` = 45
INFO - 2019-03-22 02:27:17 --> []
INFO - 2019-03-22 02:27:19 --> UPDATE `ads` SET `contractOrderId` = 47
WHERE `contractOrderId` = 46
INFO - 2019-03-22 02:27:19 --> []
INFO - 2019-03-22 02:27:20 --> Final output sent to browser
DEBUG - 2019-03-22 02:27:20 --> Total execution time: 125.1572
