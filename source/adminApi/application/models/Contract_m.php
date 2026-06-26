<?php
/**
 * Created by PhpStorm.
 * User: blumine
 * Date: 2018. 4. 2.
 * Time: AM 10:54
 */

class Contract_m extends CI_Model
{
    function __construct()
    {
        parent::__construct();
        $this->load->model('common_m');
    }

    /**
     * 계약리스트 조회
     * 입금확인되고 상태가 진행중인 것만
     * 전체 카운트 반환
     * @param $data
     * @return array
     */
    function searchContractList($data)
    {
        /*
        $sql = "
            select id, title, hospitalId, hospitalName
            FROM `contract`
            where hospitalId = '".$data['hospitalId']."'
            order by id desc
          "; //echo $sql;
        */
        //181210 contractOrderId 가져오게 수정. 틀리면 원복..
//        $sql = "
//            SELECT
//                c.id, c.title, c.hospitalId, c.hospitalName, co.id AS contractOrderId, co.adType2, c.id as contractId
//            FROM
//                `contract` AS c
//            LEFT JOIN contract_order_connect AS coc ON c.id = coc.contractId
//            LEFT JOIN contract_order AS co ON co.id = coc.contractOrderId
//            WHERE
//                c.hospitalId = '".$data['hospitalId']."'
//                and co.contractStatus = 1
//                and (co.depositDate is not null or co.depositDate != '')
//            ORDER by c.id DESC;
//        ";

        // overCharge2는 현재 입금되지 않아도 이월충전 내역이 있으면 유효한 계약인지 판단하기 위한 값이다.
        // reContractAble = 1 이면 입금이 안되더라도 무조건 유효한 계약 가능
        /**
         * BIZ-965
         * "id","parent","child","sort"
         * "54","217782","23105","2"
         * "55","217782","24206","3"
         * "63","23049","37088","0"
         * "30","24486","40067","3"
         * "29","24486","40821","2"
         * "28","41146","41147","17"
         * "27","41146","42191","16"
         * "26","41146","42786","15"
         * "25","41146","45978","14"
         * "24","41146","45979","13"
         * "23","45513","48015","3"
         * "22","41146","48104","12"
         * "21","41146","48774","11"
         * "20","41146","49401","10"
         * "57","137715","49520","4"
         * "56","137715","49535","3"
         * "19","24486","49730","1"
         * "18","45513","50910","2"
         * "16","45513","52926","1"
         * "15","41146","53005","9"
         */
        //$data['hospitalId'] = 41146; //모
        //$data['hospitalId'] = 48774; //자
       
        $CI = & $this;
        
        //모
        $parentFunc = function ($hospitalId) use ($CI)
        {
            $parentHospital = $CI->goodocapi->getParentHospitals($hospitalId);
            $parentId = isset($parentHospital['hospitalId']) ? (int) $parentHospital['hospitalId'] : null;
            return $parentId;
        };
        
        //자
        $childFunc = function ($hospitalId) use ($CI)
        {
            $return_arr = [];
            
            $childHospitals = $CI->goodocapi->getChildHospitals($hospitalId);    
            if ((int) $childHospitals['totalCount'] > 0)
            {
                foreach($childHospitals['hospitals'] as $key => $val)
                {
                    $return_arr[]   = (int) $val['id'];
                }
            }
            return $return_arr;
        };

        if (!is_null($data['hospitalId']) &&  !empty($data['hospitalId']) ) {
            $hospitalId = (int) $data['hospitalId'];
        }    
        else {
            $hospitalId = null;
        }

        $hospitalType = null;
        if (!is_null($hospitalId)) {
            $hospitalInfo = $this->goodocapi->getHospitalInfosByIds([$hospitalId]);
            
            $hospitalType = isset($hospitalInfo[$hospitalId]['networkType']) ? $hospitalInfo[$hospitalId]['networkType'] : null;
            unset($hospitalInfo);
        }
      
        $whereHospitalId = [];

        if ($hospitalType === 1) //모
        {
            $whereHospitalId   = $childFunc($hospitalId);
        } 
        else if ($hospitalType === 2) //자
        {
            //모 도 뽑고 자도 뽑는다
            $parentId = $parentFunc($hospitalId);
            if (!is_null($parentId))
            {
                $whereHospitalId = $childFunc($parentId);
                $whereHospitalId[] = $parentId;
            }
        }   
        unset($parentFunc, $childFunc);

        //병원 ID 널일 때는 검색 안하게 변경
        if (!is_null($hospitalId))
        {
            $whereHospitalId[] = $hospitalId;
        }

        foreach($whereHospitalId as $key => $val)
        {
            if (is_null($val)) {
                unset($whereHospitalId[$key]);
            }         
        }
        
        $whereQuery = " c.id is not null ";
        if(sizeof($whereHospitalId))
        {
            $whereQuery =  " c.hospitalId IN (".implode(',', $whereHospitalId).") ";
        }
 
        $sql = "
            select * , if(depositDate is not null, 1, if(depositDate is null and overCharge2 > 0, 1, 0)) as validContract
            from (
            SELECT 
                c.id, c.title, c.hospitalId, c.hospitalName, co.id AS contractOrderId, co.adType2, co.depositDate, co.parentId, 
                (select count(*) from deposit where contractOrderId=co.id and status=12) as overCharge2, co.isNetwork
            FROM 
                `contract` AS c 
            LEFT JOIN contract_order_connect AS coc ON c.id = coc.contractId
            LEFT JOIN contract_order AS co ON co.id = coc.contractOrderId
            WHERE 
                ".$whereQuery."
                and co.contractStatus = 1 
            ORDER by c.id DESC
            ) hhh
        ";

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
        //검색어 처리
        $where = ' having 1=1 ';
        $where0 = ' where 1=1 ';

        if(@$data['agencyUserId'] != '')
        {
            $where0 .= ' and c.agencyUserId = "'.$data['agencyUserId'].'"';
        }

        if(@$data['manageUserId'] != '')
        {
            $where0 .= ' and c.manageUserId = "'.$data['manageUserId'].'"';
        }

// 성능 이슈로 오픈후 cron 으로 돌려서 처리
//        if(@$data['advertiserStatus'] != '')
//        {
//            //광고주(계약)상태. 0 대기, 1 진행, 2 휴면, 3 중지, 4 이탈
//            //진행 : 진행중인 이벤트가 있는 광고주
//            //휴면 : 잔액이 0원이고 이벤트가 모두 종료된 광고주
//            //중지 : 잔액은 있거나 -마이너스 상태에서 이벤트가 기간 상관없이 모두 종료된 광고주
//            //이탈 : 휴면상태에서 3개월간 이벤트 비라이브와 미충전 상태인 광고주
//            //계약별 상태로 변경됨
//            switch ($data['advertiserStatus'])
//            {
//                case (0):
//                    $where .= " and g.hStatus like '%대기%'";
//                    break;
//                case (1):
//                    $where .= " and g.hStatus like '%진행%'";
//                    break;
//                case (2):
//                    $where .= " and g.hStatus like '%휴면%'";
//                    break;
//                case (3):
//                    $where .= " and g.hStatus like '%정지%'";
//                    break;
//                case (4):
//                    $where .= " and g.hStatus like '%이탈%'";
//                    break;
//            }
//
//        }

        if(@$data['depositStatus'] != '')
        {
            $where .= " and g.depositStatusGroup like '%".$data['depositStatus']."%'";
        }

//    case (0):
//                    $where .= " and g.advertiserStatus regexp '^(0|0,)+$' ";
//                    break;
//                case (1):
//                    $where .= " and g.advertiserStatus regexp '[1]' ";
//                    break;
//                case (2):
//                    $where .= " and g.advertiserStatus regexp '^(2|2,)+$' ";
//                    break;
//                case (3):
//                    $where .= " and g.advertiserStatus regexp '^(3|3,)+$' ";
//                    break;
//                case (4):
//                    $where .= " and g.advertiserStatus regexp '^(4|4,)+$' ";
//                    break;
//                case (5):
//                    $where .= " and g.advertiserStatus regexp '([23][^4][^1])+$' ";
//                    break;

        if(@$data['balanceStatus'] != '')
        {
            //잔액유형. 1 마이너스, 2 0원, 3 50만원이하, 4 100만원이하, 5 300만원 이하, 6 300만원 이상

            $this->db->where('co.taxIssueDate is not null');
            switch ($data['balanceStatus'])
            {
                case (1):
                    $where .= ' and g.totalReady < 0 ';
                    break;
                case (2):
                    $where .= ' and g.totalReady = 0 ';
                    break;
                case (3):
                    $where .= ' and (g.totalReady > 0 and g.totalReady <= 500000 ) ';
                    break;
                case (4):
                    $where .= ' and (g.totalReady > 500000 and g.totalReady <= 1000000 ) ';
                    break;
                case (5):
                    $where .= ' and (g.totalReady > 1000000 and g.totalReady <= 3000000 ) ';
                    break;
                case (6):
                    $where .= ' and g.totalReady > 3000000 ';
                    break;
            }

        }

        if(@$data['adType2'] != '')
        {
            //상품종류
            $where .= ' and g.adType2 = "'.$data['adType2'].'"';
        }

        if(@$data['searchType'] != '')
        {
            switch ($data['searchType'])
            {
                case "1": //병원명
                    $where .= ' and g.hospitalName like "%'. $data['searchWord'].'%"';
                    break;
                case "2": //계약명
                    $where .= ' and g.title like "%'. $data['searchWord'].'%"';
                    break;
                case "3": //수주id
                    $where .= ' and FIND_IN_SET('. $data['searchWord'].',  g.contractOrderIds )';
                    //$where .= ' and g.contractOrderId = "'. $data['searchWord'].'"';
                    break;
                case "4": //대행사명
                    $where .= ' and g.agencyCompanyName like "%'. $data['searchWord'].'%"';
                    break;
                case "5": //메모
                    $where .= ' and g.memo2 like "%'. $data['searchWord'].'%"';
                    break;
                case "6": //병원번호
                    $where .= ' and g.hospitalId = "'. $data['searchWord'].'"';
                    break;
            }
        }

        //3개월전
        $now_date = date("Y-m-d",strtotime("-3 month"));

        //갯수 산정을 위한 쿼리
        $sql = "
            select * from ( 
 
            select *  
            from 
            (
            SELECT c.id, coc.`contractOrderId`, co.adType2,co.agencyCompanyName,
            c.payType, `c`.title, c.`hospitalId`, c.`hospitalName`, c.`agencyUserId`, c.`manageUserId`, co.`adPrice` oPrice, c.`regDate`,
            co.isNetwork, co.contractStatus,
            (select group_concat(contractOrderId SEPARATOR ',') from deposit where contractId=c.id) as contractOrderIds, -- 프로그램에서 처리
            
            (select group_concat(memo) from memo where targetId=co.id and memoType in(1,3)) as memo2,
            (select group_concat(contractStatus) from contract_order where id=co.id)  as contractStatusGroup -- 계약활동분류검색용
            -- 계약상태 
            FROM `contract` `c`
            JOIN `contract_order_connect` `coc` ON `c`.`id`=`coc`.`contractId`
            JOIN `contract_order` `co` ON `coc`.`contractOrderId`=`co`.`id`
            -- left join memo m on co.id=m.`targetId` and m.memoType=3
            ".$where0."
            ) q 
            group by q.hospitalID, q.id -- , q.contractOrderId
            ) g 
            ".$where."
            
          "; //echo $sql; exit;

        $result0 = $this->db->query($sql)->result_array();     
        
        $totCnt = count($result0);
        //echo $this->db->last_query(); exit;

        if($totCnt > 0)
        {
            $limit = ' limit '.(($data['page'] - 1) * $data['limit']).', '. $data['limit'];

            //환불금액(price6)은 메출과 충전금액에서 빼준다.
            //취소금액을 (price5)을 세발매출에서만 빼준다.  2019.1.16 확정. rock, tee, martin
            $sql2 = "
            select * from ( 
 
            select *, group_concat(hStatus) as advertiserStatus,  sum(progress0), sum(progress1), sum(progress2), 
            (sum(oPrice) - sum(price5) - sum(price6)) as totalOrder, 
            sum(chargePrice) as totalCharge , 
            ((sum(chargePrice) - sum(price5)) - sum(usePrice)) as readyPrice, 
            (sum(chargePrice) - sum(usePrice)) as totalReady ,
            (sum(price1) - sum(price6)) as contractChargePrice -- 계약충전, 계약환불, 발행환불 금액 빼준
            , sum(price2) as dbUsePrice -- db소진
            , (sum(price31) - sum(price32)) as etcPrice -- 환불수수료+기타충전+기타소진+이월소진+이월충전, + - 나눠서 계산함  
            , (sum(price1) - sum(price4)) as taxProfit -- 세발매출 = 수주-(소진+환불)
            -- , (sum(price1) - sum(price5) - sum(price31) - sum(price32) - sum(price6)) as readyPrice -- 잔액
            from 
            (
            SELECT c.id, coc.`contractOrderId`, co.adType2, co.agencyCompanyName,
            -- 계약상태 
            (select count(*) from ads where contractId=c.id) as progress0,
            (select count(*) from ads where contractId=c.id and ads.isLive='Y') as progress1,
            (select count(*) from ads where contractId=c.id and ads.isLive='N') as progress2,
            if((select count(*) from ads where contractId=c.id) = 0, '대기',
            if((select count(*) from ads where contractId=c.id and ads.isLive='Y') > 0, '진행', 
                if(
                	(select count(*) from ads where contractId=c.id and ads.isLive='Y' and modDate > '" . $now_date . "') = 0 
                	and 
                	(select count(*) from ads where contractId=c.id and modDate > '" . $now_date . "') = (select count(*) from ads where contractId=c.id and ads.isLive='N' and modDate > '" . $now_date . "' 
                        and 
                        (
                        ifnull((select sum(price) from deposit where status in(2,4) and contractId=c.id and contractOrderId=co.id),0) 
                        - 
                        ifnull((select sum(price) from deposit where status in(3,5,6,7,8) and contractId=c.id and contractOrderId=co.id),0)
                	) <= 0
                )
                	, '휴면', 
                    if(
                    	(select count(*) from ads where contractId=c.id and ads.isLive='Y') = 0 
                    	and 
                    	(select count(*) from ads where contractId=c.id)=(select count(*) from ads where contractId=c.id and ads.isLive='N' 
                            and 
                            (
                            ifnull((select sum(price) from deposit where status in(2,4) and contractId=c.id and contractOrderId=co.id),0)
                            - 
                            ifnull((select sum(price) from deposit where  status in(3,5,6,7,8) and contractId=c.id and contractOrderId=co.id),0)
                    	) > 0
                    )
                    	, '중지', '이탈'
                    ) 
                )	
            )
            ) as hStatus,
            -- 계약상태 
            `c`.title, c.`hospitalId`, c.`hospitalName`, c.`agencyUserId`, c.`manageUserId`, co.`adPrice` oPrice, c.`regDate`,
            -- m.memo as depositMemo,
            (select group_concat(contractOrderId SEPARATOR ',') from deposit where contractId=c.id) as contractOrderIds,
            (select group_concat(memo) from memo where targetId=co.id and memoType in(1)) as memo,
            (select group_concat(memo) from memo where targetId=co.id and memoType in(1,3)) as memo2, 
            (select group_concat(contractStatus) from contract_order where id=co.id)  as contractStatusGroup, -- 계약활동분류검색용
            (select ifnull(sum(price), 0) from deposit where status in(2,4) and contractId=c.id and contractOrderId=co.id) as chargePrice, -- 충전금액
            (select ifnull(sum(price), 0) from deposit where status in(3,5,6,7,8) and contractId=c.id and contractOrderId=co.id) as usePrice, -- 소진금액 
            (select ifnull(sum(price), 0)  from deposit where status in(2) and contractId=c.id and contractOrderId=co.id) as price1, -- 계약충전
            (select ifnull(sum(price), 0)  from deposit where status in(3 )and contractId=c.id and contractOrderId=co.id) as price2, -- db소진
            (select ifnull(sum(price), 0)  from deposit where status in(4,12) and contractId=c.id and contractOrderId=co.id) as price31, -- 기타 + 값
            (select ifnull(sum(price), 0)  from deposit where status in(5,8,11) and contractId=c.id and contractOrderId=co.id) as price32, -- 기타 - 값
            (select ifnull(sum(price), 0)  from deposit where status in(6,7,8) and contractId=c.id and contractOrderId=co.id) as price4, -- 소진+환불
            (select ifnull(sum(price), 0)  from deposit where status in(9,10) and contractId=c.id and contractOrderId=co.id) as price5, -- 취소금액
            (select ifnull(sum(price), 0)  from deposit where status in(6,7) and contractId=c.id and contractOrderId=co.id) as price6, -- 환불금액
            c.payType,
            co.isNetwork, co.contractStatus
            FROM `contract` `c`
            JOIN `contract_order_connect` `coc` ON `c`.`id`=`coc`.`contractId`
            JOIN `contract_order` `co` ON `coc`.`contractOrderId`=`co`.`id`
            -- left join memo m on co.id=m.`targetId` and m.memoType=3
            ".$where0."
            ) q 
            group by q.hospitalID, q.id -- , q.contractOrderId
            ) g 
            ".$where."
            order by g.`regDate` desc
            ".$limit."
            ";
            //echo $sql2; exit;

            $result = $this->db->query($sql2)->result_array();

            $i=0;
            //계약건 가져오기
            foreach ($result as $item)
            {
                //취소(9 10)금액을 세발매출에서 뺄지는 추후 작업
                $sql3 = "
                    SELECT c.id,  coc.`contractOrderId`, co.adType, co.adType2,
                    -- 병원상태 
                    (select count(*) from ads where contractId=c.id) as progress0,
                    (select count(*) from ads where contractId=c.id and isLive='Y') as progress1,
                    (select count(*) from ads where contractId=c.id and isLive='N') as progress2,
                    if((select count(*) from ads where contractId=c.id) = 0, '대기',
                    if((select count(*) from ads where contractId=c.id and isLive='Y') > 0, '진행', 
                        if((select count(*) from ads where contractId=c.id and isLive='Y' and modDate > '".$now_date."') = 0 and (select count(*) from ads where contractId=c.id and modDate > '".$now_date."')=(select count(*) from ads where contractId=c.id and isLive='N' and modDate > '".$now_date."' and (ifnull((select sum(price) from deposit where status in(2,4) and contractId=c.id and contractOrderId=co.id),0) - ifnull((select sum(price) from deposit where status in(3,5,6,7,8) and contractId=c.id and contractOrderId=co.id),0)) > 0), '휴면', 
                            if((select count(*) from ads where contractId=c.id and isLive='Y' and modDate > '".$now_date."') = 0 and (select count(*) from ads where contractId=c.id and modDate > '".$now_date."')=(select count(*) from ads where contractId=c.id and isLive='N' and modDate > '".$now_date."' and (ifnull((select sum(price) from deposit where status in(2,4) and contractId=c.id and contractOrderId=co.id),0) - ifnull((select sum(price) from deposit where  status in(3,5,6,7,8) and contractId=c.id and contractOrderId=co.id ), 0)) != 0), '중지', '이탈'
                            ) 
                        )	
                    )
                    ) as hStatus,
                    -- 병원상태 
                    `c`.title, c.`hospitalId`, c.`hospitalName`, c.`agencyUserId`, c.`manageUserId`, 
                    co.agencyCompanyName, co.agencyCompanyFeeRate, 
                    co.adPrice - (select ifnull(sum(price), 0)  from deposit where status in(6,7) and contractId=c.id and contractOrderId=co.id) - (select ifnull(sum(price), 0)  from deposit where status in(9,10) and contractId=c.id and contractOrderId=co.id) oPrice, 
                    co.`regDate`,
                    (select ifnull(sum(price), 0)  from deposit where status in(2,4) and contractId=c.id and contractOrderId=co.id) as chargePrice, -- 충전금액
                    (select ifnull(sum(price), 0)  from deposit where status in(3,5,6,7,8) and contractId=c.id and contractOrderId=co.id) as usePrice, -- 소진금액
                    (select ifnull(sum(price), 0)  from deposit where status in(2) and contractId=c.id and contractOrderId=co.id) - (select ifnull(sum(price), 0)  from deposit where status in(6,7) and contractId=c.id and contractOrderId=co.id) as contractChargePrice, -- 계약충전
                    (select ifnull(sum(price), 0)  from deposit where status in(3) and contractId=c.id and contractOrderId=co.id) as dbUsePrice, -- db소진
                    -- (select ifnull(sum(price), 0)  from deposit where status in(4,5,8) and contractId=c.id and contractOrderId=co.id) as etcPrice, -- 기타
                    (select ifnull(sum(price), 0)  from deposit where status in(4,12) and contractId=c.id and contractOrderId=co.id) - (select ifnull(sum(price), 0)  from deposit where status in(5,8,11) and contractId=c.id and contractOrderId=co.id)  as etcPrice, -- 기타
                    (select ifnull(sum(price), 0)  from deposit where status in(6,7,9) and contractId=c.id and contractOrderId=co.id) as calPrice -- 소진+환불
                     
                    FROM `contract` `c`
                    JOIN `contract_order_connect` `coc` ON `c`.`id`=`coc`.`contractId`
                    JOIN `contract_order` `co` ON `coc`.`contractOrderId`=`co`.`id`
                    where 1=1 -- c.hospitalId =  '".$item['hospitalId']."'
                    -- and c.id = '".$item['id']."'
                    and coc.contractId = '".$item['id']."'
                   -- group by c.id
                    order by c.regDate desc
                ";

                $result3 = $this->db->query($sql3)->result_array();

                //$newList[$i]['contractOrderList'] = $result3;
                $result[$i]['contractOrderList'] = $result3;

                $i++;
            }
        }
        else
        {
            $result = [];
        }

        $resultArr = ['list'=>$result, 'totCnt'=>$totCnt];

        return $resultArr;
    }

