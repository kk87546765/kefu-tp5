<?php
/**
 * Created by PhpStorm.
 * User: crosstime
 * Date: 2018/8/14
 * Time: 下午5:31
 */
namespace common\sql_server;


use think\console\command\make\Model;
use think\Db;

class LtvServerStatisticSqlServer extends BaseSqlServer
{

    //获取某天所有充值用户
    public static function getTodayPayPeople($data)
    {
        $now_day = date('Y-m-d',$data['now_time']);
        $now_day_str = date('Y-m-d',$data['now_time']);
        $start_time = $data['now_time'];
        $end_time = $data['now_time']+86400;
        $sql = "SELECT 
                FROM_UNIXTIME(ur.reg_time,'%Y-%m-%d') as reg_date ,
	            count(DISTINCT ur.uid) AS pay_people,
	            count(DISTINCT ur.role_id) AS pay_role,
	            count(po.order_id) AS pay_num,
	            sum(amount) AS pay_money,
	            po.gid AS p_game_id,
	            '{$now_day}' as now_time ,
	            pg.product_id,
	            FROM_UNIXTIME(po.pay_time,'%Y-%m-%d') as pay_date ,
               {$data['platform_id']} as platform_id,
               0 as new_pay_money,
	           0 as new_pay_num,
	           0 as new_pay_people,
               ur.server_id
               FROM db_customer_{$data['platform_suffix']}.kefu_user_role ur
               LEFT JOIN db_customer_{$data['platform_suffix']}.kefu_pay_order po 
               ON ur.uid = po.uid 
               AND ur.role_id = po.role_id
               AND ur.reg_gid = po.gid
               LEFT JOIN db_statistic.platform_game_info pg 
               ON po.gid = pg.game_id
               AND pg.platform_id = {$data['platform_id']}  
               LEFT JOIN db_statistic.garrison_product gp 
               ON gp.product_id = pg.product_id 
               AND gp.platform_id = {$data['platform_id']}
               WHERE
               po.pay_time >= {$start_time}
               AND po.pay_time < {$end_time}
               AND char_length(ur.reg_time)>=10
               GROUP BY
               reg_date,
               ur.reg_gid,
               ur.server_id
              ";

        $info = Db::query($sql);

        $info = isset($info)? $info->toArray(): [];

        return $info;



    }


    //获取某天所有新注册充值用户
    public static function getTodayPayNewPeople($data)
    {
        $now_day = date('Y-m-d',$data['now_time']);
        $now_day_str = date('Y-m-d',$data['now_time']);
        $start_time = $data['now_time'];
        $end_time = $data['now_time']+86400;
        $sql = "SELECT 
                FROM_UNIXTIME(ur.reg_time,'%Y-%m-%d') as reg_date ,
	            count(DISTINCT ur.uid) AS new_pay_people,
	            count(DISTINCT ur.role_id) AS pay_role,
	            count(po.order_id) AS new_pay_num,
	            sum(amount) AS new_pay_money,
	            po.gid AS p_game_id,
	            '{$now_day}' as now_time ,
	            pg.product_id,
	            0 as pay_money,
	            0 as pay_num,
	            0 as pay_people,
	            FROM_UNIXTIME(po.pay_time,'%Y-%m-%d') as pay_date ,
               {$data['platform_id']} as platform_id,

               ur.server_id
               FROM db_customer_{$data['platform_suffix']}.kefu_user_role ur
               LEFT JOIN db_customer_{$data['platform_suffix']}.kefu_pay_order po 
               ON ur.uid = po.uid 
               AND ur.role_id = po.role_id
               AND ur.reg_gid = po.gid
               LEFT JOIN db_statistic.platform_game_info pg 
               ON po.gid = pg.game_id
               AND pg.platform_id = {$data['platform_id']}   
               WHERE
               po.pay_time >= {$start_time}
               AND po.pay_time < {$end_time}
               AND ur.reg_time >={$start_time}
               AND ur.reg_time <{$end_time}
               AND char_length(ur.reg_time)>=10
               GROUP BY
               reg_date,
               ur.reg_gid,
               ur.server_id
              ";

        $info = Db::execute($sql);

        $info = isset($info)? $info->toArray(): [];

        return $info;



    }

