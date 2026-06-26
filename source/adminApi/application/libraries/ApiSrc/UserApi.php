<?php 
namespace AdminApi\Libraries\ApiSrc;

defined('BASEPATH') OR exit('No direct script access allowed');

trait UserApi 
{   
  
    /**
     * 유저ID로 병원 정보를 가져온다
     * @param $userId int
     * @return array
     */
    final public function getHospitlaIdByUserId(int $userId) : array
    {
        $return_arr = [];
        
        $param = [
            'userId' => $userId
        ];
        
        $response = $this->getGoodDocApi('/'.$this->isWhere.'mms/admin/hospital/list',$param, USER_URL); 
        //병원 정보
        $return_arr = isset($response['result']) ? $response['result'] : [];
      
        if ( count($return_arr) == 0 )
        {
            return [];    
        }

        if (!isset($return_arr[0])) 
        {
            return [];        
        }

        return $return_arr;
    }
     /*
    final public function getHospitlaIdByUserId(int $userId) : array
    {
        $return_arr = [];
       
        $response = $this->getGoodDocApi('/'.$this->isWhere.'/mms/admin/'.$userId.'/hospital/list', [], USER_URL); 
        //병원 정보
        $return_arr = isset($response['result']) ? $response['result'] : [];
        
        if ( count($return_arr) == 0 )
        {
            return [];    
        }

        if (!isset($return_arr[0])) 
        {
            return [];        
        }

        return $return_arr;
    }
    */

    /**
     * 토큰 검증
     * @return array
     */
    final public function tokenValidate() : array
    {
        $response = $this->getGoodDocApi('/'.$this->isWhere.'tms/admin/token/validate', [], USER_URL); 
        return $response;
    }

    /**
     * 로그인
     * @param $email string
     * @param $password string
     * @return array
     */
    final public function login(string $email, string $password) : array
    {
        $param = [
            'email' => $email, 'password' => $password
        ];
        $response = $this->getGoodDocApi('/'.$this->isWhere.'tms/admin/user/login', $param, USER_URL); 
        return $response;     
    }  


    /**
     * 유저ID로 유저정보를 가져온다
     * @param $userId int
     * @return array
     */
    final public function getUserInfoByUserId(int $userId) : array
    {
        $return_arr = [];
       
        $response = $this->getGoodDocApi('/'.$this->isWhere.'mms/admin/show/'.$userId, [], USER_URL); 

        //병원 정보
        $return_arr = isset($response['result']) ? $response['result'] : [];
       
        if ( count($return_arr) == 0 )
        {
            return [];    
        }

        if (!isset($return_arr['id'])) 
        {
           return [];        
        }
     
        return $return_arr;
    }
}