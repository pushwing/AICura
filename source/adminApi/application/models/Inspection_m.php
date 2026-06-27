<?php
/**
 * Created by PhpStorm.
 * User: blumine
 * Date: 2018. 2. 12.
 * Time: PM 5:45
 */

class Inspection_m extends CI_Model
{
    function __construct()
    {
        parent::__construct();
    }

    /**
     * 검색어 master 처리
     * @param $data
     */
    function __masterWhere($data)
    {
        if(@$data['adStatus'])
        {
            //광고 쪽 쓰고 있는 거 가져와서
            
            $this->master->where('ias.adStatus = '.$data['adStatus']);
        }

        //영업담당자
        if(@$data['agencyUserId'])
        {
            $cateArr = explode(',', $data['agencyUserId']);

            $this->master->where_in('ads.agencyUserId', $cateArr);
        }

        //금일승인
        if($data['type'] == 2)
        {
            //전날 16:00 - 금일 15:59:59 동안 요청
            //BIZ-1523 요건 또 바뀜
            $toDay = date("Y-m-d 16:00:00");
            //$this->master->where("( (ias.regDate + INTERVAL 9 HOUR) >= '".$lastDay."' and (ias.regDate + INTERVAL 9 HOUR) <= '".$toDay."')");
            $this->master->where("( (ias.regDate + INTERVAL 9 HOUR) <= '".$toDay."')");
        }

        if(isset($data['searchType']) and isset($data['searchWord']))
        //if(isset($data['searchWord']))
        {
            $query = [];
            //BIZ-1544
            switch ($data['searchType'])
            {
                case 1:
                    $hospitalId = $this->goodocapi->getHospitalIdByName($data['searchWord']);
                    if (sizeof($hospitalId) > 0)
                    {
                        $hospitalIds = implode(',' ,  $hospitalId );
                        $query[] = 'ads.hospitalId in ('.$hospitalIds.')';
                        //$this->master->where('ads.hospitalId IN ('.$hospitalIds.')');
                    }
                    break;
                case 2:
                    $query[] = 'ads.adTitle LIKE "%'.$data['searchWord'].'%"';
                    break;
                case 3:
                    $query[] = 'ads.id = "'.$data['searchWord'].'"';
                    break;
            }

//            $hospitalId = $this->goodocapi->getHospitalIdByName($data['searchWord']);
//
//            if (sizeof($hospitalId) > 0)
//            {
//                $hospitalIds = implode(',', $hospitalId);
//                $this->master->where('ads.hospitalId IN ('.$hospitalIds.')');
//            }
//
//            $query[] = 'ads.adTitle LIKE "%'.$data['searchWord'].'%"';
//            $query[] = 'ads.id = "'.$data['searchWord'].'"';

            $this->master->where('('.implode(' OR ', $query).')');
        }    
    }

