<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class BoardMigration extends CI_Controller
{
    function __construct()
    {
        parent::__construct();

        $this->thisVersion = 2; //180515 버전 1로 시작
        $this->surveryArray[$this->thisVersion] = [
            's1'=>[
                [
                    'use'=>'Y',
                    'title'=>'상담',
                    'question'=>'상담은 만족하였나요?',
                    'sendType'=>0,
                    'answer'=>[
                        ['name'=>'만족', 'type'=>'0'],
                        ['name'=>'보통',  'type'=>'0'],
                        ['name'=>'아쉬움', 'type'=>'0']
                    ]
                ],
                [
                    'use'=>'N',
                    'title'=>'시술시기',
                    'question'=>'시술 한 지 얼마나 되셨나요?',
                    'sendType'=>0,
                    'answer'=>[
                        ['name'=>'일주일 이내','type'=>'0'],
                        ['name'=>'한 달 이내', 'type'=>'0'],
                        ['name'=>'6개월 이내', 'type'=>'0'],
                        ['name'=>'1년 이상 경과', 'type'=>'0'],
                        ['name'=>'직접 입력', 'type'=>'2']
                    ]
                ],
                [
                    'use'=>'Y',
                    'title'=>'개선시기',
                    'question'=>'효과는 언제부터 나타났나요?',
                    'sendType'=>0,
                    'answer'=>[
                        ['name'=>'즉시 개선', 'type'=>'0'],
                        ['name'=>'일주일', 'type'=>'0'],
                        ['name'=>'한달', 'type'=>'0'],
                        ['name'=>'효과없음', 'type'=>'0']
                    ]
                ],
                [
                    'use'=>'Y',
                    'title'=>'시술효과',
                    'question'=>'시술 효과는 만족스러우신가요?',
                    'sendType'=>0,
                    'answer'=>[
                        ['name'=>'만족', 'type'=>'0'],
                        ['name'=>'보통', 'type'=>'0'],
                        ['name'=>'아쉬움', 'type'=>'0']
                    ]
                ],
                [
                    'use'=>'Y',
                    'title'=>'지인추천',
                    'question'=>'주변에 추천해 줄 의향이 있나요?',
                    'sendType'=>0,
                    'answer'=>[
                        ['name'=>'추천', 'type'=>'0'],
                        ['name'=>'보통', 'type'=>'0'],
                        ['name'=>'추천안함', 'type'=>'0']
                    ]
                ]
            ],
            's2'=>[
                [
                    'use'=>'Y',
                    'title'=>'상담',
                    'question'=>'상담은 만족하였나요?',
                    'sendType'=>0,
                    'answer'=>[
                        ['name'=>'만족', 'type'=>'0'],
                        ['name'=>'보통', 'type'=>'0'],
                        ['name'=>'아쉬움', 'type'=>'0']
                    ]
                ],
                [
                    'use'=>'N',
                    'title'=>'시술시기',
                    'question'=>'시술 한 지 얼마나 되셨나요?',
                    'sendType'=>0,
                    'answer'=>[
                        ['name'=>'일주일 이내','type'=>'0'],
                        ['name'=>'한 달 이내', 'type'=>'0'],
                        ['name'=>'6개월 이내', 'type'=>'0'],
                        ['name'=>'1년 이상 경과', 'type'=>'0'],
                        ['name'=>'직접 입력', 'type'=>'2']
                    ]
                ],
                [
                    'use'=>'Y',
                    'title'=>'지인추천',
                    'question'=>'주변에 추천해 줄 의향이 있나요?',
                    'sendType'=>0,
                    'answer'=>[
                        ['name'=>'추천', 'type'=>'0'],
                        ['name'=>'보통', 'type'=>'0'],
                        ['name'=>'추천안함', 'type'=>'0']
                    ]
                ]
            ],
            's3'=>[
                [
                    'use'=>'Y',
                    'title'=>'상담',
                    'question'=>'상담은 만족하였나요?',
                    'sendType'=>0,
                    'answer'=>[
                        ['name'=>'만족', 'type'=>'0'],
                        ['name'=>'보통', 'type'=>'0'],
                        ['name'=>'아쉬움', 'type'=>'0']
                    ]
                ],
                [
                    'use'=>'N',
                    'title'=>'시술시기',
                    'question'=>'시술 한 지 얼마나 되셨나요?',
                    'sendType'=>0,
                    'answer'=>[
                        ['name'=>'일주일 이내','type'=>'0'],
                        ['name'=>'한 달 이내', 'type'=>'0'],
                        ['name'=>'6개월 이내', 'type'=>'0'],
                        ['name'=>'1년 이상 경과', 'type'=>'0'],
                        ['name'=>'직접 입력', 'type'=>'2']
                    ]
                ],
                [
                    'use'=>'Y',
                    'title'=>'개선시기',
                    'question'=>'효과는 언제부터 나타났나요?',
                    'sendType'=>0,
                    'answer'=>[
                        ['name'=>'즉시 개선', 'type'=>'0'],
                        ['name'=>'일주일', 'type'=>'0'],
                        ['name'=>'한달', 'type'=>'0'],
                        ['name'=>'효과없음', 'type'=>'0']
                    ]
                ],
                [
                    'use'=>'Y',
                    'title'=>'시술효과',
                    'question'=>'시술 효과는 만족스러우신가요?',
                    'sendType'=>0,
                    'answer'=>[
                        ['name'=>'만족', 'type'=>'0'],
                        ['name'=>'보통', 'type'=>'0'],
                        ['name'=>'아쉬움', 'type'=>'0']
                    ]
                ],
                [
                    'use'=>'Y',
                    'title'=>'지인추천',
                    'question'=>'주변에 추천해 줄 의향이 있나요?',
                    'sendType'=>0,
                    'answer'=>[
                        ['name'=>'추천', 'type'=>'0'],
                        ['name'=>'보통', 'type'=>'0'],
                        ['name'=>'추천안함', 'type'=>'0']
                    ]
                ]
            ],
            's4'=>[
                [
                    'use'=>'Y',
                    'title'=>'상담',
                    'question'=>'상담은 만족하였나요?',
                    'sendType'=>0,
                    'answer'=>[
                        ['name'=>'만족', 'type'=>'0'],
                        ['name'=>'보통', 'type'=>'0'],
                        ['name'=>'아쉬움', 'type'=>'0']
                    ]
                ],
                [
                    'use'=>'N',
                    'title'=>'재수술여부',
                    'question'=>'재수술 여부',
                    'sendType'=>0,
                    'answer'=>[
                        ['name'=>'예', 'type'=>'0'],
                        ['name'=>'아니요', 'type'=>'0'],
                    ]
                ],
                [
                    'use'=>'N',
                    'title'=>'수술시기',
                    'question'=>'수술 한 지 얼마나 되셨나요?',
                    'sendType'=>0,
                    'answer'=>[
                        ['name'=>'일주일 이내','type'=>'0'],
                        ['name'=>'한 달 이내', 'type'=>'0'],
                        ['name'=>'6개월 이내', 'type'=>'0'],
                        ['name'=>'1년 이상 경과', 'type'=>'0'],
                        ['name'=>'직접 입력', 'type'=>'2']
                    ]
                ],
                [
                    'use'=>'Y',
                    'title'=>'시술효과',
                    'question'=>'시술 효과는 만족스러우신가요?',
                    'sendType'=>0,
                    'answer'=>[
                        ['name'=>'만족', 'type'=>'0'],
                        ['name'=>'보통', 'type'=>'0'],
                        ['name'=>'아쉬움', 'type'=>'0']
                    ]
                ],
                [
                    'use'=>'Y',
                    'title'=>'지인추천',
                    'question'=>'주변에 추천해 줄 의향이 있나요?',
                    'sendType'=>0,
                    'answer'=>[
                        ['name'=>'추천', 'type'=>'0'],
                        ['name'=>'보통', 'type'=>'0'],
                        ['name'=>'추천안함', 'type'=>'0']
                    ]
                ],
                [
                    'use'=>'N',
                    'title'=>'부기',
                    'question'=>'수술 후 부기는 어떠셨나요?',
                    'sendType'=>0,
                    'answer'=>[
                        ['name'=>'심함', 'type'=>'0'],
                        ['name'=>'보통', 'type'=>'0'],
                        ['name'=>'심하지않음', 'type'=>'0'],
                        ['name'=>'거의없음', 'type'=>'0']
                    ]
                ]
            ],
            's5'=>[
                [
                    'use'=>'Y',
                    'title'=>'상담',
                    'question'=>'상담은 만족하였나요?',
                    'sendType'=>0,
                    'answer'=>[
                        ['name'=>'만족', 'type'=>'0'],
                        ['name'=>'보통', 'type'=>'0'],
                        ['name'=>'아쉬움', 'type'=>'0']
                    ]
                ],
                [
                    'use'=>'N',
                    'title'=>'교정 전 시력',
                    'question'=>'교정 전 시력은 어느정도인가요?',
                    'sendType'=>1,
                    'answer'=>[
                        ['name'=>'좌', 'type'=>'1'],  //직접입력방식
                        ['name'=>'우', 'type'=>'1'],
                        ['name'=>'난시/근시/원시', 'type'=>'2'], //3개중 1개번호 전송
                        ['name'=>'재수술여부', 'type'=>'3'] // 1/0 중 전송
                    ]
                ],
                [
                    'use'=>'N',
                    'title'=>'교정 후 시력',
                    'question'=>'수술 후 교정시력은 어느정도인가요?',
                    'sendType'=>0,
                    'answer'=>[
                        ['name'=>'0.8 이상', 'type'=>'0'], //숫자만 전송방식
                        ['name'=>'1.0 이상', 'type'=>'0'],
                        ['name'=>'1.5 이상', 'type'=>'0']
                    ]
                ],
                [
                    'use'=>'Y',
                    'title'=>'시력회복',
                    'question'=>'시력 회복 기간은 어느정도인가요?',
                    'sendType'=>0,
                    'answer'=>[
                        ['name'=>'수술 직후', 'type'=>'0'],
                        ['name'=>'일주일 이내','type'=>'0'],
                        ['name'=>'2주 이내', 'type'=>'0'],
                        ['name'=>'한달 이내', 'type'=>'0'],
                        ['name'=>'직접 입력', 'type'=>'2']
                    ]
                ],
                [
                    'use'=>'Y',
                    'title'=>'지인추천',
                    'question'=>'주변에 추천해 줄 의향이 있나요?',
                    'sendType'=>0,
                    'answer'=>[
                        ['name'=>'추천', 'type'=>'0'],
                        ['name'=>'보통', 'type'=>'0'],
                        ['name'=>'추천안함', 'type'=>'0']
                    ]
                ]
            ]
        ];

        //$this->v1 = $this->load->database('good', true); //v1 운영db
        //exit;

    }

    /**
     * 후기 이전 프로그램(이벤트)
     */
    function moveContents()
    {
        exit;
        $this->load->library('survey');
        $this->master = $this->load->database('goodocV2', true); //v2 운영db

        //이벤트 번호
        $arr = [
            '11315'=>'13768',
            '12812'=>'13777',
            '10785'=>'13784',
            '12594'=>'13774',
            '11316'=>'13770',
            '11886'=>'13771',
            '13424'=>'13778',
            '12661'=>'13781',
            '13453'=>'13779',
            '12689'=>'13776',
            '12757'=>'13782',
            '12772'=>'13772',
            '12773'=>'13783',
            '12774'=>'13773',
            '13294'=>'13780',
            '12786'=>'13775'
        ];


        foreach ($arr as $key=>$val)
        {
            //이벤트번호에 해당하는 후기 가져오기
            $this->master->select('(select group_concat(fileName) from board_files where boardId=board.id) as fileArray');
            $this->master->select('board.*');
            $result = $this->master->get_where('board', ['targetId'=>$key, 'type'=>1])->result_array();
            echo $this->master->last_query();

            foreach ($result as $info)
            {
                //개별후기 등록
                $iArra = [
                    'userId'=>$info['userId'],
                    'userName'=>$info['userName'],
                    'userEmail'=>$info['userEmail'],
                    'type'=>1,
                    'targetId'=>$val,
                    'rateSum'=>$info['rateSum'],
                    'rate1'=>$info['rate1'],
                    'rate2'=>$info['rate2'],
                    'rate3'=>$info['rate3'],
                    'surveyType'=>$info['surveyType'],
                    'survey1'=>$info['survey1'],
                    'survey2'=>$info['survey2'],
                    'survey3'=>$info['survey3'],
                    'survey4'=>$info['survey4'],
                    'survey5'=>$info['survey5'],
                    'survey6'=>$info['survey6'],
                    'contents'=>$info['contents'],
                    'callRequestId'=>$info['callRequestId'],
                    'notEventUser'=>$info['notEventUser'],
                    'categoryName'=>$info['categoryName'],
                    'category'=>$info['category'],
                    'regDate'=>$info['regDate'],
                    'modifyDate'=>$info['modifyDate'],
                    'likeCount'=>$info['likeCount'],
                    'isDelete'=>$info['isDelete'],
                    'device'=>$info['device'],
                    'isMember'=>$info['isMember'],
                    'ip'=>$info['ip']
                ];
                $this->master->insert('board', $iArra);
                $lastId= $this->master->insert_id();
                //dd($iArra, false);

                //image
                if($info['fileArray'])
                {
                    $fileArr = explode(',', $info['fileArray']);
                    $i=1;
                    foreach ($fileArr as $item)
                    {
                        $arr = [
                            'boardId' => $lastId,
                            'fileName' => $item,
                            'orderBy' => $i,
                            'regDate' => date("Y-m-d H:i:s")
                        ];
                        $this->master->insert('board_files', $arr);
                        $i++;
                        dd($arr, false);
                    }
                }

                //평균 재계산용
                $sql = "select round(avg(rate1), 1) as rate1, round(AVG(rate2), 1) as rate2, round(avg(rate3), 1) as rate3 
                from board where type='".$info['type']."' and targetId ='".$val."' and isDelete=0";
                $rates = $this->master->query($sql)->row_array();

                //dd($rates, false);

                //summary처리
                $sql2 = "select * from board_summary where type='".$info['type']."' and targetId='".$val."'";
                $result = $this->master->query($sql2)->row_array();

                //dd($result, false);

                //각 숫자들도 라운드 처리할지 고민
                $sArr['rate1'] = $rates['rate1'];
                $sArr['rate2'] = $rates['rate2'];
                $sArr['rate3'] = $rates['rate3'];
                $sArr['rateSum'] = round(($sArr['rate1'] + $sArr['rate2'] + $sArr['rate3'])/3, '1'); //소수점 첫째자리까지

                if($info['surveyType'] != 0)
                {
                    $surveyRules = $this->surveryArray[$this->thisVersion];
                    $surveyRule  = $surveyRules['s'.$info['surveyType']];
                    $surveyCount = count($surveyRule);
                    for($i=0; $i<$surveyCount; $i++) {
                        $rule = $surveyRule[$i];
                        $answerCount = count($rule['answer']);

                        $surveyName = 'survey'.($i+1);
                        $surveyKey  = 'survey'.$info['surveyType'].($i+1);
                        $vote = $this->parseIntDef($info[$surveyName], (
                        ($rule['use'] == 'N' && $info[$surveyName])? $answerCount:0
                        ));

                        $survey = new Survey($answerCount);
                        if ($result) $survey->initFromString($result[$surveyKey]);
                        if ($vote != 0) $survey->vote($vote);
                        $sArr[$surveyKey] = $survey->__toString();
                    }
                }

                echo $key .'--'.$info['id'].'--'.$lastId.'<br>';
                dd($sArr, false); echo '<br><br><br>';
                $this->master->where('targetId', $val)->where('type', $info['type'])->update('board_summary', $sArr);

                //이전 후기 삭제
                $this->master->delete('board', ['id'=>$info['id']]);
                $this->master->delete('board_files', ['boardId'=>$info['id']]);
            }

            //이전 summary delete
            $this->master->delete('board_summary', ['targetId'=>$key, 'type'=>1]);
        }
    }

    /**
     * 후기 이전 프로그램(병원후기)
     */
    function moveContents2()
    {
        $this->load->library('survey');
        $this->master = $this->load->database('goodocV2', true); //v2 운영db

        //병원 번호
        $arr = [
            '205241'=>'202222'
        ];


        foreach ($arr as $key=>$val)
        {
            //병원번호에 해당하는 후기 가져오기
            $this->master->select('(select group_concat(fileName) from board_files where boardId=board.id) as fileArray');
            $this->master->select('board.*');
            $result = $this->master->get_where('board', ['targetId'=>$key, 'type'=>2])->result_array();
            echo $this->master->last_query();

            foreach ($result as $info)
            {
                //개별후기 등록
                $iArra = [
                    'userId'=>$info['userId'],
                    'userName'=>$info['userName'],
                    'userEmail'=>$info['userEmail'],
                    'type'=>1,
                    'targetId'=>$val,
                    'rateSum'=>$info['rateSum'],
                    'rate1'=>$info['rate1'],
                    'rate2'=>$info['rate2'],
                    'rate3'=>$info['rate3'],
                    'surveyType'=>$info['surveyType'],
                    'survey1'=>$info['survey1'],
                    'survey2'=>$info['survey2'],
                    'survey3'=>$info['survey3'],
                    'survey4'=>$info['survey4'],
                    'survey5'=>$info['survey5'],
                    'survey6'=>$info['survey6'],
                    'contents'=>$info['contents'],
                    'callRequestId'=>$info['callRequestId'],
                    'notEventUser'=>$info['notEventUser'],
                    'categoryName'=>$info['categoryName'],
                    'category'=>$info['category'],
                    'regDate'=>$info['regDate'],
                    'modifyDate'=>$info['modifyDate'],
                    'likeCount'=>$info['likeCount'],
                    'isDelete'=>$info['isDelete'],
                    'device'=>$info['device'],
                    'isMember'=>$info['isMember'],
                    'ip'=>$info['ip']
                ];
                $this->master->insert('board', $iArra);
                $lastId= $this->master->insert_id();
                //dd($iArra, false);

                //image
                if($info['fileArray'])
                {
                    $fileArr = explode(',', $info['fileArray']);
                    $i=1;
                    foreach ($fileArr as $item)
                    {
                        $arr = [
                            'boardId' => $lastId,
                            'fileName' => $item,
                            'orderBy' => $i,
                            'regDate' => date("Y-m-d H:i:s")
                        ];
                        $this->master->insert('board_files', $arr);
                        $i++;
                        dd($arr, false);
                    }
                }

                //평균 재계산용
                $sql = "select round(avg(rate1), 1) as rate1, round(AVG(rate2), 1) as rate2, round(avg(rate3), 1) as rate3 
                from board where type='".$info['type']."' and targetId ='".$val."' and isDelete=0";
                $rates = $this->master->query($sql)->row_array();

                //dd($rates, false);

                //summary처리
                $sql2 = "select * from board_summary where type='".$info['type']."' and targetId='".$val."'";
                $result = $this->master->query($sql2)->row_array();

                //dd($result, false);

                //각 숫자들도 라운드 처리할지 고민
                $sArr['rate1'] = $rates['rate1'];
                $sArr['rate2'] = $rates['rate2'];
                $sArr['rate3'] = $rates['rate3'];
                $sArr['rateSum'] = round(($sArr['rate1'] + $sArr['rate2'] + $sArr['rate3'])/3, '1'); //소수점 첫째자리까지


                echo $key .'--'.$info['id'].'--'.$lastId.'<br>';
                dd($sArr, false); echo '<br><br><br>';
                $this->master->where('targetId', $val)->where('type', $info['type'])->update('board_summary', $sArr);

                //이전 후기 삭제
                $this->master->delete('board', ['id'=>$info['id']]);
                $this->master->delete('board_files', ['boardId'=>$info['id']]);
            }

            //이전 summary delete
            $this->master->delete('board_summary', ['targetId'=>$key, 'type'=>1]);
        }
    }

    /**
     * 정수 변환 함수
     */
    function parseIntDef($val, $default = 0)
    {
        if (is_numeric($val))
        {
            return intval($val);
        }
        else
        {
            $res = explode('/', $val);

            if(is_numeric($res[0]))
            {
                return intval($res[0]);
            }
            else
            {
                return $default;
            }
        }
    }

    function initDb()
    {
        $this->db = $this->load->database('local', true);


        $hospitalId = $this->uri->segment(3,1);

        $this->db->trans_begin();

        $adsIds = [];
        $adsIds2 = $this->db->get_where('ads', ['hospitalId'=>$hospitalId])->result_array();
        foreach ($adsIds2 as $item)
        {
            $adsIds[] = $item['id'];
        }

        //ads 삭제
        $this->db->where_in('id', $adsIds);
        $this->db->delete('ads');

        //계약상제
        $contractIds = $contractOrderIds = [];
        $contractIds2 = $this->db->get_where('contract', ['hospitalId'=>$hospitalId])->result_array();
        foreach ($contractIds2 as $it)
        {
            $contractIds[] = $it['id'];
        }

        $this->db->where_in('contractId', $contractIds);
        $contractOrderIds2 = $this->db->get('contract_order_connect')->result_array();
        foreach ($contractOrderIds2 as $it2)
        {
            $contractOrderIds[] = $it2['contractOrderId'];
        }

        $this->db->where('hospitalId', $hospitalId);
        $this->db->delete('contract');

        //수주계약, 매핑 삭제
        $this->db->where_in('id', $contractOrderIds);
        $this->db->delete('contract_order');

        $this->db->where_in('contractId', $contractIds);
        $this->db->delete('contract_order_connect');

        //deposit 삭제
        $this->db->where_in('contractId', $contractIds);
        $this->db->delete('deposit');

        //payment delete
        $this->db->where('hospitalId', $hospitalId);
        $this->db->delete('payment');

        //연결테이블 삭제
        $tables = [
            //'ads_cooperation', //삭제
            //'ads_file_history', //삭제
            'ads_history',
            'ads_history_memo',
            //'ads_image', //삭제
            'ads_keyword',
            'ads_main',
            'ads_main_map',
            //'ads_network_hospital',
            //'ads_option', //삭제
            //'ads_region', //삭제
            'ads_temporary',
            'inspecting_ads'
        ];

        foreach ($tables as $table)
        {
            $this->db->where_in('adsId', $adsIds);
            $this->db->delete($table);
        }

        if ($this->db->trans_status() === FALSE)
        {
            $this->db->trans_rollback();
            echo 'fail';
        }
        else
        {
            $this->db->trans_commit();
            echo 'success';
        }

    }

    function main()
    {
        exit;
        set_time_limit(0);
        ini_set('memory_limit','-1');

        $this->db->trans_begin();

        $limit = '';
        $date = date("Y-m-d H:i:s"); //umt기준
        $date2 = date("Y-m-d"); //umt기준

        //버킷명 정의
        if( ENVIRONMENT === 'development' )
        {
            //$bucketName = 'asset-dev.goodoc.kr';
            $bucketName = 'asset.goodoc.kr';
        }
        else if( ENVIRONMENT === 'testing' )
        {
            $bucketName = 'asset.goodoc.kr';
        }
        else if (ENVIRONMENT === 'production')
        {
            $bucketName = 'asset.goodoc.kr';
        }

        //auto_increment 수동 진행

        //shared_infos 작업  51668개
        $sql = "
            select si.*, users.username, users.email from shared_infos si
            join users on si.user_id=users.id  
            order by id desc -- limit ".$limit;
        $infos = $this->v1->query($sql)->result_array();

        foreach ($infos as $info)
        {
            //점수로직 변경
            switch ($info['rating_medical'])
            {
                case 1:
                    $info['rating_medical'] = 3;
                    break;
                case 2:
                    $info['rating_medical'] = 6;
                    break;
                case 3:
                    $info['rating_medical'] = 10;
                    break;
            }

            switch ($info['rating_staff'])
            {
                case 1:
                    $info['rating_staff'] = 3;
                    break;
                case 2:
                    $info['rating_staff'] = 6;
                    break;
                case 3:
                    $info['rating_staff'] = 10;
                    break;
            }

            switch ($info['rating_facility'])
            {
                case 1:
                    $info['rating_facility'] = 3;
                    break;
                case 2:
                    $info['rating_facility'] = 6;
                    break;
                case 3:
                    $info['rating_facility'] = 10;
                    break;
            }

            $iArra = [
                'id'=>$info['id'],
                'userId'=>$info['user_id'],
                'userName'=>$info['username'],
                'userEmail'=>$info['email'],
                'type'=>2,
                'targetId'=>$info['hospital_id'],
                'regDate'=>$info['created_at'],
                'modifyDate'=>$info['updated_at'],
                'likeCount'=>$info['like_count'],
                'isDelete'=>$info['is_blocked'],
                'deleteMemo'=>$info['block_memo'],
                'deleteDate'=>$info['blocked_at'],
                'contents'=>$info['desc'],
                'rateSum'=>round(($info['rating_medical']+$info['rating_staff']+$info['rating_facility'])/3, 1),
                'rate1'=>$info['rating_medical'],
                'rate2'=>$info['rating_staff'],
                'rate3'=>$info['rating_facility'],
                'device'=>$info['device']
            ];
            $this->db->insert('board', $iArra);

            //board_summary 작업
            $sArr = ['type'=>2, 'targetId'=>$info['hospital_id']];
            $sql00 = "select count(*) cnt from board_summary where type='2' and targetId='".$info['hospital_id']."'";
            $sResult = $this->db->query($sql00)->row_array();

            $sql01 = "select round(avg(rate1), 1) as rate1, round(AVG(rate2), 1) as rate2, round(avg(rate3), 1) as rate3 from board where type='2' and targetId='".$info['hospital_id']."'";
            $rates = $this->db->query($sql01)->row_array();

            $sArr['rate1'] = $rates['rate1']? $rates['rate1']:$info['rating_medical'];
            $sArr['rate2'] = $rates['rate2']? $rates['rate2']:$info['rating_staff'];
            $sArr['rate3'] = $rates['rate3']? $rates['rate3']:$info['rating_facility'];
            $sArr['rateSum'] = round(($sArr['rate1'] + $sArr['rate2'] + $sArr['rate3'])/3, '1'); //소수점 첫째자리까지

            if($sResult['cnt'] > 0)
            {
                $sArr['modDate'] = $date2;
                $this->db->where('targetId', $info['hospital_id'])->where('type', '2')->update('board_summary', $sArr);
            }
            else
            {
                //등록일, 평가일 추가
                $sArr['regDate'] = $sArr['modDate'] = $date2;
                $this->db->insert('board_summary', $sArr);
            }
        }


        //like_shared_info_like_users
        $sql10 = "
            select * from like_shared_info_like_users
            order by id desc -- limit ".$limit
        ;
        $likes = $this->v1->query($sql10)->result_array();

        foreach ($likes as $lik)
        {
            $lArr = [
                'type'=>1,
                'boardId'=>$lik['like_shared_info_id'],
                'userId'=>$lik['like_user_id'],
                'regDate'=>$lik['created_at'],
            ]   ;
            $this->db->insert('board_estimation', $lArr);
        }


        //comments
        $sql20 = "
            select c.*, users.username from comments c 
            join users on c.user_id=users.id  
            order by id desc -- limit ".$limit;
        $coms = $this->v1->query($sql20)->result_array();

        foreach ($coms as $cos)
        {
            $cArr = [
                'boardId'=>$cos['shared_info_id'],
                'userId'=>$cos['user_id'],
                'userName'=>$cos['username'],
                'contents'=>$cos['desc'],
                'regDate'=>$cos['created_at'],
                'modifyDate'=>$cos['updated_at'],
            ]   ;
            $this->db->insert('board_comments', $cArr);
        }

        //like_comment_like_users
        $sql30 = "
            select * from like_comment_like_users
            order by id desc -- limit ".$limit;
        $comms = $this->v1->query($sql30)->result_array();

        foreach ($comms as $coos)
        {
            $coArr = [
                'type'=>5,
                'boardId'=>$coos['like_comment_id'],
                'userId'=>$coos['like_user_id'],
                'regDate'=>$coos['created_at'],
            ]   ;

            if($coos['like_comment_id'] and $coos['like_user_id'])
            {
                $this->db->insert('board_estimation', $coArr);
            }
        }

        if ($this->db->trans_status() === FALSE)
        {
            $this->db->trans_rollback();
        }
        else
        {
            $this->db->trans_commit();
        }

        //shared_info_images
        $sql1 = "
            select * from shared_info_images sii
            order by id desc -- limit ".$limit;
        $files = $this->v1->query($sql1)->result_array();

        foreach ($files as $fls)
        {
            if(strpos($fls['file'], 'uploads/hospital_image/file') != false)
            {
                $f1 = explode('/uploads/hospital_image/file', $fls['file']);
                $fileName = 'board/picture'.$f1[1];
            }
            else if (strpos($fls['file'], 's3/uploads/shared_info_image') != false)
            {
                $f2 = explode('/s3/uploads/shared_info_image', $fls['file']);
                $fileName = 'board/picture'.$f2[1];
            }

            $iiArra = [
                'id'=>$fls['id'],
                'boardId'=>$fls['shared_info_id'],
                'type'=>'',
                'originalName'=>'http://'.$bucketName.'/'.$fileName,
                'fileName'=>'http://'.$bucketName.'/'.$fileName,
                'orderBy'=>1,
                'regDate'=>$fls['created_at']
            ];
            $this->db->insert('board_files', $iiArra);

            //s3 업로드
            //$fileData = file_get_contents('http://goodoc.co.kr'.$fls['file']);
//
//            $s3Client = Aws\S3\S3Client::factory(array(
//                'region' => 'ap-northeast-2',
//                'version' => 'latest',
//                'signature' => 'v4',
//                'credentials' => array(
//                    'key'    => S3Key,
//                    'secret' => S3Secret
//                )
//            ));
//
//            $return = $s3Client->putObject(array(
//                'Bucket' => $bucketName,
//                'Key'    => $fileName,
//                'Body' => $fileData,
//                'ACL'    => 'public-read'
//            ));
//
//            var_dump($return);


        }
    }

    function makeThumb()
    {
        exit;
        set_time_limit(0);

        //버킷명 정의
        if( ENVIRONMENT === 'development' )
        {
            $bucketName = 'asset.goodoc.kr';
        }
        else if( ENVIRONMENT === 'testing' )
        {
            $bucketName = 'asset.goodoc.kr';
        }
        else if (ENVIRONMENT === 'production')
        {
            $bucketName = 'asset.goodoc.kr';
        }
        $limit = 1000;

        $sql1 = "
            select * from shared_info_images sii  where id>= '1056'
            order by id desc ";
        $files = $this->v1->query($sql1)->result_array();

        $s3Client = Aws\S3\S3Client::factory(array(
            'region' => 'ap-northeast-2',
            'version' => 'latest',
            'signature' => 'v4',
            'credentials' => array(
                'key'    => S3Key,
                'secret' => S3Secret
            )
        ));


        $this->load->library('image_lib');

        foreach ($files as $fls)
        {
            if(strpos($fls['file'], 'uploads/hospital_image/file') != false)
            {
                $f1 = explode('/uploads/hospital_image/file', $fls['file']);
                $fileName = 'board/picture'.$f1[1];
            }
            else if (strpos($fls['file'], 's3/uploads/shared_info_image') != false)
            {
                $f2 = explode('/s3/uploads/shared_info_image', $fls['file']);
                $fileName = 'board/picture'.$f2[1];
            }

            echo $fileName."<br><br>";

            //s3 업로드

            $fileData = file_get_contents('http://goodoc.co.kr'.$fls['file']);

            $fileName2 = explode('.', $fileName);
            $fileName3 = explode('/', $fileName);
            $fileName4 = explode('.', $fileName3[3]);

            if(!is_dir(UP_ROOT.'/data/'.$fileName3[2]))
            {
                mkdir(UP_ROOT.'/data/'.$fileName3[2], 0777);
                chmod(UP_ROOT.'/data/'.$fileName3[2], 0777);
            }

            $iReturn = file_put_contents('./uploads/data/'.$fileName3[2].'/'.$fileName3[3], $fileData);

            echo 'l make<br>';
            // l size
            $config['source_image'] = './uploads/data/'.$fileName3[2].'/'.$fileName3[3];
            $config['quality'] = 85;
            $config['create_thumb'] = TRUE;
            $config['maintain_ratio'] = true;
            $config['width'] = 568;
            $config['thumb_marker']='';
            $config['new_image'] = './uploads/data/'.$fileName3[2].'/'.$fileName4[0].'-l.'.$fileName4[1];
            $this->image_lib->initialize($config);
            //echo '/uploads/data/'.$fileName3[2].'/'.$fileName4[0].'-l.'.$fileName4[1];

            $this->image_lib->resize();

            echo $this->image_lib->display_errors();
            
            $this->image_lib->clear();

            echo 't make<br>';
            //t size
            $config2['source_image'] = './uploads/data/'.$fileName3[2].'/'.$fileName3[3];
            $config2['quality'] = 85;
            $config2['create_thumb'] = TRUE;
            $config2['maintain_ratio'] = true;
            $config2['width'] = 200;
            $config2['thumb_marker']='';
            $config2['new_image'] = './uploads/data/'.$fileName3[2].'/'.$fileName4[0].'-t.'.$fileName4[1];

            $this->image_lib->initialize($config2);

            $this->image_lib->resize();
            echo $this->image_lib->display_errors();


            echo 'l upload<br>';

            $file2 = 'board/picture/'.$fileName3[2].'/'.$fileName4[0].'-l.'.$fileName4[1];

            $return = $s3Client->putObject(array(
                'Bucket' => $bucketName,
                'Key'    => $file2,
                'SourceFile' => './uploads/data/'.$fileName3[2].'/'.$fileName4[0].'-l.'.$fileName4[1],
                'ACL'    => 'public-read'
            ));


            echo 't upload<br>';
            $file1 = 'board/picture/'.$fileName3[2].'/'.$fileName4[0].'-t.'.$fileName4[1];

            $return = $s3Client->putObject(array(
                'Bucket' => $bucketName,
                'Key'    => $file1,
                'SourceFile' => './uploads/data/'.$fileName3[2].'/'.$fileName4[0].'-t.'.$fileName4[1],
                'ACL'    => 'public-read'
            ));

            //var_dump($return);
            //echo "<BR>";


            //var_dump($return);
            echo "<BR>------------------------------------------------<br>";
        }
    }

    function copyImage()
    {
        exit;
        set_time_limit(0);

        //버킷명 정의
        if( ENVIRONMENT === 'development' )
        {
            $bucketName = 'asset-dev.goodoc.kr';
        }
        else if( ENVIRONMENT === 'testing' )
        {
            $bucketName = 'asset-staging.goodoc.kr';
        }
        else if (ENVIRONMENT === 'production')
        {
            $bucketName = 'asset.goodoc.kr';
        }

        $limit = 800;
        $sql1 = "
            select * from shared_info_images
            limit ".$limit;
        $files = $this->v1->query($sql1)->result_array();

        foreach ($files as $fls)
        {
            if (strpos($fls['file'], 'uploads/hospital_image/file') != false) {
                $f1 = explode('/uploads/hospital_image/file', $fls['file']);
                $fileName = 'board/picture' . $f1[1];
            } else if (strpos($fls['file'], 's3/uploads/shared_info_image') != false) {
                $f2 = explode('/s3/uploads/shared_info_image', $fls['file']);
                $fileName = 'board/picture' . $f2[1];
            }

//            $iiArra = [
//                'id' => $fls['id'],
//                'boardId' => $fls['shared_info_id'],
//                'type' => '',
//                'originalName' => 'http://' . $bucketName . '/' . $fileName,
//                'fileName' => 'http://' . $bucketName . '/' . $fileName,
//                'orderBy' => 1,
//                'regDate' => $fls['created_at']
//            ];
//            $this->db->insert('board_files', $iiArra);

            echo '<img src="http://goodoc.co.kr' . $fls['file'].'" width="200"><br>';
            //s3 업로드
            $fileData = file_get_contents('http://goodoc.co.kr' . $fls['file']);


            $fileName2 = explode('.', $fileName);
            $fileName3 = explode('/', $fileName);
            $fileName4 = explode('.', $fileName3[3]);

            if(!is_dir(UP_ROOT.'/data/'.$fileName3[2]))
            {
                mkdir(UP_ROOT.'/data/'.$fileName3[2], 0777);
                chmod(UP_ROOT.'/data/'.$fileName3[2], 0777);
            }

            $iReturn = file_put_contents('./uploads/data/'.$fileName3[2].'/'.$fileName3[3], $fileData);

            echo '<img src="'.'/uploads/data/'.$fileName3[2].'/'.$fileName3[3] .'" width="200"><br>';

            //exit;

            if($fileData)
            {
                $s3Client = Aws\S3\S3Client::factory(array(
                    'region' => 'ap-northeast-2',
                    'version' => 'latest',
                    'signature' => 'v4',
                    'credentials' => array(
                        'key' => S3Key,
                        'secret' => S3Secret
                    ),
                    'http'    => [
                        'connect_timeout' => 0
                    ]
                ));

                $return = $s3Client->putObject(array(
                    'Bucket' => $bucketName,
                    'Key' => $fileName,
                    //'Body' => $fileData,
                    'SourceFile' => './uploads/data/'.$fileName3[2].'/'.$fileName3[3],
                    'ACL' => 'public-read'
                ));

                //var_dump($return['@metadata']['effectiveUri']);
                echo '<img src="' . $return['@metadata']['effectiveUri'].'" width="200"><br><br>';

            }

            sleep(1);
        }
    }

    function upDate()
    {
        set_time_limit(0);
        ini_set('memory_limit','-1');

        $sql = "
            select si.*, users.username, users.email from shared_infos si
            join users on si.user_id=users.id  
            order by id desc -- limit ";
        $infos = $this->v1->query($sql)->result_array();

        foreach ($infos as $info)
        {
            //board_summary 작업

            $sArr['regDate'] = $info['created_at'];
            $sArr['modDate'] = $info['updated_at'];
            $this->db->where('targetId', $info['hospital_id'])->update('board_summary', $sArr);

        }
    }

    /**
     * 10 0 0 인 후기가 있어서 값을 복사하고 별점 평균을 다시 산정하는 작업
     */
    function surveyReCal()
    {
        exit;
        set_time_limit(0);
        ini_set('memory_limit', '-1');

        $this->db->trans_begin();

        //$limit = 'limit 10';
        $limit = '';
        $date = date("Y-m-d H:i:s"); //umt기준
        $date2 = date("Y-m-d"); //umt기준

        //서머리 테이블 작업할 병원번호 먼저 추출
        $sql0 ="
        select bd.targetId from board bd 
        where bd.type=2 and bd.rate1 != 0 and bd.rate2 = 0 or bd.rate3 = 0
        group by bd.targetId
        ".$limit;
        $hArr = $this->db->query($sql0)->result_array(); //var_dump($hArr);


        //후기 테이블 업데이트 : rateSum, rate2, rate3 모두 rate1 값으로 업데이트
        $sql = "
            select 
            bd.id, bd.`rateSum`, bd.rate1, bd.rate2, bd.rate3,
            bd.targetId, bs.rateSum sSum, bs.rate1 sRate1, bs.rate2 sRate2, bs.rate3 sRate3 
            from board bd
            join board_summary bs on bd.targetId=bs.targetId and bd.type=bs.type
            where bd.type=2 and bd.rate1 != 0 and bd.rate2 = 0 and bd.rate3 = 0
            -- and bd.targetId <= 23085
            order by targetId
            
            " ;
        $infos = $this->db->query($sql)->result_array(); //var_dump($infos);

        $aUp = '';
        foreach ($infos as $info)
        {
            $aSum = $info['rate1']; //같은 점수 복사하여 산정하는 것이라 곱하기 3, 나누기 3 그래서 rate1과 동일하다
            $aUp = "
                update board set rateSum='".$aSum."', rate2='".$info['rate1']."', rate3='".$info['rate1']."' where id='".$info['id']."'
            ";
            $this->db->query($aUp);
            echo '<br><br>'.$aUp.'<br><br>';
        }

        //summary는 따로 재계산한다. 병원번호 기준으로 데이터를 가져와서
        foreach ($hArr as $item)
        {
            $sArr = ['type'=>2, 'targetId'=>$item['targetId']];

            $sql01 = "select round(avg(rate1), 1) as rate1, round(AVG(rate2), 1) as rate2, round(avg(rate3), 1) as rate3 
            from board where type='2' and targetId='".$item['targetId']."'
            ";
            $rates = $this->db->query($sql01)->row_array();

            $sArr['rate1'] = $rates['rate1'];
            $sArr['rate2'] = $rates['rate2'];
            $sArr['rate3'] = $rates['rate3'];
            $sArr['rateSum'] = round(($sArr['rate1'] + $sArr['rate2'] + $sArr['rate3'])/3, '1'); //소수점 첫째자리까지

            var_dump($sArr); echo '<br>';
            $this->db->where('targetId', $item['targetId'])->where('type', '2')->update('board_summary', $sArr);
        }

        if ($this->db->trans_status() === FALSE)
        {
            $this->db->trans_rollback();
        }
        else
        {
            $this->db->trans_commit();
        }

    }

    /**
     * 그린, 루나 biz-682
     *
     * 10 0 0 의 평점을 10으로 업데이트 한다.
     *
     * 10 0 0 과 0 0 0 은 board_summary 평균 계산할때 제외한다.
     *
     * 2018.08.29
     */
    function surveyReCal2()
    {
        exit;

        set_time_limit(0);
        ini_set('memory_limit', '-1');

        $this->db->trans_begin();

        //$limit = 'limit 10';
        $limit = '';
        $date = date("Y-m-d H:i:s"); //umt기준
        $date2 = date("Y-m-d"); //umt기준

        //서머리 테이블 작업할 병원번호 먼저 추출   17059개
        $sql0 ="
        select bd.targetId from board bd 
        where bd.type=2 and (bd.rate1 != 0 and bd.rate2 = 0 and bd.rate3 = 0) or (bd.rate1 = 0 and bd.rate2 = 0 and bd.rate3 = 0)
        group by bd.targetId
        ".$limit;
        $hArr = $this->db->query($sql0)->result_array(); //var_dump($hArr);


        //후기 테이블 업데이트 : rateSum만 rate1 값으로 업데이트
        $sql = "
            select 
            bd.id, bd.`rateSum`, bd.rate1, bd.rate2, bd.rate3,
            bd.targetId, bs.rateSum sSum, bs.rate1 sRate1, bs.rate2 sRate2, bs.rate3 sRate3 
            from board bd
            join board_summary bs on bd.targetId=bs.targetId and bd.type=bs.type
            where bd.type=2 and (bd.rate1 != 0 and bd.rate2 = 0 and bd.rate3 = 0) or (bd.rate1 = 0 and bd.rate2 = 0 and bd.rate3 = 0)
            -- and bd.targetId <= 23085
            order by targetId
            
            " ;
        $infos = $this->db->query($sql)->result_array(); //var_dump($infos);

        foreach ($infos as $info)
        {
            $aSum = $info['rate1']; //같은 점수 복사하여 산정하는 것이라 곱하기 3, 나누기 3 그래서 rate1과 동일하다
            $aUp = "
                update board set rateSum='".$aSum."' where id='".$info['id']."'
            ";
            $this->db->query($aUp);
            echo '<br><br>'.$aUp.'<br><br>';
        }

        //summary는 따로 재계산한다. 병원번호 기준으로 데이터를 가져와서
        foreach ($hArr as $item)
        {
            $sArr = ['type'=>2, 'targetId'=>$item['targetId']];

            $sql01 = "select round(avg(rate1), 1) as rate1, round(AVG(rate2), 1) as rate2, round(avg(rate3), 1) as rate3 
            from board where type='2' and targetId='".$item['targetId']."'
            ";
            $rates = $this->db->query($sql01)->row_array();

            if($rates['rate1'] != '' and $rates['rate2'] != '' and $rates['rate3'] != '')
            {
                $sArr['rate1'] = $rates['rate1'];
                $sArr['rate2'] = $rates['rate2'];
                $sArr['rate3'] = $rates['rate3'];
                $sArr['rateSum'] = round(($sArr['rate1'] + $sArr['rate2'] + $sArr['rate3'])/3, '1'); //소수점 첫째자리까지

                var_dump($sArr); echo '<br>';
                $this->db->where('targetId', $item['targetId'])->where('type', '2')->update('board_summary', $sArr);
            }
            else
            {
                echo '데이터없음 - 아무일도 안함 <br>';
            }


        }

        if ($this->db->trans_status() === FALSE)
        {
            $this->db->trans_rollback();
        }
        else
        {
            $this->db->trans_commit();
        }

    }


    /**
     * 그린, 루나 biz-682
     *
     * 10 0 0 의 평점을 10으로 업데이트 한다.
     *
     * 10 0 0 과 0 0 0 은 board_summary 평균 계산할때 0 인 것만 갯수에서 제외하여 계산한다.
     *
     * 써머리 테이블에 넣을때 카운터 0 이면 제외하고 평균을 낸다. (8월 30일 추가할것)
     * 2018.08.29
     */
    function surveyReCal3()
    {
        exit;
        set_time_limit(0);
        ini_set('memory_limit', '-1');

        $this->db->trans_begin();

        //$limit = 'limit 10, 10'; //0번째부터 100개
        $limit = '';
        $date = date("Y-m-d H:i:s"); //umt기준
        $date2 = date("Y-m-d"); //umt기준

        //병원번호 먼저 추출   17780개
        $sql0 ="
        select targetId from board 
        where type=2 
        -- and targetId=23044
        group by targetId
        ".$limit;
        $hArr = $this->db->query($sql0)->result_array(); //var_dump($hArr);

        foreach ($hArr as $item)
        {
            echo '병원번호 : '.$item['targetId'].'<br>------------------------------------------------<br>';
            //병원번호별 후기 리스트
            $sql1 = "
                select id, targetId, rateSum, rate1, rate2, rate3 from board where type=2 and targetId='".$item['targetId']."'
            ";
            $hosArr = $this->db->query($sql1)->result_array();

            $c1 = $c2 = $c3 = $s1 = $s2 = $s3 = 0;
            foreach ($hosArr as $it2)
            {
                //10 0 0 인 경우 rateSum 업데이트
                if($it2['rate1'] != 0 and $it2['rate2'] == 0 and $it2['rate3'] == 0)
                {
                    $aUp = "
                      update board set rateSum='".$it2['rate1']."' where id='".$it2['id']."'
                    ";
                    echo $aUp.'<br>';
                    $this->db->query($aUp);
                }

                //var_dump($it2); echo '<br>---------------------------------<br>';
                echo ' - 후기번호 : '.$it2['id'].'<br>---------------------<br>';
                if($it2['rate1'] != 0)
                {
                    $c1++;
                    $s1 += $it2['rate1'];
                }

                if($it2['rate2'] != 0)
                {
                    $c2++;
                    $s2 += $it2['rate2'];
                }

                if($it2['rate3'] != 0)
                {
                    $c3++;
                    $s3 += $it2['rate3'];
                }


            }

            echo '1번 합 : '.$s1.' - 카운트 : '.$c1.'<br>';
            echo '2번 합 : '.$s2.' - 카운트 : '.$c2.'<br>';
            echo '3번 합 : '.$s3.' - 카운트 : '.$c3.'<br>';

            $av1 = $av2 = $av3 = 0;

            if($s1 !=0)
            {
                $av1 = round($s1/$c1, 1);
            }

            if($s2 !=0)
            {
                $av2 = round($s2/$c2, 1);
            }

            if($s3 != 0)
            {
                $av3 = round($s3/$c3, 1);
            }

            //개별 평균값이 0이면 별점 총평균에서 제외하고 계산한다.
            $sum = $sCnt = 0;
            if($av1 != 0)
            {
                $sum += $av1;
                $sCnt++;
            }

            if($av2 != 0)
            {
                $sum += $av2;
                $sCnt++;
            }

            if($av3 != 0)
            {
                $sum += $av3;
                $sCnt++;
            }


            if($sum != 0)
            {
                $rS = round($sum/$sCnt, 1);
            }
            else
            {
                $rS = 0;
            }

            echo '1번 평균 : '.$av1.' - 2번 평균 : '.$av2.' - 3번 평균 : '.$av3.' - 총평균 : '.$rS.'<br>-------------------------------------------------------------<br>';

            //summary update
            $sUp = "
                      update board_summary set rateSum='".$rS."', rate1='".$av1."', rate2='".$av2."', rate3='".$av3."' where targetId='".$item['targetId']." and type=2'
                    ";
            echo $sUp.'<br>========================================================<br>';
            $this->db->query($sUp);

        }


        if ($this->db->trans_status() === FALSE)
        {
            $this->db->trans_rollback();
        }
        else
        {
            $this->db->trans_commit();
        }

    }
}