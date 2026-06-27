<?php
/**
 * Created by PhpStorm.
 * User: blumine
 * Date: 2018. 2. 12.
 * Time: PM 5:45
 */

class dataMigration_m extends CI_Model
{
    function __construct()
    {
        parent::__construct();
        //$this->master = $this->load->database('master', true);
    }

    /**
     * 수주계약, 계약 및 매핑 테이블까지만 입력하고 수주계약번호 리턴
     * @param $data
     * @return int
     */
    function setContractOrder($data)
    {
        //계약용 복사
        $data2 = $data;

        unset($data['id']);
        unset($data['menu_id']);
        unset($data['token']);
        unset($data['users_id']);

        if ( isset($data['refreshToken']) )
        {
            unset($data['refreshToken']);
        }

        //$data['regDate'] = date("Y-m-d H:i:s");

        //수주 테이블 입력
        $this->master->insert('contract_order', $data);
        $oIds = $this->master->insert_id();

        unset($data2['menu_id']);
        unset($data2['token']);
        unset($data2['users_id']);
        unset($data2['contractType']);
        unset($data2['adPrice']);
        unset($data2['taxIssueRequestDate']);
        unset($data2['agencyCompanyFeeRate']);
        unset($data2['agencyCompanyChargeName']);
        unset($data2['agencyCompanyChargePhone']);
        unset($data2['agencyCompanyChargeEmail']);
        unset($data2['isNetwork']);
        $this->master->insert('contract', $data2);
        $cIds = $data2['id']; // 넘어온 계약번호로 대체. contract 자동증가 품.

        //매핑테이블 입력
        $this->master->insert('contract_order_connect', array('contractId'=>$cIds, 'contractOrderId'=>$oIds));
        $cocIds = $this->master->insert_id();

        return $oIds;
    }

    /**
     * 수주계약, 계약 및 매핑 테이블까지만 입력하고 수주계약번호 리턴
     * @param $data
     * @return int
     */
    function setContractOrder2($data)
    {
        //계약용 복사
        $data2 = $data;

        unset($data2['menu_id']);
        unset($data2['token']);
        unset($data2['users_id']);
        unset($data2['contractType']);
        unset($data2['adPrice']);
        unset($data2['taxIssueRequestDate']);
        unset($data2['agencyCompanyFeeRate']);
        unset($data2['agencyCompanyChargeName']);
        unset($data2['agencyCompanyChargePhone']);
        unset($data2['agencyCompanyChargeEmail']);
        unset($data2['isNetwork']);
        unset($data2['depositDate']);
        unset($data2['taxIssueRequestDate']);
        unset($data2['taxIssueDate']);
        $this->master->insert('contract', $data2);
        $cIds = $data2['id']; // 넘어온 계약번호로 대체. contract 자동증가 품.

        unset($data['id']);
        unset($data['menu_id']);
        unset($data['token']);
        unset($data['users_id']);

        if ( isset($data['refreshToken']) )
        {
            unset($data['refreshToken']);
        }

        //$data['regDate'] = date("Y-m-d H:i:s");

        //수주 테이블 입력
        $this->master->insert('contract_order', $data);
        $oIds = $this->master->insert_id();

        //매핑테이블 입력
        $this->master->insert('contract_order_connect', array('contractId'=>$cIds, 'contractOrderId'=>$oIds));
        $cocIds = $this->master->insert_id();

        return ['contractId' =>$cIds, 'contractOrderId'=>$oIds];
    }
    /**
     * 원장입력, 수주금액 및 충전처리 진행 (원래 프로세스)
     * @param $data
     * @return bool
     */
    function chargeContractOrder($data)
    {
        //$data['regDate'] = date("Y-m-d H:i:s");

        //원장테이블 입력
        $dArr = array(
            'status'=>1, //1 수주, 2 계약충전, 3 소진, 4 서비스충전, 5 결번충줜, 6 발행 환불, 7 계약 환불, 8 기타 소진
            'isMinus' => ($data['adPrice'] > 0)? 0:1, //양수 0, 음수 1
            'contractId'=>$data['id'],
            'contractOrderId'=>$data['contractOrderId'],
            'usersId' => 1,
            'price'=>$data['adPrice'],
            'regDate'=>$data['regDate'],
            'modDate'=>$data['regDate']
        );
        $this->master->insert('deposit', $dArr);

        //$data['regDate'] = date("Y-m-d H:i:s");

        //충전
        $dArr2 = array(
            'status'=>2, //1 수주, 2 계약충전, 3 소진, 4 서비스충전, 5 결번충줜, 6 발행 환불, 7 계약 환불, 8 기타 소진
            'isMinus' => ($data['adPrice'] > 0)? 0:1, //양수 0, 음수 1
            'contractId'=>$data['id'],
            'contractOrderId'=>$data['contractOrderId'],
            'usersId' => 1,
            'price'=>$data['adPrice'],
            'regDate'=>$data['regDate'],
            'modDate'=>$data['regDate']
        );
        $this->master->insert('deposit', $dArr2);

        return true;
    }

