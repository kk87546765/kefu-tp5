<?php
/**
 * 系统
 */
namespace common\server\Vip;

use common\base\BasicServer;
use common\libraries\ApiUserInfoSecurity;
use common\model\db_customer\QcConfig;
use common\model\db_statistic\EveryDayOrderCount;
use common\model\db_statistic\GameProduct;
use common\model\db_statistic\KefuUserRecharge;
use common\model\db_statistic\SellWorkOrder;
use common\model\db_statistic\UserPrivacyInfo;
use common\model\db_statistic\VipUserInfo;
use common\model\db_statistic\VipUserOtherInfo;
use common\model\db_statistic\VipUserWelfare;
use common\server\CustomerPlatform\CommonServer;
use common\server\ListActionServer;
use common\server\SysServer;
use common\model\db_statistic\PlatformGameInfo;
use think\Db;

class VipServer extends BasicServer
{
    public static $vip_grade_list=[
        1 =>'普通VIP',
        2 =>'黄金VIP',
        3 =>'铂金VIP',
        4 =>'钻石VIP',
        5 =>'至尊VIP',
    ];
    public static $recharge_static=[
        1 =>'充值活跃',
        2 =>'充值沉默',
        3 =>'充值流失',
    ];

    public static $login_static=[
        1 =>'登录活跃',
        2 =>'登录沉默',
        3 =>'登录流失',
    ];

    public static function userRechargeList($params){

        $res = [
            'data'=>[],
            'count'=>0,
        ];
        $page = getArrVal($params,'page',1);
        $limit = getArrVal($params,'limit',20);
        $is_excel = getArrVal($params,'is_excel',0);

        $where = getDataByField($params,['uid','role_id','user_name','platform_id'],true);

        if(self::$user_data['is_admin'] == 0){
            if(self::$user_data['platform_id']){
                $where[] = ['platform_id','in',self::$user_data['platform_id']];
            }else{
                $where[] = ['platform_id','=',0];
            }
        }

        if(!empty($params['p_g'])){
            setWhereString("CONCAT(platform_id,'_',last_pay_game_id) IN('".str_replace(',',"','",$params['p_g'])."')",$where);
        }elseif(!empty($params['p_p'])){
            $PlatformGameInfoModel = new PlatformGameInfo();
            $platform_game_info_list = $PlatformGameInfoModel
                ->field('platform_id,game_id')
                ->where("concat(platform_id,'_',product_id) in ('".str_replace(',',"','",$params['p_p'])."')")
                ->select()->toArray();

            if($platform_game_info_list){
                $p_g = [];
                foreach ($platform_game_info_list as $item){
                    $p_g[] = $item['platform_id'].'_'.$item['game_id'];
                }
                setWhereString("CONCAT(platform_id,'_',last_pay_game_id) IN('".implode("','",$p_g)."')",$where);
            }else{
                setWhereString("CONCAT(platform_id,'_',last_pay_game_id) = -1",$where);
            }
        }

        if ((int)$params['server_id_max'] >= (int)$params['server_id_min'] && (int)$params['server_id_max'] >0) {
            $i = (int)$params['server_id_min'];
            for ($i; $i<= (int)$params['server_id_max']; $i++) {
                $serverIds[] = trim($params['server_prefix'].$i.$params['server_suffix'], '');
            }
        }

        if (!empty($serverIds)) {
            $where[] = ['server_id','in',$serverIds];
        }

        //充值状态
        if ($params['recharge_static'] == 1) {
            $where[] = ['thirty_day_pay','>=',1000];
        }elseif ($params['recharge_static'] == 2) {
            $where[] = ['thirty_day_pay','>=',1];
            $where[] = ['thirty_day_pay','<',1000];
        }elseif ($params['recharge_static'] == 3) {
            $where[] = ['thirty_day_pay','=',0];
        }
        //登录状态
        $dateTime = strtotime(date('Y-m-d', time()));
        if ($params['login_static'] == 1) {
            $where[] = ['last_login_time','>=',$dateTime - 3*86400];
        }elseif ($params['login_static'] == 2) {
            $where[] = ['last_login_time','>=',$dateTime - 6*86400];
            $where[] = ['last_login_time','<',$dateTime - 3*86400];
        }elseif ($params['login_static'] == 3) {
            $where[] = ['last_login_time','<',$dateTime - 6*86400];
        }

        if (!empty($params['single_hign_pay_min'])) {
            $where[] = ['single_hign_pay','>=',$params['single_hign_pay_min']];
        }

        if (!empty($params['single_hign_pay_max'])) {
            $where[] = ['single_hign_pay','<=',$params['single_hign_pay_max']];
        }

        if (!empty($params['total_pay_min'])) {
            $where[] = ['total_pay','>=',$params['total_pay_min']];
        }
        if (!empty($params['total_pay_max'])) {
            $where[] = ['total_pay','<=',$params['total_pay_max']];
        }
        if (!empty($params['thirty_day_pay_min'])) {
            $where[] = ['thirty_day_pay','>=',$params['thirty_day_pay_min']];
        }
        if (!empty($params['thirty_day_pay_max'])) {
            $where[] = ['thirty_day_pay','<=',$params['thirty_day_pay_max']];
        }
        if (!empty($params['day_hign_pay_min'])) {
            $where[] = ['day_hign_pay','>=',$params['day_hign_pay_min']];
        }
        if (!empty($params['day_hign_pay_max'])) {
            $where[] = ['day_hign_pay','<=',$params['day_hign_pay_max']];
        }
        if (!empty($params['today_pay_min']) || !empty($params['today_pay_max'])) {
            $where[] = ['last_pay_time','>=',strtotime(date("Y-m-d", strtotime("-1 day")))];
            if (!empty($params['today_pay_min'])) {
                $where[] = ['last_day_pay','>=',$params['today_pay_min']];
            }
            if (!empty($params['today_pay_max'])) {
                $where[] = ['last_day_pay','<=',$params['today_pay_max']];
            }
        }

        if ($params['vip_grade'] == 1) {
            setWhereString("total_pay >=  500 and  total_pay < 4999 and day_hign_pay >= 500 and day_hign_pay <1000",$where);
        }elseif ($params['vip_grade'] == 2) {
            setWhereString("((total_pay >=  5000 and  total_pay <= 9999) or (day_hign_pay >= 1000 and day_hign_pay <2000))",$where);
        }elseif ($params['vip_grade'] == 3) {
            setWhereString("((total_pay >=  10000 and  total_pay <= 20000) or (day_hign_pay >= 2000 and day_hign_pay <3000))",$where);
        }elseif ($params['vip_grade'] == 4) {
            setWhereString("((total_pay >=  20001 and  total_pay <= 49999) or (day_hign_pay >= 3000 and day_hign_pay <5000))",$where);
        }elseif ($params['vip_grade'] == 5) {
            setWhereString("(total_pay >=  50000 or day_hign_pay >= 5000)",$where);
        }

        $model = new KefuUserRecharge();
//        dd(compact('where','params'));
        $count = $model->where(setWhereSql($where,''))->count();

        if(!$count){
            return $res;
        }

        $res['count'] = $count;

        if($is_excel){
            $page = 0;
            $limit = 0;
        }

        $list_model = $model->where(setWhereSql($where,''));

        if($page && $limit){
            $list_model = $list_model->page($page,$limit);
        }

        if(!empty($params['sort_field'])){
            $list_model = $list_model->order("{$params['sort_field']} {$params['sort']}");
        }

        $list = $list_model->select()->toArray();

        if($list){
            $res['data'] = self::userRechargeDo($list,$is_excel);
        }

        return $res;
    }

    public static function userRechargeDo($list,$is_excel=false){
        $tmp = [];
        $new_list = [];

        $platformGameList = SysServer::getPlatformGameInfoCache();
        foreach ($list as $v) {
            $tmp[$v['platform_id'].'_'.$v['uid']] = $v;
        }
        $otherData = StatisticServer::getUserOtherDayPayData(array_keys($tmp));
        unset($list);

        //稍组装
        foreach ($tmp as $k=>$v) {

            $tmpArr = $v;
            if (!empty($otherData[$k]['today_pay']) && $otherData[$k]['today_pay'] >0) {
                $tmpArr['today_pay'] = $otherData[$k]['today_pay'];
            }else {
                $tmpArr['today_pay'] = 0;
            }
            if (!empty($otherData[$k]['last_thirty_day_pay_count']) && $otherData[$k]['last_thirty_day_pay_count'] >0) {
                $tmpArr['last_thirty_day_pay_count'] = $otherData[$k]['last_thirty_day_pay_count'];
            }else {
                $tmpArr['last_thirty_day_pay_count'] = 0;
            }

            $tmpArr['last_thirty_day_highest_pay_game'] = !empty($otherData[$k]['last_thirty_day_highest_pay_game']) ? $otherData[$k]['last_thirty_day_highest_pay_game'] : 0;
            if (!$is_excel) {
                $tmpArr['is_today_pay'] = StatisticServer::getUserTodayOrderSum($v['platform_id'],$v['uid']);
            }

            $tmpArr['vip_grade'] = self::getVipGrade($v['total_pay'], $v['day_hign_pay']);
            $tmpArr['recharge_static'] = self::getRechargeStatic($tmpArr['last_thirty_day_pay_count']);
            $tmpArr['login_static'] = self::getLoginStatic($v['last_login_time']);
            $this_p_g = $v['platform_id'].'_'.$v['last_pay_game_id'];
            $this_p_g_info = getArrVal($platformGameList,$this_p_g,[]);
            if($this_p_g_info){
                $tmpArr['product_name'] = $this_p_g_info['product_name'] ?: '';
                $tmpArr['game_name'] = $this_p_g_info['game_name'] ?: '';
            }else{
                $tmpArr['product_name'] = '';
                $tmpArr['game_name'] = '';
            }
            $this_p_g = $v['platform_id'].'_'.$tmpArr['last_thirty_day_highest_pay_game'];
            $this_p_g_info = getArrVal($platformGameList,$this_p_g,[]);
            if($this_p_g_info){
                $tmpArr['last_thirty_day_highest_pay_game'] = $this_p_g_info['product_name'] ?: '';
            }else{
                $tmpArr['last_thirty_day_highest_pay_game'] = '';
            }

            $tmpArr['vip_date'] = !empty($v['vip_time']) ? date('Y-m-d H:i:s', $v['vip_time']) : '';
            $tmpArr['last_pay_time'] = !empty($v['last_pay_time']) ? date('Y-m-d H:i:s', $v['last_pay_time']) : '';
            $tmpArr['last_login_time'] = !empty($v['last_login_time']) ? date('Y-m-d H:i:s', $v['last_login_time']) : '';
            $tmpArr['last_distribute_time'] = !empty($v['last_distribute_time']) ? date('Y-m-d H:i:s', $v['last_distribute_time']) : '';
            $tmpArr['last_record_time'] = !empty($v['last_record_time']) ? date('Y-m-d H:i:s', $v['last_record_time']) : '';

            $new_list[] = $tmpArr;
        }
        return $new_list;
    }

