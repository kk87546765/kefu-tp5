<?php
/**
 * Created by PhpStorm.
 * User: crosstime
 * Date: 2018/8/14
 * Time: 下午5:31
 */
namespace common\sql_server;
use common\model\db_statistic\SellWorkOrderStatisticGameMonth;


class MouthUpholdStatisticsSqlServer extends BaseSqlServer
{


    public static function getList($where = '1',$limit = 20,$page = 1,$order = 'id desc')
    {
        $offset = ($page- 1) * $limit;

        $model = new SellWorkOrderStatisticGameMonth();

        $sql = "select * 
                from sell_work_order_statistic_game_month
                where {$where}  
                order by {$order}
                limit {$offset},{$limit} ";

        $res = $model->query($sql);

        return $res;
    }

    public static function getCount($where = '1')
    {
        $model = new SellWorkOrderStatisticGameMonth();

        $sql = "select count(*)as `count` 
                from sell_work_order_statistic_game_month 
                where {$where}  ";

        $count = $model->query($sql);

        $ret = empty($count) ? 0 : $count[0]['count'];

        return $ret;
    }

    public static function getStatistic($where = '1')
    {
        $sql="SELECT
                  	sum(all_uphold_user_count) AS all_uphold_user_count,
                    sum(month_uphold_user_amount) AS month_uphold_user_amount,
                    sum(month_new_contact_count) AS month_new_contact_count,
                    sum(month_new_contact_amount) AS month_new_contact_amount,
                    sum(month_new_distribution_contact_count) AS month_new_distribution_contact_count,
                    sum(month_new_distribution_contact_amount)AS month_new_distribution_contact_amount, 
                    sum(month_sell_work_uphold_user_count) AS month_sell_work_uphold_user_count,
                    sum(month_sell_work_uphold_user_amount) AS month_sell_work_uphold_user_amount
                    
                FROM
                    sell_work_order_statistic_game_month
                WHERE   {$where} ";

        $model = new SellWorkOrderStatisticGameMonth();

        $res = $model->query($sql);

        return $res;
    }




}