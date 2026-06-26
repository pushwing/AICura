<?php
defined('BASEPATH') OR exit('No direct script access allowed');

// This can be removed if you use __autoload() in config.php OR use Modular Extensions
/** @noinspection PhpIncludeInspection */
require APPPATH . '/libraries/REST_Controller.php';

/**
 * Ads Controller
 * 광고 관련 api
 *
 * @category        Controller
 * @author          martin.byun@goodoc.co.kr
 */
class Ads extends REST_Controller {

    function __construct()
    {
        parent::__construct();
        $this->load->model(array('ads_m', 'common_m', 'adsTemp_m', 'replicator_m'));
        $this->master = $this->load->database('master', true);
    }

    /**
     * @api {post} /ads/register 광고 등록
     * @apiHeader {string} x-api-key="qJqKDATY0D48ZH9hCNW4N6ygHBqtoVz4a4mu0P6A"
     * @apiHeader {string} x-api-token=""
     * @apiHeader {string} x-api-refreshtoken=""
     * @apiHeader {Number} x-api-userid=""
     * @apiSampleRequest /api/v1.0/ads/register
     * @apiVersion 1.0.0
     * @apiName ads register
     * @apiGroup Ads
     * @apiDescription 광고 등록
     * @apiParam {String} adTitle 이벤트
     * @apiParam {Number} hospitalId 병원번호
     * @apiParam {Number} [hospitalType] 병원유형. 1 일반, 2 네트워크(모), 3 네트워크(자). 운영어드민은 디폴트임. 병원은 자기 상태에 따라 정의
     * @apiParam {String} [subHospitalId] 자병원번호. hospitalId가 모병원일 경우 선택한 자병원을 1,2,3 형태로 전송한다.
     * @apiParam {Number} adType 광고상품유형. 1 cpa, 2 cpm, 3 프로모션, 4 cpc, 5 옵션
     * @apiParam {String} adStartDate 광고시작일
     * @apiParam {String} adEndDate 광고종료일
     * @apiParam {String} adDateExtend 이벤트기간 연장여부. Y 연장, N 연장안함
     * @apiParam {Number} costType 가격타입. 1 숫자, 2 텍스트
     * @apiParam {Number} [generalCost] 정상가.
     * @apiParam {Number} [discountCost] 할인가. costType=1 인 경우 필수값
     * @apiParam {String} [textCost] 텍스트가격. costType=2 인 경우 필수값
     * @apiParam {Number} [dbCost] DB단가. 운영 필수값
     * @apiParam {Number} category 카테고리. 소 카테고리 번호
     * @apiParam {Number} exposure 노출영역. 1 이벤트, 2 병원상세, 3 둘다
     * @apiParam {Number} contractId 계약번호
     * @apiParam {Number} contractOrderId 수주계약번호
     * @apiParam {String} [optionAdId] 옵션이벤트번호. 1,2,3 형태
     * @apiParam {String} [region] 노출지역(1,2,3 형태) 1 서울, 2 부산, 3 인천, 4 대구, 5 광주, 6 대전, 7 울산, 8 경기, 9 강원, 10 충북, 11 충남, 12 전북, 13 전남, 14 경북, 15 경남, 16 제주. 병원은 1로 고정, 운영은 필수
     * @apiParam {String} [cooperation] 제휴매체 노출동의(1,2,3 형태). 1 쿠차, 2 리타켓팅매체, 3 페이스북, 4 네이버밴드, 5 피키캐스트, 6 SMS_KAKAO. 운영 필수값
     * @apiParam {String} [whereImage] 이미지노출. 1 이벤트모델, 2 의료진, 3 전후사진.  1,2,3 형태. 운영 필수값
     * @apiParam {String} [keyword] 검색키워드. 최대 5개, 다이어트,자임당,라섹 형태. 운영 필수값
     * @apiParam {Number} [modelImageCount] 모델모집 이미지갯수. 0, 1, 2 중 하나. 병원은 0 고정. 운영은 필수값
     * @apiParam {String} [buttonName] 신청버튼 문구
     * @apiParam {String} [buttonLink] 외부링크 url. adType cpc, cpm나 프로모션일 경우 사용
     * @apiParam {String} [buttonType] cpm용 버튼노출여부. 1 전화상담, 2 링크버튼, 3 카톡상담, 4 링크버튼+전화상담, 5 카톡상담+전화상담
     * @apiParam {String} [buttonPhone] cpm용 상담전화번호.
     * @apiParam {String} [buttonColor] 신청버튼 색상값
     * @apiParam {String} [buttonNameColor] 신청버튼 문구 색상
     * @apiParam {String} t1ImageName 썸네일1 이미지
     * @apiParam {String} t2ImageName 썸네일2 이미지
     * @apiParam {String} [dImages] 상세 이미지들 배열임. 타입은 나중에 수정 예정 
     * @apiParam {Number} [isViewBoard] 후기노출여부. 1 노출, 2 미노출. 병원은 1 고정, 운영은 필수값
     * @apiParam {String} [deliberationCode] 의료심의번호
     * @apiParam {String} [customRanding] 커스텀랜딩 (다중선택가능 1,2형태) 1 케어랩스, 2 병원사업자정보
     * @apiParam {String} [custom1] 커스텀랜딩 2인 경우 대표명
     * @apiParam {String} [custom2] 커스텀랜딩 2인 경우 사업자 등록번호
     * @apiParam {String} [custom3] 커스텀랜딩 2인 경우 연락처
     * @apiParam {String} [temporarySave] 임시저장. 1 임시저장아님, 2 임시저장 [병원어드민] 필수
     * @apiParam {Number} [tempId]  임시저장 데이터가 있을때 임시저장 Id 
     * @apiParam {Number} [channel=1] 노출대상 채널번호. 1 굿닥, 2 굿닥파트너스
     *
     * @apiSuccess {String="success", "error"} status="success" 상태
     * @apiSuccess {Number} code 코드값
     * @apiSuccess {String} message "이벤트가 등록되었습니다. 또는 임시저장 되었습니다."
     * @apiSuccess {Object[]} result 배열
     * @apiSuccess {number} result.0 광고id
     *
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *       'status': 'success',
     *       'code': '200',
     *       'message': '이벤트가 등록되었습니다. 또는 임시저장 되었습니다.',
     *       'result': {1}
     *     }
     *
     * @apiUse RegistFailError
     * @apiUse NotAuthTokenError
     */
    public function register_post()
    {
        //adType이 5인 옵션의 경우 db단가등 병원, 계약정보등을 받지 않도록 처리해야한다.
        $auth_arr = $this->load->get_vars();

        //필수키
        $data['token'] = $auth_arr['token'];
        $data['refreshToken'] = $auth_arr['refreshToken'];
        $data['users_id'] = $auth_arr['users_id'];

        //api method별 하드코딩
        $data['menu_id'] = [110000];

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
        
        if(USERAUTHCODE ==2)
        {
            //병원어드민용
            //필수키
            $necessaryParam = [
                'adTitle', 'hospitalId', 'adStartDate', 'adEndDate', 'adDateExtend'
                , 'costType', 'category', 'exposure', 'contractId', 'contractOrderId'
                , 't1ImageName', 't2ImageName'
            ];

            $commonParam = [
                'generalCost', 'discountCost', 'textCost', 'cooperation', 'whereImage'
                , 'keyword', 'dImages'
                , 'd6ImageName', 'deliberationCode', 'customRanding', 'channel'
            ];

        }
        else 
        {
            //필수키
            $necessaryParam = [
                'adTitle', 'hospitalId', 'hospitalType', 'adType', 'adStartDate', 'adEndDate'
                , 'adDateExtend', 'costType', 'dbCost', 'category', 'exposure', 'contractId'
                , 'contractOrderId', 't1ImageName', 't2ImageName', 'isViewBoard'
            ];

            $commonParam = [
                'subHospitalId', 'generalCost', 'discountCost', 'textCost', 'optionAdId'
                , 'optionAdId', 'region', 'cooperation', 'keyword', 'whereImage', 'modelImageCount'
                , 'dImages', 'buttonName'
                , 'buttonLink',  'buttonType', 'buttonPhone', 'buttonNameColor', 'buttonColor'
                , 'deliberationCode', 'customRanding', 'custom1', 'custom2', 'custom3', 'channel'
            ];
        }

        //필수 체크
        foreach($necessaryParam as $val) 
        {
            $data[$val] = $this->post($val, true);
        }
    
        $this->_check_value($data);

        //이벤트 제목에 <>를 씀. xss clean에서 걸려서 (LEFTANGLE) (RIGHTANGLE) 로 치환해서 보내기로 함함
        $data['adTitle'] = str_replace('(LEFTANGLE)', '<', $data['adTitle']);
        $data['adTitle'] = str_replace('(RIGHTANGLE)', '>', $data['adTitle']);

        //일반
        foreach($commonParam as $val)
        {
            $data[$val] = $this->post($val, true);
        } 
        unset($necessaryParam, $commonParam);

        //채널 초기화
        if($data['channel'] == '')
        {
            $data['channel'] = 1; //굿닥
        }

        if(USERAUTHCODE ==2)
        {
            $data['hospitalType'] = 1;
            $data['modelImageCount'] = 0;
            $data['region'] = '1'; //biz-1162. 이랬다 저랬다~~
            $data['isViewBoard'] = 1;
            $data['adType'] = 1; //cpa
   
            $data['customRanding'] = '';
   
            $data['custom1'] = '';
            $data['custom2'] = '';
            $data['custom3'] = '';   
            
            //조건별 필수 체크
            if($data['costType'] == 1)
            {
                if($data['discountCost'] == '')
                {
                    $this->response([
                        'status' => 'error',
                        'code' => '606',
                        'message' => '할인가는 필수값입니다.',
                        'result' => []
                    ], 200);
                }
            }
            else if($data['costType'] == 2)
            {
                if(!$data['textCost'])
                {
                    $this->response([
                        'status' => 'error',
                        'code' => '606',
                        'message' => '텍스트입력은 필수값입니다.',
                        'result' => []
                    ], 200);
                }
            }

            //병원어드민 db단가는 구간에 따라 계산된다.
            //2019.11.25 새단가표
            if($data['discountCost'] < 100000)
            {
                $data['dbCost'] = 17000;
            }
            else if($data['discountCost'] >= 100000 and $data['discountCost'] < 200000)
            {
                $data['dbCost'] = 23000;
            }
            else if($data['discountCost'] >= 200000 and $data['discountCost'] < 300000)
            {
                $data['dbCost'] = 25000;
            }
            else if($data['discountCost'] >= 300000 and $data['discountCost'] < 500000)
            {
                $data['dbCost'] = 30000;
            }
            else if($data['discountCost'] >= 500000 and $data['discountCost'] < 800000)
            {
                $data['dbCost'] = 35000;
            }
            else if($data['discountCost'] >= 800000 and $data['discountCost'] < 1000000)
            {
                $data['dbCost'] = 38000;
            }
            else if($data['discountCost'] >= 1000000 and $data['discountCost'] < 1500000)
            {
                $data['dbCost'] = 40000;
            }
            else if($data['discountCost'] >= 1500000 and $data['discountCost'] < 2000000)
            {
                $data['dbCost'] = 45000;
            }
            else if($data['discountCost'] >= 2000000 and $data['discountCost'] < 2500000)
            {
                $data['dbCost'] = 50000;
            }
            else if($data['discountCost'] >= 2500000 and $data['discountCost'] < 5000000)
            {
                $data['dbCost'] = 55000;
            }
            else if($data['discountCost'] >= 5000000)
            {
                $data['dbCost'] = 60000;
            }
        }
        else 
        {
            //조건별 필수 체크
            if($data['hospitalType'] == 2)
            {
                if(!$data['subHospitalId'])
                {
                    $this->response([
                        'status' => 'error',
                        'code' => '606',
                        'message' => '자병원번호는 필수값입니다.',
                        'result' => []
                    ], 200);
                }
            }

            if($data['costType'] == 1)
            {
                if($data['discountCost'] == '')
                {
                    $this->response([
                        'status' => 'error',
                        'code' => '606',
                        'message' => '할인가는 필수값입니다.',
                        'result' => []
                    ], 200);
                }
            }
            else if($data['costType'] == 2)
            {
                if(!$data['textCost'])
                {
                    $this->response([
                        'status' => 'error',
                        'code' => '606',
                        'message' => '텍스트입력은 필수값입니다.',
                        'result' => []
                    ], 200);
                }
            }

            if($data['adType'] == '5')
            {
                if(!$data['optionAdId'])
                {
                    $this->response([
                        'status' => 'error',
                        'code' => '606',
                        'message' => '연결 옵션은 필수값입니다.',
                        'result' => []
                    ], 200);
                }
            }

            if($data['adType'] == '2')
            {
                if(!$data['buttonType'])
                {
                    $this->response([
                        'status' => 'error',
                        'code' => '606',
                        'message' => '버튼노출여부는 필수값입니다.',
                        'result' => []
                    ], 200);
                }
                else
                {
                    if($data['buttonType'] == 1)
                    {
                        if(!$data['buttonPhone'])
                        {
                            $this->response([
                                'status' => 'error',
                                'code' => '606',
                                'message' => '상담 전화번호는 필수값입니다.',
                                'result' => []
                            ], 200);
                        }
                    }
                    else if($data['buttonType'] == 2 or $data['buttonType'] == 3)
                    {
                        if(!$data['buttonLink'])
                        {
                            $this->response([
                                'status' => 'error',
                                'code' => '606',
                                'message' => '링크/카톡 URL은 필수값입니다.',
                                'result' => []
                            ], 200);
                        }
                    }
                    else if($data['buttonType'] == 4 or $data['buttonType'] == 5)
                    {
                        if(!$data['buttonPhone'] and !$data['buttonLink'])
                        {
                            $this->response([
                                'status' => 'error',
                                'code' => '606',
                                'message' => '상담 전화번호, URL은 필수값입니다.',
                                'result' => []
                            ], 200);
                        }
                    }
                }
            }
            else if($data['adType'] == '3')
            {
                if(!$data['buttonLink'])
                {
                    $this->response([
                        'status' => 'error',
                        'code' => '606',
                        'message' => '외부링크 URL은 필수값입니다.',
                        'result' => []
                    ], 200);
                }
            }
            else if($data['adType'] == '4')
            {
                if(!$data['buttonLink'])
                {
                    $this->response([
                        'status' => 'error',
                        'code' => '606',
                        'message' => '이벤트 URL은 필수값입니다.',
                        'result' => []
                    ], 200);
                }
            }

            //커스텀랜딩 체크. 2가 있는 경우
            if(strstr(@$data['customRanding'], '2'))
            {
                if(!$data['custom1'])
                {
                    $this->response([
                        'status' => 'error',
                        'code' => '606',
                        'message' => '커스텀랜딩 대표명은 필수값입니다.',
                        'result' => []
                    ], 200);
                }

                if(!$data['custom2'])
                {
                    $this->response([
                        'status' => 'error',
                        'code' => '606',
                        'message' => '커스텀랜딩 사업자번호는은 필수값입니다.',
                        'result' => []
                    ], 200);
                }

                if(!$data['custom3'])
                {
                    $this->response([
                        'status' => 'error',
                        'code' => '606',
                        'message' => '커스텀랜딩 연락처는 필수값입니다.',
                        'result' => []
                    ], 200);
                }
            }
        }

        //병원번호에 해당하는 지역코드 리턴
        //$hospitalNameArr = $this->goodocapi->getHospitalInfosByIds([$data['HospitalId']]);
        //$data['cityId'] = $hospitalNameArr[$data['hospitalId']]['city_id'];
        $data['cityId'] = 1; //케빈 개발전까지는 1로 고정


        //빈값일 경우 1로 초기화
        $data['temporarySave']  = is_null($this->post('temporarySave', true)) ? 1 : $this->post('temporarySave', true);
        $data['tempId']         = is_null($this->post('tempId', true))  ? null : $this->post('tempId', true);

        //임시저장 일 때는 다른 메소드 사용
        if ( $data['temporarySave'] == 2 )
        {
            $return = is_null ($data['tempId']) ?  $this->adsTemp_m->setAdsTemp($data) : $this->adsTemp_m->updateAdsTemp($data);
            $msg = '임시저장 되었습니다.';
        }
        else 
        {
            $return = $this->ads_m->setAds($data);
            $msg = '이벤트가 등록되었습니다.';
        }

        if($return !== false)
        {
            $this->response([
                'status' => 'success',
                'code' => '200',
                'message' => $msg,
                'result' => $return
            ], 200);
        }
        else
        {
            $this->response([
                'status' => 'error',
                'code' => '614', //입력 실패
                'message' => '서버관리자에게 문의하세요.',
                'result' => []
            ], 200);
        }
    }