    /**
     * @param int $total_pay
     * @param int $day_pay_count
     * @return string
     */
    public static function getVipGrade($total_pay = 0, $day_pay_count = 0)
    {
        $vipGrade = 0;
        if ($total_pay >=  50000 || $day_pay_count >= 5000) {
            $vipGrade = 5;
        }elseif (($total_pay >=  20001 &&  $total_pay <= 49999) || ($day_pay_count >= 3000 && $day_pay_count <5000)) {
            $vipGrade = 4;
        }elseif (($total_pay >=  10000 &&  $total_pay <= 20000) || ($day_pay_count >= 2000 && $day_pay_count <3000)) {
            $vipGrade = 3;
        }elseif (($total_pay >=  5000 &&  $total_pay <= 9999) || ($day_pay_count >= 1000 && $day_pay_count <2000)) {
            $vipGrade = 2;
        }elseif ($total_pay >=  500 &&  $total_pay < 4999 && $day_pay_count >= 500 && $day_pay_count <1000) {
            $vipGrade = 1;
        }

        return self::$vip_grade_list[$vipGrade] ?? '无等级';
    }

    /**
     * @param int $thirty_day_pay
     * @return string
     */
    public static function getRechargeStatic($thirty_day_pay = 0)
    {

        if ($thirty_day_pay >= 1000) {
            $static = 1;
        }elseif ($thirty_day_pay >= 1 &&  $thirty_day_pay < 1000) {
            $static = 2;
        }else {
            $static = 3;
        }
        return self::$recharge_static[$static] ?? '--';
    }

    /**
     * @param int $thirty_day_pay
     * @return string
     */
    public static function getLoginStatic($last_login_time = 0)
    {
        $time = strtotime(date('Y-m-d',time()))-$last_login_time;

        if ($time <= 3*86400) {
            $static = 1;
        }elseif ($time <= 6*86400) {
            $static = 2;
        }else {
            $static = 3;
        }
        return self::$login_static[$static] ?? '--';
    }

    public static function userInputInfoList($param){

        $page = getArrVal($param,'page',1);
        $limit = getArrVal($param,'limit',20);

        $UserPrivacyInfoModel = new UserPrivacyInfo();

        $sql = "SELECT
                    A.*, B.user_name,
                    B.total_pay,
                    B.day_hign_pay,
                    B.thirty_day_pay,
                    B.ascription_vip
                FROM
                    `user_privacy_info` AS A
                INNER JOIN vip_user_info AS B ON A.platform_id = B.platform_id
                AND A.uid = B.uid ";

        $sqlCount = "SELECT
                    count(1) as sum
                FROM
                    `user_privacy_info` AS A
                INNER JOIN vip_user_info AS B ON A.platform_id = B.platform_id
                AND A.uid = B.uid ";

        $where = " WHERE 1=1 ";


        if(self::$user_data['is_admin'] == 0){
            if(self::$user_data['platform_id']){
                $where .= " and A.platform_id in(".implode(',',self::$user_data['platform_id']).")";
            }else{
                $where .= " and A.platform_id = 0";
            }
        }

        if(isset($param['p_p']) && !empty($param['p_p'])){
            $where.= " AND concat(A.platform_id,'_',A.product_id) in('".str_replace(',','\',\'',$param['p_p'])."')";
        }
        if (isset($param['platform_id']) && !empty($param['platform_id'])) {
            if(preg_match('/,/',$param['platform_id'])){
                $where .= " and B.platform_id in(".$param['platform_id'].')';
                $where .= " and A.platform_id in(".$param['platform_id'].')';
            }else{
                $where .= " and A.platform_id = ".$param['platform_id'];
                $where .= " and B.platform_id = ".$param['platform_id'];
            }
        }

        if (isset($param['ascription_vip']) && !empty($param['ascription_vip'])) {
            if(preg_match('/,/',$param['ascription_vip'])){
                $where .= " and B.ascription_vip in(".$param['ascription_vip'].')';
            }else{
                $where .= " and B.ascription_vip = ".$param['ascription_vip'];
            }

        }elseif (isset($param['group_id']) && !empty($param['group_id'])) {
            $kf_ids = SysServer::getAdminListByGroupIds($param['group_id'],2);
            if (is_array($kf_ids) && !empty($kf_ids)) {
                $str = "'".trim(implode(',' , $kf_ids),',')."'";
                $where .= " and B.ascription_vip in ($str) ";
            }else {
                $where .= " and B.ascription_vip = -1";
            }
        }

        if (isset($param['handler_id']) && !empty($param['handler_id'])) {
            $where .= " and A.handler_id = ".$param['handler_id'];
        }
        if (isset($param['user_name']) && !empty($param['user_name'])) {
            $where .= " and B.user_name = '$param[user_name]'";
        }
        if (isset($param['uid']) && !empty($param['uid'])) {
            $where .= " and A.uid = ".$param['uid'];
        }
        if (isset($param['role_id']) && !empty($param['role_id'])) {
            $where .= " and A.role_id = $param[role_id]";
        }
        if (isset($param['role_name']) && !empty($param['role_name'])) {
            $where .= " and A.role_name = $param[role_name]";
        }
        if (isset($param['add_time_start']) && !empty($param['add_time_start'])) {
            $where .= " and A.add_time >= ".strtotime($param['add_time_start']);
        }
        if (isset($param['add_time_end']) && !empty($param['add_time_end'])) {
            $where .= " and A.add_time < ".strtotime($param['add_time_end']);
        }
        if (isset($param['handler_time_start']) && !empty($param['handler_time_start'])) {
            $where .= " and A.handler_time >= ".strtotime($param['handler_time_start']);
        }
        if (isset($param['handler_time_end']) && !empty($param['handler_time_end'])) {
            $where .= " and A.handler_time < ".strtotime($param['handler_time_end']);
        }
        if (isset($param['status']) && $param['status'] != -1) {
            $where .= " and A.status = ".$param['status'];
        }
        $order = " ORDER BY id desc ";
        $limit = " limit ".($page - 1)*$limit.",$limit ";

        $list = $UserPrivacyInfoModel->query($sql.$where.$order.$limit);

        if($list){
            $list = self::handleVipUserContactInfoList($list);
        }

        $countArr = $UserPrivacyInfoModel->query($sqlCount.$where);
        $count = $countArr[0]['sum'] ? $countArr[0]['sum'] : 0;
        return [$list,$count];
    }

    /**
     * @param array $data
     * @return array|mixed
     */
    private static function handleVipUserContactInfoList($data = array())
    {
        if (empty($data)) return [];
        $admin_list = SysServer::getAdminListCache();
        $product_list = SysServer::getGameProductCache();//产品列表
        $productArr = [];
        foreach ($product_list as $v) {
            $productArr[$v['platform_id'].'_'.$v['product_id']] = $v;
        }
        $platformGameList = SysServer::getPlatformGameInfoCache();

        foreach ($data as &$val) {
            $val['action'] = ListActionServer::checkUserInputInfoListAction($val);
            $val['product_name'] = $productArr[$val['platform_id'].'_'.$val['product_id']]['name'];
            $val['game_name'] = $platformGameList[$val['platform_id'].'_'.$val['game_id']]['game_name'];
            $val['birthday_date'] = !empty($val['birthday']) ? date('Y-m-d', $val['birthday']) : '';
            $val['ascription_name'] = !empty($admin_list[$val['ascription_vip']]) ? $admin_list[$val['ascription_vip']]['realname'] : '';
            $val['add_name'] = $val['add_admin_id'] == -1 ? '用户系统录入' : (!empty($admin_list[$val['add_admin_id']]) ? $admin_list[$val['add_admin_id']]['realname'] : '');
            $val['add_date'] = date('Y-m-d H:i:s', $val['add_time']);
            $val['handler_name'] = !empty($admin_list[$val['handler_id']]) ? $admin_list[$val['handler_id']]['realname'] : '';
            $val['handler_date'] = !empty($val['handler_time']) ? date('Y-m-d H:i:s', $val['handler_time']) : '';
            if ($val['status'] == 1) {
                $val['status'] = '已添加待用户通过';
            }elseif ($val['status'] == 2) {
                $val['status'] = '添加成功';
            }elseif ($val['status'] == 3) {
                $val['status'] = '添加失败';
            }else {
                $val['status'] = '未处理';
            }
        }
        return $data;
    }

