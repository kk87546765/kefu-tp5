<?php


namespace common\sql_server;


use common\model\gr_chat\BanUserLog as BanUserLogModel;

class BanUserLog extends BaseSqlServer
{


    public static function insertLog($info=[],$type=1, $platformSuffix=''){

        $sql = "insert into gr_ban_user_log(`uid`,`user_name`,`admin_user`,`dateline`,`type`,`ban_time`,`reason`,`platform`) values";

        foreach( $info as $v ){
            $time = empty($v['dateline'])?time() : $v['dateline'];
            $ban_time = empty($v['ban_time'])? 0 : $v['ban_time'];
            $uid = empty($v['uid']) ? 0 : $v['uid'];
            $user_name = empty($v['user_name']) ? '' : $v['user_name'];
            $sql .= "('{$uid}',".
                "'{$user_name}',".
                "'{$v['admin_user']}',".
                "{$time},".
                "{$type},".
                "{$ban_time},".
                "'{$v['reason']}',".
                "'{$v['platform']}'),";
        }

        $sql = trim($sql,",");

        $model = new BanUserLogModel();

        $ret = $model->execute($sql);
        return $ret;
    }


    public static function getList($where,$offset,$limit,$order)
    {
        $model = new BanUserLogModel();
        $res = $model->where($where)->limit($offset.','.$limit)->order($order)->select();
        return $res;
    }


    public static function getCount($where)
    {
        $model = new BanUserLogModel();
        $res = $model->where($where)->count();
        return $res;

    }


}