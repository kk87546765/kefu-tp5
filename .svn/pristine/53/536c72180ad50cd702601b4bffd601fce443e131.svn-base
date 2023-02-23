<?php
/**
 * Created by PhpStorm.
 * User: crosstime
 * Date: 2018/8/14
 * Time: 下午5:31
 */
namespace common\sql_server;

use common\model\db_statistic\GameProduct;
use common\model\db_statistic\ServerStatistic;
use common\model\db_customer_platform\KefuPayOrder;

class ServerStatisticSqlServer extends BaseSqlServer
{

    /**
     *从角色信息表获取区服注册信息
     * $param array 时间区间
     */
    public function getServerUserRegInfo($start_time,$end_time){
        $reg_time = ['begin'=>$start_time,'end'=>$end_time];
        $platform_list = PlatformList::getPlatformList();

        $userData = [];

        //分别从平台拉取区服的注册人数信息
        foreach($platform_list as $k=>$v){
            if($v['platform_suffix'] != 'asjd'){

                $userData[$v['platform_suffix']] = $this->getServerRole($reg_time,$v['platform_suffix'],$v['platform_id']);

            }
        }

        return $userData;
    }



    public function getServerUserPayInfo($start_time,$end_time){

        $date = mktime(0,0,0,date('m'),date('d')-1,date('Y'));
        $platform_list = PlatformList::getPlatformList();
        $userData = [];

        //分别从平台拉取区服的充值人数信息
        foreach($platform_list as $k=>$v){
            if($v['platform_suffix'] != 'asjd'){

                $userData[$v['platform_suffix']] = $this->getServerPay($start_time,$end_time,$v['platform_id']);
//                $userData[$v['platform_id']]['pay_info'][] = ;
            }
        }


        return $userData;
    }


    private function getServerRole($reg_time = [],$platform,$platform_id){

        $start_time = strtotime($reg_time['begin']);
        $end_time = strtotime($reg_time['end']);

        $model = new GameProduct();
//        $sql = "select count(DISTINCT r.uid)as reg_num,r.server_id,r.server_name,r.reg_gid,{$platform_id} as platform_id,i.product_id,i.product_name from kefu_user_role r left join db_statistic.platform_game_info i on r.reg_gid = i.game_id and i.platform_id = {$platform_id} where r.reg_time>= {$reg_time['begin']} and reg_time<{$reg_time['end']} group by r.reg_gid,r.server_id";
        $sql = "select count(role_id)as role_reg_num,count(DISTINCT r.uid)as reg_num,r.server_id,r.server_name,r.reg_gid,{$platform_id} as platform_id,i.product_id,i.product_name,gp.id as center_product_id 
                from db_customer_{$platform}.kefu_user_role r 
                left join db_statistic.platform_game_info i 
                on r.reg_gid = i.game_id and i.platform_id = {$platform_id} 
                left join db_statistic.game_product gp 
                on i.product_id = gp.product_id and gp.platform_id = {$platform_id} 
                where r.reg_time>= {$start_time} and reg_time<{$end_time} 
                group by r.reg_gid,r.server_id";

        $res = $model->query($sql);

        return !empty($res)? $res->toArray() : false;
    }


    private function getServerPay($start_time,$end_time,$platform_id){

        $model = new GameProduct();

        $before = $start_time;
        $now = $end_time;
//        $sql = "select sum(amount_count)as pay_money,server_id,server_name,game_id from db_statistic.every_day_order_count where date between '{$before}' and '{$now}' and platform_id = {$platform_id} group by p_g_s";
        $sql = "select sum(amount)as pay_money,c.server_id,c.server_name,c.game_id,c.platform_id,i.product_id,gp.product_name,c.date,gp.id as center_product_id 
        from db_statistic.every_day_role_amount_statistics c 
        left join db_statistic.platform_game_info i on c.game_id = i.game_id and c.product_id=i.product_id and  c.platform_id = {$platform_id} 
        left join db_statistic.game_product gp on i.product_id = gp.product_id and gp.platform_id = {$platform_id}  
        where  date = '{$before}'
        and c.platform_id = {$platform_id} 
        and i.platform_id = {$platform_id} 
        group by c.server_id,c.game_id,c.platform_id";

        $res = $model->query($sql);

        return !empty($res)? $res->toArray() : false;
    }



    /**
     * @param $data
     * @return mixed
     */
    public function insertInfo($data)
    {
        $model = new ServerStatistic();
        $sql = "insert into db_statistic.server_statistic(`date`,`center_product_id`,`product_id`,`product_name`,`server_name`,`server_id`,`count_money`,`role_reg_num`,`reg_num`,`add_time`,`platform_id`) values";
        foreach( $data as $v ){

            $sql .= "('{$v['date']}',".
                "{$v['center_product_id']},".
                "{$v['product_id']},".
                "'{$v['product_name']}',".
                "'{$v['server_name']}',".
                "'{$v['server_id']}',".
                "{$v['pay_money']},".
                "{$v['role_reg_num']},".
                "{$v['reg_num']},".
                "'{$v['add_time']}',".
                "{$v['platform_id']}),";
         
        }


        $sql = trim($sql,",");
        $sql .= ' ON DUPLICATE KEY UPDATE count_money = values(count_money),
        reg_num = values(reg_num),
        role_reg_num = values(role_reg_num),
        ';

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





    /**
     * 获取120天总充值
     * @param int $product_id   平台产品id，
     * @param int $platform_id   平台id，
     * @param int $open_time   开服时间，
     */
    public static function getTimeRegAndMoney($product_id,$platform_id,$server_id,$open_time,$days = 120){

        if(empty($product_id) || empty($platform_id) || empty($server_id) || empty($open_time)){
            return false;
        }
        $stop_time = date('Y-m-d',mktime(0,0,0,date('m',$open_time),date('d',$open_time)+$days,date('Y',$open_time)));
        $open_time = date('Y-m-d',$open_time);
        $model = new serverStatistic();

        $sql = "select `date`,
        sum(count_money) AS money,
        sum(reg_num) as reg_num
         from db_statistic.server_statistic 
         
	     where
	     (`date` between  '{$open_time}' and '{$stop_time}')
	     and product_id = {$product_id} 
	     and  platform_id = {$platform_id}
	     and  server_id = '{$server_id}'
	     group by date asc,product_id,platform_id,server_id
        ";
        $server_money = $model->query($sql);

        return $server_money;
    }




}