    /**
     * 원장입력, 수주금액 및 이월충전처리 진행 (2019년 1월 이전 매출이라 이월처리함)
     * @param $data
     * @return bool
     */
    function chargeContractOrder2($data)
    {
        //원장테이블 입력
        $dArr = array(
            'status'=>1, //1 수주, 2 계약충전, 3 소진, 4 서비스충전, 5 결번충줜, 6 발행 환불, 7 계약 환불, 8 기타 소진
            'isMinus' => ($data['adPrice'] > 0)? 0:1, //양수 0, 음수 1
            'contractId'=>$data['id'],
            'contractOrderId'=>$data['contractOrderId'],
            'usersId' => 1,
            'price'=>$data['adPrice'],
            'regDate'=>$data['regDate'],
            'modDate'=>$data['regDate']
        );
        $this->master->insert('deposit', $dArr);

        //충전
        $dArr2 = array(
            'status'=>2, //1 수주, 2 계약충전, 3 소진, 4 서비스충전, 5 결번충줜, 6 발행 환불, 7 계약 환불, 8 기타 소진
            'isMinus' => ($data['adPrice'] > 0)? 0:1, //양수 0, 음수 1
            'contractId'=>$data['id'],
            'contractOrderId'=>$data['contractOrderId'],
            'usersId' => 1,
            'price'=>$data['adPrice'],
            'regDate'=>$data['regDate'],
            'modDate'=>$data['regDate']
        );
        $this->master->insert('deposit', $dArr2);

        return true;
    }

    /**
     * 입금처리 (세금계산서 금액 분리)
     * adPrice는 세금 붙은 금액임
     * @param $data
     * @return bool
     */
    function chargeContractOrder3($data)
    {
        //세금부분 계산 및 입력
        $taxPrice = round($data['adPrice'] /  11) ; //세금액
        $chargePrice = round($data['adPrice'] /  1.1) ; //계약액

        //원장테이블 수주 입력
        $dArr = array(
            'status'=>1, //1 수주, 2 계약충전, 3 소진, 4 서비스충전, 5 결번충줜, 6 발행 환불, 7 계약 환불, 8 기타 소진
            'isMinus' => 0, //양수 0, 음수 1
            'contractId'=>$data['id'],
            'contractOrderId'=>$data['contractOrderId'],
            'usersId' => 1,
            'price'=>$chargePrice, //계약액 들어감
            'regDate'=>$data['regDate'],
            'modDate'=>$data['regDate']
        );
        $this->master->insert('deposit', $dArr);

        $arr = array(
            'status'=>13, //1 수주, 2 계약충전, 3 소진, 4 기타충전, 5 환불수수료, 6 발행 환불, 7 계약 환불, 8 기타소진, 9 발행취소, 10 계약취소, 11 이월 소진, 12 이월 충전, 13 세금
            'isMinus' => 0, //양수 0, 음수 1. 고정
            'contractId'=>$data['id'],
            'contractOrderId'=>$data['contractOrderId'],
            'usersId' => 1,
            'price'=>$taxPrice,
            'regDate'=>$data['regDate'],
            'modDate'=>$data['regDate']
        );
        $this->master->insert('deposit', $arr);

        //계약금액만 입력
        $dArr = array(
            'status'=>2, //1 수주, 2 계약충전, 3 소진, 4 서비스충전, 5 결번충줜, 6 발행 환불, 7 계약 환불, 8 기타 소진
            'isMinus' => 0, //양수 0, 음수 1
            'contractId'=>$data['id'],
            'contractOrderId'=>$data['contractOrderId'],
            'usersId' => 1,
            'price'=>$chargePrice,
            'regDate'=>$data['regDate'],
            'modDate'=>$data['regDate']
        );
        $this->master->insert('deposit', $dArr);
        $depositId = $this->master->insert_id();

        if($data['memo'])
        {
            $arr2 = [
                'memoType'=>3, //원장메모(운영자)
                'targetId'=>$data['contractOrderId'],
                'targetId2'=>$depositId, //원장번호
                'userId'=>1,
                'memo'=>$data['memo']
            ];
            $this->setContractOrderMemo($arr2);
        }

        //depositDate update
        $this->master->where('id', $data['contractOrderId']);
        $this->master->update('contract_order', ['depositDate'=>$data['regDate']]);
        return true;
    }

    /**
     * 수주계약번호 구하기
     * @param $contractId
     * @return mixed
     */
    function getContractOrderId($contractId)
    {
        //이전 쿼리는 최초의 수주계약번호만 가져옴. 그래서 ads vContractOrderId가 항상 최초 수주계약번호임.
        //상태가 1(정상)인 계약번호를 구해서 입력처리한다.
        $this->master->join('contract_order co', 'coc.contractOrderId=co.id');
        $result = $this->master->get_where('contract_order_connect coc', array('coc.contractId'=>$contractId, 'co.contractStatus'=>1))->row_array();

        return $result['contractOrderId'];
    }

