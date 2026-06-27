<?php
defined('BASEPATH') OR exit('No direct script access allowed');

// This can be removed if you use __autoload() in config.php OR use Modular Extensions
/** @noinspection PhpIncludeInspection */
require APPPATH . '/libraries/REST_Controller.php';

/**
 * Advertiser Controller
 * 광고주 계약, 수주 관련 api
 *
 * @category        Controller
 * @author          martin.byun@goodoc.co.kr
 */
class Advertiser extends REST_Controller {

    function __construct()
    {
        parent::__construct();

        $this->load->model(array('advertiser_m', 'common_m', 'ads_m'));
    
        $this->master = $this->load->database('master', true);
    }

    /**
     * @api {post} /advertiser/update  계약 수정(없음)
     * @apiHeader {string} x-api-key="qJqKDATY0D48ZH9hCNW4N6ygHBqtoVz4a4mu0P6A"
     * @apiHeader {string} x-api-token=""
     * @apiHeader {string} x-api-refreshtoken=""
     * @apiHeader {string} x-api-userid=""
     * @apiSampleRequest /api/v1.0/advertiser/update
     * @apiVersion 1.0.0
     * @apiName advertiser Update
     * @apiGroup Advertiser
     * @apiDescription  계약 수정(없음). 재계약버튼 클릭한 계약등록화면에서만 입력가능
     * @apiParam {Number} contractId 계약번호
     * @apiParam {Number} payType="1" 계약금방식. 1 선불, 2 후불
     *
     * @apiSuccess {String="success", "error"} status="success" 상태
     * @apiSuccess {Number} code 코드값
     * @apiSuccess {String} message 계약이 수정되었습니다.
     * @apiSuccess {Object} result null
     *
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *       'status': 'success',
     *       'code': '200',
     *       'message': ' 계약이 수정되었습니다.',
     *       'result': null
     *     }
     *
     * @apiUse FailUpdateError
     * @apiUse NotAuthTokenError
     */
    public function update_post()
    {
        $auth_arr = $this->load->get_vars();

        //필수키
        $data['token'] = $auth_arr['token'];
        $data['refreshToken'] = $auth_arr['refreshToken'];
        $data['users_id'] = $auth_arr['users_id'];

        $data['contractId'] = $this->post('contractId', true);
        $data['payType'] = $this->post('payType', true);

        //필수체크
        $this->_check_value($data);

        //api method별 하드코딩, 권한
        $data['menu_id'] = [110000]; //auth_code_catetory 유저관리 번호 
        //계약 아님? 계약으로 넣어 놓음

        //토큰 유효성 및 권한 체크
        $check = $this->common_m->checkToken($data);

        if($check['status'] == 'error')
        {
            $this->response([
                'status' => $check['status'],
                'code' => $check['code'],
                'message' => $check['message'],
                'result' => is_null( $check['result'] ) ? $check['result'] : (object)  $check['result']
            ], 200);
        }

        $result = $this->contract_m->updateContractInfo($data);

        if($result !== false)
        {
            $this->response([
                'status' => 'success',
                'code' => '200',
                'message' => '계약이 수정되었습니다.',
                'result' => null
            ], 200);
        }
        else
        {
            $this->response([
                'status' => 'error',
                'code' => '605', //update 실패
                'message' => '서버관리자에게 문의하세요.',
                'result' => null
            ], 200);
        }
    }

