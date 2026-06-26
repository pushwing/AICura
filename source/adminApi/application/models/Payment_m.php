<?php
/**
 * Created by PhpStorm.
 * User: blumine
 * Date: 2018. 2. 12.
 * Time: PM 5:45
 */

class Payment_m extends CI_Model
{
    function __construct()
    {
        parent::__construct();
    }

    /**
     * 결제 정보 최초 등록
     * @param $data
     * @return bool|int
     */
    function setPayment($data)
    {
        $data['regDate'] = date("Y-m-d H:i:s");
        $this->db->trans_begin();

        $aArr = $data;

        //필요없는 데이터 삭제
        unset($aArr['token']);
        unset($aArr['menu_id']);
        unset($aArr['users_id']);
        if ( isset($data['refreshToken']) )
        {
            unset($data['refreshToken']);
        }
        
        $this->master->insert('payment', $aArr);
        $paymentId = $this->master->insert_id();

        //트랜잭션
        if ($this->db->trans_status() === FALSE)
        {
            $this->db->trans_rollback();
            return false;
        }
        else
        {
            $this->db->trans_commit();
            return $paymentId;
        }
    }

    /**
     * 해당 결제건이 이미 성공되어 있는지 체크.
     * 중복 충전을 막기 위한 코드
     * @param $data
     * @return bool
     */
    function checkResultCode($data)
    {
        $resultP = $this->db->get_where('payment', ['etc'=>$data['paymentId']])->row_array();

        if($resultP['resultCode'] == '0021')
        {
            return false;
        }

        return true;
    }

    /**
     * 가상계좌 업데이트 로직
     * @param $data
     * @return bool
     * @throws Exception
     */
    function updatePayment($data)
    {
        $this->load->model('common_m', 'contractOrder_m'); //시점잔액 가져오기 위한..

        $date = date("Y-m-d H:i:s");

        //기타값 삭제
        $data2 = $data;

        unset($data['paymentId']);
        unset($data['hospitalName']);
        unset($data['type']);
        unset($data['mid']);
        unset($data['amt']);
        if ( isset($data['refreshToken']) )
        {
            unset($data['refreshToken']);
        }

        //처리일시
        $data3 = [];
        $data['applyDate'] =$data3['applyDate'] = $date;
        $data3['resultCode'] = $data['resultCode'];

        //$this->db->trans_begin();

        $this->master->where('id', $data2['paymentId'])->update('payment', $data);
        log_message('info', $this->master->last_query());

        $payment = $this->master->affected_rows();

        log_message('info', 'affected-'.$payment);

        if($data['resultCode'] == '0021')
        {
            //입금확인이면 api/v1.0/contractOrder/depositConfirm 호출하여 입금처리
            $resultP = $this->db->get_where('payment', ['etc'=>$data2['paymentId']])->row_array();

            //수주계약일 업데이트
            $data5 = ['depositDate'=>$date];
            $this->master->where('id', $resultP['contractOrderId'])->update('contract_order', $data5);


            log_message('info', json_encode($resultP));

            $arr = [
                'users_id' => $resultP['userId'],
                'contractId'=> $resultP['contractId'],
                'contractOrderId'=> $resultP['contractOrderId'],
                'chargePrice'=> $resultP['amount'],
                'memo'=> $resultP['contractOrderId'].'번 수주계약 입금확인.',
                'customerMemo'=> $resultP['contractOrderId'].'번 수주계약 입금확인되었습니다.'
            ];

            //세금 분리처리를 여기서 해야함.
            log_message('info', 'deposit start');
            $ret1 = $this->contractOrder_m->depositConfirm($arr);
            log_message('info', 'deposit end-'.$ret1);

            //리플리케이터 시작
            //세금 분리된 금액으로 충전 해야 함.
            $chargePrice = round($resultP['amount'] / 1.1) ; //계약액
            $iData = [
                'contract_id' => $resultP['contractId'], //계약번호
                'payment_type' => 1,
                'price' => $chargePrice,
                'memo' => ''
            ];
            log_message('info', 'rep strat');
            $this->replicator_m->send('/api/payments', 'POST', $iData);
            log_message('info', 'rep end');
            //리플리케이터 끝

            //시점잔액 업데이트
            $rPrice = $this->contractOrder_m->getBalancePrice(['contractId'=>$resultP['contractId']]);
            $data3 = [
                'contractId'=>$resultP['contractId'],
                'totalReady'=>$rPrice,
                'type'=>1
            ];
            $this->common_m->updateTotalInfo($data3);
        }

        //트랜잭션
        if ($payment == FALSE)
        {
            log_message('info', 'rollback');
            return false;
        }
        else
        {
            log_message('info', 'commit');
            return $data2['paymentId'];
        }
    }

