<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

INFO - 2019-02-01 02:16:07 --> Config Class Initialized
INFO - 2019-02-01 02:16:07 --> Hooks Class Initialized
DEBUG - 2019-02-01 02:16:07 --> UTF-8 Support Enabled
INFO - 2019-02-01 02:16:07 --> Utf8 Class Initialized
INFO - 2019-02-01 02:16:07 --> URI Class Initialized
INFO - 2019-02-01 02:16:07 --> Router Class Initialized
INFO - 2019-02-01 02:16:07 --> Output Class Initialized
INFO - 2019-02-01 02:16:07 --> Security Class Initialized
DEBUG - 2019-02-01 02:16:07 --> Global POST, GET and COOKIE data sanitized
INFO - 2019-02-01 02:16:07 --> Input Class Initialized
INFO - 2019-02-01 02:16:07 --> Language Class Initialized
INFO - 2019-02-01 02:16:07 --> Loader Class Initialized
INFO - 2019-02-01 02:16:07 --> Helper loaded: common_helper
INFO - 2019-02-01 02:16:07 --> Database Driver Class Initialized
ERROR - 2019-02-01 02:16:07 --> Severity: Warning --> mysqli::real_connect(): (HY000/2002): Connection refused /Users/blumine/works/goodoc_v2/event/adminApi/system/database/drivers/mysqli/mysqli_driver.php 201
ERROR - 2019-02-01 02:16:07 --> Unable to connect to the database
INFO - 2019-02-01 02:16:07 --> Language file loaded: language/english/db_lang.php
INFO - 2019-02-01 02:16:13 --> Config Class Initialized
INFO - 2019-02-01 02:16:13 --> Hooks Class Initialized
DEBUG - 2019-02-01 02:16:13 --> UTF-8 Support Enabled
INFO - 2019-02-01 02:16:13 --> Utf8 Class Initialized
INFO - 2019-02-01 02:16:13 --> URI Class Initialized
INFO - 2019-02-01 02:16:13 --> Router Class Initialized
INFO - 2019-02-01 02:16:13 --> Output Class Initialized
INFO - 2019-02-01 02:16:13 --> Security Class Initialized
DEBUG - 2019-02-01 02:16:13 --> Global POST, GET and COOKIE data sanitized
INFO - 2019-02-01 02:16:13 --> Input Class Initialized
INFO - 2019-02-01 02:16:13 --> Language Class Initialized
INFO - 2019-02-01 02:16:13 --> Loader Class Initialized
INFO - 2019-02-01 02:16:13 --> Helper loaded: common_helper
INFO - 2019-02-01 02:16:13 --> Database Driver Class Initialized
ERROR - 2019-02-01 02:16:13 --> Severity: Warning --> mysqli::real_connect(): (HY000/2002): Connection refused /Users/blumine/works/goodoc_v2/event/adminApi/system/database/drivers/mysqli/mysqli_driver.php 201
ERROR - 2019-02-01 02:16:13 --> Unable to connect to the database
INFO - 2019-02-01 02:16:13 --> Language file loaded: language/english/db_lang.php
INFO - 2019-02-01 02:18:13 --> Config Class Initialized
INFO - 2019-02-01 02:18:13 --> Hooks Class Initialized
DEBUG - 2019-02-01 02:18:13 --> UTF-8 Support Enabled
INFO - 2019-02-01 02:18:13 --> Utf8 Class Initialized
INFO - 2019-02-01 02:18:13 --> URI Class Initialized
INFO - 2019-02-01 02:18:13 --> Router Class Initialized
INFO - 2019-02-01 02:18:13 --> Output Class Initialized
INFO - 2019-02-01 02:18:13 --> Security Class Initialized
DEBUG - 2019-02-01 02:18:13 --> Global POST, GET and COOKIE data sanitized
INFO - 2019-02-01 02:18:13 --> Input Class Initialized
INFO - 2019-02-01 02:18:13 --> Language Class Initialized
INFO - 2019-02-01 02:18:13 --> Loader Class Initialized
INFO - 2019-02-01 02:18:13 --> Helper loaded: common_helper
INFO - 2019-02-01 02:18:13 --> Database Driver Class Initialized
INFO - 2019-02-01 02:18:13 --> Controller Class Initialized
DEBUG - 2019-02-01 02:18:13 --> Config file loaded: /Users/blumine/works/goodoc_v2/event/adminApi/application/config/rest.php
INFO - 2019-02-01 02:18:13 --> Helper loaded: inflector_helper
INFO - 2019-02-01 02:18:13 --> Language file loaded: language/english/rest_controller_lang.php
INFO - 2019-02-01 02:18:13 --> Model "Common_m" initialized
INFO - 2019-02-01 02:18:13 --> Model "Contract_m" initialized
INFO - 2019-02-01 02:18:13 --> Database Driver Class Initialized
ERROR - 2019-02-01 02:19:28 --> Query error: You have an error in your SQL syntax; check the manual that corresponds to your MariaDB server version for the right syntax to use near 'FROM `contract` `c`
            JOIN `contract_order_connect` `coc` ON `c`.`id`=' at line 45 - Invalid query: 
            select * from ( 
 
            select *, group_concat(hStatus) as advertiserStatus,  sum(progress0), sum(progress1), sum(progress2)
            from 
            (
            SELECT c.id, coc.`contractOrderId`, co.adType2,co.agencyCompanyName,
            c.payType, `c`.title, c.`hospitalId`, c.`hospitalName`, c.`agencyUserId`, c.`manageUserId`, co.`adPrice` oPrice, c.`regDate`,
            co.isNetwork, co.contractStatus,
            (select group_concat(contractOrderId SEPARATOR ',') from deposit where contractId=c.id) as contractOrderIds,
            -- 계약상태 
            (select count(*) from ads where contractId=c.id) as progress0,
            (select count(*) from ads where contractId=c.id and ads.isLive='Y') as progress1,
            (select count(*) from ads where contractId=c.id and ads.isLive='N') as progress2,
            if((select count(*) from ads where contractId=c.id) = 0, '대기',
            if((select count(*) from ads where contractId=c.id and ads.isLive='Y') > 0, '진행', 
                if(
                	(select count(*) from ads where contractId=c.id and ads.isLive='Y' and modDate > '2018-11-01') = 0 
                	and 
                	(select count(*) from ads where contractId=c.id and modDate > '2018-11-01') = (select count(*) from ads where contractId=c.id and ads.isLive='N' and modDate > '2018-11-01' 
                        and 
                        (
                        ifnull((select sum(price) from deposit where status in(2,4) and contractId=c.id ),0) 
                        - 
                        ifnull((select sum(price) from deposit where status in(3,5,6,7,8) and contractId=c.id),0)
                	) <= 0
                )
                	, '휴면', 
                    if(
                    	(select count(*) from ads where contractId=c.id and ads.isLive='Y') = 0 
                    	and 
                    	(select count(*) from ads where contractId=c.id)=(select count(*) from ads where contractId=c.id and ads.isLive='N' 
                            and 
                            (
                            ifnull((select sum(price) from deposit where status in(2,4) and contractId=c.id ),0)
                            - 
                            ifnull((select sum(price) from deposit where  status in(3,5,6,7,8) and contractId=c.id ),0)
                    	) > 0
                    )
                    	, '중지', '이탈'
                    ) 
                )	
            )
            ) as hStatus,
            -- 계약상태 
            FROM `contract` `c`
            JOIN `contract_order_connect` `coc` ON `c`.`id`=`coc`.`contractId`
            JOIN `contract_order` `co` ON `coc`.`contractOrderId`=`co`.`id`
            -- left join memo m on co.id=m.`targetId` and m.memoType=3
             where 1=1 
            ) q 
            group by q.hospitalID, q.id -- , q.contractOrderId
            ) g 
             having 1=1 
            
          
