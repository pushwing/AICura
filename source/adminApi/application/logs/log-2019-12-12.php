<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

ERROR - 2019-12-12 00:45:24 --> 
        Exception of type \'Error\' occurred with Message: Call to a member function query() on null in File /Users/blumine/works/goodoc_v2/event/adminApi/application/models/Common_m.php at Line 33
        
 Backtrace 
.#0 /Users/blumine/works/goodoc_v2/event/adminApi/application/models/Ads_m.php(3014): Common_m->isInTrans()
#1 /Users/blumine/works/goodoc_v2/event/adminApi/application/models/Ads_m.php(2864): Ads_m->getHistoryListSec(Array)
#2 /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/MartinTest.php(36): Ads_m->gethistoryMerge(Array)
#3 /Users/blumine/works/goodoc_v2/event/adminApi/system/core/CodeIgniter.php(532): MartinTest->index()
#4 /Users/blumine/works/goodoc_v2/event/adminApi/index.php(341): require_once('/Users/blumine/...')
#5 {main}
    
ERROR - 2019-12-12 00:45:24 --> Severity: error --> Exception: Call to a member function query() on null /Users/blumine/works/goodoc_v2/event/adminApi/application/models/Common_m.php 33
ERROR - 2019-12-12 00:45:25 --> 404 Page Not Found: Faviconico/index
ERROR - 2019-12-12 00:46:26 --> Severity: Notice --> Undefined property: MartinTest::$eventupload /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/MartinTest.php 42
ERROR - 2019-12-12 00:46:26 --> 
        Exception of type \'Error\' occurred with Message: Call to a member function file_call() on null in File /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/MartinTest.php at Line 42
        
 Backtrace 
.#0 /Users/blumine/works/goodoc_v2/event/adminApi/system/core/CodeIgniter.php(532): MartinTest->index()
#1 /Users/blumine/works/goodoc_v2/event/adminApi/index.php(341): require_once('/Users/blumine/...')
#2 {main}
    
ERROR - 2019-12-12 00:46:26 --> Severity: error --> Exception: Call to a member function file_call() on null /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/MartinTest.php 42
ERROR - 2019-12-12 01:18:23 --> 
        Exception of type \'RuntimeException\' occurred with Message: Unable to open /Users/blumine/works/goodoc_v2/event/adminApi/uploads/events/10135/1576113502/index_goodoc.html using mode r: fopen(/Users/blumine/works/goodoc_v2/event/adminApi/uploads/events/10135/1576113502/index_goodoc.html): failed to open stream: No such file or directory in File /Users/blumine/works/goodoc_v2/event/adminApi/vendor/guzzlehttp/psr7/src/functions.php at Line 303
        
 Backtrace 