    /**
     * 검수리스트 조회
     * 전체 카운트 반환
     * @param $data
     * @return array
     */
    function getInspectiontList($data)
    {
        //BIZ-1523
        //$lastDay = date("Y-m-d 16:00:00", strtotime("-1days"));
        //$toDay = date("Y-m-d 15:59:59");
        $toDay = date("Y-m-d 16:00:00");

        /**
         * BIZ-1330
         * [검수관리]-[검수 전체목록]승인대상필터를 금일 승인으로 변경시 전체승인건 갯수카운터가 금일승인건카운터와 동일하게 변경됨
         * 을 수정
         * 전체 카운트 뽑는곳에서는 type 을 1로 바꿔서 진행 
         */
        $totCntData = $data;
        $totCntData['type'] = 1;

        $this->__masterWhere($totCntData);
        /**
         * 190103 f_hospital 삭제. networkcount 및 병원 명 api로 
         * API 로 변경 또는 삭제.
         */
        $this->master->join('ads', 'ias.adsId=ads.id');
        $this->master->group_by('ias.id');

        //$this->master->where(' ias.isAdmin = 2 and ias.status = 1');
        $this->master->where(' ias.status = 1'); //운영자가 등록한 이벤트도 검수를 거치도록 변경 테스트. 20191121
        $result0 = $this->master->get('inspecting_ads ias')->result_array();

//        echo $this->master->last_query();
//        exit;
        $totCnt = count($result0);

        /**
         * BIZ-1330
         * 금일 승인 건수도 바로 뽑는다.
         *  */
        $todayCnt = 0;

        if ($totCnt > 0) 
        {
            //금일승인건수
            $this->__masterWhere($data);

            $this->master->join('ads', 'ias.adsId=ads.id');
            /**
             * TODO networkCount 쓰는지 확인 후
             * API 로 변경 또는 삭제
             */
            //190103 f_hospital 제거
            $this->master->group_by('ias.id');
            /**
             * BIZ-1330
             */
            if ($data['type'] == 1)
            {
                //$this->master->where("(ias.regDate + INTERVAL 9 HOUR) <= '" . $toDay."' and ias.isAdmin = 2 and ias.status = 1");
                $this->master->where("(ias.regDate + INTERVAL 9 HOUR) <= '" . $toDay."' and ias.status = 1");
            }
            else 
            {
               //$this->master->where(" ias.isAdmin = 2 and ias.status = 1");
               $this->master->where(" ias.status = 1");
            }
            $result2 = $this->master->get('inspecting_ads ias')->result_array();
            $todayCnt = count($result2);
        } 

       
        /**
         * BIZ-1330
         * type == 2 일때는 금일 승인 카운트 : 아닐떄는 전체 카운트 기준으로 0 보다 클때 리스트를 뽑는다.
         *  */
        $checkCnt = $data['type'] == 2 ? $todayCnt : $totCnt;
       
        if($checkCnt > 0)
        {
            $this->__masterWhere($data);
            $limit = ($data['page'] - 1) * $data['limit'];
            /**
             * 190103 f_hospital 삭제. networkcount 및 병원 명 api로 
             * API 로 변경 또는 삭제 
             *  fh.name as hospitalName, ads.hospitalType, count('fhn.*') as networkCount, 
             */ 
            $this->master->select("ias.*, ads.adTitle, ads.hospitalType, concat(ec2.title,'/',ec1.title) as categoryName");
            $this->master->limit($data['limit'], $limit);
            $this->master->join('ads', 'ias.adsId=ads.id');
            $this->master->join('event_categories ec1', 'ads.category=ec1.id', 'left');
            $this->master->join('event_categories ec2', 'ec1.parent_id=ec2.id', 'left');
            $this->master->group_by('ias.id');

            if($data['type'] == 2)
            {
                //금일
                $this->master->where(" (ias.regDate + INTERVAL 9 HOUR) <= '" . $toDay."' ");
            }
            
            //$this->master->where(' ias.isAdmin = 2 and ias.status = 1');
            $this->master->where(' ias.status = 1');

            $result = $this->master->get('inspecting_ads ias')->result_array();
           //echo $this->db->last_query();exit;
        }
        else
        {
            $result = [];
        }
       
        //echo $this->db->last_query(); exit;
        //190103 병원 리스트 가져와서 네트워크모병원은 자병원카운트 가져온다
        $hospitalId_arr    = [];
        $hospitalParentId_arr = [];
        foreach($result as $key => $val)
        {
            if (!is_null($val['hospitalId']) && !empty($val['hospitalId']) ) {
                $hospitalId_arr[] = $val['hospitalId'];
                
                if ($val['hospitalType'] == 1 && !isset($hospitalParentId_arr[$val['hospitalId']])) //네트워크모병원일때 
                {
                    $hospitalParentId_arr[$val['hospitalId']] =  0;
                }
            }
        }

        //중복 제거
        $hospitalId_arr = array_unique($hospitalId_arr);

        //190103 모명원ID로 자병원 카운트 가져오기 
        if (count($hospitalParentId_arr) > 0)
        {      
            foreach($hospitalParentId_arr as $key => $val)
            {
                $checkChildCount = $this->goodocapi->getChildHospitals($key, true);
                $hospitalParentId_arr[$key] = isset($checkChildCount['totalCount']) ?  $checkChildCount['totalCount'] : 0;
            } 
        }

        //190102 병원 명 가져오기
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

                 if (isset($hospitalParentId_arr[$val['hospitalId']]))
                {
                    $result[$key]['networkCount'] = $hospitalParentId_arr[$val['hospitalId']];
                }
                else 
                {
                    $result[$key]['networkCount'] = 0;        
                }
                 unset($hospitalName, $hospitalType);
             }
        }
        unset($hospitalId_arr, $hospitalParentId_arr);

        $resultArr = ['list'=>$result, 'totCount'=>$totCnt, 'todayCount'=>$todayCnt, 'regDateCheck' => false];
        //monologSend('inspection_cnt', json_encode([ $this->master->last_query()   ,  $result]));
        return $resultArr;
    }


    /**
     * 검색어 처리
     * @param $data
     */
    function _where($data)
    {   
        //광고상태
        /**
         * BIZ-1332
         * [검수관리]-[검수 전체목록]검수상태필터를 "전체"와 "X신규 등록검토"를 제외한 항목으로 설정후 검색진행시 설정한 항목과 다른 결과가 표출됨
         * 을 수정
         */
        if(@$data['adStatus'])
        {
            //광고 쪽 쓰고 있는 거 가져와서
            
            $this->db->where('ias.adStatus = '.$data['adStatus']);

            /*
            $adsWhere = [
                1 => "(ads.isLive='Y' and ads.adStatus = 1 and ads.subAdStatus = 1  )" //O 수정 검토
                ,2 => "(ads.isLive='Y' and ads.adStatus = 1 and ads.subAdStatus = 2 )" //O 종료검토
                ,3 => "(ads.isLive='N' and ads.adStatus = 1 and ads.subAdStatus=3 )" //x신규등록검토
                ,4 => "(ads.isLive='N' and ads.adStatus = 1 and ads.subAdStatus=4 )"//x재등록검토
            ];  

            //검색 값과 일치하는게 있다면 검색
            if (isset($adsWhere[ $data['adStatus']]) )
            {
                $this->db->where($adsWhere[$data['adStatus']]);
                //monologSend
            }
            */
        }

        //영업담당자
        if(@$data['agencyUserId'])
        {
            $cateArr = explode(',', $data['agencyUserId']);

            $this->db->where_in('ads.agencyUserId', $cateArr);
        }

        //금일승인
        if($data['type'] == 2)
        {
            //전날 16:00 - 금일 15:59:59 동안 요청
            $lastDay = date("Y-m-d 16:00:00", strtotime('-1days'));
            $toDay = date("Y-m-d 15:59:59");
            $this->db->where("((ias.regDate + INTERVAL 9 HOUR) >= '".$lastDay."' and (ias.regDate + INTERVAL 9 HOUR) <= '".$toDay."')");
        }

        if(isset($data['searchWord']))
        {   
            //190103 f_hospital 제거 작업

            $query = [];

            $hospitalId = $this->goodocapi->getHospitalIdByName($data['searchWord']);
            if (sizeof($hospitalId) > 0) {
                $hospitalIds = '';
      
                foreach( $hospitalId as $key=> $val) {
                    $hospitalIds .= empty($hospitalIds) ? '"'.$val.'"' : ',"'.$val.'"';
                }
                $this->db->where('ads.hospitalId IN ('.$hospitalIds.')');
                //$hospitalIds = implode(',' ,  $hospitalId );
            }
          
            $query[] = 'ads.adTitle LIKE "%'.$data['searchWord'].'%"';
            $query[] = 'ads.id LIKE "%'.$data['searchWord'].'%"';
            
            $this->db->where('('.implode(' OR ', $query).')');
        }
    }

    /**
     * 상세검수 조회
     * @param $data
     * @return array/bool
     */
    function getInspectInfo($data)
    {  
        //190103 f_hospital 제거 작업 fh.name hospitalName    

        $adsCols = 'ads.id, ads.isLive, ads.deleteuserId , ads.isDelete, ads.callRequestId, ads.dbCount, ads.dibsCount, ads.delDate,';
        $adsCols .= 'ads.modDate, ads.regDate, ads.subAdStatus, ads.adStatus, ads.adOrder,';
        $adsCols .= 'adsHistoryJson, ads.channel,';
        $adsCols .= ' dImageJson ';
        $this->db->select("amm.url, c.title as contractTitle, ".$adsCols.", ist.id as istId, ist.historyId, ist.adsMainMapId, ist.status as inspectStatus, ist.adStatus as inspectAdStatus");
        $this->db->select("group_concat(distinct ahm.regDate, ' ', ahm.memo separator '|') as eventMemo");
        $this->db->select("(select group_concat(memo separator '|') as memo from memo where memoType=1 and targetId in (group_concat(distinct coc.contractOrderId)) ) as agencyMemo");
        //$this->db->select("( (select ifnull(sum(price), 0)  from deposit where status in(2,4) and contractId=c.id ) - (select ifnull(sum(price), 0)  from deposit where status in(3,5,6,7,8) and contractId=c.id )) as balancePrice");
        $this->db->select("trp.`totalReady` as balancePrice");
        $this->db->select("( select count(*) from board where type =1 and targetId=ads.id and isDelete=0 ) as boardCount");
        $this->db->select("( select rateSum from board_summary where type =1 and targetId=ads.id ) as boardRateSum");
        $this->db->join('ads', 'ist.adsId=ads.id', 'left');
        $this->db->join('ads_main_map amm', 'ist.adsMainMapId=amm.id', 'left');
        $this->db->join('contract c', 'ads.contractId=c.id' , 'left');
        $this->db->join('contract_order_connect coc', 'ads.contractId=coc.contractId', 'left');
        $this->db->join('ads_history_memo ahm', 'ist.adsId=ahm.adsId', 'left');
        $this->db->join('total_ready_price trp', 'c.id=trp.contractId', 'left');
        $this->db->group_by('ads.id, coc.contractId');
 
        //$info = $this->db->get_where('inspecting_ads ist', ['ist.id'=>$data['inspectId'], 'ist.isAdmin'=>2])->row_array(); //echo $this->db->last_query(); exit;
        $info = $this->db->get_where('inspecting_ads ist', ['ist.id'=>$data['inspectId']])->row_array(); //echo $this->db->last_query(); exit;

        if($info['id'] != '')
        {
            $infoHistory =  json_decode($info['adsHistoryJson'], true);
            unset($info['adsHistoryJson']);

            $checkKeyArr = [
                'adTitle', 'category', 'adStartDate', 'adEndDate', 'adDateExtend', 'adType', 'exposure',
                'costType', 'generalCost', 'discountCost', 'textCost', 'dbCost', 'whereImage', 'modelImageCount',
                'adDetailInfo', 'contractId', 'contractName', 'contractOrderId', 'hospitalId', 'hospitalType',
                'agencyUserId', 'isViewBoard', 'deliberationCode', 'customRanding', 'custom1', 'custom2', 'custom3',
                't1ImageName',  't2ImageName', 'region', 'cooperation', 'keyword', 'channel'
            ];

            foreach($infoHistory as $key => $val){  
                if (!in_array($key, $checkKeyArr)){
                    unset($infoHistory[$key]);
                }
            }

            $regionId = str_replace('|', ',', $infoHistory['region']);
            $cooperationId = str_replace('|', ',', $infoHistory['cooperation']);
            $keyword = str_replace('|', ',', $infoHistory['keyword']);
            unset($infoHistory['region']);
            unset($infoHistory['cooperation']);
            unset($infoHistory['keyword']);
            
            $infoHistory['regionId'] = $regionId;
            $infoHistory['cooperationId'] = $cooperationId;
            $infoHistory['keyword'] = $keyword;

            $check_date = ['adStartDate', 'adEndDate'];
            foreach($check_date as $key=>  $val) 
            {
                $reDate = '';
                if(isset($infoHistory[$val]) && !is_null($infoHistory[$val]) && strpos($infoHistory[$val], '-') === false)
                {
                    $reDate = substr($infoHistory[$val], 0, 4).'-'.substr($infoHistory[$val], 4, 2).'-'.substr($infoHistory[$val], 6, 2);
                    $infoHistory[$val] = $reDate;
                }
            }

            //버튼 정보 한글 정상적으로 나오게 변환
            $adDetailInfo = json_decode($infoHistory['adDetailInfo']);
            $adDetailInfo = json_encode($adDetailInfo, JSON_UNESCAPED_UNICODE );
            unset($infoHistory['adDetailInfo']);
            
            $infoHistory['adDetailInfo'] = $adDetailInfo; 

            //자기가 무슨 짓을 하는 지 모르고 개발하는 빈센트. 이 라인 때문에 아래에서 info와 history 배열에서 항목들을 서로 교환하고 있다.
            foreach( $info as $key => $val ){
                $infoHistory[$key] = $val;
            }
           
            /*
            $image_arr = ['t1', 't2'];
            $imageArr  = '';
            foreach( $image_arr as $val){
               if (isset($info[$val]) && !is_null($info[$val])){
                    $imageArr .= $imageArr === '' ? $val.'|'.$info[$val] : ','.$val.'|'.$info[$val];
               }
               unset($info[$val]);
            }   
            //$info['imageArr'] = $imageArr;
            */

            $hospitalNameArr = [];
            if (isset($infoHistory['hospitalId']) ) 
            {
                $hospitalNameArr = $this->goodocapi->getHospitalNamesByIds([$infoHistory['hospitalId']]);
                $infoHistory['hospitalName'] = count($hospitalNameArr) == 0 ? '' : $hospitalNameArr[$infoHistory['hospitalId']];
            }
            unset($hospitalNameArr);
            $infoHistory['dImages'] = $infoHistory['dImageJson'];
            unset( $infoHistory['dImageJson']);
         
            $result['info'] = $infoHistory;
            
            //변경내역
            $checkHistoryData = $infoHistory;
            /**
             * BIZ-1401 이전 검수 내역이 있나 없나 체크
             */
        
            $this->db->select("COUNT(id) AS istCnt");
            $this->db->where('adsId = '.$info['id'].' and id < '.$info['istId'].' and status = 2');

            $istCnt = $this->db->get('inspecting_ads')->row_array();
            if ($istCnt['istCnt']  > 0) {
                $this->db->select('historyId');
                $this->db->where('adsId = '.$info['id'].' and id < '.$info['istId'].' and status = 2');
                $this->db->order_by('id', 'desc');
                $this->db->limit(1,0);
                $istRow = $this->db->get('inspecting_ads')->row_array();
                $checkHistoryData['historyId'] = $istRow['historyId'];
            }

            $checkHistoryData['adsId'] = $infoHistory['id'];
            
            $this->load->model('ads_m');
            $historyMerge = $this->ads_m->gethistoryMerge($checkHistoryData, true);
            //dd($historyMerge);
            unset($checkHistoryData);

            //190103 병원명 가져오기
            $hospitalNameArr = [];
            if (isset($historyMerge['hospitalId']) )
            {
                $hospitalNameArr = $this->goodocapi->getHospitalNamesByIds([$historyMerge['hospitalId']]);
                $historyMerge['hospitalName'] = count($hospitalNameArr) == 0 ? '' : $hospitalNameArr[$historyMerge['hospitalId']];
            }
            unset($hospitalNameArr);

            $historyDimages =  $historyMerge['dImageJson'];
            
            for ($i=1; $i < 51; $i++){
                unset($historyMerge['d'.$i.'ImageName']);
            }
    
            //$result['history'] = $this->db->get_where('ads_history', ['id'=>$result['info']['historyId']])->row_array();
            unset($historyMerge['dImageJson']);
            $result['history']  = $historyMerge;
            $result['history']['dImages']  = $historyDimages;
            
            $infoToHistoryArr = [
                'regionId',  'cooperationId',  'url',  'contractTitle',  'id', 'isLive', 'deleteuserId', 'isDelete'
                , 'callRequestId', 'dbCount', 'dibsCount', 'delDate', 'modDate',  'subAdStatus', 'adStatus', 'adOrder'
                , 'istId', 'historyId', 'adsMainMapId', 'inspectStatus', 'inspectAdStatus', 'eventMemo', 'agencyMemo'
                , 'balancePrice', 'boardCount', 'boardRateSum', 'hospitalName', 'channel'
            ];
            
            foreach($result['info'] as $key => $val) {
                if (in_array($key, $infoToHistoryArr)) {
                    $result['history'][$key] = $val;
                    unset($result['info'][$key]);
                }
            }
            unset($infoToHistoryArr);
            
            $historyToInfoArr = [
                'region', 'cooperation', 'subHospitalId', 'optionAdId', 'adsId', 'userId'
            ];
          
            foreach($result['history'] as $key => $val) {
                if (in_array($key, $historyToInfoArr)) {
                    $result['info'][$key] = $val;
                    unset($result['history'][$key]);
                }
            }
            unset($historyToInfoArr);

            $cpId = $result['history']['cooperationId'];
            $cp =   $result['info']['cooperation'];
            $result['history']['cooperationId'] = $cp;
            $result['info']['cooperation'] = $cpId;

            //v1 정방향 이미지 수정일 구하기
            $data['id'] = $info['id'];

            $t2Image = $result['history']['t2ImageName'];
            $imageArray = explode('/t2_', $t2Image);

            if(count($imageArray) > 1)
            {
                //v1 이미지 포맷이면
                $data['image2'] = $imageArray['1'];
                $type3= 1;
            }
            else
            {
                //v2 이미지 면
                $imageArray = explode('/', $t2Image);
                $out = array_slice($imageArray, 3);

                $data['image2'] = implode('/',$out);
                $type3= 2;
            }

            //정방향 이미지 수정일
            $return = $this->common_m->s3DateCheck($type3, $data);
            if($return)
            {
                $result['history']['image2Date'] = date("Y-m-d\TH:i:s.000\Z", strtotime($return['@metadata']['headers']['last-modified']));
            }
            else
            {
                $result['history']['image2Date'] = '';
            }

            //최초등록여부
            $sql00000 = "select count(*) cnt from inspecting_ads where adsId='".$info['id']."' and status=2";
            $newEventResult = $this->db->query($sql00000)->row_array();
            $returnValue = 1;
            if($newEventResult['cnt'] > 0)
            {
                $returnValue = 0;
            }
            $result['history']['newEvent'] = $returnValue; //1 신규등록, 0 신규등록 아님

            $changeResult = [];
            $changeResult['info'] = $result['history'];
            $changeResult['history'] = $result['info'];    
            unset($result);

            return $changeResult;
        }
        else
        {
            return false;
        }
    }

    /**
     * 검수대기상태만 참
     * @param $data
     * @return bool
     */
    function getStatus($data)
    {
        //$result = $this->db->get_where('inspecting_ads', ['id'=>$data['inspectId'], 'status'=>1, 'isAdmin'=>2])->row_array();
        $result = $this->db->get_where('inspecting_ads', ['id'=>$data['inspectId'], 'status'=>1])->row_array();
        
        if($result)
        {
            return true;
        }
        else
        {
            return false;
        }
    }   

    /**
     * 검수 취소 (병원어드민 전용)
     * @param $data
     * @return int
     */
    function cancleInspectInfo($data)
    {
        $data2['status'] = $data['type'];

        //메소드 내부에서 트랜잭션이 시작인지 체크 
        $checkMethodTrans = false;
        if ($this->common_m->isInTrans() === false)
        {
            $this->master->trans_begin();
            $checkMethodTrans = true;   
        }
      
        //$adsId = $data['adsId'];

        $this->master->select('adsId, prevAdStatus, prevSubAdStatus');    
        $prev = $this->master->get_where('inspecting_ads', ['id' => $data['inspectId']])->result_array();    
        
        $prevAdStatus = 4; //작성중
        $prevSubAdStatus = 6; //병원 작성중
        $adsId = null;
      
        if (count($prev) > 0) 
        {   
            $prevAdStatus = $prev[0]['prevAdStatus'];
            $prevSubAdStatus = $prev[0]['prevSubAdStatus'];
            $adsId = $prev[0]['adsId'];
        }
        
        if (is_null($adsId))
        {   
            //트랜잭션
            if ($checkMethodTrans === true)
            {
                $this->master->trans_rollback();
            }
            return false;    
        }
       
        $this->master->where('id', $data['inspectId']);    
        $this->master->where('isAdmin', 2);
        $this->master->update('inspecting_ads', $data2);

        $result = $this->master->affected_rows();
        
        if ($prevAdStatus == 4) 
        {
            $this->master->select('id');
            $this->master->order_by('id', 'desc');
            $this->master->where('adsId', $adsId);
            $tempCheck = $this->master->get('ads_temporary')->result_array();
            
            if ( isset($tempCheck[0]) )
            {
                $this->master->where('id', $tempCheck[0]['id']);
                $this->master->update('ads_temporary', ['isDelete' => 2]);              
            }
        } 
       
        $this->master->where('id', $adsId);
        $this->master->update('ads', ['adStatus' => $prevAdStatus, 'subAdStatus' => $prevSubAdStatus ]);
        
        //트랜잭션
        if ($checkMethodTrans === true)
        {  
            if ($this->master->trans_status() === FALSE)
            {
                //dd($this->master->trans_status());
                $this->master->trans_rollback();
                return false;
            }
            $this->master->trans_commit();
            return $result;
        }
        return $result;
    }
 
    /**
     * 검수정보 업데이트
     * @param $data
     * @return int
     */
    function updateInspectInfo($data)
    {
        $this->load->model('ads_m');

        $date = date("Y-m-d H:i:s");

        //메소드 내부에서 트랜잭션이 시작인지 체크 
        $checkMethodTrans = false;
        if ($this->common_m->isInTrans() === false)
        {
            $this->master->trans_begin();
            $checkMethodTrans = true;   
        }

        //$cnt = $this->master->get_where('inspecting_ads',  [ 'id'=>$data['inspectId'], 'isAdmin'=>2 ] )->num_rows();
        $cnt = $this->master->get_where('inspecting_ads',  [ 'id'=>$data['inspectId'] ] )->num_rows();

        if ($cnt == 0 )
        {
            if ($checkMethodTrans === true)
            {
                $this->master->trans_rollback();
            }
            return false;
        }

        //비교용 광고정보 가져오기
        //쿼리 잘못 됨 값이 한 테이블이라도 빠지면 모두 null 됨 
        //left join으로 변경
        $this->master->select('ads.*, ia.adStatus as iaAdStatus, ia.adsMainMapId, ia.historyId, ads.vT2ImageName as t2ImageName');
        $this->master->select('ads.vRegion as region, ads.vCooperation as cooperation, ads.vKeyword as keyword');
        $this->master->join('ads', 'ia.adsId=ads.id');
        //$adOriInfo = $this->master->get_where('inspecting_ads ia', ['ia.id'=>$data['inspectId'], 'ia.isAdmin'=>2])->row_array();
        $adOriInfo = $this->master->get_where('inspecting_ads ia', ['ia.id'=>$data['inspectId']])->row_array();
        //echo $this->db->last_query();

        $hData = [];
        //히스토리 가져와서 
        //var_dump($adOriInfo);

        $iaAdsMainMapId = $adOriInfo['adsMainMapId'];
        $iaAdStatus     = $adOriInfo['iaAdStatus'];

        $param['adsId'] = $adOriInfo['id'];
        $param['historyId'] = $adOriInfo['historyId'];
        if(USERAUTHCODE == 2 && isset($data['hospitalId']) )
        {
            $param['hospitalId'] = $data['hospitalId'];
        }
        
        $historyMerge = $this->ads_m->gethistoryMerge($param);
       // monologSend('updateInspectInfo_history', json_encode($historyMerge));
       
        $dImageJson  = json_decode($historyMerge['dImageJson']);
       // monologSend('updateInspectInfo_dImageJson', json_encode($dImageJson));

        //var_dump($data);
        $inspectDate     = $date;
        $deletejson      = [];

        if($data['type'] == 1)
        {
            $dArr = ['dbCost', 'category', 'contractId', 'keyword', 'cooperation', 'region', 'exposure', 'isViewBoard'];

            $check_arr = ['keyword', 'cooperation', 'region'];

            //수정된 데이터가 있는지 체크
            foreach ($dArr as $item)
            {
                if(@$data[$item] and (@$data[$item] != $adOriInfo[$item]))
                {
                    $hData[$item]= $data[$item];
                    
                    //echo $item.':::'. $adOriInfo['id']. '</br>'; var_dump($data[$item]);
                    //ads 서브 잡
                    if( in_array($item, $check_arr) && !is_null($data[$item]) && !empty($data[$item]) )
                    {   
                        unset($historyMerge[$item]);
                    }
                }
            }   

            //var_dump($hData);
            //exit;
            //히스토리 새건으로 입력. 18.11.01 with luna
            //업무로직 개선 필요. 이벤트수정하고 승인요청한 것을 승인하면서 또 수정하는 로직임.
            //190106 adTitle 주석
            $hArr2 = [
                //'adTitle'=>'',
                'adsId'=>$adOriInfo['id'],
                'userId'=>$data['users_id'],
                'regDate'=>$date
            ];
          
            $lArr = array_merge($hData, $hArr2);
            $deletejson = $lArr;
            //190106 adTitle 주석
            //unset($deletejson['adsId'],$deletejson['adTitle']);
            unset($deletejson['adsId']);

            $lArr['deletejson'] = json_encode($deletejson, JSON_UNESCAPED_UNICODE);
            
            //한번이라도 승인 된지 확인 하기 위해 넣음 181226
            //ads 업데이트 시 inspectDate 가 0000-00-00 ~~ 이 아닌 것이 있을때는 
            //수정검토O 없을때는 신규검토X
            $lArr['inspectDate']    = $inspectDate;
            $lArr['dImageJson']    =   json_encode($dImageJson, JSON_UNESCAPED_UNICODE); 
            //var_dump($lArr);
            $historyId = $this->ads_m->setHistory($lArr);
          
        }
    
        $data2 = [
            'inspectDate' => $inspectDate
            ,'inspectUserId' => $data['users_id']
        ];

        switch ($data['type'])
        {
            case 1: //승인
                if ($iaAdStatus == 2) 
                {
                    $historyMerge['adStatus'] = 3;
                    $historyMerge['isLive'] = 'N';
                } 
                else 
                {
                    $historyMerge['adStatus'] = 2;
                    $historyMerge['isLive'] = 'Y';
                }
                $data2['status'] = 2;
                break;
            case 2: //반려
                $historyMerge['adStatus'] = 5;
                $data2['status'] = 3;
                $data2['reason'] = $data['reason'];
                $data2['rejectCode'] = $data['rejectCode'];
                $data2['agencyUserReason'] = $data['agencyUserReason'];
                break;
            case 3: //종료
                $historyMerge['adStatus'] = 3;
                $data2['status'] = 4;
                $historyMerge['isLive'] = 'N';
                break;
//            case 1:
//                $data2['memo'] = '바로승인';
//                $data2['status'] = 2;
//                break;
//            case 1:
//                $data2['memo'] = '바로승인';
//                $data2['status'] = 2;
//                break;
        }

        if($data['type'] == 4)
        {
            $data2['memo'] = '바로승인';
            $historyMerge['adStatus'] = 2;
            $historyMerge['isLive'] = 'Y';
            $data2['status'] = 2;
        }

        if($data['type'] == 5)
        {
            $data2['memo'] = '바로종료';
            $historyMerge['adStatus'] = 3;
            $historyMerge['isLive'] = 'N';
            $data2['status'] = 4;
        }
    
        $imgArr = [
            't1ImageName', 't2ImageName'
        ];

        //서브잡 돈다 
        $check_arr = [  
            'hospitalType', 'optionAdId', 'region'
            ,'cooperation', 'keyword' 
        ];

        $check_arr = array_merge($check_arr, $imgArr);

        foreach($check_arr as  $val)
        {   
            if($val != 'hospitalType')
            {
                //unset($historyMerge[$val]);
            }
        }
     
        $historyMerge['adsHistoryJson'] = json_encode( array_merge( $historyMerge, $deletejson), JSON_UNESCAPED_UNICODE);

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

        for ($i=1; $i < 51; $i++){
            unset($historyMerge['d'.$i.'ImageName']);
        }

        $historyMerge['modDate'] = $date;
        
        //dd($historyMerge);
        $this->master->where('id', $adOriInfo['id'])->update('ads', $historyMerge);

        $this->master->where('id', $data['inspectId']);
        $this->master->update('inspecting_ads', $data2);
        $result = $this->master->affected_rows();

        //ads_main_map 업데이트
        if($data['type'] == 1)
        {
            //이벤트 ID 들 메인에서 내리고
            $this->master->where('adsId', $adOriInfo['id'])->update('ads_main_map', [
                'isMain' => 2
            ]);
                
            //검수에 있는 adsMainMapId의 로우만 메인으로 설정
            $this->master->where('id', $iaAdsMainMapId)->update('ads_main_map', [
                'isMain' => 1
            ]);     
        }

        //최초 생성인지 판단, ads_file_history로 판단 - 최초여부 필요없음
        //현재 적용하는 이벤트 템플릿 버전 가져오기
        //base html 내용치환
        //병원명 , 주소, hospitalId로 조회
        // 광고명 adTitle, 가격 costType에 따라 계산, 정상가격 generalCost, 기간 adStartDate~adEndDate,
        // 사진 t1, t2, d1 ~ d6
        // 관련이벤트(만드는 로직) relationEvent
        // 신청하기 버튼(로직)
        //업로드
        //파일명 변경. 변경을 해야 db 접속 없이 특정 주소로 연결된다. index.html -> index1.html로 변경하고 신규파일을 index.html로 생성
        //오스틴 의견 - 원 파일명과 index.html 생성하여 처리. 파일명이 바뀔 필요가 없다.
        //파일 생성 절차
        //상태 값 N 으로 업데이트 후
        //db 수정  ads_main_map
        if($data['type'] == 1 or $data['type'] == 4) //승인, 바로승인
        {
            //$adsInfo = $this->getAdsInfo($data);
            $this->ads_m->make_template(1, ['adsId' =>  $adOriInfo['id']], $date, 1);
        }


        //리플리케이터 시작
        //이벤트 승인 PATCH /api/events/{event_id}
                
        //최신 광고정보 가져오기, 히스토리 데이터를 가져와야 함. 현재 ads dbCost는 변경되기전 값임
        $this->master->select('ads.*, ia.adsMainMapId, ia.historyId');
        $this->master->select('ads.vRegion as region, ads.vCooperation as cooperation, ads.vKeyword as keyword');
         
        $adsCols = 'ads.vT1ImageName as t1, ads.vT2ImageName as t2, ads.dImageJson';
        $adsCols .= ', ads.vOptions as options';
 
        $this->master->select($adsCols);
        $this->master->join('ads', 'ia.adsId=ads.id');
        $newInfo = $this->master->get_where('inspecting_ads ia', ['ia.id'=>$data['inspectId']])->row_array();

        $param['adsId'] = $adOriInfo['id'];
        $param['historyId'] = $adOriInfo['historyId'];
        if(USERAUTHCODE == 2 && isset($data['hospitalId']) )
        {
            $param['hospitalId'] = $data['hospitalId'];
        }

        $historyData = $this->ads_m->gethistoryMerge($param);
 
        $image_arr = ['t1ImageName', 't2ImageName'];
        $imageArr  = '';
        foreach( $image_arr as $val){
            if (isset($historyData[$val]) && !is_null($historyData[$val])){
               $imageArr .= $imageArr === '' ? $val.'|'.$historyData[$val] : ','.$val.'|'.$historyData[$val];
            }
            unset($historyData[$val]);
        }
        $historyData['image'] =$imageArr;
         
        //본문 이미지
        $newdImageJson = is_null($historyData['dImageJson']) || empty($historyData['dImageJson']) ? '[]' : json_decode($historyData['dImageJson']);

        //노출영역 처리
        $isE = '0';
        $isH = '0';
        if($historyData['exposure'] == 3)
        {
            //둘다 라면
            $isE = '1';
            $isH = '1';
        }
        else if($historyData['exposure'] == 2)
        {
            $isE = '0';
            $isH = '1';
        }
        else if($historyData['exposure'] == 1)
        {
            $isE = '1';
            $isH = '0';
        }

        
        //리플리케이터 시작
        //d이미지 처리용
        $iii=1;
        $dImageArr = [];
        foreach($newdImageJson as $key => $val){
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

        //t1, t2 처리
        $t1ImageName = $t2ImageName = '';
        $iArr4 = explode(',', $historyData['image']);

        foreach ($iArr4 as $item44)
        {
            $iArr44 = explode('|', $item44);

            if($iArr44[0] == 't1ImageName')
            {
                $t1ImageName = $iArr44[1];
            }
            else if($iArr44[0] == 't2ImageName')
            {
                $t2ImageName = $iArr44[1];
            }
        }


        //is_client_image2_change 정방향 이미지 변경여부 체크
        //adOriInfo(업데이트전)의 t2 이미지와 newInfo(히스토리 머지 업데이트 후)의 t2 이미지를 비교
        //if($adOriInfo['t2ImageName'] == $t2ImageName)
        if($data['isChangeImage2'] == 2)
        {
            //변경이 없으면
            $is_client_image2_change = 0;
        }
        else
        {
            $is_client_image2_change = 1;
        }

        //옵션처리
        $oArr = [];
        if($historyData['optionAdId'] != '')
        {
            $subArr = explode(',', $historyData['optionAdId']);

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

        $reAdDetailInfo = json_decode($historyData['adDetailInfo']);
 
        $reApply_text   = isset($reAdDetailInfo[0]) && $reAdDetailInfo[0] != ''  ? $reAdDetailInfo[0] : '이벤트 신청하기';
        $reApply_back_color   = isset($reAdDetailInfo[4]) && $reAdDetailInfo[4] != ''  ? $reAdDetailInfo[4] : '#1662bb';
        $reApply_text_color  = isset($reAdDetailInfo[2]) && $reAdDetailInfo[5] != ''  ? $reAdDetailInfo[5] : '#ffffff';   

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
            if( isset($historyData[$key]) && !is_null($historyData[$key]))
            {
                $customDataArr[$val] = $historyData[$key];
                $encodeCheck = true;
            }
            else 
            {
                $customDataArr[$val] = '';
            }
        }

        //커스텀값 한번더 체크.
        if(@$customDataArr['leader_name'] == '' and @$customDataArr['operation_register_name'] == '' and @$customDataArr['contact'] == '')
        {
            $encodeCheck = false;
        }

        $customData = $encodeCheck === true ? json_encode($customDataArr)  : '';

        $reGeneralCost = isset($historyData['generalCost']) && !empty($historyData['generalCost']) ? $historyData['generalCost'] : 0;
        $reGeneralCost = is_null($reGeneralCost) ? 0 : $reGeneralCost;

        $reDiscountCost = isset($historyData['discountCost']) && !empty($historyData['discountCost']) ? $historyData['discountCost'] : 0;
        $reDiscountCost = is_null($reDiscountCost) ? 0 : $reDiscountCost;

        //반려일 경우는 이벤트 수정처리하지 않는다.
        if($data['type'] != 2)
        {
            //이벤트 수정처리
            $insData = [
                'type_info'=>'admin_info',
                'contract_ids'=>$historyData['contractId'],
                'event_type'=>($historyData['channel']==2)?5:1, //일반이벤트: 1, 프로모션이벤트: 2, CPC 이벤트: 4, 굿닥파트너스: 5
                'client_searchable'=>($historyData['channel']==2)?0:1, //파트너스이면 검색 0으로 셋팅
                'event_category_ids'=>$historyData['category'], //머지 데이터로 변경
                'hospital_id'=>$historyData['hospitalId'],
                'external_media_category_ids'=>$historyData['cooperation'],
                'client_title'=>$historyData['adTitle'],
                'client_is_temporary'=> ($historyData['adDateExtend']=='Y')?'1':'0', // 1 상시진행, 0 기간설정
                'client_start_on'=>$historyData['adStartDate'],
                'client_end_on'=>$historyData['adEndDate'],
                'client_is_numerical_original_price'=>($historyData['costType'] == 1)?'1':'0', //0이면 텍스트가격
                'client_numerical_original_price'=>$reGeneralCost,
                'client_original_price'=>($historyData['costType'] == 1)?$reGeneralCost:0,
                'client_is_numerical_discounted_price'=>($historyData['costType'] == 1)?'1':'0',
                'client_numerical_discounted_price'=>$reDiscountCost,
                'is_bm_banner_show'=>($historyData['exposure'] == 1)?'1':'0', //메인배너 노출 여부 (노출 1, 비노출 0)
                'client_discounted_price'=>($historyData['costType'] == 1)?$reDiscountCost:$historyData['textCost'],
                'event_infos'=>$dImageArrJson,
                'client_event_category_ids'=>$historyData['category'],
                'client_image2'=>$t2ImageName,
                'client_image'=>$t1ImageName,
                'search_tags'=>$historyData['keyword'],
                'client_consider_number'=>is_null($historyData['deliberationCode'])?'':$historyData['deliberationCode'], //의료심의번호
                'model_image_ids'=>(is_null($historyData['whereImage']))?'':$historyData['whereImage'],
                'event_cost'=>$historyData['dbCost'], //머지 데이터로 변경
                'event_regions'=>$historyData['region'],
                'apply_text'=> $reApply_text,
                'apply_back_color' => $reApply_back_color, //버튼 컬러
                'apply_text_color' => $reApply_text_color, //버튼 텍스트 컬러
                'apply_image_count'=>(is_null($historyData['modelImageCount']))?'0':$historyData['modelImageCount'],
                'option_event_infos'=>$oArrJson,
                'hospital_operator_infos'=>$customData,
                'is_visible_on_event_list'=>$isE,
                'is_visible_on_hospital_show'=>$isH
            ];
            monologSend('event_modify', json_encode($insData));
            $result99 = $this->replicator_m->send('/api/events/'.$historyData['adsId'], 'PATCH', $insData);
            monologSend('event_modify', $result99);

            //롤백
            if($result99['message'] != 'success' or $result99 == 'Empty reply from server')
            {
                $this->master->trans_rollback();
                return false;
            }
        }



        //반려인 경우는 리플리케이터 호출이 필요없다. 종료는 바로종료 처리
        if($data['type'] == 1)
        {
            //승인처리할때는 제휴번호의 7,8을 보내지 않는다.
            $cooArr = explode(',', $historyData['cooperation']);

            $cooArr = arr_del($cooArr, 7);
            $cooArr = arr_del($cooArr, 8);

            $cooperation2 = implode(',', $cooArr);

            //이벤트 승인처리
            $insData2 = [
                'type_info'=>'event_confirm',
                'event_cost'=>$historyData['dbCost'],
                'event_regions'=>$historyData['region'],
                'apply_text'=> $reApply_text, //이벤트 등록, 후기 뷰의 워딩과 동일하게 처리
                'is_visible_on_event_list'=>$isE,
                'is_visible_on_hospital_show'=>$isH,
                'client_event_category_ids'=>$historyData['category'],
                'search_tags'=>$historyData['keyword'],
                'external_media_category_ids'=>$cooperation2,
                'is_client_image2_changed'=>$is_client_image2_change
            ];
            
            $loginsData = $insData2;
            $loginsData["method"] =[ 'PATCH', '/api/events/'.$historyData['adsId']];
            monologSend('inspect', json_encode($loginsData));
            $result00 = $this->replicator_m->send('/api/events/'.$historyData['adsId'], 'PATCH', $insData2);
            monologSend('inspect', $result00);

            //롤백
            if($result00['message'] != 'success' or $result00 == 'Empty reply from server')
            {
                $this->master->trans_rollback();
                return false;
            }

            //광고주 상태 업데이트 처리
            $data3 = [
                'contractId'=>$historyData['contractId'],
                'type'=>2
            ];
            $this->common_m->updateTotalInfo($data3);


        }
        else if($data['type'] == 4)
        {
            //이벤트 바로승인처리
            $insData2 = [
                'type_info'=>'force_live'
            ];
            monologSend('inspect', json_encode($insData2));
            $result00 = $this->replicator_m->send('/api/events/'.$historyData['adsId'], 'PATCH', $insData2);
            monologSend('inspect', $result00);

            //롤백
            if($result00['message'] != 'success' or $result00 == 'Empty reply from server')
            {
                $this->master->trans_rollback();
                return false;
            }

            //광고주 상태 업데이트 처리
            $data3 = [
                'contractId'=>$historyData['contractId'],
                'type'=>2
            ];
            $this->common_m->updateTotalInfo($data3);
        }
        else if (in_array($data['type'], [3,5]))
        {
            //3 종료, 5 바로종료
            $insData2 = [
                'type_info'=>'force_end'
            ];
            monologSend('inspect', json_encode($insData2));
            $result00 = $this->replicator_m->send('/api/events/'.$historyData['adsId'], 'PATCH', $insData2);
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
        return $result;
    }
}