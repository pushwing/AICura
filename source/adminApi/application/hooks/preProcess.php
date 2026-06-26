<?php
/**
 * Created by PhpStorm.
 * User: martin.byun@goodoc.co.kr
 * Date: 2018. 3. 2.
 * Time: PM 12:21
 */

function checkKey()
{
  
    $header_arr = getallheaders(); //var_dump($_SERVER['REQUEST_URI']); exit;
  
    if( ($_SERVER['SERVER_NAME'] == 'event-admin-prd.ap-northeast-2.elasticbeanstalk.com' and strstr($_SERVER['REQUEST_URI'], 'dashBoardEvent')) or ($_SERVER['SERVER_NAME'] == 'event-admin-prd.ap-northeast-2.elasticbeanstalk.com' and strstr($_SERVER['REQUEST_URI'], 'boardMigration')) or ($_SERVER['SERVER_NAME'] == 'event.goodoc.kr' and strstr($_SERVER['REQUEST_URI'], 'dashBoardEvent')) or ($_SERVER['SERVER_NAME'] == 'event.goodoc.kr' and strstr($_SERVER['REQUEST_URI'], 'boardMigration'))
        or ($_SERVER['SERVER_NAME'] == 'event.goodoc.kr' and strstr($_SERVER['REQUEST_URI'], 'dataMigration'))
    )
    {

    }
    else
    {
        $pos = strpos($_SERVER['REQUEST_URI'], 'payment');

        if(!$pos) {

            if ($_SERVER['SERVER_NAME'] != 'ev.com' and $_SERVER['SERVER_NAME'] != 'stagingevent.goodoc.kr' and $_SERVER['SERVER_NAME'] != 'devevent.goodoc.kr' and $_SERVER['SERVER_NAME'] != 'event.goodoc.kr')  {
                if (API_KEY != $header_arr['x-api-key']) {
                    echo json_encode(array('status' => 'error', 'code' => 401, 'message' => 'api 키가 없거나 유효하지 않은 api 키입니다.', 'result' => ''));
                    exit;
                }
            }
        }
    }


    //log, users_id, token 전역변수화
    $CI =& get_instance();

    $logData = [
        'when' => time(),
        'where' => $CI->input->ip_address(),
        'who' => @$header_arr['x-api-userid'],
        'what' => $CI->uri->uri_string(), //$this->router->fetch_method(),
        //'how' => json_encode($CI->input->post(null, true)),
        'how' => $CI->input->raw_input_stream,
        'why' => 'event',
        'success' => 'F',
        'memo' => ''
    ];


    //굿닥id v2 적용으로 refreshToken 추가
    $CI->load->vars([
        'token'=>@$header_arr['x-api-token'],
        'refreshToken'=>@$header_arr['x-api-refreshtoken'],
        'users_id'=>@$header_arr['x-api-userid'],
        'headerHospitalId' =>@$header_arr['x-api-hospitalid']
        ]);
}
