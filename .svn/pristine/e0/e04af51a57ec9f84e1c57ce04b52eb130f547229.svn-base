<?php

namespace common\sql_server;


use common\model\db_statistic\Sms;
class SmsSqlServer extends BaseSqlServer
{


    public static function add($data)
    {
        $model = new Sms();

        $data = $model->insert($data);

        return $data;
    }

    public static function getCount($where)
    {
        $model = new Sms();

        $data = $model->where($where)->count();

        return $data;
    }


    public static function getList($where)
    {
        $model = new Sms();

        $data = $model->where($where)->select();

        return $data;
    }




}