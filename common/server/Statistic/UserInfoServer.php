<?php
/**
 * 系统
 */
namespace common\server\Statistic;


use common\base\BasicServer;
use common\model\db_statistic\BecomeVipStandard;
use common\model\db_statistic\EveryDayOrderCount;
use common\model\db_statistic\SellWorkOrder;
use common\model\db_statistic\VipUserInfo;
use common\server\SysServer;
use common\model\db_statistic\KefuUserRecharge;
use common\server\CustomerPlatform\CommonServer;



class UserInfoServer extends BasicServer
{
    public static function updateUserBaseInfo($param){

        $limit = getArrVal($param,'limit',100);

        $platform = getArrVal($param,'platform','');

        if(!$platform){
            return ['code'=>3,'msg'=>'no platform'];
        }

        $platform_list = SysServer::getPlatformList();

        $platform_info = [];
        foreach ($platform_list as $item){
            if($item['suffix'] == $platform){
                $platform_info = $item;
                break;
            }
        }

        if(!$platform_info){
            return ['code'=>2,'msg'=>'no platform info'];
        }


        $KefuUserRecharge = new KefuUserRecharge();

        $where = [];
        $where['user_name'] = ['=',''];
        $where['platform_id'] = $platform_info['platform_id'];
//        dd($where);

        $list = $KefuUserRecharge->where($where)->limit($limit)->order('id asc')->select();

        if(!$list->toArray()){
            return ['code'=>1,'msg'=>'end'];
        }

        $uid_arr = [];
        foreach ($list as $item){
            $uid_arr[] = $item->uid;
        }

        $CommonUserMemberModel = CommonServer::getPlatformModel('KefuCommonMember',$platform_info['suffix']);

        $where = [];
        $where['uid'] = ['in',$uid_arr];
        $common_member_list = $CommonUserMemberModel->where($where)->select()->toArray();

        if(!$common_member_list){
            return ['code'=>4,'msg'=>'no common member list'];
        }

        $common_member_list = arrReSet($common_member_list,'uid');

        $model = new thisModel();
        $now_time = time();
        $count_kf_user = 0;//
        $count_vip_user = 0;//
        foreach ($list as $v){
            if(isset($common_member_list[$v->uid])){
                $this_info = $common_member_list[$v->uid];
                $up_data = [];
                $up_data['user_name'] = !empty($this_info['user_name']) ? addslashes($this_info['user_name']): '';
                $up_data['mobile'] = !empty($this_info['mobile']) ? addslashes($this_info['mobile']) : '';
                $up_data['game_id'] = $this_info['reg_gid'];
                $up_data['reg_ip'] = $this_info['reg_ip'];
                $up_data['reg_time'] = $this_info['reg_date'];
                $up_data['add_time'] = $now_time;

                $where = [];
                $where['platform_id'] = $platform_info['platform_id'];
                $where['uid'] = $v->uid;

                $res = $KefuUserRecharge->where($where)->update($up_data);
                if($res) $count_kf_user++;

                unset($up_data['add_time']);
                $res = $model->where($where)->update($up_data);
                if($res) $count_vip_user++;
            }
        }

        $msg = 'kf:'.$count_kf_user.'  vip:'.$count_vip_user;

        return ['code'=>0,'msg'=>$msg];
    }

    public static function checkVipUserCanDistribute($user_info){

        $platform_id = $user_info['platform_id'];

        $GameProductServer = new GameProductServer();
        $info = $GameProductServer->getPlatformGameInfoByGameId($user_info['last_pay_game_id'],$platform_id);

        if(!$info){
            return false;
        }

        $id = $GameProductServer->getVGPIdByPlatformIdProductId($platform_id,$info->product_id);

        if(!$id){
            return false;
        }

        $BecomeVipStandardModel = new BecomeVipStandard();

        $where = [];
        $where['vip_game_product_id'] = $id;
        $where['game_id'] = $user_info['last_pay_game_id'];
        $where['static'] = 1;
        $config = $BecomeVipStandardModel->where($where)->find();

        if(!$config){
            $where = [];
            $where['vip_game_product_id'] = $id;
            $where['game_id'] = 0;
            $where['static'] = 1;
            $config = $BecomeVipStandardModel->where($where)->find();
            if(!$config){
                return false;
            }
        }
        $config = $config->toArray();

        if($config['day_pay'] > $user_info['day_hign_pay']
            && $config['thirty_day_pay'] > $user_info['thirty_day_pay']
            && $config['total_pay'] > $user_info['total_pay']
        ){
            return false;
        }

        return true;

    }