    /**
     * @api {post} /advertiser/getContractInfo 수주계약 상세조회
     * @apiHeader {string} x-api-key="qJqKDATY0D48ZH9hCNW4N6ygHBqtoVz4a4mu0P6A"
     * @apiHeader {string} x-api-token=""
     * @apiHeader {string} x-api-refreshtoken=""
     * @apiHeader {string} x-api-userid=""
     * @apiSampleRequest /api/v1.0/advertiser/getContractInfo
     * @apiVersion 1.0.0
     * @apiName advertiser getContractInfo
     * @apiGroup Advertiser
     * @apiDescription 수주계약 상세조회 (계약관리)
     * @apiParam {Number} contractId 조회할 계약번호
     * @apiParam {Number} contractOrderId 조회할 수주계약번호
     *
     * @apiSuccess {String="success", "error"} status="success" 상태
     * @apiSuccess {Number} code 코드값
     * @apiSuccess {String} message 빈값
     * @apiSuccess {Object[]} result 배열
     * @apiSuccess {Object[]} result.info 배열
     * @apiSuccess {Number} result.info.id 계약번호
     * @apiSuccess {String} result.info.contractDate 계약일(아래 수주계약일 사용)
     * @apiSuccess {String} result.info.title 계약명
     * @apiSuccess {Number} result.info.adType 계약방식. 이벤트신청, 기간노출
     * @apiSuccess {Number} result.info.adType2 상품종류. 1 이벤트, 2 메인배너, 3 이벤트존메인배너, 4 CPM, 5 기타
     * @apiSuccess {String} result.info.mainContract 주 계약내용
     * @apiSuccess {String} result.info.regDate 계약 등록일시
     * @apiSuccess {String} result.info.modDate 계약 수정일시
     * @apiSuccess {Number} result.info.use 계약진행여부. 1 진행, 2 종료
     * @apiSuccess {Number} result.info.isDelete 삭제여부, 1 삭제
     * @apiSuccess {Number} result.info.channelId 추후사용
     * @apiSuccess {Number} result.info.agencyUserId 영업담당번호
     * @apiSuccess {Number} result.info.manageUserId 관리자번호
     * @apiSuccess {Number} result.info.agencyCompanyId 광고대행사번호
     * @apiSuccess {String} result.info.agencyCompanyName 광고대행사명
     * @apiSuccess {Number} result.info.hospitalType 병원종류. 1 병원, 2 약국, 3 업체
     * @apiSuccess {Number} result.info.hospitalId 병원번호. hospitalType에 따라 변동
     * @apiSuccess {String} result.info.hospitalName 병원명
     * @apiSuccess {String} result.info.hospitalChargeName 병원담당 이름
     * @apiSuccess {String} result.info.hospitalChargePhone 병원담당 전화
     * @apiSuccess {String} result.info.hospitalChargeEmail 병원담당 이메일
     * @apiSuccess {String} result.info.hospitalNetwork 네트워크 병원 인지 확인
     * @apiSuccess {String} result.info.taxChargeName 세금계산서 대표자명
     * @apiSuccess {String} result.info.taxBusinessNo 세금계산서 사업자번호
     * @apiSuccess {String} result.info.taxchargeEmail 세금계산서 받을 이메일
     * @apiSuccess {String} result.info.taxIssueRequestDate 세금계산서 발행요청일
     * @apiSuccess {String} result.info.taxIssueDate 세금계산서 발행일(날짜가 있으면 발행한 것)
     * @apiSuccess {Number} result.info.payType 계약방식. 1 선불, 2 후불
     * @apiSuccess {String} result.info.paymentStatus 입금상태. 0021 성공, 0031 실패, 0051 입금대기
     * @apiSuccess {String} result.info.hospitalNetwork 병원종류. 네트워크모병원, 네트워크자병원, 일반병원
     * @apiSuccess {String} result.info.childHospitalId 네트워크모병원일 경우 자병원 리스트. 병원번호|병원명, 병원번호|병원명
     * @apiSuccess {String} result.info.adsId 연결된 광고번호. id:광고번호, name:광고명
     * @apiSuccess {Number} result.info.contractType 계약구분. 1 신규, 2 재계약
     * @apiSuccess {String} result.info.contractOrderDate 수주계약일
     * @apiSuccess {Number} result.info.adPrice 수주 금액
     * @apiSuccess {String} result.info.depositDate  광고금액 입금일시, 입금여부로 사용
     * @apiSuccess {Object[]} result.depositList 배열
     * @apiSuccess {Number} result.depositList.id 원장번호
     * @apiSuccess {Number} result.depositList.status 상태. 1 수주, 2 계약충전, 3 소진, 4 기타충전, 5 환불수수료, 6 발행 환불, 7 계약 환불, 8 기타 소진, 9 발행취소, 10 계약취소, 11 이월 소진, 12 이월 충전
     * @apiSuccess {Number} result.depositList.isMinus 마이너스 여부. 1 마이너스, 0 플러스
     * @apiSuccess {Number} result.depositList.contractId 계약번호
     * @apiSuccess {Number} result.depositList.contractOrderId 수주계약번호
     * @apiSuccess {Number} result.depositList.usersId 유저번호
     * @apiSuccess {String} result.depositList.memo 메모
     * @apiSuccess {Number} result.depositList.price 금액
     * @apiSuccess {String} result.depositList.regDate 등록일시
     * @apiSuccess {String} result.depositList.modDate 수정일
     *
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *       'status': 'success',
     *       'code': '200',
     *       'message': '',
     *       "result": {
     *          "info" : {},
     *          "depositList" : {}
     *        }
     *     }
     *
     * @apiUse FailGetError
     * @apiUse NotAuthTokenError
     */
    function getContractInfo_post()
    {
        $auth_arr = $this->load->get_vars();
        
        ini_set('memory_limit', '-1');

        //필수키
        $data['token'] = $auth_arr['token'];
        $data['refreshToken'] = $auth_arr['refreshToken'];
        $data['users_id'] = $auth_arr['users_id'];
        $data['contractId'] = $this->post('contractId', true); //계약번호
        $data['contractOrderId'] = $this->post('contractOrderId', true); //계약번호

        //필수체크
        $this->_check_value($data);

        //api method별 하드코딩
        $data['menu_id'] = [110000]; //auth_code_catetory 유저관리 번호
        //계약 아님? 계약으로 넣어 놓음

        //토큰 유효성 및 권한 체크
        $check = $this->common_m->checkToken($data);

        if($check['status'] == 'error')
        {
            $this->response([
                'status' => $check['status'],
                'code' => $check['code'],
                'message' => $check['message'],
                'result' => is_null( $check['result'] ) ? $check['result'] : (object)  $check['result']
            ], 200);
        }

        //계약정보 가져올때 병원정보도 join 해서 가져와야 한다. 네트워크 유무 체크
        //dev - goodoc_dev - hospital_network_map 조인.
        $resultInfo = $this->advertiser_m->getContractInfo($data);
        //dd($resultInfo);
        $resultDeposit = $this->advertiser_m->getDepositList($data);

        $arr = $l2Arr = [];
       
        if($resultInfo !== false)
        {
            $arr = $resultInfo['adsId'];
            if($arr != '')
            {
                $exp = explode('>>>', $arr); //dd($exp, false);

                $c=0;
                foreach ($exp as $item)
                {
                    $l2Arr = explode('|', $item); //dd($l2Arr, false);
                    if (!isset($l2Arr[1]) || is_null($l2Arr[1]) || empty($l2Arr[1])) {
                        continue;
                    }
                    $rArr[$c]['id'] = $l2Arr[0];
                    $rArr[$c]['name'] = $l2Arr[1];
                    $c++;
                }

                //치환
                $resultInfo['adsId'] = $rArr;
            }

            $result =[
                'info'=>$resultInfo,
                'depositList'=>$resultDeposit
            ];
            $this->response([
                'status' => 'success',
                'code' => '200',
                'message' => '',
                'result' => (object) $result
            ], 200);
        }
        else
        {
            $result =[
                'info'=>[],
                'depositList'=> []
            ];
            $this->response([
                'status' => 'success',
                'code' => '610', //정보 get 실패
                'message' => '데이터가 없습니다.',
                'result' =>  (object) $result
            ], 200);
        }
    }

