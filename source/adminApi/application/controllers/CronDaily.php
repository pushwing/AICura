<?php

use Aws\S3\S3Client;

defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * 일일 cron
 * 모델 사용하지 않음
 * Class CronDaily
 */
class CronDaily extends CI_Controller
{
    function __construct()
    {
        parent::__construct();

        $this->load->helper('common');
        $this->master = $this->load->database('master', true);
        $this->load->model(['ads_m', 'replicator_m', 'common_m']);
    }

    /**
     * 1일 1회 시점잔액 동기화
     * 최초 실행시는 insert_batch 로 진행
     */
    function readyPrice()
    {
        //1 수주, 2 계약충전, 3 소진, 4 기타충전, 5 환불수수료, 6 발행 환불,
        // 7 계약 환불, 8 기타 소진, 9 발행취소, 10 계약취소, 11 이월 소진, 12 이월 충전, 13 세금계산서
        $modDate = date("Y-m-d H:i:s");
        $sql = "
            select 
            c.id, (select ifnull(sum(price), 0) from deposit where status in(2,4) and contractId=c.id) - (select ifnull(sum(price), 0) from deposit where status in(3,5,6,7,8) and contractId=c.id) as totalReady
            from contract c
            where c.adType=1  
        ";
        $result = $this->master->query($sql)->result_array();

        $sqlText = '';
        $data = [];
        foreach ($result as $item)
        {
            //$sqlText .= "insert into totalReadyPrice (contractId, totalReady, modDate) values ('".$item['id']."','".$item['totalReady']."','".$modDate."');";
            $data[] = ['contractId'=>$item['id'], 'totalReady'=>$item['totalReady'], 'modDate'=>$modDate];

        }

        //$this->master->insert_batch('total_ready_price', $data); //최초 1번 실행
        $this->master->update_batch('total_ready_price', $data, 'contractId');

        //echo $sqlText;
    }

