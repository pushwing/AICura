<?php
defined('BASEPATH') OR exit('No direct script access allowed');

// This can be removed if you use __autoload() in config.php OR use Modular Extensions
/** @noinspection PhpIncludeInspection */
require APPPATH . '/libraries/REST_Controller.php';

/**
 * EventReqeust Controller
 * 신청 관련 api
 *
 * @category        Controller
 * @author          martin.byun@goodoc.co.kr
 */
class EventRequest extends REST_Controller
{
    function __construct()
    {
        parent::__construct();

        $this->load->model(array('eventRequest_m', 'common_m', 'replicator_m'));

        $this->master = $this->load->database('master', true);
    }

    /**
     * @api {post} /eventRequest/update 신청 상태 변경
     * @apiHeader {string} x-api-key="qJqKDATY0D48ZH9hCNW4N6ygHBqtoVz4a4mu0P6A"
     * @apiHeader {string} x-api-token=""
     * @apiHeader {string} x-api-refreshtoken=""
     * @apiHeader {string} x-api-userid=""
     * @apiSampleRequest /api/v1.0/eventRequest/update
     * @apiVersion 1.0.0
     * @apiName eventRequest update
     * @apiGroup EventRequest
     * @apiDescription 신청 상태 변경
     * @apiParam {Number} requestId 신청번호
     * @apiParam {Number} status 신청상태. 2 부재중, 3 취소, 4 기타, 5 예약, 6 예약취소, 7 내원완료
     * @apiParam {Number} nowStatus 현재 신청상태값
     * @apiParam {String} [bookingDate] 예약일 2018-11-20
     * @apiParam {String} [bookingTime] 예약시간 16:00
     *
     * @apiSuccess {String="success", "error"} status="success" 상태
     * @apiSuccess {Number} code 코드값
     * @apiSuccess {String} message 신청상태가 변경되었습니다.
     * @apiSuccess {Object} result null
     *
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *       'status': 'success',
     *       'code': '200',
     *       'message': '신청상태가 변경되었습니다.',
     *       'result': null
     *     }
     *
     * @apiUse FailUpdateError
     * @apiUse DifferentFormatError
     * @apiUse NotAuthTokenError
     */
    public function update_post()
    {
        $auth_arr = $this->load->get_vars();

        //필수키
        $data['token'] = $auth_arr['token'];
        $data['refreshToken'] = $auth_arr['refreshToken'];
        $data['users_id'] = $auth_arr['users_id'];

        $data['requestId'] = $this->post('requestId', true);
        $data['nowStatus'] = $this->post('nowStatus', true);
        $data['status'] = $this->post('status', true);

        //필수체크
        $this->_check_value($data);

        $data['bookingDate'] = $this->post('bookingDate', true);
        $data['bookingTime'] = $this->post('bookingTime', true);


        if ($data['status'] == 5)
        {
            if($data['bookingDate'] == '' or $data['bookingTime'] == '')
            {
                $this->response([
                    'status' => 'error',
                    'code' => '616',
                    'message' => '예약일 경우 예약일시를 입력해야합니다.',
                    'result' => null
                ], 200);
            }
        }

            //미확인, 부재중, 기타, 취소, 예약취소 → 부재중, 기타, 취소, 예약취소, 예약
        if(in_array($data['nowStatus'], [1,2,3,4,6]))
        {
            if(!in_array($data['status'], [2,3,4,5,6]))
            {
                $this->response([
                    'status' => 'error',
                    'code' => '616',
                    'message' => '변경가능한 상태를 확인하세요.',
                    'result' => null
                ], 200);
            }
        }

        if ($data['nowStatus'] == 5)
        {
            if($data['status'] == 5)
            {
                //둘다 5(예약)인 경우는 시간을 변경하도록 한다.
                if($data['bookingDate'] == '' or $data['bookingTime'] == '')
                {
                    $this->response([
                        'status' => 'error',
                        'code' => '616',
                        'message' => '예약일 경우 예약일시를 입력해야합니다.',
                        'result' => null
                    ], 200);
                }
            }
            else if(!in_array($data['status'], [6,7]))
            {
                $this->response([
                    'status' => 'error',
                    'code' => '616',
                    'message' => '예약상태에서는 예약취소와 내원완료로만 변경할 수 있습니다.',
                    'result'  => null
                ], 200);
            }
        }

        //api method별 하드코딩, 권한
        $data['menu_id'] = [200301]; //auth_code_catetory 서비스운영-앱관리-공지사항 번호

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

        //db 입력후 후기번호 리턴
        $result = $this->eventRequest_m->updateRequest($data);

        if ($result['code'] == 200)
        {
            $this->response([
                'status' => 'success',
                'code' => '200',
                'message' => '신청이 수정되었습니다.',
                'result' => null
            ], 200);
        }
        else
        {
            $this->response([
                'status' => 'error',
                'code' => '605',
                'message' => '서버관리자에게 문의하세요.',
                'result' => null
            ], 200);
        }
    }

