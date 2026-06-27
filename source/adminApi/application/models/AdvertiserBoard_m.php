<?php
/**
 * Created by PhpStorm.
 * User: blumine
 * Date: 2018. 4. 2.
 * Time: AM 10:54
 */

class AdvertiserBoard_m extends CI_Model
{
    function __construct()
    {
        parent::__construct();
    }

    /**
     * 게시물 리스트
     * @param $data
     * @return array|bool
     */
    function getBoardList($data)
    {
        try {
            //기본
            $orderBy = " order by b.id desc";

            if($data['searchWord'])
            {
                $where = " and (b.title like '%".$data['searchWord']."%' or b.contents like '%".$data['searchWord']."%'  or b.userName like '%".$data['searchWord']."%') ";
            }
            else
            {
                $where = '';
            }

            $sqlTot = $sql = "select b.id as boardId, b.userName, 
            b.contents, b.title,b.hit, b.type,b.regDate, 
            group_concat(concat(bf.id,'|',bf.boardId,'|',bf.fileName)) as fileArray , b.userId
            from advertiser_board b 
            left join advertiser_board_files bf on b.id=bf.boardId 
            where b.type='".$data['type']."' and b.isDelete = '0' 
            ".$where."
            group by b.id
            ". $orderBy;

            $sql .= ' limit '.(($data['page'] - 1) * $data['limit']).', '. $data['limit'];

            $result = $this->db->query($sql)->result_array();

            //전체 수
            $totCount = $this->db->query($sqlTot)->num_rows();

            $return = [];
            $i=0;
            foreach ($result as $item)
            {
                $return[$i] = $item;

                //파일명 후처리
                if($item['fileArray'])
                {
                    $jArr = [];
                    $jsonArr = explode(',', $item['fileArray']);
                    foreach ($jsonArr as $jtem)
                    {
                        $jtt = explode('|', $jtem);
                        $jArr[] = ['id'=>(int)$jtt[0], 'boardId'=>(int)$jtt[1], 'fileName'=>$jtt[2]];
                    }
                    $return[$i]['fileArray'] = $jArr;
                }

                $i++;
            }

            $data = ['data'=>$return, 'totCount'=>$totCount];

            return $data;
        } catch (Exception $e) {
            $this->response($this->json([
                'status' => 'error',
                'code' => '610',
                'message' => '서버관리자에게 문의하세요.',
                'result' => []
            ]), 200);
        }
    }

