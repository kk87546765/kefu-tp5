<?php
/**
 * Created by PhpStorm.
 * User: crosstime
 * Date: 2018/8/14
 * Time: 下午5:31
 */
namespace common\sql_server;


use common\sql_server\BaseSqlServer;
use common\model\db_statistic\DistributionalServer;

class SettledSqlServer extends BaseSqlServer
{



    public static function add($data)
    {
        $model = new self;

        foreach ($data as $key => $item) {
            $model->$key = $item;
        }
        $model->create_time = time();
        $res = $model->create();

        if(!$res){
            return false;
        }
        return $model;
    }

    public static function getList($where)
    {

        $model = new DistributionalServer();

        $sql = "SELECT
                garrison_product_name,
                garrison_product_id,
                count(*) AS total_server,
                sum(CASE WHEN `status` = 0 THEN 1 ELSE 0 END) no_in,
                sum(CASE WHEN `status` = 1 THEN 1 ELSE 0 END) have_in,
                sum(CASE WHEN `status` = 2 THEN 1 ELSE 0 END) have_pao,
                sum(CASE WHEN `status` = 3 THEN 1 ELSE 0 END) have_tui
            FROM
                distributional_server
                 WHERE {$where}
            GROUP BY
                garrison_product_id
";

        $res = $model->query($sql);

        return $res;


    }



    public static function getCount($where)
    {

        $model = new DistributionalServer();


        $sql = "SELECT COUNT(*) AS count FROM (SELECT
                garrison_product_name,
                garrison_product_id,
                count(*) AS total_server,
                sum(CASE WHEN `status` = 0 THEN 1 ELSE 0 END) no_in,
                sum(CASE WHEN `status` = 1 THEN 1 ELSE 0 END) have_in,
                sum(CASE WHEN `status` = 2 THEN 1 ELSE 0 END) have_pao,
                sum(CASE WHEN `status` = 3 THEN 1 ELSE 0 END) have_tui
            FROM
                distributional_server
                 WHERE {$where}
            GROUP BY
                garrison_product_id) c
";

        $count = $model->query($sql);

        return isset($count[0]['count']) ? $count[0]['count'] : 0;
    }



    public static function getLTVList($where)
    {
        $model = new DistributionalServer();
        $sql = "SELECT
                a.date,
                a.product_name,
                sum(a.reg_num) as reg_num,
                sum(a.count_money) as money,
                a.product_id,
                a.platform_id,
                    b.`status`
                FROM
                    db_statistic.server_statistic a
                inner join db_statistic.distributional_server b ON a.product_id = b.product_id
                AND a.platform_id = b.platform_id
                AND a.server_id = b.server_id
                WHERE
                 {$where}
                GROUP BY a.date,product_id,platform_id,b.status
               
                ";

        $res = $model->query($sql);

        return $res;


    }


    public static function arrangeData($date,$data){



        if(empty($data) || empty($date)){
            return [];
        }
        $total = [];

//        $date = strtotime('2021-05-10');
        foreach($data as $k=>$v){
            //7天入驻服数据
            if(strtotime($v['date'])<=$date && $date - strtotime($v['date'])  < 7*86400 && $v['status'] == 1){

                $total['server_in_7']['reg_num'] += $v['reg_num'];
                $total['server_in_7']['money'] += $v['money'];
                $total['server_in_7']['date'] = date('Y-m-d',$date);

            }

            //7天空服数据
            if(strtotime($v['date'])<=$date && $date - strtotime($v['date']) < 7*86400 && $v['status'] == 0){
                $total['server_no_7']['reg_num'] += $v['reg_num'];
                $total['server_no_7']['money'] += $v['money'];
                $total['server_no_7']['date'] = date('Y-m-d',$date);
            }

            //14天入驻服数据
            if(strtotime($v['date'])<=$date && $date - strtotime($v['date'])< 14*86400 && $v['status'] == 1){
                $total['server_in_14']['reg_num'] += $v['reg_num'];
                $total['server_in_14']['money'] += $v['money'];
                $total['server_in_14']['date'] = date('Y-m-d',$date);
            }

            //14天空服数据
            if(strtotime($v['date'])<=$date && $date - strtotime($v['date']) < 14*86400 && $v['status'] == 0){
                $total['server_no_14']['reg_num'] += $v['reg_num'];
                $total['server_no_14']['money'] += $v['money'];
                $total['server_no_14']['date'] = date('Y-m-d',$date);
            }

            //30天入驻服数据
            if(strtotime($v['date'])<=$date && $date - strtotime($v['date']) < 30*86400 && $v['status'] == 1){
                $total['server_in_30']['reg_num'] += $v['reg_num'];
                $total['server_in_30']['money'] += $v['money'];
                $total['server_in_30']['date'] = date('Y-m-d',$date);
            }

            //30天空服数据
            if(strtotime($v['date'])<=$date && $date - strtotime($v['date']) < 30*86400 && $v['status'] == 0){

                $total['server_no_30']['reg_num'] += $v['reg_num'];
                $total['server_no_30']['money'] += $v['money'];
                $total['server_no_30']['date'] = date('Y-m-d',$date);
            }
        }



        return $total;
    }




}