    /**
     * @api {post} /advertiser/getContractList 병원어드민 계약 리스트 조회
     * @apiHeader {string} x-api-key="qJqKDATY0D48ZH9hCNW4N6ygHBqtoVz4a4mu0P6A"
     * @apiHeader {string} x-api-token=""
     * @apiHeader {string} x-api-refreshtoken=""
     * @apiHeader {string} x-api-userid=""
     * @apiSampleRequest /api/v1.0/advertiser/getContractList
     * @apiVersion 1.0.0
     * @apiName advertiser getContractList
     * @apiGroup Advertiser
     * @apiDescription  계약 리스트 조회.
     * @apiParam {Number} limit 페이징시 한 페이지에 노출할 리스트수
     * @apiParam {Number} page="1" 페이지번호
     *
     * @apiSuccess {String="success", "error"} status="success" 상태
     * @apiSuccess {Number} code 코드값
     * @apiSuccess {String} message 빈값
     * @apiSuccess {Object[]} result 배열
     * @apiSuccess {Object[]} result.eventList 계약리스트. eventList 이벤트리스트, bannerList 배너리스트, cpmList cpm리스트
     * @apiSuccess {Number} result.eventList.contractId 계약번호
     * @apiSuccess {Number} result.eventList.contractOrderId 수주계약번호
     * @apiSuccess {Number} result.eventList.lastContractStatus 최근 수주계약상태. 1 정상, 2 발행 환불, 3 발행 취소, 4 계약 취소, 5 계약 환불, 6 이월(종료) 
     * @apiSuccess {String} result.eventList.lastPaymentStatus 입금상태 (위 수주계약상태가 1인 경우 사용). 0021 성공, 0031 실패, 0051 입금대기
     * @apiSuccess {String} result.eventList.lastPaymentVbankNo  가상계좌번호/입금기한. 01|43244323123|20181012235959, 은행코드|계좌번호|입금기한
     * @apiSuccess {String} result.eventList.adCount 이벤트 매핑 수
     * @apiSuccess {String} result.eventList.title 계약명
     * @apiSuccess {Number} result.eventList.oPrice 계약금
     * @apiSuccess {String} result.eventList.regDate 계약 등록일시
     * @apiSuccess {Number} result.eventList.chargePrice 충전금액
     * @apiSuccess {Number} result.eventList.usePrice 소진금액
     * @apiSuccess {Number} result.eventList.price1 사용안함
     * @apiSuccess {Number} result.eventList.price2 사용안함
     * @apiSuccess {Number} result.eventList.price3 사용안함
     * @apiSuccess {Number} result.eventList.price4 사용안함
     * @apiSuccess {String} result.eventList.isNetwork 네트워크병원 여부
     * @apiSuccess {Number} result.eventList.hospitalID 병원번호
     * @apiSuccess {Number} result.eventList.adType2 상품종류. 이벤트, 메인배너
     * @apiSuccess {Number} result.eventList.hospitalChargeName 병원담당 이름
     * @apiSuccess {Number} result.eventList.hospitalChargePhone 병원담당 전화
     * @apiSuccess {Number} result.eventList.hospitalChargeEmail 병원담당 이메일
     * @apiSuccess {Number} result.eventList.totalOrder 수주금액 (사용안함)
     * @apiSuccess {Number} result.eventList.totalCharge 총 충전금액 (사용안함)
     * @apiSuccess {Number} result.eventList.totalUse 총 소진금액 (사용안함)
     * @apiSuccess {Number} result.eventList.totalReady 총 시점잔액
     * @apiSuccess {Number} result.eventList.contractChargePrice 계약충전
     * @apiSuccess {Number} result.eventList.dbUsePrice db소진
     * @apiSuccess {Number} result.eventList.etcPrice 환불수수료+기타충전+기타소진
     * @apiSuccess {Number} result.eventList.taxProfit 세발매출 = 수주-(소진+환불)
     * @apiSuccess {Number} result.eventList.readyPrice 잔액
     * @apiSuccess {Object[]} result.bannerList TODO 현재는 개발 안됨
     * @apiSuccess {Object[]} result.cpmList TODO 현재는 개발 안됨
     * @apiSuccess {Number} result.totCnt 전체 이벤트 수
     *
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *       'status': 'success',
     *       'code': '200',
     *       'message': '',
     *       'result': {
     *          "eventList" : {},
     *          "bannerList" : {},  
     *          "cpmList" : {}, 
     *          "totCnt" : 0
     *        }
     *     }
     *
     * @apiUse FailGetError
     * @apiUse NotAuthTokenError
     */
    public function getContractList_post()
    {
        $auth_arr = $this->load->get_vars();

        //필수키
        $data['token'] = $auth_arr['token'];
        $data['refreshToken'] = $auth_arr['refreshToken'];
        $data['users_id'] = $auth_arr['users_id'];
        $data['limit'] = $this->post('limit', true); //1페이지에 표시하는 리스트수
        $data['page'] = $this->post('page', true); //페이지 번호

        //필수체크
        $this->_check_value($data);

        //검색어
//        $data['agencyUserId'] = $this->post('agencyUserId', true);
//        $data['manageUserId'] = $this->post('manageUserId', true);
//        $data['advertiserStatus'] = $this->post('advertiserStatus', true);
//        $data['balanceStatus'] = $this->post('balanceStatus', true);
//        $data['adType2'] = $this->post('adType2', true);
//        $data['searchType'] = $this->post('searchType', true);
//        $data['searchWord'] = $this->post('searchWord', true);

        //api method별 하드코딩
        $data['menu_id'] = [110000]; //auth_code_catetory 유저관리 번호
        //계약 아님? 계약으로 넣어 놓음

        //토큰 유효성 및 권한 체크
        $check = $this->common_m->checkToken($data);

        if($check['status'] == 'error')
        {
            $this->response([
                'status' => $check['status'],
                'code' => $check['code'],
                'message' => $check['message'],
                'result' => is_null( $check['result'] ) ? $check['result'] : (object)  $check['result']
            ], 200);
        }

        //배열중 빈값 제거
        $data_arr = array_filter($data, function($v){
            return $v !== false && !is_null($v) && ($v != '' || $v == '0');
        }); //var_dump($data_arr); exit;

        $list = $this->advertiser_m->getContractList($data_arr);

        if($list !== false)
        {
            $list['page'] = $data['page'];
            $this->response([
                'status' => 'success',
                'code' => '200',
                'message' => '',
                'result' => (object) $list
            ], 200);
        }
        else
        {
            $this->response([
                'status' => 'sucess',
                'code' => '610', //정보 get 실패
                'message' => '데이터가 없습니다.',
                'result' => ['eventList'=>[], 'bannerList'=>[], 'cpmList'=>[], 'totCnt'=>0]
            ], 200);
        }
    }