.#0 [internal function]: GuzzleHttp\Psr7\{closure}(2, 'fopen(/Users/bl...', '/Users/blumine/...', 311, Array)
#1 /Users/blumine/works/goodoc_v2/event/adminApi/vendor/guzzlehttp/psr7/src/functions.php(311): fopen('/Users/blumine/...', 'r')
#2 /Users/blumine/works/goodoc_v2/event/adminApi/vendor/guzzlehttp/psr7/src/LazyOpenStream.php(37): GuzzleHttp\Psr7\try_fopen('/Users/blumine/...', 'r')
#3 /Users/blumine/works/goodoc_v2/event/adminApi/vendor/guzzlehttp/psr7/src/StreamDecoratorTrait.php(31): GuzzleHttp\Psr7\LazyOpenStream->createStream()
#4 /Users/blumine/works/goodoc_v2/event/adminApi/vendor/guzzlehttp/psr7/src/StreamDecoratorTrait.php(81): GuzzleHttp\Psr7\LazyOpenStream->__get('stream')
#5 /Users/blumine/works/goodoc_v2/event/adminApi/vendor/aws/aws-sdk-php/src/Middleware.php(230): GuzzleHttp\Psr7\LazyOpenStream->getMetadata('uri')
#6 /Users/blumine/works/goodoc_v2/event/adminApi/vendor/aws/aws-sdk-php/src/S3/ApplyChecksumMiddleware.php(76): Aws\Middleware::Aws\{closure}(Object(Aws\Command), Object(GuzzleHttp\Psr7\Request))
#7 /Users/blumine/works/goodoc_v2/event/adminApi/vendor/aws/aws-sdk-php/src/EndpointDiscovery/EndpointDiscoveryMiddleware.php(165): Aws\S3\ApplyChecksumMiddleware->__invoke(Object(Aws\Command), Object(GuzzleHttp\Psr7\Request))
#8 /Users/blumine/works/goodoc_v2/event/adminApi/vendor/aws/aws-sdk-php/src/EndpointParameterMiddleware.php(82): Aws\EndpointDiscovery\EndpointDiscoveryMiddleware->__invoke(Object(Aws\Command), Object(GuzzleHttp\Psr7\Request))
#9 /Users/blumine/works/goodoc_v2/event/adminApi/vendor/aws/aws-sdk-php/src/ClientSideMonitoring/AbstractMonitoringMiddleware.php(126): Aws\EndpointParameterMiddleware->__invoke(Object(Aws\Command), Object(GuzzleHttp\Psr7\Request))
#10 /Users/blumine/works/goodoc_v2/event/adminApi/vendor/aws/aws-sdk-php/src/ClientResolver.php(609): Aws\ClientSideMonitoring\AbstractMonitoringMiddleware->__invoke(Object(Aws\Command), Object(GuzzleHttp\Psr7\Request))
#11 /Users/blumine/works/goodoc_v2/event/adminApi/vendor/aws/aws-sdk-php/src/Middleware.php(96): Aws\ClientResolver::Aws\{closure}(Object(Aws\Command), Object(GuzzleHttp\Psr7\Request))
#12 /Users/blumine/works/goodoc_v2/event/adminApi/vendor/aws/aws-sdk-php/src/Middleware.php(80): Aws\Middleware::Aws\{closure}(Object(Aws\Command), NULL)
#13 /Users/blumine/works/goodoc_v2/event/adminApi/vendor/aws/aws-sdk-php/src/S3/S3Client.php(447): Aws\Middleware::Aws\{closure}(Object(Aws\Command), NULL)
#14 /Users/blumine/works/goodoc_v2/event/adminApi/vendor/aws/aws-sdk-php/src/S3/S3Client.php(470): Aws\S3\S3Client::Aws\S3\{closure}(Object(Aws\Command), NULL)
#15 /Users/blumine/works/goodoc_v2/event/adminApi/vendor/aws/aws-sdk-php/src/S3/S3Client.php(404): Aws\S3\S3Client::Aws\S3\{closure}(Object(Aws\Command), NULL)
#16 /Users/blumine/works/goodoc_v2/event/adminApi/vendor/aws/aws-sdk-php/src/S3/S3Client.php(423): Aws\S3\S3Client::Aws\S3\{closure}(Object(Aws\Command), NULL)
#17 /Users/blumine/works/goodoc_v2/event/adminApi/vendor/aws/aws-sdk-php/src/Middleware.php(54): Aws\S3\S3Client::Aws\S3\{closure}(Object(Aws\Command), NULL)
#18 /Users/blumine/works/goodoc_v2/event/adminApi/vendor/aws/aws-sdk-php/src/S3/SSECMiddleware.php(59): Aws\Middleware::Aws\{closure}(Object(Aws\Command), NULL)
#19 /Users/blumine/works/goodoc_v2/event/adminApi/vendor/aws/aws-sdk-php/src/IdempotencyTokenMiddleware.php(77): Aws\S3\SSECMiddleware->__invoke(Object(Aws\Command), NULL)
#20 /Users/blumine/works/goodoc_v2/event/adminApi/vendor/aws/aws-sdk-php/src/AwsClientTrait.php(64): Aws\IdempotencyTokenMiddleware->__invoke(Object(Aws\Command))
#21 /Users/blumine/works/goodoc_v2/event/adminApi/vendor/aws/aws-sdk-php/src/AwsClientTrait.php(58): Aws\AwsClient->executeAsync(Object(Aws\Command))
#22 /Users/blumine/works/goodoc_v2/event/adminApi/vendor/aws/aws-sdk-php/src/AwsClientTrait.php(77): Aws\AwsClient->execute(Object(Aws\Command))
#23 /Users/blumine/works/goodoc_v2/event/adminApi/application/libraries/EventUpload.php(317): Aws\AwsClient->__call('putObject', Array)
#24 /Users/blumine/works/goodoc_v2/event/adminApi/application/libraries/EventUpload.php(117): EventUpload->s3Client_singleUpload('events/10135/15...', '/Users/blumine/...')
#25 /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/MartinTest.php(43): EventUpload->file_call('1', Array)
#26 /Users/blumine/works/goodoc_v2/event/adminApi/system/core/CodeIgniter.php(532): MartinTest->index()
#27 /Users/blumine/works/goodoc_v2/event/adminApi/index.php(341): require_once('/Users/blumine/...')
#28 {main}
    
