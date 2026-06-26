<?php
/**
 * Created by PhpStorm.
 * User: blumine
 * Date: 2018. 4. 2.
 * Time: AM 10:54
 */

class eventRequest_m extends CI_Model
{
    function __construct()
    {
        parent::__construct();
    }

    /**
     * 병원어드민용 신청자 리스트
     * @param $data
     * @return array|bool
     */
    function getRequestList($data)
    {
        try {
            $hospitalId = $this->common_m->getHospitalId($data); //echo 'h-'.$hospitalId; exit;

            //기본
            $orderBy = " order by b.id desc";

            $where = " and b.hospitalId = '".$hospitalId."' ";

            //상태
            if($data['status'])
            {
                $where .= " and b.status = '".$data['status']."'";
            }

            //이벤트번호
            if($data['searchEventId'])
            {
                $where .= " and b.adsId = '".$data['searchEventId']."'";
            }

            //기간
            if($data['searchDateType'])
            {
                //1 오늘, 2 어제, 3 최근7일, 4 최근 30일, 5 이번달, 6 기간설정(최대 3개월)
                //계산되는 날짜에서 -9 해야 제 날짜가 나옴
                switch ($data['searchDateType'])
                {
//                    case 1:
//                        $where .= " and b.regDate like '".date("Y-m-d")."%'";
//                        break;
//                    case 2:
//                        $where .= " and (b.regDate >= '".date("Y-m-d H:i:s", strtotime('-1days', strtotime('-9 times')))."' and b.regDate < '".date("Y-m-d 23:59:59", strtotime('-9 times'))."'";
//                        break;
//                    case 3:
//                        $where .= " and b.regDate >= '".date("Y-m-d H:i:s", strtotime('-7days', strtotime('-9 times')))."'";
//                        break;
//                    case 4:
//                        //시작일
//                        $where .= " and b.regDate >= '".date("Y-m-d H:i:s", strtotime('-30days', strtotime('-9 times')))."'";
//                        break;
//                    case 5:
//                        //시작일
//                        $startDate = date("Y-m-d H:i:s", mktime(0,0,0,date("m"),1,date("Y")));
//                        $where .= " and (b.regDate >= '".$startDate."' and b.regDate <= '".date("Y-m-d H:i:s")."')";
//                        break;
                    case 1:
                    case 2:
                    case 3:
                    case 4:
                    case 5:
                    case 6:
                        $dateArr = explode('|', $data['searchDateValue']);
                        //tz 날짜 변환
                        $sDate = date("Y-m-d H:i:s", strtotime($dateArr[0]));
                        $eDate = date("Y-m-d H:i:s", strtotime($dateArr[1]));
                        $where .= " and (b.regDate >= '".$sDate."' and b.regDate <= '".$eDate."')";
                        break;
                }
            }

            //검색어. 신청자이름과 전화번호에서 검색
            if($data['searchWord'])
            {
                $where .= " and (b.name like '%".$data['searchWord']."%' or b.phone like '%".$data['searchWord']."%') ";
            }

            //channel 추가
            if($data['channel'])
            {
                //a 이면 전체
                $where .= " and b.channel = '".$data['channel']."'";
            }

            $sql = "select b.id as requestId, b.adsId, b.channel, b.status, b.name, b.phone, b.content,b.regDate, 
            group_concat(concat(left(bf.regDate, 10), ' ', bf.memo) separator '|') as memoArray , b.userId, age, sex, callTime, 
            region, ads.adTitle as eventName, b.funnel,
            -- (select bookDate from booking where callRequestId=b.callRequestId order by id desc limit 1) as bookDate
            bk.`bookDate`, b.eventCost as dbCost, b.callRequestId
            from call_request b 
            left join call_memo bf on b.callRequestId=bf.callRequestId 
            left join ads on b.adsId=ads.id 
            left join booking bk on b.callRequestId=bk.callRequestId
            where b.isDelete = '0' 
            ".$where."
            group by b.id
            ". $orderBy; //echo $sql; exit;

            $sqlTot = "select count(*) cnt
            from call_request b 
            -- left join call_memo bf on b.callRequestId=bf.callRequestId 
            -- left join ads on b.adsId=ads.id 
            -- left join booking bk on b.callRequestId=bk.callRequestId
            where b.isDelete = '0' 
            ".$where;


            $sql .= ' limit '.(($data['page'] - 1) * $data['limit']).', '. $data['limit'];

            $result = $this->db->query($sql)->result_array();

            //전체 수
            $totCount = $this->db->query($sqlTot)->row_array();

            $data = ['data'=>$result, 'totCount'=>$totCount['cnt']];

            return $data;
        } catch (Exception $e) {
            $this->response($this->json([
                'status' => 'error',
                'code' => '610',
                'message' => '서버관리자에게 문의하세요.',
                'result' => []
            ]), 200);
        }
    }