    /**
     * @api {post} /advertiser/getContractOrderList 병원어드민 수주계약 리스트 조회
     * @apiHeader {string} x-api-key="qJqKDATY0D48ZH9hCNW4N6ygHBqtoVz4a4mu0P6A"
     * @apiHeader {string} x-api-token=""
     * @apiHeader {string} x-api-refreshtoken=""
     * @apiHeader {string} x-api-userid=""
     * @apiSampleRequest /api/v1.0/advertiser/getContractOrderList
     * @apiVersion 1.0.0
     * @apiName advertiser getContractOrderList
     * @apiGroup Advertiser
     * @apiDescription  수주계약 리스트 조회.
     * @apiParam {Number} contractId 계약번호
     * @apiParam {Number} limit 페이징시 한 페이지에 노출할 리스트수
     * @apiParam {Number} page="1" 페이지번호
     *
     * @apiSuccess {String="success", "error"} status="success" 상태
     * @apiSuccess {Number} code 코드값
     * @apiSuccess {String} message 빈값
     * @apiSuccess {Object[]} result 배열
     * @apiSuccess {Object[]} result.list 수주계약리스트
     * @apiSuccess {Number} result.list.contarctOrderId 계약번호
     * @apiSuccess {String} result.list.contractDate 수주계약일
     * @apiSuccess {Number} result.list.contractStatus 수주계약상태. 1 정상, 2 발행 환불, 3 발행 취소, 4 계약 취소, 5 계약 환불
     * @apiSuccess {Number} result.list.contractType 수주계약구분. 1 신규, 2 재계
     * @apiSuccess {String} result.list.title 계약명
     * @apiSuccess {Number} result.list.adType 상품종류. 1 이벤트신청, 2 기간노출
     * @apiSuccess {Number} result.list.adType2 상품종류. 1 이벤트, 2 메인배너, 3 CPM
     * @apiSuccess {Number} result.list.evnetCount 매핑된 이벤트수
     * @apiSuccess {Number} result.list.contractChargePrice 충전금
     * @apiSuccess {Number} result.list.oPrice 계약금
     * @apiSuccess {String} result.list.regDate 등록일시
     * @apiSuccess {String} result.list.depositDate 입금일시
     * @apiSuccess {String} result.list.taxIssueDate 세금계산서발행일시
     * @apiSuccess {Number} result.totCnt 전체 수주계약 수
     *
     * @apiSuccessExample Success-Respons:
     *     HTTP/1.1 200 OK
     *     {
     *       'status': 'success',
     *       'code': '200',
     *       'message': '',
     *       'result': {
     *          'list' : {},
     *          'totCnt' : 0
     *        }
     *     }
     *
     * @apiUse FailGetError
     * @apiUse NotAuthTokenError
     */
    public function getContractOrderList_post()
    {
        $auth_arr = $this->load->get_vars();

        //필수키
        $data['token'] = $auth_arr['token'];
        $data['users_id'] = $auth_arr['users_id'];
        $data['refreshToken'] = $auth_arr['refreshToken'];
        $data['contractId'] = $this->post('contractId', true);
        $data['limit'] = $this->post('limit', true); //1페이지에 표시하는 리스트수
        $data['page'] = $this->post('page', true); //페이지 번호

        //필수체크
        $this->_check_value($data);

        //api method별 하드코딩
        $data['menu_id'] = [110000]; //auth_code_catetory 유저관리 번호
        //계약 아님? 계약으로 넣어 놓음

        //토큰 유효성 및 권한 체크
        $check = $this->common_m->checkToken($data);

        if($check['status'] == 'error')
        {
            $this->response([
                'status' => $check['status'],
                'code' => $check['code'],
                'message' => $check['message'],
                'result' => is_null( $check['result'] ) ? $check['result'] : (object)  $check['result']
            ], 200);
        }

        $list = $this->advertiser_m->getContractOrderList($data);

        if($list !== false)
        {
            $this->response([
                'status' => 'success',
                'code' => '200',
                'message' => '',
                'result' => ['list' => $list['list'] , 'totCnt' => $list['totCnt']]
            ], 200);
        }
        else
        {
            $this->response([
                'status' => 'success',
                'code' => '610', //정보 get 실패
                'message' => '데이터가 없습니다.',
                'result' => ['list' =>[], 'totCnt' => 0]
            ], 200);
        }
    }

