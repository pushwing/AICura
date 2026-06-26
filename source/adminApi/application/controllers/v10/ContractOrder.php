<?php
defined('BASEPATH') OR exit('No direct script access allowed');

// This can be removed if you use __autoload() in config.php OR use Modular Extensions
/** @noinspection PhpIncludeInspection */
require APPPATH . '/libraries/REST_Controller.php';

/**
 * ContractOrder Controller
 * 수주 계약 관련 api
 *
 * @category        Controller
 * @author          martin.byun@goodoc.co.kr
 */
class ContractOrder extends REST_Controller {

    /**
     * Auth constructor.
     */
    function __construct()
    {
        parent::__construct();

        $this->load->model(array('contractOrder_m', 'common_m', 'replicator_m', 'ads_m'));

        $this->master = $this->load->database('master', true);
    }

    /**
     * @api {post} /contractOrder/register 수주 계약 등록
     * @apiHeader {string} x-api-key="qJqKDATY0D48ZH9hCNW4N6ygHBqtoVz4a4mu0P6A"
     * @apiHeader {string} x-api-token=""
     * @apiHeader {string} x-api-refreshtoken=""
     * @apiHeader {string} x-api-userid=""
     * @apiSampleRequest /api/v1.0/contractOrder/register
     * @apiVersion 1.0.0
     * @apiName ContractOrder Register
     * @apiGroup ContractOrder
     * @apiDescription 수주 계약 등록
     * @apiParam {Number} hospitalType 계약대상유형. 1 병원, 2 약국, 3 업체
     * @apiParam {Number} hospitalId 계약병원 번호 (병원 조회시 받아 hidden 처리)
     * @apiParam {String} hospitalName 계약병원 이름
     * @apiParam {Number} isNetwork 네트워크병원 여부 및 일반병원. 0 일반, 1 네트워크 모병원, 2 네트워크 자병원
     * @apiParam {String} contractDate 계약일
     * @apiParam {String} contractType 계약구분. 1 신규, 2 재계약
     * @apiParam {String} title 수주 계약 제목
     * @apiParam {Number} adType 계약방식. 1 이벤트신청, 2 기간노출
     * @apiParam {Number} adType2 상품종류. 1 이벤트, 2 메인배너, 3 이벤트존메인배너, 4 CPM, 5 기타
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
     * @apiParam {String} [agencyCompanyChargeName] 대행사 담당자 이름
     * @apiParam {String} [agencyCompanyChargePhone] 대행사 담당자 전화
     * @apiParam {String} [agencyCompanyChargeEmail] 대행사 담당자 이메일
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
        //회원리스트 조회
        $auth_arr = $this->load->get_vars();

        //필수키
        $data['token'] = $auth_arr['token'];
        $data['refreshToken'] = $auth_arr['refreshToken'];
        $data['users_id'] = $auth_arr['users_id'];

        $data['hospitalType'] = $this->post('hospitalType', true);
        $data['hospitalId'] = $this->post('hospitalId', true);
        $data['hospitalName'] = $this->post('hospitalName', true);
        $data['contractDate'] = $this->post('contractDate', true);
        $data['isNetwork']    = $this->post('isNetwork', true);
        $data['contractType'] = $this->post('contractType', true); //(contractStatus 1로 자동셋팅)
        $data['title'] = $this->post('title', true);
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

        //biz-1028
        $data['agencyCompanyChargeName'] = $this->post('agencyCompanyChargeName', true);
        $data['agencyCompanyChargePhone'] = $this->post('agencyCompanyChargePhone', true);
        $data['agencyCompanyChargeEmail'] = $this->post('agencyCompanyChargeEmail', true);

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

        $data_arr1 = array_filter($data, function($v){
            return $v !== false && !is_null($v) && ($v != '' || $v == '0');
        });

        //신규계약일 경우 병원과 상품종류로 검색하여 중복체크한다.
        if($data['contractType'] == 1)
        {
            $result2 = $this->contractOrder_m->checkContract($data);

            if($result2 == false)
            {
                $this->response([
                    'status' => 'error',
                    'code' => '614', //등록 실패
                    'message' => '이미 등록된 계약이 있습니다.',
                    'result' => null
                ], 200);
            }
        }

        $result = $this->contractOrder_m->setContractInfo($data_arr1);

        if($result !== false)
        {
            $this->response([
                'status' => 'success',
                'code' => '200',
                'message' => '수주 계약 등록되었습니다.',
                'result' => (object) ['info' => ['contractOrderId'=>$result]]
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
     * @api {post} /contractOrder/getInfo 수주 계약 상세조회
     * @apiHeader {string} x-api-key="qJqKDATY0D48ZH9hCNW4N6ygHBqtoVz4a4mu0P6A"
     * @apiHeader {string} x-api-token=""
     * @apiHeader {string} x-api-refreshtoken=""
     * @apiHeader {string} x-api-userid=""
     * @apiSampleRequest /api/v1.0/contractOrder/getInfo
     * @apiVersion 1.0.0
     * @apiName contractOrder getInfo
     * @apiGroup ContractOrder
     * @apiDescription 수주 계약 상세조회
     * @apiParam {Number} contractOrderId 조회할 계약번호
     *
     * @apiSuccess {String="success", "error"} status="success" 상태
     * @apiSuccess {Number} code 코드값
     * @apiSuccess {String} message 빈값
     * @apiSuccess {Object[]} result 배열. 계약리스트
     * @apiSuccess {Object[]} result.info 수주 계약 상세조회 배열. 
     * @apiSuccess {Number} result.info.id 수주계약번호
     * @apiSuccess {Number} result.info.contractType 계약구분. 1 신규, 2 재계약
     * @apiSuccess {Number} result.info.contractStatus 계약상태. 1 정상, 2 발행 환불, 3 발행 취소, 4 계약 취소, 5 계약 환불
     * @apiSuccess {String} result.info.title 계약명
     * @apiSuccess {String} result.info.contractDate 계약일시
     * @apiSuccess {Number} result.info.adType 계약방식. 이벤트신청, 기간노출
     * @apiSuccess {Number} result.info.adType2 상품종류. 이벤트, 메인배너
     * @apiSuccess {Number} result.info.adPrice 계약금액
     * @apiSuccess {Number} result.info.agencyCompanyFeeRate 광고대행사 수수료(%)
     * @apiSuccess {Number} result.info.adsCount 사용안함
     * @apiSuccess {Number} result.info.adsCountBonus 사용안함
     * @apiSuccess {Number} result.info.payMethod 사용안함(지불방법)
     * @apiSuccess {String} result.info.mainContract 사용안함
     * @apiSuccess {String} result.info.regDate 수주계약 등록일시
     * @apiSuccess {String} result.info.contractAgreeDate 사용안함
     * @apiSuccess {Number} result.info.agree 사용안함
     * @apiSuccess {Number} result.info.use 계약진행여부. 1 진행, 2 종료
     * @apiSuccess {Number} result.info.depositCheckId 사용안
     * @apiSuccess {String} result.info.depositDate 사용안함
     * @apiSuccess {Number} result.info.isDelete 삭제여부, 1 삭제
     * @apiSuccess {Number} result.info.purchaseOwnerId 차감병원번호
     * @apiSuccess {Number} result.info.isNetwork 네트워크병원 여부 및 일반병원. 0 일반, 1 네트워크 모병원, 2 네트워크 자병원
     * @apiSuccess {Number} result.info.agencyUserId 영업담당번호
     * @apiSuccess {Number} result.info.manageUserId 관리자번호
     * @apiSuccess {Number} result.info.agencyCompanyId 광고대행사번호
     * @apiSuccess {String} result.info.agencyCompanyName 광고대행사명
     * @apiSuccess {Number} result.info.hospitalType 병원종류. 1 병원, 2 약국, 3 업체
     * @apiSuccess {Number} result.info.hospitalId 병원번호
     * @apiSuccess {String} result.info.hospitalName 병원명
     * @apiSuccess {String} result.info.hospitalChargeName 병원담당 이름
     * @apiSuccess {String} result.info.hospitalChargePhone 병원담당 전화
     * @apiSuccess {String} result.info.hospitalChargeEmail 병원담당 이메일
     * @apiSuccess {String} result.info.taxChargeName 세금계산서 대표자명
     * @apiSuccess {Number} result.info.taxBusinessNo 세금계산서 사업자번호
     * @apiSuccess {String} result.info.taxChargeEmail 세금계산서 받을 이메일
     * @apiSuccess {String} result.info.taxIssueRequestDate 세금계산서 발행요청일
     * @apiSuccess {String} result.info.taxIssueDate 세금계산서 발행일(날짜가 있으면 발행한 것)
     * @apiSuccess {Number} result.info.contractId 계약번호
     * @apiSuccess {Number} result.info.chargePrice 충전금액
     * @apiSuccess {Number} result.info.unpayment  미입금여부. 0이 아니면 최신수주번호
     * @apiSuccess {Number} result.info.reContractUnable 재계약가능여부. 1 재계약불가, 0 재계약가능
     * @apiSuccess {Number} result.info.lastContractId 상태정상이고 입금이 된 최신 수주계약번호
     * @apiSuccess {Number} result.info.parentId 이전 계약번호
     * @apiSuccess {Number} result.info.agencyCompanyFeeRate 광고대행사 수수료(%)
     * @apiSuccess {String} result.info.agencyCompanyChargeName 대행사 담당자 이름
     * @apiSuccess {String} result.info.agencyCompanyChargePhone 대행사 담당자 전화
     * @apiSuccess {String} result.info.agencyCompanyChargeEmail 대행사 담당자 이메일
     * @apiSuccess {Object[]} result.info.memo 메모리스트
     * @apiSuccess {Number} result.info.memo.id 메모번호
     * @apiSuccess {Number} result.info.memo.memoType 메모종류. 1 영업팀 메모, 2 세금계산서 메모
     * @apiSuccess {Number} result.info.memo.targetId 메모대상번호 type 1, 2 - contractOrderId
     * @apiSuccess {Number} result.info.memo.userId 유저번호
     * @apiSuccess {String} result.info.memo.memo 메모내용
     * @apiSuccess {String} result.info.memo.customerMemo 고객에게 보여지는 메모내용
     * @apiSuccess {String} result.info.memo.regDate 등록일
     * @apiSuccess {Object[]} result.info.taxMemo 세금계산서 메모리스트
     * @apiSuccess {Number} result.info.taxMemo.id 메모번호
     * @apiSuccess {Number} result.info.taxMemo.memoType 메모종류. 1 영업팀 메모, 2 세금계산서 메모
     * @apiSuccess {Number} result.info.taxMemo.targetId 메모대상번호 type 1, 2 - contractOrderId
     * @apiSuccess {Number} result.info.taxMemo.userId 유저번호
     * @apiSuccess {String} result.info.taxMemo.memo 메모내용
     * @apiSuccess {String} result.info.taxMemo.customerMemo 고객에게 보여지는 메모내용
     * @apiSuccess {String} result.info.taxMemo.regDate 등록일
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
    function getInfo_post()
    {
        $auth_arr = $this->load->get_vars();

        //필수키
        $data['token'] = $auth_arr['token'];
        $data['refreshToken'] = $auth_arr['refreshToken'];
        $data['users_id'] = $auth_arr['users_id'];
        $data['contractOrderId'] = $this->post('contractOrderId', true); //계약번호

        //필수체크
        $this->_check_value($data);

        //필수키
        $data['token'] = $auth_arr['token'];
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

        $result = $this->contractOrder_m->getContractInfo($data);

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
                'result' =>  (object) ['info' => []]
            ], 200);
        }
    }

    /**
     * @api {post} /contractOrder/update 수주 계약 수정
     * @apiHeader {string} x-api-key="qJqKDATY0D48ZH9hCNW4N6ygHBqtoVz4a4mu0P6A"
     * @apiHeader {string} x-api-token=""
     * @apiHeader {string} x-api-refreshtoken=""
     * @apiHeader {string} x-api-userid=""
     * @apiSampleRequest /api/v1.0/contractOrder/update
     * @apiVersion 1.0.0
     * @apiName ContractOrder Update
     * @apiGroup ContractOrder
     * @apiDescription 수주 계약 수정
     * @apiParam {Number} contractOrderId 수주계약번호
     * @apiParam {String} [contractDate] 계약일
     * @apiParam {String="3","4"} [contractStatus] 계약상태. 3 발행 취소, 4 계약 취소 (계산서 발행후 미입금에서 취소, 세금계산서 미발행 상태에서 취소)
     * @apiParam {Number} [adPrice] 계약금액(숫자만, 입금확인 전, 세금계산서 미발행만 변경가능)
     * @apiParam {Number} [agencyCompanyId] 대행사 번호
     * @apiParam {String} [agencyCompanyName] 대행사명
     * @apiParam {String} [agencyCompanyFeeRate] 대행수수료 퍼센트. 0. 5, 10, 15, 20, 25, 30%
     * @apiParam {Number} [agencyUserId] 영업담당자 회원번호
     * @apiParam {Number} [manageUserId] 관리담당자 회원번호
     * @apiParam {String} [memo] 영업팀 메모
     * @apiParam {String} [hospitalChargeName] 병원담당지명/직함
     * @apiParam {String} [hospitalChargePhone] 병원담당자 연락처
     * @apiParam {String} [hospitalChargeEmail] 병원담당자 이메일
     * @apiParam {String} [taxChargeName] 대표자명 (세금계산서 발행후엔 수정 안됨)
     * @apiParam {String} [taxBusinessNo] 사업자등록번호 (세금계산서 발행후엔 수정 안됨)
     * @apiParam {String} [taxChargeEmail] 세금계산서 Email (세금계산서 발행후엔 수정 안됨)
     * @apiParam {String} [taxIssueRequestDate] 세금계산서 발행요청일 (세금계산서 발행후엔 수정 안됨) 테스트시 영향도 보고 판단. 문제있을시 보완책 마련
     * @apiParam {String} [taxMemo] 세금계산서 메모
     * @apiParam {String} [agencyCompanyChargeName] 대행사 담당자 이름
     * @apiParam {String} [agencyCompanyChargePhone] 대행사 담당자 전화
     * @apiParam {String} [agencyCompanyChargeEmail] 대행사 담당자 이메일
     *
     * @apiSuccess {String="success", "error"} status="success" 상태
     * @apiSuccess {Number} code 코드값
     * @apiSuccess {String} message 수주 계약이 수정되었습니다.
     * @apiSuccess {Object} result null
     *
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *       'status': 'success',
     *       'code': '200',
     *       'message': '수주 계약이 수정되었습니다.',
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

        $data['contractOrderId'] = $this->post('contractOrderId', true);


        //필수체크
        $this->_check_value($data);

        $data2['contractDate'] = $this->post('contractDate', true);
        $data2['contractStatus'] = $this->post('contractStatus', true);
        $data2['adPrice'] = $this->post('adPrice', true);
        $data2['agencyCompanyId'] = $this->post('agencyCompanyId', true);
        $data2['agencyCompanyName'] = $this->post('agencyCompanyName', true);
        $data2['agencyCompanyFeeRate'] = $this->post('agencyCompanyFeeRate', true);
        $data2['agencyUserId'] = $this->post('agencyUserId', true);
        $data2['manageUserId'] = $this->post('manageUserId', true);
        $data2['memo'] = $this->post('memo', true);
        $data2['hospitalChargeName'] = $this->post('hospitalChargeName', true);
        $data2['hospitalChargePhone'] = $this->post('hospitalChargePhone', true);
        $data2['hospitalChargeEmail'] = $this->post('hospitalChargeEmail', true);
        $data2['taxChargeName'] = $this->post('taxChargeName', true);
        $data2['taxBusinessNo'] = $this->post('taxBusinessNo', true);
        $data2['taxChargeEmail'] = $this->post('taxChargeEmail', true);
        $data2['taxIssueRequestDate'] = $this->post('taxIssueRequestDate', true);
        $data2['taxMemo'] = $this->post('taxMemo', true);

        //biz-1028
        $data2['agencyCompanyChargeName'] = $this->post('agencyCompanyChargeName', true);
        $data2['agencyCompanyChargePhone'] = $this->post('agencyCompanyChargePhone', true);
        $data2['agencyCompanyChargeEmail'] = $this->post('agencyCompanyChargeEmail', true);

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

        //기존 내용을 가져와서 세금계산서 발행전, 입금확인 여부를 체크한다.
        $result = $this->contractOrder_m->getContractInfo($data); //var_dump($result); exit;
        $data['contractId'] = $result['contractId']; //원장 입력때문에 추가

//        //신규일 경우 계약명 수정 불가
//        if($result['contractType'] == '1')
//        {
//            if($data2['title'])
//            {
//                $this->response([
//                    'status' => 'error',
//                    'code' => '605', //업데이트 오류
//                    'message' => '계약명을 수정할 수 없습니다.',
//                    'result' => ''
//                ], 200);
//            }
//        }
//        else
//        {
//            //중복계약명 체크
//            if($data2['title'])
//            {
//                $check = $this->contractOrder_m->checkTitle($data);
//
//                if($check ===  false)
//                {
//                    $this->response([
//                        'status' => 'error',
//                        'code' => '615', //계약명 중복
//                        'message' => '중복된 계약명입니다.',
//                        'result' => ''
//                    ], 200);
//                }
//            }
//        }

        //세금계산서 발행후 체크
        if($result['taxIssueDate'] != '')
        {
            if($data2['taxChargeName'] or $data2['taxBusinessNo'] or $data2['taxChargeEmail'])
            {
                $this->response([
                    'status' => 'error',
                    'code' => '605', //업데이트 오류
                    'message' => '세금계산서 정보를 수정할 수 없습니다.',
                    'result' => null
                ], 200);
            }

            if($data2['adPrice'])
            {
                $this->response([
                    'status' => 'error',
                    'code' => '605', //업데이트 오류
                    'message' => '세금계산서 발행 후 계약금액을 수정할 수 없습니다.',
                    'result' => null
                ], 200);
            }
        }

        //입금확인후 체크
        if($result['depositDate'] != '' and $result['depositDate'] != '0000-00-00 00:00:00')
        {
            if($data2['adPrice'])
            {
                $this->response([
                    'status' => 'error',
                    'code' => '605', //업데이트 오류
                    'message' => '입금확인 후 계약금액을 수정할 수 없습니다.',
                    'result' => null
                ], 200);
            }
        }

        //배열중 빈값 제거
        $data_arr2 = array_filter($data2, function($v){
            return $v !== false && !is_null($v) && ($v != '' || $v == '0');
        }); //var_dump($data); var_dump($data2);  var_dump($data_arr2); exit;

        if(count($data_arr2) == 0)
        {
            $this->response([
                'status' => 'success',
                'code' => '200',
                'message' => '업데이트하였습니다.(N)',
                'result' => null
            ], 200);
        }

        $result = $this->contractOrder_m->updateContractInfo($data, $data_arr2);

        if($result !== false)
        {
            $this->response([
                'status' => 'success',
                'code' => '200',
                'message' => '수주 계약이 수정되었습니다.',
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
     * @api {post} /contractOrder/list 수주 계약 리스트 조회
     * @apiHeader {string} x-api-key="qJqKDATY0D48ZH9hCNW4N6ygHBqtoVz4a4mu0P6A"
     * @apiHeader {string} x-api-token=""
     * @apiHeader {string} x-api-refreshtoken=""
     * @apiHeader {string} x-api-userid=""
     * @apiSampleRequest /api/v1.0/contractOrder/list
     * @apiVersion 1.0.0
     * @apiName contractOrder list
     * @apiGroup ContractOrder
     * @apiDescription 수주 계약 리스트 조회. 검색조건 전체일 경우 해당 파라미터는 전송하지 않는다. 재계약여부 판단시 unpayment 우선 판단후 reContractUnable로 판단
     * @apiParam {Number} limit 페이징시 한 페이지에 노출할 리스트수
     * @apiParam {Number} page="1" 페이지번호
     * @apiParam {Number="1", "2"} [type="1"] 1 전체리스트, 2 종료리스트
     *
     * @apiParam {Number} [agencyUserId] 영업담당자 회원번호
     * @apiParam {Number} [manageUserId] 관리담당자 회원번호
     * @apiParam {Number} [contractType] 계약구분. 1 신규, 2 재계약
     * @apiParam {Number} [contractStatus] 계약상태. 1 정상, 2 발행 환불, 3 발행 취소, 4 계약 취소, 5 계약 환불
     * @apiParam {Number} [isTax] 세금계산서 발행. 1 발행, 2 발행안함
     * @apiParam {Number} [adType2] 상품종류. 1 이벤트, 2 이벤트 메인배너, 3 메인배너
     * @apiParam {Number} [searchType="1"] 검색유형. 1 병원명, 2 대표자명, 3 대행사명, 4 사업자번호
     * @apiParam {String} [searchWord] 검색어
     *
     * @apiSuccess {String="success", "error"} status="success" 상태
     * @apiSuccess {Number} code 코드값
     * @apiSuccess {String} message 빈값
     * @apiSuccess {Object[]} result 배열.
     * @apiSuccess {Object[]} result.list 수주계약리스트
     * @apiSuccess {Number} result.list.id 수주계약번호
     * @apiSuccess {Number} result.list.contractType 계약구분. 1 신규, 2 재계약
     * @apiSuccess {Number} result.list.contractStatus 계약상태. 1 정상, 2 발행 환불, 3 발행 취소, 4 계약 취소, 5 계약 환불
     * @apiSuccess {String} result.list.title 계약명
     * @apiSuccess {String} result.list.contractDate 계약일시
     * @apiSuccess {Number} result.list.adType 계약방식. 이벤트신청, 기간노출
     * @apiSuccess {Number} result.list.adType2 상품종류. 이벤트, 메인배너
     * @apiSuccess {Number} result.list.adPrice 계약금액
     * @apiSuccess {Number} result.list.agencyCompanyFeeRate 광고대행사 수수료(%)
     * @apiSuccess {Number} result.list.adsCount 사용안함
     * @apiSuccess {Number} result.list.adsCountBonus 사용안함
     * @apiSuccess {Number} result.list.payMethod 사용안함(지불방법)
     * @apiSuccess {String} result.list.mainContract 사용안함
     * @apiSuccess {String} result.list.regDate 수주계약 등록일시
     * @apiSuccess {String} result.list.contractAgreeDate 사용안함
     * @apiSuccess {Number} result.list.agree 사용안함
     * @apiSuccess {Number} result.list.use 계약진행여부. 1 진행, 2 종료
     * @apiSuccess {Number} result.list.depositCheckId 사용안
     * @apiSuccess {String} result.list.depositDate 사용안함
     * @apiSuccess {Number} result.list.isDelete 삭제여부, 1 삭제
     * @apiSuccess {Number} result.list.purchaseOwnerId 차감병원번호
     * @apiSuccess {Number} result.list.isNetwork 네트워크병원 여부 및 일반병원. 0 일반, 1 네트워크 모병원, 2 네트워크 자병원
     * @apiSuccess {Number} result.list.agencyUserId 영업담당번호
     * @apiSuccess {Number} result.list.manageUserId 관리자번호
     * @apiSuccess {Number} result.list.agencyCompanyId 광고대행사번호
     * @apiSuccess {String} result.list.agencyCompanyName 광고대행사명
     * @apiSuccess {Number} result.list.hospitalType 병원종류. 1 병원, 2 약국, 3 업체
     * @apiSuccess {Number} result.list.hospitalId 병원번호
     * @apiSuccess {String} result.list.hospitalName 병원명
     * @apiSuccess {String} result.list.hospitalChargeName 병원담당 이름
     * @apiSuccess {String} result.list.hospitalChargePhone 병원담당 전화
     * @apiSuccess {String} result.list.hospitalChargeEmail 병원담당 이메일
     * @apiSuccess {String} result.list.taxChargeName 세금계산서 대표자명
     * @apiSuccess {Number} result.list.taxBusinessNo 세금계산서 사업자번호
     * @apiSuccess {String} result.list.taxChargeEmail 세금계산서 받을 이메일
     * @apiSuccess {String} result.list.taxIssueRequestDate 세금계산서 발행요청일
     * @apiSuccess {String} result.list.taxIssueDate 세금계산서 발행일(날짜가 있으면 발행한 것)
     * @apiSuccess {Number} result.list.payType 계약방식. 1 선불, 2후불
     * @apiSuccess {Number} result.list.contractId 계약번호
     * @apiSuccess {Number} result.list.memo 메모
     * @apiSuccess {Number} result.list.unpayment 미입금여부. 0이 아니면 미입금계약이 존재
     * @apiSuccess {Number} result.list.lastContractId 상태정상이고 입금이 된 최신 수주계약번호
     * @apiSuccess {Number} result.list.paymentInfo 가상계좌데이터. 00512|2018-12-11 11:00:00|P_VACCT_NO=56207481262425|P_EXP_DT=20181219235959|04 형태 발급상태(빈값 발급전, 0051 발급후 입급대기, 0021 성공, 0031 실패)|처리일시|가상계좌번호|만료일시|은행코 형태
     * @apiSuccess {Number} result.list.reContractUnable 재계약가능여부. 1 재계약불가, 0 재계약가능
     * @apiSuccess {Number} result.list.parentId 이전 계약번호 
     * @apiSuccess {String} result.list.agencyCompanyChargeName 대행사 담당자 이름
     * @apiSuccess {String} result.list.agencyCompanyChargePhone 대행사 담당자 전화
     * @apiSuccess {String} result.list.agencyCompanyChargeEmail 대행사 변경된 이메일
     * @apiSuccess {Number} result.totCnt 전체 수  
     * @apiSuccess {Number} result.page 현재 페이지번호
     * 
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *       'status': 'success',
     *       'code': '200',
     *       'message': '수주 계약이 수정되었습니다.',
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
    public function list_post()
    {       
        $auth_arr = $this->load->get_vars();

        //필수키
        $data['token'] = $auth_arr['token'];
        $data['refreshToken'] = $auth_arr['refreshToken'];
        $data['users_id'] = $auth_arr['users_id'];
        //$data['listAll'] = $this->post('listAll', true); //Y 전체 리스트, N 페이징리스트
        $data['limit'] = $this->post('limit', true); //1페이지에 표시하는 리스트수
        $data['page'] = $this->post('page', true); //페이지 번호
        $data['type'] = $this->post('type', true); //1 전체리스트, 2 종료리스트

        if($data['type'] == '')
        {
            //기본값 고정
            $data['type'] = 1;
        }

        //필수체크
        $this->_check_value($data);

        //검색어
        $data['agencyUserId'] = $this->post('agencyUserId', true);
        $data['manageUserId'] = $this->post('manageUserId', true);
        $data['contractType'] = $this->post('contractType', true);
        $data['contractStatus'] = $this->post('contractStatus', true);
        $data['isTax'] = $this->post('isTax', true);
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

        $user = $this->contractOrder_m->getContractList($data_arr);
 
        if($user !== false)
        {
            $user['page'] = $data['page'];
            $this->response([
                'status' => 'success',
                'code' => '200',
                'message' => '',
                'result' => (object) ['list' => $user['list'], 'totCnt' => $user['totCnt'], 'page' =>  $user['page']  ]
            ], 200);
        }
        else
        {
            $this->response([
                'status' => 'error',
                'code' => '610', //정보 get 실패
                'message' => '서버관리자에게 문의하세요.',
                'result' => (object) ['list' => [], 'totCnt' => 0, 'page' => 0 ]
            ], 200);
        }
    }

    /**
     * @api {post} /contractOrder/taxIssue 세금계산서 발행
     * @apiHeader {string} x-api-key="qJqKDATY0D48ZH9hCNW4N6ygHBqtoVz4a4mu0P6A"
     * @apiHeader {string} x-api-token=""
     * @apiHeader {string} x-api-refreshtoken=""
     * @apiHeader {string} x-api-userid=""
     * @apiSampleRequest /api/v1.0/contractOrder/taxIssue
     * @apiVersion 1.0.0
     * @apiName ContractOrder taxIssue
     * @apiGroup ContractOrder
     * @apiDescription 세금계산서 발행
     * @apiParam {Number} contractOrderId 수주계약 번호
     * @apiParam {Number} taxIssueDate 발행일 2018-03-23 형태
     * @apiParam {String} [memo] 세금계산서 메모
     *
     * @apiSuccess {String="success", "error"} status="success" 상태
     * @apiSuccess {Number} code 코드값
     * @apiSuccess {String} message 세금계산서 발행되었습니다.
     * @apiSuccess {Object} result null
     *
     * @apiUse FailUpdateError
     * @apiUse NotAuthTokenError
     */
    function taxIssue_post()
    {
        $auth_arr = $this->load->get_vars();

        //필수키
        $data['token'] = $auth_arr['token'];
        $data['refreshToken'] = $auth_arr['refreshToken'];
        $data['users_id'] = $auth_arr['users_id'];

        $data['contractOrderId'] = $this->post('contractOrderId', true);
        $data['taxIssueDate'] = $this->post('taxIssueDate', true);

        //필수체크
        $this->_check_value($data);

        $data['memo'] = $this->post('memo', true);

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

        //발행여부 체크
        $check2 = $this->contractOrder_m->getContractInfo($data);

        if($check2['taxIssueDate'])
        {
            $this->response([
                'status' => 'error',
                'code' => '605', //업데이트 실패
                'message' => '이미 발행된 세금계산서입니다.',
                'result' => null
            ], 200);
        }

        //세금계산서 관련 내용 업데이트
        $result = $this->contractOrder_m->taxIssue($data);

        if($result !== false)
        {
            $this->response([
                'status' => 'success',
                'code' => '200',
                'message' => '세금계산서 발행되었습니다.',
                'result' => null
            ], 200);
        }
        else
        {
            $this->response([
                'status' => 'error',
                'code' => '605', //업데이트 실패
                'message' => '세금계산서 발행 실패하였습니다.',
                'result' => null
            ], 200);
        }
    }

