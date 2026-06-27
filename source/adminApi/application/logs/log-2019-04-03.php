<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

ERROR - 2019-04-03 00:06:47 --> Query error: FUNCTION lactea.sigmoid2 does not exist - Invalid query: 
            
                select *, (contentScore + fileScore - sigm) as orderByMe
                from (
                          select *, 
                          if(fileCount <= 2, (fileCount*5), 12) as fileScore,
                          if(contents != "", 3, 0) as contentScore,
                          if(rateSum <= 6, 0, if(rateSum < 8.7, 1.1, 1.2)) rateScore,
                          sigmoid2((unix_timestamp()-unix_timestamp(regDate))/16000, 0.0079315)*10 as sigm
                          from (
                          select b.id as boardId, b.contents, b.rateSum, b.regDate,  
                          (select count(*) from board_files where boardId=b.id) as fileCount
                          from board b
                          where b.isDelete = 0
                          group by b.id
                          ) aa
                     ) bb  
                     order by orderByMe desc, boardId desc
        
ERROR - 2019-04-03 00:06:47 --> 404 Page Not Found: Faviconico/index
ERROR - 2019-04-03 00:11:22 --> Severity: Error --> Allowed memory size of 134217728 bytes exhausted (tried to allocate 1052672 bytes) /Users/blumine/works/goodoc_v2/event/adminApi/system/database/DB_driver.php 703
