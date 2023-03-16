<?php
/**
 * Created by PhpStorm.
 * User: crosstime
 * Date: 2018/8/14
 * Time: 下午5:31
 */
namespace common\sql_server;

use common\sql_server\BaseSqlServer;
use common\model\db_statistic\RoleSearch as RoleSearchModel;
class RoleSearch extends BaseSqlServer
{


    public static function getList($platform_id,$platform_suffix,$where,$having,$page,$limit){
        $page = ($page-1)*$limit;
        $sql = "SELECT *,sum(IF (amount IS NULL, 0, amount)) AS total_amount 
                FROM(
                    SELECT
                        r.uid,
                        r.uname,
                        r.role_id,
                        r.role_name,
                        r.trans_level,
                        r.role_level,
                        r.reg_gid,
                        pgi2.game_name as account_reg_game_name,
                        pgi.game_name as role_login_game_name,
                        m.reg_channel as account_reg_channel,
                        r.reg_channel as role_login_channel,
                        m.reg_date as account_reg_time,
                        r.reg_time as role_reg_time,
                        r.server_id,
                        r.login_date,
                        s.amount
                     FROM
                     db_customer_{$platform_suffix}.kefu_user_role r 
                    LEFT JOIN	
                    db_customer_{$platform_suffix}.kefu_common_member m ON r.uid = m.uid    
                    LEFT JOIN 
                    db_customer_{$platform_suffix}.kefu_pay_order s ON r.uid = s.uid and r.role_id = s.role_id
                    LEFT JOIN 
                    db_statistic.platform_game_info pgi on pgi.game_id = r.reg_gid 	AND pgi.platform_id = {$platform_id}
                    LEFT JOIN 
                    db_statistic.platform_game_info pgi2 on pgi2.game_id = m.reg_gid AND pgi2.platform_id = {$platform_id}		
                        WHERE
                           {$where}
                           
                    ORDER BY r.reg_time DESC
                    limit 100000000
                    )b
                GROUP BY
                    role_id
                having {$having}   
         
                ORDER BY role_reg_time desc
                limit {$page},{$limit}    
        ";

        $model = new RoleSearchModel();
        $res = $model->query($sql);

        return $res;
    }

    public static  function getCount($platform_id,$platform_suffix,$where,$having){

        $sql = "SELECT count(*) AS count FROM(
                SELECT
                    r.uid,
                    r.uname,
                    r.role_id,
                    r.role_name,
                    r.trans_level,
                    r.role_level,
                    r.reg_gid,
                    pgi2.game_name as account_reg_game_name,
                    pgi.game_name as role_login_game_name,
                    m.reg_channel as account_reg_channel,
                    r.reg_channel as role_login_channel,
                    r.reg_time as role_get_time,
                    m.reg_date as account_get_time,
                    r.server_id,
                    r.login_date,
                    sum(if(s.amount is null,0,amount )) as total_amount
                 FROM
                 db_customer_{$platform_suffix}.kefu_user_role r 
                LEFT JOIN	
                db_customer_{$platform_suffix}.kefu_common_member m ON r.uid = m.uid    
                LEFT JOIN 
                db_customer_{$platform_suffix}.kefu_pay_order s ON r.uid = s.uid and r.role_id = s.role_id	
                LEFT JOIN 
                db_statistic.platform_game_info pgi on pgi.game_id = r.reg_gid AND pgi.platform_id = {$platform_id}	
                 LEFT JOIN 
                db_statistic.platform_game_info pgi2 on pgi2.game_id = m.reg_gid AND pgi2.platform_id = {$platform_id}	
                    WHERE
                       {$where}
                GROUP BY
                    r.role_id
                having {$having}    
             )a
        ";

        $model = new RoleSearchModel();
        $res = $model->query($sql);


        return $res[0]['count'];

    }



}