ERROR - 2019-12-12 01:18:23 --> Severity: error --> Exception: Unable to open /Users/blumine/works/goodoc_v2/event/adminApi/uploads/events/10135/1576113502/index_goodoc.html using mode r: fopen(/Users/blumine/works/goodoc_v2/event/adminApi/uploads/events/10135/1576113502/index_goodoc.html): failed to open stream: No such file or directory /Users/blumine/works/goodoc_v2/event/adminApi/vendor/guzzlehttp/psr7/src/functions.php 303
ERROR - 2019-12-12 01:25:31 --> 404 Page Not Found: MartinTest/event.css
ERROR - 2019-12-12 01:25:31 --> 404 Page Not Found: MartinTest/event.js
ERROR - 2019-12-12 01:25:31 --> 404 Page Not Found: MartinTest/event.js
ERROR - 2019-12-12 07:45:41 --> Severity: Warning --> Use of undefined constant images - assumed 'images' (this will throw an Error in a future version of PHP) /Users/blumine/works/goodoc_v2/event/adminApi/application/libraries/MakeTemplate.php 99
ERROR - 2019-12-12 07:46:09 --> Severity: Warning --> Use of undefined constant images - assumed 'images' (this will throw an Error in a future version of PHP) /Users/blumine/works/goodoc_v2/event/adminApi/application/libraries/MakeTemplate.php 99
ERROR - 2019-12-12 08:14:24 --> 404 Page Not Found: Faviconico/index
ERROR - 2019-12-12 08:59:26 --> 
        Exception of type \'Aws\Exception\CredentialsException\' occurred with Message: Cannot read credentials from /Users/blumine/.aws/credentials in File /Users/blumine/works/goodoc_v2/event/adminApi/vendor/aws/aws-sdk-php/src/Credentials/CredentialProvider.php at Line 399
        
 Backtrace 