    /**
     * 가상계좌 요청전에 진행중인 결제건이 있는지 체크
     * @param $data
     * @return bool
     */
    function checkPayment($data)
    {
        $date = date("YmdHis");
        $result = $this->db->get_where('payment', ['amount'=>$data['amount'], 'contractOrderId'=>$data['contractOrderId'], 'type'=>$data['type']])->result_array();

        if(count($result) > 0)
        {
            //수주계약번호와 금액, 타입이 맞는 row가 있을 경우
            $this->db->order_by('id', 'desc');
            $this->db->limit(1);
            $this->db->where('transDate > "'.$date.'"');
            $result2 = $this->db->get_where('payment', ['amount'=>$data['amount'], 'contractOrderId'=>$data['contractOrderId'], 'type'=>$data['type']])->row_array();
            //if(@$result2['resultCode'] == '0031' or @$result2['resultCode'] == '0' or @$result2['resultCode'] == '')
            if(count($result2) == 0)
            {
                //실패한 row가 있다면 다시 결제요청할 수 있다.
                return true;
            }
            else
            {
                if(@$result2['resultCode'] == '0031' or @$result2['resultCode'] == '0' or @$result2['resultCode'] == '')
                {
                    return true;
                }
                return false;
            }
        }
        else
        {
            return true;
        }
    }

    /**
     * 계약명 구하기
     * @param $contractId
     * @return mixed
     */
    function getContractInfo($contractId)
    {
        $result = $this->db->get_where('contract', ['id'=>$contractId])->row_array();

        return $result['title'];
    }

    /**
     * 환불 처리
     * @param $data
     * @return int
     */
    function setRefund($data)
    {
        //환불요청후 결과 리턴
        $reArr = [
            'charset'=>'utf-8',
            'mid'=>ORDER_ID,
            'passwd'=>md5($data['transNo'].PG_KEY)
        ];

        if($data['termType'] == 1)
        {
            //전체환불
            $reArr['refund_list'] = $data['transNo'].':'.$data['amount'];
        }
        else
        {
            //부분환불 - 1번만 가능해서 1로 고정
            $reArr['refund_list'] = $data['transNo'].':'.$data['amount'].':1';
        }

        $this->load->library('curl');

        $return = $this->curl->simple_post(REFUND_URL, $reArr);

        $return2 = urldecode($return);
        //restMsg=success:1|fail:0&respStr=SBank_CARDmid_test0020130314172422005011:1000:0000:성공

        $retArr = explode('&', $return2);

        $respMsg = explode('|', $retArr[0]);
        $suc = explode(':', $respMsg[0]);
        $sucCount = $suc[1];
        $fail = explode(':', $respMsg[1]);
        $failCount = $fail[1];

        $ret1 = explode('=', $retArr[0]);
        $ret2 = explode('=', $retArr[1]);

        //refund 테이블 입력값 작업
        $data['result1'] = $ret1[1];
        $data['result2'] = $ret2[1];
        $data['regDate'] = date("Y-m-d H:i:s");

        if($sucCount == 1 and $failCount == 0)
        {
            $data['resultCode'] = 1; //성공
        }
        else
        {
            $data['resultCode'] = 2; //실패
        }

        $this->master->insert('refund', $data);
        $refundId = $this->db->insert_id();

        return $refundId;
    }
}