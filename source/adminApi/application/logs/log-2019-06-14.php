<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

ERROR - 2019-06-14 04:11:47 --> 
        Exception of type \'Aws\S3\Exception\S3Exception\' occurred with Message: Error executing "GetObject" on "https://s3.ap-northeast-2.amazonaws.com/asset-dev.goodoc.kr/8fa176fc340a3e8fde583448a832ba07.jpg"; AWS HTTP error: Client error: `GET https://s3.ap-northeast-2.amazonaws.com/asset-dev.goodoc.kr/8fa176fc340a3e8fde583448a832ba07.jpg` resulted in a `404 Not Found` response:
<?xml version="1.0" encoding="UTF-8"?>
<Error><Code>NoSuchKey</Code><Message>The specified key does not exist.</Message> (truncated...)
 NoSuchKey (client): The specified key does not exist. - <?xml version="1.0" encoding="UTF-8"?>
<Error><Code>NoSuchKey</Code><Message>The specified key does not exist.</Message><Key>8fa176fc340a3e8fde583448a832ba07.jpg</Key><RequestId>10ABFB5280CB367E</RequestId><HostId>UwxpKBLJZA4qrAthkbU06TCPGhiC7T9gmdzC2+bEsXVvZEZbJHUd46lS4Ap52eVVOOBPYOQFRJM=</HostId></Error> in File /Users/blumine/works/goodoc_v2/event/adminApi/vendor/aws/aws-sdk-php/src/WrappedHttpHandler.php at Line 191
        
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
#16 /Users/blumine/works/goodoc_v2/event/adminApi/application/models/Common_m.php(685): Aws\AwsClient->__call('getObject', Array)
#17 /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php(38): Common_m->s3DateCheck(2, Array)
#18 /Users/blumine/works/goodoc_v2/event/adminApi/system/core/CodeIgniter.php(532): Welcome->index()
#19 /Users/blumine/works/goodoc_v2/event/adminApi/index.php(341): require_once('/Users/blumine/...')
#20 {main}
    
ERROR - 2019-06-14 04:11:48 --> Severity: error --> Exception: Error executing "GetObject" on "https://s3.ap-northeast-2.amazonaws.com/asset-dev.goodoc.kr/8fa176fc340a3e8fde583448a832ba07.jpg"; AWS HTTP error: Client error: `GET https://s3.ap-northeast-2.amazonaws.com/asset-dev.goodoc.kr/8fa176fc340a3e8fde583448a832ba07.jpg` resulted in a `404 Not Found` response:
<?xml version="1.0" encoding="UTF-8"?>
<Error><Code>NoSuchKey</Code><Message>The specified key does not exist.</Message> (truncated...)
 NoSuchKey (client): The specified key does not exist. - <?xml version="1.0" encoding="UTF-8"?>
<Error><Code>NoSuchKey</Code><Message>The specified key does not exist.</Message><Key>8fa176fc340a3e8fde583448a832ba07.jpg</Key><RequestId>10ABFB5280CB367E</RequestId><HostId>UwxpKBLJZA4qrAthkbU06TCPGhiC7T9gmdzC2+bEsXVvZEZbJHUd46lS4Ap52eVVOOBPYOQFRJM=</HostId></Error> /Users/blumine/works/goodoc_v2/event/adminApi/vendor/aws/aws-sdk-php/src/WrappedHttpHandler.php 191
ERROR - 2019-06-14 04:11:48 --> 404 Page Not Found: Faviconico/index
ERROR - 2019-06-14 04:12:39 --> 
        Exception of type \'Aws\S3\Exception\S3Exception\' occurred with Message: Error executing "GetObject" on "https://s3.ap-northeast-2.amazonaws.com/asset.goodoc.kr/8fa176fc340a3e8fde583448a832ba07.jpg"; AWS HTTP error: Client error: `GET https://s3.ap-northeast-2.amazonaws.com/asset.goodoc.kr/8fa176fc340a3e8fde583448a832ba07.jpg` resulted in a `404 Not Found` response:
<?xml version="1.0" encoding="UTF-8"?>
<Error><Code>NoSuchKey</Code><Message>The specified key does not exist.</Message> (truncated...)
 NoSuchKey (client): The specified key does not exist. - <?xml version="1.0" encoding="UTF-8"?>
<Error><Code>NoSuchKey</Code><Message>The specified key does not exist.</Message><Key>8fa176fc340a3e8fde583448a832ba07.jpg</Key><RequestId>C508F144E7489D3B</RequestId><HostId>D0IxNq9ufw0YqholA8yWGzZmXaB3hK8FeNJbfe1WbVAntGvkFHVZdfMuwmi8+lV5sK80mRWTKD0=</HostId></Error> in File /Users/blumine/works/goodoc_v2/event/adminApi/vendor/aws/aws-sdk-php/src/WrappedHttpHandler.php at Line 191
        
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
#16 /Users/blumine/works/goodoc_v2/event/adminApi/application/models/Common_m.php(685): Aws\AwsClient->__call('getObject', Array)
#17 /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php(38): Common_m->s3DateCheck(2, Array)
#18 /Users/blumine/works/goodoc_v2/event/adminApi/system/core/CodeIgniter.php(532): Welcome->index()
#19 /Users/blumine/works/goodoc_v2/event/adminApi/index.php(341): require_once('/Users/blumine/...')
#20 {main}
    
ERROR - 2019-06-14 04:12:40 --> Severity: error --> Exception: Error executing "GetObject" on "https://s3.ap-northeast-2.amazonaws.com/asset.goodoc.kr/8fa176fc340a3e8fde583448a832ba07.jpg"; AWS HTTP error: Client error: `GET https://s3.ap-northeast-2.amazonaws.com/asset.goodoc.kr/8fa176fc340a3e8fde583448a832ba07.jpg` resulted in a `404 Not Found` response:
<?xml version="1.0" encoding="UTF-8"?>
<Error><Code>NoSuchKey</Code><Message>The specified key does not exist.</Message> (truncated...)
 NoSuchKey (client): The specified key does not exist. - <?xml version="1.0" encoding="UTF-8"?>
<Error><Code>NoSuchKey</Code><Message>The specified key does not exist.</Message><Key>8fa176fc340a3e8fde583448a832ba07.jpg</Key><RequestId>C508F144E7489D3B</RequestId><HostId>D0IxNq9ufw0YqholA8yWGzZmXaB3hK8FeNJbfe1WbVAntGvkFHVZdfMuwmi8+lV5sK80mRWTKD0=</HostId></Error> /Users/blumine/works/goodoc_v2/event/adminApi/vendor/aws/aws-sdk-php/src/WrappedHttpHandler.php 191
ERROR - 2019-06-14 04:13:25 --> 
        Exception of type \'Aws\S3\Exception\S3Exception\' occurred with Message: Error executing "GetObject" on "https://s3.ap-northeast-2.amazonaws.com/asset.goodoc.kr/images/event/banner/20190611/8fa176fc340a3e8fde583448a832ba07.jpg"; AWS HTTP error: Client error: `GET https://s3.ap-northeast-2.amazonaws.com/asset.goodoc.kr/images/event/banner/20190611/8fa176fc340a3e8fde583448a832ba07.jpg` resulted in a `404 Not Found` response:
<?xml version="1.0" encoding="UTF-8"?>
<Error><Code>NoSuchKey</Code><Message>The specified key does not exist.</Message> (truncated...)
 NoSuchKey (client): The specified key does not exist. - <?xml version="1.0" encoding="UTF-8"?>