    //获取某天注册的用户后续的充值情况
    public static function getRegPeoplePay($data)
    {
        $sql = "SELECT count(DISTINCT ur.uid) as pay_people,
	                   count(DISTINCT ur.role_id)as pay_role,
	                   count(po.order_id)as pay_num,
	                   sum(amount) as pay_money,
	                   ur.reg_gid as game_id,
	                   ur.reg_gid as game_id,
	                   {$data['product_id']} as product_id,
	                   {$data['platform_id']} as platform_id,
	                   0 as new_pay_money,
	                   0 as new_pay_num,
	                   0 as new_pay_people,
	                   ur.server_id
	            FROM db_customer_{$data['platform_suffix']}.kefu_user_role ur
                LEFT JOIN db_customer_{$data['platform_suffix']}.kefu_pay_order po 
                ON ur.uid = po.uid 
                AND ur.role_id = po.role_id
                WHERE
                ur.reg_gid = {$data['game_id']} 
                AND po.gid = {$data['game_id']} 
                AND ur.server_id = {$data['server_id']}
                AND po.server_id = {$data['server_id']}
                AND ur.reg_time >= {$data['open_time']}
                AND ur.reg_time < {$data['open_time']} + 86400 
                AND po.pay_time >= {$data['open_time']}
                AND po.pay_time < {$data['open_time']} + 86400
                GROUP BY ur.server_id
                ";

        $info = Db::query($sql);
        $info = isset($info)? $info->toArray(): [];
        return $info;

    }


    public static function addData($data)
    {

        $time = time();
        $sql = "insert into pro_server_statistic_day(`reg_date`,`platform_id`,`gid`,
                `server_id`,`pay_date`,`days`,`pay_money`,`pay_people`,`pay_num`,`new_pay_money`,`new_pay_people`,`new_pay_num`,`add_time`) values";
        foreach( $data as $v ){

            $date1 = date_create($v['reg_date']);
            $date2 = date_create($v['pay_date']);
            $diff=date_diff($date1,$date2);
            $days = $diff->days;
            $sql .= "('{$v['reg_date']}',".
                "{$v['platform_id']},".
                "{$v['p_game_id']},".
                "'{$v['server_id']}',".
                "'{$v['pay_date']}',".
                "'{$days}',".
                "{$v['pay_money']},".
                "{$v['pay_people']},".
                "{$v['pay_num']},".
                "{$v['new_pay_money']},".
                "{$v['new_pay_people']},".
                "{$v['new_pay_num']},".
                "{$time}),";

        }


        $sql = trim($sql,",");
        $sql .= ' ON DUPLICATE KEY UPDATE pay_money = values(pay_money),
        pay_people = values(pay_people),
        pay_num = values(pay_num)';

        $res = Db::execute($sql);

        return $res;
    }

    public static function updateData($data)
    {

        $time = time();
        $sql = "insert into pro_server_statistic_day(`reg_date`,`platform_id`,`gid`,
                `server_id`,`pay_date`,`days`,`pay_money`,`pay_people`,`pay_num`,`new_pay_money`,`new_pay_people`,`new_pay_num`,`add_time`) values";
        foreach( $data as $v ){

            $date1 = date_create($v['reg_date']);
            $date2 = date_create($v['pay_date']);
            $diff=date_diff($date1,$date2);
            $days = $diff->days;
            $sql .= "('{$v['reg_date']}',".
                "{$v['platform_id']},".
                "{$v['p_game_id']},".
                "'{$v['server_id']}',".
                "'{$v['pay_date']}',".
                "'{$days}',".
                "{$v['pay_money']},".
                "{$v['pay_people']},".
                "{$v['pay_num']},".
                "{$v['new_pay_money']},".
                "{$v['new_pay_people']},".
                "{$v['new_pay_num']},".
                "{$time}),";

        }


        $sql = trim($sql,",");
        $sql .= ' ON DUPLICATE KEY UPDATE new_pay_money = values(new_pay_money),
        new_pay_people = values(new_pay_people),
        new_pay_num = values(new_pay_num)';

        $res = Db::execute($sql);

        return $res;
    }






}