<?php
/**
 * Created by PhpStorm.
 * User: crosstime
 * Date: 2018/8/14
 * Time: 下午5:31
 */
namespace common\sql_server;

use common\model\db_customer_platform\KefuUserRole;
use common\server\CustomerPlatform\CommonServer;

class KefuUserRoleSqlServer extends BaseSqlServer
{


    public static function getList($platform,$where='',$page=1,$limit=20,$order='id desc')
    {
        $offset = ($page-1)*$limit;

        $kefu_user_role_model = CommonServer::getPlatformModel('KefuUserRole',$platform);

        $blocks = $kefu_user_role_model->where($where)->limit($offset.','.$limit)->order($order)->select();

        $blocks = isset($blocks) ? $blocks->toArray() : [];

        return $blocks;
    }

    public static function getRoleInfo($platform,$server_id,$role_id)
    {

        if(empty($platform) || empty($server_id) || empty($role_id)){
            return false;
        }

        $model = new KefuUserRole();
        $sql = "select * from db_customer_{$platform}.kefu_user_role where role_id='{$role_id}' and server_id = '{$server_id}'";
        $res = $model->query($sql);

        return $res;
    }

    public static function getRoleInfoByUid($platform,$uid,$role_id,$field = '*')
    {
        if(empty($platform) || empty($uid) || empty($role_id)){
            return false;
        }

        $model = new KefuUserRole();
        $sql = "select {$field} from db_customer_{$platform}.kefu_user_role where  uid = {$uid} and role_id='{$role_id}' ";
        $res = $model->query($sql);

        return $res;
    }




}