<Error><Code>NoSuchKey</Code><Message>The specified key does not exist.</Message><Key>images/event/banner/20190611/8fa176fc340a3e8fde583448a832ba07.jpg</Key><RequestId>0C7615739DC90B53</RequestId><HostId>724J2xwSsgPZZmz4dZFxcwZFYAzAjU3qGEl7RqoyEeSxe/1apPlSJcfBkXSQQMV6dLvgP1hjA7I=</HostId></Error> in File /Users/blumine/works/goodoc_v2/event/adminApi/vendor/aws/aws-sdk-php/src/WrappedHttpHandler.php at Line 191
        
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
#16 /Users/blumine/works/goodoc_v2/event/adminApi/application/models/Common_m.php(685): Aws\AwsClient->__call('getObject', Array)
#17 /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php(38): Common_m->s3DateCheck(2, Array)
#18 /Users/blumine/works/goodoc_v2/event/adminApi/system/core/CodeIgniter.php(532): Welcome->index()
#19 /Users/blumine/works/goodoc_v2/event/adminApi/index.php(341): require_once('/Users/blumine/...')
#20 {main}
    
ERROR - 2019-06-14 04:13:25 --> Severity: error --> Exception: Error executing "GetObject" on "https://s3.ap-northeast-2.amazonaws.com/asset.goodoc.kr/images/event/banner/20190611/8fa176fc340a3e8fde583448a832ba07.jpg"; AWS HTTP error: Client error: `GET https://s3.ap-northeast-2.amazonaws.com/asset.goodoc.kr/images/event/banner/20190611/8fa176fc340a3e8fde583448a832ba07.jpg` resulted in a `404 Not Found` response:
<?xml version="1.0" encoding="UTF-8"?>
<Error><Code>NoSuchKey</Code><Message>The specified key does not exist.</Message> (truncated...)
 NoSuchKey (client): The specified key does not exist. - <?xml version="1.0" encoding="UTF-8"?>
