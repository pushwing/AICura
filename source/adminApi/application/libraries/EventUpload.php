<?php 
defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH . '/libraries/MakeTemplate.php';

use Aws\S3\S3Client;
use Aws\S3\Exception\S3Exception;
use Aws\Credentials\Credentials;
use Aws\Exception\AwsException;
use Aws\CloudFront\CloudFrontClient;


class EventUpload extends MakeTemplate
{
    private $bucketName = null;

    private $CI = null;

    public $s3Client = null;

    public function __construct()
    {
        $this->init();
        
        parent::__construct();
    }   

    /**
     * S3 클라이언드 셋팅
     * @return object
     */
    public function s3Client_init() : bool
    {
        $this->s3Client = S3Client::factory([
            'region'        => 'ap-northeast-2',
            'version'       => 'latest',
            'signature'     => 'v4',
            'credentials'   => [
                'key'    => S3Key,
                'secret' => S3Secret
            ]
        ]);
        return true;
    }

    /**
     * 클래스 생성시 실행
     * @return bool
     */
    private function init() : bool
    {
        //버킷명 정의
        if( ENVIRONMENT === 'development' )
        {
            $this->bucketName = 'asset-staging.goodoc.kr';
            //$this->bucketName = 'asset-dev.goodoc.kr';
//            $this->bucketName2 = 'event-file-dev';
//            $this->cfId = 'E3PFNR165LPAG';
            $this->bucketName2 = 'event-file-stg';
            $this->cfId = 'ERVVIK9KSNVVU';
        }
        else if( ENVIRONMENT === 'testing' )
        {
            $this->bucketName = 'asset-staging.goodoc.kr';
            $this->bucketName2 = 'event-file-stg';
            $this->cfId = 'ERVVIK9KSNVVU';
        }
        else if (ENVIRONMENT === 'production')
        {
            $this->bucketName = 'asset.goodoc.kr';
            $this->bucketName2 = 'event-file-prd';
            $this->cfId = 'E2NZTFWIT9M7K4';
        }

        $this->CI = &get_instance();

        return true;
    }
    
    /**
     * 메소드 콜로 파일 업로드 
     * @param $type string 이벤트 템플릿 타입
     * @param $data array 데이터
     * @param $inspect int 검수여부. 기본 0
     *
     * @return array 
     */
    public function file_call(string $type, array $data, int $inspect=0) : array
    {  
        if(empty($type))
        {
            return [];
        }
        
        parent::get_set_eventServerPath([
            'templatePath' => $type, 'defaultPath' => $data['id']
        ]);

        //내용 치환
        $tempResult = parent::setTemplate($type, $data); //dd($tempResult, false);

        if ( !isset($tempResult['success']) ||  $tempResult['success'] === false)
        {
            return ['success' => false, 'code' => 500];     
        }
       
        $config['upload_path'] = $this->eventServePath['defaultPath'];
        $config['allowed_types'] = "*";
        $config['encrypt_name']  = FALSE; //만들어진 이름 그대로 사용

        $this->s3Client_init();

        $return_arr = [];
        $upload_check = true;
        $mName = '';

        foreach($tempResult['files'] as $file)
        {
            //버킷명에 images 추가
            $nameArr = explode('/', $file);
            $dirName =  $nameArr[2].'/'.$nameArr[3].'/'.$nameArr[4];

            //히스토리용
            $bName = 'history/'.$dirName; //echo $bName; exit;
            //$rName = 'http://'.$this->bucketName2.'/'.$bName; //풀패스
            $rName = $bName; //풀패스

            $result = $this->s3Client_singleUpload( $bName, $file, $this->bucketName2, 'private');
            //dd($result, false);

            if($inspect == 1)
            {
                //검수에 들어온 것이면 메인으로 복사함
                $mNameArr = explode('/', $file);
                $mName = 'events/events/'.$mNameArr[2].'/'.$mNameArr[4];

                $this->s3Client_singleUpload( $mName, $file, $this->bucketName2, 'private');
            }

            $result['@metadata']['statusCode'] = '200';
            if ($result['@metadata']['statusCode'] != '200')
            {
                $upload_check = false;
                break;
            }
            
            if (strpos($rName, 'index.html') > -1 )
            {
                $return_arr = ['original'=>$rName, 'main'=>$mName];
            }  
        }

        if($inspect == 1)
        {
            //퍼지 sdk 호출
            $client2 = new Aws\CloudFront\CloudFrontClient([
                //'profile' => 'default',
                'version' => '2018-11-05',
                'region' => 'ap-northeast-2',
                'credentials'   => [
                    'key'    => CFKey,
                    'secret' => CFSecret
                ]
            ]);

            $callerReference = time();

            try {
                $result = $client2->createInvalidation([
                    'DistributionId' => $this->cfId,
                    'InvalidationBatch' => [
                        'CallerReference' => $callerReference,
                        'Paths' => [
                            'Items' => ['/events/'.$data['id'].'/*'],
                            'Quantity' => 1,
                        ],
                    ]
                ]);
                //dd($result, false);

            } catch (AwsException $e) {
                // output error message if fails
                echo $e->getMessage();
                echo "\n";
            }

        }
        
        return $upload_check === false ? [] : $return_arr;
    }

