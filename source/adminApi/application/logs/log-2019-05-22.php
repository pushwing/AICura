<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

ERROR - 2019-05-22 05:03:40 --> Severity: Notice --> Undefined property: CronDaily::$contractOrder_m /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/CronDaily.php 117
ERROR - 2019-05-22 05:03:40 --> 
        Exception of type \'Error\' occurred with Message: Call to a member function getLastContractOrderId() on null in File /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/CronDaily.php at Line 117
        
 Backtrace 
.#0 /Users/blumine/works/goodoc_v2/event/adminApi/system/core/CodeIgniter.php(532): CronDaily->getAdvertiserStatus()
#1 /Users/blumine/works/goodoc_v2/event/adminApi/index.php(333): require_once('/Users/blumine/...')
#2 {main}
    
ERROR - 2019-05-22 05:03:40 --> Severity: error --> Exception: Call to a member function getLastContractOrderId() on null /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/CronDaily.php 117
ERROR - 2019-05-22 05:03:41 --> 404 Page Not Found: Faviconico/index
ERROR - 2019-05-22 05:20:30 --> Query error: Unknown column 'q.hospitalID' in 'group statement' - Invalid query: 
        select * from (
        select
                    if((select count(*) from ads where contractId='3') = 0, '대기',
            if((select count(*) from ads where contractId='3' and ads.isLive='Y') > 0, '진행', 
                if(
                	(select count(*) from ads where contractId='3' and ads.isLive='Y' and modDate > '2019-02-22') = 0 
                	and 
                	(select count(*) from ads where contractId='3' and modDate > '2019-02-22') = (select count(*) from ads where contractId='3' and ads.isLive='N' and modDate > '2019-02-22' 
                        and 
                        (
                        ifnull((select sum(price) from deposit where status in(2,4) and contractId='3' and contractOrderId='50'),0) 
                        - 
                        ifnull((select sum(price) from deposit where status in(3,5,6,7,8) and contractId='3' and contractOrderId='50'),0)
                	) <= 0
                )
                	, '휴면', 
                    if(
                    	(select count(*) from ads where contractId='3' and ads.isLive='Y') = 0 
                    	and 
                    	(select count(*) from ads where contractId='3')=(select count(*) from ads where contractId='3' and ads.isLive='N' 
                            and 
                            (
                            ifnull((select sum(price) from deposit where status in(2,4) and contractId='3' and contractOrderId='50'),0)
                            - 
                            ifnull((select sum(price) from deposit where  status in(3,5,6,7,8) and contractId='3' and contractOrderId='50'),0)
                    	) > 0
                    )
                    	, '중지', '이탈'
                    ) 
                )	
            )
            ) as hStatus
            FROM `contract` `c`
            JOIN `contract_order_connect` `coc` ON `c`.`id`=`coc`.`contractId`
            JOIN `contract_order` `co` ON `coc`.`contractOrderId`=`co`.`id`
            where c.id='3'
            ) q
            group by q.hospitalID, q.id
                
