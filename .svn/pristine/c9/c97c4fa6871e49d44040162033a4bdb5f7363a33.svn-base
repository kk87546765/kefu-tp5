<?php
/**
 * Created by PhpStorm.
 * User: crosstime
 * Date: 2018/8/14
 * Time: 下午5:31
 */
namespace common\sql_server;

use common\model\db_customer_platform\KefuUserRole;
class KefuUserRoleSqlServer extends BaseSqlServer
{


    public static function getRoleInfo($platform,$server_id,$role_id){

        if(empty($platform) || empty($server_id) || empty($role_id)){
            return false;
        }

        $model = new KefuUserRole();
        $sql = "select * from db_customer_{$platform}.kefu_user_role where role_id='{$role_id}' and server_id = '{$server_id}'";
        $model->query($sql);
        $res = isset($res) ? $res->toArray() : [];
        return $res;
    }




}