    public static function userInputInfoConfig(){

        $search_config = [
            'product'=>['status'=>1],
            'admin'=>[
                'status'=>1,
                'p_data'=>['group_type'=>[QcConfig::USER_GROUP_VIP,'is_active'=>1]],
                'name'=>'ascription_vip',
            ],
        ];

        $admin_list = SysServer::getUserListByAdminInfo(self::$user_data,['is_active'=>1]);

        $status_arr = UserPrivacyInfo::$status_arr;

        return compact('admin_list','status_arr','search_config');
    }

    public static function userInputInfoDetail($id){

        $info = [];

        if($id){
            $model = new UserPrivacyInfo();

            $obj = $model->where('id',$id)->find();

            if($obj){
                $info = $obj->toArray();
                $info['birthday'] = !empty($info['birthday']) ? date('Y-m-d', $info['birthday']) : '';
            }
        }

        $config = [];
        $config['status_arr'] = UserPrivacyInfo::$status_arr;
        $config['platform_list'] = SysServer::getPlatformListByAdminInfo(self::$user_data);

        return ['data'=>$info,'config'=>$config];
    }

    public static function userInputInfoCheckUser($param){

        $code = [
            0=>'success',1=>'vip用户信息不存在',2=>'已经登记过'
        ];

        $where = getDataByField($param,['uid','platform_id']);

        $VipUserInfoModel = new VipUserInfo();

        $vip_user_info = $VipUserInfoModel->where($where)->find();

        if(!$vip_user_info){
            return ['code'=>1,'msg'=>$code[1]];
        }

        $vip_user_info = $vip_user_info->toArray();

        $model = new UserPrivacyInfo();

        $count_privacy = $model->where($where)->count();

        if($count_privacy){
            return ['code'=>2,'msg'=>$code[2]];
        }

        return ['code'=>0,'data'=>$vip_user_info];
    }

    public static function userInputInfoSave($data){
        $code = [
            0=>'成功',1=>'缺少参数',2=>'用户不存在',3=>'已登记',4=>'失败',5=>'数据不存在'
        ];
        $model = new UserPrivacyInfo();

        if (empty($data['platform_id']) || empty($data['uid']) || empty($data['qq']) ) return ['code'=>1,'msg'=>$code[1]];

        $id = getArrVal($data,'id',0);

        if(!empty($data['qq_question_answer'])){
            $data['qq_question_answer'] = addslashes($data['qq_question_answer']);
        }

        if(!empty($data['remarks'])){
            $data['remarks'] = addslashes($data['remarks']);
        }

        if(!empty($data['birthday'])){
            $data['birthday'] = strtotime($data['birthday']);
        }

        $common_field = [
            'birthday',
            'phone_num',
            'qq',
            'qq_question_answer',
            'wechat',
            'remarks',
            'status',
            'birthday',
        ];

        if($id){

            $this_info = $model->where('id',$id)->find();

            if(!$this_info) return ['code'=>5,'msg'=>$code[5]];

            $save_data = getDataByField($data,$common_field);

            if($this_info->status != $save_data['status']){//状态改变更新处理人处理时间
                $save_data['handler_id'] = self::$user_data['id'];
                $save_data['handler_time'] = time();
            }

            $res = $this_info->save($save_data);

        }else{

            $info = $model->where(getDataByField($data,['uid','platform_id']))->find();

            if($info) return ['code'=>3,'msg'=>$code[3]];

            $common_field[] = 'platform_id';
            $common_field[] = 'uid';

            $save_data = getDataByField($data,$common_field);

            $platform_list = SysServer::getPlatformList();

            $platformInfo = getArrVal($platform_list,$data['platform_id'],[]);

            //判断用户信息是否存在
            $userInfo = CommonServer::getPlatformModel('KefuCommonMember',$platformInfo['suffix'])->where('uid',$data['uid'])->find();

            if(!$userInfo) return ['code'=>2,'msg'=>$code[2]];
            //支付信息
            $payInfo = CommonServer::getPlatformModel('KefuPayOrder',$platformInfo['suffix'])
                ->field('gid,server_id,server_name,role_id,role_name')
                ->where('uid',$data['uid'])
                ->order('pay_time desc')
                ->find();

            if($payInfo){
                $save_data['game_id'] = $payInfo->gid;
                $save_data['server_id'] = $payInfo->server_id;
                $save_data['role_id'] = $payInfo->role_id;
                $save_data['server_name'] = addslashes($payInfo->server_name);
                $save_data['role_name'] = addslashes($payInfo->role_name);
            }

            if( (!isset($save_data['game_id']) || !$save_data['game_id']) && $userInfo['reg_gid']){
                $save_data['game_id'] = $userInfo['reg_gid'];
            }
            //游戏信息
            if($save_data['game_id']){
                $PlatformGameInfoModel = new PlatformGameInfo();
                $product_info = $PlatformGameInfoModel
                    ->where(getDataByField($save_data,['game_id','game_id']))
                    ->find();
                if($product_info){
                    $save_data['product_id'] = $product_info->product_id;
                }
            }

            $save_data['add_admin_id'] = self::$user_data['id'];

            $res = $model->create($save_data);
        }
        $title = $id?'修改':'添加';
        if($res){
            return ['code'=>0,'msg'=>$title.$code[0]];
        }else{
            return ['code'=>4,'msg'=>$title.$code[4]];
        }
    }

    public static function userInputInfoDelete($id){

        $code = [0=>'ok',1=>'缺少参数',2=>'操作失败'];

        if (empty($id)) return ['code'=>1,'msg'=>$code[1]];

        $model = new UserPrivacyInfo();

        $res = $model->where('id',$id)->delete();

        if($res){
            return ['code'=>0,'msg'=>$code[0]];
        }else {
            return ['code'=>2,'msg'=>$code[2]];
        }
    }

