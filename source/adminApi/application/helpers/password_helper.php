<?php
/**
 * @author   martin <blumine@gmail.com>
 */
defined('BASEPATH') OR exit('No direct script access allowed');

if ( ! function_exists('make_password'))
{
    /**
     * 비밀번호 생성
     * @param $password
     * @return array
     */
	function make_password($password)
	{
        $salt = time().random_string('numeric');
        $hashresult = hash_hmac('sha256', $password, $salt);
        return ['crypt_password'=>$hashresult, 'salt'=>$salt];
	}
}

if ( ! function_exists('check_password'))
{
    /**
     * 비밀번호 비교
     * @param $crypt_password
     * @param $password
     * @param $salt
     * @return bool
     */
    function check_password($crypt_password, $password, $salt)
    {
        $hashresult = hash_hmac('sha256', $password, $salt);

        //echo $crypt_password.'--'.$salt.'--'.$hashresult;

        if($crypt_password === $hashresult)
        {
            return true;
        }
        else
        {
            return false;
        }
    }
}