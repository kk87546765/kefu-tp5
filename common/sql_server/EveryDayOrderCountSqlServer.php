<?php
/**
 * Created by PhpStorm.
 * User: crosstime
 * Date: 2018/8/14
 * Time: 下午5:31
 */
namespace common\sql_server;

use common\model\db_statistic\EveryDayOrderCount;
class EveryDayOrderCountSqlServer extends BaseSqlServer
{

    /**
     * @param int $start_time
     * @param int $end_time
     * @return array
     */
    public function getPlatformGame($start_time = 0, $end_time = 0)
    {
        $model = new EveryDayOrderCount();
        $result = array();
        if ($start_time == 0) $start_time = strtotime(date('Y-m-d',strtotime("-1 day")));
        if ($end_time == 0) $end_time = strtotime(date('Y-m-d',time()));
        if ($start_time > $end_time) return $result;
        $sql = "select platform_id,game_id,game_name,1 as static
                FROM every_day_order_count 
                WHERE pay_time >= $start_time and pay_time < $end_time 
                GROUP BY platform_id,game_id";
        $res = $model->query($sql);
        if (!empty($res)) {
            $result = $res->toArray();
        }
        return $result;
    }

    /**
     * 统计游戏平台区分每日充值相关数据
     * @param $where
     * @param string $columns
     * @param string $group
     * @param string $order
     * @param string $having
     * @return mixed
     */
    public function getAllByWhere($where,$field  ='',$order='pay_time desc,id desc')
    {
        $model = new EveryDayOrderCount();

        $list = $model->field($columns)->where($where)->order($order)->select();

        return $list->toArray();
    }

    /**
     * @param array $where
     * @param string $field
     * @param string $order
     * @param string $group
     * @return mixed
     */
    public function getOneByWhere(array $where,$field='*',$order='id DESC',$group=''){

        $model = new EveryDayOrderCount();

        $list = $model->field($field)->where($where)->order($order)->find();

        return $list;

    }

    /**
     * @param array $where
     * @param string $field
     * @param string $group
     * @return mixed
     */
    public function countByWhere(array $where){

        $model = new EveryDayOrderCount();

        $list = $model->where($where)->count();

        return $list;

    }

    /**
     * @param $data
     * @return false
     */
    public function insertOnUpdateEveryDayOrderCount($data)
    {
        $model = new EveryDayOrderCount();
        if (empty($data)) return false;
        $sql = "insert into every_day_order_count(
                                  `date`
                                  ,`uid`
                                  ,`platform_id`
                                  ,`role_id`
                                  ,`role_name`
                                  ,`game_id`
                                  ,`game_name`
                                  ,`server_id`
                                  ,`server_name`
                                  ,`channel_id`
                                  ,`amount_count`
                                  ,`day_hign_pay`
                                  ,`order_count`
                                  ,`big_order_count`
                                  ,`big_order_sum`
                                  ,`pay_time`
                                  ,`p_u`
                                  ,`p_g`
                                  ,`p_g_s`
                                  ,`add_time`) values";
        foreach( $data as $v ){
            $sql .= "('{$v['date']}',".
                "'{$v['uid']}',".
                "{$v['platform_id']},".
                "'{$v['role_id']}',".
                "'{$v['role_name']}',".
                "'{$v['game_id']}',".
                "'{$v['game_name']}',".
                "'{$v['server_id']}',".
                "'{$v['server_name']}',".
                "'{$v['channel_id']}',".
                "{$v['amount_count']},".
                "{$v['day_hign_pay']},".
                "'{$v['order_count']}',".
                "{$v['big_order_count']},".
                "{$v['big_order_sum']},".
                "{$v['pay_time']},".
                "'{$v['platform_id']}_{$v['uid']}',".
                "'{$v['platform_id']}_{$v['game_id']}',".
                "'{$v['platform_id']}_{$v['game_id']}_{$v['server_id']}',".
                "{$v['add_time']}),";
        }
        $sql = trim($sql,",");

        $sql .= " ON DUPLICATE KEY UPDATE 
                   role_id = VALUES(role_id),
                   role_name = VALUES(role_name),
                   amount_count = VALUES(amount_count),
                   pay_time = VALUES(pay_time),
                   order_count = VALUES(order_count),
                   day_hign_pay = CASE 
                    WHEN day_hign_pay < VALUES(day_hign_pay)
                    THEN VALUES(day_hign_pay)
                    ELSE day_hign_pay
                    END,
                   big_order_count = VALUES(big_order_count),
                   big_order_sum = VALUES(big_order_sum)";
        $res = $model->execute($sql);
        return $res;
    }




}