    /**
     * 流失-列表
     * @param array $param
     * @param int $page
     * @param int $limit
     * @return array
     */
    public static function washUserList(array $param){
        $page = getArrVal($param,'page',1);
        $limit = getArrVal($param,'limit',20);

        if(isset($param['is_excel']) && $param['is_excel']){
            $page = $limit = 0;
        }

        $model = new VipUserInfo();

        $table1 = 'vip_user_info';//主表
        $table2 = 'vip_user_other_info';//关联表

        $where = getDataByField($param,['user_name','role_id','uid'],true,"$table1.");//获取为真等号查询条件
        $limit_day_before = time()-3*3600*24;//限制显示天数
        $string_sql = "($table1.last_login_time <= $limit_day_before OR $table1.last_pay_time <= $limit_day_before)";//登录或充值小于限制时间
        setWhereString($string_sql,$where);//设置查询条件

        self::vipCommonPowerWhere($where,$table1);


        //VIP专员
        if(isset($param['ascription_vip']) && $param['ascription_vip']){
            $where[] = getWhereDataArr($param['ascription_vip'],$table1.'.ascription_vip');
        }

        //主体
        if(isset($param['platform_id']) && $param['platform_id']){
            $where[] = getWhereDataArr($param['platform_id'],$table1.'.platform_id');
        }
        //产品
        if(isset($param['p_p']) && !empty($param['p_p'])){
            $PlatformGameInfo = new PlatformGameInfo();
            $where_p_g_i = [];
            $where_p_g_i[] = ["concat(platform_id,'_',product_id)",'in',splitToArr($param['p_p'])];

            $game_list = $PlatformGameInfo->field('platform_id,game_id',2)->where(setWhereSql($where_p_g_i,''))->select()->toArray();

            if($game_list){
                $this_info = [];
                foreach ($game_list as $item){
                    $this_info[] = $item['platform_id'].'_'.$item['game_id'];
                }
                $where[] = ['p_l_g','in',$this_info];
            }else{
                $where[] = ['p_l_g','=',0];
            }
        }
        //游戏
        if(isset($param['p_g']) && $param['p_g'] ){
            $where[] = getWhereDataArr($param['p_g'],'p_l_g');
        }
        //30天累充
        if(isset($param['thirty_day_pay_start']) && $param['thirty_day_pay_start']){
            $where[] = [$table1.'.thirty_day_pay','>=',$param['thirty_day_pay_start']];
        }
        if(isset($param['thirty_day_pay_end']) && $param['thirty_day_pay_end']){
            $where[] = [$table1.'.thirty_day_pay','<=',$param['thirty_day_pay_end']];
        }
        //总充值金额
        if(isset($param['total_pay_start']) && $param['total_pay_start']){
            $where[] = [$table1.'.total_pay','>=',$param['total_pay_start']];
        }
        if(isset($param['total_pay_end']) && $param['total_pay_end']){
            $where[] = [$table1.'.total_pay','<=',$param['total_pay_end']];
        }
        //是否录单
        if ($param['is_record'] == 1) {
            $where[] =[$table1.'.last_record_time','>',0];
        }elseif ($param['is_record'] == 2) {
            $where[] =[$table1.'.last_record_time','=',0];
        }
        //最后登录时间
        if(!empty($param['last_login_time'])){
            $this_time_arr = explode(' - ',$param['last_login_time']);
            $where[] = [$table1.'.last_login_time','>=', strtotime($this_time_arr[0])];
            $where[] = [$table1.'.last_login_time','<', strtotime($this_time_arr[1])+3600*24];
        }
        if(!empty($param['last_pay_time'])){
            $this_time_arr = explode(' - ',$param['last_pay_time']);
            $where[] = [$table1.'.last_pay_time','>=', strtotime($this_time_arr[0])];
            $where[] = [$table1.'.last_pay_time','<', strtotime($this_time_arr[1])+3600*24];
        }
        //未登录天数
        $days = [];
        if(!empty($param['last_login_time_num_start'])){
            $this_time = time()-(($param['last_login_time_num_start'])*3600*24);
            $where[] = [$table1.'.last_login_time','<=', $this_time];
        }
        if(!empty($param['last_login_time_num_end'])){
            $this_time = time()-(($param['last_login_time_num_end']+1)*3600*24);
            $where[] = [$table1.'.last_login_time','>=', $this_time];
        }

        //未充值天数
        if(!empty($param['last_pay_time_num_start'])){
            $this_time = time()-(($param['last_pay_time_num_start'])*3600*24);
            $where[] = [$table1.'.last_pay_time','<=', $this_time];
        }
        if(!empty($param['last_pay_time_num_end'])){
            $this_time = time()-(($param['last_pay_time_num_end']+1)*3600*24);
            $where[] = [$table1.'.last_pay_time','>=', $this_time];
        }
        //联系方式
        if(!empty($param['contact_type'])){

            $contact_type = explode(',',$param['contact_type']);

            if(in_array(1,$contact_type)){
                $where[] = [$table1.'.mobile','!=', ''];
            }
            if(in_array(2,$contact_type)){

                $where[] = [$table2.'.mobile','>', 0];
            }
            if(in_array(3,$contact_type)){

                $where[] = [$table2.'.wechat','>', 0];
            }
            if(in_array(4,$contact_type)){

                $where[] = [$table2.'.qq_number','>', 0];
            }
        }
        //统计总数
        $sql = "SELECT count($table1.id) as count From $table1";
        $sql .=" LEFT JOIN $table2 ON $table1.uid = $table2.uid AND $table1.platform_id = $table2.platform_id";
        $sql .=setWhereSql($where);

        $count_data = $model->query($sql);
        $count = $count_data[0]['count'];

        if(!$count) return [[],0];
        //查询数据
        $field = "
            $table1.id
            ,$table1.ascription_vip
            ,$table1.platform_id
            ,$table1.uid
            ,$table1.last_pay_time
            ,$table1.last_login_time
            ,$table1.user_name
            ,$table1.total_pay
            ,$table1.thirty_day_pay
            ,$table1.role_name
            ,$table1.role_id
            ,$table1.p_l_g
            ,$table1.server_id
            ,$table1.mobile
            ,$table2.wechat
            ,$table2.mobile as mobile1
            ,$table2.qq_number as qq
        ";

        $sql = "SELECT $field From $table1";
        $sql .=" LEFT JOIN $table2 ON $table1.uid = $table2.uid AND $table1.platform_id = $table2.platform_id";
        $sql .=setWhereSql($where);
        $sql.=" ORDER BY $table1.last_login_time DESC,$table1.last_pay_time DESC,$table1.id DESC";
        if($limit && $page){
            $sql.=' LIMIT '.($page - 1) * $limit.','.$limit;
        }

        $list = $model->query($sql);

        if($list){

            $platform_list = SysServer::getPlatformList();
            $admin_list = SysServer::getAdminListCache();

            $p_l_g_arr = [];

            foreach ($list as $k => &$v) {

                $v['action'] = ListActionServer::checkWashUserListAction($v);
                if($v['ascription_vip']){
                    $v['ascription_vip_str'] = isset($admin_list[$v['ascription_vip']])?$admin_list[$v['ascription_vip']]['realname']:'未知';//专员名称兑换
                }else{
                    $v['ascription_vip_str'] = '未分配';
                }

                //平台名称兑换
                $this_platform_info = getArrVal($platform_list,$v['platform_id'],[]);

                $p_l_g_arr[] = $v['p_l_g'];

                if($this_platform_info){
                    $v['platform_id_str'] = $this_platform_info['name'];
                }else{
                    $v['platform_id_str'] = '';
                }
                if($v['last_pay_time']){
                    $v['last_pay_time_str'] = date('Y/m/d H:i',$v['last_pay_time']);//支付时间转换
                    $v['last_pay_time_num'] = timeDifferDay($v['last_pay_time'],time());//支付时间与当前相差天数计算
                }
                if($v['last_login_time']){
                    $v['last_login_time_str'] = date('Y/m/d H:i',$v['last_login_time']);//登录时间转换
                    $v['last_login_time_num'] = timeDifferDay($v['last_login_time'],time());//登录时间与当前相差天数计算
                }
                $v['game_name'] = '';
            }
            if($p_l_g_arr){//游戏信息查询
                $PlatformGameInfo = new PlatformGameInfo();
                $where = [];
                setWhereString("CONCAT(platform_id,'_',game_id) in ('".implode('\',\'',$p_l_g_arr)."')",$where);
                $game_list = $PlatformGameInfo->where(setWhereSql($where,''))->select()->toArray();
                $new_game_list = [];

                if($game_list){
                    foreach ($game_list as $item){
                        $new_game_list[$item["platform_id"]."_".$item['game_id']] = $item['game_name'];
                    }
                    if($new_game_list){
                        foreach ($list as &$v){
                            $v['game_name'] = getArrVal($new_game_list,$v['p_l_g']);
                        }
                    }
                }
            }
        }

        return [$list,$count];
    }

    public static function vipCommonPowerWhere(&$where,$table1=''){
        $admin_info = self::$user_data;
        if($table1){
            $pre = $table1.'.';
        }else{
            $pre = '';
        }
        if($admin_info['is_admin'] == 0){//非管理
            if($admin_info['platform_id']){//分配平台限制
                $where[] =[$pre.'platform_id','in',$admin_info['platform_id']];
            }else{
                $where[] =[$pre.'platform_id','=',0];
            }

            if($admin_info['position_grade'] ==  QcConfig::POSITION_GRADE_NORMAL){//专员只看自己分配的
                $where[] = [$pre.'ascription_vip','=',$admin_info['id']];
            }elseif($admin_info['position_grade'] ==  QcConfig::POSITION_GRADE_LEADER){//组长只看自己组的成员
                $ids = SysServer::getAdminListByGroupIds($admin_info['group_id'],2);
                if(!$ids){
                    $ids = [0];
                }
                $where[] = [$pre.'ascription_vip','in',$ids];
            }
        }
    }

    public static function washUserListConfig(){

        $search_config = [
            'product'=>['status'=>1],
            'game'=>['status'=>1],
            'admin'=>[
                'status'=>1,
                'p_data'=>['group_type'=>[QcConfig::USER_GROUP_VIP]],
                'radio'=>1,
                'name'=>'ascription_vip',
            ],
        ];

        return compact('search_config');
    }

