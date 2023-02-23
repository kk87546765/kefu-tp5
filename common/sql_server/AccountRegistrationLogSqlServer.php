<?php
/**
 * Created by PhpStorm.
 * User: crosstime
 * Date: 2018/8/14
 * Time: 下午5:31
 */
namespace common\sql_server;

use common\model\db_statistic\AccountRegistrationLog;

class AccountRegistrationLogSqlServer extends BaseSqlServer
{
    public static $return=[];


    public static function add($data)
    {

        $model = new AccountRegistrationLog();

        $res = $model->insert($data);

        return $res;
    }


}