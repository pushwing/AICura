<?php
defined('BASEPATH') OR exit('No direct script access allowed');

// This can be removed if you use __autoload() in config.php OR use Modular Extensions
/** @noinspection PhpIncludeInspection */
require APPPATH . '/libraries/REST_Controller.php';

/**
 * Payment Controller
 * 결제 관련 api
 *
 * @category        Controller
 * @author          martin.byun@goodoc.co.kr
 */
class Payment extends REST_Controller {

    /**
     * Auth constructor.
     */
    function __construct()
    {
        parent::__construct();

        $this->load->model(array('payment_m', 'common_m', 'replicator_m', 'contractOrder_m'));

        $this->pg_auth_key = "ST1009281328226982205";

        $this->master = $this->load->database('master', true); //cud db 선언
    }

    /**
     * 성공 페이지
     */
    function process_post()
    {
        $data['resultCode'] = $this->post('PStateCd', true); //상태코드 대기 0051, 실패 0031
        $data['transNo'] = $this->post('PTrno', true); //세틀뱅크 고유 거래번호
        $data['authDate'] = $this->post('PAuthDt', true); //승인시간
        $data['authNo'] = $this->post('PAuthNo', true); //승인번호
        $data['type'] = $this->post('PType', true); //거래종류 CARD, VBANK
        $data['PMid'] = $this->post('PMid', true); //상점 번호
        $data['paymentId'] = $this->post('POid', true); //payment 번호
        $data['hash'] = $this->post('PHash', true); //해시
        $data['amount'] = $this->post('PAmt', true);


        if($data['resultCode'] == '0051')
        {
            //성공페이지 렌더링
            $this->load->view('vbankSuccess_v', $data);
        }
        else
        {
            //payment resultCode=0031로 업데이트 해야함
            $this->master->where('transNo', $data['transNo']);
            $this->master->update('payment', ['resultCode'=>'0031']);

            //실패시
            $this->load->view('vbankFail_v');
        }
    }

    /**
     * 실패 페이지
     */
    function cancel_post()
    {
        $data['resultCode'] = $this->post('PStateCd', true); //상태코드 대기 0051, 실패 0031
        $data['transNo'] = $this->post('PTrno', true); //세틀뱅크 고유 거래번호
        $data['authDate'] = $this->post('PAuthDt', true); //승인시간
        $data['authNo'] = $this->post('PAuthNo', true); //승인번호
        $data['type'] = $this->post('PType', true); //거래종류 CARD, VBANK
        $data['PMid'] = $this->post('PMid', true); //상점 번호
        $data['paymentId'] = $this->post('POid', true); //payment 번호
        $data['hash'] = $this->post('PHash', true); //해시
        $data['amount'] = $this->post('PAmt', true);

        //payment resultCode=0031로 업데이트 해야함
        $this->master->where('transNo', $data['transNo']);
        $this->master->update('payment', ['resultCode'=>'0031']);
        $this->load->view('vbankFail_v');
    }

    function cancel_get()
    {
        $this->load->view('vbankFail_v');
    }

    function index_get()
    {
        $this->load->view('test_v');

    }

    function test2_get()
    {
        $h = '2859dec77a9bbf9ba975fa60b962b891';

        $p = md5('0051'.'SBank_VBNKmid_test0020181011171913473612'.'20181011171919'.'VBANK'.ORDER_ID.'41'.'300'.PG_KEY);

        echo $h.'<br>'.$p;
    }