    public static function decryptMobile($param){

        $code = [0=>'success',1=>'获取用户数据失败',2=>'没有手机号',3=>'数据错误'];

        $model = new VipUserInfo();

        $where = getDataByField($param,['platform_id','uid'],true);//获取条件

        self::vipCommonPowerWhere($where);//获取权限条件

        $info = $model->where(setWhereSql($where,''))->find();//查询数据

        if(!$info) return returnData(1,$code[1]);

        if(!$info->mobile) return returnData(2,$code[2]);

        $mobile = ApiUserInfoSecurity::decrypt($info->mobile);//解密手机

        if(!isphone($mobile)) return returnData(3,$code[3]);

        return returnData(0,$code[0],compact('mobile'));
    }
    public static function vipUserInfoList($params){

        $result = [
            'data' => [],
            'count' => 0,
        ];

        $page = getArrVal($params,'page',1);
        $limit = getArrVal($params,'limit',20);
        $is_excel = getArrVal($params,'is_excel',0);

        if($is_excel){
            $page = $limit = 0;
        }

        $adminUserInfo = self::$user_data;

        $where = getDataByField($params,['user_name','uid','role_id'],true);
        $where['_string'] = '1=1';

        $order = 'last_distribute_time desc';
        //平台id、产品id
        $p_l_g = false;
        //处理区服
        $serverIds = [];
        if ((int)$params['server_id_max'] >= (int)$params['server_id_min'] && (int)$params['server_id_max'] >0) {
            $i = (int)$params['server_id_min'];
            for ($i; $i<= (int)$params['server_id_max']; $i++) {
                $serverIds[] = trim($params['server_prefix'].$i.$params['server_suffix'], '');
            }
        }

        if($adminUserInfo['is_admin'] == 0){
            if($adminUserInfo['platform_id']){
                $where[] =['platform_id','in',$adminUserInfo['platform_id']];
            }else{
                $where[] =['platform_id','=',0];
            }
        }

        if(isset($params['platform_id']) && $params['platform_id'] ){
            $where[] = getWhereDataArr($params['platform_id'],'platform_id');
        }
        if(!empty($params['p_g'])){
            $where[] = getWhereDataArr($params['p_g'],'p_l_g');
        }elseif(!empty($params['p_p'])){
            $PlatformGameInfoModel = new PlatformGameInfo();
            $platform_game_info_list = $PlatformGameInfoModel
                ->field('platform_id,game_id')
                ->where("concat(platform_id,'_',product_id) in ('".str_replace(',',"','",$params['p_p'])."')")
                ->select()->toArray();

            if($platform_game_info_list){
                $p_g = [];
                foreach ($platform_game_info_list as $item){
                    $p_g[] = $item['platform_id'].'_'.$item['game_id'];
                }
                $where[] = getWhereDataArr($p_g,'p_l_g');
            }else{
                $where[] = ['p_l_g','=',-1];
            }
        }
        //高管
        if ($adminUserInfo['position_grade'] > 3) {

        }elseif (in_array($adminUserInfo['position_grade'],[2,3])) {

            $ids = SysServer::getAdminListByGroupIds($adminUserInfo['group_id']);

            if(!$ids){
                $ids = [0];
            }
            $where[] = ['ascription_vip','in',$ids];

        }elseif ($adminUserInfo['position_grade'] == 1) {
            $where[] = ['ascription_vip','=',$adminUserInfo['id']];
        }else{
            return $result;
        }

        if (!empty($serverIds)) {
            $where[] = ['server_id','in',$serverIds];
        }

        if(isset($params['ascription_vip']) && $params['ascription_vip']){
            $where[] = ['ascription_vip','=',$params['ascription_vip']];
        }

        //充值状态
        if ($params['recharge_static'] == 1) {
            $where[] =['thirty_day_pay','>=',1000];
        }elseif ($params['recharge_static'] == 2) {
            $where[] =['thirty_day_pay','>=',1];
            $where[] =['thirty_day_pay','<',1000];
        }elseif ($params['recharge_static'] == 3) {
            $where[] =['thirty_day_pay','=',0];
        }
        //登录状态
        $dateTime = strtotime(date('Y-m-d', time()));
        if ($params['login_static'] == 1) {
            $where[] =['last_login_time','>=',$dateTime - 3*86400];
        }elseif ($params['login_static'] == 2) {
            $where[] =['last_login_time','>=',$dateTime - 6*86400];
            $where[] =['last_login_time','<',$dateTime - 3*86400];
        }elseif ($params['login_static'] == 3) {
            $where[] =['last_login_time','<',$dateTime - 6*86400];
        }

        if (!empty($params['single_hign_pay_min'])) {
            $where[] =['single_hign_pay','>=',$params['single_hign_pay_min']];
        }

        if (!empty($params['single_hign_pay_max'])) {
            $where[] =['single_hign_pay','<=',$params['single_hign_pay_max']];
        }

        if (!empty($params['total_pay_min'])) {
            $where[] =['total_pay','>=',$params['total_pay_min']];
        }
        if (!empty($params['total_pay_max'])) {
            $where[] =['total_pay','<=',$params['total_pay_max']];
        }
        if (!empty($params['thirty_day_pay_min'])) {
            $where[] =['thirty_day_pay','>=',$params['thirty_day_pay_min']];
        }
        if (!empty($params['thirty_day_pay_max'])) {
            $where[] =['thirty_day_pay','<=',$params['thirty_day_pay_max']];
        }
        if (!empty($params['day_hign_pay_min'])) {
            $where[] =['day_hign_pay','>=',$params['day_hign_pay_min']];
        }
        if (!empty($params['day_hign_pay_max'])) {
            $where[] =['day_hign_pay','<=',$params['day_hign_pay_max']];
        }
        if (!empty($params['today_pay_min']) || !empty($params['today_pay_max'])) {
            $where[] =['last_pay_time','>=',strtotime(date("Y-m-d", strtotime("-1 day")))];
            if (!empty($params['today_pay_min'])) {
                $where[] =['last_day_pay','>=',$params['today_pay_min']];
            }
            if (!empty($params['today_pay_max'])) {
                $where[] =['last_day_pay','<=',$params['today_pay_max']];
            }
        }

        if ($params['vip_grade'] == 1) {
            $where['_string'] .=' AND ((total_pay >=  500 and  total_pay < 4999 and day_hign_pay >= 500 and day_hign_pay <1000))';
        }elseif ($params['vip_grade'] == 2) {
            $where['_string'] .=" AND ((total_pay >=  5000 and  total_pay <= 9999) or (day_hign_pay >= 1000 and day_hign_pay <2000)) ";
        }elseif ($params['vip_grade'] == 3) {
            $where['_string'] .=" AND ((total_pay >=  10000 and  total_pay <= 20000) or (day_hign_pay >= 2000 and day_hign_pay <3000)) ";
        }elseif ($params['vip_grade'] == 4) {
            $where['_string'] .=" AND ((total_pay >=  20001 and  total_pay <= 49999) or (day_hign_pay >= 3000 and day_hign_pay <5000)) ";
        }elseif ($params['vip_grade'] == 5) {
            $where['_string'] .=" AND ((total_pay >=  50000 or day_hign_pay >= 5000)) ";
        }

        if ($params['is_record'] == 1) {
            $where[] =['last_record_time','>',0];
        }elseif ($params['is_record'] == 2) {
            $where[] =['last_record_time','=',0];
        }

        if ($params['is_remark'] == 1) {
            $where[] =['remark_time','>',0];
        }elseif ($params['is_remark'] == 2) {
            $where[] =['remark_time','=',0];
        }

        //录入用户其它资料
        if (!empty($params['is_record_data'])) {
            $where[] =['is_record_static','=',$params['is_record_data']];
        }

        //级别可以查看的数据
        if (in_array($adminUserInfo['position_grade'], [3,4,5,6])){
            //分配状态
            if ($params['distribute_static'] == 1) { //未分配
                $where[] =['ascription_vip','=',0];
            }elseif ($params['distribute_static'] == 2) {//已分配
                $where[] =['ascription_vip','>',0];
            }
        }else {
            //其它职位的选择未分配的直接返回没有数据
            if ($params['distribute_static'] == 1) return $result;
            $where[] =['ascription_vip','>',0];
        }

        if(!empty($params['sort_field'])){
            $order = "{$params['sort_field']} {$params['sort']}";
        }

        // 筛选时间
        if(!empty($params['last_distribute_time'])){
            $this_time_arr = explode(' - ',$params['last_distribute_time']);
            $where[] = ['last_distribute_time','>', strtotime($this_time_arr[0])];
            $where[] = ['last_distribute_time','<', strtotime($this_time_arr[1])+3600*24];
        }
        if(!empty($params['first_distribute_time'])){
            $this_time_arr = explode(' - ',$params['first_distribute_time']);
            $where[] = ['first_distribute_time','>', strtotime($this_time_arr[0])];
            $where[] = ['first_distribute_time','<', strtotime($this_time_arr[1])+3600*24];
        }
        if(!empty($params['last_login_time'])){
            $this_time_arr = explode(' - ',$params['last_login_time']);
            $where[] = ['last_login_time','>=', strtotime($this_time_arr[0])];
            $where[] = ['last_login_time','<', strtotime($this_time_arr[1])+3600*24];
        }
        if(!empty($params['last_pay_time'])){
            $this_time_arr = explode(' - ',$params['last_pay_time']);
            $where[] = ['last_pay_time','>=', strtotime($this_time_arr[0])];
            $where[] = ['last_pay_time','<', strtotime($this_time_arr[1])+3600*24];
        }

        if(!empty($params['last_record_time'])){
            $this_time_arr = explode(' - ',$params['last_record_time']);
            $where[] = ['last_record_time','>=', strtotime($this_time_arr[0])];
            $where[] = ['last_record_time','<', strtotime($this_time_arr[1])+3600*24];
        }

        $model = new VipUserInfo();
        $total = $model->where(setWhereSql($where,''))->count();

        if(!$total){
            return $result;
        }
        $result['count'] = (int)$total;

        $list = $model->where(setWhereSql($where,''));

        if($page && $limit){
            $list = $list->page($page,$limit);
        }
        $list = $list->order($order);

        $list = $list->select()->toArray();

        if($list){
            $result['data'] = self::userVipInfoDo($list,$is_excel);
        }

        return $result;
    }

    public static function userVipInfoDo($list,$is_excel=0){
        $tmp = [];
        $new_list = [];

        $adminUserList = SysServer::getAdminListCache();
        $platformGameList = SysServer::getPlatformGameInfoCache();
        foreach ($list as $v) {
            $tmp[$v['platform_id'].'_'.$v['uid']] = $v;
        }
        $otherData = StatisticServer::getUserOtherDayPayData(array_keys($tmp));
        unset($list);
        $thisMonth = timeCondition('month');
        //稍组装
        foreach ($tmp as $k=>$v) {
            $v['action'] = ListActionServer::checkVipUserInfoListAction($v);
            $tmpArr = $v;

            $tmpArr['remark_month_pay'] = 0;

            if (!empty($otherData[$k]['today_pay']) && $otherData[$k]['today_pay'] >0) {
                $tmpArr['today_pay'] = $otherData[$k]['today_pay'];
            }else {
                $tmpArr['today_pay'] = 0;
            }
            if (!empty($otherData[$k]['last_thirty_day_pay_count']) && $otherData[$k]['last_thirty_day_pay_count'] >0) {
                $tmpArr['last_thirty_day_pay_count'] = $otherData[$k]['last_thirty_day_pay_count'];
            }else {
                $tmpArr['last_thirty_day_pay_count'] = 0;
            }

            $tmpArr['last_thirty_day_highest_pay_game'] = !empty($otherData[$k]['last_thirty_day_highest_pay_game']) ? $otherData[$k]['last_thirty_day_highest_pay_game'] : 0;
            if (!$is_excel) {
                $tmpArr['is_today_pay'] = StatisticServer::getUserTodayOrderSum($v['platform_id'],$v['uid']);
            }

            $tmpArr['vip_grade'] = self::getVipGrade($v['total_pay'], $v['day_hign_pay']);
            $this_admin_info = getArrVal($adminUserList,$v['ascription_vip'],[]);
            if($this_admin_info){
                $tmpArr['ascription_vip_name'] = $this_admin_info['realname'];
            }else{
                $tmpArr['ascription_vip_name'] = '';
            }

            $tmpArr['before_remark_pay'] = floatval($v['total_pay'])-floatval($v['remark_pay']);

            if($v['remark_time'] > 0){
                if($v['remark_time'] > $thisMonth['starttime']){
                    $tmpArr['remark_month_pay'] = $v['remark_pay'];
                }else{
                    $tmpArr['remark_month_pay'] = $v['month_pay'];
                }
            }

            $tmpArr['recharge_static'] = self::getRechargeStatic($tmpArr['last_thirty_day_pay_count']);
            $tmpArr['login_static'] = self::getLoginStatic($v['last_login_time']);
            $this_p_g = $v['platform_id'].'_'.$v['last_pay_game_id'];
            $this_p_g_info = getArrVal($platformGameList,$this_p_g,[]);
            if($this_p_g_info){
                $tmpArr['product_name'] = $this_p_g_info['product_name'] ?: '';
                $tmpArr['game_name'] = $this_p_g_info['game_name'] ?: '';
            }else{
                $tmpArr['product_name'] = '';
                $tmpArr['game_name'] = '';
            }
            $this_p_g = $v['platform_id'].'_'.$tmpArr['last_thirty_day_highest_pay_game'];
            $this_p_g_info = getArrVal($platformGameList,$this_p_g,[]);
            if($this_p_g_info){
                $tmpArr['last_thirty_day_highest_pay_game'] = $this_p_g_info['product_name'] ?: '';
            }else{
                $tmpArr['last_thirty_day_highest_pay_game'] = '';
            }
            $tmpArr['vip_date'] = !empty($v['vip_time']) ? date('Y-m-d H:i:s', $v['vip_time']) : '';
            $tmpArr['last_pay_time'] = !empty($v['last_pay_time']) ? date('Y-m-d H:i:s', $v['last_pay_time']) : '';
            $tmpArr['last_login_time'] = !empty($v['last_login_time']) ? date('Y-m-d H:i:s', $v['last_login_time']) : '';
            $tmpArr['last_distribute_time'] = !empty($v['last_distribute_time']) ? date('Y-m-d H:i:s', $v['last_distribute_time']) : '';
            $tmpArr['last_record_time'] = !empty($v['last_record_time']) ? date('Y-m-d H:i:s', $v['last_record_time']) : '';

            $new_list[] = $tmpArr;
        }

        return $new_list;
    }

