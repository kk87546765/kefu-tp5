<?php
/**
 * 系统
 */
namespace common\sql_server;
use common\model\gr_chat\Admin;


class AdminSqlServer extends BaseSqlServer
{
    protected static $model;

    public static function adminListWhere($p_data,$user_data,$role_id_arr){

        $model = new Admin();
//        $p_data['status'] = 1;

        $where = getDataByField($p_data,['role_id','status'],true);

        if(!empty($p_data['username'])){
            $where[] = ['username','like','%'.$p_data['username'].'%'];
        }

        if(!empty($p_data['realname'])){
            $where[] = ['realname','like','%'.$p_data['realname'].'%'];
        }

        if($user_data['is_admin'] == 0){

            if($role_id_arr){
                $where[] = ['role_id','in',$role_id_arr];
            }else{
                $where[] = ['role_id','=',-1];
            }

            $admin_platform_arr = splitToArr($user_data['platform']);

            $admin_platform_sql_arr = [];
            foreach ($admin_platform_arr as $item){
                $admin_platform_sql_arr[] = "FIND_IN_SET('$item',platform)";
            }
            if($admin_platform_sql_arr){
                $where['_string'] = " (".implode(' OR ',$admin_platform_sql_arr).") ";
            }else{
                $where[] = ['platform','=',-1];
            }
        }

        return $model->where(setWhereSql($where,''));
    }

    public static function getAdminInfo($params = [])
    {
        $model = new Admin();
        $sql = "select phone from gr_admin where username = '{$params['username']}' and status= 1";
        $res = $model->query($sql);
        if(!empty($res[0]['phone'])){
            return $res[0]['phone'];
        }else{
            return false;
        }

    }

    public static function getAdminInfoByWhere($where='')
    {
        $model = new Admin();
        $sql = "select * from gr_admin where {$where} ";
        $res = $model->query($sql);

        if(!empty($res[0])){
            return $res[0];
        }else{
            return false;
        }
    }
}
