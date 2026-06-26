<?php
/**
 * Created by PhpStorm.
 * User: blumine
 * Date: 21/11/2018
 * Time: 1:45 PM
 */
class Board_m extends CI_Model
{
    function __construct()
    {
        parent::__construct();
        $this->surveryArray = [];
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
    }

    /**
     * 후기 리스트
     * @param $data
     * @return array
     */
    function getBoardList($data)
    {
        $eventStr = [];

        $hospitalId = $this->common_m->getHospitalId($data);

        $eventArr = $this->common_m->getEventId($data); //var_dump($eventArr);exit;

        foreach ($eventArr as $item)
        {
            $eventStr[] =$item['adsId'];
        }
        $eventStr = implode(',', $eventStr);

        $where = " where ((b.type=2 and b.targetId= '".$hospitalId."') or (b.type=1 and b.targetId in(".$eventStr.") ))";

        if($data['searchWord'])
        {
            $where .= " and (b.id like '%".$data['searchWord']."%' or b.targetId like '%".$data['searchWord']."%' or b.userName like '%".$data['searchWord']."%' or b.userEmail like '%".$data['searchWord']."%' or b.contents like '%".$data['searchWord']."%') ";
        }

        $sql = "
                select b.id boardId, b.type, b.targetId, b.userId, b.userName, b.contents, b.regDate
                from board b
                -- left join board_files bf on b.id=bf.`boardId`
                -- left join f_hospitals fh on b.targetId=fh.id
                ".$where."
                -- group by b.id
                order by b.id desc 
            ";
        $sql .= ' limit '.(($data['page'] - 1) * $data['limit']).', '. $data['limit'];

        $result0 = $this->db->query($sql)->result_array();

        $sql2 = "
                select count(*) cnt
                from board b
                -- where isDelete=0 
                ".$where;
        $result2 = $this->db->query($sql2)->row_array();



        return ['data'=>$result0, 'totCount'=>$result2['cnt']];
    }

    /**
     * 후기 상세보기
     * @param $data
     * @return mixed
     */
    function getBoardView($data)
    {
        $sql = "
                select b.id boardId, b.type, b.targetId, b.userId, b.userName, b.contents, b.rateSum, b.rate1, b.rate2, b.rate3,
                b.surveyType, b.survey1, b.survey2, b.survey3, b.survey4, b.survey5, b.survey6, b.regDate,
                group_concat(bf.fileName) as imgName
                from board b
                left join board_files bf on b.id=bf.`boardId`
                where b.id= '".$data['boardId']."'
            ";

        $item = $this->db->query($sql)->row_array();

        $surveyAll = [];

        $return = $item;

        switch ($item['surveyType']) {
            case 1:
                $surveyAll = [
                    '상담' => $this->getArrNameAll('s1', 1, $item['survey1'], 1),
                    '시술시기' => $this->getArrNameAll('s1', 2, $item['survey2'], 1),
                    '개선시기' => $this->getArrNameAll('s1', 3, $item['survey3'], 1),
                    '시술효과' => $this->getArrNameAll('s1', 4, $item['survey4'], 1),
                    '지인추천' => $this->getArrNameAll('s1', 5, $item['survey5'], 1)
                ];
                $return['surveyType'] = '시술';
                break;
            case 2:
                $surveyAll = [
                    '상담' => $this->getArrNameAll('s2', 1, $item['survey1'], 1),
                    '시술시기' => $this->getArrNameAll('s2', 2, $item['survey2'], 1),
                    '지인추천' => $this->getArrNameAll('s2', 3, $item['survey3'], 1)
                ];
                $return['surveyType'] = '치과';
                break;
            case 3:
                $surveyAll = [
                    '상담' => $this->getArrNameAll('s3', 1, $item['survey1'], 1),
                    '시술시기' => $this->getArrNameAll('s2', 2, $item['survey2'], 1),
                    '개선시기' => $this->getArrNameAll('s3', 3, $item['survey3'], 1),
                    '시술효과' => $this->getArrNameAll('s3', 4, $item['survey4'], 1),
                    '지인추천' => $this->getArrNameAll('s3', 5, $item['survey5'], 1)
                ];
                $return['surveyType'] = '피부';
                break;
            case 4:
                $surveyAll = [
                    '상담' => $this->getArrNameAll('s4', 1, $item['survey1'], 1),
                    '재수술여부' => $this->getArrNameAll('s4', 2, $item['survey2'], 1),
                    '수술시기' => $this->getArrNameAll('s4', 3, $item['survey3'], 1),
                    '시술효과' => $this->getArrNameAll('s4', 4, $item['survey4'], 1),
                    '지인추천' => $this->getArrNameAll('s4', 5, $item['survey5'], 1),
                    '부기' => $this->getArrNameAll('s4', 6, $item['survey6'], 1)
                ];
                $return['surveyType'] = '성형';
                break;
            case 5:
                //echo $item['survey2']."\n\r\n\r";
                $surveyAll = [
                    '상담' => $this->getArrNameAll('s5', 1, $item['survey1'], 1),
                    '교정 전 시력' => $this->getArrNameAll('s5', 2, $item['survey2'], 1),
                    '교정 후 시력' => $this->getArrNameAll('s5', 3, $item['survey3'], 1),
                    '시력회복' => $this->getArrNameAll('s5', 4, $item['survey4'], 1),
                    '지인추천' => $this->getArrNameAll('s5', 5, $item['survey5'], 1)
                ];
                $return['surveyType'] = '라식';
                break;
        }
        $aa = '';
        foreach ($surveyAll as $key => $val) {
            $aa .= $key . " : " . $val . ' || ';
        }

        $return['surveyAll'] = $aa;

        return $return;
    }