    /**
     * @api {post} /contractOrder/depositConfirmData  수주계약 입금확인 데이터
     * @apiHeader {string} x-api-key="qJqKDATY0D48ZH9hCNW4N6ygHBqtoVz4a4mu0P6A"
     * @apiHeader {string} x-api-token=""
     * @apiHeader {string} x-api-refreshtoken=""
     * @apiHeader {string} x-api-userid=""
     * @apiSampleRequest /api/v1.0/contractOrder/depositConfirmData
     * @apiVersion 1.0.0
     * @apiName ContractOrder depositConfirmData
     * @apiGroup ContractOrder
     * @apiDescription  수주계약 입금확인 데이터 : 최종계약금액, 계약일, 병워명, 수주계약 정보 등
     * @apiParam {Number} contractId 계약번호
     * @apiParam {Number} contractOrderId 수주계약번호
     *
     * @apiSuccess {String="success", "error"} status="success" 상태
     * @apiSuccess {Number} code 코드값
     * @apiSuccess {String} message 빈값
     * @apiSuccess {Object[]} result 배열
     * @apiSuccess {Object[]} result.info  수주계약 입금확인 데이터 : 최종계약금액, 계약일, 병워명, 수주계약 정보 등 의 결과값 배열
     * @apiSuccess {Number} result.info.adPrice 수주계약금액
     * @apiSuccess {Number} result.info.waitIssuePrice 발행 대기중 금액
     * @apiSuccess {Number} result.info.balancePrice 현재 잔액
     * @apiSuccess {String} result.info.regDate 계약일
     * @apiSuccess {Number} result.info.contractOrderId 수주계약번호
     * @apiSuccess {String} result.info.hospitalName 병원명
     * @apiSuccess {String} result.info.taxIssue 세금계산서 발행여부. 발행 or 미발행
     * @apiSuccess {String} result.info.agencyCompanyName 대행사명
     * @apiSuccess {Number} result.info.agencyCompanyFeeRate 대행수수료률(%)
     * @apiSuccess {Number} result.info.agencyUserId 영업담당자번호
     * @apiSuccess {Number} result.info.manageUserId 관리담당자번호
     *
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *     "status": "success",
     *     "code": "200",
     *     "message": "",
     *     "result": {
     *              "info" : {}
     *          }
     *     }
     *
     * @apiUse FailGetError
     * @apiUse NotAuthTokenError
     */
    public function depositConfirmData_post()
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

