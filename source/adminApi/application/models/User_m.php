<?php
/**
 * Created by PhpStorm.
 * User: blumine
 * Date: 2018. 2. 12.
 * Time: PM 5:45
 */

class User_m extends CI_Model
{
    function __construct()
    {
        parent::__construct();
    }

    function this_where($data)
    {
        if($data['search_word'] != '')
        {
            //검색어가 있을 경우
            $this->authDb->where('
            (`u`.`email` LIKE "%'.$data['search_word'].'%" OR `u`.`username` LIKE "%'.$data['search_word'].'%" OR `u`.`phone` LIKE "%'.$data['search_word'].'%")');


            if($data['type'] == 3)
            {
                //병원일 경우. 굿닥 병원 db가 정해지지 않아서 아직 작업을 못함
                //$this->authDb->or_like();
                //user_hospital_departments, hospitals join하여 사용
            }
        }

        if($data['search_type'] != '' or count($data['search_type']) > 0)
        {
            //검색타입(권한)이 있을 경우)
            //type=1 : 1 비휴면, 0 휴면.
            //type=2 : 1 관리자, 2 통계운영자, 3 일반운영자, 4 접수설치, 5 외부운영자(NTL), 6 외부운영자(녹심자).
            //type=3 1 광고주병원, 2 일반병원, 3 접수병원
            if($data['type'] == '1')
            {
                $sArr = explode(',', $data['search_type']);
                $sArr = array_map('trim', $sArr);

                $this->authDb->where('u.user_type', $data['type']);
                $this->authDb->where_in('u.is_dormant', $sArr);
            }
            else if($data['type'] == '3')
            {
                //201, 202, 203
                $sArr = explode(',', $data['search_type']);
                $sArr = array_map('trim', $sArr);

                $this->authDb->where_in('u.user_type', $sArr);
            }
            else if($data['type'] == '2')
            {
                //401, 402, 403, 404, 405, 406
                $sArr = explode(',', $data['search_type']);
                $sArr = array_map('trim', $sArr);

                $this->authDb->where_in('u.user_type', $sArr);
            }
        }
        else
        {
            //검색타입이 없는 경우 전체
            if($data['type'] == '1')
            {
                $this->authDb->where('u.user_type', $data['type']);
            }
            else if($data['type'] == '3')
            {
                $this->authDb->where_in('u.user_type', array('201', '202','203'));
            }
            else if($data['type'] == '2')
            {
                $this->authDb->where_in('u.user_type', array('401', '402','403', '404', '405', '406'));
            }
        }

        $this->authDb->where('u.is_deleted', 0);
    }

    /**
     * 회원리스트 리턴
     * @param $data
     * @return array
     */
    function getUserlist($data)
    {
        $this->authDb = $this->load->database('auth', true);
        //$this->db->join('keys k', 'u.id=k.users_id');

        //굿닥회원
        if($data['type'] == 1)
        {
            //검색어 처리
            $this->this_where($data);

            $this->authDb->select('u.id, u.username, u.email, u.user_type, u.where_from, u.provider, u.is_agency_account, u.picture');
            $this->authDb->select('u.bookings_count, u.user_know_hospitals_count, u.user_visit_hospitals_count');
            $this->authDb->select('u.age, u.sex, u.job, u.health_point, u.created_at, u.updated_at, u.last_login_at, u.last_logout_at');
            $this->authDb->select('u.last_activity_at, u.phone, u.note, u.is_dormant, u.dormant_at');
            $this->authDb->select('sc.oauth_token, sc.uid, group_concat(auth_code_id SEPARATOR ",") as group_auth_code');
            //$this->authDb->select('u.*, sc.oauth_token, sc.uid, group_concat(auth_code_id SEPARATOR ",") as group_auth_code');
            $this->authDb->join('sns_connection sc', 'u.id=sc.users_id', 'left');
            $this->authDb->join('device_info di', 'u.id=di.users_id', 'left');
            $this->authDb->join('user_auth_code uac', 'u.id=uac.users_id', 'left');
            $this->authDb->order_by('u.id', 'desc');
            $this->authDb->group_by('u.id');

            $resultY = $this->authDb->get('users u')->result_array();

            //검색어 처리
            $this->this_where($data);

            $limit = ($data['page'] - 1) * $data['limit'];
            $this->authDb->select('u.id, u.username, u.email, u.user_type, u.where_from, u.provider, u.is_agency_account, u.picture');
            $this->authDb->select('u.bookings_count, u.user_know_hospitals_count, u.user_visit_hospitals_count');
            $this->authDb->select('u.age, u.sex, u.job, u.health_point, u.created_at, u.updated_at, u.last_login_at, u.last_logout_at');
            $this->authDb->select('u.last_activity_at, u.phone, u.note, u.is_dormant, u.dormant_at');
            $this->authDb->select('sc.oauth_token, sc.uid, group_concat(auth_code_id SEPARATOR ",") as group_auth_code');
            //$this->authDb->select('u.*, sc.oauth_token, sc.uid, group_concat(auth_code_id SEPARATOR ",") as group_auth_code');
            $this->authDb->join('sns_connection sc', 'u.id=sc.users_id', 'left');
            $this->authDb->join('device_info di', 'u.id=di.users_id', 'left');
            $this->authDb->join('user_auth_code uac', 'u.id=uac.users_id', 'left');
            $this->authDb->order_by('u.id', 'desc');
            $this->authDb->group_by('u.id');
            $this->authDb->limit($data['limit'], $limit);
            //$this->authDb->where('u.user_type', $data['type']);
            $resultN = $this->authDb->get('users u')->result_array();

            //echo $this->authDb->last_query(); exit;
        }
        else if($data['type'] == 2)
        {
            //운영자
            //검색어 처리
            $this->this_where($data);

            $this->authDb->select('u.id, u.username, u.email, u.user_type, u.where_from, u.provider, u.is_agency_account, u.picture');
            $this->authDb->select('u.bookings_count, u.user_know_hospitals_count, u.user_visit_hospitals_count');
            $this->authDb->select('u.age, u.sex, u.job, u.health_point, u.created_at, u.updated_at, u.last_login_at, u.last_logout_at');
            $this->authDb->select('u.last_activity_at, u.phone, u.note, u.is_dormant, u.dormant_at');
            $this->authDb->select('sc.oauth_token, sc.uid, group_concat(auth_code_id SEPARATOR ",") as group_auth_code');


            $this->authDb->join('sns_connection sc', 'u.id=sc.users_id', 'left');
            $this->authDb->join('device_info di', 'u.id=di.users_id', 'left');
            $this->authDb->join('user_auth_code uac', 'u.id=uac.users_id', 'left');
            $this->authDb->order_by('u.id', 'desc');
            $this->authDb->group_by('u.id');
            //$this->authDb->where_in('u.user_type', array('401', '402','403', '404', '405', '406'));
            $resultY = $this->authDb->get('users u')->result_array();

            //검색어 처리
            $this->this_where($data);

            $limit = ($data['page'] - 1) * $data['limit'];
            $this->authDb->select('u.id, u.username, u.email, u.user_type, u.where_from, u.provider, u.is_agency_account, u.picture');
            $this->authDb->select('u.bookings_count, u.user_know_hospitals_count, u.user_visit_hospitals_count');
            $this->authDb->select('u.age, u.sex, u.job, u.health_point, u.created_at, u.updated_at, u.last_login_at, u.last_logout_at');
            $this->authDb->select('u.last_activity_at, u.phone, u.note, u.is_dormant, u.dormant_at');
            $this->authDb->select('sc.oauth_token, sc.uid, group_concat(auth_code_id SEPARATOR ",") as group_auth_code');
            //$this->authDb->select('u.*, sc.oauth_token, sc.uid, group_concat(auth_code_id SEPARATOR ",") as group_auth_code');
            $this->authDb->join('sns_connection sc', 'u.id=sc.users_id', 'left');
            $this->authDb->join('device_info di', 'u.id=di.users_id', 'left');
            $this->authDb->join('user_auth_code uac', 'u.id=uac.users_id', 'left');
            $this->authDb->order_by('u.id', 'desc');
            $this->authDb->limit($data['limit'], $limit);
            $this->authDb->group_by('u.id');
            //$this->authDb->where_in('u.user_type', array('401', '402','403', '404', '405', '406'));
            $resultN = $this->authDb->get('users u')->result_array();

            //echo $this->authDb->last_query();
        }
        else if($data['type'] == 3)
        {
            //병원
            //federated engine 작업해야 함. f_hospitals f_user_hospital_departments
            //검색어 처리
            $this->this_where($data);
            $this->authDb->select('u.id, u.username, u.email, u.user_type, u.where_from, u.provider, u.is_agency_account, u.picture');
            $this->authDb->select('u.bookings_count, u.user_know_hospitals_count, u.user_visit_hospitals_count');
            $this->authDb->select('u.age, u.sex, u.job, u.health_point, u.created_at, u.updated_at, u.last_login_at, u.last_logout_at');
            $this->authDb->select('u.last_activity_at, u.phone, u.note, u.is_dormant, u.dormant_at');
            $this->authDb->select('sc.oauth_token, sc.uid, hp.hospital_code, group_concat(auth_code_id SEPARATOR ",") as group_auth_code');
            //$this->authDb->select('u.*, sc.oauth_token, sc.uid, hp.hospital_code, group_concat(auth_code_id SEPARATOR ",") as group_auth_code');
            $this->authDb->join('sns_connection sc', 'u.id=sc.users_id', 'left');
            $this->authDb->join('device_info di', 'u.id=di.users_id', 'left');
            $this->authDb->join('user_auth_code uac', 'u.id=uac.users_id', 'left');
            $this->authDb->join('f_user_hospital_departments uhd', 'u.id=uhd.user_id', 'left');
            $this->authDb->join('f_hospitals hp', 'uhd.hospital_id=hp.id', 'left');
            $this->authDb->order_by('u.id', 'desc');
            $this->authDb->group_by('u.id');
            //$this->authDb->where('u.user_type', $data['type']);
            $resultY = $this->authDb->get('users u')->result_array();

            //검색어 처리
            $this->this_where($data);

            $limit = ($data['page'] - 1) * $data['limit'];
            //$this->authDb->select('u.*, sc.oauth_token, sc.uid, hp.hospital_code, group_concat(auth_code_id SEPARATOR ",") as group_auth_code');
            $this->authDb->select('u.id, u.username, u.email, u.user_type, u.where_from, u.provider, u.is_agency_account, u.picture');
            $this->authDb->select('u.bookings_count, u.user_know_hospitals_count, u.user_visit_hospitals_count');
            $this->authDb->select('u.age, u.sex, u.job, u.health_point, u.created_at, u.updated_at, u.last_login_at, u.last_logout_at');
            $this->authDb->select('u.last_activity_at, u.phone, u.note, u.is_dormant, u.dormant_at');
            $this->authDb->select('sc.oauth_token, sc.uid, hp.hospital_code, group_concat(auth_code_id SEPARATOR ",") as group_auth_code');
            $this->authDb->join('sns_connection sc', 'u.id=sc.users_id', 'left');
            $this->authDb->join('device_info di', 'u.id=di.users_id', 'left');
            $this->authDb->join('user_auth_code uac', 'u.id=uac.users_id', 'left');
            $this->authDb->join('f_user_hospital_departments uhd', 'u.id=uhd.user_id', 'left');
            $this->authDb->join('f_hospitals hp', 'uhd.hospital_id=hp.id', 'left');
            $this->authDb->order_by('u.id', 'desc');
            $this->authDb->group_by('u.id');
            $this->authDb->limit($data['limit'], $limit);
            //$this->authDb->where('u.user_type', $data['type']);
            $resultN = $this->authDb->get('users u')->result_array();


            //echo $this->authDb->last_query();
        }

        if($data['listAll'] == 'Y')
        {
            $resultArr = ['list'=>$resultY, 'totCnt'=>count($resultY)];
        }
        else
        {
            $resultArr = ['list'=>$resultN, 'totCnt'=>count($resultY)];
        }



        return $resultArr;
    }

    /**
     * 회원정보 리턴
     * @param $data
     * @return mixed
     */
    function getUserInfo($data)
    {
        $this->authDb = $this->load->database('auth', true);


        if($data['type'] == 1)
        {
            //굿닥회원
            //$this->authDb->select('u.*, sc.oauth_token, sc.uid, group_concat(auth_code_id SEPARATOR ",") as group_auth_code');
            $this->authDb->join('sns_connection sc', 'u.id=sc.users_id', 'left');
            $this->authDb->join('user_auth_code uac', 'u.id=uac.users_id');
            $this->authDb->where('u.user_type', $data['type']);
            $this->authDb->where('u.id', $data['getUserId']);
            $this->authDb->where('u.is_deleted', 0);
            $result = $this->authDb->get('users u')->row_array();
        }
        else if($data['type'] == 2)
        {
            //운영자
            $this->authDb->select('u.id, u.username, u.email, u.user_type, u.where_from, u.provider, u.is_agency_account, u.picture');
            $this->authDb->select('u.bookings_count, u.user_know_hospitals_count, u.user_visit_hospitals_count');
            $this->authDb->select('u.age, u.sex, u.job, u.health_point, u.created_at, u.updated_at, u.last_login_at, u.last_logout_at');
            $this->authDb->select('u.last_activity_at, u.phone, u.note, u.is_dormant, u.dormant_at');
            $this->authDb->select('sc.oauth_token, sc.uid, group_concat(auth_code_id SEPARATOR ",") as group_auth_code');
            $this->authDb->join('sns_connection sc', 'u.id=sc.users_id', 'left');
            $this->authDb->join('user_auth_code uac', 'u.id=uac.users_id');
            $this->authDb->where_in('u.user_type',  array('401', '402','403', '404', '405', '406'));
            $this->authDb->where('u.id', $data['getUserId']);
            $this->authDb->where('u.is_deleted', 0);
            $result = $this->authDb->get('users u')->row_array();
        }
        else if($data['type'] == 3)
        {
            //병원
            $this->authDb->select('u.id, u.username, u.email, u.user_type, u.where_from, u.provider, u.is_agency_account, u.picture');
            $this->authDb->select('u.bookings_count, u.user_know_hospitals_count, u.user_visit_hospitals_count');
            $this->authDb->select('u.age, u.sex, u.job, u.health_point, u.created_at, u.updated_at, u.last_login_at, u.last_logout_at');
            $this->authDb->select('u.last_activity_at, u.phone, u.note, u.is_dormant, u.dormant_at');
            $this->authDb->select('sc.oauth_token, sc.uid, hp.hospital_code, hp.name, group_concat(auth_code_id SEPARATOR ",") as group_auth_code');
            $this->authDb->join('sns_connection sc', 'u.id=sc.users_id', 'left');
            $this->authDb->join('user_auth_code uac', 'u.id=uac.users_id');
            $this->authDb->join('f_user_hospital_departments uhd', 'u.id=uhd.user_id', 'left');
            $this->authDb->join('f_hospitals hp', 'uhd.hospital_id=hp.id', 'left');
            $this->authDb->where('u.user_type', $data['type']);
            $this->authDb->where('u.id', $data['getUserId']);
            $this->authDb->where('u.is_deleted', 0);
            $result = $this->authDb->get('users u')->row_array();
        }

        return $result;
    }

}