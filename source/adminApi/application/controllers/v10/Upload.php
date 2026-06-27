<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * 다중 업로드 처리
 *
 */
require APPPATH . '/libraries/REST_Controller.php';
class Upload extends REST_Controller
{
    public function __construct()
	{
        parent::__construct();
        
        $this->master = $this->load->database('master', true);
    }

    /**
     * @api {post} /upload/file 파일 업로드 처리
     * @apiHeader {string} x-api-key="qJqKDATY0D48ZH9hCNW4N6ygHBqtoVz4a4mu0P6A"
     * @apiHeader {string} x-api-token=""
     * @apiHeader {string} x-api-refreshtoken=""
     * @apiHeader {string} x-api-userid=""
     * @apiSampleRequest /api/v1.0/upload/file
     * @apiVersion 1.0.0
     * @apiName Upload File
     * @apiGroup Upload
     * @apiDescription 파일 업로드 처리
     * @apiParam {String} type 파일전송유형. 01 유저프로필 이미지, 02 파일첨부, 03 후기, 04 이벤트 배너, 05 기획전 이미지, 06 썸네일 생성 이미지
     * @apiParam {file} uploadfile 전송할 파일명
     *
     * @apiSuccess (200) {String="success", "error"} status="success" 상태
     * @apiSuccess (200) {Number} code 코드값
     * @apiSuccess (200) {String} message 파일 업로드 성공하였습니다.
     * @apiSuccess (200) {String} result 파일명
     *
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *       'status': 'success',
     *       'code': '200',
     *       'message': '파일 업로드 성공하였습니다.',
     *       'result': 'a.jpg'
     *     }
     *
     * @apiUse FileUploadFailError
     * @apiUse NotAuthTokenError
     */
	function file_post()
    {   
        $auth_arr = $this->load->get_vars();
        
        //필수키
        $data['token'] = $auth_arr['token'];
        $data['refreshToken'] = $auth_arr['refreshToken'];
        $data['users_id'] = $auth_arr['users_id'];
        $data['type'] = $this->post('type', true); //파일정송 유형. 01 유저프로필 02 파일첨부
        //var_dump($data); exit;

        //필수체크
        $this->_check_value($data);

        if(count($_FILES) == 0)
        {
            $this->response([
                'status' => 'error',
                'code' => '404',
                'message' => '파일이 없습니다.',
                'result' => null
            ], 200);
        }

        $this->load->library("EventUpload");

        $ori_file = $_FILES['uploadfile']['name']; //echo $ori_file; exit;

        $upload_basedir = $this->eventupload->get_upload_baseurl($data['type']);

        $config['upload_path'] = $upload_basedir; //var_dump( $config['upload_path']); exit;

    
        //디렉토리 생성
        if( !is_dir($config['upload_path']) )
        {
            @mkdir($config['upload_path'], 0777, true);
        }

        //썸네일 생성
        $config['encrypt_name']  = TRUE; //파일명이 한글일 경우 에러남. 암호화하여 우회
        $config['allowed_types'] = "*";

        if ($data['type'] == '06') 
        {
            $config['make_thumb'] = true;
        }
    
        $aws_result = $this->eventupload->aws_upload($config);

        $this->response($aws_result, 200);
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

    /**  ------------------------------------------ */

}