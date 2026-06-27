<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

INFO - 2019-02-15 01:49:25 --> Config Class Initialized
INFO - 2019-02-15 01:49:25 --> Hooks Class Initialized
DEBUG - 2019-02-15 01:49:25 --> UTF-8 Support Enabled
INFO - 2019-02-15 01:49:25 --> Utf8 Class Initialized
INFO - 2019-02-15 01:49:25 --> URI Class Initialized
INFO - 2019-02-15 01:49:25 --> Router Class Initialized
INFO - 2019-02-15 01:49:25 --> Output Class Initialized
INFO - 2019-02-15 01:49:25 --> Security Class Initialized
DEBUG - 2019-02-15 01:49:25 --> Global POST, GET and COOKIE data sanitized
INFO - 2019-02-15 01:49:25 --> Input Class Initialized
INFO - 2019-02-15 01:49:25 --> Language Class Initialized
INFO - 2019-02-15 01:49:25 --> Loader Class Initialized
INFO - 2019-02-15 01:49:25 --> Helper loaded: common_helper
INFO - 2019-02-15 01:49:25 --> Database Driver Class Initialized
INFO - 2019-02-15 01:49:25 --> Controller Class Initialized
INFO - 2019-02-15 01:49:25 --> Database Driver Class Initialized
INFO - 2019-02-15 01:49:25 --> Model "contractOrder_m" initialized
INFO - 2019-02-15 01:49:25 --> Model "Ads_m" initialized
INFO - 2019-02-15 01:49:25 --> Model "dataMigration_m" initialized
ERROR - 2019-02-15 01:49:25 --> Severity: Notice --> Undefined property: DataMigration::$master /Users/blumine/works/goodoc_v2/event/adminApi/system/core/Model.php 73
ERROR - 2019-02-15 01:49:25 --> 
        Exception of type \'Error\' occurred with Message: Call to a member function insert() on null in File /Users/blumine/works/goodoc_v2/event/adminApi/application/models/DataMigration_m.php at Line 30
        
 Backtrace 
.#0 /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/DataMigration.php(294): dataMigration_m->setContractOrder(Array)
#1 /Users/blumine/works/goodoc_v2/event/adminApi/system/core/CodeIgniter.php(532): DataMigration->contractProcess()
#2 /Users/blumine/works/goodoc_v2/event/adminApi/index.php(327): require_once('/Users/blumine/...')
#3 {main}
    
ERROR - 2019-02-15 01:49:26 --> Severity: error --> Exception: Call to a member function insert() on null /Users/blumine/works/goodoc_v2/event/adminApi/application/models/DataMigration_m.php 30
INFO - 2019-02-15 01:50:21 --> Config Class Initialized
INFO - 2019-02-15 01:50:21 --> Hooks Class Initialized
DEBUG - 2019-02-15 01:50:21 --> UTF-8 Support Enabled
INFO - 2019-02-15 01:50:21 --> Utf8 Class Initialized
INFO - 2019-02-15 01:50:21 --> URI Class Initialized
INFO - 2019-02-15 01:50:21 --> Router Class Initialized
INFO - 2019-02-15 01:50:21 --> Output Class Initialized
INFO - 2019-02-15 01:50:21 --> Security Class Initialized
DEBUG - 2019-02-15 01:50:21 --> Global POST, GET and COOKIE data sanitized
INFO - 2019-02-15 01:50:21 --> Input Class Initialized
INFO - 2019-02-15 01:50:21 --> Language Class Initialized
INFO - 2019-02-15 01:50:21 --> Loader Class Initialized
INFO - 2019-02-15 01:50:21 --> Helper loaded: common_helper
INFO - 2019-02-15 01:50:21 --> Database Driver Class Initialized
INFO - 2019-02-15 01:50:21 --> Controller Class Initialized
INFO - 2019-02-15 01:50:21 --> Database Driver Class Initialized
INFO - 2019-02-15 01:50:21 --> Model "contractOrder_m" initialized
INFO - 2019-02-15 01:50:21 --> Model "Ads_m" initialized
INFO - 2019-02-15 01:50:21 --> Database Driver Class Initialized
INFO - 2019-02-15 01:50:21 --> Model "dataMigration_m" initialized
ERROR - 2019-02-15 01:50:21 --> Severity: Notice --> Undefined index: id /Users/blumine/works/goodoc_v2/event/adminApi/application/models/DataMigration_m.php 43
ERROR - 2019-02-15 01:50:21 --> Severity: Notice --> Undefined index: id /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/DataMigration.php 306
ERROR - 2019-02-15 01:50:21 --> Query error: Column 'created_at' in where clause is ambiguous - Invalid query: select p.*, cr.user_id from payments p
                  left join call_requests cr on p.call_request_id=cr.id  
                  where p.contract_id='' and created_at >= '2019-01-01 00:00:00'
                  and payment_type not in(0,1)
                  
