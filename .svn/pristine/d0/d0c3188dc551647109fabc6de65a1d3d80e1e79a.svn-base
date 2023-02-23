<?php


namespace common\sql_server;


use common\model\gr_chat\BanImeiLog as BanImeiLogModel;

class BanImeiLog extends BaseSqlServer
{

    public static function insertLog($info=[],$type=1){


        $sql = "insert into gr_ban_imei_log(`imei`,`admin_user`,`dateline`,`type`,`ban_time`,`reason`,`platform`) values";

        foreach( $info as $v ){
            $time = $v['dateline']?:time();
            $ban_time = $v['ban_time']?:0;
            $sql .="('{$v['imei']}',".
                "'{$v['admin_user']}',".
                "{$time},".
                "{$type},".
                "{$ban_time},".
                "'{$v['reason']}',".
                "'{$v['platform']}'),";
        }


        $sql = trim($sql,",");
        $model = new BanImeiLogModel();
        $ret = $model->execute($sql);
        return $ret;
    }

    public static function getList($where,$offset,$limit,$order)
    {
        $model = new BanImeiLogModel();
        $res = $model->where($where)->limit($offset.','.$limit)->order($order)->select();
        return $res;
    }


    public static function getCount($where)
    {
        $model = new BanImeiLogModel();
        $res = $model->where($where)->count();
        return $res;

    }
}