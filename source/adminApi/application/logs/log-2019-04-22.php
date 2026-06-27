<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

ERROR - 2019-04-22 06:03:47 --> Query error: You have an error in your SQL syntax; check the manual that corresponds to your MySQL server version for the right syntax to use near 'if((select count(*) from ads where contractId='3') = 0, '대기',
              ' at line 2 - Invalid query: 
                select * from (
                    if((select count(*) from ads where contractId='3') = 0, '대기',
                    if((select count(*) from ads where contractId='3' and ads.isLive='Y') > 0, '진행', 
                        if(
                            (select count(*) from ads where contractId='3' and ads.isLive='Y' and modDate > '2019-01-22') = 0 
                            and 
                            (select count(*) from ads where contractId='3' and modDate > '2019-01-22') = (select count(*) from ads where contractId='3' and ads.isLive='N' and modDate > '2019-01-22' 
                                and 
                                (
                                ifnull((select sum(price) from deposit where status in(2,4) and contractId='3' and contractOrderId='1'),0) 
                                - 
                                ifnull((select sum(price) from deposit where status in(3,5,6,7,8) and contractId='3' and contractOrderId='1'),0)
                            ) <= 0
                        )
                            , '휴면', 
                            if(
                                (select count(*) from ads where contractId='3' and ads.isLive='Y') = 0 
                                and 
                                (select count(*) from ads where contractId='3')=(select count(*) from ads where contractId='3' and ads.isLive='N' 
                                    and 
                                    (
                                    ifnull((select sum(price) from deposit where status in(2,4) and contractId='3' and contractOrderId='1'),0)
                                    - 
                                    ifnull((select sum(price) from deposit where  status in(3,5,6,7,8) and contractId='3' and contractOrderId='1'),0)
                                ) > 0
                            )
                                , '중지', '이탈'
                            ) 
                        )	
                    )
                    ) as hStatus,
                )
            
ERROR - 2019-04-22 06:23:31 --> Query error: You have an error in your SQL syntax; check the manual that corresponds to your MySQL server version for the right syntax to use near 'if((select count(*) from ads where contractId='3') = 0, '대기',
              ' at line 3 - Invalid query: 
                select * from (
                select * from (
                    if((select count(*) from ads where contractId='3') = 0, '대기',
                    if((select count(*) from ads where contractId='3' and ads.isLive='Y') > 0, '진행', 
                        if(
                            (select count(*) from ads where contractId='3' and ads.isLive='Y' and modDate > '2019-01-22') = 0 
                            and 
                            (select count(*) from ads where contractId='3' and modDate > '2019-01-22') = (select count(*) from ads where contractId='3' and ads.isLive='N' and modDate > '2019-01-22' 
                                and 
                                (
                                ifnull((select sum(price) from deposit where status in(2,4) and contractId='3' and contractOrderId='1'),0) 
                                - 
                                ifnull((select sum(price) from deposit where status in(3,5,6,7,8) and contractId='3' and contractOrderId='1'),0)
                            ) <= 0
                        )
                            , '휴면', 
                            if(
                                (select count(*) from ads where contractId='3' and ads.isLive='Y') = 0 
                                and 
                                (select count(*) from ads where contractId='3')=(select count(*) from ads where contractId='3' and ads.isLive='N' 
                                    and 
                                    (
                                    ifnull((select sum(price) from deposit where status in(2,4) and contractId='3' and contractOrderId='1'),0)
                                    - 
                                    ifnull((select sum(price) from deposit where  status in(3,5,6,7,8) and contractId='3' and contractOrderId='1'),0)
                                ) > 0
                            )
                                , '중지', '이탈'
                            ) 
                        )	
                    )
                    ) as hStatus
                    )
                )
            