    public static function updateUserThirtyDayPay($param){

        $limit = getArrVal($param,'limit',100);

        $platform_id = getArrVal($param,'platform_id','');

        if(!$platform_id){
            return ['code'=>3,'msg'=>'no platform_id'];
        }

        $KefuUserRecharge = new KefuUserRecharge();

        $before_30_day = timeCondition('day',strtotime('-31 day'));
        $where = [];
        $where[] = ['thirty_day_pay','>',0];
        $where[] = ['last_pay_time','<=',$before_30_day['endtime']];
        $where['platform_id'] = $platform_id;


        $count = $KefuUserRecharge->where($where)->count();

        if(!$count){
            return ['code'=>1,'msg'=>'end'];
        }

        $res = $KefuUserRecharge->where($where)->limit($limit)->update(['thirty_day_pay'=>0]);

        $model = new thisModel();

        $res = $model->where($where)->limit($limit)->update(['thirty_day_pay'=>0]);

        return ['code'=>0,'msg'=>'continue'];
    }

    /**
     * @param $data
     * @return false
     */
    public static function insertOnUpdateEveryDayOrderCount($data)
    {
        if (empty($data)) return false;

        $model = new EveryDayOrderCount();

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
        return $model->execute($sql);
    }

    public static function updateRemarkTime($param){


        $limit = getArrVal($param,'limit',10);

        $limit_start = 0;

        $sql = 'SELECT s.platform_id,s.uid,max(s.add_time) as add_time FROM sell_work_order AS s INNER JOIN vip_user_info AS v ON s.platform_id = v.platform_id AND s.uid = v.uid';

        $where = [];
        $where[] = ['s.type','=',SellWorkOrder::REMARK_ORDER];
        $where[] = ['s.status','>',0];
        $where[] = ['v.remark_time','=',0];
        $sql .= setWhereSql($where);
        $sql .= " GROUP BY s.p_u LIMIT $limit_start,$limit";

        $model = new VipUserInfo();

        $list = $model->query($sql);

        if(!$list){
            return ['code'=>1,'msg'=>'no_user_data'];
        }

        $EveryDayCountModel = new EveryDayOrderCount();
        $flag = 0;
        foreach ($list as $item){
            $where = [];
            $where['p_u'] = $item['platform_id'].'_'.$item['uid'];
            $where[] = ['date','>=',date('Y-m-d',$item['add_time'])];
            $pay_info = $EveryDayCountModel->field('sum(amount_count) as amount_count')->where(setWhereSql($where,''))->find()->toArray();

            $save_data = [];
            $save_data['remark_time'] = $item['add_time'];
            if($pay_info['amount_count'] > 0){
                $save_data['remark_pay'] = $pay_info['amount_count'];
            }
            $res = $model->where(getDataByField($item,['platform_id','uid']))->update($save_data);
            if($res) $flag++;
        }

        return ['code'=>0,'msg'=>'continue count:'.$limit.' success:'.$flag];
    }

    /**
     * 清除异常用户数据(订单存在，用户信息不存在)
     * @param $param
     * @return array
     */
    public static function delErrorUser($param){

        $limit = getArrVal($param,'limit',100);

        $platform_id = getArrVal($param,'platform_id','');

        if(!$platform_id){
            return ['code'=>3,'msg'=>'no platform_id'];
        }

        $platform_list = SysServer::getPlatformList();

        $platform_info = [];
        foreach ($platform_list as $item){
            if($item['platform_id'] == $platform_id){
                $platform_info = $item;
                break;
            }
        }

        if(!$platform_info){
            return ['code'=>2,'msg'=>'no platform info'];
        }


        $KefuUserRecharge = new KefuUserRecharge();

        $where = [];
        $where[] = ['user_name','=',''];
        $where['platform_id'] = $platform_info['platform_id'];


        $list = $KefuUserRecharge->field('uid,platform_id,is_vip')->where($where)->limit(0,$limit)->order('id asc')->select()->toArray();

        if(!$list){
            return ['code'=>1,'msg'=>'end'];
        }

        $CommonUserMemberModel = CommonServer::getPlatformModel('KefuCommonMember',$platform_info['suffix']);

        $VipModel = new VipUserInfo();

        $EveryDayOrderCountModel = new EveryDayOrderCount();
        $flag = 0;
        foreach ($list as $item){
            $where = [];
            $where[] = ['uid','=',$item['uid']];
            $where[] = ['user_name','!=',''];
            $count_res = $CommonUserMemberModel->where($where)->count();

            if($count_res){
                continue;
            }
            //删除 kefu_user_recharge
            $sql = "DELETE FROM kefu_user_recharge where uid ='".$item['uid']."' and platform_id = $item[platform_id]";
            $res = $KefuUserRecharge->execute($sql);

            //删除 every_day_order_count
            $sql = "DELETE FROM every_day_order_count where uid ='".$item['uid']."' and platform_id = $item[platform_id]";
            $res = $EveryDayOrderCountModel->execute($sql);

            //删除 kefu_pay_order
            $sql = "DELETE FROM kefu_pay_order where uid =".$item['uid'];
            $res = $CommonUserMemberModel->execute($sql);

            //删除 vip_user_info
            if($item['is_vip'] == 1){
                $sql = "DELETE FROM vip_user_info where uid ='".$item['uid']."' and platform_id = $item[platform_id]";
                $res = $VipModel->execute($sql);
            }
            $flag++;
        }


        $msg = 'count:'.count($list).'  finish:'.$flag;

        return ['code'=>0,'msg'=>$msg];
    }
}
