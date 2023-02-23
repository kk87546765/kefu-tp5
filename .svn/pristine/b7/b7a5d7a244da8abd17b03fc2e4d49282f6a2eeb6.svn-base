<?php

namespace common\sql_server;


use common\model\gr_chat\Admin;


class GsuserSqlServer extends BaseSqlServer
{

    public static function getUser($where,$page=1,$limit=20,$order='')
    {
        $model = new Admin();
        $res = $model->where($where)->limit(($page-1)*$limit.','.$limit)->order($order)->select();
        $res = !empty($res) ? $res : [];
        return $res;
    }

    public static function getCount($where)
    {
        $model = new Admin();
        $res = $model->where($where)->count();
        $res = !empty($res) ? $res : 0;
        return $res;
    }

}