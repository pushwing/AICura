<?php
/**
 * Created by PhpStorm.
 * User: blumine
 * Date: 2018. 2. 12.
 * Time: PM 5:45
 */
use GuzzleHttp\Client;
use GuzzleHttp\Psr7;
use GuzzleHttp\Exception\RequestException;

class OutApi_m extends CI_Model
{
    function __construct()
    {
        parent::__construct();
    }

    /**
     * 계약병원 유무 리턴, 이벤트 유뮤 추가
     * @param $data
     * @return array
     */
    function getAdsInfo($data)
    {
        $hArr = explode(',', $data['hospitalId']);

        $rArr = [];
        $i=0;

        foreach ($hArr as $item)
        {
            $rArr[$i]['hospitalId'] = $item;

            $result = $this->db->select('count(*) cnt')->get_where('contract', ['hospitalId'=>$item])->row_array();

            if($result['cnt'] > 0)
            {
                $rArr[$i]['isContract'] = true;
            }
            else
            {
                $rArr[$i]['isContract'] = false;
            }

            $result2 = $this->db->select('count(*) cnt')->get_where('ads', ['hospitalId'=>$item])->row_array();

            if($result2['cnt'] > 0)
            {
                $rArr[$i]['isAds'] = true;
            }
            else
            {
                $rArr[$i]['isAds'] = false;
            }

            $i++;
        }

        return ['contractHospital' => $rArr];
    }

    /**
     * 이벤트번호(콤마 구분)에 해당하는 정보 리턴
     * @param $data
     * @return array
     */
    function getADsInfoForBoard($data)
    {
        $hArr = explode(',', $data['adsId']);

        $rArr = [];
        $i=0;

        foreach ($hArr as $item)
        {
            $rArr[$i]['adsId'] = $item;

            $this->db->select('ads.*, ec.title as name');
            $this->db->join('event_categories ec', 'ads.vCategory=ec.id');
            $result = $this->db->get_where('ads', ['ads.id'=>$item])->row_array();

            $hospitalNameArr = $this->goodocapi->getHospitalNamesByIds([$result['hospitalId']]);
            $result['hospitalName'] = count($hospitalNameArr) == 0 ? '' : $hospitalNameArr[$result['hospitalId']];

            $rArr[$i]['category']['id'] = @$result['vCategory'];
            $rArr[$i]['category']['name'] = @$result['name'];
            $rArr[$i]['hospitalId'] = @$result['hospitalId'];
            $rArr[$i]['hospitalName'] = @$result['hospitalName'];
            $rArr[$i]['image'] = @$result['vT2ImageName'];
            $rArr[$i]['thumbnailImage'] = @$result['vT1ImageName'];
            $rArr[$i]['title'] = @$result['adTitle'];
            $rArr[$i]['discountCost'] = @$result['discountCost'];
            $rArr[$i]['generalCost'] = @$result['generalCost'];

            $i++;
        }

        return ['data' => $rArr];
    }

    /**
     * 네트워크병원 해제가능여부 체크
     * @param $data
     * @return array
     */
    function getRevocationInfo($data)
    {
        $rArr = [];
        $where = [
            'c.hospitalId'=>$data['hospitalId'],
            'co.contractStatus'=>1 //수주계약이 정상
        ];

        $this->db->select('count(*) cnt');
        $this->db->join('contract_order_connect coc', 'c.id=coc.contractId');
        $this->db->join('contract_order co', 'coc.contractOrderId=co.id');
        $result = $this->db->get_where('contract c', $where)->row_array(); //echo $this->db->last_query(); exit;

        if($result['cnt'] > 0)
        {
            $rArr['isDisconnectable'] = false;
        }
        else
        {
            $rArr['isDisconnectable'] = true;
        }

        return $rArr;
    }