    /**
     * 운영어드민용 신청자 리스트
     * @param $data
     * @return array|bool
     */
    function getAdminRequestList($data)
    {
        try {
            //기본
            $orderBy = " order by b.id desc";
            $where = '';

            //상태
            if($data['status'])
            {
                $where .= " and b.status = '".$data['status']."'";
            }

            //이벤트번호
            if($data['eventId'])
            {
                if(strpos($data['eventId'], ','))
                {
                    //다중
                    $where .= " and b.adsId in (".$data['eventId'].")";
                }
                else
                {
                    $where .= " and b.adsId = '".$data['eventId']."'";
                }
            }

            //광고유형
            if($data['eventType'])
            {
                $where .= " and ads.adType = '".$data['eventType']."'";
            }

            if($data['device'])
            {
                $where .= " and b.device = '".$data['device']."'";
            }

            if($data['category'])
            {
                $where .= " and ads.category = '".$data['category']."'";
            }

            if($data['funnel'])
            {
                $where .= " and b.funnel = '".$data['funnel']."'";
            }

            if($data['adTitle'])
            {
                $where .= " and ads.adTitle = '".$data['adTitle']."'";
            }

            //기간
            if($data['dateType'])
            {
                //1 오늘, 2 어제, 3 최근7일, 4 최근 30일, 5 이번달, 6 기간설정(최대 3개월)
                //계산되는 날짜에서 -9 해야 제 날짜가 나옴
                switch ($data['dateType'])
                {
                    case 1:
                    case 2:
                    case 3:
                    case 4:
                    case 5:
                    case 6:
                        $dateArr = explode('|', $data['dateValue']);
                        //tz 날짜 변환
                        $sDate = date("Y-m-d H:i:s", strtotime($dateArr[0]));
                        $eDate = date("Y-m-d H:i:s", strtotime($dateArr[1]));
                        $where .= " and (b.regDate >= '".$sDate."' and b.regDate <= '".$eDate."')";
                        break;
                }
            }

            //검색어.
            if($data['searchType'] and $data['searchWord'])
            {
                //1 이름, 2 전화번호, 3 병원명(번호)
                switch ($data['searchType'])
                {
                    case 1:
                        $where .= " and b.name like '%".$data['searchWord']."%' ";
                        break;
                    case 2:
                        $where .= " and b.phone like '%".$data['searchWord']."%' ";
                        break;
                    case 3:
                        if(strpos($data['searchWord'], ','))
                        {
                            //다중
                            $where .= " and ads.hospitalId in (".$data['searchWord'].")";
                        }
                        else
                        {
                            $where .= " and ads.hospitalId = '".$data['searchWord']."' ";
                        }
                        break;
                }
            }

            //channel 추가
            if($data['channel'])
            {
                //a 이면 전체
                $where .= " and b.channel = '".$data['channel']."'";
            }

            $sql = "select b.id as requestId, b.adsId, b.channel, b.status, b.name, b.phone, b.content,b.regDate, 
            group_concat(concat(left(bf.regDate, 10), ' ', bf.memo) separator '|') as memoArray , b.userId, age, sex, callTime, 
            region, ads.adTitle as eventName, b.funnel,
            bk.`bookDate`, b.eventCost as dbCost, b.callRequestId, b.device, b.hospitalId
            from call_request b 
            left join call_memo bf on b.callRequestId=bf.callRequestId 
            left join ads on b.adsId=ads.id 
            left join booking bk on b.callRequestId=bk.callRequestId
            where b.isDelete = '0' 
            ".$where."
            group by b.id
            ". $orderBy; //echo $sql; exit;

            $sqlTot = "select count(*) cnt
            from call_request b 
            -- left join call_memo bf on b.callRequestId=bf.callRequestId 
            left join ads on b.adsId=ads.id 
            -- left join booking bk on b.callRequestId=bk.callRequestId
            where b.isDelete = '0' 
            ".$where;


            $sql .= ' limit '.(($data['page'] - 1) * $data['limit']).', '. $data['limit'];

            $result = $this->db->query($sql)->result_array();

            //병원명 치환
            $hospitalId_arr    = [];
            foreach($result as $key => $val)
            {
                if (!is_null($val['hospitalId']) && !empty($val['hospitalId']) ) {
                    $hospitalId_arr[] = $val['hospitalId'];
                }
            }

            //중복 제거
            $hospitalId_arr = array_unique($hospitalId_arr);

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

            //전체 수
            $totCount = $this->db->query($sqlTot)->row_array();

            $data = ['data'=>$result, 'totCount'=>$totCount['cnt']];

            return $data;
        } catch (Exception $e) {
            $this->response($this->json([
                'status' => 'error',
                'code' => '610',
                'message' => '서버관리자에게 문의하세요.',
                'result' => []
            ]), 200);
        }
    }

