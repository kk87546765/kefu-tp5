<?php


namespace common\sql_server;

use common\sql_server\BaseSqlServer;
use common\model\gr_chat\BanIpLog as BanIpLogModel;


class BanIpLog extends BaseSqlServer
{


    public static function insertLog($info=[],$type=1)
    {
        $sql = "insert into gr_ban_ip_log(`ip`,`admin_user`,`dateline`,`type`,`ban_time`,`reason`,`platform`) values";

        foreach( $info as $v ){
            $time = $v['dateline']?:time();
            $ban_time = $v['ban_time']?:0;
            $sql .="('{$v['ip']}',".
                "'{$v['admin_user']}',".
                "{$time},".
                "{$type},".
                "{$ban_time},".
                "'{$v['reason']}',".
                "'{$v['platform']}'),";
        }

        $sql = trim($sql,",");
        $model = new BanIpLogModel();
        $ret = $model->execute($sql);
        return $ret;
    }

    public static function getList($where,$offset,$limit,$order)
    {
        $model = new BanIpLogModel();
        $res = $model->where($where)->limit($offset.','.$limit)->order($order)->select();

        return $res;
    }


    public static function getCount($where)
    {
        $model = new BanIpLogModel();
        $res = $model->where($where)->count();
        return $res;

    }


}