    /**
     * 결번 번호 체크
     * 스윗트래커 카카오톡 비즈메시지 API v2.01.pdf 참고
     * array $data [['msgid'=>'goc_0001817478'], ['msgid'=>'gdc_0001817477'], ['msgid'=>'gdc_0001817476'], ['msgid'=>'gdc_0001817475']] 형태
     */
    function getWrongNumber($data)
    {
        $wrongNumberArray = [];

        $url = 'https://alimtalk-api.sweettracker.net/v1/1236a8ec5fb1516ab48d853378760d8aca3b7624/response';
        //$url = 'https://dev-alimtalk-api.sweettracker.net/v1/89823b83f2182b1e229c2e95e21cf5e6301eed98/response'; //개발

        $headers = array('Content-Type: application/json');
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 0);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);

        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $output = curl_exec($ch);

        if ($output === false) {
            $result = curl_error($ch);
        } else {
            $result = json_decode($output, true);
        }
        //dd($result, false);
        curl_close($ch);

        foreach ($result as $item)
        {
            dd($item, false);
            if(in_array($item['code'], ['M102', 'E104']))
            {
                $return = explode('_', $item['msgid']);

                $wrongNumberArray[] = $return[1];
            }
        }

        return $wrongNumberArray;
    }

    function testW()
    {
        $arr = [['msgid'=>'gdc_0001816461'], ['msgid'=>'gdc_0001816291']];
        $return = $this->getWrongNumber($arr);

        dd($return);
    }

    /**
     * 결번 처리 (실행주기 매일?)
     * lib/tasks/daily_tasks.rake
     * update_wrong_number
     */
    function updateWrongNumber()
    {
        $this->load->model('contractOrder_m');

        $dateBegin = date("Y-m-d", strtotime('-220 days'));
        //$dateBegin = date("Y-m-d", strtotime('-1 days'));
        $dateEnd = date("Y-m-d");

        $wrongNumberArray = [];
        $callRequestArray = [];

        $sql = "select callRequestId from call_request where regDate >= '".$dateBegin."' and regDate < '".$dateEnd."' and isDelete=0 and status not in(1,5,7,6,8)";
        $result = $this->master->query($sql)->result_array();

        $i=0;
        foreach ($result as $item)
        {
            //첫번째 인덱스는 건너뛰고 100번에 한번 실행
            if($i != 0 and $i%100==0)
            {
                //100개 배열 결번 체크하고 $callRequestArray 초기화
                $return = $this->getWrongNumber($callRequestArray);

                $wrongNumberArray[] = $return;
                $callRequestArray = [];
            }

            //100개 번호 배열에 담기
            $callRequestArray[] = ['msgid'=>'gdc_'.str_pad($item['callRequestId'], 10, "0", STR_PAD_LEFT)]; //10자리 만들고 0으로 채우기
            $i++;
        }

        //100개 이외 건수 처리
        if(count($callRequestArray) > 0)
        {
            $return = $this->getWrongNumber($callRequestArray);

            $wrongNumberArray[] = $return;
            $callRequestArray = [];
        }

        foreach ($wrongNumberArray as $it)
        {
            //callrequest, deposit, contract data 가져와서,
            $sql2 = "select contractId, contractOrderId, price from deposit where callRequestId='".$it."'";
            $result2 = $this->master->query($sql2)->row_array();

            if($result2['price'] != 0)
            {
                //deposit update
                $this->master->where('callRequestId', $it)->update('deposit', ['price'=>0]);

                //callrequest update
                $this->master->where('callRequestId', $it)->update('call_request', ['status'=>9]);

                //시점잔액 재계산
                $balancePrice = (int) $this->contractOrder_m->getBalancePrice(['contractId' => $result2['contractId']]);

                $data3 = [
                    'contractId'=>$result2['contractId'],
                    'totalReady'=>$balancePrice,
                    'type'=>1
                ];
                $this->common_m->updateTotalInfo($data3);
            }
        }
    }

    /**
     * 이벤트 마감 임박한 날짜 체크하여 슬랙 발송 - 작업 필요
     */
    function check_event_period()
    {
        /**
         * if eve.end_on == (Date.today + 1.days)
        if eve.is_temporary == false # 자동 OFF 이벤트
        send_text += "#{eve.id}:#{eve.title} 이벤트 종료가 하루 남았습니다.\n"
        text_count += 1
        end
        elsif eve.end_on == (Date.today + 3.days)
        send_text += "#{eve.id}:#{eve.title} 이벤트 종료가 3일 남았습니다. 종료일 : #{eve.end_on}\n"
        text_count += 1
        end

        if text_count > 10
        send_to_async_slack(send_text, eve.id)
        send_text = ""
        text_count = 0
        end
         */
    }

    /**
     * 광고주상태 구하기
     */
    function getAdvertiserStatus()
    {
        $this->load->model('contractOrder_m');
        //3개월전
        $now_date = date("Y-m-d",strtotime("-3 month"));
        $modDate = date("Y-m-d H:i:s");

        //$this->master->limit(3);
        $result = $this->master->get_where('contract', ['isDelete'=>'N'])->result_array();

        $data = [];

        foreach ($result as $item)
        {
            echo $item['id'],'<br>';
            //$contractOrderId = $this->contractOrder_m->getLastContractOrderId($item['id']);
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
                ) as hStatus, c.id,
                (select group_concat(distinct status) from deposit where contractId=c.id group by contractId) as depositStatusGroup
                from contract c
                JOIN `contract_order_connect` `coc` ON `c`.`id`=`coc`.`contractId`
                JOIN `contract_order` `co` ON `coc`.`contractOrderId`=`co`.`id`
                where c.id='".$item['id']."'
                group by c.hospitalId, c.id
                ";
            $result2 = $this->master->query($sql)->row_array();
            //var_dump($result2); exit;

            $data[] = ['contractId'=>$item['id'], 'advertiserStatus'=>$result2['hStatus'], 'modDate'=>$modDate, 'depositStatusGroup'=>$result2['depositStatusGroup']];
        }

        //var_dump($data); exit;
        $this->master->update_batch('total_ready_price', $data, 'contractId');

    }

    function index()
    {

        $fruits = array(
            "apple" => "yummy",
            "orange" => "ah ya, nice",
            "grape" => "wow, I love it!",
            "plum" => "nah, not me"
        );
        $obj = new ArrayObject( $fruits );
        $it = $obj->getIterator();

        // How many items are we iterating over?

        echo "Iterating over: " . $obj->count() . " values\n";

        // Iterate over the values in the ArrayObject:
        while( $it->valid() )
        {
            echo $it->key() . "=" . $it->current() . "\n";
            $it->next();
        }

        foreach ($it as $key=>$val)
            echo $key.":".$val."\n";

        exit;

        $this->load->helper('common');
        slackSend('1', '테스트입니다.');
    }

    /**
     * 계약 자동 연장
     * 당일 23시 30분에 해당일에 만료되는 이벤트(자동연장 가능한) 대상으로 1달 기간을 연장하고 메모를 남긴다.
     * 이벤트 수정 리플리케이터 호출하여 이벤트 날짜 수정한다.
     *
     */
    function extendEventDate()
    {
        $today = date("Y-m-d");
        //$today = '2019-02-13';

        //대상 가져오기
        $result = $this->master->get_where('ads', ['adDateExtend'=> 'Y', 'adEndDate'=> $today, 'isLive'=>'Y'])->result_array();

        //var_dump($result);

        $historyCheckArr = [];

        //트랜잭션 시작
        $this->master->trans_begin();

        foreach ($result as $item)
        {
            $adsInfoOld = $this->master->get_where('ads', ['id'=> $item['id']])->row_array();

            $sDate = $today; //오늘로 하지 않으면 잠깐의 갭이 생길 수 있다.
            $eDate = date("Y-m-d", strtotime("+1 month", strtotime($today)));

            $this->master->where('id', $item['id']);
            $this->master->update('ads', ['adStartDate'=>$sDate, 'adEndDate'=>$eDate]);

            $adsData = [
                'adsId'=>$item['id'],
                'client_start_on'=>$adsInfoOld['adStartDate'],
                'client_end_on'=>$adsInfoOld['adEndDate'],
                'start_on'=>$sDate,
                'end_on'=>$eDate,
            ];
            //리플리케이터
            //$return = $this->repli($item['id']);
            $return = $this->repliNew($adsData);

            if($return == false)
            {
                //롤백 및 db 입력, 이후 액션 중지
                $this->master->trans_rollback();

                $iArr = [
                    'adsId'=>$item['id'],
                    'result'=>2,
                    'regDate'=>$today
                ];
            }
            else
            {
                $historyCheckArr[$item['id']] = $sDate.'||'.$eDate.'||'.$item['dImageJson']; //d이미지 추가

                $this->master->trans_commit();

                //성공도 로그 입력
                $iArr = [
                    'adsId'=>$item['id'],
                    'result'=>1,
                    'regDate'=>$today
                ];
            }

            $this->master->insert('ads_extend_log', $iArr);
        }

        //메모리 주소 참조
        $Obj = & $this;
        $regDate = $today.' '.date('H:i:s');
        
        //히스토리 등록 및 정보 제너레이터 리턴
        $historyReturn = function (array & $historyCheckArr, string $regDate) use ( & $Obj )
        {
            foreach($historyCheckArr as $key => $val)
            {
                //히스토리 넣고
                $eDateArr = explode('||', $val);
                $param = ['adsId' => $key,  'adStartDate' => $eDateArr[0], 'adEndDate' => $eDateArr[1], 'userId' => 1, 'regDate' => $regDate];
                $param['deletejson'] = json_encode($param); 
                $param['dImageJson'] = $eDateArr[2];  //이미지는 왜 맨날 넣게 해 놨는지?
                $historyId = $Obj->ads_m->setHistory($param);
                
                //제너레이터로 리턴
                yield  $historyId => $Obj->ads_m->gethistoryMerge(['adsId' => $key]);
                unset($historyCheckArr[$key]); 
            }
        };

        //echo 'history start<br>';
        //key = historyId, $val = historyInfo
        foreach($historyReturn($historyCheckArr, $regDate) as $key => $val )
        {
            $adsInfo = $this->master->get_where('ads_history', ['id'=>$key])->row_array();

            //광고 JSON 업데이트, 이미지는 변경되지않았고 별도 필드라 굳이 없데이트할 필요 없다.
            $this->master->where('id', $adsInfo['adsId']);
            $this->master->update('ads', ['adsHistoryJson' => json_encode($val, JSON_UNESCAPED_UNICODE)]);
            var_dump(json_encode($val, JSON_UNESCAPED_UNICODE));
        } 
        unset($historyReturn, $mainAndInspect);
        //echo 'history end<br>';

    }

    /**
     * 계약 자동 연장 재실행
     * 당일 23시 50분에 당일 실패한 이벤트를 대상으로 1달 기간을 연장하고 메모를 남긴다.
     * 이벤트 수정 리플리케이터 호출하여 이벤트 날짜 수정한다.
     * cronDaily/reExtendEventDate/one  23시 50분
     * cronDaily/reExtendEventDate/two  23시 59분
     */
    function reExtendEventDate()
    {
        $this->load->library('curl');

        $type = $this->uri->segment(3, 'one');

        //횟수에 따라 분기
        if($type == 'one')
        {
            //재실행
            $reCount = 0;
        }
        else
        {
            //2회째 시도. 실패하면 더이상 진행하지 않고 슬랙만 전송
            $reCount = 1;
        }

        $today = date("Y-m-d");
        $todayTime = date("Y-m-d H:i:s");

        //대상 가져오기
        $this->master->select('ads.*');
        $this->master->join('ads', 'ael.ads_id=ads.id');
        $result = $this->master->get_where('ads_extend_log ael', ['ael.result'=> 2, 'ael.regDate'=> $today, 'ael.reCount'=>$reCount])->result_array();

        if(count($result) == 0)
        {
            echo '대상 없음';
            exit;
        }

        $historyCheckArr = [];

        //트랜잭션 시작
        $this->master->trans_begin();

        foreach ($result as $item)
        {
            $trueText = true;

            $adsInfoOld = $this->master->get_where('ads', ['id'=> $item['id']])->row_array();

            $sDate = $today; //오늘로 하지 않으면 잠깐의 갭이 생길 수 있다.
            $eDate = date("Y-m-d", strtotime("+1 month", strtotime($today)));

            $this->master->where('id', $item['id']);
            $this->master->update('ads', ['adStartDate'=>$sDate, 'adEndDate'=>$eDate]);

            $adsData = [
                'adsId'=>$item['id'],
                'client_start_on'=>$adsInfoOld['adStartDate'],
                'client_end_on'=>$adsInfoOld['adEndDate'],
                'start_on'=>$sDate,
                'end_on'=>$eDate,
            ];
            //리플리케이터
            //$return = $this->repli($item['id']);
            $return = $this->repliNew($adsData);

            if($return == false)
            {
                //롤백 및 db 입력, 이후 액션 중지
                $this->master->trans_rollback();

                $uArr = [
                    'reCount'=> $reCount + 1, //타입에 따라 재실행 횟수 입력
                    'result'=>2,
                    'reOneDate'=>$todayTime
                ];

                $trueText = false;
            }
            else
            {
                $historyCheckArr[$item['id']] = $sDate.'||'.$eDate.'||'.$item['dImageJson']; //d이미지 추가

                $this->master->trans_commit();

                //성공도 로그 입력
                $uArr = [
                    'reCount'=> $reCount+1,
                    'result'=>1,
                    'reOneDate'=>$todayTime
                ];
            }

            $this->master->where('adsId', $item['id']);
            $this->master->update('ads_extend_log', $uArr);

            if($trueText == false and $type != 'one')
            {
                //2회 실패이면 슬랙발송
                $message = $item['id'].' 번 이벤트 자동연장 2회 실패';

                $slackWebhookUrl="https://hooks.slack.com/services/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";

                //발송자
                $slackUsername="이벤트자동연장";

                //채널
                $slackChannel = "#bizma_event_history";

                $array=json_encode(["channel"=>$slackChannel, "username"=> $slackUsername, "text"=> $message]);
                $payload = "payload=".$array;

                $this->curl->simple_post($slackWebhookUrl, $payload);
            }
        }

        //메모리 주소 참조
        $Obj = & $this;
        $regDate = $today.' '.date('H:i:s');

        //히스토리 등록 및 정보 제너레이터 리턴
        $historyReturn = function (array & $historyCheckArr, string $regDate) use ( & $Obj )
        {
            foreach($historyCheckArr as $key => $val)
            {
                //히스토리 넣고
                $eDateArr = explode('||', $val);
                $param = ['adsId' => $key,  'adStartDate' => $eDateArr[0], 'adEndDate' => $eDateArr[1], 'userId' => 1, 'regDate' => $regDate];
                $param['deletejson'] = json_encode($param);
                $param['dImageJson'] = $eDateArr[2];  //이미지는 왜 맨날 넣게 해 놨는지?
                $historyId = $Obj->ads_m->setHistory($param);

                //제너레이터로 리턴
                yield  $historyId => $Obj->ads_m->gethistoryMerge(['adsId' => $key]);
                unset($historyCheckArr[$key]);
            }
        };

        foreach($historyReturn($historyCheckArr, $regDate) as $key => $val )
        {
            $adsInfo = $this->master->get_where('ads_history', ['id'=>$key])->row_array();

            //광고 JSON 업데이트, 이미지는 변경되지않았고 별도 필드라 굳이 없데이트할 필요 없다.
            $this->master->where('id', $adsInfo['adsId']);
            $this->master->update('ads', ['adsHistoryJson' => json_encode($val, JSON_UNESCAPED_UNICODE)]);
            var_dump(json_encode($val, JSON_UNESCAPED_UNICODE));
        }
        unset($historyReturn, $mainAndInspect);
    }

    /**
     * 계약 자동 종료
     * 당일 23시 58분에 해당일에 만료되는 이벤트(자동연장 안되는)를 종료한다.
     * 이벤트 바로종료 리플리케이터 호출하여 이벤트 종료처리한다.
     *
     */
    function endEventDate()
    {
        $today = date("Y-m-d");
        //$today = '2019-02-13';

        //대상 가져오기
        $result = $this->db->get_where('ads', ['adDateExtend'=> 'N', 'adEndDate'=> $today, 'isLive'=>'Y'])->result_array();

        //var_dump($result);
        foreach ($result as $item)
        {
            $data['type'] = 1; //바로종료
            $data['users_id'] = 1; //바로종료
            $data['adsId'] = $item['id'];
            $data['isHospital'] = 'N';

            //바로 종료 처리. 리플리케이터까지 되어 있음
            echo 'model start<br>';
            $this->ads_m->updateListAction($data);
            echo 'model end<br>';
        }
    }

    /**
     * 날짜 변경과 바로승인만 호출하는 리플리케이터
     * @param $adsId
     * @return bool
     * @throws Exception
     */
    function repliNew($data)
    {
        //리플리케이터 시작
        //이벤트 날짜 수정 처리
        $insData = [
            'type_info'=>'event_period',
            'client_start_on'=>$data['start_on'],
            'client_end_on'=>$data['end_on'],
            'start_on'=> $data['start_on'],
            'end_on'=>$data['end_on']
        ];
        monologSend('event_peroid', json_encode($data));
        monologSend('event_peroid', json_encode($insData));
        $result99 = $this->replicator_m->send('/api/events/'.$data['adsId'], 'PATCH', $insData);
        monologSend('event_peroid', $result99);

        //이미 진행중인 것이라 승인이 필요없다.
//        //바로승인
//        $insData2 = [
//            'type_info'=>'force_live'
//        ];
//        monologSend('inspect', json_encode($data));
//        monologSend('inspect', json_encode($insData2));
//        $result00 = $this->replicator_m->send('/api/events/'.$data['adsId'], 'PATCH', $insData2);
//        monologSend('inspect', $result00);
//
//        //리플리케이터 종료
//
//        $r99 = $r00 = true;

        if($result99['message'] != 'success' or $result99 == 'Empty reply from server')
        {
            return false;
        }

//        if($result99['message'] != 'success' or $result99 == 'Empty reply from server')
//        {
//            $r00 = false;
//        }
//
//        if($r99 == false or $r00 == false)
//        {
//            return false;
//        }

        return true;
    }

    /**
     * 이벤트 수정 리플리케이터 호출
     * @param $adsId
     * @throws Exception
     */
    function repli($adsId)
    {
        //리플리케이터 시작
        //최신 광고정보 가져오기
        $this->master->select('ads.*');
        $this->master->select('vRegion as region, vCooperation as cooperation, vKeyword as keyword');

        $adsCols = 'ads.vT1ImageName as t1, ads.vT2ImageName as t2';
        $adsCols .= ', ads.vOptions as options';

        $this->master->select($adsCols);

        $newInfo = $this->master->get_where('ads', ['ads.id'=>$adsId])->row_array(); //var_dump($newInfo); //exit;

        //노출영역 처리
        $isE = '0';
        $isH = '0';
        if($newInfo['exposure'] == 3)
        {
            //둘다 라면
            $isE = '1';
            $isH = '1';
        }
        else if($newInfo['exposure'] == 2)
        {
            $isE = '0';
            $isH = '1';
        }
        else if($newInfo['exposure'] == 1)
        {
            $isE = '1';
            $isH = '0';
        }

        $iii=1;
        $dImageArr = [];
        $iArr3 = json_decode($newInfo['dImageJson'], JSON_UNESCAPED_UNICODE);

        foreach ($iArr3 as $item22)
        {
            $dImageArr[] = ['client_sort'=>$iii, 'client_image'=>$item22];

            $iii++;
        }

        if(count($dImageArr) > 0)
        {
            $dImageArrJson = json_encode($dImageArr);
        }
        else
        {
            $dImageArrJson = ''; //값이 없다면 빈값으로......
        }

        //t1, t2 처리
        $t1ImageName = $newInfo['t1'];
        $t2ImageName = $newInfo['t2'];

        //is_client_image2_change 정방향 이미지 변경여부 체크
        //변경이 없으면
        $is_client_image2_change = 0;

        //옵션처리
        $oArr = [];
        if($newInfo['options'] !== '')
        {
            $subArr = explode(',', $newInfo['options']);

            foreach ($subArr as $item3)
            {
                $this->master->select('adTitle');
                $rTitle = $this->master->get_where('ads', ['id'=>$item3])->row_array();
                $oArr[] = ['event_id'=>$item3, 'label'=>$rTitle['adTitle']];
            }

            if(count($oArr) > 0)
            {
                $oArrJson = json_encode($oArr);
            }
            else
            {
                $oArrJson = ''; //값이 없다면 빈값으로......
            }
        }
        else
        {
            $oArrJson = '';
        }

        $reAdDetailInfo = json_decode($newInfo['adDetailInfo']);

        $reApply_text   = isset($reAdDetailInfo[0]) && $reAdDetailInfo[0] != ''  ? $reAdDetailInfo[0] : '이벤트 신청하기';
        $reApply_back_color   = isset($reAdDetailInfo[4]) && $reAdDetailInfo[4] != ''  ? $reAdDetailInfo[4] : '#1662bb';
        $reApply_text_color  = isset($reAdDetailInfo[2]) && $reAdDetailInfo[5] != ''  ? $reAdDetailInfo[5] : '#ffffff';

        //커스텀 랜딩처리
        //if($data['custom1'] and $data['custom2'] and $data['custom3'])
        $customDataArr = [];
        $checkCustomArr = [
            'custom1' => 'leader_name'
            , 'custom2' => 'operation_register_name'
            , 'custom3' => 'contact'
        ];

        $encodeCheck = false;
        foreach($checkCustomArr  as $key => $val)
        {
            if( isset($newInfo[$key]) && $newInfo[$key] !== '')
            {
                $customDataArr[$val] = $newInfo[$key];
                $encodeCheck = true;
            }
            else
            {
                $customDataArr[$val] = '';
            }
        }

        if(@$customDataArr['leader_name'] == '' and @$customDataArr['operation_register_name'] == '' and @$customDataArr['contact'] == '')
        {
            $encodeCheck = false;
        }

        $customData = $encodeCheck === true ? json_encode($customDataArr)  : '';

        $reGeneralCost = isset($newInfo['generalCost']) && !empty($newInfo['generalCost']) ? $newInfo['generalCost'] : 0;
        $reGeneralCost = is_null($reGeneralCost) ? 0 : $reGeneralCost;

        $reDiscountCost = isset($newInfo['discountCost']) && !empty($newInfo['discountCost']) ? $newInfo['discountCost'] : 0;
        $reDiscountCost = is_null($reDiscountCost) ? 0 : $reDiscountCost;

        //이벤트 수정처리
        $insData = [
            'type_info'=>'admin_info',
            'contract_ids'=>$newInfo['contractId'],
            'client_searchable'=>1,
            'client_title'=>$newInfo['adTitle'],
            'client_is_temporary'=> ($newInfo['adDateExtend']=='Y')?'1':'0', // 1 상시진행, 0 기간설정
            'client_start_on'=>$newInfo['adStartDate'],
            'client_end_on'=>$newInfo['adEndDate'],
            'client_is_numerical_original_price'=>($newInfo['costType'] == 1)?'1':'0', //0이면 텍스트가격
            'client_numerical_original_price'=>$reGeneralCost,
            'client_original_price'=>($newInfo['costType'] == 1)?$reGeneralCost:0,
            'client_is_numerical_discounted_price'=>($newInfo['costType'] == 1)?'1':'0',
            'client_numerical_discounted_price'=>$reDiscountCost,
            'client_discounted_price'=>($newInfo['costType'] == 1)?$reDiscountCost:$newInfo['textCost'],
            'hospital_id'=>$newInfo['hospitalId'],
            'event_infos'=>$dImageArrJson,
            'client_event_category_ids'=>$newInfo['category'],
            'client_image2'=>$t2ImageName,
            'client_image'=>$t1ImageName,
            'search_tags'=>$newInfo['keyword'],
            'client_consider_number'=>is_null($newInfo['deliberationCode'])?'':$newInfo['deliberationCode'], //의료심의번호
            'model_image_ids'=>(is_null($newInfo['whereImage']))?'':$newInfo['whereImage'],
            'external_media_category_ids'=>$newInfo['cooperation'],
            'event_cost'=>$newInfo['dbCost'],
            'apply_text'=> $reApply_text,
            'apply_text_color' => $reApply_text_color, //버튼 텍스트 컬러
            'apply_back_color' => $reApply_back_color, //버튼 컬러
            'event_regions'=>$newInfo['region'],
            'is_visible_on_event_list'=>$isE,
            'is_visible_on_hospital_show'=>$isH,
            'apply_image_count'=>(is_null($newInfo['modelImageCount']))?'0':$newInfo['modelImageCount'],
            'option_event_infos'=>$oArrJson,
            'hospital_operator_infos'=>$customData
        ];
        monologSend('event_modify', json_encode($insData));
        $result99 = $this->replicator_m->send('/api/events/'.$newInfo['id'], 'PATCH', $insData);
        monologSend('event_modify', $result99);

        //이벤트 승인처리
        $insData2 = [
            'type_info'=>'event_confirm',
            'event_cost'=>$newInfo['dbCost'],
            'event_regions'=>$newInfo['region'],
            'apply_text'=> $reApply_text, //이벤트 등록, 후기 뷰의 워딩과 동일하게 처리
            'is_visible_on_event_list'=>$isE,
            'is_visible_on_hospital_show'=>$isH,
            'client_event_category_ids'=>$newInfo['category'],
            'search_tags'=>$newInfo['keyword'],
            'external_media_category_ids'=>$newInfo['cooperation'],
            'is_client_image2_changed'=>$is_client_image2_change
        ];
        monologSend('inspect', json_encode($insData2));
        $result00 = $this->replicator_m->send('/api/events/'.$newInfo['id'], 'PATCH', $insData2);
        monologSend('inspect', $result00);

        //리플리케이터 종료

        $r99 = $r00 = true;

        if($result99['message'] != 'success' or $result99 == 'Empty reply from server')
        {
            $r99 = false;
        }

        if($result99['message'] != 'success' or $result99 == 'Empty reply from server')
        {
            $r00 = false;
        }

        if($r99 == false or $r00 == false)
        {
            return false;
        }

        return true;
    }

    /**
     * 후기 정렬 생성 - 사용안함
     * 1: 후기존 (파일수, 내용, 시간점수)
     * 2: 이벤트상세 (시간점수 대신 평점)
     * 정보보기 / 정보받기 / 좋아요(현재 없는 기능) : 고유액션당 +1
    댓글 (현재 없는 기능) : 고유액션당 +2
    Negative 점수
    신고버튼 : -1점
     */
    function boardRank()
    {
        $type = $this->uri->segment(3, 1);

        $orderDate = date("Ymd", strtotime('+9 times')); // 9시간후의 날짜로 셋팅
        $regDate = date("Y-m-d H:i:s");

        $this->master = $this->load->database('master', true);

        if($type == 1)
        {
            //후기존
            $checkData = $this->db->get_where('board_rank', ['orderDate'=>$orderDate, 'type'=>$type])->num_rows();

            if(!$checkData)
            {
                //내용 구간 점수 도입, 가슴성형 카테고리 제외
                $sql = '
                select *, (contentScore + fileScore - sigm) as orderByMe 
                    from (
                         select *,
                         if(fileCount <= 2, (fileCount*5), 12) as fileScore,
                         case
                         when uText >= 1 and uText < 6
                         then 1
                         when uText >=6 and uText < 11
                         then 2
                         when uText >= 10 and uText < 16
                         then 3
                         when uText >= 16 and uText < 21
                         then 4
                         when uText >= 21
                         then 5
                         else 0
                         end as contentScore,                         
                         sigmoid2((unix_timestamp()-unix_timestamp(regDate))/16000, 0.0079315)*10 as sigm
                         from (
                         select b.id as boardId, b.contents, b.regDate, ads.category, 
                         (select count(*) from board_files where boardId=b.id) as fileCount,
                         CHAR_LENGTH(urldecode3(contents)) as uText
                         from board b
                         join ads on b.targetId=ads.id
                         where b.isDelete = 0 and b.type = 1 and ads.category != 57
                         group by b.id 
                         ) aa
                    ) bb  
                order by orderByMe desc, boardId desc
                ';
                $result = $this->db->query($sql)->result_array();

                $i=1;
                foreach ($result as $item)
                {

                    $iArr = [
                        'type' => $type,
                        'orderDate' => $orderDate,
                        'boardId' => $item['boardId'],
                        'orderBy' => $i,
                        'regDate' => $regDate
                    ];
                    $this->master->insert('board_rank', $iArr);

                    $i++;
                }

                echo $orderDate.' end';
            }
            else
            {
                echo 'duplicated date - '.$orderDate;
            }
        }
        else
        {
            //이벤트 상세
            $checkData = $this->db->get_where('board_rank', ['orderDate'=>$orderDate, 'type'=>$type])->num_rows();

            if(!$checkData)
            {
                $sql = '
            
                select *, (contentScore + fileScore + (rateSum*rateScore)) as orderByMe
                from (
                         select *, 
                         if(fileCount <= 2, (fileCount*5), 12) as fileScore,
                         case
                         when uText >= 1 and uText < 6
                         then 1
                         when uText >=6 and uText < 11
                         then 2
                         when uText >= 10 and uText < 16
                         then 3
                         when uText >= 16 and uText < 21
                         then 4
                         when uText >= 21
                         then 5
                         else 0
                         end as contentScore,  
                         if(rateSum <= 6, 0, if(rateSum < 8.7, 1.1, 1.2)) rateScore
                         from (
                         select b.id as boardId, b.contents, b.rateSum,  
                         (select count(*) from board_files where boardId=b.id) as fileCount,
                         CHAR_LENGTH(urldecode3(contents)) as uText
                         from board b
                         join ads on b.targetId=ads.id
                         where b.isDelete = 0 and b.type = 1 and ads.category != 57
                         group by b.id
                         ) aa
                    ) bb  
                order by orderByMe desc, boardId desc
        ';
                $result = $this->db->query($sql)->result_array();

                $i=1;
                foreach ($result as $item)
                {

                    $iArr = [
                        'type' => $type,
                        'orderDate' => $orderDate,
                        'boardId' => $item['boardId'],
                        'orderBy' => $i,
                        'regDate' => $regDate
                    ];
                    $this->master->insert('board_rank', $iArr);

                    $i++;
                }

                echo $orderDate.' end';
            }
            else
            {
                echo 'duplicated date - '.$orderDate;
            }
        }
    }

    /**
     * 누락된 신청db 를 입력하는 프로그램
     * 신청db 및 원장입력
     */
    function insertCallRequest()
    {
        $this->v2 = $this->load->database('goodocV2', true); //v2 운영
        $this->v1 = $this->load->database('goodoc', true); //v1 운영
        $this->load->model('contractOrder_m');

        $a = '1582159, 1582233';
        $arr = explode(',', $a);

        //신청db
        set_time_limit(0);
        ini_set('memory_limit','-1');

        //call_requests insert
//        $sql999 = "select * from call_requests where id in (".$a.")";
//        $pArr = $this->v1->query($sql999)->result_array();
//        foreach ($pArr as $it)
//        {
//            $type = $this->typeChange($it['status']);
//            $iArr = [
//                'callRequestId'=>$it['id'],
//                'hospitalId'=>$it['hospital_id'],
//                'adsId'=>$it['event_id'],
//                'userId'=>$it['user_id'],
//                'device'=>$it['device'],
//                'status'=>$type,
//                'confirmDate'=>date("Y-m-d H:i:s", strtotime("-9 hours" , strtotime($it['confirmed_at']))),
//                'regDate'=>date("Y-m-d H:i:s", strtotime("-9 hours" , strtotime($it['created_at']))),
//                'modifyDate'=>date("Y-m-d H:i:s", strtotime("-9 hours" , strtotime($it['updated_at']))),
//                'name'=>$it['name'],
//                'phone'=>$it['phone'],
//                'content'=>$it['content'],
//                'privacyAgree'=>$it['privacy_agree'],
//                'funnel'=>$it['funnel'],
//                'eventCost'=>$it['event_cost'],
//                'callTime'=>$it['call_time'],
//                'isMigration'=>$it['is_migration'],
//                'age'=>$it['age'],
//                'sex'=>$it['sex'],
//                'onlySms'=>$it['only_sms'],
//                'parentId'=>$it['parent_id'],
//                'messageId'=>$it['message_id'],
//                'isDelete'=>$it['is_deleted'],
//                'supplyThirdPartyAgree'=>$it['supply_third_party_agree'],
//                'fingerPrint'=>$it['finger_print'],
//                'region'=>$it['region'],
//                'isSavePhone'=>$it['is_save_phone']
//            ]   ;
//            $this->v2->insert('call_request', $iArr);
//        }

        //원장 입력
        $sql999 = "select p.* from payments p
              where p.call_request_id in (".$a.") 
              order by created_at
              "; //echo $sql999; order by payment_type, created_at
        $pArr2 = $this->v1->query($sql999)->result_array();

        foreach ($pArr2 as $item)
        {
            //최근 수주계약번호 구하기
            $contractOrderId= $this->contractOrder_m->getLastContractOrderId($item['contract_id']);
            $status = $this->paymentType($item['payment_type']);
            $iArr2 = [
                'status'=>$status['status'],
                'isMinus'=>$status['minus'],
                'contractId'=>$item['contract_id'],
                'contractOrderId'=>$contractOrderId,
                'usersId'=>1,
                'memo'=>$item['memo'],
                'price'=>is_null($item['price'])? 0 : $item['price'],
                'regDate'=>date("Y-m-d H:i:s", strtotime("-9 hours" , strtotime($item['created_at']))),
                'modDate'=>date("Y-m-d H:i:s", strtotime("-9 hours" , strtotime($item['updated_at']))),
                'callRequestId'=>($item['call_request_id'])?$item['call_request_id']:0
            ]   ;
            var_dump($iArr2);
            $this->v2->insert('deposit', $iArr2);
        }
    }

    /**
     * 신청db 상태 치환 v1 -> v2
     */
    function typeChange($type)
    {
        $ret = '';
        switch ($type)
        {
            case '0':
                $ret = 1;
                break;
            case '1':
                $ret = 1;
                break;
            case '2':
                $ret = 7;
                break;
            case '3':
                $ret = 3;
                break;
            case '4':
                $ret = 5;
                break;
            case '5':
                $ret = 7;
                break;
            case '6':
                $ret = 9;
                break;
            case '7':
                $ret = 2;
                break;
            case '8':
                $ret = 4;
                break;
            case '9':
                $ret = 6;
                break;
            case '10':
                $ret = 8;
                break;
        }
        return $ret;
    }

    /**
     * 1 수주, 2 계약충전, 3 소진, 4 기타충전, 5 환불수수료, 6 발행 환불, 7 계약 환불, 8 기타 소진, 9 발행취소, 10 계약취소, 11 이월 소진, 12 이월 충전, 13 세금계산서
     * @param $type
     * @return mixed
     */
    function paymentType($type)
    {
        switch ($type)
        {
            case 0:
                $rt['status'] = 2;
                $rt['memo'] = '최초 충전-';
                $rt['minus'] = 0;
                break;
            case 1:
                $rt['status'] = 2;
                $rt['memo'] = '재계약 충전-';
                $rt['minus'] = 0;
                break;
            case 2:
                $rt['status'] = 4;
                $rt['memo'] = '서비스충전-';
                $rt['minus'] = 0;
                break;
            case 3:
                $rt['status'] = 3;
                $rt['memo'] = '';
                $rt['minus'] = 1;
                break;
            case 4:
                $rt['status'] = 4;
                $rt['memo'] = '중복db 서비스충전-';
                $rt['minus'] = 0;
                break;
            case 5:
                $rt['status'] = 4;
                $rt['memo'] = '기타충전-';
                $rt['minus'] = 0;
                break;
            case 6:
                $rt['status'] = 8;
                $rt['memo'] = '기타차감-';
                $rt['minus'] = 1;
                break;
            case 7:
                $rt['status'] = 4;
                $rt['memo'] = '클라이언트충전-';
                $rt['minus'] = 0;
                break;
            case 8:
                $rt['status'] = 8;
                $rt['memo'] = 'cpm차감-';
                $rt['minus'] = 0;
                break;
            case 9:
                $rt['status'] = 6; //발행환불 6, 계약환불 7, 계약환불 케이스는 없다고 가정함
                $rt['memo'] = '환불-';
                $rt['minus'] = 1;
                break;
        }
        return $rt;
    }

    /**
     * td 데이터(s3파일)을 읽어서 db에 저장
     * 이벤트 일간 뷰, 주간 뷰 랭킹정보
     */
    function setS3EventCount()
    {
        $type = $this->uri->segment(3, 'daily');

        $s3Client = Aws\S3\S3Client::factory(array(
            'region' => 'ap-northeast-1',
            'version' => 'latest',
            'signature' => 'v4',
            'credentials' => array(
                'key'    => S3Key,
                'secret' => S3Secret
            )
        ));

        $path = 'ranking/'.$type.'.csv';
        $toFile = UP_ROOT.'/'.$type.'.csv';

        //s3 get
        $result = $s3Client->getObject([
            'Bucket' => 'event-ranking',
            'Key'    => $path,
            'SaveAs' => $toFile
        ]);

        $content = file_get_contents($toFile);

        $csv = explode("\n", $content);

        foreach ($csv as $key => $line)
        {
            //$csv[$key] = str_getcsv($line);
            $csv = str_getcsv($line);

            $iArr = [
                'adsId' => $csv[0],
                'count' => $csv[1],
                'rank' => $csv[2],
                'date' => $csv[3]
            ];

            if(@$csv[0] != '')
            {
                $this->master->insert('ads_'.$type.'_ranking', $iArr);
            }

        }
    }
}