    /**
     * 신청db 상세보기
     * @param $data
     * @return mixed
     */
    function getRequestView($data)
    {
        try {
            $hospitalId = $this->common_m->getHospitalId($data); //echo 'h-'.$hospitalId; exit;

            $sql = "select b.id as requestId, b.adsId, b.status, b.name, b.phone, b.content,b.modifyDate, 
            b.userId, age, sex, callTime, region, b.eventCost as dbCost, b.callRequestId, b.channel, bk.`bookDate`
            from call_request b 
            left join booking bk on b.callRequestId=bk.callRequestId
            where b.isDelete = '0' and b.hospitalId = '".$hospitalId."'
            and b.id = '".$data['requestId']."' 
            "; //echo $sql; exit;

            $result = $this->db->query($sql)->row_array();

            return $result;
        } catch (Exception $e) {
            $this->response($this->json([
                'status' => 'error',
                'code' => '610',
                'message' => '서버관리자에게 문의하세요.',
                'result' => []
            ]), 200);
        }
    }


    /**
     * 상담관리 대시보드 숫자 리턴
     * @param $data
     * @return array
     */
    function getRequestStatus($data)
    {
        $hospitalId = $this->common_m->getHospitalId($data);

        $where = " ";

        //이벤트번호
        if($data['searchEventId'])
        {
            $where .= " and adsId = '".$data['searchEventId']."'";
        }

        //기간
        if($data['searchDateValue'])
        {
            //1 오늘, 2 어제, 3 최근7일, 4 최근 30일, 5 이번달, 6 기간설정(최대 3개월)
            //계산되는 날짜에서 -9 해야 제 날짜가 나옴
            $dateArr = explode('|', $data['searchDateValue']);
            //tz 날짜 변환
            $sDate = date("Y-m-d H:i:s", strtotime($dateArr[0]));
            $eDate = date("Y-m-d H:i:s", strtotime($dateArr[1]));
            $where .= " and (regDate >= '".$sDate."' and regDate <= '".$eDate."')";
        }

        //검색어. 신청자이름과 전화번호에서 검색
        if($data['searchWord'])
        {
            $where .= " and (name like '%".$data['searchWord']."%' or phone like '%".$data['searchWord']."%') ";
        }

        //channel 추가
        if($data['channel'])
        {
            //a 이면 전체
            $where .= " and channel = '".$data['channel']."'";
        }

        $arr = [0,1,2,3,4,5,6,7,8,9];
        $return = [];

        foreach ($arr as $it)
        {
            if($it == 0)
            {
                $where2 = '';
            }
            else
            {
                $where2 = " and status='".$it."'";
            }

            //$sql = "select * from call_request where isDelete=0 and hospitalId = '".$hospitalId."' ".$where;
            $sql = "select count(*) as cnt from call_request  where isDelete=0 and hospitalId = '".$hospitalId."' ".$where.$where2;
            $result = $this->db->query($sql)->row_array();
            $return['return'.$it] = $result['cnt'];
        }

        //echo $sql; exit;

        return $return;
    }