INFO - 2019-02-15 01:50:21 --> Language file loaded: language/english/db_lang.php
INFO - 2019-02-15 01:51:19 --> Config Class Initialized
INFO - 2019-02-15 01:51:19 --> Hooks Class Initialized
DEBUG - 2019-02-15 01:51:19 --> UTF-8 Support Enabled
INFO - 2019-02-15 01:51:19 --> Utf8 Class Initialized
INFO - 2019-02-15 01:51:19 --> URI Class Initialized
INFO - 2019-02-15 01:51:19 --> Router Class Initialized
INFO - 2019-02-15 01:51:19 --> Output Class Initialized
INFO - 2019-02-15 01:51:19 --> Security Class Initialized
DEBUG - 2019-02-15 01:51:19 --> Global POST, GET and COOKIE data sanitized
INFO - 2019-02-15 01:51:19 --> Input Class Initialized
INFO - 2019-02-15 01:51:19 --> Language Class Initialized
INFO - 2019-02-15 01:51:19 --> Loader Class Initialized
INFO - 2019-02-15 01:51:19 --> Helper loaded: common_helper
INFO - 2019-02-15 01:51:19 --> Database Driver Class Initialized
INFO - 2019-02-15 01:51:19 --> Controller Class Initialized
INFO - 2019-02-15 01:51:19 --> Database Driver Class Initialized
INFO - 2019-02-15 01:51:19 --> Model "contractOrder_m" initialized
INFO - 2019-02-15 01:51:19 --> Model "Ads_m" initialized
INFO - 2019-02-15 01:51:19 --> Database Driver Class Initialized
INFO - 2019-02-15 01:51:19 --> Model "dataMigration_m" initialized
ERROR - 2019-02-15 01:51:19 --> Severity: Notice --> Undefined index: id /Users/blumine/works/goodoc_v2/event/adminApi/application/models/DataMigration_m.php 43
ERROR - 2019-02-15 01:51:19 --> Severity: Notice --> Undefined index: id /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/DataMigration.php 306
INFO - 2019-02-15 01:51:20 --> Final output sent to browser
DEBUG - 2019-02-15 01:51:20 --> Total execution time: 1.4863
INFO - 2019-02-15 01:54:55 --> Config Class Initialized
INFO - 2019-02-15 01:54:55 --> Hooks Class Initialized
DEBUG - 2019-02-15 01:54:55 --> UTF-8 Support Enabled
INFO - 2019-02-15 01:54:55 --> Utf8 Class Initialized
INFO - 2019-02-15 01:54:55 --> URI Class Initialized
INFO - 2019-02-15 01:54:55 --> Router Class Initialized
INFO - 2019-02-15 01:54:55 --> Output Class Initialized
INFO - 2019-02-15 01:54:55 --> Security Class Initialized
DEBUG - 2019-02-15 01:54:55 --> Global POST, GET and COOKIE data sanitized
INFO - 2019-02-15 01:54:55 --> Input Class Initialized
INFO - 2019-02-15 01:54:55 --> Language Class Initialized
INFO - 2019-02-15 01:54:55 --> Loader Class Initialized
INFO - 2019-02-15 01:54:55 --> Helper loaded: common_helper
INFO - 2019-02-15 01:54:55 --> Database Driver Class Initialized
INFO - 2019-02-15 01:54:55 --> Controller Class Initialized
INFO - 2019-02-15 01:54:55 --> Database Driver Class Initialized
INFO - 2019-02-15 01:54:55 --> Model "contractOrder_m" initialized
INFO - 2019-02-15 01:54:55 --> Model "Ads_m" initialized
INFO - 2019-02-15 01:54:55 --> Database Driver Class Initialized
INFO - 2019-02-15 01:54:55 --> Model "dataMigration_m" initialized
ERROR - 2019-02-15 01:54:56 --> Severity: Notice --> Undefined variable: adType2 /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/DataMigration.php 173
ERROR - 2019-02-15 01:54:56 --> Query error: Duplicate entry '3-0' for key 'PRIMARY' - Invalid query: INSERT INTO `contract` (`hospitalType`, `id`, `hospitalId`, `hospitalName`, `contractDate`, `title`, `adType`, `adType2`, `agencyUserId`, `taxChargeName`, `taxBusinessNo`, `taxChargeEmail`, `manageUserId`, `hospitalChargeName`, `hospitalChargePhone`, `hospitalChargeEmail`, `agencyCompanyId`, `agencyCompanyName`, `regDate`) VALUES (1, '3', '35467', '아이디치과의원', '2015-05-06 18:36:12', '아이디치과의원', 1, NULL, '8', NULL, '', NULL, '', NULL, NULL, NULL, '', '', '2019-02-15 01:54:56')
INFO - 2019-02-15 01:54:56 --> Language file loaded: language/english/db_lang.php
INFO - 2019-02-15 01:56:39 --> Config Class Initialized
INFO - 2019-02-15 01:56:39 --> Hooks Class Initialized
DEBUG - 2019-02-15 01:56:39 --> UTF-8 Support Enabled
INFO - 2019-02-15 01:56:39 --> Utf8 Class Initialized
INFO - 2019-02-15 01:56:39 --> URI Class Initialized
INFO - 2019-02-15 01:56:39 --> Router Class Initialized
INFO - 2019-02-15 01:56:39 --> Output Class Initialized
INFO - 2019-02-15 01:56:39 --> Security Class Initialized
DEBUG - 2019-02-15 01:56:39 --> Global POST, GET and COOKIE data sanitized
INFO - 2019-02-15 01:56:39 --> Input Class Initialized
INFO - 2019-02-15 01:56:39 --> Language Class Initialized
INFO - 2019-02-15 01:56:39 --> Loader Class Initialized
INFO - 2019-02-15 01:56:39 --> Helper loaded: common_helper
INFO - 2019-02-15 01:56:39 --> Database Driver Class Initialized
INFO - 2019-02-15 01:56:39 --> Controller Class Initialized
INFO - 2019-02-15 01:56:39 --> Database Driver Class Initialized
INFO - 2019-02-15 01:56:39 --> Model "contractOrder_m" initialized
INFO - 2019-02-15 01:56:39 --> Model "Ads_m" initialized
INFO - 2019-02-15 01:56:39 --> Database Driver Class Initialized
INFO - 2019-02-15 01:56:39 --> Model "dataMigration_m" initialized
ERROR - 2019-02-15 01:56:39 --> Severity: Notice --> Undefined variable: adType2 /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/DataMigration.php 173
ERROR - 2019-02-15 01:56:39 --> Severity: Notice --> Undefined index: payment_type /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/DataMigration.php 283
ERROR - 2019-02-15 01:56:39 --> Query error: Unknown column 'callReqeustId' in 'field list' - Invalid query: INSERT INTO `deposit` (`status`, `isMinus`, `contractId`, `contractOrderId`, `usersId`, `memo`, `price`, `regDate`, `modDate`, `callReqeustId`) VALUES (2, 0, '3', 3, NULL, '최초 충전-', '40000', '2019-01-01 15:15:47', '2019-01-01 15:15:47', '1389406')
INFO - 2019-02-15 01:56:39 --> Language file loaded: language/english/db_lang.php
INFO - 2019-02-15 02:01:06 --> Config Class Initialized
INFO - 2019-02-15 02:01:06 --> Hooks Class Initialized
DEBUG - 2019-02-15 02:01:06 --> UTF-8 Support Enabled
INFO - 2019-02-15 02:01:06 --> Utf8 Class Initialized
INFO - 2019-02-15 02:01:06 --> URI Class Initialized
INFO - 2019-02-15 02:01:06 --> Router Class Initialized
INFO - 2019-02-15 02:01:06 --> Output Class Initialized
INFO - 2019-02-15 02:01:06 --> Security Class Initialized
DEBUG - 2019-02-15 02:01:06 --> Global POST, GET and COOKIE data sanitized
INFO - 2019-02-15 02:01:06 --> Input Class Initialized
INFO - 2019-02-15 02:01:06 --> Language Class Initialized
INFO - 2019-02-15 02:01:06 --> Loader Class Initialized
INFO - 2019-02-15 02:01:06 --> Helper loaded: common_helper
INFO - 2019-02-15 02:01:06 --> Database Driver Class Initialized
INFO - 2019-02-15 02:01:06 --> Controller Class Initialized
INFO - 2019-02-15 02:01:06 --> Database Driver Class Initialized
INFO - 2019-02-15 02:01:06 --> Model "contractOrder_m" initialized
INFO - 2019-02-15 02:01:06 --> Model "Ads_m" initialized
INFO - 2019-02-15 02:01:06 --> Database Driver Class Initialized
INFO - 2019-02-15 02:01:06 --> Model "dataMigration_m" initialized
INFO - 2019-02-15 02:01:08 --> Final output sent to browser
DEBUG - 2019-02-15 02:01:08 --> Total execution time: 2.4016
INFO - 2019-02-15 02:03:08 --> Config Class Initialized
INFO - 2019-02-15 02:03:08 --> Hooks Class Initialized
DEBUG - 2019-02-15 02:03:08 --> UTF-8 Support Enabled
INFO - 2019-02-15 02:03:08 --> Utf8 Class Initialized
INFO - 2019-02-15 02:03:08 --> URI Class Initialized
INFO - 2019-02-15 02:03:08 --> Router Class Initialized
INFO - 2019-02-15 02:03:08 --> Output Class Initialized
INFO - 2019-02-15 02:03:08 --> Security Class Initialized
DEBUG - 2019-02-15 02:03:08 --> Global POST, GET and COOKIE data sanitized
INFO - 2019-02-15 02:03:08 --> Input Class Initialized
INFO - 2019-02-15 02:03:08 --> Language Class Initialized
INFO - 2019-02-15 02:03:08 --> Loader Class Initialized
INFO - 2019-02-15 02:03:08 --> Helper loaded: common_helper
INFO - 2019-02-15 02:03:08 --> Database Driver Class Initialized
INFO - 2019-02-15 02:03:08 --> Controller Class Initialized
INFO - 2019-02-15 02:03:08 --> Database Driver Class Initialized
INFO - 2019-02-15 02:03:09 --> Model "contractOrder_m" initialized
INFO - 2019-02-15 02:03:09 --> Model "Ads_m" initialized
INFO - 2019-02-15 02:03:09 --> Database Driver Class Initialized
INFO - 2019-02-15 02:03:09 --> Model "dataMigration_m" initialized
INFO - 2019-02-15 02:03:12 --> Final output sent to browser
DEBUG - 2019-02-15 02:03:12 --> Total execution time: 3.4836
INFO - 2019-02-15 02:06:30 --> Config Class Initialized
INFO - 2019-02-15 02:06:30 --> Hooks Class Initialized
DEBUG - 2019-02-15 02:06:30 --> UTF-8 Support Enabled
INFO - 2019-02-15 02:06:30 --> Utf8 Class Initialized
INFO - 2019-02-15 02:06:30 --> URI Class Initialized
INFO - 2019-02-15 02:06:30 --> Router Class Initialized
INFO - 2019-02-15 02:06:30 --> Output Class Initialized
INFO - 2019-02-15 02:06:30 --> Security Class Initialized
DEBUG - 2019-02-15 02:06:30 --> Global POST, GET and COOKIE data sanitized
INFO - 2019-02-15 02:06:30 --> Input Class Initialized
INFO - 2019-02-15 02:06:30 --> Language Class Initialized
INFO - 2019-02-15 02:06:30 --> Loader Class Initialized
INFO - 2019-02-15 02:06:30 --> Helper loaded: common_helper
INFO - 2019-02-15 02:06:30 --> Database Driver Class Initialized
INFO - 2019-02-15 02:06:30 --> Controller Class Initialized
INFO - 2019-02-15 02:06:30 --> Database Driver Class Initialized
INFO - 2019-02-15 02:06:30 --> Model "contractOrder_m" initialized
INFO - 2019-02-15 02:06:30 --> Model "Ads_m" initialized
INFO - 2019-02-15 02:06:30 --> Database Driver Class Initialized
INFO - 2019-02-15 02:06:30 --> Model "dataMigration_m" initialized
ERROR - 2019-02-15 02:06:30 --> Query error: Unknown column 'contractType' in 'field list' - Invalid query: INSERT INTO `contract` (`hospitalType`, `hospitalId`, `hospitalName`, `contractDate`, `contractType`, `title`, `adType`, `adType2`, `adPrice`, `agencyUserId`, `taxChargeName`, `taxBusinessNo`, `taxChargeEmail`, `manageUserId`, `hospitalChargeName`, `hospitalChargePhone`, `hospitalChargeEmail`, `taxIssueRequestDate`, `agencyCompanyChargeName`, `agencyCompanyChargePhone`, `agencyCompanyChargeEmail`, `agencyCompanyId`, `agencyCompanyName`, `agencyCompanyFeeRate`, `regDate`) VALUES (1, '35467', '아이디치과의원', '2015-05-06 18:36:12', 1, '아이디치과의원', 1, 1, '1110000', '8', NULL, '', NULL, '', NULL, NULL, NULL, '', '', '', '', '', '', '', '2019-02-15 02:06:30')
INFO - 2019-02-15 02:06:30 --> Language file loaded: language/english/db_lang.php
INFO - 2019-02-15 02:07:14 --> Config Class Initialized
INFO - 2019-02-15 02:07:14 --> Hooks Class Initialized
DEBUG - 2019-02-15 02:07:14 --> UTF-8 Support Enabled
INFO - 2019-02-15 02:07:14 --> Utf8 Class Initialized
INFO - 2019-02-15 02:07:14 --> URI Class Initialized
INFO - 2019-02-15 02:07:14 --> Router Class Initialized
INFO - 2019-02-15 02:07:14 --> Output Class Initialized
INFO - 2019-02-15 02:07:14 --> Security Class Initialized
DEBUG - 2019-02-15 02:07:14 --> Global POST, GET and COOKIE data sanitized
INFO - 2019-02-15 02:07:14 --> Input Class Initialized
INFO - 2019-02-15 02:07:14 --> Language Class Initialized
INFO - 2019-02-15 02:07:14 --> Loader Class Initialized
INFO - 2019-02-15 02:07:14 --> Helper loaded: common_helper
INFO - 2019-02-15 02:07:14 --> Database Driver Class Initialized
INFO - 2019-02-15 02:07:14 --> Controller Class Initialized
INFO - 2019-02-15 02:07:14 --> Database Driver Class Initialized
INFO - 2019-02-15 02:07:15 --> Model "contractOrder_m" initialized
INFO - 2019-02-15 02:07:15 --> Model "Ads_m" initialized
INFO - 2019-02-15 02:07:15 --> Database Driver Class Initialized
INFO - 2019-02-15 02:07:15 --> Model "dataMigration_m" initialized
INFO - 2019-02-15 02:07:18 --> Final output sent to browser
DEBUG - 2019-02-15 02:07:18 --> Total execution time: 3.5523
