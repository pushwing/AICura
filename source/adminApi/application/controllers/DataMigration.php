<?php

use Aws\S3\S3Client;

defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Class DataMigration
 * v1 -> v2 data migration
 */

class DataMigration extends CI_Controller
{
    function __construct()
    {
        parent::__construct();

        //$this->v1 = $this->load->database('goodocStg', true); //v1 스테이징
        $this->v1 = $this->load->database('goodoc', true); //v1 운영
        //$this->master = $this->load->database('master2', true); //v2 개발
        //$this->master = $this->load->database('goodocV2Stg', true); //v2 스테이징db
        $this->master = $this->load->database('goodocV2', true); //v2 운영

        //영업담당자 배열
        $this->mArr = [
            '50316'=>'6',
            '54531'=>'5',
            '60385'=>'7',
            '69709'=>'8',
            '127876'=>'10',
            '242494'=>'9'
        ];

        $this->pType = [
            "신규계약충전"=>0,
            "계약 충전"=>1,
            "서비스 충전"=>2,
            "디비유입사용"=>3,
            "중복 DB 서비스 충전"=>4,
            "기타 충전"=>5,
            "기타 차감"=>6,
            "클라이언트 충전"=>7,
            "CPM 차감"=>8,
            "환불"=>9
        ];

        $this->load->model('dataMigration_m');


        exit; echo '11';
    }

    /**
     * paymet_type=0 이 없는 데이터 수정
     * 계약번호 121, 144, 389, 894
     *
     * 콘솔에서 마이그레이션 실행시 ssh 접속 끊어지고 프로세스 죽는걸 방지하기 위해 백그라운드(&)로 실행하고
     * 실행직후 disown -h 명령어를 실행한다.
     */

    /**
     * 마이그레이션용 테이블 비우기
     *
    truncate table contract;
    truncate table contract_order;
    truncate table contract_order_connect;
    truncate table call_request;
    truncate table call_memo;
    truncate table deposit;
    truncate table payment;
    truncate table ads;
    truncate table ads_history;
    truncate table ads_main;
    truncate table ads_main_map;
    truncate table ads_temporary;
    truncate table inspecting_ads;
    truncate table memo;
    truncate table ads_history_memo;
     */

    /**
     * payment_type=0 보다 다른 타입이 먼저 있는 건을 수정하는 프로그램
     * 마이그레이션 실행전에 v1 운영 db에서 실행해야 함
     */
    function checkPaymentType()
    {
        //333번 계약 체크

        $sql = "select * from payments p1
                where p1.payment_type=0 
                group by p1.contract_id";
        $result = $this->v1->query($sql)->result_array();

        foreach ($result as $item)
        {
            //echo '계약번호 '.$item['id'].'<br>';
            $sql2 = "select min(created_at) mins from payments where payment_type in (2,3,4,5,6,7,9) and contract_id='".$item['contract_id']."'";
            $result2 = $this->v1->query($sql2)->row_array();
            //$cnt = count($result2);
            //echo $item['created_at'] .'--'. $result2['mins'].'<br>';
            if(($item['created_at'] > $result2['mins']) and !is_null($result2['mins']))
            {
                $date = date('Y-m-d H:i:s', strtotime('-1 days', strtotime($result2['mins'])));
                echo $item['contract_id'].'--'.$result2['mins'].'--'.$date.'<br>';


                $this->v1->where('id', $item['id']);
                $this->v1->update('payments', ['created_at'=>$date]);
            }
        }
    }

    /**
     * 계약 생성하고 이벤트 매핑은 그 이후로. event_contracts
     * auto_increment 해제
     * 이벤트 매핑하면서 main 테이블 연 작업
     * @throws Exception
     */
    function contractProcess2()
    {
        $this->load->library('session');
        //계약 히스토리가 없어서 현재 잔액을 기준으로 계약을 생성한다.
        //잔액은 신청으로 잔액을 맞춘다. (또는 이전 신청내역을 볼 수 있는 메뉴를 따로 준비하고 v2는 새로 시작한다)

        //새기준 : 살아있는 계약을 구한다(영구종료나 폐업이 아닌)
        //19.1.1 00:00:00 이전의 신규 및 재계약건은 신규계약생성(계약금 0)하고 이월충전으로 잔액을 넘긴다. payment_type=0 기준(최초 입금, 계약생성일로 봄) payCount=1인 계약임
        //19.1.1 00:00:00 이후의 신규 및 재계약건은 신규계약생성하고 재계약처리로 기존프로세스와 동일하게 처리, payCount=0
        //19.1.31 테디, 헨도, 탑, 곤, 티, 마틴 회의 결과 반영

//        "신규계약충전"=>0,
//            "계약 충전"=>1,
//            "서비스 충전"=>2,
//            "디비유입사용"=>3,
//            "중복 DB 서비스 충전"=>4,
//            "기타 충전"=>5,
//            "기타 차감"=>6,
//            "클라이언트 충전"=>7,
//            "CPM 차감"=>8,
//            "환불"=>9
        // + 0 1 2 4 5 7
        // - 3 6 8 9

        //병원번호 없는 계약 뺀다.

        set_time_limit(0);
        ini_set('memory_limit','-1');

        echo 'start time - '.date("Y-m-d H:i:s").'<br><br>';

        $sql = "SELECT `c`.*, `h`.`id` `hospital_id2`, `h`.`name`, `u`.`id` `user_id`, `u`.`username`, `u`.`email`, 
        `ct`.`hospital_phone`, `ct`.`hospital_phone`, `ct`.`user_username`, `ct`.`user_phone`, `ct`.`user_email`,
        group_concat(distinct hc.hospital_id) as network_id,
        ui.agencyCompanyName as uAgencyCompanyName, ui.agencyCompanyChargeName as uAgencyCompanyChargeName
        , ui.agencyCompanyChargePhone as uAgencyCompanyChargePhone, ui.agencyCompanyChargeEmail as uAgencyCompanyChargeEmail 
        , ui.agencyCompanyFeeRate as uAgencyCompanyFeeRate,  
        ui.hospitalChargeName as uHospitalChargeName,
        ui.hospitalChargePhone as uHospitalChargePhone, ui.hospitalChargeEmail as uHospitalChargeEmail, 
        ui.taxChargeName as uTaxChargeName, ui.taxChargeEmail as uTaxChargeEmail, ui.taxBusinessNo as uTaxBusinessNo,
        ui.manageUserId as uManageUserId 
        -- ,(select hospital_id from hospital_contracts where contract_id=c.id limit 1) as hospital_id2
        -- , (select name from hospitails where id=hc.hospital_id) as hid2
        FROM `contracts` `c`
        LEFT JOIN `hospital_contracts` `hc` ON `c`.`id`=`hc`.`contract_id`
        LEFT JOIN `hospitals` `h` ON `hc`.`hospital_id`=`h`.`id`
        LEFT JOIN `user_hospital_departments` `uhd` ON `hc`.`hospital_id`=`uhd`.`hospital_id`
        LEFT JOIN `users` `u` ON `uhd`.`user_id`=`u`.`id`
        LEFT JOIN `contacts` `ct` ON `u`.`id`=`ct`.`user_id`
        LEFT JOIN `updateInfo` `ui` ON `c`.`id`=`ui`.`contractId`
        WHERE `c`.`title` NOT LIKE '%영구종료%' ESCAPE '!'
        and c.is_visible=1 
        and c.id != 1
        -- and c.id in(137,144)
        
        GROUP BY `c`.`id`
        having hospital_id2 is not null
        ";

        $adsIds2 = $this->v1->query($sql)->result_array(); //var_dump($adsIds2); exit;

        foreach ($adsIds2 as $item)
        {
            echo $item['id'].'-'.$item['contract_manager_id'].' start!!<br>';

            //기본 데이터 선언
            if($item['is_cpm'] == 1)
            {
                $adType2 = 4;
            }
            else
            {
                $adType2 = 1;
            }

            //contract_manager_id 이 값이 없는 계약도 존재함
            $contract_manager_id = ($item['contract_manager_id'])? $item['contract_manager_id'] : 50316;
            $agencyId = $this->mArr[$contract_manager_id];

            $data['hospitalType'] = 1; //이벤트신청으로 고
            $data['id'] = $item['id'];
            $data['hospitalId'] = $item['hospital_id2'];
            $data['hospitalName'] = $item['name'];
            //$data['contractDate'] = date("Y-m-d H:i:s", strtotime("-9 hours" , strtotime($item['created_at'])));
            $data['contractType'] = 1; //신규로 넘긴다.
            $data['title'] = $item['title'];
            $data['adType'] = 1; //
            $data['adType2'] = $adType2;

            $data['agencyUserId'] = $data['manageUserId'] = ($item['uManageUserId'])? $item['uManageUserId'] : $agencyId;

            $data['taxChargeName'] = ($item['uTaxChargeName'])? $item['uTaxChargeName'] : $item['user_username'];
            $data['taxBusinessNo'] = ($item['uTaxBusinessNo'])? $item['uTaxBusinessNo'] : '';
            $data['taxChargeEmail'] = ($item['uTaxChargeEmail'])? $item['uTaxChargeEmail'] : $item['user_email'];

            $data['hospitalChargeName'] = ($item['uHospitalChargeName'])? $item['uHospitalChargeName'] : $item['user_username'];
            $data['hospitalChargePhone'] = ($item['uHospitalChargePhone'])? $item['uHospitalChargePhone'] : $item['user_phone'];
            $data['hospitalChargeEmail'] = ($item['uHospitalChargeEmail'])? $item['uHospitalChargeEmail'] : $item['user_email'];
            $data['taxIssueRequestDate'] = '';
            $data['agencyCompanyChargeName'] = ($item['uAgencyCompanyChargeName'])? $item['uAgencyCompanyChargeName'] : '';
            $data['agencyCompanyChargePhone'] = ($item['uAgencyCompanyChargePhone'])? $item['uAgencyCompanyChargePhone'] : '';
            $data['agencyCompanyChargeEmail'] = ($item['uAgencyCompanyChargeEmail'])? $item['uAgencyCompanyChargeEmail'] : '';

            $data['agencyCompanyId'] = '';
            $data['agencyCompanyName'] = ($item['uAgencyCompanyName'])? $item['uAgencyCompanyName'] : '';
            $data['agencyCompanyFeeRate'] = ($item['uAgencyCompanyFeeRate'])? $item['uAgencyCompanyFeeRate'] : '';

            //네트워크 처리
            $nArr = explode(',', $item['network_id']);
            $nCount = count($nArr);
            $data['isNetwork'] = ($nCount > 1)? 1: 0; //일반 0, 네트워크 모병원 1, 네트워크자병원 2. 자병원인지 판단할 근거가 없다.
            //기본 데이터 선언

            //payment -> deposit
            //모든 상태를 가져와서 분기처리한다.
            //id는 순서가 안 맞음. 등록일로 정렬한다.
            $sql999 = "select p.*, cr.user_id from payments p
              left join call_requests_back cr on p.call_request_id=cr.id  
              where p.contract_id='".$item['id']."' 
              order by created_at
              "; //echo $sql999; order by payment_type, created_at
            $pArr = $this->v1->query($sql999)->result_array();
            //var_dump($pArr);
            //exit;

            $contractId = '';
            $contractOrderId = '';

            foreach ($pArr as $it)
            {
                $data2 = $data3 = $data4 = $data5 = [];

                //모든 날짜는 payments 테이블에서 가져온다
                $data['contractDate'] = $data['regDate'] = $data['taxIssueRequestDate'] = $data['taxIssueDate'] = date("Y-m-d H:i:s", strtotime("-9 hours" , strtotime($it['created_at'])));

                if($it['payment_type'] == 0)
                {
                    //계약 및 수주계약 생성처리 (payment_type=0 무조건 처리) 세금계산서 발행요청 및 발급 처리
                    echo '계약 -';
                    $data['adPrice'] = is_null($it['price'])? 0 : round($it['price']); //payments 금액

                    $cArr = $this->dataMigration_m->setContractOrder2($data);

                    echo $cArr['contractId'].' 수주계약'.$cArr['contractOrderId'].' 생성<br>';

                    $this->session->set_flashdata('contractId', $cArr['contractId']);
                    $this->session->set_flashdata('contractOrderId', $cArr['contractOrderId']);

                    $this->contractId = $cArr['contractId'];
                    $this->contractOrderId = $cArr['contractOrderId'];

                    //계좌생성
                    $data5['hospitalId'] = $data['hospitalId'];
                    $data5['contractId'] = $cArr['contractId'];
                    $data5['contractOrderId'] = $cArr['contractOrderId'];
                    $data5['type'] = 1; //가상계좌
                    $data5['hospitalName'] = $data['hospitalName'];
                    $data5['amount'] = round($it['price'] * 1.1); //입금시 세금 붙은 상태로 계산함
                    $data5['userId'] = 1;
                    $data5['transDate'] = '20191231235959';
                    $data5['regDate'] = date("Y-m-d H:i:s", strtotime("-9 hours" , strtotime($it['created_at'])));
                    $data5['applyDate'] = date("Y-m-d H:i:s", strtotime("-9 hours" , strtotime($it['updated_at'])));
                    $data5['result1'] = 'P_VACCT_NO=100000000000|P_EXP_DT=20191231235959';
                    $data5['result2'] = '20191231235959';
                    $data5['resultCode'] = '0021';
                    $data5['transNo'] = 'SBank_VBNKmid_test0020190220184342797420';
                    $data5['fnCode1'] = '04';
                    $data5['fnCode2'] = 'kb';
                    $data5['authDate'] = '20190220184420';
                    $this->master->insert('payment', $data5);

                    //수주계약금 세금 분리 충전(입금)처리, 수주계약 입금일 업데이트
                    $data2['id'] = $item['id'];
                    $data2['adPrice'] = round($it['price'] * 1.1);
                    $data2['contractOrderId'] = $cArr['contractOrderId'];
                    $data2['regDate'] = date("Y-m-d H:i:s", strtotime("-9 hours" , strtotime($item['created_at'])));
                    $data2['memo'] = ($it['memo'])? $it['memo'] : '';
                    $this->dataMigration_m->chargeContractOrder3($data2);
                }

                if($it['payment_type'] == 1)
                {
                    //재계약 생성(세금계산서 발행요청 및 발급 처리 추가), 원장 처리는 아래에서
                    echo '이전 계약 - '.$this->session->flashdata('contractId').' 이전 수주번호 - '.$this->session->flashdata('contractOrderId').'<br>';
                    echo ' 수주계약- ';
                    $data3 = $data;
                    $data3['contractId'] = $this->session->flashdata('contractId');
                    $data3['contractOrderId'] = $this->session->flashdata('contractOrderId');
                    $data3['adPrice'] = is_null($it['price'])? 0 : round($it['price']);
                    $data3['contractDate'] = $data3['taxIssueRequestDate'] = $data3['taxIssueDate'] = $data3['regDate'] = date("Y-m-d H:i:s", strtotime("-9 hours" , strtotime($it['created_at'])));
                    $data3['users_id']=1;
                    $contractOrderIdNew = $this->dataMigration_m->setContractInfo($data3);

                    echo $contractOrderIdNew.' 생성<br>';

                    //수주번호 갱신
                    $this->session->set_flashdata('contractOrderId', $contractOrderIdNew);
                    $this->contractOrderId = $contractOrderIdNew;

                    //계좌생성
                    $data5['hospitalId'] = $data['hospitalId'];
                    $data5['contractId'] = $this->session->flashdata('contractId');
                    $data5['contractOrderId'] = $this->session->flashdata('contractOrderId');
                    $data5['type'] = 1; //가상계좌
                    $data5['hospitalName'] = $data['hospitalName'];
                    $data5['amount'] = round($it['price'] * 1.1); //입금시 세금 붙은 상태로 계산함
                    $data5['userId'] = 1;
                    $data5['transDate'] = '20191231235959';
                    $data5['regDate'] = date("Y-m-d H:i:s", strtotime("-9 hours" , strtotime($it['created_at'])));
                    $data5['applyDate'] = date("Y-m-d H:i:s", strtotime("-9 hours" , strtotime($it['created_at'])));
                    $data5['result1'] = 'P_VACCT_NO=100000000000|P_EXP_DT=20191231235959';
                    $data5['result2'] = '20191231235959';
                    $data5['resultCode'] = '0021';
                    $data5['transNo'] = 'SBank_VBNKmid_test0020190220184342797420';
                    $data5['fnCode1'] = '04';
                    $data5['fnCode2'] = 'kb';
                    $data5['authDate'] = '20190220184420';
                    $this->master->insert('payment', $data5);

                    //수주계약금 세금 분리 충전(입금)처리, 수주계약 입금일 업데이트
                    $data2['id'] = $item['id'];
                    $data2['adPrice'] = round($it['price'] * 1.1);
                    $data2['contractOrderId'] = $contractOrderIdNew;
                    $data2['regDate'] = date("Y-m-d H:i:s", strtotime("-9 hours" , strtotime($it['created_at'])));
                    $data2['memo'] = ($it['memo'])? $it['memo'] : '';
                    $this->dataMigration_m->chargeContractOrder3($data2);
                }

                if(!in_array($it['payment_type'], [0,1]))
                {
                    //0, 1 이 아닌 나머지 케이스
                    //echo ' 원장 - '.$it['payment_type'].' 입력<br>';
                    //echo ' 수주번호 - '.$this->session->flashdata('contractOrderId').'<br>';

                    $status = $this->paymentType($it['payment_type']);
                    $iArr = [
                        'status'=>$status['status'],
                        'isMinus'=>$status['minus'],
                        'contractId'=>$data['id'],
                        'contractOrderId'=>$this->contractOrderId,
                        'usersId'=>1,
                        'memo'=>$it['memo'],
                        'price'=>is_null($it['price'])? 0 : $it['price'],
                        'regDate'=>date("Y-m-d H:i:s", strtotime("-9 hours" , strtotime($it['created_at']))),
                        'modDate'=>date("Y-m-d H:i:s", strtotime("-9 hours" , strtotime($it['updated_at']))),
                        'callRequestId'=>($it['call_request_id'])?$it['call_request_id']:0
                    ]   ;
                    $this->master->insert('deposit', $iArr);
                }

            }

            echo $item['id'].' end!!<br>';
            //sleep('0.3');

            echo 'end time - '.date("Y-m-d H:i:s").'<br><br>';
        }
    }