    public static function vipUserInfoListConfig(){

        $search_config = [
            'product'=>[
                'status'=>1,
                'name'=>'p_p',
            ],
            'game'=>[
                'status'=>1,
                'name'=>'p_g',
            ],
            'admin'=>[
                'status'=>1,
                'p_data'=>['group_type'=>[QcConfig::USER_GROUP_VIP]],
                'name'=>'ascription_vip',
                'radio'=>true,
            ],
        ];

        $admin_list = SysServer::getUserListByAdminInfo(self::$user_data,['group_type'=>[QcConfig::USER_GROUP_VIP,'is_active'=>1]]);

        return compact('search_config','admin_list');
    }

    public static function vipDistribution($params){
        $model = new VipUserInfo();
        $tmpTime = time();
        $sql = "UPDATE vip_user_info SET ascription_vip = {$params['admin_id']},last_distribute_time={$tmpTime},update_time={$tmpTime},first_distribute_time = CASE WHEN first_distribute_time = 0 THEN {$tmpTime} ELSE first_distribute_time END WHERE id in (".implode(',',$params['idStr']).")";

        return $model->execute($sql);
    }

    public static function vipDistributionDelete($id){

        $code = [
            0=>'success',1=>'id为空',2=>'操作失败'
        ];

        if (empty($id)) {
            return ['code'=>1,'msg'=>$code[1]];
        }

        if(is_array($id)){
            $this_info = $id;
        }else{
            $this_info = explode(',',$id);
        }

        if(count($this_info) == 1){
            $where['id'] = $this_info[0];
        }else{
            $where[] = ['id','in',$this_info];
        }
        $model = new VipUserInfo();
        $tmpTime = time();
        $data = [
            'ascription_vip' => 0,
            'last_distribute_time' => $tmpTime,
            'update_time' => $tmpTime
        ];

        $res = $model->where(setWhereSql($where,''))->update($data);

        if($res){
            return ['code'=>0,'msg'=>$code[0].$res];
        }else{
            return ['code'=>2,'msg'=>$code[2]];
        }
    }

    public static function userOtherInfoList($param){

        $page = getArrVal($param,'page',1);
        $limit = getArrVal($param,'limit',20);

        $model = new VipUserInfo();

        $table1 = 'vip_user_other_info';
        $table2 = 'vip_user_info';

        $where = [];

        if(self::$user_data['is_admin'] == 0){
            if(self::$user_data['platform_id']){
                $where[] =[$table1.'.platform_id','in',self::$user_data['platform_id']];
            }else{
                $where[] =[$table1.'.platform_id','=',0];
            }
        }

        if(isset($param['ascription_vip']) && $param['ascription_vip']){
            $where[] = getWhereDataArr($param['ascription_vip'],$table2.'.ascription_vip');
        }

        if(isset($param['platform_id']) && $param['platform_id']){
            $where[] = getWhereDataArr($param['platform_id'],$table2.'.platform_id');
        }

        if(isset($param['p_p']) && !empty($param['p_p'])){
            $p_p_info = explode('_',$param['p_p']);
            if(count($p_p_info) == 2){
                $where[$table1.'.platform_id'] = $p_p_info[0];
                $where[$table1.'.product_id'] = $p_p_info[1];
            }
        }

        if(isset($param['group_id']) && $param['group_id']){
            $kf_ids = SysServer::getAdminListByGroupIds($param['group_id']);
            if($kf_ids){
                $where[] = getWhereDataArr($kf_ids,$table2.'.ascription_vip');
            }else{
                $where[] =[$table2.'.ascription_vip','=',-1];
            }
        }

        if(isset($param['birthday_start']) && $param['birthday_start'] ){
            $where[] = ["CONCAT($table1.birth_month,IF($table1.birth_day<10,CONCAT(0,$table1.birth_day), $table1.birth_day))",'>=',intval(date('md',strtotime($param['birthday_start'])))];
        }
        if(isset($param['birthday_end']) && $param['birthday_end']){
            $where[] = ["CONCAT($table1.birth_month,IF($table1.birth_day<10,CONCAT(0,$table1.birth_day), $table1.birth_day))",'<=',intval(date('md',strtotime($param['birthday_end'])))];
        }

        if(isset($param['thirty_day_pay_start']) && $param['thirty_day_pay_start']){
            $where[] = [$table2.'.thirty_day_pay','>=',$param['thirty_day_pay_start']];
        }
        if(isset($param['thirty_day_pay_end']) && $param['thirty_day_pay_end']){
            $where[] = [$table2.'.thirty_day_pay','<=',$param['thirty_day_pay_end']];
        }

        if(isset($param['total_pay_start']) && $param['total_pay_start']){
            $where[] = [$table2.'.total_pay','>=',$param['total_pay_start']];
        }

        if(isset($param['total_pay_end']) && $param['total_pay_end']){
            $where[] = [$table2.'.total_pay','<=',$param['total_pay_end']];
        }

        $sql = "SELECT count($table1.id) as count From $table1 INNER JOIN $table2 ON $table1.uid = $table2.uid AND $table1.platform_id = $table2.platform_id".setWhereSql($where);

        $count_data = $model->query($sql);
        $count = $count_data[0]['count'];

        if(!$count) return [[],0];

        $field = "
            $table2.id
            ,$table2.ascription_vip
            ,$table2.platform_id
            ,$table2.uid
            ,$table2.last_pay_time
            ,$table2.user_name
            ,$table2.total_pay
            ,$table2.thirty_day_pay
            ,$table2.role_name
            ,$table2.role_id
            ,$table2.server_id
            ,$table1.platform_id
            ,$table1.product_id
            ,$table1.birth_month
            ,$table1.birth_day
            ,$table1.qq_number as qq            
        ";

        $sql = "SELECT $field From $table1 INNER JOIN $table2 ON $table1.uid = $table2.uid AND $table1.platform_id = $table2.platform_id".setWhereSql($where);
        $sql.=" ORDER BY $table2.total_pay DESC,$table1.birth_month ASC,$table1.birth_day ASC,$table2.id DESC";
        if($limit && $page){
            $sql.=' LIMIT '.($page - 1) * $limit.','.$limit;
        }

        $list = $model->query($sql);

        if($list){

            $platform_list = SysServer::getPlatformList();
            $admin_list = SysServer::getAdminListCache();

            $game_list = SysServer::getGameProductCache();//游戏列表
            $new_game_list = arrReSet($game_list,'id_str');

            foreach ($list as $k => &$v) {

                $v['action'] = ListActionServer::checkVipUserOtherInfoAction($v);

                $v['ascription_vip_str'] = isset($admin_list[$v['ascription_vip']])?$admin_list[$v['ascription_vip']]['realname']:'未知';

                $this_platform_info = getArrVal($platform_list,$v['platform_id'],[]);

                if($this_platform_info){
                    $v['platform_id_str'] = $this_platform_info['name'];
                }else{
                    $v['platform_id_str'] = '';
                }
                if($v['last_pay_time'] >= time()-86400*3){
                    $v['charge_status'] = '活跃';
                } else if ($v['last_pay_time'] >= time()-86400*7){
                    $v['charge_status'] = '沉默';
                } else if ($v['last_pay_time'] >= time()-86400*15){
                    $v['charge_status'] = '预流失';
                } else if ($v['last_pay_time'] < time()-86400*15){
                    $v['charge_status'] = '流失';
                }
                if($v['birth_month'] && $v['birth_day']){
                    $v['birthday_str'] = "$v[birth_month]月$v[birth_day]日";
                }else{
                    $v['birthday_str'] = '';
                }
                $this_pu_str = $v['platform_id'].'_'.$v['uid'];

                $this_p_p = $v['platform_id'].'_'.$v['product_id'];

                $v['p_u'] = $this_pu_str;
                $v['p_p'] = $this_p_p;

                $this_product_info = getArrVal($new_game_list,$this_p_p,[]);
                $v['game_name'] = '';
                if($this_product_info){
                    $v['game_name'] = $this_product_info['name'];
                }
            }
        }

        return [$list,$count];
    }

