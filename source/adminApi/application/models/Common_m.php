<?php
/**
 * Created by PhpStorm.
 * User: blumine
 * Date: 2018. 2. 12.
 * Time: PM 5:45
 */

use Aws\S3\S3Client;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7;
use GuzzleHttp\Exception\RequestException;

class Common_m extends CI_Model
{
    private $CI = null;
    
    function __construct()
    {
        parent::__construct();
        //$this->load->library('Goodocv2api');
        $this->CI =& get_instance();
         
    }

    /**
     * 트랜잭션 상황인지 학인
     * @return bool
     */
    public function isInTrans() : bool
    {   
        //1: 오토커밋 실행중 , 0 : 오토커밋 중지중
        $result = $this->master->query("SELECT @@autocommit as isInTrans;")->result_array();
        $isInTrans = isset($result[0]['isInTrans']) ? (int) $result[0]['isInTrans'] : 1;
        
        return $isInTrans == 0 ? true : false;
    }

    /**
     * 토큰 체크 및 권한 리턴후 권한 체크
     * 다중menu_id에 대해 다중 권한 체크
     * @param $data
     * @return array|mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    function checkToken($data)
    {
        try {
            $data00 = $this->goodocapi->tokenValidate();
           
            if($data00['code'] != 200)
            { 
                //캡슐화 후 메소드 정보 가져와서 호출
                $data00['status'] = 'error';
                if (!isset($data00['result']))
                {
                    $data00['result'] = null;
                }
                return $data00;
            }
            else
            {
                /*
                 * 권한부분 변경 예정. 1010101 형태로 권한코드 변경.
                 * 앞 6자리는 대분류2 중분류2 소분류2 자리코드이고
                 * 맨뒤 1자라는 0 모든권한, 1 읽기만
                 * 병원어드민은 구분하기 쉽게 16진수로 시작. 예 a0
                 */
                //권한 체크
                $result = $data00['result'];
                $authArr = $result['auth'];
                $userType = $result['userType'];

                if(count($authArr) == 0)
                {
                    $returnArr = array(
                        'status' => 'error',
                        'code' => 401,
                        'message' => '접근권한이 없습니다.(N)',
                        'result' => null
                    );
                    return $returnArr;
                }

                //병원인지 운영인지 체크
                if($userType == 0)
                {
                    $tasks = 4; //운영
                }
                else
                {
                    $tasks = 2; //병원
                }
                //$tasks = 4;
                define('USERAUTHCODE', $tasks); //1 일반, 2 병원, 4 운영자
              