    /**
     * total_ready_price 테이블 입력 작업 실시
     * php index.php cronDaily readyPrice
     */

    /**
     * 이벤트 마이그레이션 전에 실행한다.
     * 진행중이 아닌 이벤트중에 카테고리가 2개이상 매핑된것을 지운다
     * 대상 카테고리 1132개.
     */
    function deleteCategory()
    {
        $sql = "
            select events.id, event_event_categories.`event_category_id` e1, client_event_event_categories.`event_category_id` e2, count(*) cnt from events 
            LEFT outer JOIN `event_event_categories` ON `event_event_categories`.`event_id` = `events`.`id`
            LEFT outer JOIN `client_event_event_categories` ON `client_event_event_categories`.`event_id` = `events`.`id`
            where current_status != 1 
            group by events.id
            having cnt > 1
        ";
        $result = $this->v1->query($sql)->result_array();

        foreach ($result as $item)
        {
            $this->v1->where('event_id', $item['id'])->delete('event_event_categories');
            $this->v1->where('event_id', $item['id'])->delete('client_event_event_categories');
        }
    }

    /**
     * 이벤트 입력
     * 네트워크병원 부분을 옵션이벤트로 넣는다. 19.4.12 - v1에서 네트워크 병원이 작동하지 않기 때문에 v2에 넣어봐야 소용이 없다.
     */
    function eventProcess()
    {
        /**
         * EVENT_STATUS  이전 상태임.
         *
         * EVENT_STATUS = [
        "공백", #0
        "수정중", #1
        "검토중", #2
        "진행중", #3
        "게시대기중", #4
        "종료", #5
        "수정중", #6
        "반려" #7
        ]

        *
         *   EVENT_TYPE_STR = [
        "공백",
        "일반이벤트",
        "프로모션",
        "CPM이벤트",
        "급여이벤트"
        ]

        EVENT_TYPE = {
        "전체" => 0,
        "CPA" => 1,
        "프로모션" => 2,
        "CPM" => 3,
        "CPC" => 4
        }
         *

         * 현재 이벤트 상태
         * current_status = [
        "공백", #0
        "O 진행", #1
        "O 병원 수정중", #2
        "O 어드민 수정중", #3
        "O 수정 검토 요청", #4
        "O 종료 검토 요청", #5
        "O 반려", #6
        "X 종료", #7
        "X 병원 수정중", #8
        "X 최초 등록중", #9
        "X 어드민 수정 중", #10
        "X 최초 등록 검토 요청", #11
        "X 비라이브 종료 요청(미사용)", #12
        "X 수정 검토 요청", #13
        "X 반려", #14
        "X 삭제 대기" #15
        ]
         */
        set_time_limit(0);
        ini_set('memory_limit','-1');

        //더미이벤트 거르기. 19.03.08 오전 회의 확인 https://goodoc.atlassian.net/wiki/spaces/goodoc/pages/672564178
        //1. 네트워크 이벤트중 이미지1,2가 없는 번호 추출 <- 안가져올 리스트
        //2. 옵션이벤트 번호 추출.
        //3. 1번의 번호에서 겹치는 2번의 번호를 뺀다
        //4. 3의 결과를 쿼리에서 빼고 데이터를 가져온다
        // 19.3.11 최종. v1에서 네트워크병원 및 옵션이벤트를 옵션이벤트로 만들어 사용중. 옵션이벤트중 어디에도 노출되지 않고 이미지가 없는 것을 뺀다.

//        $nSql= " select group_concat(distinct e.id) as notArr
// from events e
// join event_options eo on e.id=eo.option_event_id
//  where
//   (e.image is null or e.image2 is null )
//   and e.current_status != 15
//   and (e.`is_visible_on_event_list` =0 and `is_visible_on_hospital_show` = 0)
//";   // 빼고 간다. 4. 16. 이미지 없으면 더미이미지 넣어서 처리.
        //$nArr = $this->v1->query($nSql)->row_array();

        $this->v1->query("set @@group_concat_max_len = 50240");

        //계약중 종료된거 제외
        $nSql = "select group_concat(distinct id) as notArr from contracts where is_visible=2";
        $nArr = $this->v1->query($nSql)->row_array();

        //카테고리 아예 없는거 나옴
        $nSql2 = "
        select group_concat(events.id) as notArr from events
            
            LEFT JOIN `event_event_categories` ON `event_event_categories`.`event_id` = `events`.`id`
            LEFT JOIN `client_event_event_categories` ON `client_event_event_categories`.`event_id` = `events`.`id`     
            where event_event_categories.event_category_id is null and client_event_event_categories.event_category_id is null
        ";

        $nArr2 = $this->v1->query($nSql2)->row_array();

        //$noArr = $nArr['notArr'].','.$nArr2['notArr'];
        $noArr = $nArr2['notArr'];

        //var_dump($nArr2); exit;

        //이벤트 및 부수 테이블 한꺼번에 가져와서 처리
        //1 = 13215, 4 = 13191, 5=12993, 6=12932, 2=12902, 7=13133, 14=13228, 8=12522, 11=13245
        $sql = "
            SELECT group_concat(distinct emem.external_media_category_id) cooperationId, group_concat(distinct miem.model_image_category_id) modelId, 
group_concat(distinct cemem.external_media_category_id) cooperationId2, group_concat(distinct cmiem.model_image_category_id) modelId2, 
group_concat(distinct er.region_id) regionId, group_concat(distinct eo.option_event_id) optionId, group_concat(distinct st.tag) keywords,group_concat(distinct cst.tag) keywords2, events.*, hospitals.name as hospital_name, hospitals.addr as hospital_addr, hospitals.latitude as hospital_latitude, hospitals.longitude as hospital_longitude , event_event_categories.`event_category_id`, evc.contract_id, client_event_event_categories.`event_category_id` as client_event_category_id
            FROM `events` 
            left JOIN `hospitals` ON `hospitals`.`id` = `events`.`hospital_id` 
            LEFT outer JOIN `event_event_categories` ON `event_event_categories`.`event_id` = `events`.`id`
            LEFT outer JOIN `client_event_event_categories` ON `client_event_event_categories`.`event_id` = `events`.`id`              
            LEFT JOIN `event_search_tags` est ON `events`.`id` = est.`event_id`
            left join search_tags st on est.`search_tag_id`=st.id
            LEFT JOIN `event_options` eo ON `events`.`id` = eo.`event_id`
            LEFT JOIN `event_regions` er ON `events`.`id` = er.`event_id`
            LEFT JOIN `model_image_event_maps` miem ON `events`.`id` = miem.`event_id`
            LEFT JOIN `client_model_image_event_maps` cmiem ON `events`.`id` = cmiem.`event_id`
            LEFT JOIN `external_media_event_maps` emem ON `events`.`id` = emem.`event_id`
            LEFT JOIN `client_external_media_event_maps` cemem ON `events`.`id` = cemem.`event_id`
            join event_contracts evc on events.id=evc.event_id
            LEFT JOIN `event_client_search_tags` cest ON `events`.`id` = cest.`event_id`
            left join client_search_tags cst on cest.client_search_tag_id=cst.id
            WHERE 
            events.event_type=1 -- sevice_v2_tft 방에 핀한 내용 참고. 이벤트만 가져오고 나머지는 v1에서 관리. 추후 다시 마이그레이션한다. with tee. 2019.2.20
            -- and events.current_status in (2,4,5,6,7,8,9,10,11,13,14) 
            -- and events.current_status = 1 
            AND 
            `events`.`is_deleted` = 0 -- AND `events`.`is_visible_on_event_list` = 1 
            and events.id not in(".$noArr.")
            and evc.contract_id not in(".$nArr['notArr'].")
           --  and events.id != 9461
            -- and events.id = 11834
            group by events.id
            order by events.id
        "; //echo $sql; exit;
        //제휴, 모델, 키워드 양쪽값이 다를 경우 client의 데이터를 사용한다.
        //카테고리가 다르면 client 사용하고 client가 빈 값인 경우는 원데이터로.

        //echo $sql; exit;

        $pArr = $this->v1->query($sql)->result_array();

        if(count($pArr) == 0)
        {
            echo '대상 이벤트가 없다.'; exit;
        }

        foreach ($pArr as $item)
        {
            //is_temporary =1 이면 상시진행, 0이면 기간설정 이벤트

            //이벤트 입력, 모델포함
            $data['id'] = $item['id']; // 자동증가 풀어야함

            $data['hospitalId'] = $item['hospital_id'];
            $data['hospitalType'] = 1; //일반으로 고정

            if(is_null($item['optionId']))
            {
                $data['adType'] = 1;
            }
            else
            {
                $opCount = explode(',', $item['optionId']);

                if(count($opCount) > 0)
                {
                    $data['adType'] = 5;  //옵션이벤ㅌ,
                }
                else
                {
                    $data['adType'] = 1; //cpa
                }
            }

            echo "이벤트번호 : ".$data['id']."<BR><BR>";
            echo "카테고리번호 : ".$item['event_category_id'].'--'.$item['client_event_category_id']."<BR><BR>";

            //진행중이면 둘다 데이터가 있다. 히스토리 등록이 필요함
            //4, 13 수정검토 요청은 로직이 다르다. 현재 데이터로 이벤트 생성하고 승인처리, 이전 데이터로 이벤트 생성후 승인요청
            if(in_array($item['current_status'], [1,2,3,5,11,9]))  //4,6 제거, 14도 제거. 반대임
            {
                $data['adStartDate'] = $item['client_start_on'];
                $data['adEndDate'] = $item['client_end_on'];
                $data['adDateExtend'] = ($item['client_is_temporary'] == 1)? 'Y':'N';
                $data['adTitle'] = $item['client_title'];
                if($item['client_is_numerical_discounted_price'] == 0)
                {
                    //텍스트단가
                    $costType = 2;
                    $discountCost = '';
                    $generalCost = '';
                    $textCost = $item['client_discounted_price'];
                }
                else
                {
                    $costType = 1;
                    $discountCost = $item['client_numerical_discounted_price'];
                    $generalCost = $item['client_numerical_original_price'];
                    $textCost = '';
                }
                $t1ImageName = $item['client_image']; //리스트
                $t2ImageName = $item['client_image2']; //정방향
                $data['deliberationCode'] = $item['client_consider_number'];
                $client=1;
                $category = ($item['client_event_category_id'])?$item['client_event_category_id']:$item['event_category_id'];
            }
            else
            {
                $data['adStartDate'] = $item['start_on'];
                $data['adEndDate'] = $item['end_on'];
                $data['adDateExtend'] = ($item['is_temporary'] == 1)? 'Y':'N';
                $data['adTitle'] = $item['title'];
                if($item['is_numerical_discounted_price'] == 0)
                {
                    //텍스트단가
                    $costType = 2;
                    $discountCost = '';
                    $generalCost = '';
                    $textCost = $item['discounted_price'];
                }
                else
                {
                    $costType = 1;
                    $discountCost = $item['numerical_discounted_price'];
                    $generalCost = $item['numerical_original_price'];
                    $textCost = '';
                }
                $t1ImageName = $item['image']; //리스트
                $t2ImageName = $item['image2']; //정방향
                $data['deliberationCode'] = $item['consider_number'];
                $client=0;
                //$category = $item['event_category_id'];
                $category = ($item['client_event_category_id'])?$item['client_event_category_id']:$item['event_category_id'];
            }

            //정상인데 이미지가 없다면 리스트를 정방향으로 복사하고 둘다 없다면 더미이미지를 넣는다.
            if(in_array($item['current_status'], [1,2,3,4,5,6]) and !isset($t2ImageName))
            {
                $t2ImageName = $t1ImageName;
            }

            echo "리스트 : ".$t1ImageName."<BR><BR>";
            echo "정방향 : ".$t2ImageName."<BR><BR>";


            $data['costType'] = $costType;
            $data['dbCost'] = $item['event_cost'];
            $data['generalCost'] = $generalCost;
            $data['discountCost'] = $discountCost;
            $data['category'] = $category;

            if($item['is_visible_on_event_list'] == 1 and $item['is_visible_on_hospital_show']==1)
            {
                $exposure = 3;
            }
            else if($item['is_visible_on_event_list'] == 1 and $item['is_visible_on_hospital_show']==0)
            {
                $exposure = 1;
            }
            else if($item['is_visible_on_event_list'] == 0 and $item['is_visible_on_hospital_show']==1)
            {
                $exposure = 2;
            }
            else
            {
                $exposure = 4;
            }
            $data['exposure'] = $exposure; //1 이벤트, 2 병원상세, 3 둘다, 4 미노출

            $data['contractId'] = $item['contract_id'];
            $data['regDate'] = date("Y-m-d H:i:s", strtotime("-9 hours" , strtotime($item['created_at'])));
            $data['modDate'] = date("Y-m-d H:i:s", strtotime("-9 hours" , strtotime($item['updated_at']))); // -9 해서 넣어야

            $contractOrderId = $this->dataMigration_m->getContractOrderId($item['contract_id']);
            $contractOrderId = ($contractOrderId == '')? '2':$contractOrderId ; //실제 컨버팅시 주석처리. 이벤트 테스트용

            $data['contractOrderId'] = $contractOrderId;
            $data['isViewBoard'] = 1;
            $data['subHospitalId'] = '';
            $data['textCost'] = $textCost;
            $data['optionAdId'] = is_null($item['optionId'])? '':$item['optionId'];
            $data['region'] = is_null($item['regionId'])? '':$item['regionId'];
            $data['cooperation'] = ($item['cooperationId2'])? $item['cooperationId2']:$item['cooperationId'];
            $data['keyword'] = ($item['keywords2'])? $item['keywords2']:$item['keywords'];
            $data['whereImage'] = ($item['modelId2'])? $item['modelId2']:$item['modelId'];
            $data['modelImageCount'] = $item['apply_image_count'];
            $data['buttonName'] = ($item['apply_text'])? $item['apply_text']:'';
            $data['buttonLink'] = ($item['promotion_url'])? $item['promotion_url']:'';
            $data['buttonType'] = ($item['is_show_phonecall'])? '1':'2';
            $data['buttonPhone'] = ($item['phone_number'])? $item['phone_number']:'';
            $data['buttonNameColor'] = ($item['apply_text_color'])? $item['apply_text_color']:'';
            $data['buttonColor'] = ($item['apply_back_color'])? $item['apply_back_color']:'';

            $data['customRanding'] = '';
            $data['custom1'] = '';
            $data['custom2'] = '';
            $data['custom3'] = '';
            $data['searchable'] = $item['searchable'];
            $data['agencyUserId'] = $this->dataMigration_m->getAgencyUserId($data);

            //정상이벤트인 경우는 로직이 다르다, 있는 이미지를 복사
            if(in_array($item['current_status'], [1,2,3,4,5,6]))
            {
                if($t1ImageName and $t2ImageName)
                {
                    $t1Image = $this->imageProcess(1, $t1ImageName, $item['id'], $client);
                    $t2Image = $this->imageProcess(2, $t2ImageName, $item['id'], $client);
                }
                else if($t1ImageName and !isset($t2ImageName))
                {
                    $t1Image = $this->imageProcess(1, $t1ImageName, $item['id'], $client);
                    $t2Image = $t1Image;
                }
                else  if($t2ImageName and !isset($t1ImageName))
                {

                    $t2Image = $this->imageProcess(2, $t2ImageName, $item['id'], $client);
                    $t1Image = $t2Image;
                }
                else  if(!isset($t2ImageName) and !isset($t1ImageName))
                {
                    $t1Image = 'http://asset.goodoc.kr/images/event/common/t2-s.png';
                    $t2Image = 'http://asset.goodoc.kr/images/event/common/t1-s.png'; //둘다 더미주소
                }

            }
            else
            {
                if($t1ImageName and $t2ImageName)
                {
                    $t1Image = $this->imageProcess(1, $t1ImageName, $item['id'], $client);
                    $t2Image = $this->imageProcess(2, $t2ImageName, $item['id'], $client);
                }
                else if($t1ImageName and !isset($t2ImageName))
                {
                    $t1Image = $this->imageProcess(1, $t1ImageName, $item['id'], $client);
                    $t2Image = 'http://asset.goodoc.kr/images/event/common/t1-s.png'; //s2 정방향 더미주소
                }
                else  if($t2ImageName and !isset($t1ImageName))
                {
                    $t1Image = 'http://asset.goodoc.kr/images/event/common/t2-s.png'; //s3 리스트 더미주소
                    $t2Image = $this->imageProcess(2, $t2ImageName, $item['id'], $client);
                }
                else  if(!isset($t2ImageName) and !isset($t1ImageName))
                {
                    $t1Image = 'http://asset.goodoc.kr/images/event/common/t2-s.png';
                    $t2Image = 'http://asset.goodoc.kr/images/event/common/t1-s.png'; //둘다 더미주소
                }

            }

            $data['t1ImageName'] = $t1Image; //리스트
            $data['t2ImageName'] = $t2Image; //정방향

            //상세이미지 갯수만큼 리셋
            for ($kkk=0; $kkk < 40; $kkk++)
            {
                $lll= $kkk+1;
                $imgName = 'd'.$lll.'ImageName';
                unset($data[$imgName]);
            }

            $dImageArr = [];

            if($item['event_type'] == 3)
            {
                //cpm이면 이미지가 없다
                $dImageArr[] = 'http://asset.goodoc.kr/images/event/common/d1-s.png'; //더미 상세
            }
            else
            {
                $eInfos = $this->dataMigration_m->getImage($item['id']); //var_dump($eInfos); exit;
                $cnt = count($eInfos);
                echo '상세이미지수 : '.$cnt.'<br><br>';

                if($cnt > 0)
                {
                    for ($i=0; $i < $cnt; $i++)
                    {
                        $j=$i+1;
                        $name = 'd'.$j.'ImageName';
                        if(in_array($item['current_status'], [1,2,3,5,14,11,9]))
                        {
                            if($eInfos[$i]['client_image'])
                            {
                                $dImageArr[] = $this->imageProcess(3, $eInfos[$i]['client_image'], $eInfos[$i]['id'], 1);
                            }
                        }
                        else
                        {
                            if($eInfos[$i]['image'])
                            {
                                $dImageArr[] = $this->imageProcess(3, $eInfos[$i]['image'], $eInfos[$i]['id'], 0);
                            }
                        }
                    }
                }
            }

            //상세이미지 json화
            $data['dImageJson'] = json_encode($dImageArr, JSON_UNESCAPED_UNICODE);

            //유저번호 강제할당
            $data['users_id'] = 1;

            /**
             * 작업순서
             *
             * % 데이터가 둘다 있을 경우
             * 1. client 데이터로 ads를 등록한다. (히스토리, main, 검수까지 등록됨)
             * 2. 검수처리(진행)
             * 3. 원래 데이터로 ads update를 한다. (검수, 히스토리 등록됨)
             * 4. current_status에 따라 반려, 진행, 종료 처리를 한다.
             *
             * % client 데이터만 있는 경우
             * 1. 임시저장 처리한다.
             *
             * 모델을 복사하여 리플리케이터를 제거하고 사용한다.
             */

            //상태에 따라 검수완료 및 진행 등 추가 처리
            //1-6은 라이브, 7-14는 비라이브
            //검수타입 . 1 수정검토(O), 2 종료검토(O), 3 수정검토(X), 4 신규등록검토(X), 5 재등록(X)
            //이벤트 상태. 1 검토, 2 진행, 3 종료, 4 작성중, 5 반려
            //sub이벤트 상태. 1 수정검토, 2 종료검토, 3신규등록검토, 4 재등록 검토, 5 어드민작성중, 6 병원작성중
           switch ($item['current_status'])
            {
                case 1: //진행중
                    $data['isLive'] = 'Y';
                    $data['adStatus'] = 2;
                    $data['subAdStatus'] = '';
                    $insStatus= '1';
                    break;
                case 2: //병원수정
                    $data['isLive'] = 'Y';
                    $data['adStatus'] = 4;
                    $data['subAdStatus'] = 6;
                    $insStatus= '';
                    break;
                case 3: //어드민 수정
                    $data['isLive'] = 'Y';
                    $data['adStatus'] = 4;
                    $data['subAdStatus'] = 5;
                    $insStatus= '';
                    break;
                case 4: //수정검토요청
                    $data['isLive'] = 'Y';
                    $data['adStatus'] = 1;
                    $data['subAdStatus'] = 1;
                    $insStatus= '1';
                    break;
                case 5: //종료검토요청
                    $data['isLive'] = 'Y';
                    $data['adStatus'] = 1;
                    $data['subAdStatus'] = 2;
                    $insStatus= '2'; //검수타입 . 1 수정검토(O), 2 종료검토(O), 3 수정검토(X), 4 신규등록검토(X), 5 재등록(X). - 3번 사용안함. 5번으로 대체
                    break;
                case 6: //반려
                    $data['isLive'] = 'Y';
                    $data['adStatus'] = 5;
                    $data['subAdStatus'] = '';
                    $insStatus= '';
                    break;
                case 7: //x 종료
                    $data['isLive'] = 'N';
                    $data['adStatus'] = 3;
                    $data['subAdStatus'] = '';
                    $insStatus= '4';
                    break;
                case 8: //x 병원수정중
                    $data['isLive'] = 'N';
                    $data['adStatus'] = 4;
                    $data['subAdStatus'] = 6;
                    $insStatus= '4';
                    break;
                case 9: //x 최초 등록중, 상태 없어서 어드민수정중과 동일하게 사용, 임시저장 처리
                    $data['isLive'] = 'N';
                    $data['adStatus'] = 4;
                    $data['subAdStatus'] = 5;
                    $insStatus= '';
                    break;
                case 10: //x어드민수정중
                    $data['isLive'] = 'N';
                    $data['adStatus'] = 4;
                    $data['subAdStatus'] = 5;
                    $insStatus= '4';
                    break;
                case 11: //x최초 등록검토 요청
                    $data['isLive'] = 'N';
                    $data['adStatus'] = 1;
                    $data['subAdStatus'] = 3;
                    $insStatus= '4';
                    break;
                case 13: //x 수정검토요청
                    $data['isLive'] = 'N';
                    $data['adStatus'] = 1;
                    $data['subAdStatus'] = 4;
                    $insStatus= '5'; //v2에선 재등록검토로 처리. 3에서 5로 변경. BIZ-1496
                    break;
                case 14: //x 반려
                    $data['isLive'] = 'N';
                    $data['adStatus'] = 5;
                    $data['subAdStatus'] = '';
                    $insStatus= '4';
                    break;

                    //12는 사용안함, 15는 삭제함
            }

            var_dump($data); echo '<br><br>';
            //데이터 체크, 클라만 있는지(200) 둘다 있는지(400)
            $dataCheck = $this->dataCheck($item);
            echo 'data = '.$dataCheck.'<br><br>';

            //어드민 작성중 버림. 3, 10. 현재 표현할 길이 없음. 어드민 작성중은 데이터 마이그레이션전에 모두 정리한다.
            //4 로직 분리
            if(in_array($item['current_status'], [1,5]))
            {
                //검수전까지 등록
                $result2 = $this->dataMigration_m->setEvent($data);

                //검수등록
                $inspectId = $this->dataMigration_m->setInspectingAds([
                    'date'          => $data['modDate'],
                    'adStatus'      => 4,//x 신규등록검토
                    'hospitalId'    => $data['hospitalId'],
                    'prevAdStatus'   =>   $data['adStatus'], //작성중
                    'prevSubAdStatus' =>  $data['subAdStatus'], // 어드민작성중
                    'historyId'     => $result2['historyId'],
                    'adsId'         => $result2['adsId'],
                    'users_id'      => 1,
                    'agencyUserId'  => $data['agencyUserId'],
                    'adsMainMapId'  => $result2['adsMainMapId']
                ]);

//                echo 'ins1 id:'.$inspectId.'<br><br>';
//                sleep(1);
//                echo '1 second later <br><br>';

                //검수(바로승인)
                $ins['inspectId'] = $inspectId;
                $ins['type'] = 4;
                $ins['users_id']=1;
                $ins['confirmDate'] = date("Y-m-d H:i:s", strtotime("-9 hours" , strtotime($item['confirmed_at'])));
                $this->dataMigration_m->updateInspectInfo($ins);


                //원데이터로 재등록 및 검수등록, 검수완료처리 필요
                //원데이터 선언
                $data['adStartDate'] = $item['start_on'];
                $data['adEndDate'] = $item['end_on'];
                $data['adDateExtend'] = ($item['is_temporary'] == 1)? 'Y':'N';
                $data['adTitle'] = $item['title'];
                if($item['is_numerical_discounted_price'] == 0)
                {
                    //텍스트단가
                    $costType = 2;
                    $discountCost = '';
                    $generalCost = '';
                    $textCost = $item['discounted_price'];
                }
                else
                {
                    $costType = 1;
                    $discountCost = $item['numerical_discounted_price'];
                    $generalCost = $item['numerical_original_price'];
                    $textCost = '';
                }
                $t1ImageName = $item['image']; //리스트
                $t2ImageName = $item['image2']; //정방향

                //정상인데 이미지가 없다면 리스트를 정방향으로 복사하고 둘다 없다면 더미이미지를 넣는다.
                if(!isset($t2ImageName))
                {
                    $t2ImageName = $t1ImageName;
                }

                $data['costType'] = $costType;
                $data['dbCost'] = $item['event_cost'];
                $data['generalCost'] = $generalCost;
                $data['discountCost'] = $discountCost;
                $data['textCost'] = $textCost;

                //원데이터로 이미지 다시 등록
                //$t1Image = $this->imageProcess(1, $t1ImageName, $item['id']);
                //$t2Image = $this->imageProcess(2, $t2ImageName, $item['id']);

                $client = 0; //현 데이터로 변경
                //정상용 로직
                if($t1ImageName and $t2ImageName)
                {
                    $t1Image = $this->imageProcess(1, $t1ImageName, $item['id'], $client);
                    $t2Image = $this->imageProcess(2, $t2ImageName, $item['id'], $client);
                }
                else if($t1ImageName and !isset($t2ImageName))
                {
                    $t1Image = $this->imageProcess(1, $t1ImageName, $item['id'], $client);
                    $t2Image = $this->imageProcess(1, $t1ImageName, $item['id'], $client);
                }
                else  if($t2ImageName and !isset($t1ImageName))
                {
                    $t1Image = $this->imageProcess(2, $t2ImageName, $item['id'], $client);
                    $t2Image = $this->imageProcess(2, $t2ImageName, $item['id'], $client);
                }
                else  if(!isset($t2ImageName) and !isset($t1ImageName))
                {
                    $t1Image = 'http://asset.goodoc.kr/images/event/common/t2-s.png';
                    $t2Image = 'http://asset.goodoc.kr/images/event/common/t1-s.png'; //둘다 더미주소
                }

                $data['t1ImageName'] = $t1Image; //리스트
                $data['t2ImageName'] = $t2Image; //정방향
                $data['deliberationCode'] = $item['consider_number'];

                //상세이미지 갯수만큼 리셋
                for ($kkk=0; $kkk < 40; $kkk++)
                {
                    $lll= $kkk+1;
                    $imgName = 'd'.$lll.'ImageName';
                    unset($data[$imgName]);
                }

                //원 이미지 재할당
                $eInfos = $this->dataMigration_m->getImage($item['id']);
                $cnt2 = count($eInfos);

                $dImageArr = [];
                for ($i=0; $i < $cnt2; $i++)
                {
                    if($eInfos[$i]['image'])
                    {
                        $dImageArr[] = $this->imageProcess(3, $eInfos[$i]['image'], $eInfos[$i]['id'], 0);
                    }
                }

                $uData = $data; //업데이트용 데이터 할당
                $uData['dImageJson'] = json_encode($dImageArr, JSON_UNESCAPED_UNICODE);
                $uData['adsId'] = $uData['id'];
                unset($uData['id']);

                //원데이터로 검수 등록전까지 진행 -> 업데이트로 변경해야함.
                $this->dataMigration_m->updateAdsTemp($uData);

                $result2 = $this->dataMigration_m->updateAds($uData);

                //검수등록, 두번째는 current_status에 맞게 검수타입 등록 $insStatus
                //검수타입 . 1 수정검토(O), 2 종료검토(O), 3 수정검토(X), 4 신규등록검토(X), 5 재등록(X)
                $inspectId20 = $this->dataMigration_m->setInspectingAds([
                    'date'          => $data['regDate'],
                    'adStatus'      => $insStatus,
                    'hospitalId'    => $data['hospitalId'],
                    'prevAdStatus'   =>   $data['adStatus'], //진행중
                    'prevSubAdStatus' =>  $data['subAdStatus'], // 어드민작성중
                    'historyId'     => $result2['historyId'],
                    'adsId'         => $data['id'], //이벤트 아이디는 변경없음
                    'users_id'      => 1,
                    'agencyUserId'  => $data['agencyUserId'],
                    'adsMainMapId'  => $result2['adsMainMapId']
                ]);

                //echo 'ins2 id:'.$inspectId20.'<br><br>';

                //5는 검수요청이라 아무 처리도 안한다.
                if($item['current_status'] ==  1)
                {
                    //echo '<br><br>------'.$inspectId20.'----<br><br>';
                    //검수(바로승인)
                    $ins2['inspectId'] = $inspectId20;
                    $ins2['type'] = 4;
                    $ins2['users_id']=1;
                    $ins2['confirmDate'] = date("Y-m-d H:i:s", strtotime("-9 hours" , strtotime($item['confirmed_at'])));
                    //var_dump($ins2);
                    $this->dataMigration_m->updateInspectInfo($ins2);
                }

                //ads adStatus 원상태화 - 검수시 2로 변경됨
                $this->master->where('id', $uData['adsId']);
                $this->master->update('ads', ['adStatus'=>$data['adStatus']]);
            }
            else if(in_array($item['current_status'], [2])) //병원 작성중
            {
                //검수전까지 등록
                $result2 = $this->dataMigration_m->setEvent($data);

                //검수등록
                $inspectId = $this->dataMigration_m->setInspectingAds([
                    'date'          => $data['modDate'],
                    'adStatus'      => 4,//x 신규등록검토
                    'hospitalId'    => $data['hospitalId'],
                    'prevAdStatus'   =>   $data['adStatus'], //작성중
                    'prevSubAdStatus' =>  $data['subAdStatus'], // 어드민작성중
                    'historyId'     => $result2['historyId'],
                    'adsId'         => $data['id'],
                    'users_id'      => 1,
                    'agencyUserId'  => $data['agencyUserId'],
                    'adsMainMapId'  => $result2['adsMainMapId']
                ]);

                //검수(바로승인)
                $ins['inspectId'] = $inspectId;
                $ins['type'] = 4;
                $ins['users_id']=1;
                $ins['confirmDate'] = date("Y-m-d H:i:s", strtotime("-9 hours" , strtotime($item['confirmed_at'])));
                $this->dataMigration_m->updateInspectInfo($ins);

                //원데이터로 재등록 및 검수등록, 검수완료처리 필요

                //원데이터 선언
                $data['adStartDate'] = $item['start_on'];
                $data['adEndDate'] = $item['end_on'];
                $data['adDateExtend'] = ($item['is_temporary'] == 1)? 'Y':'N';
                $data['adTitle'] = $item['title'];
                if($item['is_numerical_discounted_price'] == 0)
                {
                    //텍스트단가
                    $costType = 2;
                    $discountCost = '';
                    $generalCost = '';
                    $textCost = $item['discounted_price'];
                }
                else
                {
                    $costType = 1;
                    $discountCost = $item['numerical_discounted_price'];
                    $generalCost = $item['numerical_original_price'];
                    $textCost = '';
                }
                $t1ImageName = $item['image']; //리스트
                $t2ImageName = $item['image2']; //정방향

                //정상인데 이미지가 없다면 리스트를 정방향으로 복사하고 둘다 없다면 더미이미지를 넣는다.
                if(!isset($t2ImageName))
                {
                    $t2ImageName = $t1ImageName;
                }

                $data['costType'] = $costType;
                $data['dbCost'] = $item['event_cost'];
                $data['generalCost'] = $generalCost;
                $data['discountCost'] = $discountCost;
                $data['textCost'] = $textCost;

                //원데이터로 이미지 다시 등록
                $client = 0; //현 데이터로 변경
                //정상용 로직
                if($t1ImageName and $t2ImageName)
                {
                    $t1Image = $this->imageProcess(1, $t1ImageName, $item['id'], $client);
                    $t2Image = $this->imageProcess(2, $t2ImageName, $item['id'], $client);
                }
                else if($t1ImageName and !isset($t2ImageName))
                {
                    $t1Image = $this->imageProcess(1, $t1ImageName, $item['id'], $client);
                    $t2Image = $t1Image;
                }
                else  if($t2ImageName and !isset($t1ImageName))
                {
                    $t2Image = $this->imageProcess(2, $t2ImageName, $item['id'], $client);
                    $t1Image = $t2Image;
                }
                else  if(!isset($t2ImageName) and !isset($t1ImageName))
                {
                    $t1Image = 'http://asset.goodoc.kr/images/event/common/t2-s.png';
                    $t2Image = 'http://asset.goodoc.kr/images/event/common/t1-s.png'; //둘다 더미주소
                }

                $data['t1ImageName'] = $t1Image; //리스트
                $data['t2ImageName'] = $t2Image; //정방향
                $data['deliberationCode'] = $item['consider_number'];

                //상세이미지 리셋
                for ($kkk=0; $kkk < 40; $kkk++)
                {
                    $lll= $kkk+1;
                    $imgName = 'd'.$lll.'ImageName';
                    unset($data[$imgName]);
                }

                //원 이미지 재할당
                $eInfos = $this->dataMigration_m->getImage($item['id']);
                $cnt3 = count($eInfos);

                $dImageArr = [];
                for ($i=0; $i < $cnt3; $i++)
                {
                    if($eInfos[$i]['image'])
                    {
                        $dImageArr[] = $this->imageProcess(3, $eInfos[$i]['image'], $eInfos[$i]['id'], 0);
                    }
                }

                $data['dImageJson'] = json_encode($dImageArr, JSON_UNESCAPED_UNICODE);

                //작성중은 검수에 넣지 않고 임시저장 처리한다.
                $this->dataMigration_m->setAdsTemp($data);

                //ads adStatus 원상태화 - 검수시 2로 변경됨
                $this->master->where('id', $result2['adsId']);
                $this->master->update('ads', ['adStatus'=>$data['adStatus']]);
            }
            else if(in_array($item['current_status'], [7]) or $item['hospital_id'] == '')
            {
                //current_status 7 종료처리, 병원아이디가 없는 이벤트도 종료처리한다.
                if($dataCheck == 200)
                {
                    //클라이언트데이터만 존재할 경우

                    //검수전까지 등록
                    $result2 = $this->dataMigration_m->setEvent($data);

                    //검수등록
                    //검수타입 . 1 수정검토(O), 2 종료검토(O), 3 수정검토(X), 4 신규등록검토(X), 5 재등록(X)
                    $inspectId = $this->dataMigration_m->setInspectingAds([
                        'date'          => $data['modDate'],
                        'adStatus'      => $insStatus,
                        'hospitalId'    => $data['hospitalId'],
                        'prevAdStatus'   =>   $data['adStatus'],
                        'prevSubAdStatus' =>  $data['subAdStatus'],
                        'historyId'     => $result2['historyId'],
                        'adsId'         => $data['id'],
                        'users_id'      => 1,
                        'agencyUserId'  => $data['agencyUserId'],
                        'adsMainMapId'  => $result2['adsMainMapId']
                    ]);

                    //검수(바로종료)
                    $ins['inspectId'] = $inspectId;
                    $ins['type'] = 5;
                    $ins['users_id']=1;
                    $ins['confirmDate'] = date("Y-m-d H:i:s", strtotime("-9 hours" , strtotime($item['confirmed_at'])));
                    $this->dataMigration_m->updateInspectInfo($ins);
                }
                else if($dataCheck == 400)
                {
                    //두 데이터 모두 존재할 경우

                    //검수전까지 등록
                    $result2 = $this->dataMigration_m->setEvent($data);

                    //검수등록
                    //검수타입 . 1 수정검토(O), 2 종료검토(O), 3 수정검토(X), 4 신규등록검토(X), 5 재등록(X)
                    $inspectId = $this->dataMigration_m->setInspectingAds([
                        'date'          => $data['modDate'],
                        'adStatus'      => $insStatus,
                        'hospitalId'    => $data['hospitalId'],
                        'prevAdStatus'   =>   $data['adStatus'], //작성중
                        'prevSubAdStatus' =>  $data['subAdStatus'], // 어드민작성중
                        'historyId'     => $result2['historyId'],
                        'adsId'         => $data['id'],
                        'users_id'      => 1,
                        'agencyUserId'  => $data['agencyUserId'],
                        'adsMainMapId'  => $result2['adsMainMapId']
                    ]);

                    //검수(바로종료)
                    $ins['inspectId'] = $inspectId;
                    $ins['type'] = 5;
                    $ins['users_id']=1;
                    $ins['confirmDate'] = date("Y-m-d H:i:s", strtotime("-9 hours" , strtotime($item['confirmed_at'])));
                    $this->dataMigration_m->updateInspectInfo($ins);

                    //원데이터 선언
                    $data['adStartDate'] = $item['start_on'];
                    $data['adEndDate'] = $item['end_on'];
                    $data['adDateExtend'] = ($item['is_temporary'] == 1)? 'Y':'N';
                    $data['adTitle'] = $item['title'];
                    if($item['is_numerical_discounted_price'] == 0)
                    {
                        //텍스트단가
                        $costType = 2;
                        $discountCost = '';
                        $generalCost = '';
                        $textCost = $item['discounted_price'];
                    }
                    else
                    {
                        $costType = 1;
                        $discountCost = $item['numerical_discounted_price'];
                        $generalCost = $item['numerical_original_price'];
                        $textCost = '';
                    }
                    $t1ImageName = $item['image']; //리스트
                    $t2ImageName = $item['image2']; //정방향

                    $data['costType'] = $costType;
                    $data['dbCost'] = $item['event_cost'];
                    $data['generalCost'] = $generalCost;
                    $data['discountCost'] = $discountCost;
                    $data['textCost'] = $textCost;

                    //원데이터로 이미지 다시 등록
                    //$t1Image = $this->imageProcess(1, $t1ImageName, $item['id']);
                    //$t2Image = $this->imageProcess(2, $t2ImageName, $item['id']);
                    $client = 0; //현 데이터로 변경
                    if($t1ImageName and $t2ImageName)
                    {
                        $t1Image = $this->imageProcess(1, $t1ImageName, $item['id'], $client);
                        $t2Image = $this->imageProcess(2, $t2ImageName, $item['id'], $client);
                    }
                    else if($t1ImageName and !isset($t2ImageName))
                    {
                        $t1Image = $this->imageProcess(1, $t1ImageName, $item['id'], $client);
                        $t2Image = 'http://asset.goodoc.kr/images/event/common/t1-s.png'; //s2 정방향 더미주소
                    }
                    else  if($t2ImageName and !isset($t1ImageName))
                    {
                        $t1Image = 'http://asset.goodoc.kr/images/event/common/t2-s.png'; //s3 리스트 더미주소
                        $t2Image = $this->imageProcess(2, $t2ImageName, $item['id'], $client);
                    }
                    else  if(!isset($t2ImageName) and !isset($t1ImageName))
                    {
                        $t1Image = 'http://asset.goodoc.kr/images/event/common/t2-s.png';
                        $t2Image = 'http://asset.goodoc.kr/images/event/common/t1-s.png'; //둘다 더미주소
                    }

                    $data['t1ImageName'] = $t1Image; //리스트
                    $data['t2ImageName'] = $t2Image; //정방향
                    $data['deliberationCode'] = $item['consider_number'];

                    //상세이미지 리셋
                    for ($kkk=0; $kkk < 40; $kkk++)
                    {
                        $lll= $kkk+1;
                        $imgName = 'd'.$lll.'ImageName';
                        unset($data[$imgName]);
                    }

                    //원 이미지 재할당
                    $eInfos = $this->dataMigration_m->getImage($item['id']);
                    $cnt4 = count($eInfos);

                    $dImageArr = [];
                    for ($i=0; $i < $cnt4; $i++)
                    {
                        if($eInfos[$i]['image'])
                        {
                            $dImageArr[] = $this->imageProcess(3, $eInfos[$i]['image'], $eInfos[$i]['id'], 0);
                        }
                    }

                    $uData = $data; //업데이트용 데이터 할당
                    $uData['dImageJson'] = json_encode($dImageArr, JSON_UNESCAPED_UNICODE);
                    $uData['adsId'] = $uData['id'];
                    unset($uData['id']);

                    //원데이터로 검수 등록전까지 진행 -> 업데이트로 변경해야함.
                    $this->dataMigration_m->updateAdsTemp($uData);

                    $result2 = $this->dataMigration_m->updateAds($uData);

                    //검수등록
                    $inspectId = $this->dataMigration_m->setInspectingAds([
                        'date'          => $data['modDate'],
                        'adStatus'      => $insStatus,
                        'hospitalId'    => $data['hospitalId'],
                        'prevAdStatus'   =>   $data['adStatus'], //작성중
                        'prevSubAdStatus' =>  $data['subAdStatus'], // 어드민작성중
                        'historyId'     => $result2['historyId'],
                        'adsId'         => $data['id'],
                        'users_id'      => 1,
                        'agencyUserId'  => $data['agencyUserId'],
                        'adsMainMapId'  => $result2['adsMainMapId']
                    ]);

                    //검수(바로종료)
                    $ins['inspectId'] = $inspectId;
                    $ins['type'] = 5;
                    $ins['users_id']=1;
                    $ins['confirmDate'] = date("Y-m-d H:i:s", strtotime("-9 hours" , strtotime($item['confirmed_at'])));
                    $this->dataMigration_m->updateInspectInfo($ins);
                }
            }
            else if(in_array($item['current_status'], [14]) or $item['hospital_id'] == '')
            {
                if($dataCheck == 200)
                {
                    //클라이언트데이터만 존재할 경우 이 데이터만 넣고 종료->반려로 만듬
                    //원데이터 선언
                    $data['adStartDate'] = $item['client_start_on'];
                    $data['adEndDate'] = $item['client_end_on'];
                    $data['adDateExtend'] = ($item['client_is_temporary'] == 1)? 'Y':'N';
                    $data['adTitle'] = $item['client_title'];
                    if($item['client_is_numerical_discounted_price'] == 0)
                    {
                        //텍스트단가
                        $costType = 2;
                        $discountCost = '';
                        $generalCost = '';
                        $textCost = $item['client_discounted_price'];
                    }
                    else
                    {
                        $costType = 1;
                        $discountCost = $item['client_numerical_discounted_price'];
                        $generalCost = $item['client_numerical_original_price'];
                        $textCost = '';
                    }
                    $t1ImageName = $item['client_image']; //리스트
                    $t2ImageName = $item['client_image2']; //정방향

                    $data['costType'] = $costType;
                    $data['dbCost'] = $item['event_cost'];
                    $data['generalCost'] = $generalCost;
                    $data['discountCost'] = $discountCost;
                    $data['textCost'] = $textCost;

                    $client = 1; //현 데이터로 변경
                    if($t1ImageName and $t2ImageName)
                    {
                        $t1Image = $this->imageProcess(1, $t1ImageName, $item['id'], $client);
                        $t2Image = $this->imageProcess(2, $t2ImageName, $item['id'], $client);
                    }
                    else if($t1ImageName and !isset($t2ImageName))
                    {
                        $t1Image = $this->imageProcess(1, $t1ImageName, $item['id'], $client);
                        $t2Image = 'http://asset.goodoc.kr/images/event/common/t1-s.png'; //s2 정방향 더미주소
                    }
                    else  if($t2ImageName and !isset($t1ImageName))
                    {
                        $t1Image = 'http://asset.goodoc.kr/images/event/common/t2-s.png'; //s3 리스트 더미주소
                        $t2Image = $this->imageProcess(2, $t2ImageName, $item['id'], $client);
                    }
                    else  if(!isset($t2ImageName) and !isset($t1ImageName))
                    {
                        $t1Image = 'http://asset.goodoc.kr/images/event/common/t2-s.png';
                        $t2Image = 'http://asset.goodoc.kr/images/event/common/t1-s.png'; //둘다 더미주소
                    }

                    $data['t1ImageName'] = $t1Image; //리스트
                    $data['t2ImageName'] = $t2Image; //정방향
                    $data['deliberationCode'] = $item['client_consider_number'];

                    //상세이미지 리셋
                    for ($kkk=0; $kkk < 40; $kkk++)
                    {
                        $lll= $kkk+1;
                        $imgName = 'd'.$lll.'ImageName';
                        unset($data[$imgName]);
                    }

                    //원 이미지 재할당
                    $eInfos = $this->dataMigration_m->getImage($item['id']);
                    $cnt4 = count($eInfos);

                    $dImageArr = [];
                    for ($i=0; $i < $cnt4; $i++)
                    {
                        if($eInfos[$i]['client_image'])
                        {
                            $dImageArr[] = $this->imageProcess(3, $eInfos[$i]['client)image'], $eInfos[$i]['id'], 1);
                        }
                    }

                    $data['dImageJson'] = json_encode($dImageArr, JSON_UNESCAPED_UNICODE);

                    //검수전까지 등록
                    $result2 = $this->dataMigration_m->setEvent($data);

                    //검수등록
                    //검수타입 . 1 수정검토(O), 2 종료검토(O), 3 수정검토(X), 4 신규등록검토(X), 5 재등록(X)
                    $inspectId = $this->dataMigration_m->setInspectingAds([
                        'date'          => $data['modDate'],
                        'adStatus'      => $insStatus,
                        'hospitalId'    => $data['hospitalId'],
                        'prevAdStatus'   =>   $data['adStatus'],
                        'prevSubAdStatus' =>  $data['subAdStatus'],
                        'historyId'     => $result2['historyId'],
                        'adsId'         => $data['id'],
                        'users_id'      => 1,
                        'agencyUserId'  => $data['agencyUserId'],
                        'adsMainMapId'  => $result2['adsMainMapId']
                    ]);

                    //검수(반려)
                    $ins['inspectId'] = $inspectId;
                    $ins['type'] = 2;
                    $ins['users_id']=1;
                    $ins['reason']=$item['deny_message'];
                    $ins['rejectCode']='';
                    $ins['agencyUserReason']='';
                    $ins['confirmDate'] = date("Y-m-d H:i:s", strtotime("-9 hours" , strtotime($item['confirmed_at'])));
                    $this->dataMigration_m->updateInspectInfo($ins);
                }
                else if($dataCheck == 400)
                {
                    //두 데이터 모두 존재할 경우

                    //검수전까지 등록
                    $result2 = $this->dataMigration_m->setEvent($data);

                    //검수등록
                    //검수타입 . 1 수정검토(O), 2 종료검토(O), 3 수정검토(X), 4 신규등록검토(X), 5 재등록(X)
                    $inspectId = $this->dataMigration_m->setInspectingAds([
                        'date'          => $data['modDate'],
                        'adStatus'      => $insStatus,
                        'hospitalId'    => $data['hospitalId'],
                        'prevAdStatus'   =>   $data['adStatus'], //작성중
                        'prevSubAdStatus' =>  $data['subAdStatus'], // 어드민작성중
                        'historyId'     => $result2['historyId'],
                        'adsId'         => $data['id'],
                        'users_id'      => 1,
                        'agencyUserId'  => $data['agencyUserId'],
                        'adsMainMapId'  => $result2['adsMainMapId']
                    ]);

                    if(in_array($item['current_status'], [7,10]))
                    {
                        //검수(바로종료)
                        $ins['inspectId'] = $inspectId;
                        $ins['type'] = 5;
                        $ins['users_id']=1;
                        $ins['confirmDate'] = date("Y-m-d H:i:s", strtotime("-9 hours" , strtotime($item['confirmed_at'])));
                        $this->dataMigration_m->updateInspectInfo($ins);
                    }

                    //원데이터 선언
                    $data['adStartDate'] = $item['client_start_on'];
                    $data['adEndDate'] = $item['client_end_on'];
                    $data['adDateExtend'] = ($item['client_is_temporary'] == 1)? 'Y':'N';
                    $data['adTitle'] = $item['client_title'];
                    if($item['client_is_numerical_discounted_price'] == 0)
                    {
                        //텍스트단가
                        $costType = 2;
                        $discountCost = '';
                        $generalCost = '';
                        $textCost = $item['client_discounted_price'];
                    }
                    else
                    {
                        $costType = 1;
                        $discountCost = $item['client_numerical_discounted_price'];
                        $generalCost = $item['client_numerical_original_price'];
                        $textCost = '';
                    }
                    $t1ImageName = $item['client_image']; //리스트
                    $t2ImageName = $item['client_image2']; //정방향

                    $data['costType'] = $costType;
                    $data['dbCost'] = $item['event_cost'];
                    $data['generalCost'] = $generalCost;
                    $data['discountCost'] = $discountCost;
                    $data['textCost'] = $textCost;

                    //클라이언트 데이터로 이미지 다시 등록
                    $client = 1; //client 데이터로 변경
                    if($t1ImageName and $t2ImageName)
                    {
                        $t1Image = $this->imageProcess(1, $t1ImageName, $item['id'], $client);
                        $t2Image = $this->imageProcess(2, $t2ImageName, $item['id'], $client);
                    }
                    else if($t1ImageName and !isset($t2ImageName))
                    {
                        $t1Image = $this->imageProcess(1, $t1ImageName, $item['id'], $client);
                        $t2Image = 'http://asset.goodoc.kr/images/event/common/t1-s.png'; //s2 정방향 더미주소
                    }
                    else  if($t2ImageName and !isset($t1ImageName))
                    {
                        $t1Image = 'http://asset.goodoc.kr/images/event/common/t2-s.png'; //s3 리스트 더미주소
                        $t2Image = $this->imageProcess(2, $t2ImageName, $item['id'], $client);
                    }
                    else  if(!isset($t2ImageName) and !isset($t1ImageName))
                    {
                        $t1Image = 'http://asset.goodoc.kr/images/event/common/t2-s.png';
                        $t2Image = 'http://asset.goodoc.kr/images/event/common/t1-s.png'; //둘다 더미주소
                    }

                    $data['t1ImageName'] = $t1Image; //리스트
                    $data['t2ImageName'] = $t2Image; //정방향
                    $data['deliberationCode'] = $item['client_consider_number'];

                    //상세이미지 리셋
                    for ($kkk=0; $kkk < 40; $kkk++)
                    {
                        $lll= $kkk+1;
                        $imgName = 'd'.$lll.'ImageName';
                        unset($data[$imgName]);
                    }

                    //원 이미지 재할당
                    $eInfos = $this->dataMigration_m->getImage($item['id']);
                    $cnt4 = count($eInfos);

                    $dImageArr = [];
                    for ($i=0; $i < $cnt4; $i++)
                    {
                        if($eInfos[$i]['client_image'])
                        {
                            $dImageArr[] = $this->imageProcess(3, $eInfos[$i]['client_image'], $eInfos[$i]['id'], $client);
                        }
                    }

                    $uData = $data; //업데이트용 데이터 할당
                    $uData['dImageJson'] = json_encode($dImageArr, JSON_UNESCAPED_UNICODE);
                    $uData['adsId'] = $uData['id'];
                    unset($uData['id']);

                    //원데이터로 검수 등록전까지 진행 -> 업데이트로 변경해야함.
                    $this->dataMigration_m->updateAdsTemp($uData);

                    $result2 = $this->dataMigration_m->updateAds($uData);

                    //검수등록
                    $inspectId = $this->dataMigration_m->setInspectingAds([
                        'date'          => $data['modDate'],
                        'adStatus'      => $insStatus,
                        'hospitalId'    => $data['hospitalId'],
                        'prevAdStatus'   =>   $data['adStatus'], //작성중
                        'prevSubAdStatus' =>  $data['subAdStatus'], // 어드민작성중
                        'historyId'     => $result2['historyId'],
                        'adsId'         => $data['id'],
                        'users_id'      => 1,
                        'agencyUserId'  => $data['agencyUserId'],
                        'adsMainMapId'  => $result2['adsMainMapId']
                    ]);

                    if(in_array($item['current_status'], [7]))
                    {
                        //검수(바로종료)
                        $ins['inspectId'] = $inspectId;
                        $ins['type'] = 5;
                        $ins['users_id']=1;
                        $ins['confirmDate'] = date("Y-m-d H:i:s", strtotime("-9 hours" , strtotime($item['confirmed_at'])));
                        $this->dataMigration_m->updateInspectInfo($ins);
                    }
                    else if(in_array($item['current_status'], [14]))
                    {
                        //검수(반려)
                        $ins['inspectId'] = $inspectId;
                        $ins['type'] = 2;
                        $ins['users_id']=1;
                        $ins['reason']=$item['deny_message'];
                        $ins['rejectCode']='';
                        $ins['agencyUserReason']='';
                        $ins['confirmDate'] = date("Y-m-d H:i:s", strtotime("-9 hours" , strtotime($item['confirmed_at'])));
                        $this->dataMigration_m->updateInspectInfo($ins);
                    }
                }
            }
            else if(in_array($item['current_status'], [8])) //x 병원작성중, 원데이터 등록후 클라이언트 데이터 등록 필요
            {
                if($dataCheck == 200)
                {
                    //클라이언트데이터만 존재할 경우 임시저장만 함
                    $this->dataMigration_m->setAdsTemp($data);
                }
                else if($dataCheck == 400)
                {
                    //두 데이터 모두 존재할 경우

                    //검수전까지 등록
                    $result2 = $this->dataMigration_m->setEvent($data);

                    //검수등록
                    //검수타입 . 1 수정검토(O), 2 종료검토(O), 3 수정검토(X), 4 신규등록검토(X), 5 재등록(X)
                    $inspectId = $this->dataMigration_m->setInspectingAds([
                        'date'          => $data['modDate'],
                        'adStatus'      => $insStatus,
                        'hospitalId'    => $data['hospitalId'],
                        'prevAdStatus'   =>   $data['adStatus'], //작성중
                        'prevSubAdStatus' =>  $data['subAdStatus'], // 어드민작성중
                        'historyId'     => $result2['historyId'],
                        'adsId'         => $data['id'],
                        'users_id'      => 1,
                        'agencyUserId'  => $data['agencyUserId'],
                        'adsMainMapId'  => $result2['adsMainMapId']
                    ]);

                    //검수(바로종료)
                    $ins['inspectId'] = $inspectId;
                    $ins['type'] = 5;
                    $ins['users_id']=1;
                    $ins['confirmDate'] = date("Y-m-d H:i:s", strtotime("-9 hours" , strtotime($item['confirmed_at'])));
                    $this->dataMigration_m->updateInspectInfo($ins);

                    //클라이언트 데이터 선언
                    $data['adStartDate'] = $item['client_start_on'];
                    $data['adEndDate'] = $item['client_end_on'];
                    $data['adDateExtend'] = ($item['client_is_temporary'] == 1)? 'Y':'N';
                    $data['adTitle'] = $item['client_title'];
                    if($item['client_is_numerical_discounted_price'] == 0)
                    {
                        //텍스트단가
                        $costType = 2;
                        $discountCost = '';
                        $generalCost = '';
                        $textCost = $item['client_discounted_price'];
                    }
                    else
                    {
                        $costType = 1;
                        $discountCost = $item['client_numerical_discounted_price'];
                        $generalCost = $item['client_numerical_original_price'];
                        $textCost = '';
                    }
                    $t1ImageName = $item['client_image']; //리스트
                    $t2ImageName = $item['client_image2']; //정방향

                    $data['costType'] = $costType;
                    $data['dbCost'] = $item['event_cost'];
                    $data['generalCost'] = $generalCost;
                    $data['discountCost'] = $discountCost;
                    $data['textCost'] = $textCost;

                    //원데이터로 이미지 다시 등록
                    //$t1Image = $this->imageProcess(1, $t1ImageName, $item['id']);
                    //$t2Image = $this->imageProcess(2, $t2ImageName, $item['id']);
                    $client = 0; //현 데이터로 변경
                    if($t1ImageName and $t2ImageName)
                    {
                        $t1Image = $this->imageProcess(1, $t1ImageName, $item['id'], $client);
                        $t2Image = $this->imageProcess(2, $t2ImageName, $item['id'], $client);
                    }
                    else if($t1ImageName and !isset($t2ImageName))
                    {
                        $t1Image = $this->imageProcess(1, $t1ImageName, $item['id'], $client);
                        $t2Image = 'http://asset.goodoc.kr/images/event/common/t1-s.png'; //s2 정방향 더미주소
                    }
                    else  if($t2ImageName and !isset($t1ImageName))
                    {
                        $t1Image = 'http://asset.goodoc.kr/images/event/common/t2-s.png'; //s3 리스트 더미주소
                        $t2Image = $this->imageProcess(2, $t2ImageName, $item['id'], $client);
                    }
                    else  if(!isset($t2ImageName) and !isset($t1ImageName))
                    {
                        $t1Image = 'http://asset.goodoc.kr/images/event/common/t2-s.png';
                        $t2Image = 'http://asset.goodoc.kr/images/event/common/t1-s.png'; //둘다 더미주소
                    }

                    $data['t1ImageName'] = $t1Image; //리스트
                    $data['t2ImageName'] = $t2Image; //정방향
                    $data['deliberationCode'] = $item['consider_number'];

                    //클라이언트 이미지 재할당
                    $eInfos = $this->dataMigration_m->getImage($item['id']);
                    $cnt5 = count($eInfos);

                    //상세이미지 리셋
                    for ($kkk=0; $kkk < 40; $kkk++)
                    {
                        $lll= $kkk+1;
                        $imgName = 'd'.$lll.'ImageName';
                        unset($data[$imgName]);
                    }

                    $dImageArr = [];
                    for ($i=0; $i < $cnt5; $i++)
                    {
                        if($eInfos[$i]['client_image'])
                        {
                            $dImageArr[] = $this->imageProcess(3, $eInfos[$i]['client_image'], $eInfos[$i]['id'], 1);
                        }
                    }

                    $data['dImageJson'] = json_encode($dImageArr, JSON_UNESCAPED_UNICODE);

                    //임시저장
                    $this->dataMigration_m->setAdsTemp($data);

                    //종료된 상태를 병원수정중으로 다시 만든다. ads 업데이트만 한다
                    $this->master->where('id', $data['id']);
                    $this->master->update('ads', ['adStatus'=>$data['adStatus'], 'subAdStatus'=>$data['subAdStatus']]);
                }
            }
            else if(in_array($item['current_status'], [11,13]))
            {
                //최초 검토요청이나 수정검토 요청이나 로직은 동일함
                //current_status 11 최초 검토 클라만 있다

                //검수전까지 등록
                $result2 = $this->dataMigration_m->setEvent($data);

                //검수등록
                //검수타입 . 1 수정검토(O), 2 종료검토(O), 3 수정검토(X), 4 신규등록검토(X), 5 재등록(X)
                $inspectId = $this->dataMigration_m->setInspectingAds([
                    'date'          => $data['modDate'],
                    'adStatus'      => $insStatus,
                    'hospitalId'    => $data['hospitalId'],
                    'prevAdStatus'   =>   $data['adStatus'], //작성중
                    'prevSubAdStatus' =>  $data['subAdStatus'], // 어드민작성중
                    'historyId'     => $result2['historyId'],
                    'adsId'         => $data['id'],
                    'users_id'      => 1,
                    'agencyUserId'  => $data['agencyUserId'],
                    'adsMainMapId'  => $result2['adsMainMapId']
                ]);
            }
            else if(in_array($item['current_status'], [9]))
            {
                //임시저장 처리
                $this->dataMigration_m->setAdsTemp($data);
            }
            else if(in_array($item['current_status'], [4,6]))
            {
                //수정검토요청은 현 데이터로 이벤트 생성후 승인처리, 이전 데이터로 이벤트 생성후 승인 요청한다.
                //검수전까지 등록
                $result2 = $this->dataMigration_m->setEvent($data);

                //검수등록
                $inspectId = $this->dataMigration_m->setInspectingAds([
                    'date'          => $data['modDate'],
                    'adStatus'      => 4,//x 신규등록검토
                    'hospitalId'    => $data['hospitalId'],
                    'prevAdStatus'   =>   $data['adStatus'], //작성중
                    'prevSubAdStatus' =>  $data['subAdStatus'], // 어드민작성중
                    'historyId'     => $result2['historyId'],
                    'adsId'         => $result2['adsId'],
                    'users_id'      => 1,
                    'agencyUserId'  => $data['agencyUserId'],
                    'adsMainMapId'  => $result2['adsMainMapId']
                ]);

                //검수(바로승인)
                $ins['inspectId'] = $inspectId;
                $ins['type'] = 4;
                $ins['users_id']=1;
                $ins['confirmDate'] = date("Y-m-d H:i:s", strtotime("-9 hours" , strtotime($item['confirmed_at'])));
                $this->dataMigration_m->updateInspectInfo($ins);


                //수정데이터로 재등록 및 검수등록, 검수완료처리 필요
                //수정데이터 선언
                $data['adStartDate'] = $item['client_start_on'];
                $data['adEndDate'] = $item['client_end_on'];
                $data['adDateExtend'] = ($item['client_is_temporary'] == 1)? 'Y':'N';
                $data['adTitle'] = $item['client_title'];
                if($item['client_is_numerical_discounted_price'] == 0)
                {
                    //텍스트단가
                    $costType = 2;
                    $discountCost = '';
                    $generalCost = '';
                    $textCost = $item['client_discounted_price'];
                }
                else
                {
                    $costType = 1;
                    $discountCost = $item['client_numerical_discounted_price'];
                    $generalCost = $item['client_numerical_original_price'];
                    $textCost = '';
                }
                $t1ImageName = $item['client_image']; //리스트
                $t2ImageName = $item['client_image2']; //정방향

                //정상인데 이미지가 없다면 리스트를 정방향으로 복사하고 둘다 없다면 더미이미지를 넣는다.
                if(!isset($t2ImageName))
                {
                    $t2ImageName = $t1ImageName;
                }

                $data['costType'] = $costType;
                $data['dbCost'] = $item['event_cost'];
                $data['generalCost'] = $generalCost;
                $data['discountCost'] = $discountCost;
                $data['textCost'] = $textCost;

                $client = 1; //수정(이전) 데이터로 변경
                //정상용 로직
                if($t1ImageName and $t2ImageName)
                {
                    $t1Image = $this->imageProcess(1, $t1ImageName, $item['id'], $client);
                    $t2Image = $this->imageProcess(2, $t2ImageName, $item['id'], $client);
                }
                else if($t1ImageName and !isset($t2ImageName))
                {
                    $t1Image = $this->imageProcess(1, $t1ImageName, $item['id'], $client);
                    $t2Image = $this->imageProcess(1, $t1ImageName, $item['id'], $client);
                }
                else  if($t2ImageName and !isset($t1ImageName))
                {
                    $t1Image = $this->imageProcess(2, $t2ImageName, $item['id'], $client);
                    $t2Image = $this->imageProcess(2, $t2ImageName, $item['id'], $client);
                }
                else  if(!isset($t2ImageName) and !isset($t1ImageName))
                {
                    $t1Image = 'http://asset.goodoc.kr/images/event/common/t2-s.png';
                    $t2Image = 'http://asset.goodoc.kr/images/event/common/t1-s.png'; //둘다 더미주소
                }

                $data['t1ImageName'] = $t1Image; //리스트
                $data['t2ImageName'] = $t2Image; //정방향
                $data['deliberationCode'] = $item['client_consider_number'];

                //상세이미지 리셋
                for ($kkk=0; $kkk < 40; $kkk++)
                {
                    $lll= $kkk+1;
                    $imgName = 'd'.$lll.'ImageName';
                    unset($data[$imgName]);
                }

                //원 이미지 재할당
                $eInfos = $this->dataMigration_m->getImage($item['id']);
                $cnt6 = count($eInfos);

                $dImageArr = [];
                for ($i=0; $i < $cnt6; $i++)
                {
                    if($eInfos[$i]['image'])
                    {
                        $dImageArr[] = $this->imageProcess(3, $eInfos[$i]['image'], $eInfos[$i]['id'], 0);
                    }
                }

                $uData = $data; //업데이트용 데이터 할당
                $uData['dImageJson'] = json_encode($dImageArr, JSON_UNESCAPED_UNICODE);
                $uData['adsId'] = $uData['id'];
                unset($uData['id']);

                //원데이터로 검수 등록전까지 진행 -> 업데이트로 변경해야함.
                $this->dataMigration_m->updateAdsTemp($uData);

                $result2 = $this->dataMigration_m->updateAds($uData);

                //검수등록, 두번째는 current_status에 맞게 검수타입 등록 $insStatus
                //검수타입 . 1 수정검토(O), 2 종료검토(O), 3 수정검토(X), 4 신규등록검토(X), 5 재등록(X)
                $inspectId20 = $this->dataMigration_m->setInspectingAds([
                    'date'          => $data['modDate'],
                    'adStatus'      => $insStatus,
                    'hospitalId'    => $data['hospitalId'],
                    'prevAdStatus'   =>   $data['adStatus'], //진행중
                    'prevSubAdStatus' =>  $data['subAdStatus'], // 어드민작성중
                    'historyId'     => $result2['historyId'],
                    'adsId'         => $data['id'], //이벤트 아이디는 변경없음
                    'users_id'      => 1,
                    'agencyUserId'  => $data['agencyUserId'],
                    'adsMainMapId'  => $result2['adsMainMapId']
                ]);

                //4번은 검수요청까지만 처리

                //6번 반려 처리
                if($item['current_status'] == 6)
                {
                    //검수(반려)
                    $ins2['inspectId'] = $inspectId20;
                    $ins2['type'] = 2;
                    $ins2['reason'] = $item['deny_message'];
                    $ins2['users_id']=1;
                    $ins2['rejectCode']='';
                    $ins2['agencyUserReason']='';
                    $ins2['confirmDate'] = date("Y-m-d H:i:s", strtotime("-9 hours" , strtotime($item['confirmed_at'])));
                    $this->dataMigration_m->updateInspectInfo($ins2);
                }

                //ads adStatus 원상태화 - 검수시 2로 변경됨
                $this->master->where('id', $uData['adsId']);
                $this->master->update('ads', ['adStatus'=>$data['adStatus']]);
            }
        }
    }

