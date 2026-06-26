<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

ERROR - 2020-01-23 03:38:24 --> Query error: Unknown column 'b.id' in 'order clause' - Invalid query: select * from call_request_black_list  
            where 1=1   
            
             order by b.id desc limit 0, 10
ERROR - 2020-01-23 03:41:41 --> Query error: Unknown column 'b.phone' in 'where clause' - Invalid query: select * from call_request_black_list  
            where 1=1   
             and b.phone = '01044946976' 
             order by id desc limit 0, 10
