<?php
/**
 * 系统
 */
namespace common\server\CustomerPlatform;

use common\base\BasicServer;


class CommonServer extends BasicServer
{
    public static function getPlatformModel($model_name,$suffix){
        $model_name = camelize($model_name,2);
        $func = '\common\model\db_customer_'.$suffix.'\\'.$model_name;
        return new $func();
    }
}
