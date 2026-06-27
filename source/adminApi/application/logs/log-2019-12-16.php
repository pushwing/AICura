<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

ERROR - 2019-12-16 02:15:25 --> Severity: Compile Error --> Cannot redeclare MartinTest::index2() /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/MartinTest.php 745
ERROR - 2019-12-16 02:15:48 --> 
        Exception of type \'Aws\S3\Exception\S3Exception\' occurred with Message: Error executing "GetObject" on "https://event-file-dev.s3.ap-northeast-2.amazonaws.com//history/10135/1576218354/index.html"; AWS HTTP error: Client error: `GET https://event-file-dev.s3.ap-northeast-2.amazonaws.com//history/10135/1576218354/index.html` resulted in a `404 Not Found` response:
<?xml version="1.0" encoding="UTF-8"?>
<Error><Code>NoSuchKey</Code><Message>The specified key does not exist.</Message> (truncated...)
 NoSuchKey (client): The specified key does not exist. - <?xml version="1.0" encoding="UTF-8"?>
<Error><Code>NoSuchKey</Code><Message>The specified key does not exist.</Message><Key>/history/10135/1576218354/index.html</Key><RequestId>056470959F455736</RequestId><HostId>mef7RnlBlu72680iXZQ2rbIFQ2WdW0mJmj1A11wUl2V/AFNz9dVdfJHnHr/c8sqqLnlgbkqKwoo=</HostId></Error> in File /Users/blumine/works/goodoc_v2/event/adminApi/vendor/aws/aws-sdk-php/src/WrappedHttpHandler.php at Line 191
        
 Backtrace 
.#0 /Users/blumine/works/goodoc_v2/event/adminApi/vendor/aws/aws-sdk-php/src/WrappedHttpHandler.php(97): Aws\WrappedHttpHandler->parseError(Array, Object(GuzzleHttp\Psr7\Request), Object(Aws\Command), Array)
#1 /Users/blumine/works/goodoc_v2/event/adminApi/vendor/guzzlehttp/promises/src/Promise.php(203): Aws\WrappedHttpHandler->Aws\{closure}(Array)
#2 /Users/blumine/works/goodoc_v2/event/adminApi/vendor/guzzlehttp/promises/src/Promise.php(174): GuzzleHttp\Promise\Promise::callHandler(2, Array, Array)
#3 /Users/blumine/works/goodoc_v2/event/adminApi/vendor/guzzlehttp/promises/src/RejectedPromise.php(40): GuzzleHttp\Promise\Promise::GuzzleHttp\Promise\{closure}(Array)
#4 /Users/blumine/works/goodoc_v2/event/adminApi/vendor/guzzlehttp/promises/src/TaskQueue.php(47): GuzzleHttp\Promise\RejectedPromise::GuzzleHttp\Promise\{closure}()
#5 /Users/blumine/works/goodoc_v2/event/adminApi/vendor/guzzlehttp/guzzle/src/Handler/CurlMultiHandler.php(98): GuzzleHttp\Promise\TaskQueue->run()
#6 /Users/blumine/works/goodoc_v2/event/adminApi/vendor/guzzlehttp/guzzle/src/Handler/CurlMultiHandler.php(125): GuzzleHttp\Handler\CurlMultiHandler->tick()
#7 /Users/blumine/works/goodoc_v2/event/adminApi/vendor/guzzlehttp/promises/src/Promise.php(246): GuzzleHttp\Handler\CurlMultiHandler->execute(true)
#8 /Users/blumine/works/goodoc_v2/event/adminApi/vendor/guzzlehttp/promises/src/Promise.php(223): GuzzleHttp\Promise\Promise->invokeWaitFn()
#9 /Users/blumine/works/goodoc_v2/event/adminApi/vendor/guzzlehttp/promises/src/Promise.php(267): GuzzleHttp\Promise\Promise->waitIfPending()
#10 /Users/blumine/works/goodoc_v2/event/adminApi/vendor/guzzlehttp/promises/src/Promise.php(225): GuzzleHttp\Promise\Promise->invokeWaitList()
#11 /Users/blumine/works/goodoc_v2/event/adminApi/vendor/guzzlehttp/promises/src/Promise.php(267): GuzzleHttp\Promise\Promise->waitIfPending()
#12 /Users/blumine/works/goodoc_v2/event/adminApi/vendor/guzzlehttp/promises/src/Promise.php(225): GuzzleHttp\Promise\Promise->invokeWaitList()
#13 /Users/blumine/works/goodoc_v2/event/adminApi/vendor/guzzlehttp/promises/src/Promise.php(62): GuzzleHttp\Promise\Promise->waitIfPending()
#14 /Users/blumine/works/goodoc_v2/event/adminApi/vendor/aws/aws-sdk-php/src/AwsClientTrait.php(58): GuzzleHttp\Promise\Promise->wait()
#15 /Users/blumine/works/goodoc_v2/event/adminApi/vendor/aws/aws-sdk-php/src/AwsClientTrait.php(77): Aws\AwsClient->execute(Object(Aws\Command))
#16 /Users/blumine/works/goodoc_v2/event/adminApi/application/models/Common_m.php(1029): Aws\AwsClient->__call('getObject', Array)
#17 /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/MartinTest.php(64): Common_m->getPrivateS3Object('event-file-dev', '/history/10135/...')
#18 /Users/blumine/works/goodoc_v2/event/adminApi/system/core/CodeIgniter.php(532): MartinTest->getHistoryFile()
#19 /Users/blumine/works/goodoc_v2/event/adminApi/index.php(341): require_once('/Users/blumine/...')
#20 {main}
    
