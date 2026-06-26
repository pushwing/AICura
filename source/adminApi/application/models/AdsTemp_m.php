<?php
/**
 * Created by PhpStorm.
 * User: blumine
 * Date: 2018. 2. 12.
 * Time: PM 5:45
 */

class AdsTemp_m extends CI_Model
{
    function __construct()
    {
        parent::__construct();

        /**
         * 1 옵션여부, 2 옵션이벤트번호, 3 db단가, 4 메모, 5 카테고리, 6계약대상(수주계약번호), 7 이벤트타입, 8 병원id, 9 이벤트명, 10 네트워크병원,
         * 11 기간, 12 정상가, 13 할인가, 14 텍스트가격, 15 노출영역, 16 지역, 17 제휴매체 18 모델동의, 19 키워드, 20 모델이미지갯수,
         * 21 후기노출여부, 22 신청버튼, 23  썸네일1, 24 썸네일2, 25 상세1, 26 상세2, 27 상세3, 28 상세4, 29 상세5, 30 상세6
         * 31 이벤트명, 32 기간연장여부, 33 가격타입
         */
        $this->historyArr = [
            'contractOrderId' => '6',
            'hospitalId' => '8',
            'adTitle' => '31',
            'adType' => '7',
            'adStartDate' => '11',
            'adEndDate' => '11',
            'adDateExtend' => '32',
            'costType' => '33',
            'generalCost' => '12',
            'discountCost' => '13',
            'textCost' => '14',
            'dbCost' => '3',
            'category' => '5',
            'exposure' => '15',

            'region' => '16',
            'cooperation' => '17',
            'whereImage' => '18',
            'keyword' => '19',
            'modelImageCount' => '20',
            't1ImageName' => '23',
            't2ImageName' => '24',
            'd1ImageName' => '25',
            'isViewBoard' => '21',
            'optionAdId' => '2',
            'd2ImageName' => '26',
            'd3ImageName' => '27',
            'd4ImageName' => '28',
            'd5ImageName' => '29',
            'd6ImageName' => '30',
            'buttonName' => '22',
            'buttonLink' => '22',
            'buttonType' => '22',
            'buttonPhone' => '22',
            'buttonNameColor' => '22',
            'buttonColor' => '22'
            ];
        //'isOption' => '1', 삭제함. adType=5로 대체
    }


