<?php
/**
 * Created by PhpStorm.
 * User: crosstime
 * Date: 2018/8/14
 * Time: 下午5:31
 */
namespace common\sql_server;


use common\model\db_customer\LossUser;
use common\model\db_customer\LossUserSub;
use think\Config;
use think\Db;

class LossUserSqlServer extends BaseSqlServer
{

    public static function insertInfo($data)
    {
        $time = time();
        $model = new LossUser();
        $sql = "insert ignore  into loss_user (`platform_id`,`phone`,`udid`,`last_login_time`,`reg_time`,`add_time`) value
                            ({$data['platform_id']},'{$data['mobile']}','{$data['udid']}',{$data['login_date']},{$data['reg_time']},{$time})";
        $res = $model->execute($sql);
        return $res;
    }

    public static function insertInfoSub($data)
    {
        $time = time();
        $model = new LossUserSub();
        $sql = "insert ignore into loss_user_sub (`platform_id`,`phone`,`udid`,`uid`,`str_uid`,`username`,`add_time`) value
                            ({$data['platform_id']},'{$data['mobile']}','{$data['udid']}','{$data['uid']}','{$data['uid']}','{$data['uname']}','{$time}')";
        $res = $model->execute($sql);
        return $res;
    }

    public static function update($data)
    {
        $model = new LossUserSub();
        $res = $model->isUpdate(1)->update($data);
        return $res;
    }

    public static function getInfo($where)
    {
        $model = new LossUser();
        $res = $model->where($where)->select();
        return $res;
    }

    public static function getSubInfo($where)
    {
        $model = new LossUserSub();
        $res = $model->where($where)->select();
        return $res;
    }

    public static function getCount($where)
    {
        $model = new LossUser();

//        $config =  Config::get('database')['rds'];
//        $ab = Db::connect($config);

        $sql = "select count(*)as count from db_customer.loss_user  {$where}";
//        $res = $ab->query($sql);

        $res = $model->where($where)->count();

        return $res[0]['count'];
    }

    //判断全账号充值、发送次数、间隔日期
    public static function screenUidsByLossUser($data ,$Platform_suffex='')
    {

        $interval_day_time = mktime(0,0,0,date('m'),date('d')-$data['interval_day'],date('Y'));
        $min_loss_day = mktime(0,0,0,date('m'),date('d')-$data['max_loss_day']-1,date('Y'));
        $max_loss_day = mktime(0,0,0,date('m'),date('d')-$data['min_loss_day']-1,date('Y'));

        $sql = "SELECT
                z.*,
                upp.up_id,
                upp.up_name,
                sum(edras.amount_count) AS product_money,
                kur.role_level AS `level`
            FROM
                (
                    SELECT
                    a.platform_id,
                    a.account_money,
                    a.phone,
                    a.last_login_time,
                    str_uid AS uid
                    FROM
                        loss_user a
                    LEFT JOIN loss_user_sub b ON a.phone = b.phone
                    WHERE
                        1 = 1
                    AND a.platform_id = {$data['platform_id']} 
                    AND a.account_money >= {$data['min_account_money']} 
                    AND a.account_money <= {$data['max_account_money']} 
                    AND a.send_num <= {$data['send_num']}
                    AND a.last_send_time <= {$interval_day_time}
                    AND a.last_login_time >= {$min_loss_day}
                    AND a.last_login_time <= {$max_loss_day}
                    
                  
                ) z
            LEFT JOIN db_statistic.every_day_order_count edras ON z.uid = edras.uid
            AND edras.platform_id = {$data['platform_id']} 
            LEFT JOIN db_customer_{$Platform_suffex['platform_suffix']}.kefu_user_role kur ON z.uid = kur.uid
            AND edras.role_id = kur.role_id
            LEFT JOIN db_statistic.platform_game_info pg ON edras.game_id = pg.game_id
            AND edras.platform_id = pg.platform_id
            LEFT JOIN db_statistic.game_product pp ON pp.product_id = pg.product_id
            AND pp.platform_id = pg.platform_id
            LEFT JOIN db_statistic.up_game_product upp ON pp.up_id = upp.up_id
            AND pp.platform_id = upp.platform_id
            AND upp.up_id != ''
            WHERE
                edras.platform_id = {$data['platform_id']} 
            AND kur.role_level >= {$data['min_level']} 
            AND kur.role_level <= {$data['max_level']} 
            GROUP BY
                z.phone,
                edras.uid,
                upp.up_id
            HAVING
                up_id IN ({$data['loss_up_id']})
            AND product_money >= {$data['min_loss_up_money']}
            AND product_money <= {$data['max_loss_up_money']}

        ";


        $model = new LossUser();
        $res = $model->query($sql);

        return $res;


    }
    public static function updateSendStatus($data,$where)
    {
        $model = new LossUser();
        $res = $model->isUpdate(1)->update($data,$where);
        return $res;
    }


    //判断流失产品充值
    public static function screenUidsByRecharge($user_info)
    {
        array_column('uid');
        $sql = "select ";
    }

}