<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

ERROR - 2019-05-20 00:57:04 --> Severity: Warning --> mysqli::real_connect(): (HY000/2002): Operation timed out /Users/blumine/works/goodoc_v2/event/adminApi/system/database/drivers/mysqli/mysqli_driver.php 201
ERROR - 2019-05-20 00:57:04 --> Unable to connect to the database
ERROR - 2019-05-20 01:58:47 --> 
        Exception of type \'Error\' occurred with Message: Call to undefined method CI_DB_mysqli_result::result_arry() in File /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/CronDaily.php at Line 29
        
 Backtrace 
.#0 /Users/blumine/works/goodoc_v2/event/adminApi/system/core/CodeIgniter.php(532): CronDaily->readyPrice()
#1 /Users/blumine/works/goodoc_v2/event/adminApi/index.php(333): require_once('/Users/blumine/...')
#2 {main}
    
ERROR - 2019-05-20 01:58:48 --> Severity: error --> Exception: Call to undefined method CI_DB_mysqli_result::result_arry() /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/CronDaily.php 29
