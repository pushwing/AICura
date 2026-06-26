<?php
defined('BASEPATH') OR exit('No direct script access allowed');

// This can be removed if you use __autoload() in config.php OR use Modular Extensions
/** @noinspection PhpIncludeInspection */
require APPPATH . '/libraries/REST_Controller.php';

/**
 * Contract Controller
 * 계약 관련 api
 *
 * @category        Controller
 * @author          martin.byun@goodoc.co.kr
 */
class Contract extends REST_Controller {

    function __construct()
    {
        parent::__construct();

        $this->load->model(array('contract_m', 'common_m'));

        $this->master = $this->load->database('master', true);
    }

    /**
     * @api {post} /contract/update  계약 수정
     * @apiHeader {string} x-api-key="qJqKDATY0D48ZH9hCNW4N6ygHBqtoVz4a4mu0P6A"
     * @apiHeader {string} x-api-token=""
     * @apiHeader {string} x-api-refreshtoken=""
     * @apiHeader {string} x-api-userid=""
     * @apiSampleRequest /api/v1.0/contract/update
     * @apiVersion 1.0.0
     * @apiName contract Update
     * @apiGroup Contract
     * @apiDescription  계약 수정
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
     *       'message': '계약이 수정되었습니다.',
     *       'result': null
     *     }
     *
     * @apiUse FailUpdateError
     * @apiUse NotAuthTokenError
     */
    public function update_post()
    {
        //계약 테이블의 상태를 기준으로 제어한다. 입금확인전, 세금계산서 발행전 등
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
        //계약으로 넣음

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
                'message' => ' 계약이 수정되었습니다.',
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
     * @api {post} /contract/getContractInfo 계약 상세조회
     * @apiHeader {string} x-api-key="qJqKDATY0D48ZH9hCNW4N6ygHBqtoVz4a4mu0P6A"
     * @apiHeader {string} x-api-token=""
     * @apiHeader {string} x-api-refreshtoken=""
     * @apiHeader {string} x-api-userid=""
     * @apiSampleRequest /api/v1.0/contract/getContractInfo
     * @apiVersion 1.0.0
     * @apiName contract getContractInfo
     * @apiGroup Contract
     * @apiDescription 계약 상세조회 (계약관리)
     * @apiParam {Number} contractId 조회할 계약번호
     *
     * @apiSuccess {String="success", "error"} status="success" 상태
     * @apiSuccess {Number} code 코드값
     * @apiSuccess {String} message 빈값
     * @apiSuccess {Object[]} result 배열
     * @apiSuccess {Object[]} result.info 계약 상세조회 (계약관리) 결과값 배열
     * @apiSuccess {Number} result.info.id 계약번호
     * @apiSuccess {String} result.info.contractDate 계약일
     * @apiSuccess {String} result.info.title 계약명
     * @apiSuccess {Number} result.info.adType 계약방식. 이벤트신청, 기간노출
     * @apiSuccess {Number} result.info.adType2 상품종류. 이벤트, 메인배너
     * @apiSuccess {String} result.info.mainContract 사용안함
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
     * @apiSuccess {String} result.info.taxChargeName 세금계산서 대표자명
     * @apiSuccess {String} result.info.taxBusinessNo 세금계산서 사업자번호
     * @apiSuccess {String} result.info.taxchargeEmail 세금계산서 받을 이메일
     * @apiSuccess {String} result.info.taxIssueRequestDate 세금계산서 발행요청일
     * @apiSuccess {String} result.info.taxIssueDate 세금계산서 발행일(날짜가 있으면 발행한 것)
     * @apiSuccess {Number} result.info.payType 계약방식. 1 선불, 2 후불
     * @apiSuccess {String} result.info.hospitalNetwork 병원종류. 네트워크모병원, 네트워크자병원, 일반병원
     * @apiSuccess {String} result.info.childHospitalId 네트워크모병원일 경우 자병원 리스트. 병원번호|병원명, 병원번호|병원명
     * @apiSuccess {String} result.info.adsId 연결된 광고번호. 광고번호|광고명, 광고번호|광고명
     *
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *       'status': 'success',
     *       'code': '200',
     *       'message': '',
     *       "result": {
     *          'info' : {}
     *        }
     *     }
     *
     * @apiUse FailGetError
     * @apiUse NotAuthTokenError
     */
    function getContractInfo_post()
    {
        $auth_arr = $this->load->get_vars();

        //필수키
        $data['token'] = $auth_arr['token'];
        $data['refreshToken'] = $auth_arr['refreshToken'];
        $data['users_id'] = $auth_arr['users_id'];
        $data['contractId'] = $this->post('contractId', true); //계약번호

        //필수체크
        $this->_check_value($data);

        //api method별 하드코딩
        $data['menu_id'] = [110000]; //auth_code_catetory 유저관리 번호
        //계약으로 넣음

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
        $result = $this->contract_m->getContractInfo($data);

        if($result !== false)
        {
            $this->response([
                'status' => 'success',
                'code' => '200',
                'message' => '',
                'result' => (object) ['info' => $result]
            ], 200);
        }
        else
        {
            $this->response([
                'status' => 'error',
                'code' => '610', //정보 get 실패
                'message' => '서버관리자에게 문의하세요.',
                'result' => (object) ['info' => []]
            ], 200);
        }
    }