    /**
     * 신청db insert
     * @param $data
     * @return int
     */
    function setEventDb($data)
    {
        $data['status'] = 1;
        $data['regDate'] = date("Y-m-d H:i:s");
        if ( isset($data['refreshToken']) )
        {
            unset($data['refreshToken']);
        }
        
        if (!is_numeric($data['adsId'])) {
            return false;
        }

        //광고 컨트랙트 ID 가져온다 
        $this->master->select('contractId');
        $adsCidRow = $this->master->get_where('ads', ['id' =>  $data['adsId']])->row_array();

        $ctId = empty($adsCidRow['contractId']) || is_null($adsCidRow['contractId']) ? false :  $adsCidRow['contractId'];
        unset($adsCidRow);

        if ($ctId === false) {
            return false;
        }

        $always = [
            432,32,102,774,449,655,656,469,768,65,14,879,717
        ];
        
        $migrate = [
            53,115,123,641,378,3,634,368,59,809,55,82,21,664,293,713,540,681,81,646,765,205,800,295,430,598,698,126,472,488,723,801,677,696,694,784,805,720,912,289,318,352,616,693,913,605,612,786,22,28,268,381,400,401,412,448,501,517,576,588,589,593,606,607,608,610,611,659,666,731,919,379,819,100,98,557,817,890,699,802,506,898,753,900,622,657,791,688,523,509,50,207,201,532,798,70,597,908,905,653,650,884,904,671,263,806,902,137,891,109,676,903,812,897,410,907,887,447,679,384,764,714,909,651,623,458,621,411,735,877,110,73,253,739,636,729,893,178,374,803,882,918,920,916,637,600,64,48,334,210,415,431,270,766,703,189,675,433,460,462,709,332,97,632,528,303,818,758,626,895,399,176,757,820,813,807,307,513,527,78,661,217,454,814,538,93,175,199,232,234,258,282,286,304,369,429,452,474,512,519,530,669,889,302,911,881,590,625,450,770,416,252,702,31,302,581,331,422,33,673,892,680,166,535,604,439,421,881,896,901,118,225,122,708,779,906,113,239,311,751,899,886,915,638,559
        ];

        //$check_ctId = true;

        if (in_array($ctId, $always)) {
            $check_ctId = false;
        }
        else {
            if (in_array($ctId, $migrate)) {
                $check_ctId = false;
            }
        }

        //비즈팀에서 이야기할때까지 이벤트를 종료하지 않는다. 6-7, 티, 헨도 통화로 확인한 사항
        //병원에 공지한 일정이 급박해서 서비스개념으로 마이너스가 되도 이벤트를 종료하지 않는다고 함
        $check_ctId = false;

        // 현재 시점 잔액, 아래에서 사용해서 위치 이동
        $this->load->model('contractOrder_m');
        $balancePrice = (int) $this->contractOrder_m->getBalancePrice(['contractId' => $ctId]);

        if ($check_ctId === true) {
            //광고 컨트랙트 ID로 dbCost를 찾는다
            $this->master->select('MAX(dbCost) AS dbCost');
            $dbCostRow = $this->master->get_where('ads', ['contractId' =>  $ctId])->row_array();    
            $maxDbCost =  empty($dbCostRow['dbCost']) || is_null($dbCostRow['dbCost']) ? false :  $dbCostRow['dbCost'];  
            unset($dbCostRow);

            if ($maxDbCost === false) {
                return false;    
            }

            //가장 큰 디비코스트 보다 현재 시점 잔액이 적다면 종료
            //dd([$maxDbCost,$balancePrice]);
            if ((int)$maxDbCost > $balancePrice) {
                $this->master->select('id');
                $adsIdRows = $this->master->get_where('ads', ['contractId' => $ctId, 'isLive' => 'Y'])->result_array();
                $adsIdArr = [];
                foreach ($adsIdRows as $key => $val) 
                {
                    $adsIdArr[] = $val['id'];
                }
                unset($adsIdRows);  
                
                $this->load->model('ads_m');
                $this->load->model('replicator_m');

                foreach($adsIdArr as $key => $val) {
                    $inspectionData = [];
                    $inspectionData['users_id'] = 1;
                    $inspectionData["type"] = 1;    
                    $inspectionData['adsId'] = $val;
                    $inspectionData['isHospital'] = 'N';
                    $this->ads_m->updateListAction($inspectionData); 
                }
            
                return false;
            }
            
            //넘어온 디비코스트가 시점 잔액보다 크다면 종료
            if ((int) $data['dbCost'] > $balancePrice) {
                $this->master->select('id');
                $adsIdRows = $this->master->get_where('ads', ['contractId' => $ctId, 'isLive' => 'Y'])->result_array();
                $adsIdArr = [];
                foreach ($adsIdRows as $key => $val) 
                {
                    $adsIdArr[] = $val['id'];
                }
                unset($adsIdRows);  
            
                $this->load->model('ads_m');
                $this->load->model('replicator_m');

                foreach($adsIdArr as $key => $val) {
                    $inspectionData = [];
                    $inspectionData['users_id'] = 1;
                    $inspectionData["type"] = 1;    
                    $inspectionData['adsId'] = $val;
                    $inspectionData['isHospital'] = 'N';
                    $this->ads_m->updateListAction($inspectionData); 
                } 
                return false;
            }   
        }

        $this->master->insert('call_request', $data);
        $insertId = $this->master->insert_id();
            
        //원장 차감 액션. 3 소진
        //이벤트 및 계약정보 가져오기
        $sql = "
            select cr.callRequestId, cr.eventCost, ads.contractId, ads.contractOrderId from call_request cr 
            join ads on cr.adsId=ads.id
            where cr.callRequestId='".$data['callRequestId']."'
        ";
        $adsInfo = $this->master->query($sql)->row_array();

        $rArr['contractId'] = $adsInfo['contractId'];
        $rArr['contractOrderId'] = $adsInfo['contractOrderId'];
        $rArr['users_id'] = 1; //자동처리라 유저번호가 없다.
        $rArr['chargePrice'] = $adsInfo['eventCost'];
        $rArr['type']= 3; //소진
        $rArr['memo'] = 'db소진';
        $rArr['callRequestId'] = $adsInfo['callRequestId'];
        $rArr['checkRefundFee'] == 2; //둘다 차감안함으로 기본값 셋팅

        log_message('info', 'dbStart - '.json_encode($data));

        $res = $this->contract_m->depositConfirm($rArr);

        log_message('info', 'dbEnd - '.$res);
        //원장 액션 끝

        //시점잔액 업데이트
        $rPrice = $balancePrice - $adsInfo['eventCost'];
//        $this->master->where('contractId', $ctId)->update('totalReadyPrice', ['totalReady'=>$rPrice, 'modDate'=>$data['regDate']]);
        $data3 = [
            'contractId'=>$adsInfo['contractId'],
            'totalReady'=>$rPrice,
            'type'=>1
        ];
        $this->common_m->updateTotalInfo($data3);

        if($res)
        {
            return $insertId;
        }

        return false;

    }