    /**
     * @api {post} /ads/update 광고 수정
     * @apiHeader {string} x-api-key="qJqKDATY0D48ZH9hCNW4N6ygHBqtoVz4a4mu0P6A"
     * @apiHeader {string} x-api-token=""
     * @apiHeader {string} x-api-refreshtoken=""
     * @apiHeader {string} x-api-userid=""
     * @apiSampleRequest /api/v1.0/ads/update
     * @apiVersion 1.0.0
     * @apiName ads update
     * @apiGroup Ads
     * @apiDescription 광고 수정
     * @apiParam {Number} adsId 이벤트번호 [병원어드민]
     * @apiParam {Number} [hospitalId] 병원번호
     * @apiParam {Number} [hospitalType]  병원타입 
     * @apiParam {Number} [contractId] 계약번호
     * @apiParam {Number} [contractOrderId] 수주계약번호
     * @apiParam {String} [adTitle] 이벤트제목 [병원어드민]
     * @apiParam {Number} [adType] 광고상품유형. 1 cpa, 2 cpm, 3 프로모션, 4 cpc, 5 옵션
     * @apiParam {String} [adStartDate] 광고시작일 [병원어드민]
     * @apiParam {String} [adEndDate] 광고종료일 [병원어드민]
     * @apiParam {String} [adDateExtend] 이벤트기간 연장여부. Y 연장, N 연장안함 [병원어드민]
     * @apiParam {Number} [costType] 가격타입. 1 숫자, 2 텍스트 [병원어드민]
     * @apiParam {Number} [generalCost] 정상가. [병원어드민]
     * @apiParam {Number} [discountCost] 할인가. costType=1 인 경우 필수값 [병원어드민]
     * @apiParam {String} [textCost] 텍스트가격. costType=2 인 경우 필수값 [병원어드민]
     * @apiParam {Number} [dbCost] DB단가 [병원어드민]
     * @apiParam {Number} [category] 카테고리. 소 카테고리 번호 [병원어드민]
     * @apiParam {Number} [exposure] 노출영역. 1 이벤트, 2 병원상세, 3 둘다 [병원어드민]
     * @apiParam {String} [optionAdId] 옵션이벤트번호. 1,2,3 형태
     * @apiParam {String} [region] 노출지역(1,2,3 형태) 1 서울, 2 부산, 3 인천, 4 대구, 5 광주, 6 대전, 7 울산, 8 경기, 9 강원, 10 충북, 11 충남, 12 전북, 13 전남, 14 경북, 15 경남, 16 제주
     * @apiParam {String} [cooperation] 제휴매체 노출동의(1,2,3 형태). 1 쿠차, 2 리타켓팅매체, 3 페이스북, 4 네이버밴드, 5 피키캐스트, 6 SMS_KAKAO [병원어드민]
     * @apiParam {String} [whereImage] 이미지노출. 1 이벤트모델, 2 의료진, 3 전후사진.  1,2,3 형태 [병원어드민]
     * @apiParam {String} [keyword] 검색키워드. 최대 5개, 다이어트,자임당,라섹 형태 [병원어드민]
     * @apiParam {Number} [modelImageCount] 모델모집 이미지갯수. 0, 1, 2 중 하나
     * @apiParam {String} [buttonName] 신청버튼 문구
     * @apiParam {String} [buttonLink] 외부링크 url. adType cpc, cpm나 프로모션일 경우 사용
     * @apiParam {String} [buttonType] cpm용 버튼노출여부. 1 전화상담, 2 링크버튼, 3 카톡상담, 4 링크버튼+전화상담, 5 카톡상담+전화상담
     * @apiParam {String} [buttonPhone] cpm용 상담전화번호.
     * @apiParam {String} [buttonColor] 신청버튼 색상값
     * @apiParam {String} [buttonNameColor] 신청버튼 문구 색상
     * @apiParam {String} [t1ImageName] 썸네일1 이미지 [병원어드민]
     * @apiParam {String} [t2ImageName] 썸네일2 이미지 [병원어드민]
     * @apiParam {String} [dImages] 상세 이미지들 배열임. 타입은 나중에 수정 예정      
     * @apiParam {Number} [isViewBoard] 후기노출여부. 1 노출, 2 미노출
     * @apiParam {String} [deliberationCode] 심의번호 [병원어드민]
     * @apiParam {String} [customRanding] 커스텀랜딩 (다중선택가능 1,2형태) 1 케어랩스, 2 병원사업자정보
     * @apiParam {String} [custom1] 커스텀랜딩 2인 경우 대표명
     * @apiParam {String} [custom2] 커스텀랜딩 2인 경우 사업자 등록번호
     * @apiParam {String} [custom3] 커스텀랜딩 2인 경우 연락처
     * @apiParam {String} [temporarySave] 임시저장. 1 임시저장아님, 2 임시저장 [병원어드민] 필수
     * @apiParam {Number} [tempId]  임시저장 데이터가 있을때 임시저장 Id
     * @apiParam {Number} [channel] 노출대상 채널번호. 1 굿닥, 2 굿닥파트너스
     *
     * @apiSuccess {String="success", "error"} status="success" 상태
     * @apiSuccess {Number} code 코드값
     * @apiSuccess {String} message "이벤트가 수정되었습니다. 또는 임시저장 되었습니다."
     * @apiSuccess {Object[]} result 배열
     * @apiSuccess {number} result.0 광고id
     *
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *       'status': 'success',
     *       'code': '200',
     *       'message': '이벤트가 수정되었습니다. 또는 임시저장 되었습니다.',
     *       'result': {1}
     *     }
     *
     * @apiUse RegistFailError
     * @apiUse NotAuthTokenError
     * @apiUse FailGetError
     */
    public function update_post()
    {
        $auth_arr = $this->load->get_vars();

        //필수키
        $data['token'] = $auth_arr['token'];
        $data['refreshToken'] = $auth_arr['refreshToken'];
        $data['users_id'] = $auth_arr['users_id'];
        $data['adsId'] = $this->post('adsId', true);

        //필수체크
        $this->_check_value($data);

        $data['hospitalId'] = $this->post('hospitalId', true);
        $data['hospitalType'] = $this->post('hospitalType', true);
        $data['contractId'] = $this->post('contractId', true);
        $data['contractOrderId'] = $this->post('contractOrderId', true);
        $data['adTitle'] = $this->post('adTitle', true);
        $data['adType'] = $this->post('adType', true);
        $data['adStartDate'] = $this->post('adStartDate', true);
        $data['adEndDate'] = $this->post('adEndDate', true);
        $data['adDateExtend'] = $this->post('adDateExtend', true);
        $data['costType'] = $this->post('costType', true);
        $data['generalCost'] = $this->post('generalCost', true);
        $data['discountCost'] = $this->post('discountCost', true);
        $data['textCost'] = $this->post('textCost', true);
        $data['dbCost'] = $this->post('dbCost', true);
        $data['category'] = $this->post('category', true);
        $data['exposure'] = $this->post('exposure', true);
        $data['region'] = $this->post('region', true);
        $data['cooperation'] = $this->post('cooperation', true);
        $data['whereImage'] = $this->post('whereImage', true);
        $data['keyword'] = $this->post('keyword', true);
        $data['modelImageCount'] = $this->post('modelImageCount', true);
        $data['t1ImageName'] = $this->post('t1ImageName', true);
        $data['t2ImageName'] = $this->post('t2ImageName', true);
      
        $data['dImages'] = $this->post('dImages', true);

        $data['isViewBoard'] = $this->post('isViewBoard', true);
        $data['deliberationCode'] = $this->post('deliberationCode', true);
        $data['customRanding'] = $this->post('customRanding', true);
        $data['custom1'] = $this->post('custom1', true);
        $data['custom2'] = $this->post('custom2', true);
        $data['custom3'] = $this->post('custom3', true);
        $data['optionAdId'] = $this->post('optionAdId', true);    
     
        $data['buttonName'] = $this->post('buttonName', true);
        $data['buttonLink'] = $this->post('buttonLink', true);
        $data['buttonType'] = $this->post('buttonType', true);
        $data['buttonPhone'] = $this->post('buttonPhone', true);
        $data['buttonNameColor'] = $this->post('buttonNameColor', true);
        $data['buttonColor'] = $this->post('buttonColor', true);

        $data['channel'] = $this->post('channel', true);

        /**
         * BIZ-1382
         */
        $data['subHospitalId'] = $this->post('subHospitalId', true);

        /**
         * biz-1537
         */
        if($data['adTitle'])
        {
            //이벤트 제목에 <>를 씀. xss clean에서 걸려서 (LEFTANGLE) (RIGHTANGLE) 로 치환해서 보내기로 함함
            $data['adTitle'] = str_replace('(LEFTANGLE)', '<', $data['adTitle']);
            $data['adTitle'] = str_replace('(RIGHTANGLE)', '>', $data['adTitle']);
        }


        if($data['costType'] == 1)
        {
            if($data['discountCost'] == '')
            {
                $this->response([
                    'status' => 'error',
                    'code' => '606',
                    'message' => '할인가는 필수값입니다.',
                    'result' => null
                ], 200);
            }
        }
        else if($data['costType'] == 2)
        {
            if(!$data['textCost'])
            {
                $this->response([
                    'status' => 'error',
                    'code' => '606',
                    'message' => '텍스트입력은 필수값입니다.',
                    'result' => null
                ], 200);
            }
        }

        if($data['adType'] == '5')
        {
            if(!$data['optionAdId'])
            {
                $this->response([
                    'status' => 'error',
                    'code' => '606',
                    'message' => '연결 옵션은 필수값입니다.',
                    'result' => null
                ], 200);
            }
        }

        if($data['adType'] == '2')
        {
            if(!$data['buttonType'])
            {
                $this->response([
                    'status' => 'error',
                    'code' => '606',
                    'message' => '버튼노출여부는 필수값입니다.',
                    'result' => null
                ], 200);
            }
            else
            {
                if($data['buttonType'] == 1)
                {
                    if(!$data['buttonPhone'])
                    {
                        $this->response([
                            'status' => 'error',
                            'code' => '606',
                            'message' => '상담 전화번호는 필수값입니다.',
                            'result' => null
                        ], 200);
                    }
                }
                else if($data['buttonType'] == 2 or $data['buttonType'] == 3)
                {
                    if(!$data['buttonLink'])
                    {
                        $this->response([
                            'status' => 'error',
                            'code' => '606',
                            'message' => '링크/카톡 URL은 필수값입니다.',
                            'result' => null
                        ], 200);
                    }
                }
                else if($data['buttonType'] == 4 or $data['buttonType'] == 5)
                {
                    if(!$data['buttonPhone'] and !$data['buttonLink'])
                    {
                        $this->response([
                            'status' => 'error',
                            'code' => '606',
                            'message' => '상담 전화번호, URL은 필수값입니다.',
                            'result' => null
                        ], 200);
                    }
                }
            }
        }
        else if($data['adType'] == '3')
        {
            if(!$data['buttonLink'])
            {
                $this->response([
                    'status' => 'error',
                    'code' => '606',
                    'message' => '외부링크 URL은 필수값입니다.',
                    'result' => null
                ], 200);
            }
        }
        else if($data['adType'] == '4')
        {
            if(!$data['buttonLink'])
            {
                $this->response([
                    'status' => 'error',
                    'code' => '606',
                    'message' => '이벤트 URL은 필수값입니다.',
                    'result' => null
                ], 200);
            }
        }

        //커스텀랜딩 체크. 2가 있는 경우
        if(strstr(@$data['customRanding'], '2'))
        {
            if(!$data['custom1'])
            {
                $this->response([
                    'status' => 'error',
                    'code' => '606',
                    'message' => '커스텀랜딩 대표명은 필수값입니다.',
                    'result' => null
                ], 200);
            }

            if(!$data['custom2'])
            {
                $this->response([
                    'status' => 'error',
                    'code' => '606',
                    'message' => '커스텀랜딩 사업자번호는은 필수값입니다.',
                    'result' => null
                ], 200);
            }

            if(!$data['custom3'])
            {
                $this->response([
                    'status' => 'error',
                    'code' => '606',
                    'message' => '커스텀랜딩 연락처는 필수값입니다.',
                    'result' => null
                ], 200);
            }
        }

        //api method별 하드코딩
        $data['menu_id'] = [110000];

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
        
        //빈값일 경우 1로 초기화
        $data['temporarySave']  = is_null($this->post('temporarySave', true)) ? 1 : $this->post('temporarySave', true);
        $data['tempId']         = is_null($this->post('tempId', true))  ? null : $this->post('tempId', true);
    
        if (USERAUTHCODE == 2) 
        {
            $check_status = $this->ads_m->getAdsStatus((int) $data['adsId']);

            if ($check_status === 1 ||  $check_status === 0) 
            {
                $this->response([
                    'status' => 'success',
                    'code' => '610',
                    'message' => '서버관리자에게 문의하세요.',
                    'result' => null
                ], 200);    
            }

            //190108 병원어드민 등록시 서울만
            $data['region'] = '1';
        }

        //Null 삭제
        foreach($data as $key=>$val)
        {   
            if( is_null($val))
            {
                unset($data[$key]);    
            }    
        }

        $imgArr = [
            'd2ImageName' , 'd3ImageName', 'd4ImageName', 'd5ImageName', 'd6ImageName'
        ];
        
        foreach($imgArr as $key=>$val)
        {
            if (!isset($data[$val]))
            {
                $data[$val] = null;
            }    
        }

        //임시저장 일 때는 다른 메소드 사용
        if ( $data['temporarySave'] == 2 )
        {
            $check_temp = $this->adsTemp_m->getAdsTempAdsId($data['adsId']);

            if ($check_temp === true) 
            {
                $return = $this->adsTemp_m->updateAdsTemp($data);
            }
            else 
            {
                $return = !isset($data['tempId']) || is_null($data['tempId']) ?  $this->adsTemp_m->setAdsTemp($data) : $this->adsTemp_m->updateAdsTemp($data);
            } 
            $msg = '임시저장 되었습니다.';
        }
        else 
        {
            $tempArr = $data;
            $tempArr['isDelete'] = 1;
            $tempArr['deleteUserId'] = $data['users_id'];
            $this->adsTemp_m->updateAdsTemp($data);
        
            //빈 값으로 보내면 삭제처리
            $return = $this->ads_m->updateAds($data);
            $msg = '이벤트가 수정 되었습니다.';
        }
        
        if($return !== false)
        {
            $this->response([
                'status' => 'success',
                'code' => '200',
                'message' => $msg,
                'result' => is_array($return) ? (object) [$return] : (object) $return
            ], 200);
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
     * @api {post} /ads/view 광고 내용 조회
     * @apiHeader {string} x-api-key="qJqKDATY0D48ZH9hCNW4N6ygHBqtoVz4a4mu0P6A"
     * @apiHeader {string} x-api-token=""
     * @apiHeader {string} x-api-refreshtoken=""
     * @apiHeader {Number} x-api-userid=""
     * @apiSampleRequest /api/v1.0/ads/view
     * @apiVersion 1.0.0
     * @apiName ads view
     * @apiGroup Ads
     * @apiDescription 광고 내용 조회
     * @apiParam {Number} adsId 광고번호
     *
     * @apiSuccess {String="success", "error"} status="success" 상태
     * @apiSuccess {Number} code 코드값
     * @apiSuccess {String} message 빈값
     * @apiSuccess {Object[]} result 배열
     * @apiSuccess {Object[]} result.info  광고 결과값 배열
     * @apiSuccess {String} result.info.adTitle 광고명
     * @apiSuccess {Number} result.info.adStatus  이벤트 상태. 1 검토, 2 진행, 3 종료, 4 작성중, 5 반려
     * @apiSuccess {Number} result.info.adSubStatus sub이벤트 상태. 1 수정검토, 2 종료검토, 3신규등록검토, 4 재등록 검토, 5 어드민작성중, 6 병원작성중
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
     * @apiSuccess {Number} result.info.whereImage 이미지노출. 1 이s벤트모델, 2 의료진, 3 전후사진.  1,2,3 형태, like 검색으로 처리
     * @apiSuccess {Number} result.info.modelImageCount 모델이미지수s
     * @apiSuccess {String} result.info.adDetailInfo 버튼 관련 정보
     * @apiSuccess {String} result.info.regDate 등록일시
     * @apiSuccess {Number} result.info.contractId 계약번호
     * @apiSuccess {Number} result.info.contractOrderId 수주계약번호
     * @apiSuccess {Number} result.info.hospitalId 병원번호
     * @apiSuccess {Number} result.info.hospitalType 병원타입
     * @apiSuccess {Number} result.info.agencyUserId 영업담당자번호
     * @apiSuccess {String} result.info.deliberationCode 심의번호 [병원어드민]
     * @apiSuccess {String} result.info.customRanding 커스텀랜딩 (다중선택가능 1,2형태) 1 케어랩스, 2 병원사업자정보
     * @apiSuccess {String} result.info.custom1 커스텀랜딩 2인 경우 대표명
     * @apiSuccess {String} result.info.custom2 커스텀랜딩 2인 경우 사업자 등록번호
     * @apiSuccess {String} result.info.custom3 커스텀랜딩 2인 경우 연락처
     * @apiSuccess {String} result.info.hospitalName 병원명
     * @apiSuccess {String} result.info.cooperation 제휴매체. 1,3,4 형태
     * @apiSuccess {String} result.info.keyword 키워드. 콤마로 구분
     * @apiSuccess {String} result.info.subHospitalId 네트워크병원 번호(네트워크병원인 경우). 콤마로 구분
     * @apiSuccess {String} result.info.buttonName 버튼명
     * @apiSuccess {String} result.info.buttonLink 버튼링크
     * @apiSuccess {String} result.info.buttonType 버튼형식
     * @apiSuccess {String} result.info.buttonPhone 버튼 상담 전화번호
     * @apiSuccess {String} result.info.buttonColor 버튼색상
     * @apiSuccess {String} result.info.buttonNameColor 버튼명 색상
     * @apiSuccess {String} result.info.t1ImageName 썸네일1 이미지 (필수)
     * @apiSuccess {String} result.info.t2ImageName 썸네일2 이미지 (필수)
     * @apiSuccess {String} result.info.dImages 본문 썸네일들 (필수)
     * @apiSuccess {Number} result.info.tempId 임시저장ID 없을떄는 NULL
     * @apiSuccess {String} result.info.boardCount 후기갯수
     * @apiSuccess {String} result.info.contractName 계역명
     * @apiSuccess {Number} result.info.isViewBoard 후기노출여부. 1 노출, 2 미노출
     * @apiSuccess {String} result.info.optionAdId 옵션이벤트 번호들
     * @apiSuccess {Number} result.info.adsId 광고 번호
     * @apiSuccess {Number} result.info.userId 광고 등록 유저 ID
     * @apiSuccess {String} result.info.region 광고 노출 지역
     * @apiSuccess {String} result.info.rateSum 총평점
     * @apiSuccess {String} result.info.channel 노출대상 채널. 1 굿닥, 2 굿닥파트너스
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
        $data['adsId'] = $this->post('adsId', true);

        //필수체크
        $this->_check_value($data);

        //api method별 하드코딩
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

        $data['hospitalId'] = $this->common_m->getHospitalId($data);
        
        $result = $this->ads_m->getAds($data);

        if($result != false)
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
                'code' => '404',
                'message' => '존재하지 않는 이벤트이거나 데이터가 없습니다.',
                'result' => (object) ['info' => []]
            ], 200);
        }
    }

    /**
     * @api {post} /ads/eventList 광고 리스트 조회
     * @apiHeader {string} x-api-key="qJqKDATY0D48ZH9hCNW4N6ygHBqtoVz4a4mu0P6A"
     * @apiHeader {string} x-api-token=""
     * @apiHeader {string} x-api-refreshtoken=""
     * @apiHeader {string} x-api-userid=""
     * @apiSampleRequest /api/v1.0/ads/eventList
     * @apiVersion 1.0.0
     * @apiName ads list
     * @apiGroup Ads
     * @apiDescription 광고 리스트 조회, status는 isLive, adStatus, subAdStatus 항목의 조합값이다.
     * @apiParam {Number} limit 페이징시 한 페이지에 노출할 리스트수
     * @apiParam {Number} page="1" 페이지번호
     * @apiParam {String} [channel]  노출대상 채널. a 전체, 1 굿닥, 2 굿닥파트너스
     * @apiParam {String} [status] (운영) 전체보기/어드민 작성중/진행/검토/종료, (병원) 전체보기/작성중/검토/반려/진행/종료
     * @apiParam {String} [adStatus] 광고상태(1,2,3 형태) 셀렉트  1 O 진행, 2 O 어드민작성중, 3 O 병원작성중, 4 O 수정검토, 5 O 종료검토, 6 O 반려, 7 X 종료, 8 X 어드민작성중, 9 X 병원작성중, 10 X 반려, 11 X 신규등록검토, 12 X 재등록검토, 13 X 반려 어드민작성중, 14 O 반려 어드민작성중
     * @apiParam {String} [searchCategory] 카테고리 [병원어드민]. 1,2,3 카테고리 번호 형태
     * @apiParam {String} [searchAdType] 광고유형 [병원어드민]. 광고유형. a 전체, 1 cpa, 2 cpm, 3 프로모션, 4 cpc
     * @apiParam {String} [searchRegion] 지역. a 전체, 1 서울, 2 부산, 3 인천, 4 대구, 5 광주, 6 대전, 7 울산, 8 경기, 9 강원, 10 충북, 11 충남, 12 전북, 13 전남, 14 경북, 15 경남, 16 제주0
     * @apiParam {Number} [searchExposure] 노출영역 1 이벤트, 2 병원상세, 3 둘다
     * @apiParam {String} [searchNetwork] 병원종류. a 전체, 1 일반, 2 네트워크(모), 3 네트워크(자)
     * @apiParam {String} [searchAgencyUserId] 영업담당자 번호
     * @apiParam {Number} [searchDbCost] DB단가
     * @apiParam {String} [searchDbCostOperator] DB단가 연산자. 1 =, 2 >=, 3 >, 4 <, 5 <=
     * @apiParam {String} [searchCooperation] 제휴매체(1,2,3 형태). 1 쿠차, 2 리타켓팅매체, 3 페이스북, 4 네이버밴드, 5 피키캐스트, 6 SMS_KAKAO
     * @apiParam {String} [searchWhereImage] 이미지노출. 1 이벤트모델, 2 의료진, 3 전후사진.  1,2,3 형태
     * @apiParam {String} [searchType] 검색종류. 1 병원명, 2 이벤트명, 3 이벤트 Id. 4 메모
     * @apiParam {String} [searchWord] 검색어 [병원어드민]. (searchType=3일 경우 콤마로 구분하여 검색 가능)
     *
     * @apiSuccess {String="success", "error"} status="success" 상태
     * @apiSuccess {Number} code 코드값
     * @apiSuccess {String} message 빈값
     * @apiSuccess {Object[]} result 배열
     * @apiSuccess {Object[]} result.list 광고리스트 배열
     * @apiSuccess {Number} result.list.id 광고번호
     * @apiSuccess {String} result.list.isLive 라이브 여부
     * @apiSuccess {Number} result.list.adStatus  이벤트 상태. 1 검토, 2 진행, 3 종료, 4 작성중, 5 반려
     * @apiSuccess {Number} result.list.channel  노출대상 채널. 1 굿닥, 2 굿닥파트너스
     * @apiSuccess {Number} result.list.adSubStatus sub이벤트 상태. 1 수정검토, 2 종료검토, 3신규등록검토, 4 재등록 검토, 5 어드민작성중, 6 병원작성중
     * @apiSuccess {String} result.list.hospitalName 병원명
     * @apiSuccess {Number} result.list.agencyUserId 영업담당자번호
     * @apiSuccess {String} result.list.adTitle 광고명
     * @apiSuccess {Number} result.list.category 카테고리
     * @apiSuccess {String} result.list.adStartDate 광고시작일
     * @apiSuccess {String} result.list.adEndDate 광고종료일
     * @apiSuccess {String} result.list.regDate 등록일시
     * @apiSuccess {String} result.list.modDate 수정일시
     * @apiSuccess {Number} result.list.hospitalType 병원타입
     * @apiSuccess {Number} result.list.adType 광고유형 1cpa, 2 cpm, 3 프로모션, 4 cpc, 5 옵션
     * @apiSuccess {Number} result.list.dbCost db단가
     * @apiSuccess {String} result.list.contractName 계약명
     * @apiSuccess {String} result.list.contractId 계약ID 
     * @apiSuccess {String} result.list.contractOrderId 계약ID  
     * @apiSuccess {Number} result.list.inspectId 수주id
     * @apiSuccess {String} result.list.inspectReason 반려 사유
     * @apiSuccess {String} result.list.t1ImageName 썸네일1 이미지
     * @apiSuccess {String} result.list.t2ImageName 썸네일2 이미지
     * @apiSuccess {Number} result.list.subAdStatus sub이벤트 상태. 1 수정검토, 2 종료검토, 3신규등록검토, 4 재등록 검토, 5 어드민작성중, 6 병원작성중
     * @apiSuccess {Number} result.list.hospitalId  병원ID
     * @apiSuccess {String} result.list.adDateExtend 이벤트기간연장 Y, N
     * @apiSuccess {Number} result.totCnt 전체 리스트 수
     * 
     * 
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *       'status': 'success',
     *       'code': '200',
     *       'message': '',
     *       'result': {
     *          'list' : {},
     *          'totCnt' : 0
     *        } 
     *     }
     *
     * @apiUse FailGetError
     * @apiUse NotAuthTokenError
     */
    public function eventList_post()
    {
        //[adStatus] 광고상태 a 전체, 1 검토, 2 진행, 3 종료, 4 작성중
        //[subAdStatus] sub이벤트 상태. 1 수정검토, 2 종료검토, 3신규등록검토, 4 재등록 검토, 5 어드민작성중, 6 병원작성중
        // 검토에 대한 서브상태 1-4, 진행 및 종료는 서브상태 없음. 작성중 서브상태 5,6
        ini_set('memory_limit', '-1');
        //필수키
        $auth_arr = $this->load->get_vars();
        $data['token'] = $auth_arr['token'];
        $data['refreshToken'] = $auth_arr['refreshToken'];
        $data['users_id'] = $auth_arr['users_id'];

        //토큰 유효성 및 권한 체크
        $data['menu_id'] = [120000];

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

        //필수키
        $data['limit'] = $this->post('limit', true); //1페이지에 표시하는 리스트수
        $data['page'] = $this->post('page', true); //페이지 번호

        //필수체크
        $this->_check_value($data);

        $data['channel'] = $this->post('channel', true);

        if(USERAUTHCODE == 2)
        {
            $data['status'] = $this->post('status', true); //181221 추가
            $data['searchCategory'] = $this->post('searchCategory', true);
            $data['searchAdType'] = $this->post('searchAdType', true);
            $data['searchType'] = 2; //이벤트명으로 고정
            $data['searchWord'] = $this->post('searchWord', true);
            $data['hospitalId'] = $this->common_m->getHospitalId($data);
        }
        else
        {
            $data['status'] = $this->post('status', true);
            $data['searchCategory'] = $this->post('searchCategory', true);
            $data['searchAdType'] = $this->post('searchAdType', true);
            $data['searchRegion'] = $this->post('searchRegion', true);
            $data['searchExposure'] = $this->post('searchExposure', true);
            $data['searchNetwork'] = $this->post('searchNetwork', true);
            $data['searchAgencyUserId'] = $this->post('searchAgencyUserId', true);
            $data['searchDbCost'] = $this->post('searchDbCost', true);
            $data['searchDbCostOperator'] = $this->post('searchDbCostOperator', true);
            $data['searchCooperation'] = $this->post('searchCooperation', true);
            $data['searchWhereImage'] = $this->post('searchWhereImage', true);
            $data['searchType'] = $this->post('searchType', true);
            $data['searchWord'] = $this->post('searchWord', true);
            //이벤트 상태 셀렉트 추가
            $data['adStatus']  = $this->post('adStatus', true);
        }

        $list = $this->ads_m->getAdsList($data);

        if(isset($list['totCnt']) && $list['totCnt'] > 0)
        {
            $this->response([
                'status' => 'success',
                'code' => '200',
                'message' => '',
                'result' => (object) ['list'=>$list['list'], 'totCnt'=>$list['totCnt'] ]
            ], 200);
        }
        else
        {
            $this->response([
                'status' => 'success',
                'code' => '204',
                'message' => '데이터가 없습니다.',
                'result' => (object) ['list'=>[], 'totCnt'=>0]
            ], 200);
        }
    }

    /**
     * @api {post} /ads/listCount 광고 리스트 상단 카운트
     * @apiHeader {string} x-api-key="qJqKDATY0D48ZH9hCNW4N6ygHBqtoVz4a4mu0P6A"
     * @apiHeader {string} x-api-token=""
     * @apiHeader {string} x-api-refreshtoken=""
     * @apiHeader {string} x-api-userid=""
     * @apiSampleRequest /api/v1.0/ads/listCount
     * @apiVersion 1.0.0
     * @apiName ads listCount
     * @apiGroup Ads
     * @apiDescription 광고 리스트 상단 카운트
     *
     * @apiParam {String} [channel]  노출대상 채널. 1 굿닥, 2 굿닥파트너스
     *
     * @apiSuccess {String="success", "error"} status="success" 상태
     * @apiSuccess {Number} code 코드값
     * @apiSuccess {String} message 빈값
     * @apiSuccess {Object[]} result 배열
     * @apiSuccess {Number} result.total 전체
     * @apiSuccess {Number} result.write 작성중
     * @apiSuccess {Number} result.progress 진행
     * @apiSuccess {Number} result.review 검토
     * @apiSuccess {Number} result.close 종료
     * @apiSuccess {Number} result.reject 반려 [병원어드민]
     *
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *       'status': 'success',
     *       'code': '200',
     *       'message': '상태변경되었습니다.',
     *       'result': {
     *              'total' => 0, 
     *              'write' => 0, 
     *              'progress' => 0, 
     *              'close' => 0
     *        }
     *     }
     *
     * @apiUse FailGetError
     * @apiUse NotAuthTokenError
     */
    public function listCount_post()
    {
        //바로종료시 비라이브도 진행? 바로승인시 라이브도 같이 진행? <- 동시진행
        $auth_arr = $this->load->get_vars();

        //필수키
        $data['token'] = $auth_arr['token'];
        $data['refreshToken'] = $auth_arr['refreshToken'];
        $data['users_id'] = $auth_arr['users_id'];

        //필수체크
        $this->_check_value($data);

        $data['channel'] = $this->post('channel', true);

        //api method별 하드코딩
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

        $result = $this->ads_m->getListCount($data);
       
        if($result !== false)
        {
            if (USERAUTHCODE == 2) {
                $return = 
                ['total' => $result['total'], 'write' => $result['write'], 'reject' => $result['reject'], 'progress' => $result['progress'], 'review' => $result['review'], 'close' => $result['close']];
            }
            else {
                $return = ['total' => $result['total'], 'write' => $result['write'], 'reject' => 0, 'progress' => $result['progress'], 'review' => $result['review'], 'close' => $result['close']];
            }
            
            $this->response([
                'status' => 'success',
                'code' => '200',
                'message' => '',
                'result' => $return
            ], 200);
        }
        else
        {
            $this->response([
                'status' => 'success',
                'code' => '610', //정보 get 실패
                'message' => '서버관리자에게 문의하세요.',
                'result' => ['total' => 0, 'write' => 0, 'reject' => 0, 'progress' => 0, 'review' => $result['review'], 'close' => 0]
            ], 200);
        }
    }

    /**
     * @api {post} /ads/listAction 광고 리스트 액션 : 바로종료, 바로승인
     * @apiHeader {string} x-api-key="qJqKDATY0D48ZH9hCNW4N6ygHBqtoVz4a4mu0P6A"
     * @apiHeader {string} x-api-token=""
     * @apiHeader {string} x-api-refreshtoken=""
     * @apiHeader {string} x-api-userid=""
     * @apiSampleRequest /api/v1.0/ads/listAction
     * @apiVersion 1.0.0
     * @apiName ads listAction
     * @apiGroup Ads
     * @apiDescription 광고 리스트 액션 : 바로종료, 바로승인 - 검수 테이블 동시 업데이트
     * @apiParam {Number} type 액션종류. 1 바로종료, 2 바로승인 (바로승인 사라짐. 여기서 템플릿 만들일 없음)
     * @apiParam {String} adsId 광고번호 또는 광고번호들
     * @apiParam {String} [isHospital] 병원 유무 (Y/N) 기본 N
     *
     * @apiSuccess {String="success", "error"} status="success" 상태
     * @apiSuccess {Number} code 코드값
     * @apiSuccess {String} message 상태변경되었습니다.
     * @apiSuccess {Object} result null
     *
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *       'status': 'success',
     *       'code': '200',
     *       'message': '상태변경되었습니다.',
     *       'result': null
     *     }
     *
     * @apiUse FailUpdateError
     * @apiUse NotAuthTokenError
     */
    public function listAction_post()
    {
        //바로종료시 비라이브도 진행? 바로승인시 라이브도 같이 진행? <- 동시진행
        $auth_arr = $this->load->get_vars();

        //필수키
        $data['token'] = $auth_arr['token'];
        $data['refreshToken'] = $auth_arr['refreshToken'];
        $data['users_id'] = $auth_arr['users_id'];
        $data['type'] = $this->post('type', true);
        $data['adsId'] = $this->post('adsId', true);

        //필수체크
        $this->_check_value($data);

        $data['isHospital'] = $this->post('isHospital', true);
        $data['isHospital'] = is_null($data['isHospital']) ?  'N' : $data['isHospital'];

        //api method별 하드코딩
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
        
        if ($data['isHospital'] == 'N') 
        {
            $result = $this->ads_m->updateListAction($data);
        }
        else 
        {
            //아이디 여러개 일때
            $subData = $data;
            $idArr   = [];
            if (strpos($data['adsId'], ',')  !== false )
            {
                $subData['adsId'] = null; 

                $id_arr = explode(',', $data['adsId']);
                foreach($id_arr as $key => $val)
                {
                    if (empty($val) || is_null($val))
                    {
                        unset($id_arr[$key]);
                        continue;
                    } 
                }
                $idArr = $id_arr;
                $subData['adsId'] = $id_arr;
            }
            else 
            {
                $idArr[] = $data['adsId'];
            }

            $check = $this->ads_m->isAdsInspection($idArr); 

            if ($check === true)
            {
                $this->response([
                    'status' => 'error',
                    'code' => '605', //업데이트 실패
                    'message' => '이미 검수중인 광고가 포함 되어 있습니다.',
                    'result' => null
                ], 200);
            }
            unset($idArr, $check);

            $result = $this->ads_m->updateListAction($subData);
        }

        if($result !== false)
        {
            $this->response([
                'status' => 'success',
                'code' => '200',
                'message' => '상태변경되었습니다.',
                'result' => null
            ], 200);
        }
        else
        {
            $this->response([
                'status' => 'error',
                'code' => '605', //정보 update 실패
                'message' => '서버관리자에게 문의하세요.',
                'result' => null
            ], 200);
        }
    }

    /**
     * @api {post} /ads/historyList 광고 히스토리 리스트
     * @apiHeader {string} x-api-key="qJqKDATY0D48ZH9hCNW4N6ygHBqtoVz4a4mu0P6A"
     * @apiHeader {string} x-api-token=""
     * @apiHeader {string} x-api-refreshtoken=""
     * @apiHeader {string} x-api-userid=""
     * @apiSampleRequest /api/v1.0/ads/historyList
     * @apiVersion 1.0.0
     * @apiName ads historyList
     * @apiGroup Ads
     * @apiDescription 광고 히스토리 리스트
     * @apiParam {Number} adsId 광고번호
     * @apiParam {Number} limit 페이징시 한 페이지에 노출할 리스트수
     * @apiParam {Number} page="1" 페이지번호
     *
     * @apiSuccess {String="success", "error"} status="success" 상태
     * @apiSuccess {Number} code 코드값
     * @apiSuccess {String} message 빈값
     * @apiSuccess {Object[]} result
     * @apiSuccess {Object[]} result.list 히스토리 정보
     * @apiSuccess {String} result.list.inspectDate 승인날짜
     * @apiSuccess {Number} result.list.inspectUserid 승인한 유저ID (TODO 이름은 V2유저 연동후)
     * @apiSuccess {String} result.list.regDate 수정날짜
     * @apiSuccess {Number} result.list.userId 수정한 유저ID (TODO 이름은 V2유저 연동후)
     * @apiSuccess {Object[]} result.list.editColumns 수정한 컬럼들 배열
     * @apiSuccess {String} [result.list.editColumns.adTitle] 수정한 타이틀
     * @apiSuccess {String} [result.list.editColumns.category] 수정한 카테고리
     * @apiSuccess {String} [result.list.editColumns.adStartDate] 수정한 이벤트 시작일
     * @apiSuccess {String} [result.list.editColumns.adEndDate] 수정한 이벤트 종료일
     * @apiSuccess {String} [result.list.editColumns.adDateExtend] 수정한 이벤트 자동연장 여부
     * @apiSuccess {String} [result.list.editColumns.adType] 수정한 이벤트 타입
     * @apiSuccess {String} [result.list.editColumns.exposure] 수정한 이벤트 노출영역
     * @apiSuccess {String} [result.list.editColumns.costType] 수정한 이벤트 가격타입
     * @apiSuccess {String} [result.list.editColumns.generalCost] 수정한 이벤트 정상가격
     * @apiSuccess {String} [result.list.editColumns.discountCost] 수정한 이벤트 할인가격
     * @apiSuccess {String} [result.list.editColumns.textCost] 수정한 이벤트 텍스트가격
     * @apiSuccess {Number} [result.list.editColumns.dbCost] 수정한 이벤트 디비단가
     * @apiSuccess {String} [result.list.editColumns.whereImage] 수정한 이벤트 이미지노출영역
     * @apiSuccess {Number} [result.list.editColumns.modelImageCount] 수정한 모델 이미지 카운트
     * @apiSuccess {String} [result.list.editColumns.whereImage] 수정한 이벤트 이미지노출영역
     * @apiSuccess {String} [result.list.editColumns.adDetailInfo] 수정한 이벤트 신청버튼 
     * @apiSuccess {String} [result.list.editColumns.contractId] 수정한 이벤트 계약번호
     * @apiSuccess {String} [result.list.editColumns.contractName] 수정한 이벤트 계약명
     * @apiSuccess {String} [result.list.editColumns.contractOrderId] 수정한 이벤트 수주계약번호
     * @apiSuccess {String} [result.list.editColumns.hospitalId] 수정한 이벤트 병원Id
     * @apiSuccess {Number} [result.list.editColumns.hospitalType] 수정한 이벤트 병원타입
     * @apiSuccess {String} [result.list.editColumns.agencyUserId] 수정한 이벤트 영업담당자ID 
     * @apiSuccess {Number} [result.list.editColumns.isViewBoard] 수정한 이벤트 계약명
     * @apiSuccess {String} [result.list.editColumns.deliberationCode] 수정한 이벤트 심의번호
     * @apiSuccess {String} [result.list.editColumns.customRanding] 수정한 이벤트 커스텀랜딩
     * @apiSuccess {String} [result.list.editColumns.custom1] 수정한 이벤트 커스텀랜딩 대표명
     * @apiSuccess {String} [result.list.editColumns.custom2] 수정한 이벤트 커스텀랜딩 사업자번호
     * @apiSuccess {String} [result.list.editColumns.custom3] 수정한 이벤트 커스텀랜딩 연락처
     * @apiSuccess {String} [result.list.editColumns.subHospitalId] 수정한 이벤트 자병원 Id
     * @apiSuccess {String} [result.list.editColumns.cooperation] 수정한 이벤트 제휴매체
     * @apiSuccess {String} [result.list.editColumns.region] 수정한 이벤트 노출지역
     * @apiSuccess {String} [result.list.editColumns.keyword] 수정한 이벤트 키워드
     * @apiSuccess {String} [result.list.editColumns.t1ImageName] 수정한 이벤트 이미지
     * @apiSuccess {String} [result.list.editColumns.t2ImageName] 수정한 이벤트 이미지
     * @apiSuccess {String} [result.list.editColumns.dImages]  수정한 이벤트 상세 이미지
     * @apiSuccess {String} result.totCnt 전체 갯수
     *
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *       'status': 'success',
     *       'code': '200',
     *       'message': '상태변경되었습니다.',
     *       'result': {
     *          'list' {},
     *          'totCnt' : 0
     *       }
     *     }
     * 
     * @apiUse FailGetError
     * @apiUse NotAuthTokenError
     */
    public function historyList_post()
    {
        //메모, 히스토리 리스트 분리
        //히스토리는 관리테이블 기준으로 페이징을 하고 히스토리는 배열로 첨부하여 리턴
        $auth_arr = $this->load->get_vars();

        //필수키
        $data['token'] = $auth_arr['token'];
        $data['refreshToken'] = $auth_arr['refreshToken'];
        $data['users_id'] = $auth_arr['users_id'];
        $data['adsId'] = $this->post('adsId', true);
        $data['limit'] = $this->post('limit', true);
        $data['page'] = $this->post('page', true);

        //필수체크
        $this->_check_value($data);

        //api method별 하드코딩
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

        $result = $this->ads_m->getHistoryList($data);

        if($result['totCnt'] != false)
        {
            $this->response([
                'status' => 'success',
                'code' => '200',
                'message' => '',
                'result' => ['list' => $result['info'], 'totCnt' => $result['totCnt']]
            ], 200);
        }
        else
        {
            $this->response([
                'status' => 'success',
                'code' => '204',
                'message' => '데이터가 없습니다.',
                'result' => ['list' => [], 'totCnt' => 0]
            ], 200);
        }
    }

    /**
     * @api {post} /ads/addPackage 기획전 등록
     * @apiHeader {string} x-api-key="qJqKDATY0D48ZH9hCNW4N6ygHBqtoVz4a4mu0P6A"
     * @apiHeader {string} x-api-token=""
     * @apiHeader {string} x-api-refreshtoken=""
     * @apiHeader {string} x-api-userid=""
     * @apiSampleRequest /api/v1.0/ads/addPackage
     * @apiVersion 1.0.0
     * @apiName package addPackage
     * @apiGroup Package
     * @apiDescription 기획전 등록
     * @apiParam {String} title 기획전 제목
     * @apiParam {Number} bannerViewType 배너노출영역. 1. 굿닥메인배너, 2 병원이벤트 메인배너, 3 기획전 리스트. 1,2,3 형태
     * @apiParam {Number} viewType 노출기간. 0 미노출, 1 상시노출, 2 기간노출
     * @apiParam {String} [startDate] 기간노출시 시작일
     * @apiParam {String} [endDate] 기간노출시 종료일
     * @apiParam {String} banner 기획전 배너이미지
     * @apiParam {String} detailInfo 기획전 설명
     * @apiParam {String} adsId 광고번호. 1,2,3,4 형태 맨앞이 최상위 순서
     *
     * @apiSuccess {String="success", "error"} status="success" 상태
     * @apiSuccess {Number} code 코드값
     * @apiSuccess {String} message 등록되었습니다.
     * @apiSuccess {Object[]} result 배열ㅈ
     * @apiSuccess {Object[]} result.info  기획전 등록  결과값 
     * @apiSuccess {Number} result.info.ads_package_mep_test 패키지 매핑 id
     *
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *       'status': 'success',
     *       'code': '200',
     *       'message': '등록되었습니다.',
     *       'result': {
     *          'info' : {}
     *        }
     *     }
     *
     * @apiUse RegistFailError
     * @apiUse NotAuthTokenError
     */
    public function addPackage_post()
    {
        $auth_arr = $this->load->get_vars();

        //필수키
        $data['token'] = $auth_arr['token'];
        $data['refreshToken'] = $auth_arr['refreshToken'];
        $data['users_id'] = $auth_arr['users_id'];
        $data['adsId'] = $this->post('adsId', true);
        $data['title'] = $this->post('title', true);
        $data['bannerViewType'] = $this->post('bannerViewType', true);
        $data['viewType'] = $this->post('viewType', true);
        $data['banner'] = $this->post('banner', true);
        $data['detailInfo'] = $this->post('detailInfo', true);

        //필수체크
        $this->_check_value($data);

        $data['startDate'] = $this->post('startDate', true);
        $data['endDate'] = $this->post('endDate', true);

        //기간노출시 체크
        if($data['viewType'] == 2)
        {
            if(!$data['startDate'] or !$data['endDate'])
            {
                $this->response([
                    'status' => 'error',
                    'code' => '606',
                    'message' => '시작일과 종료일은 필수값입니다.',
                    'result' => null
                ], 200);
            }
        }

        //api method별 하드코딩
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

        $result = $this->ads_m->addPackage($data);

        if($result !== false)
        {
            $this->response([
                'status' => 'success',
                'code' => '200',
                'message' => '기획전이 등록되었습니다.',
                'result' => (object) ['info' => ['ads_package_mep_test'=>$result]]
            ], 200);
        }
        else
        {
            $this->response([
                'status' => 'error',
                'code' => '614',
                'message' => '서버관리자에게 문의하세요.',
                'result' => (object) ['info' =>[]]
            ], 200);
        }
    }

    /**
     * @api {post} /ads/packageList 기획전 리스트
     * @apiHeader {string} x-api-key="qJqKDATY0D48ZH9hCNW4N6ygHBqtoVz4a4mu0P6A"
     * @apiHeader {string} x-api-token=""
     * @apiHeader {string} x-api-refreshtoken=""
     * @apiHeader {string} x-api-userid=""
     * @apiSampleRequest /api/v1.0/ads/packageList
     * @apiVersion 1.0.0
     * @apiName package packageList
     * @apiGroup Package
     * @apiDescription 기획전 리스트
     * @apiParam {Number} page 페이지번호
     * @apiParam {Number} limit 한 페이지에 노출할 리스트 갯수
     * @apiParam {Number} [status] 상태. 1 진행중, 2 종료, 3 전체
     * @apiParam {Number} [bannerViewType] 배너노출영역. 1. 굿닥메인배너, 2 병원이벤트 메인배너, 3 기획전 리스트, 4 전체
     * @apiParam {String} [startDate] 기간노출시 시작일
     * @apiParam {String} [endDate] 기간노출시 종료일
     * @apiParam {Number} [searchType] 검색타입. 1 기획전ID, 2 기획전명, 3 설명
     * @apiParam {String} [searchWord] 검색어
     *
     * @apiSuccess {String="success", "error"} status="success" 상태
     * @apiSuccess {Number} code 코드값
     * @apiSuccess {String} message 등록되었습니다.
     * @apiSuccess {Object[]} result 빈값
     * @apiSuccess {Object[]} result.packageList 기획전 리스트 결과 배열
     * @apiSuccess {Number} result.packageList.id id                                                                                              
     * @apiSuccess {Number} result.packageList.status 상태. 1 진행, 2 종료                                                                      
     * @apiSuccess {String} result.packageList.title  기획전명                                                                                    
     * @apiSuccess {Number} result.packageList.viewType 노출기간. 0 미노출, 1 상시노출, 2 기간노출                                       
     * @apiSuccess {String} result.packageList.startDate viewType 2일때 사용                                                                         
     * @apiSuccess {String} result.packageList.endDate viewType 2일때 사용                                                                         
     * @apiSuccess {String} result.packageList.detailInfo 기획전설명                                                                                 
     * @apiSuccess {String} result.packageList.regDate 등록일                                                                                       
     * @apiSuccess {String} result.packageList.modDate 수정일                                                                                       
     * @apiSuccess {String} result.packageList.isDelete Y이면 완전 삭제                                                                           
     * @apiSuccess {String} result.packageList.banner 기획전 배너이미지                                                                       
     * @apiSuccess {Number} result.packageList.adsCount  연결 광고 수                                                                               
     * @apiSuccess {Number} result.packageList.userId  등록자번호                                                                                 
     * @apiSuccess {Number} result.packageList.bannerViewType1 배너노출영역. 1 굿닥메인배너, 2병원이벤트 메인배너, 3 기획전 리스트  
     * @apiSuccess {Number} result.packageList.bannerViewType2 배너노출영역. 1 굿닥메인배너, 2병원이벤트 메인배너, 3 기획전 리스트  
     * @apiSuccess {Number} result.packageList.bannerViewType3 배너노출영역. 1 굿닥메인배너, 2병원이벤트 메인배너, 3 기획전 리스트  
     * @apiSuccess {Number} result.totCount 총 카운트  
     * 
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *       'status': 'success',
     *       'code': '200',
     *       'message': '',
     *       'result': {
     *           'packageList' : {},
     *           'totCount' : 0 
     *        }
     *     }
     *
     * @apiUse NotAuthTokenError
     */
    public function packageList_post()
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

        $data['status'] = $this->post('status', true);
        $data['bannerViewType'] = $this->post('bannerViewType', true);
        $data['startDate'] = $this->post('startDate', true);
        $data['endDate'] = $this->post('endDate', true);
        $data['searchType'] = $this->post('searchType', true);
        $data['searchWord'] = $this->post('searchWord', true);

        //시작, 종료일 수동 필수체크
        if($data['startDate'] and !$data['endDate'])
        {
            $this->response([
                'status' => 'error',
                'code' => '606',
                'message' => '종료일은 필수값입니다.',
                'result' => null
            ], 200);
        }
        else if(!$data['startDate'] and $data['endDate'])
        {
            $this->response([
                'status' => 'error',
                'code' => '606',
                'message' => '시작일은 필수값입니다.',
                'result' => null
            ], 200);
        }

        //검색타입, 검색어 체크
        if($data['searchType'] and !$data['searchWord'])
        {
            $this->response([
                'status' => 'error',
                'code' => '606',
                'message' => '검색어는 필수값입니다.',
                'result' => null
            ], 200);
        }
        else if(!$data['searchType'] and $data['searchWord'])
        {
            $this->response([
                'status' => 'error',
                'code' => '606',
                'message' => '검색타입은 필수값입니다.',
                'result' => null
            ], 200);
        }

        //api method별 하드코딩
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

        $result = $this->ads_m->packageList($data);

        if($result['code'] == 200)
        {
            $this->response([
                'status' => 'success',
                'code' => '200',
                'message' => '',
                'result' => (object) ['packageList'=>$result['list'], 'totCount'=>$result['totCnt']]
            ], 200);
        }
        else
        {
            $this->response([
                'status' => 'success',
                'code' => '200',
                'message' => '검색결과가 없거나 데이터가 없습니다.',
                'result' => (object)['packageList'=>[], 'totCount'=>0 ]
            ], 200);
        }
    }

    /**
     * @api {post} /ads/viewPackage 기획전 내용보기
     * @apiHeader {string} x-api-key="qJqKDATY0D48ZH9hCNW4N6ygHBqtoVz4a4mu0P6A"
     * @apiHeader {string} x-api-token=""
     * @apiHeader {string} x-api-refreshtoken=""
     * @apiHeader {string} x-api-userid=""
     * @apiSampleRequest /api/v1.0/ads/viewPackage
     * @apiVersion 1.0.0
     * @apiName package view
     * @apiGroup Package
     * @apiDescription 기획전 내용보기
     * @apiParam {Number} packageId 기획전 번호
     * @apiSuccess {String="success", "error"} status="success" 상태
     * @apiSuccess {Number} code 코드값
     * @apiSuccess {String} message 빈값
     * @apiSuccess {Object[]} result
     * @apiSuccess {Object[]} result.info
     * @apiSuccess {Number} result.info.id 기획전 번호
     * @apiSuccess {String} result.info.title 기획전 제목 
     * @apiSuccess {Number} result.info.status 상태. 1 진행, 2 종료
     * @apiSuccess {Number} result.info.bannerViewType1 배너노출영역. 굿닥메인배너
     * @apiSuccess {Number} result.info.bannerViewType2 배너노출영역. 병원이벤트 메인배너
     * @apiSuccess {Number} result.info.bannerViewType3 배너노출영역. 기획전 리스트
     * @apiSuccess {Number} result.info.viewType 노출기간. 0 미노출, 1 상시노출, 2 기간노출
     * @apiSuccess {String} result.info.startDate 시작일. viewType 2일때 사용
     * @apiSuccess {String} result.info.endDate 종료일. viewType 2일때 사용
     * @apiSuccess {String} result.info.detailInfo 기획전 설명
     * @apiSuccess {String} result.info.regDate 기획전 등록일
     * @apiSuccess {String} result.info.modDate 기획전 수정
     * @apiSuccess {String} result.info.isDelete 기획전 삭제여부 (사용안함)
     * @apiSuccess {String} result.info.banner 기획전 배너이미지
     * @apiSuccess {Number} result.info.adsCount 연결광고수
     * @apiSuccess {Number} result.info.userId 등록자 번호
     * @apiSuccess {String} result.info.adsId 매핑된 이벤트번호. 1,2,3 형태
     *
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *       'status': 'success',
     *       'code': '200',
     *       'message': '등록되었습니다.',
     *       'result': {
     *          'info' : {}
     *        }
     *     }
     * 
     * @apiUse FailGetError
     */
    public function viewPackage_post()
    {
        $auth_arr = $this->load->get_vars();

        //필수키
        $data['token'] = $auth_arr['token'];
        $data['refreshToken'] = $auth_arr['refreshToken'];
        $data['users_id'] = $auth_arr['users_id'];
        $data['packageId'] = $this->post('packageId', true);

        //필수체크
        $this->_check_value($data);

        //api method별 하드코딩
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

        $result = $this->ads_m->getPackage($data);

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
                'code' => '610',
                'message' => '데이터가 없습니다.',
                'result' => (object) ['info' => []]
            ], 200);
        }
    }

    /**
     * @api {post} /ads/updatePackage 기획전 수정
     * @apiHeader {string} x-api-key="qJqKDATY0D48ZH9hCNW4N6ygHBqtoVz4a4mu0P6A"
     * @apiHeader {string} x-api-token=""
     * @apiHeader {string} x-api-refreshtoken=""
     * @apiHeader {string} x-api-userid=""
     * @apiSampleRequest /api/v1.0/ads/updatePackage
     * @apiVersion 1.0.0
     * @apiName package updatePackage
     * @apiGroup Package
     * @apiDescription 기획전 수정
     * @apiParam {Number} packageId 기획전번호
     * @apiParam {String} [title] 기획전 제목
     * @apiParam {Number} [bannerViewType] 배너노출영역. 1. 굿닥메인배너, 2 병원이벤트 메인배너, 3 기획전 리스트
     * @apiParam {Number} [viewType] 노출기간. 0 미노출, 1 상시노출, 2 기간노출
     * @apiParam {String} [startDate] 기간노출시 시작일
     * @apiParam {String} [endDate] 기간노출시 종료일
     * @apiParam {String} [banner] 기획전 배너이미지
     * @apiParam {String} [detailInfo] 기획전 설명
     * @apiParam {String} [adsId] 광고번호. 1,2,3,4 형태 맨앞이 최상위 순서
     *
     * @apiSuccess {String="success", "error"} status="success" 상태
     * @apiSuccess {Number} code 코드값
     * @apiSuccess {String} message 등록되었습니다.
     * @apiSuccess {Object} result null
     *
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *       'status': 'success',
     *       'code': '200',
     *       'message': '등록되었습니다.',
     *       'result': null
     *     }
     *
     * @apiUse FailUpdateError
     * @apiUse NotAuthTokenError
     */
    public function updatePackage_post()
    {
        $auth_arr = $this->load->get_vars();

        //필수키
        $data['token'] = $auth_arr['token'];
        $data['refreshToken'] = $auth_arr['refreshToken'];
        $data['users_id'] = $auth_arr['users_id'];
        $data['packageId'] = $this->post('packageId', true);

        //필수체크
        $this->_check_value($data);

        $data['adsId'] = $this->post('adsId', true);
        $data['title'] = $this->post('title', true);
        $data['bannerViewType'] = $this->post('bannerViewType', true);
        $data['viewType'] = $this->post('viewType', true);
        $data['banner'] = $this->post('banner', true);
        $data['detailInfo'] = $this->post('detailInfo', true);
        $data['startDate'] = $this->post('startDate', true);
        $data['endDate'] = $this->post('endDate', true);

        //기간노출시 체크
        if($data['viewType'] == 2)
        {
            if(!$data['startDate'] or !$data['endDate'])
            {
                $this->response([
                    'status' => 'error',
                    'code' => '606',
                    'message' => '시작일과 종료일은 필수값입니다.',
                    'result' => null
                ], 200);
            }
        }

        //api method별 하드코딩
        $data['menu_id'] = [110000];

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

        $result = $this->ads_m->updatePackage($data);

        if($result !== false)
        {
            $this->response([
                'status' => 'success',
                'code' => '200',
                'message' => '기획전이 수정되었습니다.',
                'result' => null
            ], 200);
        }
        else
        {
            $this->response([
                'status' => 'error',
                'code' => '605', //update fail
                'message' => '서버관리자에게 문의하세요.',
                'result' => null
            ], 200);
        }
    }

    /**
     * @api {post} /ads/historyMemo 광고 히스토리 메모 등록
     * @apiHeader {string} x-api-key="qJqKDATY0D48ZH9hCNW4N6ygHBqtoVz4a4mu0P6A"
     * @apiHeader {string} x-api-token=""
     * @apiHeader {string} x-api-refreshtoken=""
     * @apiHeader {string} x-api-userid=""
     * @apiSampleRequest /api/v1.0/ads/historyMemo
     * @apiVersion 1.0.0
     * @apiName ads historyMemo
     * @apiGroup Ads
     * @apiDescription 광고 히스토리 메모 등록
     * @apiParam {Number} adsId 광고번호
     * @apiParam {String} memo 광고 히스토리 메모
     *
     * @apiSuccess {String="success", "error"} status="success" 상태
     * @apiSuccess {Number} code 코드값
     * @apiSuccess {String} message 메모 등록되었습니다.
     * @apiSuccess {Object} result null
     *
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *       'status': 'success',
     *       'code': '200',
     *       'message': '메모 등록되었습니다.',
     *       'result': null
     *     }
     *
     * @apiUse RegistFailError
     * @apiUse NotAuthTokenError
     */
    public function historyMemo_post()
    {
        $auth_arr = $this->load->get_vars();

        //필수키
        $data['token'] = $auth_arr['token'];
        $data['refreshToken'] = $auth_arr['refreshToken'];
        $data['users_id'] = $auth_arr['users_id'];
        $data['adsId'] = $this->post('adsId', true);
        $data['memo'] = $this->post('memo', true);

        //필수체크
        $this->_check_value($data);

        //api method별 하드코딩
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

        //작업필요
        $result = $this->ads_m->setHistoryMemo($data);

        if($result !== false)
        {
            $this->response([
                'status' => 'success',
                'code' => '200',
                'message' => '메모 등록되었습니다.',
                'result' => null
            ], 200);
        }
        else
        {
            $this->response([
                'status' => 'error',
                'code' => '614',
                'message' => '서버관리자에게 문의하세요.',
                'result' => null
            ], 200);
        }
    }

    /**
     * @api {post} /ads/getHistoryMemo 광고 히스토리 메모 리스트
     * @apiHeader {string} x-api-key="qJqKDATY0D48ZH9hCNW4N6ygHBqtoVz4a4mu0P6A"
     * @apiHeader {string} x-api-token=""
     * @apiHeader {string} x-api-refreshtoken=""
     * @apiHeader {string} x-api-userid=""
     * @apiSampleRequest /api/v1.0/ads/getHistoryMemo
     * @apiVersion 1.0.0
     * @apiName ads getHistoryMemo
     * @apiGroup Ads
     * @apiDescription 광고 히스토리 메모 리스트
     * @apiParam {Number} adsId 광고번호
     *
     * @apiSuccess {String="success", "error"} status="success" 상태
     * @apiSuccess {Number} code 코드값
     * @apiSuccess {String} message 빈값
     * @apiSuccess {Object[]} result 배열
     * @apiSuccess {Object[]} result.list  광고 히스토리 메모 리스트 배열
     * @apiSuccess {Number} result.list.id 메모번호
     * @apiSuccess {Number} result.list.adsId 광고번호
     * @apiSuccess {String} result.list.memo 메모내용
     * @apiSuccess {String} result.list.userId 작성자번호
     * @apiSuccess {String} result.list.regDate 작성일시
     * @apiSuccess {String} result.list.userName 작성자명
     *
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *       'status': 'success',
     *       'code': '200',
     *       'message': '',
     *       'result': {
     *          'list' : {}
     *        }
     *     }
     *
     * @apiUse FailGetError
     * @apiUse NotAuthTokenError
     */
    public function getHistoryMemo_post()
    {
        $auth_arr = $this->load->get_vars();

        //필수키
        $data['token'] = $auth_arr['token'];
        $data['refreshToken'] = $auth_arr['refreshToken'];
        $data['users_id'] = $auth_arr['users_id'];
        $data['adsId'] = $this->post('adsId', true);

        //필수체크
        $this->_check_value($data);
        
        //api method별 하드코딩
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

        $result = $this->ads_m->getHistoryMemo($data);
 
        if($result !== false)
        {
            $this->response([
                'status' => 'success',
                'code' => '200',
                'message' => '',
                'result' => (object)['list' => $result]
            ], 200);
        }
        else
        {
            $this->response([
                'status' => 'success',
                'code' => '610',
                'message' => '서버관리자에게 문의하세요.',
                'result' =>(object) ['list' => []]
            ], 200);
        }
    }

    /**
     * @api {post} /ads/tempList 광고 임시저장 리스트 
     * @apiHeader {string} x-api-key="qJqKDATY0D48ZH9hCNW4N6ygHBqtoVz4a4mu0P6A"
     * @apiHeader {string} x-api-token=""
     * @apiHeader {string} x-api-refreshtoken=""
     * @apiHeader {string} x-api-userid=""
     * @apiSampleRequest /api/v1.0/ads/tempList
     * @apiVersion 1.0.0
     * @apiName ads tempList
     * @apiGroup Ads
     * @apiDescription 광고 임시저장 리스트 
     * @apiParam {Number} limit 페이징시 한 페이지에 노출할 리스트수
     * @apiParam {Number} page="1" 페이지번호
     * @apiParam {String} [searchCategory] 카테고리 [병원어드민]. 1,2,3 카테고리 번호 형태
     * @apiParam {String} [searchAdType] 광고유형 [병원어드민]. 광고유형. a 전체, 1 cpa, 2 cpm, 3 프로모션, 4 cpc
     * @apiParam {String} [searchRegion] 지역. a 전체, 1 서울, 2 부산, 3 인천, 4 대구, 5 광주, 6 대전, 7 울산, 8 경기, 9 강원, 10 충북, 11 충남, 12 전북, 13 전남, 14 경북, 15 경남, 16 제주
     * @apiParam {Number} [searchExposure] 노출영역 1 이벤트, 2 병원상세, 3 둘다
     * @apiParam {String} [searchNetwork] 병원종류. a 전체, 1 일반, 2 네트워크(모), 3 네트워크(자)
     * @apiParam {String} [searchAgencyUserId] 영업담당자 번호
     * @apiParam {Number} [searchDbCost] DB단가
     * @apiParam {String} [searchDbCostOperator] DB단가 연산자. 1 =, 2 >=, 3 >, 4 <, 5 <=
     * @apiParam {String} [searchCooperation] 제휴매체(1,2,3 형태). 1 쿠차, 2 리타켓팅매체, 3 페이스북, 4 네이버밴드, 5 피키캐스트, 6 SMS_KAKAO
     * @apiParam {String} [searchWhereImage] 이미지노출. 1 이벤트모델, 2 의료진, 3 전후사진.  1,2,3 형태
     * @apiParam {String} [searchType] 검색종류. 1 병원명, 2 이벤트명, 3 이벤트 Id. 4 메모
     * @apiParam {String} [searchWord] 검색어 [병원어드민]. (searchType=3일 경우 콤마로 구분하여 검색 가능)
     *
     * @apiSuccess {String="success", "error"} status="success" 상태
     * @apiSuccess {Number} code 코드값
     * @apiSuccess {String} message 빈값
     * @apiSuccess {Object[]} result 배열
     * @apiSuccess {Object[]} result.list 광고리스트 배열
     * @apiSuccess {Number} result.list.id 광고번호
     * @apiSuccess {String} result.list.adTitle 광고명
     * @apiSuccess {Number} result.list.adStatus  이벤트 상태. 1 검토, 2 진행, 3 종료, 4 작성중, 5 반려
     * @apiSuccess {Number} result.list.category 카테고리
     * @apiSuccess {String} result.list.adStartDate 광고시작일
     * @apiSuccess {String} result.list.adEndDate 광고종료일
     * @apiSuccess {String} result.list.adDateExtend 이벤트기간연장 Y, N
     * @apiSuccess {Number} result.list.adType 광고유형 1cpa, 2 cpm, 3 프로모션, 4 cpc, 5 옵션
     * @apiSuccess {Number} result.list.exposure 노출영역. 1 이벤트존, 2 병원상세, 3 둘다
     * @apiSuccess {Number} result.list.costType 가격타입, 1 숫자, 2 텍스트
     * @apiSuccess {Number} result.list.generalCost 정상가
     * @apiSuccess {Number} result.list.discountCost 할인가
     * @apiSuccess {String} result.list.textCost 텍스트단가
     * @apiSuccess {Number} result.list.dbCost db단가
     * @apiSuccess {String} result.list.whereImage 이미지노출. 1 이s벤트모델, 2 의료진, 3 전후사진.  1,2,3 형태, like 검색으로 처리
     * @apiSuccess {Number} result.list.modelImageCount 모델이미지수s
     * @apiSuccess {String} result.list.adDetailInfo 버튼 관련 정보
     * @apiSuccess {String} result.list.regDate 등록일시
     * @apiSuccess {String} result.list.modDate 수정일시
     * @apiSuccess {Number} result.list.contractId 계약번호
     * @apiSuccess {Number} result.list.contractOrderId 수주계약번호
     * @apiSuccess {Number} result.list.hospitalId 병원번호
     * @apiSuccess {Number} result.list.hospitalType 병원타입
     * @apiSuccess {Number} result.list.agencyUserId 영업담당자번호
     * @apiSuccess {String} result.list.hospitalName 병원명
     * @apiSuccess {String} result.list.isLive  라이브여부 Y/N
     * @apiSuccess {Number} result.list.subAdStatus sub이벤트 상태. 1 수정검토, 2 종료검토, 3신규등록검토, 4 재등록 검토, 5 어드민작성중, 6 병원작성중
     * @apiSuccess {Number} result.list.adsId 광고Id
     * @apiSuccess {String} result.list.inspectDate 승인날짜
     * @apiSuccess {Number} result.list.isViewBoard 후기노출여부. 1 노출, 2 미노출. 병원은 1 고정, 운영은 필수값
     * @apiSuccess {String} result.list.deliberationCode 심의번호
     * @apiSuccess {String} result.list.customRanding 커스텀랜딩
     * @apiSuccess {String} result.list.optionAdId 옵션이벤트 번호들
     * @apiSuccess {String} result.list.custom1 커스텀랜딩 2인 경우 대표명
     * @apiSuccess {String} result.list.custom2 커스텀랜딩 2인 경우 사업자번호
     * @apiSuccess {String} result.list.custom3 커스텀랜딩 2인 경우 연락처
     * @apiSuccess {String} result.list.cooperation 제휴매체 1,2,3형태
     * @apiSuccess {Number} result.list.userId 등록Id
     * @apiSuccess {String} result.list.contractName 계약명
     * @apiSuccess {String} result.list.keyword 키워드 키,워,드 형태
     * @apiSuccess {Number} result.list.isDelete 1:삭제, 2:미삭제
     * @apiSuccess {String} result.list.subHospitalId 네트워크 병원 Id들
     * @apiSuccess {String} result.list.t1ImageName 썸네일1 이미지
     * @apiSuccess {String} result.list.t2ImageName 썸네일2 이미지
     * @apiSuccess {String} result.list.dImages 임시저장
     * @apiSuccess {String} result.list.delDate 삭제 날짜
     * @apiSuccess {String} result.list.deleteuserId 삭제 유저 Id
     * @apiSuccess {Number} result.list.channel 노출대상 채널. 1 굿닥, 2 굿닥파트너스
     * @apiSuccess {Number} result.totCnt 전체 리스트 수
     *
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *       'status': 'success',
     *       'code': '200',
     *       'message': '',
     *       'result': {
     *          'list' : {},
     *          'totCnt' : 0
     *        }
     *     }
     *
     * @apiUse FailGetError
     * @apiUse NotAuthTokenError
     */
    public function tempList_post()
    {
        //필수키
        $auth_arr = $this->load->get_vars();
        $data['token'] = $auth_arr['token'];
        $data['refreshToken'] = $auth_arr['refreshToken'];
        $data['users_id'] = $auth_arr['users_id'];

        //토큰 유효성 및 권한 체크
        $data['menu_id'] = [120000];
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

        //필수키
        $data['limit'] = $this->post('limit', true); //1페이지에 표시하는 리스트수
        $data['page'] = $this->post('page', true); //페이지 번호
        
        //필수체크
        $this->_check_value($data);

        if(USERAUTHCODE == 2)
        {
            $data['searchCategory'] = $this->post('searchCategory', true);
            $data['searchAdType'] = $this->post('searchAdType', true);
            $data['searchType'] = 2; //이벤트명으로 고정
            $data['searchWord'] = $this->post('searchWord', true);
            $data['hospitalId'] = $this->common_m->getHospitalId($data);
        }
        else
        {
            $data['searchCategory'] = $this->post('searchCategory', true);
            $data['searchAdType'] = $this->post('searchAdType', true);
            $data['searchRegion'] = $this->post('searchRegion', true);
            $data['searchExposure'] = $this->post('searchExposure', true);
            $data['searchNetwork'] = $this->post('searchNetwork', true);
            $data['searchAgencyUserId'] = $this->post('searchAgencyUserId', true);
            $data['searchDbCost'] = $this->post('searchDbCost', true);
            $data['searchDbCostOperator'] = $this->post('searchDbCostOperator', true);
            $data['searchCooperation'] = $this->post('searchCooperation', true);
            $data['searchWhereImage'] = $this->post('searchWhereImage', true);
            $data['searchType'] = $this->post('searchType', true);
            $data['searchWord'] = $this->post('searchWord', true);
        }

        $return_arr = $this->adsTemp_m->getAdsTempList($data);

        if($return_arr['totCnt'] == false)
        {
            $this->response([
                'status' => 'success',
                'code' => '204',
                'message' => '데이터가 없습니다.',
                'result' => null
            ], 200);
        }

        $this->response([
            'status' => 'success',
            'code' => '200',
            'message' => '',
            'result' => is_array($return_arr) ? (object) $return_arr : (object) [$return_arr]
        ], 200); 
    }

    /**
     * @api {post} /ads/delete 광고 삭제
     * @apiHeader {string} x-api-key="qJqKDATY0D48ZH9hCNW4N6ygHBqtoVz4a4mu0P6A"
     * @apiHeader {string} x-api-token=""
     * @apiHeader {string} x-api-refreshtoken=""
     * @apiHeader {string} x-api-userid=""
     * @apiSampleRequest /api/v1.0/ads/delete
     * @apiVersion 1.0.0
     * @apiName ads delete
     * @apiGroup Ads
     * @apiDescription 광고 광고 삭제
     * @apiParam {Number} adsId 광고 번호
     *
     * @apiSuccess {String="success", "error"} status="success" 상태
     * @apiSuccess {Number} code 코드값
     * @apiSuccess {String} message "광고가 삭제 되었습니다."
     * @apiSuccess {Object} result null
     * 
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *       'status': 'success',
     *       'code': '200',
     *       'message': '광고가 삭제 되었습니다.',
     *       'result': null
     *     }
     *
     * @apiUse FailUpdateError
     * @apiUse NotAuthTokenError
     */
    public function delete_post()
    {
        //필수키
        $auth_arr = $this->load->get_vars();
        $data['token'] = $auth_arr['token'];
        $data['refreshToken'] = $auth_arr['refreshToken'];
        $data['users_id'] = $auth_arr['users_id'];

        $data['adsId']  = $this->post('adsId', true);

        //토큰 유효성 및 권한 체크
        $data['menu_id'] = [110000];
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

        $result_arr = $this->ads_m->adsDelete($data);

        if (!isset($result_arr['success'])) 
        {
            $this->response([
                'status' => 'error',
                'code' => $result_arr['code'],
                'message' => '서버관리자에게 문의하세요.',
                'result' => null
            ], 200);
        }
        
        if ($result_arr['success'] === false) 
        {
            $this->response([
                'status' => 'error',
                'code' => $result_arr['code'],
                'message' => $result_arr['message'],
                'result' => null
            ], 200);
        }

        $this->response([
            'status' => 'success',
            'code' => '200',
            'message' => '광고가 삭제 되었습니다.',
            'result' => null
        ], 200);
    }   

    /**
     * @api {post} /ads/tempUpdate 광고 임시저장 수정
     * @apiHeader {string} x-api-key="qJqKDATY0D48ZH9hCNW4N6ygHBqtoVz4a4mu0P6A"
     * @apiHeader {string} x-api-token=""
     * @apiHeader {string} x-api-refreshtoken=""
     * @apiHeader {string} x-api-userid=""
     * @apiSampleRequest /api/v1.0/ads/tempUpdate
     * @apiVersion 1.0.0
     * @apiName ads tempUpdate
     * @apiGroup Ads
     * @apiDescription 광고 임시저장 수정
     * @apiParam {Number} tempId 임시저장 번호
     * @apiParam {Number} [adsId] 광고Id 
     * @apiParam {String} adTitle 이벤트
     * @apiParam {Number} hospitalId 병원번호
     * @apiParam {Number} [hospitalType] 병원유형. 1 일반, 2 네트워크(모), 3 네트워크(자). 운영어드민은 디폴트임. 병원은 자기 상태에 따라 정의
     * @apiParam {String} [subHospitalId] 자병원번호. hospitalId가 모병원일 경우 선택한 자병원을 1,2,3 형태로 전송한다.
     * @apiParam {Number} adType 광고상품유형. 1 cpa, 2 cpm, 3 프로모션, 4 cpc, 5 옵션
     * @apiParam {String} adStartDate 광고시작일
     * @apiParam {String} adEndDate 광고종료일
     * @apiParam {String} adDateExtend 이벤트기간 연장여부. Y 연장, N 연장안함
     * @apiParam {Number} costType 가격타입. 1 숫자, 2 텍스트
     * @apiParam {Number} [generalCost] 정상가.
     * @apiParam {Number} [discountCost] 할인가. costType=1 인 경우 필수값
     * @apiParam {String} [textCost] 텍스트가격. costType=2 인 경우 필수값
     * @apiParam {Number} [dbCost] DB단가
     * @apiParam {Number} category 카테고리. 소 카테고리 번호
     * @apiParam {Number} exposure 노출영역. 1 이벤트, 2 병원상세, 3 둘다
     * @apiParam {Number} contractId 계약번호
     * @apiParam {Number} contractOrderId 수주계약번호
     * @apiParam {String} [optionAdId] 옵션이벤트번호. 1,2,3 형태
     * @apiParam {String} [region] 노출지역(1,2,3 형태) 1 서울, 2 부산, 3 인천, 4 대구, 5 광주, 6 대전, 7 울산, 8 경기, 9 강원, 10 충북, 11 충남, 12 전북, 13 전남, 14 경북, 15 경남, 16 제주. 병원은 1로 고정, 운영은 필수
     * @apiParam {String} [cooperation] 제휴매체 노출동의(1,2,3 형태). 1 쿠차, 2 리타켓팅매체, 3 페이스북, 4 네이버밴드, 5 피키캐스트, 6 SMS_KAKAO
     * @apiParam {String} [whereImage] 이미지노출. 1 이벤트모델, 2 의료진, 3 전후사진.  1,2,3 형태
     * @apiParam {String} [keyword] 검색키워드. 최대 5개, 다이어트,자임당,라섹 형태
     * @apiParam {Number} [modelImageCount] 모델모집 이미지갯수. 0, 1, 2 중 하나. 병원은 0 고정. 운영은 필수값
     * @apiParam {String} [buttonName] 신청버튼 문구
     * @apiParam {String} [buttonLink] 외부링크 url. adType cpc, cpm나 프로모션일 경우 사용
     * @apiParam {String} [buttonType] cpm용 버튼노출여부. 1 전화상담, 2 링크버튼, 3 카톡상담, 4 링크버튼+전화상담, 5 카톡상담+전화상담
     * @apiParam {String} [buttonPhone] cpm용 상담전화번호.
     * @apiParam {String} [buttonColor] 신청버튼 색상값
     * @apiParam {String} [buttonNameColor] 신청버튼 문구 색상
     * @apiParam {String} t1ImageName 썸네일1 이미지
     * @apiParam {String} t2ImageName 썸네일2 이미지
     * @apiParam {String} dImages 본문 이미지
     * @apiParam {Number} [isViewBoard] 후기노출여부. 1 노출, 2 미노출. 병원은 1 고정, 운영은 필수값
     * @apiParam {String} [deliberationCode] 의료심의번호
     * @apiParam {String} [customRanding] 커스텀랜딩 (다중선택가능 1,2형태) 1 케어랩스, 2 병원사업자정보
     * @apiParam {String} [custom1] 커스텀랜딩 2인 경우 대표명
     * @apiParam {String} [custom2] 커스텀랜딩 2인 경우 사업자 등록번호
     * @apiParam {String} [custom3] 커스텀랜딩 2인 경우 연락처
     * @apiParam {Number} channel 노출대상 채널. 1 굿닥, 2 굿닥파트너스
     *
     * @apiSuccess {String="success", "error"} status="success" 상태
     * @apiSuccess {Number} code 코드값
     * @apiSuccess {String} message "임시저장 수정이 완료 되었습니다."
     * @apiSuccess {Object[]} result 
     * @apiSuccess {Number}  result.0 tempId
     *
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *       'status': 'success',
     *       'code': '200',
     *       'message': '임시저장 수정이 완료 되었습니다.',
     *       'result': {1}
     *     }
     *
     * @apiUse RegistFailError
     * @apiUse NotAuthTokenError
     */
    public function tempUpdate_post()
    {
        //adType이 5인 옵션의 경우 db단가등 병원, 계약정보등을 받지 않도록 처리해야한다.
        $auth_arr = $this->load->get_vars();

        //필수키
        $data['token'] = $auth_arr['token'];
        $data['refreshToken'] = $auth_arr['refreshToken'];
        $data['users_id'] = $auth_arr['users_id'];
        $data['tempId']  = $this->post('tempId', true);
        $data['channel']  = $this->post('channel', true);

        //api method별 하드코딩
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

       if(USERAUTHCODE ==2)
       {
           //병원어드민용
           //필수키
           $data['adTitle'] = $this->post('adTitle', true);
           $data['hospitalId'] = $this->post('hospitalId', true);

           $data['adStartDate'] = $this->post('adStartDate', true);
           $data['adEndDate'] = $this->post('adEndDate', true);
           $data['adDateExtend'] = $this->post('adDateExtend', true);
           $data['costType'] = $this->post('costType', true);
           $data['category'] = $this->post('category', true);
           $data['exposure'] = $this->post('exposure', true);
           $data['contractId'] = $this->post('contractId', true);
           $data['contractOrderId'] = $this->post('contractOrderId', true);

           $data['t1ImageName'] = $this->post('t1ImageName', true);
           $data['t2ImageName'] = $this->post('t2ImageName', true);

           //필수체크
           $this->_check_value($data);

           /**
            * biz-1537
            */
           $data['adTitle'] = str_replace('(LEFTANGLE)', '<', $data['adTitle']);
           $data['adTitle'] = str_replace('(RIGHTANGLE)', '>', $data['adTitle']);

           $data['cooperation'] = $this->post('cooperation', true);
           $data['whereImage'] = $this->post('whereImage', true);
           $data['keyword'] = $this->post('keyword', true);
           $data['dbCost'] = $this->post('dbCost', true);

           $data['hospitalType'] = 1;
           $data['modelImageCount'] = 0;
           $data['region'] = 1;
           $data['isViewBoard'] = 1;
           $data['adType'] = 1; //cpa

           $data['generalCost'] = $this->post('generalCost', true);
           $data['discountCost'] = $this->post('discountCost', true);
           $data['textCost'] = $this->post('textCost', true);
           $data['dImages']  = $this->post('dImages', true);
           $data['deliberationCode'] = $this->post('deliberationCode', true);

           //조건별 필수 체크
           if($data['costType'] == 1)
           {
               if($data['discountCost'] == '')
               {
                   $this->response([
                       'status' => 'error',
                       'code' => '606',
                       'message' => '할인가는 필수값입니다.',
                       'result' => null
                   ], 200);
               }
           }
           else if($data['costType'] == 2)
           {
               if(!$data['textCost'])
               {
                   $this->response([
                       'status' => 'error',
                       'code' => '606',
                       'message' => '텍스트입력은 필수값입니다.',
                       'result' => null
                   ], 200);
               }
           }
       }
       else
       {
           //필수키
           $data['adTitle'] = $this->post('adTitle', true);
           $data['hospitalId'] = $this->post('hospitalId', true);
           $data['hospitalType'] = $this->post('hospitalType', true);
           $data['adType'] = $this->post('adType', true);
           $data['adStartDate'] = $this->post('adStartDate', true);
           $data['adEndDate'] = $this->post('adEndDate', true);
           $data['adDateExtend'] = $this->post('adDateExtend', true);
           $data['costType'] = $this->post('costType', true);

           $data['category'] = $this->post('category', true);
           $data['exposure'] = $this->post('exposure', true);
           $data['contractId'] = $this->post('contractId', true);
           $data['contractOrderId'] = $this->post('contractOrderId', true);
           $data['region'] = $this->post('region', true);

           $data['modelImageCount'] = $this->post('modelImageCount', true);
           $data['t1ImageName'] = $this->post('t1ImageName', true);
           $data['t2ImageName'] = $this->post('t2ImageName', true);
           $data['isViewBoard'] = $this->post('isViewBoard', true);

           //필수체크
           $this->_check_value($data);

           /**
            * biz-1537
            */
           $data['adTitle'] = str_replace('(LEFTANGLE)', '<', $data['adTitle']);
           $data['adTitle'] = str_replace('(RIGHTANGLE)', '>', $data['adTitle']);

           $data['dbCost'] = $this->post('dbCost', true);

           $data['whereImage'] = $this->post('whereImage', true);
           $data['cooperation'] = $this->post('cooperation', true);
           $data['keyword'] = $this->post('keyword', true);
           $data['subHospitalId'] = $this->post('subHospitalId', true);
           $data['generalCost'] = $this->post('generalCost', true);
           $data['discountCost'] = $this->post('discountCost', true);
           $data['textCost'] = $this->post('textCost', true);
           $data['optionAdId'] = $this->post('optionAdId', true);
           $data['dImages']    = $this->post('dImages', true);
           $data['buttonName'] = $this->post('buttonName', true);
           $data['buttonLink'] = $this->post('buttonLink', true);
           $data['buttonType'] = $this->post('buttonType', true);
           $data['buttonPhone'] = $this->post('buttonPhone', true);
           $data['buttonNameColor'] = $this->post('buttonNameColor', true);
           $data['buttonColor'] = $this->post('buttonColor', true);
           $data['deliberationCode'] = $this->post('deliberationCode', true);
           $data['customRanding'] = $this->post('customRanding', true);
           $data['custom1'] = $this->post('custom1', true);
           $data['custom2'] = $this->post('custom2', true);
           $data['custom3'] = $this->post('custom3', true);

           //조건별 필수 체크
           if($data['hospitalType'] == 2)
           {
               if(!$data['subHospitalId'])
               {
                   $this->response([
                       'status' => 'error',
                       'code' => '606',
                       'message' => '자병원번호는 필수값입니다.',
                       'result' => null
                   ], 200);
               }
           }

           if($data['costType'] == 1)
           {
               if($data['discountCost'] == '')
               {
                   $this->response([
                       'status' => 'error',
                       'code' => '606',
                       'message' => '할인가는 필수값입니다.',
                       'result' => null
                   ], 200);
               }
           }
           else if($data['costType'] == 2)
           {
               if(!$data['textCost'])
               {
                   $this->response([
                       'status' => 'error',
                       'code' => '606',
                       'message' => '텍스트입력은 필수값입니다.',
                       'result' => null
                   ], 200);
               }
           }

           if($data['adType'] == '5')
           {
               if(!$data['optionAdId'])
               {
                   $this->response([
                       'status' => 'error',
                       'code' => '606',
                       'message' => '연결 옵션은 필수값입니다.',
                       'result' => null
                   ], 200);
               }
           }

           if($data['adType'] == '2')
           {
               if(!$data['buttonType'])
               {
                   $this->response([
                       'status' => 'error',
                       'code' => '606',
                       'message' => '버튼노출여부는 필수값입니다.',
                       'result' => null
                   ], 200);
               }
               else
               {
                   if($data['buttonType'] == 1)
                   {
                       if(!$data['buttonPhone'])
                       {
                           $this->response([
                               'status' => 'error',
                               'code' => '606',
                               'message' => '상담 전화번호는 필수값입니다.',
                               'result' => null
                           ], 200);
                       }
                   }
                   else if($data['buttonType'] == 2 or $data['buttonType'] == 3)
                   {
                       if(!$data['buttonLink'])
                       {
                           $this->response([
                               'status' => 'error',
                               'code' => '606',
                               'message' => '링크/카톡 URL은 필수값입니다.',
                               'result' => null
                           ], 200);
                       }
                   }
                   else if($data['buttonType'] == 4 or $data['buttonType'] == 5)
                   {
                       if(!$data['buttonPhone'] and !$data['buttonLink'])
                       {
                           $this->response([
                               'status' => 'error',
                               'code' => '606',
                               'message' => '상담 전화번호, URL은 필수값입니다.',
                               'result' => null
                           ], 200);
                       }
                   }
               }
           }
           else if($data['adType'] == '3')
           {
               if(!$data['buttonLink'])
               {
                   $this->response([
                       'status' => 'error',
                       'code' => '606',
                       'message' => '외부링크 URL은 필수값입니다.',
                       'result' => null
                   ], 200);
               }
           }
           else if($data['adType'] == '4')
           {
               if(!$data['buttonLink'])
               {
                   $this->response([
                       'status' => 'error',
                       'code' => '606',
                       'message' => '이벤트 URL은 필수값입니다.',
                       'result' => null
                   ], 200);
               }
           }

           //커스텀랜딩 체크. 2가 있는 경우
           if(strstr(@$data['customRanding'], '2'))
           {
               if(!$data['custom1'])
               {
                   $this->response([
                       'status' => 'error',
                       'code' => '606',
                       'message' => '커스텀랜딩 대표명은 필수값입니다.',
                       'result' => null
                   ], 200);
               }

               if(!$data['custom2'])
               {
                   $this->response([
                       'status' => 'error',
                       'code' => '606',
                       'message' => '커스텀랜딩 사업자번호는은 필수값입니다.',
                       'result' => null
                   ], 200);
               }

               if(!$data['custom3'])
               {
                   $this->response([
                       'status' => 'error',
                       'code' => '606',
                       'message' => '커스텀랜딩 연락처는 필수값입니다.',
                       'result' => null
                   ], 200);
               }
           }
       }

       $data['adsId'] = $this->post('adsId', true);
      
       foreach($data as $key => $val)
       {
           if (is_null($data[$key]))
           {
                unset($data[$key]);
           }
       }

       $return = $this->adsTemp_m->updateAdsTemp($data);

       if($return !== false)
       {
           $this->response([
               'status' => 'success',
               'code' => '200',
               'message' => '임시저장 수정이 완료 되었습니다.',
               'result' => is_array($return) ? (object) $return : (object) [$return]
           ], 200);
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
     * @api {post} /ads/tempDelete 광고 임시저장 삭제
     * @apiHeader {string} x-api-key="qJqKDATY0D48ZH9hCNW4N6ygHBqtoVz4a4mu0P6A"
     * @apiHeader {string} x-api-token=""
     * @apiHeader {string} x-api-refreshtoken=""
     * @apiHeader {string} x-api-userid=""
     * @apiSampleRequest /api/v1.0/ads/tempDelete
     * @apiVersion 1.0.0
     * @apiName ads tempDelete
     * @apiGroup Ads
     * @apiDescription 광고 임시저장 삭제
     * @apiParam {string} tempId 임시저장 아이디 또는 아이디들 
     *
     * @apiSuccess {String="success", "error"} status="success" 상태
     * @apiSuccess {Number} code 코드값
     * @apiSuccess {String} message "임시저장 삭제가 완료 되었습니다."
     * @apiSuccess {Object[]} result 
     * @apiSuccess {Number} result.0 adsId
     *
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *       'status': 'success',
     *       'code': '200',
     *       'message': '임시저장 삭제가 완료 되었습니다.',
     *       'result': {1}
     *     }
     *
     * @apiUse RegistFailError
     * @apiUse NotAuthTokenError
     */
    public function tempDelete_post()
    {
        //adType이 5인 옵션의 경우 db단가등 병원, 계약정보등을 받지 않도록 처리해야한다.
        $auth_arr = $this->load->get_vars();

        //필수키
        $data['token'] = $auth_arr['token'];
        $data['refreshToken'] = $auth_arr['refreshToken'];
        $data['users_id'] = $auth_arr['users_id'];
        $data['tempId']  = $this->post('tempId', true);
       
        //api method별 하드코딩
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

        $result = $this->adsTemp_m->deleteAdsTemp($data['tempId'], $data['users_id']);
    
        if ($result === false)
        {
            $this->response([
                'status' => 'error',
                'code' => '614', //입력 실패
                'message' => '서버관리자에게 문의하세요.',
                'result' => null
            ], 200);
        }

        $this->response([
            'status' => 'success',
            'code' => '200',
            'message' => '임시저장 삭제가 완료 되었습니다.',
            'result' =>  null
        ], 200);
    }
    

   /**
     * @api {post} /ads/tempView 광고 임시저장 내용 조회
     * @apiHeader {string} x-api-key="qJqKDATY0D48ZH9hCNW4N6ygHBqtoVz4a4mu0P6A"
     * @apiHeader {string} x-api-token=""
     * @apiHeader {string} x-api-refreshtoken=""
     * @apiHeader {string} x-api-userid=""
     * @apiSampleRequest /api/v1.0/ads/tempView
     * @apiVersion 1.0.0
     * @apiName ads tempView
     * @apiGroup Ads
     * @apiDescription 광고 임시저장 내용 조회
     * @apiParam {Number} tempId 임시저장 번호
     *
     * @apiSuccess {String="success", "error"} status="success" 상태
     * @apiSuccess {Number} code 코드값
     * @apiSuccess {String} message 빈값
     * @apiSuccess {Object[]} result 배열
     * @apiSuccess {Object[]} result.info 광고 임시저장 내용 조회 결과값 배열
     * @apiSuccess {Number} result.info.adsId 광고Id 없으면 null
     * @apiSuccess {String} result.info.adTitle 광고명
     * @apiSuccess {Number} result.info.adStatus  이벤트 상태. 1 검토, 2 진행, 3 종료, 4 작성중, 5 반려
     * @apiSuccess {Number} result.info.adSubStatus sub이벤트 상태. 1 수정검토, 2 종료검토, 3신규등록검토, 4 재등록 검토, 5 어드민작성중, 6 병원작성중
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
     * @apiSuccess {Number} result.info.whereImage 이미지노출. 1 이s벤트모델, 2 의료진, 3 전후사진.  1,2,3 형태, like 검색으로 처리
     * @apiSuccess {Number} result.info.modelImageCount 모델이미지수s
     * @apiSuccess {String} result.info.adDetailInfo 버튼 관련 정보
     * @apiSuccess {String} result.info.regDate 등록일시
     * @apiSuccess {String} result.info.modDate 수정일시
     * @apiSuccess {Number} result.info.contractId 계약번호
     * @apiSuccess {Number} result.info.contractOrderId 수주계약번호
     * @apiSuccess {Number} result.info.hospitalId 병원번호
     * @apiSuccess {Number} result.info.hospitalType 병원타입
     * @apiSuccess {Number} result.info.agencyUserId 영업담당자번호
     * @apiSuccess {String} result.info.deliberationCode 심의번호 [병원어드민]
     * @apiSuccess {String} result.info.customRanding 커스텀랜딩 (다중선택가능 1,2형태) 1 케어랩스, 2 병원사업자정보
     * @apiSuccess {String} result.info.custom1 커스텀랜딩 2인 경우 대표명
     * @apiSuccess {String} result.info.custom2 커스텀랜딩 2인 경우 사업자 등록번호
     * @apiSuccess {String} result.info.custom3 커스텀랜딩 2인 경우 연락처
     * @apiSuccess {String} result.info.hospitalName 병원명
     * @apiSuccess {String} result.info.cooperation 제휴매체. 1,3,4 형태
     * @apiSuccess {String} result.info.keyword 키워드. 콤마로 구분
     * @apiSuccess {String} result.info.subHospitalId 네트워크병원 번호(네트워크병원인 경우). 콤마로 구분
     * @apiSuccess {String} result.info.buttonName 버튼명
     * @apiSuccess {String} result.info.buttonLink 버튼링크
     * @apiSuccess {String} result.info.buttonType 버튼형식
     * @apiSuccess {String} result.info.buttonPhone 버튼 상담 전화번호
     * @apiSuccess {String} result.info.buttonColor 버튼색상
     * @apiSuccess {String} result.info.buttonNameColor 버튼명 색상
     * @apiSuccess {String} result.info.t1ImageName 썸네일1 이미지 (필수)
     * @apiSuccess {String} result.info.t2ImageName 썸네일2 이미지 (필수)
     * @apiSuccess {String} result.info.dImages 상세 이미지 
     * @apiSuccess {String} result.info.id 아이디
     * @apiSuccess {String} result.info.isLive 라이브여부 Y/N
     * @apiSuccess {Number} result.info.subAdStatus sub이벤트 상태. 1 수정검토, 2 종료검토, 3신규등록검토, 4 재등록 검토, 5 어드민작성중, 6 병원작성중
     * @apiSuccess {String} result.info.inspectDate 승인날짜
     * @apiSuccess {String} result.info.isViewBoard 후기노출여부. 1 노출, 2 미노출
     * @apiSuccess {String} result.info.optionAdId 옵션이벤트 번호들
     * @apiSuccess {String} result.info.region 광고 노출 지역
     * @apiSuccess {Number} result.info.userId 광고 등록 유저 Id
     * @apiSuccess {String} result.info.contractName 계약명
     * @apiSuccess {Number} result.info.isDelete 삭제 유무 1: 삭제, 2: 미삭제
     * @apiSuccess {String} result.info.delDate 삭제한 날짜
     * @apiSuccess {String} result.info.deleteuserId 삭제한 유저 Id
     * @apiSuccess {Number} result.info.channel 노출대상 채널. 1 굿닥, 2 굿닥파트너스
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
    public function tempView_post()
    {
       //adType이 5인 옵션의 경우 db단가등 병원, 계약정보등을 받지 않도록 처리해야한다.
       $auth_arr = $this->load->get_vars();

       //필수키
       $data['token'] = $auth_arr['token'];
       $data['refreshToken'] = $auth_arr['refreshToken'];
       $data['users_id'] = $auth_arr['users_id'];
       $data['tempId']  = $this->post('tempId', true); 

        //api method별 하드코딩
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

        $result = $this->adsTemp_m->getAdsTemp($data['tempId'] , $data['token'], $data['users_id']);
        
        if(count($result) == 0)
        {
            $this->response([
                'status' => 'success',
                'code' => '404',
                'message' => '존재하지 임시저장 번호 입니다.',
                'result' => (object) ['info' => []]
            ], 200);
        }

        $this->response([
            'status' => 'success',
            'code' => '200',
            'message' => '',
            'result' => (object) ['info' => $result]
        ], 200);
    }

    /**
     * @api {post} /ads/historyView 광고 관리 - 히스토리 조회
     * @apiHeader {string} x-api-key="qJqKDATY0D48ZH9hCNW4N6ygHBqtoVz4a4mu0P6A"
     * @apiHeader {string} x-api-token=""
     * @apiHeader {string} x-api-refreshtoken=""
     * @apiHeader {Number} x-api-userid=""
     * @apiSampleRequest /api/v1.0/ads/historyView
     * @apiVersion 1.0.0
     * @apiName ads view
     * @apiGroup Ads
     * @apiDescription 광고 관리 - 히스토리 조회
     * @apiParam {String} adsUrl 광고파일 경로. 예) /events/10135/15093423/index.html
     *
     * @apiSuccess {String="success", "error"} status="success" 상태
     * @apiSuccess {Number} code 코드값
     * @apiSuccess {String} message 빈값
     * @apiSuccess {String} result 해당 광고 html
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
    public function history_view_post()
    {
        $auth_arr = $this->load->get_vars();

        //필수키
        $data['adsUrl'] = $this->post('adsUrl', true);

        //필수체크
        $this->_check_value($data);

        //api method별 하드코딩
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

        $result = $this->ads_m->getHistoryView($data);

        if($result != false)
        {
            $this->response([
                'status' => 'success',
                'code' => '200',
                'message' => '',
                'result' => $result
            ], 200);
        }
        else
        {
            $this->response([
                'status' => 'success',
                'code' => '404',
                'message' => '존재하지 않는 파일이거나 데이터가 없습니다.',
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