    /**
     * @api {post} /payment/check 가상계좌 발급여부 체크
     * @apiHeader {string} x-api-key="qJqKDATY0D48ZH9hCNW4N6ygHBqtoVz4a4mu0P6A"
     * @apiHeader {string} x-api-token=""
     * @apiHeader {string} x-api-refreshtoken=""
     * @apiHeader {string} x-api-userid=""
     * @apiSampleRequest /api/v1.0/payment/check
     * @apiVersion 1.0.0
     * @apiName payment check
     * @apiGroup Payment
     * @apiDescription 가상계좌 발급여부 체크
     * @apiParam {Number} contractOrderId 수주계약번호 POid
     * @apiParam {Number} type 결제유형. 1 가상계좌, 2 신용카드
     * @apiParam {Number} amount 금액
     *
     * @apiSuccess {String="success", "error"} status="success" 상태
     * @apiSuccess {Number} code 코드값
     * @apiSuccess {String} message
     * @apiSuccess {String} result paymentId 주문번호(POid)
     *
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *       'status': 'success',
     *       'code': '200',
     *       'message': '',
     *       'result': {'1'}
     *     }
     *
     * @apiUse RegistFailError
     * @apiUse NotAuthTokenError
     */
    function check_post()
    {
        $auth_arr = $this->load->get_vars();

        //필수키
        $data['token'] = $auth_arr['token'];
        $data['refreshToken'] = $auth_arr['refreshToken'];
        $data['userId'] = $data['users_id'] = $auth_arr['users_id'];
        $data['type'] = $this->post('type', true);
        $data['amount'] = $this->post('amount', true);
        $data['contractOrderId'] = $this->post('contractOrderId', true);

        //필수체크
        $this->_check_value($data);

        //api method별 하드코딩
        $data['menu_id'] = [120000]; // 4, 20, 21, 22,23 광고관리라 광고로 해놓음

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

        $return = $this->payment_m->checkPayment($data);

        if($return !== false)
        {
            $this->response([
                'status' => 'success',
                'code' => '200',
                'message' => '가상계좌 생성 가능',
                'result' => null
            ], 200);
        }
        else
        {
            $this->response([
                'status' => 'error',
                'code' => '605', //입력 실패
                'message' => '이미 발급된 가상계좌가 있습니다.',
                'result' => null
            ], 200);
        }
    }

    /**
     * @api {post} /payment/register 최초 결제 정보 등록
     * @apiHeader {string} x-api-key="qJqKDATY0D48ZH9hCNW4N6ygHBqtoVz4a4mu0P6A"
     * @apiHeader {string} x-api-token=""
     * @apiHeader {string} x-api-refreshtoken=""
     * @apiHeader {string} x-api-userid=""
     * @apiSampleRequest /api/v1.0/payment/register
     * @apiVersion 1.0.0
     * @apiName payment register
     * @apiGroup Payment
     * @apiDescription 결제 최초 등록 payment table insert
     * @apiParam {Number} userId 사용자번호(운영자)
     * @apiParam {Number} hospitalId 병원번호
     * @apiParam {Number} contractId 계약번호
     * @apiParam {Number} contractOrderId 수주계약번호
     * @apiParam {Number} type 결제유형. 1 가상계좌, 2 신용카드
     * @apiParam {String} hospitalName 병원 담당자 이름
     * @apiParam {String} [hospitalEmail] 병원 담당자 이메일
     * @apiParam {String} [hospitalPhone] 병원 담당자 전화번호
     * @apiParam {Number} amount 금액
     *
     * @apiSuccess {String="success", "error"} status="success" 상태
     * @apiSuccess {Number} code 코드값
     * @apiSuccess {String} message
     * @apiSuccess {String} result paymentId 주문번호(POid)
     *
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *       'status': 'success',
     *       'code': '200',
     *       'message': '',
     *       'result': {'1'}
     *     }
     *
     * @apiUse RegistFailError
     * @apiUse NotAuthTokenError
     */
    public function register_post()
    {
        //필수키
        $data['userId'] = $this->post('userId', true);
        $data['hospitalId'] = $this->post('hospitalId', true);
        $data['contractId'] = $this->post('contractId', true);
        $data['contractOrderId'] = $this->post('contractOrderId', true);
        $data['type'] = $this->post('type', true);
        $data['hospitalName'] = $this->post('hospitalName', true);
        $data['amount'] = $this->post('amount', true);

        //필수체크
        $this->_check_value($data);

        $data['hospitalEmail'] = $this->post('hospitalEmail', true);
        $data['hospitalPhone'] = $this->post('hospitalPhone', true);


        $data['etc'] = $this->post('etc', true);

        //api method별 하드코딩
        $data['menu_id'] = [120000]; // 4, 20, 21, 22,23 광고관리라 광고로 해놓음

        //입금만료일시 BIZ-1143
        //$data['transDate'] = date('Ymd', strtotime('+33 hours', time())).'235959';
        $data['transDate'] = date('Ymd', strtotime('+12 hours', time())).'235959';

        $data['paymentId'] = $paymentId = $this->payment_m->setPayment($data);

        if($paymentId !== false)
        {
            //계약명 구하기
            $data['contractTitle'] = $contractTitle = $this->payment_m->getContractInfo($data['contractId']);

            $param = [
                'PHash' => '',
                'PDate' => '',
                'PStateCd' => '',
                'POrderId' => '',
                'PNoteUrl' => SITE_URL.'/api/v1.0/payment/update', //db처리
                'PNextPUrl' => SITE_URL.'/api/v1.0/payment/process', //성공, 실패시 화면처리
                'PCancPUrl' => SITE_URL.'/api/v1.0/payment/cancel', //결제창 닫았을때 화면
                'PEmail' => $data['hospitalEmail'],
                'PPhone' => $data['hospitalPhone'],
                'POid' => $paymentId,
                'PGoods' => urlencode($contractTitle), //계약명
                'PNoti' => '', //여유필드
                'PMname' => urlencode('굿닥'),
                'PUname' => urlencode($data['hospitalName']), //결제자명
                'PBname' => urlencode('주식회사케어랩스'), //가상계좌 고객통장에 찍힐 이름
                'PVtransDt' => $data['transDate'], //가상계좌 마감일, 1일+ 9시간
                'PAmt' => $data['amount'],
                'PMid' => ORDER_ID
            ];
            log_message('info', json_encode($param));
            log_message('info', 'payParam');

            if($data['type']==1)
            {
                $addr = VBANK_URL;
            }
            else if($data['type']==2)
            {
                $addr = CARD_URL;
            }

            $data['url'] = ORDER_URL.$addr; //echo $url; exit;

            $this->load->view('order_v', $data);
        }
        else
        {
            $this->response([
                'status' => 'error',
                'code' => '614', //입력 실패
                'message' => '서버관리자에게 문의하세요.',
                'result' => null
            ], 200);
        }
    }

