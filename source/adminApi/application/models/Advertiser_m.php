<?php
/**
 * Created by PhpStorm.
 * User: blumine
 * Date: 2018. 4. 2.
 * Time: AM 10:54
 */

class Advertiser_m extends CI_Model
{
    function __construct()
    {
        parent::__construct();
       
    }

    function getDashBoard($data)
    {
        $hospitalId = $this->common_m->getHospitalId($data);

        //잔여충전금
        $sql1 = "select sum(trp.`totalReady`) as readyPrice from contract c
            join `total_ready_price` trp on c.id=trp.`contractId`
            where c.`hospitalId`='".$hospitalId."' and c.adType=1
            ";
//        $sql1 = "
//            select (sum(chargePrice) - sum(usePrice)) as readyPrice -- 잔액 //잔액 로직 변경 with tee. 20190722
//            from
//            (
//                SELECT c.hospitalId,
//--                (select ifnull(sum(price), 0)  from deposit where status in(2,4) and contractId=c.id and contractOrderId=co.id) as price1, -- 계약충전
//--                (select ifnull(sum(price), 0)  from deposit where status in(3) and contractId=c.id and contractOrderId=co.id) as price2, -- db소진
//                -- (select ifnull(sum(price), 0)  from deposit where status in(4,5,8) and contractId=c.id and contractOrderId=co.id) as price3, -- 기타
//--                (select ifnull(sum(price), 0)  from deposit where status in(4,12) and contractId=c.id and contractOrderId=co.id) - (select ifnull(sum(price), 0)  from deposit where status in(5,8,11) and contractId=c.id and contractOrderId=co.id) as price3, -- 기타
//--                (select ifnull(sum(price), 0)  from deposit where status in(6,7,8) and contractId=c.id and contractOrderId=co.id) as price4 -- 소진+환불
//            (select ifnull(sum(price), 0) from deposit where status in(2,4) and contractId=c.id and contractOrderId=co.id) as chargePrice, -- 충전금액
//            (select ifnull(sum(price), 0) from deposit where status in(3,5,6,7,8) and contractId=c.id and contractOrderId=co.id) as usePrice -- 소진금액  and contractOrderId=co.id
//          --  (select ifnull(sum(price), 0)  from deposit where status in(9,10) and contractId=c.id and contractOrderId=co.id) as price5 -- 취소금액
//
//                FROM `contract` `c`
//                JOIN `contract_order_connect` `coc` ON `c`.`id`=`coc`.`contractId`
//                JOIN `contract_order` `co` ON `coc`.`contractOrderId`=`co`.`id`
//                where c.hospitalId='".$hospitalId."' and c.adType=1
//            ) q
//            group by q.hospitalID
//        ";
        $res1 = $this->db->query($sql1)->row_array();

        //진행중이벤트수
        //$res2 = $this->common_m->getEventId($data);
        //진행중인 이벤트 수
        //O 진행, O 어드민 작성중, O 수정검토, O 종료검토, O 반려
        $progressWhere = "(
            (adStatus = 2) 
            OR ( adStatus = 4 and  subAdStatus = 5)
            OR ( adStatus = 1 and subAdStatus = 1) 
            OR ( adStatus = 1 and subAdStatus = 2 )
            OR ( adStatus = 5 )
        )";
        $this->db->select('id');
        $this->db->where($progressWhere);
        $res2 = $this->db->get_where('ads', [ 'hospitalId' => $hospitalId, 'isDelete' => 'N', 'isLive' => 'Y'])->num_rows();
        

        //이벤트검토수
        $sql3 = "
            select count(*) as cnt from inspecting_ads
            where status=1 and hospitalId='".$hospitalId."' and isAdmin = 2 
        ";
        $res3 = $this->db->query($sql3)->row_array();

        //콜완료수 신청상태. 1 미확인, 2 부재중, 3 취소, 4 기타, 5 예약, 6 예약취소, 7 내원완료, 8 중복, 9 결번
        /**
         *  이번주 시작일 종료일 추가
         *  일요일 - 토요일
         * */ 
        $onDay = 86400; // 하루 
        $current_week = strtotime('this week') - $onDay - $onDay - (3600 * 9); //일요일 부터 , 9시간 더빼기
        $current_week_end = $current_week + ($onDay * 6);    