    function dataCheck($data)
    {
        $eventArrAnd = [
            //'title', 'image', 'hospital_id'
            //'title', 'image'
            'title'
        ];

        $eventArrOr = ['numerical_original_price','numerical_discounted_price','discounted_price'];

        $i=0;
        foreach ($eventArrAnd as $item)
        {
            if($data[$item] != '')
                $i++;
        }
        $j=0;
        foreach ($eventArrOr as $item)
        {
            if($data[$item] != '')
                $j++;
        }

        $oData = $cData = '';

        if($i == 1 and $j==0)
        {
            $oData= 10; //원데이터 미비
        }
        else if($i < 1 and $j > 0)
        {
            $oData= 10; //원데이터 미비
        }
        else if($i == 1 and $j > 0)
        {
            $oData= 20; //원데이터있음
        }
        else
        {
            $oData= 10; //원데이터 미비
        }

        echo '=='.$i.'=='.$j.'==';

        //클라이언트 데이터
        $k=0;
        foreach ($eventArrAnd as $item)
        {
            if($data['client_'.$item] != '')
                $k++;
        }
        $l=0;
        foreach ($eventArrOr as $item)
        {
            if($data['client_'.$item] != '')
                $l++;
        }

        if($k == 1 and $l==0)
        {
            $cData= 10; //원데이터 미비
        }
        else if($k < 1 and $l > 0)
        {
            $cData= 10; //원데이터 미비
        }
        else if($k == 1 and $l > 0)
        {
            $cData= 20; //원데이터있음
        }

        if($oData == 10 and $cData == 10)
        {
            return 100;//둘다 미비
        }

        if($oData == 10 and $cData == 20)
        {
            return 200;//클라만 존재
        }

        if($oData == 20 and $cData == 10)
        {
            return 300;//원데이터만 존재
        }

        if($oData == 20 and $cData == 20)
        {
            return 400;//둘다 존재
        }
    }