    /**
     * @api {post} /payment/update 결제정보 업데이트
     * @apiSampleRequest /api/v1.0/payment/update
     * @apiVersion 1.0.0
     * @apiName payment update
     * @apiGroup Payment
     * @apiDescription 결제정보 업데이트
     * @apiParam {String} PStateCd 거래상태. 0021 성공, 0031 실패, 0051 가상계죄 입금대기중
     * @apiParam {Number} PTrno 세틀뱅크에서 부여하는 고유 거래번호
     * @apiParam {String} PAuthDt 승인일시
     * @apiParam {String} PAuthNo 승인번호
     * @apiParam {String} PType 거래종류. CARD 신용카드, VBANK 가상계좌
     * @apiParam {String} PMid 상점아이디
     * @apiParam {Number} POid 주문번호(수주계약번호)
     * @apiParam {String} PFnCd1 금융사코드1(숫자)
     * @apiParam {String} PFnCd2 금융사코드2(영문)
     * @apiParam {String} PFnNm 금융사명
     * @apiParam {String} PUname 주문자명
     * @apiParam {Number} PAmt 거래금액
     * @apiParam {String} PNoti 여유필드
     * @apiParam {String} PRmesg1 가상계좌번호|입금기한
     * @apiParam {Number} PRmesg2 가상계좌번호
     * @apiParam {String} PHash 체크용 해시키
     *
     * @apiSuccess {String="success", "error"} status="success" 상태
     * @apiSuccess {Number} code 코드값
     * @apiSuccess {String} message ""
     * @apiSuccess {String} result OK or FAIL
     *
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *     OK or Fail
     *
     * @apiUse FailUpdateError
     * @apiUse NotAuthTokenError
     */
    public function update_post()
    {
        //필수키
        $data['resultCode'] = $this->post('PStateCd', true);
        $data['transNo'] = $this->post('PTrno', true);
        $data['authDate'] = $this->post('PAuthDt', true);
        $data['authNo'] = $this->post('PAuthNo', true);
        $data['type'] = $this->post('PType', true);
        $data['paymentId'] = $this->post('POid', true);
        $data['fnCode1'] = $this->post('PFnCd1', true);
        $data['fnCode2'] = $this->post('PFnCd2', true);
        $data['fnName'] = $this->post('PFnNm', true);
        $data['hospitalName'] = $this->post('PUname', true);
        $data['amt'] = $this->post('PAmt', true);
        $data['etc'] = $this->post('PNoti', true);
        $data['result1'] = $this->post('PRmesg1', true);
        $data['result2'] = $this->post('PRmesg2', true);
        $data['hash'] = $this->post('PHash', true);

        log_message('error', json_encode($data));

        $data['fnName'] = mb_convert_encoding($data['fnName'], 'utf-8', 'euc-kr');

        //필수체크
        //$this->_check_value($data);

        //해시키 체크
        $md5_hash = md5($data['resultCode'].$data['transNo'].$data['authDate'].$data['type'].ORDER_ID.$data['paymentId'].$data['amt'].PG_KEY);

        log_message('info', $md5_hash);

        if($data['hash'] == $md5_hash)
        {
            //방어코드
            $resultPP = $this->payment_m->checkResultCode($data);

            if(!$resultPP)
            {
                echo "FAIL(3)";
                log_message('error', $data['paymentId'].' 중복');
                exit;
            }

            log_message('info', 'up start');

            $return = $this->payment_m->updatePayment($data);
            
            log_message('info', 'up end');

            if($return)
            {
                echo "OK";
                log_message('info', $data['paymentId'].' update ok');
            }
            else
            {
                echo "FAIL(1)";
                log_message('error', $data['paymentId'].' update fail');
            }
        }
        else
        {
            echo "FAIL(2)";
            log_message('error', $data['paymentId'].' hash fail');
        }

        exit;
    }

