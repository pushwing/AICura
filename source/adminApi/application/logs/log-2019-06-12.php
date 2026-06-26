<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

ERROR - 2019-06-12 01:54:55 --> 404 Page Not Found: Faviconico/index
ERROR - 2019-06-12 01:56:17 --> Severity: Notice --> Undefined property: CronDaily::$v1 /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/CronDaily.php 664
ERROR - 2019-06-12 01:56:17 --> 
        Exception of type \'Error\' occurred with Message: Call to a member function query() on null in File /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/CronDaily.php at Line 664
        
 Backtrace 
.#0 /Users/blumine/works/goodoc_v2/event/adminApi/system/core/CodeIgniter.php(532): CronDaily->readyPriceCheck()
#1 /Users/blumine/works/goodoc_v2/event/adminApi/index.php(333): require_once('/Users/blumine/...')
#2 {main}
    
ERROR - 2019-06-12 01:56:17 --> Severity: error --> Exception: Call to a member function query() on null /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/CronDaily.php 664
ERROR - 2019-06-12 03:12:43 --> Severity: Warning --> Missing argument 2 for Common_m::s3DateCheck(), called in /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php on line 42 and defined /Users/blumine/works/goodoc_v2/event/adminApi/application/models/Common_m.php 599
ERROR - 2019-06-12 03:12:43 --> Severity: Notice --> Undefined variable: data /Users/blumine/works/goodoc_v2/event/adminApi/application/models/Common_m.php 655
ERROR - 2019-06-12 03:12:43 --> Severity: Notice --> Undefined variable: data /Users/blumine/works/goodoc_v2/event/adminApi/application/models/Common_m.php 655
ERROR - 2019-06-12 03:12:43 --> 
        Exception of type \'Aws\S3\Exception\S3Exception\' occurred with Message: Error executing "GetObject" on "https://s3.ap-northeast-1.amazonaws.com/image.goodoc/dev/uploads/event/image2//"; AWS HTTP error: Client error: `GET https://s3.ap-northeast-1.amazonaws.com/image.goodoc/dev/uploads/event/image2//` resulted in a `404 Not Found` response:
<?xml version="1.0" encoding="UTF-8"?>
<Error><Code>NoSuchKey</Code><Message>The specified key does not exist.</Message> (truncated...)
 NoSuchKey (client): The specified key does not exist. - <?xml version="1.0" encoding="UTF-8"?>
<Error><Code>NoSuchKey</Code><Message>The specified key does not exist.</Message><Key>dev/uploads/event/image2//</Key><RequestId>C202EFB034676587</RequestId><HostId>+fGJI50JHLxGVozaeMaChEWWggpIyCyEcFDbj/M5GXedqfXpCP8XBAn8P7ibr08uYndZlaD/jGA=</HostId></Error> in File /Users/blumine/works/goodoc_v2/event/adminApi/vendor/aws/aws-sdk-php/src/WrappedHttpHandler.php at Line 191
        
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
#16 /Users/blumine/works/goodoc_v2/event/adminApi/application/models/Common_m.php(662): Aws\AwsClient->__call('getObject', Array)
#17 /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php(42): Common_m->s3DateCheck(Array)
#18 /Users/blumine/works/goodoc_v2/event/adminApi/system/core/CodeIgniter.php(532): Welcome->index()
#19 /Users/blumine/works/goodoc_v2/event/adminApi/index.php(333): require_once('/Users/blumine/...')
#20 {main}
    
ERROR - 2019-06-12 03:12:44 --> Severity: error --> Exception: Error executing "GetObject" on "https://s3.ap-northeast-1.amazonaws.com/image.goodoc/dev/uploads/event/image2//"; AWS HTTP error: Client error: `GET https://s3.ap-northeast-1.amazonaws.com/image.goodoc/dev/uploads/event/image2//` resulted in a `404 Not Found` response:
<?xml version="1.0" encoding="UTF-8"?>
<Error><Code>NoSuchKey</Code><Message>The specified key does not exist.</Message> (truncated...)
 NoSuchKey (client): The specified key does not exist. - <?xml version="1.0" encoding="UTF-8"?>