INFO - 2019-02-01 02:19:28 --> Language file loaded: language/english/db_lang.php
INFO - 2019-02-01 02:21:49 --> Config Class Initialized
INFO - 2019-02-01 02:21:49 --> Hooks Class Initialized
DEBUG - 2019-02-01 02:21:49 --> UTF-8 Support Enabled
INFO - 2019-02-01 02:21:49 --> Utf8 Class Initialized
INFO - 2019-02-01 02:21:49 --> URI Class Initialized
INFO - 2019-02-01 02:21:49 --> Router Class Initialized
INFO - 2019-02-01 02:21:49 --> Output Class Initialized
INFO - 2019-02-01 02:21:49 --> Security Class Initialized
DEBUG - 2019-02-01 02:21:49 --> Global POST, GET and COOKIE data sanitized
INFO - 2019-02-01 02:21:49 --> Input Class Initialized
INFO - 2019-02-01 02:21:49 --> Language Class Initialized
INFO - 2019-02-01 02:21:49 --> Loader Class Initialized
INFO - 2019-02-01 02:21:49 --> Helper loaded: common_helper
INFO - 2019-02-01 02:21:49 --> Database Driver Class Initialized
INFO - 2019-02-01 02:21:49 --> Controller Class Initialized
DEBUG - 2019-02-01 02:21:49 --> Config file loaded: /Users/blumine/works/goodoc_v2/event/adminApi/application/config/rest.php
INFO - 2019-02-01 02:21:49 --> Helper loaded: inflector_helper
INFO - 2019-02-01 02:21:49 --> Language file loaded: language/english/rest_controller_lang.php
INFO - 2019-02-01 02:21:49 --> Model "Common_m" initialized
INFO - 2019-02-01 02:21:49 --> Model "Contract_m" initialized
INFO - 2019-02-01 02:21:49 --> Database Driver Class Initialized
INFO - 2019-02-01 02:23:06 --> Final output sent to browser
DEBUG - 2019-02-01 02:23:06 --> Total execution time: 76.1786
INFO - 2019-02-01 02:24:59 --> Config Class Initialized
INFO - 2019-02-01 02:24:59 --> Hooks Class Initialized
DEBUG - 2019-02-01 02:24:59 --> UTF-8 Support Enabled
INFO - 2019-02-01 02:24:59 --> Utf8 Class Initialized
INFO - 2019-02-01 02:24:59 --> URI Class Initialized
INFO - 2019-02-01 02:24:59 --> Router Class Initialized
INFO - 2019-02-01 02:24:59 --> Output Class Initialized
INFO - 2019-02-01 02:24:59 --> Security Class Initialized
DEBUG - 2019-02-01 02:24:59 --> Global POST, GET and COOKIE data sanitized
INFO - 2019-02-01 02:24:59 --> Input Class Initialized
INFO - 2019-02-01 02:24:59 --> Language Class Initialized
INFO - 2019-02-01 02:24:59 --> Loader Class Initialized
INFO - 2019-02-01 02:24:59 --> Helper loaded: common_helper
INFO - 2019-02-01 02:24:59 --> Database Driver Class Initialized
INFO - 2019-02-01 02:24:59 --> Controller Class Initialized
DEBUG - 2019-02-01 02:24:59 --> Config file loaded: /Users/blumine/works/goodoc_v2/event/adminApi/application/config/rest.php
INFO - 2019-02-01 02:24:59 --> Helper loaded: inflector_helper
INFO - 2019-02-01 02:24:59 --> Language file loaded: language/english/rest_controller_lang.php
INFO - 2019-02-01 02:24:59 --> Model "Common_m" initialized
INFO - 2019-02-01 02:24:59 --> Model "Contract_m" initialized
INFO - 2019-02-01 02:24:59 --> Database Driver Class Initialized
INFO - 2019-02-01 02:26:15 --> Final output sent to browser
DEBUG - 2019-02-01 02:26:15 --> Total execution time: 76.1749