    /**
     * 게시물에 대한 권한 체크
     * @param $data
     * @return bool
     */
    function checkBoardAuth($data)
    {
        $sql = "select count(*) as cnt from advertiser_board where type='".$data['type']."' and userId='".$data['userId']."' 
                and id='".$data['boardId']."'  
                ";
        $result = $this->db->query($sql)->row_array();

        if($result['cnt'] > 0)
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    /**
     * 게시글 삭제. board table update
     * @param $data
     * @return array
     */
    function deleteBoard($data)
    {
        
        //메소드 내부에서 트랜잭션이 시작인지 체크 
        $checkMethodTrans = false;
        if ($this->common_m->isInTrans() === false)
        {
            $this->master->trans_begin();
            $checkMethodTrans = true;   
        }

        //board 테이블 업데이트
        $this->master->where('userId', $data['userId'])->where('id', $data['boardId'])->where('type', $data['type'])->update('advertiser_board', ['isDelete'=>1]);
        $this->master->where('boardId', $data['boardId'])->delete('advertiser_board_files');

        //트랜잭션
        if ($checkMethodTrans === true)
        {
            if ($this->master->trans_status() === FALSE)
            {
                $this->master->trans_rollback();
                return ['code'=>500, 'message'=>''];
            }
            $this->master->trans_commit();
            return ['code'=>200, 'message'=>''];
        }
        return ['code'=>200, 'message'=>''];
    }


    /**
     * 후기 등록
     * @param $data
     * @return mixed
     */
    function setBoardInfo($data)
    {
        
        //메소드 내부에서 트랜잭션이 시작인지 체크 
        $checkMethodTrans = false;
        if ($this->common_m->isInTrans() === false)
        {
            $this->master->trans_begin();
            $checkMethodTrans = true;   
        }

        $date = date("Y-m-d H:i:s"); //umt기준
        $date2 = date("Y-m-d"); //umt기준

        //board insert
        $iArr = [
            'userId' => $data['userId'],
            'userName' => $data['userName'],
            'type' => $data['type'],
            'regDate' => $date,
            'modifyDate' => $date,
            'title' => $data['title'],
            'contents' => $data['contents']
        ];

        $this->master->insert('advertiser_board', $iArr);
        $insertId = $this->master->insert_id();

        //이미지 처리
        if($data['photoJson'])
        {
            $fileArr = json_decode($data['photoJson'], true);
            $i=1;
            foreach ($fileArr as $item)
            {
                $arr = [
                    'boardId' => $insertId,
                    'fileName' => $item,
                    'orderBy' => $i,
                    'regDate' => date("Y-m-d H:i:s")
                ];
                $this->master->insert('advertiser_board_files', $arr);
                $i++;
            }
        }

        //트랜잭션
        if ($checkMethodTrans === true)
        {
            if ($this->master->trans_status() === FALSE)
            {
                $this->master->trans_rollback();
                return ['code'=>500, 'message'=>''];
            }
            $this->master->trans_commit();
            return ['code'=>200, 'message'=>$insertId];
        }
        return ['code'=>200, 'message'=>$insertId];
    }


    /**
     * 게시글 상세 보기
     * @param $data
     * @return mixed
     */
    function getBoardView($data)
    {
        try {
            $sql = "select b.id as boardId, b.contents, b.title, b.regDate, 
            group_concat(concat(bf.id,'|',bf.boardId,'|',bf.fileName)) as fileArray, b.userId ,
            userName, b.type 
            from advertiser_board b 
            left join advertiser_board_files bf on b.id=bf.boardId 
            where b.id='".$data['boardId']."'  and b.isDelete = '0'
            ";

            $result = $this->db->query($sql)->row_array();

            if($result['boardId'] !== null)
            {
                //파일명 후처리
                if($result['fileArray'])
                {
                    $jArr = [];
                    $jsonArr = explode(',', $result['fileArray']);
                    foreach ($jsonArr as $jtem)
                    {
                        $jtt = explode('|', $jtem);
                        $jArr[] = ['id'=>(int)$jtt[0], 'boardId'=>(int)$jtt[1], 'fileName'=>$jtt[2]];
                    }
                    $result['fileArray'] = $jArr;
                }

                return $result;
            }
            else
            {
                return false;
            }

        } catch (Exception $e) {
            $this->response($this->json([
                'status' => 'error',
                'code' => '610',
                'message' => '서버관리자에게 문의하세요.',
                'result' => []
            ]), 200);
        }

    }

    /**
     * 후기 신고, 좋아요 등록
     * @param $data
     * @return bool
     */
    function setEstimation($data)
    {
        //메소드 내부에서 트랜잭션이 시작인지 체크 
        $checkMethodTrans = false;
        if ($this->common_m->isInTrans() === false)
        {
            $this->master->trans_begin();
            $checkMethodTrans = true;   
        }

        //board update
        switch ($data['type'])
        {
            case 1:
                if($data['check'] == 1)
                {
                    //토글 -
                    $sql10 = "likeCount=likeCount-1";
                }
                else
                {
                    $sql10 = "likeCount=likeCount+1";
                }

                break;
            case 2: //신고
                $sql10 = "complainCount=complainCount+1";
                break;
            case 3:
                $sql10 = "helpYesCount=helpYesCount+1";
                break;
            case 4:
                $sql10 = "helpNoCount=helpNoCount+1";
                break;
        }

        $sql1 = "update board set ".$sql10." where id='".$data['boardId']."'";
        $this->master->rawQuery($sql1);

        if($data['type'] == 1 and $data['check'] == 1)
        {
            //table insert
            $arr = [
                'boardId' => $data['boardId'],
                'userId' => $data['userId'],
                'type' => 7, //좋아요 빼기
                'regDate' => date("Y-m-d H:i:s")
            ];
        }
        else
        {
            //table insert
            $arr = [
                'boardId' => $data['boardId'],
                'userId' => $data['userId'],
                'type' => $data['type'],
                'regDate' => date("Y-m-d H:i:s")
            ];
        }

        $insertId = $this->master->insert('board_estimation', $arr);

         //트랜잭션
        if ($checkMethodTrans === true)
        {
            if (!$insertId) 
            {
                $this->master->rollback();
                return false;    
            }
            $this->master->commit();
            return $insertId;
        }
        return !$insertId ? false : $insertId;
    }

    /**
     * 후기 신고, 좋아요 중복 체크
     * @param $data
     * @return bool
     */
    function checkEstimation($data)
    {
        $this->db->where('type', $data['type']);
        $this->db->where('boardId', $data['boardId']);
        $this->db->where('userId', $data['userId']);
        $result = $this->db->getOne('board_estimation', 'count(*) as cnt');

        if($result['cnt'] > 0)
        {
            if($data['type'] == 1)
            {
                $this->db->where('type', 7);
                $this->db->where('boardId', $data['boardId']);
                $this->db->where('userId', $data['userId']);
                $result2 = $this->db->getOne('board_estimation', 'count(*) as cnt');

                if($result2['cnt'] > 0)
                {
                    return false; //1번, 7번이 존재하면 좋아요 취소한 상태라 중복 아님
                }
                else
                {
                    return true; //중복
                }
            }
            else
            {
                return true; //중복
            }
        }
        else
        {
            return false;
        }
    }

     /**
     * 후기 작성여부 리턴
     * @param $data
     * @return mixed
     */
    function getIsWrite($data)
    {
        $this->db->where('userId', $data['userId']);
        $this->db->where('isDelete', 0);
        $this->db->where('callRequestId', $data['callRequestId']);
        $result = $this->db->getOne('board', 'count(*) as cnt');

        $result['code'] = 200;

        if($result['cnt'] > 0)
        {
            $result['data'] = 1;
        }
        else
        {
            $result['data'] = 0;
        }

        return $result;
    }

    /**
     * 게시글 수정
     * @param $data
     * @return mixed
     */
    function updateBoard($data)
    {    
        //메소드 내부에서 트랜잭션이 시작인지 체크 
        $checkMethodTrans = false;
        if ($this->common_m->isInTrans() === false)
        {
            $this->master->trans_begin();
            $checkMethodTrans = true;   
        }

        $date = date("Y-m-d H:i:s"); //umt기준

        //board update
        $uArr = [
            'userName'=> $data['userName'],
            'title'=> $data['title'],
            'contents' => $data['contents'],
            'modifyDate' => $date
        ];

        //후기내용 수정
        $this->master->where('type', $data['type']);
        $this->master->where('id', $data['boardId']);
        $this->master->where('userId', $data['userId']);
        $result1 = $this->master->update('advertiser_board', $uArr); //echo $this->db->getLastQuery(); exit;

        //이미지 삭제후 업데이트
        $result2 = $this->master->where('boardId', $data['boardId'])->delete('advertiser_board_files');

        $fileArr = json_decode($data['photoJson'], true);
        $i=1;
        foreach ($fileArr as $item)
        {
            $arr = [
                'boardId' => $data['boardId'],
                'fileName' => $item,
                'orderBy' => $i,
                'regDate' => $date
            ];
            $this->master->insert('advertiser_board_files', $arr);
            $i++;
        }

        //트랜잭션
        if ($checkMethodTrans === true)
        {
            if ($this->master->trans_status() === FALSE)
            {
                $this->master->trans_rollback();
                return ['code'=>500, 'message'=>''];
            }
            $this->master->trans_commit();
            return ['code'=>200, 'message'=>''];
        }
        return ['code'=>200, 'message'=>''];
    }
}