.#0 /Users/blumine/works/goodoc_v2/event/adminApi/vendor/aws/aws-sdk-php/src/Credentials/CredentialProvider.php(295): Aws\Credentials\CredentialProvider::reject('Cannot read cre...')
#1 /Users/blumine/works/goodoc_v2/event/adminApi/vendor/aws/aws-sdk-php/src/Middleware.php(121): Aws\Credentials\CredentialProvider::Aws\Credentials\{closure}()
#2 /Users/blumine/works/goodoc_v2/event/adminApi/vendor/aws/aws-sdk-php/src/RetryMiddleware.php(264): Aws\Middleware::Aws\{closure}(Object(Aws\Command), Object(GuzzleHttp\Psr7\Request))
#3 /Users/blumine/works/goodoc_v2/event/adminApi/vendor/aws/aws-sdk-php/src/Middleware.php(206): Aws\RetryMiddleware->__invoke(Object(Aws\Command), Object(GuzzleHttp\Psr7\Request))
#4 /Users/blumine/works/goodoc_v2/event/adminApi/vendor/aws/aws-sdk-php/src/EndpointDiscovery/EndpointDiscoveryMiddleware.php(165): Aws\Middleware::Aws\{closure}(Object(Aws\Command), Object(GuzzleHttp\Psr7\Request))
#5 /Users/blumine/works/goodoc_v2/event/adminApi/vendor/aws/aws-sdk-php/src/EndpointParameterMiddleware.php(82): Aws\EndpointDiscovery\EndpointDiscoveryMiddleware->__invoke(Object(Aws\Command), Object(GuzzleHttp\Psr7\Request))
#6 /Users/blumine/works/goodoc_v2/event/adminApi/vendor/aws/aws-sdk-php/src/ClientSideMonitoring/AbstractMonitoringMiddleware.php(126): Aws\EndpointParameterMiddleware->__invoke(Object(Aws\Command), Object(GuzzleHttp\Psr7\Request))
#7 /Users/blumine/works/goodoc_v2/event/adminApi/vendor/aws/aws-sdk-php/src/ClientResolver.php(609): Aws\ClientSideMonitoring\AbstractMonitoringMiddleware->__invoke(Object(Aws\Command), Object(GuzzleHttp\Psr7\Request))
#8 /Users/blumine/works/goodoc_v2/event/adminApi/vendor/aws/aws-sdk-php/src/Middleware.php(96): Aws\ClientResolver::Aws\{closure}(Object(Aws\Command), Object(GuzzleHttp\Psr7\Request))
#9 /Users/blumine/works/goodoc_v2/event/adminApi/vendor/aws/aws-sdk-php/src/Middleware.php(80): Aws\Middleware::Aws\{closure}(Object(Aws\Command), NULL)
#10 /Users/blumine/works/goodoc_v2/event/adminApi/vendor/aws/aws-sdk-php/src/IdempotencyTokenMiddleware.php(77): Aws\Middleware::Aws\{closure}(Object(Aws\Command), NULL)
#11 /Users/blumine/works/goodoc_v2/event/adminApi/vendor/aws/aws-sdk-php/src/AwsClientTrait.php(64): Aws\IdempotencyTokenMiddleware->__invoke(Object(Aws\Command))
#12 /Users/blumine/works/goodoc_v2/event/adminApi/vendor/aws/aws-sdk-php/src/AwsClientTrait.php(58): Aws\AwsClient->executeAsync(Object(Aws\Command))
#13 /Users/blumine/works/goodoc_v2/event/adminApi/vendor/aws/aws-sdk-php/src/AwsClientTrait.php(77): Aws\AwsClient->execute(Object(Aws\Command))
#14 /Users/blumine/works/goodoc_v2/event/adminApi/application/libraries/EventUpload.php(153): Aws\AwsClient->__call('createInvalidat...', Array)
#15 /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/MartinTest.php(49): EventUpload->file_call('1', Array, 1)
#16 /Users/blumine/works/goodoc_v2/event/adminApi/system/core/CodeIgniter.php(532): MartinTest->index()
#17 /Users/blumine/works/goodoc_v2/event/adminApi/index.php(341): require_once('/Users/blumine/...')
#18 {main}
    
ERROR - 2019-12-12 08:59:26 --> Severity: error --> Exception: Cannot read credentials from /Users/blumine/.aws/credentials /Users/blumine/works/goodoc_v2/event/adminApi/vendor/aws/aws-sdk-php/src/Credentials/CredentialProvider.php 399
ERROR - 2019-12-12 08:59:26 --> Severity: Warning --> Cannot modify header information - headers already sent by (output started at /Users/blumine/works/goodoc_v2/event/adminApi/application/helpers/common_helper.php:72) /Users/blumine/works/goodoc_v2/event/adminApi/system/core/Common.php 570
ERROR - 2019-12-12 08:59:54 --> 
        Exception of type \'Aws\Exception\CredentialsException\' occurred with Message: Cannot read credentials from /Users/blumine/.aws/credentials in File /Users/blumine/works/goodoc_v2/event/adminApi/vendor/aws/aws-sdk-php/src/Credentials/CredentialProvider.php at Line 399
        
 Backtrace 
