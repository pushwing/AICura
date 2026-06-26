<?php
defined('BASEPATH') OR exit('No direct script access allowed');

// This can be removed if you use __autoload() in config.php OR use Modular Extensions
/** @noinspection PhpIncludeInspection */
require APPPATH . '/libraries/REST_Controller.php';

/**
 * AdvertiserBoard Controller
 * 공지사항, 1:1 문의등 게시판 관련 api
 *
 * @category        Controller
 * @author          martin.byun@goodoc.co.kr
 */
class AdvertiserBoard extends REST_Controller
{
    function __construct()
    {
        parent::__construct();

        $this->load->model(array('advertiserBoard_m', 'common_m'));
    
        $this->master = $this->load->database('master', true);
    }

    /**
     * @api {post} /advertiserBoard/write 게시글 작성
     * @apiHeader {string} x-api-key="qJqKDATY0D48ZH9hCNW4N6ygHBqtoVz4a4mu0P6A"
     * @apiHeader {string} x-api-token=""
     * @apiHeader {string} x-api-refreshtoken=""
     * @apiHeader {string} x-api-userid=""
     * @apiSampleRequest /api/v1.0/advertiserBoard/write
     * @apiVersion 1.0.0
     * @apiName advertiserBoard write
     * @apiGroup AdvertiserBoard
     * @apiDescription 게시글 작성
     * @apiParam {String} userName 유저이름
     * @apiParam {Number} type 게시판종류 1 공지사항, 2 1:1문의, 3 faq
     * @apiParam {String} title 게시글제목
     * @apiParam {String} contents 게시글내용
     * @apiParam {String} [fileArray] 사진주소 배열 json ["a.jpg","b.jpg","c.jpg"] 형태
     *
     * @apiSuccess {String="success", "error"} status="success" 상태
     * @apiSuccess {Number} code 코드값
     * @apiSuccess {String} message 작성되었습니다.
     * @apiSuccess {Object[]} result 배열
     * @apiSuccess {String} result.boardId 후기번호
     *
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *       'status': 'success',
     *       'code': '200',
     *       'message': '작성되었습니다.',
     *       'result': '메세지'
     *     }
     *
     * @apiUse RegistFailError
     * @apiUse DifferentFormatError
     * @apiUse NotAuthTokenError
     */
    public function write_post()
    {
        $auth_arr = $this->load->get_vars();

        //필수키
        $data['token'] = $auth_arr['token'];
        $data['refreshToken'] = $auth_arr['refreshToken'];
        $data['userId'] = $data['users_id'] = $auth_arr['users_id'];

        //필수파라미터
        $data['userName'] = $this->post('userName', true);
        $data['type'] = $this->post('type', true);
        $data['title'] = $this->post('title', true);
        $data['contents'] = $this->post('contents', true);

        //필수체크
        $this->_check_value($data);

        $data['photoJson'] = $this->post('fileArray', true); //필수아님

        if ($data['photoJson']) {
            //json 여부 검사
            $result = json_decode($data['photoJson']);

            if (json_last_error() !== JSON_ERROR_NONE) {
                // JSON is not valid
                // 'result' => json_encode([], JSON_FORCE_OBJECT)
                $this->response([
                    'status' => 'error',
                    'code' => '616',
                    'message' => '정상적인 json 포맷이 아닙니다.',
                    'result' => null
                ], 200);
            }
        }

        //api method별 하드코딩, 권한
        $data['menu_id'] = [200300]; //auth_code_catetory 서비스운영-앱관리-공지사항 번호

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
        $result = $this->advertiserBoard_m->setBoardInfo($data);

        if ($result['code'] == 200) {
            $this->response([
                'status' => 'success',
                'code' => '200',
                'message' => '게시글이 등록되었습니다.',
                'result' => (object) ['boardId' => $result['message']]
            ], 200);
        } else {
            $this->response([
                'status' => 'error',
                'code' => '614',
                'message' => '서버관리자에게 문의하세요.',
                'result' => (object) ['message' => $result['message']]
            ], 200);
        }
    }

