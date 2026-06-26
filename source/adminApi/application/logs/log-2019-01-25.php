<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

INFO - 2019-01-25 04:17:50 --> Config Class Initialized
INFO - 2019-01-25 04:17:50 --> Hooks Class Initialized
DEBUG - 2019-01-25 04:17:50 --> UTF-8 Support Enabled
INFO - 2019-01-25 04:17:50 --> Utf8 Class Initialized
INFO - 2019-01-25 04:17:50 --> URI Class Initialized
INFO - 2019-01-25 04:17:50 --> Router Class Initialized
INFO - 2019-01-25 04:17:50 --> Output Class Initialized
INFO - 2019-01-25 04:17:50 --> Security Class Initialized
DEBUG - 2019-01-25 04:17:50 --> Global POST, GET and COOKIE data sanitized
INFO - 2019-01-25 04:17:50 --> Input Class Initialized
INFO - 2019-01-25 04:17:50 --> Language Class Initialized
INFO - 2019-01-25 04:17:50 --> Loader Class Initialized
INFO - 2019-01-25 04:17:50 --> Helper loaded: common_helper
INFO - 2019-01-25 04:17:50 --> Database Driver Class Initialized
INFO - 2019-01-25 04:17:50 --> Controller Class Initialized
DEBUG - 2019-01-25 04:17:50 --> Config file loaded: /Users/blumine/works/goodoc_v2/event/adminApi/application/config/rest.php
INFO - 2019-01-25 04:17:50 --> Helper loaded: inflector_helper
INFO - 2019-01-25 04:17:50 --> Language file loaded: language/english/rest_controller_lang.php
INFO - 2019-01-25 04:17:50 --> Model "Ads_m" initialized
INFO - 2019-01-25 04:17:50 --> Model "Common_m" initialized
INFO - 2019-01-25 04:17:50 --> Model "AdsTemp_m" initialized
DEBUG - 2019-01-25 04:17:50 --> cURL Class Initialized
INFO - 2019-01-25 04:17:50 --> Model "Replicator_m" initialized
INFO - 2019-01-25 04:17:50 --> Database Driver Class Initialized
INFO - 2019-01-25 04:17:51 --> Final output sent to browser
DEBUG - 2019-01-25 04:17:51 --> Total execution time: 0.8738
INFO - 2019-01-25 04:18:27 --> Config Class Initialized
INFO - 2019-01-25 04:18:27 --> Hooks Class Initialized
DEBUG - 2019-01-25 04:18:27 --> UTF-8 Support Enabled
INFO - 2019-01-25 04:18:27 --> Utf8 Class Initialized
INFO - 2019-01-25 04:18:27 --> URI Class Initialized
INFO - 2019-01-25 04:18:27 --> Router Class Initialized
INFO - 2019-01-25 04:18:27 --> Output Class Initialized
INFO - 2019-01-25 04:18:27 --> Security Class Initialized
DEBUG - 2019-01-25 04:18:27 --> Global POST, GET and COOKIE data sanitized
INFO - 2019-01-25 04:18:27 --> Input Class Initialized
INFO - 2019-01-25 04:18:27 --> Language Class Initialized
INFO - 2019-01-25 04:18:27 --> Loader Class Initialized
INFO - 2019-01-25 04:18:27 --> Helper loaded: common_helper
INFO - 2019-01-25 04:18:27 --> Database Driver Class Initialized
INFO - 2019-01-25 04:18:27 --> Controller Class Initialized
DEBUG - 2019-01-25 04:18:27 --> Config file loaded: /Users/blumine/works/goodoc_v2/event/adminApi/application/config/rest.php
INFO - 2019-01-25 04:18:27 --> Helper loaded: inflector_helper
INFO - 2019-01-25 04:18:27 --> Language file loaded: language/english/rest_controller_lang.php
INFO - 2019-01-25 04:18:27 --> Model "Ads_m" initialized
INFO - 2019-01-25 04:18:27 --> Model "Common_m" initialized
INFO - 2019-01-25 04:18:27 --> Model "AdsTemp_m" initialized
DEBUG - 2019-01-25 04:18:27 --> cURL Class Initialized
INFO - 2019-01-25 04:18:27 --> Model "Replicator_m" initialized
INFO - 2019-01-25 04:18:27 --> Database Driver Class Initialized
ERROR - 2019-01-25 04:18:28 --> Query error: You have an error in your SQL syntax; check the manual that corresponds to your MySQL server version for the right syntax to use near 'select memo from ads_history_memo where adsId = ads.id) as memos, `ads`.`id`
FRO' at line 1 - Invalid query: SELECT group_concat(select memo from ads_history_memo where adsId = ads.id) as memos, `ads`.`id`
FROM `ads`
LEFT JOIN `ads_history` `ah` ON `ads`.`id`=`ah`.`adsId`
LEFT JOIN `f_hospitals` `fhl` ON `ads`.`hospitalId`=`fhl`.`id`
WHERE (`ads`.`subAdStatus` <> 6 )
AND  `memos` LIKE '%메모%' ESCAPE '!'
AND `ads`.`isDelete` = 'N'
GROUP BY `ads`.`id`
INFO - 2019-01-25 04:18:28 --> Language file loaded: language/english/db_lang.php
INFO - 2019-01-25 05:50:38 --> Config Class Initialized
INFO - 2019-01-25 05:50:38 --> Hooks Class Initialized
DEBUG - 2019-01-25 05:50:38 --> UTF-8 Support Enabled
INFO - 2019-01-25 05:50:38 --> Utf8 Class Initialized
INFO - 2019-01-25 05:50:38 --> URI Class Initialized
INFO - 2019-01-25 05:50:38 --> Router Class Initialized
INFO - 2019-01-25 05:50:38 --> Output Class Initialized
INFO - 2019-01-25 05:50:38 --> Security Class Initialized
DEBUG - 2019-01-25 05:50:38 --> Global POST, GET and COOKIE data sanitized
INFO - 2019-01-25 05:50:38 --> Input Class Initialized
INFO - 2019-01-25 05:50:38 --> Language Class Initialized
INFO - 2019-01-25 05:50:38 --> Loader Class Initialized
INFO - 2019-01-25 05:50:38 --> Helper loaded: common_helper
INFO - 2019-01-25 05:50:38 --> Database Driver Class Initialized
INFO - 2019-01-25 05:50:38 --> Controller Class Initialized
DEBUG - 2019-01-25 05:50:38 --> Config file loaded: /Users/blumine/works/goodoc_v2/event/adminApi/application/config/rest.php
INFO - 2019-01-25 05:50:38 --> Helper loaded: inflector_helper
INFO - 2019-01-25 05:50:38 --> Language file loaded: language/english/rest_controller_lang.php
INFO - 2019-01-25 05:50:38 --> Model "Ads_m" initialized
INFO - 2019-01-25 05:50:38 --> Model "Common_m" initialized
INFO - 2019-01-25 05:50:38 --> Model "AdsTemp_m" initialized
DEBUG - 2019-01-25 05:50:38 --> cURL Class Initialized
INFO - 2019-01-25 05:50:38 --> Model "Replicator_m" initialized
INFO - 2019-01-25 05:50:38 --> Database Driver Class Initialized
INFO - 2019-01-25 05:52:30 --> Config Class Initialized
INFO - 2019-01-25 05:52:30 --> Hooks Class Initialized
DEBUG - 2019-01-25 05:52:30 --> UTF-8 Support Enabled
INFO - 2019-01-25 05:52:30 --> Utf8 Class Initialized
INFO - 2019-01-25 05:52:30 --> URI Class Initialized
INFO - 2019-01-25 05:52:30 --> Router Class Initialized
INFO - 2019-01-25 05:52:30 --> Output Class Initialized
INFO - 2019-01-25 05:52:30 --> Security Class Initialized
DEBUG - 2019-01-25 05:52:30 --> Global POST, GET and COOKIE data sanitized
INFO - 2019-01-25 05:52:30 --> Input Class Initialized
INFO - 2019-01-25 05:52:30 --> Language Class Initialized
INFO - 2019-01-25 05:52:30 --> Loader Class Initialized
INFO - 2019-01-25 05:52:30 --> Helper loaded: common_helper
INFO - 2019-01-25 05:52:30 --> Database Driver Class Initialized
INFO - 2019-01-25 05:52:30 --> Controller Class Initialized
DEBUG - 2019-01-25 05:52:30 --> Config file loaded: /Users/blumine/works/goodoc_v2/event/adminApi/application/config/rest.php
INFO - 2019-01-25 05:52:30 --> Helper loaded: inflector_helper
INFO - 2019-01-25 05:52:30 --> Language file loaded: language/english/rest_controller_lang.php
INFO - 2019-01-25 05:52:30 --> Model "Ads_m" initialized
INFO - 2019-01-25 05:52:30 --> Model "Common_m" initialized
INFO - 2019-01-25 05:52:30 --> Model "AdsTemp_m" initialized
DEBUG - 2019-01-25 05:52:30 --> cURL Class Initialized
INFO - 2019-01-25 05:52:30 --> Model "Replicator_m" initialized
INFO - 2019-01-25 05:52:30 --> Database Driver Class Initialized
INFO - 2019-01-25 05:53:08 --> Config Class Initialized
INFO - 2019-01-25 05:53:08 --> Hooks Class Initialized
DEBUG - 2019-01-25 05:53:08 --> UTF-8 Support Enabled
INFO - 2019-01-25 05:53:08 --> Utf8 Class Initialized
INFO - 2019-01-25 05:53:08 --> URI Class Initialized
INFO - 2019-01-25 05:53:08 --> Router Class Initialized
INFO - 2019-01-25 05:53:08 --> Output Class Initialized
INFO - 2019-01-25 05:53:08 --> Security Class Initialized
DEBUG - 2019-01-25 05:53:08 --> Global POST, GET and COOKIE data sanitized
INFO - 2019-01-25 05:53:08 --> Input Class Initialized
INFO - 2019-01-25 05:53:08 --> Language Class Initialized
INFO - 2019-01-25 05:53:08 --> Loader Class Initialized
INFO - 2019-01-25 05:53:08 --> Helper loaded: common_helper
INFO - 2019-01-25 05:53:08 --> Database Driver Class Initialized
INFO - 2019-01-25 05:53:08 --> Controller Class Initialized
DEBUG - 2019-01-25 05:53:08 --> Config file loaded: /Users/blumine/works/goodoc_v2/event/adminApi/application/config/rest.php
INFO - 2019-01-25 05:53:08 --> Helper loaded: inflector_helper
INFO - 2019-01-25 05:53:08 --> Language file loaded: language/english/rest_controller_lang.php
INFO - 2019-01-25 05:53:08 --> Model "Ads_m" initialized
INFO - 2019-01-25 05:53:08 --> Model "Common_m" initialized
INFO - 2019-01-25 05:53:08 --> Model "AdsTemp_m" initialized
DEBUG - 2019-01-25 05:53:08 --> cURL Class Initialized
INFO - 2019-01-25 05:53:08 --> Model "Replicator_m" initialized
INFO - 2019-01-25 05:53:08 --> Database Driver Class Initialized
ERROR - 2019-01-25 05:53:09 --> Query error: Column 'id' in group statement is ambiguous - Invalid query: SELECT *
FROM `ads`
LEFT JOIN `ads_history` `ah` ON `ads`.`id`=`ah`.`adsId`
LEFT JOIN `f_hospitals` `fhl` ON `ads`.`hospitalId`=`fhl`.`id`
WHERE (`ads`.`subAdStatus` <> 6 )
AND `ads`.`isDelete` = 'N'
GROUP BY `ads`.`id`, `id`
INFO - 2019-01-25 05:53:09 --> Language file loaded: language/english/db_lang.php
INFO - 2019-01-25 05:53:26 --> Config Class Initialized
INFO - 2019-01-25 05:53:26 --> Hooks Class Initialized
DEBUG - 2019-01-25 05:53:26 --> UTF-8 Support Enabled
INFO - 2019-01-25 05:53:26 --> Utf8 Class Initialized
INFO - 2019-01-25 05:53:26 --> URI Class Initialized
INFO - 2019-01-25 05:53:26 --> Router Class Initialized
INFO - 2019-01-25 05:53:26 --> Output Class Initialized
INFO - 2019-01-25 05:53:26 --> Security Class Initialized
DEBUG - 2019-01-25 05:53:26 --> Global POST, GET and COOKIE data sanitized
INFO - 2019-01-25 05:53:26 --> Input Class Initialized
INFO - 2019-01-25 05:53:26 --> Language Class Initialized
INFO - 2019-01-25 05:53:26 --> Loader Class Initialized
INFO - 2019-01-25 05:53:26 --> Helper loaded: common_helper
INFO - 2019-01-25 05:53:26 --> Database Driver Class Initialized
INFO - 2019-01-25 05:53:26 --> Controller Class Initialized
DEBUG - 2019-01-25 05:53:26 --> Config file loaded: /Users/blumine/works/goodoc_v2/event/adminApi/application/config/rest.php
INFO - 2019-01-25 05:53:26 --> Helper loaded: inflector_helper
INFO - 2019-01-25 05:53:26 --> Language file loaded: language/english/rest_controller_lang.php
INFO - 2019-01-25 05:53:26 --> Model "Ads_m" initialized
INFO - 2019-01-25 05:53:26 --> Model "Common_m" initialized
INFO - 2019-01-25 05:53:26 --> Model "AdsTemp_m" initialized
DEBUG - 2019-01-25 05:53:26 --> cURL Class Initialized
INFO - 2019-01-25 05:53:26 --> Model "Replicator_m" initialized
INFO - 2019-01-25 05:53:26 --> Database Driver Class Initialized
INFO - 2019-01-25 05:55:08 --> Config Class Initialized
INFO - 2019-01-25 05:55:08 --> Hooks Class Initialized
DEBUG - 2019-01-25 05:55:08 --> UTF-8 Support Enabled
INFO - 2019-01-25 05:55:08 --> Utf8 Class Initialized
INFO - 2019-01-25 05:55:08 --> URI Class Initialized
INFO - 2019-01-25 05:55:08 --> Router Class Initialized
INFO - 2019-01-25 05:55:08 --> Output Class Initialized
INFO - 2019-01-25 05:55:08 --> Security Class Initialized
DEBUG - 2019-01-25 05:55:08 --> Global POST, GET and COOKIE data sanitized
INFO - 2019-01-25 05:55:08 --> Input Class Initialized
INFO - 2019-01-25 05:55:08 --> Language Class Initialized
INFO - 2019-01-25 05:55:08 --> Loader Class Initialized
INFO - 2019-01-25 05:55:08 --> Helper loaded: common_helper
INFO - 2019-01-25 05:55:08 --> Database Driver Class Initialized
INFO - 2019-01-25 05:55:08 --> Controller Class Initialized
DEBUG - 2019-01-25 05:55:08 --> Config file loaded: /Users/blumine/works/goodoc_v2/event/adminApi/application/config/rest.php
INFO - 2019-01-25 05:55:08 --> Helper loaded: inflector_helper
INFO - 2019-01-25 05:55:08 --> Language file loaded: language/english/rest_controller_lang.php
INFO - 2019-01-25 05:55:08 --> Model "Ads_m" initialized
INFO - 2019-01-25 05:55:08 --> Model "Common_m" initialized
INFO - 2019-01-25 05:55:08 --> Model "AdsTemp_m" initialized
DEBUG - 2019-01-25 05:55:08 --> cURL Class Initialized
INFO - 2019-01-25 05:55:08 --> Model "Replicator_m" initialized
INFO - 2019-01-25 05:55:08 --> Database Driver Class Initialized
INFO - 2019-01-25 05:59:55 --> Config Class Initialized
INFO - 2019-01-25 05:59:55 --> Hooks Class Initialized
DEBUG - 2019-01-25 05:59:55 --> UTF-8 Support Enabled
INFO - 2019-01-25 05:59:55 --> Utf8 Class Initialized
INFO - 2019-01-25 05:59:55 --> URI Class Initialized
INFO - 2019-01-25 05:59:55 --> Router Class Initialized
INFO - 2019-01-25 05:59:55 --> Output Class Initialized
INFO - 2019-01-25 05:59:55 --> Security Class Initialized
DEBUG - 2019-01-25 05:59:55 --> Global POST, GET and COOKIE data sanitized
INFO - 2019-01-25 05:59:55 --> Input Class Initialized
INFO - 2019-01-25 05:59:55 --> Language Class Initialized
INFO - 2019-01-25 05:59:55 --> Loader Class Initialized
INFO - 2019-01-25 05:59:55 --> Helper loaded: common_helper
INFO - 2019-01-25 05:59:55 --> Database Driver Class Initialized
INFO - 2019-01-25 05:59:56 --> Controller Class Initialized
DEBUG - 2019-01-25 05:59:56 --> Config file loaded: /Users/blumine/works/goodoc_v2/event/adminApi/application/config/rest.php
INFO - 2019-01-25 05:59:56 --> Helper loaded: inflector_helper
INFO - 2019-01-25 05:59:56 --> Language file loaded: language/english/rest_controller_lang.php
INFO - 2019-01-25 05:59:56 --> Model "Ads_m" initialized
INFO - 2019-01-25 05:59:56 --> Model "Common_m" initialized
INFO - 2019-01-25 05:59:56 --> Model "AdsTemp_m" initialized
DEBUG - 2019-01-25 05:59:56 --> cURL Class Initialized
INFO - 2019-01-25 05:59:56 --> Model "Replicator_m" initialized
INFO - 2019-01-25 05:59:56 --> Database Driver Class Initialized
INFO - 2019-01-25 06:00:33 --> Config Class Initialized
INFO - 2019-01-25 06:00:33 --> Hooks Class Initialized
DEBUG - 2019-01-25 06:00:33 --> UTF-8 Support Enabled
INFO - 2019-01-25 06:00:33 --> Utf8 Class Initialized
INFO - 2019-01-25 06:00:33 --> URI Class Initialized
INFO - 2019-01-25 06:00:33 --> Router Class Initialized
INFO - 2019-01-25 06:00:33 --> Output Class Initialized
INFO - 2019-01-25 06:00:33 --> Security Class Initialized
DEBUG - 2019-01-25 06:00:33 --> Global POST, GET and COOKIE data sanitized
INFO - 2019-01-25 06:00:33 --> Input Class Initialized
INFO - 2019-01-25 06:00:33 --> Language Class Initialized
INFO - 2019-01-25 06:00:33 --> Loader Class Initialized
INFO - 2019-01-25 06:00:33 --> Helper loaded: common_helper
INFO - 2019-01-25 06:00:33 --> Database Driver Class Initialized
INFO - 2019-01-25 06:00:33 --> Controller Class Initialized
DEBUG - 2019-01-25 06:00:33 --> Config file loaded: /Users/blumine/works/goodoc_v2/event/adminApi/application/config/rest.php
INFO - 2019-01-25 06:00:33 --> Helper loaded: inflector_helper
INFO - 2019-01-25 06:00:33 --> Language file loaded: language/english/rest_controller_lang.php
INFO - 2019-01-25 06:00:33 --> Model "Ads_m" initialized
INFO - 2019-01-25 06:00:33 --> Model "Common_m" initialized
INFO - 2019-01-25 06:00:33 --> Model "AdsTemp_m" initialized
DEBUG - 2019-01-25 06:00:33 --> cURL Class Initialized
INFO - 2019-01-25 06:00:33 --> Model "Replicator_m" initialized
INFO - 2019-01-25 06:00:33 --> Database Driver Class Initialized
INFO - 2019-01-25 06:10:47 --> Config Class Initialized
INFO - 2019-01-25 06:10:47 --> Hooks Class Initialized
DEBUG - 2019-01-25 06:10:47 --> UTF-8 Support Enabled
INFO - 2019-01-25 06:10:47 --> Utf8 Class Initialized
INFO - 2019-01-25 06:10:47 --> URI Class Initialized
INFO - 2019-01-25 06:10:47 --> Router Class Initialized
INFO - 2019-01-25 06:10:47 --> Output Class Initialized
INFO - 2019-01-25 06:10:47 --> Security Class Initialized
DEBUG - 2019-01-25 06:10:47 --> Global POST, GET and COOKIE data sanitized
INFO - 2019-01-25 06:10:47 --> Input Class Initialized
INFO - 2019-01-25 06:10:47 --> Language Class Initialized
INFO - 2019-01-25 06:10:47 --> Loader Class Initialized
INFO - 2019-01-25 06:10:47 --> Helper loaded: common_helper
INFO - 2019-01-25 06:10:47 --> Database Driver Class Initialized
INFO - 2019-01-25 06:10:47 --> Controller Class Initialized
DEBUG - 2019-01-25 06:10:47 --> Config file loaded: /Users/blumine/works/goodoc_v2/event/adminApi/application/config/rest.php
INFO - 2019-01-25 06:10:47 --> Helper loaded: inflector_helper
INFO - 2019-01-25 06:10:47 --> Language file loaded: language/english/rest_controller_lang.php
INFO - 2019-01-25 06:10:47 --> Model "Ads_m" initialized
INFO - 2019-01-25 06:10:47 --> Model "Common_m" initialized
INFO - 2019-01-25 06:10:47 --> Model "AdsTemp_m" initialized
DEBUG - 2019-01-25 06:10:47 --> cURL Class Initialized
INFO - 2019-01-25 06:10:47 --> Model "Replicator_m" initialized
INFO - 2019-01-25 06:10:47 --> Database Driver Class Initialized
ERROR - 2019-01-25 06:10:48 --> Query error: You have an error in your SQL syntax; check the manual that corresponds to your MySQL server version for the right syntax to use near 'select memo from ads_history_memo where adsId = ads.id) as memos, (select id fro' at line 1 - Invalid query: SELECT ads.id, ads.isLive, ads.adStatus, ads.subAdStatus, fhl.name hospitalName, ads.vAgencyUserId as agencyUserId, ads.vAdTitle as adTitle, ads.vCategory as category, ads.vAdStartDate as adStartDate, ads.vAdEndDate as adEndDate, ads.regDate, ads.modDate, ads.vHospitalType as hospitalType, ads.vAdType as adType, ads.vDbCost as dbCost, ads.vContractName as contractName, ads.vContractId as contractId, ads.vContractOrderId as contractOrderId, ads.vT1ImageName as t1ImageName, ads.vT2ImageName as t2ImageName, group_concat(select memo from ads_history_memo where adsId = ads.id) as memos, (select id from inspecting_ads where adsId = ads.id order by id desc limit 1) as inspectId, (select ifnull(reason, null) from inspecting_ads where adsId = ads.id and status = 3 order by id desc limit 1) as inspectReason
FROM `ads`
LEFT JOIN `ads_history` `ah` ON `ads`.`id`=`ah`.`adsId`
LEFT JOIN `f_hospitals` `fhl` ON `ads`.`vHospitalId`=`fhl`.`id`
WHERE (`ads`.`subAdStatus` <> 6 )
AND `ads`.`isDelete` = 'N'
GROUP BY `ads`.`id`
ORDER BY `ads`.`id` DESC
INFO - 2019-01-25 06:10:48 --> Language file loaded: language/english/db_lang.php
INFO - 2019-01-25 06:11:55 --> Config Class Initialized
INFO - 2019-01-25 06:11:55 --> Hooks Class Initialized
DEBUG - 2019-01-25 06:11:55 --> UTF-8 Support Enabled
INFO - 2019-01-25 06:11:55 --> Utf8 Class Initialized
INFO - 2019-01-25 06:11:55 --> URI Class Initialized
INFO - 2019-01-25 06:11:55 --> Router Class Initialized
INFO - 2019-01-25 06:11:55 --> Output Class Initialized
INFO - 2019-01-25 06:11:55 --> Security Class Initialized
DEBUG - 2019-01-25 06:11:55 --> Global POST, GET and COOKIE data sanitized
INFO - 2019-01-25 06:11:55 --> Input Class Initialized
INFO - 2019-01-25 06:11:55 --> Language Class Initialized
INFO - 2019-01-25 06:11:56 --> Loader Class Initialized
INFO - 2019-01-25 06:11:56 --> Helper loaded: common_helper
INFO - 2019-01-25 06:11:56 --> Database Driver Class Initialized
INFO - 2019-01-25 06:11:56 --> Controller Class Initialized
DEBUG - 2019-01-25 06:11:56 --> Config file loaded: /Users/blumine/works/goodoc_v2/event/adminApi/application/config/rest.php
INFO - 2019-01-25 06:11:56 --> Helper loaded: inflector_helper
INFO - 2019-01-25 06:11:56 --> Language file loaded: language/english/rest_controller_lang.php
INFO - 2019-01-25 06:11:56 --> Model "Ads_m" initialized
INFO - 2019-01-25 06:11:56 --> Model "Common_m" initialized
INFO - 2019-01-25 06:11:56 --> Model "AdsTemp_m" initialized
DEBUG - 2019-01-25 06:11:56 --> cURL Class Initialized
INFO - 2019-01-25 06:11:56 --> Model "Replicator_m" initialized
INFO - 2019-01-25 06:11:56 --> Database Driver Class Initialized
INFO - 2019-01-25 06:11:56 --> Final output sent to browser
DEBUG - 2019-01-25 06:11:56 --> Total execution time: 0.9570
INFO - 2019-01-25 06:12:39 --> Config Class Initialized
INFO - 2019-01-25 06:12:39 --> Hooks Class Initialized
DEBUG - 2019-01-25 06:12:39 --> UTF-8 Support Enabled
INFO - 2019-01-25 06:12:39 --> Utf8 Class Initialized
INFO - 2019-01-25 06:12:39 --> URI Class Initialized
INFO - 2019-01-25 06:12:39 --> Router Class Initialized
INFO - 2019-01-25 06:12:39 --> Output Class Initialized
INFO - 2019-01-25 06:12:39 --> Security Class Initialized
DEBUG - 2019-01-25 06:12:39 --> Global POST, GET and COOKIE data sanitized
INFO - 2019-01-25 06:12:39 --> Input Class Initialized
INFO - 2019-01-25 06:12:39 --> Language Class Initialized
INFO - 2019-01-25 06:12:39 --> Loader Class Initialized
INFO - 2019-01-25 06:12:39 --> Helper loaded: common_helper
INFO - 2019-01-25 06:12:39 --> Database Driver Class Initialized
INFO - 2019-01-25 06:12:39 --> Controller Class Initialized
DEBUG - 2019-01-25 06:12:39 --> Config file loaded: /Users/blumine/works/goodoc_v2/event/adminApi/application/config/rest.php
INFO - 2019-01-25 06:12:39 --> Helper loaded: inflector_helper
INFO - 2019-01-25 06:12:39 --> Language file loaded: language/english/rest_controller_lang.php
INFO - 2019-01-25 06:12:39 --> Model "Ads_m" initialized
INFO - 2019-01-25 06:12:39 --> Model "Common_m" initialized
INFO - 2019-01-25 06:12:39 --> Model "AdsTemp_m" initialized
DEBUG - 2019-01-25 06:12:39 --> cURL Class Initialized
INFO - 2019-01-25 06:12:39 --> Model "Replicator_m" initialized
INFO - 2019-01-25 06:12:39 --> Database Driver Class Initialized
INFO - 2019-01-25 06:12:51 --> Config Class Initialized
INFO - 2019-01-25 06:12:51 --> Hooks Class Initialized
DEBUG - 2019-01-25 06:12:51 --> UTF-8 Support Enabled
INFO - 2019-01-25 06:12:51 --> Utf8 Class Initialized
INFO - 2019-01-25 06:12:51 --> URI Class Initialized
INFO - 2019-01-25 06:12:51 --> Router Class Initialized
INFO - 2019-01-25 06:12:51 --> Output Class Initialized
INFO - 2019-01-25 06:12:51 --> Security Class Initialized
DEBUG - 2019-01-25 06:12:51 --> Global POST, GET and COOKIE data sanitized
INFO - 2019-01-25 06:12:51 --> Input Class Initialized
INFO - 2019-01-25 06:12:51 --> Language Class Initialized
INFO - 2019-01-25 06:12:51 --> Loader Class Initialized
INFO - 2019-01-25 06:12:51 --> Helper loaded: common_helper
INFO - 2019-01-25 06:12:51 --> Database Driver Class Initialized
INFO - 2019-01-25 06:12:51 --> Controller Class Initialized
DEBUG - 2019-01-25 06:12:51 --> Config file loaded: /Users/blumine/works/goodoc_v2/event/adminApi/application/config/rest.php
INFO - 2019-01-25 06:12:51 --> Helper loaded: inflector_helper
INFO - 2019-01-25 06:12:51 --> Language file loaded: language/english/rest_controller_lang.php
INFO - 2019-01-25 06:12:51 --> Model "Ads_m" initialized
INFO - 2019-01-25 06:12:51 --> Model "Common_m" initialized
INFO - 2019-01-25 06:12:51 --> Model "AdsTemp_m" initialized
DEBUG - 2019-01-25 06:12:51 --> cURL Class Initialized
INFO - 2019-01-25 06:12:51 --> Model "Replicator_m" initialized
INFO - 2019-01-25 06:12:51 --> Database Driver Class Initialized
INFO - 2019-01-25 06:12:52 --> Final output sent to browser
DEBUG - 2019-01-25 06:12:52 --> Total execution time: 1.0827
INFO - 2019-01-25 06:16:01 --> Config Class Initialized
INFO - 2019-01-25 06:16:01 --> Hooks Class Initialized
DEBUG - 2019-01-25 06:16:01 --> UTF-8 Support Enabled
INFO - 2019-01-25 06:16:01 --> Utf8 Class Initialized
INFO - 2019-01-25 06:16:01 --> URI Class Initialized
INFO - 2019-01-25 06:16:01 --> Router Class Initialized
INFO - 2019-01-25 06:16:01 --> Output Class Initialized
INFO - 2019-01-25 06:16:01 --> Security Class Initialized
DEBUG - 2019-01-25 06:16:01 --> Global POST, GET and COOKIE data sanitized
INFO - 2019-01-25 06:16:01 --> Input Class Initialized
INFO - 2019-01-25 06:16:01 --> Language Class Initialized
INFO - 2019-01-25 06:16:01 --> Loader Class Initialized
INFO - 2019-01-25 06:16:01 --> Helper loaded: common_helper
INFO - 2019-01-25 06:16:01 --> Database Driver Class Initialized
INFO - 2019-01-25 06:16:01 --> Controller Class Initialized
DEBUG - 2019-01-25 06:16:01 --> Config file loaded: /Users/blumine/works/goodoc_v2/event/adminApi/application/config/rest.php
INFO - 2019-01-25 06:16:01 --> Helper loaded: inflector_helper
INFO - 2019-01-25 06:16:01 --> Language file loaded: language/english/rest_controller_lang.php
INFO - 2019-01-25 06:16:01 --> Model "Ads_m" initialized
INFO - 2019-01-25 06:16:01 --> Model "Common_m" initialized
INFO - 2019-01-25 06:16:01 --> Model "AdsTemp_m" initialized
DEBUG - 2019-01-25 06:16:01 --> cURL Class Initialized
INFO - 2019-01-25 06:16:01 --> Model "Replicator_m" initialized
INFO - 2019-01-25 06:16:01 --> Database Driver Class Initialized
INFO - 2019-01-25 06:16:03 --> Final output sent to browser
DEBUG - 2019-01-25 06:16:03 --> Total execution time: 1.3412
INFO - 2019-01-25 06:17:02 --> Config Class Initialized
INFO - 2019-01-25 06:17:02 --> Hooks Class Initialized
DEBUG - 2019-01-25 06:17:02 --> UTF-8 Support Enabled
INFO - 2019-01-25 06:17:02 --> Utf8 Class Initialized
INFO - 2019-01-25 06:17:02 --> URI Class Initialized
INFO - 2019-01-25 06:17:02 --> Router Class Initialized
INFO - 2019-01-25 06:17:02 --> Output Class Initialized
INFO - 2019-01-25 06:17:02 --> Security Class Initialized
DEBUG - 2019-01-25 06:17:02 --> Global POST, GET and COOKIE data sanitized
INFO - 2019-01-25 06:17:02 --> Input Class Initialized
INFO - 2019-01-25 06:17:02 --> Language Class Initialized
INFO - 2019-01-25 06:17:02 --> Loader Class Initialized
INFO - 2019-01-25 06:17:02 --> Helper loaded: common_helper
INFO - 2019-01-25 06:17:02 --> Database Driver Class Initialized
INFO - 2019-01-25 06:17:02 --> Controller Class Initialized
DEBUG - 2019-01-25 06:17:02 --> Config file loaded: /Users/blumine/works/goodoc_v2/event/adminApi/application/config/rest.php
INFO - 2019-01-25 06:17:02 --> Helper loaded: inflector_helper
INFO - 2019-01-25 06:17:02 --> Language file loaded: language/english/rest_controller_lang.php
INFO - 2019-01-25 06:17:02 --> Model "Ads_m" initialized
INFO - 2019-01-25 06:17:02 --> Model "Common_m" initialized
INFO - 2019-01-25 06:17:02 --> Model "AdsTemp_m" initialized
DEBUG - 2019-01-25 06:17:02 --> cURL Class Initialized
INFO - 2019-01-25 06:17:02 --> Model "Replicator_m" initialized
INFO - 2019-01-25 06:17:02 --> Database Driver Class Initialized
INFO - 2019-01-25 06:17:03 --> Final output sent to browser
DEBUG - 2019-01-25 06:17:03 --> Total execution time: 1.2053
INFO - 2019-01-25 06:22:55 --> Config Class Initialized
INFO - 2019-01-25 06:22:55 --> Hooks Class Initialized
DEBUG - 2019-01-25 06:22:55 --> UTF-8 Support Enabled
INFO - 2019-01-25 06:22:55 --> Utf8 Class Initialized
INFO - 2019-01-25 06:22:55 --> URI Class Initialized
INFO - 2019-01-25 06:22:55 --> Router Class Initialized
INFO - 2019-01-25 06:22:55 --> Output Class Initialized
INFO - 2019-01-25 06:22:55 --> Security Class Initialized
DEBUG - 2019-01-25 06:22:55 --> Global POST, GET and COOKIE data sanitized
INFO - 2019-01-25 06:22:55 --> Input Class Initialized
INFO - 2019-01-25 06:22:55 --> Language Class Initialized
INFO - 2019-01-25 06:22:55 --> Loader Class Initialized
INFO - 2019-01-25 06:22:55 --> Helper loaded: common_helper
INFO - 2019-01-25 06:22:55 --> Database Driver Class Initialized
INFO - 2019-01-25 06:22:55 --> Controller Class Initialized
DEBUG - 2019-01-25 06:22:55 --> Config file loaded: /Users/blumine/works/goodoc_v2/event/adminApi/application/config/rest.php
INFO - 2019-01-25 06:22:55 --> Helper loaded: inflector_helper
INFO - 2019-01-25 06:22:55 --> Language file loaded: language/english/rest_controller_lang.php
INFO - 2019-01-25 06:22:55 --> Model "Ads_m" initialized
INFO - 2019-01-25 06:22:55 --> Model "Common_m" initialized
INFO - 2019-01-25 06:22:55 --> Model "AdsTemp_m" initialized
DEBUG - 2019-01-25 06:22:55 --> cURL Class Initialized
INFO - 2019-01-25 06:22:55 --> Model "Replicator_m" initialized
INFO - 2019-01-25 06:22:55 --> Database Driver Class Initialized
INFO - 2019-01-25 06:23:22 --> Config Class Initialized
INFO - 2019-01-25 06:23:22 --> Hooks Class Initialized
DEBUG - 2019-01-25 06:23:22 --> UTF-8 Support Enabled
INFO - 2019-01-25 06:23:22 --> Utf8 Class Initialized
INFO - 2019-01-25 06:23:22 --> URI Class Initialized
INFO - 2019-01-25 06:23:22 --> Router Class Initialized
INFO - 2019-01-25 06:23:22 --> Output Class Initialized
INFO - 2019-01-25 06:23:22 --> Security Class Initialized
DEBUG - 2019-01-25 06:23:22 --> Global POST, GET and COOKIE data sanitized
INFO - 2019-01-25 06:23:22 --> Input Class Initialized
INFO - 2019-01-25 06:23:22 --> Language Class Initialized
INFO - 2019-01-25 06:23:22 --> Loader Class Initialized
INFO - 2019-01-25 06:23:22 --> Helper loaded: common_helper
INFO - 2019-01-25 06:23:22 --> Database Driver Class Initialized
INFO - 2019-01-25 06:23:22 --> Controller Class Initialized
DEBUG - 2019-01-25 06:23:22 --> Config file loaded: /Users/blumine/works/goodoc_v2/event/adminApi/application/config/rest.php
INFO - 2019-01-25 06:23:22 --> Helper loaded: inflector_helper
INFO - 2019-01-25 06:23:22 --> Language file loaded: language/english/rest_controller_lang.php
INFO - 2019-01-25 06:23:22 --> Model "Ads_m" initialized
INFO - 2019-01-25 06:23:22 --> Model "Common_m" initialized
INFO - 2019-01-25 06:23:22 --> Model "AdsTemp_m" initialized
DEBUG - 2019-01-25 06:23:22 --> cURL Class Initialized
INFO - 2019-01-25 06:23:22 --> Model "Replicator_m" initialized
INFO - 2019-01-25 06:23:22 --> Database Driver Class Initialized
INFO - 2019-01-25 06:24:27 --> Config Class Initialized
INFO - 2019-01-25 06:24:27 --> Hooks Class Initialized
DEBUG - 2019-01-25 06:24:27 --> UTF-8 Support Enabled
INFO - 2019-01-25 06:24:27 --> Utf8 Class Initialized
INFO - 2019-01-25 06:24:27 --> URI Class Initialized
INFO - 2019-01-25 06:24:27 --> Router Class Initialized
INFO - 2019-01-25 06:24:27 --> Output Class Initialized
INFO - 2019-01-25 06:24:27 --> Security Class Initialized
DEBUG - 2019-01-25 06:24:27 --> Global POST, GET and COOKIE data sanitized
INFO - 2019-01-25 06:24:27 --> Input Class Initialized
INFO - 2019-01-25 06:24:27 --> Language Class Initialized
INFO - 2019-01-25 06:24:27 --> Loader Class Initialized
INFO - 2019-01-25 06:24:27 --> Helper loaded: common_helper
INFO - 2019-01-25 06:24:27 --> Database Driver Class Initialized
INFO - 2019-01-25 06:24:27 --> Controller Class Initialized
DEBUG - 2019-01-25 06:24:27 --> Config file loaded: /Users/blumine/works/goodoc_v2/event/adminApi/application/config/rest.php
INFO - 2019-01-25 06:24:27 --> Helper loaded: inflector_helper
INFO - 2019-01-25 06:24:27 --> Language file loaded: language/english/rest_controller_lang.php
INFO - 2019-01-25 06:24:27 --> Model "Ads_m" initialized
INFO - 2019-01-25 06:24:27 --> Model "Common_m" initialized
INFO - 2019-01-25 06:24:27 --> Model "AdsTemp_m" initialized
DEBUG - 2019-01-25 06:24:27 --> cURL Class Initialized
INFO - 2019-01-25 06:24:27 --> Model "Replicator_m" initialized
INFO - 2019-01-25 06:24:27 --> Database Driver Class Initialized
INFO - 2019-01-25 06:25:51 --> Config Class Initialized
INFO - 2019-01-25 06:25:51 --> Hooks Class Initialized
DEBUG - 2019-01-25 06:25:51 --> UTF-8 Support Enabled
INFO - 2019-01-25 06:25:51 --> Utf8 Class Initialized
INFO - 2019-01-25 06:25:51 --> URI Class Initialized
INFO - 2019-01-25 06:25:51 --> Router Class Initialized
INFO - 2019-01-25 06:25:51 --> Output Class Initialized
INFO - 2019-01-25 06:25:51 --> Security Class Initialized
DEBUG - 2019-01-25 06:25:51 --> Global POST, GET and COOKIE data sanitized
INFO - 2019-01-25 06:25:51 --> Input Class Initialized
INFO - 2019-01-25 06:25:51 --> Language Class Initialized
INFO - 2019-01-25 06:25:51 --> Loader Class Initialized
INFO - 2019-01-25 06:25:51 --> Helper loaded: common_helper
INFO - 2019-01-25 06:25:51 --> Database Driver Class Initialized
INFO - 2019-01-25 06:25:51 --> Controller Class Initialized
DEBUG - 2019-01-25 06:25:51 --> Config file loaded: /Users/blumine/works/goodoc_v2/event/adminApi/application/config/rest.php
INFO - 2019-01-25 06:25:51 --> Helper loaded: inflector_helper
INFO - 2019-01-25 06:25:51 --> Language file loaded: language/english/rest_controller_lang.php
INFO - 2019-01-25 06:25:51 --> Model "Ads_m" initialized
INFO - 2019-01-25 06:25:51 --> Model "Common_m" initialized
INFO - 2019-01-25 06:25:51 --> Model "AdsTemp_m" initialized
DEBUG - 2019-01-25 06:25:51 --> cURL Class Initialized
INFO - 2019-01-25 06:25:51 --> Model "Replicator_m" initialized
INFO - 2019-01-25 06:25:51 --> Database Driver Class Initialized
INFO - 2019-01-25 06:26:02 --> Config Class Initialized
INFO - 2019-01-25 06:26:02 --> Hooks Class Initialized
DEBUG - 2019-01-25 06:26:02 --> UTF-8 Support Enabled
INFO - 2019-01-25 06:26:02 --> Utf8 Class Initialized
INFO - 2019-01-25 06:26:02 --> URI Class Initialized
INFO - 2019-01-25 06:26:02 --> Router Class Initialized
INFO - 2019-01-25 06:26:02 --> Output Class Initialized
INFO - 2019-01-25 06:26:02 --> Security Class Initialized
DEBUG - 2019-01-25 06:26:02 --> Global POST, GET and COOKIE data sanitized
INFO - 2019-01-25 06:26:02 --> Input Class Initialized
INFO - 2019-01-25 06:26:02 --> Language Class Initialized
INFO - 2019-01-25 06:26:02 --> Loader Class Initialized
INFO - 2019-01-25 06:26:02 --> Helper loaded: common_helper
INFO - 2019-01-25 06:26:02 --> Database Driver Class Initialized
INFO - 2019-01-25 06:26:02 --> Controller Class Initialized
DEBUG - 2019-01-25 06:26:02 --> Config file loaded: /Users/blumine/works/goodoc_v2/event/adminApi/application/config/rest.php
INFO - 2019-01-25 06:26:02 --> Helper loaded: inflector_helper
INFO - 2019-01-25 06:26:02 --> Language file loaded: language/english/rest_controller_lang.php
INFO - 2019-01-25 06:26:02 --> Model "Ads_m" initialized
INFO - 2019-01-25 06:26:02 --> Model "Common_m" initialized
INFO - 2019-01-25 06:26:02 --> Model "AdsTemp_m" initialized
DEBUG - 2019-01-25 06:26:02 --> cURL Class Initialized
INFO - 2019-01-25 06:26:02 --> Model "Replicator_m" initialized
INFO - 2019-01-25 06:26:02 --> Database Driver Class Initialized
INFO - 2019-01-25 06:28:02 --> Config Class Initialized
INFO - 2019-01-25 06:28:02 --> Hooks Class Initialized
DEBUG - 2019-01-25 06:28:02 --> UTF-8 Support Enabled
INFO - 2019-01-25 06:28:02 --> Utf8 Class Initialized
INFO - 2019-01-25 06:28:02 --> URI Class Initialized
INFO - 2019-01-25 06:28:02 --> Router Class Initialized
INFO - 2019-01-25 06:28:02 --> Output Class Initialized
INFO - 2019-01-25 06:28:02 --> Security Class Initialized
DEBUG - 2019-01-25 06:28:02 --> Global POST, GET and COOKIE data sanitized
INFO - 2019-01-25 06:28:02 --> Input Class Initialized
INFO - 2019-01-25 06:28:02 --> Language Class Initialized
INFO - 2019-01-25 06:28:02 --> Loader Class Initialized
INFO - 2019-01-25 06:28:02 --> Helper loaded: common_helper
INFO - 2019-01-25 06:28:02 --> Database Driver Class Initialized
INFO - 2019-01-25 06:28:02 --> Controller Class Initialized
DEBUG - 2019-01-25 06:28:02 --> Config file loaded: /Users/blumine/works/goodoc_v2/event/adminApi/application/config/rest.php
INFO - 2019-01-25 06:28:02 --> Helper loaded: inflector_helper
INFO - 2019-01-25 06:28:02 --> Language file loaded: language/english/rest_controller_lang.php
INFO - 2019-01-25 06:28:02 --> Model "Ads_m" initialized
INFO - 2019-01-25 06:28:02 --> Model "Common_m" initialized
INFO - 2019-01-25 06:28:02 --> Model "AdsTemp_m" initialized
DEBUG - 2019-01-25 06:28:02 --> cURL Class Initialized
INFO - 2019-01-25 06:28:02 --> Model "Replicator_m" initialized
INFO - 2019-01-25 06:28:02 --> Database Driver Class Initialized
INFO - 2019-01-25 06:28:49 --> Config Class Initialized
INFO - 2019-01-25 06:28:49 --> Hooks Class Initialized
DEBUG - 2019-01-25 06:28:49 --> UTF-8 Support Enabled
INFO - 2019-01-25 06:28:49 --> Utf8 Class Initialized
INFO - 2019-01-25 06:28:49 --> URI Class Initialized
INFO - 2019-01-25 06:28:49 --> Router Class Initialized
INFO - 2019-01-25 06:28:49 --> Output Class Initialized
INFO - 2019-01-25 06:28:49 --> Security Class Initialized
DEBUG - 2019-01-25 06:28:49 --> Global POST, GET and COOKIE data sanitized
INFO - 2019-01-25 06:28:49 --> Input Class Initialized
INFO - 2019-01-25 06:28:49 --> Language Class Initialized
INFO - 2019-01-25 06:28:49 --> Loader Class Initialized
INFO - 2019-01-25 06:28:49 --> Helper loaded: common_helper
INFO - 2019-01-25 06:28:49 --> Database Driver Class Initialized
INFO - 2019-01-25 06:28:49 --> Controller Class Initialized
DEBUG - 2019-01-25 06:28:49 --> Config file loaded: /Users/blumine/works/goodoc_v2/event/adminApi/application/config/rest.php
INFO - 2019-01-25 06:28:49 --> Helper loaded: inflector_helper
INFO - 2019-01-25 06:28:49 --> Language file loaded: language/english/rest_controller_lang.php
INFO - 2019-01-25 06:28:49 --> Model "Ads_m" initialized
INFO - 2019-01-25 06:28:49 --> Model "Common_m" initialized
INFO - 2019-01-25 06:28:49 --> Model "AdsTemp_m" initialized
DEBUG - 2019-01-25 06:28:49 --> cURL Class Initialized
INFO - 2019-01-25 06:28:49 --> Model "Replicator_m" initialized
INFO - 2019-01-25 06:28:49 --> Database Driver Class Initialized
INFO - 2019-01-25 06:29:53 --> Config Class Initialized
INFO - 2019-01-25 06:29:53 --> Hooks Class Initialized
DEBUG - 2019-01-25 06:29:53 --> UTF-8 Support Enabled
INFO - 2019-01-25 06:29:53 --> Utf8 Class Initialized
INFO - 2019-01-25 06:29:53 --> URI Class Initialized
INFO - 2019-01-25 06:29:53 --> Router Class Initialized
INFO - 2019-01-25 06:29:53 --> Output Class Initialized
INFO - 2019-01-25 06:29:53 --> Security Class Initialized
DEBUG - 2019-01-25 06:29:53 --> Global POST, GET and COOKIE data sanitized
INFO - 2019-01-25 06:29:53 --> Input Class Initialized
INFO - 2019-01-25 06:29:53 --> Language Class Initialized
INFO - 2019-01-25 06:29:53 --> Loader Class Initialized
INFO - 2019-01-25 06:29:53 --> Helper loaded: common_helper
INFO - 2019-01-25 06:29:53 --> Database Driver Class Initialized
INFO - 2019-01-25 06:29:53 --> Controller Class Initialized
DEBUG - 2019-01-25 06:29:53 --> Config file loaded: /Users/blumine/works/goodoc_v2/event/adminApi/application/config/rest.php
INFO - 2019-01-25 06:29:53 --> Helper loaded: inflector_helper
INFO - 2019-01-25 06:29:53 --> Language file loaded: language/english/rest_controller_lang.php
INFO - 2019-01-25 06:29:53 --> Model "Ads_m" initialized
INFO - 2019-01-25 06:29:53 --> Model "Common_m" initialized
INFO - 2019-01-25 06:29:53 --> Model "AdsTemp_m" initialized
DEBUG - 2019-01-25 06:29:53 --> cURL Class Initialized
INFO - 2019-01-25 06:29:53 --> Model "Replicator_m" initialized
INFO - 2019-01-25 06:29:53 --> Database Driver Class Initialized
INFO - 2019-01-25 06:30:12 --> Config Class Initialized
INFO - 2019-01-25 06:30:12 --> Hooks Class Initialized
DEBUG - 2019-01-25 06:30:12 --> UTF-8 Support Enabled
INFO - 2019-01-25 06:30:12 --> Utf8 Class Initialized
INFO - 2019-01-25 06:30:12 --> URI Class Initialized
INFO - 2019-01-25 06:30:12 --> Router Class Initialized
INFO - 2019-01-25 06:30:12 --> Output Class Initialized
INFO - 2019-01-25 06:30:12 --> Security Class Initialized
DEBUG - 2019-01-25 06:30:12 --> Global POST, GET and COOKIE data sanitized
INFO - 2019-01-25 06:30:12 --> Input Class Initialized
INFO - 2019-01-25 06:30:12 --> Language Class Initialized
INFO - 2019-01-25 06:30:12 --> Loader Class Initialized
INFO - 2019-01-25 06:30:12 --> Helper loaded: common_helper
INFO - 2019-01-25 06:30:12 --> Database Driver Class Initialized
INFO - 2019-01-25 06:30:12 --> Controller Class Initialized
DEBUG - 2019-01-25 06:30:12 --> Config file loaded: /Users/blumine/works/goodoc_v2/event/adminApi/application/config/rest.php
INFO - 2019-01-25 06:30:12 --> Helper loaded: inflector_helper
INFO - 2019-01-25 06:30:12 --> Language file loaded: language/english/rest_controller_lang.php
INFO - 2019-01-25 06:30:12 --> Model "Ads_m" initialized
INFO - 2019-01-25 06:30:12 --> Model "Common_m" initialized
INFO - 2019-01-25 06:30:12 --> Model "AdsTemp_m" initialized
DEBUG - 2019-01-25 06:30:12 --> cURL Class Initialized
INFO - 2019-01-25 06:30:12 --> Model "Replicator_m" initialized
INFO - 2019-01-25 06:30:12 --> Database Driver Class Initialized
INFO - 2019-01-25 06:30:59 --> Config Class Initialized
INFO - 2019-01-25 06:30:59 --> Hooks Class Initialized
DEBUG - 2019-01-25 06:30:59 --> UTF-8 Support Enabled
INFO - 2019-01-25 06:30:59 --> Utf8 Class Initialized
INFO - 2019-01-25 06:30:59 --> URI Class Initialized
INFO - 2019-01-25 06:30:59 --> Router Class Initialized
INFO - 2019-01-25 06:30:59 --> Output Class Initialized
INFO - 2019-01-25 06:30:59 --> Security Class Initialized
DEBUG - 2019-01-25 06:30:59 --> Global POST, GET and COOKIE data sanitized
INFO - 2019-01-25 06:30:59 --> Input Class Initialized
INFO - 2019-01-25 06:30:59 --> Language Class Initialized
INFO - 2019-01-25 06:30:59 --> Loader Class Initialized
INFO - 2019-01-25 06:30:59 --> Helper loaded: common_helper
INFO - 2019-01-25 06:30:59 --> Database Driver Class Initialized
INFO - 2019-01-25 06:30:59 --> Controller Class Initialized
DEBUG - 2019-01-25 06:30:59 --> Config file loaded: /Users/blumine/works/goodoc_v2/event/adminApi/application/config/rest.php
INFO - 2019-01-25 06:30:59 --> Helper loaded: inflector_helper
INFO - 2019-01-25 06:30:59 --> Language file loaded: language/english/rest_controller_lang.php
INFO - 2019-01-25 06:30:59 --> Model "Ads_m" initialized
INFO - 2019-01-25 06:30:59 --> Model "Common_m" initialized
INFO - 2019-01-25 06:30:59 --> Model "AdsTemp_m" initialized
DEBUG - 2019-01-25 06:30:59 --> cURL Class Initialized
INFO - 2019-01-25 06:30:59 --> Model "Replicator_m" initialized
INFO - 2019-01-25 06:30:59 --> Database Driver Class Initialized
INFO - 2019-01-25 06:31:49 --> Config Class Initialized
INFO - 2019-01-25 06:31:49 --> Hooks Class Initialized
DEBUG - 2019-01-25 06:31:49 --> UTF-8 Support Enabled
INFO - 2019-01-25 06:31:49 --> Utf8 Class Initialized
INFO - 2019-01-25 06:31:49 --> URI Class Initialized
INFO - 2019-01-25 06:31:49 --> Router Class Initialized
INFO - 2019-01-25 06:31:49 --> Output Class Initialized
INFO - 2019-01-25 06:31:49 --> Security Class Initialized
DEBUG - 2019-01-25 06:31:49 --> Global POST, GET and COOKIE data sanitized
INFO - 2019-01-25 06:31:49 --> Input Class Initialized
INFO - 2019-01-25 06:31:49 --> Language Class Initialized
INFO - 2019-01-25 06:31:49 --> Loader Class Initialized
INFO - 2019-01-25 06:31:49 --> Helper loaded: common_helper
INFO - 2019-01-25 06:31:49 --> Database Driver Class Initialized
INFO - 2019-01-25 06:31:49 --> Controller Class Initialized
DEBUG - 2019-01-25 06:31:49 --> Config file loaded: /Users/blumine/works/goodoc_v2/event/adminApi/application/config/rest.php
INFO - 2019-01-25 06:31:49 --> Helper loaded: inflector_helper
INFO - 2019-01-25 06:31:49 --> Language file loaded: language/english/rest_controller_lang.php
INFO - 2019-01-25 06:31:49 --> Model "Ads_m" initialized
INFO - 2019-01-25 06:31:49 --> Model "Common_m" initialized
INFO - 2019-01-25 06:31:49 --> Model "AdsTemp_m" initialized
DEBUG - 2019-01-25 06:31:49 --> cURL Class Initialized
INFO - 2019-01-25 06:31:49 --> Model "Replicator_m" initialized
INFO - 2019-01-25 06:31:49 --> Database Driver Class Initialized
INFO - 2019-01-25 06:32:12 --> Config Class Initialized
INFO - 2019-01-25 06:32:12 --> Hooks Class Initialized
DEBUG - 2019-01-25 06:32:12 --> UTF-8 Support Enabled
INFO - 2019-01-25 06:32:12 --> Utf8 Class Initialized
INFO - 2019-01-25 06:32:12 --> URI Class Initialized
INFO - 2019-01-25 06:32:12 --> Router Class Initialized
INFO - 2019-01-25 06:32:12 --> Output Class Initialized
INFO - 2019-01-25 06:32:12 --> Security Class Initialized
DEBUG - 2019-01-25 06:32:12 --> Global POST, GET and COOKIE data sanitized
INFO - 2019-01-25 06:32:12 --> Input Class Initialized
INFO - 2019-01-25 06:32:12 --> Language Class Initialized
INFO - 2019-01-25 06:32:12 --> Loader Class Initialized
INFO - 2019-01-25 06:32:12 --> Helper loaded: common_helper
INFO - 2019-01-25 06:32:12 --> Database Driver Class Initialized
INFO - 2019-01-25 06:32:13 --> Controller Class Initialized
DEBUG - 2019-01-25 06:32:13 --> Config file loaded: /Users/blumine/works/goodoc_v2/event/adminApi/application/config/rest.php
INFO - 2019-01-25 06:32:13 --> Helper loaded: inflector_helper
INFO - 2019-01-25 06:32:13 --> Language file loaded: language/english/rest_controller_lang.php
INFO - 2019-01-25 06:32:13 --> Model "Ads_m" initialized
INFO - 2019-01-25 06:32:13 --> Model "Common_m" initialized
INFO - 2019-01-25 06:32:13 --> Model "AdsTemp_m" initialized
DEBUG - 2019-01-25 06:32:13 --> cURL Class Initialized
INFO - 2019-01-25 06:32:13 --> Model "Replicator_m" initialized
INFO - 2019-01-25 06:32:13 --> Database Driver Class Initialized
INFO - 2019-01-25 06:32:52 --> Config Class Initialized
INFO - 2019-01-25 06:32:52 --> Hooks Class Initialized
DEBUG - 2019-01-25 06:32:52 --> UTF-8 Support Enabled
INFO - 2019-01-25 06:32:52 --> Utf8 Class Initialized
INFO - 2019-01-25 06:32:52 --> URI Class Initialized
INFO - 2019-01-25 06:32:52 --> Router Class Initialized
INFO - 2019-01-25 06:32:52 --> Output Class Initialized
INFO - 2019-01-25 06:32:52 --> Security Class Initialized
DEBUG - 2019-01-25 06:32:52 --> Global POST, GET and COOKIE data sanitized
INFO - 2019-01-25 06:32:52 --> Input Class Initialized
INFO - 2019-01-25 06:32:52 --> Language Class Initialized
INFO - 2019-01-25 06:32:52 --> Loader Class Initialized
INFO - 2019-01-25 06:32:52 --> Helper loaded: common_helper
INFO - 2019-01-25 06:32:52 --> Database Driver Class Initialized
INFO - 2019-01-25 06:32:52 --> Controller Class Initialized
DEBUG - 2019-01-25 06:32:52 --> Config file loaded: /Users/blumine/works/goodoc_v2/event/adminApi/application/config/rest.php
INFO - 2019-01-25 06:32:52 --> Helper loaded: inflector_helper
INFO - 2019-01-25 06:32:52 --> Language file loaded: language/english/rest_controller_lang.php
INFO - 2019-01-25 06:32:52 --> Model "Ads_m" initialized
INFO - 2019-01-25 06:32:52 --> Model "Common_m" initialized
INFO - 2019-01-25 06:32:52 --> Model "AdsTemp_m" initialized
DEBUG - 2019-01-25 06:32:52 --> cURL Class Initialized
INFO - 2019-01-25 06:32:52 --> Model "Replicator_m" initialized
INFO - 2019-01-25 06:32:52 --> Database Driver Class Initialized
INFO - 2019-01-25 06:33:08 --> Config Class Initialized
INFO - 2019-01-25 06:33:08 --> Hooks Class Initialized
DEBUG - 2019-01-25 06:33:08 --> UTF-8 Support Enabled
INFO - 2019-01-25 06:33:08 --> Utf8 Class Initialized
INFO - 2019-01-25 06:33:08 --> URI Class Initialized
INFO - 2019-01-25 06:33:08 --> Router Class Initialized
INFO - 2019-01-25 06:33:08 --> Output Class Initialized
INFO - 2019-01-25 06:33:08 --> Security Class Initialized
DEBUG - 2019-01-25 06:33:08 --> Global POST, GET and COOKIE data sanitized
INFO - 2019-01-25 06:33:08 --> Input Class Initialized
INFO - 2019-01-25 06:33:08 --> Language Class Initialized
INFO - 2019-01-25 06:33:08 --> Loader Class Initialized
INFO - 2019-01-25 06:33:08 --> Helper loaded: common_helper
INFO - 2019-01-25 06:33:08 --> Database Driver Class Initialized
INFO - 2019-01-25 06:33:08 --> Controller Class Initialized
DEBUG - 2019-01-25 06:33:08 --> Config file loaded: /Users/blumine/works/goodoc_v2/event/adminApi/application/config/rest.php
INFO - 2019-01-25 06:33:08 --> Helper loaded: inflector_helper
INFO - 2019-01-25 06:33:08 --> Language file loaded: language/english/rest_controller_lang.php
INFO - 2019-01-25 06:33:08 --> Model "Ads_m" initialized
INFO - 2019-01-25 06:33:08 --> Model "Common_m" initialized
INFO - 2019-01-25 06:33:08 --> Model "AdsTemp_m" initialized
DEBUG - 2019-01-25 06:33:08 --> cURL Class Initialized
INFO - 2019-01-25 06:33:08 --> Model "Replicator_m" initialized
INFO - 2019-01-25 06:33:08 --> Database Driver Class Initialized
INFO - 2019-01-25 06:33:09 --> Final output sent to browser
DEBUG - 2019-01-25 06:33:09 --> Total execution time: 1.0108
INFO - 2019-01-25 06:33:14 --> Config Class Initialized
INFO - 2019-01-25 06:33:14 --> Hooks Class Initialized
DEBUG - 2019-01-25 06:33:14 --> UTF-8 Support Enabled
INFO - 2019-01-25 06:33:14 --> Utf8 Class Initialized
INFO - 2019-01-25 06:33:14 --> URI Class Initialized
INFO - 2019-01-25 06:33:14 --> Router Class Initialized
INFO - 2019-01-25 06:33:14 --> Output Class Initialized
INFO - 2019-01-25 06:33:14 --> Security Class Initialized
DEBUG - 2019-01-25 06:33:14 --> Global POST, GET and COOKIE data sanitized
INFO - 2019-01-25 06:33:14 --> Input Class Initialized
INFO - 2019-01-25 06:33:14 --> Language Class Initialized
INFO - 2019-01-25 06:33:14 --> Loader Class Initialized
INFO - 2019-01-25 06:33:14 --> Helper loaded: common_helper
INFO - 2019-01-25 06:33:14 --> Database Driver Class Initialized
INFO - 2019-01-25 06:33:14 --> Controller Class Initialized
DEBUG - 2019-01-25 06:33:14 --> Config file loaded: /Users/blumine/works/goodoc_v2/event/adminApi/application/config/rest.php
INFO - 2019-01-25 06:33:14 --> Helper loaded: inflector_helper
INFO - 2019-01-25 06:33:14 --> Language file loaded: language/english/rest_controller_lang.php
INFO - 2019-01-25 06:33:14 --> Model "Ads_m" initialized
INFO - 2019-01-25 06:33:14 --> Model "Common_m" initialized
INFO - 2019-01-25 06:33:14 --> Model "AdsTemp_m" initialized
DEBUG - 2019-01-25 06:33:14 --> cURL Class Initialized
INFO - 2019-01-25 06:33:14 --> Model "Replicator_m" initialized
INFO - 2019-01-25 06:33:14 --> Database Driver Class Initialized
INFO - 2019-01-25 06:33:15 --> Final output sent to browser
DEBUG - 2019-01-25 06:33:15 --> Total execution time: 1.2989
INFO - 2019-01-25 06:33:45 --> Config Class Initialized
INFO - 2019-01-25 06:33:45 --> Hooks Class Initialized
DEBUG - 2019-01-25 06:33:45 --> UTF-8 Support Enabled
INFO - 2019-01-25 06:33:45 --> Utf8 Class Initialized
INFO - 2019-01-25 06:33:45 --> URI Class Initialized
INFO - 2019-01-25 06:33:45 --> Router Class Initialized
INFO - 2019-01-25 06:33:45 --> Output Class Initialized
INFO - 2019-01-25 06:33:45 --> Security Class Initialized
DEBUG - 2019-01-25 06:33:45 --> Global POST, GET and COOKIE data sanitized
INFO - 2019-01-25 06:33:45 --> Input Class Initialized
INFO - 2019-01-25 06:33:45 --> Language Class Initialized
INFO - 2019-01-25 06:33:45 --> Loader Class Initialized
INFO - 2019-01-25 06:33:45 --> Helper loaded: common_helper
INFO - 2019-01-25 06:33:45 --> Database Driver Class Initialized
INFO - 2019-01-25 06:33:45 --> Controller Class Initialized
DEBUG - 2019-01-25 06:33:45 --> Config file loaded: /Users/blumine/works/goodoc_v2/event/adminApi/application/config/rest.php
INFO - 2019-01-25 06:33:45 --> Helper loaded: inflector_helper
INFO - 2019-01-25 06:33:45 --> Language file loaded: language/english/rest_controller_lang.php
INFO - 2019-01-25 06:33:45 --> Model "Ads_m" initialized
INFO - 2019-01-25 06:33:45 --> Model "Common_m" initialized
INFO - 2019-01-25 06:33:45 --> Model "AdsTemp_m" initialized
DEBUG - 2019-01-25 06:33:45 --> cURL Class Initialized
INFO - 2019-01-25 06:33:45 --> Model "Replicator_m" initialized
INFO - 2019-01-25 06:33:45 --> Database Driver Class Initialized
INFO - 2019-01-25 06:52:50 --> Config Class Initialized
INFO - 2019-01-25 06:52:50 --> Hooks Class Initialized
DEBUG - 2019-01-25 06:52:50 --> UTF-8 Support Enabled
INFO - 2019-01-25 06:52:50 --> Utf8 Class Initialized
INFO - 2019-01-25 06:52:50 --> URI Class Initialized
INFO - 2019-01-25 06:52:50 --> Router Class Initialized
INFO - 2019-01-25 06:52:50 --> Output Class Initialized
INFO - 2019-01-25 06:52:50 --> Security Class Initialized
DEBUG - 2019-01-25 06:52:50 --> Global POST, GET and COOKIE data sanitized
INFO - 2019-01-25 06:52:50 --> Input Class Initialized
INFO - 2019-01-25 06:52:50 --> Language Class Initialized
INFO - 2019-01-25 06:52:50 --> Loader Class Initialized
INFO - 2019-01-25 06:52:50 --> Helper loaded: common_helper
INFO - 2019-01-25 06:52:50 --> Database Driver Class Initialized
INFO - 2019-01-25 06:52:50 --> Controller Class Initialized
DEBUG - 2019-01-25 06:52:50 --> Config file loaded: /Users/blumine/works/goodoc_v2/event/adminApi/application/config/rest.php
INFO - 2019-01-25 06:52:50 --> Helper loaded: inflector_helper
INFO - 2019-01-25 06:52:50 --> Language file loaded: language/english/rest_controller_lang.php
INFO - 2019-01-25 06:52:50 --> Model "contractOrder_m" initialized
INFO - 2019-01-25 06:52:50 --> Model "Common_m" initialized
DEBUG - 2019-01-25 06:52:50 --> cURL Class Initialized
INFO - 2019-01-25 06:52:50 --> Model "Replicator_m" initialized
INFO - 2019-01-25 06:52:50 --> Model "Ads_m" initialized
INFO - 2019-01-25 06:52:50 --> Database Driver Class Initialized
INFO - 2019-01-25 06:53:30 --> Config Class Initialized
INFO - 2019-01-25 06:53:30 --> Hooks Class Initialized
DEBUG - 2019-01-25 06:53:30 --> UTF-8 Support Enabled
INFO - 2019-01-25 06:53:30 --> Utf8 Class Initialized
INFO - 2019-01-25 06:53:30 --> URI Class Initialized
INFO - 2019-01-25 06:53:30 --> Router Class Initialized
INFO - 2019-01-25 06:53:30 --> Output Class Initialized
INFO - 2019-01-25 06:53:30 --> Security Class Initialized
DEBUG - 2019-01-25 06:53:30 --> Global POST, GET and COOKIE data sanitized
INFO - 2019-01-25 06:53:30 --> Input Class Initialized
INFO - 2019-01-25 06:53:30 --> Language Class Initialized
INFO - 2019-01-25 06:53:30 --> Loader Class Initialized
INFO - 2019-01-25 06:53:30 --> Helper loaded: common_helper
INFO - 2019-01-25 06:53:30 --> Database Driver Class Initialized
INFO - 2019-01-25 06:53:30 --> Controller Class Initialized
DEBUG - 2019-01-25 06:53:30 --> Config file loaded: /Users/blumine/works/goodoc_v2/event/adminApi/application/config/rest.php
INFO - 2019-01-25 06:53:30 --> Helper loaded: inflector_helper
INFO - 2019-01-25 06:53:30 --> Language file loaded: language/english/rest_controller_lang.php
INFO - 2019-01-25 06:53:30 --> Model "contractOrder_m" initialized
INFO - 2019-01-25 06:53:30 --> Model "Common_m" initialized
DEBUG - 2019-01-25 06:53:30 --> cURL Class Initialized
INFO - 2019-01-25 06:53:30 --> Model "Replicator_m" initialized
INFO - 2019-01-25 06:53:30 --> Model "Ads_m" initialized
INFO - 2019-01-25 06:53:30 --> Database Driver Class Initialized
INFO - 2019-01-25 06:53:32 --> Final output sent to browser
DEBUG - 2019-01-25 06:53:32 --> Total execution time: 1.2618
INFO - 2019-01-25 06:54:10 --> Config Class Initialized
INFO - 2019-01-25 06:54:10 --> Hooks Class Initialized
DEBUG - 2019-01-25 06:54:10 --> UTF-8 Support Enabled
INFO - 2019-01-25 06:54:10 --> Utf8 Class Initialized
INFO - 2019-01-25 06:54:10 --> URI Class Initialized
INFO - 2019-01-25 06:54:10 --> Router Class Initialized
INFO - 2019-01-25 06:54:10 --> Output Class Initialized
INFO - 2019-01-25 06:54:10 --> Security Class Initialized
DEBUG - 2019-01-25 06:54:10 --> Global POST, GET and COOKIE data sanitized
INFO - 2019-01-25 06:54:10 --> Input Class Initialized
INFO - 2019-01-25 06:54:10 --> Language Class Initialized
INFO - 2019-01-25 06:54:10 --> Loader Class Initialized
INFO - 2019-01-25 06:54:10 --> Helper loaded: common_helper
INFO - 2019-01-25 06:54:10 --> Database Driver Class Initialized
INFO - 2019-01-25 06:54:10 --> Controller Class Initialized
DEBUG - 2019-01-25 06:54:10 --> Config file loaded: /Users/blumine/works/goodoc_v2/event/adminApi/application/config/rest.php
INFO - 2019-01-25 06:54:10 --> Helper loaded: inflector_helper
INFO - 2019-01-25 06:54:10 --> Language file loaded: language/english/rest_controller_lang.php
INFO - 2019-01-25 06:54:10 --> Model "contractOrder_m" initialized
INFO - 2019-01-25 06:54:10 --> Model "Common_m" initialized
DEBUG - 2019-01-25 06:54:10 --> cURL Class Initialized
INFO - 2019-01-25 06:54:10 --> Model "Replicator_m" initialized
INFO - 2019-01-25 06:54:10 --> Model "Ads_m" initialized
INFO - 2019-01-25 06:54:10 --> Database Driver Class Initialized
INFO - 2019-01-25 06:55:07 --> Config Class Initialized
INFO - 2019-01-25 06:55:07 --> Hooks Class Initialized
DEBUG - 2019-01-25 06:55:07 --> UTF-8 Support Enabled
INFO - 2019-01-25 06:55:07 --> Utf8 Class Initialized
INFO - 2019-01-25 06:55:07 --> URI Class Initialized
INFO - 2019-01-25 06:55:07 --> Router Class Initialized
INFO - 2019-01-25 06:55:07 --> Output Class Initialized
INFO - 2019-01-25 06:55:07 --> Security Class Initialized
DEBUG - 2019-01-25 06:55:07 --> Global POST, GET and COOKIE data sanitized
INFO - 2019-01-25 06:55:07 --> Input Class Initialized
INFO - 2019-01-25 06:55:07 --> Language Class Initialized
INFO - 2019-01-25 06:55:07 --> Loader Class Initialized
INFO - 2019-01-25 06:55:07 --> Helper loaded: common_helper
INFO - 2019-01-25 06:55:07 --> Database Driver Class Initialized
INFO - 2019-01-25 06:55:07 --> Controller Class Initialized
DEBUG - 2019-01-25 06:55:07 --> Config file loaded: /Users/blumine/works/goodoc_v2/event/adminApi/application/config/rest.php
INFO - 2019-01-25 06:55:07 --> Helper loaded: inflector_helper
INFO - 2019-01-25 06:55:07 --> Language file loaded: language/english/rest_controller_lang.php
INFO - 2019-01-25 06:55:07 --> Model "contractOrder_m" initialized
INFO - 2019-01-25 06:55:07 --> Model "Common_m" initialized
DEBUG - 2019-01-25 06:55:07 --> cURL Class Initialized
INFO - 2019-01-25 06:55:07 --> Model "Replicator_m" initialized
INFO - 2019-01-25 06:55:07 --> Model "Ads_m" initialized
INFO - 2019-01-25 06:55:07 --> Database Driver Class Initialized
INFO - 2019-01-25 06:55:21 --> Config Class Initialized
INFO - 2019-01-25 06:55:21 --> Hooks Class Initialized
DEBUG - 2019-01-25 06:55:21 --> UTF-8 Support Enabled
INFO - 2019-01-25 06:55:21 --> Utf8 Class Initialized
INFO - 2019-01-25 06:55:21 --> URI Class Initialized
INFO - 2019-01-25 06:55:21 --> Router Class Initialized
INFO - 2019-01-25 06:55:21 --> Output Class Initialized
INFO - 2019-01-25 06:55:21 --> Security Class Initialized
DEBUG - 2019-01-25 06:55:21 --> Global POST, GET and COOKIE data sanitized
INFO - 2019-01-25 06:55:21 --> Input Class Initialized
INFO - 2019-01-25 06:55:21 --> Language Class Initialized
INFO - 2019-01-25 06:55:21 --> Loader Class Initialized
INFO - 2019-01-25 06:55:21 --> Helper loaded: common_helper
INFO - 2019-01-25 06:55:21 --> Database Driver Class Initialized
INFO - 2019-01-25 06:55:21 --> Controller Class Initialized
DEBUG - 2019-01-25 06:55:21 --> Config file loaded: /Users/blumine/works/goodoc_v2/event/adminApi/application/config/rest.php
INFO - 2019-01-25 06:55:21 --> Helper loaded: inflector_helper
INFO - 2019-01-25 06:55:21 --> Language file loaded: language/english/rest_controller_lang.php
INFO - 2019-01-25 06:55:21 --> Model "contractOrder_m" initialized
INFO - 2019-01-25 06:55:21 --> Model "Common_m" initialized
DEBUG - 2019-01-25 06:55:21 --> cURL Class Initialized
INFO - 2019-01-25 06:55:21 --> Model "Replicator_m" initialized
INFO - 2019-01-25 06:55:21 --> Model "Ads_m" initialized
INFO - 2019-01-25 06:55:21 --> Database Driver Class Initialized
INFO - 2019-01-25 06:58:17 --> Config Class Initialized
INFO - 2019-01-25 06:58:17 --> Hooks Class Initialized
DEBUG - 2019-01-25 06:58:17 --> UTF-8 Support Enabled
INFO - 2019-01-25 06:58:17 --> Utf8 Class Initialized
INFO - 2019-01-25 06:58:17 --> URI Class Initialized
INFO - 2019-01-25 06:58:17 --> Router Class Initialized
INFO - 2019-01-25 06:58:17 --> Output Class Initialized
INFO - 2019-01-25 06:58:17 --> Security Class Initialized
DEBUG - 2019-01-25 06:58:17 --> Global POST, GET and COOKIE data sanitized
INFO - 2019-01-25 06:58:17 --> Input Class Initialized
INFO - 2019-01-25 06:58:17 --> Language Class Initialized
INFO - 2019-01-25 06:58:17 --> Loader Class Initialized
INFO - 2019-01-25 06:58:17 --> Helper loaded: common_helper
INFO - 2019-01-25 06:58:17 --> Database Driver Class Initialized
INFO - 2019-01-25 06:58:17 --> Controller Class Initialized
DEBUG - 2019-01-25 06:58:17 --> Config file loaded: /Users/blumine/works/goodoc_v2/event/adminApi/application/config/rest.php
INFO - 2019-01-25 06:58:17 --> Helper loaded: inflector_helper
INFO - 2019-01-25 06:58:17 --> Language file loaded: language/english/rest_controller_lang.php
INFO - 2019-01-25 06:58:17 --> Model "contractOrder_m" initialized
INFO - 2019-01-25 06:58:17 --> Model "Common_m" initialized
DEBUG - 2019-01-25 06:58:17 --> cURL Class Initialized
INFO - 2019-01-25 06:58:17 --> Model "Replicator_m" initialized
INFO - 2019-01-25 06:58:17 --> Model "Ads_m" initialized
INFO - 2019-01-25 06:58:17 --> Database Driver Class Initialized
INFO - 2019-01-25 06:58:18 --> Final output sent to browser
DEBUG - 2019-01-25 06:58:18 --> Total execution time: 0.9901
