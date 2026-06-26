<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

ERROR - 2019-12-13 02:56:29 --> 
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
#20 /Users/blumine/works/goodoc_v2/event/adminApi/application/libraries/EventUpload.php(150): Aws\AwsClient->__call('createInvalidat...', Array)
#21 /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/MartinTest.php(49): EventUpload->file_call('1', Array, 1)
#22 /Users/blumine/works/goodoc_v2/event/adminApi/system/core/CodeIgniter.php(532): MartinTest->index()
#23 /Users/blumine/works/goodoc_v2/event/adminApi/index.php(341): require_once('/Users/blumine/...')
#24 {main}
    
ERROR - 2019-12-13 02:56:29 --> Severity: error --> Exception: Error retrieving credentials from the instance profile metadata server. (cURL error 28: Connection timed out after 1001 milliseconds (see http://curl.haxx.se/libcurl/c/libcurl-errors.html)) /Users/blumine/works/goodoc_v2/event/adminApi/vendor/aws/aws-sdk-php/src/Credentials/InstanceProfileProvider.php 95
ERROR - 2019-12-13 02:56:29 --> Severity: Warning --> Cannot modify header information - headers already sent by (output started at /Users/blumine/works/goodoc_v2/event/adminApi/application/helpers/common_helper.php:72) /Users/blumine/works/goodoc_v2/event/adminApi/system/core/Common.php 570
ERROR - 2019-12-13 03:25:32 --> 
        Exception of type \'Aws\Exception\CredentialsException\' occurred with Message: Error retrieving credentials from the instance profile metadata server. (cURL error 28: Connection timed out after 1000 milliseconds (see http://curl.haxx.se/libcurl/c/libcurl-errors.html)) in File /Users/blumine/works/goodoc_v2/event/adminApi/vendor/aws/aws-sdk-php/src/Credentials/InstanceProfileProvider.php at Line 95
        
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
#20 /Users/blumine/works/goodoc_v2/event/adminApi/application/libraries/EventUpload.php(151): Aws\AwsClient->__call('createInvalidat...', Array)
#21 /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/MartinTest.php(49): EventUpload->file_call('1', Array, 1)
#22 /Users/blumine/works/goodoc_v2/event/adminApi/system/core/CodeIgniter.php(532): MartinTest->index()
#23 /Users/blumine/works/goodoc_v2/event/adminApi/index.php(341): require_once('/Users/blumine/...')
#24 {main}
    
ERROR - 2019-12-13 03:25:32 --> Severity: error --> Exception: Error retrieving credentials from the instance profile metadata server. (cURL error 28: Connection timed out after 1000 milliseconds (see http://curl.haxx.se/libcurl/c/libcurl-errors.html)) /Users/blumine/works/goodoc_v2/event/adminApi/vendor/aws/aws-sdk-php/src/Credentials/InstanceProfileProvider.php 95
ERROR - 2019-12-13 03:26:30 --> 
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
#14 /Users/blumine/works/goodoc_v2/event/adminApi/application/libraries/EventUpload.php(151): Aws\AwsClient->__call('createInvalidat...', Array)
#15 /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/MartinTest.php(49): EventUpload->file_call('1', Array, 1)
#16 /Users/blumine/works/goodoc_v2/event/adminApi/system/core/CodeIgniter.php(532): MartinTest->index()
#17 /Users/blumine/works/goodoc_v2/event/adminApi/index.php(341): require_once('/Users/blumine/...')
#18 {main}
    
ERROR - 2019-12-13 03:26:30 --> Severity: error --> Exception: Cannot read credentials from /Users/blumine/.aws/credentials /Users/blumine/works/goodoc_v2/event/adminApi/vendor/aws/aws-sdk-php/src/Credentials/CredentialProvider.php 399
ERROR - 2019-12-13 03:29:20 --> 
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
#14 /Users/blumine/works/goodoc_v2/event/adminApi/application/libraries/EventUpload.php(155): Aws\AwsClient->__call('createInvalidat...', Array)
#15 /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/MartinTest.php(49): EventUpload->file_call('1', Array, 1)
#16 /Users/blumine/works/goodoc_v2/event/adminApi/system/core/CodeIgniter.php(532): MartinTest->index()
#17 /Users/blumine/works/goodoc_v2/event/adminApi/index.php(341): require_once('/Users/blumine/...')
#18 {main}
    
ERROR - 2019-12-13 03:29:20 --> Severity: error --> Exception: Cannot read credentials from /Users/blumine/.aws/credentials /Users/blumine/works/goodoc_v2/event/adminApi/vendor/aws/aws-sdk-php/src/Credentials/CredentialProvider.php 399
ERROR - 2019-12-13 04:59:07 --> 
        Exception of type \'Aws\Exception\UnresolvedApiException\' occurred with Message: The cloudfront service does not have version: 2019-03-26. in File /Users/blumine/works/goodoc_v2/event/adminApi/vendor/aws/aws-sdk-php/src/Api/ApiProvider.php at Line 85
        
 Backtrace 
.#0 /Users/blumine/works/goodoc_v2/event/adminApi/vendor/aws/aws-sdk-php/src/ClientResolver.php(443): Aws\Api\ApiProvider::resolve(Object(Aws\Api\ApiProvider), 'api', 'cloudfront', '2019-03-26')
#1 /Users/blumine/works/goodoc_v2/event/adminApi/vendor/aws/aws-sdk-php/src/ClientResolver.php(304): Aws\ClientResolver::_apply_api_provider(Object(Aws\Api\ApiProvider), Array, Object(Aws\HandlerList))
#2 /Users/blumine/works/goodoc_v2/event/adminApi/vendor/aws/aws-sdk-php/src/AwsClient.php(174): Aws\ClientResolver->resolve(Array, Object(Aws\HandlerList))
#3 /Users/blumine/works/goodoc_v2/event/adminApi/application/libraries/EventUpload.php(142): Aws\AwsClient->__construct(Array)
#4 /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/MartinTest.php(49): EventUpload->file_call('1', Array, 1)
#5 /Users/blumine/works/goodoc_v2/event/adminApi/system/core/CodeIgniter.php(532): MartinTest->index()
#6 /Users/blumine/works/goodoc_v2/event/adminApi/index.php(341): require_once('/Users/blumine/...')
#7 {main}
    
ERROR - 2019-12-13 04:59:07 --> Severity: error --> Exception: The cloudfront service does not have version: 2019-03-26. /Users/blumine/works/goodoc_v2/event/adminApi/vendor/aws/aws-sdk-php/src/Api/ApiProvider.php 85