    /**
     * @api {post} /advertiser/getDepositList  계약 원장 리스트 조회
     * @apiHeader {string} x-api-key="qJqKDATY0D48ZH9hCNW4N6ygHBqtoVz4a4mu0P6A"
     * @apiHeader {string} x-api-token=""
     * @apiHeader {string} x-api-refreshtoken=""
     * @apiHeader {string} x-api-userid=""
     * @apiSampleRequest /api/v1.0/advertiser/getDepositList
     * @apiVersion 1.0.0
     * @apiName advertiser getDepositList
     * @apiGroup Advertiser
     * @apiDescription  계약 원장 리스트 조회.
     * @apiParam {Number} contractId 계약번호
     * @apiParam {Number} contractOrderId 수주번호
     *
     * @apiSuccess {String="success", "error"} status="success" 상태
     * @apiSuccess {Number} code 코드값
     * @apiSuccess {String} message 빈값
     * @apiSuccess {Object[]} result 배열. 원장리스트
     * @apiSuccess {Object[]} result.list 배열
     * @apiSuccess {Number} result.list.id 원장번호
     * @apiSuccess {Number} result.list.status 상태. 1 수주, 2 계약충전, 3 소진, 4 기타충전, 5 환불수수료, 6 발행 환불, 7 계약 환불, 8 기타 소진, 9 발행취소, 10 계약취소, 11 이월 소진, 12 이월 충전
     * @apiSuccess {Number} result.list.isMinus 마이너스 여부. 1 마이너스, 0 플러스
     * @apiSuccess {Number} result.list.contractId 계약번호
     * @apiSuccess {Number} result.list.contractOrderId 수주계약번호
     * @apiSuccess {Number} result.list.usersId 유저번호
     * @apiSuccess {String} result.list.memo 메모
     * @apiSuccess {Number} result.list.price 금액
     * @apiSuccess {String} result.list.regDate 등록일시
     * @apiSuccess {String} result.list.modDate 수정일
     *
     *  @apiSuccessExample Success-Respons:
     *     HTTP/1.1 200 OK
     *     {
     *       'status': 'success',
     *       'code': '200',
     *       'message': '',
     *       'result': {
     *          'list' : {}
     *        }
     *     }
     * 
     * @apiUse NotAuthTokenError
     */
    public function getDepositList_post()
    {
        $auth_arr = $this->load->get_vars();

        //필수키
        $data['token'] = $auth_arr['token'];
        $data['users_id'] = $auth_arr['users_id'];
        $data['refreshToken'] = $auth_arr['refreshToken'];
        $data['contractId'] = $this->post('contractId', true);
        $data['contractOrderId'] = $this->post('contractOrderId', true);

        //필수체크
        $this->_check_value($data);

        //api method별 하드코딩
        $data['menu_id'] = [110000]; //auth_code_catetory 유저관리 번호
        //계약 아님? 계약으로 넣어 놓음

        //토큰 유효성 및 권한 체크
        $check = $this->common_m->checkToken($data);

        if($check['status'] == 'error')
        {
            $this->response([
                'status' => $check['status'],
                'code' => $check['code'],
                'message' => $check['message'],
                'result' => is_null( $check['result'] ) ? $check['result'] : (object)  $check['result']
            ], 200);
        }

        $list = $this->advertiser_m->getDepositList($data);

        if($list != false)
        {
            $this->response([
                'status' => 'success',
                'code' => '200',
                'message' => '',
                'result' => (object) ['list' => $list]
            ], 200);
        }
        else
        {
            $this->response([
                'status' => 'success',
                'code' => '200', //정보 get 실패
                'message' => '데이터가 없습니다.',
                'result' =>  (object) ['list' => []]
            ], 200);
        }
    }

