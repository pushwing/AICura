<?php
use GuzzleHttp\Client;
use GuzzleHttp\Psr7;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;
/**
 * V1-V2 이벤트 리플리케이터
 * 각 액션별로 Ads_m 에서 호출하여 사용.
 * V2 분리후엔 폐기한다.
 * 2018. 12. 07 martin
 */


class Replicator_m extends CI_Model
{
    function __construct()
    {
        parent::__construct();
        $this->load->library('curl');
    }

    /**
     * v1 api에 데이터 전송
     * @param $url
     * @param $method
     * @param $data
     * @return mixed|string
     * @throws Exception
     */
    function send($url, $method, $data)
    {
        $headers = array('x-api-key:CzEyvC+Q20PFJcBpUm2ApjATq8jq+p1jT/kjjg9wfgg');
        $ch = curl_init();
        curl_setopt($ch,CURLOPT_URL,REPLICATOR_URL.$url);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
        switch ($method)
        {
            case "POST":
                curl_setopt($ch, CURLOPT_POST, 1);
                break;
            case "PATCH":
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
                break;
            case "PUT":
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
                break;
            case "DELETE":
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
                break;
        }   
        //바로 종료로 인한 타임아웃 추가
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 0); 
        curl_setopt($ch, CURLOPT_TIMEOUT, 60); 

        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $output=curl_exec($ch);

        if($output === false)
        {
            $result = curl_error($ch);
        }
        else
        {
            $result = json_decode($output, true);
        }

        curl_close($ch);

        if(ENVIRONMENT != 'production')
        {
            //로그남김 스테이징까지
            monologSend('allLog', $result, $data);
        }

