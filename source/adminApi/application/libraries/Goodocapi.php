<?php
//namespace AdminApi\Libraries;

defined('BASEPATH') OR exit('No direct script access allowed');

use AdminApi\Libraries\Guzzleutil as Guzzleutil;
use AdminApi\Libraries\ApiSrc\HospitalApi as HospitalApi; 
use AdminApi\Libraries\ApiSrc\UserApi as UserApi; 

/**
 * Class Common_m Method __construct 에서 호출 중
 * /api/v2/admin/hospital/common/listHospitals //병원 리스트 API 	
 * /api/v2/admin/hospital/common/getChildHospitals //모병원ID로 자병원들 리스트 API
 * 사용 중 
 * TODO 
 * 자병원 ID로 모병원 정보 가져오는 API 요청 
 * 사용 
 * 자병원으로 로그인 하여 수주계약 검색시 모병원 정보와 같이 검색을 하기 때문에.
 * 
 * TODO 
 * USER API
 * user_hospital_departments` => 유저 정보로 유저의 병원정보 죄회 관련 된 API 필요
 */
class Goodocapi extends Guzzleutil
{
    use HospitalApi;
    use UserApi;

    private $CI = null;

    protected $isWhere = null;

    protected $headerInfo = [
        'x-api-token'    => null,
        'x-api-userid'   => null,
        'x-api-refreshtoken' => null   
    ];

    /**
     * 생성자
     * @param $param array
     * @return bool
     */
    public function __construct()
    {   
        $this->CI = & get_instance();



        $headerSet = $this->CI->load->get_vars();
        /**
         * Hook 진행이 __construct 후 에 일어난다.
         */
        if (sizeof($headerSet) == 0)
        {
            require_once FCPATH.'/application/hooks/preProcess.php';
            checkKey();
            $headerSet = $this->CI->load->get_vars();
        }
        
        $this->headerInfo['x-api-token'] = isset($headerSet['token']) ? $headerSet['token'] : null;
        $this->headerInfo['x-api-userid'] = isset($headerSet['users_id']) ? (int) $headerSet['users_id'] : null;
        $this->headerInfo['x-api-refreshtoken'] =  isset($headerSet['refreshToken']) ? $headerSet['refreshToken'] : null; 
        
      
        //굿닥ID 용
        $this->isWhere = ENVIRONMENT == 'development' ? 'dev/' : ( ENVIRONMENT == 'testing' ?  'stg/' : '' ); 
        //$this->isWhere =  'stg'; 
    }
        

    /**
     * 굿닥 API 실행
     * @param $resource string
     * @param $data  array
     * @param $url string
     * @param $method string
     * @param $type string
     * @param $isAsync bool
     * @return array
     */
    public function getGoodDocApi(string $resource, array $data, string $url = '',  string $metod = 'POST', string $type = 'json',  bool $isAsync = false) : array
    {
        $return_arr = [];
        
        /**
         * Curl 통합 로그  
         * api 주소
         * api request param
         * token, userid, refreshtoken
         */
        $this->log['Goodocapi_construct'] = json_encode($this->headerInfo, JSON_UNESCAPED_UNICODE);
        $this->log['Goodocapi_getGoodDocApi_url']  = $metod.' : '.$url.$resource;

        $this->log['Goodocapi_request_info'] = [
            'routing' => $this->getRoute()
            ,'ip'     => $this->CI->input->ip_address()
        ];

        $isAllReturn  = $this->isAllReturn();
        
        try  
        {
            $client = $this->returnClient($url);

            $requestParam = $this->returnRequestBody($data, $type);
              
            /**
             * Curl 통합 로그  
             * 결과 코드
             * 결과 메시지
             */
            $logParam = $requestParam;
            if ( isset($logParam['json']['password']))   
            {
                unset($logParam['json']['password']);
            }   
            $this->log['Goodocapi_getGoodDocApi_data'] = json_encode($logParam, JSON_UNESCAPED_UNICODE);

    
            ///dd($requestParam, false);
            $response = $isAsync == false ? $this->returnResponse($client, $metod, $resource, $requestParam) : $this->returnResponseAsync($client, $metod, $resource, $requestParam);
         
            if (!isset($response['code']))
            {   
                $code = '614';
                $reason = '서버관리자에게 문의 해 주세요.';
                $decode = [];
            }
            else 
            {
                $code   = $response['code'];
                $reason = $response['reason'];
                $decode   = json_decode($response['body'], true);
                //dd([$decode], false);
            }          
            /**
             * Curl 통합 로그  
             * 결과 코드
             * 결과 메시지
             */
            $this->log['Goodocapi_returnResponse_code']  = $code;
            $this->log['Goodocapi_returnResponse_reason']  = $reason;
            
            /**
             * Curl 통합 로그  
             * 결과값
             */
            $this->log['Goodocapi_returnResponse_decode']   = json_encode($decode, JSON_UNESCAPED_UNICODE);
            unset($client,$response);

            $checkFlag = false;
          
            //디코딩 실패시 리턴
            if (!isset($decode['code']) || !isset($decode['status'])) 
            {
                $return_arr =  $isAllReturn == true ? $decode : [];    
                $checkFlag = true;
            }       
          
            //상태값이 성공이 아닐시 리턴
            if ($checkFlag === false && ($decode['code'] != 200 || $decode['status'] != 'success'))
            {
                $return_arr =  $isAllReturn == true ? $decode : [];
                $checkFlag = true;               
            }
           
            //토탈 카운트 0 이면 리턴
           /*
            if ($checkFlag === false && isset($decode['result']) )
            {dd($decode);
                $return_arr =  $isAllReturn == true ? $decode : [];
                $checkFlag = true;    
            } 
            */
            $return_arr = $checkFlag === false ? $decode : $return_arr;
            //dd($return_arr);
        }
        catch (Exception $e) 
        {
            $return_arr = [];
            /**
             * Curl 통합 로그  
             * 예외사항 
             */
            $this->log['Goodocapi_getGoodDocApi_Exception']  = $e->getMessage();
        }

        //운영에서 주석 남기지 말랬더니 말 안듣고 남겨놓음. v씨. v2오픈후 문제됨. 19-06-11
        if(ENVIRONMENT != 'production')
        {
            $this->setLog();
        }

        //$result = TdSendLog($this->log);
        return $return_arr;
    }   

    /**
     * 전부 넘겨줄지아닐지
     * @return bool
     */
    private function isAllReturn() : bool
    {
        $allReturnFunc = ['tokenValidate'];
        $checkBackTrace = debug_backtrace(2);
       
        if (isset($checkBackTrace[2]['function']) && in_array($checkBackTrace[2]['function'], $allReturnFunc))
        {
            return true;
        }
        return false;
    }

    /**
     * 메소드 확인 후 실행  
     */
    private function routerMethodExec(string $method)
    {
        if (method_exists($this->CI->router, $method))
        {
            return $this->CI->router->$method();
        }
        
        return '';
    }


    /**
     * 라우팅 확인 
     */
    public function getRoute() : string
    {   
        $checkMethod = ['fetch_class', 'fetch_method'];

        $routringStr = '';

        foreach($checkMethod as $key => $val) 
        {
            $routringStr .= $this->routerMethodExec($val);
            $routringStr .= $key == 0 ? '/' : '';
        }

        return $routringStr;
    }
}