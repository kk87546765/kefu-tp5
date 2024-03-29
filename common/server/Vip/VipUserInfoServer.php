<?php
/**
 * 系统
 */
namespace common\server\Vip;

use common\base\BasicServer;
use common\model\db_statistic\AdminUserGameServer;
use common\model\db_statistic\BecomeVipStandard;
use common\model\db_statistic\EveryDayOrderCount;
use common\model\db_statistic\KefuUserRecharge;
use common\model\db_statistic\PlatformGameInfo;
use common\model\db_statistic\VipUserInfo;
use common\server\CustomerPlatform\CommonServer;
use common\server\Statistic\UserInfoServer;
use common\server\SysServer;


class VipUserInfoServer extends BasicServer
{
    const INSERT_NUMBER = 2000;

    const PLATFORM_USER_INFO_P_U_KEY = 'platform_user_info_';
    /**
     * @param $start_time
     * @param $platformSuffix
     * @param $platform_id
     */
    public static function everyDayOrderCountNew($start_time, $platformSuffix,$platform_id)
    {
        $date = date('Y-m-d', $start_time);

        $model = new EveryDayOrderCount();

        $where = [];
        $where['date'] = $date;
        $where['platform_id'] = $platform_id;
        //删除已经存在的数据
        $res = $model->where($where)->delete();
        //查出昨天有充值用户及昨天充值总数
        $data = self::getUserDayPayCount($start_time, $platformSuffix,$platform_id);

        if (!empty($data)) {
            $result = self::insertEveryDayPayCount($data,$start_time);

            if ($result) {
                self::updateKeFuUserOtherFieldNew($data);
            }
        }
    }

    /**
     * @param $start_time
     * @param string $platformSuffix
     * @param int $platform
     * @return false
     */
    public static function getUserDayPayCount($start_time, $platformSuffix='', $platform = 0)
    {

        $model = CommonServer::getPlatformModel('KefuPayOrder',$platformSuffix);
        $start_time = strtotime(date('Y-m-d', $start_time));
        $end_time = $start_time + 86400;
        $sql = "select 
                    uid
                    ,user_name
                    ,gid as game_id
                    ,game_name
                    ,server_id
                    ,server_name
                    ,role_id
                    ,role_name
                    ,pay_channel
                    ,SUM(amount) as amount_count
                    ,MAX(amount) as day_hign_pay
                    ,MAX(pay_time) as pay_time
                    ,$platform as platform_id
                    ,count(id) as order_count
                    ,SUM(CASE WHEN amount > ".StatisticServer::$big_order_limit_def." THEN amount ELSE 0 END ) AS big_order_sum
                    ,SUM(CASE WHEN amount > ".StatisticServer::$big_order_limit_def." THEN 1 ELSE 0 END ) AS big_order_count
                from kefu_pay_order 
                where 1 and pay_time >= $start_time and pay_time < $end_time 
                GROUP BY uid,gid,server_id,pay_channel";

        return $model->query($sql);//统计昨天充值总数
    }

    /**
     * @param $data
     * @param $start_time
     * @return false
     */
    public static function insertEveryDayPayCount($data,$start_time)
    {
        if (empty($data)) return false;
        $date = date('Y-m-d', $start_time);
        $strTime = time();
        $insertData = [];
        foreach ($data as $value){
            $value['date'] = $date;
            $value['add_time'] = $strTime;
            $value['channel_id'] = $value['pay_channel'];
            $value['user_name'] = !empty($value['user_name']) ? addslashes($value['user_name']) : '';
            $value['game_name'] = !empty($value['game_name']) ? addslashes($value['game_name']) : '';
            $value['server_name'] = !empty($value['server_name']) ? addslashes($value['server_name']) : '';
            $value['role_name'] = !empty($value['role_name']) ? addslashes($value['role_name']) : '';
            unset($value['pay_channel']);
            $value['p_u'] = $value['platform_id'].'_'.$value['uid'];
            $value['p_g'] = $value['platform_id'].'_'.$value['game_id'];
            $value['p_g_s'] = $value['platform_id'].'_'.$value['game_id'].'_'.$value['server_id'];
            $insertData[] = $value;
        }
        unset($date);
        $keFuUserRechargeModel = new KefuUserRecharge();

        return $keFuUserRechargeModel->insertAll($insertData);
    }