        return $result;
    }


    /**
     * v2에서 v1으로 특정 이벤트정보 마이그레이션(리플리케이션)
     * @param $data
     * @param $data 1 승인, 2 종료
     * @return bool|int
     * @throws Exception
     */
    function v2FromV1Event($data, $type=1)
    {
        $this->v1 = $this->load->database('goodoc', true);
        //$this->v1 = $this->load->database('goodocStg', true);
        $this->v2 = $this->load->database('goodocV2', true);
        //$this->v2 = $this->load->database('goodocV2Stg', true);

        $this->load->model(['common_m']);

        //대상이벤트 정보 가져오기
        $resultArr = explode(',', $data);

        foreach ($resultArr as $item)
        {
            //리플리케이터 시작
            //이벤트 승인 PATCH /api/events/{event_id}

            $sql = "
                select  (select id from ads_history where adsId=ads.id order by id desc limit 1) historyId, ads.*, ads.vT1ImageName as t1, ads.vT2ImageName as t2
                from ads 
                where id = '".$item."'
            "; echo $sql;

            $newInfo = $this->v2->query($sql)->row_array();

            dd($newInfo, false);

            $param['adsId'] = $newInfo['id'];
            $param['historyId'] = $newInfo['historyId'];

            $historyData = $this->gethistoryMerge($param);

            dd($historyData, false);

            $image_arr = ['t1', 't2'];
            $imageArr  = '';
            foreach( $image_arr as $val){
                if (isset($newInfo[$val]) && !is_null($newInfo[$val])){
                    $imageArr .= $imageArr === '' ? $val.'|'.$newInfo[$val] : ','.$val.'|'.$newInfo[$val];
                }
                unset($newInfo[$val]);
            }
            $newInfo['image'] =$imageArr;

            //본문 이미지
            $newdImageJson = is_null($newInfo['dImageJson']) || empty($newInfo['dImageJson']) ? '[]' : json_decode($newInfo['dImageJson']);

            //노출영역 처리
            $isE = '0';
            $isH = '0';
            if($historyData['exposure'] == 3)
            {
                //둘다 라면
                $isE = '1';
                $isH = '1';
            }
            else if($historyData['exposure'] == 2)
            {
                $isE = '0';
                $isH = '1';
            }
            else if($historyData['exposure'] == 1)
            {
                $isE = '1';
                $isH = '0';
            }


            //리플리케이터 시작
            //d이미지 처리용
            $iii=1;
            $dImageArr = [];
            foreach($newdImageJson as $key => $val){
                $dImageArr[] = ['client_sort'=>$iii, 'client_image'=>$val];
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
            $t1ImageName = $t2ImageName = '';
            $iArr4 = explode(',', $newInfo['image']);

            foreach ($iArr4 as $item44)
            {
                $iArr44 = explode('|', $item44);

                if($iArr44[0] == 't1')
                {
                    $t1ImageName = $iArr44[1];
                }
                else if($iArr44[0] == 't2')
                {
                    $t2ImageName = $iArr44[1];
                }
            }


            //is_client_image2_change 정방향 이미지 변경여부 체크
            //adOriInfo(업데이트전)의 t2 이미지와 newInfo(히스토리 머지 업데이트 후)의 t2 이미지를 비교
            //if($adOriInfo['t2ImageName'] == $t2ImageName)

            //v1애 이미지가 아예 없기 때문에 업데이트를 한다.
            $is_client_image2_change = 1;

            //옵션처리
            $oArr = [];
            if($newInfo['vOptions'] != '')
            {
                $subArr = explode(',', $newInfo['vOptions']);

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
                if( isset($newInfo[$key]) && !is_null($newInfo[$key]))
                {
                    $customDataArr[$val] = $newInfo[$key];
                    $encodeCheck = true;
                }
                else
                {
                    $customDataArr[$val] = '';
                }
            }

            //커스텀값 한번더 체크.
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
                'event_category_ids'=>$historyData['category'], //머지 데이터로 변경
                'hospital_id'=>$newInfo['hospitalId'],
                'search_tags'=>$newInfo['vKeyword'],
                'external_media_category_ids'=>$newInfo['vCooperation'],
                'client_title'=>$newInfo['adTitle'],
                'client_is_temporary'=> ($newInfo['adDateExtend']=='Y')?'1':'0', // 1 상시진행, 0 기간설정
                'client_start_on'=>$newInfo['adStartDate'],
                'client_end_on'=>$newInfo['adEndDate'],
                'client_is_numerical_original_price'=>($newInfo['costType'] == 1)?'1':'0', //0이면 텍스트가격
                'client_numerical_original_price'=>$reGeneralCost,
                'client_original_price'=>($newInfo['costType'] == 1)?$reGeneralCost:0,
                'client_is_numerical_discounted_price'=>($newInfo['costType'] == 1)?'1':'0',
                'client_numerical_discounted_price'=>$reDiscountCost,
                'is_bm_banner_show'=>($historyData['exposure'] == 1)?'1':'0', //메인배너 노출 여부 (노출 1, 비노출 0)
                'client_discounted_price'=>($newInfo['costType'] == 1)?$reDiscountCost:$newInfo['textCost'],
                'event_infos'=>$dImageArrJson,
                'client_event_category_ids'=>$newInfo['category'],
                'client_image2'=>$t2ImageName,
                'client_image'=>$t1ImageName,
                'search_tags'=>$newInfo['vKeyword'],
                'client_consider_number'=>is_null($newInfo['deliberationCode'])?'':$newInfo['deliberationCode'], //의료심의번호
                'model_image_ids'=>(is_null($newInfo['whereImage']))?'':$newInfo['whereImage'],
                'event_cost'=>$historyData['dbCost'], //머지 데이터로 변경
                'event_regions'=>$newInfo['vRegion'],
                'apply_text'=> $reApply_text,
                'apply_back_color' => $reApply_back_color, //버튼 컬러
                'apply_text_color' => $reApply_text_color, //버튼 텍스트 컬러
                'apply_image_count'=>(is_null($newInfo['modelImageCount']))?'0':$newInfo['modelImageCount'],
                'option_event_infos'=>$oArrJson,
                'hospital_operator_infos'=>$customData,
                'is_visible_on_event_list'=>$isE,
                'is_visible_on_hospital_show'=>$isH
            ];

            dd($insData, false);
            monologSend('event_modify', json_encode($insData));
            $result99 = $this->send('/api/events/'.$newInfo['id'], 'PATCH', $insData);
            monologSend('event_modify', $result99);

            //승인처리
            $cooArr = explode(',', $newInfo['vCooperation']);

            //$cooArr = arr_del($cooArr, 7);
            //$cooArr = arr_del($cooArr, 8);

            $cooperation2 = implode(',', $cooArr);

            if($type == 1)
            {
                //이벤트 승인처리
                $insData2 = [
                    'type_info'=>'event_confirm',
                    'event_cost'=>$historyData['dbCost'],
                    'event_regions'=>$newInfo['vRegion'],
                    'apply_text'=> $reApply_text, //이벤트 등록, 후기 뷰의 워딩과 동일하게 처리
                    'is_visible_on_event_list'=>$isE,
                    'is_visible_on_hospital_show'=>$isH,
                    'client_event_category_ids'=>$historyData['category'],
                    'search_tags'=>$newInfo['vKeyword'],
                    'external_media_category_ids'=>$cooperation2,
                    'is_client_image2_changed'=>$is_client_image2_change
                ];

                $loginsData = $insData2;
                $loginsData["method"] =[ 'PATCH', '/api/events/'.$newInfo['id']];
                monologSend('inspect', json_encode($loginsData));
                $result00 = $this->send('/api/events/'.$newInfo['id'], 'PATCH', $insData2);
                monologSend('inspect', $result00);

                //광고주 상태 업데이트 처리
                $data3 = [
                    'contractId'=>$newInfo['contractId'],
                    'type'=>2
                ];
                $this->common_m->updateTotalInfo($data3);
            }
            else if ($type == 2)
            {
                //종료
                $insData2 = [
                    'type_info'=>'force_end'
                ];
                monologSend('inspect', json_encode($insData2));
                $result00 = $this->send('/api/events/'.$newInfo['id'], 'PATCH', $insData2);
                monologSend('inspect', $result00);
            }


            //리플리케이터 끝
        }



        return true;
    }

    /**
     * adsID 에 맞는 히스토리 정보를 뽑아서 병합 후 리턴
     * @param $data array
     * @return array
     */
    public function gethistoryMerge(array $data, bool $ins = false) : array
    {
        //히스토리 가져 와서 병합한다.
        //dd($data);
        $data['orderby'] = 'asc';
        if ($ins === true)
        {
            $historys = $this->getHistoryListIns($data);
        }
        else
        {
            $historys = $this->getHistoryListSec($data);
        }
        log_message('error', 'historyMerge1 : '.json_encode( [$historys]));

        //exit;
        $historyInfo = [];
        $dImageJson  = '';
        $hSize = sizeof($historys['info']) > 0 ? sizeof($historys['info']) - 1 : 0;
        foreach($historys['info'] as $key => $history)
        {
            //최초 히스토리는 전체 데이터가 들어가 있으니 바로 병합
            $deleteArr = isset($history['deletejson']) ? json_decode($history['deletejson'], true) : [];

            $historyInfo = array_merge($historyInfo, $deleteArr);
            if ($hSize == $key) {
                $dImageJson = $history['dImageJson'];
            }
        }

        $check_date = ['adStartDate', 'adEndDate'];
        foreach($check_date as $key=>  $val)
        {
            $reDate = '';
            if(isset($historyInfo[$val]) && !is_null($historyInfo[$val]) && strpos($historyInfo[$val], '-') === false)
            {
                $reDate = substr($historyInfo[$val], 0, 4).'-'.substr($historyInfo[$val], 4, 2).'-'.substr($historyInfo[$val], 6, 2);
                $historyInfo[$val] = $reDate;
            }
        }

        $historyInfo['dImageJson'] = $dImageJson;
        //log_message('info', 'historyMerge3 : '.json_encode( [$historyInfo]));
        return $historyInfo;
    }

    /**
     * 히스토리 머지용
     *
     */
    /**
     * 광고 히스토리 리스트
     * @param $data
     * @return array
     */
    function getHistoryListSec($data)
    {
        if ( isset($data['refreshToken']) )
        {
            unset($data['refreshToken']);
        }

        $param = [];
        if ( isset($data['refreshToken']) )
        {
            unset($data['refreshToken']);
        }
        if ((defined('USERAUTHCODE') &&  USERAUTHCODE == 2 ) && isset($data['hospitalId']))
        {
            //$param['hospitalId'] = $data['hospitalId'];
        }


        $param['adsId']  = $data['adsId'];

        if(isset($data['historyId']))
        {
            $this->v2->where('id >=', $data['historyId']);
        }

        $history['totCnt'] = $this->v2->get_where('ads_history', $param)->num_rows();

        if (isset($data['limit']))
        {
            $limit = ($data['page'] - 1) * $data['limit'];
            $this->v2->limit($data['limit'], $limit);
        }

        $orderby = isset($data['orderby']) ? $data['orderby'] : 'desc';

        $this->v2->order_by('id', $orderby);
        $history['info'] = $this->v2->get_where('ads_history', $param)->result_array();
        //dd($this->v2->last_query());
        //log_message('info', 'historyListSec4 : '.json_encode( [$checkDb->last_query()]));
        //var_dump($history);
        return $history;
    }




}