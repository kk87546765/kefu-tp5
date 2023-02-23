<?php
/**
 * Created by PhpStorm.
 * User: crosstime
 * Date: 2018/8/14
 * Time: 下午5:31
 */
namespace common\sql_server;


use common\model\db_customer\{RecallPlan,RecallPlanLog,RecallPlanPeopleLog};


class RecallPlanSqlServer extends BaseSqlServer
{

    public static function insertInfo($data)
    {
        $time = time();
        $model = new RecallPlanplanLog();
        $res = $model->insert($data);
        return $res;
    }




    public static function getInfo($where)
    {
        $model = new RecallPlan();
        $res = $model->where($where)->select();
        return $res;
    }



}