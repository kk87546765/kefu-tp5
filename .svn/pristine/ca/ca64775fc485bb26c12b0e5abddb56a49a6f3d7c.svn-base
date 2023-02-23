<?php
/**
 * Created by PhpStorm.
 * User: crosstime
 * Date: 2018/8/14
 * Time: 下午5:31
 */
namespace common\sql_server;

use common\libraries\Common;
use common\model\db_statistic\DistributionalGameServer;
use common\model\db_statistic\EveryDayRoleAmountStatistics;

class EveryDayRoleAmountStatisticsSqlServer extends BaseSqlServer
{


    /**
     * @param $data
     * @return mixed
     */
    public static function insertInfo($data)
    {
        $model = new EveryDayRoleAmountStatistics();
        $time = time();
        $sql = "insert into every_day_role_amount_statistics(`date`,`uid`,`product_id`,`platform_id`,`role_id`,`role_name`,`game_id`,`server_id`,`server_name`,`amount`,`orders`,`add_time`) values";
        foreach( $data as $v ){

            $sql .= "('{$v['date_time']}',".
                "{$v['uid']},".
                "{$v['product_id']},".
                "{$v['platform_id']},".
                "'{$v['role_id']}',".
                "'".addslashes($v['role_name'])."',".
                "{$v['gid']},".
                "'{$v['server_id']}',".
                "'{$v['server_name']}',".
                "{$v['money']},".
                "{$v['orders']},".
                "'{$time}'),";

        }

        $sql = trim($sql,",");
        $sql .= ' ON DUPLICATE KEY UPDATE amount = values(amount),
        orders = values(orders)';
        $res = $model->execute($sql);
        return $res;
    }



    public static function getRoleNameByRoleID($data){


        $platform = Common::getPlatformList();

        $platform_suffix = '';
        foreach($platform as $k=>$v){
            if($v['platform_id'] == $data['platform_id']){
                $platform_suffix = $v['platform_suffix'];
            }
        }
        if(!$platform_suffix){
            return false;
        }


        //通过角色id和区服id获取信息，有可能不是唯一，所以通过游戏id核对产品id
        $sql = "select role_name,server_id,reg_gid from db_customer_{$platform_suffix}.kefu_user_role where role_id = '{$data['role_id']}' and server_id = '{$data['server_id']}'";
        $res = self::execute($sql);
        $res = isset($res) ? $res->toArray() : [];
        if(!$res){
            return false;
        }
        $role_name = "";
        foreach($res as $k=>$v){
            $sql = "select product_id from db_statistic.platform_game_info where game_id = {$v['reg_gid']} and platform_id = {$data['platform_id']}";
            $product_id = self::execute($sql);
            $product_id = isset($product_id) ? $product_id->toArray() : [];
            if($product_id[0]['product_id'] == $data['product_id']){
                $role_name = $res[$k]['role_name'];
                return $role_name;
            }
        }

        return $role_name;
    }


    public static function getUserRoleLastLoginTime($data){
        if(empty($data['role_id']) || empty($data['server_id'])){
            return false;
        }
        $platform = Common::getPlatformList();

        $platform_suffix = '';
        foreach($platform as $k=>$v){
            if($v['platform_id'] == $data['platform_id']){
                $platform_suffix = $v['platform_suffix'];
            }
        }
        if(!$platform_suffix){
            return false;
        }



        //通过角色id和区服id获取信息，有可能不是唯一，所以通过游戏id核对产品id
        $sql = "select login_date,role_name,server_id,reg_gid from db_customer_{$platform_suffix}.kefu_user_role where role_id = '{$data['role_id']}' and server_id = '{$data['server_id']}'";

        $res = self::excuteSQL($sql);
        $res = isset($res) ? $res->toArray() : [];

        return $res[0]['login_date'];
    }

}