    /**
     * 신청db 입력
     * event_cost 없는 항목 7만여개 존재 2015년까지.
     * 병원번호 없는 것도 2천여개 존재
     * 신청정보 아무것도 없는 신청도 있음
     */
    function callRequestProcess()
    {
        set_time_limit(0);
        ini_set('memory_limit','-1');

        //call_requests insert
        $sql999 = "select count(*) as cnt from call_requests";
        $pArr3 = $this->v1->query($sql999)->row_array();

        $count = intval($pArr3['cnt'] / 100000);
        for ($i=0; $i <= $count; $i++)
        {
            $j = $i * 100000 ;

            $sql999 = "select * from call_requests limit ".$j.", 100000";
            $pArr = $this->v1->query($sql999)->result_array();

            foreach ($pArr as $it)
            {
                $iArr = [
                    'callRequestId'=>$it['id'],
                    'hospitalId'=>$it['hospital_id'],
                    'adsId'=>$it['event_id'],
                    'userId'=>$it['user_id'],
                    'device'=>$it['device'],
                    'status'=>$it['status'],
                    'confirmDate'=>date("Y-m-d H:i:s", strtotime("-9 hours" , strtotime($it['confirmed_at']))),
                    'regDate'=>date("Y-m-d H:i:s", strtotime("-9 hours" , strtotime($it['created_at']))),
                    'modifyDate'=>date("Y-m-d H:i:s", strtotime("-9 hours" , strtotime($it['updated_at']))),
                    'name'=>$it['name'],
                    'phone'=>$it['phone'],
                    'content'=>$it['content'],
                    'privacyAgree'=>$it['privacy_agree'],
                    'funnel'=>$it['funnel'],
                    'eventCost'=>$it['event_cost'],
                    'callTime'=>$it['call_time'],
                    'isMigration'=>$it['is_migration'],
                    'age'=>$it['age'],
                    'sex'=>$it['sex'],
                    'onlySms'=>$it['only_sms'],
                    'parentId'=>$it['parent_id'],
                    'messageId'=>$it['message_id'],
                    'isDelete'=>$it['is_deleted'],
                    'supplyThirdPartyAgree'=>$it['supply_third_party_agree'],
                    'fingerPrint'=>$it['finger_print'],
                    'region'=>$it['region'],
                    'isSavePhone'=>$it['is_save_phone']
                ]   ;
                $this->master->insert('call_request', $iArr);
            }
        }
    }

