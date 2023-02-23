<?php
/**
 * Created by PhpStorm.
 * User: crosstime
 * Date: 2018/8/14
 * Time: 下午5:31
 */
namespace common\sql_server;

use common\model\db_statistic\GameProduct;
use common\model\db_statistic\GameServerStatistic;
use common\model\db_customer_platform\KefuPayOrder;

class GameServerStatisticSqlServer extends BaseSqlServer
{








    /**
     * @param $data
     * @return mixed
     */
    public function insertInfo($data)
    {
        $model = new GameServerStatistic();
        $sql = "insert into game_server_statistic(`date`,`game_id`,`center_product_id`,`product_id`,`product_name`,`server_name`,`server_id`,`count_money`,`reg_num`,`role_reg_num`,`add_time`,`platform_id`) values";
        foreach( $data as $v ){
            if(empty($v['date'])){
                continue;
            }
            $sql .= "('{$v['date']}',".
                "{$v['game_id']},".
                "{$v['center_product_id']},".
                "{$v['product_id']},".
                "'{$v['product_name']}',".
                "'{$v['server_name']}',".
                "'{$v['server_id']}',".
                "{$v['pay_money']},".
                "{$v['reg_num']},".
                "{$v['role_reg_num']},".
                "'{$v['add_time']}',".
                "{$v['platform_id']}),";
         
        }


        $sql = trim($sql,",");
        $sql .= ' ON DUPLICATE KEY UPDATE count_money = values(count_money),
        reg_num = values(reg_num),
        role_reg_num = values(role_reg_num)';

        $res = $model->execute($sql);

        return $res;
    }


    //根据角色注册区服统计充值
    public function getRoleAmount($start_time,$end_time,$platform = '',$platform_id = 0){

        $pay_order_obj = new KefuPayOrder();
        $data = [];
        if($platform && $platform_id){
            $sql = "select {$platform_id} as platform_id,FROM_UNIXTIME(pay_time,'%Y-%m-%d')as date_time,b.product_id,b.product_name,a.uid,c.server_id,c.server_name,a.role_id,a.role_name,a.gid,sum(a.amount) as money,count(a.order_id)as orders 
                    from db_customer_{$platform}.kefu_pay_order a 
                    left join db_customer_{$platform}.kefu_user_role c on c.role_id = a.role_id 
                    left join db_statistic.platform_game_info b on a.gid = b.game_id and b.platform_id = {$platform_id} where a.pay_time >={$start_time} and a.pay_time<{$end_time} group by a.role_id,a.gid,date_time having c.server_id is not null";


            $res = $pay_order_obj->query($sql);

            $res = isset($res) ? $res->toArray() : [];

            $data[$platform_id] = $res;
        }else{
            $platform_list = PlatformList::getPlatformList();

            foreach($platform_list as $k=>$v){
                if($v['platform_suffix'] != 'asjd'){
                    $sql = "select {$v['platform_id']} as platform_id,FROM_UNIXTIME(pay_time,'%Y-%m-%d')as date_time,b.product_id,b.product_name,a.uid,c.server_id,c.server_name,a.role_id,a.role_name,a.gid,sum(a.amount) as money,count(a.order_id)as orders  
                        from db_customer_{$v['platform_suffix']}.kefu_pay_order a 
                        left join db_customer_{$v['platform_suffix']}.kefu_user_role c on c.role_id = a.role_id 
                        left join db_statistic.platform_game_info b on a.gid = b.game_id and b.platform_id = {$v['platform_id']} 
                        where a.pay_time >={$start_time} and a.pay_time<{$end_time} group by role_id,gid,date_time having c.server_id is not null";
                    $res = $pay_order_obj->query($sql);
                    $res = isset($res) ? $res->toArray() : [];
                    $data[$v['platform_id']] = $res;
                }

            }
        }

        return $data;

    }



    public static function getMoneyCount($data){

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
	     left join db_statistic.distributional_game_server b 
	     on b.product_id = a.product_id
	     and b.platform_id = a.platform_id 
	     and a.server_id=b.server_id
	     where {$data['condition']} and b.garrison_product_id is not null
	     group by a.product_id,a.platform_id,a.server_id 
	      ";

        $model = new GameServerStatistic();
        $count = $model->query($sql);

        return $count;
    }





    /**
     * 获取120天总充值
     * @param int $product_id   平台产品id，
     * @param int $platform_id   平台id，
     * @param int $open_time   开服时间，
     */
    public static function getTimeRegAndMoney($game_id,$platform_id,$server_id,$open_time,$days = 120){

        if(empty($game_id) || empty($platform_id) || empty($server_id) || empty($open_time)){
            return false;
        }
        $stop_time = date('Y-m-d',mktime(0,0,0,date('m',$open_time),date('d',$open_time)+$days,date('Y',$open_time)));
        $open_time = date('Y-m-d',$open_time);
        $model = new GameServerStatistic();

        $sql = "select 
         a.*,b.reg_date,b.pay_date,b.days,b.pay_money,b.pay_people,b.pay_num
         from db_statistic.game_server_statistic a
         Left Join db_statistic.pro_server_statistic_day b 
         on a.game_id = b.gid and a.platform_id = b.platform_id and a.server_id = b.server_id
	     where
	     a.`date` = '{$open_time}'
	     and a.game_id = {$game_id} 
	     and a.platform_id = {$platform_id}
	     and a.server_id = '{$server_id}'
	     and b.gid = {$game_id} 
	     and b.platform_id = {$platform_id}
	     and b.server_id = '{$server_id}'
	     and b.reg_date = '{$open_time}'
	     and b.days <= {$days}
         ORDER BY days
        ";

        $server_money = $model->query($sql);

        return $server_money;
    }




}