    /**
     * 메모입력
     * @param $data
     * @return int
     */
    function setRequestMemo($data)
    {
        try {
            $callArr = $this->db->get_where('call_request', ['id'=>$data['requestId']])->row_array();
            $iArr = [
                'userId'=>$data['users_id'],
                'memo'=>$data['memo'],
                'regDate'=>date("Y-m-d H:i:s"),
                'callRequestId'=>$callArr['callRequestId']
            ];
            $this->master->insert('call_memo', $iArr);

            return $this->master->insert_id();
        } catch (Exception $e) {
            $this->response($this->json([
                'status' => 'error',
                'code' => '614',
                'message' => '서버관리자에게 문의하세요.',
                'result' => []
            ]), 200);
        }

    }


    /**
     * 신청상태 변경
     * @param $data
     * @return mixed
     */
    function updateRequest($data)
    {
        $hospitalId = $this->common_m->getHospitalId($data);
        $date = date("Y-m-d H:i:s"); //umt기준

        $uArr = [
            'status'=> $data['status'],
            'modifyDate' => $date,
            'confirmDate' => $date
        ];

        $this->master->where('hospitalId', $hospitalId);
        $this->master->where('id', $data['requestId']);
        $this->master->update('call_request', $uArr);

        $result = $this->master->affected_rows();

        //리플리케이터 시작
        //2 부재중, 3 취소, 4 기타, 5 예약, 6 예약취소, 7 내원완료

        //callRequestId 를 얻기 위한
        $callArr = $this->master->get_where('call_request', ['id'=>$data['requestId']])->row_array();

        switch ($data['status'])
        {
            case 2:
                $cIds = 7;
                break;
            case 3:
                $cIds = 3;
                break;
            case 4:
                $cIds = 8;
                break;
            case 5:
                $cIds = 4;
                break;
            case 6:
                $cIds = 9;
                break;
            case 7:
                $cIds = 5;
                break;
        }
        //계약 등록
        $iData = [
            'status' => $cIds //미확인:0, 미완료:1, 내원:2, 취소:3, 예약:4, 내원:5, 결번:6, 부재중:7, 기타:8, 예약 취소:9, 중복:10
        ];

        $this->replicator_m->send('/api/call_requests/'.$callArr['callRequestId'], 'PATCH', $iData);
        //리플리케이터 끝

        if ($data['status'] == 5 and $data['nowStatus'] != 5)
        {
            $item = $this->master->get_where('call_request', ['id'=> $data['requestId']])->row_array();

            $this->master->order_by('id', 'desc');
            $this->master->limit(1);
            $bookCount = $this->master->get_where('booking', ['callRequestId'=> $callArr['callRequestId']])->row_array();

            if(count($bookCount) == 0)
            {
                //예약 첫 입력
                $iArr = [
                    'userId'=>$item['userId'],
                    'hospitalId'=>$item['hospitalId'],
                    'status'=>$data['status'],
                    'bookDate'=>$data['bookingDate'].' '.$data['bookingTime'],
                    'confirmDate'=>$date,
                    'oldId'=>'',
                    'regDate'=>$date,
                    'modifyDate'=>$date,
                    'name'=>$item['name'],
                    'phone'=>$item['phone'],
                    'callRequestId'=>$callArr['callRequestId']
                ];
                $this->master->insert('booking', $iArr);
            }
            else
            {
//                $iArr = [
//                    'userId'=>$item['userId'],
//                    'hospitalId'=>$item['hospitalId'],
//                    'status'=>$data['status'],
//                    'bookDate'=>$data['bookingDate'].' '.$data['bookingTime'],
//                    'confirmDate'=>$date,
//                    'oldId'=>$bookCount['id'], //입력된 번호
//                    'regDate'=>$date,
//                    'modifyDate'=>$date,
//                    'name'=>$item['name'],
//                    'phone'=>$item['phone'],
//                    'callRequestId'=>$callArr['callRequestId']
//                ];
                $uArr = [
                    'modifyDate'=>$date,
                    'bookDate' => $data['bookingDate'].' '.$data['bookingTime']
                ];

                $this->master->where('id', $bookCount['id']);
                $this->master->update('booking', $uArr);
            }


        }
        else if ($data['status'] == 5 and $data['nowStatus'] == 5)
        {
            //시간만 변경한다.
            $this->master->order_by('id', 'desc');
            $this->master->limit(1);
            $bookCount = $this->master->get_where('booking', ['callRequestId'=> $callArr['callRequestId']])->row_array();

            $uArr = [
                'modifyDate'=>$date,
                'bookDate' => $data['bookingDate'].' '.$data['bookingTime']
            ];

            $this->master->where('id', $bookCount['id']);
            $this->master->update('booking', $uArr);

        }


        if ($result === FALSE)
        {
            return ['code'=>500, 'message'=>''];
        }
        else
        {
            return ['code'=>200, 'message'=>''];
        }
    }