    /**
     * 更新用户每天充值相关信息（30天累充、总充另外统计）
     * @param $data
     * @return bool
     */
    protected static function updateKeFuUserOtherFieldNew($data)
    {
        $updateInfo = $updateByInsert = $existUserArr = [];
        $nowTime = time();

        foreach ($data as $value) {
            $key = $value['platform_id'].'_'.$value['uid'];
            if (!isset($updateInfo[$key])) {
                $updateInfo[$key]['platform_id'] = $value['platform_id'];
                $updateInfo[$key]['uid'] = $value['uid'];

                $updateInfo[$key]['single_hign_pay'] = 0;
                $updateInfo[$key]['day_hign_pay'] = 0;
                $updateInfo[$key]['total_pay'] = 0;
                $updateInfo[$key]['last_day_pay'] = 0;
                $updateInfo[$key]['last_pay_time'] = 0;
                $updateInfo[$key]['total_time'] = $nowTime;
                $updateInfo[$key]['update_time'] = $nowTime;

            }
            if ($value['pay_time'] >= $updateInfo[$key]['last_pay_time']){
                $updateInfo[$key]['server_id'] = $value['server_id'];
                $updateInfo[$key]['server_name'] = !empty($value['server_name']) ? addslashes($value['server_name']) : '';
                $updateInfo[$key]['last_pay_game_id'] = $value['game_id'];
                $updateInfo[$key]['role_id'] = $value['role_id'];
                $updateInfo[$key]['role_name'] = !empty($value['role_name']) ? addslashes($value['role_name']) : '';
                $updateInfo[$key]['last_pay_time'] = $value['pay_time'];
            }
            if ($value['day_hign_pay'] > $updateInfo[$key]['single_hign_pay']) {
                $updateInfo[$key]['single_hign_pay'] = $value['day_hign_pay'];
            }

            $updateInfo[$key]['total_pay'] += $value['amount_count'];//当日总充值
            $updateInfo[$key]['day_hign_pay'] = $updateInfo[$key]['total_pay'];//当日总充值
            $updateInfo[$key]['last_day_pay'] = $updateInfo[$key]['total_pay'];//当日总充值
        }

        $keFuUserRechargeModels = new KefuUserRecharge();
        $countUpdateInfo = array_keys($updateInfo);

        //每2000条数据插入一次
        for ($i=0;count($countUpdateInfo) >$i*self::INSERT_NUMBER;$i++)
        {
            $start = empty($i*self::INSERT_NUMBER) ? 0 : $i*self::INSERT_NUMBER;
            $tmpWhereArr = array_slice($countUpdateInfo, $start, self::INSERT_NUMBER);
            $res = $keFuUserRechargeModels->rechargeUserIfExist($tmpWhereArr);
            if (!empty($res)) $existUserArr = array_merge($existUserArr, $res);
        }
        unset($countUpdateInfo,$res);

        foreach ($updateInfo as $key=>$value){
            if (in_array($key, $existUserArr)) {
                $updateByInsert[] = $value;
            }
        }

        unset($updateInfo,$existUserArr);
        if (!empty($updateByInsert)) {
            for ($i=0;count($updateByInsert) >$i*self::INSERT_NUMBER;$i++)
            {
                $start = empty($i*self::INSERT_NUMBER) ? 0 : $i*self::INSERT_NUMBER;
                $tmpArr = array_slice($updateByInsert, $start, self::INSERT_NUMBER);
                $res = $keFuUserRechargeModels->updateUserOtherFieldNew($tmpArr);
            }
        }
        return true;
    }