        $current_week_date = date( 'Y-m-d', $current_week).' 15:00:00';
        $current_week_end_date = date( 'Y-m-d', $current_week_end ).' 15:00:00';
        //echo $current_week_date.'</br>'.$current_week_end_date; exit;

        $sql4 = "
            select count(*) as cnt from call_request
            where status in(2,3,4,5,6,7) and hospitalId='".$hospitalId."' 
            and (regDate >= '".$current_week_date."' and regDate <= '".$current_week_end_date."')
        ";
        $res4 = $this->db->query($sql4)->row_array();
            

        //미획인수
        $sql5 = "
            select count(*) as cnt from call_request
            where status =1 and hospitalId='".$hospitalId."' 
            and (regDate >= '".$current_week_date."' and regDate <= '".$current_week_end_date."')
        ";
        $res5 = $this->db->query($sql5)->row_array();

        //병원후기갯수
        $sql6 = "
            select count(*) as cnt from board
            where type =2 and targetId='".$hospitalId."' and isDelete=0 
        ";
        $res6 = $this->db->query($sql6)->row_array();

        //평점
        $sql7 = "
            select rateSum from board_summary
            where type =2 and targetId='".$hospitalId."' 
        ";
        $res7 = $this->db->query($sql7)->row_array();

        return [
            'return1'=>round($res1['readyPrice']),
            'return2'=>round($res2),
            'return3'=>round($res3['cnt']),
            'return4'=>round($res4['cnt']),
            'return5'=>round($res5['cnt']),
            'return6'=>round($res6['cnt']),
            'return7'=>round($res7['rateSum'])
        ];
    }


    /**
     * 계약리스트 조회
     * 전체 카운트 반환
     * @param $data
     * @return array
     */
    function searchContractList($data)
    {

        $sql = "
            select id, title, hospitalId, hospitalName
            FROM `contract`
            where hospitalId = '".$data['hospitalId']."'
            order by id desc
          "; //echo $sql;

        $result = $this->db->query($sql)->result_array();

        return $result;
    }

    /**
     * 계약리스트 조회
     * 전체 카운트 반환
     * @param $data
     * @return array
     */
    function getContractList($data)
    {
        
        //모병원이 있는지 체크
        /**
         * 자병원ID로 모병원ID 가져오는 API 필요함
         */
        
        /**
         * BIZ-1247
         */
        $hospitalId = $this->common_m->getHospitalId($data);
        //$hospitalId = 23059;
        $mHospitalInfo = [];
        if (!empty($hospitalId))
        {
            $mHospitalInfo =  $this->goodocapi->getParentHospitals($hospitalId);//검색어 처리 
        }
       
        $mHospitalId =  isset($mHospitalInfo['hospitalId']) ? $mHospitalInfo['hospitalId'] : null;
        
        $where = ' having 1=1 ';

        if(!is_null($mHospitalId) )
        {
            $where0 = " where c.hospitalId='".$hospitalId."' and co.contractStatus=1 "; //병원에 해당하는 계약만
        }
        else
        {
            $where0 = " where (c.hospitalId='".$hospitalId."' or c.hospitalId='".$mHospitalId."') and co.contractStatus=1"; //자기+모병원
        }

        //3개월전
        $now_date = date("Y-m-d",strtotime("-3 month"));

        $sql = "
            select * from ( 
 
            select *
            from 
            (
            SELECT c.id contractId, coc.`contractOrderId`, co.adType2,co.agencyCompanyName,
            `c`.title, c.`hospitalId`, c.`hospitalName`, c.`agencyUserId`, c.`manageUserId`, co.`adPrice` oPrice, c.`regDate`, 
            c.payType,
            co.isNetwork, co.contractStatus
            FROM `contract` `c`
            JOIN `contract_order_connect` `coc` ON `c`.`id`=`coc`.`contractId`
            JOIN `contract_order` `co` ON `coc`.`contractOrderId`=`co`.`id`
            -- left join memo m on co.id=m.`targetId` and m.memoType=3
            ".$where0."
            ) q 
            group by q.hospitalId, q.contractId
            ) g 
            ".$where."
            
          ";

        $result0 = $this->db->query($sql)->result_array();
        $totCnt = count($result0);

        if($totCnt > 0)
        {
            if ($data['page'] == 0 && $data['limit'] == 0)
            {
                $limit = '';
            }
            else 
            {
                $limit = ' limit '.(($data['page'] - 1) * $data['limit']).', '. $data['limit'];
            }
            //1 수주, 2 계약충전, 3 소진, 4 기타충전, 5 환불수수료,
            //6 발행 환불, 7 계약 환불, 8 기타 차감, 9 발행취소, 10 계약취소,
            //11 이월소진, 12 이월충전
            $sql2 = "
            select * from ( 
 
            select *, 
            (sum(oPrice) - sum(price5)- sum(price6)) as totalOrder, 
            sum(chargePrice) as totalCharge , 
            sum(usePrice) as totalUse, (sum(chargePrice) - sum(usePrice)) as totalReady ,
            (sum(price1) - sum(price6)) as contractChargePrice -- 계약충전
            , sum(price2) as dbUsePrice -- db소진
            , (sum(price31) - sum(price32)) as etcPrice -- 환불수수료+기타충전+기타소진+이월소진+이월충전, + - 나눠서 계산함
            , (sum(price1) - sum(price4)) as taxProfit -- 세발매출 = 수주-(소진+환불)
            , ((sum(chargePrice) - sum(price5)) - sum(usePrice)) as readyPrice -- 잔액
            from 
            (
            SELECT c.id contractId, coc.`contractOrderId`, 
            -- 계약상태. 수주계약의 최종건 상태 
            (
            select contractStatus from contract_order co1 
            join contract_order_connect coc1 on co1.id=coc1.contractOrderId 
            where coc1.contractId=c.id 
            order by co1.id desc limit 1 
            ) as lastContractStatus, -- 최종수주계약의 계약상태
            (
            select pt.resultCode from contract_order co1 
            join contract_order_connect coc1 on co1.id=coc1.contractOrderId 
            join payment pt on co1.id=pt.contractOrderId 
            where coc1.contractId=c.id and co1.contractStatus=1
            order by pt.id desc limit 1 
            ) as lastPaymentStatus, -- lastContractStatus가 1일 경우(정상) 입금대기, 성공, 실패를 판단하기 위한 항목. 0021 성공, 0031 실패, 0051 입금대기
            (
            select concat(pt.fnCode1,'|', pt.result2,'|',pt.transDate) from contract_order co1 
            join contract_order_connect coc1 on co1.id=coc1.contractOrderId 
            join payment pt on co1.id=pt.contractOrderId 
            where coc1.contractId=c.id and co1.contractStatus=1
            order by pt.id desc limit 1 
            ) as lastPaymentVbankNo, -- 은행코드|계좌번호|입금기한
            -- 이벤트매핑여부 및 라이브 여부 리턴
            (select count(*) from ads where contractId=c.id and contractOrderId=co.id) as adCount,
            -- 계약상태 
            `c`.title, co.`adPrice` oPrice, c.`regDate`,
            (select ifnull(sum(price), 0) from deposit where status in(2,4) and contractId=c.id and contractOrderId=co.id) as chargePrice, -- 충전금액
            (select ifnull(sum(price), 0) from deposit where status in(3,5,6,7,8) and contractId=c.id and contractOrderId=co.id) as usePrice, -- 소진금액  and contractOrderId=co.id
            (select ifnull(sum(price), 0)  from deposit where status in(2) and contractId=c.id and contractOrderId=co.id) as price1, -- 계약충전
            (select ifnull(sum(price), 0)  from deposit where status in(3) and contractId=c.id and contractOrderId=co.id) as price2, -- db소진
            (select ifnull(sum(price), 0)  from deposit where status in(4,12) and contractId=c.id and contractOrderId=co.id) as price31, -- 기타 + 값
            (select ifnull(sum(price), 0)  from deposit where status in(5,8,11) and contractId=c.id and contractOrderId=co.id) as price32, -- 기타 - 값
            (select ifnull(sum(price), 0)  from deposit where status in(6,7,8) and contractId=c.id and contractOrderId=co.id) as price4, -- 소진+환불
            (select ifnull(sum(price), 0)  from deposit where status in(9,10) and contractId=c.id and contractOrderId=co.id) as price5, -- 취소금액
            (select ifnull(sum(price), 0)  from deposit where status in(6,7) and contractId=c.id and contractOrderId=co.id) as price6, -- 환불금액
            co.isNetwork, co.hospitalId, co.adType2, 
            c.hospitalChargeName, c.hospitalChargePhone, c.hospitalChargeEmail
            FROM `contract` `c`
            JOIN `contract_order_connect` `coc` ON `c`.`id`=`coc`.`contractId`
            JOIN `contract_order` `co` ON `coc`.`contractOrderId`=`co`.`id`
            -- left join memo m on co.id=m.`targetId` and m.memoType=3
            ".$where0."
            ) q 
            group by q.hospitalId, q.contractId
            ) g 
            ".$where."
            order by g.`regDate` desc
            ".$limit
            ;
            $result = $this->db->query($sql2)->result_array();

            $eventArr = $bannerArr = $cpmArr = [];
            //adType2에 따라 데이터 분기
            //cpm은 나중에.. 현재는 없다.
            foreach ($result as $item)
            {
                if($item['adType2'] == 1)
                {
                    //이벤트
                    $eventArr[] = $item;
                }
                else if($item['adType2'] == 2 or $item['adType2'] == 3)
                {
                    //메인배너. 이벤트메인배너
                    $bannerArr[] = $item;
                }
            }
        }
        else
        {
            $result = [];
            $eventArr = $bannerArr = $cpmArr = [];
            $totCnt = 0;
        }

        $resultArr = ['eventList'=>$eventArr, 'bannerList'=>$bannerArr, 'cpmList'=>$cpmArr, 'totCnt'=>$totCnt];

        return $resultArr;
    }

    /**
     * 수주계약 리스트
     * @param $data
     * @return mixed
     */
    function getContractOrderList($data)
    {
        $hospitalId = $this->common_m->getHospitalId($data);
        $limit = ' limit '.(($data['page'] - 1) * $data['limit']).', '. $data['limit'];

        //190116 JOY 요청 contract_order 최신순으로 변경 
        $sql2 = "
                SELECT count(*) cnt
                FROM `contract` `c`
                JOIN `contract_order_connect` `coc` ON `c`.`id`=`coc`.`contractId`
                JOIN `contract_order` `co` ON `coc`.`contractOrderId`=`co`.`id`
                where  c.hospitalId =  '".$hospitalId."'
                and c.id = '".$data['contractId']."'
            ";

        $result2 = $this->db->query($sql2)->row_array();

        $sql3 = "
                SELECT coc.`contractOrderId`, co.contractDate, co.contractStatus, co.contractType, `c`.title, co.adType, co.adType2,
                (select count(*) from ads where contractId=c.id) as eventCount,
                (select ROUND(ifnull(sum(price), 0))  from deposit where status in(2) and contractId=c.id and contractOrderId=co.id) as contractChargePrice,    
                co.`adPrice` oPrice, co.`regDate`,  co.depositDate, co.taxIssueDate
                FROM `contract` `c`
                JOIN `contract_order_connect` `coc` ON `c`.`id`=`coc`.`contractId`
                JOIN `contract_order` `co` ON `coc`.`contractOrderId`=`co`.`id`
                where  c.hospitalId =  '".$hospitalId."'
                and c.id = '".$data['contractId']."'
                -- group by c.id
                -- order by c.regDate desc
                order by co.regDate desc
            ". $limit;

        $result3 = $this->db->query($sql3)->result_array();

        $result = ['list'=>$result3, 'totCnt'=>$result2['cnt']];

        return $result;
    }

    /**
     * 수주계약 상세 조회
     * @param $data
     * @return array
     */
    function getContractInfo($data)
    {
        $this->db->select('c.id, c.contractDate, co.title, c.adType, c.adType2, c.mainContract, c.regDate');
        $this->db->select('c.modDate, c.use, c.isDelete, co.agencyUserId, co.manageUserId, co.agencyCompanyId');
        $this->db->select('co.agencyCompanyName, co.hospitalType, co.hospitalId, co.hospitalName, co.hospitalChargeName');
        $this->db->select('co.hospitalChargePhone, co.hospitalChargeEmail, co.taxChargeName');
        $this->db->select('co.taxBusinessNo, co.taxChargeEmail, co.taxIssueRequestDate, co.taxIssueDate');
        $this->db->select('co.contractDate as contractOrderDate, co.contractType, co.adPrice, co.depositDate, pt.resultCode paymentStatus');
        $this->db->select("(select group_concat(distinct id,'|', adTitle separator '>>>') from ads where contractId=c.id) adsId", true);
        $this->db->join('contract_order_connect coc', 'c.id=coc.contractId');
        $this->db->join('contract_order co', 'coc.contractOrderId=co.id');
        $this->db->join('payment pt', 'co.id=pt.contractOrderId', 'left');
        $this->db->group_by('c.id');
        $result = $this->db->get_where('contract c', ['co.id'=>$data['contractOrderId']])->row_array();
        //echo $this->db->last_query();
      
        /**
         * 19013 f_hospital 제거
         */
        if (isset($result['hospitalId']) && !empty($result['hospitalId']))
        {   
            $apiParam = ['searchType' => 4,'searchValue'  => $result['hospitalId']];
         
            $hospitalInfo = $this->goodocapi->listHospitals($apiParam);

            if (isset($hospitalInfo['hospitals'][0]['networkType']))
            {
                $networkType = $hospitalInfo['hospitals'][0]['networkType'];
                $result['hospitalNetwork'] =  $networkType == 2 ? "네트워크자병원" : ($networkType == 1 ? "네트워크모병원" : "일반병원");
            } 
            else 
            {
                $result['hospitalNetwork'] = "일반병원";
            }
        }
        else 
        {
            $result = [];
        } 
        //dd($result);
        return $result;
    }

    /**
     * 계약원장 리스트 조회
     * @param $data
     * @return array
     */
    function getDepositList($data)
    {
        
        $sql = "select * from
            (
                (
                select dt.id, status, isMinus, contractId, contractOrderId, userId, m.memo, price, dt.regDate, dt.modDate from deposit dt 
                left join  memo m on m.targetId2=dt.id and m.memoType=3
                where dt.contractOrderId='".$data['contractOrderId']."' and status != '13'  group by dt.id
                -- where m.`targetId`='".$data['contractOrderId']."' and memoType=3
                )
                union all
                (
                select id, t1, t2, t3, targetId, userId, memo, memoType, regDate, customerMemo from  memo m 
                where m.`targetId`='".$data['contractOrderId']."' and memoType=3 and (m.targetId2 is null or m.targetId2 = 0)
                )

            )subs
            order by regDate  
        ";
        $result = $this->db->query($sql)->result_array(); //var_dump($result);

        $stat = '';
        $statCnt = $i = 0;
        $statArr = [];
        foreach ($result as $item)
        {
            //if($item['status'] == 3)
            //{
            //    $stat += $item['price'];
            //    $statCnt++;
            //}
            //else
            //{
                $statArr[$i] = $item;
                $i++;
            //}
        }

        //합산한 소진 row 생성
        /*
        $statArr[$i] = [
            'id' => '9999999',
            'status' => 3,
            'isMinus' => 1,
            'contractId' => $data['contractId'],
            'contractOrderId' => $data['contractOrderId'],
            'userId' => '',
            'memo' => '',
            'price' => $stat,
            'regDate' => '',
            'modDate' => ''
        ];
        */
        //$arr = ['contractId'=>$data['contractId'], 'contractOrderId'=>$data['contractOrderId']];
        //$this->db->select('deposit.*, m2.memo as memos');
        //$this->db->join('memo m2', 'deposit.id=m2.targetId2 and deposit.contractOrderId=m2.targetId', 'left');
        //$this->db->join('memo m2', 'deposit.contractOrderId=m2.targetId', 'left');
        //$this->db->group_by('deposit.id');
        //$this->db->where('m2.memoType', 3);
        //$result = $this->db->get_where('deposit', $arr)->result_array();
        //echo $this->db->last_query(); exit;
    
        return  $statArr;
    }

    /**
     * 계약정보 업데이트
     * @param $data
     * @return int
     */
    function updateContractInfo($data)
    {
  
      $data2 = [
            'payType' => $data['payType'],
            'modDate' => date("Y-m-d H:i:s")
        ];

        $this->master->where('id', $data['contractId']);
        $this->master->update('contract', $data2);
        $result = $this->master->affected_rows();

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
     * 수주계약에 해당하는 원장에서 기타충전이 있으면 금액 리턴.
     * 기타 차감이 있으면 그 금액 차감하고 그 금액이 마이너스이면 0으로 리턴
     * @param $contractOrderId
     * @return int or bool
     */
    function checkServiceCharge($contractOrderId)
    {
        //기타 충전금액
        $this->master->select_sum('price');
        $result = $this->master->get_where('deposit', ['contractOrderId'=>$contractOrderId, 'status'=>4])->row_array();

        //기타 차감금액
        $this->master->select_sum('price');
        $result2 = $this->master->get_where('deposit', ['contractOrderId'=>$contractOrderId, 'status'=>8])->row_array();

        if($result['price'] != '')
        {
            if($result2['price'] != '')
            {
                $dd = $result2['price'];
            }
            else
            {
                $dd = 0;
            }

            //기타충전 - 기타소진
            $cc = $result['price'] - $dd;

            if($cc > 0)
            {
                //0보다 크면
                return $cc;
            }
            else
            {
                return 0;
            }
        }
        else
        {
            return false;
        }
    }

    /**
     * 수주관리 (계약을 건드리지 않는다)
     * @param $data
     * @return bool
     */
    function depositConfirm($data)
    {
        
        //$oriData = $this->getContractInfo($data);
        if(in_array($data['type'], [3,5,6,7,8,9,10,11]))
        {
            $minus = 1;
        }
        else
        {
            $minus = 0;
        }

        $regDate = date("Y-m-d H:i:s");

        //환불수수료 차감을 사용하면
        if(in_array($data['type'], [6,7]) and $data['checkRefundFee'] == '1')
        {
            //환불수수료 원장 입력
            $arr = array(
                'status'=>5, //1 수주, 2 계약충전, 3 소진, 4 기타충전, 5 환불수수료, 6 발행 환불, 7 계약 환불, 8 기타소진, 9 발행취소, 10 계약취소, 11 이월 소진, 12 이월 충전
                'isMinus' => 1, //양수 0, 음수 1. 고정
                'contractId'=>$data['contractId'],
                'contractOrderId'=>$data['contractOrderId'],
                'usersId' => $data['users_id'],
                'price'=>'300000', //환불수수료 30만원 고정값
                'regDate'=>$regDate,
                'modDate'=>$regDate
            );
            $this->master->insert('deposit', $arr);

            $data['chargePrice'] = $data['chargePrice'] - 300000; //금액에서 환불수수료 뺀다.
        }

        //원장 입력
        $arr = array(
            'status'=>$data['type'], //1 수주, 2 계약충전, 3 소진, 4 기타충전, 5 환불수수료, 6 발행 환불, 7 계약 환불, 8 기타 소진, 9 발행취소, 10 계약취소, 11 이월 소진, 12 이월 충전
            'isMinus' => $minus, //양수 0, 음수 1
            'contractId'=>$data['contractId'],
            'contractOrderId'=>$data['contractOrderId'],
            'usersId' => $data['users_id'],
            'price'=>$data['chargePrice'],
            'regDate'=>$regDate,
            'modDate'=>$regDate
        );
        $this->master->insert('deposit', $arr);
        $result= $this->master->insert_id();

        //세금계산서 관련 값일 경우 수주계약 업데이트
        if(in_array($data['type'], [6,7,9,10]))
        {
            switch ($data['type'])
            {
                case 6:
                    $arr = ['contractStatus'=>2];
                    break;
                case 7:
                    $arr = ['contractStatus'=>5];
                    break;
                case 9:
                    $arr = ['contractStatus'=>3];
                    break;
                case 10:
                    $arr = ['contractStatus'=>4];
                    break;
            }
            $this->db->where('id', $data['contractOrderId']);
            $this->db->update('contract_order', $arr);
        }

        //memo
        if(@$data['memo'])
        {
            $arr2 = [
                'memoType'=>3, //원장메모(운영자)
                'targetId2'=>$result, //원장번호
                'targetId'=>$data['contractOrderId'],
                'userId'=>$data['users_id'],
                'memo'=>$data['memo']
            ];
            $this->common_m->setContractOrderMemo($arr2);
        }

        //발행, 계약 환불. 기타 충전, 기타 소진 시 시점잔액 업데이트
        //계약충전시는 contractOrder_m 에서 호
        if(in_array($data['type'], [6,7,4,8]))
        {
            //시점잔액 업데이트
            $rPrice = $this->common_m->getBalancePrice(['contractId'=>$data['contractId']]);
            $data3 = [
                'contractId'=>$data['contractId'],
                'totalReady'=>$rPrice,
                'type'=>1
            ];
            $this->common_m->updateTotalInfo($data3);
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
     * 수주관리 관리항목 판단을 위한 기준 데이터 조회
     * @param $data
     * @return mixed
     */
    function getContractStatus($data)
    {
        //세금계산서 발행여부, 입금 여부, 현재 잔액(환불가능금액)
        $sql = "
            select id, tax, balance, if(contractStatus = 1 and charge > 0 and charge2 != 0, '부분입금', deposit2) as deposit, contractStatus, charge, charge1, charge2, charge3 from 
            (
            select id, 
            if(taxIssueDate is null, '미발행', '발행') as tax,
            if(contractStatus= 1 and `depositDate` = '0000-00-00 00:00:00', '미입금', if(contractStatus= 1 and `depositDate` is null, '미입금', '입금')) as deposit2,

            case contractStatus
            	when 1 then ifnull((select sum(price) from deposit where status in(2,4)  and contractOrderId=contract_order.id),0) - ifnull((select sum(price) from deposit where status in(3,5,8) and contractOrderId=contract_order.id),0) -- (계약충전+기타충전) - (소진+기타 차감+환불수수료)
            	else 0
			end as 			balance ,
			case contractStatus
            	when 1 then ifnull((select sum(price) from deposit where status in(1)  and contractOrderId=contract_order.id),0) - ifnull((select sum(price) from deposit where status in(2) and contractOrderId=contract_order.id),0) -- (수주) - (계약충전)
            	else 0
			end as 			charge ,
			ifnull((select sum(price) from deposit where status in(2,4)  and contractOrderId=contract_order.id),0) as charge1,
			ifnull((select sum(price) from deposit where status in(2) and contractOrderId=contract_order.id), 0) as charge2,
			ifnull((select sum(price) from deposit where status in(1) and contractOrderId=contract_order.id), 0) as charge3,
			contractStatus
            from contract_order  
            
            where id='".$data['contractOrderId']."'
            ) alis
        ";
        $result = $this->db->query($sql)->row_array();

        return $result;
    }

    /** ---------------------------------------------------------------- */

    /**
     * 계약 등록. 트랜잭션은 나중에....
     * @param $data
     * @return int
     */
    function setContractInfo($data)
    {
        unset($data['menu_id']);
        unset($data['token']);
        unset($data['users_id']);
        if ( isset($data['refreshToken']) )
        {
            unset($data['refreshToken']);
        }
        if($data['contractId'])
        {
            $cntId = $data['contractId'];
            unset($data['contractId']);
        }

        $data['regDate'] = date("Y-m-d H:i:s");

        //수주 테이블 입력
        $this->master->insert('contract_order', $data);
        $oIds = $this->master->insert_id();

        if($data['contractType'] == '1')
        {
            //신규일 경우
            //성공이면 계약테이블 수주번호와 나머지 데이터 입력
            $this->master->insert('contract', $data);
            $cIds = $this->master->insert_id();
        }
        else
        {
            //재계약일 경우
            $cIds = $cntId;
        }

        //매핑테이블 입력
        $this->master->insert('contract_order_connect', array('contractId'=>$cIds, 'contractOrderId'=>$oIds));
        $cocIds = $this->master->insert_id();

        return $cocIds;
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
     * 수주번호로 계약번호 구하기
     * @param $data
     * @return mixed
     */
    function getContractId($data)
    {
        $result = $this->db->get_where('contract_order_connect', array('contractOrderId'=>$data['contractId']))->row_array();

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
        if($data['memo'])
        {
            $iArr = array(
                'memoType'=>$data['memoType'],
                'memo'=>$data['memo'],
                'userId'=>$data['users_id'],
                'targetId'=>$data['contractOrderId'],
                'regDate'=>date("Y-m-d H:i:s")
            );
            $this->master->insert('memo', $iArr);
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
     * 검수 승인요청, 종료요청 개별 처리
     * @param $data
     * @return int
     */
    function requestInspect($data)
    {
        
        $orgInfo = $this->ads_m->getAds($data['adsId']);

        $date = date("Y-m-d H:i:s");

        if($data['type'] == 1)
        {
            $bbArr = [
                'adTitle' => $orgInfo['adTitle'],
                'adsId' => $data['adsId'],
                'hospitalId' => $orgInfo['hospitalId'],
                'agencyUserId' => $orgInfo['agencyUserId'],
                'regDate' => $date
            ];

            $this->master->insert('ads_main', $bbArr);
            $adsMainId = $this->master->insert_id();

            //ads_main_map insert  메인노출 히스토리 테이블
            $abbArr = [
                'adsMainId' => $adsMainId,
                'adsId' => $orgInfo['adsId'],
                'isMain' => 2, //검수전이라 메인미노출
                'isInspect' => 2, //미검수
                'regDate' => $date
            ];

            $this->master->insert('ads_main_map', $abbArr);
            $adsMainMapId = $this->master->insert_id();

            //승인이면 검수타입 . 1 수정검토(O), 2 종료검토(O), 3 수정검토(X), 4 신규등록검토(X), 5 재등록(X)
            $insArr = [
                'status' => 1, //검수대기
                'adStatus' => 4, //x 신규등록검토
                'regDate' => $date,
                'hospitalId' => $orgInfo['hospitalId'],
                'historyId' => $data['historyId'],
                'adsId' => $data['adsId'],
                'userId' => $data['users_id'],
                'agencyUserId' => $orgInfo['agencyUserId'],
                'adsMainMapId' => $adsMainMapId,
                'isAdmin' => 2
            ];
        }
        else if($data['type'] == 2)
        {
            //종료이면 검수타입 . 1 수정검토(O), 2 종료검토(O), 3 수정검토(X), 4 신규등록검토(X), 5 재등록(X)
            $insArr = [
                'status' => 1, //검수대기
                'adStatus' => 2, //0 종료검토
                'regDate' => $date,
                'hospitalId' => $orgInfo['hospitalId'],
                'historyId' => '',
                'adsId' => $data['adsId'],
                'userId' => $data['users_id'],
                'agencyUserId' => $orgInfo['agencyUserId'],
                'adsMainMapId' => $data['adsMainMapId'],
                'isAdmin' => 2
            ];
        }

        $this->master->insert('inspecting_ads', $insArr);
        $result = $this->master->insert_id();

        return $result;
    }

    /** -------------------------------------  */
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


}