    /**
     * 업로드 베이스URL 전달 
     * @param $type string 파일 타입
     * @return string
     */
    public function get_upload_baseurl(string $type) : string
    {    
        switch ($type)
        {
            case "01": //유저 프로필
                $upload_basedir = UP_ROOT.'/user/picture/'.date("Ymd").'/';
                break;
            case "02": //파일첨부
                $upload_basedir = UP_ROOT.'/files/'.date("Ymd").'/';
                break;
            case "03": //후기
                $upload_basedir = UP_ROOT.'/board/picture/'.date("Ymd").'/';
                break;
            case "04": //이벤트배너
                $upload_basedir = UP_ROOT.'/event/banner/'.date("Ymd").'/';
                break;
            case "05": //이벤트 기획전
                $upload_basedir = UP_ROOT.'/event/package/'.date("Ymd").'/';
                break;
            case "06": //썸네일만들기
                $upload_basedir = UP_ROOT.'/thumb/'.date("Ymd").'/';
                break;        
            default :
                $upload_basedir = '';
                break; 
        }
        
        return $upload_basedir;
    }
    
    /**
     * aws S3 업로드 
     * @param $config array 업로드 설정
     * @return array
     */
    public function aws_upload(array $config) : array
    {
        $this->CI->load->library('upload', $config);

        if (!$this->CI->upload->do_upload('uploadfile'))
        {
            return [
                'status' => 'error',
                'code' => '612',
                'message' => '파일업로드 실패하였습니다.',
                'result' => $this->CI->upload->display_errors()
            ];
        }

        $data = $this->CI->upload->data(); 
        
        $this->s3Client_init();

        //썸네일 생성
        if (isset( $config['make_thumb']) && $config['make_thumb'] === true)
        {   
            $thumb_result = $this->aws_thumb_upload($data['full_path']);

            if ($thumb_result['status'] == 'error')
            {   
                return $thumb_result;
            } 
        }

        //이미지 업로드 시작
        $nameArr = explode('uploads', $data['full_path']);
        
        $dirName = $nameArr[1];
        
        //버킷명에 images 추가
        $bName = 'images'.$dirName; //echo $bName; exit;
        $rName = 'https://'.$this->bucketName.'/'.$bName; //풀패스
        
        $result = $this->s3Client_singleUpload($bName, $data['full_path'], $this->bucketName, 'public-read');
        
        if ($result['@metadata']['statusCode'] == '200')
        {
            return [
                'status' => 'success',
                'code' => '200',
                'message' => '파일 업로드 성공하였습니다.',
                'result' => (object) ['imageName' => $rName]
            ];
        }
  
        return [
            'status' => 'error',
            'code' => '612',
            'message' => 'S3 파일 업로드 실패하였습니다.',
            'result' => (object) []
        ];
        //가비지파일은 삭제한다.
        //unlink($data['full_path']);
    } 


