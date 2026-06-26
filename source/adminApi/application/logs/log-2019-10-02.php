<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

ERROR - 2019-10-02 08:16:12 --> 
        Exception of type \'Aws\S3\Exception\PermanentRedirectException\' occurred with Message: Encountered a permanent redirect while requesting https://event-ranking.s3.ap-northeast-2.amazonaws.com/ranking/daily.csv. Are you sure you are using the correct region for this bucket? in File /Users/blumine/works/goodoc_v2/event/adminApi/vendor/aws/aws-sdk-php/src/S3/PermanentRedirectMiddleware.php at Line 49
        
 Backtrace 
.#0 /Users/blumine/works/goodoc_v2/event/adminApi/vendor/guzzlehttp/promises/src/Promise.php(203): Aws\S3\PermanentRedirectMiddleware->Aws\S3\{closure}(Object(Aws\Result))
#1 /Users/blumine/works/goodoc_v2/event/adminApi/vendor/guzzlehttp/promises/src/Promise.php(156): GuzzleHttp\Promise\Promise::callHandler(1, Object(Aws\Result), Array)
#2 /Users/blumine/works/goodoc_v2/event/adminApi/vendor/guzzlehttp/promises/src/TaskQueue.php(47): GuzzleHttp\Promise\Promise::GuzzleHttp\Promise\{closure}()
#3 /Users/blumine/works/goodoc_v2/event/adminApi/vendor/guzzlehttp/guzzle/src/Handler/CurlMultiHandler.php(98): GuzzleHttp\Promise\TaskQueue->run()
#4 /Users/blumine/works/goodoc_v2/event/adminApi/vendor/guzzlehttp/guzzle/src/Handler/CurlMultiHandler.php(125): GuzzleHttp\Handler\CurlMultiHandler->tick()
#5 /Users/blumine/works/goodoc_v2/event/adminApi/vendor/guzzlehttp/promises/src/Promise.php(246): GuzzleHttp\Handler\CurlMultiHandler->execute(true)
#6 /Users/blumine/works/goodoc_v2/event/adminApi/vendor/guzzlehttp/promises/src/Promise.php(223): GuzzleHttp\Promise\Promise->invokeWaitFn()
#7 /Users/blumine/works/goodoc_v2/event/adminApi/vendor/guzzlehttp/promises/src/Promise.php(267): GuzzleHttp\Promise\Promise->waitIfPending()
#8 /Users/blumine/works/goodoc_v2/event/adminApi/vendor/guzzlehttp/promises/src/Promise.php(225): GuzzleHttp\Promise\Promise->invokeWaitList()
#9 /Users/blumine/works/goodoc_v2/event/adminApi/vendor/guzzlehttp/promises/src/Promise.php(267): GuzzleHttp\Promise\Promise->waitIfPending()
#10 /Users/blumine/works/goodoc_v2/event/adminApi/vendor/guzzlehttp/promises/src/Promise.php(225): GuzzleHttp\Promise\Promise->invokeWaitList()
#11 /Users/blumine/works/goodoc_v2/event/adminApi/vendor/guzzlehttp/promises/src/Promise.php(62): GuzzleHttp\Promise\Promise->waitIfPending()
#12 /Users/blumine/works/goodoc_v2/event/adminApi/vendor/aws/aws-sdk-php/src/AwsClientTrait.php(58): GuzzleHttp\Promise\Promise->wait()
#13 /Users/blumine/works/goodoc_v2/event/adminApi/vendor/aws/aws-sdk-php/src/AwsClientTrait.php(77): Aws\AwsClient->execute(Object(Aws\Command))
#14 /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/CronDaily.php(1093): Aws\AwsClient->__call('getObject', Array)
#15 /Users/blumine/works/goodoc_v2/event/adminApi/system/core/CodeIgniter.php(532): CronDaily->getS3Info()
#16 /Users/blumine/works/goodoc_v2/event/adminApi/index.php(341): require_once('/Users/blumine/...')
#17 {main}
    
ERROR - 2019-10-02 08:16:12 --> Severity: error --> Exception: Encountered a permanent redirect while requesting https://event-ranking.s3.ap-northeast-2.amazonaws.com/ranking/daily.csv. Are you sure you are using the correct region for this bucket? /Users/blumine/works/goodoc_v2/event/adminApi/vendor/aws/aws-sdk-php/src/S3/PermanentRedirectMiddleware.php 49
ERROR - 2019-10-02 08:44:12 --> Severity: Notice --> Undefined offset: 1 /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/CronDaily.php 1109
ERROR - 2019-10-02 08:44:12 --> Severity: Notice --> Undefined offset: 2 /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/CronDaily.php 1110
ERROR - 2019-10-02 08:44:12 --> Severity: Notice --> Undefined offset: 3 /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/CronDaily.php 1111
ERROR - 2019-10-02 08:46:07 --> Severity: Notice --> Undefined offset: 1 /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/CronDaily.php 1109
ERROR - 2019-10-02 08:46:07 --> Severity: Notice --> Undefined offset: 2 /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/CronDaily.php 1110
ERROR - 2019-10-02 08:46:07 --> Severity: Notice --> Undefined offset: 3 /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/CronDaily.php 1111
ERROR - 2019-10-02 08:46:46 --> Severity: Notice --> Undefined offset: 1 /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/CronDaily.php 1109
ERROR - 2019-10-02 08:46:46 --> Severity: Notice --> Undefined offset: 2 /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/CronDaily.php 1110
ERROR - 2019-10-02 08:46:46 --> Severity: Notice --> Undefined offset: 3 /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/CronDaily.php 1111