    public static function updateUserPayInfo($start_time, $platform_info){
        $time_log = [];
        logTime($time_log,'all','start');
        $date = date('Y-m-d', $start_time);

        $EveryDayOrderCount = new EveryDayOrderCount();
        //查询当天有充值用户
        $where = [];
        $where['date'] = $date;
        $where['platform_id'] = $platform_info['platform_id'];
        logTime($time_log,'user_list','start');
        $user_list = $EveryDayOrderCount->field('p_u')->where($where)->group('p_u')->select()->toArray();
        logTime($time_log,'user_list','end');

        if(!$user_list){
            echo 'no user';
            return false;
        }
        $limit = 2000;
        $p_u_arr = [];
        foreach ($user_list as $k => $v){
            $p_u_arr[] = $v['p_u'];
        }

        $time_arr = timeCondition('month',$start_time);
        $thirty_day_before = date('Y-m-d',($start_time-30*24*3600));

        $where = [];
        $where[] = ['p_u','in',$p_u_arr];
        $where[] = ['date','<=',$date];

        $field = "uid";
        $field .= ",platform_id";
        $field .= ",sum(amount_count) as total_pay";
        $field .= ",sum(CASE WHEN date > '$thirty_day_before' THEN amount_count ELSE 0 END) as thirty_day_pay";
        $field .= ",sum(CASE WHEN date >= '".date('Y-m-d',$time_arr['starttime'])."' AND date <= '".date('Y-m-d',$time_arr['endtime'])."' THEN amount_count ELSE 0 END) as month_pay";

        $time_log['count_data_begin'] = time();
        logTime($time_log,'count_data','start');


        $count_data = $EveryDayOrderCount
            ->field($field,2)
            ->where(setWhereSql($where,''))
            ->group('p_u')
            ->select()
            ->toArray();

        logTime($time_log,'count_data','end');
        $keFuUserRechargeModels = new KefuUserRecharge();
        logTime($time_log,'do_count_data','start');
        for ($i=0;count($count_data) >$i*$limit;$i++)
        {
            $start = empty($i*$limit) ? 0 : $i*$limit;
            $tmpArr = array_slice($count_data, $start, $limit);

            $res = $keFuUserRechargeModels->updateUserRechargeTotalPayAndThirtyDayPayField($tmpArr);
        }
        logTime($time_log,'do_count_data','end');
        logTime($time_log,'all','end');
        print_r($time_log);
        return $time_log;
    }

    /**
     * 获取平台用户信息
     * @param string $platform
     * @param string $uid
     * @return array|false|mixed
     */
    public static function getPlatformUserInfoByUid($platform = '',$uid = '')
    {
        $result = false;
        if (empty($platform) || empty($uid)) return $result;

        $redisModel = new Redis();
        $cacheKey = Common::creatCacheKey(self::PLATFORM_USER_INFO_P_U_KEY.$platform."_".$uid);
        $info = $redisModel->get()->get($cacheKey);
        if (!empty($info)) {
            $result = json_decode($info,true);
            return $result;
        }
        //数据库查找平台用户信息
        $uids = [$uid];
        $field = ['uid','user_name','reg_date','reg_channel','mobile','reg_gid','reg_ip'];
        $res = KefuCommonMember::getFieldInfoByUidAndSuffix($uids,$platform,$field);
        if (!empty($res[0]) && is_array($res[0])) {
            $result = $res[0];
            $result['platform'] = $platform; //增加平台标识字段
            $redisModel->get()->set($cacheKey,json_encode($result),86400*30);
        }
        return $result;
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
        $config = $BecomeVipStandardModel->getOneByWhere($where);

        if(!$config){
            $where = [];
            $where['vip_game_product_id'] = $id;
            $where['game_id'] = 0;
            $where['static'] = 1;
            $config = $BecomeVipStandardModel->getOneByWhere($where);
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
        $where[] = ['user_name','=',''];
        $where['platform_id'] = $platform_info['platform_id'];


        $list = $KefuUserRecharge->where(setWhereSql($where,''))->limit(0,$limit)->order('id asc')->select();

        if(!$list->toArray()){
            return ['code'=>1,'msg'=>'end'];
        }

        $uid_arr = [];
        foreach ($list as $item){
            $uid_arr[] = $item->uid;
        }

        $CommonUserMemberModel = CommonServer::getPlatformModel('KefuCommonMember',$platform_info['suffix']);

        $where = [];
        $where[] = ['uid','in',$uid_arr];
        $common_member_list = $CommonUserMemberModel->where(setWhereSql($where,''))->select()->toArray();

        if(!$common_member_list){
            return ['code'=>4,'msg'=>'no common member list'];
        }

        $common_member_list = arrReSet($common_member_list,'uid');

        $model = new VipUserInfo();
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
                $res = $model->update($up_data,$where);
                if($res) $count_vip_user++;
            }
        }

        $msg = 'kf:'.$count_kf_user.'  vip:'.$count_vip_user;