.#0 /Users/blumine/works/goodoc_v2/event/adminApi/vendor/aws/aws-sdk-php/src/Credentials/CredentialProvider.php(295): Aws\Credentials\CredentialProvider::reject('Cannot read cre...')
#1 /Users/blumine/works/goodoc_v2/event/adminApi/vendor/aws/aws-sdk-php/src/Middleware.php(121): Aws\Credentials\CredentialProvider::Aws\Credentials\{closure}()
#2 /Users/blumine/works/goodoc_v2/event/adminApi/vendor/aws/aws-sdk-php/src/RetryMiddleware.php(264): Aws\Middleware::Aws\{closure}(Object(Aws\Command), Object(GuzzleHttp\Psr7\Request))
#3 /Users/blumine/works/goodoc_v2/event/adminApi/vendor/aws/aws-sdk-php/src/Middleware.php(206): Aws\RetryMiddleware->__invoke(Object(Aws\Command), Object(GuzzleHttp\Psr7\Request))
#4 /Users/blumine/works/goodoc_v2/event/adminApi/vendor/aws/aws-sdk-php/src/EndpointDiscovery/EndpointDiscoveryMiddleware.php(165): Aws\Middleware::Aws\{closure}(Object(Aws\Command), Object(GuzzleHttp\Psr7\Request))
#5 /Users/blumine/works/goodoc_v2/event/adminApi/vendor/aws/aws-sdk-php/src/EndpointParameterMiddleware.php(82): Aws\EndpointDiscovery\EndpointDiscoveryMiddleware->__invoke(Object(Aws\Command), Object(GuzzleHttp\Psr7\Request))
#6 /Users/blumine/works/goodoc_v2/event/adminApi/vendor/aws/aws-sdk-php/src/ClientSideMonitoring/AbstractMonitoringMiddleware.php(126): Aws\EndpointParameterMiddleware->__invoke(Object(Aws\Command), Object(GuzzleHttp\Psr7\Request))
#7 /Users/blumine/works/goodoc_v2/event/adminApi/vendor/aws/aws-sdk-php/src/ClientResolver.php(609): Aws\ClientSideMonitoring\AbstractMonitoringMiddleware->__invoke(Object(Aws\Command), Object(GuzzleHttp\Psr7\Request))
#8 /Users/blumine/works/goodoc_v2/event/adminApi/vendor/aws/aws-sdk-php/src/Middleware.php(96): Aws\ClientResolver::Aws\{closure}(Object(Aws\Command), Object(GuzzleHttp\Psr7\Request))
#9 /Users/blumine/works/goodoc_v2/event/adminApi/vendor/aws/aws-sdk-php/src/Middleware.php(80): Aws\Middleware::Aws\{closure}(Object(Aws\Command), NULL)
#10 /Users/blumine/works/goodoc_v2/event/adminApi/vendor/aws/aws-sdk-php/src/IdempotencyTokenMiddleware.php(77): Aws\Middleware::Aws\{closure}(Object(Aws\Command), NULL)
#11 /Users/blumine/works/goodoc_v2/event/adminApi/vendor/aws/aws-sdk-php/src/AwsClientTrait.php(64): Aws\IdempotencyTokenMiddleware->__invoke(Object(Aws\Command))
#12 /Users/blumine/works/goodoc_v2/event/adminApi/vendor/aws/aws-sdk-php/src/AwsClientTrait.php(58): Aws\AwsClient->executeAsync(Object(Aws\Command))
#13 /Users/blumine/works/goodoc_v2/event/adminApi/vendor/aws/aws-sdk-php/src/AwsClientTrait.php(77): Aws\AwsClient->execute(Object(Aws\Command))
#14 /Users/blumine/works/goodoc_v2/event/adminApi/application/libraries/EventUpload.php(153): Aws\AwsClient->__call('createInvalidat...', Array)
#15 /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/MartinTest.php(49): EventUpload->file_call('1', Array, 1)
#16 /Users/blumine/works/goodoc_v2/event/adminApi/system/core/CodeIgniter.php(532): MartinTest->index()
#17 /Users/blumine/works/goodoc_v2/event/adminApi/index.php(341): require_once('/Users/blumine/...')
#18 {main}
    
ERROR - 2019-12-12 08:59:54 --> Severity: error --> Exception: Cannot read credentials from /Users/blumine/.aws/credentials /Users/blumine/works/goodoc_v2/event/adminApi/vendor/aws/aws-sdk-php/src/Credentials/CredentialProvider.php 399
ERROR - 2019-12-12 08:59:54 --> Severity: Warning --> Cannot modify header information - headers already sent by (output started at /Users/blumine/works/goodoc_v2/event/adminApi/application/helpers/common_helper.php:72) /Users/blumine/works/goodoc_v2/event/adminApi/system/core/Common.php 570
ERROR - 2019-12-12 09:09:11 --> 
        Exception of type \'Aws\Exception\UnresolvedApiException\' occurred with Message: The cloudfront service does not have version: 2019-12-12. in File /Users/blumine/works/goodoc_v2/event/adminApi/vendor/aws/aws-sdk-php/src/Api/ApiProvider.php at Line 85
        
 Backtrace 
