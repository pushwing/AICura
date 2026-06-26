<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

ERROR - 2019-11-26 01:09:56 --> Severity: Notice --> Undefined property: MartinTest::$common_m /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/MartinTest.php 734
ERROR - 2019-11-26 01:09:56 --> 
        Exception of type \'Error\' occurred with Message: Call to a member function s3DateCheck() on null in File /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/MartinTest.php at Line 734
        
 Backtrace 
.#0 /Users/blumine/works/goodoc_v2/event/adminApi/system/core/CodeIgniter.php(532): MartinTest->index2()
#1 /Users/blumine/works/goodoc_v2/event/adminApi/index.php(341): require_once('/Users/blumine/...')
#2 {main}
    
ERROR - 2019-11-26 01:09:56 --> Severity: error --> Exception: Call to a member function s3DateCheck() on null /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/MartinTest.php 734
ERROR - 2019-11-26 01:09:56 --> 404 Page Not Found: Faviconico/index
ERROR - 2019-11-26 01:10:45 --> 
        Exception of type \'Aws\S3\Exception\S3Exception\' occurred with Message: Error executing "GetObject" on "https://s3.ap-northeast-2.amazonaws.com/asset-staging.goodoc.kr/http%3A//asset.goodoc.kr/images/event/10800/t2_8678f2d6-33d1-4479-8c72-2cef73a1bf9a.jpg"; AWS HTTP error: Client error: `GET https://s3.ap-northeast-2.amazonaws.com/asset-staging.goodoc.kr/http%3A//asset.goodoc.kr/images/event/10800/t2_8678f2d6-33d1-4479-8c72-2cef73a1bf9a.jpg` resulted in a `404 Not Found` response:
<?xml version="1.0" encoding="UTF-8"?>
<Error><Code>NoSuchKey</Code><Message>The specified key does not exist.</Message> (truncated...)
 NoSuchKey (client): The specified key does not exist. - <?xml version="1.0" encoding="UTF-8"?>
<Error><Code>NoSuchKey</Code><Message>The specified key does not exist.</Message><Key>http://asset.goodoc.kr/images/event/10800/t2_8678f2d6-33d1-4479-8c72-2cef73a1bf9a.jpg</Key><RequestId>9E585A378BC272C6</RequestId><HostId>hY1wglS4wsaEU+PFg9NFVWW1BPcfElRGyRHvOerD3rqoMPmVwwJ15dLSQOQos9b1uWHmmK/7oaY=</HostId></Error> in File /Users/blumine/works/goodoc_v2/event/adminApi/vendor/aws/aws-sdk-php/src/WrappedHttpHandler.php at Line 191
        
 Backtrace 
.#0 /Users/blumine/works/goodoc_v2/event/adminApi/vendor/aws/aws-sdk-php/src/WrappedHttpHandler.php(100): Aws\WrappedHttpHandler->parseError(Array, Object(GuzzleHttp\Psr7\Request), Object(Aws\Command), Array)
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
#16 /Users/blumine/works/goodoc_v2/event/adminApi/application/models/Common_m.php(713): Aws\AwsClient->__call('getObject', Array)
#17 /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/MartinTest.php(734): Common_m->s3DateCheck(2, Array)
#18 /Users/blumine/works/goodoc_v2/event/adminApi/system/core/CodeIgniter.php(532): MartinTest->index2()
#19 /Users/blumine/works/goodoc_v2/event/adminApi/index.php(341): require_once('/Users/blumine/...')
#20 {main}
    
ERROR - 2019-11-26 01:10:45 --> Severity: error --> Exception: Error executing "GetObject" on "https://s3.ap-northeast-2.amazonaws.com/asset-staging.goodoc.kr/http%3A//asset.goodoc.kr/images/event/10800/t2_8678f2d6-33d1-4479-8c72-2cef73a1bf9a.jpg"; AWS HTTP error: Client error: `GET https://s3.ap-northeast-2.amazonaws.com/asset-staging.goodoc.kr/http%3A//asset.goodoc.kr/images/event/10800/t2_8678f2d6-33d1-4479-8c72-2cef73a1bf9a.jpg` resulted in a `404 Not Found` response:
<?xml version="1.0" encoding="UTF-8"?>
<Error><Code>NoSuchKey</Code><Message>The specified key does not exist.</Message> (truncated...)
 NoSuchKey (client): The specified key does not exist. - <?xml version="1.0" encoding="UTF-8"?>
