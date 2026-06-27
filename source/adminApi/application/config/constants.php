<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| Display Debug backtrace
|--------------------------------------------------------------------------
|
| If set to TRUE, a backtrace will be displayed along with php errors. If
| error_reporting is disabled, the backtrace will not display, regardless
| of this setting
|
*/
defined('SHOW_DEBUG_BACKTRACE') OR define('SHOW_DEBUG_BACKTRACE', TRUE);

/*
|--------------------------------------------------------------------------
| File and Directory Modes
|--------------------------------------------------------------------------
|
| These prefs are used when checking and setting modes when working
| with the file system.  The defaults are fine on servers with proper
| security, but you may wish (or even need) to change the values in
| certain environments (Apache running a separate process for each
| user, PHP under CGI with Apache suEXEC, etc.).  Octal values should
| always be used to set the mode correctly.
|
*/
defined('FILE_READ_MODE')  OR define('FILE_READ_MODE', 0644);
defined('FILE_WRITE_MODE') OR define('FILE_WRITE_MODE', 0666);
defined('DIR_READ_MODE')   OR define('DIR_READ_MODE', 0755);
defined('DIR_WRITE_MODE')  OR define('DIR_WRITE_MODE', 0755);

/*
|--------------------------------------------------------------------------
| File Stream Modes
|--------------------------------------------------------------------------
|
| These modes are used when working with fopen()/popen()
|
*/
defined('FOPEN_READ')                           OR define('FOPEN_READ', 'rb');
defined('FOPEN_READ_WRITE')                     OR define('FOPEN_READ_WRITE', 'r+b');
defined('FOPEN_WRITE_CREATE_DESTRUCTIVE')       OR define('FOPEN_WRITE_CREATE_DESTRUCTIVE', 'wb'); // truncates existing file data, use with care
defined('FOPEN_READ_WRITE_CREATE_DESTRUCTIVE')  OR define('FOPEN_READ_WRITE_CREATE_DESTRUCTIVE', 'w+b'); // truncates existing file data, use with care
defined('FOPEN_WRITE_CREATE')                   OR define('FOPEN_WRITE_CREATE', 'ab');
defined('FOPEN_READ_WRITE_CREATE')              OR define('FOPEN_READ_WRITE_CREATE', 'a+b');
defined('FOPEN_WRITE_CREATE_STRICT')            OR define('FOPEN_WRITE_CREATE_STRICT', 'xb');
defined('FOPEN_READ_WRITE_CREATE_STRICT')       OR define('FOPEN_READ_WRITE_CREATE_STRICT', 'x+b');

/*
|--------------------------------------------------------------------------
| Exit Status Codes
|--------------------------------------------------------------------------
|
| Used to indicate the conditions under which the script is exit()ing.
| While there is no universal standard for error codes, there are some
| broad conventions.  Three such conventions are mentioned below, for
| those who wish to make use of them.  The CodeIgniter defaults were
| chosen for the least overlap with these conventions, while still
| leaving room for others to be defined in future versions and user
| applications.
|
| The three main conventions used for determining exit status codes
| are as follows:
|
|    Standard C/C++ Library (stdlibc):
|       http://www.gnu.org/software/libc/manual/html_node/Exit-Status.html
|       (This link also contains other GNU-specific conventions)
|    BSD sysexits.h:
|       http://www.gsp.com/cgi-bin/man.cgi?section=3&topic=sysexits
|    Bash scripting:
|       http://tldp.org/LDP/abs/html/exitcodes.html
|
*/
defined('EXIT_SUCCESS')        OR define('EXIT_SUCCESS', 0); // no errors
defined('EXIT_ERROR')          OR define('EXIT_ERROR', 1); // generic error
defined('EXIT_CONFIG')         OR define('EXIT_CONFIG', 3); // configuration error
defined('EXIT_UNKNOWN_FILE')   OR define('EXIT_UNKNOWN_FILE', 4); // file not found
defined('EXIT_UNKNOWN_CLASS')  OR define('EXIT_UNKNOWN_CLASS', 5); // unknown class
defined('EXIT_UNKNOWN_METHOD') OR define('EXIT_UNKNOWN_METHOD', 6); // unknown class member
defined('EXIT_USER_INPUT')     OR define('EXIT_USER_INPUT', 7); // invalid user input
defined('EXIT_DATABASE')       OR define('EXIT_DATABASE', 8); // database error
defined('EXIT__AUTO_MIN')      OR define('EXIT__AUTO_MIN', 9); // lowest automatically-assigned error code
defined('EXIT__AUTO_MAX')      OR define('EXIT__AUTO_MAX', 125); // highest automatically-assigned error code