                //권한 체크
                if($data['menu_id'] == 'A')
                {
                    $returnArr = array(
                        'status' => 'success',
                        'code' => 200,
                        'message' => '권한체크 안함',
                        'result' => null
                    );
                }
                else
                {
                    if($tasks == 2)
                    {
                        //병원이면
                        //현재는 모든 메뉴에 접근가능한데 병원용 메뉴번호도 작업을 해야함. 병원용은 api 명으로 접근가능여부 체크하도록...
                        //api명으로 접근제어
                        $allowArr = [
                            'ads/register', 'ads/listCount', 'ads/view', 'ads/eventList', 'ads/update',
                            'ads/tempList', 'ads/tempDelete', 'ads/tempView', 'ads/tempUpdate', 'ads/listAction',
                            'ads/getHistoryMemo',
                            'common/getContractOrderMemo', 'common/createFile', 'advertiser/getContractList',
                            'advertiser/getContractInfo','advertiser/getContractOrderList','advertiser/getDepositList',
                            'advertiser/register','advertiser/getInfo','advertiser/update', 'advertiser/dashBoard',
                            'payment/check', 'payment/register', 'upload/file', 'user/getUserInfo',
                            'advertiserBoard/view','advertiserBoard/list',
                            'eventRequest/lists', 'eventRequest/update', 'eventRequest/memo', 'eventRequest/parameter', 'eventRequest/getRequestStatus',
                            'eventRequest/view',
                            'inspection/cancel',
                            'board/lists', 'board/view',
                            'contractOrder/getStatusDepositTotal',
                            'contract/searchContractList'
                        ];
                       
                        $words = explode('/', $this->uri->uri_string());
                   
                        if(count($words) < 3)
                        {
                            $returnArr = array(
                                'status' => 'error',
                                'code' => 401,
                                'message' => '병원 api 접근권한이 없습니다.(1)',
                                'result' => null
                            );
                            return $returnArr;
                        }

                        $uris = $words[1].'/'.$words[2];

                        if( !in_array($uris, $allowArr) )
                        {
                            $returnArr = array(
                                'status' => 'error',
                                'code' => 401,
                                'message' => '병원 api 접근권한이 없습니다.',
                                'result' => null
                            );
                        }
                        else
                        {
                            $returnArr = array(
                                'status' => 'success',
                                'code' => 200,
                                'message' => '병원운영자 권한체크 성공',
                                'result' => null
                            );
                        }

                    }
                    else
                    {
                        $i = 0;

                        //메뉴번호 여러개 처리
                        foreach ($data['menu_id'] as $authCode)
                        {
                            //운영자이면
                            $bMyAuth = substr($authCode, 0, 2); //대분류
                            $mMyAuth = substr($authCode, 2, 2); //중분류
                            $sMyAuth = substr($authCode, 4, 2); //소분류
                            //dd($bMyAuth, false);
                            //dd($mMyAuth, false);
                            //dd($sMyAuth, false);

                            foreach ($authArr as $it=>$val)
                            {
                                $bAuth = substr($it, 0, 2); //대분류
                                $mAuth = substr($it, 2, 2); //중분류
                                $sAuth = substr($it, 4, 2); //소분류
                                //$wAuth = $val; //all 읽기, 쓰기, 전체. 나중에 사용
                                //dd($bAuth, false);
                                //dd($mAuth, false);
                                //dd($sAuth, false);
                                if($mAuth == '00' and $sAuth == '00')
                                {
                                    //대분류에 대해 권한을 가지고 있다면 하위는 당연히 권한이 있다.
                                    if($bAuth == $bMyAuth)
                                    {
                                        $i++;
                                    }
                                }

                                if($mAuth != '00' and $sAuth == '00')
                                {
                                    //중분류에 대해 권한을 가지고 있다면 하위는 당연히 권한이 있다.
                                    if($bAuth.$mAuth == $bMyAuth.$mMyAuth)
                                    {
                                        $i++;
                                    }
                                }

                                if($mAuth != '00' and $sAuth != '00')
                                {
                                    //소분류에 대한 권한만 있는 경우
                                    if($bAuth.$mAuth.$sAuth == $bMyAuth.$mMyAuth.$sMyAuth)
                                    {
                                        $i++;
                                    }
                                }
                            }
                        }
                        //dd($i);
                        if($i > 0)
                        {
                            $returnArr = array(
                                'status' => 'success',
                                'code' => 200,
                                'message' => '권한체크 성공',
                                'result' => null
                            );
                        }
                        else
                        {
                            $returnArr = array(
                                'status' => 'error',
                                'code' => 401,
                                'message' => '권한이 없습니다.',
                                'result' => null
                            );
                        }
                    }
                }

