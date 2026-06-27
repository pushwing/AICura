<?php
/**
 * Created by PhpStorm.
 * User: blumine
 * Date: 2018. 2. 12.
 * Time: PM 5:45
 */

class Ads_m extends CI_Model
{
    private $hospitalQuery = [
        1 => "([ads]isLive='Y' and [ads]adStatus = 2)" // O 진행
        , 2 => "([ads]isLive='Y' and [ads]adStatus = 4 and  [ads]subAdStatus = 5 )" //O 어드민 작성중
        , 3 => "([ads]isLive='Y' and [ads]adStatus = 4 and  [ads]subAdStatus = 6 )" //O 병원 작성중
        , 4 => "([ads]isLive='Y' and [ads]adStatus = 1 and [ads]subAdStatus = 1  )" //O 수정 검토
        , 5 => "([ads]isLive='Y' and [ads]adStatus = 1 and [ads]subAdStatus = 2  )" //O 종료검토
        , 6 => "([ads]isLive='Y' and [ads]adStatus = 5 )" //O 반려
        , 7 => "([ads]isLive='N' and [ads]adStatus = 3 )" //X종료
        , 8 => "([ads]isLive='N' and [ads]adStatus = 4 and  [ads]subAdStatus = 5 )" //X 어드민 작성중
        , 9 => "([ads]isLive='N' and [ads]adStatus = 4 and  [ads]subAdStatus = 6 )" //X 병원 작성중
        , 10 => "([ads]isLive='N' and [ads]adStatus = 5 )" //X 반려
        , 11 => "([ads]isLive='N' and [ads]adStatus = 1 and [ads]subAdStatus=3 )" //x신규등록검토
        , 12 => "([ads]isLive='N' and [ads]adStatus = 1 and [ads]subAdStatus=4 )"//x재등록검토
        , 13 => "([ads]isLive='N' and [ads]adStatus = 5 and [ads]subAdStatus=5 )" //x반려 어드민 작성중
        , 14 => "([ads]isLive='Y' and [ads]adStatus = 5 and [ads]subAdStatus=5 )" //o반려 어드민 작성중
    ];

    function __construct()
    {
        parent::__construct();

        /**
         * 1 옵션여부, 2 옵션이벤트번호, 3 db단가, 4 메모, 5 카테고리, 6계약대상(수주계약번호), 7 이벤트타입, 8 병원id, 9 이벤트명, 10 네트워크병원,
         * 11 기간, 12 정상가, 13 할인가, 14 텍스트가격, 15 노출영역, 16 지역, 17 제휴매체 18 모델동의, 19 키워드, 20 모델이미지갯수,
         * 21 후기노출여부, 22 신청버튼, 23  썸네일1, 24 썸네일2, 25 상세1, 26 상세2, 27 상세3, 28 상세4, 29 상세5, 30 상세6
         * 31 이벤트명, 32 기간연장여부, 33 가격타입    public function test_Encrpyt_server() 

         */
        
    }

    /**
     * 광고 입력시 히스토리 데이터 정제
     * @param $data
     * @param $adsId
     * @return array
     */
    function refineHistoryData($data, $adsId)
    {
        //매핑 안되는것 삭제
        unset($data['token']);
        unset($data['menu_id']);
        unset($data['users_id']);
        unset($data['hospitalType']);
        unset($data['contractId']);
        unset($data['subHospitalId']);

        $i=0;
        $hData = [];

        foreach ($data as $key=>$val)
        {
            $hData[$i]['type'] = $this->historyArr[$key];
            $hData[$i]['adsId'] = $adsId;
            $hData[$i]['beforeChange'] = $data[$key];
            $hData[$i]['afterChange'] = $data[$key];

            $i++;
        }

        return$hData;
    }