<Error><Code>NoSuchKey</Code><Message>The specified key does not exist.</Message><Key>dev/uploads/event/image2//</Key><RequestId>C202EFB034676587</RequestId><HostId>+fGJI50JHLxGVozaeMaChEWWggpIyCyEcFDbj/M5GXedqfXpCP8XBAn8P7ibr08uYndZlaD/jGA=</HostId></Error> /Users/blumine/works/goodoc_v2/event/adminApi/vendor/aws/aws-sdk-php/src/WrappedHttpHandler.php 191
ERROR - 2019-06-12 04:13:08 --> 
        Exception of type \'Aws\S3\Exception\PermanentRedirectException\' occurred with Message: Encountered a permanent redirect while requesting https://s3.ap-northeast-2.amazonaws.com/image.goodoc/images/event/banner/20190611/d0a8dc6cff32918080465e0ec7ff3d88.jpg. Are you sure you are using the correct region for this bucket? in File /Users/blumine/works/goodoc_v2/event/adminApi/vendor/aws/aws-sdk-php/src/S3/PermanentRedirectMiddleware.php at Line 49
        
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
#14 /Users/blumine/works/goodoc_v2/event/adminApi/application/models/Common_m.php(650): Aws\AwsClient->__call('getObject', Array)
#15 /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php(46): Common_m->s3DateCheck(2, Array)
#16 /Users/blumine/works/goodoc_v2/event/adminApi/system/core/CodeIgniter.php(532): Welcome->index()
#17 /Users/blumine/works/goodoc_v2/event/adminApi/index.php(333): require_once('/Users/blumine/...')
#18 {main}
    
ERROR - 2019-06-12 04:13:08 --> Severity: error --> Exception: Encountered a permanent redirect while requesting https://s3.ap-northeast-2.amazonaws.com/image.goodoc/images/event/banner/20190611/d0a8dc6cff32918080465e0ec7ff3d88.jpg. Are you sure you are using the correct region for this bucket? /Users/blumine/works/goodoc_v2/event/adminApi/vendor/aws/aws-sdk-php/src/S3/PermanentRedirectMiddleware.php 49
ERROR - 2019-06-12 04:15:15 --> 
        Exception of type \'Aws\S3\Exception\S3Exception\' occurred with Message: Error executing "GetObject" on "https://s3.ap-northeast-2.amazonaws.com/asset-dev.goodoc.kr/images/event/banner/20190611/d0a8dc6cff32918080465e0ec7ff3d88.jpg"; AWS HTTP error: Client error: `GET https://s3.ap-northeast-2.amazonaws.com/asset-dev.goodoc.kr/images/event/banner/20190611/d0a8dc6cff32918080465e0ec7ff3d88.jpg` resulted in a `404 Not Found` response:
<?xml version="1.0" encoding="UTF-8"?>
<Error><Code>NoSuchKey</Code><Message>The specified key does not exist.</Message> (truncated...)
 NoSuchKey (client): The specified key does not exist. - <?xml version="1.0" encoding="UTF-8"?>
<Error><Code>NoSuchKey</Code><Message>The specified key does not exist.</Message><Key>images/event/banner/20190611/d0a8dc6cff32918080465e0ec7ff3d88.jpg</Key><RequestId>6B18E9E489A1B182</RequestId><HostId>CYhNFan2waE3333/zpi+OJGWPuHF61D3o6F3Wfrf3ZzGHuGoj1vGNFSWnmRi2Pa7Y3cWsal1Ntg=</HostId></Error> in File /Users/blumine/works/goodoc_v2/event/adminApi/vendor/aws/aws-sdk-php/src/WrappedHttpHandler.php at Line 191
        
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
#16 /Users/blumine/works/goodoc_v2/event/adminApi/application/models/Common_m.php(664): Aws\AwsClient->__call('getObject', Array)
#17 /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php(46): Common_m->s3DateCheck(2, Array)
#18 /Users/blumine/works/goodoc_v2/event/adminApi/system/core/CodeIgniter.php(532): Welcome->index()
#19 /Users/blumine/works/goodoc_v2/event/adminApi/index.php(333): require_once('/Users/blumine/...')
#20 {main}
    
ERROR - 2019-06-12 04:15:15 --> Severity: error --> Exception: Error executing "GetObject" on "https://s3.ap-northeast-2.amazonaws.com/asset-dev.goodoc.kr/images/event/banner/20190611/d0a8dc6cff32918080465e0ec7ff3d88.jpg"; AWS HTTP error: Client error: `GET https://s3.ap-northeast-2.amazonaws.com/asset-dev.goodoc.kr/images/event/banner/20190611/d0a8dc6cff32918080465e0ec7ff3d88.jpg` resulted in a `404 Not Found` response:
<?xml version="1.0" encoding="UTF-8"?>
<Error><Code>NoSuchKey</Code><Message>The specified key does not exist.</Message> (truncated...)
 NoSuchKey (client): The specified key does not exist. - <?xml version="1.0" encoding="UTF-8"?>