.#0 /Users/blumine/works/goodoc_v2/event/adminApi/vendor/aws/aws-sdk-php/src/ClientResolver.php(443): Aws\Api\ApiProvider::resolve(Object(Aws\Api\ApiProvider), 'api', 'cloudfront', '2019-12-12')
#1 /Users/blumine/works/goodoc_v2/event/adminApi/vendor/aws/aws-sdk-php/src/ClientResolver.php(304): Aws\ClientResolver::_apply_api_provider(Object(Aws\Api\ApiProvider), Array, Object(Aws\HandlerList))
#2 /Users/blumine/works/goodoc_v2/event/adminApi/vendor/aws/aws-sdk-php/src/AwsClient.php(174): Aws\ClientResolver->resolve(Array, Object(Aws\HandlerList))
#3 /Users/blumine/works/goodoc_v2/event/adminApi/application/libraries/EventUpload.php(141): Aws\AwsClient->__construct(Array)
#4 /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/MartinTest.php(49): EventUpload->file_call('1', Array, 1)
#5 /Users/blumine/works/goodoc_v2/event/adminApi/system/core/CodeIgniter.php(532): MartinTest->index()
#6 /Users/blumine/works/goodoc_v2/event/adminApi/index.php(341): require_once('/Users/blumine/...')
#7 {main}
    
ERROR - 2019-12-12 09:09:11 --> Severity: error --> Exception: The cloudfront service does not have version: 2019-12-12. /Users/blumine/works/goodoc_v2/event/adminApi/vendor/aws/aws-sdk-php/src/Api/ApiProvider.php 85
ERROR - 2019-12-12 09:09:11 --> Severity: Warning --> Cannot modify header information - headers already sent by (output started at /Users/blumine/works/goodoc_v2/event/adminApi/application/helpers/common_helper.php:72) /Users/blumine/works/goodoc_v2/event/adminApi/system/core/Common.php 570
ERROR - 2019-12-12 09:09:47 --> 
        Exception of type \'Aws\Exception\CredentialsException\' occurred with Message: Error retrieving credentials from the instance profile metadata server. (cURL error 28: Connection timed out after 1001 milliseconds (see http://curl.haxx.se/libcurl/c/libcurl-errors.html)) in File /Users/blumine/works/goodoc_v2/event/adminApi/vendor/aws/aws-sdk-php/src/Credentials/InstanceProfileProvider.php at Line 95
        
 Backtrace 