    /**
     * 원장상태 수정
     * @param $data
     * @return int
     */
    function updateEventDb($data)
    {
        if($data['status'] == 10)
        {
            //삭제처리
            $uArr = [
                'isDelete' => 1,
                'modifyDate' => date("Y-m-d H:i:s")
            ];
        }
        else
        {
            //상태 업데이트
            $uArr = [
                'status' => $data['status'],
                'modifyDate' => date("Y-m-d H:i:s")
            ];
        }

        $this->master->where('callRequestId', $data['callRequestId']);
        $this->master->update('call_request', $uArr);
        $return = $this->master->affected_rows();

        //원장 액션 추가 8 중복, 9 결번
        //type, contractId, contractOrderId, chargePrice
        //이벤트 및 계약정보 가져오기
//        $sql = "
//            select cr.eventCost, ads.contractId, ads.contractOrderId from call_request cr
//            join ads on cr.adsId=ads.id
//            where cr.callRequestId='".$data['callRequestId']."'
//        ";
//        $adsInfo = $this->db->query($sql)->row_array();

//        $rArr['contractId'] = $adsInfo['contractId'];
//        $rArr['contractOrderId'] = $adsInfo['contractOrderId'];
//        $rArr['users_id'] = 1; //자동처리라 유저번호가 없다.
//        $rArr['chargePrice'] = $adsInfo['eventCost'];
//
//        if($data['status'] == 8)
//        {
//            $rArr['type']= 4; //기타충전
//            $rArr['memo'] = '중복번호 충전 처리';
//        }
//        else if($data['status'] == 9)
//        {
//            $rArr['type']= 4; //기타충전
//            $rArr['memo'] = '결번 충전 처리';
//        }
//        else if($data['status'] == 10)
//        {
//            $rArr['type']= 4; //삭제
//            $rArr['memo'] = '결번 충전 처리';
//        }

        //$this->contract_m->depositConfirm($rArr);

        //callRequestId 에 해당하는 원장의 price를 0으로 업데이트 한다.
        $this->master->where('callRequestId', $data['callRequestId']);
        $this->master->update('deposit', ['price'=>0]);
        //원장 액션 끝

        return $return;
    }


}