<?php
/**
 * Created by PhpStorm.
 * User: EDZ
 * Date: 2019/4/12
 * Time: 11:10
 */

namespace common\base;

use think\Controller;

class BasicController extends Controller
{

    protected $rs;//返回数据
    protected $msg;//信息
    protected $req;//请求数据
    protected $time;//请求当前时间
    protected $error_code = [1];
    public function _initialize()
    {
        $this->time = time();
        $this->rs = ['data'=>[],'time'=>$this->time,'code'=>0,'msg'=>'ok','time_m'=>[]];
        $this->req = request();

        $this->error_code = config('error_code');

    }

    protected function setMicrotime($name){
        $this->rs['time_m'][microtime()] = $name;
    }
}