    /**
     * @api {post} /advertiserBoard/update 게시글 수정
     * @apiHeader {string} x-api-key="qJqKDATY0D48ZH9hCNW4N6ygHBqtoVz4a4mu0P6A"
     * @apiHeader {string} x-api-token=""
     * @apiHeader {string} x-api-refreshtoken=""
     * @apiHeader {string} x-api-userid=""
     * @apiSampleRequest /api/v1.0/advertiserBoard/update
     * @apiVersion 1.0.0
     * @apiName advertiserBoard update
     * @apiGroup AdvertiserBoard
     * @apiDescription 게시글 수정
     * @apiParam {Number} type 게시판종류 1 공지사항, 2 1:1문의, 3 faq
     * @apiParam {Number} boardId 게시물번호
     * @apiParam {String} [userName] 유저이름
     * @apiParam {String} [title] 게시글제목
     * @apiParam {String} [contents] 게시글내용
     * @apiParam {String} [fileArray] 사진주소 배열 json ["a.jpg","b.jpg","c.jpg"] 형태
     *
     * @apiSuccess {String="success", "error"} status="success" 상태
     * @apiSuccess {Number} code 코드값
     * @apiSuccess {String} message 수정되었습니다.
     * @apiSuccess {Object} result null
     *
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *       'status': 'success',
     *       'code': '200',
     *       'message': '수정되었습니다.',
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
        $data['userId'] = $data['users_id'] = $auth_arr['users_id'];

        $data['type'] = $this->post('type', true);
        $data['boardId'] = $this->post('boardId', true);

        //필수체크
        $this->_check_value($data);

        $data['userName'] = $this->post('userName', true);
        $data['title'] = $this->post('title', true);
        $data['contents'] = $this->post('contents', true);

        $data['photoJson'] = $this->post('fileArray', true);

        if ($data['photoJson']) {
            //json 여부 검사
            $result = json_decode($data['photoJson']);

            if (json_last_error() !== JSON_ERROR_NONE) {
                // JSON is not valid
                $this->response([
                    'status' => 'error',
                    'code' => '616',
                    'message' => '정상적인 json 포맷이 아닙니다.',
                    'result' => null
                ], 200);
            }
        }

        $checkAuth = $this->advertiserBoard_m->checkBoardAuth($data);

        if($checkAuth == '')
        {
            $this->response([
                'status' => 'error',
                'code' => '613',
                'message' => '수정권한이 없습니다.',
                'result' => is_array($checkAuth) ? (object) $checkAuth : (object) [$checkAuth]
            ], 200);
        }

        //api method별 하드코딩, 권한
        $data['menu_id'] = [200300]; //auth_code_catetory 서비스운영-앱관리-공지사항 번호

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
        $result = $this->advertiserBoard_m->updateBoard($data);

        if ($result['code'] == 200) {
            $this->response([
                'status' => 'success',
                'code' => '200',
                'message' => '게시글이 수정되었습니다.',
                'result' => null
            ], 200);
        } else {
            $this->response([
                'status' => 'error',
                'code' => '605',
                'message' => '서버관리자에게 문의하세요.',
                'result' => null
            ], 200);
        }
    }

    /**
     * @api {post} /advertiserBoard/delete 게시글 삭제
     * @apiHeader {string} x-api-key="qJqKDATY0D48ZH9hCNW4N6ygHBqtoVz4a4mu0P6A"
     * @apiHeader {string} x-api-token=""
     * @apiHeader {string} x-api-refreshtoken=""
     * @apiHeader {string} x-api-userid=""
     * @apiSampleRequest /api/v1.0/advertiserBoard/delete
     * @apiVersion 1.0.0
     * @apiName advertiserBoard delete
     * @apiGroup AdvertiserBoard
     * @apiDescription 게시글 삭제
     * @apiParam {Number} type 게시판종류 1 공지사항, 2 1:1문의, 3 faq
     * @apiParam {Number} boardId 게시물번호
     *
     * @apiSuccess {String="success", "error"} status="success" 상태
     * @apiSuccess {Number} code 코드값
     * @apiSuccess {String} message 삭제되었습니다.
     * @apiSuccess {Object} result null
     *
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *       'status': 'success',
     *       'code': '200',
     *       'message': '삭제되었습니다.',
     *       'result': null
     *     }
     *
     * @apiUse FailUpdateError
     * @apiUse DifferentFormatError
     * @apiUse NotAuthTokenError
     */
    public function delete_post()
    {
        $auth_arr = $this->load->get_vars();

        //필수키
        $data['token'] = $auth_arr['token'];
        $data['refreshToken'] = $auth_arr['refreshToken'];
        $data['userId'] = $data['users_id'] = $auth_arr['users_id'];

        $data['type'] = $this->post('type', true);
        $data['boardId'] = $this->post('boardId', true);

        //필수체크
        $this->_check_value($data);

        $checkAuth = $this->advertiserBoard_m->checkBoardAuth($data);

        if($checkAuth == '')
        {
            $this->response([
                'status' => 'error',
                'code' => '613',
                'message' => '삭제권한이 없습니다.',
                'result' => null
            ], 200);
        }

        //api method별 하드코딩, 권한
        $data['menu_id'] = [200300]; //auth_code_catetory 서비스운영-앱관리-공지사항 번호

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
        $result = $this->advertiserBoard_m->deleteBoard($data);

        if ($result['code'] == 200) {
            $this->response([
                'status' => 'success',
                'code' => '200',
                'message' => '게시글이 삭제되었습니다.',
                'result' => null
            ], 200);
        } else {
            $this->response([
                'status' => 'error',
                'code' => '605',
                'message' => '서버관리자에게 문의하세요.',
                'result' => null
            ], 200);
        }
    }

