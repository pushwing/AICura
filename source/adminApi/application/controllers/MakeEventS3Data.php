<?php

use Aws\S3\S3Client;

defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * 검색용 이벤트 초기 데이터 생성
 */
class MakeEventS3Data extends CI_Controller
{
    function __construct()
    {
        parent::__construct();

        $this->load->helper(['common', 'file']);
        $this->master = $this->load->database('goodocV2', true);
        $this->load->model(['ads_m', 'common_m']);
    }

    /**
     * 초기데이터 생성
     */
    function firstAction()
    {
        set_time_limit(0);
        ini_set('memory_limit','-1');

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

        $s3Client = Aws\S3\S3Client::factory(array(
            'region' => 'ap-northeast-2',
            'version' => 'latest',
            'signature' => 'v4',
            'credentials' => array(
                'key'    => S3Key,
                'secret' => S3Secret
            )
        ));

        $toPath = 'search/event/event_init_'.time().'.json';

        $this->master->select('ads.id, ads.isLive, ads.vHospitalId as hospitalId,ec.title categoryName');

        $this->master->where('channel', 1); //굿닥만
        $this->master->where('ads.id', 14453);
        $this->master->join('event_categories ec', 'ads.vCategory=ec.id', 'left');
        //$this->master->limit(200);
        $result = $this->master->get('ads')->result_array();
        //$totCnt = count($result);

        //dd($result);

        $return = [];
        foreach ($result as $item)
        {
            $adsInfo = $this->ads_m->gethistoryMerge(['adsId' => $item['id']]);
            dd($adsInfo, false);

            $delArr = [
                'costType', 'dbCost', 'whereImage', 'modelImageCount', 'adDetailInfo', 'regDate',
                'agencyUserId', 'isViewBoard', 'deliberationCode', 'customRanding', 'custom1',
                'custom2', 'custom3', 'searchable', 'subHospitalId', 'hospitalType', 'optionAdId',
                'userId', 'cooperation', 't1ImageName', 'modDate', 'isLive', 'adStatus', 'subAdStatus', 'dImageJson'
            ];

            foreach ($delArr as $dt)
            {
                unset($adsInfo[$dt]);
            }

            $regionArr = explode(',',$adsInfo['region']);
            $rgArr = [];
            foreach ($regionArr as $rg)
            {
                switch ($rg)
                {
                    case 1:
                        $rgArr[] = '서울';
                        break;
                    case 2:
                        $rgArr[] = '부산';
                        break;
                    case 3:
                        $rgArr[] = '인천';
                        break;
                    case 4:
                        $rgArr[] = '대구';
                        break;
                    case 5:
                        $rgArr[] = '광주';
                        break;
                    case 6:
                        $rgArr[] = '대전';
                        break;
                    case 7:
                        $rgArr[] = '울산';
                        break;
                    case 8:
                        $rgArr[] = '경기';
                        break;
                    case 9:
                        $rgArr[] = '강원';
                        break;
                    case 10:
                        $rgArr[] = '충북';
                        break;
                    case 11:
                        $rgArr[] = '충남';
                        break;
                    case 12:
                        $rgArr[] = '전북';
                        break;
                    case 13:
                        $rgArr[] = '전남';
                        break;
                    case 14:
                        $rgArr[] = '경북';
                        break;
                    case 15:
                        $rgArr[] = '경남';
                        break;
                    case 16:
                        $rgArr[] = '제주';
                        break;
                    case 17:
                        $rgArr[] = '세종';
                        break;
                }
            }
            unset($adsInfo['region']);
            $adsInfo['region'] = implode(',', $rgArr);

            $adsInfo['isLive'] = $item['isLive'];
            $adsInfo['categoryName'] = $item['categoryName'];

            $return[] = $adsInfo;
        }


        $hospitalId_arr    = [];
        foreach($return as $key => $val)
        {
            if (!is_null($val['hospitalId']) && !empty($val['hospitalId']) ) {
                $hospitalId_arr[] = $val['hospitalId'];
            }
        }

        //중복 제거
        $hArr2 = $hospitalId_arr = array_unique($hospitalId_arr); //dd($hospitalId_arr, false);

        //190102 병원 명 가져오기
        //result = array, key : 병원ID , val : 병원명
        if (count($hospitalId_arr) > 0 )
        {

            //190102 병원명 가져오기
//            $count = round(count($hospitalId_arr) / 50);
//            for($k=0;$k < $count; $k++)
//            {
//                $hArr3 = array_slice($hArr2, 0, 50); //가져오고
//                //dd($hArr3, false);
//
//                //$hArr2 = array_slice($hArr2, 0, 50); //삭제하고
//                //dd($hArr2, false);
//                $hospitalNamesArr = $hospitalNamesArr2 = $this->goodocapi->getHospitalInfosByIds($hArr3); //dd($hospitalNamesArr2);
//
//                if($k > 0)
//                {
//                    $hospitalNamesArr = array_merge($hospitalNamesArr, $hospitalNamesArr2);
//                }
//
//                for($j=0;$j<50;$j++)
//                {
//                    unset($hArr2[$j]);
//                }
//                $hArr2 = array_values($hArr2);
//
////                echo '<br><br>---22222----------<br><br>';
////                dd($hArr2, false);
////                echo '<br><br>---22222----------<br><br>';
//            }

            //dd($hospitalNamesArr);

            foreach($return as $key => $val)
            {
                $hospitalNamesArr = $this->goodocapi->getHospitalInfosByIds([$val['hospitalId']]); //dd($hospitalNamesArr2);

                if (!is_null($val['hospitalId']) && !empty($val['hospitalId']) ) {
                    $hospitalInfo = isset($hospitalNamesArr[$val['hospitalId']]) ? $hospitalNamesArr[$val['hospitalId']] : [];
                }
                else {
                    $hospitalInfo = [];
                }

                $hospitalName = '병원명없음';
                $hospitalType = 0; // 0 : 일반, 1 : 모병원, 2: 자병원
                if(sizeof($hospitalInfo) > 0)
                {
                    $hospitalName = $hospitalInfo['name'];
                    $hospitalType = $hospitalInfo['networkType'];
                }

                $return[$key]['hospital']['id'] = $return[$key]['hospitalId'];
                $return[$key]['hospital']['name'] = $hospitalName;
                $return[$key]['hospital']['address'] = $hospitalInfo['addr'];
                //$result[$key]['hospitalType'] = $hospitalType;
                unset($return[$key]['hospitalId']);
                unset($hospitalName, $hospitalType);
            }
        }

        //$resultArr = ['list'=>$return, 'totCnt'=>$totCnt];

        //dd($return);

        //파일로 저장
        $toFile = 'uploads/event_init_'.time().'.json';
        write_file($toFile, json_encode($return, JSON_UNESCAPED_UNICODE));

        //s3 업로드
//        $return2 = $s3Client->putObject(array(
//            'Bucket' => $bucketName,
//            'Key'    => $toPath,
//            //'Body' => json_encode($return, JSON_UNESCAPED_UNICODE),
//            'SourceFile' => $toFile,
//            'ACL'    => 'private'
//        ));
//
//        dd($return2);
    }

}