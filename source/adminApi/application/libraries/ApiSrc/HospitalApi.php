<?php 
namespace AdminApi\Libraries\ApiSrc;

defined('BASEPATH') OR exit('No direct script access allowed');

trait HospitalApi 
{
    /**
    * 병원ID들로 병원명들 가져오기
    * @param $idArr array
    * @param $token string
    * @param $userId int
    * @return array
    */
    final public function getHospitalNamesByIds(array $idArr) : array
    {
        $hospitalArr = [];
      
        $ids = implode(',', $idArr);

        $apiParam = ['searchType' => 4,'searchValue'  => $ids];
        
        $response = $this->listHospitals($apiParam);

        //병원 정보
        $decodeHospital = isset($response['hospitals']) ? $response['hospitals'] : [];

        /**
         * 익명 메소드 제너레이터
         */
        $noNameFunc = function ($idArr) use ( & $decodeHospital)  
        {
            foreach ($idArr as $key => $val)
            {
                foreach($decodeHospital as $subkey=>$subVal)
                {
                    if ($val !=  $subVal['id'])
                    {
                        continue;    
                    } 
                    yield $val => $subVal['name'];
                    break;
                }     
            }
        };
        
        foreach($noNameFunc($idArr) as $key=>$val)
        {
            $hospitalArr[$key] = $val;
        } 
        unset($noNameFunc, $decodeHospital);
        
        return $hospitalArr;
    }

    /**
     * 병원ID들로 병원정보들 가져오기
     * @param $idArr array
     * @param $token string
     * @param $userId int
     * @return array
     */
    final public function getHospitalInfosByIds(array $idArr) : array
    {
        $hospitalArr = [];
      
        $ids = implode(',', $idArr);

        $apiParam = ['searchType' => 4,'searchValue'  => $ids];
        
        $response = $this->listHospitals($apiParam);

        //병원 정보
        $decodeHospital = isset($response['hospitals']) ? $response['hospitals'] : [];

        /**
         * 익명 메소드 제너레이터
         */
        $noNameFunc = function ($idArr) use ( & $decodeHospital)  
        {
            foreach ($idArr as $key => $val)
            {
                foreach($decodeHospital as $subkey=>$subVal)
                {
                    if ($val !=  $subVal['id'])
                    {
                        continue;    
                    } 
                    yield $val => $subVal;
                    break;
                }     
            }
        };

        foreach($noNameFunc($idArr) as $key=>$val)
        {
            $hospitalArr[$key] = $val;
        } 
        unset($noNameFunc, $decodeHospital);

        return $hospitalArr;
    }

    /**
     * 네트워크 병원 이름으로 ID 가져오기
     * @param $hospitalName string
     * @return mixed
     */
    final public function getHospitalIdByName(string $hospitalName)
    {
        $returnId = '';
    
        $apiParam = ['searchType' => 1,'searchValue'  => $hospitalName];
        
        $response = $this->listHospitals($apiParam);

        //병원 정보
        $decodeHospital = $response['hospitals'];

        $returnId = [];
        
        if (sizeof($decodeHospital) > 0) {
           foreach($decodeHospital as $key => $val){
                $returnId[] = $val['id'];   
           }  
        }

        //$returnId = isset($decodeHospital[0]['id']) ? $decodeHospital[0]['id'] : '';
        
        return $returnId;
    }   

     /**
     * 모병원ID로 자병원 리스트 찾기
     * @param $hospitalId int
     * @param $onlyCount bool
     * @return array
     */
    final public function  getChildHospitals(int $hospitalId,  bool $onlyCount = false) : array
    {
        $return_arr = [];
      
        $apiParam = ['hospitalId' => $hospitalId];
        
        $response = $this->getGoodDocApi('/api/v2/hospital/outapi/getChildHospitals', $apiParam, HOSPITAL_URL); 

        //병원 정보
        $return_arr = isset($response['result']) ? $response['result'] : [];

        if ( count($return_arr) == 0 )
        {
            return [];    
        }

        if ($onlyCount === true )
        {
            return ['totalCount' => $return_arr['totalCount']];    
        }
        
        return $return_arr;
    } 

    /**
     * 자병원 아이디로 모병원 찾기
     * @param $hospitalId int
     * @return array
     */
    final public function getParentHospitals(int $hospitalId,  bool $onlyCount = false) : array
    {
        $return_arr = [];
      
        $apiParam = ['hospitalId' => $hospitalId];
        
        $response = $this->getGoodDocApi('/api/v2/hospital/outapi/getParentHospital', $apiParam, HOSPITAL_URL); 

        //병원 정보
        $return_arr = isset($response['result']) ? $response['result'] : [];

        if ( count($return_arr) == 0 )
        {
            return [];    
        }
        
        if (!isset($return_arr['hospital']['isExist']) || $return_arr['hospital']['isExist'] === false)
        {
            return [];    
        }
        unset($return_arr['hospital']['isExist']);

        return $return_arr['hospital'];
    }


    /**
     * 병원 리스트 API 
     * @param $param array
     * @param $token string
     * @param $userId int
     * @param $onlyCount bool
     * @return array
     */
    final public function listHospitals(array $param,  bool $onlyCount = false) : array
    {
        $return_arr = [];

        $response = $this->getGoodDocApi('/api/v2/hospital/outapi/listHospitals', $param, HOSPITAL_URL); 
    
        //병원 정보
        $return_arr = @$response['result'];
        
        if ( count($return_arr) == 0 )
        {
            return [];    
        }

        if ($onlyCount === true )
        {
            return ['totalCount' => $return_arr['totalCount']];    
        }

        return $return_arr;
    }
}