    /**
     * 광고 등록
     * 관련테이블 입력후 검수테이블
     * 181207 관련 테이블 + 히스토리 최초 등록
     * 181207 검수 테이블은 승인?? 을 누르게 되면 넣는다
     * 191114 광고 등록후 이벤트를 파일화 한다. (히스토리 관리 차원)
     * @param $data
     * @return bool|int
     */
    function setAds($data)
    {
        /**
         * 리프레쉬 토큰 제거
         */
        if ( isset($data['refreshToken']) )
        {
            unset($data['refreshToken']);
        }

        $date_ymd = date("Y-m-d");
        $date_his = date("H:i:s");
        $date = $date_ymd.' '.$date_his;

        $tempArr = $data;
        //메소드 내부에서 트랜잭션이 시작인지 체크 
        $checkMethodTrans = false;
        if ($this->common_m->isInTrans() === false)
        {
            $this->master->trans_begin();
            $checkMethodTrans = true;   
        }

        //계약명 가져오기
        $contArr = $this->master->get_where('contract', ['id'=>$data['contractId']])->row_array();

        $tempId = isset($data['tempId'])  ? $data['tempId'] : null;
        unset($data['tempId']);

        //ads 입력
        if(USERAUTHCODE == 2)
        {
            $adDetailInfo = json_encode([
                '','','','','',''
            ]); //버튼관련
        }
        else
        {
            //[buttonName] 신청버튼 문구 0
            //[buttonColor] 신청버튼 색상값 4
            //[buttonNameColor] 신청버튼 문구 색상 5
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

        $historyArr = $aArr = [
            'adTitle'=>$data['adTitle'],
            'channel'=>$data['channel'],
            'adStatus'=>USERAUTHCODE == 2 ? 1 : 4, //검토 1, 작성중 4
            'subAdStatus'=>USERAUTHCODE == 2 ? 3: 5, //3 신규등록검토 6, 어드민 작성중
            'category'=>$data['category'],
            'adStartDate'=>$data['adStartDate'],
            'adEndDate'=>$data['adEndDate'],
            'adDateExtend'=>$data['adDateExtend'],
            'adType'=>$data['adType'],
            'exposure'=>$data['exposure'],
            'costType'=>$data['costType'],
            'generalCost'=>$data['generalCost'],
            'discountCost'=>$data['discountCost'],
            'textCost'=>$data['textCost'],
            'dbCost'=>$data['dbCost'],
            'whereImage'=>$data['whereImage'],
            'modelImageCount'=>$data['modelImageCount'],
            'adDetailInfo'=>$adDetailInfo,
            'regDate'=>$date,
            'modDate'=>$date,
            'contractId'=>$data['contractId'],
            'contractName'=>$contArr['title'],
            'contractOrderId'=>$data['contractOrderId'],
            'hospitalId'=>$data['hospitalId'],
            'hospitalType'=>$data['hospitalType'],
            'agencyUserId'=>$agencyUserId,
            'isViewBoard'=>$data['isViewBoard'],
            'deliberationCode'=>$data['deliberationCode'],
            'customRanding'=>$data['customRanding'],
            'custom1'=>$data['custom1'],
            'custom2'=>$data['custom2'],
            'custom3'=>$data['custom3']
        ];

        $historyArr['subHospitalId'] = isset( $data['subHospitalId'] ) ? $data['subHospitalId'] : null;
        $historyArr['isViewBoard']   = isset( $data['isViewBoard'] ) ? $data['isViewBoard'] : null;
        $historyArr['optionAdId']    = isset( $data['optionAdId'] ) ? $data['optionAdId'] : null;

        if (USERAUTHCODE == 2) 
        {
            $historyArr['isViewBoard'] = 1;    
        }
        
        //히스토리용 정리
        unset($historyArr['adStatus']);
        unset($historyArr['subAdStatus']);
        unset($historyArr['modDate']);
        //unset($historyArr['contractId']);
        //unset($historyArr['contractOrderId']);
        //unset($historyArr['hospitalId']);
        
        $aArr['dImageJson'] = json_encode($data['dImages'], JSON_UNESCAPED_UNICODE);
        $tempArr = $historyArr;

        $this->master->insert('ads', $aArr);
        $adsId = $this->master->insert_id();
        //임시저장(병원어드민)의 경우 메인노출 테이블 입력 안함
        if((USERAUTHCODE == 2 and $data['temporarySave'] != 2) or USERAUTHCODE == 4 )
        {
            $adsMainMapId = $this->setadsMain([
                'adsId'         => $adsId,
                'adTitle'       => $data['adTitle'],
                'hospitalId'    => $data['hospitalId'],
                'agencyUserId'  => $agencyUserId,
                'date'          => $date
            ]);
        }

        //히스토리 입력
        $hArr2 = [
            'adsId'=>$adsId,
            'userId'=>$data['users_id'],
            'cooperation'=>$data['cooperation'],
            'region'=>$data['region'],
            'keyword'=>$data['keyword'],
            't1ImageName'=>$data['t1ImageName'],
            't2ImageName'=>$data['t2ImageName'],
            'regDate'    =>$date
        ];

        $lArr       = array_merge($historyArr, $hArr2);   
        $tempArr    = array_merge($tempArr, $hArr2);
      
        $lArr['deletejson'] = json_encode($lArr, JSON_UNESCAPED_UNICODE);
        $lArr['dImageJson'] = json_encode($data['dImages'], JSON_UNESCAPED_UNICODE);
        $tempArr['dImages'] = $data['dImages'];
        //dd($lArr);
        $historyId = $this->setHistory($lArr);
        //dd($lArr['deletejson']);
        //광고 JSON 업데이트
        $this->master->where('id', $adsId);
        $this->master->update('ads', ['adsHistoryJson' => $lArr['deletejson'], 'dImageJson' => $lArr['dImageJson'] ]);

        if (USERAUTHCODE == '2') 
        {
            $this->load->model('adstemp_m');
            //var_dump($tempArr);
            $tempArr['adsId'] = $adsId;
            $tempArr['isDelete'] = 1;
            $tempArr['deleteUserId'] = $tempArr['userId'];
            $tempArr['users_id'] = $tempArr['userId'];

            unset($tempArr['userId']);

            $cnt = $this->adstemp_m->adsTempCheck($adsId);
            if ($cnt === 0 )
            {
                $this->adstemp_m->setAdsTemp($tempArr);
            }
            else 
            {
                $this->adstemp_m->updateAdsTemp($tempArr);
            }
        }

        //임시저장(병원어드민)의 경우 검수입력 안함
        if((USERAUTHCODE == 2 and $data['temporarySave'] != 2) or  USERAUTHCODE == 4)
        {   
            //검수타입 . 1 수정검토(O), 2 종료검토(O), 3 수정검토(X), 4 신규등록검토(X), 5 재등록(X)    
            //이벤트 상태. 1 검토, 2 진행, 3 종료, 4 작성중, 5 반려
            //sub이벤트 상태. 1 수정검토, 2 종료검토, 3신규등록검토, 4 재등록 검토, 5 어드민작성중, 6 병원작성중
            //검수 등록
            $this->setInspectingAds([
                'date'          => $date,
                'adStatus'      => 4,//USERAUTHCODE == 2  ? 1 : 4, //x 신규등록검토
                'hospitalId'    => $data['hospitalId'],
                'prevAdStatus'   =>   4, //작성중
                'prevSubAdStatus' =>  USERAUTHCODE == 2 ? 6 : 5, //병원작성중 / 어드민작성중
                'historyId'     => $historyId,
                'adsId'         => $adsId,
                'users_id'      => $data['users_id'],
                'agencyUserId'  => $agencyUserId,
                'adsMainMapId'  => $adsMainMapId
            ]);       
        }

        //임시저장 삭제
        if (!is_null($tempId)) 
        {
            $this->master->where('id', $tempId);
            $this->master->update('ads_temporary', ['isDelete' => 1, 'deleteUserId' => $data['users_id'], 'delDate' => $date]);
        }

        //리플리케이터 시작
        //d이미지 처리용
         $iii=1;
         $dImageArr = [];
        foreach($data['dImages'] as $key => $val){
            $dImageArr[] = ['client_sort'=>$iii, 'client_image'=>$val];    
            $iii++;
        }
       
        /*
        $imgArr2 = [
            'd1ImageName','d2ImageName','d3ImageName',
            'd4ImageName','d5ImageName','d6ImageName'
        ];

        $iii=1;
        $dImageArr = [];
        foreach ($imgArr2 as $item2)
        {
            if($data[$item2])
            {
                 $dImageArr[] = ['client_sort'=>$iii, 'client_image'=>$data[$item2]];
            }
            $iii++;
        }
        */

        if(count($dImageArr) > 0)
        {
            $dImageArrJson = json_encode($dImageArr);
        }
        else
        {
            $dImageArrJson = ''; //값이 없다면 빈값으로......
        }
        
        //커스텀 랜딩처리
        //if($data['custom1'] and $data['custom2'] and $data['custom3'])
        $customDataArr = [];
        $checkCustomArr = [
            'custom1' => 'leader_name'
            , 'custom2' => 'operation_register_name'
            , 'custom3' => 'contact'
        ];

        $encodeCheck = false;
        foreach($checkCustomArr  as $key => $val)
        {
            if( isset($data[$key]) && !is_null($data[$key]))
            {
                $customDataArr[$val] = $data[$key];
                $encodeCheck = true;
            }
            else
            {
                $customDataArr[$val] = '';
            }
        }

        if(@$customDataArr['leader_name'] == '' and @$customDataArr['operation_register_name'] == '' and @$customDataArr['contact'] == '')
        {
            $encodeCheck = false;
        }

        $customData = $encodeCheck === true ? json_encode($customDataArr)  : '';

        //노출영역 처리
        $isE = '0';
        $isH = '0';
        if($data['exposure'] == 3)
        {
            //둘다 라면
            $isE = '1';
            $isH = '1';
        }
        else if($data['exposure'] == 2)
        {
            $isE = '0';
            $isH = '1';
        }
        else if($data['exposure'] == 1)
        {
            $isE = '1';
            $isH = '0';
        }

        //옵션처리
        $oArr = [];
        if($data['adType'] == '5')
        {
            $subArr = explode(',', $data['optionAdId']);

            foreach ($subArr as $item3)
            {
                $this->master->select('adTitle');
                $rTitle = $this->master->get_where('ads', ['id'=>$item3])->row_array();
                $oArr[] = ['event_id'=>$item3, 'label'=>$rTitle['adTitle']];
            }

            if(count($oArr) > 0)
            {
                $oArrJson = json_encode($oArr);
            }
            else
            {
                $oArrJson = ''; //값이 없다면 빈값으로......
            }
        }
        else
        {
            $oArrJson = '';
        }   
        $reGeneralCost = isset($data['generalCost']) && !empty($data['generalCost']) ? $data['generalCost'] : 0;
        $reGeneralCost = is_null($reGeneralCost) ? 0 : $reGeneralCost;

        $reDiscountCost = isset($data['discountCost']) && !empty($data['discountCost']) ? $data['discountCost'] : 0;
        $reDiscountCost = is_null($reDiscountCost) ? 0 : $reDiscountCost;

        $repData = [
            'id'=>$adsId,
            'event_type'=>($data['channel']==2)?5:1, //일반이벤트: 1, 프로모션이벤트: 2, CPC 이벤트: 4, 굿닥파트너스: 5
            'contract_ids'=> $data['contractId'],
            'type_info'=> (USERAUTHCODE == 4)?'admin_info':'client_info',
            'client_searchable'=>($data['channel']==2)?0:1,
            'client_title'=>$data['adTitle'],
            'client_is_temporary'=> ($data['adDateExtend']=='Y')?'1':'0', // 1 상시진행, 0 기간설정
            'client_start_on'=>$data['adStartDate'],
            'client_end_on'=>$data['adEndDate'],
            'client_is_numerical_original_price'=>($data['costType'] == 1)?'1':'0', //0이면 텍스트가격
            'client_numerical_original_price'=>$reGeneralCost,
            'client_original_price'=>($data['costType'] == 1)? $reGeneralCost:0,
            'client_is_numerical_discounted_price'=>($data['costType'] == 1)?'1':'0',
            'client_numerical_discounted_price'=>$reDiscountCost,
            'is_bm_banner_show'=>($data['exposure'] == 1)?'1':'0', //메인배너 노출 여부 (노출 1, 비노출 0)
            'client_discounted_price'=>($data['costType'] == 1)?$reDiscountCost:$data['textCost'],
            //'client_discounted_price'=>'문의',
            'hospital_id'=>$data['hospitalId'], //네트워크 병원 처리 안됨
            'event_infos'=>$dImageArrJson,
            'client_event_category_ids'=>$data['category'],
            'client_image2'=>$data['t2ImageName'],
            'client_image'=>$data['t1ImageName'],
            'search_tags'=>$data['keyword'],
            'client_consider_number'=>is_null($data['deliberationCode'])?'':$data['deliberationCode'], //의료심의번호
            'model_image_ids'=>(is_null($data['whereImage']))?'':$data['whereImage'],
            'external_media_category_ids'=>$data['cooperation'],
            'event_cost'=>$data['dbCost'],
            //'apply_text'=>'이벤트 신청하기',
            'apply_text'=> (@$data['buttonName'] != '')?@$data['buttonName']:'이벤트 신청하기',
            'apply_back_color' => isset($data['buttonColor']) && $data['buttonColor'] != '' ? @$data['buttonName'] : '#1662bb', //버튼 컬러
            'apply_text_color' => isset($data['buttonNameColor']) && $data['buttonNameColor'] != '' ? @$data['buttonNameColor'] : '#ffffff', //버튼 텍스트 컬러
            'event_regions'=>$data['region'],
            'apply_image_count'=>(is_null($data['modelImageCount']))?'0':$data['modelImageCount'],
            'option_event_infos'=>$oArrJson,
            'hospital_operator_infos'=>$customData,
            'is_visible_on_event_list'=>$isE,
            'is_visible_on_hospital_show'=>$isH
        ]; //var_dump($repData);
       
        $loginsData = $repData;
        $loginsData["method"] =[ 'POST', '/api/events'];
        monologSend('adsInsert', json_encode($loginsData));
        $result = $this->replicator_m->send('/api/events', 'POST', $repData); //var_dump($result); exit;
        monologSend('adsInsert', $result);
        //리플리케이터 끝

        //트랜잭션
        if ($checkMethodTrans === true )
        {
            if ($this->master->trans_status() === FALSE)
            {
                $this->master->trans_rollback();
                return false;
            }
            $this->master->trans_commit();
            return $adsId;
        }    
        return $adsId;
    }

    /**
     * 광고 내용상세보기
     * @param $data
     * @return array
     */
    function getAds($data)
    {       
        if(USERAUTHCODE == 2 && isset($data['hospitalId']) )
        {
            $param['hospitalId'] = $data['hospitalId'];
        }
            
        $param['adsId'] = is_array($data) ?  $data['adsId'] : $data;


        $cnt = $this->db->get_where('ads' ,['id'  => $param['adsId'], 'isDelete' => 'N'])->num_rows();

        if($cnt== 0)
        {
            return false;
        }
        
        $result = $this->gethistoryMerge($param);
        
        if(count($result) == 0)
        {
            return false;
        }

        //JSON 및 가상 컬럼
        $vColumns = [
            'adsHistoryJson', 'vT1ImageName', 'vD1ImageName', 'vD2ImageName', 'vD3ImageName'
            , 'vD4ImageName', 'vD5ImageName', 'vD6ImageName', 'vT2ImageName', 'vAdStartDate'
            , 'vHospitalId', 'vAdEndDate', 'vAdDateExtend', 'vCategory', 'vAdType', 'vAdTitle'
            , 'vRegion', 'vExposure', 'vHospitalType', 'vDbCost', 'vCooperation', 'vWhereImage'
            , 'vAgencyUserId', 'vContractId', 'vContractName', 'vContractOrderId'
        ];  
       
        foreach($vColumns as $vColumn)
        {
            if (isset($result[$vColumn]))
            {
                unset($result[$vColumn]);
            } 
        }
    
        //버튼정보 가공
        $bt = json_decode($result['adDetailInfo'], true);
        $result['buttonName'] = $bt[0];
        $result['buttonLink'] = $bt[1];
        $result['buttonType'] = $bt[2];
        $result['buttonPhone'] = $bt[3];
        $result['buttonColor'] = $bt[4];
        $result['buttonNameColor'] = $bt[5];
    
        //본문 이미지 배열로 
        $result['dImages'] = is_null($result['dImageJson']) || empty($result['dImageJson']) ? '[]' : $result['dImageJson']; 
        unset($result['dImageJson']);

        for ($i=1; $i < 51; $i++){
            if (isset($result['d'.$i.'ImageName'])){
                unset($result['d'.$i.'ImageName']);
            }
        }

        //181227 임시저장 정보 ID  
        $tempAdsId = is_array($data) ?  $data['adsId'] : $data;
        $this->db->select('id');
        $tempResult = $this->db->get_where('ads_temporary', ['adsId' =>$tempAdsId, 'isDelete' => 2])->result_array();

        $result['tempId'] = count($tempResult) == 0 ? null : $tempResult[0]['id'];

         //dd($data);
        //190102 병원명 가져오기

        $hospitalNameArr = $this->goodocapi->getHospitalNamesByIds([$result['hospitalId']]);
        $result['hospitalName'] = count($hospitalNameArr) == 0 ? '' : $hospitalNameArr[$result['hospitalId']];

        //후기정보 가져오기
        $this->db->select('count(*) boardCount, bs.rateSum', false);
        $this->db->join('board_summary bs', 'board.targetId=bs.targetId and board.type=bs.type');
        $bInfo = $this->db->get_where('board', ['board.type'=>1, 'board.targetId'=>$data['adsId']])->row_array();
        $result['boardCount'] = $bInfo['boardCount'];
        $result['rateSum'] = $bInfo['rateSum'];

        return $result;
    }

    /**
     * 히스토리 상세 입력 - 사용안함. ads_history로 통합
     * 타입. 1 옵션여부, 2 옵션이벤트번호, 3 db단가, 4 메모, 5 카테고리, 6계약대상,  7 이벤트타입, 8 병원명, 9 이벤트명, 10 네트워크병원,
     * 11 기간, 12 정상가, 13 할인가, 14 텍스트가격, 15 노출영역, 16 지역, 17 제휴매체 18 모델동의, 19 키워드, 20 모델이미지갯수,
     * 21 후기노출여부, 22 신청버튼, 23  썸네일1, 24 썸네일2, 25 상세1, 26 상세2, 27 상세3, 28 상세4, 29 상세5, 30 상세6
     *
     * 사용안함
     *
     * @param $data
     * @return number
     */
    function setHistoryDetail($data)
    {
        if ( isset($data['refreshToken']) )
        {
            unset($data['refreshToken']);
        }
        $this->master->insert('ads_history_detail', $data);
        //dd($this->master->last_query(), false);
        return $this->master->insert_id();
    }


    /**
     * 광고 수정
     * @param $data
     * @return bool|int
     */
    function updateAds($data)
    {
        if ( isset($data['refreshToken']) )
        {
            unset($data['refreshToken']);
        }

        $date_ymd = date("Y-m-d");
        $date_time = date("H:i:s");

        unset($data['tempId']);

        if ( isset($data['refreshToken']) )
        {
            unset($data['refreshToken']);
        }

        $date = $date_ymd.' '.$date_time;   

        if (USERAUTHCODE == 2) 
        {
            $data['isViewBoard'] = 1;    
        }
        
        //메소드 내부에서 트랜잭션이 시작인지 체크 
        $checkMethodTrans = false;
        if ($this->common_m->isInTrans() === false)
        {
            $this->master->trans_begin();
            $checkMethodTrans = true;   
        }
        
        //히스토리 정보 머지
        $historyInfo = $this->gethistoryMerge($data);
        //dd($historyInfo);
        if (isset($historyInfo['dImageJson'])) {
            unset($historyInfo['dImageJson']);
        }

        //버튼 
        $buttons = ['buttonName', 'buttonLink', 'buttonType', 'buttonPhone', 'buttonColor', 'buttonNameColor'];
        $adDetailInfo = [];
        foreach($buttons as $button)
        {
            if (isset($data[$button]))
            {   
                $adDetailInfo[] = $data[$button];
            } 
            else 
            {
                $adDetailInfo[] = null;
            }
            unset($data[$button]);
        }

       $data['adDetailInfo'] =  json_encode($adDetailInfo);
        
       if (!isset($data['agencyUserId'] ) )
       {
            $data['agencyUserId'] =  $this->common_m->getAgencyUserId($data);
       }
       
       //이미지 
       $checkImgArr = ['t1ImageName', 't2ImageName'];
    
        foreach($checkImgArr as $key => $val)
        {
            if(isset($historyInfo[$val]) && !isset($data[$val]))
            {
                $data[$val] = null;   
            }
        }
     
        //넘어온 데이터와 히스토리인포 diff 
        $data_diff = array_diff_assoc($data, $historyInfo);
        $unsetArr = [
            'users_id', 'menu_id', 'token', 'temporarySave', 'dImageJson', 'dImages'
        ];
        foreach($unsetArr as $key => $val)
        {
            unset($data_diff[$val]);
        }
        
        for($i = 0; $i < 7; $i++)
        {
            unset($data_diff['d'.$i.'ImageName']);
        }

        $data_diff['userId']     = $data['users_id'];
        $data_diff['adsId']      = $data['adsId'];
        $data_diff['regDate']    = $date_ymd;
        //$data_diff['dImageJson'] = json_encode($data['dImages'],JSON_UNESCAPED_UNICODE);
        
        $data_diff['deletejson'] = json_encode($data_diff, JSON_UNESCAPED_UNICODE);
        $data_diff['dImageJson'] = json_encode($data['dImages'],JSON_UNESCAPED_UNICODE);

        //히스트로 등록 
        $historyId = $this->setHistory($data_diff);

        //광고 JSON 업데이트
        $adsHistoryJson = json_encode( array_merge( $historyInfo, $data_diff),  JSON_UNESCAPED_UNICODE);
        $this->master->where('id', $data_diff['adsId'] );
        $this->master->update('ads', ['adsHistoryJson' => $adsHistoryJson, 'dImageJson' =>json_encode($data['dImages'],JSON_UNESCAPED_UNICODE), 'modDate' => $date]);
        
        /**
         * 수정시에도 검수 등록 추가
         */
       
        //임시저장(병원어드민)의 경우 메인노출 테이블 입력 안함
        if((USERAUTHCODE == 2 and $data['temporarySave'] != 2)  or USERAUTHCODE == 4)
        {
             //이전 상태 가져온다
             $this->master->select('isLive, adStatus, subAdStatus');
             $adsresult = $this->master->get_where('ads', ['id' => $data['adsId']])->result_array();

             $prevAdStatus = 4; //작성중
             $prevSubAdStatus = 6;//병원작성중
             $prevIsLive = 'N';
             if (isset($adsresult[0])) {
                 $prevAdStatus = $adsresult[0]['adStatus'];
                 $prevSubAdStatus = $adsresult[0]['subAdStatus'];
                 $prevIsLive = $adsresult[0]['isLive'];
             }
             unset($adsresult);

             $this->master->select('id');
             //$this->db->where('inspectDate <> ', '0000-00-00 00:00:00');
             $this->master->where('inspectDate is not null');
             $liveCnt = $this->master->get_where('ads_history', ['adsId' => $data['adsId']])->num_rows();

             //검수타입 . 1 수정검토(O), 2 종료검토(O), 3 수정검토(X), 4 신규등록검토(X), 5 재등록(X)
             //181226 검수통과여부 및 라이브 상태 가져와서 수정검토O 인지 X인지 확인
             //181226 검수타입중 수정검토X 빠짐. 재등록 검토X로 사용
             $updateAdsParam = [];
             $updateAdsParam['adStatus'] = USERAUTHCODE == 2 ? 1 : 4; //검토 1, 작성중 4

             //반려 중일 때  어드민이 수정 하면 반려 / 어드민 작성 중
             if (USERAUTHCODE == 4 && $prevAdStatus == 5) {
                 $updateAdsParam['adStatus'] = 5;
             }

            if ($liveCnt == 0)
            {
                 //검수타입 . 1 수정검토(O), 2 종료검토(O), 3 수정검토(X), 4 신규등록검토(X), 5 재등록(X)
                 $inspectAdStatus = 4;
                 //sub이벤트 상태. 1 수정검토, 2 종료검토, 3신규등록검토, 4 재등록 검토, 5 어드민작성중, 6 병원작성중
                 $updateAdsParam['subAdStatus'] = USERAUTHCODE == 2 ? 3 : 5; //신규 또는 어드민 작성

                 //190103 리스트액션은 히스토리에 쌓이지 않는다. 히스토리 참조 라이브 했던 유무 확인 어려움
                 //병원이고 이전 라이브 상태값이 Y 이면 수정 검토로 바꾼다
                if (USERAUTHCODE == 2 && $prevIsLive == 'Y')
                {
                     $inspectAdStatus = 1;
                     $updateAdsParam['subAdStatus'] = 1;
                }
            }
            else
            {
                 $inspectAdStatus = $prevIsLive == 'Y' ? 1 : 5;

                if (USERAUTHCODE == 2) {
                    $updateAdsParam['subAdStatus'] = $prevIsLive == 'Y' ? 1 : 4; //수정 또는 재등록 검토
                } else {
                    $updateAdsParam['subAdStatus'] = 5; //어드민 작성
                }
            }

            $this->master->where('id', $data['adsId']);
            $this->master->update('ads', ['adStatus' => $updateAdsParam['adStatus'], 'subAdStatus' => $updateAdsParam['subAdStatus'], 'modDate' => $date]);

            $setAdTitle = isset($data_diff['adTitle']) ? $data_diff['adTitle'] : $data['adTitle'];
            $setHospitalId = isset($data_diff['hospitalId']) ? $data_diff['hospitalId'] : $data['hospitalId'];
            $setAgencyUserId = isset($data_diff['agencyUserId']) ? $data_diff['agencyUserId'] : $data['agencyUserId'];

            //191128 다시 검수등록
            //190116 어드민 작성시 검수 태우지 않는
//            if (USERAUTHCODE != 4)
//            {
            $adsMainMapId = $this->setadsMain([
                'adsId' => $data_diff['adsId'],
                'adTitle' => $setAdTitle,
                'hospitalId' => $setHospitalId,
                'agencyUserId' => $setAgencyUserId,
                'date' => $date
            ]);
            //var_dump($adsMainMapId);

            if (USERAUTHCODE == 4) //운영자이면 검수 안된 이전 검수리스트 삭제
            {
                $this->master->delete('inspecting_ads', ['adsId'=>$data_diff['adsId'], 'status'=>1]);
            }

            //검수 등록
            $this->setInspectingAds([
               'date'          => $date,
               'adStatus'      => $inspectAdStatus,
               'hospitalId'    => $setHospitalId,
               'historyId'     => $historyId,
               'prevAdStatus'   =>   $prevAdStatus,
               'prevSubAdStatus' =>  $prevSubAdStatus,
               'adsId'         => $data_diff['adsId'] ,
               'users_id'      => $data['users_id'],
               'agencyUserId'  => $setAgencyUserId,
               'adsMainMapId'  => $adsMainMapId,
            ]);
            //}
        }

        //임시저장 삭제 추가
        $this->master->where('adsId', $data_diff['adsId'] );
        $this->master->update('ads_temporary', ['isDelete' => 1, 'deleteuserId' => $data['users_id'], 'delDate' => $date]);
        //var_dump('44');
        
        //트랜잭션
        if ($checkMethodTrans === true)
        {
            if ($this->master->trans_status() === FALSE)
            {
                $this->master->trans_rollback();
                return false;
            }
            $this->master->trans_commit();
            return $historyId;
        }
        return $historyId;
    }   

    /**
     * 광고리스트 상태별 카운트
     * @return mixed
     */
    function getListCount($data)
    {
        //[adStatus] 광고상태 a 전체, 1 검토, 2 진행, 3 종료, 4 작성중
        //[subAdStatus] sub이벤트 상태. 1 수정검토, 2 종료검토, 3신규등록검토, 4 재등록 검토, 5 어드민작성중, 6 병원작성중
        if(USERAUTHCODE  == 2)
        {   
            $hospitalId = $this->common_m->getHospitalId($data);
            //임시저장
            $this->db->where('hospitalId', $hospitalId);
            $tempCnt = $this->db->get_where('ads_temporary', [ 'isDelete' => 2])->num_rows();
             
            //또는 그냥 전체만
            $result = [
                'total' => 0, 'write' => 0, 'reject' => 0, 'progress' => 0, 'review' => 0, 'close' => 0
            ];
        
            foreach($result as $key => $val)
            {
                $whereSql = [];
                switch ($key) 
                {
                    case 'total': 
                        $whereSql = $this->hospitalQuery;
                        //unset($whereSql[3], $whereSql[9]); //병원작성중 삭제
                        break;   
                        
                    case 'write':
                        $whereSql = [
                            $this->hospitalQuery[9] //x 병원 작성중
                            ,$this->hospitalQuery[3] //O 병원 작성중  USERAUTHCODE=2 는 병원임
                        ];
                        break;
                    case 'reject':
                        $whereSql = [
                            $this->hospitalQuery[10]  //X 반려 
                            ,$this->hospitalQuery[6] //O 반려
                        ];
                        break;
                    case 'progress':
                        $whereSql = [
                            $this->hospitalQuery[1]  //O 진행 
                            ,$this->hospitalQuery[2] //O 어드민 작성중
                            ,$this->hospitalQuery[4] //O 수정검토
                            ,$this->hospitalQuery[5] //O 종료검토
                            ,$this->hospitalQuery[6] //O 반려
                        ];
                        break;   
                    case 'review':
                        $whereSql = [
                            $this->hospitalQuery[4]  //O 수정검토 
                            ,$this->hospitalQuery[5] //O 종료검토
                            ,$this->hospitalQuery[11] //x 신규등록검토
                            ,$this->hospitalQuery[12] //x 재등록검토 
                        ];
                        break;
                    case 'close':
                        $whereSql = [
                            $this->hospitalQuery[7] //X종료
                        ];    
                        break;
                }
                $whereSql = str_replace(['[ads]'] , [''], implode(' or ', $whereSql));

                //channel 추가
                if($data['channel'])
                {
                    //a 이면 전체
                    $this->db->where('channel', $data['channel']);
                }

                $this->db->select('count(*) cnt');
                $this->db->where('('.$whereSql.')');
                //$this->db->where('(adType = 1 or adType = 5)');
                $this->db->where('vhospitalId', $hospitalId);
                $this->db->where('isDelete = "N"');
                $count = $this->db->get('ads')->row_array();
                $result[$key] = $count['cnt'];

                if(in_array($key, ['total', 'write'])) 
                {
                    $result[$key] =  $result[$key] + $tempCnt;
                }
            }
    
        }
        else
        {
            //병원에서 작성중인것은뺀다
            //반려도 뺸다
            //이벤트 상태. 1 검토, 2 진행, 3 종료, 4 작성중, 5 반려
            //sub이벤트 상태. 1 수정검토, 2 종료검토, 3신규등록검토, 4 재등록 검토, 5 어드민작성중, 6 병원작성중
            //어드민 작성중   진행    검토    종료
            //병원작성중(임시저장) 빼고 토탈 확인
            $rTotal = $this->db->select('count(*) cnt')->where('
                (
                    ( adStatus = 4 AND subAdStatus = 5 ) OR adStatus IN (1,2,3,5)
                )
            ')->get_where('ads', ['isDelete' => 'N'])->row_array();
            $result['total'] = $rTotal['cnt'];

            $rTotal = $this->db->select('count(*) cnt')->where('((adStatus = 4 and subAdStatus=5) OR adStatus = 5)')->get_where('ads', ['isDelete' => 'N'])->row_array();
            $result['write'] = $rTotal['cnt'];

            $rTotal = $this->db->select('count(*) cnt')->get_where('ads', ['adStatus'=>2, 'isDelete' => 'N'])->row_array();
            $result['progress'] = $rTotal['cnt'];

            $rTotal = $this->db->select('count(*) cnt')->get_where('ads', ['adStatus'=>1, 'isDelete' => 'N'])->row_array();
            $result['review'] = $rTotal['cnt'];

            $rTotal = $this->db->select('count(*) cnt')->get_where('ads', ['adStatus'=>3, 'isDelete' => 'N'])->row_array();
            $result['close'] = $rTotal['cnt'];
        }

        return $result;
    }

    /**
     * 광고 리스트
     * @param $data
     * @return array
     */
    function getAdsList($data)
    {
        if(USERAUTHCODE == 4)
        {
            if($data['status'] == 'a')
            {
                $data['status'] = '';
            }

            if($data['searchAdType'] == 'a')
            {
                $data['searchAdType'] = '';
            }

            if($data['searchRegion'] == 'a')
            {
                $data['searchRegion'] = '';
            }
        }
        else
        {
            if($data['status'] == 'a')
            {
                $data['status'] = '';
            }

            if($data['searchAdType'] == 'a')
            {
                $data['searchAdType'] = '';
            }
        }
        
       
        //전체 카운트용
        $this->_where($data);
        $this->db->select('ads.*');
        $this->db->join('ads_history ah', 'ads.id=ah.adsId', 'left');
        $this->db->join('ads_history_memo ahm', 'ads.id=ahm.adsId', 'left');
        //$this->db->join('ads_history_mem ahm', 'ads.id=ahm.adsId', 'left');
        $this->db->group_by('ads.id');
        $result0 = $this->db->get('ads')->result_array(); //echo $this->db->last_query(); exit;
        monologSend('adsListQuery',$this->db->last_query(), []);
        //메모 검색은 쿼리 한번 더 돌려서 처리
        $adsArr0 = $adsArr1 = [];
        /*
        //Message: preg_match(): Compilation failed: regular expression is too large at offset 80027
        //Filename: database/DB_query_builder.php
        //으로 인해 변경
        if($data['searchType'] == 4)
        {
            foreach ($result0 as $item1)
            {
                $adsArr1[] = $item1['id'];
            }

            //메모에 해당하는 adsId 구하기
            $mResult = $this->db->like('memo', $data['searchWord'], 'both')->group_by('adsId')->where_in('adsId', $adsArr1)->get('ads_history_memo')->result_array();

            //위 adsId 에 해당하는 row만 배열에 담고 리턴
            foreach ($result0 as $item2)
            {
                foreach ($mResult as $mr)
                {
                    //echo $mr['adsId'] .'==', $item2['id'].'<br>';
                    if($mr['adsId'] == $item2['id'])
                    {
                        $adsArr0[] = $item2;
                    }
                }

            }
        }
        else
        {
            //dd(['2', $result0], false);
            $adsArr0 = $result0;
        }
        */
        $adsArr0 = $result0;
        $totCnt = count($adsArr0);

        $adsArr = [];
        if($totCnt > 0)
        {
            //검색데이터
            $this->_where($data);
            
            $limit = ($data['page'] - 1) * $data['limit'];
            $this->db->limit($data['limit'], $limit);

            /**
             * 190103 networkCount 제거
             */
            $this->db->select('
                ads.id, ads.isLive, ads.adStatus, ads.subAdStatus, ads.vHospitalId as hospitalId,
                ads.vAgencyUserId as agencyUserId,  ads.vAdTitle as adTitle, ads.vCategory as category, 
                ads.vAdStartDate as adStartDate, ads.vAdEndDate as adEndDate, ads.regDate, ads.modDate, 
                ads.vHospitalType as hospitalType, ads.vAdType as adType, ads.vDbCost as dbCost, 
                ads.vContractName as contractName, ads.vContractId as contractId, ads.vContractOrderId as contractOrderId,
                ads.vT1ImageName as t1ImageName, ads.vT2ImageName as t2ImageName,  ads.vAdDateExtend as adDateExtend   
            ', false);
            $this->db->select('(select id from inspecting_ads where adsId = ads.id order by id desc limit 1) as inspectId');
            $this->db->select('(select ifnull(reason, null) from inspecting_ads where adsId = ads.id and status = 3 order by id desc limit 1) as inspectReason');
            $this->db->join('ads_history ah', 'ads.id=ah.adsId', 'left');
            $this->db->join('ads_history_memo ahm', 'ads.id=ahm.adsId', 'left');
            $this->db->group_by('ads.id');
            $this->db->order_by('ads.id', 'desc');
            
            $result = $this->db->get('ads')->result_array();

            //monologSend('getAdsListQuery', $this->db->last_query());

            /*
            if($data['searchType'] == 4)
            {
                //위 adsId 에 해당하는 row만 배열에 담고 리턴
                foreach ($result as $item2)
                {
                    foreach ($mResult as $mr)
                    {
                        if($mr['adsId'] == $item2['id'])
                        {
                            $adsArr[] = $item2;
                        }
                    }

                }
            }
            else
            {
                $adsArr = $result;
            }
            */
            $adsArr = $result;
        }
        else
        {
           
            return  ['list'=>[], 'totCnt'=>0];  //echo $this->db->last_query(); exit;
        }
        
        $hospitalId_arr    = [];
        //$hospitalNetId_arr = [];
        foreach($result as $key => $val)
        {
            if (!is_null($val['hospitalId']) && !empty($val['hospitalId']) ) {
                $hospitalId_arr[] = $val['hospitalId'];
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
            foreach($result as $key => $val)
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

                $result[$key]['hospitalName'] = $hospitalName;
                $result[$key]['hospitalType'] = $hospitalType;
                unset($hospitalName, $hospitalType);
            }
        } 
        $resultArr = ['list'=>$result, 'totCnt'=>$totCnt];  //echo $this->db->last_query(); exit;
        return $resultArr;
    }

    /**
     * 검색어 처리
     * 운영과 병원 어드민 분기처리
     * @param $data
     */
    function _where($data)
    {
        if(USERAUTHCODE ==2)
        {   
            $whereSql= [];
            //광고상태
             if(isset($data['status']))
             {    
                //이벤트 상태. 1 검토, 2 진행, 3 종료, 4 작성중, 5 반려
                //sub이벤트 상태. 1 수정검토, 2 종료검토, 3신규등록검토, 4 재등록 검토, 5 어드민작성중, 6 병원작성중
                 //자기것만  
                switch($data['status'])
                {
                    case '3,9' : //작성중
                        $whereSql = [
                            $this->hospitalQuery[9] //x 병원 작성중
                            ,$this->hospitalQuery[3] //O 병원 작성중
                        ];   //v씨 똥. 병원 상태인데 왜 어드민것이 들어가 있는지?
                        break;
                    case '1,2,3,4,5,6' : //진행
                        $whereSql = [
                            $this->hospitalQuery[1]  //O 진행 
                            ,$this->hospitalQuery[2] //O 어드민 작성중
                            ,$this->hospitalQuery[4] //O 수정검토
                            ,$this->hospitalQuery[5] //O 종료검토
                            ,$this->hospitalQuery[6] //O 반려
                        ];   
                        break;
                    case '4,5,13,14' :  //검토
                        $whereSql = [
                            $this->hospitalQuery[4]  //O 수정검토 
                            ,$this->hospitalQuery[5] //O 종료검토
                            ,$this->hospitalQuery[11] //x 신규등록검토
                            ,$this->hospitalQuery[12] //x 재등록검토 
                        ];    
                        break;
                    case '6,12' : //반려
                        $whereSql = [    
                            $this->hospitalQuery[10]  //X 반려 
                            ,$this->hospitalQuery[6] //O 반려
                        ];
                        
                        break;
                    case '7' : //종료
                        $whereSql = [
                            $this->hospitalQuery[7] //X종료
                        ];    
                        
                        break;
                    default : //전체
                        //$whereSql = $this->hospitalQuery;
                        $whereSql = [];
                        //unset($whereSql[3], $whereSql[9]); //병원작성중 삭제
                        break;   
                }
            }
            else
            {
                //$whereSql = $this->hospitalQuery;
                $whereSql = [];
                //unset($whereSql[3], $whereSql[9]); //병원작성중 삭제
            }

            if (count($whereSql) > 0)
            {
                $whereSql2 = str_replace(['[ads]'] , ['ads.'], implode(' or ', $whereSql));
                $this->db->where('('.$whereSql2.')');
            }
            
            //병원 인것
            $this->db->where("(
                ads.hospitalId = ".$data['hospitalId']."
                OR
                ads.vHospitalId = ".$data['hospitalId']."
            )");
    
            //광고유형
            if($data['searchAdType'])
            {
                $this->db->where('ads.vAdType', $data['searchAdType']);
            }

            //병원admin용
            if($data['searchCategory'])
            {
                $cateArr = explode(',', $data['searchCategory']);

                $this->db->where_in('ads.VCategory', $cateArr);
            }

            if($data['searchWord'])
            {
                $this->db->like('ads.vAdTitle', $data['searchWord']);
            } 
        }
        else
        {
            //광고상태
            if($data['status'])
            {
                //전체 : a
                //어드민 작성 : 2,8
                //진행 : 1,2,3,4,5,6
                //검토 : 4,5,10,11,13,14
                //종료 7
                $whereSql= '';      
                switch($data['status'])
                {      
                    case '2,8': //어드민 작성
                        $whereSql = "((ads.adStatus = 4 and ads.subAdStatus=5) OR ads.adStatus = 5)";
                        //$whereSql = "(ads.id > 999999999999999999 OR ads.adStatus = 5)";
                        break;
                    case '1,2,3,4,5,6':
                        //$whereSql = "(ads.isLive='Y' and ads.adStatus = 2 )";
                        $whereSql = "(ads.adStatus = 2 )";
                        break;
                    case '4,5,10,11,13,14' :
                        $whereSql = "(ads.adStatus = 1 )";
                        break;
                    case '7':
                        $whereSql = "(ads.adStatus = 3 )";
                        break;
                    default:
                        //continue;
                        //병원에서 작성중인것은뺀다
                        //$this->db->where('( ads.subAdStatus <> 6 )');
                        $whereSql = '
                            (( ads.adStatus = 4 AND ads.subAdStatus = 5 ) OR ads.adStatus IN (1,2,3,5))
                        ';
                        break;
                }
            
                if (!empty($whereSql))
                {
                    $this->db->where($whereSql);
                }
            }
            else 
            {
                //$this->db->where('( ads.subAdStatus <> 6 )');
                $this->db->where('(( ads.adStatus = 4 AND ads.subAdStatus = 5 ) OR ads.adStatus IN (1,2,3,5))');
            }
        
            //상태 셀렉트
            if (isset($data['adStatus']) && !is_null($data['adStatus'])) 
            {
                $adStatus_arr = explode(',' , $data['adStatus']);
                $adStatusSql_arr = [];
              
                foreach($adStatus_arr as $val)
                {
                    $adStatusSql_arr[] = isset($this->hospitalQuery[$val]) ? $this->hospitalQuery[$val] : null;
                }   

                $adStatusSql = str_replace(['[ads]'] , ['ads.'], implode(' or ', $adStatusSql_arr));
                $this->db->where('('.$adStatusSql.')');
            }

            //카테고리
            if($data['searchCategory'])
            {
                $cateArr = explode(',', $data['searchCategory']);

                $this->db->where_in('ads.vCategory', $cateArr);
            }

            //광고유형
            if($data['searchAdType'])
            {
                $this->db->where('ads.vAdType', $data['searchAdType']);
            }

            //광고지역
            if($data['searchRegion'])
            {
                $this->db->where('ads.vRegion', $data['searchRegion']);
            }

            //노출영역
            if($data['searchExposure'])
            {
                $this->db->where('ads.vExposure', $data['searchExposure']);
            }

            //네트워크병원
            if($data['searchNetwork'])
            {
                $this->db->where('ads.vHospitalType', $data['searchNetwork']);
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
                $this->db->where('ads.vDbCost '.$oper, $data['searchDbCost']);
            }

            //제휴매체
            if($data['searchCooperation'])
            {
                $cooArr = explode(',', $data['searchCooperation']);

                $sql = '';
                foreach($cooArr as $key => $val)
                {
                    $sql = empty($sql) ?  '( FIND_IN_SET('.$val.', ads.vCooperation ) )' : ' OR ( FIND_IN_SET('.$val.', ads.vCooperation ) )';
                }

                $sql = '('.$sql.')';

                $this->db->where($sql);  
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
                        $this->db->like('ads.vWhereImage', $img);
                    }
                    else
                    {
                        $this->db->or_like('ads.vWhereImage', $img);
                    }
                    $i++;
                }
            }

            //광고유형
            if($data['searchAdType'])
            {
                $this->db->where('ads.vAdType', $data['searchAdType']);
            }

            if($data['searchWord'])
            {
                switch ($data['searchType'])
                {   
                    case 1:
                        //190102 병원명으로 병원ID 가져와서 검색
                        $hospitalId = $this->goodocapi->getHospitalIdByName($data['searchWord'] );
                        if (sizeof($hospitalId) > 0) {

//                            foreach( $hospitalId as $key=> $val) {
//                                $hospitalIds .= empty($hopistalIds) ? '"'.$val.'"' : ',"'.$val.'"';
//                            }
                            $hospitalIds = implode(',', $hospitalId);
                            
                            $this->db->where('ads.hospitalId IN ('.$hospitalIds.')');
                            //$hospitalIds = implode(',' ,  $hospitalId );
                        }
                        else
                        {
                            $this->db->where('ads.hospitalId is null');
                        }
                        break;
                    case 2:
                        $this->db->like('ads.vAdTitle', $data['searchWord']);
                        break;
                    case 3:
                        $adsArr = explode(',', $data['searchWord']);

                        if(count($adsArr) > 1)
                        {
                            $this->db->where_in('ads.id', $adsArr); //여러개인경우
                        }
                        else
                        {
                            $this->db->where('ads.id', $data['searchWord']); //1개인경우
                        }

                        break;
                    //메모는 쿼리한번 더 돌리고 foreach문 돌려서 처리한다. db파워 보다는 서버 파워로 처리
                    /**
                     * 다시 변경...
                     * Message: preg_match(): Compilation failed: regular expression is too large at offset 80027 Filename: database/DB_query_builder.php
                     */
                    case 4:
                        $this->db->like('ahm.memo', $data['searchWord']);
                        break;
                    //190103 병원 ID 검색 추가
                    case 5:
                        $this->db->where('ads.vHospitalId', $data['searchWord']);
                        break;
                }
            }
            //monologSend('getAdsListParam', $data['searchAgencyUserId']);
            //BIZ-1279
            if(isset($data['searchAgencyUserId']))
            {
                $this->db->where('ads.vAgencyUserId', $data['searchAgencyUserId']);
            }
        }

        //channel 추가
        if($data['channel'] != '')
        {
            //a 이면 전체
            $this->db->where('ads.channel', $data['channel']);
        }

        $this->db->where('ads.isDelete', 'N'); //공용 미삭제
    }

    /**
     * 광고 액션 처리
     * 검수 처리 필요
     * 바로 종료, 승인 처리시 히스토리도 입력 처리.
     * @param $data
     * @return bool
     */
    function updateListAction($data)
    {
        if ( isset($data['refreshToken']) )
        {
            unset($data['refreshToken']);
        }
        

        $date_ymd = date('Y-m-d');
        $date_his = date('H:i:s');

        $date = $date_ymd.' '.$date_his;
        
        //메소드 내부에서 트랜잭션이 시작인지 체크 
        $checkMethodTrans = false;
        if ($this->common_m->isInTrans() === false)
        {
            $this->master->trans_begin();
            $checkMethodTrans = true;   
        }
    
        $this->master->select('
            ads.id, ads.hospitalId, ads.adStatus, ads.subAdStatus, ads.vAdTitle as adTitle, ads.agencyUserId
            , ads.isLive,  ads.adStartDate,  ads.adEndDate,  IFNULL("", ads.vT2ImageName) as t2ImageName, dImageJson
            , ads.channel
        ');
        //$this->master->join('ads_image as ai', 'ads.id = ai.adsId and ai.type ="t2"', 'left');
        $this->master->where_in('ads.id',  $data['adsId']); 
        $adsInfo = $this->master->get('ads')->result_array();


        $t2ImageName =  $data['isHospital'] == 'N'  ? $adsInfo[0]['t2ImageName'] : '';
        $dImageJson =   $adsInfo[0]['dImageJson'];
        /**
         * TODO 상태가 종료이고 이벤트 종료일이 오늘보다 앞날인 광고에 대하여
         * 이벤트 시작일 오늘 날짜, 이벤트 종료일 오늘 날짜 + 30일, 라이브 상태 -> Y로 변경
         */
        //어드민이고 바로 라이브승인이면
        if ( $data['isHospital'] == 'N' && $data['type'] == 2)
        {   
            $adEndDate   = $adsInfo[0]['adEndDate'];

            $now_strtotime = strtotime($date_ymd);
            $adEndDate_strtorime = strtotime($adEndDate);
            
            //비라이드 상태이면서 종료일이 오늘보다 앞날일떄
            if ($adsInfo[0]['isLive'] == 'N' && $adsInfo[0]['adStatus'] == '3'  && ($now_strtotime > $adEndDate_strtorime))
            {
                $adStartDate = $date_ymd;
                $adEndDatePlusThirtyDays  = date("Y-m-d", strtotime("+29 days" ,$now_strtotime));
                
                $param = [
                    'userId'    => $data['users_id']
                    ,'adsId'    => $data['adsId']
                    ,'regDate'  => $date
                    ,'adStartDate' => $adStartDate
                    ,'adEndDate'   => $adEndDatePlusThirtyDays
                ];
            
                $param['deletejson'] = json_encode($param, JSON_UNESCAPED_UNICODE);
                $param['dImageJson'] = $dImageJson;

                //히스트로 등록 
                $this->setHistory($param);
                unset($param);
            }
            unset($adEndDate, $now_strtotime, $adEndDate_strtorime);
        }

        if($data['type'] == 1)
        {
            //바로종료
            //이벤트 상태. 1 검토, 2 진행, 3 종료, 4 작성중, 5 반려
            //sub이벤트 상태. 1 수정검토, 2 종료검토, 3신규등록검토, 4 재등록 검토, 5 어드민작성중, 6 병원작성중
            //검수단계 1 검수대기, 2 통과, 3 반려 4 종료, 5 취소
            //검수타입 1 수정검토(O), 2 종료검토(O), 3 수정검토(X), 4 신규등록검토(X), 5 재등록(X)
            //병원이면 ins 검수대기 , 종료 검토
            if ($data['isHospital'] == 'Y')
            {
                $memo =  '종료검토 요청';

                $insParam = [];
                foreach($adsInfo as $k=>$v)
                {
                    $check_param = [];
                    $check_param['adsId'] = $v['id'];
                    $check_param['status'] = 1;
                    $check_param['adStatus'] = 2;
                    $check_param['userId'] = $data['users_id'];
                    $check_param['hospitalId'] = $v['hospitalId'];
                    $check_param['prevAdStatus'] = $v['adStatus'];
                    $check_param['prevSubAdStatus'] = $v['subAdStatus'];

                    $check_param['agencyUserId']    = $v['agencyUserId'];
                    $check_param['adTitle']         = $v['adTitle'];
                    $check_param['isAdmin']         = 2;
                    $check_param['inspectDate']     = $date;

                    $insParam[] = $check_param;
                    //dd($insParam);
                    $this->master->where('id', $check_param['adsId']);
                    $this->master->update('ads', [ 'adStatus' => 1, 'subAdStatus'=> 2, 'modDate'  => $date]);
                }
            }
            else 
            {   
                $insParam = [
                    [
                        'adsId'=>  $adsInfo[0]['id'], 'status'=>2, 'adStatus' =>2, 'inspectDate'=>$date
                        , 'userId'=>$data['users_id'], 'hospitalId'=> $adsInfo[0]['hospitalId']
                        , 'agencyUserId' => $adsInfo[0]['agencyUserId'], 'adTitle' => $adsInfo[0]['adTitle']
                        , 'prevAdStatus' => $adsInfo[0]['adStatus'], 'prevSubAdStatus' => $adsInfo[0]['subAdStatus'], 'isAdmin' => 1
                    ]
                ];
 
                $memo = '바로종료 처리';              
                //광고
                //$this->master->where('id',  $adsInfo[0]['id']);
                //$this->master->update('ads', [ 'adStatus' => 3,'isLive'   => 'N','modDate'  => $date]);
            }

            //검수  
            foreach( $insParam as $k =>  $v)
            {   
                $check_pararm = $v;
                $check_adsId = $check_pararm['adsId'];
                $check_adTitle = $check_pararm['adTitle'];
                $check_agencyUserId = $check_pararm['agencyUserId'];
                $check_isAdmin      = $check_pararm['isAdmin'];
                unset($check_pararm['adsId'], $check_pararm['adTitle'], $check_pararm['agencyUserId'], $check_pararm['isAdmin']);

                /*
                1901016 로직변경
                */
                //historyId 가져오기
                $this->master->select('MAX(id) as historyId');
                $this->master->where('adsId', $check_adsId);
                $historyInfo = $this->master->get('ads_history')->result_array();

                $historyId = $historyInfo[0]['historyId'];
                unset($historyInfo);

                //main_map
                $adsMainMapId = $this->setadsMain([
                    'adsId'         => $check_adsId,
                    'adTitle'       => $check_adTitle,
                    'hospitalId'    => $check_pararm['hospitalId'],
                    'agencyUserId'  => $check_agencyUserId,
                    'date'          => $check_pararm['inspectDate']
                ]);
                    

                //검수
                $this->setInspectingAds([
                    'date'          => $check_pararm['inspectDate'],
                    'status'        => $check_pararm['status'],
                    'adStatus'      => $check_pararm['adStatus'],  
                    'hospitalId'    => $check_pararm['hospitalId'],
                    'historyId'     => $historyId,
                    'prevAdStatus'   =>   $check_pararm['prevAdStatus'],
                    'prevSubAdStatus' =>  $check_pararm['prevSubAdStatus'],
                    'adsId'         => $check_adsId,
                    'users_id'      => $check_pararm['userId'],
                    'agencyUserId'  => $check_agencyUserId,
                    'adsMainMapId'  => $adsMainMapId,
                    'isAdmin'       => $check_isAdmin
                ]);       

                $hData = [
                    'adsId'   =>$check_adsId,
                    'users_id'=>$check_pararm['userId'],
                    'memo'    =>$memo,
                ];
               $this->setHistoryMemo($hData);
            }
        }        
        else
        {
            //병원이면 ins 검수대기 , 재등록 검토
            if ($data['isHospital'] == 'Y')
            {
                $memo =  '승인검토 요청';

                $insParam = [];
                foreach($adsInfo as $k=>$v)
                {
                    $check_param = [];
                    $check_param['adsId'] = $v['id'];
                    $check_param['status'] = 1;
                    $check_param['adStatus'] = 5;
                    $check_param['userId'] = $data['users_id'];
                    $check_param['hospitalId'] = $v['hospitalId'];
                    $check_param['prevAdStatus'] = $v['adStatus'];
                    $check_param['prevSubAdStatus'] = $v['subAdStatus'];

                    $check_param['agencyUserId']    = $v['agencyUserId'];
                    $check_param['adTitle']         = $v['adTitle'];
                    $check_param['isAdmin']         = 2;
                    $check_param['inspectDate']     = $date;

                    $insParam[] = $check_param;
                    
                    $this->master->where('id', $check_param['adsId']);
                    $this->master->update('ads', [ 'adStatus' => 1, 'subAdStatus'=> 4, 'modDate'  => $date]);
                }
            }
            else 
            {
                $memo = '바로승인 처리';

                //바로승인
                $insParam = [
                    [
                        'adsId' => $data['adsId'], 'status'=>2, 'adStatus' => 5,  'inspectDate'=>$date
                        , 'userId'=>$data['users_id'], 'hospitalId'=>$adsInfo[0]['hospitalId']
                        , 'agencyUserId' => $adsInfo[0]['agencyUserId'], 'adTitle' => $adsInfo[0]['adTitle']
                        , 'prevAdStatus' => $adsInfo[0]['adStatus'], 'prevSubAdStatus' => $adsInfo[0]['subAdStatus'], 'isAdmin' => 1
                    ]
                ];

                //광고
                //$this->master->where('id', $data['adsId']);
                //$this->master->update('ads', ['adStatus'=>2, 'isLive'=>'Y', 'modDate'=>$date]);
            }
            
            //검수  
            foreach( $insParam as $k =>  $v)
            {   
                $check_pararm = $v;
                $check_adsId = $check_pararm['adsId'];
                $check_adTitle = $check_pararm['adTitle'];
                $check_agencyUserId = $check_pararm['agencyUserId'];
                $check_isAdmin      = $check_pararm['isAdmin'];
                unset($check_pararm['adsId'], $check_pararm['adTitle'], $check_pararm['agencyUserId'], $check_pararm['isAdmin']);
                
                /*
                1901016 로직변경
                */
                //historyId 가져오기
                $this->master->select('MAX(id) as historyId');
                $this->master->where('adsId', $check_adsId);
                $historyInfo = $this->master->get('ads_history')->result_array();

                $historyId = $historyInfo[0]['historyId'];
                unset($historyInfo);

                //main_map
                $adsMainMapId = $this->setadsMain([
                    'adsId'         => $check_adsId,
                    'adTitle'       => $check_adTitle,
                    'hospitalId'    => $check_pararm['hospitalId'],
                    'agencyUserId'  => $check_agencyUserId,
                    'date'          => $check_pararm['inspectDate']
                ]);
                    
                //검수
                $this->setInspectingAds([
                    'date'          => $check_pararm['inspectDate'],
                    'status'        => $check_pararm['status'],
                    'adStatus'      => $check_pararm['adStatus'],  
                    'hospitalId'    => $check_pararm['hospitalId'],
                    'historyId'     => $historyId,
                    'prevAdStatus'   =>   $check_pararm['prevAdStatus'],
                    'prevSubAdStatus' =>  $check_pararm['prevSubAdStatus'],
                    'adsId'         => $check_adsId,
                    'users_id'      => $check_pararm['userId'],
                    'agencyUserId'  => $check_agencyUserId,
                    'adsMainMapId'  => $adsMainMapId,
                    'isAdmin'       => $check_isAdmin
                ]);       

                //echo $this->db->last_query();
                //history
                $hData = [
                    'adsId'=>$check_adsId,
                    'users_id'=>$data['users_id'],
                    'memo'=>$memo,
                ];

                $this->setHistoryMemo($hData);
            }

            //파일 생성 및 s3 업로드, cf 배포,
            //리치가 cf 연결해놔서 asset에 올리면 자동 배포됨. cf 퍼지만 추가하면 됨
        }

        //바로액션, 바로종료는 광고쪽에 바로 반영
        if ($data['isHospital'] == 'N')
        {
             $historyMerge =$this->gethistoryMerge(['adsId' => $data['adsId']]);
             
             for ($i=1; $i < 51; $i++){
                unset($historyMerge['d'.$i.'ImageName']);
             }

             log_message('info', 'updateListAction1 '.$data['adsId'].' : '.json_encode($historyMerge,JSON_UNESCAPED_UNICODE));
            
             $dImageJson = empty($historyMerge['dImageJson']) || is_null($historyMerge['dImageJson']) ? [] :  json_decode($historyMerge['dImageJson']);
             $replicaterHistory = $historyMerge;
          
             $imgArr = [
                't1ImageName', 't2ImageName'
             ];
    
            //서브잡 돈다 
            $check_arr = [  
                'hospitalType', 'optionAdId', 'region'
                ,'cooperation', 'keyword' 
            ];
    
            $check_arr = array_merge($check_arr, $imgArr);
            //dd($historyMerge);
          
            $historyMerge['adsHistoryJson'] = json_encode($historyMerge, JSON_UNESCAPED_UNICODE);

            foreach($check_arr as $val)
            {
                if($val != 'hospitalType')
                {
                    unset($historyMerge[$val]);
                }
            } 
    
            $unsetKeys = ['subHospitalId', 'adsId', 'userId', 'optionAdId'];
            foreach($unsetKeys as $key)
            {
                unset($historyMerge[$key]);
            }

            if($data['type'] == 1)
            {
                $historyMerge['adStatus'] = 3;
                $historyMerge['isLive'] = 'N';
            }
            else 
            {
                $historyMerge['adStatus'] = 2;
                $historyMerge['isLive'] = 'Y';
            }  

            //dd($historyMerge);
            $this->master->where('id', $data['adsId'])->update('ads', $historyMerge);
        }  

        //메인 미노출 상태로 전부 돌린 후.
        //진행 중 일 때 템플릿 생성 후 업로드
        if ($data['type'] == 2 && $data['isHospital'] == 'N') 
        {
            $this->make_template(1, $adsInfo[0], $date);
        }
        
        //리플리케이터 시작
        if ($data['isHospital'] == 'N')  
        {   
         
            $iii=1;
            $dImageArr = [];
           
            foreach ($dImageJson as $key=>$val)  {
                $dImageArr[] = ['client_sort'=>$iii, 'client_image'=>$val];
                $iii++;
            }
            
            if(count($dImageArr) > 0)
            {
                $dImageArrJson = json_encode($dImageArr);
            }
            else
            {
                $dImageArrJson = ''; //값이 없다면 빈값으로......
            }
            
            //옵션처리
            $oArr = [];
            if($replicaterHistory['adType'] == '5')
            {
                $subArr = explode(',', $replicaterHistory['optionAdId']);

                foreach ($subArr as $item3)
                {
                    $this->master->select('adTitle');
                    $rTitle = $this->master->get_where('ads', ['id'=>$item3])->row_array();
                    $oArr[] = ['event_id'=>$item3, 'label'=>$rTitle['adTitle']];
                }

                if(count($oArr) > 0)
                {
                    $oArrJson = json_encode($oArr);
                }
                else
                {
                    $oArrJson = ''; //값이 없다면 빈값으로......
                }
            }
            else
            {
                $oArrJson = '';
            }   

            //커스텀 랜딩처리
            //if($data['custom1'] and $data['custom2'] and $data['custom3'])
            $customDataArr = [];
            $checkCustomArr = [
                'custom1' => 'leader_name'
                , 'custom2' => 'operation_register_name'
                , 'custom3' => 'contact'
            ];

            $encodeCheck = false;
            foreach($checkCustomArr  as $key => $val)
            {
                if( isset($replicaterHistory[$key]) && !is_null($replicaterHistory[$key]))
                {
                    $customDataArr[$val] = $replicaterHistory[$key];
                    $encodeCheck = true;
                }
                else 
                {
                    $customDataArr[$val] = '';
                }
            }

            if(@$customDataArr['leader_name'] == '' and @$customDataArr['operation_register_name'] == '' and @$customDataArr['contact'] == '')
            {
                $encodeCheck = false;
            }

            $customData = $encodeCheck === true ? json_encode($customDataArr)  : '';

            /**
             * BIZ-1129
             */
            $reAdDetailInfo = json_decode($replicaterHistory['adDetailInfo']);
            //log_message('info', 'button : '.$replicaterHistory['adDetailInfo']);
            //log_message('info', 'history : '. json_encode($replicaterHistory));
            
            $reApply_text   = isset($reAdDetailInfo[0]) && $reAdDetailInfo[0] != ''  ? $reAdDetailInfo[0] : '이벤트 신청하기';
            $reApply_back_color   = isset($reAdDetailInfo[4]) && $reAdDetailInfo[4] != ''  ? $reAdDetailInfo[4] : '#1662bb';
            $reApply_text_color  = isset($reAdDetailInfo[5]) && $reAdDetailInfo[5] != ''  ? $reAdDetailInfo[5] : '#ffffff';
  
            //노출영역 처리
            $isE = '0';
            $isH = '0';
            if($replicaterHistory['exposure'] == 3)
            {
                //둘다 라면
                $isE = '1';
                $isH = '1';
            }
            else if($replicaterHistory['exposure'] == 2)
            {
                $isE = '0';
                $isH = '1';
            }
            else if($replicaterHistory['exposure'] == 1)
            {
                $isE = '1';
                $isH = '0';
            }
            
            //is_client_image2_change 정방향 이미지 변경여부 체크
            //adOriInfo(업데이트전)의 t2 이미지와 newInfo(히스토리 머지 업데이트 후)의 t2 이미지를 비교
            if($replicaterHistory['t2ImageName'] == $t2ImageName)
            {
                //변경이 없으면
                $is_client_image2_change = 0;
            }
            else
            {
                $is_client_image2_change = 1;
            }
            /**
             * 버튼 변경 안됨 이슈 처리
             */
            $reAdDetailInfo = json_decode($replicaterHistory['adDetailInfo']);
            $reButtonName   = $reAdDetailInfo[0];

            $reGeneralCost = isset($replicaterHistory['generalCost']) && !empty($replicaterHistory['generalCost']) ? $replicaterHistory['generalCost'] : 0;
            $reGeneralCost = is_null($reGeneralCost) ? 0 : $reGeneralCost;

            $reDiscountCost = isset($replicaterHistory['discountCost']) && !empty($replicaterHistory['discountCost']) ? $replicaterHistory['discountCost'] : 0;
            $reDiscountCost = is_null($reDiscountCost) ? 0 : $reDiscountCost;
//dd($replicaterHistory);
            $iData = [
                'id' => $data['adsId'],
                'type_info' =>  'admin_info', //리플리케이터용    
                'contract_ids'=> $replicaterHistory['contractId'],
                'event_type'=>($replicaterHistory['channel']==2)?5:1, //일반이벤트: 1, 프로모션이벤트: 2, CPC 이벤트: 4, 굿닥파트너스: 5
                'client_searchable'=>($replicaterHistory['channel']==2)?0:1,
                'client_title'=>$replicaterHistory['adTitle'],
                'client_is_temporary'=> ($replicaterHistory['adDateExtend']=='Y')?'1':'0', // 1 상시진행, 0 기간설정
                'client_start_on'=>$replicaterHistory['adStartDate'],
                'client_end_on'=>$replicaterHistory['adEndDate'],
                'client_is_numerical_original_price'=>($replicaterHistory['costType'] == 1)?'1':'0', //0이면 텍스트가격
                'client_numerical_original_price'=> $reGeneralCost,
                'client_original_price'=>($replicaterHistory['costType'] == 1)? $reGeneralCost:0,
                'client_is_numerical_discounted_price'=>($replicaterHistory['costType'] == 1)?'1':'0',
                'client_numerical_discounted_price'=>$reDiscountCost,
                'is_bm_banner_show'=>($replicaterHistory['exposure'] == 1)?'1':'0', //메인배너 노출 여부 (노출 1, 비노출 0)
                'client_discounted_price'=>($replicaterHistory['costType'] == 1)? $reDiscountCost:$replicaterHistory['textCost'],
                //'client_discounted_price'=>'문의',
                'hospital_id'=>$replicaterHistory['hospitalId'], //네트워크 병원 처리 안됨
                'event_infos'=>$dImageArrJson,
                'client_event_category_ids'=>$replicaterHistory['category'],
                'client_image2'=>$replicaterHistory['t2ImageName'],
                'client_image'=>$replicaterHistory['t1ImageName'],
                'search_tags'=>$replicaterHistory['keyword'],
                'client_consider_number'=>is_null($replicaterHistory['deliberationCode'])?'':$replicaterHistory['deliberationCode'], //의료심의번호
                'model_image_ids'=>(is_null($replicaterHistory['whereImage']))?'':$replicaterHistory['whereImage'],
                'external_media_category_ids'=>$replicaterHistory['cooperation'],
                'event_cost'=>$replicaterHistory['dbCost'],
                //'apply_text'=>'이벤트 신청하기',
                'apply_text'=> $reApply_text,
                'apply_back_color' => $reApply_back_color, //버튼 컬러
                'apply_text_color' => $reApply_text_color, //버튼 텍스트 컬러
                'event_regions'=>$replicaterHistory['region'],
                'apply_image_count'=>(is_null($replicaterHistory['modelImageCount']))?'0':$replicaterHistory['modelImageCount'],
                'option_event_infos'=>$oArrJson,
                'hospital_operator_infos'=>$customData,
                'is_visible_on_event_list'=>$isE,
                'is_visible_on_hospital_show'=>$isH
            ]; //var_dump($repData);
            $loginsData = $iData;
            $loginsData["method"] =[ 'PATCH', '/api/events/'.$data['adsId']];
            monologSend('event_modify', json_encode($iData, JSON_UNESCAPED_UNICODE));
            $result99 = $this->replicator_m->send('/api/events/'.$data['adsId'], 'PATCH', $iData);
            monologSend('event_modify', $result99);

            //롤백
            if($result99['message'] != 'success' or $result99 == 'Empty reply from server')
            {
                $this->master->trans_rollback();
                return false;
            }
           
            //이벤트 승인처리
            $insData2 = [
                'type_info'=> $data['type'] == 1 ? 'force_end' : 'force_live'
            ];
            monologSend('inspect', json_encode($insData2, JSON_UNESCAPED_UNICODE));
            $result00 = $this->replicator_m->send('/api/events/'.$data['adsId'], 'PATCH', $insData2);
            monologSend('inspect', $result00);

            //롤백
            if($result00['message'] != 'success' or $result00 == 'Empty reply from server')
            {
                $this->master->trans_rollback();
                return false;
            }
        }
        //리플리케이터 끝
        
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
     * 템플릿 만들때 사용
     * @param $templateType int 템플릿 타입
     * @param $adsInfo array 광고 정보
     * @param $date string 날짜
     * @param $fromInspect int 검수 여부. 1 검수에서 요청됨, 0 기타
     * @return bool
     */
    public function make_template(int $templateType=1, array $adsInfo, string $date, int $fromInspect=0) : bool
    {
        if ( isset($adsInfo['refreshToken']) )
        {
            unset($adsInfo['refreshToken']);
        }
        
        $this->load->library('EventUpload');    
        $param = [];
     
        if(USERAUTHCODE == 2 && isset($adsInfo['hospitalId']) )
        {
            $param['hospitalId'] = $adsInfo['hospitalId'];
        }
        $param['adsId'] = $adsInfo['adsId'];

        $adsHistory = $this->gethistoryMerge($param);
        $adsHistory['id']   =  $param['adsId'];

        $hospitalArr = $this->goodocapi->getHospitalInfosByIds([$adsHistory['hospitalId']]); //dd($hospitalArr);
        $adsHistory['hospitalName'] = $hospitalArr[$adsHistory['hospitalId']]['name'];
        $hospitalAddress = explode(' ', $hospitalArr[$adsHistory['hospitalId']]['addr']);
        $adsHistory['hospitalAddress'] = $hospitalAddress[1]. ' ' .$hospitalAddress[2]; //dd($adsHistory);

        //카테고리 정보
        $cateArr = $this->common_m->getCategoryDepth($adsHistory['category']);

        $adsHistory = array_merge($adsHistory, $cateArr); //dd($adsHistory);
        
        //실제 파일 생성 검수에서 호출한 것이면 세번째 파라미터를 1로 줘야 함
        $eventupload = $this->eventupload->file_call($templateType ,$adsHistory, $fromInspect);
        
        if ( !isset($eventupload[0]) )
        {   
            return false;
        }   

        //메인 미노출 업데이트
        $param = [];
        $param['adsId'] = $adsInfo['adsId'];

        $this->master->where('adsId',  $param['adsId']);
        $this->master->update('ads_main_map', ['isMain' => 2]);

        $adsMainId = $this->master->select('id')->get_where('ads_main', ['adsId'=>$param['adsId']])->row_array();
    
        //ads_main_map insert  메인노출 히스토리 테이블
        $abbArr = [
            'adsMainId' => $adsMainId['id'],
            'adsId'     => $param['adsId'],
            'isMain'    => 1, //메인미노출
            'isInspect' => 1, //미검수
            'url'       => $eventupload,
            'regDate'   => $date
        ];

        $this->master->insert('ads_main_map', $abbArr);

        return true;
    }


    /**
     * 히스토리 입력
     * @param $data
     * @return int
     */
    function setHistory($data)
    {
        if ( isset($data['refreshToken']) )
        {
            unset($data['refreshToken']);
        }
        
        $this->master->insert('ads_history', $data);
        return $this->master->insert_id();
    }

    /**
     * 히스토리 테이블에 존재하는지 체크
     * @param $data
     * @return int|bool
     */
    function getHistoryInfo($adsId)
    {
        $this->db->select('id');
        $totCnt = $this->db->get_where('ads_history', ['adsId'=>$adsId])->row_array();

        if($totCnt['id'])
        {
            return $totCnt['id'];
        }
        else {
            return false;
        }
    }

    /**
     * 파일 히스토리 입력
     * @param $data
     * @return int
     */
    function setFileHistory($data)
    {
        if ( isset($data['refreshToken']) )
        {
            unset($data['refreshToken']);
        }
        $this->master->insert('ads_file_history', $data);
        return $this->master->insert_id();
    }

    /**
     * 광고 히스토리 리스트
     * @param $data
     * @return array
     */
    function getHistoryList($data)
    {
        $param = [];
        
        if(USERAUTHCODE == 2 && isset($data['hospitalId']) )
        {
            $param['ads_history.hospitalId'] = $data['hospitalId'];
        }
    
        $param['ads_history.adsId']  = $data['adsId'];

        if(isset($data['historyId'])) 
        {
            $this->db->where('ads_history.id >=', $data['historyId']);
        }

        //승인된 내역만 히스토리에 추가
        $this->db->join('inspecting_ads ia', 'ads_history.id=ia.historyId and ia.status=2');
        $history['totCnt'] = $this->db->get_where('ads_history', $param)->num_rows();

        if (isset($data['limit']))
        {
            $limit = ($data['page'] - 1) * $data['limit'];
            $this->db->limit($data['limit'], $limit);
        }
        
        $orderby = isset($data['orderby']) ? $data['orderby'] : 'desc';
        
        $this->db->order_by('ads_history.id', $orderby);

        //승인된 내역만 히스토리에 추가
        $this->db->select('
            ia.inspectDate, ia.inspectUserId, ads_history.deletejson, ads_history.dImageJson,  ads_history.regDate, ads_history.userId
        '); 
        $this->db->join('inspecting_ads ia', 'ads_history.id=ia.historyId and ia.status=2');
        //$history['info'] = $this->db->get_where('ads_history', $param)->result_array();
        $historyInfo  = $this->db->get_where('ads_history', $param)->result_array();

        $hitoryUnsets = ['userId' , 'adsId', 'regDate'];
        foreach($historyInfo as $key => $val)
        {
            $deleteJson = json_decode( $val['deletejson'], true);
            foreach($hitoryUnsets  as $skey) 
            {
                if (isset( $deleteJson[$skey]))
                {
                    unset($deleteJson[$skey]);
                }
            }

            for ($i=1; $i < 51; $i++){
                unset($deleteJson['d'.$i.'ImageName']);
            }

            $deleteJson['dImages']   = is_null($val['dImageJson']) || empty($val['dImageJson']) ? '[]' : $val['dImageJson'];
            $historyInfo[$key]['editColumns'] = $deleteJson;
            unset($historyInfo[$key]['deletejson']);
        }

       
        $history['info'] = $historyInfo;
        //var_dump($history);
        return $history;
    }

    /**
     * 히스토리 메모 리스트 리턴
     * @param $data
     * @return array
     */
    function getHistoryMemo($data)
    {
        /**
         * 비동기로 바로 이어지는 부분 많아서 master로 변경 함
         */
    
        $this->master->select('ahm.*');
        $this->master->order_by('ahm.id', 'desc');
        $info = $this->master->get_where('ads_history_memo ahm', ['ahm.adsId'=>$data['adsId']])->result_array();
      
        $list = [];
        foreach($info as $key=>$val)
        {
            $userInfo = $this->goodocapi->getUserInfoByUserId((int)$val['userId']);
        
            $username = isset($userInfo['name']) ? $userInfo['name'] : '';
            $val['username'] = $username;
            unset($userInfo);
            $list[] = $val;
        }
        //dd($list);
        return $list;
    }

    /**
     * 히스토리 메모 입력
     * 2018.10.18 추가. 메모는 detail 테이블에 있어서 ads_history에 row가 없을 경우 입력하고 처리한다.
     * ads_history 테이블로 통합
     * 2018.11.26 ads_history_memo 생성. ads_history는 말 그대로 히스토리만 1건씩 입력하도록 구조 변경
     * @param $data
     * @return int
     */
    function setHistoryMemo($data)
    {
        //history 입력
        $iArr = [
            'adsId' => $data['adsId'],
            'userId' => $data['users_id'],
            'memo' => $data['memo'],
            'regDate' => date("Y-m-d H:i:s")
        ];

        $this->master->insert('ads_history_memo', $iArr);

        return $this->master->insert_id();
    }

    /**
     * 기획전 등록
     * @param $data
     * @return bool|int
     * @throws Exception
     */
    function addPackage($data)
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

        
        //ads_package insert
        $arr = $data;
        unset($arr['token']);
        unset($arr['users_id']);
        unset($arr['adsId']);
        unset($arr['menu_id']);

        $arr['userId'] = $data['users_id'];

        if($arr['viewType'] == 0)
        {
            $arr['status'] = 2;
        }
        else
        {
            $arr['status'] = 1;
        }

        $arr['regDate'] = $arr['modDate'] = $date;
        $arr['isDelete'] = 'N';

        $adsArr = explode(',', $data['adsId']);
        $arr['adsCount'] = count($adsArr);

        //노출위치 처리
        $bViewArr = explode(',', $data['bannerViewType']);
        $bCnt = count($bViewArr);

        foreach ($bViewArr as $bv)
        {
            if($bv == 1)
            {
                $arr['bannerViewType1'] = $bv;
            }
            else if($bv == 2)
            {
                $arr['bannerViewType2'] = $bv;
            }
            else if($bv == 3)
            {
                $arr['bannerViewType3'] = $bv;
            }
        }
        unset($arr['bannerViewType']);

        $this->master->insert('ads_package', $arr);
        $packageId = $this->master->insert_id();

        //package_map insert
        $i=1;
        foreach ($adsArr as $item)
        {
            $mArr = [
                'regDate'=>$date,
                'modDate'=>$date,
                'adsPackageId'=>$packageId,
                'adsId'=>$item,
                'order'=>$i
            ];
            $this->master->insert('ads_package_map', $mArr);
            $i++;
        }


        //리플리케이터 시작
        $visible = '0';
        if($data['viewType'] == 1)
        {
            //상시노출 없음. 기간을 길게
            $visible = '2';
            $data['startDate'] = date("Y-m-d");
            $data['endDate'] = date("Y-m-d", strtotime('+1000 days')); //상시노출이 없어서 1000일후로 설정
        }
        else if($data['viewType'] == 2)
        {
            //기간노출
            $visible = '2';
        }

        $adsIdArr = explode(',', $data['adsId']);
        $adsIdArr2 = [];
        $ii=0;
        foreach ($adsIdArr as $item)
        {
            $adsIdArr2[] = ['event_id'=>$item, 'priority'=>$ii];
            $ii++;
        }

        $adsIdArrJson = json_encode($adsIdArr2);

        $iData = [
            'id' => $packageId,
            'visible' => $visible,
            'title' => $data['title'],
            'desc' => $data['detailInfo'],
            'is_show' => 1,
            'is_bm_banner_show' => 1,
            'rank_score' => 1, //우선순위 없음. 무조건 1
            'image' => $data['banner'],
            'start_on' => $data['startDate'],
            'end_on' => $data['endDate'],
            'event_package_events_attributes'=>$adsIdArrJson
        ];
        $this->replicator_m->send('/api/event_packages/', 'POST', $iData);
        //리플리케이터 끝

        //트랜잭션
        //트랜잭션
        if ($checkMethodTrans === true)
        {
            if ($this->master->trans_status() === FALSE)
            {
                $this->master->trans_rollback();
                return false;
            }
            $this->master->trans_commit();
            return $packageId;
        }
        return $packageId;
    }

    /**
     * 기획전 수정
     * @param $data
     * @return bool
     * @throws Exception
     */
    function updatePackage($data)
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

        //ads_package update
        $arr = [];
        $arr['modDate'] = $date;

        if (@$data['title'])
        {
           $arr['title'] = $data['title'];
        }

        if (@$data['bannerViewType'])
        {
            //노출위치 처리
            $bViewArr = explode(',', $data['bannerViewType']);
            $bCnt = count($bViewArr);

            $arr['bannerViewType1'] = $arr['bannerViewType2'] = $arr['bannerViewType3'] = '';

            foreach ($bViewArr as $bv)
            {
                if($bv == 1)
                {
                    $arr['bannerViewType1'] = $bv;
                }
                else if($bv == 2)
                {
                    $arr['bannerViewType2'] = $bv;
                }
                else if($bv == 3)
                {
                    $arr['bannerViewType3'] = $bv;
                }
            }
        }

        if (@$data['viewType'] != '')
        {
            $arr['viewType'] = $data['viewType'];
        }

        if (@$data['banner'])
        {
            $arr['banner'] = $data['banner'];
        }

        if (@$data['detailInfo'])
        {
            $arr['detailInfo'] = $data['detailInfo'];
        }

        if (@$data['viewType'])
        {
            if($data['viewType'] == 0)
            {
                $arr['status'] = 2;
            }
            else
            {
                $arr['status'] = 1;
            }

            if($data['viewType'] == 2)
            {
                $arr['startDate'] = $data['startDate'];
                $arr['endDate'] = $data['endDate'];
            }
        }

        if(@$data['adsId'])
        {
            $adsArr = explode(',', $data['adsId']);
            $arr['adsCount'] = count($adsArr);

            //package_map delete, insert
            $this->master->where('adsPackageId', $data['packageId']);
            $this->master->delete('ads_package_map');

            $i=1;
            foreach ($adsArr as $item)
            {
                $mArr = [
                    'regDate'=>$date,
                    'modDate'=>$date,
                    'adsPackageId'=>$data['packageId'],
                    'adsId'=>$item,
                    'order'=>$i
                ];
                $this->master->insert('ads_package_map', $mArr);
                $i++;
            }
        }

        $this->master->where('id', $data['packageId']);
        $this->master->update('ads_package', $arr);
        log_message('info', 'packageUpdate-'.json_encode($arr));

        //리플리케이터 시작

        //업데이트된 값을 가져와서 그대로 v1에 전송한다.
        $this->master->select('ap.*, group_concat(apm.adsId) as adsId');
        $this->master->join('ads_package_map apm', 'ap.id=apm.adsPackageId');
        $this->master->group_by('ap.id');
        $orgInfo = $this->master->get_where('ads_package ap', ['ap.id'=>$data['packageId']])->row_array();

        $visible = '0';

        if($orgInfo['viewType'] == 1)
        {
            //상시노출 없음. 기간을 길게
            $visible = '2';
            $orgInfo['startDate'] = date("Y-m-d");
            $orgInfo['endDate'] = date("Y-m-d", strtotime('+1000 days')); //상시노출이 없어서 1000일후로 설정
        }
        else if($orgInfo['viewType'] == 2)
        {
            //기간노출
            $visible = '2';
        }

        $adsIdArr = explode(',', $orgInfo['adsId']);
        $adsIdArr2 = [];
        $ii=0;
        foreach ($adsIdArr as $item)
        {
            $adsIdArr2[] = ['event_id'=>$item, 'priority'=>$ii];
            $ii++;
        }

        $adsIdArrJson = json_encode($adsIdArr2);

        $iData = [
            'visible' => $visible,
            'title' => $orgInfo['title'],
            'desc' => $orgInfo['detailInfo'],
            'is_show' => 1,
            'rank_score' => 1, //우선순위 없음. 무조건 1
            'image' => $orgInfo['banner'],
            'start_on' => $orgInfo['startDate'],
            'end_on' => $orgInfo['endDate'],
            'event_package_events_attributes'=>$adsIdArrJson,
            'image_desc'=>'',
            'image_desc2'=>''
        ];
        $this->replicator_m->send('/api/event_packages/'.$orgInfo['id'], 'PATCH', $iData);
        //리플리케이터 끝

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
     * 기획전 리스트
     * @param $data
     * @return array
     */
    function packageList($data)
    {
        if($data['status'] == '3')
        {
            $data['status'] = '';
        }

        //전체 카운트용
        $this->_packageWhere($data);

        $this->db->where('isDelete', 'N');
        $result0 = $this->db->get('ads_package')->result_array(); //echo $this->db->last_query();
        $totCnt = count($result0);

        if($totCnt > 0)
        {
            //검색데이터
            $this->_packageWhere($data);

            $offset = ($data['page'] - 1) * $data['limit'];
            $this->db->limit($data['limit'], $offset);
            $result = $this->db->get('ads_package')->result_array();
        }
        else
        {
            $resultArr = ['code'=>400, 'list'=>[], 'totCnt'=>$totCnt];
        }

        if($totCnt == 0)
        {
            $resultArr = ['code'=>400, 'list'=>[], 'totCnt'=>$totCnt];
        }
        else
        {
            $resultArr = ['code'=>200, 'list'=>$result, 'totCnt'=>$totCnt];
        }

        return $resultArr;
    }

    /**
     * 기획전리스트 검색어 처리
     * @param $data
     */
    function _packageWhere($data)
    {
        //상태
        if($data['status'])
        {
            $this->db->where('status', $data['status']);
        }

        //배너노출영역
        if($data['bannerViewType'])
        {
            switch ($data['bannerViewType'])
            {
                case 1:
                    $this->db->where('bannerViewType1', 1);
                    break;
                case 2:
                    $this->db->where('bannerViewType2', 2);
                    break;
                case 3:
                    $this->db->where('bannerViewType3', 3);
                    break;
                case 4:
                    $this->db->where('bannerViewType1', 1);
                    $this->db->or_where('bannerViewType2', 2);
                    $this->db->or_where('bannerViewType3', 3);
                    break;
            }
        }

        //기간
        if($data['startDate'] and $data['endDate'])
        {
            $this->db->where('viewType', 2);
            $this->db->where('startDate >= "'.$data['startDate'].'" and endDate <= "'.$data['endDate'].'"');
        }

        if($data['searchWord'])
        {
            switch ($data['searchType'])
            {
                case 1:
                    $this->db->like('id', $data['searchWord']);
                    break;
                case 2:
                    $this->db->like('title', $data['searchWord']);
                    break;
                case 3:
                    $this->db->like('detailInfo', $data['searchWord']);
                    break;
            }
        }
    }

    /**
     * 기획전 상세정보 보기
     * @param $data
     * @return array
     */
    function getPackage($data)
    {
        $this->db->select('ap.*, group_concat(apm.adsId) as adsId');
        $this->db->join('ads_package_map apm', 'ap.id=apm.adsPackageId', 'left');
        $result = $this->db->get_where('ads_package ap', ['ap.id'=>$data['packageId'], 'ap.isDelete'=>'N'])->row_array();
        return $result;
    }

    /**
     * ads_main, ads_main_map 등록
     * @param $data array
     * @return int
     */
    public function setadsMain(array $data) : int
    {   
        if ( isset($data['refreshToken']) )
        {
            unset($data['refreshToken']);
        }
        
        $return_arr = [];
        if ( isset($data['refreshToken']) )
        {
            unset($data['refreshToken']);
        }
        //ads_main insert  메인노출관리 테이블, 기존 데이터가 없으면 입력
        $adsMainArr = $this->master->get_where('ads_main', ['adsId' => $data['adsId']])->row_array();

        if (count($adsMainArr) == 0) 
        {
            $bbArr = [
                'adTitle' => $data['adTitle'],
                'adsId' => $data['adsId'],
                'hospitalId' => $data['hospitalId'],
                'agencyUserId' => $data['agencyUserId'],
                'regDate' => $data['date']
            ];

            $this->master->insert('ads_main', $bbArr);
            $return_arr['adsMainId'] = $this->master->insert_id();
        } 
        else 
        {
            $return_arr['adsMainId'] = $adsMainArr['id'];
        }

        //ads_main_map insert  메인노출 히스토리 테이블
        //운영자는 1 : (검수통과) 클라이언트는 2: (검수미통과) 
        if (defined('USERAUTHCODE')) 
        {
            $isInspect = USERAUTHCODE == 4 ? 1 : 2;
        }   
        else 
        {
            $isInspect = isset($data['isAdmin']) ? $data['isAdmin'] : 1; 
        }

        $abbArr = [
            'adsMainId' => $return_arr['adsMainId'],
            'adsId' => $data['adsId'],
            'isMain' => 2, //승인 전 이라 메인미노출 
            'isInspect' => $isInspect,
            'regDate' => $data['date']
        ];

        $this->master->insert('ads_main_map', $abbArr);
        $adsMainMapId = $this->master->insert_id();
    
        return $adsMainMapId;
    }

    /**
     * inspecting_ads 등록
     * @param $data array
     * @return bool
     */
    public function setInspectingAds(array $data) : bool
    { 
        if ( isset($data['refreshToken']) )
        {
            unset($data['refreshToken']);
        }
        
        $date = date('Y-m-d H:i:s');
        if ( isset($data['refreshToken']) )
        {
            unset($data['refreshToken']);
        }
        //운영자 등록 게시물인지 확인
        if (defined('USERAUTHCODE'))
        {
            if ( isset($data['isAdmin']) )
            {
                $isAdmin = isset($data['isAdmin']) ? $data['isAdmin'] : 1;
            }
            else 
            {
                $isAdmin = USERAUTHCODE == 4 ? 1 : 2;
            }
        }
        else 
        {
            $isAdmin = isset($data['isAdmin']) ? $data['isAdmin'] : 1;
        }

        //검수 올라와 있는 동일 광고 취소 처리
        $this->master->where('adsId', $data['adsId']);
        $this->master->where('status', 1);
        $this->master->update('inspecting_ads', ['status' => 5, 'inspectUserId' => $data['users_id'], 'inspectDate' => $data['date']]);

        //$this->master->select('id');
        //$result = $this->master->get_where('inspecting_ads', ['adsId' => $data['adsId']])->row_array();
        /*
        */     
        //if (!isset($result['id']))
        //{
           //검수 입력
            $insArr = [
                'status' => isset($data['status']) ? $data['status'] : 1, //검수대기
                'adStatus' => $data['adStatus'], //신규등록검토
                'regDate' => $data['date'],
                'hospitalId' => $data['hospitalId'],
                'historyId' => $data['historyId'],
                'adsId' => $data['adsId'],
                'userId' => $data['users_id'],
                'prevAdStatus' => $data['prevAdStatus'],
                'prevSubAdStatus' => $data['prevSubAdStatus'],
                'agencyUserId' => $data['agencyUserId'],
                'adsMainMapId' => $data['adsMainMapId'],
                'isAdmin' => $isAdmin
            ];  

            //검수 요청이 아닐떄 승인ID, 승인날짜 도 입력
            if ($insArr['status'] != 1) 
            {   
                $insArr['inspectUserId'] = $data['users_id'];
                $insArr['inspectDate'] =  $data['date'];
            }

            $this->master->insert('inspecting_ads', $insArr);
        //}     
        //else 
        /*
        {
            //검수 
            $upsArr = [
                'status' => 1, //검수대기
                'adStatus' => $data['adStatus'], 
                'historyId' => $data['historyId'],
                'prevAdStatus' => $data['prevAdStatus'],
                'prevSubAdStatus' => $data['prevSubAdStatus'],
                'agencyUserId' => $data['agencyUserId'],
                'adsMainMapId' => $data['adsMainMapId'],
                'isAdmin' => $isAdmin
            ];

            $this->master->where('adsId',$data['adsId'])->update('inspecting_ads', $upsArr);
        }
        */

        return true;
    }


    /**
     * adsID 에 맞는 히스토리 정보를 뽑아서 병합 후 리턴 
     * @param $data array
     * @return array
     */
    public function gethistoryMerge(array $data, bool $ins = false) : array
    {
        //히스토리 가져 와서 병합한다.
        //dd($data);
        $data['orderby'] = 'asc';
        if ($ins === true) 
        {
            $historys = $this->getHistoryListIns($data);     
        }
        else 
        {
            $historys = $this->getHistoryListSec($data);     
        }
        //log_message('info', 'historyMerge1 : '.json_encode( [$historys]));

        //exit;
        $historyInfo = [];  
        $dImageJson  = '';
        $hSize = sizeof($historys['info']) > 0 ? sizeof($historys['info']) - 1 : 0;
        foreach($historys['info'] as $key => $history)
        {
            //최초 히스토리는 전체 데이터가 들어가 있으니 바로 병합
            $deleteArr = isset($history['deletejson']) ? json_decode($history['deletejson'], true) : [];
            //var_dump($history['deletejson']);
            $historyInfo = array_merge($historyInfo, $deleteArr);
            //log_message('info', 'historyMerge2-1 : '.json_encode( [$deleteArr]));
            //log_message('info', 'historyMerge2-2 : '.json_encode( [$historyInfo]));
            //dd([$hSize, $key ], false);
            // dd([$hSize, $history ], false);
            if ($hSize == $key) {
                $dImageJson = $history['dImageJson'];
            }
        }
        //var_dump($historyInfo);
        //181227 이벤트 시작/종료일 형변환 추가
        $check_date = ['adStartDate', 'adEndDate'];
        foreach($check_date as $key=>  $val) 
        {
            $reDate = '';
            if(isset($historyInfo[$val]) && !is_null($historyInfo[$val]) && strpos($historyInfo[$val], '-') === false)
            {
                $reDate = substr($historyInfo[$val], 0, 4).'-'.substr($historyInfo[$val], 4, 2).'-'.substr($historyInfo[$val], 6, 2);
                $historyInfo[$val] = $reDate;
            }
        }

        $historyInfo['dImageJson'] = $dImageJson;
        //log_message('info', 'historyMerge3 : '.json_encode( [$historyInfo]));
        return $historyInfo;
    }

    /**
     * 광고 삭제 플래그
     * @param $data array
     * @return array
     */
    public function adsDelete(array $data) : array
    {
        if ( isset($data['refreshToken']) )
        {
            unset($data['refreshToken']);
        }
       
        $date = date("Y-m-d H:i:s");
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
        
        //삭제 안된 종료된 이벤트
        $cnt = $this->master->get_where('ads', [
                'id' => $data['adsId'], 'isDelete'  => 'N', 'adStatus'=> 3
        ])->num_rows();
     
        if ($cnt == 0) 
        {
            if ( $checkMethodTrans === true)
            {
                $this->master->trans_rollback();
            }
            return ['success' => false, 'code' => '204', 'message' => '데이터가 없습니다.'];         
        }

        //업데이트
        $this->master->where(['id'=> $data['adsId'],'adStatus'=> 3]);
        $result = $this->master->update('ads', ['isDelete' => 'Y', 'deleteuserId' => $data['users_id'], 'delDate' => $date]);

        if ($result !== true) 
        {
            if ( $checkMethodTrans === true)
            {
                $this->master->trans_rollback();
            }
            return ['success' => false, 'code' => '610', 'message' => '서버관리자에게 문의하세요.'];    
        }

       
        //트랜잭션
        if ($checkMethodTrans === true )
        {
            if ($this->master->trans_status() === FALSE)
            {
                $this->master->trans_rollback();
                return ['success' => false, 'code' => '610', 'message' => '서버관리자에게 문의하세요.'];    
            }
            $this->master->trans_commit();
            return ['success' => true];
        }    
        return ['success' => true];
    }

    /**
     * 광고 상태 가져오기
     * @param $adsId 광고iD
     * @return int
     */
    public function getAdsStatus(int $adsId) : int
    {   
        $this->db->select('adStatus');
        $result = $this->db->get_where('ads', ['id' => $adsId])->result_array();

        return count($result) == 0 ? 0 : (int) $result[0]['adStatus'];
    }


    /**
     * 히스토리 머지용
     * 
     */
    /**
     * 광고 히스토리 리스트
     * @param $data
     * @return array
     */
    function getHistoryListSec($data)
    {
        if ( isset($data['refreshToken']) )
        {
            unset($data['refreshToken']);
        }
        
        $param = [];
        if ( isset($data['refreshToken']) )
        {
            unset($data['refreshToken']);
        }
        if ((defined('USERAUTHCODE') &&  USERAUTHCODE == 2 ) && isset($data['hospitalId']))
        {
            //$param['hospitalId'] = $data['hospitalId'];
        }
       

        $param['adsId']  = $data['adsId'];

        $checkDb = $this->common_m->isInTrans() === true ? $this->master : $this->db;
        //log_message('info', 'historyListSec1 : '.json_encode( [$this->common_m->isInTrans()] ));
        //log_message('info', 'historyListSec2 : '.json_encode( [$checkDb]));
        //log_message('info', 'historyListSec3 : '.json_encode( [$data]));
        
        if(isset($data['historyId'])) 
        {
            $this->master->where('id >=', $data['historyId']);
        }

        $history['totCnt'] = $this->master->get_where('ads_history', $param)->num_rows();

        if (isset($data['limit']))
        {
            $limit = ($data['page'] - 1) * $data['limit'];
            $this->master->limit($data['limit'], $limit);
        }
        
        $orderby = isset($data['orderby']) ? $data['orderby'] : 'desc';

        $this->master->order_by('id', $orderby);
        $history['info'] = $this->master->get_where('ads_history', $param)->result_array();
        //dd($this->master->last_query());
        //log_message('info', 'historyListSec4 : '.json_encode( [$checkDb->last_query()]));
        //var_dump($history);
        return $history;
    }


    /**
     * 히스토리 머지용
     * 
     */
    /**
     * 광고 히스토리 리스트
     * @param $data
     * @return array
     */
    function getHistoryListIns($data)
    {
        if ( isset($data['refreshToken']) )
        {
            unset($data['refreshToken']);
        }
        
        $param = [];
        if ( isset($data['refreshToken']) )
        {
            unset($data['refreshToken']);
        }
        if ((defined('USERAUTHCODE') &&  USERAUTHCODE == 2 ) && isset($data['hospitalId']))
        {
            //$param['hospitalId'] = $data['hospitalId'];
        }

        $param['adsId']  = $data['adsId'];

        $checkDb = $this->common_m->isInTrans() === true ? $this->master : $this->db;
        //log_message('info', 'historyListSec1 : '.json_encode( [$this->common_m->isInTrans()] ));
        //log_message('info', 'historyListSec2 : '.json_encode( [$checkDb]));
        //log_message('info', 'historyListSec3 : '.json_encode( [$data]));
        
        if(isset($data['historyId'])) 
        {
            /**
             * BIZ-1401 이전 검수 내역이 있나 없나 체크
             */
            $this->master->where('id <=', $data['historyId']);
        }

        $history['totCnt'] = $this->master->get_where('ads_history', $param)->num_rows();

        if(isset($data['historyId'])) 
        {
            /**
             * BIZ-1401 이전 검수 내역이 있나 없나 체크
             */
            $this->master->where('id <=', $data['historyId']);
        }

        if (isset($data['limit']))
        {
            $limit = ($data['page'] - 1) * $data['limit'];
            $this->master->limit($data['limit'], $limit);
        }
        
        $orderby = isset($data['orderby']) ? $data['orderby'] : 'desc';

        $this->master->order_by('id', $orderby);

        $history['info'] = $this->master->get_where('ads_history', $param)->result_array();

        //log_message('info', 'historyListSec4 : '.json_encode( [$checkDb->last_query()]));
        //var_dump($history);
        return $history;
    }

    /**
     * 검수중인것이 있는지 확인
     * @param @ids array
     * @return bool
     */
    public function isAdsInspection(array $ids) : bool
    {
        $this->db->select('id');
        $this->db->where_in('id',  $ids); 
        $this->db->where('adStatus', 1);
        $count = $this->db->get('ads')->num_rows(); 
        //dd($count);
        return $count == 0 ? false : true;
    }
}