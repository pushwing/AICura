<?php
defined('BASEPATH') OR exit('No direct script access allowed');

// This can be removed if you use __autoload() in config.php OR use Modular Extensions
/** @noinspection PhpIncludeInspection */
require APPPATH . '/libraries/REST_Controller.php';

/**
 * Common Controller
 * 공통 api
 *
 * @category        Controller
 * @author          martin.byun@goodoc.co.kr
 */
class Common extends REST_Controller {

    /**
     * Common constructor.
     */
    function __construct()
    {
        parent::__construct();

        $this->load->model('common_m');
        $this->master = $this->load->database('master', true);
    }

    /**
     * @api {post} /common/searchParameter 검색 파라미터 조회
     * @apiHeader {string} x-api-key="qJqKDATY0D48ZH9hCNW4N6ygHBqtoVz4a4mu0P6A"
     * @apiHeader {string} x-api-token=""
     * @apiHeader {string} x-api-refreshtoken=""
     * @apiHeader {string} x-api-userid=""
     * @apiSampleRequest /api/v1.0/common/searchParameter
     * @apiVersion 1.0.0
     * @apiName search parameter
     * @apiGroup Common
     * @apiDescription 검색 파라미터 조회 : 변경빈도가 높은 값만 처리. 예) 영업담당자, 관리담당자 등
     * @apiParam {String} type 메뉴정의. 01 계약, 02 카테고리
     *
     * @apiSuccess {String="success", "error"} status="success" 상태
     * @apiSuccess {Number} code 코드값
     * @apiSuccess {String} message 빈값
     * @apiSuccess {Object[]} result 배열. 검색용 파라미터
     *
     * @apiUse FailGetError
     * @apiUse NotAuthTokenError
     */
    public function searchParameter_post()
    {
        $auth_arr = $this->load->get_vars();
        //log_message('info', 'searchParameter_post auth_arr= '.json_encode($auth_arr));

        //필수키
        $data['token'] = $auth_arr['token'];
        $data['refreshToken'] = $auth_arr['refreshToken'];
        $data['users_id'] = $auth_arr['users_id'];
        $data['type'] = $this->post('type', true);

        //필수체크
        $this->_check_value($data);

        //api method별 하드코딩
        $data['menu_id'] = 'A'; //전체 용

        //토큰 유효성 및 권한 체크
        $check = $this->common_m->checkToken($data);
        //log_message('info', 'searchParameter_post check= '.json_encode($check));


        if($check['status'] == 'error')
        {
            $this->response([
                'status' => $check['status'],
                'code' => $check['code'],
                'message' => $check['message'],
                'result' => is_null( $check['result'] ) ? $check['result'] : (object)  $check['result']
            ], 200);
        }

        $check = false;

        switch ($data['type'])
        {
            case "01": //계약관리 리스트, 나중에 db 연결
                $returnArr['agencyUserId'] = ['전체'=>'A', '로키'=>10, '켈리'=>2, '레이'=>3, '사무엘'=>4, '락'=>5, '테디'=>6, '노아'=>11, '카이'=>12,'곤'=>7, '헨도'=>8, '탑'=>9];
                $returnArr['manageUserId'] = ['전체'=>'A', '켈리'=>2, '레이'=>3, '사무엘'=>4, '락'=>5, '테디'=>6, '곤'=>7, '헨도'=>8, '탑'=>9,'로키'=>10, '노아'=>11, '카이'=>12];
                $check = true;
                break;
            case "02": //카테고리
                $returnArr['category'] = $this->common_m->getCategory();
                $check = true;
                break;
            default: //1 계약관리
                $returnArr['agencyUserId'] = ['전체'=>'A', '로키'=>10, '켈리'=>2, '레이'=>3, '사무엘'=>4, '락'=>5, '테디'=>6, '노아'=>11, '카이'=>12,'곤'=>7, '헨도'=>8, '탑'=>9];
                $returnArr['manageUserId'] = ['전체'=>'A', '켈리'=>2, '레이'=>3, '사무엘'=>4, '락'=>5, '테디'=>6, '곤'=>7, '헨도'=>8, '탑'=>9,'로키'=>10, '노아'=>11, '카이'=>12];
                $check = true;
                break;
        }

        if($check !== false)
        {
            $this->response([
                'status' => 'success',
                'code' => '200',
                'message' => '',
                'result' => is_array($returnArr) ? (object) $returnArr : (object) [$returnArr]
            ], 200);
        }
        else
        {
            $this->response([
                'status' => 'error',
                'code' => '610', //정보 get 실패
                'message' => '서버관리자에게 문의하세요.',
                'result' => ''
            ], 200);
        }
    }

