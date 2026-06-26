<?php

use Aws\S3\S3Client;

defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Class archive
 * v2 request data archive
 */

class RequestArchive extends CI_Controller
{
    function __construct()
    {
        parent::__construct();

        //$this->v2 = $this->load->database('goodocV2Stg', true); //v2 스테이징, 삭제도 해야해서 마스터로 사용
        $this->v2 = $this->load->database('goodocV2', true); //v2 운영, 삭제도 해야해서 마스터로 사용
        $this->arc = $this->load->database('archive', true); //archive master

        //exit; echo '11';
    }


    /**
     * 신청db 입력
     * 새벽 4시 진행.
     * 일 900~1600개 정도.(대략)
     */
    function callRequestSend()
    {
        $this->load->library('zip');

        set_time_limit(0);
        ini_set('memory_limit','-1');

        $data['lastDate'] = date("Y-m-d 15:00:00", strtotime('-2 years -1 day'));
        $data['thisDate'] = date("Y-m-d 15:00:00", strtotime('-2 years'));
        $data['thisMonth'] = date("Ym", strtotime('-2 years'));
        $data['thisDay'] = date("Ymd", strtotime('-2 years'));

        //dd($data);

        //call_request
        $sql999 = "select * from call_request where regDate >= '".$data['lastDate']."' and regDate < '".$data['thisDate']."'";
        $pArr = $this->v2->query($sql999)->result_array(); //echo $sql999;

        $count = count($pArr);

        if($count > 0)
        {
            //파일 저장
            $fileJson = json_encode($pArr, JSON_UNESCAPED_UNICODE);
            //dd($fileJson);

            //월별로 저장
//            $name = $data['thisDay'].'.json';
//            //$path = '/home/data/'.$data['thisMonth'].'/'.$data['thisDay'].'.zip';
//            $path = UP_ROOT.'/'.$data['thisMonth'].'/'.$data['thisDay'].'.zip';
//            $pathDir = UP_ROOT.'/'.$data['thisMonth'].'/';

            //디렉토리 생성
//            if( !is_dir($pathDir) )
//            {
//                @mkdir($pathDir, 0777, true);
//            }

//            $this->zip->add_data($name, $fileJson);
//            $this->zip->archive($path);

            //s3 업로드
            $bucketName = 'event-request-db-archive';
            $toPath = $data['thisMonth'].'/'.$data['thisDay'].'.json';

            $s3Client = S3Client::factory([
                'region'        => 'ap-northeast-2',
                'version'       => 'latest',
                'signature'     => 'v4',
                'credentials'   => [
                    'key'    => S3Key,
                    'secret' => S3Secret
                ]
            ]);


            $returnS3 = $s3Client->putObject(array(
                'Bucket' => $bucketName,
                'Key'    => $toPath,
                'Body' => $fileJson,
                'ACL'    => 'private'
            ));

            //dd($returnS3);

            //아카이빙 데이터 입력
            $return = $this->arc->insert_batch('call_request', $pArr);

            //삭제
            if( $return == $count )
            {
                //입력성공한 수가 최초 갯수와 동일하면 삭제처리
                $sqlDelete = "delete from call_request where regDate >= '".$data['lastDate']."' and regDate < '".$data['thisDate']."'";
                $this->v2->query($sqlDelete);
                echo $return.' 개 아카이브 성공';
            }
            else
            {
                //실패처리 어떻게 할지?
            }
        }
        else
        {
            echo '신청db수가 0이어서 저장처리 안함';
        }


    }

}