    /**
     * @api {post} /advertiser/register 수주 계약 등록(재계약)
     * @apiHeader {string} x-api-key="qJqKDATY0D48ZH9hCNW4N6ygHBqtoVz4a4mu0P6A"
     * @apiHeader {string} x-api-token=""
     * @apiHeader {string} x-api-refreshtoken=""
     * @apiHeader {string} x-api-userid=""
     * @apiSampleRequest /api/v1.0/advertiser/register
     * @apiVersion 1.0.0
     * @apiName advertiser Register
     * @apiGroup Advertiser
     * @apiDescription 수주 계약 등록(재계약)
     * @apiParam {Number} hospitalType 계약대상유형. 1 병원, 2 약국, 3 업체
     * @apiParam {Number} hospitalId 계약병원 번호 (병원 조회시 받아 hidden 처리)
     * @apiParam {String} hospitalName 계약병원 이름
     * @apiParam {Number} isNetwork 네트워크병원 여부 및 일반병원. 0 일반, 1 네트워크 모병원, 2 네트워크 자병원
     * @apiParam {String} contractDate 계약일
     * @apiParam {String} title 수주 계약 제목
     * @apiParam {Number} adType 계약방식. 1 이벤트신청, 2 기간노출
     * @apiParam {Number} adType2 상품종류. 1 이벤트, 2 메인배너, 3 이벤트존메인배너
     * @apiParam {Number} adPrice 계약금액(숫자만)
     * @apiParam {Number} [agencyCompanyId] 대행사 번호
     * @apiParam {String} [agencyCompanyName] 대행사명
     * @apiParam {String} [agencyCompanyFeeRate] 대행수수료 퍼센트. 0. 5, 10, 15, 20, 25, 30%
     * @apiParam {Number} agencyUserId 영업담당자 회원번호
     * @apiParam {Number} [manageUserId] 관리담당자 회원번호
     * @apiParam {String} [memo] 영업팀 메모
     * @apiParam {String} [hospitalChargeName] 병원담당자명/직함
     * @apiParam {String} [hospitalChargePhone] 병원담당자 연락처
     * @apiParam {String} [hospitalChargeEmail] 병원담당자 이메일
     * @apiParam {String} taxChargeName 대표자명
     * @apiParam {String} taxBusinessNo 사업자등록번호
     * @apiParam {String} taxChargeEmail 세금계산서 Email
     * @apiParam {String} [taxIssueRequestDate] 세금계산서 발행요청일
     * @apiParam {String} [taxMemo] 세금계산서 메모
     * @apiParam {Number} [contractId] 계약번호 (재계약일 경우)
     * @apiParam {Number} [contractOrderId] 수주계약번호 (재계약일 경우)
     *
     * @apiSuccess {String="success", "error"} status="success" 상태
     * @apiSuccess {Number} code 코드값
     * @apiSuccess {String} message 수주 계약 등록되었습니다.
     * @apiSuccess {Object[]} result 배열
     * @apiSuccess {Object[]} result.info 수주 계약 등록 결과값 배열
     * @apiSuccess {Number} result.info.contractOrderId 수주계약번호
     *
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *       'status': 'success',
     *       'code': '200',
     *       'message': '수주 계약 등록되었습니다.',
     *       'result': {
     *          'info' : {}
     *        }
     *     }
     *
     * @apiUse RegistFailError
     * @apiUse NotAuthTokenError
     */
    public function register_post()
    {
        $this->load->model('contractOrder_m');

        $auth_arr = $this->load->get_vars();

        //필수키
        $data['token'] = $auth_arr['token'];
        $data['refreshToken'] = $auth_arr['refreshToken'];
        $data['users_id'] = $auth_arr['users_id'];

        $data['hospitalType'] = $this->post('hospitalType', true);
        $data['hospitalId'] = $this->post('hospitalId', true);
        $data['hospitalName'] = $this->post('hospitalName', true);
        $data['contractDate'] = $this->post('contractDate', true);
        $data['contractType'] = 2;
        $data['title'] = $this->post('title', true);
        $data['isNetwork']    = $this->post('isNetwork', true);
        $data['adType'] = $this->post('adType', true);
        $data['adType2'] = $this->post('adType2', true);
        $data['adPrice'] = $this->post('adPrice', true);
        $data['agencyUserId'] = $this->post('agencyUserId', true);

        $data['taxChargeName'] = $this->post('taxChargeName', true);
        $data['taxBusinessNo'] = $this->post('taxBusinessNo', true);
        $data['taxChargeEmail'] = $this->post('taxChargeEmail', true);


        //필수체크
        $this->_check_value($data);

        //biz-709
        $data['manageUserId'] = $this->post('manageUserId', true);

        //180703 biz-469, 452
        $data['hospitalChargeName'] = $this->post('hospitalChargeName', true);
        $data['hospitalChargePhone'] = $this->post('hospitalChargePhone', true);
        $data['hospitalChargeEmail'] = $this->post('hospitalChargeEmail', true);
        $data['taxIssueRequestDate'] = $this->post('taxIssueRequestDate', true);

        $data['memo'] = $this->post('memo', true);
        $data['taxMemo'] = $this->post('taxMemo', true);
        $data['contractId'] = $this->post('contractId', true);
        $data['contractOrderId'] = $this->post('contractOrderId', true);
        $data['agencyCompanyId'] = $this->post('agencyCompanyId', true);
        $data['agencyCompanyName'] = $this->post('agencyCompanyName', true);
        $data['agencyCompanyFeeRate'] = $this->post('agencyCompanyFeeRate', true);


        //재계약일 경우 계약번호 체크
        if($data['contractType'] == 2)
        {
            if($data['contractId'] == '')
            {
                $this->response([
                    'status' => 'error',
                    'code' => '614', //등록 실패
                    'message' => '재계약일 경우 계약번호는 필수입니다.',
                    'result' => null
                ], 200);
            }

            if($data['contractOrderId'] == '')
            {
                $this->response([
                    'status' => 'error',
                    'code' => '614', //등록 실패
                    'message' => '재계약일 경우 수주계약번호는 필수입니다.',
                    'result' => null
                ], 200);
            }
        }

        //api method별 하드코딩, 권한
        $data['menu_id'] = [110000]; //auth_code_catetory 유저관리 번호
        //계약 아님? 계약으로 넣어 놓음

        //토큰 유효성 및 권한 체크
        $check = $this->common_m->checkToken($data);

        if($check['status'] == 'error')
        {
            $this->response([
                'status' => $check['status'],
                'code' => $check['code'],
                'message' => $check['message'],
                'result' => is_null( $check['result'] ) ? $check['result'] : (object)  $check['result']
            ], 200);
        }

        $data_arr1 = array_filter($data, function($v){
            return $v !== false && !is_null($v) && ($v != '' || $v == '0');
        });

        //로직이 동일하여 그대로 사용
        $result = $this->contractOrder_m->setContractInfo($data_arr1);

        if($result !== false)
        {
            $this->response([
                'status' => 'success',
                'code' => '200',
                'message' => '수주 계약 등록되었습니다.',
                'result' => (object) ['info' => ['contractOrderId'=>$result] ]
            ], 200);
        }
        else
        {
            $this->response([
                'status' => 'error',
                'code' => '614', //등록 실패
                'message' => '서버관리자에게 문의하세요.',
                'result' => (object) ['info' => []]
            ], 200);
        }
    }