    /**
     * @api {post} /Common/getContractOrderMemo 수주계약 메모 가져오기
     * @apiHeader {string} x-api-key="qJqKDATY0D48ZH9hCNW4N6ygHBqtoVz4a4mu0P6A"
     * @apiHeader {string} x-api-token=""
     * @apiHeader {string} x-api-refreshtoken=""
     * @apiHeader {string} x-api-userid=""
     * @apiSampleRequest /api/v1.0/Common/getContractOrderMemo
     * @apiVersion 1.0.0
     * @apiName Common getMemo
     * @apiGroup Common
     * @apiDescription 수주계약 메모 가져오기
     * @apiParam {Number} contractOrderId 수주계약 번호
     * @apiParam {Number} [memoType] 메모유형. 0 전부, 1 영업, 2 세금계산서, 3 원장(운영자), 4 원장(고객). 운영자는 필수, 병원은 4로 고정됨
     *
     * @apiSuccess {String="success", "error"} status="success" 상태
     * @apiSuccess {Number} code 코드값
     * @apiSuccess {String} message
     * @apiSuccess {Object[]} result 배열
     * @apiSuccess {Number} result.id 메모번호
     * @apiSuccess {Number} result.memoType 메모타입
     * @apiSuccess {Number} result.targetId 수주계약번호
     * @apiSuccess {Number} result.userId 작성자번호
     * @apiSuccess {String} result.memo 메모유형 1 영업, 2 세금계산서, 3 원장(운영자), 4 원장(고객)
     * @apiSuccess {String} result.customerMemo 고객에게 보여지는 메모
     * @apiSuccess {String} result.regDate 등록일시
     *
     * @apiUse FailGetError
     * @apiUse NotAuthTokenError
     */
    function getContractOrderMemo_post()
    {
        $auth_arr = $this->load->get_vars();

        //필수키
        $data['token'] = $auth_arr['token'];
        $data['refreshToken'] = $auth_arr['refreshToken'];
        $data['users_id'] = $auth_arr['users_id'];

        //api method별 하드코딩
        $data['menu_id'] = 'A'; //전체 용

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

        $data['contractOrderId'] = $this->post('contractOrderId', true);
        $data['memoType'] = $this->post('memoType', true);

        //필수키 체크
        $this->_check_value($data);

        if(USERAUTHCODE ==2)
        {
            //병원은 메모타입 4로 고정
            $data['memoType'] = 4;
        }

        $result = $this->common_m->getContractOrderMemo($data);

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
     * @api {post} /Common/setContractOrderMemo 수주계약 메모 입력
     * @apiHeader {string} x-api-key="qJqKDATY0D48ZH9hCNW4N6ygHBqtoVz4a4mu0P6A"
     * @apiHeader {string} x-api-token=""
     * @apiHeader {string} x-api-refreshtoken=""
     * @apiHeader {string} x-api-userid=""
     * @apiSampleRequest /api/v1.0/Common/setContractOrderMemo
     * @apiVersion 1.0.0
     * @apiName Common setMemo
     * @apiGroup Common
     * @apiDescription 수주계약 메모 입력
     * @apiParam {Number} contractOrderId 수주계약 번호
     * @apiParam {Number} memoType 메모유형 1 영업, 2 세금계산서, 3 원장(운영자), 4 원장(고객)
     * @apiParam {String} memo 메모
     *
     * @apiSuccess {String="success", "error"} status="success" 상태
     * @apiSuccess {Number} code 코드값
     * @apiSuccess {String} message 메모가 작성되었습니다.
     * @apiSuccess {String} result 빈값
     *
     * @apiUse FailGetError
     * @apiUse NotAuthTokenError
     */
    function setContractOrderMemo_post()
    {
        $auth_arr = $this->load->get_vars();

        //필수키
        $data['token'] = $auth_arr['token'];
        $data['refreshToken'] = $auth_arr['refreshToken'];
        $data['userId'] = $data['users_id'] = $auth_arr['users_id'];

        $data['targetId'] = $data['contractOrderId'] = $this->post('contractOrderId', true);
        $data['memoType'] = $this->post('memoType', true);
        $data['memo'] = $this->post('memo', true);

        //필수체크
        $this->_check_value($data);

        //api method별 하드코딩
        $data['menu_id'] = 'A'; //전체 용

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

        $data['targetId2'] =''; //원장번호 없는 경우

        $result = $this->common_m->setContractOrderMemo($data);

        if($result !== false)
        {
            $this->response([
                'status' => 'success',
                'code' => '200',
                'message' => '메모가 작성되었습니다.',
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
     * @api {post} /common/getOrderAmount 총 수주액 조회
     * @apiHeader {string} x-api-key="qJqKDATY0D48ZH9hCNW4N6ygHBqtoVz4a4mu0P6A"
     * @apiHeader {string} x-api-token=""
     * @apiHeader {string} x-api-refreshtoken=""
     * @apiHeader {string} x-api-userid=""
     * @apiSampleRequest /api/v1.0/common/getOrderAmount
     * @apiVersion 1.0.0
     * @apiName common getOrderAmount
     * @apiGroup Common
     * @apiDescription 총 수주액 조회. 지난달, 이번달
     * @apiParam {Number} [month] 조회할 월. 2018-01 형태
     *
     * @apiSuccess {String="success", "error"} status="success" 상태
     * @apiSuccess {Number} code 코드값
     * @apiSuccess {String} message 빈값
     * @apiSuccess {Object[]} result 배열
     * @apiSuccess {Object[]} result.thisMonth 배열
     * @apiSuccess {Number} result.thisMonth.totSum 이번달 총 수주금액
     * @apiSuccess {Object[]} result.lastMonth 배열
     * @apiSuccess {Number} result.lastMonth.totSum 지난달 총 수주금액
     *
     * @apiUse FailGetError
     * @apiUse NotAuthTokenError
     */
    function getOrderAmount_post()
    {
        $auth_arr = $this->load->get_vars();

        //필수키
        $data['token'] = $auth_arr['token'];
        $data['refreshToken'] = $auth_arr['refreshToken'];
        $data['users_id'] = $auth_arr['users_id'];

        //필수체크
        $this->_check_value($data);

        $data['month'] = $this->post('month', true);

        if($data['month'] == '')
        {
            $data['month'] = date("Y-m");
        }

        //이전 달
        $thisDay = explode("-", $data['month']);
        $firstDay = mktime(0, 0, 0, $thisDay[1], 0, $thisDay[0]); //해당 달의 첫날
        $data['lastMonth'] = date("Y-m", strtotime("-1 month", $firstDay)); //한달전

        //api method별 하드코딩
        $data['menu_id'] = 'A'; //전체 용

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

        $result = $this->common_m->getOrderAmount($data);

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
     * @api {post} /common/getAgencyInfo 광고대행사 api 키 조회
     * @apiHeader {string} x-api-key="qJqKDATY0D48ZH9hCNW4N6ygHBqtoVz4a4mu0P6A"
     * @apiHeader {string} x-api-token=""
     * @apiHeader {string} x-api-refreshtoken=""
     * @apiHeader {string} x-api-userid=""
     * @apiSampleRequest /api/v1.0/common/getAgencyInfo
     * @apiVersion 1.0.0
     * @apiName common getAgencyInfo
     * @apiGroup Common
     * @apiDescription 광고대행사 api 키 조회
     * @apiParam {Number} userId 대행사 유저번호
     * @apiParam {String} name 대행사명
     *
     * @apiSuccess {String="success", "error"} status="success" 상태
     * @apiSuccess {Number} code 코드값
     * @apiSuccess {String} message 빈값
     * @apiSuccess {Object[]} result 배열
     * @apiSuccess {String} result.key 대행사 api key
     *
     * @apiUse FailGetError
     * @apiUse NotAuthTokenError
     */
    function getAgencyInfo_post()
    {
        $auth_arr = $this->load->get_vars();

        //필수키
        $data['token'] = $auth_arr['token'];
        $data['refreshToken'] = $auth_arr['refreshToken'];
        $data['users_id'] = $auth_arr['users_id'];

        $data['userId'] = $this->post('userId', true);
        $data['name'] = $this->post('name', true);

        //필수체크
        $this->_check_value($data);

        //api method별 하드코딩
        $data['menu_id'] = 'A'; //전체 용

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

        $result = $this->common_m->getAgencyInfo($data);

        if($result !== false)
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
                'status' => 'error',
                'code' => '610', //정보 get 실패
                'message' => '서버관리자에게 문의하세요.',
                'result' => null
            ], 200);
        }
    }

    /**
     * @api {post} /common/getCategoryList 카테고리 리스트
     * @apiHeader {string} x-api-key="qJqKDATY0D48ZH9hCNW4N6ygHBqtoVz4a4mu0P6A"
     * @apiHeader {string} x-api-token=""
     * @apiHeader {string} x-api-refreshtoken=""
     * @apiHeader {string} x-api-userid=""
     * @apiSampleRequest /api/v1.0/common/getCategoryList
     * @apiVersion 1.0.0
     * @apiName get Category List
     * @apiGroup Common
     * @apiDescription 카테고리 리스트
     *
     * @apiSuccess {String="success", "error"} status="success" 상태
     * @apiSuccess {Number} code 코드값
     * @apiSuccess {String} message 빈값
     * @apiSuccess {Object[]} result 배열.
     * @apiSuccess {Number} result.id 카테고리번호
     * @apiSuccess {String} result.title 카테고리명
     * @apiSuccess {Number} result.sort 우선순위
     * @apiSuccess {Number} result.isVisible 노출여부. 0 미노출, 1 노출
     * @apiSuccess {String} result.image 이미지
     *
     * @apiUse FailGetError
     * @apiUse NotAuthTokenError
     */
    public function getCategoryList_post()
    {
        $auth_arr = $this->load->get_vars();

        //필수키
        $data['token'] = $auth_arr['token'];
        $data['refreshToken'] = $auth_arr['refreshToken'];
        $data['users_id'] = $auth_arr['users_id'];

        //필수체크
        $this->_check_value($data);

        //api method별 하드코딩
        $data['menu_id'] = 'A'; //전체 용

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

        $return = $this->common_m->getCategoryList();

        if($return !== false)
        {
            $this->response([
                'status' => 'success',
                'code' => '200',
                'message' => '',
                'result' => is_array($return) ? (object) $return : (object) [$return]
            ], 200);
        }
        else
        {
            $this->response([
                'status' => 'error',
                'code' => '610', //정보 get 실패
                'message' => '서버관리자에게 문의하세요.',
                'result' => ''
            ], 200);
        }
    }

    /**
     * @api {post} /common/setCategory 카테고리 등록
     * @apiHeader {string} x-api-key="qJqKDATY0D48ZH9hCNW4N6ygHBqtoVz4a4mu0P6A"
     * @apiHeader {string} x-api-token=""
     * @apiHeader {string} x-api-refreshtoken=""
     * @apiHeader {string} x-api-userid=""
     * @apiSampleRequest /api/v1.0/common/setCategory
     * @apiVersion 1.0.0
     * @apiName set Category
     * @apiGroup Common
     * @apiDescription 카테고리 등록
     *
     * @apiParam {Number} isVisible 노출여부. 0 미노출, 1 노출
     * @apiParam {String} title
     * @apiParam {Number} parentId 0 부모카테고리, 나머지는 부모카테고리 번호
     * @apiParam {String} image
     * @apiParam {Number} sort
     *
     * @apiSuccess {Number} code 코드값
     * @apiSuccess {String} message 카테고리가 등록되었습니다.
     * @apiSuccess {String} result 빈값
     *
     * @apiUse FailGetError
     * @apiUse NotAuthTokenError
     */
    public function setCategory_post()
    {
        $auth_arr = $this->load->get_vars();

        //필수키
        $data['token'] = $auth_arr['token'];
        $data['refreshToken'] = $auth_arr['refreshToken'];
        $data['users_id'] = $auth_arr['users_id'];
        $data['is_visible'] = $this->post('isVisible', true);
        $data['title'] = $this->post('title', true);
        $data['parent_id'] = $this->post('parentId', true);
        $data['image'] = $this->post('image', true);
        $data['sort'] = $this->post('sort', true);

        //필수체크
        $this->_check_value($data);

        //api method별 하드코딩
        $data['menu_id'] = 'A'; //전체 용

        //토큰 유효성 및 권한 체크
        $check = $this->common_m->checkToken($data);

        if($check['status'] == 'error')
        {
            $this->response([
                'status' => $check['status'],
                'code' => $check['code'],
                'message' => $check['message'],
                'result' => null( $check['result'] ) ? $check['result'] : (object)  $check['result']
            ], 200);
        }

        $return = $this->common_m->setCategory($data);

        if($return !== false)
        {
            $this->response([
                'status' => 'success',
                'code' => '200',
                'message' => '카테고리가 등록되었습니다.',
                'result' => null
            ], 200);
        }
        else
        {
            $this->response([
                'status' => 'error',
                'code' => '610', //정보 get 실패
                'message' => '서버관리자에게 문의하세요.',
                'result' => ''
            ], 200);
        }
    }

    /**
     * @api {post} /common/updateCategory 카테고리 수정
     * @apiHeader {string} x-api-key="qJqKDATY0D48ZH9hCNW4N6ygHBqtoVz4a4mu0P6A"
     * @apiHeader {string} x-api-token=""
     * @apiHeader {string} x-api-refreshtoken=""
     * @apiHeader {string} x-api-userid=""
     * @apiSampleRequest /api/v1.0/common/updateCategory
     * @apiVersion 1.0.0
     * @apiName update Category
     * @apiGroup Common
     * @apiDescription 카테고리 수정(모든 값 필수 전송)
     *
     * @apiParam {Number} id 카테고리 번호
     * @apiParam {Number} isVisible 노출여부. 0 미노출, 1 노출
     * @apiParam {String} title
     * @apiParam {Number} parentId 0 부모카테고리, 나머지는 부모카테고리 번호
     * @apiParam {String} image
     * @apiParam {Number} sort
     *
     * @apiSuccess {Number} code 코드값
     * @apiSuccess {String} message 카테고리가 수정되었습니다.
     * @apiSuccess {String} result 빈값
     *
     * @apiUse FailGetError
     * @apiUse NotAuthTokenError
     */
    public function updateCategory_post()
    {
        $auth_arr = $this->load->get_vars();

        //필수키
        $data['token'] = $auth_arr['token'];
        $data['refreshToken'] = $auth_arr['refreshToken'];
        $data['users_id'] = $auth_arr['users_id'];
        $data['id'] = $this->post('id', true);
        $data['is_visible'] = $this->post('isVisible', true);
        $data['title'] = $this->post('title', true);
        $data['parent_id'] = $this->post('parentId', true);
        $data['image'] = $this->post('image', true);
        $data['sort'] = $this->post('sort', true);

        //필수체크
        $this->_check_value($data);

        //api method별 하드코딩
        $data['menu_id'] = 'A'; //전체 용

        //토큰 유효성 및 권한 체크
        $check = $this->common_m->checkToken($data);

        if($check['status'] == 'error')
        {
            $this->response([
                'status' => $check['status'],
                'code' => $check['code'],
                'message' => $check['message'],
                'result' => null( $check['result'] ) ? $check['result'] : (object)  $check['result']
            ], 200);
        }

        $return = $this->common_m->updateCategory($data);

        if($return !== false)
        {
            $this->response([
                'status' => 'success',
                'code' => '200',
                'message' => '카테고리가 수정되었습니다.',
                'result' => null
            ], 200);
        }
        else
        {
            $this->response([
                'status' => 'error',
                'code' => '610', //정보 get 실패
                'message' => '서버관리자에게 문의하세요.',
                'result' => ''
            ], 200);
        }
    }

    /**
     * @api {post} /common/deleteCategory 카테고리 삭제
     * @apiHeader {string} x-api-key="qJqKDATY0D48ZH9hCNW4N6ygHBqtoVz4a4mu0P6A"
     * @apiHeader {string} x-api-token=""
     * @apiHeader {string} x-api-refreshtoken=""
     * @apiHeader {string} x-api-userid=""
     * @apiSampleRequest /api/v1.0/common/deleteCategory
     * @apiVersion 1.0.0
     * @apiName delete Category
     * @apiGroup Common
     * @apiDescription 카테고리 삭제
     *
     * @apiParam {Number} id 카테고리 번호
     * @apiParam {Number} parentId 부모 카테고리 번호
     *
     * @apiSuccess {Number} code 코드값
     * @apiSuccess {String} message 카테고리가 삭제되었습니다.
     * @apiSuccess {String} result 빈값
     *
     * @apiUse FailGetError
     * @apiUse NotAuthTokenError
     */
    public function deleteCategory_post()
    {
        $auth_arr = $this->load->get_vars();

        //필수키
        $data['token'] = $auth_arr['token'];
        $data['refreshToken'] = $auth_arr['refreshToken'];
        $data['users_id'] = $auth_arr['users_id'];
        $data['id'] = $this->post('id', true);
        $data['parent_id'] = $this->post('parentId', true);

        //필수체크
        $this->_check_value($data);

        //api method별 하드코딩
        $data['menu_id'] = 'A'; //전체 용

        //토큰 유효성 및 권한 체크
        $check = $this->common_m->checkToken($data);

        if($check['status'] == 'error')
        {
            $this->response([
                'status' => $check['status'],
                'code' => $check['code'],
                'message' => $check['message'],
                'result' => null( $check['result'] ) ? $check['result'] : (object)  $check['result']
            ], 200);
        }

        //노출중인 하위 카테고리 존재 체크
        if($data['parent_id'] == 0)
        {
            $check = $this->common_m->checkCategory($data);

            if($check > 0)
            {
                $this->response([
                    'status' => 'error',
                    'code' => '610', //정보 get 실패
                    'message' => '노출중인 하위 카테고리가 존재합니다.',
                    'result' => ''
                ], 200);
            }
        }

        $return = $this->common_m->deleteCategory($data);

        if($return !== false)
        {
            $this->response([
                'status' => 'success',
                'code' => '200',
                'message' => '카테고리가 삭제되었습니다.',
                'result' => null
            ], 200);
        }
        else
        {
            $this->response([
                'status' => 'error',
                'code' => '610', //정보 get 실패
                'message' => '서버관리자에게 문의하세요.',
                'result' => ''
            ], 200);
        }
    }

    /**
     * @api {post} /common/getCategoryInfo 카테고리 정보 보기
     * @apiHeader {string} x-api-key="qJqKDATY0D48ZH9hCNW4N6ygHBqtoVz4a4mu0P6A"
     * @apiHeader {string} x-api-token=""
     * @apiHeader {string} x-api-refreshtoken=""
     * @apiHeader {string} x-api-userid=""
     * @apiSampleRequest /api/v1.0/common/getCategoryInfo
     * @apiVersion 1.0.0
     * @apiName get Category Info
     * @apiGroup Common
     * @apiDescription 카테고리 정보 보기
     *
     * @apiParam {Number} parentId 부모 카테고리 번호
     *
     * @apiSuccess {String="success", "error"} status="success" 상태
     * @apiSuccess {Number} code 코드값
     * @apiSuccess {String} message 빈값
     * @apiSuccess {Object[]} result 배열.
     * @apiSuccess {Object[]} result.parentInfo 부모카테고리 배열.
     * @apiSuccess {String} result.parentInfo.title 카테고리명
     * @apiSuccess {Number} result.parentInfo.isVisible 노출여부. 0 미노출, 1 노출
     * @apiSuccess {String} result.parentInfo.image 이미지
     * @apiSuccess {Object[]} result.childInfo 자식카테고리 배열.
     * @apiSuccess {Number} result.childInfo.id 카테고리번호
     * @apiSuccess {String} result.childInfo.title 카테고리명
     * @apiSuccess {Number} result.childInfo.sort 우선순위
     * @apiSuccess {Number} result.childInfo.isVisible 노출여부. 0 미노출, 1 노출
     * @apiSuccess {String} result.childInfo.image 이미지
     *
     * @apiUse FailGetError
     * @apiUse NotAuthTokenError
     */
    public function getCategoryInfo_post()
    {
        $auth_arr = $this->load->get_vars();

        //필수키
        $data['token'] = $auth_arr['token'];
        $data['refreshToken'] = $auth_arr['refreshToken'];
        $data['users_id'] = $auth_arr['users_id'];
        $data['id'] = $this->post('parentId', true);

        //필수체크
        $this->_check_value($data);

        //api method별 하드코딩
        $data['menu_id'] = 'A'; //전체 용

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

        $return = $this->common_m->getCategoryInfo($data);

        if($return !== false)
        {
            $this->response([
                'status' => 'success',
                'code' => '200',
                'message' => '',
                'result' => is_array($return) ? (object) $return : (object) [$return]
            ], 200);
        }
        else
        {
            $this->response([
                'status' => 'error',
                'code' => '610', //정보 get 실패
                'message' => '서버관리자에게 문의하세요.',
                'result' => ''
            ], 200);
        }
    }

    /**
     * @api {post} /common/getEvnetRecommendList 이벤트 추천 검색어 리스트
     * @apiHeader {string} x-api-key="qJqKDATY0D48ZH9hCNW4N6ygHBqtoVz4a4mu0P6A"
     * @apiHeader {string} x-api-token=""
     * @apiHeader {string} x-api-refreshtoken=""
     * @apiHeader {string} x-api-userid=""
     * @apiSampleRequest /api/v1.0/common/getEvnetRecommendList
     * @apiVersion 1.0.0
     * @apiName get Event Recommend List
     * @apiGroup Common
     * @apiDescription 이벤트 추천 검색어 리스트
     *
     * @apiSuccess {String="success", "error"} status="success" 상태
     * @apiSuccess {Number} code 코드값
     * @apiSuccess {String} message 빈값
     * @apiSuccess {Object[]} result 배열.
     * @apiSuccess {Number} result.id 추천검색어 번호
     * @apiSuccess {String} result.tag 추천검색어
     * @apiSuccess {Number} result.sort 우선순위
     *
     * @apiUse FailGetError
     * @apiUse NotAuthTokenError
     */
    public function getEvnetRecommendList_post()
    {
        $auth_arr = $this->load->get_vars();

        //필수키
        $data['token'] = $auth_arr['token'];
        $data['refreshToken'] = $auth_arr['refreshToken'];
        $data['users_id'] = $auth_arr['users_id'];

        //필수체크
        $this->_check_value($data);

        //api method별 하드코딩
        $data['menu_id'] = 'A'; //전체 용

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

        $return = $this->common_m->getEventRecommendList();

        if($return !== false)
        {
            $this->response([
                'status' => 'success',
                'code' => '200',
                'message' => '',
                'result' => is_array($return) ? (object) $return : (object) [$return]
            ], 200);
        }
        else
        {
            $this->response([
                'status' => 'error',
                'code' => '610', //정보 get 실패
                'message' => '서버관리자에게 문의하세요.',
                'result' => ''
            ], 200);
        }
    }

    /**
     * @api {post} /common/setEventRecommend 추천검색어 등록
     * @apiHeader {string} x-api-key="qJqKDATY0D48ZH9hCNW4N6ygHBqtoVz4a4mu0P6A"
     * @apiHeader {string} x-api-token=""
     * @apiHeader {string} x-api-refreshtoken=""
     * @apiHeader {string} x-api-userid=""
     * @apiSampleRequest /api/v1.0/common/setEventRecommend
     * @apiVersion 1.0.0
     * @apiName set EventRecommend
     * @apiGroup Common
     * @apiDescription 추천검색어 등록
     *
     * @apiParam {String} tag
     *
     * @apiSuccess {Number} code 코드값
     * @apiSuccess {String} message 추천검색어가 등록되었습니다.
     * @apiSuccess {String} result 빈값
     *
     * @apiUse FailGetError
     * @apiUse NotAuthTokenError
     */
    public function setEventRecommend_post()
    {
        $auth_arr = $this->load->get_vars();

        //필수키
        $data['token'] = $auth_arr['token'];
        $data['refreshToken'] = $auth_arr['refreshToken'];
        $data['users_id'] = $auth_arr['users_id'];
        $data['tag'] = $this->post('tag', true);

        //필수체크
        $this->_check_value($data);

        //api method별 하드코딩
        $data['menu_id'] = 'A'; //전체 용

        //토큰 유효성 및 권한 체크
        $check = $this->common_m->checkToken($data);

        if($check['status'] == 'error')
        {
            $this->response([
                'status' => $check['status'],
                'code' => $check['code'],
                'message' => $check['message'],
                'result' => null( $check['result'] ) ? $check['result'] : (object)  $check['result']
            ], 200);
        }

        $return = $this->common_m->setEventRecommend($data);

        if($return !== false)
        {
            $this->response([
                'status' => 'success',
                'code' => '200',
                'message' => '카테고리가 등록되었습니다.',
                'result' => null
            ], 200);
        }
        else
        {
            $this->response([
                'status' => 'error',
                'code' => '610', //정보 get 실패
                'message' => '서버관리자에게 문의하세요.',
                'result' => ''
            ], 200);
        }
    }

    /**
     * @api {post} /common/deleteEventRecommend 추천검색어 삭제
     * @apiHeader {string} x-api-key="qJqKDATY0D48ZH9hCNW4N6ygHBqtoVz4a4mu0P6A"
     * @apiHeader {string} x-api-token=""
     * @apiHeader {string} x-api-refreshtoken=""
     * @apiHeader {string} x-api-userid=""
     * @apiSampleRequest /api/v1.0/common/deleteEventRecommend
     * @apiVersion 1.0.0
     * @apiName delete EventRecommend
     * @apiGroup Common
     * @apiDescription 추천검색어 삭제
     *
     * @apiParam {Number} id 추천검색어 번호
     *
     * @apiSuccess {Number} code 코드값
     * @apiSuccess {String} message 추천검색어가 삭제되었습니다.
     * @apiSuccess {String} result 빈값
     *
     * @apiUse FailGetError
     * @apiUse NotAuthTokenError
     */
    public function deleteEventRecommend_post()
    {
        $auth_arr = $this->load->get_vars();

        //필수키
        $data['token'] = $auth_arr['token'];
        $data['refreshToken'] = $auth_arr['refreshToken'];
        $data['users_id'] = $auth_arr['users_id'];
        $data['id'] = $this->post('id', true);

        //필수체크
        $this->_check_value($data);

        //api method별 하드코딩
        $data['menu_id'] = 'A'; //전체 용

        //토큰 유효성 및 권한 체크
        $check = $this->common_m->checkToken($data);

        if($check['status'] == 'error')
        {
            $this->response([
                'status' => $check['status'],
                'code' => $check['code'],
                'message' => $check['message'],
                'result' => null( $check['result'] ) ? $check['result'] : (object)  $check['result']
            ], 200);
        }

        $return = $this->common_m->deleteEventRecommend($data);

        if($return !== false)
        {
            $this->response([
                'status' => 'success',
                'code' => '200',
                'message' => '추천검색어가 삭제되었습니다.',
                'result' => null
            ], 200);
        }
        else
        {
            $this->response([
                'status' => 'error',
                'code' => '610', //정보 get 실패
                'message' => '서버관리자에게 문의하세요.',
                'result' => ''
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
