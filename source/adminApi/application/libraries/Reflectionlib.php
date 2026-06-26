<?php 
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * 클래스 캡슐화 유틸
 */
class Reflectionlib
{
    private  $class = null; //클래스
    private  $classRef = null; //캡슐화 할 클래스
    
    /**
     * 생성자
     */
    public function  __construct(array $param)
    {
        $this->class = new $param['name'];
        $this->classRef = new ReflectionClass($this->class);
    }       

    /**
     * 메소드 추출
     * @return ReflectionMethod
     */
    private function getMethod(string $name) //: ReflectionMethod
    {   
        if (!method_exists($this->class, $name))
        {
            return false;
        }
        
        return $this->classRef->getMethod($name);
    }


    /**
     * 메소드들 추출
     * @return array
     */
    private function getMethods() : array
    {
        return $this->classRef->getMethods();
    }


    /**
     * 메소드명들 추출
     * @param $obj object
     * @return array
     */
    private function getMethodNames(object $obj = null)  : array
    {
        $method = !is_null($obj) ? $obj :  $this->getMethod();

        $nameArr = [];
        foreach($method as $key => $val)
        {
            $nameArr[] = $val->name;
        }   
        return $nameArr;
    }

    /**
     * 메소드들 세팅
     * @param $methods array
     * @return bool
     */
    public function setMethod(array $methods = []) : bool
    {
        $check = true;

        if (sizeof($methods) === 0 ) 
        {
            $refMethods = $this->getMethods();
            foreach($refMethods as $key => $val)
            {
                $val->setAccessible(true);
                $this->{$val->name} = $val;
            }
        }
        else
        {
            foreach($methods as $key=>$val)
            {
                $checkMethod = $this->getMethod($val);
                if ($checkMethod === false)
                {
                    $check = false;
                    break;       
                }
                $checkMethod->setAccessible(true);
                $this->{$val} = $checkMethod;
            }
        }
        return $check;
    }

    /**
     * 메소드 실행  
     * @param $method string 
     * @param $param array  
     * @return mix
     */
    public function execMethod(string $method, array $param = [])
    {
        return $this->{$method}->invokeArgs($this->class, $param);
    }
}