ERROR - 2019-05-22 05:34:40 --> Severity: Notice --> Undefined index: hStatus /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/CronDaily.php 154
ERROR - 2019-05-22 05:34:40 --> Severity: Notice --> Undefined index: hStatus /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/CronDaily.php 154
ERROR - 2019-05-22 05:34:40 --> Severity: Notice --> Undefined index: hStatus /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/CronDaily.php 154
ERROR - 2019-05-22 05:35:09 --> Severity: Notice --> Undefined index: hStatus /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/CronDaily.php 155
ERROR - 2019-05-22 05:35:09 --> Severity: Notice --> Undefined index: hStatus /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/CronDaily.php 155
ERROR - 2019-05-22 05:35:09 --> Severity: Notice --> Undefined index: hStatus /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/CronDaily.php 155
ERROR - 2019-05-22 05:36:12 --> Severity: Notice --> Undefined index: hStatus /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/CronDaily.php 157
ERROR - 2019-05-22 05:36:12 --> Severity: Notice --> Undefined index: hStatus /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/CronDaily.php 157
ERROR - 2019-05-22 05:36:12 --> Severity: Notice --> Undefined index: hStatus /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/CronDaily.php 157
ERROR - 2019-05-22 05:43:38 --> Severity: Notice --> Undefined index: hStatus /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/CronDaily.php 159
ERROR - 2019-05-22 05:43:39 --> Severity: Notice --> Undefined index: hStatus /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/CronDaily.php 159
ERROR - 2019-05-22 05:43:39 --> Severity: Notice --> Undefined index: hStatus /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/CronDaily.php 159
ERROR - 2019-05-22 05:44:18 --> Severity: Notice --> Undefined index: hStatus /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/CronDaily.php 160
ERROR - 2019-05-22 05:44:18 --> Severity: Notice --> Undefined index: hStatus /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/CronDaily.php 160
ERROR - 2019-05-22 05:44:18 --> Severity: Notice --> Undefined index: hStatus /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/CronDaily.php 160
ERROR - 2019-05-22 05:45:57 --> Severity: Notice --> Undefined index: hStatus /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/CronDaily.php 160
ERROR - 2019-05-22 05:45:57 --> Severity: Notice --> Undefined index: hStatus /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/CronDaily.php 160
ERROR - 2019-05-22 05:45:57 --> Severity: Notice --> Undefined index: hStatus /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/CronDaily.php 160
ERROR - 2019-05-22 05:47:29 --> Severity: Notice --> Undefined index: hStatus /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/CronDaily.php 160
ERROR - 2019-05-22 05:47:29 --> Severity: Notice --> Undefined index: hStatus /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/CronDaily.php 160
ERROR - 2019-05-22 05:47:29 --> Severity: Notice --> Undefined index: hStatus /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/CronDaily.php 160
ERROR - 2019-05-22 05:49:26 --> Severity: Notice --> Undefined index: hStatus /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/CronDaily.php 160
ERROR - 2019-05-22 05:49:26 --> Severity: Notice --> Undefined index: hStatus /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/CronDaily.php 160
ERROR - 2019-05-22 05:49:26 --> Severity: Notice --> Undefined index: hStatus /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/CronDaily.php 160
ERROR - 2019-05-22 06:38:17 --> Severity: Notice --> Undefined index: depositStatusGroup /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/CronDaily.php 161
ERROR - 2019-05-22 06:38:17 --> Severity: Notice --> Undefined index: depositStatusGroup /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/CronDaily.php 161
ERROR - 2019-05-22 06:38:17 --> Severity: Notice --> Undefined index: depositStatusGroup /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/CronDaily.php 161
ERROR - 2019-05-22 06:38:17 --> Severity: Notice --> Undefined index: depositStatusGroup /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/CronDaily.php 161
ERROR - 2019-05-22 06:38:17 --> Severity: Notice --> Undefined index: depositStatusGroup /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/CronDaily.php 161
ERROR - 2019-05-22 06:38:17 --> Severity: Notice --> Undefined index: depositStatusGroup /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/CronDaily.php 161
ERROR - 2019-05-22 06:38:17 --> Severity: Notice --> Undefined index: depositStatusGroup /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/CronDaily.php 161
ERROR - 2019-05-22 06:38:17 --> Severity: Notice --> Undefined index: depositStatusGroup /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/CronDaily.php 161
ERROR - 2019-05-22 06:38:17 --> Severity: Notice --> Undefined index: depositStatusGroup /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/CronDaily.php 161
ERROR - 2019-05-22 06:38:17 --> Severity: Notice --> Undefined index: depositStatusGroup /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/CronDaily.php 161
ERROR - 2019-05-22 06:38:17 --> Severity: Notice --> Undefined index: depositStatusGroup /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/CronDaily.php 161
ERROR - 2019-05-22 06:38:17 --> Severity: Notice --> Undefined index: depositStatusGroup /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/CronDaily.php 161
ERROR - 2019-05-22 06:38:17 --> Severity: Notice --> Undefined index: depositStatusGroup /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/CronDaily.php 161
ERROR - 2019-05-22 06:38:17 --> Severity: Notice --> Undefined index: depositStatusGroup /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/CronDaily.php 161
ERROR - 2019-05-22 06:38:17 --> Severity: Notice --> Undefined index: depositStatusGroup /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/CronDaily.php 161
ERROR - 2019-05-22 06:38:17 --> Severity: Notice --> Undefined index: depositStatusGroup /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/CronDaily.php 161
ERROR - 2019-05-22 06:38:17 --> Severity: Notice --> Undefined index: depositStatusGroup /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/CronDaily.php 161
ERROR - 2019-05-22 06:38:17 --> Severity: Notice --> Undefined index: depositStatusGroup /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/CronDaily.php 161
ERROR - 2019-05-22 06:38:17 --> Severity: Notice --> Undefined index: depositStatusGroup /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/CronDaily.php 161
ERROR - 2019-05-22 06:38:17 --> Severity: Notice --> Undefined index: depositStatusGroup /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/CronDaily.php 161
ERROR - 2019-05-22 06:38:17 --> Severity: Notice --> Undefined index: depositStatusGroup /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/CronDaily.php 161
ERROR - 2019-05-22 06:38:17 --> Severity: Notice --> Undefined index: depositStatusGroup /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/CronDaily.php 161
ERROR - 2019-05-22 06:38:17 --> Severity: Notice --> Undefined index: depositStatusGroup /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/CronDaily.php 161
ERROR - 2019-05-22 06:38:17 --> Severity: Notice --> Undefined index: depositStatusGroup /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/CronDaily.php 161
ERROR - 2019-05-22 06:38:17 --> Severity: Notice --> Undefined index: depositStatusGroup /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/CronDaily.php 161
ERROR - 2019-05-22 06:38:17 --> Severity: Notice --> Undefined index: depositStatusGroup /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/CronDaily.php 161
ERROR - 2019-05-22 06:38:17 --> Severity: Notice --> Undefined index: depositStatusGroup /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/CronDaily.php 161
ERROR - 2019-05-22 06:38:18 --> Severity: Notice --> Undefined index: depositStatusGroup /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/CronDaily.php 161
ERROR - 2019-05-22 06:38:18 --> Severity: Notice --> Undefined index: depositStatusGroup /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/CronDaily.php 161
ERROR - 2019-05-22 06:38:18 --> Severity: Notice --> Undefined index: depositStatusGroup /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/CronDaily.php 161
ERROR - 2019-05-22 06:38:18 --> Severity: Notice --> Undefined index: depositStatusGroup /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/CronDaily.php 161
ERROR - 2019-05-22 06:38:18 --> Severity: Notice --> Undefined index: depositStatusGroup /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/CronDaily.php 161
ERROR - 2019-05-22 06:38:18 --> Severity: Notice --> Undefined index: depositStatusGroup /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/CronDaily.php 161
ERROR - 2019-05-22 06:38:18 --> Severity: Notice --> Undefined index: depositStatusGroup /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/CronDaily.php 161
ERROR - 2019-05-22 06:38:18 --> Severity: Notice --> Undefined index: depositStatusGroup /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/CronDaily.php 161
ERROR - 2019-05-22 06:38:18 --> Severity: Notice --> Undefined index: depositStatusGroup /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/CronDaily.php 161
ERROR - 2019-05-22 06:38:18 --> Severity: Notice --> Undefined index: depositStatusGroup /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/CronDaily.php 161
ERROR - 2019-05-22 06:38:18 --> Severity: Notice --> Undefined index: depositStatusGroup /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/CronDaily.php 161
ERROR - 2019-05-22 06:38:18 --> Severity: Notice --> Undefined index: depositStatusGroup /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/CronDaily.php 161
ERROR - 2019-05-22 06:38:18 --> Severity: Notice --> Undefined index: depositStatusGroup /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/CronDaily.php 161
ERROR - 2019-05-22 06:38:18 --> Severity: Notice --> Undefined index: depositStatusGroup /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/CronDaily.php 161
ERROR - 2019-05-22 06:38:18 --> Severity: Notice --> Undefined index: depositStatusGroup /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/CronDaily.php 161
ERROR - 2019-05-22 06:38:18 --> Severity: Notice --> Undefined index: depositStatusGroup /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/CronDaily.php 161
ERROR - 2019-05-22 06:38:18 --> Severity: Notice --> Undefined index: depositStatusGroup /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/CronDaily.php 161
ERROR - 2019-05-22 06:38:18 --> Severity: Notice --> Undefined index: depositStatusGroup /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/CronDaily.php 161
ERROR - 2019-05-22 06:38:18 --> Severity: Notice --> Undefined index: depositStatusGroup /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/CronDaily.php 161
ERROR - 2019-05-22 06:38:18 --> Severity: Notice --> Undefined index: depositStatusGroup /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/CronDaily.php 161
ERROR - 2019-05-22 06:38:18 --> Severity: Notice --> Undefined index: depositStatusGroup /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/CronDaily.php 161
ERROR - 2019-05-22 06:38:18 --> Severity: Notice --> Undefined index: depositStatusGroup /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/CronDaily.php 161
ERROR - 2019-05-22 06:38:18 --> Severity: Notice --> Undefined index: depositStatusGroup /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/CronDaily.php 161
ERROR - 2019-05-22 06:38:18 --> Severity: Notice --> Undefined index: depositStatusGroup /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/CronDaily.php 161
ERROR - 2019-05-22 06:38:18 --> Severity: Notice --> Undefined index: depositStatusGroup /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/CronDaily.php 161
ERROR - 2019-05-22 06:38:18 --> Severity: Notice --> Undefined index: depositStatusGroup /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/CronDaily.php 161
ERROR - 2019-05-22 06:38:18 --> Severity: Notice --> Undefined index: depositStatusGroup /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/CronDaily.php 161
ERROR - 2019-05-22 06:38:18 --> Severity: Notice --> Undefined index: depositStatusGroup /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/CronDaily.php 161
ERROR - 2019-05-22 06:38:18 --> Severity: Notice --> Undefined index: depositStatusGroup /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/CronDaily.php 161
ERROR - 2019-05-22 06:38:18 --> Severity: Notice --> Undefined index: depositStatusGroup /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/CronDaily.php 161
ERROR - 2019-05-22 06:38:18 --> Severity: Notice --> Undefined index: depositStatusGroup /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/CronDaily.php 161
ERROR - 2019-05-22 06:38:18 --> Severity: Notice --> Undefined index: depositStatusGroup /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/CronDaily.php 161
ERROR - 2019-05-22 06:38:18 --> Severity: Notice --> Undefined index: depositStatusGroup /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/CronDaily.php 161
ERROR - 2019-05-22 06:38:18 --> Severity: Notice --> Undefined index: depositStatusGroup /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/CronDaily.php 161
ERROR - 2019-05-22 06:38:18 --> Severity: Notice --> Undefined index: depositStatusGroup /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/CronDaily.php 161
ERROR - 2019-05-22 06:38:18 --> Severity: Notice --> Undefined index: depositStatusGroup /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/CronDaily.php 161
ERROR - 2019-05-22 06:38:18 --> Severity: Notice --> Undefined index: depositStatusGroup /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/CronDaily.php 161
ERROR - 2019-05-22 06:38:18 --> Severity: Notice --> Undefined index: depositStatusGroup /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/CronDaily.php 161
ERROR - 2019-05-22 06:38:18 --> Severity: Notice --> Undefined index: depositStatusGroup /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/CronDaily.php 161
ERROR - 2019-05-22 06:38:18 --> Severity: Notice --> Undefined index: depositStatusGroup /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/CronDaily.php 161
