<?php
defined('BASEPATH') OR exit('No direct script access allowed');

// This can be removed if you use __autoload() in config.php OR use Modular Extensions
/** @noinspection PhpIncludeInspection */
require APPPATH . '/libraries/REST_Controller.php';

/**
 * User Controller
 * 회원 관련 api
 *
 * @category        Controller
 * @author          martin.byun@goodoc.co.kr
 */
class User extends REST_Controller {

    /**
     * Auth constructor.
     */
    function __construct()
    {
        parent::__construct();

        $this->load->model(array('user_m', 'common_m'));
        //$this->load->helper(['common']);

        $this->master = $this->load->database('master', true);
    }


    /**
     * @api {post} /user/list 회원리스트 조회
     * @apiHeader {string} x-api-key="qJqKDATY0D48ZH9hCNW4N6ygHBqtoVz4a4mu0P6A"
     * @apiHeader {string} x-api-token=""
     * @apiHeader {string} x-api-refreshToken=""
     * @apiHeader {string} x-api-userid="15"
     * @apiSampleRequest /api/v1.0/user/list
     * @apiVersion 1.0.0
     * @apiName customer list
     * @apiGroup User
     * @apiDescription 회원 리스트 조회
     * @apiParam {String} listAll Y 전체리스트, N 페이징 리스트
     * @apiParam {Number} limit 페이징시 한 페이지에 노출할 리스트수
     * @apiParam {Number} page="1" 페이지번호
     * @apiParam {Number} type="1" 회원구분 1 사용자, 2 운영자, 3 광고주
     *
     * @apiParam {String} [search_type] 검색구분(401,402 형태). type=1 : 1 비휴면, 0 휴면,  type=2 : 401 관리자, 402 통계운영자, 403 일반운영자, 404 접수설치, 405 외부운영자(NTL), 406 외부운영자(녹십자). type=3 201 광고주병원, 202 일반병원, 203 접수병원
     * @apiParam {String} [search_word] 검색어
     *
     * @apiSuccess (200) {String="success", "error"} status="success" 상태
     * @apiSuccess (200) {Number} code 코드값
     * @apiSuccess (200) {String} message 빈값
     * @apiSuccess (200) {Object[]} result 배열.
     * @apiSuccess (200) {Object[]} result.list 회원리스트
     * @apiSuccess (200) {Number} result.list.id 유저번호
     * @apiSuccess (200) {String} result.list.username 유저명
     * @apiSuccess (200) {String} result.list.email 이메일(아이디)
     * @apiSuccess (200) {Number} result.list.user_type 유저타입
     * @apiSuccess (200) {Number} result.list.where_from 가입종류 1 웹, 2 ios, 3 android, 4 어드민
     * @apiSuccess (200) {Number} result.list.provider sns 회원구분 9 = email(일반), 1 = facebook, 2 = naver, 3 = kakao
     * @apiSuccess (200) {Number} result.list.is_agency_account 대행사 계정 여부
     * @apiSuccess (200) {String} result.list.picture 프로필사진
     * @apiSuccess (200) {Number} result.list.bookings_count
     * @apiSuccess (200) {Number} result.list.user_know_hospitals_count
     * @apiSuccess (200) {Number} result.list.user_visit_hospitals_count
     * @apiSuccess (200) {Number} result.list.age 나이
     * @apiSuccess (200) {String} result.list.sex 성별
     * @apiSuccess (200) {String} result.list.job 직업
     * @apiSuccess (200) {Number} result.list.heath_point
     * @apiSuccess (200) {String} result.list.created_at 등록일시
     * @apiSuccess (200) {String} result.list.updated_at 수정일시
     * @apiSuccess (200) {String} result.list.last_login_at 마지막 로그인 일시
     * @apiSuccess (200) {String} result.list.last_logout_at 마지막 로그아웃 일시
     * @apiSuccess (200) {String} result.list.last_activity_at
     * @apiSuccess (200) {String} result.list.phone 전화번호
     * @apiSuccess (200) {String} result.list.note 메모
     * @apiSuccess (200) {Number} result.list.is_dormant 휴면계정여부 1 비휴면, 0 휴면
     * @apiSuccess (200) {String} result.list.dormant_at 휴면 등록일
     * @apiSuccess (200) {String} result.list.oauth_token
     * @apiSuccess (200) {String} result.list.uid sns 유저번호(로그인)
     * @apiSuccess (200) {String} result.list.group_auth_code 권한번호
     * @apiSuccess (200) {Number} result.totCnt 리스트 전체수
     *
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *       'status': 'success',
     *       'code': '200',
     *       'message': '',
     *       'result': {
     *          배열
     *        }
     *     }
     *
     * @apiUse FailGetError
     * @apiUse NotAuthTokenError
     */
    public function list_post()
    {
        //회원리스트 조회
        $auth_arr = $this->load->get_vars();

        //필수키
        $data['token'] = $auth_arr['token'];
        $data['users_id'] = $auth_arr['users_id'];
        $data['refreshToken'] = $auth_arr['refreshToken'];
        $data['listAll'] = $this->post('listAll', true); //Y 전체 리스트, N 페이징리스트
        $data['limit'] = $this->post('limit', true); //1페이지에 표시하는 리스트수
        $data['page'] = $this->post('page', true); //페이지 번호
        $data['type'] = $this->post('type', true); //구분 1 사용자, 2 운영자, 3 광고주

        //필수체크
        $this->_check_value($data);

        //검색어
        $data['search_type'] = $this->post('search_type', true);
        $data['search_word'] = $this->post('search_word', true);

        //api method별 하드코딩
        $data['menu_id'] = [180000, 'A00000']; //auth_code_catetory 유저관리 번호

        //토큰 유효성 및 권한 체크
        $check = $this->common_m->checkToken($data); //var_dump($check); exit;

        if($check['status'] == 'error' or $check == '')
        {
            $this->response([
                'status' => $check['status'],
                'code' => $check['code'],
                'message' => $check['message'],
                'result' => is_null( $check['result'] ) ? $check['result'] : (object)  $check['result']
            ], 200);
        }

        $user = $this->user_m->getUserList($data);

        if($user['list'] !== false)
        {
            $this->response([
                'status' => 'success',
                'code' => '200',
                'message' => '',
                'result' => is_arry( $user ) ? (object) $user : (object) [$user]
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
     * @api {post} /user/getUserInfo 회원 정보 조회
     * @apiHeader {string} x-api-key="qJqKDATY0D48ZH9hCNW4N6ygHBqtoVz4a4mu0P6A"
     * @apiHeader {string} x-api-token=""
     * @apiHeader {string} x-api-refreshToken=""
     * @apiHeader {string} x-api-userid="15"
     * @apiSampleRequest /api/v1.0/user/getUserInfo
     * @apiVersion 1.0.0
     * @apiName customer Info
     * @apiGroup User
     * @apiDescription 회원 정보 조회
     * @apiParam {Number} getUserId 조회하려는 회원번호
     * @apiParam {Number} type="1" 회원구분 1 사용자, 2 운영자, 3 광고주
     *
     * @apiSuccess (200) {String="success", "error"} status="success" 상태
     * @apiSuccess (200) {Number} code 코드값
     * @apiSuccess (200) {String} message 빈값
     * @apiSuccess (200) {Object[]} result 배열. 회원정보
     * @apiSuccess (200) {Number} result.id 유저번호
     * @apiSuccess (200) {String} result.username 유저명
     * @apiSuccess (200) {String} result.email 이메일(아이디)
     * @apiSuccess (200) {Number} result.user_type 유저타입
     * @apiSuccess (200) {Number} result.where_from 가입종류 1 웹, 2 ios, 3 android, 4 어드민
     * @apiSuccess (200) {Number} result.provider sns 회원구분 9 = email(일반), 1 = facebook, 2 = naver, 3 = kakao
     * @apiSuccess (200) {Number} result.is_agency_account 대행사 계정 여부
     * @apiSuccess (200) {String} result.picture 프로필사진
     * @apiSuccess (200) {Number} result.bookings_count
     * @apiSuccess (200) {Number} result.user_know_hospitals_count
     * @apiSuccess (200) {Number} result.user_visit_hospitals_count
     * @apiSuccess (200) {Number} result.age 나이
     * @apiSuccess (200) {String} result.sex 성별
     * @apiSuccess (200) {String} result.job 직업
     * @apiSuccess (200) {Number} result.heath_point
     * @apiSuccess (200) {String} result.created_at 등록일시
     * @apiSuccess (200) {String} result.updated_at 수정일시
     * @apiSuccess (200) {String} result.last_login_at 마지막 로그인 일시
     * @apiSuccess (200) {String} result.last_logout_at 마지막 로그아웃 일시
     * @apiSuccess (200) {String} result.last_activity_at
     * @apiSuccess (200) {String} result.phone 전화번호
     * @apiSuccess (200) {String} result.note 메모
     * @apiSuccess (200) {Number} result.is_dormant 휴면계정여부 1 비휴면, 0 휴면
     * @apiSuccess (200) {String} result.dormant_at 휴면 등록일
     * @apiSuccess (200) {String} result.oauth_token
     * @apiSuccess (200) {String} result.uid sns 유저번호(로그인)
     * @apiSuccess (200) {String} result.group_auth_code 권한번호
     *
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *       'status': 'success',
     *       'code': '200',
     *       'message': '',
     *       'result': {
     *          배열
     *        }
     *     }
     *
     * @apiUse FailGetError
     * @apiUse NotAuthTokenError
     */
    function getUserInfo_post()
    {
        //회원 조회

        $auth_arr = $this->load->get_vars();

        //필수키
        $data['token'] = $auth_arr['token'];
        $data['refreshToken'] = $auth_arr['refreshToken'];
        $data['users_id'] = $auth_arr['users_id'];
        $data['getUserId'] = $this->post('getUserId', true); //조회하려는 회원번호
        $data['type'] = $this->post('type', true); //구분 1 사용자, 2 운영자, 3 광고주

        //필수체크
        $this->_check_value($data);

        //api method별 하드코딩
        $data['menu_id'] = [180000, 'A00000']; //auth_code_catetory 유저관리 번호
        
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

        $user = $this->user_m->getUserInfo($data);

        if($user !== false)
        {
            $this->response([
                'status' => 'success',
                'code' => '200',
                'message' => '',
                'result' => is_arry( $user ) ? (object) $user : (object) [$user]
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