    public static function userOtherInfoListConfig(){
        $this_month_arr = timeCondition('month');
        $this_month_arr['starttime_str'] = date('Y-m-d',$this_month_arr['starttime']);
        $this_month_arr['endtime_str'] = date('Y-m-d',$this_month_arr['endtime']);

        $group_list = SysServer::getGroupListByAdminInfo(QcConfig::USER_GROUP_VIP,self::$user_data);

        $search_config = [
            'product'=>[
                'status'=>1
            ],
            'admin'=>[
                'status'=>1,
                'p_data'=>['group_type'=>[QcConfig::USER_GROUP_VIP]],
                'name'=>'ascription_vip',
            ],
        ];

        return compact('search_config','group_list','this_month_arr');
    }

    public static function vipUserOtherInfoDetail($platform_id,$uid){

        $VipUserInfoModel = new VipUserInfo();

        $where = compact('platform_id','uid');

        $vip_user_info = $VipUserInfoModel->where($where)->find();

        if(!$vip_user_info){
            return [];
        }

        $vip_user_info = $vip_user_info->toArray();

        $detail = getDataByField($vip_user_info,['user_name','uid','platform_id']);

        $detail['id'] = 0;

        $model = new VipUserOtherInfo();

        $info = $model->where($where)->find();

        if($info){
            $detail = array_merge($detail,$info->toArray());
        }

        $config = [];

        $config['game_list'] = SysServer::getGameProductCache($platform_id,1);
        $config['platform_list'] = SysServer::getPlatformList();


        return compact('detail','config');
    }

    public static function vipUserOtherInfoSave($param){

        $code = [
            0=>'success',1=>'用户信息不存在',2=>'操作失败'
        ];

        $id = getArrVal($param,'id',0);

        $VipUserInfoModel = new VipUserInfo();

        $VipUserInfo = $VipUserInfoModel->where(getDataByField($param,['platform_id','uid']))->find();

        if(!$VipUserInfo){
            return ['code'=>1,'msg'=>$code[1]];
        }

        $model = new VipUserOtherInfo();

        $vipGame = $model->where('id',$id)->find();

        if (!empty($vipGame)) {
            $param['edit_admin_id'] = self::$user_data['id'];

            $res = $vipGame->save($param);
        }else {
            $res = $model->create($param);

            if ($res) {
                $VipUserInfo->save(['is_record_static'=>1]);
            }
        }

        if($res){
            return ['code'=>0,'msg'=>$code[0]];
        }else{
            return ['code'=>2,'msg'=>$code[2]];
        }
    }

    public static function getVipUserWelfareDetail(int $id){

        $model = new VipUserWelfare();

        $where = compact('id');

        $info = $model->where($where)->find();

        if(!$info){
            return [];
        }

        $res = $info->toArray();

        $user_info = self::getVipUserInfoByPidUid($res['platform_id'],$res['uid']);

        if($user_info){
            $res['user_name'] = $user_info['user_name'];
        }

        return $res;
    }

    public static function userWelfareSave($p_data){
        $code = [
            0=>'success',1=>'操作失败',2=>'信息不存在'
        ];

        $id = getArrVal($p_data,'id',0);

        $model = new VipUserWelfare();

        $common_field = [
            'platform_id'
            ,'uid'
            ,'p_u'
            ,'qq'
            ,'goods'
            ,'address'
            ,'phone'
            ,'is_send'
            ,'desc'
            ,'content'
            ,'imgs'
            ,'send_time'
        ];

        if(isset($p_data['platform_id']) && $p_data['platform_id'] && isset($p_data['uid']) && $p_data['uid']){
            $p_data['p_u'] = $p_data['platform_id'].'_'.$p_data['uid'];
        }

        if(isset($p_data['content']) && $p_data['content']){
            $img_info = getHtmlImgSrc($p_data['content']);
            if(isset($img_info[1])){
                $p_data['imgs'] = json_encode($img_info[1]);
            }
        }

        if($id){

            $this_info = $model->where(['id'=>$id])->find();

            if(!$this_info){
                return ['code'=>2,'msg'=>$code[2]];
            }

            //筛选要更新数据
            $save_data = getDataByField($p_data,$common_field);

            $save_data['admin_id'] = self::$user_data['id'];

            $res = $this_info->save($save_data);

            if(!$res) return ['code'=>1,'msg'=>$code[1]];
        }else{

            $add_data = getDataByField($p_data,$common_field);

            $add_data['admin_id'] = self::$user_data['id'];

            $res = $model->create($add_data);

            if(!$res) return ['code'=>1,'msg'=>$code[1]];

        }

        return ['code'=>0,'msg'=>$code[0]];
    }

    public static function userWelfareDetailConfig(){

        $platform_list = SysServer::getPlatformListByAdminInfo(self::$user_data);

        $is_send_arr = VipUserWelfare::$is_send_arr;

        return compact('platform_list','is_send_arr');
    }

    public static function getVipUserInfoByPidUid($platform_id,$uid){

        $model = new VipUserInfo();

        $where = compact('platform_id','uid');

        $info = $model->where($where)->find();

        if($info){
            $info = $info->toArray();
        }

        return $info;
    }

    public static function userWelfareList(array $param)
    {
        $page = getArrVal($param,'page',1);
        $limit = getArrVal($param,'limit',20);

        $model = new VipUserWelfare();

        $VipUserInfo = new VipUserInfo();

        $where = getDataByField($param, ['admin_id', 'platform_id'],true);

        if (self::$user_data['is_admin'] == 0) {
            $where[] = getWhereDataArr(self::$user_data['platform_id'], 'platform_id');
        }

        if (isset($param['user_name']) && $param['user_name']) {
            $where_recharge_user = [];

            $where_recharge_user['user_name'] = $param['user_name'];

            $recharge_user = $VipUserInfo->field('uid,platform_id')->where($where_recharge_user)->select()->toArray();

            if ($recharge_user) {
                $this_pu_arr = [];
                foreach ($recharge_user as $v) {
                    $this_pu_arr[] = $v['platform_id'] . '_' . $v['uid'];
                }
                $where[] = ['p_u', 'in', $this_pu_arr];
            } else {
                $where['uid'] = 0;
            }
        }

        if (isset($param['p_u']) && $param['p_u']) {
            $where['p_u'] = $param['p_u'];
        }

        if (isset($param['is_send']) && $param['is_send'] != -1) {
            $where['is_send'] = $param['is_send'];
        }

        if (!empty($param['group_id'])) {
            $kf_ids = SysServer::getAdminListByGroupIds($param['group_id'],2);

            if ($kf_ids) {
                $where[] = ['admin_id', 'in', $kf_ids];
            } else {
                $where[] = ['admin_id', '=', -1];
            }
        }

        if (isset($param['add_time_start'])) {
            if ($param['add_time_start']) {
                $where[] = ['add_time', '>=', strtotime($param['add_time_start'])];
            }
        }

        if (isset($param['add_time_end'])) {
            if ($param['add_time_end']) {
                $where[] = ['add_time', '<', strtotime($param['add_time_end'])];
            }
        }

        $count = $model->where(setWhereSql($where,''))->count();

        if (!$count) return [[], 0];

        $list = $model->where(setWhereSql($where,''));

        if ($limit && $page) {
            $list = $list->page($page,$limit);
        }

        $list = $list->select()->toArray();

        if ($list) {

            $admin_list = SysServer::getAdminListCache();
            $is_send_arr = VipUserWelfare::$is_send_arr;
            $platform_list = SysServer::getPlatformList();
            $user_id_arr = [];
            foreach ($list as $k => &$v) {

                $v['action'] = ListActionServer::checkVipUserWelfareAction($v);

                $v['is_send_str'] = isset($is_send_arr[$v['is_send']]) ? $is_send_arr[$v['is_send']] : '';

                $v['admin_id_str'] = isset($admin_list[$v['admin_id']]) ? $admin_list[$v['admin_id']]['realname'] : '未知';

                $this_platform_info = getArrVal($platform_list, $v['platform_id'], []);

                if ($this_platform_info) {
                    $v['platform_id_str'] = $this_platform_info['name'];
                } else {
                    $v['platform_id_str'] = '';
                }

                $this_pu_str = $v['platform_id'] . '_' . $v['uid'];

                $v['p_u'] = $this_pu_str;

                $user_id_arr[$this_pu_str] = $this_pu_str;

            }

            if ($user_id_arr) {
                self::getVipUserInfoByPU($user_id_arr, $list);
            }

        }

        return [$list, $count];
    }

    public static function userWelfareListConfig(){

        $platform_list = SysServer::getPlatformList();

        $is_send_arr = VipUserWelfare::$is_send_arr;

        $search_config = [
            'admin'=>[
                'status'=>1,
                'p_data'=>['group_type'=>[QcConfig::USER_GROUP_VIP]],
            ],
        ];

        return compact('platform_list','is_send_arr','search_config');
    }

    public static function VipUserWelfareSave($p_data){
        $code = [
            0=>'success',1=>'操作失败',2=>'信息不存在'
        ];

        $id = isset($p_data['id'])?$p_data['id']:0;

        $model = new VipUserWelfare();


        $common_field = [
            'platform_id'
            ,'uid'
            ,'p_u'
            ,'qq'
            ,'goods'
            ,'address'
            ,'phone'
            ,'is_send'
            ,'desc'
            ,'content'
            ,'imgs'
            ,'send_time'
        ];

        if(isset($p_data['platform_id']) && $p_data['platform_id'] && isset($p_data['uid']) && $p_data['uid']){
            $p_data['p_u'] = $p_data['platform_id'].'_'.$p_data['uid'];
        }

        if(isset($p_data['content']) && $p_data['content']){
            $img_info = getHtmlImgSrc($p_data['content']);
            if(isset($img_info[1])){
                $p_data['imgs'] = json_encode($img_info[1]);
            }
        }

        if($id){

            $this_info = $model->where(['id'=>$id])->find();

            if(!$this_info){
                return ['code'=>2,'msg'=>$code[2]];
            }

            $common_field[] = 'id';
            //筛选要更新数据
            $save_data = getDataByField($p_data,$common_field);

            $save_data['admin_id'] = self::$user_data['id'];

            $res = $this_info->save($save_data);

            if(!$res) return ['code'=>1,'msg'=>$code[1]];
        }else{

            $add_data = getDataByField($p_data,$common_field);

            $add_data['admin_id'] = self::$user_data['id'];

            $res = $model->create($add_data);

            if(!$res) return ['code'=>1,'msg'=>$code[1]];
        }

        return ['code'=>0,'msg'=>$code[0]];
    }