        $list = $this->contractOrder_m->getContractInfo2($data);

        if($list != false)
        {
            $this->response([
                'status' => 'success',
                'code' => '200',
                'message' => '',
                'result' => (object)['info' => $list]
            ], 200);
        }
        else
        {
            $this->response([
                'status' => 'error',
                'code' => '610', //정보 get 실패
                'message' => '서버관리자에게 문의하세요.',
                'result' => (object)['info' => []]
            ], 200);
        }
    }

    /**
     * @api {post} /contractOrder/depositConfirm  수주계약 입금확인
     * @apiHeader {string} x-api-key="qJqKDATY0D48ZH9hCNW4N6ygHBqtoVz4a4mu0P6A"
     * @apiHeader {string} x-api-token=""
     * @apiHeader {string} x-api-refreshtoken=""
     * @apiHeader {string} x-api-userid=""
     * @apiSampleRequest /api/v1.0/contractOrder/depositConfirm
     * @apiVersion 1.0.0
     * @apiName ContractOrder depositConfirm
     * @apiGroup ContractOrder
     * @apiDescription  수주계약 입금확인 (원장 입력)
     * @apiParam {Number} contractId 계약번호
     * @apiParam {Number} contractOrderId 수주계약번호
     * @apiParam {Number} chargePrice 충전금액
     * @apiParam {String} memo 메모
     * @apiParam {String} customerMemo 고객노출메모
     *
     * @apiSuccess {String="success", "error"} status="success" 상태
     * @apiSuccess {Number} code 코드값
     * @apiSuccess {String} message 입금확인되었습니다.
     * @apiSuccess {Object} result null
     *
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *     "status": "success",
     *     "code": "200",
     *     "message": "입금확인되었습니다.",
     *     "result": null
     *     }
     *
     * @apiUse FailGetError
     * @apiUse FailUpdateError
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
        $data['chargePrice'] = $this->post('chargePrice', true);

        //필수체크
        $this->_check_value($data);

        $data['memo'] = $this->post('memo', true);
        $data['customerMemo'] = $this->post('customerMemo', true);

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

        $list = $this->contractOrder_m->depositConfirm($data);

        if($list == 200)
        {
            $this->response([
                'status' => 'success',
                'code' => '200',
                'message' => '입금확인되었습니다.',
                'result' => null
            ], 200);
        }
        else if($list == 400)
        {
            $this->response([
                'status' => 'error',
                'code' => '605',
                'message' => '업데이트 실패하였습니다.',
                'result' => null
            ], 200);
        }
        else
        {
            $this->response([
                'status' => 'error',
                'code' => '610', //정보 get 실패
                'message' => '충전 총액이 계약금액을 초과할 수 없습니다.',
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
