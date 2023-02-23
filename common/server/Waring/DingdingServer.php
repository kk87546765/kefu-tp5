<?php
/**
 * Created by PhpStorm.
 * User: crosstime
 * Date: 2017/10/12
 * Time: 上午11:57
 */
namespace common\server\Waring;


use common\base\BasicServer;

use common\libraries\{Common,Dingding};



class DingdingServer extends BasicServer
{

    const TEST_WARING = 0;

    const REMOTE_LOGIN_WARING = 1;


    //0:测试警告 1：异地登录警告 2：封禁异常警告
    public $waring_type = [];


    public function send($waring_type,$msg,$mobile = [])
    {

    }
}