    /**
     * 진행계약리스트 조회 - 프로그램에서 검색
     * 전체 카운트 반환
     * @param $data
     * @return array
     */
    function getContractListProgram($data)
    {
        //검색어 처리
        $where = ' having 1=1 ';
        $where0 = ' where 1=1 ';

        if(@$data['agencyUserId'] != '')
        {
            //영업담당자
            $where0 .= ' and c.agencyUserId = "'.$data['agencyUserId'].'"';
        }

        if(@$data['manageUserId'] != '')
        {
            //관리담당자
            $where0 .= ' and c.manageUserId = "'.$data['manageUserId'].'"';
        }

        if(@$data['adType2'] != '')
        {
            //상품종류
            $where .= ' and g.adType2 = "'.$data['adType2'].'"';
        }

        if(@$data['depositStatus'] != '')
        {
            $where .= " and g.depositStatusGroup like '%".$data['depositStatus']."%'";
        }

        if(@$data['advertiserStatus'] != '')
        {
            //광고주(계약)상태. 0 대기, 1 진행, 2 휴면, 3 중지, 4 이탈
            //진행 : 진행중인 이벤트가 있는 광고주
            //휴면 : 잔액이 0원이고 이벤트가 모두 종료된 광고주
            //중지 : 잔액은 있거나 -마이너스 상태에서 이벤트가 기간 상관없이 모두 종료된 광고주
            //이탈 : 휴면상태에서 3개월간 이벤트 비라이브와 미충전 상태인 광고주
            //계약별 상태로 변경됨
            switch ($data['advertiserStatus'])
            {
                case (0):
                    $where .= " and g.advertiserStatus = '대기'";
                    break;
                case (1):
                    $where .= " and g.advertiserStatus = '진행'";
                    break;
                case (2):
                    $where .= " and g.advertiserStatus = '휴면'";
                    break;
                case (3):
                    $where .= " and g.advertiserStatus = '정지'";
                    break;
                case (4):
                    $where .= " and g.advertiserStatus = '이탈'";
                    break;
            }

        }

        if(@$data['balanceStatus'] != '')
        {
            //당장 처리 못함
            //잔액유형. 1 마이너스, 2 0원, 3 50만원이하, 4 100만원이하, 5 300만원 이하, 6 300만원 이상

            $this->db->where('co.taxIssueDate is not null');
            switch ($data['balanceStatus'])
            {
                case (1):
                    $where .= ' and g.totalReady < 0 ';
                    break;
                case (2):
                    $where .= ' and g.totalReady = 0 ';
                    break;
                case (3):
                    $where .= ' and (g.totalReady > 0 and g.totalReady <= 500000 ) ';
                    break;
                case (4):
                    $where .= ' and (g.totalReady > 500000 and g.totalReady <= 1000000 ) ';
                    break;
                case (5):
                    $where .= ' and (g.totalReady > 1000000 and g.totalReady <= 3000000 ) ';
                    break;
                case (6):
                    $where .= ' and g.totalReady > 3000000 ';
                    break;
            }

        }

        if(@$data['searchType'] != '')
        {
            switch ($data['searchType'])
            {
                case "1": //병원명
                    $where .= ' and g.hospitalName like "%'. $data['searchWord'].'%"';
                    break;
                case "2": //계약명
                    $where .= ' and g.title like "%'. $data['searchWord'].'%"';
                    break;
                case "3": //수주id
                    $where .= ' and FIND_IN_SET('. $data['searchWord'].',  g.contractOrderIds )';
                    //$where .= ' and g.contractOrderId = "'. $data['searchWord'].'"';
                    break;
                case "4": //대행사명
                    $where .= ' and g.agencyCompanyName like "%'. $data['searchWord'].'%"';
                    break;
                case "5": //메모
                    $where .= ' and g.memo2 like "%'. $data['searchWord'].'%"';
                    break;
                case "6": //병원번호
                    $where .= ' and g.hospitalId = "'. $data['searchWord'].'"';
                    break;
            }
        }

        //3개월전
        $now_date = date("Y-m-d",strtotime("-3 month"));

        //갯수 산정을 위한 쿼리
        $sql = "
            select * from ( 
            select *  ,sum(oPrice) as oPriceSum
            from 
            (
            SELECT c.id, coc.`contractOrderId`, co.adType2,co.agencyCompanyName,
            c.payType, `c`.title, c.`hospitalId`, c.`hospitalName`, c.`agencyUserId`, c.`manageUserId`, co.`adPrice` oPrice, c.`regDate`,
            co.isNetwork, co.contractStatus, 
            (select group_concat(distinct co.id) from contract_order co join contract_order_connect coc on co.id=coc.contractOrderId where coc.contractId=c.id) as contractOrderIds, 
	        (select group_concat(distinct memo) from memo where targetId=co.id and memoType in(1,3)) as memo2,
	        trp.totalReady, trp.advertiserStatus, trp.depositStatusGroup
            FROM `contract` `c`
            JOIN `contract_order_connect` `coc` ON `c`.`id`=`coc`.`contractId`
            JOIN `contract_order` `co` ON `coc`.`contractOrderId`=`co`.`id`
            join total_ready_price trp on `c`.id=trp.contractId
            ".$where0."
            ) q 
            group by q.id 
            ) g 
            ".$where."
            order by g.`id` desc
          "; //echo $sql; exit;

        $result0 = $this->db->query($sql)->result_array();

        $totCnt = count($result0);
        //echo $this->db->last_query(); exit;

        $gogo=[];

        if($totCnt > 0)
        {
            $limit =  $data['limit'];
            $offset = (($data['page'] - 1) * $data['limit']);

            //위의 result0의 데이터에 서브쿼리를 추가하여 리턴한다.
            $output = array_slice($result0, $offset, $limit);

            $m=0;
            foreach ($output as $item)
            {
                $gogo[$m] = $item;

                $sql01 = "select ifnull(sum(price), 0) as chargePrice from deposit where status in(2,4) and contractId='".$item['id']."'";
                $sql02 = "select ifnull(sum(price), 0) as usePrice from deposit where status in(3,5,6,7,8) and contractId='".$item['id']."'";
                $sql03 = "select ifnull(sum(price), 0) as price1 from deposit where status in(2) and contractId='".$item['id']."'";
                $sql04 = "select ifnull(sum(price), 0) as price2 from deposit where status in(3) and contractId='".$item['id']."'";
                $sql05 = "select ifnull(sum(price), 0) as price31 from deposit where status in(4,12) and contractId='".$item['id']."'";
                $sql06 = "select ifnull(sum(price), 0) as price32 from deposit where status in(5,8,11) and contractId='".$item['id']."'";
                $sql07 = "select ifnull(sum(price), 0) as price4 from deposit where status in(6,7,9) and contractId='".$item['id']."'";
                $sql08 = "select ifnull(sum(price), 0) as price5 from deposit where status in(9,10) and contractId='".$item['id']."'";
                $sql09 = "select ifnull(sum(price), 0) as price6 from deposit where status in(6,7) and contractId='".$item['id']."'";
                $sql10 = "select group_concat(distinct memo) as memo from memo where targetId='".$item['contractOrderId']."' and memoType = 3";

                $chargePrice = $this->db->query($sql01)->row_array();
                $usePrice = $this->db->query($sql02)->row_array();
                $price1 = $this->db->query($sql03)->row_array();
                $price2 = $this->db->query($sql04)->row_array();
                $price31 = $this->db->query($sql05)->row_array();
                $price32 = $this->db->query($sql06)->row_array();
                $price4 = $this->db->query($sql07)->row_array();
                $price5 = $this->db->query($sql08)->row_array();
                $price6 = $this->db->query($sql09)->row_array();
                $memo = $this->db->query($sql10)->row_array();

                //데이터 추가
                $gogo[$m]['totalCharge'] = $gogo[$m]['chargePrice'] = $chargePrice['chargePrice'];
                $gogo[$m]['usePrice'] = $usePrice['usePrice'];
                $gogo[$m]['price1'] = $price1['price1'];
                $gogo[$m]['dbUsePrice'] = $gogo[$m]['price2'] = $price2['price2'];
                $gogo[$m]['price31'] = $price31['price31'];
                $gogo[$m]['price32'] = $price32['price32'];
                $gogo[$m]['calPrice'] = $price4['price4'];
                $gogo[$m]['price5'] = $price5['price5'];
                $gogo[$m]['price6'] = $price6['price6'];
                $gogo[$m]['totalOrder'] = $item['oPriceSum'] - $price5['price5'] - $price6['price6'];
                $gogo[$m]['readyPrice'] = $chargePrice['chargePrice'] - $price5['price5'] - $usePrice['usePrice'];
                $gogo[$m]['totalReady'] = $item['totalReady'];
                $gogo[$m]['contractChargePrice'] = $price1['price1'] - $price6['price6'];
                $gogo[$m]['etcPrice'] = $price31['price31'] - $price32['price32'];
                $gogo[$m]['taxProfit'] = $price1['price1'] - $price4['price4'];
                $gogo[$m]['memo'] = $memo['memo'];
                $gogo[$m]['advertiserStatus'] = $item['advertiserStatus'];
                $gogo[$m]['depositStatusGroup'] = $item['depositStatusGroup'];
                $m++;
            }

            //var_dump($gogo); exit;

            $i=0;
            //계약건 가져오기
            foreach ($gogo as $item2)
            {
                //취소(9 10)금액을 세발매출에서 뺄지는 추후 작업
                $sql3 = "
                    SELECT c.id,  coc.`contractOrderId`, co.adType, co.adType2, co.adPrice,
                    `c`.title, c.`hospitalId`, c.`hospitalName`, c.`agencyUserId`, c.`manageUserId`, 
                    co.agencyCompanyName, co.agencyCompanyFeeRate
                    FROM `contract` `c`
                    JOIN `contract_order_connect` `coc` ON `c`.`id`=`coc`.`contractId`
                    JOIN `contract_order` `co` ON `coc`.`contractOrderId`=`co`.`id`
                    where coc.contractId = '".$item2['id']."'
                    order by c.`id` desc
                ";

                $result3 = $this->db->query($sql3)->result_array();

                $m1=0;
                $gogo1=[];
                foreach ($result3 as $item3)
                {
                    $gogo1[$m1] = $item3;

                    $sql001 = "select ifnull(sum(price), 0) as chargePrice from deposit where status in(2,4) and contractId='".$item3['id']."' and contractOrderId='".$item3['contractOrderId']."'";
                    $sql002 = "select ifnull(sum(price), 0) as usePrice from deposit where status in(3,5,6,7,8) and contractId='".$item3['id']."' and contractOrderId='".$item3['contractOrderId']."'";
                    $sql003 = "select ifnull(sum(price), 0) as price1 from deposit where status in(2) and contractId='".$item3['id']."' and contractOrderId='".$item3['contractOrderId']."'";
                    $sql004 = "select ifnull(sum(price), 0) as price2 from deposit where status in(3) and contractId='".$item3['id']."' and contractOrderId='".$item3['contractOrderId']."'";
                    $sql005 = "select ifnull(sum(price), 0) as price31 from deposit where status in(4,12) and contractId='".$item3['id']."' and contractOrderId='".$item3['contractOrderId']."'";
                    $sql006 = "select ifnull(sum(price), 0) as price32 from deposit where status in(5,8,11) and contractId='".$item3['id']."' and contractOrderId='".$item3['contractOrderId']."'";
                    $sql007 = "select ifnull(sum(price), 0) as price4 from deposit where status in(6,7,9) and contractId='".$item3['id']."' and contractOrderId='".$item3['contractOrderId']."'";
                    $sql008 = "select ifnull(sum(price), 0) as price5 from deposit where status in(9,10) and contractId='".$item3['id']."' and contractOrderId='".$item3['contractOrderId']."'";
                    $sql009 = "select ifnull(sum(price), 0) as price6 from deposit where status in(6,7) and contractId='".$item3['id']."' and contractOrderId='".$item3['contractOrderId']."'";

                    $chargePriceC = $this->db->query($sql001)->row_array();
                    $usePriceC = $this->db->query($sql002)->row_array();
                    $price1C = $this->db->query($sql003)->row_array();
                    $price2C = $this->db->query($sql004)->row_array();
                    $price31C = $this->db->query($sql005)->row_array();
                    $price32C = $this->db->query($sql006)->row_array();
                    $price4C = $this->db->query($sql007)->row_array();
                    $price5C = $this->db->query($sql008)->row_array();
                    $price6C = $this->db->query($sql009)->row_array();

                    //데이터 추가
                    $gogo1[$m1]['oPrice'] = $item3['adPrice'] - $price5C['price5'] - $price6C['price6'];
                    $gogo1[$m1]['chargePrice'] = $chargePriceC['chargePrice'];
                    $gogo1[$m1]['usePrice'] = $usePriceC['usePrice'];
                    $gogo1[$m1]['contractChargePrice'] = $price1C['price1'] - $price6C['price6'];
                    $gogo1[$m1]['dbUsePrice'] = $price2C['price2'];
                    $gogo1[$m1]['etcPrice'] = $price31C['price31'] - $price32C['price32'];
                    $gogo1[$m1]['calPrice'] = $price4C['price4'];

                    $m1++;
                }

                $gogo[$i]['contractOrderList'] = $gogo1;

                $i++;
            }
        }
        else
        {
            $result = [];
        }

        $resultArr = ['list'=>$gogo, 'totCnt'=>$totCnt];

        return $resultArr;
    }