    /**
     * 이벤트 상세이미지 가져오기
     * @param $eventId
     * @return array|bool
     */
    function getImage($eventId)
    {
        $this->v1->order_by('sort');
        $this->v1->select('id, image, client_image, sort', false);
        //$this->v1->limit(6);  //이미지 제한 풀기
        $this->v1->where('(image is not null or image != "")');
        $result = $this->v1->get_where('event_infos', ['event_id'=>$eventId])->result_array(); //echo $this->v1->last_query();

        if(count($result) > 0)
        {
            return $result;
        }

        return false;
    }

    /**
     * 검수정보 업데이트
     * @param $data
     * @return int
     */
    function updateInspectInfo($data)
    {
        //비교용 광고정보 가져오기
        //쿼리 잘못 됨 값이 한 테이블이라도 빠지면 모두 null 됨
        //left join으로 변경
//        $this->master->select('ads.*, ia.adsMainMapId, ia.historyId');
//        //$this->master->select('group_concat(ar.regionCode) region, group_concat(ac.adMedium) cooperation, group_concat(ak.keyword) as keyword');
//        $this->master->join('ads', 'ia.adsId=ads.id');
//        $adOriInfo = $this->master->get_where('inspecting_ads ia', ['ia.id'=>$data['inspectId']])->row_array();

        $this->master->select('ads.*, ia.adStatus as iaAdStatus, ia.adsMainMapId, ia.historyId, ads.vT2ImageName as t2ImageName');
        $this->master->select('ads.vRegion as region, ads.vCooperation as cooperation, ads.vKeyword as keyword');
        $this->master->join('ads', 'ia.adsId=ads.id');
        $adOriInfo = $this->master->get_where('inspecting_ads ia', ['ia.id'=>$data['inspectId']])->row_array();


        $hData = [];
        //히스토리 가져와서
        $param['adsId'] = $adOriInfo['id'];
        $param['historyId'] = $adOriInfo['historyId'];
        if(isset($data['hospitalId']) )
        {
            $param['hospitalId'] = $data['hospitalId'];
        }

        $historyMerge = $this->gethistoryMerge($param);
        $dImageJson  = json_decode($historyMerge['dImageJson']);

        //d 이미지 무조건 제거
        for ($kkk=0; $kkk < 40; $kkk++)
        {
            $lll= $kkk+1;
            $imgName = 'd'.$lll.'ImageName';
            unset($historyMerge[$imgName]);
        }

        $inspectDate     = $data['confirmDate'];
        $deleteJson      = [];

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
                'regDate'=>$data['regDate']
            ];

            $lArr = array_merge($hData, $hArr2);

            //d 이미지 무조건 제거
            for ($kkk=0; $kkk < 40; $kkk++)
            {
                $lll= $kkk+1;
                $imgName = 'd'.$lll.'ImageName';
                unset($lArr[$imgName]);
            }

            $deleteJson = $lArr;
            //190106 adTitle 주석
            //unset($deleteJson['adsId'],$deleteJson['adTitle']);
            unset($deleteJson['adsId']);

            $lArr['deletejson'] = json_encode($deleteJson, JSON_UNESCAPED_UNICODE);

            //한번이라도 승인 된지 확인 하기 위해 넣음 181226
            //ads 업데이트 시 inspectDate 가 0000-00-00 ~~ 이 아닌 것이 있을때는
            //수정검토O 없을때는 신규검토X
            $lArr['inspectDate']    = $inspectDate;
            $lArr['dImageJson']    =   json_encode($dImageJson, JSON_UNESCAPED_UNICODE);

