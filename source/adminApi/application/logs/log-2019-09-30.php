<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

ERROR - 2019-09-30 04:59:19 --> Severity: Notice --> Array to string conversion /Users/blumine/works/goodoc_v2/event/adminApi/application/helpers/common_helper.php 116
ERROR - 2019-09-30 06:27:40 --> 
        Exception of type \'GuzzleHttp\Exception\ConnectException\' occurred with Message: cURL error 7: Failed to connect to 192.168.30.71 port 9020: Operation timed out (see http://curl.haxx.se/libcurl/c/libcurl-errors.html) in File /Users/blumine/works/goodoc_v2/event/adminApi/vendor/guzzlehttp/guzzle/src/Handler/CurlFactory.php at Line 185
        
 Backtrace 
.#0 /Users/blumine/works/goodoc_v2/event/adminApi/vendor/guzzlehttp/guzzle/src/Handler/CurlFactory.php(149): GuzzleHttp\Handler\CurlFactory::createRejection(Object(GuzzleHttp\Handler\EasyHandle), Array)
#1 /Users/blumine/works/goodoc_v2/event/adminApi/vendor/guzzlehttp/guzzle/src/Handler/CurlFactory.php(102): GuzzleHttp\Handler\CurlFactory::finishError(Object(GuzzleHttp\Handler\CurlHandler), Object(GuzzleHttp\Handler\EasyHandle), Object(GuzzleHttp\Handler\CurlFactory))
#2 /Users/blumine/works/goodoc_v2/event/adminApi/vendor/guzzlehttp/guzzle/src/Handler/CurlHandler.php(43): GuzzleHttp\Handler\CurlFactory::finish(Object(GuzzleHttp\Handler\CurlHandler), Object(GuzzleHttp\Handler\EasyHandle), Object(GuzzleHttp\Handler\CurlFactory))
#3 /Users/blumine/works/goodoc_v2/event/adminApi/vendor/guzzlehttp/guzzle/src/Handler/Proxy.php(28): GuzzleHttp\Handler\CurlHandler->__invoke(Object(GuzzleHttp\Psr7\Request), Array)
#4 /Users/blumine/works/goodoc_v2/event/adminApi/vendor/guzzlehttp/guzzle/src/Handler/Proxy.php(51): GuzzleHttp\Handler\Proxy::GuzzleHttp\Handler\{closure}(Object(GuzzleHttp\Psr7\Request), Array)
#5 /Users/blumine/works/goodoc_v2/event/adminApi/vendor/guzzlehttp/guzzle/src/PrepareBodyMiddleware.php(37): GuzzleHttp\Handler\Proxy::GuzzleHttp\Handler\{closure}(Object(GuzzleHttp\Psr7\Request), Array)
#6 /Users/blumine/works/goodoc_v2/event/adminApi/vendor/guzzlehttp/guzzle/src/Middleware.php(30): GuzzleHttp\PrepareBodyMiddleware->__invoke(Object(GuzzleHttp\Psr7\Request), Array)
#7 /Users/blumine/works/goodoc_v2/event/adminApi/vendor/guzzlehttp/guzzle/src/RedirectMiddleware.php(70): GuzzleHttp\Middleware::GuzzleHttp\{closure}(Object(GuzzleHttp\Psr7\Request), Array)
#8 /Users/blumine/works/goodoc_v2/event/adminApi/vendor/guzzlehttp/guzzle/src/Middleware.php(60): GuzzleHttp\RedirectMiddleware->__invoke(Object(GuzzleHttp\Psr7\Request), Array)
#9 /Users/blumine/works/goodoc_v2/event/adminApi/vendor/guzzlehttp/guzzle/src/HandlerStack.php(67): GuzzleHttp\Middleware::GuzzleHttp\{closure}(Object(GuzzleHttp\Psr7\Request), Array)
#10 /Users/blumine/works/goodoc_v2/event/adminApi/vendor/guzzlehttp/guzzle/src/Client.php(277): GuzzleHttp\HandlerStack->__invoke(Object(GuzzleHttp\Psr7\Request), Array)
#11 /Users/blumine/works/goodoc_v2/event/adminApi/vendor/guzzlehttp/guzzle/src/Client.php(125): GuzzleHttp\Client->transfer(Object(GuzzleHttp\Psr7\Request), Array)
#12 /Users/blumine/works/goodoc_v2/event/adminApi/vendor/guzzlehttp/guzzle/src/Client.php(131): GuzzleHttp\Client->requestAsync('POST', Object(GuzzleHttp\Psr7\Uri), Array)
#13 /Users/blumine/works/goodoc_v2/event/adminApi/application/helpers/common_helper.php(119): GuzzleHttp\Client->request('POST', 'http://192.168....', Array)
#14 /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/MartinTest.php(32): searchLoggerSend('U', Array)
#15 /Users/blumine/works/goodoc_v2/event/adminApi/system/core/CodeIgniter.php(532): MartinTest->index()
#16 /Users/blumine/works/goodoc_v2/event/adminApi/index.php(341): require_once('/Users/blumine/...')
#17 {main}
    