ERROR - 2019-04-22 06:26:16 --> Severity: Warning --> Cannot modify header information - headers already sent by (output started at /Users/blumine/works/goodoc_v2/event/adminApi/application/models/Contract_m.php:624) /Users/blumine/works/goodoc_v2/event/adminApi/system/core/Common.php 570
ERROR - 2019-04-22 08:25:03 --> Query error: You have an error in your SQL syntax; check the manual that corresponds to your MySQL server version for the right syntax to use near ') and contractId='937' and contractOrderId='5600'' at line 1 - Invalid query: select ifnull(sum(price), 0) as usePrice from deposit where status in(3,5,6,7,8,) and contractId='937' and contractOrderId='5600'
ERROR - 2019-04-22 08:25:10 --> Query error: You have an error in your SQL syntax; check the manual that corresponds to your MySQL server version for the right syntax to use near ') and contractId='937' and contractOrderId='5600'' at line 1 - Invalid query: select ifnull(sum(price), 0) as usePrice from deposit where status in(3,5,6,7,8,) and contractId='937' and contractOrderId='5600'
ERROR - 2019-04-22 08:27:15 --> Severity: Notice --> Undefined index: chargPrice /Users/blumine/works/goodoc_v2/event/adminApi/application/models/Contract_m.php 625
ERROR - 2019-04-22 08:27:15 --> Severity: Notice --> Undefined index: chargPrice /Users/blumine/works/goodoc_v2/event/adminApi/application/models/Contract_m.php 625
ERROR - 2019-04-22 08:27:15 --> Severity: Notice --> Undefined index: chargPrice /Users/blumine/works/goodoc_v2/event/adminApi/application/models/Contract_m.php 625
ERROR - 2019-04-22 08:27:15 --> Severity: Notice --> Undefined index: chargPrice /Users/blumine/works/goodoc_v2/event/adminApi/application/models/Contract_m.php 625
ERROR - 2019-04-22 08:27:15 --> Severity: Notice --> Undefined index: chargPrice /Users/blumine/works/goodoc_v2/event/adminApi/application/models/Contract_m.php 625
ERROR - 2019-04-22 08:27:15 --> Severity: Notice --> Undefined index: chargPrice /Users/blumine/works/goodoc_v2/event/adminApi/application/models/Contract_m.php 625
ERROR - 2019-04-22 08:27:15 --> Severity: Notice --> Undefined index: chargPrice /Users/blumine/works/goodoc_v2/event/adminApi/application/models/Contract_m.php 625
ERROR - 2019-04-22 08:27:15 --> Severity: Notice --> Undefined index: chargPrice /Users/blumine/works/goodoc_v2/event/adminApi/application/models/Contract_m.php 625
ERROR - 2019-04-22 08:27:16 --> Severity: Notice --> Undefined index: chargPrice /Users/blumine/works/goodoc_v2/event/adminApi/application/models/Contract_m.php 625
ERROR - 2019-04-22 08:27:16 --> Severity: Notice --> Undefined index: chargPrice /Users/blumine/works/goodoc_v2/event/adminApi/application/models/Contract_m.php 625
ERROR - 2019-04-22 08:37:51 --> Query error: You have an error in your SQL syntax; check the manual that corresponds to your MySQL server version for the right syntax to use near ''937' at line 1 - Invalid query: select ifnull(sum(price), 0) as price6 from deposit where status in(6,7) and contractId='937
ERROR - 2019-04-22 10:21:38 --> Severity: Notice --> Undefined index: adPrice /Users/blumine/works/goodoc_v2/event/adminApi/application/models/Contract_m.php 688
ERROR - 2019-04-22 10:21:38 --> Severity: Notice --> Undefined index: adPrice /Users/blumine/works/goodoc_v2/event/adminApi/application/models/Contract_m.php 688
ERROR - 2019-04-22 10:21:38 --> Severity: Notice --> Undefined index: adPrice /Users/blumine/works/goodoc_v2/event/adminApi/application/models/Contract_m.php 688
ERROR - 2019-04-22 10:21:38 --> Severity: Notice --> Undefined index: adPrice /Users/blumine/works/goodoc_v2/event/adminApi/application/models/Contract_m.php 688
ERROR - 2019-04-22 10:21:38 --> Severity: Notice --> Undefined index: adPrice /Users/blumine/works/goodoc_v2/event/adminApi/application/models/Contract_m.php 688
ERROR - 2019-04-22 10:21:38 --> Severity: Notice --> Undefined index: adPrice /Users/blumine/works/goodoc_v2/event/adminApi/application/models/Contract_m.php 688
ERROR - 2019-04-22 10:21:38 --> Severity: Notice --> Undefined index: adPrice /Users/blumine/works/goodoc_v2/event/adminApi/application/models/Contract_m.php 688
ERROR - 2019-04-22 10:21:38 --> Severity: Notice --> Undefined index: adPrice /Users/blumine/works/goodoc_v2/event/adminApi/application/models/Contract_m.php 688
ERROR - 2019-04-22 10:21:38 --> Severity: Notice --> Undefined index: adPrice /Users/blumine/works/goodoc_v2/event/adminApi/application/models/Contract_m.php 688
ERROR - 2019-04-22 10:21:38 --> Severity: Notice --> Undefined index: adPrice /Users/blumine/works/goodoc_v2/event/adminApi/application/models/Contract_m.php 688
ERROR - 2019-04-22 10:21:38 --> Severity: Notice --> Undefined index: adPrice /Users/blumine/works/goodoc_v2/event/adminApi/application/models/Contract_m.php 688
ERROR - 2019-04-22 10:21:38 --> Severity: Notice --> Undefined index: adPrice /Users/blumine/works/goodoc_v2/event/adminApi/application/models/Contract_m.php 688
ERROR - 2019-04-22 10:21:38 --> Severity: Notice --> Undefined index: adPrice /Users/blumine/works/goodoc_v2/event/adminApi/application/models/Contract_m.php 688
ERROR - 2019-04-22 10:21:38 --> Severity: Notice --> Undefined index: adPrice /Users/blumine/works/goodoc_v2/event/adminApi/application/models/Contract_m.php 688
ERROR - 2019-04-22 10:21:38 --> Severity: Notice --> Undefined index: adPrice /Users/blumine/works/goodoc_v2/event/adminApi/application/models/Contract_m.php 688
ERROR - 2019-04-22 10:21:38 --> Severity: Warning --> Cannot modify header information - headers already sent by (output started at /Users/blumine/works/goodoc_v2/event/adminApi/system/core/Exceptions.php:271) /Users/blumine/works/goodoc_v2/event/adminApi/system/core/Common.php 570