    /**



    (select ifnull(sum(price), 0)  from deposit where status in(6,7,9) and contractId=c.id and contractOrderId=co.id) as calPrice -- 소진+환불
     */

    /**
     * 상세계약 조회
     * @param $data
     * @return array
     */
    function getContractInfo($data)
    {
        
        $this->db->select('c.*');
        /**
         * f_hospital 제거
         */
        $this->db->select('(select group_concat(distinct id,\'|\', adTitle separator \',\') from ads where contractId=c.id) adsId', true);
        $this->db->join('contract_order_connect coc', 'c.id=coc.contractId');
        $this->db->join('contract_order co', 'coc.contractOrderId=co.id');
        $this->db->group_by('c.id');
        $result = $this->db->get_where('contract c', ['c.id'=>$data['contractId']])->row_array();
        //echo $this->db->last_query(); exit;
        
        //190104 f_hospital 제거
        //병원 아이디가 있으면 
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
            $result['hospitalNetwork'] = "일반병원";
        } 

        return $result;
    }

    /**
     * 계약원장 리스트 조회
     * @param $data
     * @return array
     */
    function getDepositList($data)
    {
        $sql = "
            select * from
            (
                (
                    select dt.id, status, isMinus, contractId, contractOrderId, userId, m.memo, price, dt.regDate, dt.modDate from deposit dt
                    left join  memo m on m.targetId2=dt.id and memoType=3
                    where dt.contractOrderId='".$data['contractOrderId']."' and status != '13' group by dt.id
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
        $totalSum = []; //상태별 합계
        foreach ($result as $item)
        {
            if($item['status'] == 3)
            {
                $stat += $item['price'];
                $statCnt++;
            }
            else
            {
                $statArr[$i] = $item;
                $i++;
            }
        }

        //합산한 소진 row 생성
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
            'modDate' => '',
        ];  

        //array_unshift($statArr,$totalSum);

        //$arr = ['contractId'=>$data['contractId'], 'contractOrderId'=>$data['contractOrderId']];
        //$this->db->select('deposit.*, m2.memo as memos');
        //$this->db->join('memo m2', 'deposit.id=m2.targetId2 and deposit.contractOrderId=m2.targetId', 'left');
        //$this->db->join('memo m2', 'deposit.contractOrderId=m2.targetId', 'left');
        //$this->db->group_by('deposit.id');
        //$this->db->where('m2.memoType', 3);
        //$result = $this->db->get_where('deposit', $arr)->result_array();
        //echo $this->db->last_query(); exit;

        return $statArr;
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
        $this->load->model('replicator_m');

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

        //충전이면 10%를 제하고 충전금액으로 한다. 나머지는 13번 세금으로 처리
        if(in_array($data['type'], [2]))
        {
            $taxPrice = $data['chargePrice'] * 10 / 100 ; //10%

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

            $data['chargePrice'] = $data['chargePrice'] - $taxPrice; //금액에서 세금 뺀다.
        }

        $originalChargePrice = $data['chargePrice']; //v1 환불처리를 위한 금액

        //수수료 차감을 사용
        //1 환불수수료 차감, 2 환불/대행수수료 차감안함, 3 대행수수료 차감, 4 환불/대행수수료 차감
        if(in_array($data['type'], [6,7]))
        {
            switch ($data['checkRefundFee'])
            {
                case 1:
                    //환불수수료 원장 입력
                    $arr = array(
                        'status'=>5, //1 수주, 2 계약충전, 3 소진, 4 기타충전, 5 환불수수료, 6 발행 환불, 7 계약 환불, 8 기타소진, 9 발행취소, 10 계약취소, 11 이월 소진, 12 이월 충전, 13 세금, 14 대행수수료
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
                    break;
                case 3:
                    //대행수수료 원장 입력
                    $contractOrderInfo = $this->getAgencyFeeRate($data['contractOrderId']);
                    if($contractOrderInfo['agencyCompanyFeeRate'] == '')
                    {
                        $contractOrderInfo['agencyCompanyFeeRate'] = 0;
                    }
                    $fee = round(($contractOrderInfo['adPrice'] * $contractOrderInfo['agencyCompanyFeeRate'])/100);
                    $arr = array(
                        'status'=>14, //1 수주, 2 계약충전, 3 소진, 4 기타충전, 5 환불수수료, 6 발행 환불, 7 계약 환불, 8 기타소진, 9 발행취소, 10 계약취소, 11 이월 소진, 12 이월 충전, 13 세금, 14 대행수수료
                        'isMinus' => 1, //양수 0, 음수 1. 고정
                        'contractId'=>$data['contractId'],
                        'contractOrderId'=>$data['contractOrderId'],
                        'usersId' => $data['users_id'],
                        'price'=> $fee,
                        'regDate'=>$regDate,
                        'modDate'=>$regDate
                    );
                    $this->master->insert('deposit', $arr);

                    $data['chargePrice'] = $data['chargePrice'] - $fee; //금액에서 수수료 뺀다.
                    break;
                case 4:
                    //작업보류 19-10-28. 대행사 수수료는 기타 매출 성격이라 여기에 넣는 것은 맞지 않다.
                    //회사간 거래에 해당하는 금액을 고객에게 보여줄 필요가 없다.
                    $contractOrderInfo = $this->getAgencyFeeRate($data['contractOrderId']);
                    if($contractOrderInfo['agencyCompanyFeeRate'] == '')
                    {
                        $contractOrderInfo['agencyCompanyFeeRate'] = 0;
                    }
                    //시점잔액 에서 대행사수수료를 구한다.
                    $fee = round(($contractOrderInfo['adPrice'] * $contractOrderInfo['agencyCompanyFeeRate'])/100);
                    $arr = array(
                        'status'=>14, //1 수주, 2 계약충전, 3 소진, 4 기타충전, 5 환불수수료, 6 발행 환불, 7 계약 환불, 8 기타소진, 9 발행취소, 10 계약취소, 11 이월 소진, 12 이월 충전, 13 세금, 14 대행수수료
                        'isMinus' => 1, //양수 0, 음수 1. 고정
                        'contractId'=>$data['contractId'],
                        'contractOrderId'=>$data['contractOrderId'],
                        'usersId' => $data['users_id'],
                        'price'=> $fee,
                        'regDate'=>$regDate,
                        'modDate'=>$regDate
                    );
                    $this->master->insert('deposit', $arr);

                    //환불수수료 원장 입력
                    $arr = array(
                        'status'=>5, //1 수주, 2 계약충전, 3 소진, 4 기타충전, 5 환불수수료, 6 발행 환불, 7 계약 환불, 8 기타소진, 9 발행취소, 10 계약취소, 11 이월 소진, 12 이월 충전, 13 세금, 14 대행수수료
                        'isMinus' => 1, //양수 0, 음수 1. 고정
                        'contractId'=>$data['contractId'],
                        'contractOrderId'=>$data['contractOrderId'],
                        'usersId' => $data['users_id'],
                        'price'=>'300000', //환불수수료 30만원 고정값
                        'regDate'=>$regDate,
                        'modDate'=>$regDate
                    );
                    $this->master->insert('deposit', $arr);
                    $data['chargePrice'] = $data['chargePrice'] - $fee - 300000; //금액에서 수수료 뺀다.

                    break;
            }

        }

        //기타충전, 소진 금액을 따로 분리하지 않고 시점잔액 기준으로 환불한다. 2019.1.16 tee, hendo, martin
        //환불의 경우 서비스(기타)충전이 있다면 그 금액을 먼저 차감하고 나머지를 환불한다.
//        if(in_array($data['type'], [6,7]))
//        {
//            $sCharge = $this->checkServiceCharge($data['contractOrderId']); //false나 0 이 리턴되면 차감하지 않는다.
//
//            if($sCharge)
//            {
//                //서비스충전 금액 원장 입력
//                $arr = array(
//                    'status'=>8, //1 수주, 2 계약충전, 3 소진, 4 기타충전, 5 환불수수료, 6 발행 환불, 7 계약 환불, 8 기타 소진, 9 발행취소, 10 계약취소, 11 이월 소진, 12 이월 충전, 13 세금
//                    'isMinus' => 1, //양수 0, 음수 1. 고정
//                    'contractId'=>$data['contractId'],
//                    'contractOrderId'=>$data['contractOrderId'],
//                    'usersId' => $data['users_id'],
//                    'price'=> $sCharge,
//                    'regDate'=>$regDate,
//                    'modDate'=>$regDate
//                );
//                $this->master->insert('deposit', $arr);
//
//                $data['chargePrice'] = $data['chargePrice'] - $sCharge; //금액에서 서비스충전금액을 뺀다.
//            }
//        }

        //원장 입력
        $arr = array(
            'status'=>$data['type'], //1 수주, 2 계약충전, 3 소진, 4 기타충전, 5 환불수수료, 6 발행 환불, 7 계약 환불, 8 기타 소진, 9 발행취소, 10 계약취소, 11 이월 소진, 12 이월 충전, 13 세금
            'isMinus' => $minus, //양수 0, 음수 1
            'contractId'=>$data['contractId'],
            'contractOrderId'=>$data['contractOrderId'],
            'usersId' => $data['users_id'],
            'price'=>$data['chargePrice'],
            'regDate'=>$regDate,
            'modDate'=>$regDate
        );

        //소진의 경우 callRequestId를 넣어준다
        if($data['type'] == 3)
        {
            $arr['callRequestId'] = $data['callRequestId'];
        }

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
            $this->master->where('id', $data['contractOrderId']);
            $this->master->update('contract_order', $arr);
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

        //리플리케이터 시작. v1 리플리케이터가 스테이징부터 있어서 개발을 스테이징에서 할수밖에 없다.
        //dev는 재택중인 마틴 집에서 제대로 작동하지 않는다. (병원api의 경우 회사내부에 위치해있어서 접근이 안됨, 테스트 불가)
        if(in_array($data['type'], [6,7]))
        {
            $iData = [
                'contract_id' => $data['contractId'], //계약번호
                'payment_type' => 9, //환불
                'price' => $originalChargePrice,
                'memo' => '환불처리'
            ];
            log_message('info', 'rep strat');
            $this->replicator_m->send('/api/payments', 'POST', $iData);
            log_message('info', 'rep end');
        }
        //리플리케이터 종료

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
            select id, tax, balance, if(contractStatus = 1 and charge > 0 and charge2 != 0, '부분입금', deposit2) as deposit, 
            contractStatus, charge, charge1, charge2, charge3, liveAdsCount, feeRate, feePrice from 
            (
            select co.id, 
            if(co.taxIssueDate is null, '미발행', '발행') as tax,
            if(co.contractStatus= 1 and co.depositDate = '0000-00-00 00:00:00', '미입금', if(co.contractStatus= 1 and co.depositDate is null, '미입금', '입금')) as deposit2,

            case contractStatus
            	when 1 then ifnull((select sum(price) from deposit where status in(2,4)  and contractId=coc.contractId),0) - ifnull((select sum(price) from deposit where status in(3,5,6,7,8) and contractId=coc.contractId),0) -- (계약충전+기타충전) - (소진+기타 차감+환불수수료)
            	else 0
			end as 			balance ,
			case contractStatus
            	when 1 then ifnull((select sum(price) from deposit where status in(1)  and contractId=coc.contractId),0) - ifnull((select sum(price) from deposit where status in(2) and contractId=coc.contractId),0) -- (수주) - (계약충전)
            	else 0
			end as 			charge ,
			ifnull((select sum(price) from deposit where status in(2,4)  and contractId=coc.contractId),0) as charge1,
			ifnull((select sum(price) from deposit where status in(2) and contractId=coc.contractId), 0) as charge2,
			ifnull((select sum(price) from deposit where status in(1) and contractId=coc.contractId), 0) as charge3,
			co.contractStatus,
			(select count(*) from ads where isLive = 'Y' and hospitalId=co.`hospitalId`) as liveAdsCount,
			co.agencyCompanyFeeRate as feeRate,
			ROUND((co.adPrice * co.agencyCompanyFeeRate) / 100) as feePrice
			from contract_order  co 
            join contract_order_connect coc on co.id=coc.contractOrderId
            where co.id='".$data['contractOrderId']."'
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
    /*
    190131 안쓰는거 같음
    function setContractInfo($data)
    {
        unset($data['menu_id']);
        unset($data['token']);
        unset($data['users_id']);

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
    */

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
     * 수주계약번호에 해당하는 대행사 수수료 % 리턴
     * @param $contractOrderId
     * @return array
     */
    function getAgencyFeeRate($contractOrderId)
    {
        $this->db->where('id', $contractOrderId);
        $result = $this->db->get('contract_order')->row_array();

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