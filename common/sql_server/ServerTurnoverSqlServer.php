<?php
/**
 * Created by PhpStorm.
 * User: crosstime
 * Date: 2018/8/14
 * Time: 下午5:31
 */
namespace common\sql_server;
use common\sql_server\BaseSqlServer;
use common\model\db_statistic\ServerStatistic;


class ServerTurnoverSqlServer extends BaseSqlServer
{


    public static function getCount($where)
    {
        $model = new ServerStatistic();
        $sql = "select count(*) as count
         from (select a.*,b.open_time,b.platform_id as b_platform_id,b.garrison_product_id
         from db_statistic.server_statistic a
	     left join db_statistic.distributional_server b 
	     on b.product_id = a.product_id
         and b.platform_id = a.platform_id
         and a.server_id=b.server_id
	     where {$where} and b.garrison_product_id is not null
	     group by a.product_id,a.platform_id,a.server_id ) a
	      ;";
        $count = $model->query($sql);
        return $count;

    }

    public static function getList($where,$page=1,$limit=20)
    {
        $model = new ServerStatistic();

        $offset = ($page - 1) * $limit;

        $sql = "select	a.date,
                    a.platform_id,
                    a.server_id,
                    a.product_id,
                    b.garrison_product_id,
                  b.garrison_product_name, 
                    b.server_name,
                    b.role_id,
                    b.role_name,
                    b.admin_id,
                    b.admin_name,
                    b.add_time,
                    b.start_time,
                    b.end_time,
                    b.open_time,
                    b.is_end,
                    b.`status`,
                    sum(a.count_money) AS money,
                    sum(a.reg_num) AS reg_num 
	     from db_statistic.server_statistic a
	     left join db_statistic.distributional_server b 
	     on b.product_id = a.product_id
	     and b.platform_id = a.platform_id 
	     and a.server_id=b.server_id
	     where {$where} and b.garrison_product_id is not null
	     group by a.product_id,a.platform_id,a.server_id 
	     
         ORDER BY b.garrison_product_id desc,b.server_id*1 asc
	     limit {$offset},{$limit} ";

        $list = $model->query($sql);
        return $list;
    }



    public static function getMoneyCount($where){

        $model = new ServerStatistic();

        $sql = "select	a.date,
                    a.platform_id,
                    a.server_id,
                    a.product_id,
                    b.garrison_product_id,
                  b.garrison_product_name, 
                    b.server_name,
                    b.role_id,
                    b.role_name,
                    b.admin_id,
                    b.admin_name,
                    b.add_time,
                    b.start_time,
                    b.end_time,
                    b.open_time,
                    b.is_end,
                    b.`status`,
                    sum(a.count_money) AS money,
                    sum(a.reg_num) AS reg_num 
	     from db_statistic.server_statistic a
	     left join db_statistic.distributional_server b 
	     on b.product_id = a.product_id
	     and b.platform_id = a.platform_id 
	     and a.server_id=b.server_id
	     where {$where} and b.garrison_product_id is not null
	     group by a.product_id,a.platform_id,a.server_id 
	      ";

        $count = $model->query($sql);

        return $count;
    }




}