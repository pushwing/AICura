<?php
/**
 * Created by PhpStorm.
 * User: blumine
 * Date: 2018. 2. 12.
 * Time: PM 5:45
 */

class contractOrder_m extends CI_Model
{
    function __construct()
    {
        parent::__construct();
    }

    function _where($data)
    {
        if(@$data['agencyUserId'] != '')
        {
            $this->db->where('co.agencyUserId', $data['agencyUserId']);
        }

        if(@$data['manageUserId'] != '')
        {
            $this->db->where('co.manageUserId', $data['manageUserId']);
        }

        if(@$data['contractType'] != '')
        {
            $this->db->where('co.contractType', $data['contractType']);
        }

        if(@$data['contractStatus'] != '')
        {
            $this->db->where('co.contractStatus', $data['contractStatus']);
        }

        if(@$data['isTax'] != '')
        {
            if($data['isTax'] == '1')
            {
                $this->db->where('co.taxIssueDate is not null');
            }
            else
            {
                $this->db->where('co.taxIssueDate is null');
            }
        }

        if(@$data['adType2'] != '')
        {
            $this->db->where('co.adType2', $data['adType2']);
        }

        if(@$data['searchType'] != '')
        {
            switch ($data['searchType'])
            {
                case "1": //병원명
                    $this->db->like('co.hospitalName', $data['searchWord']);
                    break;
                case "2": //대표자명
                    $this->db->like('co.taxChargeName', $data['searchWord']);
                    break;
                case "3": //대행사명
                    $this->db->like('co.agencyCompanyName', $data['searchWord']);
                    break;
                case "4": //사업자번호
                    $this->db->like('co.taxBusinessNo', $data['searchWord']);
                    break;
            }
        }

        if($data['type'] == 2)
        {
            //종료계약이면
            $this->db->where('use', $data['type']);
            //use 필드는 진행중(1), 종료(2) 필드
        }
    }

    /**
     * 계약리스트 조회
     * 전체 카운트 반환
     * @param $data
     * @return array
     */
    function getContractList($data)
    {
        if($data['type'] == '')
        {
            //기본값 고정
            $data['type'] = 1;
        }

        //var_dump($data['isTax']);

        //검색어 처리
        $this->_where($data);

        //선불여부 가져오기 위해
        $this->db->select('co.*, c.payType, c.id as contractId, m.memo');
        $this->db->join('contract_order_connect coc', 'co.id=coc.contractOrderId');
        $this->db->join('contract c', 'coc.contractId=c.id');
        $this->db->join('memo m', 'co.id=m.targetId and m.memoType=3', 'left');
        $this->db->group_by('co.id');
        $result0 = $this->db->get('contract_order co')->result_array();
        $totCnt = count($result0);

        if($totCnt > 0)
        {
            //검색어 처리
            $this->_where($data);

            $limit = ($data['page'] - 1) * $data['limit'];
            $this->db->limit($data['limit'], $limit);

            $this->db->select('co.*, c.payType, c.id as contractId, m.memo');
            //$this->db->select('p.resultCode, p.applyDate, p.result1');
            $this->db->select('(select count(*) from contract_order where  contractStatus=1 and  
            hospitalId=co.hospitalId and (depositDate is null or depositDate = "" or depositDate = "0000-00-00 00:00:00")) as unpayment', false);
            $this->db->select('ifnull((select id from contract_order where  contractStatus=1 and adType=co.adType and adType2=co.adType2 and 
            hospitalId=co.hospitalId and (depositDate is not null or depositDate != "" or depositDate != "0000-00-00 00:00:00") order by id desc limit 1), "0") as lastContractId', false);
            $this->db->select('(select concat(resultCode,"|",  ifnull(applyDate, ""),"|", result1, "|", fnCode1) from payment 
            where contractId=coc.contractId and contractOrderId=co.id order by id desc limit 1) as paymentInfo', false);
            $this->db->select('(select count(*) from deposit where contractId=c.id and contractOrderId=co.id and status=11) as reContractUnable', false);
            $this->db->join('contract_order_connect coc', 'co.id=coc.contractOrderId');
            $this->db->join('contract c', 'coc.contractId=c.id');
            $this->db->join('memo m', 'co.id=m.targetId and m.memoType=3', 'left');
            //$this->db->join('payment p', 'co.id=p.contractOrderId', 'left');
            $this->db->group_by('co.id');
            $this->db->order_by('co.id', 'desc');
            $result = $this->db->get('contract_order co')->result_array();
        }
        else
        {
            $result = [];
        }

