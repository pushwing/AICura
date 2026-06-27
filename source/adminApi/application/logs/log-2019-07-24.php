<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

ERROR - 2019-07-24 00:51:24 --> Severity: Notice --> Undefined property: MartinTest::$common_m /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/MartinTest.php 31
ERROR - 2019-07-24 00:51:24 --> 
        Exception of type \'Error\' occurred with Message: Call to a member function getS3Info() on null in File /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/MartinTest.php at Line 31
        
 Backtrace 
.#0 /Users/blumine/works/goodoc_v2/event/adminApi/system/core/CodeIgniter.php(532): MartinTest->index()
#1 /Users/blumine/works/goodoc_v2/event/adminApi/index.php(341): require_once('/Users/blumine/...')
#2 {main}
    
ERROR - 2019-07-24 00:51:25 --> Severity: error --> Exception: Call to a member function getS3Info() on null /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/MartinTest.php 31
ERROR - 2019-07-24 00:51:36 --> 
        Exception of type \'Aws\S3\Exception\PermanentRedirectException\' occurred with Message: Encountered a permanent redirect while requesting https://external-ads.s3.ap-northeast-2.amazonaws.com/https%3A//external-ads.s3-ap-northeast-1.amazonaws.com/event/event_view_count.csv.gz. Are you sure you are using the correct region for this bucket? in File /Users/blumine/works/goodoc_v2/event/adminApi/vendor/aws/aws-sdk-php/src/S3/PermanentRedirectMiddleware.php at Line 49
        
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
#14 /Users/blumine/works/goodoc_v2/event/adminApi/application/models/Common_m.php(736): Aws\AwsClient->__call('getObject', Array)
#15 /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/MartinTest.php(32): Common_m->getS3Info()
#16 /Users/blumine/works/goodoc_v2/event/adminApi/system/core/CodeIgniter.php(532): MartinTest->index()
#17 /Users/blumine/works/goodoc_v2/event/adminApi/index.php(341): require_once('/Users/blumine/...')
#18 {main}
    
ERROR - 2019-07-24 00:51:36 --> Severity: error --> Exception: Encountered a permanent redirect while requesting https://external-ads.s3.ap-northeast-2.amazonaws.com/https%3A//external-ads.s3-ap-northeast-1.amazonaws.com/event/event_view_count.csv.gz. Are you sure you are using the correct region for this bucket? /Users/blumine/works/goodoc_v2/event/adminApi/vendor/aws/aws-sdk-php/src/S3/PermanentRedirectMiddleware.php 49
ERROR - 2019-07-24 00:52:54 --> 
        Exception of type \'Aws\S3\Exception\PermanentRedirectException\' occurred with Message: Encountered a permanent redirect while requesting https://external-ads.s3.ap-northeast-2.amazonaws.com/event/event_view_count.csv.gz. Are you sure you are using the correct region for this bucket? in File /Users/blumine/works/goodoc_v2/event/adminApi/vendor/aws/aws-sdk-php/src/S3/PermanentRedirectMiddleware.php at Line 49
        
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
#14 /Users/blumine/works/goodoc_v2/event/adminApi/application/models/Common_m.php(736): Aws\AwsClient->__call('getObject', Array)
#15 /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/MartinTest.php(32): Common_m->getS3Info()
#16 /Users/blumine/works/goodoc_v2/event/adminApi/system/core/CodeIgniter.php(532): MartinTest->index()
#17 /Users/blumine/works/goodoc_v2/event/adminApi/index.php(341): require_once('/Users/blumine/...')
#18 {main}
    
ERROR - 2019-07-24 00:52:55 --> Severity: error --> Exception: Encountered a permanent redirect while requesting https://external-ads.s3.ap-northeast-2.amazonaws.com/event/event_view_count.csv.gz. Are you sure you are using the correct region for this bucket? /Users/blumine/works/goodoc_v2/event/adminApi/vendor/aws/aws-sdk-php/src/S3/PermanentRedirectMiddleware.php 49
ERROR - 2019-07-24 01:17:33 --> Severity: Warning --> file_get_contents(): Filename cannot be empty /Users/blumine/works/goodoc_v2/event/adminApi/application/models/Common_m.php 740
ERROR - 2019-07-24 01:17:55 --> Severity: Warning --> file_get_contents(): Filename cannot be empty /Users/blumine/works/goodoc_v2/event/adminApi/application/models/Common_m.php 740
ERROR - 2019-07-24 01:18:13 --> Severity: Warning --> file_get_contents(): Filename cannot be empty /Users/blumine/works/goodoc_v2/event/adminApi/application/models/Common_m.php 740
ERROR - 2019-07-24 01:24:37 --> 
        Exception of type \'Aws\S3\Exception\S3Exception\' occurred with Message: Error executing "GetObject" on "https://external-ads.s3.ap-northeast-1.amazonaws.com/event/event_view_count.csv.gz"; AWS HTTP error: Directory /Users/blumine/works/goodoc_v2/event/adminApi/uploads/event_view does not exist for sink value of /Users/blumine/works/goodoc_v2/event/adminApi/uploads/event_view/event_view_count.csv.gz in File /Users/blumine/works/goodoc_v2/event/adminApi/vendor/aws/aws-sdk-php/src/WrappedHttpHandler.php at Line 191
        
 Backtrace 
