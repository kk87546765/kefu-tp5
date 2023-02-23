<?php
/**
 * Created by PhpStorm.
 * User: crosstime
 * Date: 2018/8/14
 * Time: 下午5:31
 */
namespace common\sql_server;

use common\sql_server\BaseSqlServer;
use common\model\db_customer\KefuCommonMember;
use common\model\db_statistic\KefuRecharge;
use common\server\CustomerPlatform\CommonServer;

class AccountSearch extends BaseSqlServer
{
    public static $return=[];





    public static function getUserInfo($where,$platform_info){

        $platform_suffix = empty($platform_info['platform_suffix']) ? '' :'_'.$platform_info['platform_suffix'];

        $model = CommonServer::getPlatformModel('KefuCommonMember',$platform_info['platform_suffix']);

        $sql = "select c.*,{$platform_info['platform_id']} as platform_id,'{$platform_info['platform_name']}' as platform_name 
        from 
        db_customer{$platform_suffix}.kefu_common_member c
        left join db_customer{$platform_suffix}.kefu_user_role r
        on c.uid = r.uid
        where {$where}
        group by uid
        ";

        $res = $model->query($sql);
        return $res;

    }

    public static function getUserTotalPay($uid,$platform_id){

        $model = new KefuRecharge();

        $sql = "select total_pay,day_hign_pay from db_statistic.kefu_user_recharge where uid='{$uid}' and platform_id={$platform_id}";

        $res = $model->query($sql);

        return $res;

    }


    public static function getRoleLost($uid,$platform_info){

        $platform_suffix = empty($platform_info['platform_suffix']) ? '' :'_'.$platform_info['platform_suffix'];
        $model = CommonServer::getPlatformModel('KefuUserRole',$platform_info['platform_suffix']);
        $sql = "select a.*,b.product_name,b.game_name,sum(c.amount) as count_money
                from db_customer{$platform_suffix}.kefu_user_role a 
                left join db_statistic.platform_game_info b 
                on 
                a.reg_gid = b.game_id and b.platform_id = {$platform_info['platform_id']}
                left join db_statistic.every_day_role_amount_statistics c 
                on 
                a.role_id = c.role_id and a.reg_gid = c.game_id and c.platform_id = {$platform_info['platform_id']}
                 where a.uid = {$uid}
                  group by a.role_id
                 ";

        $res = $model->query($sql);

        return $res;

    }


    public static function addChangeLog($data){

    }


}