        //echo $this->db->last_query();

        $resultArr = ['list'=>$result, 'totCnt'=>$totCnt];

        return $resultArr;
    }

    /**
     * 상세계약 조회
     * @param $data
     * @return array
     */
    function getContractInfo($data)
    {
        //리턴 항목에 현재 입금된 금액 리턴, chargePrice
        $sql = "
            select 
                co.*, coc.contractId, 
                (select sum(price) from deposit where contractOrderId=co.id and status=2) chargePrice ,
                (select count(*) from contract_order where contractStatus=1 and hospitalId=co.hospitalId and (depositDate is null or depositDate = '' or depositDate = '0000-00-00 00:00:00') and adType2=co.adType2 and adType=co.adType) as unpayment,
                (select count(*) from deposit where contractId=coc.contractId and contractOrderId=co.id and status=11) as reContractUnable,
                ifnull((select id from contract_order where  contractStatus=1 and  
                    hospitalId=co.hospitalId  and adType2=co.adType2 and adType=co.adType and (depositDate is not null or depositDate != '' or depositDate != '0000-00-00 00:00:00') order by id desc limit 1), '0') as lastContractId
            from 
                contract_order co 
            join 
                contract_order_connect coc on co.id=coc.contractOrderId
            where co.id ='".$data['contractOrderId']."'
        ";
        $result = $this->db->query($sql)->row_array();

        if(!$result)
        {
            return false;
        }

        $this->db->order_by('id', 'desc');
        $result['memo'] = $this->db->get_where('memo', ['targetId'=>$data['contractOrderId'], 'memoType'=>1])->result_array();

        $this->db->order_by('id', 'desc');
        $result['taxMemo'] = $this->db->get_where('memo', ['targetId'=>$data['contractOrderId'], 'memoType'=>2])->result_array();

        return $result;
    }

    /**
     * 입금확인용 계약정보 조회
     * @param $data
     * @return array
     */
    function getContractInfo2($data)
    {
        $contactData = $this->getContractInfo($data);
        $sumData = $this->getSumCharge($data);
     

        $result['adPrice'] = $contactData['adPrice']; //계약금액
        $result['waitIssuePrice'] = $contactData['adPrice'] - $sumData['price']; //발행 대기중 금액
        $result['balancePrice'] = $this->getBalancePrice($data); //현재 잔액 = 전체 충전금액 - 소진금액
        $result['regDate'] = $contactData['regDate'];
        $result['contractOrderId'] = $data['contractOrderId'];
        $result['hospitalName'] = $contactData['hospitalName'];

        if($contactData['taxIssueDate'])
        {
            $tax = '발행';
        }
        else
        {
            $tax = '미발행';
        }
        $result['taxIssue'] = $tax; //세금개산서 발행여부
        $result['agencyCompanyName'] = $contactData['agencyCompanyName'];
        $result['agencyCompanyFeeRate'] = $contactData['agencyCompanyFeeRate'];
        $result['agencyUserId'] = $contactData['agencyUserId'];
        $result['manageUserId'] = $contactData['manageUserId'];

        return $result;
    }

    /**
     * 수주계약번호에 해당하는 현재 잔액 구하기 -> 계약에 해당하는 현재 잔액 구하기로 변경
     * 1 수주, 2 계약충전, 3 소진, 4 기타충전, 5 환불수수료, 6 발행 환불, 7 계약 환불, 8 기타 소진, 9 발행취소, 10 계약취소,
     * 11 이월소진, 12 이월충전
     * @param $data
     * @return float
     */
    function getBalancePrice($data)
    {
        $checkDb = $this->common_m->isInTrans() === true ?  $this->master  :  $this->db;

        $sql = '
            select sum(price) chargePrice from deposit 
            where status in(2,4) 
            and contractId="'.$data['contractId'].'" 
            '; //충전금액
        $r1 = $checkDb->query($sql)->row_array();

        $sql2 = '
            select sum(price) usePrice from deposit 
            where status in(3,5,6,7,8)
            and contractId="'.$data['contractId'].'" 
            '; //소진금액
        $r2 = $checkDb->query($sql2)->row_array();

        return round($r1['chargePrice'] - $r2['usePrice']);
    }

    /**
     * 계약번호에 해당하는 최종 수주계약번호 리턴
     * @param $contractId
     * @return bool
     */
    function getLastContractOrderId($contractId)
    {
        $this->db->select('co.id');
        $this->db->join('contract_order_connect coc', 'co.id=coc.contractOrderId');
        $this->db->join('contract c', 'coc.contractId=c.id');
        $this->db->order_by('co.id', 'desc');
        $this->db->limit(1);
        $rest2 = $this->db->get_where('contract_order co', ['c.id'=>$contractId])->row_array();
        if($rest2)
        {
            return $rest2['id'];
        }
        else
        {
            return false;
        }
    }

    /**
     * 영업담당자에 해당하는 v1 번호 리턴
     * @param $contractManagerId
     * @return false|int|string
     */
    function changeContractManager($contractManagerId)
    {
        //영업담당자 배열
        $mArr = [
            '6'=>'50316',
            '5'=>'54531',
            '7'=>'60385',
            '8'=>'69709',
            '10'=>'127876',
            '9'=>'242494'
        ];

        $nmArr = [
            '50316'=>'6',
            '54531'=>'5',
            '60385'=>'7',
            '69709'=>'8',
            '127876'=>'10',
            '242494'=>'9'
        ];

        $key = array_search($contractManagerId, $nmArr);

        if(!$key)
        {
            return 50316;
        }

        return $key;

    }

    /**
     * 계약등록
     * @param $data
     * @return bool
     * @throws Exception
     */
    function setContractInfo($data)
    {
        $usersId = $data['users_id'] ;
        $adPrice = $data['adPrice'];
        $contractType = $data['contractType'];

        if(@$data['memo'])
        {
            $memo = $data['memo'];
            unset($data['memo']);
        }

        if(@$data['taxMemo'])
        {
            $taxMemo = $data['taxMemo'];
            unset($data['taxMemo']);
        }

        unset($data['menu_id']);
        unset($data['token']);
        unset($data['users_id']);
        unset($data['refreshToken']);

        if(@$data['contractId'])
        {
            //이전 계약번호
            $cntId = $data['contractId'];
            unset($data['contractId']);
        }

        if(@$data['contractOrderId'])
        {
            //이전 수주계약번호
            $cntOrderId = $data['contractOrderId'];
            unset($data['contractOrderId']);
        }

        $data['regDate'] = date("Y-m-d H:i:s");

        //메소드 내부에서 트랜잭션이 시작인지 체크 
        $checkMethodTrans = false;
        if ($this->common_m->isInTrans() === false)
        {
            $this->master->trans_begin();
            $checkMethodTrans = true;   
        }

        if($contractType == '2')
        {
            $data['parentId'] = $cntOrderId;
        }
        //수주 테이블 입력
        $this->master->insert('contract_order', $data);
        $oIds = $this->master->insert_id();

        if($contractType == '1')
        {
            //신규일 경우
            //성공이면 계약테이블 수주번호와 나머지 데이터 입력
            unset($data['contractType']);
            unset($data['adPrice']);
            unset($data['taxIssueRequestDate']);
            unset($data['agencyCompanyFeeRate']);
            unset($data['agencyCompanyChargeName']);
            unset($data['agencyCompanyChargePhone']);
            unset($data['agencyCompanyChargeEmail']);
            unset($data['isNetwork']);
            $this->master->insert('contract', $data);
            $cIds = $this->master->insert_id();

            //total_ready_price insert
            $tArr = ['contractId'=>$cIds, 'totalReady'=>0, 'modDate'=>date("Y-m-d H:i:s")];
            $this->master->insert('total_ready_price', $tArr);

            //리플리케이터 시작
            //계약 등록
            $iData = [
                'id' => $cIds, //계약번호
                'title' => $data['title'],
                'recharge_price' => $adPrice,
                'contract_manager_id' => $this->changeContractManager($data['agencyUserId']), //영업담당
                'use_auto_end_events' => 1, //잔액소진시 자동종료 1, 자동종료안함 0
                'marketing_group_id' => 0, //마케팅그룹 의미없음
                'event_limit' => 100, //이벤트생성제한 의미없음
                'hospital_contracts_attributes'=>$data['hospitalId'], //매핑병원
                'event_contracts_attributes'=> '' //매핑 이벤트.
            ];
            $this->replicator_m->send('/api/contracts', 'POST', $iData);
            //리플리케이터 끝
        }
        else
        {
            //재계약일 경우
            $cIds = $cntId;
        }

        //매핑테이블 입력
        $this->master->insert('contract_order_connect', array('contractId'=>$cIds, 'contractOrderId'=>$oIds));
        $cocIds = $this->master->insert_id();

        if($contractType == '2')
        {
            //재계약일 경우
            //최종 수주계약번호 리턴, 잔액조회시 사용
            //$lastContractOrderId = $this->getLastContractOrderId($cIds);

            //기존 계약여부 체크하여 잔액이 있다면 이월 처리한다. (원장 입력)
            $data2['contractId'] = $cIds;
            $data2['contractOrderId'] = $cntOrderId;
            $checkBal = $this->getBalancePrice($data2);


            //최근 수주 계약번호 가져와서 원장에 이월소진 처리
            //마이너스 여도 이월처리 똑같이 한다. 썩을 레거시 19-06-11

            //원장테이블 입력
            $dArr = array(
                'status'=>11, //이월소진
                'isMinus' => ($checkBal > 0)? 0:1, //양수 0, 음수 1
                'contractId'=>$cIds,
                'contractOrderId'=>$cntOrderId,
                'usersId' => $usersId,
                'price'=>$checkBal, //잔액
                'regDate'=>$data['regDate'],
                'modDate'=>$data['regDate']
            );
            $this->master->insert('deposit', $dArr);

            //$cIds, $oIds 에 해당하는 원장 입력(이월 충전)
            $dArr = array(
                'status'=>12, //이월충전
                'isMinus' => ($checkBal > 0)? 0:1, //양수 0, 음수 1
                'contractId'=>$cIds,
                'contractOrderId'=>$oIds,
                'usersId' => $usersId,
                'price'=>$checkBal, //잔액
                'regDate'=>$data['regDate'],
                'modDate'=>$data['regDate']
            );
            $this->master->insert('deposit', $dArr);


            //이월 금액 여부와 상관없이 무조건 진행한다.

            //수주계약에 이월소진으로 업데이트 contractStatus=6
            //수주계약은 종료의 개념이 필요가 없었으나 이월소진, 충전 개념이 들어가면서 이원소진된 계약의 종료여부(재계약이나 기타 환불
            //등의 액션에서)를 알 수 없어서 이월(종료)는 contractStatus=6 을 추가하여 처리한다. biz-1199
            $this->master->where('id', $cntOrderId);
            $this->master->update('contract_order', ['contractStatus'=>6]);

            //biz-1195 재계약시 매핑된 이벤트의 수주계약번호를 재계약 수주번호로 업데이트한다.
            $this->master->where(['contractOrderId'=>$cntOrderId]);
            $this->master->update('ads', ['contractOrderId'=>$oIds]);
            log_message('info', $this->master->last_query());

            //ads v 접두어 필드 업데이트를 위한 절차
            //1. ads_history 테이블의 deleteJson에 contractOrderId를 json화 하여 ads번호와 함께 insert
            //2. getHistoryMerge 함수로 해당 내용을 가져온다
            //3. 가져온 json 값을 ads 테이블의 adsHistoryJson 항목에 업데이트 한다.
            $this->setVColumn($cntId, $oIds);

            
            //임시저장도 업데이트 친다.
            $this->master->where(['contractOrderId'=>$cntOrderId]);
            $this->master->update('ads_history', ['contractOrderId'=>$oIds]);


        }

        //메모 입력
        if(@$memo)
        {
            $arr = [
                'memoType'=>1,
                'targetId'=>$oIds,
                'targetId2'=>'', //원장번호 없는 경우
                'userId'=>$usersId,
                'memo'=>$memo
            ];
            $this->common_m->setContractOrderMemo($arr);
            //$this->db->insert('memo', ['memoType'=>1, 'targetId'=>$oIds, 'userId'=>$usersId, 'memo'=>$memo, 'regDate'=>$data['regDate']]);
        }

        if(@$taxMemo)
        {
            $arr2 = [
                'memoType'=>2,
                'targetId'=>$oIds,
                'targetId2'=>'', //원장번호 없는 경우
                'userId'=>$usersId,
                'memo'=>$taxMemo
            ];
            $this->common_m->setContractOrderMemo($arr2);
            //$this->db->insert('memo', ['memoType'=>2, 'targetId'=>$oIds, 'userId'=>$usersId, 'memo'=>$taxMemo, 'regDate'=>$data['regDate']]);
        }

        //원장테이블 입력
        $dArr = array(
            'status'=>1, //1 수주, 2 계약충전, 3 소진, 4 서비스충전, 5 결번충줜, 6 발행 환불, 7 계약 환불, 8 기타 소진
            'isMinus' => ($adPrice > 0)? 0:1, //양수 0, 음수 1
            'contractId'=>$cIds,
            'contractOrderId'=>$oIds,
            'usersId' => $usersId,
            'price'=>$adPrice,
            'regDate'=>$data['regDate'],
            'modDate'=>$data['regDate']
        );
        $this->master->insert('deposit', $dArr);
        
        //트랜잭션
        if ($checkMethodTrans === true)
        {
            if ($this->master->trans_status() === FALSE)
            {
                $this->master->trans_rollback();
                return false;
            }
            $this->master->trans_commit();
            
        }
        return $oIds;
    }

    /**
     * ads 테이블 vContractOrderId 값 수정을 위한 함수
     * @param $contractId
     * @param $contractOrderId
     */
    function setVColumn($contractId, $contractOrderId)
    {
        //$this->load->model('ads_m');

        //ads v 접두어 필드 업데이트를 위한 절차
        //1. ads_history 테이블의 deleteJson에 contractOrderId를 json화 하여 ads번호와 함께 insert
        //2. getHistoryMerge 함수로 해당 내용을 가져온다
        //3. 가져온 json 값rlwhs을 ads 테이블의 adsHistoryJson 항목에 업데이트 한다.
        $adsArr = $this->master->select('id, hospitalId, dImageJson')->get_where('ads', ['contractId'=>$contractId, 'contractOrderId'=>$contractOrderId])->result_array();
        log_message('info', json_encode($adsArr));
   
        if(count($adsArr) > 0)
        {
            foreach ($adsArr as $item)
            {
                //1
                //빈센트 -> 재계약시 dImageJson 정보도 가져와서 히스토리에 넣어준다.
                $iArr9 = [
                    'adsId'=> $item['id'], 'contractOrderId'=>$contractOrderId, 'deletejson'=>json_encode(['contractOrderId'=>$contractOrderId]),
                    'dImageJson' => $item['dImageJson']
                ];
                $this->master->insert('ads_history', $iArr9);

                //2
                $param = [];

                if(USERAUTHCODE == 2 && isset($item['hospitalId']) )
                {
                    $param['hospitalId'] = $item['hospitalId'];
                }

                $param['adsId'] = $item['id'];
                log_message('info', 'setv : '.json_encode($param));
                $historyResult = $this->ads_m->gethistoryMerge($param);

                //빈센트 히스토리 머지 가져와서 dImageJson 정보를 삭제 해 준다.
                unset($historyResult['dImageJson']);

                //34
                $uArr = [
                    'adsHistoryJson'=>json_encode($historyResult)
                ] ;
                $this->master->where('id', $item['id']);
                $this->master->update('ads', $uArr);
            }
        }
    }

    /**
     * 계약명 중복체크
     * @param $data
     * @return bool
     */
    function checkTitle($data)
    {
        $count = $this->db->get_where('contract_order', array('title'=>$data['title']))->num_rows();

        if($count > 0)
        {
            return false;
        }
        else
        {
            return true;
        }
    }

    /**
     * 계약정보 업데이트
     * @param $data
     * @return int
     */
    function updateContractInfo($data, $data2)
    {
        $regDate = date("Y-m-d H:i:s");

        //메소드 내부에서 트랜잭션이 시작인지 체크 
        $checkMethodTrans = false;
        if ($this->common_m->isInTrans() === false)
        {
            $this->master->trans_begin();
            $checkMethodTrans = true;   
        }

        if(@$data2['memo'])
        {
            $memo = $data2['memo'];
            unset($data2['memo']);
        }

        if(@$data2['taxMemo'])
        {
            $taxMemo = $data2['taxMemo'];
            unset($data2['taxMemo']);
        }

        if(count($data2) > 0)
        {
            $this->master->where('id', $data['contractOrderId']);
            $this->master->update('contract_order', $data2);
            $result = $this->master->affected_rows(); //echo $result; exit;

            //취소 처리
            if(@$data2['contractStatus'])
            {
                if($data2['contractStatus'] == 4 or $data2['contractStatus'] == 3)
                {
                    if($data2['contractStatus'] = 3)
                    {
                        $status = 9;
                    }
                    else
                    {
                        $status = 10;
                    }
                    //deposit 입력처리
                    $dArr = array(
                        'status'=>$status, //1 수주, 2 계약충전, 3 소진, 4 서비스충전, 5 결번충줜, 6 발행 환불, 7 계약 환불, 8 기타 소진, 9 발행취소, 10 계약취소
                        'contractId'=>$data['contractId'],
                        'contractOrderId'=>$data['contractOrderId'],
                        'usersId' => $data['users_id'],
                        'regDate'=>$regDate,
                        'modDate'=>$regDate
                    );
                    $this->master->insert('deposit', $dArr);
                    $depositId = $this->master->insert_id();
                }
            }

            //contract 업데이트를 위한 데이터 가공
            $dataContract = $data2;

            unset($dataContract['contractStatus']);
            unset($dataContract['agencyCompanyFeeRate']);
            unset($dataContract['memo']);
            unset($dataContract['taxIssueRequestDate']);
            unset($dataContract['taxMemo']);
            unset($dataContract['adPrice']);
            unset($dataContract['agencyCompanyChargeName']);
            unset($dataContract['agencyCompanyChargePhone']);
            unset($dataContract['agencyCompanyChargeEmail']);

            if(count($dataContract) > 0)
            {
                $contractId = $this->getContractId($data); //계약번호

                $this->master->where('id', $contractId);
                $this->master->update('contract', $dataContract);
                $result2 = $this->master->affected_rows();
            }
        }

        //메모 입력
        if(@$memo)
        {
            $arr = [
                'memoType'=>1,
                'targetId'=>$data['contractOrderId'],
                'targetId2'=>'', //원장번호 없는 경우
                'userId'=>$data['users_id'],
                'memo'=>$memo
            ];
            $this->common_m->setContractOrderMemo($arr);
        }

        if(@$taxMemo)
        {
            $arr2 = [
                'memoType'=>2,
                'targetId'=>$data['contractOrderId'],
                'targetId2'=>'', //원장번호 없는 경우
                'userId'=>$data['users_id'],
                'memo'=>$taxMemo
            ];
            $this->common_m->setContractOrderMemo($arr2);
        }

        //금액 변경이 있으면..
        if(@$data2['adPrice'])
        {
            $dArr = array(
                'isMinus' => ($data2['adPrice'] > 0)? 0:1, //양수 0, 음수 1
                'price'=>$data2['adPrice'],
                'modDate'=>date("Y-m-d H:i:s")
            );
            $this->master->where('contractOrderId', $data['contractOrderId']); //수주계약번호
            $this->master->where('status', 1); //수주인 것만 변경
            $this->master->update('deposit', $dArr);
        }

        if ($this->master->trans_status() === FALSE)
        {
            $this->master->trans_rollback();
            return false;
        }
        else
        {
            $this->master->trans_commit();
            return true;
        }
    }

    /**
     * 수주번호로 계약번호 구하기
     * @param $data
     * @return mixed
     */
    function getContractId($data)
    {
        $checkDb = $this->common_m->isInTrans() === true ?  $this->master  :  $this->db;

        $result = $checkDb->get_where('contract_order_connect', array('contractOrderId'=>$data['contractOrderId']))->row_array();

        return $result['contractId'];
    }

    /**
     * 세금계산서 발행 및 메모 입력
     * @param $data
     * @return bool
     */
    function taxIssue($data)
    {
        $this->master->where('id', $data['contractOrderId']);
        $this->master->update('contract_order', array('taxIssueDate' => $data['taxIssueDate']));
        $result = $this->master->affected_rows();

        //메모 입력
        if(@$data['memo'])
        {
            $arr2 = [
                'memoType'=>2,
                'targetId'=>$data['contractOrderId'],
                'targetId2'=>'', //원장번호 없는 경우
                'userId'=>$data['users_id'],
                'memo'=>$data['memo']
            ];
            $this->common_m->setContractOrderMemo($arr2);
        }

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
     * 입금확인 처리
     * @param $data
     * @return int
     */
    function depositConfirm($data)
    {
        $regDate = date("Y-m-d H:i:s");

        //수주계약금액 가져오기
        $orderData = $this->getContractInfo($data);

        //현재 계약충전금액 가져오기
        $sumDate = $this->getSumCharge($data); //var_dump($sumDate);

        //메소드 내부에서 트랜잭션이 시작인지 체크 
        $checkMethodTrans = false;
        if ($this->common_m->isInTrans() === false)
        {
            $this->master->trans_begin();
            $checkMethodTrans = true;   
        }

        //계약금액 < 충전합계  + 현 충전액 이면 금액 오버라 리턴 처리한다.
        if($orderData['adPrice'] < ($sumDate['price'] + $data['chargePrice']))
        {
            //return 500;
        }

        //세금부분 계산 및 입력
        $taxPrice = $data['chargePrice'] /  11 ; //세금액
        $chargePrice = $data['chargePrice'] /  1.1 ; //계약액

        $arr = array(
            'status'=>13, //1 수주, 2 계약충전, 3 소진, 4 기타충전, 5 환불수수료, 6 발행 환불, 7 계약 환불, 8 기타소진, 9 발행취소, 10 계약취소, 11 이월 소진, 12 이월 충전, 13 세금
            'isMinus' => 0, //양수 0, 음수 1. 고정
            'contractId'=>$data['contractId'],
            'contractOrderId'=>$data['contractOrderId'],
            'usersId' => $data['users_id'],
            'price'=>$taxPrice,
            'regDate'=>$regDate,
            'modDate'=>$regDate
        );
        $this->master->insert('deposit', $arr);

        //$data['chargePrice'] = $data['chargePrice'] - $taxPrice; //금액에서 세금 뺀다.
        $data['chargePrice'] = $chargePrice;

        //계약금액만 입력
        $dArr = array(
            'status'=>2, //1 수주, 2 계약충전, 3 소진, 4 서비스충전, 5 결번충줜, 6 발행 환불, 7 계약 환불, 8 기타 소진
            'isMinus' => ($data['chargePrice'] > 0)? 0:1, //양수 0, 음수 1
            'contractId'=>$data['contractId'],
            'contractOrderId'=>$data['contractOrderId'],
            'usersId' => $data['users_id'],
            'price'=>$data['chargePrice'],
            'regDate'=>$regDate,
            'modDate'=>$regDate
        );
        $this->master->insert('deposit', $dArr);
        $depositId = $this->master->insert_id();

        if($data['memo'])
        {
            $arr2 = [
                'memoType'=>3, //원장메모(운영자)
                'targetId'=>$data['contractOrderId'],
                'targetId2'=>$depositId, //원장번호
                'userId'=>$data['users_id'],
                'memo'=>$data['memo']
            ];
            $this->common_m->setContractOrderMemo($arr2);
        }

        if($data['customerMemo'])
        {
            $arr2 = [
                'memoType'=>4, //원장메모(고객노출용)
                'targetId'=>$data['contractOrderId'],
                'targetId2'=>$depositId, //원장번호
                'userId'=>$data['users_id'],
                'memo'=>$data['customerMemo']
            ];
            $this->common_m->setContractOrderMemo($arr2);
        }

        $sumDate2 = $this->getSumCharge($data); //var_dump($sumDate); exit;

        //트랜잭션
        if ($checkMethodTrans === true)
        {
            if ($this->master->trans_status() === FALSE)
            {
                $this->master->trans_rollback();
                return 400;
            }
            $this->master->trans_commit();
            return 200;
        }   
        return 200;
    }

    /**
     * 수주계약번호에 해당하는 계약충전 총액 조회
     * @param $data
     * @return array
     */
    function getSumCharge($data)
    {
        $checkDb = $this->common_m->isInTrans() === true ?  $this->master  :  $this->db;
        
        $checkDb->select_sum('price')->group_by('contractOrderId');

        $result = $checkDb->get_where('deposit', ['contractOrderId'=>$data['contractOrderId'], 'contractId'=>$data['contractId'], 'status'=>2])->row_array();

        return $result;
    }

    /* -------------------------------------  */
    /**
     * 계약 종료
     * @param $data
     * @return int
     */
    function closeContract($data)
    {
        //contract table
        $this->master->where('id', $data['contractId']);
        $this->master->update('contract', ['use'=>2]);
        $result = $this->master->affected_rows();

        //기타 테이블 처리(있으면)

        return $result;
    }

    /**
     * 병원번호, 상품종류에 해당하는 계약존재 여부 리턴
     * biz-1193 정상인 계약만 체크
     * @param $data
     * @return bool
     */
    function checkContract($data)
    {
        //$cnt = $this->db->get_where('contract', ['hospitalId'=>$data['hospitalId'], 'adType2'=>$data['adType2']])->num_rows();
        $this->db->join('contract_order_connect coc', 'c.id=coc.contractId');
        $this->db->join('contract_order co', 'coc.contractOrderId=co.id');
        $cnt = $this->db->get_where('contract c', ['c.hospitalId'=>$data['hospitalId'], 'c.adType2'=>$data['adType2'], 'co.contractStatus'=>1])->num_rows();
        //echo $this->db->last_query(); exit;
        //수주계약 상태에 이월 종료 추가 필요함. 아니면 deposit에서 체크

        if($cnt > 0)
        {
            return false; //중복계약존재
        }

        return true; //계약생성가능
    }
}