    /**
     * 신청db 입력
     * event_cost 없는 항목 7만여개 존재 2015년까지.
     * 병원번호 없는 것도 2천여개 존재
     * 신청정보 아무것도 없는 신청도 있음
     */
    function callRequestProcessAll()
    {
        set_time_limit(0);
        ini_set('memory_limit','-1');

        //call_requests insert
        $sql999 = "select * from call_requests where id >= 1389035 and id < 1499800";
        $pArr = $this->v1->query($sql999)->result_array();
        foreach ($pArr as $it)
        {
            $type = $this->typeChange($it['status']);
            $iArr = [
                'callRequestId'=>$it['id'],
                'hospitalId'=>$it['hospital_id'],
                'adsId'=>$it['event_id'],
                'userId'=>$it['user_id'],
                'device'=>$it['device'],
                'status'=>$type,
                'confirmDate'=>date("Y-m-d H:i:s", strtotime("-9 hours" , strtotime($it['confirmed_at']))),
                'regDate'=>date("Y-m-d H:i:s", strtotime("-9 hours" , strtotime($it['created_at']))),
                'modifyDate'=>date("Y-m-d H:i:s", strtotime("-9 hours" , strtotime($it['updated_at']))),
                'name'=>$it['name'],
                'phone'=>$it['phone'],
                'content'=>$it['content'],
                'privacyAgree'=>$it['privacy_agree'],
                'funnel'=>$it['funnel'],
                'eventCost'=>$it['event_cost'],
                'callTime'=>$it['call_time'],
                'isMigration'=>$it['is_migration'],
                'age'=>$it['age'],
                'sex'=>$it['sex'],
                'onlySms'=>$it['only_sms'],
                'parentId'=>$it['parent_id'],
                'messageId'=>$it['message_id'],
                'isDelete'=>$it['is_deleted'],
                'supplyThirdPartyAgree'=>$it['supply_third_party_agree'],
                'fingerPrint'=>$it['finger_print'],
                'region'=>$it['region'],
                'isSavePhone'=>$it['is_save_phone']
            ]   ;
            $this->master->insert('call_request', $iArr);
        }
    }