.#0 /Users/blumine/works/goodoc_v2/event/adminApi/vendor/aws/aws-sdk-php/src/WrappedHttpHandler.php(100): Aws\WrappedHttpHandler->parseError(Array, Object(GuzzleHttp\Psr7\Request), Object(Aws\Command), Array)
#1 /Users/blumine/works/goodoc_v2/event/adminApi/vendor/guzzlehttp/promises/src/Promise.php(203): Aws\WrappedHttpHandler->Aws\{closure}(Array)
#2 /Users/blumine/works/goodoc_v2/event/adminApi/vendor/guzzlehttp/promises/src/Promise.php(174): GuzzleHttp\Promise\Promise::callHandler(2, Array, Array)
#3 /Users/blumine/works/goodoc_v2/event/adminApi/vendor/guzzlehttp/promises/src/RejectedPromise.php(40): GuzzleHttp\Promise\Promise::GuzzleHttp\Promise\{closure}(Array)
#4 /Users/blumine/works/goodoc_v2/event/adminApi/vendor/guzzlehttp/promises/src/TaskQueue.php(47): GuzzleHttp\Promise\RejectedPromise::GuzzleHttp\Promise\{closure}()
#5 /Users/blumine/works/goodoc_v2/event/adminApi/vendor/guzzlehttp/promises/src/Promise.php(246): GuzzleHttp\Promise\TaskQueue->run(true)
#6 /Users/blumine/works/goodoc_v2/event/adminApi/vendor/guzzlehttp/promises/src/Promise.php(223): GuzzleHttp\Promise\Promise->invokeWaitFn()
#7 /Users/blumine/works/goodoc_v2/event/adminApi/vendor/guzzlehttp/promises/src/Promise.php(267): GuzzleHttp\Promise\Promise->waitIfPending()
#8 /Users/blumine/works/goodoc_v2/event/adminApi/vendor/guzzlehttp/promises/src/Promise.php(225): GuzzleHttp\Promise\Promise->invokeWaitList()
#9 /Users/blumine/works/goodoc_v2/event/adminApi/vendor/guzzlehttp/promises/src/Promise.php(62): GuzzleHttp\Promise\Promise->waitIfPending()
#10 /Users/blumine/works/goodoc_v2/event/adminApi/vendor/aws/aws-sdk-php/src/AwsClientTrait.php(58): GuzzleHttp\Promise\Promise->wait()
#11 /Users/blumine/works/goodoc_v2/event/adminApi/vendor/aws/aws-sdk-php/src/AwsClientTrait.php(77): Aws\AwsClient->execute(Object(Aws\Command))
#12 /Users/blumine/works/goodoc_v2/event/adminApi/application/models/Common_m.php(738): Aws\AwsClient->__call('getObject', Array)
#13 /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/MartinTest.php(32): Common_m->getS3Info()
#14 /Users/blumine/works/goodoc_v2/event/adminApi/system/core/CodeIgniter.php(532): MartinTest->index()
#15 /Users/blumine/works/goodoc_v2/event/adminApi/index.php(341): require_once('/Users/blumine/...')
#16 {main}
    
ERROR - 2019-07-24 01:24:37 --> Severity: error --> Exception: Error executing "GetObject" on "https://external-ads.s3.ap-northeast-1.amazonaws.com/event/event_view_count.csv.gz"; AWS HTTP error: Directory /Users/blumine/works/goodoc_v2/event/adminApi/uploads/event_view does not exist for sink value of /Users/blumine/works/goodoc_v2/event/adminApi/uploads/event_view/event_view_count.csv.gz /Users/blumine/works/goodoc_v2/event/adminApi/vendor/aws/aws-sdk-php/src/WrappedHttpHandler.php 191
ERROR - 2019-07-24 03:01:13 --> Severity: Warning --> array_map(): Argument #2 should be an array /Users/blumine/works/goodoc_v2/event/adminApi/application/models/Common_m.php 750
