<?php
/**
 * Created by PhpStorm.
 * User: crosstime
 * Date: 2018/8/14
 * Time: 下午5:31
 */
namespace common\server\Gsuser;
use common\base\BasicServer;
use common\server\SysServer;
use common\sql_server\GsuserSqlServer;

class GsuserServer extends BasicServer
{

    private $gs_role_id = 8; #GS助理角色ID

    public static function getUser($data){

        $obj = new self();
        $where = "role_id = ".$obj->gs_role_id;
        if( $data['realname'] ){
            $where .= " and realname= '{$data['realname']}' ";
        }
        if( $data['username'] ){
            $where .= " and username= '{$data['username']}' ";
        }
//        SysServer::getUserListByAdminInfo(self::$user_data);

        $users = GsuserSqlServer::getUser($where,$page=1,$limit=20,$order='');
//        AdminSqlServer::getUser($pwd,$page,$limit,'id desc');

        return $users;
    }

    public static function getCount($data)
    {
        $obj = new self();
        $where = "role_id = ".$obj->gs_role_id;
        if( $data['realname'] ){
            $where .= " and realname= '{$data['realname']}' ";
        }
        if( $data['username'] ){
            $where .= " and username= '{$data['username']}' ";
        }
//        SysServer::getUserListByAdminInfo(self::$user_data);

        $count = GsuserSqlServer::getCount($where);
//        AdminSqlServer::getUser($pwd,$page,$limit,'id desc');

        return $count;
    }
}