    /**
     * @api {post} /eventRequest/lists 신청 리스트(병원 어드민용)
     * @apiHeader {string} x-api-key="qJqKDATY0D48ZH9hCNW4N6ygHBqtoVz4a4mu0P6A"
     * @apiHeader {string} x-api-token=""
     * @apiHeader {string} x-api-refreshtoken=""
     * @apiHeader {string} x-api-userid=""
     * @apiHeader {string} x-api-hospitalid=""
     * @apiSampleRequest /api/v1.0/eventRequest/lists
     * @apiVersion 1.0.0
     * @apiName eventRequest list
     * @apiGroup EventRequest
     * @apiDescription 신청 리스트(병원 어드민)
     * @apiParam {Number} [status] 신청상태(없으면 전체). 1 미확인, 2 부재중, 3 취소, 4 기타, 5 예약, 6 예약취소, 7 내원완료, 8 중복, 9 결번
     * @apiParam {Number} page=1 페이징번호
     * @apiParam {Number} limit=10 페이지당 출력갯수
     * @apiParam {String} [channel]  노출대상 채널. 1 굿닥, 2 굿닥파트너스
     * @apiParam {Number} [searchDateType] 기간타입. 1 오늘, 2 어제, 3 최근7일, 4 최근 30일, 5 이번달, 6 기간설정(최대 3개월)
     * @apiParam {String} [searchDateValue] 기간. searchDateType=6일 경우 2018-11-21T01:00:00.000Z|2018-11-22T01:00:00.000Z 형태로 날짜구간 전송 (-9 시간 전송)
     * @apiParam {Number} [searchEventId] 이벤트번호별 검색. (없으면 전체)
     * @apiParam {String} [searchWord] 검색어. 기본은 이름, 전화번호대상.
     *
     * @apiSuccess {String="success", "error"} status="success" 상태
     * @apiSuccess {Number} code 코드값
     * @apiSuccess {String} message 빈값
     * @apiSuccess {Object[]} result 배열
     * @apiSuccess {Object[]} result.data 후기 배열
     * @apiSuccess {Number} result.data.requestId 신청번호
     * @apiSuccess {Number} result.data.callRequestId 신청번호(v1)
     * @apiSuccess {String} result.data.adsId 이벤트번호
     * @apiSuccess {Number} result.data.status 신청상태
     * @apiSuccess {String} result.data.name 신청자명
     * @apiSuccess {String} result.data.phone 신청자번호
     * @apiSuccess {String} result.data.content 문의내용
     * @apiSuccess {String} result.data.regDate 신청일시
     * @apiSuccess {String} result.data.memo 메모내용. 파이프(|)로 구분된 형태 (2018-11-21 전화안받음|2018-11-21 전화안받음2)
     * @apiSuccess {Number} result.data.userId 신청자번호
     * @apiSuccess {Number} result.data.age 출생연도
     * @apiSuccess {Number} result.data.sex 성별
     * @apiSuccess {String} result.data.callTime 통화가능시간
     * @apiSuccess {Number} result.data.region 지역
     * @apiSuccess {String} result.data.eventName 이벤트명
     * @apiSuccess {String} result.data.bookDate 예약일시
     * @apiSuccess {String} result.data.dbCost db단가
     * @apiSuccess {String} result.data.channel 노출대상 채널번호. 1 굿닥, 2 굿닥파트너스
     * @apiSuccess {String} result.data.funnel 퍼널
     * @apiSuccess {Number} result.totCount 총 신청 수
     *
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *       'status': 'success',
     *       'code': '200',
     *       'message': '',
     *       'result': {
     *          'data' : {},
     *          'totCount' : 0
     *        }
     *     }
     *
     * @apiUse FailGetError
     * @apiUse NotAuthTokenError
     */
    public function lists_post()
    {
        $auth_arr = $this->load->get_vars();

        //필수키
        $data['token'] = $auth_arr['token'];
        $data['refreshToken'] = $auth_arr['refreshToken'];
        $data['users_id'] = $auth_arr['users_id'];

        $data['page'] = $this->post('page', true);
        $data['limit'] = $this->post('limit', true);


        //필수체크
        $this->_check_value($data);

        $data['channel'] = $this->post('channel', true);

        $data['status'] = $this->post('status', true);
        $data['searchDateType'] = $this->post('searchDateType', true);
        $data['searchDateValue'] = $this->post('searchDateValue', true);
        $data['searchEventId'] = $this->post('searchEventId', true);
        $data['searchWord'] = $this->post('searchWord', true);

        if(in_array($data['searchDateType'], [1,2,3,4,5,6]))
        {
            if($data['searchDateValue'] == '')
            {
                $this->response([
                    'status' => 'error',
                    'code' => '606',
                    'message' => '기간은 필수값입니다.',
                    'result'  => null
                ], '200');
            }
        }

        //api method별 하드코딩, 권한
        // TODO 아직 정의된 코드 없어 수정 필요
        $data['menu_id'] = [200301, 'A00000']; //auth_code_catetory 서비스운영-앱관리-공지사항 번호, 병원어드민도 권한 있음.

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

        //신청 리스트
        $result = $this->eventRequest_m->getRequestList($data);

        if($result['data'])
        {
            $this->response([
                'status' => 'success',
                'code' => '200',
                'message' => '',
                'result' => (object) $result
            ], 200);
        }
        else
        {
            $this->response([
                'status' => 'success',
                'code' => '200',
                'message' => '데이터가 없습니다.',
                'result' => (object) ['data' => [], 'totCount' => 0]
            ], 200);
        }
    }