            $historyId = $this->setHistory($lArr);
        }

        $data2 = [
            'inspectDate' => $inspectDate
            ,'inspectUserId' => $data['users_id']
        ];

        switch ($data['type'])
        {
            case 1: //승인
                $historyMerge['adStatus'] = 2;
                $data2['status'] = 2;
                $historyMerge['isLive'] = 'Y';
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

        //d 이미지 무조건 제거
        for ($kkk=0; $kkk < 40; $kkk++)
        {
            $lll= $kkk+1;
            $imgName = 'd'.$lll.'ImageName';
            unset($historyMerge[$imgName]);
        }

        unset($historyMerge['dImageJson']);

        $historyMerge['adsHistoryJson'] = json_encode( array_merge( $historyMerge, $deleteJson), JSON_UNESCAPED_UNICODE);

        $unsetKeys = ['subHospitalId', 'adsId', 'userId', 'optionAdId',  'region' ,'cooperation', 'keyword', 't1ImageName', 't2ImageName'];
        foreach($unsetKeys as $key)
        {
            unset($historyMerge[$key]);
        }

        //$historyMerge['modDate'] = $data['confirmDate']; //ads의 수정일을 업데이트하지 않는다.

        $this->master->where('id', $adOriInfo['id'])->update('ads', $historyMerge);

        $this->master->where('id', $data['inspectId']);

        $this->master->update('inspecting_ads', $data2);
        $result = $this->master->affected_rows();

        //ads_main_map update 1 승인, 2 반려, 3 종료, 4 바로승인, 5 바로종료
        if(in_array($data['type'], [1,4]))
        {
            //동일 광고의 메인을 전부 미노출로 변경하고
            $this->master->where('adsId', $adOriInfo['id']);
            $this->master->update('ads_main_map', ['isMain'=>2]);

            //해당 row만 노출로 변경
            $this->master->where('id', $adOriInfo['adsMainMapId']);
            $this->master->update('ads_main_map', ['isMain'=>1]);
        }

        //최초 생성인지 판단, ads_file_history로 판단
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
        if($data['type'] == 1 or $data['type'] == 4)
        {
            //$adsInfo = $this->getAdsInfo($data);
            //$this->ads_m->make_template(['adsId' =>  $adOriInfo['id']], $date);
        }

        return $result;
    }

    /**
     * 이벤트 등록, 메인 등록, 히스토리 등록, 이미지까지만 진행
     * @param $data
     * @return mixed
     */
    function setEvent($data)
    {
        //상세이미지 리셋, 노파심 코드
        for ($kkk=0; $kkk < 40; $kkk++)
        {
            $lll= $kkk+1;
            $imgName = 'd'.$lll.'ImageName';
            unset($data[$imgName]);
        }

        //계약명 가져오기
        $contArr = $this->master->get_where('contract', ['id' => $data['contractId']])->row_array();

        $adDetailInfo = json_encode([
            $data['buttonName'],
            $data['buttonLink'],
            $data['buttonType'],
            $data['buttonPhone'],
            $data['buttonColor'],
            $data['buttonNameColor']
        ]); //버튼관련

        $historyArr = $aArr = [
            'adTitle' => $data['adTitle'],
            'adStatus' => $data['adStatus'],
            'subAdStatus' => $data['subAdStatus'],
            'category' => $data['category'],
            'adStartDate' => $data['adStartDate'],
            'adEndDate' => $data['adEndDate'],
            'adDateExtend' => $data['adDateExtend'],
            'adType' => $data['adType'],
            'exposure' => $data['exposure'],
            'costType' => $data['costType'],
            'generalCost' => $data['generalCost'],
            'discountCost' => $data['discountCost'],
            'textCost' => $data['textCost'],
            'dbCost' => $data['dbCost'],
            'whereImage' => $data['whereImage'],
            'modelImageCount' => $data['modelImageCount'],
            'adDetailInfo' => $adDetailInfo,
            'regDate' => $data['regDate'],
            'modDate' => $data['modDate'],
            'contractId' => $data['contractId'],
            'contractName' => $contArr['title'],
            'contractOrderId' => $data['contractOrderId'],
            'hospitalId' => $data['hospitalId'],
            'hospitalType' => $data['hospitalType'],
            'agencyUserId' => $data['agencyUserId'],
            'isViewBoard' => $data['isViewBoard'],
            'deliberationCode' => $data['deliberationCode'],
            'customRanding' => $data['customRanding'],
            'custom1' => $data['custom1'],
            'custom2' => $data['custom2'],
            'custom3' => $data['custom3'],
            'searchable' => $data['searchable']
        ];

        $historyArr['subHospitalId'] = isset($data['subHospitalId']) ? $data['subHospitalId'] : null;
        $historyArr['isViewBoard'] = isset($data['isViewBoard']) ? $data['isViewBoard'] : null;
        $historyArr['optionAdId'] = isset($data['optionAdId']) ? $data['optionAdId'] : null;

        //히스토리용 정리
        unset($historyArr['adStatus']);
        unset($historyArr['subAdStatus']);
        unset($historyArr['modDate']);

        $aArr['dImageJson'] = $data['dImageJson'];

        $aArr['id'] = $data['id']; //이벤트 아이디 동일하게 유지

        $this->master->insert('ads', $aArr);
        $adsId = $this->master->insert_id();

        $adsMainMapId = $this->setAdsMain([
            'adsId'         => $data['id'],
            'adTitle'       => $data['adTitle'],
            'hospitalId'    => $data['hospitalId'],
            'agencyUserId'  => $data['agencyUserId'],
            'date'          => $data['regDate']
        ]);

        //히스토리 입력
        $hArr2 = [
            'adsId'=>$data['id'],
            'userId'=>$data['users_id'],
            'cooperation'=>$data['cooperation'],
            'region'=>$data['region'],
            'keyword'=>$data['keyword'],
            't1ImageName'=>$data['t1ImageName'],
            't2ImageName'=>$data['t2ImageName'],
            'regDate'=>$data['regDate']
        ];

        $lArr = array_merge($historyArr, $hArr2);

        //상세이미지 리셋
        for ($kkk=0; $kkk < 40; $kkk++)
        {
            $lll= $kkk+1;
            $imgName = 'd'.$lll.'ImageName';
            unset($lArr[$imgName]);
        }

        unset($lArr['dImageJson']);

        $lArr['deleteJson'] = json_encode($lArr, JSON_UNESCAPED_UNICODE);
        $lArr['dImageJson'] = $data['dImageJson'];

        $historyId = $this->setHistory($lArr);
        //dd($lArr['deletejson']);
        //광고 JSON 업데이트
        $this->master->where('id', $adsId);
        $this->master->update('ads', ['adsHistoryJson' => $lArr['deleteJson'], 'dImageJson'=>$lArr['dImageJson']]);

        return ['adsId'=>$data['id'], 'adsMainMapId'=>$adsMainMapId, 'historyId'=>$historyId];
    }

    function setAds($data)
    {
        //계약명 가져오기
        $contArr = $this->master->get_where('contract', ['id'=>$data['contractId']])->row_array();

        //ads 입력
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


        //이미지 배열처리, 내용 있는 것만 배열에 담음
        $dImageArr = [];
        for($ccc=0;$ccc < 40;$ccc++)
        {
            $hhh = $ccc+1;
            $imgName = 'd'.$hhh.'ImageName';
            if(@$data[$imgName] != '')
            {
                $dImageArr[] = @$data[$imgName];
            }
        }

        $agencyUserId = $this->dataMigration_m->getAgencyUserId($data);

        $historyArr = $aArr = [
            'adTitle'=>$data['adTitle'],
            'adStatus'=> 4, //검토 1, 작성중 4
            'subAdStatus'=> 5, //3 신규등록검토 6, 어드민 작성중
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
            'regDate'=>$data['regDate'],
            'modDate'=>$data['modDate'],
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
            'custom3'=>$data['custom3'],
            'searchable' => $data['searchable']
        ];

        $historyArr['subHospitalId'] = isset( $data['subHospitalId'] ) ? $data['subHospitalId'] : null;
        $historyArr['isViewBoard']   = isset( $data['isViewBoard'] ) ? $data['isViewBoard'] : null;
        $historyArr['optionAdId']    = isset( $data['optionAdId'] ) ? $data['optionAdId'] : null;

        //히스토리용 정리
        unset($historyArr['adStatus']);
        unset($historyArr['subAdStatus']);
        unset($historyArr['modDate']);

        $aArr['dImageJson'] = json_encode($dImageArr, JSON_UNESCAPED_UNICODE);
        $tempArr = $historyArr;

        $this->master->insert('ads', $aArr);
        $adsId = $this->master->insert_id();

        $adsMainMapId = $this->setAdsMain([
            'adsId'         => $adsId,
            'adTitle'       => $data['adTitle'],
            'hospitalId'    => $data['hospitalId'],
            'agencyUserId'  => $agencyUserId,
            'date'          => $data['regDate']
        ]);

        //히스토리 입력
        $hArr2 = [
            'adsId'=>$adsId,
            'userId'=>$data['users_id'],
            'cooperation'=>$data['cooperation'],
            'region'=>$data['region'],
            'keyword'=>$data['keyword'],
            't1ImageName'=>$data['t1ImageName'],
            't2ImageName'=>$data['t2ImageName'],
            'regDate'=>$data['regDate']
        ];

        $lArr = array_merge($historyArr, $hArr2);

        //상세이미지 리셋
        for ($kkk=0; $kkk < 40; $kkk++)
        {
            $lll= $kkk+1;
            $imgName = 'd'.$lll.'ImageName';
            unset($lArr[$imgName]);
        }

        unset($lArr['dImageJson']);

        $lArr['deleteJson'] = json_encode($lArr, JSON_UNESCAPED_UNICODE);
        $lArr['dImageJson'] = json_encode($dImageArr, JSON_UNESCAPED_UNICODE); // 이미지 배열처리

        $historyId = $this->setHistory($lArr);

        //광고 JSON 업데이트
        $this->master->where('id', $adsId);
        $this->master->update('ads', ['adsHistoryJson' => $lArr['deleteJson'], 'dImageJson'=>$lArr['dImageJson']]);

        //검수타입 . 1 수정검토(O), 2 종료검토(O), 3 수정검토(X), 4 신규등록검토(X), 5 재등록(X)
        //이벤트 상태. 1 검토, 2 진행, 3 종료, 4 작성중, 5 반려
        //sub이벤트 상태. 1 수정검토, 2 종료검토, 3신규등록검토, 4 재등록 검토, 5 어드민작성중, 6 병원작성중
        //검수 등록
        $inspectId = $this->setInspectingAds([
            'date'          => $data['regDate'],
            'adStatus'      => 1, //라이브 수정검토
            'hospitalId'    => $data['hospitalId'],
            'prevAdStatus'   =>   4, //작성중
            'prevSubAdStatus' =>  5, //병원작성중 / 어드민작성중
            'historyId'     => $historyId,
            'adsId'         => $adsId,
            'users_id'      => $data['users_id'],
            'agencyUserId'  => $agencyUserId,
            'adsMainMapId'  => $adsMainMapId
        ]);

        return ['ads_id'=>$adsId, 'inspectId'=>$inspectId];
    }



    /**
     * ads_main, ads_main_map 등록
     * @param $data array
     * @return int
     */
    public function setAdsMain(array $data) : int
    {
        $return_arr = [];

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

        $isInspect = isset($data['isAdmin']) ? $data['isAdmin'] : 1;

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
     * @return int
     */
    public function setInspectingAds(array $data)
    {
        $isAdmin =  2; //사용자 작성한걸로 하는게 기회비용이 싸다. 19-05-03

        //검수 올라와 있는 동일 광고 취소 처리
        $this->master->where('adsId', $data['adsId']);
        $this->master->where('status', 1);
        $this->master->update('inspecting_ads', ['status' => 5, 'inspectUserId' => $data['users_id'], 'inspectDate' => $data['date']]);

        //검수 입력
        $insArr = [
            'status' => isset($data['status']) ? $data['status'] : 1, //검수대기
            'adStatus' => $data['adStatus'],
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
        $iId = $this->master->insert_id();

        return $iId;
    }

    /**
     * 기획전 등록
     * @param $data
     * @return bool|int
     * @throws Exception
     */
    function addPackage($data)
    {
        //ads_package insert
        $arr = $data;

        unset($arr['adsId']);

        $arr['userId'] = 1; //v1에 등록자 없음

        if($arr['viewType'] == 0)
        {
            $arr['status'] = 2;
        }
        else
        {
            $arr['status'] = 1;
        }

        $adsArr = explode(',', $data['adsId']);
        $arr['adsCount'] = count($adsArr);

        //노출위치 처리
        $bViewArr = explode(',', $data['bannerViewType']);

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
                'regDate'=>$arr['regDate'],
                'modDate'=>$arr['modDate'],
                'adsPackageId'=>$packageId,
                'adsId'=>$item,
                'order'=>$i
            ];
            $this->master->insert('ads_package_map', $mArr);
            $i++;
        }

        return $packageId;
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

        $data['userId']    = $data['users_id'];
        $tempId            = isset($data['tempId']) && !is_null($data['tempId']) ? $data['tempId'] : null;

        unset($data['tempId']);
        unset($data['token']);
        unset($data['users_id']);
        unset($data['menu_id']);
        unset($data['temporarySave']);

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

        if (count($result) == 0)
        {
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
        $data['adStatus'] = 4; //작성중
        $data['subAdStatus'] = 6; //병원작성중
        $data['isViewBoard'] = 1; //병원 1 고정
        $data['dImageJson']   = $data['dImageJson'];

        $this->master->where('id', $tempId)->update('ads_temporary', $data);

        return $tempId;
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

        $date = $date_ymd.' '.$date_time;

        $data['isViewBoard'] = 1;

        //히스토리 정보 머지
        $historyInfo = $this->gethistoryMerge($data);

        if (isset($historyInfo['dImageJson']))
        {
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
        unset(
            $data_diff['users_id'], $data_diff['menu_id'],
            $data_diff['token'], $data_diff['temporarySave']
        );

        $data_diff['userId']    = $data['users_id'];
        $data_diff['adsId']     = $data['adsId'];
        $data_diff['regDate']   = $data['regDate'];

        //상세이미지 리셋
        for ($kkk=0; $kkk < 40; $kkk++)
        {
            $lll= $kkk+1;
            $imgName = 'd'.$lll.'ImageName';
            unset($data_diff[$imgName]);
            unset($historyInfo[$imgName]);
        }

        unset($historyInfo['dImageJson']);
        unset($data_diff['dImageJson']);

        $data_diff['deletejson'] = json_encode($data_diff, JSON_UNESCAPED_UNICODE);
        $data_diff['dImageJson'] = $data['dImageJson'];

        //히스트로 등록
        $historyId = $this->setHistory($data_diff);

        //광고 JSON 업데이트
        $adsHistoryJson = json_encode( array_merge( $historyInfo, $data_diff),  JSON_UNESCAPED_UNICODE);
        $this->master->where('id', $data_diff['adsId'] );
        $this->master->update('ads', ['adsHistoryJson' => $adsHistoryJson, 'dImageJson'=>$data_diff['dImageJson'], 'modDate' => $data['modDate']]);

        $setAdTitle = isset( $data_diff['adTitle'])  ? $data_diff['adTitle'] : $data['adTitle'];
        $setHospitalId = isset( $data_diff['hospitalId'])  ? $data_diff['hospitalId'] : $data['hospitalId'];
        $setAgencyUserId = isset( $data_diff['agencyUserId'])  ? $data_diff['agencyUserId'] : $data['agencyUserId'];

        $adsMainMapId = $this->setAdsMain([
            'adsId'         => $data_diff['adsId'],
            'adTitle'       => $setAdTitle,
            'hospitalId'    => $setHospitalId,
            'agencyUserId'  => $setAgencyUserId,
            'date'          => $data['regDate']
        ]);

        //임시저장 삭제 추가
        $this->master->where('adsId', $data_diff['adsId'] );
        $this->master->update('ads_temporary', ['isDelete' => 1, 'deleteuserId' => $data['users_id'], 'delDate' => $data['modDate']]);

        return ['historyId'=>$historyId, 'adsMainMapId'=>$adsMainMapId];
    }

    /**
     * adsID 에 맞는 히스토리 정보를 뽑아서 병합 후 리턴
     * @param $data array
     * @return array
     */
    public function gethistoryMerge(array $data) : array
    {
        //히스토리 가져 와서 병합한다.
        $data['orderby'] = 'asc';
        $historys = $this->getHistoryListSec($data);

        //exit;
        $historyInfo = [];
        $dImageJson  = '';
        $hSize = sizeof($historys['info']) > 0 ? sizeof($historys['info']) - 1 : 0;
        foreach($historys['info'] as $key => $history)
        {
            $deleteArr = isset($history['deletejson']) ? json_decode($history['deletejson'], true) : [];
            //var_dump($deleteArr);
            $historyInfo = array_merge($historyInfo, $deleteArr);

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

        log_message('info', 'historyMerge3 : '.json_encode( [$historyInfo]));
        return $historyInfo;
    }

    /**
     * 히스토리 머지용
     *
     */
    function getHistoryListSec($data)
    {
        if ( isset($data['refreshToken']) )
        {
            unset($data['refreshToken']);
        }

        $param = [];

        $param['adsId']  = $data['adsId'];

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

        return $history;
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

        if ( isset($data['modDate']) )
        {
            unset($data['modDate']);
        }

        if ( isset($data['isLive']) )
        {
            unset($data['isLive']);
        }

        if ( isset($data['adStatus']) )
        {
            unset($data['adStatus']);
        }

        if ( isset($data['subAdStatus']) )
        {
            unset($data['subAdStatus']);
        }

        //상세이미지 리셋, 노파심에 또 추가
        for ($kkk=0; $kkk < 40; $kkk++)
        {
            $lll= $kkk+1;
            $imgName = 'd'.$lll.'ImageName';
            unset($data[$imgName]);
        }

        $this->master->insert('ads_history', $data);
        return $this->master->insert_id();
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

        $adDetailInfo = json_encode([
            '','','','','',''
        ]); //버튼관련

        $data['adsId'] = $data['id'];

        $agencyUserId = $this->dataMigration_m->getAgencyUserId($data);
        //$agencyUserId = 4;
        $TempArr = $aArr = [
            'adsId' => isset($data['adsId']) ? $data['adsId'] : null,
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
            'regDate'=>$data['regDate'],
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
            'adsId' => isset($data['adsId']) ? $data['adsId'] : null,
            'isDelete'  =>  isset($data['isDelete']) ? $data['isDelete'] : 2,
            'deleteUserId'  =>  isset($data['deleteUserId']) ? $data['deleteUserId'] : null,
            'userId'      => isset($data['users_id']) ? $data['users_id'] : null,
            'dImageJson' => $data['dImageJson']
        ];

        //히스토리용 정리
        unset($TempArr['modDate']);

        $this->master->insert('ads_temporary', $TempArr);
        $tempId = $this->master->insert_id();

        return $tempId;
    }

    /**
     * 재계약등록
     * @param $data
     * @return bool
     * @throws Exception
     */
    function setContractInfo($data)
    {
        //var_dump($data);
        unset($data['id']);
        $usersId = $data['users_id'] ;
        $adPrice = $data['adPrice'];
        $data['contractType']=2;

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

        unset($data['users_id']);

        //이전 계약번호
        $cntId = $data['contractId'];
        unset($data['contractId']);
        //이전 수주계약번호
        $cntOrderId = $data['contractOrderId'];
        unset($data['contractOrderId']);
        $data['parentId'] = $cntOrderId;

        //수주 테이블 입력
        $this->master->insert('contract_order', $data);
        $oIds = $this->master->insert_id();

        //재계약일 경우
        $cIds = $cntId;

        //매핑테이블 입력
        $this->master->insert('contract_order_connect', array('contractId'=>$cIds, 'contractOrderId'=>$oIds));
        $cocIds = $this->master->insert_id();

        //기존 계약여부 체크하여 잔액이 있다면 이월 처리한다. (원장 입력)
        $data2['contractId'] = $cIds;
        $data2['contractOrderId'] = $cntOrderId;
        $checkBal = $this->getBalancePrice($data2); //시점잔액을 따로 구해야할 수도 있음. 일단 해보고 ....

        //이월소진, 충전 처리. - 여부와 상관없이
        $dArr = array(
            'status'=>11, //이월소진
            'isMinus' => 1, //양수 0, 음수 1
            'contractId'=>$cIds,
            'contractOrderId'=>$cntOrderId,
            'usersId' => $usersId,
            //'memo'=>$memo,
            'price'=>$checkBal, //잔액
            'regDate'=>$data['regDate'],
            'modDate'=>$data['regDate']
        );
        $this->master->insert('deposit', $dArr);

        //$cIds, $oIds 에 해당하는 원장 입력(이월 충전)
        $dArr = array(
            'status'=>12, //이월충전
            'isMinus' => 0, //양수 0, 음수 1
            'contractId'=>$cIds,
            //'memo'=>$memo,
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
            $this->setContractOrderMemo($arr);
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
            $this->setContractOrderMemo($arr2);
        }

        //원장테이블 입력. 따로 처리
//        $dArr = array(
//            'status'=>1, //1 수주, 2 계약충전, 3 소진, 4 서비스충전, 5 결번충줜, 6 발행 환불, 7 계약 환불, 8 기타 소진
//            'isMinus' => ($adPrice > 0)? 0:1, //양수 0, 음수 1
//            'contractId'=>$cIds,
//            'contractOrderId'=>$oIds,
//            'usersId' => $usersId,
//            'price'=>$adPrice,
//            'regDate'=>$data['regDate'],
//            'modDate'=>$data['regDate']
//        );
//        $this->master->insert('deposit', $dArr);

        return $oIds;
    }

    /**
     * 입금확인 처리
     * @param $data
     * @return int
     */
    function depositConfirm($data)
    {
        $regDate = $data['regDate'];

        //수주계약금액 가져오기
        $orderData = $this->getContractInfo($data);

        //현재 계약충전금액 가져오기
        $sumDate = $this->getSumCharge($data); //var_dump($sumDate);

        if($orderData['adPrice'] > $sumDate['price'])
        {
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
                $this->setContractOrderMemo($arr2);
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
                $this->setContractOrderMemo($arr2);
            }
        }

        $sumDate2 = $this->getSumCharge($data); //var_dump($sumDate); exit;

        return 200;
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
        $checkDb = $this->master;

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
     * 메모 입력
     * @param $data
     */
    function setContractOrderMemo($data)
    {
        $arr = [
            'memoType'=>$data['memoType'],
            'targetId'=>$data['targetId'],
            'targetId2'=>$data['targetId2'],
            'userId'=>$data['userId'],
            'memo'=>$data['memo'],
            'regDate'=>date("Y-m-d H:i:s")
        ];
        $this->master->insert('memo', $arr);
    }

    /**
     * 영업담당자 리턴
     * @param $data
     * @return bool
     */
    function getAgencyUserId($data)
    {
        $arr = array('id'=>$data['contractId']);

        $result = $this->master->get_where('contract', $arr)->row_array();

        if(count($result) > 0)
        {
            return $result['agencyUserId'];
        }
        else
        {
            return 6;
        }
    }

    /**
     * ads 테이블 vContractOrderId 값 수정을 위한 함수
     * @param $contractId
     * @param $contractOrderId
     */
    function setVColumn($contractId, $contractOrderId)
    {
        //ads v 접두어 필드 업데이트를 위한 절차
        //1. ads_history 테이블의 deleteJson에 contractOrderId를 json화 하여 ads번호와 함께 insert
        //2. getHistoryMerge 함수로 해당 내용을 가져온다
        //3. 가져온 json 값rlwhs을 ads 테이블의 adsHistoryJson 항목에 업데이트 한다.
        $adsArr = $this->master->select('id, hospitalId')->get_where('ads', ['contractId'=>$contractId, 'contractOrderId'=>$contractOrderId])->result_array();
        log_message('info', json_encode($adsArr));

        if(count($adsArr) > 0)
        {
            foreach ($adsArr as $item)
            {
                //1
                $iArr9 = [
                    'adsId'=> $item['id'], 'contractOrderId'=>$contractOrderId, 'deletejson'=>json_encode(['contractOrderId'=>$contractOrderId])
                ];
                $this->master->insert('ads_history', $iArr9);

                //2
                $param = [];

                $param['adsId'] = $item['id'];
                log_message('info', 'setv : '.json_encode($param));
                $historyResult = $this->gethistoryMerge($param);

                //34
                $uArr = [
                    'adsHistoryJson'=>json_encode($historyResult)
                ] ;
                $this->master->where('id', $item['id']);
                $this->master->update('ads', $uArr);
            }
        }
    }
}