    /**
     * @api {post} /contract/getContractList  계약 리스트 조회
     * @apiHeader {string} x-api-key="qJqKDATY0D48ZH9hCNW4N6ygHBqtoVz4a4mu0P6A"
     * @apiHeader {string} x-api-token=""
     * @apiHeader {string} x-api-refreshtoken=""
     * @apiHeader {string} x-api-userid=""
     * @apiSampleRequest /api/v1.0/contract/getContractList
     * @apiVersion 1.0.0
     * @apiName contract getContractList
     * @apiGroup Contract
     * @apiDescription  계약 리스트 조회. 검색조건 전체일 경우 해당 파라미터는 전송하지 않는다. 병원 기준 계약 리스트 출력 -> 수주번호별 원장 리스트
     * @apiParam {Number} limit 페이징시 한 페이지에 노출할 리스트수
     * @apiParam {Number} page="1" 페이지번호
     *
     * @apiParam {Number} [agencyUserId] 영업담당자 회원번호
     * @apiParam {Number} [manageUserId] 관리담당자 회원번호
     * @apiParam {Number} [advertiserStatus] 광고주(병원계약)상태. 0 대기, 1 진행, 2 휴면, 3 중지, 4 이탈
     * @apiParam {Number} [depositStatus] 계약활동분류. 1 수주, 2 계약충전, 3 소진, 4 기타충전, 5 환불수수료, 6 발행 환불, 7 계약 환불, 8 기타 소진, 9 발행취소, 10 계약취소, 11 이월 소진, 12 이월 충전
     * @apiParam {Number} [adType2] 상품종류. 1 이벤트, 2 이벤트 메인배너, 3 메인배너
     * @apiParam {Number} [balanceStatus] 잔액유형. 1 마이너스, 2 0원, 3 50만원이하, 4 100만원이하, 5 300만원 이하, 6 300만원 이상
     * @apiParam {Number} [searchType="1"] 검색유형. 1 병원명, 2 계약명, 3 수주ID, 4 대행사명, 5 메모내용, 6 병원번호
     * @apiParam {String} [searchWord] 검색어
     *
     * @apiSuccess {String="success", "error"} status="success" 상태
     * @apiSuccess {Number} code 코드값
     * @apiSuccess {String} message 빈값
     * @apiSuccess {Object[]} result 배열
     * @apiSuccess {Object[]} result.list 계약리스트
     * @apiSuccess {Number} result.list.id 계약번호
     * @apiSuccess {Number} result.list.contractOrderId 수주계약번호
     * @apiSuccess {Number} result.list.adType2 상품종류. 이벤트, 메인배너
     * @apiSuccess {Number} result.list.progress0 사용안함(계산값)
     * @apiSuccess {Number} result.list.progress1 사용안함(계산값)
     * @apiSuccess {Number} result.list.progress2 사용안함(계산값)
     * @apiSuccess {Number} result.list.hStatus 계약상태. 대기, 진행, 휴면, 중지, 이탈
     * @apiSuccess {String} result.list.title 계약명
     * @apiSuccess {Number} result.list.hospitalId 병원번호
     * @apiSuccess {String} result.list.hospitalName 병원명
     * @apiSuccess {Number} result.list.agencyUserId 영업담당번호
     * @apiSuccess {Number} result.list.manageUserId 관리자번호
     * @apiSuccess {Number} result.list.oPrice 계약금
     * @apiSuccess {String} result.list.regDate 계약 등록일시
     * @apiSuccess {String} result.list.memo 메모
     * @apiSuccess {String} result.list.memo2 검색대상 메모(노출X)
     * @apiSuccess {Number} result.list.chargePrice 충전금액
     * @apiSuccess {Number} result.list.usePrice 소진금액
     * @apiSuccess {Number} result.list.advertiserStatus 계산값(사용안함)
     * @apiSuccess {Number} result.list.sum(progress0) 사용안함
     * @apiSuccess {Number} result.list.sum(progress1) 사용안함
     * @apiSuccess {Number} result.list.sum(progress2) 사용안삼
     * @apiSuccess {Number} result.list.totalOrder 수주금액 (사용안함)
     * @apiSuccess {Number} result.list.totalCharge 총 충전금액 (사용안함)
     * @apiSuccess {Number} result.list.totalUse 총 소진금액 (사용안함)
     * @apiSuccess {Number} result.list.totalReady 총 시점잔액 (사용안함)
     * @apiSuccess {Number} result.list.contractChargePrice 계약충전
     * @apiSuccess {Number} result.list.dbUsePrice db소진
     * @apiSuccess {Number} result.list.etcPrice 환불수수료+기타충전+기타소진
     * @apiSuccess {Number} result.list.taxProfit 세발매출 = 수주-(소진+환불)
     * @apiSuccess {Number} result.list.readyPrice 잔액
     * @apiSuccess {Object[]} result.list.contractOrderList 수주계약리스트
     * @apiSuccess {Number} result.list.contractOrderList.id 계약번호
     * @apiSuccess {Number} result.list.contractOrderList.contractOrderId 수주계약번호
     * @apiSuccess {Number} result.list.contractOrderList.contractStatus 계약상태
     * @apiSuccess {Number} result.list.contractOrderList.adType 상품종류. 이벤트신청, 기간노출
     * @apiSuccess {Number} result.list.contractOrderList.adType2 상품종류. 이벤트, 메인배너
     * @apiSuccess {Number} result.list.contractOrderList.progress0 사용안함(계산값)
     * @apiSuccess {Number} result.list.contractOrderList.progress1 사용안함(계산값)
     * @apiSuccess {Number} result.list.contractOrderList.progress2 사용안함(계산값)
     * @apiSuccess {Number} result.list.contractOrderList.hStatus 계약상태. 대기, 진행, 휴면, 중지, 이탈
     * @apiSuccess {String} result.list.contractOrderList.title 계약명
     * @apiSuccess {Number} result.list.contractOrderList.hospitalID 병원번호
     * @apiSuccess {String} result.list.contractOrderList.hospitalName 병원명
     * @apiSuccess {Number} result.list.contractOrderList.agencyUserId 영업담당번호
     * @apiSuccess {Number} result.list.contractOrderList.manageUserId 관리담당번호
     * @apiSuccess {String} result.list.contractOrderList.agencyCompanyName 대행사 명
     * @apiSuccess {Number} result.list.contractOrderList.agencyCompanyFeeRate 대행사 수수료(%)
     * @apiSuccess {Number} result.list.contractOrderList.oPrice 수주금액
     * @apiSuccess {String} result.list.contractOrderList.regDate 계약 등록일시
     * @apiSuccess {Number} result.list.contractOrderList.chargePrice 충전금액
     * @apiSuccess {Number} result.list.contractOrderList.usePrice 소진금액
     * @apiSuccess {Number} result.list.contractOrderList.contractChargePrice 계약충전
     * @apiSuccess {Number} result.list.contractOrderList.dbUsePrice db소진
     * @apiSuccess {Number} result.list.contractOrderList.etcPrice 환불수수료+기타충전+기타소진
     * @apiSuccess {Number} result.list.contractOrderList.calPrice 소진+환불
     * @apiSuccess {Number} result.totCnt 전체 이벤트 수
     * @apiSuccess {Number} result.page 현재 페이지번호
     *
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *       'status': 'success',
     *       'code': '200',
     *       'message': '',
     *       'result': {
     *          'list' : {},    
     *          'totCnt' : 0,
     *          'page'   : 0
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
        //$data['listAll'] = $this->post('listAll', true); //Y 전체 리스트, N 페이징리스트
        $data['limit'] = $this->post('limit', true); //1페이지에 표시하는 리스트수
        $data['page'] = $this->post('page', true); //페이지 번호

        //필수체크
        $this->_check_value($data);

        //검색어
        $data['agencyUserId'] = $this->post('agencyUserId', true);
        $data['manageUserId'] = $this->post('manageUserId', true);
        $data['advertiserStatus'] = $this->post('advertiserStatus', true);
        $data['depositStatus'] = $this->post('depositStatus', true);
        $data['balanceStatus'] = $this->post('balanceStatus', true);
        $data['adType2'] = $this->post('adType2', true);
        $data['searchType'] = $this->post('searchType', true);
        $data['searchWord'] = $this->post('searchWord', true);

        //api method별 하드코딩
        $data['menu_id'] = [110000]; //auth_code_catetory 유저관리 번호
        //계약으로 넣음

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

        $list = $this->contract_m->getContractListProgram($data_arr);

        if($list !== false)
        {
            $list['page'] = $data['page'];
            $this->response([
                'status' => 'success',
                'code' => '200',
                'message' => '',
                'result' => (object) ['list' => $list['list'], 'totCnt' => $list['totCnt'], 'page' => $list['page']]
            ], 200);
        }
        else
        {
            $this->response([
                'status' => 'error',
                'code' => '610', //정보 get 실패
                'message' => '서버관리자에게 문의하세요.',
                'result' => (object) ['list' => [], 'totCnt' => 0, 'page' => 0]
            ], 200);
        }
    }

    /**
     * @api {post} /contract/searchContractList  계약 리스트 검색
     * @apiHeader {string} x-api-key="qJqKDATY0D48ZH9hCNW4N6ygHBqtoVz4a4mu0P6A"
     * @apiHeader {string} x-api-token=""
     * @apiHeader {string} x-api-refreshtoken=""
     * @apiHeader {string} x-api-userid=""
     * @apiSampleRequest /api/v1.0/contract/searchContractList
     * @apiVersion 1.0.0
     * @apiName contract searchContractList
     * @apiGroup Contract
     * @apiDescription  병원번호에 해당하는 계약 리스트 검색
     * @apiParam {Number} hospitalId 병원번호
     *
     * @apiSuccess {String="success", "error"} status="success" 상태
     * @apiSuccess {Number} code 코드값
     * @apiSuccess {String} message 빈값
     * @apiSuccess {Object[]} result 배열
     * @apiSuccess {Object[]} result.list 계약리스트
     * @apiSuccess {String} result.list.id 계약번호
     * @apiSuccess {String} result.list.title 계약명
     * @apiSuccess {String} result.list.hospitalID 병원번호
     * @apiSuccess {String} result.list.hospitalName 병원명
     * @apiSuccess {String} result.list.contractOrderId 수주계약번호
     * @apiSuccess {String} result.list.adType2 상품유형
     * @apiSuccess {String} result.list.depositDate 입금일시
     * @apiSuccess {String} result.list.parentId 이전 수주계약번호
     * @apiSuccess {String} result.list.overCharge2 현재 입금되지 않아도 이월충전 내역이 있으면 유효한 계약인지 판단하기 위한 값
     * @apiSuccess {String} result.list.isNetwork 네트워크병원 여부 및 일반병원. 0 일반, 1 네트워크 모병원, 2 네트워크 자병원
     * @apiSuccess {String} result.list.validContract 입금되거나 이월충전된 유효계약여부. 1 유효계약, 0 미입금계약
     *
     * @apiSuccessExample Success-Response:
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
     * @apiUse FailGetError
     * @apiUse NotAuthTokenError
     */
    public function searchContractList_post()
    {
        $auth_arr = $this->load->get_vars();

        //필수키
        $data['token'] = $auth_arr['token'];
        $data['refreshToken'] = $auth_arr['refreshToken'];
        $data['users_id'] = $auth_arr['users_id'];
        $data['hospitalId'] = $this->post('hospitalId', true);

        //필수체크
        $this->_check_value($data);

        //api method별 하드코딩
        $data['menu_id'] = [110000];
        //계약으로 넣음

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

        $list = $this->contract_m->searchContractList($data);

        if($list !== false)
        {
            $this->response([
                'status' => 'success',
                'code' => '200',
                'message' => '',
                'result' => (object) ['list'=>$list]
            ], 200);
        }
        else
        {
            $this->response([
                'status' => 'error',
                'code' => '610', //정보 get 실패
                'message' => '서버관리자에게 문의하세요.',
                'result' => (object) ['list' => []]
            ], 200);
        }
    }   