.#0 /Users/blumine/works/goodoc_v2/event/adminApi/vendor/guzzlehttp/promises/src/Promise.php(203): Aws\Credentials\InstanceProfileProvider->Aws\Credentials\{closure}(Object(GuzzleHttp\Exception\ConnectException))
#1 /Users/blumine/works/goodoc_v2/event/adminApi/vendor/guzzlehttp/promises/src/Promise.php(156): GuzzleHttp\Promise\Promise::callHandler(2, Array, Array)
#2 /Users/blumine/works/goodoc_v2/event/adminApi/vendor/guzzlehttp/promises/src/TaskQueue.php(47): GuzzleHttp\Promise\Promise::GuzzleHttp\Promise\{closure}()
#3 /Users/blumine/works/goodoc_v2/event/adminApi/vendor/guzzlehttp/guzzle/src/Handler/CurlMultiHandler.php(98): GuzzleHttp\Promise\TaskQueue->run()
#4 /Users/blumine/works/goodoc_v2/event/adminApi/vendor/guzzlehttp/guzzle/src/Handler/CurlMultiHandler.php(125): GuzzleHttp\Handler\CurlMultiHandler->tick()
#5 /Users/blumine/works/goodoc_v2/event/adminApi/vendor/guzzlehttp/promises/src/Promise.php(246): GuzzleHttp\Handler\CurlMultiHandler->execute(true)
#6 /Users/blumine/works/goodoc_v2/event/adminApi/vendor/guzzlehttp/promises/src/Promise.php(223): GuzzleHttp\Promise\Promise->invokeWaitFn()
#7 /Users/blumine/works/goodoc_v2/event/adminApi/vendor/guzzlehttp/promises/src/Promise.php(267): GuzzleHttp\Promise\Promise->waitIfPending()
#8 /Users/blumine/works/goodoc_v2/event/adminApi/vendor/guzzlehttp/promises/src/Promise.php(225): GuzzleHttp\Promise\Promise->invokeWaitList()
#9 /Users/blumine/works/goodoc_v2/event/adminApi/vendor/guzzlehttp/promises/src/Promise.php(62): GuzzleHttp\Promise\Promise->waitIfPending()
#10 /Users/blumine/works/goodoc_v2/event/adminApi/vendor/guzzlehttp/promises/src/Coroutine.php(65): GuzzleHttp\Promise\Promise->wait()
#11 /Users/blumine/works/goodoc_v2/event/adminApi/vendor/guzzlehttp/promises/src/Promise.php(246): GuzzleHttp\Promise\Coroutine->GuzzleHttp\Promise\{closure}(true)
#12 /Users/blumine/works/goodoc_v2/event/adminApi/vendor/guzzlehttp/promises/src/Promise.php(223): GuzzleHttp\Promise\Promise->invokeWaitFn()
#13 /Users/blumine/works/goodoc_v2/event/adminApi/vendor/guzzlehttp/promises/src/Promise.php(62): GuzzleHttp\Promise\Promise->waitIfPending()
#14 /Users/blumine/works/goodoc_v2/event/adminApi/vendor/guzzlehttp/promises/src/Coroutine.php(85): GuzzleHttp\Promise\Promise->wait(false)
#15 /Users/blumine/works/goodoc_v2/event/adminApi/vendor/guzzlehttp/promises/src/Promise.php(273): GuzzleHttp\Promise\Coroutine->wait(false)
#16 /Users/blumine/works/goodoc_v2/event/adminApi/vendor/guzzlehttp/promises/src/Promise.php(225): GuzzleHttp\Promise\Promise->invokeWaitList()
#17 /Users/blumine/works/goodoc_v2/event/adminApi/vendor/guzzlehttp/promises/src/Promise.php(62): GuzzleHttp\Promise\Promise->waitIfPending()
#18 /Users/blumine/works/goodoc_v2/event/adminApi/vendor/aws/aws-sdk-php/src/AwsClientTrait.php(58): GuzzleHttp\Promise\Promise->wait()
#19 /Users/blumine/works/goodoc_v2/event/adminApi/vendor/aws/aws-sdk-php/src/AwsClientTrait.php(77): Aws\AwsClient->execute(Object(Aws\Command))
#20 /Users/blumine/works/goodoc_v2/event/adminApi/application/libraries/EventUpload.php(153): Aws\AwsClient->__call('createInvalidat...', Array)
#21 /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/MartinTest.php(49): EventUpload->file_call('1', Array, 1)
#22 /Users/blumine/works/goodoc_v2/event/adminApi/system/core/CodeIgniter.php(532): MartinTest->index()
#23 /Users/blumine/works/goodoc_v2/event/adminApi/index.php(341): require_once('/Users/blumine/...')
#24 {main}
    
ERROR - 2019-12-12 09:09:47 --> Severity: error --> Exception: Error retrieving credentials from the instance profile metadata server. (cURL error 28: Connection timed out after 1001 milliseconds (see http://curl.haxx.se/libcurl/c/libcurl-errors.html)) /Users/blumine/works/goodoc_v2/event/adminApi/vendor/aws/aws-sdk-php/src/Credentials/InstanceProfileProvider.php 95
ERROR - 2019-12-12 09:09:47 --> Severity: Warning --> Cannot modify header information - headers already sent by (output started at /Users/blumine/works/goodoc_v2/event/adminApi/application/helpers/common_helper.php:72) /Users/blumine/works/goodoc_v2/event/adminApi/system/core/Common.php 570