    /**
     * @api {post} /advertiser/dashBoard  대시보드
     * @apiHeader {string} x-api-key="qJqKDATY0D48ZH9hCNW4N6ygHBqtoVz4a4mu0P6A"
     * @apiHeader {string} x-api-token=""
     * @apiHeader {string} x-api-refreshtoken=""
     * @apiHeader {string} x-api-userid=""
     * @apiSampleRequest /api/v1.0/advertiser/dashBoard
     * @apiVersion 1.0.0
     * @apiName advertiser dashBoard
     * @apiGroup Advertiser
     * @apiDescription  대시보드
     *
     * @apiSuccess {String="success", "error"} status="success" 상태
     * @apiSuccess {Number} code 코드값
     * @apiSuccess {String} message 빈값
     * @apiSuccess {Object[]} result
     * @apiSuccess {Object[]} result.info
     * @apiSuccess {Number} result.info.return1 잔여충전금(cpa만)
     * @apiSuccess {Number} result.info.return2 진행중인 이벤트수
     * @apiSuccess {Number} result.info.return3 이벤트 검토수
     * @apiSuccess {Number} result.info.return4 콜 완료수
     * @apiSuccess {Number} result.info.return5 미획인수
     * @apiSuccess {Number} result.info.return6 병원후기 갯수
     * @apiSuccess {Number} result.info.return7 평점 평균
     *
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *       'status': 'success',
     *       'code': '200',
     *       'message': '',
     *       'result': {
     *          'info' : {}
     *        }
     *     }
     *
     * @apiUse FailGetError
     * @apiUse NotAuthTokenError
     */
    public function dashBoard_post()
    {
        $auth_arr = $this->load->get_vars();

        //필수키
        $data['token'] = $auth_arr['token'];
        $data['refreshToken'] = $auth_arr['refreshToken'];
        $data['users_id'] = $auth_arr['users_id'];

        //필수체크
        $this->_check_value($data);

        //api method별 하드코딩, 권한
        $data['menu_id'] = [110000]; //??이건 비어있었지만 계약 쪽에 있으니 계약 넣음

        //토큰 유효성 및 권한 체크
        $check = $this->common_m->checkToken($data);

        if($check['status'] == 'error')
        {
            $this->response([
                'status' => $check['status'],
                'code' => $check['code'],
                'message' => $check['message'],
                'result' => is_null( $check['result'] ) ? $check['result'] : (object)  $check['result']
            ], 200);
        }

        $result = $this->advertiser_m->getDashBoard($data);

        if($result !== false)
        {
            $this->response([
                'status' => 'success',
                'code' => '200',
                'message' => '',
                'result' => (object) [
                    'info' => [
                        'return1'=>$result['return1'],
                        'return2'=>$result['return2'],
                        'return3'=>$result['return3'],
                        'return4'=>$result['return4'],
                        'return5'=>$result['return5'],
                        'return6'=>$result['return6'],
                        'return7'=>$result['return7']
                    ]
                ]
            ], 200);
        }
        else
        {
            $this->response([
                'status' => 'success',
                'code' => '610', //update 실패
                'message' => '데이터가 없습니다.',
                'result' => (object) ['info'=>[]]
            ], 200);
        }
    }

    /**
     * @api {post} /advertiser/requestInspect  개별 승인 및 종료 요청
     * @apiHeader {string} x-api-key="qJqKDATY0D48ZH9hCNW4N6ygHBqtoVz4a4mu0P6A"
     * @apiHeader {string} x-api-token=""
     * @apiHeader {string} x-api-refreshtoken=""
     * @apiHeader {string} x-api-userid=""
     * @apiSampleRequest /api/v1.0/advertiser/requestInspect
     * @apiVersion 1.0.0
     * @apiName advertiser requestInspect
     * @apiGroup Advertiser
     * @apiDescription  개별 승인 및 종료 요청
     * @apiParam {Number} adsId 광고번호
     * @apiParam {Number} type 검수요청타입. 1 승인, 2 종료
     *
     * @apiSuccess {String="success", "error"} status="success" 상태
     * @apiSuccess {Number} code 코드값
     * @apiSuccess {String} message 요청되었습니다.
     * @apiSuccess {Object} result null
     *
     * @apiUse NotAuthTokenError
     */
    public function requestInspect_post()
    {
        $auth_arr = $this->load->get_vars();

        //필수키
        $data['token'] = $auth_arr['token'];
        $data['refreshToken'] = $auth_arr['refreshToken'];
        $data['users_id'] = $auth_arr['users_id'];
        $data['inspectId'] = $this->post('inspectId', true);
        $data['type'] = $this->post('type', true);

        //필수체크
        $this->_check_value($data);

        //api method별 하드코딩
        $data['menu_id'] = [110000]; //auth_code_catetory 유저관리 번호
        //계약 아님? 계약으로 넣어 놓음

        //토큰 유효성 및 권한 체크
        $check = $this->common_m->checkToken($data);

        if($check['status'] == 'error')
        {
            $this->response([
                'status' => $check['status'],
                'code' => $check['code'],
                'message' => $check['message'],
                'result' => is_null( $check['result'] ) ? $check['result'] : (object)  $check['result']
            ], 200);
        }

        $list = $this->advertiser_m->requestInspect($data);

        if($list != false)
        {
            $this->response([
                'status' => 'success',
                'code' => '200',
                'message' => '요청되었습니다.',
                'result' => null
            ], 200);
        }
        else
        {
            $this->response([
                'status' => 'error',
                'code' => '200', //정보 get 실패
                'message' => '데이터가 없습니다.',
                'result' => null
            ], 200);
        }
    }

