<?php 
defined('BASEPATH') OR exit('No direct script access allowed');

class MakeTemplate
{
    protected $eventServePath = [
        'templatePath'  => 'template/{templatePath}/',
        'defaultPath'   => 'uploads/events/{defaultPath}/'
    ];

    protected $time = null;

    public function __construct()
    {
        $this->time = time(); 
    }

    /**
     * 이벤트 업로드 서버 경로 설정
     * @param $pathParam array 이벤트 형태 템플릿 번호, 이벤트 번호 
     * @return bool
     */ 
    public function get_set_eventServerPath(array $pathParam) : bool
    {   
        foreach($this->eventServePath as $k => $v) 
        {  
            $this->eventServePath[$k] = str_replace('{'.$k.'}', $pathParam[$k], $this->eventServePath[$k]);
        }

        $this->eventServePath['defaultPath'] .= time().'/';

        if (!is_dir($this->eventServePath['defaultPath'])) 
        {
            if (!mkdir($this->eventServePath['defaultPath'], 0777, true))
            {
                return false;   
            }
        }
        
        return true;
    }

    /**
     * type 에 해당하는 템플릿 치환
     * @param int $type
     * @param array $data
     * @return array
     */
    protected function setTemplate(int $type, array $data) : array
    {
        $fileNames = ['event.js', 'event.css', 'index.html'];

        $filecopy_check = true;
        for ($i=0; $i < 2; $i++)
        {
            //기본파일 복사
            if(!copy($this->eventServePath['templatePath'].$fileNames[$i], $this->eventServePath['defaultPath'].$fileNames[$i]))
            {
                $filecopy_check= false;
                break;
            }
        }

        if ($filecopy_check === false)
        {
            return [
                'success' => false
                ,'code'   => 500
            ];
        }

        //index.html 읽어들임
        $indexFile = file_get_contents($this->eventServePath['templatePath'].$fileNames[2]);
        //dd($data, false);
        //dd($indexFile, false);

        if($data['textCost'])
        {
            $discountCost = $data['textCost'];
        }
        else
        {
            $discountCost = number_format($data['discountCost']);
        }

        $adDetailInfo = json_decode($data['adDetailInfo']);

        //상세이미지 처리
        $imageArr = json_decode($data['dImageJson']); //dd($imageArr,false);
        $images = '';
        foreach ($imageArr as $item)
        {
            if($item)
            {
                $images .= '<img class="event-info-image" src="'.$item.'"><br>';
            }
        }

        //var_dump($images); exit;
        //html 내용치환
        $desc = "굿닥 입점된 ".$data['hospitalName']." 병원에서 제공하는 ".$data['adTitle']." 수술/시술 관련 이벤트를 다양한 혜택으로 제공합니다.";
        $indexFile = str_replace('{{url_path}}', 'https://www.goodoc.co.kr/events/'.$data['id'], $indexFile);
        $indexFile = str_replace('{{thumb_nail_image}}', $data['t1ImageName'], $indexFile);
        $indexFile = str_replace('{{description}}', $desc, $indexFile);
        $indexFile = str_replace('{{subject}}', '메인 > 이벤트 모아보기 > '.$data['oneDepth'], $indexFile);
        $indexFile = str_replace('{{one_depth}}', $data['oneDepth'], $indexFile);
        $indexFile = str_replace('{{one_depth_id}}', $data['oneDepthId'], $indexFile);
        $indexFile = str_replace('{{two_depth}}', $data['twoDepth'], $indexFile);
        $indexFile = str_replace('{{two_depth_id}}', $data['twoDepthId'], $indexFile);

        $indexFile = str_replace('{{top-hospital-name}}', $data['hospitalName'], $indexFile);
        $indexFile = str_replace('{{top-hospital-location}}', $data['hospitalAddress'], $indexFile);
        $indexFile = str_replace('{{startDate}}', $data['adStartDate'], $indexFile);
        $indexFile = str_replace('{{endDate}}', $data['adEndDate'], $indexFile);
        $indexFile = str_replace('{{title}}', $data['adTitle'].' - '.$data['hospitalName'].' | 병원약국검색어플, 굿닥', $indexFile);
        $indexFile = str_replace('{{title2}}', $data['adTitle'], $indexFile);
        $indexFile = str_replace('{{originalPrice}}', number_format($data['generalCost']), $indexFile);
        $indexFile = str_replace('{{discountedPrice}}', $discountCost, $indexFile);
        $indexFile = str_replace('{{image}}', $images, $indexFile);
        $indexFile = str_replace('{{date}}', date("Y-m-d H:i:s"), $indexFile);

        //버튼부분. 프로그램화 필요 buttonType에 따라 주소나 전화번호가 있음
        $indexFile = str_replace('{{buttonName}}', $adDetailInfo[0], $indexFile);
        $indexFile = str_replace('{{buttonLink}}', $adDetailInfo[1], $indexFile);
        $indexFile = str_replace('{{buttonType}}', $adDetailInfo[2], $indexFile);
        $indexFile = str_replace('{{buttonPhone}}', $adDetailInfo[3], $indexFile);
        $indexFile = str_replace('{{buttonColor}}', $adDetailInfo[4], $indexFile);
        $indexFile = str_replace('{{buttonNameColor}}', $adDetailInfo[5], $indexFile);

        //치환 성공후 파일 복사
        file_put_contents($this->eventServePath['defaultPath'].'index.html', $indexFile);

//        array(2) {
//        ["success"]=>
//          bool(true)
//          ["files"]=>
//          array(3) {
//                    [0]=>
//            string(86) "/Users/blumine/works/goodoc_v2/event/adminApi/uploads/events/10135/1576113582/event.js"
//                    [1]=>
//            string(87) "/Users/blumine/works/goodoc_v2/event/adminApi/uploads/events/10135/1576113582/event.css"
//                    [2]=>
//            string(88) "/Users/blumine/works/goodoc_v2/event/adminApi/uploads/events/10135/1576113582/index.html"
//          }
//        }

        return [
            'success'   => true,
            'files'     => [
                $this->eventServePath['defaultPath'].'event.js',
                $this->eventServePath['defaultPath'].'event.css',
                $this->eventServePath['defaultPath'].'index.html'
            ]
        ];
    }
}