    /**
     * 업로드 이미지 썸네일 변환 후 업로드
     * @param $fullpath string 소스이미지 경로
     * @return array
     */
    protected function aws_thumb_upload(string $fullpath) : array
    {
        if ($this->s3Client === null) 
        {
            $this->s3Client_init();    
        }
        
        $full_path_explode = explode('.' , $fullpath);
        
        $new_image  = $full_path_explode[0].'_t.'.$full_path_explode[1];
      
        $config_thumb   = [
            'image_library'     => 'gd2',
            'source_image'      => $fullpath,
            'new_image'         => $new_image,
            'maintain_ratio'    => TRUE,
            'width' => 150,
            'height' => 150    
        ];
           
        $this->CI->load->library('image_lib', $config_thumb);
            
        if (!$this->CI->image_lib->resize()) {
            return [
                'status' => 'error',
                'code' => '612',
                'message' => 'S3 파일 업로드 실패하였습니다.',
                'result' => (object) []
            ];
        }

        $this->CI->image_lib->clear();

        $nameArr = explode('uploads', $new_image);
        
        $dirName = $nameArr[1];
            
        //버킷명에 images 추가
        $bName = 'images'.$dirName; //echo $bName; exit;
        $rName = 'https://'.$this->bucketName.'/'.$bName; //풀패스
        
        $result = $this->s3Client_singleUpload($bName, $new_image, $this->bucketName, 'public-read');

        if ($result['@metadata']['statusCode'] != '200')
        {
            return [
                'status' => 'error',
                'code' => '612',
                'message' => 'S3 파일 업로드 실패하였습니다.',
                'result' => (object) []
            ];
        }

        return ['status' => 'success'];
    }

    /**
     * 싱글 파일 업로드
     * @param $key string Key
     * @param $fullPath string SourceFile
     * @param $bucket string Bucket
     * @param $acl string acl private or public-read
     * @return array
     */
    public  function s3Client_singleUpload( string $key, string $fullPath, string $bucket, string $acl)
    {
        /**
         * TODO
         * 7.1 버전 타입 힌팅 ?Aws\Result 로 처리 가능 public function ~~~ () ? Aws\Result 
         * 정상 동작했을때 Aws\Result 객체 catch 로 빠지면 null로 리턴 처리 하면 끝
         */
        try 
        {
            return $this->s3Client->putObject([
                'Bucket'        => $bucket,
                'Key'           => $key,
                'SourceFile'    => $fullPath,
                'ACL'           => $acl
            ]);
        } 
        catch (S3Exception $e) 
        {
            echo $e->getMessage();
            return [];
        }
        catch (AwsException $e) 
        {
            echo $e->getAwsRequestId() . "\n" . $e->getAwsErrorType() . "\n" .  $e->getAwsErrorCode();
            return [];
        }
    }


     /**
     * 버킷의 파일을 다른 이름으로 카피
     * @param $key string Key
     * @param $source string SourceFile
     * @return array
     */
    public  function s3Client_copyRenameBucket(string $key, string $source)  
    {
        /**
         * TODO
         * 7.1 버전 타입 힌팅 ?Aws\Result (객체명) 로 처리 가능 public function ~~~ () ? Aws\Result 
         * 정상 동작했을때 Aws\Result 객체 catch 로 빠지면 null로 리턴 처리 하면 끝
         */
        try 
        {
            $time = $this->time + 1;
            return $this->s3Client->copyObject([
                'Bucket'        => $this->bucketName,
                'Key'           => $key, //'images/user/picture/20181203/4.JPG',
                'CopySource'    => $this->bucketName.'/'.$source, //images/user/picture/20181203/70925791bc333323e90a5710bccff1ed.JPG
                'ACL'           => 'public-read'
            ]);
            
        } 
        catch (S3Exception $e) 
        {
            //echo $e->getMessage();
            return [];
        }
        catch (AwsException $e) 
        {
            //echo $e->getAwsRequestId() . "\n" . $e->getAwsErrorType() . "\n" .  $e->getAwsErrorCode();
            return [];
        }
    }

    /**
     * 파일 존재 하는지 확인
     * @param $source string 파일경로
     * @return bool
     */
    public function s3Client_isFile(string $source) : bool
    {
        $info = $this->s3Client->doesObjectExist($this->bucketName, $source);
        return $info ? true : false;
    }
}