    /**
     * 후기 상세설문 내용 리턴(개별)
     * @param $type
     * @param $section
     * @param $result
     * @param $what 0 boardMain에서 사용하는 배열, 1 boardList에서 사용하는 개별값 형태
     * @return string
     */
    function getArrNameAll($type, $section, $result, $what='0')
    {
        //echo 'result: '.$result."\n\r";
        //$result2 = $result;
        //$result = urldecode($result);

        $section2 = $section; //what=1일 경우 예외처리를 위해
        $section = $section - 1;
        $answerArr1 = $this->surveryArray[$this->thisVersion][$type][$section]['answer'];

        if($what == 0)
        {
            $i = $check = 0;
            $resArr = explode('|', $result);
            $resCnt = count($resArr); //3
            $resSum = array_sum($resArr);
            $return = [];

            foreach ($resArr as $key=>$val)
            {
                $ccc = $val==0? 0:round(($val/$resSum)*100);
                $return[] = $ccc.'|'.$answerArr1[$key]['name'];
            }

            return implode(',', $return);
        }
        else
        {
            $return = '';

            if($result === '' or $result === ' ' or $result === 0)
            {
                //echo 'result3: '; var_dump($result2)."\n\r";
                return ''; //빈 값이면 그냥 빈 값으로 리턴
            }
            else
            {
                //echo $type.'--'. $section2.'--'. $result.'--=========';
                //echo 'result2: '; var_dump($result2)."\n\r";
                //var_dump(strpos($result, '5/'));
                if($type == 's1' and $section2 == 2 and strpos($result, '5/') !== false)
                {
                    $res = explode('/', $result);
                    $return = $res[1];
                }
                else if($type == 's2' and $section2 == 2 and strpos($result, '5/') !== false)
                {
                    $res = explode('/', $result);
                    $return = $res[1];
                }
                else if($type == 's3' and $section2 == 2 and strpos($result, '5/') !== false)
                {
                    $res = explode('/', $result);
                    $return = $res[1];
                }
                else if($type == 's4' and $section2 == 3 and strpos($result, '5/') !== false)
                {
                    $res = explode('/', $result);
                    $return = $res[1];
                }
                else if($type == 's5' and $section2 == 4 and strpos($result, '5/') !== false)
                {
                    $res = explode('/', $result);
                    $return = $res[1];
                }
                else if($type == 's5' and $section2 == 2)
                {
                    $res = explode('|', $result); //var_dump($res);
                    switch ($res[2])
                    {
                        case 111:
                            $chks = '난시,근시,원시';
                            break;
                        case 110:
                            $chks = '난시,근시';
                            break;
                        case 100:
                            $chks = '난시';
                            break;
                        case 101:
                            $chks = '난시,원시';
                            break;
                        case 11:
                            $chks = '근시,원시';
                            break;
                        case 1:
                            $chks = '원시';
                            break;
                        case 10:
                            $chks = '근시';
                            break;
                        default:
                            $chks = '';
                            break;
                    }

                    $vals1 = '';

                    if($res[0] != '')
                    {
                        $vals1 .= '좌 '.$res[0].'/';
                    }
                    if($res[1] != '')
                    {
                        $vals1 .= '우 '.$res[1].'/';
                    }
                    if($res[2] != '')
                    {
                        $vals1 .= $chks.'/';
                    }


                    if($res[3] != '')
                    {
                        if($res[3] == 2)
                        {
                            $return = $vals1.'재수술';
                        }
                        else
                        {
                            if($vals1 == '')
                            {
                                $return = '';
                            }
                            else
                            {
                                $return = substr($vals1, 0,strlen($vals1)-1);
                            }
                        }
                    }
                    else
                    {
                        if($res[3] == 1)
                        {
                            if($vals1 == '')
                            {
                                $return = '';
                            }
                            else
                            {
                                $return = substr($vals1, 0,strlen($vals1)-1);
                            }
                        }
                    }
                }
                else
                {
                    //echo $result.'--=';
                    $result = $result - 1;
                    $return = $answerArr1[$result]['name'];
                }

                return $return;
            }
        }
    }

    /**
     * 배열의 가장 큰 값 반환.
     * 중복된 값이 있을 경우 처음 나온 인덱스의 값을 %로 반환한다.
     * @param $data
     * @return mixed
     */
    function maxArrayValue($data)
    {
        //%로 변환한다.
        $dataSum = array_sum($data);
        $data2 = [];
        foreach ($data as $key=>$val)
        {
            $data2[$key] = $val==0? 0:round(($val/$dataSum)*100);
        }
        //큰 값
        $maxInt = max($data2);

        //그 값의 index
        $maxIntKey = array_search($maxInt, $data2);
        $maxValue = $data2[$maxIntKey]; //제일 큰 값
        return $maxValue."|".$maxIntKey;
    }

    /**
     * 후기 상세설문 내용 리턴(summary)
     * @param $type
     * @param $section
     * @param $result
     * @return string
     */
    function getArrName($type, $section, $result)
    {
        $section = $section - 1;
        $answerArr1 = $this->surveryArray[$this->thisVersion][$type][$section]['answer'];
        $s11Arr = explode("|", $this->maxArrayValue(explode('|', $result)));
        return $s11Arr[0].'|'.$answerArr1[$s11Arr[1]]['name'];
    }
}