    /**
     * @api {post} /payment/refund 환불 연동
     * @apiHeader {string} x-api-key="qJqKDATY0D48ZH9hCNW4N6ygHBqtoVz4a4mu0P6A"
     * @apiHeader {string} x-api-token=""
     * @apiHeader {string} x-api-refreshtoken=""
     * @apiHeader {string} x-api-userid=""
     * @apiSampleRequest /api/v1.0/payment/refund
     * @apiVersion 1.0.0
     * @apiName payment refund
     * @apiGroup Payment
     * @apiDescription 환불 연동
     * @apiParam {Number} userId 사용자번호(운영자)
     * @apiParam {Number} transNo 세틀뱅크거래번호
     * @apiParam {Number} termType 환불유형. 1 전체, 2 부분
     * @apiParam {Number} contractId 계약번호
     * @apiParam {Number} contractOrderId 수주계약번호
     * @apiParam {Number} paymentId payment번호
     * @apiParam {Number} type 결제유형. 1 가상계좌, 2 신용카드
     * @apiParam {Number} amount 금액
     *
     * @apiSuccess {String="success", "error"} status="success" 상태
     * @apiSuccess {Number} code 코드값
     * @apiSuccess {String} message
     * @apiSuccess {String} result paymentId 주문번호(POid)
     *
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *       'status': 'success',
     *       'code': '200',
     *       'message': '환불처리 되었습니다.',
     *       'result': []
     *     }
     *
     * @apiUse RegistFailError
     * @apiUse NotAuthTokenError
     */
    public function refund_post()
    {
        //필수키
        $data['userId'] = $this->post('userId', true);
        $data['transNo'] = $this->post('transNo', true);
        $data['termType'] = $this->post('termType', true);
        $data['contractId'] = $this->post('contractId', true);
        $data['contractOrderId'] = $this->post('contractOrderId', true);
        $data['paymentId'] = $this->post('paymentId', true);
        $data['type'] = $this->post('type', true);
        $data['amount'] = $this->post('amount', true);

        //필수체크
        $this->_check_value($data);

        //api method별 하드코딩
        $data['menu_id'] = [120000]; // 4, 20, 21, 22,23 광고관리라 광고로 해놓음

        $return = $this->payment_m->setRefund($data);

        if($return !== false)
        {
            $this->response([
                'status' => 'success',
                'code' => '200',
                'message' => '환불처리 되었습니다.',
                'result' => null
            ], 200);
        }
        else
        {
            $this->response([
                'status' => 'error',
                'code' => '605', //업데이트 실패
                'message' => '환불 실패하였습니다.',
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