<Error><Code>NoSuchKey</Code><Message>The specified key does not exist.</Message><Key>http://asset.goodoc.kr/images/event/10800/t2_8678f2d6-33d1-4479-8c72-2cef73a1bf9a.jpg</Key><RequestId>9E585A378BC272C6</RequestId><HostId>hY1wglS4wsaEU+PFg9NFVWW1BPcfElRGyRHvOerD3rqoMPmVwwJ15dLSQOQos9b1uWHmmK/7oaY=</HostId></Error> /Users/blumine/works/goodoc_v2/event/adminApi/vendor/aws/aws-sdk-php/src/WrappedHttpHandler.php 191
ERROR - 2019-11-26 01:11:39 --> 
        Exception of type \'Aws\S3\Exception\S3Exception\' occurred with Message: Error executing "GetObject" on "https://s3.ap-northeast-2.amazonaws.com/asset-staging.goodoc.kr//images/event/10800/t2_8678f2d6-33d1-4479-8c72-2cef73a1bf9a.jpg"; AWS HTTP error: Client error: `GET https://s3.ap-northeast-2.amazonaws.com/asset-staging.goodoc.kr//images/event/10800/t2_8678f2d6-33d1-4479-8c72-2cef73a1bf9a.jpg` resulted in a `404 Not Found` response:
<?xml version="1.0" encoding="UTF-8"?>
<Error><Code>NoSuchKey</Code><Message>The specified key does not exist.</Message> (truncated...)
 NoSuchKey (client): The specified key does not exist. - <?xml version="1.0" encoding="UTF-8"?>
<Error><Code>NoSuchKey</Code><Message>The specified key does not exist.</Message><Key>/images/event/10800/t2_8678f2d6-33d1-4479-8c72-2cef73a1bf9a.jpg</Key><RequestId>44A3ECA940469BD4</RequestId><HostId>v9yj6A1uwZ/UU3DWGPzLFgdW3XV9IbbH/iWSZBXdrevZYf/owK2MStIcF587+VBpH2bB2zsGjmk=</HostId></Error> in File /Users/blumine/works/goodoc_v2/event/adminApi/vendor/aws/aws-sdk-php/src/WrappedHttpHandler.php at Line 191
        
 Backtrace 
.#0 /Users/blumine/works/goodoc_v2/event/adminApi/vendor/aws/aws-sdk-php/src/WrappedHttpHandler.php(100): Aws\WrappedHttpHandler->parseError(Array, Object(GuzzleHttp\Psr7\Request), Object(Aws\Command), Array)
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
#16 /Users/blumine/works/goodoc_v2/event/adminApi/application/models/Common_m.php(713): Aws\AwsClient->__call('getObject', Array)
#17 /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/MartinTest.php(735): Common_m->s3DateCheck(2, Array)
#18 /Users/blumine/works/goodoc_v2/event/adminApi/system/core/CodeIgniter.php(532): MartinTest->index2()
#19 /Users/blumine/works/goodoc_v2/event/adminApi/index.php(341): require_once('/Users/blumine/...')
#20 {main}
    
ERROR - 2019-11-26 01:11:39 --> Severity: error --> Exception: Error executing "GetObject" on "https://s3.ap-northeast-2.amazonaws.com/asset-staging.goodoc.kr//images/event/10800/t2_8678f2d6-33d1-4479-8c72-2cef73a1bf9a.jpg"; AWS HTTP error: Client error: `GET https://s3.ap-northeast-2.amazonaws.com/asset-staging.goodoc.kr//images/event/10800/t2_8678f2d6-33d1-4479-8c72-2cef73a1bf9a.jpg` resulted in a `404 Not Found` response:
<?xml version="1.0" encoding="UTF-8"?>
<Error><Code>NoSuchKey</Code><Message>The specified key does not exist.</Message> (truncated...)
 NoSuchKey (client): The specified key does not exist. - <?xml version="1.0" encoding="UTF-8"?>
<Error><Code>NoSuchKey</Code><Message>The specified key does not exist.</Message><Key>/images/event/10800/t2_8678f2d6-33d1-4479-8c72-2cef73a1bf9a.jpg</Key><RequestId>44A3ECA940469BD4</RequestId><HostId>v9yj6A1uwZ/UU3DWGPzLFgdW3XV9IbbH/iWSZBXdrevZYf/owK2MStIcF587+VBpH2bB2zsGjmk=</HostId></Error> /Users/blumine/works/goodoc_v2/event/adminApi/vendor/aws/aws-sdk-php/src/WrappedHttpHandler.php 191
