<?php
/**
 * Created by PhpStorm.
 * User: crosstime
 * Date: 2018/8/14
 * Time: 下午5:31
 */
namespace common\sql_server;

use common\model\gr_chat\ApiLog;
use common\server\CustomerPlatform\CommonServer;

class ApiLogSqlServer extends BaseSqlServer
{


    public static function add($method,$url,$msg,$remark)
    {
        if(is_array($msg)){
            $msg = json_encode($msg);
        }
        $data['method'] = $method;
        $data['url'] = $url;
        $data['info'] = $msg;
        $data['addtime'] = time();
        $data['remark'] = $remark;
        $model = new ApiLog();

        $res = $model->save($data);
        return $res;
    }

 

}