        return ['code'=>0,'msg'=>$msg];
    }

    public static function vipAscriptionDistribution(){
        //获取产品分配配置及游戏数据
        //[p_p.'_'.game_id] => Array
        //(
        //    [day_pay] => 1000
        //    [thirty_day_pay] => 5000
        //    [total_pay] => 5000
        //)
        $gameBecomeConfig = [];

        $vgpConfig = [];
        $model =new BecomeVipStandard();
        $becomeArr = $model->getConfigInfo(['A.static'=>1]);

        foreach ($becomeArr as $value) {
            $this_p_p = $value['platform_id'].'_'.$value['product_id'];
            $vgpConfig[$value['id']] = [
                'platform_id'=>$value['platform_id'],
                'product_id'=>$value['product_id'],
                'p_p'=>$this_p_p,
            ];

            $this_key = $this_p_p.'_'.$value['game_id'];

            $gameBecomeConfig[$this_key]['day_pay'] = $value['day_pay'];
            $gameBecomeConfig[$this_key]['thirty_day_pay'] = $value['thirty_day_pay'];
            $gameBecomeConfig[$this_key]['total_pay'] = $value['total_pay'];
        }
        unset($becomeArr);

        //获取分配的区服对应的
        $AdminUserGameServerModel = new AdminUserGameServer();
        $res = $AdminUserGameServerModel->select()->toArray();

        $adminArr = [];
        //[p_p] => Array
        //(
        //    [admin_user_id] => Array
        //    (
        //        server_ids
        //    )
        //)
        foreach ($res as $v) {
            $this_info = getArrVal($vgpConfig,$v['vip_game_product_id']);
            if(!$this_info) continue;
            $adminArr[$this_info['p_p']][$v['admin_user_id']][]=$v['server_id'];
        }
        unset($res);

        $PlatformGameInfoModel = new PlatformGameInfo();
        $platformGameList = $PlatformGameInfoModel->select()->toArray();

        foreach ($platformGameList as $value) {
            $this_p_p = $value['platform_id'].'_'.$value['product_id'];
            $this_key = $value['platform_id'].'_'.$value['product_id'].'_'.$value['game_id'];

            $this_info = getArrVal($gameBecomeConfig,$this_key);
            if(!$this_info){
                $this_key = $value['platform_id'].'_'.$value['product_id'].'_0';
            }

            foreach (getArrVal($adminArr,$this_p_p,[]) as $adminArr_k => $adminArr_v){
                foreach ($adminArr_v as $adminArr_v_k => $adminArr_v_v){
                    $gameBecomeConfig[$this_key]['p_g'][$adminArr_k][] = $value['platform_id'].'_'.$value['game_id'].'_'.$adminArr_v_v;
                }
            }
        }
        $model = new VipUserInfo();
        foreach ($gameBecomeConfig as $gbc_ppg=>$gbc_v) {

            $this_info = $gbc_v;
            if(isset($gbc_v['p_g']) && $gbc_v['p_g']){

                foreach ($gbc_v['p_g'] as $admin_id => $v) {
                    $this_info['p_g_s'] = $v;
                    $model->userDistributeVipAscription($admin_id, $this_info);
                }
            }
        }
    }


    public static function updateUserRechargeOverTime($param){

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

        $before_30_day = timeCondition('day',strtotime('-31 day'));
        $where = [];
        $where[] = ['thirty_day_pay','>',0];
        $where[] = ['last_pay_time','>=',$before_30_day['starttime']];
        $where[] = ['last_pay_time','<=',$before_30_day['endtime']];
        $where['platform_id'] = $platform_info['platform_id'];


        $count = $KefuUserRecharge->where(setWhereSql($where,''))->count();

        if(!$count){
            return ['code'=>1,'msg'=>'end'];
        }

        $res = $KefuUserRecharge->where(setWhereSql($where,''))->limit($limit)->update(['thirty_day_pay'=>0]);

        $model = new VipUserInfo();

        $res = $model->where(setWhereSql($where,''))->limit($limit)->update(['thirty_day_pay'=>0]);

        return ['code'=>0,'msg'=>'continue'];
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

        $count = $KefuUserRecharge->where(setWhereSql($where,''))->count();

        if(!$count){
            return ['code'=>1,'msg'=>'end'];
        }

        $res = $KefuUserRecharge->where(setWhereSql($where,''))->limit($limit)->update(['thirty_day_pay'=>0]);

        $model = new VipUserInfo();

        $res = $model->where(setWhereSql($where,''))->limit($limit)->update(['thirty_day_pay'=>0]);

        return ['code'=>0,'msg'=>'continue'];
    }

    /**
     * @param int $startTime
     * @param int $endTime
     * @param string $platformSuffix
     * @param $platform_id
     * @return bool
     */
    public static function todayOrderCount($startTime=0, $endTime=0,$platformSuffix='',$platform_id)
    {
        $result = false;

        //获取用户充值信息
        $res = self::getUserToDayOrderCount($startTime,$endTime,$platformSuffix,$platform_id);//

        $sum = 0;
        if (!empty($res) && is_array($res)) {
            $sum = self::insertOnUpdateEveryDayPayCount($res,$startTime);
        }

        if ($sum) $result = true;
        return $result;
    }

    /**
     * @param $start_time
     * @param $end_time
     * @param string $platformSuffix
     * @param int $platform
     * @return false
     */
    public static function getUserToDayOrderCount($start_time, $end_time,$platformSuffix='', $platform = 0)
    {
        $model = CommonServer::getPlatformModel('KefuPayOrder',$platformSuffix);

        $sql = "select uid from kefu_pay_order 
            where dateline >= $start_time and dateline < $end_time 
            GROUP BY uid";

        $uid_list = $model->query($sql);
        
        if(!$uid_list){
            return false;
        }
        $uid = [];
        foreach ($uid_list as $item){
            $uid[] = $item['uid'];
        }

        if(!$uid){
            return false;
        }

        $uid = implode(',',$uid);

        $payStarTime = strtotime(date('Y-m-d', $start_time));
        $payEndTime = $payStarTime + 86400;
        $sql = "select 
                    uid
                    ,user_name
                    ,gid as game_id
                    ,game_name
                    ,server_id
                    ,server_name
                    ,role_id
                    ,role_name
                    ,pay_channel
                    ,SUM(amount) as amount_count
                    ,MAX(amount) as day_hign_pay
                    ,MAX(pay_time) as pay_time
                    ,$platform as platform_id
                    ,count(id) as order_count
                    ,SUM(CASE WHEN amount > ".StatisticServer::$big_order_limit_def." THEN amount ELSE 0 END ) AS big_order_sum
                    ,SUM(CASE WHEN amount > ".StatisticServer::$big_order_limit_def." THEN 1 ELSE 0 END ) AS big_order_count
                from kefu_pay_order 
                where uid in($uid) and pay_time >= $payStarTime and pay_time < $payEndTime  
                GROUP BY uid,gid,server_id,pay_channel";
        $res = $model->query($sql);

        if (!empty($res)) return $res;

        return false;
    }

    /**
     * @param $data
     * @param $start_time
     * @return false|\Phalcon\Mvc\Model\Resultset\Simple
     */
    public static function insertOnUpdateEveryDayPayCount($data,$start_time)
    {
        if (empty($data)) return false;
        $date = date('Y-m-d', $start_time);
        $strTime = time();
        $insertData = [];
        foreach ($data as $value){
            $value['date'] = $date;
            $value['add_time'] = $strTime;
            $value['channel_id'] = $value['pay_channel'];
            $value['user_name'] = !empty($value['user_name']) ? addslashes($value['user_name']) : '';
            $value['game_name'] = !empty($value['game_name']) ? addslashes($value['game_name']) : '';
            $value['server_name'] = !empty($value['server_name']) ? addslashes($value['server_name']) : '';
            $value['role_name'] = !empty($value['role_name']) ? addslashes($value['role_name']) : '';
            unset($value['pay_channel']);
            $insertData[] = $value;
        }
        unset($date);

        return UserInfoServer::insertOnUpdateEveryDayOrderCount($insertData);
    }

    /**
     * 更新EveryDayCount 30天累充
     * @param $start_time
     * @return false
     */
    public static function __updateUserRechargeThirtyDayPay($start_time)
    {

        $date = isset($start_time) ? date('Y-m-d', ($start_time - 30*86400)) : date("Y-m-d", strtotime(date('Y-m-d', time())." -31 day"));

        $res = $updateByInsert = $existUserArr = [];

        $EveryDayOrderCountModel = new EveryDayOrderCount();

        $where = [];
        $where['date'] = $date;

        $field = 'platform_id,uid,p_u as puid,sum(amount_count) as thirty_day_pay';

        $data = $EveryDayOrderCountModel
            ->field($field)
            ->where($where)
            ->group('p_u')
            ->select()->toArray();

        if (empty($data)) return false;

        $KeFuUserRechargeModel = new KefuUserRecharge();
        $countUpdateInfo = array_column($res, 'puid');
        //每2000条数据插入一次
        for ($i=0;count($countUpdateInfo) >$i*self::INSERT_NUMBER;$i++)
        {
            $start = empty($i*self::INSERT_NUMBER) ? 0 : $i*self::INSERT_NUMBER;
            $tmpWhereArr = array_slice($countUpdateInfo, $start, self::INSERT_NUMBER);
            $tmp = $KeFuUserRechargeModel->rechargeUserIfExist($tmpWhereArr);
            if (!empty($tmp)) $existUserArr = array_merge($existUserArr, $tmp);
        }
        unset($countUpdateInfo);

        foreach ($res as $v) {
            if (in_array($v['puid'], $existUserArr)) {
                $updateByInsert[] = $v;
            }
        }

        unset($updateInfo,$existUserArr);
        if (!empty($updateByInsert)) {
            for ($i=0;count($updateByInsert) >$i*self::INSERT_NUMBER;$i++)
            {
                $start = empty($i*self::INSERT_NUMBER) ? 0 : $i*self::INSERT_NUMBER;
                $tmpArr = array_slice($updateByInsert, $start, self::INSERT_NUMBER);
                $res = $KeFuUserRechargeModel->updateUserRechargeThirtyDayPayField($tmpArr);
            }
        }
    }

    /**
     * 月支付统计归零
     * @param $param
     * @return array
     */
    public static function updateUserRechargeOverTimeMonthPay($param){

        $platform_id = getArrVal($param,'platform_id',0);

        if(!$platform_id){
            return ['code'=>3,'msg'=>'no platform'];
        }

        $KefuUserRecharge = new KefuUserRecharge();

        $time_arr = timeCondition('month');

        $where = [];
        $where[] = ['month_pay','>',0];
        $where[] = ['last_pay_time','<=',$time_arr['starttime']];
        $where['platform_id'] = $platform_id;


        $count = $KefuUserRecharge->where(setWhereSql($where,''))->count();

        if(!$count){
            return ['code'=>1,'msg'=>'end'];
        }

        $res = $KefuUserRecharge->where(setWhereSql($where,''))->update(['month_pay'=>0]);

        $model = new VipUserInfo();

        $res = $model->where(setWhereSql($where,''))->update(['month_pay'=>0]);

        return ['code'=>0,'msg'=>'continue'];
    }

    public static function updateUserRemarkPay($param){

        $platform_info = getArrVal($param,'platform_info',[]);

        $time_arr = timeCondition('day',strtotime('yesterday'));

        $where = [];
        $where['platform_id'] = $platform_info['platform_id'];
        $where[] = ['last_pay_time','>=',$time_arr['starttime']];
        $where[] = ['last_pay_time','<=',$time_arr['endtime']];
        $where[] = ['remark_time','>=',0];

        $field = "
            platform_id
            ,uid
            ,remark_time
        ";

        $model = new VipUserInfo();

        $user_list = $model->field($field)->where(setWhereSql($where,''))->select()->toArray();

        if(!$user_list) return false;

        $EveryDayCountModel = new EveryDayOrderCount();
        foreach ($user_list as $item){
            $where = [];
            $where['p_u'] = $item['platform_id'].'_'.$item['uid'];
            $where[] = ['date','>=',date('Y-m-d',$item['remark_time'])];
            $where[] = ['date','<=',date('Y-m-d',$time_arr['starttime'])];

            $pay_count = $EveryDayCountModel->field('sum(amount_count)')->where(setWhereSql($where,''))->find()->toArray();

            $model->where(getDataByField($item,['platform_id','uid']))->update(['remark_pay'=>$pay_count[0]?$pay_count[0]:0]);
        }

        return true;
    }

}
