<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

ERROR - 2020-01-07 00:24:01 --> 404 Page Not Found: MartinTest/gogo22
ERROR - 2020-01-07 00:25:22 --> 404 Page Not Found: MartinTest/test22
ERROR - 2020-01-07 00:29:27 --> Severity: Notice --> iconv(): Detected an illegal character in input string /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/MartinTest.php 1424
ERROR - 2020-01-07 00:30:18 --> Severity: Notice --> iconv(): Detected an illegal character in input string /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/MartinTest.php 1424
ERROR - 2020-01-07 00:30:20 --> Severity: Notice --> iconv(): Detected an illegal character in input string /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/MartinTest.php 1424
ERROR - 2020-01-07 00:30:22 --> Severity: Notice --> iconv(): Detected an illegal character in input string /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/MartinTest.php 1424
ERROR - 2020-01-07 00:30:35 --> Severity: Notice --> iconv(): Detected an illegal character in input string /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/MartinTest.php 1424
ERROR - 2020-01-07 00:30:35 --> 404 Page Not Found: Faviconico/index
ERROR - 2020-01-07 00:31:57 --> Severity: Notice --> iconv(): Detected an illegal character in input string /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/MartinTest.php 1424
ERROR - 2020-01-07 03:54:42 --> Query error: FUNCTION lactea.urldecode3 does not exist - Invalid query: 
                select *, (contentScore + fileScore - sigm) as orderByMe 
                    from (
                         select *,
                         if(fileCount <= 2, (fileCount*5), 12) as fileScore,
                         case
                         when uText >= 1 and uText < 6
                         then 1
                         when uText >=6 and uText < 11
                         then 2
                         when uText >= 10 and uText < 16
                         then 3
                         when uText >= 16 and uText < 21
                         then 4
                         when uText >= 21
                         then 5
                         else 0
                         end as contentScore,                         
                         sigmoid2((unix_timestamp()-unix_timestamp(regDate))/16000, 0.0079315)*10 as sigm
                         from (
                         select b.id as boardId, b.contents, b.regDate, ads.category, 
                         (select count(*) from board_files where boardId=b.id) as fileCount,
                         CHAR_LENGTH(urldecode3(contents)) as uText
                         from board b
                         join ads on b.targetId=ads.id
                         where b.isDelete = 0 and b.type = 1 and ads.category != 57
                         group by b.id 
                         ) aa
                    ) bb  
                order by orderByMe desc, boardId desc
                