    /**  ------------------------------------------ */

    /**
     * @apiDefine AleadyUserError
     *
     * @apiError AleadyUser 이미 가입하셨군요.
     *
     * @apiErrorExample Error-Response:
     *     HTTP/1.1 200 OK
     *     {
     *       "status": "error",
     *       "code": "600",
     *       "message": "이미 가입하셨군요.",
     *       "result": null
     *     }
     */

    /**
     * @apiDefine RegistFailError
     *
     * @apiError RegistFail 등록 실패
     *
     * @apiErrorExample Error-Response:
     *     HTTP/1.1 200 OK
     *     {
     *       "status": "error",
     *       "code": "614",
     *       "message": "등록 실패",
     *       "result": null
     *     }
     */

    /**
     * @apiDefine AskServerAdminError
     *
     * @apiError AskServerAdmin 유저 테이블 입력실패
     *
     * @apiErrorExample Error-Response:
     *     HTTP/1.1 200 OK
     *     {
     *       "status": "error",
     *       "code": "602",
     *       "message": "서버관리자에게 문의하세요.",
     *       "result": null
     *     }
     */

    /**
     * @apiDefine AskServerAdminError2
     *
     * @apiError AskServerAdmin2 토큰 테이블 입력실패
     *
     * @apiErrorExample Error-Response:
     *     HTTP/1.1 200 OK
     *     {
     *       "status": "error",
     *       "code": "601",
     *       "message": "서버관리자에게 문의하세요.",
     *       "result": null
     *     }
     */

    /**
     * @apiDefine NotAuthTokenError
     *
     * @apiError NotAuthToken 토큰 검증 에러
     *
     * @apiErrorExample Error-Response:
     *     HTTP/1.1 200 OK
     *     {
     *       "status": "error",
     *       "code": "603",
     *       "message": "유효하지 않은 토큰입니다",
     *       "result": null
     *     }
     */

    /**
     * @apiDefine NotFoundUserError
     *
     * @apiError NotFoundUser 존재하지 않는 회원정보
     *
     * @apiErrorExample Error-Response:
     *     HTTP/1.1 200 OK
     *     {
     *       "status": "error",
     *       "code": "604",
     *       "message": "서버관리자에게 문의하세요.",
     *       "result": null
     *     }
     */

    /**
     * @apiDefine FailUpdateError
     *
     * @apiError FailUpdate 업데이트 실패
     *
     * @apiErrorExample Error-Response:
     *     HTTP/1.1 200 OK
     *     {
     *       "status": "error",
     *       "code": "605",
     *       "message": "서버관리자에게 문의하세요.",
     *       "result": null
     *     }
     */

    /**
     * @apiDefine FailGetError
     *
     * @apiError FailGet 정보 Get 실패
     *
     * @apiErrorExample Error-Response:
     *     HTTP/1.1 200 OK
     *     {
     *       "status": "error",
     *       "code": "610",
     *       "message": "서버관리자에게 문의하세요.",
     *       "result": null
     *     }
     */

    /**
     * @apiDefine AleadyDeviceError
     *
     * @apiError AleadyDevice 중복된 Device Id
     *
     * @apiErrorExample Error-Response:
     *     HTTP/1.1 200 OK
     *     {
     *       "status": "error",
     *       "code": "611",
     *       "message": "중복된 Device Id입니다.",
     *       "result": null
     *     }
     */

    /**
     * @apiDefine LoginFailError
     *
     * @apiError LoginFail 아이디, 비밀번호를 확인하세요. (아이디가 없는 경우)
     *
     * @apiErrorExample Error-Response:
     *     HTTP/1.1 200 OK
     *     {
     *       "status": "error",
     *       "code": "607",
     *       "message": "서버관리자에게 문의하세요.",
     *       "result": null
     *     }
     */

    /**
     * @apiDefine LoginFailError2
     *
     * @apiError LoginFail2 아이디, 비밀번호를 확인하세요. (비밀번호가 맞지 경우)
     *
     * @apiErrorExample Error-Response:
     *     HTTP/1.1 200 OK
     *     {
     *       "status": "error",
     *       "code": "608",
     *       "message": "서버관리자에게 문의하세요.",
     *       "result": null
     *     }
     */

    /**
     * @apiDefine DuplicateFailError
     *
     * @apiError DuplicateFail 중복된 계약명입니다.
     *
     * @apiErrorExample Error-Response:
     *     HTTP/1.1 200 OK
     *     {
     *       "status": "error",
     *       "code": "615",
     *       "message": "중복된 계약명입니다.",
     *       "result": null
     *     }
     */

    /**  ------------------------------------------ */



}