    /**
     * @api {post} /advertiserBoard/lists 게시글 리스트
     * @apiHeader {string} x-api-key="qJqKDATY0D48ZH9hCNW4N6ygHBqtoVz4a4mu0P6A"
     * @apiHeader {string} x-api-token=""
     * @apiHeader {string} x-api-refreshtoken=""
     * @apiHeader {string} x-api-userid=""
     * @apiSampleRequest /api/v1.0/advertiserBoard/lists
     * @apiVersion 1.0.0
     * @apiName advertiserBoard list
     * @apiGroup AdvertiserBoard
     * @apiDescription 게시글 리스트
     * @apiParam {Number} type 게시판종류. 1 공지사항, 2 1:1문의, 3 faq
     * @apiParam {Number} page=1 페이징번호
     * @apiParam {Number} limit=10 페이지당 출력갯수
     * @apiParam {String} [searchWord] 검색어
     *
     * @apiSuccess {String="success", "error"} status="success" 상태
     * @apiSuccess {Number} code 코드값
     * @apiSuccess {String} message 빈값
     * @apiSuccess {Object[]} result 배열
     * @apiSuccess {Object[]} result.list 후기 배열
     * @apiSuccess {Number} result.list.boardId 후기번호
     * @apiSuccess {Number} result.list.userId 작성자번호
     * @apiSuccess {String} result.list.userName 작성자명
     * @apiSuccess {String} result.list.title 게시글 제목
     * @apiSuccess {String} result.list.contents 게시글 내용
     * @apiSuccess {String} result.list.regDate 등록일시
     * @apiSuccess {Number} result.list.hit 조회수
     * @apiSuccess {Number} result.list.type 게시판 종류. 1 공지사항, 2 1:1문의, 3 faq
     * @apiSuccess {Object[]} result.list.fileArray 첨부파일 배열
     * @apiSuccess {Number} result.list.fileArray.id 파일번호
     * @apiSuccess {Number} result.list.fileArray.boardId 게시물번호
     * @apiSuccess {String} result.list.fileArray.fileName 첨부파일주소
     * @apiSuccess {Number} result.totCount 총 게시글 수
     *
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *       'status': 'success',
     *       'code': '200',
     *       'message': '',
     *       'result': {
     *          'list' : {},    
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

        $data['type'] = $this->post('type', true);
        $data['page'] = $this->post('page', true);
        $data['limit'] = $this->post('limit', true);

        //필수체크
        $this->_check_value($data);

        $data['searchWord'] = $this->post('searchWord', true);

        //api method별 하드코딩, 권한
        $data['menu_id'] = [200300]; //auth_code_catetory 서비스운영-앱관리-공지사항 번호, 비회원, 병원어드민도 권한 있음.

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

        //게시글 리스트
        $result = $this->advertiserBoard_m->getBoardList($data);

        if($result['data'])
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
                'result' =>  [
                    'list' => [],    
                    'totCount' => 0
                ]       
            ], 200);
        }
    }

    /**
     * @api {post} /advertiserBoard/view 게시글 보기
     * @apiHeader {string} x-api-key="qJqKDATY0D48ZH9hCNW4N6ygHBqtoVz4a4mu0P6A"
     * @apiHeader {string} x-api-token=""
     * @apiHeader {string} x-api-refreshtoken=""
     * @apiHeader {string} x-api-userid=""
     * @apiSampleRequest /api/v1.0/advertiserBoard/view
     * @apiVersion 1.0.0
     * @apiName advertiserBoard view
     * @apiGroup AdvertiserBoard
     * @apiDescription 게시글 보기
     * @apiParam {Number} type 게시판종류. 1 공지사항, 2 1:1문의, 3 faq
     * @apiParam {Number} boardId 게시글번호
     *
     * @apiSuccess {String="success", "error"} status="success" 상태
     * @apiSuccess {Number} code 코드값
     * @apiSuccess {String} message 빈값
     * @apiSuccess {Object[]} result 배열
     * @apiSuccess {Object[]} result.info  게시글 보기 배열
     * @apiSuccess {Number} result.info.boardId 후기번호
     * @apiSuccess {Number} result.info.userId 작성자번호
     * @apiSuccess {String} result.info.userName 작성자명
     * @apiSuccess {String} result.info.title 게시글 제목
     * @apiSuccess {String} result.info.contents 게시글 내용
     * @apiSuccess {String} result.info.regDate 등록일시
     * @apiSuccess {Number} result.info.hit 조회수
     * @apiSuccess {Number} result.info.type 게시판 종류. 1 공지사항, 2 1:1문의, 3 faq
     * @apiSuccess {Object[]} result.info.fileArray 첨부파일 배열
     * @apiSuccess {Number} result.info.fileArray.id 파일번호
     * @apiSuccess {Number} result.info.fileArray.boardId 게시물번호
     * @apiSuccess {String} result.info.fileArray.fileName 첨부파일주소
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
    public function view_post()
    {
        $auth_arr = $this->load->get_vars();

        //필수키
        $data['token'] = $auth_arr['token'];
        $data['refreshToken'] = $auth_arr['refreshToken'];
        $data['users_id'] = $auth_arr['users_id'];

        $data['type'] = $this->post('type', true);
        $data['boardId'] = $this->post('boardId', true);

        //필수체크
        $this->_check_value($data);

        //api method별 하드코딩, 권한
        $data['menu_id'] = [200300]; //auth_code_catetory 서비스운영-앱관리-공지사항 번호, 비회원, 병원어드민도 권한 있음.

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

        //게시글 보기
        $result = $this->advertiserBoard_m->getBoardView($data);

        if($result)
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
                'status' => 'success',
                'code' => '200',
                'message' => '삭제되었거나 데이터가 없습니다.',
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