<Error><Code>NoSuchKey</Code><Message>The specified key does not exist.</Message><Key>images/event/banner/20190611/d0a8dc6cff32918080465e0ec7ff3d88.jpg</Key><RequestId>6B18E9E489A1B182</RequestId><HostId>CYhNFan2waE3333/zpi+OJGWPuHF61D3o6F3Wfrf3ZzGHuGoj1vGNFSWnmRi2Pa7Y3cWsal1Ntg=</HostId></Error> /Users/blumine/works/goodoc_v2/event/adminApi/vendor/aws/aws-sdk-php/src/WrappedHttpHandler.php 191
ERROR - 2019-06-12 04:18:38 --> 
        Exception of type \'Aws\S3\Exception\S3Exception\' occurred with Message: Error executing "GetObject" on "https://s3.ap-northeast-2.amazonaws.com/asset-dev.goodoc.kr/images/event/banner/20190611/d0a8dc6cff32918080465e0ec7ff3d88.jpg"; AWS HTTP error: Client error: `GET https://s3.ap-northeast-2.amazonaws.com/asset-dev.goodoc.kr/images/event/banner/20190611/d0a8dc6cff32918080465e0ec7ff3d88.jpg` resulted in a `404 Not Found` response:
<?xml version="1.0" encoding="UTF-8"?>
<Error><Code>NoSuchKey</Code><Message>The specified key does not exist.</Message> (truncated...)
 NoSuchKey (client): The specified key does not exist. - <?xml version="1.0" encoding="UTF-8"?>
<Error><Code>NoSuchKey</Code><Message>The specified key does not exist.</Message><Key>images/event/banner/20190611/d0a8dc6cff32918080465e0ec7ff3d88.jpg</Key><RequestId>8E6BBA0261DFA1C6</RequestId><HostId>h9a+Nxs/2LMdCPTLsar5e6BtsPbaxoDVtfj68HWN9xsdypAQY4hqO++VWsbA2ylyrczGwMR4QEg=</HostId></Error> in File /Users/blumine/works/goodoc_v2/event/adminApi/vendor/aws/aws-sdk-php/src/WrappedHttpHandler.php at Line 191
        
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
#16 /Users/blumine/works/goodoc_v2/event/adminApi/application/models/Common_m.php(665): Aws\AwsClient->__call('getObject', Array)
#17 /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php(46): Common_m->s3DateCheck(2, Array)
#18 /Users/blumine/works/goodoc_v2/event/adminApi/system/core/CodeIgniter.php(532): Welcome->index()
#19 /Users/blumine/works/goodoc_v2/event/adminApi/index.php(333): require_once('/Users/blumine/...')
#20 {main}
    
ERROR - 2019-06-12 04:18:38 --> Severity: error --> Exception: Error executing "GetObject" on "https://s3.ap-northeast-2.amazonaws.com/asset-dev.goodoc.kr/images/event/banner/20190611/d0a8dc6cff32918080465e0ec7ff3d88.jpg"; AWS HTTP error: Client error: `GET https://s3.ap-northeast-2.amazonaws.com/asset-dev.goodoc.kr/images/event/banner/20190611/d0a8dc6cff32918080465e0ec7ff3d88.jpg` resulted in a `404 Not Found` response:
<?xml version="1.0" encoding="UTF-8"?>
<Error><Code>NoSuchKey</Code><Message>The specified key does not exist.</Message> (truncated...)
 NoSuchKey (client): The specified key does not exist. - <?xml version="1.0" encoding="UTF-8"?>
<Error><Code>NoSuchKey</Code><Message>The specified key does not exist.</Message><Key>images/event/banner/20190611/d0a8dc6cff32918080465e0ec7ff3d88.jpg</Key><RequestId>8E6BBA0261DFA1C6</RequestId><HostId>h9a+Nxs/2LMdCPTLsar5e6BtsPbaxoDVtfj68HWN9xsdypAQY4hqO++VWsbA2ylyrczGwMR4QEg=</HostId></Error> /Users/blumine/works/goodoc_v2/event/adminApi/vendor/aws/aws-sdk-php/src/WrappedHttpHandler.php 191
