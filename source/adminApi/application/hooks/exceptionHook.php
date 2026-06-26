<?php
use AdminApi\Libraries\Guzzleutil as Guzzleutil;

function setExceptionHandler()
{
    set_exception_handler(['ExceptionSendDm', 'handleExceptions']);
}

/**
 * 빈센트 개인 슬랙 DM으로 PHP 에러 정보 전달
 */
class ExceptionSendDm extends Guzzleutil
{
    private static $msgKeyErr = [
        'date', 'route', 'queryString', 'post', 'errorInfo'
    ];

    private static $msg = "
        Exception of type \'{class}\' occurred with Message: {message} in File {file} at Line {line}
        "."\r\n Backtrace \r\n."."{backtrace}
    ";

    /**
     * PHP 에러 메시지 발송
     * @param  Exception
     * @return _exception_handler
     */
    public static function handleExceptions($exception) 
    {
        $error = [];    

        //정보 받아오기
        foreach(SELF::$msgKeyErr as $key => $val)
        {
            $error[$val] = $val == 'errorInfo' ? SELF::{$val.'Return'}($exception) : SELF::{$val.'Return'}();
        }

        $erroInfo = [];
        foreach($error['errorInfo'] as $val)
        {
            $erroInfo[] = $val;
        }

        $msg = str_replace(
            ['{class}',  '{file}', '{line}', '{message}',  '{backtrace}']
            , $erroInfo, SELF::$msg);

        //로그 남기기   
        log_message('error', $msg, TRUE);
        unset($erroInfo, $msg);
            
        //if (ENVIRONMENT == 'testing'  || ENVIRONMENT == 'production') 
        //{
            //'/services/T03SZS1JM/BFMK3UJUC/Zdl7jcVZ3hc1qDaDHMkWdyHP'); //빈센트 개인DM
            ///services/T03SZS1JM/BFNR2HE8J/1lPK2jhtfByaMllcGTY0iUXE 에러 채널

            $error = json_encode($error, JSON_UNESCAPED_UNICODE);

            $obj    = new self;
            $client = $obj->returnClient('https://hooks.slack.com');
            $param   = ['text' => $error];
            $message = $obj->returnRequestBody($param);

            //$obj->returnResponse($client, 'post', '/services/T03SZS1JM/BFMK3UJUC/Zdl7jcVZ3hc1qDaDHMkWdyHP', $message);
            
            unset($client, $obj);
        //}
        unset($error);
        
        return _exception_handler($exception);
    }

    /**
     * 라우팅 리턴
     * @return string
     */
    protected static function routeReturn() : string
    {
        $segments = explode('?', trim($_SERVER['REQUEST_URI'], '?'));
        return  isset($segments[0]) ? $segments[0] : '';
    }

    /**
     * Get 쿼리스트링 리턴
     * @return string
     */
    protected static function queryStringReturn() : string 
    {
        $segments = explode('?', trim($_SERVER['REQUEST_URI'], '?'));
        return  isset($segments[1]) ? $segments[1] : '';
    } 

    /**
     * 날짜 리턴
     * @return string
     */
    protected static function dateReturn() : string
    {
        return date('Y-m-d H:i:s');
    }

    /**
     * POST 리턴
     * @return array
     */
    protected static function postReturn() : array
    {
        return $_POST;
    }   

    /**
     * 에러정보 리턴
     * @return array
     */
    protected static function errorInfoReturn($exception) : array
    {
        return [
            'class' =>  get_class($exception)
            ,'file' =>  $exception->getFile()
            ,'line' =>  $exception->getLine()
            ,'message'  => $exception->getMessage()
            ,'backtrace' => $exception->getTraceAsString()
        ];
    }
}