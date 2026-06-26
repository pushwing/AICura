<?php
defined('BASEPATH') OR exit('No direct script access allowed');

// This can be removed if you use __autoload() in config.php OR use Modular Extensions
/** @noinspection PhpIncludeInspection */
require APPPATH . '/libraries/REST_Controller.php';

/**
 * Inspection Controller
 * 검수관리 api
 *
 * @category        Controller
 * @author          martin.byun@goodoc.co.kr
 */
class Inspection extends REST_Controller {

    /**
     * inspection constructor.
     */
    function __construct()
    {
        parent::__construct();

        $this->load->model(['inspection_m', 'common_m', 'replicator_m']);
        $this->load->helper(['common']);
        $this->master = $this->load->database('master', true);
    }

    /**
     * @api {post} /inspection/list 검수 리스트 조회
     * @apiHeader {string} x-api-key="qJqKDATY0D48ZH9hCNW4N6ygHBqtoVz4a4mu0P6A"
     * @apiHeader {string} x-api-token=""
     * @apiHeader {string} x-api-refreshtoken=""
     * @apiHeader {string} x-api-userid=""
     * @apiSampleRequest /api/v1.0/inspection/list
     * @apiVersion 1.0.0
     * @apiName inspection list
     * @apiGroup Inspection
     * @apiDescription 검수 리스트 조회
     * @apiParam {Number} limit 페이징시 한 페이지에 노출할 리스트수
     * @apiParam {Number} page="1" 페이지번호
     * @apiParam {Number="1", "2"} type="2" 1 전체리스트, 2 금일
     * @apiParam {Number} [adStatus] 이벤트상태. 1 수정검토(O), 2 종료검토(O), 4 신규등록검토(X), 5 재등록(X) - (3 수정검토(X) 삭제)
     * @apiParam {Number} [agencyUserId] 영업담당자번호. 1,2,3 형태
     * @apiParam {Number} [searchType] 검색어 type. 1 병원명, 2 이벤트명, 3 이벤트 Id
     * @apiParam {String} [searchWord] 검색어. 병원명, 이벤트명, 이벤트 Id
     *
     * @apiSuccess (200) {String="success", "error"} status="success" 상태
     * @apiSuccess (200) {Number} code 코드값
     * @apiSuccess (200) {String} message 빈값
     * @apiSuccess (200) {Object[]} result 배열
     * @apiSuccess (200) {Object[]} result.list 검수리스트 배열
     * @apiSuccess (200) {Number} result.list.id 검수번호
     * @apiSuccess (200) {Number} result.list.status 단계 1 검수대기, 2 통과, 3 반려, 4 종료, 5 취소(병원어드민용)
     * @apiSuccess (200) {Number} result.list.adStatus 검수타입 . 1 수정검토(O), 2 종료검토(O), 3 수정검토(X), 4 신규등록검토(X), 5 재등록(X)
     * @apiSuccess (200) {String} result.list.reason 검수의견
     * @apiSuccess (200) {Number} result.list.rejectCode 반려코드
     * @apiSuccess (200) {String} result.list.regDate 검수등록일시
     * @apiSuccess (200) {String} result.list.inspectDate 검수일시
     * @apiSuccess (200) {Number} result.list.hospitalId 병원번호
     * @apiSuccess (200) {Number} result.list.historyId 광고히스토리번호
     * @apiSuccess (200) {Number} result.list.adsId 광고번호
     * @apiSuccess (200) {Number} result.list.userId 유저번호
     * @apiSuccess (200) {Number} result.list.agencyUserId 영업담당자번호
     * @apiSuccess (200) {String} result.list.agencyUserReason 영업담당자용 검수의견
     * @apiSuccess (200) {String} result.list.memo 메모
     * @apiSuccess (200) {String} result.list.adTitle 광고명
     * @apiSuccess (200) {String} result.list.hospitalName 병원명
     * @apiSuccess (200) {String} result.list.categoryName 카테고리. 대분류/중분류 형태
     * @apiSuccess (200) {Number} result.list.hospitalType 병원타입. 1 일반, 2 네트워크
     * @apiSuccess (200) {Number} result.list.networkCount 네트워크병원수. type=2일 경우 사용
     * @apiSuccess (200) {Number} result.list.inspectUserId 검수한 유저 Id
     * @apiSuccess (200) {Number} result.list.prevAdStatus 검수 넘어오기전 광고 상태
     * @apiSuccess (200) {Number} result.list.prevSubAdStatus 검수 넘어오기전 광고 서브 상태
     * @apiSuccess (200) {Number} result.list.adsMainMapId 광고 메인맵 ID
     * @apiSuccess (200) {Number} result.list.isAdmin   어드민 작성 유무. 1 어드민, 2 병원
     * @apiSuccess (200) {Number} result.totCount 전체승인 갯수
     * @apiSuccess (200) {Number} result.todayCount 금일승인 갯수
     *
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *       'status': 'success',
     *       'code': '200',
     *       'message': '',
     *       'result': {
     *          'list': {},
     *          'totCount' : 0,
     *          'todayCout' : 0
     *        }
     *     }
     *
     * @apiUse FailGetError
     * @apiUse NotAuthTokenError
     */
    public function list_post()
    {
        //이벤트상태 정의 1 수정검토(O), 2 종료검토(O), 3 수정검토(X), 4 신규등록검토(X), 5 재등록(X)
        // 1 라이브중인 이벤트 수정
        // 2 라이브중인 이벤트 종료
        // 3 비 라이브중인 이벤트의 수정 -> 라이브 됨
        // 4 신규등록검토(당연 비라이브)
        // 5 비라이브 재등록 : 종료되어 있던 이벤트를 라이브하기 위해.
        // 5번 삭제, 3번이 재등록으로 변경 18.10.30
        ini_set('memory_limit', '-1');
        $auth_arr = $this->load->get_vars();

        //필수키
        $data['token'] = $auth_arr['token'];
        $data['refreshToken'] = $auth_arr['refreshToken'];
        $data['users_id'] = $auth_arr['users_id'];
        $data['limit'] = $this->post('limit', true);
        $data['page'] = $this->post('page', true);
        $data['type'] = $this->post('type', true);

        //필수체크
        $this->_check_value($data);

        //검색어
        $data['adStatus'] = $this->post('adStatus', true);
        $data['agencyUserId'] = $this->post('agencyUserId', true);
        $data['searchType'] = $this->post('searchType', true);
        $data['searchWord'] = $this->post('searchWord', true);

        if($data['searchType'] and $data['searchWord'] == '')
        {
            $this->response([
                'status' => 'success',
                'code' => 606,
                'message' => '검색어는 필수입니다.',
                'result' => null
            ], 200);
        }

        //api method별 하드코딩
        $data['menu_id'] = [130000];

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

        $list = $this->inspection_m->getInspectiontList($data);

        if($list['totCount'] != 0)
        {   
            $this->response([
                'status' => 'success',
                'code' => '200',
                'message' => '',
                'result' => (object) $list
            ], 200);
        }
        else
        {
            $this->response([
                'status' => 'success',
                'code' => '200', //정보 get 실패
                'message' => '데이터가 없습니다.',
                'result' => (object) [ 'list'=>[], 'totCount'=>0, 'todayCount'=>0]
            ], 200);
        }
    }

