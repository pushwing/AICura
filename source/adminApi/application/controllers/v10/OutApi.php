<?php
defined('BASEPATH') OR exit('No direct script access allowed');

// This can be removed if you use __autoload() in config.php OR use Modular Extensions
/** @noinspection PhpIncludeInspection */
require APPPATH . '/libraries/REST_Controller.php';

/**
 * 외부 api
 *
 * @category        Controller
 * @author          martin.byun@goodoc.co.kr
 */
class OutApi extends REST_Controller {

    /**
     * Common constructor.
     */
    function __construct()
    {
        parent::__construct();

        $this->load->model('outApi_m');
        $this->load->model('common_m');
        $this->load->model('contract_m');

        $this->master = $this->load->database('master', true);
    }

    /**
     * @api {post} /outApi/getAdsInfo 병원 상태 조회, 계약 및 이벤트 유무
     * @apiHeader {string} x-api-key="qJqKDATY0D48ZH9hCNW4N6ygHBqtoVz4a4mu0P6A"
     * @apiSampleRequest /api/v1.0/outApi/getAdsInfo
     * @apiVersion 1.0.0
     * @apiName outApi getAdsInfo
     * @apiGroup OutApi
     * @apiDescription 병원 상태 조회, 계약 및 이벤트 유무. 단순히 존재 유무만 체크
     * @apiParam {String} hospitalId 조회할 병원번호. 1,2,3 형태
     *
     * @apiSuccess {String="success", "error"} status="success" 상태
     * @apiSuccess {Number} code 코드값
     * @apiSuccess {String} message 빈값
     * @apiSuccess {Object[]} result 배열
     * @apiSuccess {Object[]} result.contractHospital 병원정보 배열
     * @apiSuccess {Number} result.contractHospital.hospitalId 병원번호
     * @apiSuccess {String} result.contractHospital.isContract 계약유무. true, false
     * @apiSuccess {String} result.contractHospital.isAds 광고존재유무. true, false
     *
     * @apiUse FailGetError
     * @apiUse NotAuthTokenError
     */
    function getAdsInfo_post()
    {
        //필수키
        $data['hospitalId'] = $this->post('hospitalId', true);

        //필수체크
        $this->_check_value($data);

        $result = $this->outApi_m->getAdsInfo($data);

        if($result !== false)
        {
            $this->response([
                'status' => 'success',
                'code' => '200',
                'message' => '',
                'result' => is_array($result) ? (object) $result : (object) [$result]
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
     * @api {post} /outApi/getRevocationInfo 네트워크병원 해제가능상태여부 확인
     * @apiHeader {string} x-api-key="qJqKDATY0D48ZH9hCNW4N6ygHBqtoVz4a4mu0P6A"
     * @apiSampleRequest /api/v1.0/outApi/getRevocationInfo
     * @apiVersion 1.0.0
     * @apiName outApi getRevocationInfo
     * @apiGroup OutApi
     * @apiDescription 네트워크병원 해제가능상태여부 확인
     * @apiParam {Number} hospitalId 조회할 병원번호
     *
     * @apiSuccess {String="success", "error"} status="success" 상태
     * @apiSuccess {Number} code 코드값
     * @apiSuccess {String} message 빈값
     * @apiSuccess {Object[]} result 배열
     * @apiSuccess {String} result.isDisconnectable 네트워크 해제가능 유무. true, false
     *
     * @apiUse FailGetError
     * @apiUse NotAuthTokenError
     */
    function getRevocationInfo_post()
    {
        //필수키
        $data['hospitalId'] = $this->post('hospitalId', true);

        //필수체크
        $this->_check_value($data);

        $result = $this->outApi_m->getRevocationInfo($data);

        if($result !== false)
        {
            $this->response([
                'status' => 'success',
                'code' => '200',
                'message' => '',
                'result' => is_array($result) ? (object) $result : (object) [$result]
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
     * @api {post} /outApi/setEventDb v1 신청 db 입력
     * @apiHeader {string} x-api-key="qJqKDATY0D48ZH9hCNW4N6ygHBqtoVz4a4mu0P6A"
     * @apiSampleRequest /api/v1.0/outApi/setEventDb
     * @apiVersion 1.0.0
     * @apiName outApi setEventDb
     * @apiGroup OutApi
     * @apiDescription v1 신청 db를 v2 db에 입력
     * @apiParam {Number} callRequestId 신청번호
     * @apiParam {Number} channel 노출대상 채널번호. 1 굿닥, 2 굿닥파트너스
     * @apiParam {Number} hospitalId 병원번호
     * @apiParam {Number} adsId 이벤트번호
     * @apiParam {Number} [userId] 유저번호
     * @apiParam {Number} [messageId] 문의내용번호
     * @apiParam {Number} device 디바이스. 1 안드, 2 ios, 3 웹
     * @apiParam {String} name 신청자이름
     * @apiParam {String} phone 신청자전화번호
     * @apiParam {String} [content] 문의내용
     * @apiParam {Number} [privacyAgree] 개인정보활용동의
     * @apiParam {String} funnel 퍼널
     * @apiParam {Number} eventCost 이벤트단가
     * @apiParam {String} [callTime] 통화가능시간
     * @apiParam {Number} [age] 출생연도
     * @apiParam {Number} [sex] 성별. 1 남자, 2 여자, 3 선택안함
     * @apiParam {Number} [parentId] ?
     * @apiParam {Number} [supplyThirdPartyAgree]
     * @apiParam {String} [fingerPrint]
     * @apiParam {Number} [region]
     * @apiParam {Number} [isSavePhone]
     *
     * @apiSuccess {String="success", "error"} status="success" 상태
     * @apiSuccess {Number} code 코드값
     * @apiSuccess {String} message 등록완료
     * @apiSuccess {Object[]} result 빈배열
     *
     * @apiUse RegistFailError
     * @apiUse NotAuthTokenError
     */
    function setEventDb_post()
    {   
        set_time_limit(0);
        ini_set('memory_limit', '-1');
        
        monologSend('setEventDb', ['POST' => $_POST, 'GET' => $_GET ]);

        //필수키
        $data['callRequestId'] = $this->post('callRequestId', true);
        $data['channel'] = $this->post('channel', true);
        $data['hospitalId'] = $this->post('hospitalId', true);
        $data['adsId'] = $this->post('adsId', true);
        $data['device'] = $this->post('device', true);
        $data['name'] = $this->post('name', true);
        $data['phone'] = $this->post('phone', true);
        $data['funnel'] = $this->post('funnel', true);
        $data['eventCost'] = $this->post('eventCost', true);

        //필수체크
        $this->_check_value($data);

        $data['userId'] = $this->post('userId', true);
        $data['messageId'] = $this->post('messageId', true);
        $data['content'] = $this->post('content', true);
        $data['privacyAgree'] = $this->post('privacyAgree', true);
        $data['age'] = $this->post('age', true);
        $data['callTime'] = $this->post('callTime', true);
        $data['sex'] = $this->post('sex', true);
        $data['parentId'] = $this->post('parentId', true);
        $data['supplyThirdPartyAgree'] = $this->post('supplyThirdPartyAgree', true);
        $data['fingerPrint'] = $this->post('fingerPrint', true);
        $data['region'] = $this->post('region', true);
        $data['isSavePhone'] = $this->post('isSavePhone', true);


        $result = $this->outApi_m->setEventDb($data);

        if($result !== false)
        {
            $this->response([
                'status' => 'success',
                'code' => '200',
                'message' => '등록완료',
                'result' => null
            ], 200);
        }
        else
        {
            $this->response([
                'status' => 'error',
                'code' => '614', //insert 실패
                'message' => '서버관리자에게 문의하세요.',
                'result' => null
            ], 200);
        }
    }

    /**
     * @api {post} /outApi/updateEventDb v1 신청 db 상태 수정
     * @apiHeader {string} x-api-key="qJqKDATY0D48ZH9hCNW4N6ygHBqtoVz4a4mu0P6A"
     * @apiSampleRequest /api/v1.0/outApi/updateEventDb
     * @apiVersion 1.0.0
     * @apiName outApi updateEventDb
     * @apiGroup OutApi
     * @apiDescription v1 신청 db 상태 수정. 중복, 결번처리 등 업데이트
     * @apiParam {Number} callRequestId 신청번호
     * @apiParam {Number} status 신청상태. 8 중복, 9 결번, 10 삭제
     *
     * @apiSuccess {String="success", "error"} status="success" 상태
     * @apiSuccess {Number} code 코드값
     * @apiSuccess {String} message 수정완료
     * @apiSuccess {Object[]} result 빈배열
     *
     * @apiUse FailUpdateError
     * @apiUse NotAuthTokenError
     */
    function updateEventDb_post()
    {
        //필수키
        $data['callRequestId'] = $this->post('callRequestId', true);
        $data['status'] = $this->post('status', true);

        //필수체크
        $this->_check_value($data);

        $result = $this->outApi_m->updateEventDb($data); //var_dump($result); exit;

        if($result != false)
        {
            $this->response([
                'status' => 'success',
                'code' => '200',
                'message' => '수정완료',
                'result' => null
            ], 200);
        }
        else
        {
            $this->response([
                'status' => 'error',
                'code' => '605', //업데이트 실패
                'message' => '업데이트 대상번호가 없습니다.',
                'result' => null
            ], 200);
        }
    }

    /**
     * @api {post} /outApi/getAdsInfoForBoard 마이페이지용 이벤트 정보 조회
     * @apiHeader {string} x-api-key="qJqKDATY0D48ZH9hCNW4N6ygHBqtoVz4a4mu0P6A"
     * @apiSampleRequest /api/v1.0/outApi/getAdsInfoForBoard
     * @apiVersion 1.0.0
     * @apiName outApi getAdsInfoForBoard
     * @apiGroup OutApi
     * @apiDescription 앱 마이페이지용 이벤트 정보 조회
     * @apiParam {String} adsId 조회할 이벤트번호. 1,2,3 형태
     *
     * @apiSuccess {String="success", "error"} status="success" 상태
     * @apiSuccess {Number} code 코드값
     * @apiSuccess {String} message 빈값
     * @apiSuccess {Object[]} result 배열
     * @apiSuccess {Object[]} result.data 배열
     * @apiSuccess {Object[]} result.data.category 카테고리 배열
     * @apiSuccess {Number} result.data.category.id 카테고리 번호
     * @apiSuccess {String} result.data.category.name 카테고리 이름
     * @apiSuccess {Number} result.data.hospitalId 병원번호
     * @apiSuccess {Number} result.data.adsId 이벤트번호
     * @apiSuccess {Number} result.data.hospitalName 병원이름
     * @apiSuccess {String} result.data.image 정방향이미지
     * @apiSuccess {String} result.data.thumbnailImage 리스트이미지
     * @apiSuccess {String} result.data.title 이벤트 제목
     * @apiSuccess {String} result.data.discountCost 굿닥가
     * @apiSuccess {String} result.data.generalCost 정상가
     * @apiSuccess {String} result.data. 정상가
     *
     * @apiUse FailGetError
     * @apiUse NotAuthTokenError
     */
    function getAdsInfoForBoard_post()
    {
        //필수키
        $data['adsId'] = $this->post('adsId', true);

        //필수체크
        $this->_check_value($data);

        $result = $this->outApi_m->getAdsInfoForBoard($data);

        if($result !== false)
        {
            $this->response([
                'status' => 'success',
                'code' => '200',
                'message' => '',
                'result' => is_array($result) ? (object) $result : (object) [$result]
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
     * @api {post} /outApi/getAdsInfoForSearch 통합검색 업데이트용 이벤트 정보 조회
     * @apiHeader {string} x-api-key="qJqKDATY0D48ZH9hCNW4N6ygHBqtoVz4a4mu0P6A"
     * @apiSampleRequest /api/v1.0/outApi/getAdsInfoForSearch
     * @apiVersion 1.0.0
     * @apiName outApi getAdsInfoForSearch
     * @apiGroup OutApi
     * @apiDescription 통합검색 업데이트용 이벤트 정보 조회
     * @apiParam {String} adsId 조회할 이벤트번호. 1,2,3 형태
     *
     * @apiSuccess {String="success", "error"} status="success" 상태
     * @apiSuccess {Number} code 코드값
     * @apiSuccess {String} message 빈값
     * @apiSuccess {Object[]} result 배열
     * @apiSuccess {Object[]} result.contractHospital 병원정보 배열
     * @apiSuccess {Number} result.contractHospital.hospitalId 병원번호
     * @apiSuccess {String} result.contractHospital.isContract 계약유무. true, false
     * @apiSuccess {String} result.contractHospital.isAds 광고존재유무. true, false
     *
     * @apiUse FailGetError
     * @apiUse NotAuthTokenError
     */
    function getAdsInfoForSearch_post()
    {
        $this->load->model('ads_m');
        define('USERAUTHCODE', 4); //운영자로 셋팅

        //필수키
        $data['adsId'] = $this->post('adsId', true);

        //필수체크
        $this->_check_value($data);

        $result = $this->ads_m->getAds($data);

        if($result !== false)
        {
            $this->response([
                'status' => 'success',
                'code' => '200',
                'message' => '',
                'result' => is_array($result) ? (object) $result : (object) [$result]
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

    /**  ------------------------------------------ */

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

    /**  ------------------------------------------ */



}