                return $returnArr;
            }

        } catch (RequestException $e) {
            //echo Psr7\str($e->getRequest());
            if ($e->hasResponse()) {
                //echo Psr7\str($e->getResponse());
            }
        }
    }

    /**
     * 수주계약 메모 가져오기
     * @param $data
     * @return array
     */
    function getContractOrderMemo($data)
    {
        if($data['memoType'] == 0)
        {
            //전체 검색
            $arr = array('targetId'=>$data['contractOrderId']);
        }
        else
        {
            $arr = array('memoType'=>$data['memoType'], 'targetId'=>$data['contractOrderId']);
        }

        $result = $this->db->get_where('memo', $arr)->result_array();

        return $result;
    }

    /**
     * 메모 입력
     * @param $data
     */
    function setContractOrderMemo($data)
    {
        $arr = [
            'memoType'=>$data['memoType'],
            'targetId'=>$data['targetId'],
            'targetId2'=>$data['targetId2'],
            'userId'=>$data['userId'],
            'memo'=>$data['memo'],
            'regDate'=>date("Y-m-d H:i:s")
        ];
        $this->master->insert('memo', $arr);
    }

    /**
     * v1 카테고리 구하기(소분류)
     * @return mixed
     */
    function getCategory()
    {
        $sql = "
            select  
            -- ec2.id bId, 
            ec1.id sId, concat(ec2.title,' > ', ec1.title) as categoryTitle
            -- , ec2.sort,ec1.sort
            from  event_categories ec1 -- 소분류
			join event_categories ec2 on ec1.parent_id=ec2.id -- 대분류
			where ec2.is_visible=1
            order by ec2.sort, ec1.sort;
        ";

        $result0 = [['sId'=>'A', 'categoryTitle'=>'전체']];
        $result1 = $this->db->query($sql)->result_array();

        $result = array_merge($result0, $result1);

        return $result;
    }

    /**
     * 총 수주액 조회
     * @param $data
     * @return array
     */
    function getOrderAmount($data)
    {
        //월 기준 세금계산서 발행된 총합 - 그달 발행환불, 발행취소 총액 = 그달 수주액

        //당월
//        $this->db->select('sum(adPrice) as totSum');
//        $this->db->like('taxIssueDate', $data['month'], 'left');
//        //$this->db->where_not_in('contractStatus', array(4,5)); //발행환불, 발행취소 제외
//        $this->db->where_in('contractStatus', array(1)); //정상인 경우만
//        $thisTotSum = $this->db->get('contract_order')->row_array();
//
//        //이전 월, 발행환불은 실제 환불한 금액만.
//        $this->db->select('sum(adPrice) as totSum');
//        $this->db->like('taxIssueDate', $data['lastMonth'], 'left');
//        $this->db->where_not_in('contractStatus', array(5)); //발행취소 제외
//        $this->db->where_in('contractStatus', array(1)); //정상인 경우만
//        $lastTotSum = $this->db->get('contract_order')->row_array();
        /**
         * select  (select ifnull(sum(ds.price), 0)  from contract_order co
        left join deposit ds on co.id=ds.`contractOrderId`
        where ds.status=1 and co.contractStatus in(1,2,3) and co.taxIssueDate like '2018-09%' )
        -
        (select (select ifnull(sum(ds.price), 0) from contract_order co
        left join deposit ds on co.id=ds.`contractOrderId`
        where co.taxIssueDate like '2018-09%' and ds.status in(6,9) and ds.regDate like '2018-09%'))
         */

        //당월
        $sql1 = "
            select (select ifnull(sum(ds.price), 0) from contract_order co
              join deposit ds on co.id=ds.`contractOrderId`
              where co.taxIssueDate like '".$data['month']."%' and ds.status=1) - 
            (select ifnull(sum(price), 0) from deposit where status in(6,7) and regDate like '".$data['month']."%') totSum
        ";
        $sql1 = "
            select  (select ifnull(sum(ds.price), 0)  from contract_order co
        left join deposit ds on co.id=ds.`contractOrderId`
        where ds.status=1 and co.contractStatus in(1,2,3) and co.taxIssueDate like '".$data['month']."%')
        -
        (select (select ifnull(sum(ds.price), 0) from contract_order co
        left join deposit ds on co.id=ds.`contractOrderId`
        where co.taxIssueDate like '".$data['month']."%' and ds.status in(6,7) and ds.regDate like '".$data['month']."%')) totSum
        ";

        $sql1 = "
            select  (select ifnull(sum(ds.price), 0)  from contract_order co
        left join deposit ds on co.id=ds.`contractOrderId`
        where ds.status=1 and co.contractStatus in(1,2,3) and co.taxIssueDate like '".$data['month']."%')
        -
        (select (select ifnull(sum(ds.price), 0) from contract_order co
        left join deposit ds on co.id=ds.`contractOrderId`
        where co.taxIssueDate like '".$data['month']."%' and ds.status in(6,7,8) and ds.regDate like '".$data['month']."%')) totSum
        ";

        $sql1 = "select (
            (select  (select ifnull(sum(ds.price), 0)  from contract_order co
        left join deposit ds on co.id=ds.`contractOrderId`
        where ds.status=1 and co.contractStatus in(1) and co.taxIssueDate like '".$data['month']."%'))
        -
        (select (select ifnull(sum(ds.price), 0) from contract_order co
        left join deposit ds on co.id=ds.`contractOrderId`
        where ds.status in(6,9) and ds.regDate like '".$data['month']."%'))
        -
        (select (select ifnull(sum(ds.price), 0) from contract_order co
        left join deposit ds on co.id=ds.`contractOrderId`
        where co.contractStatus in(2) and ds.status in(8) and ds.regDate like '".$data['month']."%'))
        ) totSum
        ";

        //세금발행매출 = 수주 - (기타소진(차감)+환불) 이다. 2018.10.5 with luna
        // 바뀐 기준 적용. 18.10.29. 이번달 세금계산서 발행 수주금액 총합 - 이번달 발행환불, 발행취소 금액
        // 바뀐 기준 적용. 18.10.30. 이번달 세금계산서 발행 수주금액 총합 - 이번달 발행환불, 발행취소 금액 - (발행환불이면서 원장에 기타차감된 금액)
        $thisTotSum = $this->db->query($sql1)->row_array();

        //지난달
        $sql2 = "select (
            (select  (select ifnull(sum(ds.price), 0)  from contract_order co
        left join deposit ds on co.id=ds.`contractOrderId`
        where ds.status=1 and co.contractStatus in(1) and co.taxIssueDate like '".$data['lastMonth']."%'))
        -
        (select (select ifnull(sum(ds.price), 0) from contract_order co
        left join deposit ds on co.id=ds.`contractOrderId`
        where ds.status in(6,9) and ds.regDate like '".$data['lastMonth']."%'))
        -
        (select (select ifnull(sum(ds.price), 0) from contract_order co
        left join deposit ds on co.id=ds.`contractOrderId`
        where co.contractStatus in(2) and ds.status in(8) and ds.regDate like '".$data['lastMonth']."%'))
        ) totSum
        ";
        $lastTotSum = $this->db->query($sql2)->row_array();

        $sumArr = array('thisMonth'=>$thisTotSum, 'lastMonth'=>$lastTotSum);

        return $sumArr;
    }

    /**
     * contractId와 contractOrderId 매칭 체크
     * @param $data
     * @return bool
     */
    function getContractId($data)
    {
        $arr = array('contractId'=>$data['contractId'], 'contractOrderId'=>$data['contractOrderId']);
        $result = $this->db->get_where('contract_order_connect', $arr)->result_array();

        if(count($result) >= 1)
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    /**
     * 영업담당자 리턴
     * @param $data
     * @return bool
     */
    function getAgencyUserId($data)
    {
        $arr = array('id'=>$data['contractId']);

        $checkDb = $this->isInTrans() === true ?  $this->master  :  $this->db;

        $result = $checkDb->get_where('contract', $arr)->row_array();

        if(count($result > 0))
        {
            return $result['agencyUserId'];
        }
        else
        {
            return false;
        }
    }

    /**
     * 유저번호에 해당하는 병원번호 리턴
     * @param $data
     * @return mixed
     */
    function getHospitalId($data = null)
    {
        //$data['users_id'] = 69;
        /*
            t1 ~ t50
        */
        
        $CI = & get_instance(); 
        $auth_arr = $CI->load->get_vars();
        if(isset($auth_arr['headerHospitalId']))
        {
            return $auth_arr['headerHospitalId'];
        }

        return isset($auth_arr['headerHospitalId']) ? (int) $auth_arr['headerHospitalId'] : '';

        $hospitalIdArr = $this->goodocapi->getHospitlaIdByUserId($data['users_id']);
        ///dd($hospitalIdArr);
        //return isset($hospitalIdArr[0]) ? (int) $hospitalIdArr[0] : '';
    }

    /**
     * 내 이벤트번호 리턴
     * @param $data
     * @return array
     */
    function getEventId($data)
    {   
        //삭제유무 추가 18.12.21
        $hospitalId = $this->getHospitalId($data);
        $result = $this->db->select('id as adsId, adTitle')->get_where('ads', ['hospitalId'=>$hospitalId, 'isDelete'=>'N'])->result_array();

        return $result;
    }

    /**
     * 계약리스트 및 검색에서 사용되는 시점잔액, 광고주상태, 원장상태 summary table update
     *
     * @param $data
     */
    function updateTotalInfo($data)
    {
        $date = date("Y-m-d H-i:s");

        $uArr = [];

        switch ($data['type'])
        {
            case 1: //시점잔액과 원장상태. 같이 움직임(신청db) 광고주 상태에도 영향을 줌
                $depositStatusGroup = $this->getDepositStatus($data);
                $advertiserStatus = $this->getAdvertiserStatus($data);
                $uArr = ['totalReady'=>$data['totalReady'], 'depositStatusGroup'=>$depositStatusGroup, 'advertiserStatus'=>$advertiserStatus, 'modDate'=>$date];
                break;
            case 2: //광고주 상태. 이벤트 승인시
                $advertiserStatus = $this->getAdvertiserStatus($data);
                $uArr = ['advertiserStatus'=>$advertiserStatus, 'modDate'=>$date];
                break;
        }

        if(count($uArr) > 0)
        {
            $this->master->where('contractId', $data['contractId']);
            $this->master->update('total_ready_price', $uArr);
        }
    }

    /**
     * 원장 상태 리턴
     * @param $data
     * @return mixed
     */
    function getDepositStatus($data)
    {
        $sql = "select group_concat(distinct status) as depositStatusGroup from deposit where contractId='".$data['contractId']."' group by contractId";
        $result2 = $this->master->query($sql)->row_array();

        return $result2['depositStatusGroup'];
    }

    /**
     * 광고주 상태 리턴, 계약 상태변경에서 호출
     * @param $data
     * @return mixed
     */
    function getAdvertiserStatus($data)
    {
        $now_date = date("Y-m-d",strtotime("-3 month"));

        $sql = "
            select
                    if((select count(*) from ads where contractId=c.id) = 0, '대기',
                if((select count(*) from ads where contractId=c.id and ads.isLive='Y') > 0, '진행', 
                    if(
                        (select count(*) from ads where contractId=c.id and ads.isLive='Y' and modDate > '" . $now_date . "') = 0 
                        and 
                        (select count(*) from ads where contractId=c.id and modDate > '" . $now_date . "') = (select count(*) from ads where contractId=c.id and ads.isLive='N' and modDate > '" . $now_date . "' 
                            and 
                            (
                            ifnull((select sum(price) from deposit where status in(2,4) and contractId=c.id and contractOrderId=co.id),0) 
                            - 
                            ifnull((select sum(price) from deposit where status in(3,5,6,7,8) and contractId=c.id and contractOrderId=co.id),0)
                        ) <= 0
                    )
                        , '휴면', 
                        if(
                            (select count(*) from ads where contractId=c.id and ads.isLive='Y') = 0 
                            and 
                            (select count(*) from ads where contractId=c.id)=(select count(*) from ads where contractId=c.id and ads.isLive='N' 
                                and 
                                (
                                ifnull((select sum(price) from deposit where status in(2,4) and contractId=c.id and contractOrderId=co.id),0)
                                - 
                                ifnull((select sum(price) from deposit where  status in(3,5,6,7,8) and contractId=c.id and contractOrderId=co.id),0)
                            ) > 0
                        )
                            , '중지', '이탈'
                        ) 
                    )	
                )
                ) as hStatus, c.id
                from contract c
                JOIN `contract_order_connect` `coc` ON `c`.`id`=`coc`.`contractId`
                JOIN `contract_order` `co` ON `coc`.`contractOrderId`=`co`.`id`
                where c.id='".$data['contractId']."'
                group by c.hospitalId, c.id
                ";
        $result2 = $this->master->query($sql)->row_array();

        return $result2['hStatus'];
    }

    /**
     * s3 이미지 정보 가져오기
     * @param $type 1 v1, 2 v2
     * @param $data
     * @return \Aws\Result
     */
    function s3DateCheck($type, $data)
    {
        if($type == 1)
        {
            $s3Client = Aws\S3\S3Client::factory(array(
                'region' => 'ap-northeast-1',
                'version' => 'latest',
                'signature' => 'v4',
                'credentials' => array(
                    'key'    => 'AKIAJ56IRBSYEFJNTKQQ',
                    'secret' => '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'
                )
            ));

            if ( ENVIRONMENT == "production" )
            {
                $path = "production/uploads/event/image2/{$data['id']}/{$data['image2']}";
            }
            else if( ENVIRONMENT == "testing" )
            {
                //$path = "staging/uploads/event/image2/{$data['id']}/{$data['image2']}";
                $path = "production/uploads/event/image2/{$data['id']}/{$data['image2']}";
            }
            else
            {
                $path = "dev/uploads/event/image2/{$data['id']}/{$data['image2']}";
            }

            $fPath = 'https://s3.ap-northeast-1.amazonaws.com/image.goodoc/'.$path;
            $resultImage = $this->remoteFileExist($fPath);

            if(!$resultImage)
            {
                if ( ENVIRONMENT == "production" )
                {
                    $path = "production/uploads/event/client_image2/{$data['id']}/{$data['image2']}";
                }
                else if( ENVIRONMENT == "testing" )
                {
                    //$path = "staging/uploads/event/client_image2/{$data['id']}/{$data['image2']}";
                    $path = "production/uploads/event/client_image2/{$data['id']}/{$data['image2']}";
                }
                else
                {
                    $path = "dev/uploads/event/client_image2/{$data['id']}/{$data['image2']}";
                }
            }

            //정방향도 없으면
            $fPath2 = 'https://s3.ap-northeast-1.amazonaws.com/image.goodoc/'.$path;
            $resultImage2 = $this->remoteFileExist($fPath2);

            if(!$resultImage2)
            {
                return false;
            }

            //s3 가져오기
            $return = $s3Client->getObject([
                'Bucket' => 'image.goodoc',
                'Key'    => $path
            ]);
        }
        else
        {
            if( ENVIRONMENT === 'development' )
            {
                //$bucketName = 'asset-dev.goodoc.kr';
                $bucketName = 'asset-staging.goodoc.kr';
            }
            else if( ENVIRONMENT === 'testing' )
            {
                $bucketName = 'asset-staging.goodoc.kr';
            }
            else if (ENVIRONMENT === 'production')
            {
                $bucketName = 'asset.goodoc.kr';
            }
            //$bucketName = 'asset.goodoc.kr';

            $s3Client = Aws\S3\S3Client::factory(array(
                'region' => 'ap-northeast-2',
                'version' => 'latest',
                'signature' => 'v4',
                'credentials' => array(
                    'key'    => S3Key,
                    'secret' => S3Secret
                )
            ));

            $path = $data['image2'];

            $resultImage2 = $this->remoteFileExist('https://s3.ap-northeast-2.amazonaws.com/'.$bucketName.'/'.$path);

            if(!$resultImage2)
            {
                return false;
            }

            if(strpos($path, 't1-s.png'))
            {
                return false;
            }

            if(strpos($path, 't2-s.png'))
            {
                return false;
            }

            //s3 가져오기
            $return = $s3Client->getObject([
                'Bucket' => $bucketName,
                'Key'    => $path
            ]);
        }

        return $return;
    }

    /**
     * TD에서 이벤트 뷰 카운트 받아서 배열화
     * 이벤트번호, 웹카운트, 앱카운트 형태
     * 5239,7287,3269
    9624,2008,570
    10276,471,754
     */
    function getS3Info()
    {
        $s3Client = Aws\S3\S3Client::factory(array(
            'region' => 'ap-northeast-1',
            'version' => 'latest',
            'signature' => 'v4',
            'credentials' => array(
                'key'    => 'AKIAJ56IRBSYEFJNTKQQ',
                'secret' => '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'
            )
        ));

        $path = 'event/event_view_count.csv.gz';
        $toFile = UP_ROOT.'/event_view_count.csv.gz';

        //s3 get
        $result = $s3Client->getObject([
            'Bucket' => 'external-ads',
            'Key'    => $path,
            'SaveAs' => $toFile
        ]);

        $row = 1;

        $content = file_get_contents($toFile);

        $content2 = gzdecode($content); //var_dump($content2); exit;

        $csv = explode("\n", $content2);

        foreach ($csv as $key => $line)
        {
            $csv[$key] = str_getcsv($line);
        }

        $csvOut = array_pop($csv);

        dd($csv);
    }

    /**
     * 원격지 파일 존재 여부 체크
     * @param $filepath
     * @return bool
     */
    function remoteFileExist($filepath)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,$filepath);
        curl_setopt($ch, CURLOPT_NOBODY, 1);
        curl_setopt($ch, CURLOPT_FAILONERROR, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        if(curl_exec($ch)!==false)
        {
            curl_close($ch);
            return true;
        }
        else
        {
            curl_close($ch);
            return false;
        }
    }

    /**
     * 수주계약번호에 해당하는 현재 잔액 구하기 -> 계약에 해당하는 현재 잔액 구하기로 변경
     * 1 수주, 2 계약충전, 3 소진, 4 기타충전, 5 환불수수료, 6 발행 환불, 7 계약 환불, 8 기타 소진, 9 발행취소, 10 계약취소,
     * 11 이월소진, 12 이월충전
     * @param $data
     * @return float
     */
    function getBalancePrice($data)
    {
        $checkDb = $this->isInTrans() === true ?  $this->master  :  $this->db;

        $sql = '
            select sum(price) chargePrice from deposit 
            where status in(2,4) 
            and contractId="'.$data['contractId'].'" 
            '; //충전금액
        $r1 = $checkDb->query($sql)->row_array();

        $sql2 = '
            select sum(price) usePrice from deposit 
            where status in(3,5,6,7,8)
            and contractId="'.$data['contractId'].'" 
            '; //소진금액
        $r2 = $checkDb->query($sql2)->row_array();

        return round($r1['chargePrice'] - $r2['usePrice']);
    }

    /**
     * 광고대행사 api key 리턴
     * @param $data
     * @return bool
     */
    function getAgencyInfo($data)
    {
        $this->db->select('key');
        $result = $this->db->get_where('api_key', ['name'=>$data['name'], 'userId'=>$data['userId']])->row_array(); //dd($result['key']);

        if(count($result) > 0)
        {
            return $result;
        }

        return false;
    }

    /**
     * 카테고리 리스트
     * @return mixed
     */
    function getCategoryList()
    {
        $sql = "
            select id, title, sort, is_visible as isVisible, image from  event_categories 
			where parent_id=0 and id>32
            order by sort;
        ";

        $result = $this->db->query($sql)->result_array();

        return $result;
    }

    /**
     * 카테고리 등록
     * @param $data
     * @return int
     */
    function setCategory($data)
    {
        $iArr = [
            'title'=>$data['title'],
            'is_visible'=>$data['is_visible'],
            'sort'=>$data['sort'],
            'image'=>$data['image'],
            'parent_id'=>$data['parent_id']
        ];

        $this->master->insert('event_categories', $iArr);
        $result = $this->master->insert_id();

        return $result;
    }

    /**
     * 카테고리 update
     * @param $data
     * @return int
     */
    function updateCategory($data)
    {
        $uArr = [
            'title'=>$data['title'],
            'is_visible'=>$data['is_visible'],
            'sort'=>$data['sort'],
            'image'=>$data['image'],
            'parent_id'=>$data['parent_id']
        ];

        $this->master->where('id', $data['id']);
        $this->master->update('event_categories', $uArr);
        $result = $this->master->affected_rows();

        return $result;
    }

    /**
     * 카테고리 delete
     * @param $data
     * @return int
     */
    function deleteCategory($id)
    {
        $this->master->where('id', $id);
        $this->master->delete('event_categories');
        $result = $this->master->affected_rows();

        return $result;
    }

    /**
     * 노출중인 하위카테고리가 존재하는지 체크
     * @param $data
     * @return int
     */
    function checkCategory($data)
    {
        $count = $this->db->get_where('event_categories', ['parent_id'=>$data['id'], 'is_visible'=>1])->num_rows();

        return $count;
    }

    function getCategoryInfo($data)
    {
        $sql = "
            select ec1.id, ec1.title, ec1.sort, ec1.is_visible as isVisible, ec1.image 
            ,ec2.id cId, ec2.title cTitle, ec2.sort cSort, ec2.is_visible as cIsVisible, ec2.image cImage
            from  event_categories ec1
            join event_categories ec2 on ec1.id=ec2.parent_id
			where ec1.id = '".$data['id']."' order by ec2.sort
        ";
        $result = $this->db->query($sql)->result_array();

        $return['parentInfo'] = [
            'isVisible'=>$result[0]['isVisible'],
            'title'=>$result[0]['title'],
            'image'=>$result[0]['image']
        ];

        foreach ($result as $item)
        {
            $return['childInfo'][] = [
                'id'=>$item['cId'],
                'isVisible'=>$item['cIsVisible'],
                'title'=>$item['cTitle'],
                'image'=>$item['cImage'],
                'sort'=>$item['cSort']
            ];
        }

        return $return;
    }

    /**
     * 추천 검색어 리스트
     * @return mixed
     */
    function getEventRecommendList()
    {
        $this->db->select('id, tag, sort');
        $this->db->order_by('sort');
        $count = $this->db->get('event_recommend_tags')->result_array();

        return $count;
    }

    /**
     * 추천검색어 등록
     * @param $data
     * @return int
     */
    function setEventRecommend($data)
    {
        $date = date("Y-m-d H:i:s");
        $this->db->select_max('sort');
        $max = $this->db->get('event_recommend_tags')->row_array();

        $iArr = [
            'tag'=>$data['tag'],
            'regDate'=>$date,
            'modDate'=>$date,
            'sort'=>$max['sort']+1
        ];

        //dd($iArr, false);

        $this->master->insert('event_recommend_tags', $iArr);
        $result = $this->master->insert_id();

        return $result;
    }

    /**
     * 추천검색어 삭제
     * @param $data
     * @return int
     */
    function deleteEventRecommend($data)
    {
        $this->master->where('id', $data['id']);
        $this->master->delete('event_recommend_tags');
        $result = $this->master->affected_rows();

        return $result;
    }

    /**
     * private s3 object 가져오기 (이벤트 히스토리 파일)
     * @param $bucketName
     * @param $path
     * @return \Aws\Result
     */
    function getPrivateS3Object($bucketName, $path)
    {
        $s3Client = Aws\S3\S3Client::factory(array(
            'region' => 'ap-northeast-2',
            'version' => 'latest',
            'signature' => 'v4',
            'credentials' => array(
                'key'    => S3Key,
                'secret' => S3Secret
            )
        ));

        $params = array(
            'Bucket'                        => $bucketName,
            'Key'                           => $path,
            'ResponseContentType'           => 'application/octet-stream',
            'ResponseContentDisposition'    => 'attachment; filename="index.html"',
        );



        $command = $s3Client->getCommand('GetObject',$params); // dd($command);
        $request = $s3Client->createPresignedRequest($command, "+30 minutes");
        return (string)$request->getUri();


//
//
//
//        //s3 가져오기
//        $return = $s3Client->getObject([
//            'Bucket' => $bucketName,
//            'Key'    => $path,
//            'ResponseContentType' => 'application/octet-stream',
//            'ResponseContentDisposition' => 'attachment; filename="index.html"'
//        ]);
//
//        return $return;
    }

    /**
     * 카테고리 depth 정보(카테고리명, 번호) 리턴
     * @param $category
     * @return mixed
     */
    function getCategoryDepth($category)
    {
        $sql = "
                select 
                ec.title as twoDepth, 
                ec2.title as oneDepth, 
                ec.id as twoDepthId, 
                ec2.id as oneDepthId 
                from event_categories ec 
                join event_categories ec2 on ec.parent_id=ec2.id
                where ec.id='".$category."' 
            ";
        $result = $this->db->query($sql)->row_array();

        return $result;
    }
}