    /**
     * @api {post} /inspection/getInfo 검수 상세조회
     * @apiHeader {string} x-api-key="qJqKDATY0D48ZH9hCNW4N6ygHBqtoVz4a4mu0P6A"
     * @apiHeader {string} x-api-token=""
     * @apiHeader {string} x-api-refreshtoken=""
     * @apiHeader {string} x-api-userid=""
     * @apiSampleRequest /api/v1.0/inspection/getInfo
     * @apiVersion 1.0.0
     * @apiName inspection getInfo
     * @apiGroup Inspection
     * @apiDescription 검수 상세조회
     * @apiParam {Number} inspectId 조회할 검수번호
     *
     * @apiSuccess {String="success", "error"} status="success" 상태
     * @apiSuccess {Number} code 코드값
     * @apiSuccess {String} message 빈값
     * @apiSuccess {Object[]} result 배열
     * @apiSuccess {Object[]} result.info 배열. 검수내용
     * @apiSuccess {String} result.info.url 바로전 이벤트 주소
     * @apiSuccess {String} result.info.contractTitle 계약명
     * @apiSuccess {Number} result.info.id 광고번호
     * @apiSuccess {String} result.info.isLive 라이브 여부
     * @apiSuccess {String} result.info.adTitle 광고명
     * @apiSuccess {Number} result.info.adStatus 광고상태. 1 검토, 2 진행, 3 종료, 4 어드민 작성중, 5 클라이언트 작성중
     * @apiSuccess {Number} result.info.category 카테고리
     * @apiSuccess {String} result.info.adStartDate 광고시작일
     * @apiSuccess {String} result.info.adEndDate 광고종료일
     * @apiSuccess {String} result.info.adDateExtend 이벤트기간연장 Y, N
     * @apiSuccess {Number} result.info.adType 광고유형 1cpa, 2 cpm, 3 프로모션, 4 cpc, 5 옵션
     * @apiSuccess {Number} result.info.exposure 노출영역. 1 이벤트존, 2 병원상세, 3 둘다
     * @apiSuccess {Number} result.info.costType 가격타입, 1 숫자, 2 텍스트
     * @apiSuccess {Number} result.info.generalCost 정상가
     * @apiSuccess {Number} result.info.discountCost 할인가
     * @apiSuccess {String} result.info.textCost 텍스트단가
     * @apiSuccess {Number} result.info.dbCost db단가
     * @apiSuccess {Number} result.info.whereImage 이미지노출. 1 이벤트모델, 2 의료진, 3 전후사진.  1,2,3 형태, like 검색으로 처리
     * @apiSuccess {Number} result.info.modelImageCount 모델이미지수
     * @apiSuccess {String} result.info.adDetailInfo 버튼 관련 정보
     * @apiSuccess {String} result.info.regDate 등록일시
     * @apiSuccess {String} result.info.modDate 수정일시
     * @apiSuccess {Number} result.info.adOrder 정렬순서
     * @apiSuccess {Number} result.info.dibsCount 찜 수
     * @apiSuccess {Number} result.info.dbCount 신청db수
     * @apiSuccess {Number} result.info.contractId 계약번호
     * @apiSuccess {Number} result.info.contractOrderId 수주계약번호
     * @apiSuccess {Number} result.info.hospitalId 병원번호
     * @apiSuccess {Number} result.info.hospitalType 병원타입
     * @apiSuccess {Number} result.info.agencyUserId 영업담당자번호
     * @apiSuccess {Number} result.info.callRequestId 신청db번호
     * @apiSuccess {String} result.info.deliberationCode 심의번호 [병원어드민]
     * @apiSuccess {String} result.info.customRanding 커스텀랜딩 (다중선택가능 1,2형태) 1 케어랩스, 2 병원사업자정보
     * @apiSuccess {String} result.info.custom1 커스텀랜딩 2인 경우 대표명
     * @apiSuccess {String} result.info.custom2 커스텀랜딩 2인 경우 사업자 등록번호
     * @apiSuccess {String} result.info.custom3 커스텀랜딩 2인 경우 연락처
     * @apiSuccess {String} result.info.cooperationId 제휴매체. 1,3,4 형태
     * @apiSuccess {String} result.info.keyword 키워드. 콤마로 구분
     * @apiSuccess {String} result.info.regionId 지역번호. 콤마로 구분
     * @apiSuccess {String} result.info.hospitalName 병원명
     * @apiSuccess {String} result.info.t1ImageName t1 이미지
     * @apiSuccess {String} result.info.t2ImageName t2 이미지
     * @apiSuccess {String} result.info.dImages  상세 이미지 
     * @apiSuccess {Number} result.info.historyId 히스토리번호(참고용)
     * @apiSuccess {Number} result.info.adsMainMapId 메인멥번호(참고용)
     * @apiSuccess {Number} result.info.inspectStatus 검수상태. 1 검수대기, 2 통과, 3 반려 4 종료, 5 취소
     * @apiSuccess {Number} result.info.inspectAdStatus 검수상태. 검수타입 . 1 수정검토(O), 2 종료검토(O), 3 수정검토(X), 4 신규등록검토(X), 5 재등록(X)
     * @apiSuccess {String} result.info.eventMemo 이벤트메모. 콤마로 구분
     * @apiSuccess {String} result.info.agencyMemo 영업메모. 콤마로 구분
     * @apiSuccess {Number} result.info.balancePrice 시점잔액
     * @apiSuccess {Number} result.info.boardCount 후기 갯수
     * @apiSuccess {Number} result.info.boardRateSum 후기 총평점
     * @apiSuccess {String} result.info.subAdStatus sub이벤트 상태. 1 수정검토, 2 종료검토, 3신규등록검토, 4 재등록 검토, 5 어드민작성중, 6 병원작성중
     * @apiSuccess {String} result.info.contractName 계약명
     * @apiSuccess {String} result.info.isViewBoard 후기 노출 여부 1/ 2
     * @apiSuccess {String} result.info.isDelete    광고 삭제 유무 Y/N    
     * @apiSuccess {Number} result.info.deleteuserId 광고 삭제 유저ID
     * @apiSuccess {String} result.info.delDate 광고 삭제일
     * @apiSuccess {String} result.info.image2Date 정방향 이미지 수정일
     * @apiSuccess {String} result.info.newEvent 신규등록여부. 1 신규, 0 신규아님
     * @apiSuccess {String} result.info.channel 노출대상 채널. 1 굿닥, 0 굿닥파트너스
     * @apiSuccess {Object[]} result.history 배열. 변경 히스토리
     * @apiSuccess {Number} result.history.adsId 광고번호
     * @apiSuccess {String} result.history.adTitle 광고명
     * @apiSuccess {Number} result.history.category 카테고리
     * @apiSuccess {String} result.history.adStartDate 광고시작일
     * @apiSuccess {String} result.history.adEndDate 광고종료일
     * @apiSuccess {String} result.history.adDateExtend 이벤트기간연장 Y, N
     * @apiSuccess {Number} result.history.adType 광고타입
     * @apiSuccess {Number} result.history.exposure 노출영역. 1 이벤트존, 2 병원상세, 3 둘다
     * @apiSuccess {Number} result.history.costType 가격타입, 1 숫자, 2 텍스트
     * @apiSuccess {Number} result.history.generalCost 정상가
     * @apiSuccess {Number} result.history.discountCost 할인가
     * @apiSuccess {String} result.history.textCost 텍스트단가
     * @apiSuccess {Number} result.history.dbCost db단가
     * @apiSuccess {Number} result.history.whereImage 이미지노출. 1 이벤트모델, 2 의료진, 3 전후사진.  1,2,3 형태, like 검색으로 처리
     * @apiSuccess {Number} result.history.modelImageCount 모델이미지수
     * @apiSuccess {Number} result.history.adDetailInfo 버튼형태
     * @apiSuccess {String} result.history.regDate 등록일
     * @apiSuccess {Number} result.history.hospitalId 병원번호
     * @apiSuccess {String} result.history.hospitalType 병원타입
     * @apiSuccess {Number} result.history.agencyUserId 영업담당자번호
     * @apiSuccess {Number} result.history.isViewBoard 후기노출여부
     * @apiSuccess {String} result.history.deliberationCode 심의번호
     * @apiSuccess {Number} result.history.customRanding 커스텀랜딩 (다중선택가능 1,2형태) 1 케어랩스, 2 병원사업자정보
     * @apiSuccess {String} result.history.custom1 커스텀랜딩 2인 경우 대표명
     * @apiSuccess {String} result.history.custom2 커스텀랜딩 2인 경우 사업자 등록번호
     * @apiSuccess {String} result.history.custom3 커스텀랜딩 2인 경우 연락처
     * @apiSuccess {String} result.history.region 지역
     * @apiSuccess {Number} result.history.cooperation 제휴메체
     * @apiSuccess {Number} result.history.userId 작성자번호
     * @apiSuccess {Number} result.history.contractOrderId 수주계약번호
     * @apiSuccess {String} result.history.keyword 키워드
     * @apiSuccess {String} result.history.t1ImageName 썸네일1 이미지
     * @apiSuccess {String} result.history.t2ImageName 썸네일2 이미지
     * @apiSuccess {String} result.history.dImages  상세 이미지 
     * @apiSuccess {String} result.history.contractName 계약명
     * @apiSuccess {Number} result.history.subHospitalId 네트워크병원 번호(네트워크병원인 경우). 콤마로 구분
     * @apiSuccess {Number} result.history.optionAdId 옵션이벤트 번호들
     * @apiSuccess {String} result.history.hospitalName 병원명
     * @apiSuccess {String} result.history.channel 노출대상 채널. 1 굿닥, 0 굿닥파트너스
     *
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *       'status': 'success',
     *       'code': '200',
     *       'message': '',
     *       'result': {
     *         'info' : {}
     *        }
     *     }
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
        $data['inspectId'] = $this->post('inspectId', true); //검수번호

        //필수체크
        $this->_check_value($data);

        //api method별 하드코딩
        $data['menu_id'] = [130000];

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

        $result = $this->inspection_m->getInspectInfo($data);

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
                'message' => '데이터가 없습니다.',
                'result' => null
            ], 200);
        }
    }

    /**
     * @api {post} /inspection/update 검수 액션(승인, 반려, 종료)
     * @apiHeader {string} x-api-key="qJqKDATY0D48ZH9hCNW4N6ygHBqtoVz4a4mu0P6A"
     * @apiHeader {string} x-api-token=""
     * @apiHeader {string} x-api-refreshtoken=""
     * @apiHeader {string} x-api-userid=""
     * @apiSampleRequest /api/v1.0/inspection/update
     * @apiVersion 1.0.0
     * @apiName inspection Update
     * @apiGroup Inspection
     * @apiDescription 검수 액션(승인, 반려, 종료) 바로종료, 승인시 메모항목에 표시
     * @apiParam {Number} inspectId 검수번호
     * @apiParam {String} type 검수종류. 1 승인, 2 반려, 3 종료, 4 바로승인, 5 바로종료
     * @apiParam {String} [reason] 반려사유
     * @apiParam {Number} [rejectCode] 반려코드
     * @apiParam {String} [agencyUserReason] 영업담당자 반려사유

     * @apiParam {Number} [dbCost] db단가
     * @apiParam {String} [category] 카테고리
     * @apiParam {Number} [contractId] 계약번호
     * @apiParam {String} [keyword] 검색키워드. 최대 5개, 다이어트,자임당,라섹 형태
     * @apiParam {String} [cooperation] 제휴매체 노출동의(1,2,3 형태). 1 쿠차, 2 리타켓팅매체, 3 페이스북, 4 네이버밴드, 5 피키캐스트, 6 SMS_KAKAO
     * @apiParam {String} [region] 노출지역(1,2,3 형태) 1 서울, 2 부산, 3 인천, 4 대구, 5 광주, 6 대전, 7 울산, 8 경기, 9 강원, 10 충북, 11 충남, 12 전북, 13 전남, 14 경북, 15 경남, 16 제주
     * @apiParam {Number} [exposure] 노출영역. 1 이벤트, 2 병원상세, 3 둘다
     * @apiParam {Number} [isViewBoard] 후기노출여부. 1 노출, 2 미노출
     * @apiParam {Number} isChangeImage2 정방향 이미지 변경 여부. 1 변경, 2 미변경
     *
     * @apiSuccess (200) {String="success", "error"} status="success" 상태
     * @apiSuccess (200) {Number} code 코드값
     * @apiSuccess (200) {String} message 처리되었습니다.
     * @apiSuccess (200) {Object} result null
     *
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *       'status': 'success',
     *       'code': '200',
     *       'message': '처리되었습니다.',
     *       'result': null
     *     }
     *
     * @apiUse FailUpdateError
     * @apiUse NotAuthTokenError
     */
    public function update_post()
    {
        //필수키
        $auth_arr = $this->load->get_vars();

        //필수키
        $data['token'] = $auth_arr['token'];
        $data['refreshToken'] = $auth_arr['refreshToken'];
        $data['users_id'] = $auth_arr['users_id'];
        $data['inspectId'] = $this->post('inspectId', true);
        $data['type'] = $this->post('type', true);


        //필수체크
        $this->_check_value($data);

        $data['reason'] = $this->post('reason', true);
        $data['rejectCode'] = $this->post('rejectCode', true);
        $data['agencyUserReason'] = $this->post('agencyUserReason', true);
        $data['isChangeImage2'] = $this->post('isChangeImage2', true);
        //$data['isChangeImage2'] = 2; //미변경으로 고정

        if($data['type'] == 1)
        {
            $data['dbCost'] = $this->post('dbCost', true);
            $data['category'] = $this->post('category', true);
            $data['contractId'] = $this->post('contractId', true);
            $data['keyword'] = $this->post('keyword', true);
            $data['cooperation'] = $this->post('cooperation', true);
            $data['region'] = $this->post('region', true);
            $data['exposure'] = $this->post('exposure', true);
            $data['isViewBoard'] = $this->post('isViewBoard', true);

            if($data['isChangeImage2'] == '')
            {
                $this->response([
                    'status' => 'error',
                    'code' => 605,
                    'message' => '승인일 경우 정방향 이미지 변경여부는 필수입니다.',
                    'result' => null
                ], 200);
            }
        }

        if($data['type'] == 2)
        {
            if($data['reason'] == '' and $data['agencyUserReason'] == '')
            {
                $this->response([
                    'status' => 'error',
                    'code' => 605,
                    'message' => '반려사유, 영업담당자 반려내용은 필수입니다.',
                    'result' => null
                ], 200);
            }
        }

        //api method별 하드코딩, 권한
        $data['menu_id'] = [130000];

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

        $result = $this->inspection_m->updateInspectInfo($data);

        if($result !== false)
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
                'code' => '605', //업데이트 실패
                'message' => '서버관리자에게 문의하세요.',
                'result' => null
            ], 200);
        }
    }

    /**
     * @api {post} /inspection/cancel 검수 취소
     * @apiHeader {string} x-api-key="qJqKDATY0D48ZH9hCNW4N6ygHBqtoVz4a4mu0P6A"
     * @apiHeader {string} x-api-token=""
     * @apiHeader {string} x-api-refreshtoken=""
     * @apiHeader {string} x-api-userid=""
     * @apiSampleRequest /api/v1.0/inspection/cancel
     * @apiVersion 1.0.0
     * @apiName inspection cancel
     * @apiGroup Inspection
     * @apiDescription 검수 취소. 병원어드민 전용
     * @apiParam {Number} inspectId 검수번호
     *
     * @apiSuccess (200) {String="success", "error"} status="success" 상태
     * @apiSuccess (200) {Number} code 코드값
     * @apiSuccess (200) {String} message 처리되었습니다.
     * @apiSuccess (200) {Object} result null
     *
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *       'status': 'success',
     *       'code': '200',
     *       'message': '처리되었습니다.',
     *       'result': null
     *     }
     *
     * @apiUse FailUpdateError
     * @apiUse NotAuthTokenError
     */
    public function cancel_post()
    {
        //필수키
        $auth_arr = $this->load->get_vars();
      
        //필수키
        $data['token'] = $auth_arr['token'];
        $data['refreshToken'] = $auth_arr['refreshToken'];
        $data['users_id'] = $auth_arr['users_id'];
        $data['inspectId'] = $this->post('inspectId', true);
        $data['type'] = 5;

        //필수체크
        $this->_check_value($data);

        $data['reason'] = $this->post('reason', true);
        $data['rejectCode'] = $this->post('rejectCode', true);
        $data['agencyUserReason'] = $this->post('agencyUserReason', true);

        //api method별 하드코딩, 권한
        $data['menu_id'] = [130000];

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
     
        $status = $this->inspection_m->getStatus($data);

        if($status === false)
        {
            $this->response([
                'status' => 'error',
                'code' => '605', //업데이트 실패
                'message' => '이미 검토가 완료되었습니다. 원하는 상태로 다시 검토/종료 요청하세요.',
                'result' => null
            ], 200);
        }

        $result = $this->inspection_m->cancleInspectInfo($data);

        if($result !== false)
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
                'code' => '605', //업데이트 실패
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