    /**
     * @api {post} /contract/getDepositList  계약 원장 리스트 조회
     * @apiHeader {string} x-api-key="qJqKDATY0D48ZH9hCNW4N6ygHBqtoVz4a4mu0P6A"
     * @apiHeader {string} x-api-token=""
     * @apiHeader {string} x-api-refreshtoken=""
     * @apiHeader {string} x-api-userid=""
     * @apiSampleRequest /api/v1.0/contract/getDepositList
     * @apiVersion 1.0.0
     * @apiName contract getDepositList
     * @apiGroup Contract
     * @apiDescription  계약 원장 리스트 조회.
     * @apiParam {Number} contractId 계약번호
     * @apiParam {Number} contractOrderId 수주번호
     *
     * @apiSuccess {String="success", "error"} status="success" 상태
     * @apiSuccess {Number} code 코드값
     * @apiSuccess {String} message 빈값
     * @apiSuccess {Object[]} result 배열 원장리스트
     * @apiSuccess {Object[]} result.list 계약 원장 리스트 조회 결과값 배열
     * @apiSuccess {Number} result.list.id 원장번호
     * @apiSuccess {Number} result.list.status 상태. 1 수주, 2 계약충전, 3 소진, 4 기타충전, 5 환불수수료, 6 발행 환불, 7 계약 환불, 8 기타 소진, 9 발행취소, 10 계약취소, 11 이월 소진, 12 이월 충전, 13 세금계산서(미노출), 14 대행사 수수료
     * @apiSuccess {Number} result.list.isMinus 마이너스 여부. 1 마이너스, 0 플러스
     * @apiSuccess {Number} result.list.contractId 계약번호
     * @apiSuccess {Number} result.list.contractOrderId 수주계약번호
     * @apiSuccess {Number} result.list.usersId 유저번호
     * @apiSuccess {String} result.list.memo 메모
     * @apiSuccess {Number} result.list.price 금액
     * @apiSuccess {String} result.list.regDate 등록일시
     * @apiSuccess {String} result.list.modDate 수정일
     *
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *     "status": "success",
     *     "code": "200",
     *     "message": "",
     *     "result": {
     *          'list' : {}
     *      }   
     *     }
     *
     * @apiUse NotAuthTokenError
     */
    public function getDepositList_post()
    {
        $auth_arr = $this->load->get_vars();

        //필수키
        $data['token'] = $auth_arr['token'];
        $data['refreshToken'] = $auth_arr['refreshToken'];
        $data['users_id'] = $auth_arr['users_id'];
        $data['contractId'] = $this->post('contractId', true);
        $data['contractOrderId'] = $this->post('contractOrderId', true);

        //필수체크
        $this->_check_value($data);

        //api method별 하드코딩
        $data['menu_id'] = [110000]; //auth_code_catetory 유저관리 번호
        //계약으로 넣음

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

        $list = $this->contract_m->getDepositList($data);

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
                'status' => 'error',
                'code' => '200', //정보 get 실패
                'message' => '데이터가 없습니다.',
                'result' => (object) ['list' => []]
            ], 200);
        }
    }

    /**
     * @api {post} /contract/depositConfirm  계약 충전 관리
     * @apiHeader {string} x-api-key="qJqKDATY0D48ZH9hCNW4N6ygHBqtoVz4a4mu0P6A"
     * @apiHeader {string} x-api-token=""
     * @apiHeader {string} x-api-refreshtoken=""
     * @apiHeader {string} x-api-userid=""
     * @apiSampleRequest /api/v1.0/contract/depositConfirm
     * @apiVersion 1.0.0
     * @apiName contract depositConfirm
     * @apiGroup Contract
     * @apiDescription  계약 충전 관리 (원장 입력). type 5, 11, 12, 13는 시스템에서 자동으로 처리하는 값임
     * @apiParam {Number} contractId 계약번호
     * @apiParam {Number} contractOrderId 수주계약번호
     * @apiParam {Number} type 충전타입. 1 수주, 2 계약충전, 3 소진, 4 기타충전, 5 환불수수료, 6 발행 환불, 7 계약 환불, 8 기타 소진, 9 발행취소, 10 계약취소, 11 이월 소진, 12 이월 충전, 13 세금(미노출), 14 대행사수수료
     * @apiParam {Number} chargePrice 충전금액
     * @apiParam {String} [memo] 메모
     * @apiParam {Number} [checkRefundFee] 수수료 차감여부. 1 환불수수료 차감, 2 환불/대행수수료 차감안함, 3 대행수수료 차감, 4 환불/대행수수료 차감
     *
     * @apiSuccess {String="success", "error"} status="success" 상태
     * @apiSuccess {Number} code 코드값
     * @apiSuccess {String} message 처리되었습니다.
     * @apiSuccess {Object} result null
     *
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *       "status": "success",
     *       "code": "200",
     *       "message": "처리되었습니다.",
     *       "result": null
     *     }
     *
     * @apiUse FailGetError
     * @apiUse NotAuthTokenError
     */
    public function depositConfirm_post()
    {
        $auth_arr = $this->load->get_vars();

        //필수키
        $data['token'] = $auth_arr['token'];
        $data['refreshToken'] = $auth_arr['refreshToken'];
        $data['users_id'] = $auth_arr['users_id'];
        $data['contractId'] = $this->post('contractId', true);
        $data['contractOrderId'] = $this->post('contractOrderId', true);
        $data['type'] = $this->post('type', true);
        $data['chargePrice'] = $this->post('chargePrice', true);

        //필수체크
        $this->_check_value($data);

        $data['memo'] = $this->post('memo', true);
        $data['checkRefundFee'] = $this->post('checkRefundFee', true);

        if(in_array($data['type'], [1,2,3,4,5,8,9,10,11,12,13,14]) and $data['checkRefundFee'] != '')
        {
            $this->response([
                'status' => 'error',
                'code' => '610', //정보 get 실패
                'message' => '환불일 경우에만 환불수수료 차감 항목을 사용할 수 있습니다.',
                'result' => null
            ], 200);
        }

        if(in_array($data['type'], [6,7]) and $data['checkRefundFee'] == 1)
        {
            $dataP = $data['chargePrice'] - 300000;
            if($dataP < 0)
            {
                $this->response([
                    'status' => 'error',
                    'code' => '610', //정보 get 실패
                    'message' => '환불금액이 환불수수료보다 작습니다.',
                    'result' => null
                ], 200);
            }

        }

        if($data['checkRefundFee'] == '')
        {
            //default
            $data['checkRefundFee'] == 2; //둘다 차감안함으로 기본값 셋팅
        }

        //api method별 하드코딩
        $data['menu_id'] = [110000]; //auth_code_catetory 유저관리 번호
        //계약으로 넣음

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

        //계약번호 체크
        $checkId = $this->common_m->getContractID($data);

        if(!$checkId)
        {
            $this->response([
                'status' => 'error',
                'code' => '610', //정보 get 실패
                'message' => '서버관리자에게 문의하세요.(계약번호 체크)',
                'result' => null
            ], 200);
        }

        $list = $this->contract_m->depositConfirm($data);

        if($list != false)
        {
            $this->response([
                'status' => 'success',
                'code' => '200',
                'message' => '처리되었습니다.',
                'result' => null
            ], 200);
        }
        else
        {
            $this->response([
                'status' => 'error',
                'code' => '610', //정보 get 실패
                'message' => '서버관리자에게 문의하세요.',
                'result' => null
            ], 200);
        }
    }

    /**
     * @api {post} /contract/getContractStatus  계약 상태 조회
     * @apiHeader {string} x-api-key="qJqKDATY0D48ZH9hCNW4N6ygHBqtoVz4a4mu0P6A"
     * @apiHeader {string} x-api-token=""
     * @apiHeader {string} x-api-refreshtoken=""
     * @apiHeader {string} x-api-userid=""
     * @apiSampleRequest /api/v1.0/contract/getContractStatus
     * @apiVersion 1.0.0
     * @apiName contract getContractStatus
     * @apiGroup Contract
     * @apiDescription  계약 상태 조회 (수주 관리에서 관리항목 상태 파악을 위한 자료) 세금계산서 발행여부, 입금 여부, 현재 잔액(환불가능금액)
     * @apiParam {Number} contractOrderId 수주계약번호
     *
     * @apiSuccess {String="success", "error"} status="success" 상태
     * @apiSuccess {Number} code 코드값
     * @apiSuccess {String} message 빈값
     * @apiSuccess {Object[]} result 배열 
     * @apiSuccess {Object[]} result.info 계약 상태 조회 결과값 배열
     * @apiSuccess {Number} result.info.id 수주계약번호
     * @apiSuccess {String} result.info.tax 세금계산서 발행여부. 미발행, 발행
     * @apiSuccess {String} result.info.deposit 입금여부. 미입금, 부분입금, 입금
     * @apiSuccess {Number} result.info.balance 현재 잔액 (계약충전+서비스충전+결번충전) - (소진+기타 소진)
     * @apiSuccess {Number} result.info.contractStatus 수주계약 상태 1 정상, 2 발행 환불, 3 발행 취소, 4 계약 취소, 5 계약 환불, 6 이월(종료)
     * @apiSuccess {Number} result.info.charge 잔액2 (수주금) - (계약충전)
     * @apiSuccess {Number} result.info.charge1 총 충전액 (계약충전+서비스충전+결번충전)
     * @apiSuccess {Number} result.info.charge2 총 계약충전액
     * @apiSuccess {Number} result.info.charge3 총 수주금액
     * @apiSuccess {Number} result.info.feeRate 대행수수료률(%)
     * @apiSuccess {Number} result.info.feePrice 대행수수료 금액
     * @apiSuccess {Number} result.info.liveAdsCount 진행중인 이벤트 수
     *
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *     "status": "success",
     *     "code": "200",
     *     "message": "",
     *     "result": {
     *          "info" : {}    
     *       }
     *     }
     *
     * @apiUse FailGetError
     * @apiUse NotAuthTokenError
     */
    function getContractStatus_post()
    {
        $auth_arr = $this->load->get_vars();

        //필수키
        $data['token'] = $auth_arr['token'];
        $data['refreshToken'] = $auth_arr['refreshToken'];
        $data['users_id'] = $auth_arr['users_id'];
        $data['contractOrderId'] = $this->post('contractOrderId', true);

        //필수체크
        $this->_check_value($data);

        //api method별 하드코딩
        $data['menu_id'] = [110000]; //auth_code_catetory 유저관리 번호
        //계약으로 넣음

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

        $list = $this->contract_m->getContractStatus($data);

        if($list != false)
        {
            $this->response([
                'status' => 'success',
                'code' => '200',
                'message' => '',
                'result' => (object) ['info' => $list]
            ], 200);
        }
        else
        {
            $this->response([
                'status' => 'error',
                'code' => '610', //정보 get 실패
                'message' => '서버관리자에게 문의하세요.',
                'result' => (object) ['info' => []]
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