    /**
     * 광고 임시저장 등록 
     * @param $data
     * @return bool|int
     */
    function setAdsTemp($data)
    {
        if ( isset($data['refreshToken']) )
        {
            unset($data['refreshToken']);
        }

        $date = date("Y-m-d H:i:s");

        //메소드 내부에서 트랜잭션이 시작인지 체크 
        $checkMethodTrans = false;
        if ($this->common_m->isInTrans() === false)
        {
            $this->master->trans_begin();
            $checkMethodTrans = true;
        }

        //ads 입력
        if(USERAUTHCODE == 2)
        {
            $adDetailInfo = json_encode([
                '','','','','',''
            ]); //버튼관련
        }
        else
        {
            $adDetailInfo = json_encode([
                $data['buttonName'],
                $data['buttonLink'],
                $data['buttonType'],
                $data['buttonPhone'],
                $data['buttonColor'],
                $data['buttonNameColor']
            ]); //버튼관련
        }
        
        $agencyUserId = $this->common_m->getAgencyUserId($data);
        $agencyUserId = 4;
        $TempArr = $aArr = [
            'adsId' => isset($data['adsId']) ? $data['adsId'] : null,
            'channel' => isset($data['channel']) ? $data['channel'] : null,
            'cityId' => isset($data['cityId']) ? $data['cityId'] : 1, //일단 서울로 고정
            'adTitle'=>isset($data['adTitle']) ? $data['adTitle'] : null,
            'adStatus'=>4, //작성중
            'subAdStatus'=>6, //병원작성중
            'category'=>isset($data['category']) ? $data['category'] : null,
            'adStartDate'=>isset($data['adStartDate']) ? $data['adStartDate'] : null,
            'adEndDate'=>isset($data['adEndDate']) ? $data['adEndDate'] : null,
            'adDateExtend'=>isset($data['adDateExtend']) ? $data['adDateExtend'] : null,
            'adType'=>isset($data['adType']) ? $data['adType'] : null,
            'exposure'=>isset($data['exposure']) ? $data['exposure'] : null,
            'costType'=>isset($data['costType']) ? $data['costType'] : null,
            'generalCost'=>isset($data['generalCost']) ? $data['generalCost'] : null,
            'discountCost'=>isset($data['discountCost']) ? $data['discountCost'] : null,
            'textCost'=>isset($data['textCost']) ? $data['textCost'] : null,
            'dbCost'=>isset($data['dbCost']) ? $data['dbCost'] : null,
            'whereImage'=>isset($data['whereImage']) ? $data['whereImage'] : null,
            'modelImageCount'=>isset($data['modelImageCount']) ? $data['modelImageCount'] : null,
            'adDetailInfo'=>$adDetailInfo,
            'regDate'=>$date,
            'contractId'=>isset($data['contractId']) ? $data['contractId'] : null,
            'contractOrderId'=>isset($data['contractOrderId']) ? $data['contractOrderId'] : null,
            'hospitalId'=>isset($data['hospitalId']) ? $data['hospitalId'] : null,
            'hospitalType'=>isset($data['hospitalType']) ? $data['hospitalType'] : null,
            'agencyUserId'=>$agencyUserId,
            'isViewBoard'=>isset($data['isViewBoard']) ? $data['isViewBoard'] : null,
            'userId'=>isset($data['users_id']) ? $data['users_id'] : null,
            'cooperation'=>isset($data['cooperation']) ? $data['cooperation'] : null,
            'region'=>isset($data['region']) ? $data['region'] : null,
            'keyword'=>isset($data['keyword']) ? $data['keyword'] : null,
            't1ImageName'=>isset($data['t1ImageName']) ? $data['t1ImageName'] : null,
            't2ImageName'=>isset($data['t2ImageName']) ? $data['t2ImageName'] : null,
            'isViewBoard'=>1,
            'regDate'=>$date,
            'adsId' => isset($data['adsId']) ? $data['adsId'] : null,
            'isDelete'  =>  isset($data['isDelete']) ? $data['isDelete'] : 2,
            'deleteUserId'  =>  isset($data['deleteUserId']) ? $data['deleteUserId'] : null,
            'userId'      => isset($data['users_id']) ? $data['users_id'] : null,
            'dImageJson'  => isset($data['dImages']) ?  json_encode($data['dImages'], JSON_UNESCAPED_UNICODE) : null
        ];
        //히스토리용 정리
        //unset($TempArr['adStatus']);
        //unset($TempArr['subAdStatus']);
        unset($TempArr['modDate']);
        //unset($TempArr['contractId']);
        //unset($TempArr['contractOrderId']);
        //unset($TempArr['hospitalId']);

        $this->master->insert('ads_temporary', $TempArr);
        $tempId = $this->master->insert_id();

        //트랜잭션
        if ($checkMethodTrans === true)
        {
            if ($this->master->trans_status() === FALSE)
            {
                $this->master->trans_rollback();
                return false;
            }
            else
            {
                $this->master->trans_commit();
                return $tempId;
            }
        }
        return $tempId;
    }

    /**
     * 광고 임시저장 내용상세보기
     * @param $data
     * @return array
     */
    function getAdsTemp(int $tempId, string $token, int $userId) : array
    {
        //임시저장 정보 가져오기
        $result = $this->db->get_where('ads_temporary', ['id' => $tempId, 'isDelete' => 2])->row_array();

        if (count($result) == 0)  
        {
            return [];    
        }

        //버튼정보 가공
        $bt = json_decode($result['adDetailInfo'], true);
        $result['buttonName'] = $bt[0];
        $result['buttonLink'] = $bt[1];
        $result['buttonType'] = $bt[2];
        $result['buttonPhone'] = $bt[3];
        $result['buttonColor'] = $bt[4];
        $result['buttonNameColor'] = $bt[5];

        $result['dImages']  = is_null($result['dImageJson']) || empty($result['dImageJson']) ? '[]' : $result['dImageJson'];
    
        $hospitalNameArr = $this->goodocapi->getHospitalNamesByIds([$result['hospitalId']]);
        $result['hospitalName'] = count($hospitalNameArr) == 0 ? '' : $hospitalNameArr[$result['hospitalId']];

        return $result;
    }