ERROR - 2019-09-30 06:27:41 --> Severity: error --> Exception: cURL error 7: Failed to connect to 192.168.30.71 port 9020: Operation timed out (see http://curl.haxx.se/libcurl/c/libcurl-errors.html) /Users/blumine/works/goodoc_v2/event/adminApi/vendor/guzzlehttp/guzzle/src/Handler/CurlFactory.php 185
ERROR - 2019-09-30 06:29:44 --> 
        Exception of type \'InvalidArgumentException\' occurred with Message: URI must be a string or UriInterface in File /Users/blumine/works/goodoc_v2/event/adminApi/vendor/guzzlehttp/psr7/src/functions.php at Line 62
        
 Backtrace 
.#0 /Users/blumine/works/goodoc_v2/event/adminApi/vendor/guzzlehttp/guzzle/src/Client.php(144): GuzzleHttp\Psr7\uri_for(Array)
#1 /Users/blumine/works/goodoc_v2/event/adminApi/vendor/guzzlehttp/guzzle/src/Client.php(117): GuzzleHttp\Client->buildUri(Array, Array)
#2 /Users/blumine/works/goodoc_v2/event/adminApi/vendor/guzzlehttp/guzzle/src/Client.php(131): GuzzleHttp\Client->requestAsync('POST', Array, Array)
#3 /Users/blumine/works/goodoc_v2/event/adminApi/application/helpers/common_helper.php(118): GuzzleHttp\Client->request('POST', Array)
#4 /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/MartinTest.php(32): searchLoggerSend('U', Array)
#5 /Users/blumine/works/goodoc_v2/event/adminApi/system/core/CodeIgniter.php(532): MartinTest->index()
#6 /Users/blumine/works/goodoc_v2/event/adminApi/index.php(341): require_once('/Users/blumine/...')
#7 {main}
    
ERROR - 2019-09-30 06:29:45 --> Severity: error --> Exception: URI must be a string or UriInterface /Users/blumine/works/goodoc_v2/event/adminApi/vendor/guzzlehttp/psr7/src/functions.php 62
ERROR - 2019-09-30 06:30:09 --> 
        Exception of type \'InvalidArgumentException\' occurred with Message: URI must be a string or UriInterface in File /Users/blumine/works/goodoc_v2/event/adminApi/vendor/guzzlehttp/psr7/src/functions.php at Line 62
        
 Backtrace 
.#0 /Users/blumine/works/goodoc_v2/event/adminApi/vendor/guzzlehttp/guzzle/src/Client.php(144): GuzzleHttp\Psr7\uri_for(Array)
#1 /Users/blumine/works/goodoc_v2/event/adminApi/vendor/guzzlehttp/guzzle/src/Client.php(117): GuzzleHttp\Client->buildUri(Array, Array)
#2 /Users/blumine/works/goodoc_v2/event/adminApi/vendor/guzzlehttp/guzzle/src/Client.php(131): GuzzleHttp\Client->requestAsync('POST', Array, Array)
#3 /Users/blumine/works/goodoc_v2/event/adminApi/application/helpers/common_helper.php(118): GuzzleHttp\Client->request('POST', Array)
#4 /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/MartinTest.php(32): searchLoggerSend('U', Array)
#5 /Users/blumine/works/goodoc_v2/event/adminApi/system/core/CodeIgniter.php(532): MartinTest->index()
#6 /Users/blumine/works/goodoc_v2/event/adminApi/index.php(341): require_once('/Users/blumine/...')
#7 {main}
    
ERROR - 2019-09-30 06:30:09 --> Severity: error --> Exception: URI must be a string or UriInterface /Users/blumine/works/goodoc_v2/event/adminApi/vendor/guzzlehttp/psr7/src/functions.php 62
ERROR - 2019-09-30 06:30:20 --> 
        Exception of type \'InvalidArgumentException\' occurred with Message: URI must be a string or UriInterface in File /Users/blumine/works/goodoc_v2/event/adminApi/vendor/guzzlehttp/psr7/src/functions.php at Line 62
        
 Backtrace 
.#0 /Users/blumine/works/goodoc_v2/event/adminApi/vendor/guzzlehttp/guzzle/src/Client.php(144): GuzzleHttp\Psr7\uri_for(Array)
#1 /Users/blumine/works/goodoc_v2/event/adminApi/vendor/guzzlehttp/guzzle/src/Client.php(117): GuzzleHttp\Client->buildUri(Array, Array)
#2 /Users/blumine/works/goodoc_v2/event/adminApi/vendor/guzzlehttp/guzzle/src/Client.php(131): GuzzleHttp\Client->requestAsync('POST', Array, Array)
#3 /Users/blumine/works/goodoc_v2/event/adminApi/application/helpers/common_helper.php(118): GuzzleHttp\Client->request('POST', Array)
#4 /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/MartinTest.php(32): searchLoggerSend('U', Array)
#5 /Users/blumine/works/goodoc_v2/event/adminApi/system/core/CodeIgniter.php(532): MartinTest->index()
#6 /Users/blumine/works/goodoc_v2/event/adminApi/index.php(341): require_once('/Users/blumine/...')
#7 {main}
    
ERROR - 2019-09-30 06:30:20 --> Severity: error --> Exception: URI must be a string or UriInterface /Users/blumine/works/goodoc_v2/event/adminApi/vendor/guzzlehttp/psr7/src/functions.php 62
ERROR - 2019-09-30 06:31:05 --> 
        Exception of type \'InvalidArgumentException\' occurred with Message: URI must be a string or UriInterface in File /Users/blumine/works/goodoc_v2/event/adminApi/vendor/guzzlehttp/psr7/src/functions.php at Line 62
        
 Backtrace 
.#0 /Users/blumine/works/goodoc_v2/event/adminApi/vendor/guzzlehttp/guzzle/src/Client.php(144): GuzzleHttp\Psr7\uri_for(Array)
#1 /Users/blumine/works/goodoc_v2/event/adminApi/vendor/guzzlehttp/guzzle/src/Client.php(117): GuzzleHttp\Client->buildUri(Array, Array)
#2 /Users/blumine/works/goodoc_v2/event/adminApi/vendor/guzzlehttp/guzzle/src/Client.php(131): GuzzleHttp\Client->requestAsync('POST', Array, Array)
#3 /Users/blumine/works/goodoc_v2/event/adminApi/application/helpers/common_helper.php(118): GuzzleHttp\Client->request('POST', Array)
#4 /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/MartinTest.php(32): searchLoggerSend('U', Array)
#5 /Users/blumine/works/goodoc_v2/event/adminApi/system/core/CodeIgniter.php(532): MartinTest->index()
#6 /Users/blumine/works/goodoc_v2/event/adminApi/index.php(341): require_once('/Users/blumine/...')
#7 {main}
    
ERROR - 2019-09-30 06:31:06 --> Severity: error --> Exception: URI must be a string or UriInterface /Users/blumine/works/goodoc_v2/event/adminApi/vendor/guzzlehttp/psr7/src/functions.php 62
ERROR - 2019-09-30 06:33:16 --> 
        Exception of type \'GuzzleHttp\Exception\ConnectException\' occurred with Message: cURL error 7: Failed to connect to 192.168.30.71 port 9020: Operation timed out (see http://curl.haxx.se/libcurl/c/libcurl-errors.html) in File /Users/blumine/works/goodoc_v2/event/adminApi/vendor/guzzlehttp/guzzle/src/Handler/CurlFactory.php at Line 185
        
 Backtrace 
.#0 /Users/blumine/works/goodoc_v2/event/adminApi/vendor/guzzlehttp/guzzle/src/Handler/CurlFactory.php(149): GuzzleHttp\Handler\CurlFactory::createRejection(Object(GuzzleHttp\Handler\EasyHandle), Array)
#1 /Users/blumine/works/goodoc_v2/event/adminApi/vendor/guzzlehttp/guzzle/src/Handler/CurlFactory.php(102): GuzzleHttp\Handler\CurlFactory::finishError(Object(GuzzleHttp\Handler\CurlHandler), Object(GuzzleHttp\Handler\EasyHandle), Object(GuzzleHttp\Handler\CurlFactory))
#2 /Users/blumine/works/goodoc_v2/event/adminApi/vendor/guzzlehttp/guzzle/src/Handler/CurlHandler.php(43): GuzzleHttp\Handler\CurlFactory::finish(Object(GuzzleHttp\Handler\CurlHandler), Object(GuzzleHttp\Handler\EasyHandle), Object(GuzzleHttp\Handler\CurlFactory))
#3 /Users/blumine/works/goodoc_v2/event/adminApi/vendor/guzzlehttp/guzzle/src/Handler/Proxy.php(28): GuzzleHttp\Handler\CurlHandler->__invoke(Object(GuzzleHttp\Psr7\Request), Array)
#4 /Users/blumine/works/goodoc_v2/event/adminApi/vendor/guzzlehttp/guzzle/src/Handler/Proxy.php(51): GuzzleHttp\Handler\Proxy::GuzzleHttp\Handler\{closure}(Object(GuzzleHttp\Psr7\Request), Array)
#5 /Users/blumine/works/goodoc_v2/event/adminApi/vendor/guzzlehttp/guzzle/src/PrepareBodyMiddleware.php(37): GuzzleHttp\Handler\Proxy::GuzzleHttp\Handler\{closure}(Object(GuzzleHttp\Psr7\Request), Array)
#6 /Users/blumine/works/goodoc_v2/event/adminApi/vendor/guzzlehttp/guzzle/src/Middleware.php(30): GuzzleHttp\PrepareBodyMiddleware->__invoke(Object(GuzzleHttp\Psr7\Request), Array)
#7 /Users/blumine/works/goodoc_v2/event/adminApi/vendor/guzzlehttp/guzzle/src/RedirectMiddleware.php(70): GuzzleHttp\Middleware::GuzzleHttp\{closure}(Object(GuzzleHttp\Psr7\Request), Array)
#8 /Users/blumine/works/goodoc_v2/event/adminApi/vendor/guzzlehttp/guzzle/src/Middleware.php(60): GuzzleHttp\RedirectMiddleware->__invoke(Object(GuzzleHttp\Psr7\Request), Array)
#9 /Users/blumine/works/goodoc_v2/event/adminApi/vendor/guzzlehttp/guzzle/src/HandlerStack.php(67): GuzzleHttp\Middleware::GuzzleHttp\{closure}(Object(GuzzleHttp\Psr7\Request), Array)
#10 /Users/blumine/works/goodoc_v2/event/adminApi/vendor/guzzlehttp/guzzle/src/Client.php(277): GuzzleHttp\HandlerStack->__invoke(Object(GuzzleHttp\Psr7\Request), Array)
#11 /Users/blumine/works/goodoc_v2/event/adminApi/vendor/guzzlehttp/guzzle/src/Client.php(125): GuzzleHttp\Client->transfer(Object(GuzzleHttp\Psr7\Request), Array)
#12 /Users/blumine/works/goodoc_v2/event/adminApi/vendor/guzzlehttp/guzzle/src/Client.php(131): GuzzleHttp\Client->requestAsync('POST', Object(GuzzleHttp\Psr7\Uri), Array)
#13 /Users/blumine/works/goodoc_v2/event/adminApi/application/helpers/common_helper.php(118): GuzzleHttp\Client->request('POST', '/api/v2/search/...', Array)
#14 /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/MartinTest.php(32): searchLoggerSend('U', Array)
#15 /Users/blumine/works/goodoc_v2/event/adminApi/system/core/CodeIgniter.php(532): MartinTest->index()
#16 /Users/blumine/works/goodoc_v2/event/adminApi/index.php(341): require_once('/Users/blumine/...')
#17 {main}
    
ERROR - 2019-09-30 06:33:16 --> Severity: error --> Exception: cURL error 7: Failed to connect to 192.168.30.71 port 9020: Operation timed out (see http://curl.haxx.se/libcurl/c/libcurl-errors.html) /Users/blumine/works/goodoc_v2/event/adminApi/vendor/guzzlehttp/guzzle/src/Handler/CurlFactory.php 185
ERROR - 2019-09-30 06:53:21 --> 
        Exception of type \'InvalidArgumentException\' occurred with Message: URI must be a string or UriInterface in File /Users/blumine/works/goodoc_v2/event/adminApi/vendor/guzzlehttp/psr7/src/functions.php at Line 62
        
 Backtrace 
.#0 /Users/blumine/works/goodoc_v2/event/adminApi/vendor/guzzlehttp/guzzle/src/Client.php(144): GuzzleHttp\Psr7\uri_for(Array)
#1 /Users/blumine/works/goodoc_v2/event/adminApi/vendor/guzzlehttp/guzzle/src/Client.php(117): GuzzleHttp\Client->buildUri(Array, Array)
#2 /Users/blumine/works/goodoc_v2/event/adminApi/vendor/guzzlehttp/guzzle/src/Client.php(131): GuzzleHttp\Client->requestAsync('POST', Array, Array)
#3 /Users/blumine/works/goodoc_v2/event/adminApi/application/helpers/common_helper.php(118): GuzzleHttp\Client->request('POST', Array)
#4 /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/MartinTest.php(32): searchLoggerSend('U', Array)
#5 /Users/blumine/works/goodoc_v2/event/adminApi/system/core/CodeIgniter.php(532): MartinTest->index()
#6 /Users/blumine/works/goodoc_v2/event/adminApi/index.php(341): require_once('/Users/blumine/...')
#7 {main}
    
ERROR - 2019-09-30 06:53:22 --> Severity: error --> Exception: URI must be a string or UriInterface /Users/blumine/works/goodoc_v2/event/adminApi/vendor/guzzlehttp/psr7/src/functions.php 62
ERROR - 2019-09-30 06:55:00 --> 
        Exception of type \'GuzzleHttp\Exception\ConnectException\' occurred with Message: cURL error 7: Failed to connect to 192.168.30.171 port 9020: Connection refused (see http://curl.haxx.se/libcurl/c/libcurl-errors.html) in File /Users/blumine/works/goodoc_v2/event/adminApi/vendor/guzzlehttp/guzzle/src/Handler/CurlFactory.php at Line 185
        
 Backtrace 
.#0 /Users/blumine/works/goodoc_v2/event/adminApi/vendor/guzzlehttp/guzzle/src/Handler/CurlFactory.php(149): GuzzleHttp\Handler\CurlFactory::createRejection(Object(GuzzleHttp\Handler\EasyHandle), Array)
#1 /Users/blumine/works/goodoc_v2/event/adminApi/vendor/guzzlehttp/guzzle/src/Handler/CurlFactory.php(102): GuzzleHttp\Handler\CurlFactory::finishError(Object(GuzzleHttp\Handler\CurlHandler), Object(GuzzleHttp\Handler\EasyHandle), Object(GuzzleHttp\Handler\CurlFactory))
#2 /Users/blumine/works/goodoc_v2/event/adminApi/vendor/guzzlehttp/guzzle/src/Handler/CurlHandler.php(43): GuzzleHttp\Handler\CurlFactory::finish(Object(GuzzleHttp\Handler\CurlHandler), Object(GuzzleHttp\Handler\EasyHandle), Object(GuzzleHttp\Handler\CurlFactory))
#3 /Users/blumine/works/goodoc_v2/event/adminApi/vendor/guzzlehttp/guzzle/src/Handler/Proxy.php(28): GuzzleHttp\Handler\CurlHandler->__invoke(Object(GuzzleHttp\Psr7\Request), Array)
#4 /Users/blumine/works/goodoc_v2/event/adminApi/vendor/guzzlehttp/guzzle/src/Handler/Proxy.php(51): GuzzleHttp\Handler\Proxy::GuzzleHttp\Handler\{closure}(Object(GuzzleHttp\Psr7\Request), Array)
#5 /Users/blumine/works/goodoc_v2/event/adminApi/vendor/guzzlehttp/guzzle/src/PrepareBodyMiddleware.php(37): GuzzleHttp\Handler\Proxy::GuzzleHttp\Handler\{closure}(Object(GuzzleHttp\Psr7\Request), Array)
#6 /Users/blumine/works/goodoc_v2/event/adminApi/vendor/guzzlehttp/guzzle/src/Middleware.php(30): GuzzleHttp\PrepareBodyMiddleware->__invoke(Object(GuzzleHttp\Psr7\Request), Array)
#7 /Users/blumine/works/goodoc_v2/event/adminApi/vendor/guzzlehttp/guzzle/src/RedirectMiddleware.php(70): GuzzleHttp\Middleware::GuzzleHttp\{closure}(Object(GuzzleHttp\Psr7\Request), Array)
#8 /Users/blumine/works/goodoc_v2/event/adminApi/vendor/guzzlehttp/guzzle/src/Middleware.php(60): GuzzleHttp\RedirectMiddleware->__invoke(Object(GuzzleHttp\Psr7\Request), Array)
#9 /Users/blumine/works/goodoc_v2/event/adminApi/vendor/guzzlehttp/guzzle/src/HandlerStack.php(67): GuzzleHttp\Middleware::GuzzleHttp\{closure}(Object(GuzzleHttp\Psr7\Request), Array)
#10 /Users/blumine/works/goodoc_v2/event/adminApi/vendor/guzzlehttp/guzzle/src/Client.php(277): GuzzleHttp\HandlerStack->__invoke(Object(GuzzleHttp\Psr7\Request), Array)
#11 /Users/blumine/works/goodoc_v2/event/adminApi/vendor/guzzlehttp/guzzle/src/Client.php(125): GuzzleHttp\Client->transfer(Object(GuzzleHttp\Psr7\Request), Array)
#12 /Users/blumine/works/goodoc_v2/event/adminApi/vendor/guzzlehttp/guzzle/src/Client.php(131): GuzzleHttp\Client->requestAsync('POST', Object(GuzzleHttp\Psr7\Uri), Array)
#13 /Users/blumine/works/goodoc_v2/event/adminApi/application/helpers/common_helper.php(118): GuzzleHttp\Client->request('POST', '/api/v2/search/...', Array)
#14 /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/MartinTest.php(32): searchLoggerSend('C', Array)
#15 /Users/blumine/works/goodoc_v2/event/adminApi/system/core/CodeIgniter.php(532): MartinTest->index()
#16 /Users/blumine/works/goodoc_v2/event/adminApi/index.php(341): require_once('/Users/blumine/...')
#17 {main}
    
ERROR - 2019-09-30 06:55:00 --> Severity: error --> Exception: cURL error 7: Failed to connect to 192.168.30.171 port 9020: Connection refused (see http://curl.haxx.se/libcurl/c/libcurl-errors.html) /Users/blumine/works/goodoc_v2/event/adminApi/vendor/guzzlehttp/guzzle/src/Handler/CurlFactory.php 185
ERROR - 2019-09-30 07:19:30 --> 
        Exception of type \'TypeError\' occurred with Message: Argument 3 passed to GuzzleHttp\Client::request() must be of the type array, string given, called in /Users/blumine/works/goodoc_v2/event/adminApi/application/helpers/common_helper.php on line 118 in File /Users/blumine/works/goodoc_v2/event/adminApi/vendor/guzzlehttp/guzzle/src/Client.php at Line 128
        
 Backtrace 
.#0 /Users/blumine/works/goodoc_v2/event/adminApi/application/helpers/common_helper.php(118): GuzzleHttp\Client->request('POST', 'http://192.168....', '{"serviceId":"1...', Array)
#1 /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/MartinTest.php(32): searchLoggerSend('C', Array)
#2 /Users/blumine/works/goodoc_v2/event/adminApi/system/core/CodeIgniter.php(532): MartinTest->index()
#3 /Users/blumine/works/goodoc_v2/event/adminApi/index.php(341): require_once('/Users/blumine/...')
#4 {main}
    
ERROR - 2019-09-30 07:19:31 --> Severity: error --> Exception: Argument 3 passed to GuzzleHttp\Client::request() must be of the type array, string given, called in /Users/blumine/works/goodoc_v2/event/adminApi/application/helpers/common_helper.php on line 118 /Users/blumine/works/goodoc_v2/event/adminApi/vendor/guzzlehttp/guzzle/src/Client.php 128
ERROR - 2019-09-30 07:19:48 --> 
        Exception of type \'TypeError\' occurred with Message: Argument 3 passed to GuzzleHttp\Client::request() must be of the type array, string given, called in /Users/blumine/works/goodoc_v2/event/adminApi/application/helpers/common_helper.php on line 118 in File /Users/blumine/works/goodoc_v2/event/adminApi/vendor/guzzlehttp/guzzle/src/Client.php at Line 128
        
 Backtrace 
.#0 /Users/blumine/works/goodoc_v2/event/adminApi/application/helpers/common_helper.php(118): GuzzleHttp\Client->request('POST', 'http://192.168....', '[{"serviceId":"...', Array)
#1 /Users/blumine/works/goodoc_v2/event/adminApi/application/controllers/MartinTest.php(32): searchLoggerSend('C', Array)
#2 /Users/blumine/works/goodoc_v2/event/adminApi/system/core/CodeIgniter.php(532): MartinTest->index()
#3 /Users/blumine/works/goodoc_v2/event/adminApi/index.php(341): require_once('/Users/blumine/...')
#4 {main}
    
ERROR - 2019-09-30 07:19:48 --> Severity: error --> Exception: Argument 3 passed to GuzzleHttp\Client::request() must be of the type array, string given, called in /Users/blumine/works/goodoc_v2/event/adminApi/application/helpers/common_helper.php on line 118 /Users/blumine/works/goodoc_v2/event/adminApi/vendor/guzzlehttp/guzzle/src/Client.php 128
