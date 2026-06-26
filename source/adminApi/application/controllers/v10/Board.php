<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH . '/libraries/REST_Controller.php';

class Board extends REST_Controller
{
    function __construct()
    {
        parent::__construct();

        $this->load->helper('url');

        $this->load->model(array('board_m', 'common_m'));

        $this->master = $this->load->database('master', true);
    }

    function blindAction()
    {
        $post = $this->input->post(null, true);
        //var_dump($post); exit;

        $this->master->where('id', $post['blindId'])->update('board', ['isDelete'=>1]);
        $affeced = $this->master->affected_rows();

        if($affeced)
        {
            $iArr = ['type'=>6, 'boardId'=>$post['blindId'], 'userId'=>$post['users_id'], 'regDate'=>date("Y-m-d H:i:s")];
            $this->master->insert('board_estimation', $iArr);

            echo true;
        }
        else
        {
            echo false;
        }
    }

    /**
     * @api {post} /board/lists 후기 리스트
     * @apiHeader {string} x-api-key="qJqKDATY0D48ZH9hCNW4N6ygHBqtoVz4a4mu0P6A"
     * @apiHeader {string} x-api-token=""
     * @apiHeader {string} x-api-refreshtoken=""
     * @apiHeader {string} x-api-userid=""
     * @apiSampleRequest /api/v1.0/board/lists
     * @apiVersion 1.0.0
     * @apiName board list
     * @apiGroup Board
     * @apiDescription 후기 리스트
     * @apiParam {Number} page=1 페이징번호
     * @apiParam {Number} limit=10 페이지당 출력갯수
     * @apiParam {String} [searchWord] 검색어
     *
     * @apiSuccess {String="success", "error"} status="success" 상태
     * @apiSuccess {Number} code 코드값
     * @apiSuccess {String} message 빈값
     * @apiSuccess {Object[]} result 배열
     * @apiSuccess {Object[]} result.data 후기 배열
     * @apiSuccess {Number} result.data.boardId 후기번호
     * @apiSuccess {Number} result.data.type 후기유형. 1 이벤트, 2 병원
     * @apiSuccess {Number} result.data.targetId 타켓번호. type=1 이벤트번호, type=2 병원번호
     * @apiSuccess {Number} result.data.userId 작성자번호
     * @apiSuccess {String} result.data.userName 작성자명
     * @apiSuccess {String} result.data.contents 게시글 내용
     * @apiSuccess {String} result.data.regDate 등록일시
     * @apiSuccess {Number} result.totCount 총 게시글 수
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
    function lists_post()
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
        $result = $this->board_m->getBoardList($data);

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
                'result' => null
            ], 200);
        }
    }

    /**
     * @api {post} /board/view 후기 상세보기
     * @apiHeader {string} x-api-key="qJqKDATY0D48ZH9hCNW4N6ygHBqtoVz4a4mu0P6A"
     * @apiHeader {string} x-api-token=""
     * @apiHeader {string} x-api-refreshtoken=""
     * @apiHeader {string} x-api-userid=""
     * @apiSampleRequest /api/v1.0/board/view
     * @apiVersion 1.0.0
     * @apiName board view
     * @apiGroup Board
     * @apiDescription 후기 상세보기
     * @apiParam {Number} boardId 후기번호
     *
     * @apiSuccess {String="success", "error"} status="success" 상태
     * @apiSuccess {Number} code 코드값
     * @apiSuccess {String} message 빈값
     * @apiSuccess {Object[]} result 배열
     * @apiSuccess {Object[]} result.data 후기 배열
     * @apiSuccess {Number} result.data.boardId 후기번호
     * @apiSuccess {Number} result.data.type 후기유형. 1 이벤트, 2 병원
     * @apiSuccess {Number} result.data.targetId 타켓번호. type=1 이벤트번호, type=2 병원번호
     * @apiSuccess {Number} result.data.userId 작성자번호
     * @apiSuccess {String} result.data.userName 작성자명
     * @apiSuccess {String} result.data.contents 게시글 내용
     * @apiSuccess {Number} result.data.rateSum 별점 평균
     * @apiSuccess {Number} result.data.rate1 서비스 점수
     * @apiSuccess {Number} result.data.rate2 진료/시술/수설 점수
     * @apiSuccess {Number} result.data.rate3 시설 점수
     * @apiSuccess {Number} result.data.surveyType 설문타입
     * @apiSuccess {Number} result.data.survey1
     * @apiSuccess {Number} result.data.survey2
     * @apiSuccess {Number} result.data.survey3
     * @apiSuccess {Number} result.data.survey4
     * @apiSuccess {Number} result.data.survey5
     * @apiSuccess {Number} result.data.survey6
     * @apiSuccess {String} result.data.regDate 등록일시
     * @apiSuccess {Number} result.data.imgName 첨부파일. 콤마로 구분
     * @apiSuccess {String} result.data.surveyAll 유형별 설문요. 상담 : 만족 || 시술시기 : 일주일 이내 형태
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
    function view_post()
    {
        $auth_arr = $this->load->get_vars();

        //필수키
        $data['token'] = $auth_arr['token'];
        $data['refreshToken'] = $auth_arr['refreshToken'];
        $data['users_id'] = $auth_arr['users_id'];

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
        $result = $this->board_m->getBoardView($data);

        if($result['boardId'] != '')
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