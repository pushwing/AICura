<?php
/**
 * Created by PhpStorm.
 * User: blumine
 * Date: 2018. 3. 8.
 * Time: AM 10:21
 */
use \Monolog\Logger as Logger;
use Monolog\Handler\StreamHandler;
use Fluent\Logger\FluentLogger;

function TdSendLog($data)
{
    $logger = new FluentLogger("localhost", "24224");
    $re = $logger->post("td.admin_v2.api_log", $data);
}


/**
 * 디버깅용 로그 남김
 * @param $to 로그파일명
 * @param $data api 리턴메세지
 * @param $param 배열형태
 * @throws Exception
 */
function monologSend($to, $data, $param=[])
{
    $log = new Logger('logger');

    // log/your.log 파일에 로그 생성. 로그 레벨은 Info
    $log->pushHandler(new StreamHandler('uploads/log/' . $to . '.log', Logger::DEBUG));
    $log->addInfo(json_encode($data, JSON_UNESCAPED_UNICODE), $param);
}

/**
 * 슬랙 메세지 발송
 * 테스트 완료.
 * 타입별로 정의 필요
 * @param $type
 * @param $message
 */
function slackSend($type, $message)
{
    $slackWebhookUrl="https://hooks.slack.com/services/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";

    //발송자
    $slackUsername="TEST";

    //채널
    $slackChannel = "#server-release";

    $array=json_encode(["channel"=>$slackChannel, "username"=> $slackUsername, "text"=> $message]);
    $payload = "payload=".$array;

    $CI = &get_instance();
    $CI->load->library('curl');

    $return = $CI->curl->simple_post($slackWebhookUrl, $payload);

//curl -X POST --data "payload={"channel":"#server-release", "username": "${SLACK_USERNAME}", "text": "${GIT_MESSAGE}"}" ${SLACK_WEBHOOK_URL}
//curl -X POST --data "payload={"channel":"#server-release", "username": "${SLACK_USERNAME}", "text": "Event staging deployment completed.<@UDAJA9JRJ> <@U0H7L42JU>"}" ${SLACK_WEBHOOK_URL}
}

/**
 * 라라벨 dd function 
 * @param $data mixed 
 * @return bool
 */ 
function dd($data, bool $exit = true) : bool
{   
    echo '<pre>';   
    var_dump($data);

    if ($exit === true )
    {
        exit('</pre>');
    }
    else
    {
        echo  '</pre>'; 
        return true;
    }   
}

/**
 * 배열에서 값을 찾아 키를 삭제
 * @param $list_arr
 * @param $del_value
 * @return mixed
 */
function arr_del($list_arr, $del_value)
{
    $b = array_search($del_value,$list_arr);
    if($b!==FALSE) unset($list_arr[$b]);
    return $list_arr;
}

/**
 * 이벤트 정보 변경시 검색서버에 색인 갱신 요청
 * @param $type
 * @param $data
 * @return bool
 * @throws \GuzzleHttp\Exception\GuzzleException
 */
function searchLoggerSend($type, $data)
{
    $url = 'http://192.168.30.171:9020';
    $url2 = '/api/v2/search/log/logger/createLogger';

    $array =
        [
            'serviceId'=> $data['adsId'],
            'serviceName' => 'event',
            'type'=> $type
        ];

    $client = new GuzzleHttp\Client();
    $res = $client->request('POST', $url.$url2,  [
        'body' => json_encode($array),
        'headers' => [
            'Content-Type'     => 'application/json',
        ]
    ]);

    //echo $res->getStatusCode()

    if($res->getStatusCode() == '200')
    {
        return true;
    }

    return false;

}