    /**
     * 예약정보 마이그레이션
     */
    function bookingProcess()
    {
        set_time_limit(0);
        ini_set('memory_limit','-1');

        //insert
        $sql999 = "select * from bookings where id > 180000";
        $pArr = $this->v1->query($sql999)->result_array();
        foreach ($pArr as $it)
        {
            $iArr = [
                'userId'=>$it['user_id'],
                'hospitalId'=>$it['hospital_id'],
                'status'=>$it['status'],
                'bookDate'=>date("Y-m-d H:i:s", strtotime("-9 hours" , strtotime($it['booked_at']))),
                'confirmDate'=>date("Y-m-d H:i:s", strtotime("-9 hours" , strtotime($it['confirmed_at']))),
                'oldId'=>$it['old_id'],
                'regDate'=>date("Y-m-d H:i:s", strtotime("-9 hours" , strtotime($it['created_at']))),
                'modifyDate'=>date("Y-m-d H:i:s", strtotime("-9 hours" , strtotime($it['updated_at']))),
                'name'=>$it['name'],
                'phone'=>$it['phone'],
                'callRequestId'=>$it['call_request_id']
            ]   ;
            $this->master->insert('booking', $iArr);
        }
    }

    /**
     * 신청db 메모 입력
     */
    function adminHistoryProcess()
    {
        set_time_limit(0);
        ini_set('memory_limit','-1');

        //call_requests insert
        $sql999 = "select * from admin_histories where id > 700000";
        $pArr = $this->v1->query($sql999)->result_array();
        foreach ($pArr as $it)
        {
            $iArr = [
                'callRequestId'=>$it['content_id'],
                'memo'=>$it['message'],
                'userId'=>$it['user_id'],
                'regDate'=>date("Y-m-d H:i:s", strtotime("-9 hours" , strtotime($it['created_at'])))
            ]   ;
            $this->master->insert('call_memo', $iArr);
        }
    }

    /**
     * 현재 v2에 등록된 이벤트만 대상으로 v1에서 이미지배열을 가져와서
     * v2 ads, history table의 dImageJson 항목을 업데이트 한다.
     * 모두 원 이미지를 대상으로 한다.
     */
    function imageRevision()
    {
        set_time_limit(0);
        ini_set('memory_limit','-1');


        $sql999 = "select id, dImageJson from ads";
        $pArr = $this->master->query($sql999)->result_array();

        echo 'v2 <br><br>';
        dd($pArr, false);


        foreach ($pArr as $item)
        {
            $dImageArr = [];

            $eInfos = $this->dataMigration_m->getImage($item['id']);
            //dd($eInfos, false);
            $cnt = count($eInfos);
            echo '상세이미지수 : '.$cnt.'<br><br>';

            if($cnt > 0)
            {
                for ($i=0; $i < $cnt; $i++)
                {
                    if($eInfos[$i]['image'])
                    {
                        $image2 = $eInfos[$i]['image'];
                        if(preg_match("/[\xE0-\xFF][\x80-\xFF][\x80-\xFF]/", $eInfos[$i]['image']))
                        {
                            $image2 = urlencode($eInfos[$i]['image']);
                        }

                        $dImageArr[] = 'http://asset.goodoc.kr/images/event/'.$eInfos[$i]['id'].'/d_'.$image2;
                    }
                }

                $dImageJson = json_encode($dImageArr, JSON_UNESCAPED_UNICODE);
            }
            else
            {
                $dImageJson = '';
            }

            echo 'update - ';
            dd($dImageJson, false);

            //업데이트
            $this->master->where('id', $item['id'])->update('ads', ['dImageJson'=>$dImageJson]);

            $this->master->where('adsId', $item['id'])->update('ads_history', ['dImageJson'=>$dImageJson]);

        }
    }

    /**
     call_request status 변경
     */
    function callUpdate()
    {
        set_time_limit(0);
        ini_set('memory_limit', '-1');

        $arr = [2, 4, 5, 6, 7, 8, 9, 10];
        foreach ($arr as $item) {
            $sql999 = "select group_concat(id) gId from call_request where status='" . $item . "'";
            $pArr = $this->master->query($sql999)->row_array();

            echo $item . ' 번 상태 - ' . $pArr['gId'] . '<br><br>';
        }
    }


    /**
     * 기획전 입력
     * auto_increment 풀어야함
     * 345개
     */
    function packageProcess()
    {
        set_time_limit(0);
        ini_set('memory_limit','-1');

        $sql = "
            SELECT  `event_packages`.*, group_concat(event_package_events.`event_id`) eventArr FROM `event_packages`  
            INNER JOIN `event_package_events` ON `event_packages`.`id` = `event_package_events`.`event_package_id` 
            group by event_packages.id
            ORDER BY event_package_events.priority
        
        ";

        $result = $this->v1->query($sql)->result_array();

        foreach ($result as $item)
        {
            $data['id'] = $item['id'];
            $data['adsId'] = $item['eventArr'];
            $data['title'] = $item['title'];
            $data['bannerViewType'] = 3; //배너노출영역. 1. 굿닥메인배너, 2 병원이벤트 메인배너, 3 기획전 리스트. 1,2,3 형태
            $data['viewType'] = $item['visible']; //0 미노출, 1 상시, 2 기간
            $newImage = $this->imageProcess(4, $item['image'], $item['id']);
            $data['banner'] = $newImage;
            $data['detailInfo'] = $item['desc'];
            $data['startDate'] = $item['start_on'];
            $data['endDate'] = $item['end_on'];
            $data['regDate'] = date("Y-m-d H:i:s", strtotime("-9 hours" , strtotime($item['created_at'])));
            $data['modDate'] = date("Y-m-d H:i:s", strtotime("-9 hours" , strtotime($item['updated_at'])));
            $data['isDelete'] = ($item['is_show'] == 0)?'Y':'N';

            $this->dataMigration_m->addPackage($data);
        }
    }

    /**
     * 마이그레이션후 payments 보정 프로그램
     */
    function revisionCall()
    {
        set_time_limit(0);
        ini_set('memory_limit','-1');

        echo '시작 <br><br>';

        $sql = "select * from payments where `created_at` > '2019-04-27 15:00:00' and payment_type=3";
        //$sql = "select * from payments where id=1355168 and payment_type=3";
        $result = $this->v1->query($sql)->result_array(); //dd($result, false);

        $i=$j=$k=$l=0;
        $callArr = [];
        foreach ($result as $item)
        {
            echo 'callRequestId - '.$item['call_request_id'].' start <br>';
            $sql2 = "select count(*) cnt, ifnull(price,0) as price from deposit where callRequestId='".$item['call_request_id']."'";
            $result2 = $this->master->query($sql2)->row_array();
            //dd($result2, false);

            if($result2['cnt'] == 0)
            {
                //v2에 존재하지 않는 것, 입력처리
                echo 'v2 입력처리<br>';
                //계약번호 구하기
                $adsInfo = $this->master->get_where('ads', ['id'=>$item['event_id']])->row_array();

                $iArr = [
                    'status'=>3,
                    'isMinus'=>1,
                    'contractId'=>$adsInfo['contractId'],
                    'contractOrderId'=>$adsInfo['contractOrderId'],
                    'usersId'=>1,
                    'memo'=>$item['memo'],
                    'price'=>$item['price'],
                    'regDate'=>date("Y-m-d H:i:s", strtotime("-9 hours" , strtotime($item['created_at']))),
                    'modDate'=>date("Y-m-d H:i:s", strtotime("-9 hours" , strtotime($item['updated_at']))),
                    'callRequestId'=>($item['call_request_id'])?$item['call_request_id']:0
                ]   ;
                $this->master->insert('deposit', $iArr);
                //dd($iArr, false);

                $callArr[] = $item['call_request_id'];

                $i++;
            }
            else
            {
                if(($item['price'] == 0 and $result2['price'] == 0) or ($item['price'] == $result2['price'] ))
                {
                    echo 'v2 중복처리 안함<br>';
                    $j++;
                }
                else if($item['price'] != 0 and $result2['price'] == 0)
                {
                    echo '업데이트 실수 보정<br>';

                    $this->master->where('callRequestId', $item['call_request_id']);
                    $this->master->update('deposit', ['price'=>$item['price'], 'memo'=>$item['memo']]);
                    $k++;
                }
                else if($item['price'] == 0 and $result2['price'] != 0)
                {
                    echo 'v2 중복처리 필요<br>';

                    $this->master->where('callRequestId', $item['call_request_id']);
                    $this->master->update('deposit', ['price'=>0, 'memo'=>$item['memo']]);
                    $l++;
                }

            }
            echo 'callRequestId - '.$item['call_request_id'].' end <br><br>';

        }


        $sum = $i+$j+$k+$l;
        echo '총 '.$sum.'건 ---'.$i.'-'.$j.'-'.$k.'-'.$l.' 건 처리<br><br>';
    }

    /**
     * 신청db 상태 치환 v1 -> v2
     */
    function typeChange($type)
    {
        $ret = '';
        switch ($type)
        {
            case '0':
                $ret = 1;
                break;
            case '1':
                $ret = 1;
                break;
            case '2':
                $ret = 7;
                break;
            case '3':
                $ret = 3;
                break;
            case '4':
                $ret = 5;
                break;
            case '5':
                $ret = 7;
                break;
            case '6':
                $ret = 9;
                break;
            case '7':
                $ret = 2;
                break;
            case '8':
                $ret = 4;
                break;
            case '9':
                $ret = 6;
                break;
            case '10':
                $ret = 8;
                break;

        }

        return $ret;
    }

    function addCall()
    {
        set_time_limit(0);
        ini_set('memory_limit','-1');

        echo '시작 <br><br>';

        $sql = "select * from call_requests where `created_at` > '2019-04-27 15:00:00' ";
        //$sql = "select * from payments where id=1355168 and payment_type=3";
        $result = $this->v1->query($sql)->result_array(); //dd($result, false);

        $i=0;
        $callArr = [];
        foreach ($result as $it)
        {
            echo 'callRequestId - '.$it['id'].' start <br>';
            //먼저 지우고
            $this->master->where('callRequestId',$it['id'])->delete('call_request');

            //다시 넣기
            $iArr0 = [
                    'callRequestId'=>$it['id'],
                    'hospitalId'=>$it['hospital_id'],
                    'adsId'=>$it['event_id'],
                    'userId'=>$it['user_id'],
                    'device'=>$it['device'],
                    'status'=>3,
                    'confirmDate'=>'',
                    'regDate'=>date("Y-m-d H:i:s", strtotime("-9 hours" , strtotime($it['created_at']))),
                    'modifyDate'=>date("Y-m-d H:i:s", strtotime("-9 hours" , strtotime($it['updated_at']))),
                    'name'=>$it['name'],
                    'phone'=>$it['phone'],
                    'content'=>$it['content'],
                    'privacyAgree'=>$it['privacy_agree'],
                    'funnel'=>$it['funnel'],
                    'eventCost'=>$it['event_cost'],
                    'callTime'=>$it['call_time'],
                    'isMigration'=>$it['is_migration'],
                    'age'=>$it['age'],
                    'sex'=>$it['sex'],
                    'onlySms'=>$it['only_sms'],
                    'parentId'=>$it['parent_id'],
                    'messageId'=>$it['message_id'],
                    'isDelete'=>$it['is_deleted'],
                    'supplyThirdPartyAgree'=>$it['supply_third_party_agree'],
                    'fingerPrint'=>$it['finger_print'],
                    'region'=>$it['region'],
                    'isSavePhone'=>$it['is_save_phone']
                ]   ;
                $this->master->insert('call_request', $iArr0);
                echo 'callRequestId - '.$it['id'].' end <br><br>';
                $i++;
        }


        echo '총 '.$i.' 건 처리<br><br>';
    }


    function deleteEventContracts()
    {
        $sql2 = "select * from contracts";
        $adsIds2 = $this->v1->query($sql2)->result_array(); //var_dump($adsIds2); exit;

        $cc = [];
        foreach ($adsIds2 as $item)
        {
            $cc[] = $item['id'];
        }

        $range = range(1, 962);

        $dd = array_diff($range, $cc);

        foreach ($dd as $key=>$val)
        {
            echo $val.'<br>';
            $sql = "select count(*) as cnt from event_contracts where contract_id='".$val."'";
            $cccc = $this->v1->query($sql)->row_array(); //var_dump($adsIds2); exit;

            if($cccc['cnt'] != 0)
            {
                echo $cccc['cnt'].' del '. $val.'<br>';
                $this->v1->where('contract_id', $val);
                $this->v1->delete('event_contracts');
            }
        }
    }

