<?php 
namespace AdminApi\Libraries;

defined('BASEPATH') OR exit('No direct script access allowed');

use GuzzleHttp\Client;
use GuzzleHttp\Psr7;
use GuzzleHttp\RequestOptions;
use Psr\Http\Message\ResponseInterface as ResponseInterface;
use GuzzleHttp\Exception\RequestException as RequestException;
use GuzzleHttp\Exception\ClientErrorResponseException as ClientErrorResponseException; 

/**
 * 서버통신 관련 유틸
 */
class Guzzleutil
{
    private $timeout = 3.00;
    
    protected $log = [];


    /**
     * GuzzleHttpClient return 
     * @param $url string
     * @return Client
     */
    protected function returnClient(string $url = '', array $headerInfo = []) : Client
    {   
        $config_arr = [];
        $config_arr['base_uri'] = $url;
        
        if (sizeof($headerInfo)  > 0 )
        {
            $config_arr['headers']  = $headerInfo;
        }
        else 
        {
            $debug_args = debug_backtrace(2);

            foreach($debug_args as $key => $val)
            {   
                if (isset($val['class']) && $val['class'] == 'Goodocapi')
                {
                    $config_arr['headers'] = [];
                    $config_arr['headers']['x-api-key'] = API_KEY;
                    $config_arr['headers']['x-api-token'] = $this->headerInfo['x-api-token'];
                    $config_arr['headers']['x-api-userid'] =  $this->headerInfo['x-api-userid'];
                    $config_arr['headers']['x-api-refreshtoken'] = $this->headerInfo['x-api-refreshtoken'];
                    break;
                }
            }
        }
        //dd($config_arr);
        return new Client($config_arr); 
    }

    /**
     * 동기 형태 응답값
     * @param $client Client
     * @param $method string 
     * @param $resource string 
     * @param $requestParam array
     * @return array
     */
    protected function returnResponse(Client $client, string $method, string $resource, array $requestParam) : array
    {   
        $check = false; 
    
        try 
        {
            $response   = $client->request($method, $resource, $requestParam, [
                'debug' => ENVIRONMENT == 'development' ? true : false
               , 'connect_timeout' => $this->timeout
            ]);  
            
            $code       = $response->getStatusCode();
            $reason     = $response->getReasonPhrase();
            $body       = $response->getBody(); //var_dump($body); exit;

            $check = true;
        }
        catch (RequestException $e) 
        {
            /**
             * Curl 통합 로그  
             * 리퀘스트 예외사항
             */
            $this->log['Goodocapi_getGoodDocApi_RequestException'] = $e->getMessage();
        }
        catch (ClientErrorResponseException $e) 
        {
            /**
             * Curl 통합 로그  
             * 리스폰스 예외사항 
             */
            $this->log['Goodocapi_getGoodDocApi_ClientErrorResponseException'] = $e->getMessage();
        }

        if ($check === false) 
        {
            //$this->setLog();
            return [ ];
        }
        //dd($response, false);
        return [ 'code' => $code, 'reason' => $reason, 'body' => $body];  
    }
    
    /**
     * 비동기 형태 응답값 TODO 개발 및 진행 예정
     * @param $client Client
     * @param $method string 
     * @param $resource string 
     * @param $requestParam array
     * @return array
     */
    protected function returnResponseAsync(Client & $client, string $method, string $resource, array $requestParam)
    {
        $promise = $client->requestAsync($method, $resource, $requestParam);
        $promise->then(
            function (ResponseInterface $res) {
                echo $res->getStatusCode() . "\n";
            },
            function (RequestException $e) {
                echo $e->getMessage() . "\n";
                echo $e->getRequest()->getMethod();
            }
        );
    }

    /**
     * 리퀘스트 데이터 리턴
     * @param $data array 
     * @param $type  string 
     * @return array
     */
    protected function returnRequestBody(array $data, string $type = 'json') : array
    {
        if (sizeof($data) == 0) 
        {
            return $data;
        }

        return $type == 'json'  ? [ RequestOptions::JSON => $data ] : [ 'query' => $data];
    }

    /**
     * 로그 저장
     */
    protected function setLog(string $name = 'goodocapilog') : bool
    {
        $name = $name.'_'.date('Ymd');
        monologSend($name, $this->log, []);
        unset($this->log);
        return true; 
    }
}