    /**
     * 광고 임시저장 수정
     * @param $data array
     * @return bool|int
     */
    function updateAdsTemp(array $data)
    {   
        if ( isset($data['refreshToken']) )
        {
            unset($data['refreshToken']);
        }
        
        $date = date("Y-m-d H:i:s");
  
        $data['userId']    = $data['users_id'];
        $tempId            = isset($data['tempId']) && !is_null($data['tempId']) ? $data['tempId'] : null;
    
        unset($data['tempId']);
        unset($data['token']);
        unset($data['users_id']);
        unset($data['menu_id']);    
        unset($data['temporarySave']);
        if ( isset($data['refreshToken']) )
        {
            unset($data['refreshToken']);
        }

        //메소드 내부에서 트랜잭션이 시작인지 체크 
        $checkMethodTrans = false;
        if ($this->common_m->isInTrans() === false)
        {
            $this->master->trans_begin();
            $checkMethodTrans = true;
        }
        //삭제 된것의 업데이트는 안됨

        $this->master->select('id');
       
        if (is_null($tempId)) 
        {
            $result = $this->master->get_where('ads_temporary', ['adsId'=>$data['adsId'], 'isDelete' => 2])->result_array();
        }
        else
        {
            $result = $this->master->get_where('ads_temporary', ['id'=>$tempId, 'isDelete' => 2])->result_array();
        }    
        
        //echo $this->db->last_query();
        //dd($result);
        if (count($result) == 0) 
        {
            if ($checkMethodTrans === true)
            {
                $this->master->trans_rollback();
            }
            return false;
        }

        $tempId = is_null($tempId) ? $result[0]['id'] : $tempId;
        unset($result);

        $buttons      = ['buttonName', 'buttonLink', 'buttonType', 'buttonPhone', 'buttonColor', 'buttonNameColor'];
        $adDetailInfo = [];    

        foreach($buttons as $val)
        {
            if (isset($data[$val]))
            {
                $adDetailInfo[] = $data[$val];
            }   
            else
            {
                $adDetailInfo[] = null;
            }
            unset($data[$val]); 
        }
        unset($buttons);

        $data['adDetailInfo'] = json_encode($adDetailInfo);
        $data['modDate']   = $date;
        $data['adStatus'] = 4; //작성중
        $data['subAdStatus'] = 6; //병원작성중
        $data['isViewBoard'] = 1; //병원 1 고정
        $data['dImageJson']   = isset($data['dImages']) ?  json_encode($data['dImages'], JSON_UNESCAPED_UNICODE) : null;
        if (isset($data['dImages'])){
            unset( $data['dImages']);
        }
        //dd($data);
        $this->master->where('id', $tempId)->update('ads_temporary', $data);

        //트랜잭션
        if ($checkMethodTrans === true)
        {
            if ($this->master->trans_status() === FALSE)
            {
                $this->master->trans_rollback();
                return false;
            }
            $this->master->trans_commit();
            return $tempId;
        }

        return $tempId;
    }