    /**
     * 이미지를 s3로 복사하고 풀url을 리턴
     * 각 이미지들은 client 일 경우엔 디렉토리에 client_ 를 붙인다.
     * @param $type 이미지 종류. 1 이벤트리스트, 2 이벤트 정방향, 3 이벤트 상세이미지, 4 기획전 배너
     * @param $image
     * @param $client 1이면 클라이언트 이미지
     * @return string
     */
    function imageProcess($type, $image, $eventId, $client=0)
    {
        //정방향 image2 /s3/uploads/event/image2/이벤트번호/파일명
        //list image  /uploads/event/image/이벤트번호/파일명
        //둘다 도메인은 같으나 s3라고 들어가있으면 s3에서 불러옴

        //event_info 테이블의 주소는 /uploads/event_info/image/이벤트번호/파일명

        //s3는 sdk를 이용하여 복사하고 나머지는 다운받아서 s3에 올린다.
        //생성규칙은 /uploads/event/이벤트번호/image/파일명 으로 한다.

        //버킷명 정의
        if( ENVIRONMENT === 'development' )
        {
            //$bucketName = 'asset-dev.goodoc.kr';
            $bucketName = 'asset-dev.goodoc.kr';
        }
        else if( ENVIRONMENT === 'testing' )
        {
            $bucketName = 'asset-staging.goodoc.kr';
        }
        else if (ENVIRONMENT === 'production')
        {
            $bucketName = 'asset.goodoc.kr';
        }

        $s3Client = Aws\S3\S3Client::factory(array(
            'region' => 'ap-northeast-2',
            'version' => 'latest',
            'signature' => 'v4',
            'credentials' => array(
                'key'    => S3Key,
                'secret' => S3Secret
            )
        ));

        $clientN = '';
        if($client == 1)
        {
            $clientN = 'client_';
        }

        $image2 = $image;
        if(preg_match("/[\xE0-\xFF][\x80-\xFF][\x80-\xFF]/", $image))
        {
            $image2 = urlencode($image);
        }

        switch ($type)
        {
            case 1:
                $path = 'http://www.goodoc.co.kr/uploads/event/'.$clientN.'image/'.$eventId.'/'.$image;
                $savePath = UP_ROOT.'/data/event/'.$eventId;
                $toPath = 'images/event/'.$eventId.'/t1_'.$image2;
                break;
            case 2: //정방향
                $path = 'http://www.goodoc.co.kr/s3/uploads/event/'.$clientN.'image2/'.$eventId.'/'.$image;
                $savePath = UP_ROOT.'/data/event/'.$eventId;
                $toPath = 'images/event/'.$eventId.'/t2_'.$image2;
                break;
            case 3:
                $path = 'http://www.goodoc.co.kr/uploads/event_info/'.$clientN.'image/'.$eventId.'/'.$image;
                $savePath = UP_ROOT.'/data/event_info/'.$eventId;
                $toPath = 'images/event/'.$eventId.'/d_'.$image2;
                break;
            case 4:
                $path = 'http://www.goodoc.co.kr/uploads/event_package/'.$clientN.'image/'.$eventId.'/'.$image;
                $savePath = UP_ROOT.'/data/event_package/'.$eventId;
                $toPath = 'images/event_package/'.$eventId.'/'.$image2;
                break;
        }

        echo "image path : ".$path."<br><Br>";

//        if(!is_dir($savePath))
//        {
//            mkdir($savePath, 0777);
//            chmod($savePath, 0777);
//        }

        //파일 읽어서 로컬에 저장
        //$fileData = file_get_contents($path);
        $fileData = $this->getImage($path);
        //file_put_contents($savePath.'/'.$image, $fileData);

        if(!$fileData)
        {
            return 'http://asset.goodoc.kr/images/event/common/d1-s.png';
        }

        //s3 업로드
        $return = $s3Client->putObject(array(
            'Bucket' => $bucketName,
            'Key'    => $toPath,
            //'SourceFile' => $savePath.'/'.$image,
            'Body' => $fileData,
            'ACL'    => 'public-read'
        ));


        //퍼징 id E3BRKK32K1AORL
        $returnImg = 'http://'.$bucketName.'/'.$toPath;

        //dd($returnImg, true);

        return $returnImg;
    }


    function getImage($url)
    {

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        //curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);

        $g = curl_exec($ch);

        curl_close($ch);

        return $g;

    }

    /**
     * 1 수주, 2 계약충전, 3 소진, 4 기타충전, 5 환불수수료, 6 발행 환불, 7 계약 환불, 8 기타 소진, 9 발행취소, 10 계약취소, 11 이월 소진, 12 이월 충전, 13 세금계산서
     * @param $type
     * @return mixed
     */
    function paymentType($type)
    {
        switch ($type)
        {
            case 0:
                $rt['status'] = 2;
                $rt['memo'] = '최초 충전-';
                $rt['minus'] = 0;
                break;
            case 1:
                $rt['status'] = 2;
                $rt['memo'] = '재계약 충전-';
                $rt['minus'] = 0;
                break;
            case 2:
                $rt['status'] = 4;
                $rt['memo'] = '서비스충전-';
                $rt['minus'] = 0;
                break;
            case 3:
                $rt['status'] = 3;
                $rt['memo'] = '';
                $rt['minus'] = 1;
                break;
            case 4:
                $rt['status'] = 4;
                $rt['memo'] = '중복db 서비스충전-';
                $rt['minus'] = 0;
                break;
            case 5:
                $rt['status'] = 4;
                $rt['memo'] = '기타충전-';
                $rt['minus'] = 0;
                break;
            case 6:
                $rt['status'] = 8;
                $rt['memo'] = '기타차감-';
                $rt['minus'] = 1;
                break;
            case 7:
                $rt['status'] = 4;
                $rt['memo'] = '클라이언트충전-';
                $rt['minus'] = 0;
                break;
            case 8:
                $rt['status'] = 8;
                $rt['memo'] = 'cpm차감-';
                $rt['minus'] = 0;
                break;
            case 9:
                $rt['status'] = 6; //발행환불 6, 계약환불 7, 계약환불 케이스는 없다고 가정함
                $rt['memo'] = '환불-';
                $rt['minus'] = 1;
                break;
        }

        return $rt;
    }

    function eventCountCheck()
    {
        $v1Arr = $v2Arr = [];
        $v2 = $this->master->select('id')->get('ads')->result_array();
        echo 'v2 cnt : '.count($v2). '----<br>';

        $sql = "
            SELECT events.id            FROM `events` 
            left JOIN `hospitals` ON `hospitals`.`id` = `events`.`hospital_id` 
            LEFT outer JOIN `event_event_categories` ON `event_event_categories`.`event_id` = `events`.`id`
            LEFT outer JOIN `client_event_event_categories` ON `client_event_event_categories`.`event_id` = `events`.`id`              
            LEFT JOIN `event_search_tags` est ON `events`.`id` = est.`event_id`
            left join search_tags st on est.`search_tag_id`=st.id
            LEFT JOIN `event_options` eo ON `events`.`id` = eo.`event_id`
            LEFT JOIN `event_regions` er ON `events`.`id` = er.`event_id`
            LEFT JOIN `model_image_event_maps` miem ON `events`.`id` = miem.`event_id`
            LEFT JOIN `client_model_image_event_maps` cmiem ON `events`.`id` = cmiem.`event_id`
            LEFT JOIN `external_media_event_maps` emem ON `events`.`id` = emem.`event_id`
            LEFT JOIN `client_external_media_event_maps` cemem ON `events`.`id` = cemem.`event_id`
            join event_contracts evc on events.id=evc.event_id
            LEFT JOIN `event_client_search_tags` cest ON `events`.`id` = cest.`event_id`
            left join client_search_tags cst on cest.client_search_tag_id=cst.id
            WHERE 
            events.event_type=1 
            AND 
            `events`.`is_deleted` = 0 
            and events.id not in(1132,1214,1215,1253,1255,1404,1459,1460,1586,1587,1593,1622,1650,1652,1698,1717,1720,1766,1793,1794,1883,1887,1895,2038,2067,2068,2074,2101,2124,2133,2186,2210,2271,2272,2293,2308,2317,2318,2354,2396,2404,2441,2491,2492,2501,2531,2534,2554,2566,2567,2568,2595,2741,3081,3082,3084,3085,3444,3445,3446,3447,3448,3449,3450,3451,3452,3453,3909,4009,4368,5112,5135,5173,5307,5322,6987,7541,10,1800,1866,1867,1886,2324,2378,2387,2496,2514,2593,2742,3056,3057,38,2753,2754,2755,3351,3353,622,625,751,755,775,947,583,2289,2421,1244,1958,1209,1755,1850,2327,2600,3285,3604,58,2746,2908,188,189,2857,170,182,183,184,1565,1927,2505,2506,2507,2508,1965,1971,582,2726,1311,3095,3096,471,475,479,483,710,1495,1496,3542,472,476,480,484,715,581,110,195,1104,1105,1106,3423,2052,2053,2762,2903,3184,3359,235,377,704,705,706,778,1730,3165,3166,877,879,686,689,701,623,624,749,753,953,5300,690,944,5303,5320,1037,1038,1148,1205,978,979,132,133,143,252,292,327,328,329,336,337,366,438,626,627,681,180,181,191,989,423,424,428,432,716,725,729,154,439,2247,2409,2543,1064,1065,1066,1175,1195,1267,1297,1324,1327,1330,415,473,477,481,485,718,579,1687,1715,1729,19,24,57,2197,4534,4153,4154,4155,2071,1062,1173,1296,1322,1487,1853,2006,2744,2786,394,464,839,977,1097,1099,1185,1186,47,48,1547,1548,1961,1964,2393,2395,876,2408,307,2761,3083,3325,5466,7827,1869,1871,1962,2136,2219,1920,1921,2939,2940,3067,106,144,146,258,299,300,402,682,694,2143,2147,2148,2242,2371,2372,2373,1591,1592,2653,2784,1308,515,792,1918,2060,2061,2301,2458,2475,1220,1221,1410,1411,1533,1546,30,31,2333,1834,1976,2167,1084,420,425,429,434,720,724,728,763,864,865,512,513,607,608,609,3176,3188,3029,237,380,641,642,962,1342,1344,1712,1713,36,943,585,1265,1305,1563,1765,1767,1854,1911,4440,1392,1397,2879,3180,3181,520,521,523,1985,2179,2432,164,215,283,353,383,436,695,2103,2104,2198,2307,2360,2542,2685,2804,50,61,125,148,196,262,352,2406,2641,2244,2312,137,138,139,227,519,522,524,673,674,1987,1990,1061,1172,1194,1268,1295,1321,1329,1283,1368,1934,2008,2149,2181,2236,2497,2631,2679,2723,2933,4420,4502,5223,633,634,733,6289,740,3596,6,9,77,197,2959,5228,173,174,2278,1561,2617,2898,2899,2916,2936,3070,3182,3226,3390,3417,1745,1786,1844,1863,3121,3404,3482,2764,32,54,1980,87,193,384,391,437,668,671,3158,3159,3393,2463,2611,382,2840,2841,1639,2141,84,198,1596,2634,2656,3062,1032,1103,880,882,1005,223,250,334,357,447,692,693,1354,85,1637,1641,1735,1736,1864,1931,1941,1977,2221,2385,2648,3478,3744,3788,4064,4628,5464,745,63,90,111,194,284,285,286,408,409,662,1339,1346,1409,1444,1518,1763,2205,2206,2275,2356,3332,169,172,255,395,659,247,888,1113,1160,1161,3509,3016,3017,3625,171,1699,1815,2158,3189,3190,3411,5615,2836,2837,422,427,430,433,707,1434,1435,1438,1492,1619,1747,1981,2309,2361,2442,2522,1543,1544,59,128,222,516,750,754,776,805,806,807,810,811,812,1102,2681,2839,71,72,73,218,264,265,266,267,660,584,2688,2689,3069,3150,869,421,426,435,717,815,1216,1067,1068,1176,1177,1196,1269,1298,1325,318,1025,645,2214,107,113,203,652,702,761,16,18,25,8,1969,1510,1902,1903,1904,2106,2107,2108,2818,2820,2821,2849,3958,2665,2791,2914,3340,2182,2183,2184,2440,599,2725,1420,1545,159,160,161,225,226,277,278,658,44,312,2342,359,403,404,405,854,2618,2699,532,533,534,535,536,737,2883,2884,2885,3012,3217,1690,1722,1723,1731,1801,190,4975,843,586,590,1493,688,1134,588,949,2873,3258,3293,3320,3521,1782,1248,1266,589,1073,1074,1075,1076,2163,2455,2673,592,3105,3106,3146,450,451,453,456,713,571,591,1550,587,449,452,454,457,709,726,727,764,866,867,2412,2460,1959,2011,2269,2270,2814,2815,2816,1577,2731,2969,3718,3728,3894,2682,3032,2904,3022,2582,2583,2808,2811,2812,2813,3090,3094,3588,3589,2633,598,952,861,862,685,803,2495,2674,2675,2563,2564,2732,2781,597,1768,1769,1770,1823,1825,596,487,491,495,499,712,945,2966,595,488,492,496,500,711,946,2772,3154,3173,3326,3327,3630,3148,489,493,497,501,719,957,1353,594,490,494,498,502,708,1437,1385,1532,593,1776,1777,1779,1824,1826,236,379,612,698,779,1203,149,150,151,813,211,378,643,644,697,646,338,398,238,376,632,736,443,1500,1502,354,241,242,310,374,440,385,1645,1646,2190,2192,2225,2513,2757,2758,2794,448,872,3291,618,635,664,1087,474,478,482,486,714,462,463,504,505,506,507,525,526,538,628,629,630,637,638,639,640,657,859,613,615,616,617,2647,802,431,814,816,817,955,956,948,951,954,1094,1152,909,3241,3263,5468,1314,1359,88,5237,165,251,256,400,401,4505,1688,2450,2451,2452,3473,3474,3475,1313,1312,155,455,469,683,1689,773,179,517,2705,2717,2922,2924,3265,3266,3268,3439,2889,1315,212,229,230,3383,3385,2872,217,1310,1316,105,528,529,530,610,2622,2626,3073,3074,3337,70,92,93,1398,1497,1943,2287,2288,2457,2639,2792,2824,2877,2902,2971,2979,3065,3125,3139,3196,3261,3301,3512,3547,3634,14,78,1686,340,341,527,744,1108,1485,1995,2039,2283,2284,2355,2386,2778,3076,3392,1413,1868,2405,2526,2527,2701,2702,2829,2832,2833,2946,2947,2948,3178,3436,3571,3578,3709,3721,4007,4008,389,411,412,465,466,467,672,675,29,248,102,1701,1947,1724,1746,1821,1935,2538,2615,2616,2787,3345,9988,518,2297,2298,2299,2826,2827,1114,2154,2173,2204,386,722,723,785,1000,1361,1399,1457,1725,1727,2125,2358,2381,2867,3246,3329,3336,3400,5465,101,325,654,768,2960,2961,3418,3549,3821,121,122,123,231,232,233,274,941,208,600,762,1899,1900,3470,5381,112,118,205,108,2111,2159,2557,2558,2621,3172,3314,4486,4487,4488,162,163,176,261,43,126,2128,2131,2488,2489,2519,2671,2684,2917,2918,2919,3168,335,362,1287,1476,1707,1708,1710,1744,2359,2414,2415,2548,3397,3688,81,3123,3218,3219,103,104,2743,3010,3324,3569,730,731,789,885,886,1006,1140,1197,1522,1523,1568,1842,2069,2161,2905,45,46,83,220,221,304,305,306,5377,942,134,136,275,320,2162,1246,795,135,276,319,321,721,1847,1851,40,41,56,86,202,1281,3992,2943,1242,1578,1588,17,95,96,1232,1251,1415,1447,1451,1676,1677,1678,1716,1925,2300,2842,2843,2887,2560,2561,15,2007,2091,2092,2174,2337,2445,2446,2447,2448,2449,2490,2500,2503,2575,2810,2847,2852,2853,2854,3162,444,445,446,3764,129,130,131,224,342,343,349,350,363,406,407,669,670,39,53,94,367,791,2001,2002,2082,2700,2925,3092,4296,42,62,65,89,177,270,322,332,279,280,293,410,621,758,649,650,651,784,822,823,824,825,826,1818,1453,145,294,2860,2861,2862,666,667,691,703,739,765,1030,1081,2864,2890,2891,2945,2965,3035,1785,7065,1490,2871,2043,2241,2736,2765,5316,1446,1474,1528,2208,2368,3378,1380,1805,1809,1812,1623,1719,1213,3205,1389,1063,1174,1299,1326,1328,1697,1609,1610,1611,1764,2613,1880,1881,1882,2357,2774,3147,3577,5334,1443,1605,1422,1423,5502,1004,2759,2760,2941,2949,2950,3539,3583,3584,3585,3586,1245,3058,1003,3243,3247,3279,3298,3354,3395,2310,2311,2377,1440,1483,1250,3149,1337,1681,1913,1915,1916,1917,2093,2266,2363,2364,2403,2435,1470,2650,2651,2652,1120,1227,1301,1300,1390,1442,1657,1872,1874,1910,2227,2370,2453,2476,3762,5949,2282,2285,2286,2331,2366,2367,2375,2690,2691,2697,2718,2719,3346,3396,3399,1461,1607,2809,1534,1535,1557,1558,1538,1628,3026,1585,1905,1906,1907,1908,2420,1595,1618,1620,2026,2027,2028,2029,2079,2438,2510,2579,2627,2698,3072,3133,3273,3281,3438,3616,3799,4233,4234,5324,5463,5499,1614,1615,1654,1655,1748,1749,2142,2509,1662,1664,1667,1671,1674,2909,2910,1702,1718,1760,1836,1891,2073,2215,2588,3167,3420,3620,3626,1288,1289,1709,1691,1772,1773,1774,1802,1700,1783,1784,2228,2229,2230,2400,2434,2498,2666,2968,2974,2975,2983,3038,3322,3323,3343,3494,3937,4843,1752,2461,2462,2253,2041,1726,2120,2121,2123,2166,2334,2379,2380,2429,2459,2481,2516,2517,2642,2659,2661,2716,2805,3044,3045,3046,3047,3075,3192,3193,1738,1798,1799,1804,1986,2339,2431,2277,2304,3231,1781,3597,3598,2047,2048,2049,2050,2436,2437,2474,2797,2993,3004,3005,3361,4597,4598,4599,4703,4704,4705,2114,2115,2487,2882,1831,2365,3052,1840,1953,2045,2046,2559,2773,3156,3203,3328,3380,1860,1861,1896,1972,1973,1975,2211,2212,1956,2194,1954,2036,2083,2539,2540,2031,2033,2209,2216,2362,2704,2737,2102,2484,3430,2077,2245,2246,2035,2085,2086,2835,2845,2846,2118,2119,2292,2294,2640,3007,3093,2915,3275,2165,2306,2382,2132,2134,2135,2279,2315,2733,2934,2146,2590,2592,2533,2536,2537,3002,3127,2185,2187,2188,2199,2201,2202,2203,2319,2260,2341,4025,2994,2996,3311,2248,2483,2995,3213,3331,3517,4550,4551,4552,5383,5467,2231,2232,2233,2237,2238,2239,2240,2255,2256,2258,2261,2262,2263,2775,2776,2777,2976,3269,3296,2267,2302,2303,3401,2290,2326,2296,2322,2515,2351,2345,2346,3080,2485,12748,2343,2344,2413,2416,2424,2425,2550,2646,2695,2967,3066,3387,3131,2494,3295,4310,4312,2502,2504,2596,3869,3876,4084,3071,2771,2520,2586,2587,2607,2676,2678,2694,3179,3198,3558,3286,3185,2632,2672,2734,3391,3088,3161,3222,3914,3415,2838,3091,3227,3240,3312,3488,2801,3244,3944,5407,2892,5342,5385,2389,3051,5501,3347,5498,3356,3386,3506,3525,3033,5820,2913,2927,2928,13025,3024,3025,3202,3256,3122,3060,3640,3153,3294,3086,3289,3504,3507,3667,3129,3130,3220,3221,3223,3271,3272,3282,3287,3388,3421,3264,3483,4044,7058,7059,3409,3543,3555,3628,2798,3815,5614,4647,4671,5153,5174,5241,5397,5850,5987,6115,6142,6257,6321,6341,6449,6544,6583,6666,6851,6930,7542,7831,7832,7833,7834,5000,5031,5043,5409,5301,5302,8477,13562)
            and evc.contract_id not in(4,5,8,10,11,12,13,15,16,18,19,26,27,29,30,34,35,36,37,38,39,43,44,45,46,47,54,56,57,58,63,66,68,71,72,75,76,79,80,83,84,88,90,91,92,94,95,96,99,103,104,105,106,107,108,111,114,117,119,120,125,134,135,136,139,149,159,163,164,167,168,169,170,171,172,173,177,178,179,180,181,182,183,184,185,186,187,188,191,192,193,194,195,196,197,198,202,203,204,206,213,214,222,226,228,230,235,236,237,243,244,246,248,257,259,261,265,267,271,272,273,274,275,276,277,278,279,280,281,283,284,285,287,288,292,296,297,298,301,305,306,308,310,312,314,315,316,321,322,325,328,329,336,337,338,341,343,344,346,348,349,351,354,355,357,358,359,361,366,372,374,375,376,377,382,385,386,388,390,391,392,394,395,407,408,409,413,420,423,425,426,427,428,434,436,437,441,444,445,457,459,461,463,464,466,467,470,477,478,479,480,481,482,483,485,487,489,491,493,494,496,498,500,502,505,507,508,510,515,516,519,520,522,525,526,529,534,536,541,543,544,545,547,548,554,555,556,558,560,569,570,572,574,575,582,583,586,587,592,599,614,619,627,628,635,642,644,663,665,668,670,682,684,686,687,689,691,692,695,697,701,704,710,718,721,724,725,727,740,742,743,744,746,750,752,761,767,769,777,780,789,792,794,795,808,888,910)
            group by events.id
            order by events.id
        ";
        $v1 = $this->v1->query($sql)->result_array();

        echo 'v1 cnt : '.count($v1). '----<br>';

        foreach ($v2 as $item)
        {
            $v2Arr[] = $item['id'];
        }

        foreach ($v1 as $it)
        {
            if(!in_array($it['id'], $v2Arr))
            {
                $v1Arr[] = $it['id'];
            }
        }

        echo 'diff cnt : '.count($v1Arr). '----<br>';
        var_dump($v1Arr);

    }