    /**
     * @api {post} /eventRequest/getRequestStatus 신청 리스트 상단 대시보드 값
     * @apiHeader {string} x-api-key="qJqKDATY0D48ZH9hCNW4N6ygHBqtoVz4a4mu0P6A"
     * @apiHeader {string} x-api-token=""
     * @apiHeader {string} x-api-refreshtoken=""
     * @apiHeader {string} x-api-userid=""
     * @apiSampleRequest /api/v1.0/eventRequest/getRequestStatus
     * @apiVersion 1.0.0
     * @apiName eventRequest getRequestStatus
     * @apiGroup EventRequest
     * @apiDescription 신청 리스트 상단 대시보드 값들
     *
     * @apiParam {String} [channel=1]  노출대상 채널. 1 굿닥, 2 굿닥파트너스
     * @apiParam {Number} [searchDateType] 검색타입. 1 오늘, 2 어제, 3 최근7일, 4 최근 30일, 5 이번달, 6 기간설정(최대 3개월)
     * @apiParam {Number} [searchDateValue] 기간. searchDateType=6일 경우 2018-11-21T01:00:00.000Z|2018-11-22T01:00:00.000Z 형태로 날짜구간 전송 (-9 시간 전송)
     * @apiParam {Number} [searchEventId] 이벤트번호별 검색. (없으면 전체)
     * @apiParam {String} [searchWord] 검색어. 기본은 이름, 전화번호대상.

     *
     * @apiSuccess {String="success", "error"} status="success" 상태
     * @apiSuccess {Number} code 코드값
     * @apiSuccess {String} message 빈값
     * @apiSuccess {Object[]} result 배열
     * @apiSuccess {Number} result.data.return0 전체신청자
     * @apiSuccess {Number} result.data.return1 미확인
     * @apiSuccess {Number} result.data.return2 부재중
     * @apiSuccess {Number} result.data.return3 취소
     * @apiSuccess {Number} result.data.return4 기타
     * @apiSuccess {Number} result.data.return5 예약
     * @apiSuccess {Number} result.data.return6 예약취소
     * @apiSuccess {Number} result.data.return7 내원완료
     * @apiSuccess {Number} result.data.return8 중복
     * @apiSuccess {Number} result.data.return9 결번
     *
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *       'status': 'success',
     *       'code': '200',
     *       'message': '',
     *       'result': {
     *          'data' : {}
     *        }
     *     }
     *
     * @apiUse FailGetError
     * @apiUse NotAuthTokenError
     */
    public function getRequestStatus_post()
    {
        $auth_arr = $this->load->get_vars();

        //필수키
        $data['token'] = $auth_arr['token'];
        $data['refreshToken'] = $auth_arr['refreshToken'];
        $data['users_id'] = $auth_arr['users_id'];

        $this->_check_value($data);

        $data['channel'] = $this->post('channel', true);
        $data['searchDateType'] = $this->post('searchDateType', true);
        $data['searchDateValue'] = $this->post('searchDateValue', true);
        $data['searchEventId'] = $this->post('searchEventId', true);
        $data['searchWord'] = $this->post('searchWord', true);

        //api method별 하드코딩, 권한
        $data['menu_id'] = [200301, 'A00000']; //auth_code_catetory 서비스운영-앱관리-공지사항 번호, 병원어드민도 권한 있음.

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

        //신청 리스트
        $result = $this->eventRequest_m->getRequestStatus($data);

        if($result)
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
                'status' => 'success',
                'code' => '200',
                'message' => '데이터가 없습니다.',
                'result' => null
            ], 200);
        }
    }

    /**
     * @api {post} /eventRequest/memo 신청 메모 입력
     * @apiHeader {string} x-api-key="qJqKDATY0D48ZH9hCNW4N6ygHBqtoVz4a4mu0P6A"
     * @apiHeader {string} x-api-token=""
     * @apiHeader {string} x-api-refreshtoken=""
     * @apiHeader {string} x-api-userid=""
     * @apiSampleRequest /api/v1.0/eventRequest/memo
     * @apiVersion 1.0.0
     * @apiName eventRequest memo
     * @apiGroup EventRequest
     * @apiDescription 신청 메모 입력
     * @apiParam {Number} requestId 신청번호
     * @apiParam {String} memo 메모내용
     * @apiSuccess {String="success", "error"} status="success" 상태
     * @apiSuccess {Number} code 코드값
     * @apiSuccess {String} message 입력되었습니다.
     * @apiSuccess {Object} result null
     *
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *       'status': 'success',
     *       'code': '200',
     *       'message': '입력되었습니다.',
     *       'result': null
     *     }
     *
     * @apiUse RegistFailError
     * @apiUse NotAuthTokenError
     */
    public function memo_post()
    {
        $auth_arr = $this->load->get_vars();

        //필수키
        $data['token'] = $auth_arr['token'];
        $data['refreshToken'] = $auth_arr['refreshToken'];
        $data['users_id'] = $auth_arr['users_id'];

        $data['requestId'] = $this->post('requestId', true);
        $data['memo'] = $this->post('memo', true);

        //필수체크
        $this->_check_value($data);

        //api method별 하드코딩, 권한
        $data['menu_id'] = [200301, 'A00000']; //auth_code_catetory 서비스운영-앱관리-공지사항 번호, 비회원, 병원어드민도 권한 있음.
        //비회원은 모르겠음

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

        //메모입력
        $result = $this->eventRequest_m->setRequestMemo($data);

        if($result)
        {
            $this->response([
                'status' => 'success',
                'code' => '200',
                'message' => '입력되었습니다.',
                'result' => []
            ], 200);
        }
        else
        {
            $this->response([
                'status' => 'success',
                'code' => '614',
                'message' => '관리자에게 문의하세요',
                'result' => []
            ], 200);
        }
    }

    /**
     * @api {post} /eventRequest/parameter 신청 리스트 검색용 파라미터(이벤트번호)
     * @apiHeader {string} x-api-key="qJqKDATY0D48ZH9hCNW4N6ygHBqtoVz4a4mu0P6A"
     * @apiHeader {string} x-api-token=""
     * @apiHeader {string} x-api-refreshtoken=""
     * @apiHeader {string} x-api-userid=""
     * @apiSampleRequest /api/v1.0/eventRequest/parameter
     * @apiVersion 1.0.0
     * @apiName eventRequest parameter
     * @apiGroup EventRequest
     * @apiDescription 신청 리스트 검색용 파라미터(이벤트번호)
     * @apiSuccess {String="success", "error"} status="success" 상태
     * @apiSuccess {Number} code 코드값
     * @apiSuccess {String} message 빈값
     * @apiSuccess {Object[]} result
     * @apiSuccess {Object[]} result.info  신청 리스트 검색용 파라미터 결과 값 배열
     * @apiSuccess {Number} result.info.adsId 이벤트번호
     * @apiSuccess {Number} result.info.adTitle 이벤트 제목
     *
    * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *       'status': 'success',
     *       'code': '200',
     *       'message': '입력되었습니다.',
     *       'result': {
     *          'info' : {}
     *        }
     *     }
     * @apiUse FailGetError
     * @apiUse NotAuthTokenError
     */
    public function parameter_post()
    {
        $auth_arr = $this->load->get_vars();

        //필수키
        $data['token'] = $auth_arr['token'];
        $data['refreshToken'] = $auth_arr['refreshToken'];
        $data['users_id'] = $auth_arr['users_id'];

        //필수체크
        $this->_check_value($data);

        //api method별 하드코딩, 권한
        $data['menu_id'] = [11,49,103]; //auth_code_catetory 서비스운영-앱관리-공지사항 번호, 비회원, 병원어드민도 권한 있음.

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

        $result = $this->common_m->getEventId($data);

        if($result)
        {
            $this->response([
                'status' => 'success',
                'code' => '200',
                'message' => '.',
                'result' => (object)['info' => $result]
            ], 200);
        }
        else
        {
            $this->response([
                'status' => 'success',
                'code' => '610',
                'message' => '관리자에게 문의하세요',
                'result' => (object)['info' => []]
            ], 200);
        }
    }

    /**
     * @api {post} /eventRequest/view 신청 db 상세보기
     * @apiHeader {string} x-api-key="qJqKDATY0D48ZH9hCNW4N6ygHBqtoVz4a4mu0P6A"
     * @apiHeader {string} x-api-token=""
     * @apiHeader {string} x-api-refreshtoken=""
     * @apiHeader {string} x-api-userid=""
     * @apiHeader {string} x-api-hospitalid=""
     * @apiSampleRequest /api/v1.0/eventRequest/view
     * @apiVersion 1.0.0
     * @apiName eventRequest view
     * @apiGroup EventRequest
     * @apiDescription 신청 db 상세보기
     * @apiParam {Number} requestId 신청db 번호
     *
     * @apiSuccess {String="success", "error"} status="success" 상태
     * @apiSuccess {Number} code 코드값
     * @apiSuccess {String} message 빈값
     * @apiSuccess {Object[]} result 배열
     * @apiSuccess {Number} result.requestId 신청번호
     * @apiSuccess {Number} result.callRequestId 신청번호(v1)
     * @apiSuccess {String} result.adsId 이벤트번호
     * @apiSuccess {Number} result.status 신청상태
     * @apiSuccess {String} result.name 신청자명
     * @apiSuccess {String} result.phone 신청자번호
     * @apiSuccess {String} result.content 문의내용
     * @apiSuccess {String} result.modifyDate 수정일시
     * @apiSuccess {Number} result.userId 신청자번호
     * @apiSuccess {Number} result.age 출생연도
     * @apiSuccess {Number} result.sex 성별
     * @apiSuccess {String} result.callTime 통화가능시간
     * @apiSuccess {Number} result.region 지역
     * @apiSuccess {Number} result.dbCost db단가
     * @apiSuccess {Number} result.channel 노출대상 채널. 1 굿닥, 2 굿닥파트너스
     * @apiSuccess {String} result.bookDate 예약일시
     *
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *       'status': 'success',
     *       'code': '200',
     *       'message': '',
     *       'result': {
     *
     *        }
     *     }
     *
     * @apiUse FailGetError
     * @apiUse NotAuthTokenError
     */
    public function view_post()
    {
        $auth_arr = $this->load->get_vars();

        //필수키
        $data['token'] = $auth_arr['token'];
        $data['refreshToken'] = $auth_arr['refreshToken'];
        $data['users_id'] = $auth_arr['users_id'];

        $data['requestId'] = $this->post('requestId', true);

        //필수체크
        $this->_check_value($data);

        //api method별 하드코딩, 권한
        // TODO 아직 정의된 권한 없어 수정 필요
        $data['menu_id'] = [200301, 'A00000'];

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

        //신청 리스트
        $result = $this->eventRequest_m->getRequestView($data);

        if($result)
        {
            $this->response([
                'status' => 'success',
                'code' => '200',
                'message' => '',
                'result' => (object) $result
            ], 200);
        }
        else
        {
            $this->response([
                'status' => 'success',
                'code' => '200',
                'message' => '데이터가 없습니다.',
                'result' => (object) []
            ], 200);
        }
    }

    /**
     * @api {post} /eventRequest/adminLists 신청 리스트(운영 어드민용)
     * @apiHeader {string} x-api-key="qJqKDATY0D48ZH9hCNW4N6ygHBqtoVz4a4mu0P6A"
     * @apiHeader {string} x-api-token=""
     * @apiHeader {string} x-api-refreshtoken=""
     * @apiHeader {string} x-api-userid=""
     * @apiSampleRequest /api/v1.0/eventRequest/adminLists
     * @apiVersion 1.0.0
     * @apiName adminList
     * @apiGroup EventRequest
     * @apiDescription 신청 리스트(운영 어드민), 이벤트번호 및 병원번호 다중 입력 처리?
     * @apiParam {Number} [status] 신청상태(없으면 전체). 1 미확인, 2 부재중, 3 취소, 4 기타, 5 예약, 6 예약취소, 7 내원완료, 8 중복, 9 결번
     * @apiParam {Number} page=1 페이징번호
     * @apiParam {Number} limit=10 페이지당 출력갯수
     * @apiParam {String} [channel]  노출대상 채널. 1 굿닥, 2 굿닥파트너스
     * @apiParam {Number} dateType 기간타입. 1 오늘, 2 어제, 3 최근7일, 4 최근 30일, 5 이번달, 6 기간설정(최대 3개월)
     * @apiParam {String} dateValue 기간. dateType=6일 경우 2018-11-21T01:00:00.000Z|2018-11-22T01:00:00.000Z 형태로 날짜구간 전송 (-9 시간 전송)
     * @apiParam {String} [eventId] 이벤트번호별 검색. (없으면 전체). 콤마로 구분하여 여러개 검색
     * @apiParam {Number} [eventType] 이벤트타입. 광고유형 1cpa, 2 cpm, 3 프로모션, 4 cpc
     * @apiParam {Number} [device] 디바이스 1 안드, 2 ios, 3 웹
     * @apiParam {Number} [category] 이벤트 카테고리
     * @apiParam {String} [funnel] 퍼널
     * @apiParam {Number} [adTitle] 이벤트명 검색
     * @apiParam {Number} [searchType] 검색타입. 1 이름, 2 전화번호, 3 병원번호(콤마로 구분하여 여러개 검색)
     * @apiParam {String} [searchWord] 검색어.
     *
     * @apiSuccess {String="success", "error"} status="success" 상태
     * @apiSuccess {Number} code 코드값
     * @apiSuccess {String} message 빈값
     * @apiSuccess {Object[]} result 배열
     * @apiSuccess {Object[]} result.data 후기 배열
     * @apiSuccess {Number} result.data.requestId 신청번호
     * @apiSuccess {Number} result.data.callRequestId 신청번호(v1)
     * @apiSuccess {String} result.data.adsId 이벤트번호
     * @apiSuccess {Number} result.data.status 신청상태
     * @apiSuccess {String} result.data.name 신청자명
     * @apiSuccess {String} result.data.phone 신청자번호
     * @apiSuccess {String} result.data.content 문의내용
     * @apiSuccess {String} result.data.regDate 신청일시
     * @apiSuccess {String} result.data.memo 메모내용. 파이프(|)로 구분된 형태 (2018-11-21 전화안받음|2018-11-21 전화안받음2)
     * @apiSuccess {Number} result.data.userId 신청자번호
     * @apiSuccess {Number} result.data.age 출생연도
     * @apiSuccess {Number} result.data.sex 성별
     * @apiSuccess {String} result.data.callTime 통화가능시간
     * @apiSuccess {Number} result.data.region 지역
     * @apiSuccess {String} result.data.eventName 이벤트명
     * @apiSuccess {String} result.data.bookDate 예약일시
     * @apiSuccess {String} result.data.dbCost db단가
     * @apiSuccess {String} result.data.channel 노출대상 채널번호. 1 굿닥, 2 굿닥파트너스
     * @apiSuccess {String} result.data.funnel 퍼널
     * @apiSuccess {String} result.data.hospitalId 병원번호
     * @apiSuccess {String} result.data.hospitalType 병원타입
     * @apiSuccess {String} result.data.hospitalName 병원명
     * @apiSuccess {String} result.data.device 디바이스. 1 안드, 2 ios, 3 웹
     * @apiSuccess {Number} result.totCount 총 신청 수
     *
     * @apiUse FailGetError
     * @apiUse NotAuthTokenError
     */
    public function adminLists_post()
    {
        $auth_arr = $this->load->get_vars();

        //필수키
        $data['token'] = $auth_arr['token'];
        $data['refreshToken'] = $auth_arr['refreshToken'];
        $data['users_id'] = $auth_arr['users_id'];
        $data['page'] = $this->post('page', true);
        $data['limit'] = $this->post('limit', true);

        //필수체크
        $this->_check_value($data);

        $data['channel'] = $this->post('channel', true);
        $data['status'] = $this->post('status', true);
        $data['dateType'] = $this->post('dateType', true);
        $data['dateValue'] = $this->post('dateValue', true);
        $data['eventId'] = $this->post('eventId', true);
        $data['eventType'] = $this->post('eventType', true);
        $data['device'] = $this->post('device', true);
        $data['category'] = $this->post('category', true);
        $data['funnel'] = $this->post('funnel', true);
        $data['adTitle'] = $this->post('adTitle', true);
        $data['searchType'] = $this->post('searchType', true);
        $data['searchWord'] = $this->post('searchWord', true);

        if(in_array($data['dateType'], [1,2,3,4,5,6]))
        {
            if($data['dateValue'] == '')
            {
                $this->response([
                    'status' => 'error',
                    'code' => '606',
                    'message' => '기간은 필수값입니다.',
                    'result'  => null
                ], '200');
            }
        }

        if($data['searchType'])
        {
            if($data['searchWord'] == '')
            {
                $data['searchType'] = '';
            }
        }

        //api method별 하드코딩, 권한
        // 현재는 이벤트 하위로 들어가 해당 메뉴 권한으로 넣어놨지만, 추후 권한이 새로 정의되면 변경 필요
        $data['menu_id'] = [120000];

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

        //신청 리스트
        $result = $this->eventRequest_m->getAdminRequestList($data);

        if($result['data'])
        {
            $this->response([
                'status' => 'success',
                'code' => '200',
                'message' => '',
                'result' => (object) $result
            ], 200);
        }
        else
        {
            $this->response([
                'status' => 'success',
                'code' => '200',
                'message' => '데이터가 없습니다.',
                'result' => (object) ['data' => [], 'totCount' => 0]
            ], 200);
        }
    }

    /**
     * @api {post} /eventRequest/blackList 블랙 리스트(운영 어드민용)
     * @apiHeader {string} x-api-key="qJqKDATY0D48ZH9hCNW4N6ygHBqtoVz4a4mu0P6A"
     * @apiHeader {string} x-api-token=""
     * @apiHeader {string} x-api-refreshtoken=""
     * @apiHeader {string} x-api-userid=""
     * @apiSampleRequest /api/v1.0/eventRequest/blackList
     * @apiVersion 1.0.0
     * @apiName eventRequest blacklist
     * @apiGroup EventRequest
     * @apiDescription 블랙 리스트(운영 어드민)
     * @apiParam {Number} page=1 페이징번호
     * @apiParam {Number} limit=10 페이지당 출력갯수
     * @apiParam {String} [searchWord] 검색어. 전화번호
     *
     * @apiSuccess {String="success", "error"} status="success" 상태
     * @apiSuccess {Number} code 코드값
     * @apiSuccess {String} message 빈값
     * @apiSuccess {Object[]} result 배열
     * @apiSuccess {Object[]} result.data 블랙리스트 배열
     * @apiSuccess {Number} result.data.id 일련번호
     * @apiSuccess {String} result.data.regDate. 등록일
     * @apiSuccess {String} result.data.phone 전화번호
     * @apiSuccess {String} result.data.userName 처리자
     * @apiSuccess {String} result.data.desc 사유
     * @apiSuccess {Number} result.totCount 총 갯수
     *
     * @apiUse FailGetError
     * @apiUse NotAuthTokenError
     */
    public function blackList_post()
    {
        $auth_arr = $this->load->get_vars();

        //필수키
        $data['token'] = $auth_arr['token'];
        $data['refreshToken'] = $auth_arr['refreshToken'];
        $data['users_id'] = $auth_arr['users_id'];

        $data['page'] = $this->post('page', true);
        $data['limit'] = $this->post('limit', true);

        //필수체크
        $this->_check_value($data);

        $data['searchWord'] = $this->post('searchWord', true);

        //api method별 하드코딩, 권한
        $data['menu_id'] = [190200];

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

        //블랙리스트
        $result = $this->eventRequest_m->getBlackList($data);

        if($result['data'])
        {
            $this->response([
                'status' => 'success',
                'code' => '200',
                'message' => '',
                'result' => (object) $result
            ], 200);
        }
        else
        {
            $this->response([
                'status' => 'success',
                'code' => '200',
                'message' => '데이터가 없습니다.',
                'result' => (object) ['data' => [], 'totCount' => 0]
            ], 200);
        }
    }

    /**
     * @api {post} /eventRequest/blackListAdd 블랙 리스트 추가(운영 어드민용)
     * @apiHeader {string} x-api-key="qJqKDATY0D48ZH9hCNW4N6ygHBqtoVz4a4mu0P6A"
     * @apiHeader {string} x-api-token=""
     * @apiHeader {string} x-api-refreshtoken=""
     * @apiHeader {string} x-api-userid=""
     * @apiSampleRequest /api/v1.0/eventRequest/blackListAdd
     * @apiVersion 1.0.0
     * @apiName eventRequest blacklist add
     * @apiGroup EventRequest
     * @apiDescription 블랙 리스트 추가(운영 어드민)
     * @apiParam {Number} phone 페이징번호
     * @apiParam {String} desc 페이지당 출력갯수
     *
     * @apiSuccess {String="success", "error"} status="success" 상태
     * @apiSuccess {Number} code 코드값
     * @apiSuccess {String} message 블랙리스트가 추가되었습니다.
     * @apiSuccess {Object} result null
     *
     * @apiUse FailGetError
     * @apiUse NotAuthTokenError
     */
    public function blackListAdd_post()
    {
        $auth_arr = $this->load->get_vars();

        //필수키
        $data['token'] = $auth_arr['token'];
        $data['refreshToken'] = $auth_arr['refreshToken'];
        $data['users_id'] = $auth_arr['users_id'];

        $data['phone'] = $this->post('phone', true);
        $data['desc'] = $this->post('desc', true);

        //필수체크
        $this->_check_value($data);

        //api method별 하드코딩, 권한
        $data['menu_id'] = [190200];

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

        //중복체크
        $check = $this->eventRequest_m->checkBlackList($data);

        if($check)
        {
            $this->response([
                'status' => 'error',
                'code' => '606',
                'message' => '중복된 전화번호입니다.',
                'result' => null
            ], 200);
        }

        //블랙리스트 add
        $result = $this->eventRequest_m->setBlackList($data);

        if($result)
        {
            $this->response([
                'status' => 'success',
                'code' => '200',
                'message' => '블랙리스트가 추가되었습니다.',
                'result' => null
            ], 200);
        }
        else
        {
            $this->response([
                'status' => 'error',
                'code' => '606',
                'message' => '서버관리자에게 문의하세요.',
                'result' => null
            ], 200);
        }
    }

    /**
     * @api {post} /eventRequest/blackListDelete 블랙 리스트 삭제(운영 어드민용)
     * @apiHeader {string} x-api-key="qJqKDATY0D48ZH9hCNW4N6ygHBqtoVz4a4mu0P6A"
     * @apiHeader {string} x-api-token=""
     * @apiHeader {string} x-api-refreshtoken=""
     * @apiHeader {string} x-api-userid=""
     * @apiSampleRequest /api/v1.0/eventRequest/blackListDelete
     * @apiVersion 1.0.0
     * @apiName eventRequest blacklist delete
     * @apiGroup EventRequest
     * @apiDescription 블랙 리스트 삭(운영 어드민)
     * @apiParam {Number} id 블랙리스트 번호
     *
     * @apiSuccess {String="success", "error"} status="success" 상태
     * @apiSuccess {Number} code 코드값
     * @apiSuccess {String} message 블랙리스트가 삭되었습니다.
     * @apiSuccess {Object} result null
     *
     * @apiUse FailGetError
     * @apiUse NotAuthTokenError
     */
    public function blackListDelete_post()
    {
        $auth_arr = $this->load->get_vars();

        //필수키
        $data['token'] = $auth_arr['token'];
        $data['refreshToken'] = $auth_arr['refreshToken'];
        $data['users_id'] = $auth_arr['users_id'];

        $data['id'] = $this->post('id', true);

        //필수체크
        $this->_check_value($data);

        //api method별 하드코딩, 권한
        $data['menu_id'] = [190200];

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

        //블랙리스트 add
        $result = $this->eventRequest_m->deleteBlackList($data);

        if($result)
        {
            $this->response([
                'status' => 'success',
                'code' => '200',
                'message' => '블랙리스가 삭제되었습니다.',
                'result' => null
            ], 200);
        }
        else
        {
            $this->response([
                'status' => 'error',
                'code' => '606',
                'message' => '서버관리자에게 문의하세요.',
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
     * @apiDefine LogoutError
     *
     * @apiError Logout 로그아웃 에러
     *
     * @apiErrorExample Error-Response:
     *     HTTP/1.1 200 OK
     *     {
     *       "status": "error",
     *       "code": "609",
     *       "message": "로그아웃 실패하였습니다..",
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
     * @apiDefine FileUploadFailError
     *
     * @apiError FileUploadFail 파일 업로드 실패
     *
     * @apiErrorExample Error-Response:
     *     HTTP/1.1 200 OK
     *     {
     *       "status": "error",
     *       "code": "612",
     *       "message": "파일 업로드 실패했습니다.",
     *       "result": null
     *     }
     */

    /**
     * @apiDefine NotAuthError
     *
     * @apiError NotAuth 권한이 없습니다.
     *
     * @apiErrorExample Error-Response:
     *     HTTP/1.1 200 OK
     *     {
     *       "status": "error",
     *       "code": "613",
     *       "message": "권한이 없습니다.",
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
     * @apiDefine DuplicateFailError
     *
     * @apiError DuplicateFail 중복된 내용입니다.
     *
     * @apiErrorExample Error-Response:
     *     HTTP/1.1 200 OK
     *     {
     *       "status": "error",
     *       "code": "615",
     *       "message": "중복된 내용입니다.",
     *       "result": null
     *     }
     */

    /**
     * @apiDefine DifferentFormatError
     *
     * @apiError DifferentFormat 다른 포맷입니다.
     *
     * @apiErrorExample Error-Response:
     *     HTTP/1.1 200 OK
     *     {
     *       "status": "error",
     *       "code": "616",
     *       "message": "다른 포맷입니다.",
     *       "result": null
     *     }
     */

    /**  ------------------------------------------ */


}