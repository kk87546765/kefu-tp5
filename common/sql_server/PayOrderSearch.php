<?php
/**
 * Created by PhpStorm.
 * User: crosstime
 * Date: 2018/8/14
 * Time: 下午5:31
 */
namespace common\sql_server;

use common\model\db_customer_platform\KefuPayOrder;
use common\server\CustomerPlatform\CommonServer;
class PayOrderSearch extends BaseSqlServer
{
    public static $return=[];

    public static function getList($platform_info,$where,$order,$order_type,$page,$limit){
        $page = ($page-1)*$limit;
        $sql = "SELECT
                    po.uid,
                    po.order_id,
                    po.third_party_order_id,
                    po.user_name,
                    po.amount,
                    po.gid,
                    po.game_name,
                    po.server_id,
                    po.server_name,
                    po.role_id,
                    po.role_name,
                    po.reg_channel,
                    po.pay_channel,
                    po.payment,
                    po.yuanbao_status,
                    po.pay_time,
                    po.first_login_game_id,
                    po.first_login_game_time,
                    pgi.game_name as first_login_game_name,
                    pgi.product_name,
                    r.role_level,
                    '{$platform_info['platform_name']}' as platform
             
                 FROM
                    db_customer_{$platform_info['platform_suffix']}.kefu_pay_order po 
                 LEFT JOIN db_customer_{$platform_info['platform_suffix']}.kefu_user_role r ON r.role_id = po.role_id
                 LEFT JOIN db_statistic.platform_game_info pgi
                 ON  pgi.game_id = po.first_login_game_id AND pgi.platform_id = {$platform_info['platform_id']}
           
                 WHERE
                 {$where}
                order by {$order} {$order_type}
                limit {$page},{$limit}    
        ";
        $model = CommonServer::getPlatformModel('KefuPayOrder',$platform_info['platform_suffix']);
        $res = $model->query($sql);


        return $res;
    }

    public static  function getCount($platform_info,$where){

        $sql = "SELECT count(*) as count FROM(
                SELECT
                    po.uid,
                    po.order_id,
                    po.third_party_order_id,
                    po.user_name,
                    po.amount,
                    po.gid,
                    po.game_name,
                    po.server_id,
                    po.server_name,
                    po.role_id,
                    po.role_name,
                    po.reg_channel,
                    po.pay_channel,
                    po.payment,
                    po.yuanbao_status,
                    po.pay_time,
                    po.first_login_game_id,
                    po.first_login_game_time,
                    pgi.game_name as first_login_game_name,
                    pgi.product_name,
                    r.role_level,
                    '{$platform_info['platform_name']}' as platform
                FROM
                    db_customer_{$platform_info['platform_suffix']}.kefu_pay_order po
                LEFT JOIN db_customer_{$platform_info['platform_suffix']}.kefu_user_role r ON r.role_id = po.role_id
                LEFT JOIN db_statistic.platform_game_info pgi
                ON  pgi.game_id = po.first_login_game_id AND pgi.platform_id = {$platform_info['platform_id']} 
                WHERE
                {$where}
             )a
        ";
        $model = CommonServer::getPlatformModel('KefuPayOrder',$platform_info['platform_suffix']);
        $count = $model->query($sql);

        return $count[0]['count'];

    }



}