    /**
     * 양쪽 시점잔액 비교
     */
    function readyPrice()
    {
        //,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,
        set_time_limit(0);
        ini_set('memory_limit','-1');

        $sql = "
            select 
            c.id, (select ifnull(sum(price), 0) from deposit where status in(2,4) and contractId=c.id) - (select ifnull(sum(price), 0) from deposit where status in(3,5,6,7,8) and contractId=c.id) as current_price
            from contract c
            where c.adType=1  -- and c.id=33
            
            order by c.id  
        ";
        $result = $this->master->query($sql)->result_array();

        $cArr = [];
        foreach ($result as $item)
        {
            $cArr[] = $item['id']; //뽑아올 계약번호만 뽑기
        }

        $cStr = implode(',', $cArr);

        $sql2 = "
            select 
            c.id, current_price as current_price1
            from contracts c
            where c.id in(".$cStr.")
            order by c.id  
        ";
        //echo $sql2 ; exit;
        $result3 = $this->v1->query($sql2)->result_array();

        //$result4 = array_merge($result, $result3);
        $i=0;
        foreach ($result as $it)
        {
            foreach ($result3 as $it3)
            {
                if($it['id'] == $it3['id'])
                {
                    if($it['current_price'] != $it3['current_price1'])
                    {
                        echo $it['id'] .'  '.$it['current_price'] .'  '.$it3['current_price1'] .'<br>';
                        $i++;
                    }

                }
            }
        }

        echo 'count--'.$i;
    }


    function callRe()
    {
        $cId = '14,48,49,50,53,59,65,98,100,122,123,166,201,210,302,303,309,331,340,379,383,415,416,417,421,422,431,449,450,456,460,469,506,523,532,557,559,590,600,618,622,632,634,637,657,673,681,699,702,731,754,758,770,788,791,802,804,806,817,818,819,879,890,895,898,902,908,911,919,920,922,926';
        $result = explode(',', $cId);

        foreach ($result as $item)
        {
            $sql1= "select count(*) cnt1 from deposit where contractId= '".$item."' ";
            $result1 = $this->master->query($sql1)->result_array();

            $sql2= "select count(*) cnt2 from payments where contract_id= '".$item."' ";
            $result2 = $this->v1->query($sql2)->result_array();

            echo $item . ' : '. $result1['cnt1']. '----'. $result2['cnt2'].'<br>';
        }

    }

    /**
     * 배너 마이그레이션
     * type 1 bm배너, 2 리스트 배너, 3 메인하단배너
     */
    function bannerProcess()
    {
        $type = $this->uri->segment(3, 1);

        switch ($type)
        {
            case 1:  //bm
                $sql = "select * from bm_banners";
                $table = 'bm_banners';
                break;
            case 2: //list
                $sql = "select * from event_banners";
                $table = 'event_banners';
                break;
            case 3: //main bottom
                $sql = "select * from goodoc_banners";
                $table = 'goodoc_banners';
                break;
        }

        $result2 = $this->v1->query($sql)->result_array();

        foreach ($result2 as $item)
        {
            //echo $item . ' : '. $result2['cnt1']. '----'. $result2['cnt2'].'<br>';

            switch ($type)
            {
                case 1:  //bm

                    //이미지처리
                    $imageUrl = $this->bannerImageProcess(1, $item['image'], $item['id']);

                    //0 이벤트, 1 기획전 번호
                    if($item['destination_type'] == 0)
                    {
                        $type1 = 1;
                    }
                    else
                    {
                        $type1 = 5;
                    }

                    $vis = 1; //기본 미노출

                    if($item['is_visible'] == 1)
                    {
                        $vis = 2;
                    }
                    else if($item['is_visible'] == 2)
                    {
                        $vis = 3;
                    }

                    $iArr = [
                        'id'=>$item['id'],
                        'bannerType'=>1, //배너타입 1 bm, 2 리스트, 3 메인하단
                        'type1'=>$type1, //노출위치. 1 이벤트, 2 굿닥캐스트, 3 병원상세, 4 약국상세, 5 기획전, 6외부링크(타브라우저), 7 외브링크(인앱)
                        'type2'=>'', //배너모양. 1 일반배너, 2 띠배너
                        'targetId'=>$item['destination_id'],
                        'isVisible'=>$vis, //노출여부. 1 미노출, 2 상시노출 ,3 기간노출
                        'regDate'=>$item['created_at'],
                        'modDate'=>$item['updated_at'],
                        'startDate'=>$item['start_on'],
                        'endDate'=>$item['end_on'],
                        'orderBy'=>0,
                        'image'=>$imageUrl
                    ];
                    break;
                case 2: //list
                    //이미지처리
                    $imageUrl = $this->bannerImageProcess(2, $item['image'], $item['id']);

                    //노출위치. 1 이벤트, 2 굿닥캐스트, 3 병원상세, 4 약국상세, 5 기획전, 6외부링크(타브라우저), 7 외브링크(인앱)
                    if($item['destination_type'] == 0)
                    {
                        $type1 = 1;
                    }
                    else if($item['destination_type'] == 1)
                    {
                        $type1 = 2;
                    }
                    else if($item['destination_type'] == 2) //병원, 약국 같은 타입번호 사용함
                    {
                        $type1 = 3;
                    }
                    else if($item['destination_type'] == 3)
                    {
                        $type1 = 5;
                    }
                    else if($item['destination_type'] == 50)
                    {
                        $type1 = 6;
                    }
                    else if($item['destination_type'] == 51)
                    {
                        $type1 = 7;
                    }

                    $vis = 1; //기본 미노출

                    if($item['is_visible'] == 1)
                    {
                        $vis = 2;
                    }
                    else if($item['is_visible'] == 2)
                    {
                        $vis = 3;
                    }

                    $iArr = [
                        'id'=>$item['id'],
                        'bannerType'=>2, //배너타입 1 bm, 2 리스트, 3 메인하단
                        'type1'=>$type1,
                        'type2'=>($item['banner_type'] == 0)? 2:1, //배너모양. 1 일반배너, 2 띠배너
                        'targetId'=>(in_array($item['destination_type'], [50,51]))?$item['destination_url']:$item['destination_id'],
                        'isVisible'=>$vis, //노출여부. 1 미노출, 2 상시노출 ,3 기간노출
                        'regDate'=>$item['created_at'],
                        'modDate'=>$item['updated_at'],
                        'startDate'=>'',
                        'endDate'=>'',
                        'orderBy'=>0,
                        'image'=>$imageUrl
                    ];
                    break;
                case 3: //main bottom
                    //이미지처리
                    $imageUrl = $this->bannerImageProcess(3, $item['image'], $item['id']);

                    //노출위치. 1 이벤트, 2 굿닥캐스트, 3 병원상세, 4 약국상세, 5 기획전, 6외부링크(타브라우저), 7 외브링크(인앱)
                    if($item['destination_type'] == 0)
                    {
                        $type1 = 1;
                    }
                    else if($item['destination_type'] == 1)
                    {
                        $type1 = 2;
                    }
                    else if($item['destination_type'] == 2) //병원, 약국 같은 타입번호 사용함
                    {
                        $type1 = 3;
                    }
                    else if($item['destination_type'] == 3)
                    {
                        $type1 = 6;
                    }
                    else if($item['destination_type'] == 4)
                    {
                        $type1 = 7;
                    }
                    else if($item['destination_type'] == 5)
                    {
                        $type1 = 5;
                    }

                    $vis = 1; //기본 미노출

                    if($item['is_visible'] == 1)
                    {
                        $vis = 2;
                    }
                    else if($item['is_visible'] == 2)
                    {
                        $vis = 3;
                    }

                    $iArr = [
                        'id'=>$item['id'],
                        'bannerType'=>3, //배너타입 1 bm, 2 리스트, 3 메인하단
                        'type1'=>$type1, //노출위치. 1 이벤트, 2 굿닥캐스트, 3 병원상세, 4 약국상세, 5 기획전, 6외부링크(타브라우저), 7 외브링크(인앱)
                        'type2'=>'', //배너모양. 1 일반배너, 2 띠배너
                        'targetId'=>(in_array($item['destination_type'], [3,4]))?$item['destination_url']:$item['destination_id'],
                        'isVisible'=>$vis, //노출여부. 1 미노출, 2 상시노출 ,3 기간노출
                        'regDate'=>$item['created_at'],
                        'modDate'=>$item['updated_at'],
                        'startDate'=>$item['start_on'],
                        'endDate'=>$item['end_on'],
                        'orderBy'=>$item['sort'],
                        'image'=>$imageUrl
                    ];
                    break;
            }

            $this->master->insert($table, $iArr);
        }

    }

    /**
     * 이미지를 s3로 복사하고 풀url을 리턴
     * 각 이미지들은 client 일 경우엔 디렉토리에 client_ 를 붙인다.
     * @param $type 이미지 종류. 1 이벤트리스트, 2 이벤트 정방향, 3 이벤트 상세이미지, 4 기획전 배너
     * @param $image
     * @param $client 1이면 클라이언트 이미지
     * @return string
     */
    function bannerImageProcess($type, $image, $eventId)
    {
        //정방향 image2 /s3/uploads/event/image2/이벤트번호/파일명
        //list image  /uploads/event/image/이벤트번호/파일명
        //둘다 도메인은 같으나 s3라고 들어가있으면 s3에서 불러옴

        //event_info 테이블의 주소는 /uploads/event_info/image/이벤트번호/파일명

        //s3는 sdk를 이용하여 복사하고 나머지는 다운받아서 s3에 올린다.
        //생성규칙은 /uploads/event/이벤트번호/image/파일명 으로 한다.

        //버킷명 정의
        if( ENVIRONMENT === 'development' )
        {
            //$bucketName = 'asset-dev.goodoc.kr';
            $bucketName = 'asset-dev.goodoc.kr';
        }
        else if( ENVIRONMENT === 'testing' )
        {
            $bucketName = 'asset-staging.goodoc.kr';
        }
        else if (ENVIRONMENT === 'production')
        {
            $bucketName = 'asset.goodoc.kr';
        }

        $s3Client = Aws\S3\S3Client::factory(array(
            'region' => 'ap-northeast-2',
            'version' => 'latest',
            'signature' => 'v4',
            'credentials' => array(
                'key'    => S3Key,
                'secret' => S3Secret
            )
        ));

        $image2 = $image;
        if(preg_match("/[\xE0-\xFF][\x80-\xFF][\x80-\xFF]/", $image))
        {
            $image2 = urlencode($image);
        }

        switch ($type)
        {
            case 1: //bm
                $path = 'http://www.goodoc.co.kr/uploads/bm_banner/image/'.$eventId.'/'.$image;
                $toPath = 'images/banner/bm/'.$eventId.'/'.$image2;
                break;
            case 2: //list
                $path = 'http://www.goodoc.co.kr/uploads/event_banner/image/'.$eventId.'/'.$image;
                $toPath = 'images/banner/event/'.$eventId.'/'.$image2;
                break;
            case 3: //main
                $path = 'http://www.goodoc.co.kr/uploads/goodoc_banner/image/'.$eventId.'/'.$image;
                $toPath = 'images/banner/goodoc/'.$eventId.'/'.$image2;
                break;
        }

        echo "image path : ".$path."<br><Br>";
        $fileData = $this->getImage($path);

        //s3 업로드
        $return = $s3Client->putObject(array(
            'Bucket' => $bucketName,
            'Key'    => $toPath,
            //'SourceFile' => $savePath.'/'.$image,
            'Body' => $fileData,
            'ACL'    => 'public-read'
        ));

        //퍼징 id E3BRKK32K1AORL
        $returnImg = 'http://'.$bucketName.'/'.$toPath;

        //dd($returnImg, true);

        return $returnImg;
    }
}