    /**
     * 운영어드민용 블랙리스트
     * @param $data
     * @return array|bool
     */
    function getBlackList($data)
    {
        try {
            //기본
            $orderBy = " order by id desc";
            $where = '';

            //검색어.
            if($data['searchWord'])
            {
                $where .= " and phone = '".$data['searchWord']."' ";
            }

            $sql = "select * from call_request_black_list  
            where 1=1   
            ".$where."
            ". $orderBy; //echo $sql; exit;

            $sqlTot = "select count(*) cnt
            from call_request_black_list 
            where 1=1  
            ".$where;

            $sql .= ' limit '.(($data['page'] - 1) * $data['limit']).', '. $data['limit'];

            $result = $this->db->query($sql)->result_array();

            $return = [];
            foreach ($result as $item)
            {
                //유저명 가져오기
                $userName = '';
                //유저명 추가
                $return = $item;
                $return['userName'] = $userName;
            }

            //전체 수
            $totCount = $this->db->query($sqlTot)->row_array();

            $data = ['data'=>$return, 'totCount'=>$totCount['cnt']];

            return $data;
        } catch (Exception $e) {
            $this->response($this->json([
                'status' => 'error',
                'code' => '610',
                'message' => '서버관리자에게 문의하세요.',
                'result' => []
            ]), 200);
        }
    }

    /**
     * 전화번호 중복 체크
     * @param $data
     * @return mixed
     */
    function checkBlackList($data)
    {
        $this->master->select('count(*) as cnt');
        $result = $this->master->get_where('call_request_black_list', ['phone'=>$data['phone']])->row_array();

        return $result['cnt'];
    }

    /**
     * black list add
     * @param $data
     * @return int
     */
    function setBlackList($data)
    {
        $date = date("Y-m-d H:i:s");
        $iArr = [
            'phone' => $data['phone'],
            'userId' => $data['users_id'],
            'desc' => $data['desc'],
            'regDate' => $date,
            'modDate' => $date
        ];

        $this->master->insert('call_request_black_list', $iArr);

        return $this->master->insert_id();
    }

    /**
     * black list delete
     * @param $data
     * @return int
     */
    function deleteBlackList($data)
    {
        $this->master->where('id', $data['id']);
        $this->master->delete('call_request_black_list');

        return $this->master->affected_rows();
    }
}