<Error><Code>NoSuchKey</Code><Message>The specified key does not exist.</Message><Key>images/event/banner/20190611/8fa176fc340a3e8fde583448a832ba07.jpg</Key><RequestId>0C7615739DC90B53</RequestId><HostId>724J2xwSsgPZZmz4dZFxcwZFYAzAjU3qGEl7RqoyEeSxe/1apPlSJcfBkXSQQMV6dLvgP1hjA7I=</HostId></Error> /Users/blumine/works/goodoc_v2/event/adminApi/vendor/aws/aws-sdk-php/src/WrappedHttpHandler.php 191
ERROR - 2019-06-14 08:24:49 --> 404 Page Not Found: Faviconico/index
ERROR - 2019-06-14 08:58:24 --> Severity: Notice --> Undefined index: addId /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 98
ERROR - 2019-06-14 08:58:24 --> Query error: Table 'lactea.events' doesn't exist - Invalid query: select * from events where id=''
ERROR - 2019-06-14 08:58:43 --> Severity: Notice --> Undefined index: addId /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 98
ERROR - 2019-06-14 08:58:43 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:43 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:43 --> Severity: Notice --> Undefined index: addId /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 98
ERROR - 2019-06-14 08:58:43 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:43 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:43 --> Severity: Notice --> Undefined index: addId /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 98
ERROR - 2019-06-14 08:58:43 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:43 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:43 --> Severity: Notice --> Undefined index: addId /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 98
ERROR - 2019-06-14 08:58:43 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:43 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:43 --> Severity: Notice --> Undefined index: addId /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 98
ERROR - 2019-06-14 08:58:43 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:43 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:43 --> Severity: Notice --> Undefined index: addId /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 98
ERROR - 2019-06-14 08:58:43 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:43 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:43 --> Severity: Notice --> Undefined index: addId /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 98
ERROR - 2019-06-14 08:58:43 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:43 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:43 --> Severity: Notice --> Undefined index: addId /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 98
ERROR - 2019-06-14 08:58:43 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:43 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:43 --> Severity: Notice --> Undefined index: addId /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 98
ERROR - 2019-06-14 08:58:43 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:43 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:43 --> Severity: Notice --> Undefined index: addId /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 98
ERROR - 2019-06-14 08:58:44 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:44 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:44 --> Severity: Notice --> Undefined index: addId /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 98
ERROR - 2019-06-14 08:58:44 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:44 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:44 --> Severity: Notice --> Undefined index: addId /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 98
ERROR - 2019-06-14 08:58:44 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:44 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:44 --> Severity: Notice --> Undefined index: addId /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 98
ERROR - 2019-06-14 08:58:44 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:44 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:44 --> Severity: Notice --> Undefined index: addId /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 98
ERROR - 2019-06-14 08:58:44 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:44 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:44 --> Severity: Notice --> Undefined index: addId /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 98
ERROR - 2019-06-14 08:58:44 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:44 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:44 --> Severity: Notice --> Undefined index: addId /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 98
ERROR - 2019-06-14 08:58:44 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:44 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:44 --> Severity: Notice --> Undefined index: addId /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 98
ERROR - 2019-06-14 08:58:44 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:44 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:44 --> Severity: Notice --> Undefined index: addId /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 98
ERROR - 2019-06-14 08:58:44 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:44 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:44 --> Severity: Notice --> Undefined index: addId /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 98
ERROR - 2019-06-14 08:58:44 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:44 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:44 --> Severity: Notice --> Undefined index: addId /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 98
ERROR - 2019-06-14 08:58:44 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:44 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:44 --> Severity: Notice --> Undefined index: addId /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 98
ERROR - 2019-06-14 08:58:44 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:44 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:44 --> Severity: Notice --> Undefined index: addId /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 98
ERROR - 2019-06-14 08:58:44 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:44 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:44 --> Severity: Notice --> Undefined index: addId /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 98
ERROR - 2019-06-14 08:58:44 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:44 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:44 --> Severity: Notice --> Undefined index: addId /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 98
ERROR - 2019-06-14 08:58:44 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:44 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:44 --> Severity: Notice --> Undefined index: addId /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 98
ERROR - 2019-06-14 08:58:44 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:44 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:44 --> Severity: Notice --> Undefined index: addId /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 98
ERROR - 2019-06-14 08:58:44 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:44 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:44 --> Severity: Notice --> Undefined index: addId /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 98
ERROR - 2019-06-14 08:58:44 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:44 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:44 --> Severity: Notice --> Undefined index: addId /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 98
ERROR - 2019-06-14 08:58:44 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:44 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:44 --> Severity: Notice --> Undefined index: addId /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 98
ERROR - 2019-06-14 08:58:44 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:44 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:44 --> Severity: Notice --> Undefined index: addId /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 98
ERROR - 2019-06-14 08:58:44 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:44 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:44 --> Severity: Notice --> Undefined index: addId /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 98
ERROR - 2019-06-14 08:58:44 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:44 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:44 --> Severity: Notice --> Undefined index: addId /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 98
ERROR - 2019-06-14 08:58:44 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:44 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:44 --> Severity: Notice --> Undefined index: addId /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 98
ERROR - 2019-06-14 08:58:44 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:44 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:44 --> Severity: Notice --> Undefined index: addId /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 98
ERROR - 2019-06-14 08:58:45 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:45 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:45 --> Severity: Notice --> Undefined index: addId /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 98
ERROR - 2019-06-14 08:58:45 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:45 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:45 --> Severity: Notice --> Undefined index: addId /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 98
ERROR - 2019-06-14 08:58:45 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:45 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:45 --> Severity: Notice --> Undefined index: addId /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 98
ERROR - 2019-06-14 08:58:45 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:45 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:45 --> Severity: Notice --> Undefined index: addId /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 98
ERROR - 2019-06-14 08:58:45 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:45 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:45 --> Severity: Notice --> Undefined index: addId /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 98
ERROR - 2019-06-14 08:58:45 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:45 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:45 --> Severity: Notice --> Undefined index: addId /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 98
ERROR - 2019-06-14 08:58:45 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:45 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:45 --> Severity: Notice --> Undefined index: addId /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 98
ERROR - 2019-06-14 08:58:45 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:45 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:45 --> Severity: Notice --> Undefined index: addId /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 98
ERROR - 2019-06-14 08:58:45 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:45 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:45 --> Severity: Notice --> Undefined index: addId /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 98
ERROR - 2019-06-14 08:58:45 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:45 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:45 --> Severity: Notice --> Undefined index: addId /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 98
ERROR - 2019-06-14 08:58:45 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:45 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:45 --> Severity: Notice --> Undefined index: addId /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 98
ERROR - 2019-06-14 08:58:45 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:45 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:45 --> Severity: Notice --> Undefined index: addId /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 98
ERROR - 2019-06-14 08:58:45 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:45 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:45 --> Severity: Notice --> Undefined index: addId /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 98
ERROR - 2019-06-14 08:58:45 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:45 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:45 --> Severity: Notice --> Undefined index: addId /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 98
ERROR - 2019-06-14 08:58:45 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:45 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:45 --> Severity: Notice --> Undefined index: addId /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 98
ERROR - 2019-06-14 08:58:45 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:45 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:45 --> Severity: Notice --> Undefined index: addId /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 98
ERROR - 2019-06-14 08:58:45 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:45 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:45 --> Severity: Notice --> Undefined index: addId /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 98
ERROR - 2019-06-14 08:58:45 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:45 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:45 --> Severity: Notice --> Undefined index: addId /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 98
ERROR - 2019-06-14 08:58:45 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:45 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:45 --> Severity: Notice --> Undefined index: addId /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 98
ERROR - 2019-06-14 08:58:45 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:45 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:45 --> Severity: Notice --> Undefined index: addId /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 98
ERROR - 2019-06-14 08:58:45 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:45 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:45 --> Severity: Notice --> Undefined index: addId /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 98
ERROR - 2019-06-14 08:58:45 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:45 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:45 --> Severity: Notice --> Undefined index: addId /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 98
ERROR - 2019-06-14 08:58:46 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:46 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:46 --> Severity: Notice --> Undefined index: addId /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 98
ERROR - 2019-06-14 08:58:46 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:46 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:46 --> Severity: Notice --> Undefined index: addId /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 98
ERROR - 2019-06-14 08:58:46 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:46 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:46 --> Severity: Notice --> Undefined index: addId /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 98
ERROR - 2019-06-14 08:58:46 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:46 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:46 --> Severity: Notice --> Undefined index: addId /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 98
ERROR - 2019-06-14 08:58:46 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:46 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:46 --> Severity: Notice --> Undefined index: addId /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 98
ERROR - 2019-06-14 08:58:46 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:46 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:46 --> Severity: Notice --> Undefined index: addId /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 98
ERROR - 2019-06-14 08:58:46 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:46 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:46 --> Severity: Notice --> Undefined index: addId /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 98
ERROR - 2019-06-14 08:58:46 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:46 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:46 --> Severity: Notice --> Undefined index: addId /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 98
ERROR - 2019-06-14 08:58:46 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:46 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:46 --> Severity: Notice --> Undefined index: addId /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 98
ERROR - 2019-06-14 08:58:46 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:46 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:46 --> Severity: Notice --> Undefined index: addId /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 98
ERROR - 2019-06-14 08:58:46 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:46 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:46 --> Severity: Notice --> Undefined index: addId /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 98
ERROR - 2019-06-14 08:58:46 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:46 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:46 --> Severity: Notice --> Undefined index: addId /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 98
ERROR - 2019-06-14 08:58:46 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:46 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:46 --> Severity: Notice --> Undefined index: addId /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 98
ERROR - 2019-06-14 08:58:46 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:46 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:46 --> Severity: Notice --> Undefined index: addId /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 98
ERROR - 2019-06-14 08:58:46 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:46 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:46 --> Severity: Notice --> Undefined index: addId /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 98
ERROR - 2019-06-14 08:58:46 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:46 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:46 --> Severity: Notice --> Undefined index: addId /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 98
ERROR - 2019-06-14 08:58:46 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:46 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:46 --> Severity: Notice --> Undefined index: addId /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 98
ERROR - 2019-06-14 08:58:46 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:46 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:46 --> Severity: Notice --> Undefined index: addId /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 98
ERROR - 2019-06-14 08:58:46 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:46 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:46 --> Severity: Notice --> Undefined index: addId /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 98
ERROR - 2019-06-14 08:58:46 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:46 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:46 --> Severity: Notice --> Undefined index: addId /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 98
ERROR - 2019-06-14 08:58:46 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:46 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:46 --> Severity: Notice --> Undefined index: addId /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 98
ERROR - 2019-06-14 08:58:46 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:46 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:46 --> Severity: Notice --> Undefined index: addId /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 98
ERROR - 2019-06-14 08:58:46 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:46 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:46 --> Severity: Notice --> Undefined index: addId /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 98
ERROR - 2019-06-14 08:58:46 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:46 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:46 --> Severity: Notice --> Undefined index: addId /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 98
ERROR - 2019-06-14 08:58:47 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:47 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:47 --> Severity: Notice --> Undefined index: addId /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 98
ERROR - 2019-06-14 08:58:47 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:47 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:47 --> Severity: Notice --> Undefined index: addId /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 98
ERROR - 2019-06-14 08:58:47 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:47 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:47 --> Severity: Notice --> Undefined index: addId /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 98
ERROR - 2019-06-14 08:58:47 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:47 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:47 --> Severity: Notice --> Undefined index: addId /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 98
ERROR - 2019-06-14 08:58:47 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:47 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:47 --> Severity: Notice --> Undefined index: addId /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 98
ERROR - 2019-06-14 08:58:47 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:47 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:47 --> Severity: Notice --> Undefined index: addId /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 98
ERROR - 2019-06-14 08:58:47 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:47 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:47 --> Severity: Notice --> Undefined index: addId /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 98
ERROR - 2019-06-14 08:58:47 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:47 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:47 --> Severity: Notice --> Undefined index: addId /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 98
ERROR - 2019-06-14 08:58:47 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:47 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:47 --> Severity: Notice --> Undefined index: addId /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 98
ERROR - 2019-06-14 08:58:47 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:47 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:47 --> Severity: Notice --> Undefined index: addId /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 98
ERROR - 2019-06-14 08:58:47 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:47 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:47 --> Severity: Notice --> Undefined index: addId /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 98
ERROR - 2019-06-14 08:58:47 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:47 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:47 --> Severity: Notice --> Undefined index: addId /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 98
ERROR - 2019-06-14 08:58:47 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:47 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:47 --> Severity: Notice --> Undefined index: addId /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 98
ERROR - 2019-06-14 08:58:47 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:47 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:47 --> Severity: Notice --> Undefined index: addId /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 98
ERROR - 2019-06-14 08:58:47 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:47 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:47 --> Severity: Notice --> Undefined index: addId /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 98
ERROR - 2019-06-14 08:58:47 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:47 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:47 --> Severity: Notice --> Undefined index: addId /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 98
ERROR - 2019-06-14 08:58:47 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:47 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:47 --> Severity: Notice --> Undefined index: addId /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 98
ERROR - 2019-06-14 08:58:47 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:47 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:47 --> Severity: Notice --> Undefined index: addId /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 98
ERROR - 2019-06-14 08:58:47 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:47 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:47 --> Severity: Notice --> Undefined index: addId /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 98
ERROR - 2019-06-14 08:58:47 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:47 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:47 --> Severity: Notice --> Undefined index: addId /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 98
ERROR - 2019-06-14 08:58:47 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:47 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:47 --> Severity: Notice --> Undefined index: addId /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 98
ERROR - 2019-06-14 08:58:47 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:47 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:47 --> Severity: Notice --> Undefined index: addId /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 98
ERROR - 2019-06-14 08:58:47 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:47 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:47 --> Severity: Notice --> Undefined index: addId /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 98
ERROR - 2019-06-14 08:58:48 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:48 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:48 --> Severity: Notice --> Undefined index: addId /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 98
ERROR - 2019-06-14 08:58:48 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:48 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:48 --> Severity: Notice --> Undefined index: addId /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 98
ERROR - 2019-06-14 08:58:48 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:48 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:48 --> Severity: Notice --> Undefined index: addId /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 98
ERROR - 2019-06-14 08:58:48 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:48 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:48 --> Severity: Notice --> Undefined index: addId /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 98
ERROR - 2019-06-14 08:58:48 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:48 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:48 --> Severity: Notice --> Undefined index: addId /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 98
ERROR - 2019-06-14 08:58:48 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:48 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:48 --> Severity: Notice --> Undefined index: addId /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 98
ERROR - 2019-06-14 08:58:48 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:48 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:48 --> Severity: Notice --> Undefined index: addId /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 98
ERROR - 2019-06-14 08:58:48 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:48 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:48 --> Severity: Notice --> Undefined index: addId /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 98
ERROR - 2019-06-14 08:58:48 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:48 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:48 --> Severity: Notice --> Undefined index: addId /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 98
ERROR - 2019-06-14 08:58:48 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:48 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:48 --> Severity: Notice --> Undefined index: addId /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 98
ERROR - 2019-06-14 08:58:48 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:48 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:48 --> Severity: Notice --> Undefined index: addId /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 98
ERROR - 2019-06-14 08:58:48 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:48 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:48 --> Severity: Notice --> Undefined index: addId /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 98
ERROR - 2019-06-14 08:58:48 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:48 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:48 --> Severity: Notice --> Undefined index: addId /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 98
ERROR - 2019-06-14 08:58:48 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:48 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:48 --> Severity: Notice --> Undefined index: addId /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 98
ERROR - 2019-06-14 08:58:48 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:48 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:48 --> Severity: Notice --> Undefined index: addId /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 98
ERROR - 2019-06-14 08:58:48 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:48 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:48 --> Severity: Notice --> Undefined index: addId /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 98
ERROR - 2019-06-14 08:58:48 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:48 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:48 --> Severity: Notice --> Undefined index: addId /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 98
ERROR - 2019-06-14 08:58:48 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:48 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:48 --> Severity: Notice --> Undefined index: addId /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 98
ERROR - 2019-06-14 08:58:48 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:48 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:48 --> Severity: Notice --> Undefined index: addId /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 98
ERROR - 2019-06-14 08:58:48 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:48 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:48 --> Severity: Notice --> Undefined index: addId /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 98
ERROR - 2019-06-14 08:58:48 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:48 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:48 --> Severity: Notice --> Undefined index: addId /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 98
ERROR - 2019-06-14 08:58:48 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:48 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:48 --> Severity: Notice --> Undefined index: addId /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 98
ERROR - 2019-06-14 08:58:48 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:48 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:48 --> Severity: Notice --> Undefined index: addId /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 98
ERROR - 2019-06-14 08:58:49 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:49 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:49 --> Severity: Notice --> Undefined index: addId /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 98
ERROR - 2019-06-14 08:58:49 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:49 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:49 --> Severity: Notice --> Undefined index: addId /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 98
ERROR - 2019-06-14 08:58:49 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:58:49 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:15 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:15 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:15 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:15 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:15 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:15 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:15 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:15 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:15 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:15 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:15 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:15 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:15 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:15 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:15 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:15 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:15 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:15 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:15 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:15 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:15 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:15 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:15 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:15 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:15 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:15 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:15 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:15 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:15 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:15 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:15 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:15 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:15 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:15 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:15 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:15 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:16 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:16 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:16 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:16 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:16 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:16 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:16 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:16 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:16 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:16 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:16 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:16 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:16 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:16 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:16 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:16 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:16 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:16 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:16 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:16 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:16 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:16 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:16 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:16 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:16 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:16 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:16 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:16 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:16 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:16 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:16 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:16 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:16 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:16 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:16 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:16 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:16 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:16 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:16 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:16 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:16 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:16 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:16 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:16 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:16 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:16 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:16 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:16 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:16 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:16 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:17 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:17 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:17 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:17 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:17 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:17 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:17 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:17 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:17 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:17 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:17 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:17 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:17 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:17 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:17 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:17 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:17 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:17 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:17 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:17 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:17 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:17 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:17 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:17 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:17 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:17 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:17 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:17 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:17 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:17 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:17 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:17 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:17 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:17 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:17 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:17 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:17 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:17 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:17 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:17 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:17 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:17 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:17 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:17 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:17 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:17 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:17 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:17 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:17 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:17 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:17 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:17 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:17 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:17 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:18 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:18 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:18 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:18 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:18 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:18 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:18 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:18 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:18 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:18 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:18 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:18 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:18 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:18 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:18 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:18 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:18 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:18 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:18 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:18 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:18 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:18 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:18 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:18 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:18 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:18 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:18 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:18 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:18 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:18 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:18 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:18 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:18 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:18 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:18 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:18 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:18 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:18 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:18 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:18 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:18 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:18 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:18 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:18 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:18 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:18 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:18 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:18 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:18 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:18 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:18 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:18 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:19 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:19 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:19 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:19 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:19 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:19 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:19 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:19 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:19 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:19 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:19 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:19 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:19 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:19 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:19 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:19 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:19 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:19 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:19 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:19 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:19 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:19 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:19 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:19 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:19 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:19 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:19 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:19 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:20 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:20 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:20 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:20 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:20 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:20 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:20 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:20 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:20 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:20 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:20 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:20 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:20 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:20 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:20 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:20 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:20 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:20 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:20 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:20 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:20 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:20 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:20 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:20 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:20 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:20 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:20 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:20 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:20 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:20 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:20 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:20 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:20 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:20 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:20 --> Severity: Notice --> Undefined index: vT2ImageName /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:20 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:29 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:29 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:29 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:29 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:29 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:29 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:29 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:29 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:29 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:29 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:29 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:29 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:29 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:29 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:29 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:29 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:29 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:30 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:30 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:30 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:30 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:30 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:30 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:30 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:30 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:30 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:30 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:30 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:30 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:30 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:30 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:30 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:30 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:30 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:30 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:30 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:30 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:30 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:30 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:30 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:30 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:30 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:30 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:31 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:31 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:31 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:31 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:31 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:31 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:31 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:31 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:31 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:31 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:31 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:31 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:31 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:31 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:31 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:31 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:31 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:31 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:31 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:31 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:31 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:31 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:31 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:31 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:31 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:32 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:32 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:32 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:32 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:32 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:32 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:32 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:32 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:32 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:32 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:32 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:32 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:32 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:32 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:32 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:32 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:32 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:32 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:32 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:32 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:32 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:32 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:32 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:32 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:32 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:32 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:33 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:33 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:33 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:33 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:33 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:33 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:33 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:33 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:33 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:33 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:33 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:33 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:33 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:33 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:33 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:33 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:33 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:33 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:33 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:33 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:33 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:33 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:33 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:34 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:34 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:34 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:34 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:34 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:34 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:34 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:34 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:34 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:34 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
ERROR - 2019-06-14 08:59:34 --> Severity: Notice --> Undefined index: isLive /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/Welcome.php 105