    /**
     * 获取vip_user_info数据
     * @param array $user_id_arr
     * @param array $list
     * @param string $field
     */
    public static function getVipUserInfoByPU(array $user_id_arr,array &$list,string $field = 'user_name,total_pay'){
        $VipUserInfo = new VipUserInfo();

        $where = [];
        $where['_string'] = 'CONCAT(platform_id,"_",uid) IN("'.implode('","', $user_id_arr).'")';

        $columns = 'CONCAT(platform_id,"_",uid) AS p_u';

        if($field){
            $columns .= ','.$field;
        }

        $user_info = $VipUserInfo->field($columns)->where(setWhereSql($where,''))->select()->toArray();

        $user_info = arrReSet($user_info,'p_u');

        if($user_info){
            foreach ($list as $k => &$v) {
                $this_user_info = getArrVal($user_info,$v['p_u'],[]);
                if($this_user_info){
                    $v['user_name'] = $this_user_info['user_name'];
                    $v['total_pay'] = $this_user_info['total_pay'];
                }else{
                    $v['user_name'] = '';
                    $v['total_pay'] = 0;
                }
            }
        }
    }

    /**
     * 用户信息查询&历史游戏信息查询
     * @param $id vip用户数据id
     * @return array
     */
    public static function getUserRechargeInfoById($id)
    {
        $VipUserInfo = new VipUserInfo();

        $columns = '
            id
            ,platform_id
            ,uid
            ,user_name
            ,mobile
            ,game_id
            ,server_id
            ,server_name
            ,role_id
            ,reg_time
            ,total_pay
            ,is_record_static
            ,ascription_vip
        ';

        $where = compact('id');
        //查询vip详情数据
        $info = $VipUserInfo->field($columns)->where($where)->find();

        if(!$info){
            return [];
        }

        $res = $info->toArray();

        $config = [];

        $config['type_arr'] = SellWorkOrder::$type_arr;// 工单类型

        //获取平台数据
        $platform_list = SysServer::getPlatformList();

        $VipUserOtherInfo = new VipUserOtherInfo();

        //获取用户其他信息
        $where = [];
        $where['uid'] = $res['uid'];
        $where['platform_id'] = $res['platform_id'];
        $user_other_info = $VipUserOtherInfo->where($where)->find();
        //数据整理
        if($user_other_info){
            $user_other_info = $user_other_info->toArray();

            $user_other_info['sex_str'] = $user_other_info['sex'] == 1?'男':'女';

            $productList = SysServer::getGameProductCache();

            $productList = arrReSet($productList,'id_str');

            $this_product_info = getArrVal($productList,$user_other_info['platform_id'].'_'.$user_other_info['product_id'],[]);

            if($this_product_info) $user_other_info['product_id_str'] = $this_product_info['name'];

            $user_other_info['birthday'] = $user_other_info['birth_month']."-".$user_other_info['birth_day'];
        }else{
            $user_other_info['sex_str'] = '男';
        }

        $res['reg_time_str'] = date('Y-m-d H:i',$res['reg_time']);

        $res['mobile'] = $res['mobile']?replacePhone($res['mobile']):'未绑定';

        $res['platform_id_str'] = isset($platform_list[$res['platform_id']])?$platform_list[$res['platform_id']]['name']:'未知';


        $EveryDayOrderCount = new EveryDayOrderCount();
        //查询历史游戏信息
        $where = [];
        $where['uid'] = $res['uid'];
        $where['platform_id'] = $res['platform_id'];

        $columns = '
            role_id
            ,role_name
            ,game_name
            ,server_name
            ,sum(amount_count) AS amount_count_sum
            ,pay_time
            ,game_id
            ,platform_id
        ';

        $history_game_info = $EveryDayOrderCount->where($where)->field($columns)->group('game_id')->select()->toArray();//,server_id
        $history_order = [];
        if($history_game_info){

            if(!isset($platform_list[$res['platform_id']])){
                return [];
            }
            $platformInfo = $platform_list[$res['platform_id']];

            $KefuLoginLogModel = CommonServer::getPlatformModel('KefuLoginLog',$platformInfo['suffix']);

            $gameIds = array_column($history_game_info, 'game_id');

            //获取游戏对应的大类列表
            $PlatformGameInfoModel = new PlatformGameInfo();
            $platformGameList = $PlatformGameInfoModel->where(['platform_id'=>$res['platform_id'], 'game_id'=>['in',$gameIds]])->select()->toArray();

            $platformGameArr = $history_order = [];
            foreach ($platformGameList as $v) {
                $platformGameArr[$v['platform_id'].'_'.$v['game_id']] = $v['product_id'];
            }

            foreach ($history_game_info as $k => &$v) {
                //查询vip用户登录记录
                $where = [];
                $where['uid'] = $res['uid'];
                $where['gid'] = $v['game_id'];
                $this_login_info = [];
                $this_login_Obj = $KefuLoginLogModel->where($where)->find();
                if ($this_login_Obj) $this_login_info = $this_login_Obj->toArray();

                $v['login_date'] = isset($this_login_info['login_date']) ? $this_login_info['login_date'] : 0;
                if (empty($history_order[$platformGameArr[$v['platform_id'].'_'.$v['game_id']]])) {
                    $history_order[$platformGameArr[$v['platform_id'].'_'.$v['game_id']]] = $v;
                }else {
                    $history_order[$platformGameArr[$v['platform_id'].'_'.$v['game_id']]]['amount_count_sum'] += $v['amount_count_sum'];
                    if ($v['pay_time'] > $history_order[$platformGameArr[$v['platform_id'].'_'.$v['game_id']]]['pay_time'] && $v['pay_time'] > 0) {
                        $history_order[$platformGameArr[$v['platform_id'].'_'.$v['game_id']]]['pay_time'] = $v['pay_time'];
                        $history_order[$platformGameArr[$v['platform_id'].'_'.$v['game_id']]]['role_name'] = $v['role_name'];
                        $history_order[$platformGameArr[$v['platform_id'].'_'.$v['game_id']]]['server_name'] = $v['server_name'];
                    }
                    if ( isset($this_login_info['login_date']) && $this_login_info['login_date'] > $history_order[$platformGameArr[$v['platform_id'].'_'.$v['game_id']]]['login_date'] && $this_login_info['login_date'] > 0) {
                        $history_order[$platformGameArr[$v['platform_id'].'_'.$v['game_id']]]['login_date'] = $this_login_info['login_date'];
                    }
                }
            }
            //获取游戏产品
            $GameProductModel = new GameProduct();
            $gameProductList = $GameProductModel->where(['platform_id'=>$res['platform_id']])->select()->toArray();

            $gameProductArr = [];
            foreach ($gameProductList as $v) {
                $gameProductArr[$v['product_id']] = $v;
            }
            foreach ($history_order as $k=>&$v) {
                $history_order[$k]['login_time_str'] = !empty($v['login_date']) ? date('Y-m-d H:i',$v['login_date']) : '';
                $history_order[$k]['pay_time_str'] = !empty($v['pay_time']) ? date('Y-m-d H:i',$v['pay_time']) : '';
                $history_order[$k]['product_name'] = !empty($gameProductArr[$k]['product_name']) ? $gameProductArr[$k]['product_name'] : '';
            }
        }
        $history_order = array_values($history_order);

        $res_field = [
            'platform_id',
            'platform_id_str',
            'user_name',
            'uid',
            'reg_time_str',
            'total_pay',
            'mobile',
            'role_id',
        ];

        $user_other_info_field = [
            'product_id_str',
            'qq_number',
            'wechat',
            'sex_str',
            'birthday',
            'mobile',
        ];

        $detail = getDataByField($res,$res_field,0,'res_');

        $detail = array_merge($detail,getDataByField($user_other_info,$user_other_info_field,0,'user_other_info_'));

        return compact('detail','history_order','config');
    }

    /**
     * @param $p_data
     * @param $platform_id
     * @return array
     */
    public static function getVUServerInfoByWhere($p_data){

        $platform_id = getArrVal($p_data,'platform_id',0);

        if(!$platform_id){
            return [];
        }

        $p_data['platform_id'] = $platform_id;

        if(isset($p_data['product_id'])){
            if($p_data['product_id']){
                $PlatformGameInfo = new PlatformGameInfo();
                $game_ids = [];
                $where = [];
                $where['product_id'] = $p_data['product_id'];
                $where['platform_id'] = $platform_id;
                //查询游戏大类下面的game_id
                $this_info = $PlatformGameInfo->where($where)->select()->toArray();

                foreach ($this_info as $v){
                    $game_ids[] = $v['game_id'];
                }
                if($game_ids){
                    $p_data[] = ['game_id','in',$game_ids];
                }else{
                    $p_data[] = ['game_id','=',0];
                }
            }
            unset($p_data['product_id']);
        }

        clearZero($p_data,['uid']);

        $EveryDayOrderCount = new EveryDayOrderCount();

        return $EveryDayOrderCount
            ->where(setWhereSql($p_data,''))
            ->field('game_id,server_id,date,uid')
            ->group('game_id,server_id')
            ->order('pay_time DESC')
            ->select()->toArray();
    }
}