define('API_KEY', 'qJqKDATY0D48ZH9hCNW4N6ygHBqtoVz4a4mu0P6A');

if( ENVIRONMENT === 'development' )
{
    $data_root = dirname(FCPATH).'/adminApi/uploads';
    $up_dir = 'http://devevent.goodoc.kr';

    //재택용 설정
//    if($_SERVER['HTTP_HOST'] == 'ev.com')
//    {
//        define('AUTH_URL', 'https://stagingauth.goodoc.kr');
//    }
//    else
//    {
//        define('AUTH_URL', 'http://devauth.com');
//    }

    define('AUTH_URL', 'http://devauth.com');

    define('ORDER_URL', 'https://pgtest.settlebank.co.kr');
    define('ORDER_ID', 'mid_test');
    define('PG_KEY', 'ST1009281328226982205');
    define('REFUND_URL', 'https://test.settlebank.co.kr/pgtrans/CaprevMultiAction.do?_mothod=caprevReqFromMem');
    //define('REPLICATOR_URL', 'http://dev.goodoc.co.kr:3000'); //v1-v2 데이터 리플리케이터 주소. v2 전환후 사용안함
    //define('REPLICATOR_URL', 'http://staging.goodoc.co.kr');
    define('REPLICATOR_URL', 'http://staging.goodoc.co.kr');
    //define('HOSPITAL_URL', 'http://192.168.30.3:8080'); //병원 정보 API URL
    define('HOSPITAL_URL', 'https://stg-hospitalinfo.goodoc.kr'); //병원 정보 API URL
    define('USER_URL','https://heratest.goodoc.kr'); //유저 정보 API URL
}
else if( ENVIRONMENT === 'testing' )
{
    $data_root = dirname(FCPATH).'/current/uploads';
    $up_dir = 'https://stagingevent.goodoc.kr';

    define('AUTH_URL', 'https://stagingauth.goodoc.kr');

    define('ORDER_URL', 'https://pgtest.settlebank.co.kr');
    define('ORDER_ID', 'mid_test');
    define('PG_KEY', 'ST1009281328226982205');
    define('REFUND_URL', 'https://test.settlebank.co.kr/pgtrans/CaprevMultiAction.do?_mothod=caprevReqFromMem');
    define('REPLICATOR_URL', 'http://staging.goodoc.co.kr');
    define('HOSPITAL_URL', 'https://stg-hospitalinfo.goodoc.kr'); //병원 정보 API URL
    define('USER_URL','https://heratest.goodoc.kr'); //유저 정보 API URL
}
else
{
    $data_root = dirname(FCPATH).'/current/uploads';
    $up_dir = 'https://event.goodoc.kr'; //실서버 주소 아직 확정 아님
    
    define('AUTH_URL', 'https://auth.goodoc.kr');

    define('ORDER_URL', 'https://pg.settlebank.co.kr');
    define('ORDER_ID', 'goodocdev'); //실제 아이디
    define('PG_KEY', 'ST1811091110407219283'); //실제 키
    define('REFUND_URL', 'https://www.settlebank.co.kr/pgtrans/CaprevMultiAction.do?_mothod=caprevReqFromMem');
    //define('REPLICATOR_URL', 'http://www.goodoc.co.kr');
    define('REPLICATOR_URL', 'http://52.199.55.196');
    define('HOSPITAL_URL', 'https://prd-hospitalinfo.goodoc.kr'); //병원 정보 API URL
    define('USER_URL','https://hera.goodoc.kr'); //유저 정보 API URL
}

//세틀뱅크 호출주소
define('CARD_URL', '/card/NewCardAction.do');
define('VBANK_URL', '/vbank/NewVBankAction.do');

define('UP_ROOT', $data_root);
define('UP_ROOT2', dirname(FCPATH).'/adminApi');
define('SITE_URL', $up_dir);

define('S3Key' , 'AKIAI6FNACJZZKT4IZLQ');
define('S3Secret', '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');

define('CFKey' , 'AKIAU4M33J2OCNQFVWAR');
define('CFSecret', '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');