    /**
     * 광고 임시정보 리스트
     * @param $data
     * @return array
     */     
    public function getAdsTempList(array $data)
    {
        $obj = & $this;
       
        $tempFunc = function (string $type = 'list', array $data ) use  ( & $obj) : array
        {
            $result = [];
            
            $startLimit = (int) $data['limit'];
            
            $limit = (int) ($data['page'] - 1) * $startLimit;
            
            $obj->_where($data);
        
            $obj->db->select('tmp.* ', false);
            $obj->db->order_by('tmp.id', 'desc');
        
            if ($type == 'list')
            {
                $obj->db->limit($startLimit, $limit);
                $list = $obj->db->get_where('ads_temporary tmp', ['tmp.isDelete' => 2])->result_array(); 
               
                $result['list'] = $list;
            }
            else 
            {
                $cnt = $obj->db->get_where('ads_temporary tmp', ['tmp.isDelete' => 2])->num_rows(); 
                $result['totCnt'] = $cnt;
            }
            //dd($result, false);
            return $result;
        };

        $totCnt = $tempFunc('count', $data)['totCnt'];
        $result = $tempFunc('list', $data);
        $result['totCnt'] = $totCnt;
        //dd($result, false);
        unset($tempFunc);
       
        //dd($result);
        if ($totCnt === 0)
        {
            $result = ['list' => [], 'totCnt' => 0];  
        }

        //날짜 처리 부분
        $regDateChange = function () use (& $result) 
        {
            foreach($result['list'] as $k => $v)
            {
                $v['regDate'] =  str_replace('-', '.', $v['regDate']);
                $v['modDate'] =  str_replace('-', '.', $v['modDate']);
                yield $k => $v;
            }
        };
        /*
        foreach($regDateChange() as $key => $val) 
        {
            $result['list'][$key] = $val;  
        }
        */
        $hospitalId_arr    = [];
        //$hospitalNetId_arr = [];
        $unsetArr = [
            'd1ImageName', 'd2ImageName', 'd3ImageName', 
            'd4ImageName', 'd5ImageName'
        ];

        foreach($result['list'] as $key => $val)
        {
            foreach($unsetArr as $subKey => $subVal){
                unset($result['list'][$key][$subVal]);    
            }

            if (!is_null($val['hospitalId']) && !empty($val['hospitalId']) ) {
                $hospitalId_arr[$key] = $val['hospitalId'];
            }    
        }

        //중복 제거
        $hospitalId_arr = array_unique($hospitalId_arr);
        
        //190102 병원 명 가져오기
        //result = array, key : 병원ID , val : 병원명
        if (count($hospitalId_arr) > 0 ) 
        {
            //190102 병원명 가져오기
            $hospitalNamesArr = $this->goodocapi->getHospitalInfosByIds($hospitalId_arr);
            foreach($result['list'] as $key => $val)
            {
                if (!is_null($val['hospitalId']) && !empty($val['hospitalId']) ) {
                    $hospitalInfo = isset($hospitalNamesArr[$val['hospitalId']]) ? $hospitalNamesArr[$val['hospitalId']] : [];
                }
                else {
                    $hospitalInfo = [];
                }
                
                $hospitalName = '병원명없음';
                $hospitalType = 0; // 0 : 일반, 1 : 모병원, 2: 자병원
                if(sizeof($hospitalInfo) > 0)
                {
                    $hospitalName = $hospitalInfo['name'];
                    $hospitalType = $hospitalInfo['networkType'];
                } 

                $result['list'][$key]['hospitalName'] = $hospitalName;
                $result['list'][$key]['hospitalType'] = $hospitalType;
                unset($hospitalName, $hospitalType);
             }
        }
      
        return ['list' => $result['list'], 'totCnt' => $result['totCnt']];
    }

    /**
     * 광고 임시정보 삭제
     * @param $tempId string
     * @param $userId int
     * @return bool 
     */
    public function deleteAdsTemp(string $tempId, int $userId)
    {
        $date = date('Y-m-d H:i:s');
        
        $checkMethodTrans = false;
        if ($this->common_m->isInTrans() === false)
        {
            $this->master->trans_begin();
            $checkMethodTrans = true;   
        }
        
        //혹시 모르니 빈거 지워버린다.
        if (strpos($tempId, ',') > -1 ) 
        {
            $id_arr = explode(',', $tempId);
            foreach($id_arr as $key => $val)
            {
                if (empty($val) || is_null($val))
                {
                    unset($id_arr[$key]);
                }
                else 
                {
                    $id_arr[$key] = (int) $val;   
                }
            }
            $this->master->where_in('id', $id_arr);
        }
        else
        {
            $this->master->where('id', $tempId);
        }

        $this->master->update('ads_temporary', ['isDelete' => 1, 'deleteUserId' =>$userId, 'delDate' => $date]);
        
        //트랜잭션
        if ($checkMethodTrans === true)
        {
            if ($this->master->trans_status() === FALSE)
            {
                $this->master->trans_rollback();
                return false;
            }
            $this->master->trans_commit();
            return true;
        }
        return true;
    }  
    