ERROR - 2019-12-16 02:15:48 --> Severity: error --> Exception: Error executing "GetObject" on "https://event-file-dev.s3.ap-northeast-2.amazonaws.com//history/10135/1576218354/index.html"; AWS HTTP error: Client error: `GET https://event-file-dev.s3.ap-northeast-2.amazonaws.com//history/10135/1576218354/index.html` resulted in a `404 Not Found` response:
<?xml version="1.0" encoding="UTF-8"?>
<Error><Code>NoSuchKey</Code><Message>The specified key does not exist.</Message> (truncated...)
 NoSuchKey (client): The specified key does not exist. - <?xml version="1.0" encoding="UTF-8"?>
<Error><Code>NoSuchKey</Code><Message>The specified key does not exist.</Message><Key>/history/10135/1576218354/index.html</Key><RequestId>056470959F455736</RequestId><HostId>mef7RnlBlu72680iXZQ2rbIFQ2WdW0mJmj1A11wUl2V/AFNz9dVdfJHnHr/c8sqqLnlgbkqKwoo=</HostId></Error> /Users/blumine/works/goodoc_v2/event/adminApi/vendor/aws/aws-sdk-php/src/WrappedHttpHandler.php 191
ERROR - 2019-12-16 02:22:49 --> Severity: Warning --> file_get_contents(https://event-file-dev.s3.ap-northeast-2.amazonaws.com/history/10135/1576218354/index.html): failed to open stream: HTTP request failed! HTTP/1.1 403 Forbidden
 /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/MartinTest.php 68
ERROR - 2019-12-16 02:43:43 --> 
        Exception of type \'ArgumentCountError\' occurred with Message: Too few arguments to function Aws\S3\S3Client::getObjectUrl(), 1 passed in /Users/blumine/works/goodoc_v2/event/adminApi/application/models/Common_m.php on line 1028 and exactly 2 expected in File /Users/blumine/works/goodoc_v2/event/adminApi/vendor/aws/aws-sdk-php/src/S3/S3Client.php at Line 360
        
 Backtrace 
.#0 /Users/blumine/works/goodoc_v2/event/adminApi/application/models/Common_m.php(1028): Aws\S3\S3Client->getObjectUrl(Array)
#1 /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/MartinTest.php(65): Common_m->getPrivateS3Object('event-file-dev', 'history/10135/1...')
#2 /Users/blumine/works/goodoc_v2/event/adminApi/system/core/CodeIgniter.php(532): MartinTest->getHistoryFile()
#3 /Users/blumine/works/goodoc_v2/event/adminApi/index.php(341): require_once('/Users/blumine/...')
#4 {main}
    
ERROR - 2019-12-16 02:43:43 --> Severity: error --> Exception: Too few arguments to function Aws\S3\S3Client::getObjectUrl(), 1 passed in /Users/blumine/works/goodoc_v2/event/adminApi/application/models/Common_m.php on line 1028 and exactly 2 expected /Users/blumine/works/goodoc_v2/event/adminApi/vendor/aws/aws-sdk-php/src/S3/S3Client.php 360
ERROR - 2019-12-16 02:47:31 --> 
        Exception of type \'Error\' occurred with Message: Call to undefined function redirect() in File /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/MartinTest.php at Line 67
        
 Backtrace 
.#0 /Users/blumine/works/goodoc_v2/event/adminApi/system/core/CodeIgniter.php(532): MartinTest->getHistoryFile()
#1 /Users/blumine/works/goodoc_v2/event/adminApi/index.php(341): require_once('/Users/blumine/...')
#2 {main}
    
ERROR - 2019-12-16 02:47:31 --> Severity: error --> Exception: Call to undefined function redirect() /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/MartinTest.php 67
ERROR - 2019-12-16 02:52:13 --> 
        Exception of type \'Error\' occurred with Message: Call to undefined method Aws\Command::createPresignedUrl() in File /Users/blumine/works/goodoc_v2/event/adminApi/application/models/Common_m.php at Line 1036
        
 Backtrace 
.#0 /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/MartinTest.php(65): Common_m->getPrivateS3Object('event-file-dev', 'history/10135/1...')
#1 /Users/blumine/works/goodoc_v2/event/adminApi/system/core/CodeIgniter.php(532): MartinTest->getHistoryFile()
#2 /Users/blumine/works/goodoc_v2/event/adminApi/index.php(341): require_once('/Users/blumine/...')
#3 {main}
    
ERROR - 2019-12-16 02:52:13 --> Severity: error --> Exception: Call to undefined method Aws\Command::createPresignedUrl() /Users/blumine/works/goodoc_v2/event/adminApi/application/models/Common_m.php 1036
ERROR - 2019-12-16 02:57:28 --> 
        Exception of type \'Error\' occurred with Message: Call to undefined method Aws\Command::createPresignedRequest() in File /Users/blumine/works/goodoc_v2/event/adminApi/application/models/Common_m.php at Line 1036
        
 Backtrace 
.#0 /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/MartinTest.php(65): Common_m->getPrivateS3Object('event-file-dev', 'history/10135/1...')
#1 /Users/blumine/works/goodoc_v2/event/adminApi/system/core/CodeIgniter.php(532): MartinTest->getHistoryFile()
#2 /Users/blumine/works/goodoc_v2/event/adminApi/index.php(341): require_once('/Users/blumine/...')
#3 {main}
    
ERROR - 2019-12-16 02:57:28 --> Severity: error --> Exception: Call to undefined method Aws\Command::createPresignedRequest() /Users/blumine/works/goodoc_v2/event/adminApi/application/models/Common_m.php 1036
ERROR - 2019-12-16 02:59:25 --> Severity: 4096 --> Object of class GuzzleHttp\Psr7\Request could not be converted to string /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/MartinTest.php 67
ERROR - 2019-12-16 03:00:57 --> 
        Exception of type \'ArgumentCountError\' occurred with Message: Too few arguments to function Aws\S3\S3Client::getObjectUrl(), 1 passed in /Users/blumine/works/goodoc_v2/event/adminApi/application/models/Common_m.php on line 1045 and exactly 2 expected in File /Users/blumine/works/goodoc_v2/event/adminApi/vendor/aws/aws-sdk-php/src/S3/S3Client.php at Line 360
        
 Backtrace 
.#0 /Users/blumine/works/goodoc_v2/event/adminApi/application/models/Common_m.php(1045): Aws\S3\S3Client->getObjectUrl(Array)
#1 /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/MartinTest.php(65): Common_m->getPrivateS3Object('event-file-dev', 'history/10135/1...')
#2 /Users/blumine/works/goodoc_v2/event/adminApi/system/core/CodeIgniter.php(532): MartinTest->getHistoryFile()
#3 /Users/blumine/works/goodoc_v2/event/adminApi/index.php(341): require_once('/Users/blumine/...')
#4 {main}
    
ERROR - 2019-12-16 03:00:57 --> Severity: error --> Exception: Too few arguments to function Aws\S3\S3Client::getObjectUrl(), 1 passed in /Users/blumine/works/goodoc_v2/event/adminApi/application/models/Common_m.php on line 1045 and exactly 2 expected /Users/blumine/works/goodoc_v2/event/adminApi/vendor/aws/aws-sdk-php/src/S3/S3Client.php 360
ERROR - 2019-12-16 03:28:11 --> Severity: 4096 --> Object of class GuzzleHttp\Psr7\Request could not be converted to string /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/MartinTest.php 67
ERROR - 2019-12-16 03:31:49 --> Severity: Notice --> Trying to get property 'get' of non-object /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/MartinTest.php 67
ERROR - 2019-12-16 04:28:16 --> Severity: Warning --> Illegal string offset '@metadata' /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/MartinTest.php 70
ERROR - 2019-12-16 04:28:16 --> Severity: Warning --> Illegal string offset 'effectiveUri' /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/MartinTest.php 70
ERROR - 2019-12-16 04:28:16 --> Severity: Warning --> file_get_contents(h): failed to open stream: No such file or directory /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/MartinTest.php 70
ERROR - 2019-12-16 04:28:35 --> Severity: Warning --> file_get_contents(https://event-file-dev.s3.ap-northeast-2.amazonaws.com/history/10135/1576218354/index.html?response-content-type=application%2Foctet-stream&amp;response-content-disposition=attachment%3B%20filename%3D%22history%2F10135%2F1576218354%2Findex.html&amp;X-Amz-Content-Sha256=UNSIGNED-PAYLOAD&amp;X-Amz-Algorithm=AWS4-HMAC-SHA256&amp;X-Amz-Credential=AKIAI6FNACJZZKT4IZLQ%2F20191216%2Fap-northeast-2%2Fs3%2Faws4_request&amp;X-Amz-Date=20191216T042833Z&amp;X-Amz-SignedHeaders=host&amp;X-Amz-Expires=86400&amp;X-Amz-Signature=8a37055cfe394c5f11fa0e816d9ed1cd43bc834780d518a7e6aa046c0c1bb890): failed to open stream: HTTP request failed! HTTP/1.1 400 Bad Request
 /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/MartinTest.php 69
ERROR - 2019-12-16 05:33:14 --> 404 Page Not Found: /index