    /**
     * 광고ID로 확인
     * @param $adsId int
     * @return bool
     */ 
    public function getAdsTempAdsId(int $adsId) : bool
    {
        $result = $this->db->get_where('ads_temporary', ['adsId' => $adsId, 'isDelete' => 2])->result_array();
           
        if(count($result) == 0)     
        {
            return false;
        }   

        return true;
    }   


    /**
     * 광고 임시정보 검색어 처리
     * 운영과 병원 어드민 분기처리
     * @param $data
     */
    function _where($data)
    {
        //병원admin용
        if($data['searchCategory'])
        {
            $cateArr = explode(',', $data['searchCategory']);

            $this->db->where_in('tmp.category', $cateArr);
        }

        if($data['searchAdType'] &&  $data['searchAdType'] != 'a')
        {
            $this->db->where('tmp.adType', $data['searchAdType']);
        }

        if(USERAUTHCODE ==2)
        {
            if($data['searchWord'])
            {
                $this->db->like('tmp.adTitle', $data['searchWord']);
            }

            //자기것만
            $this->db->where('tmp.hospitalId', $data['hospitalId']);
        }
        else
        {
            //광고지역
            if($data['searchRegion'])
            {
                $this->db->where('tmp.regionCode', $data['searchRegion']);
            }

            //노출영역
            if($data['searchExposure'])
            {
                $this->db->where('tmp.exposure', $data['searchExposure']);
            }

            //네트워크병원
            if($data['searchNetwork'])
            {
                $this->db->where('tmp.hospitalType', $data['searchNetwork']);
            }

            //db단가, 연산자
            if($data['searchDbCost'] and $data['searchDbCostOperator'])
            {
                switch ($data['searchDbCostOperator'])
                {
                    case 1:
                        $oper = '=';
                        break;
                    case 2:
                        $oper = '>=';
                        break;
                    case 3:
                        $oper = '>';
                        break;
                    case 4:
                        $oper = '<';
                        break;
                    case 5:
                        $oper = '<=';
                        break;
                }
                $this->db->where('tmp.dbCost '.$oper, $data['searchDbCost']);
            }

            //제휴매체
            if($data['searchCooperation'])
            {
                $cooArr = explode(',', $data['searchCooperation']);

                $this->db->where_in('tmp.adMedium', $cooArr);
            }

            //이미지노출
            if($data['searchWhereImage'])
            {
                $imgArr = explode(',', $data['searchWhereImage']);

                $i=1;
                foreach ($imgArr as $img)
                {
                    if($i==1)
                    {
                        $this->db->like('tmp.whereImage', $img);
                    }
                    else
                    {
                        $this->db->or_like('tmp.whereImage', $img);
                    }
                    $i++;
                }
            }

            if($data['searchWord'])
            {
                switch ($data['searchType'])
                {
                    case 1:
                        $this->db->like('fhl.name', $data['searchWord']);
                        break;
                    case 2:
                        $this->db->like('tmp.adTitle', $data['searchWord']);
                        break;
                    case 3:
                        $adsArr = explode(',', $data['searchWord']);

                        if(count($adsArr) > 1)
                        {
                            $this->db->where_in('tmp.id', $adsArr); //여러개인경우
                        }
                        else
                        {
                            $this->db->where('tmp.id', $data['searchWord']); //1개인경우
                        }
                        break;
                }
            }
        }
    }

    /**
     * 임시저장 있는지 확인
     * @param $adsId int
     * @return int
     */
    public function adsTempCheck(int $adsId) : int
    {
        $checkDb = $this->common_m->isInTrans() === true ? $this->master : $this->db;
        
        $checkDb->select('id');
        $checkDb->where('adsId', $adsId)->where('isDelete', 2);
        $cnt = $checkDb->get('ads_temporary')->num_rows();
        
        return $cnt;
    }
}