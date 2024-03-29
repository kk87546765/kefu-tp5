<?php
/**
 * 系统
 */
namespace common\server\Vip;

use common\base\BasicServer;
use common\model\db_customer\QcConfig;
use common\model\db_statistic\EveryDayOrderCount;
use common\model\db_statistic\SellWorkOrder;
use common\model\db_statistic\VipKfDayStatistic;
use common\model\db_statistic\VipUserInfo;
use common\model\db_statistic\VipUserRebateProp;
use common\server\CustomerPlatform\CommonServer;
use common\server\SysServer;
use common\model\db_statistic\PlatformGameInfo;

class StatisticServer extends BasicServer
{
    protected static $amount_area = [
        ['title'=>'1K单笔(金额/次数)','start'=>1000,'end'=>1999],
        ['title'=>'2K单笔(金额/次数)','start'=>2000,'end'=>2999],
        ['title'=>'3K单笔(金额/次数)','start'=>3000,'end'=>4999],
        ['title'=>'5K单笔(金额/次数)','start'=>5000,'end'=>9999],
        ['title'=>'10K单笔(金额/次数)','start'=>10000,'end'=>0],
    ];

    public static $big_order_limit_def = 10000;

    private static $recharge_range = [
        1=>'0-99',
        2=>'100-499',
        3=>'500-999',
        4=>'1000-1999',
        5=>'2000-4999',
        6=>'5000-9999',
        7=>'10000-49999',
        8=>'50000-99999',
        9=>'100000+'
    ];

    private static $hour_phase = [
        '00'=>'00:00:00-00:59:59',
        '01'=>'01:00:00-01:59:59',
        '02'=>'02:00:00-02:59:59',
        '03'=>'03:00:00-03:59:59',
        '04'=>'04:00:00-04:59:59',
        '05'=>'05:00:00-05:59:59',
        '06'=>'06:00:00-06:59:59',
        '07'=>'07:00:00-07:59:59',
        '08'=>'08:00:00-08:59:59',
        '09'=>'09:00:00-09:59:59',
        '10'=>'10:00:00-10:59:59',
        '11'=>'11:00:00-11:59:59',
        '12'=>'12:00:00-12:59:59',
        '13'=>'13:00:00-13:59:59',
        '14'=>'14:00:00-14:59:59',
        '15'=>'15:00:00-15:59:59',
        '16'=>'16:00:00-16:59:59',
        '17'=>'17:00:00-17:59:59',
        '18'=>'18:00:00-18:59:59',
        '19'=>'19:00:00-19:59:59',
        '20'=>'20:00:00-20:59:59',
        '21'=>'21:00:00-21:59:59',
        '22'=>'22:00:00-22:59:59',
        '23'=>'23:00:00-23:59:59',
    ];

    /**
     * 获取用户的今天充值累计
     * @param int $platform_id
     * @param int $uid
     * @return int
     */
    public static function getUserTodayOrderSum($platform_id=0, $uid=0)
    {
        $sum = 0;
        if (empty($platform_id) || empty($uid)) return $sum;
        $platform_list = SysServer::getPlatformList();
        if(isset($platform_list[$platform_id])){
            $model = CommonServer::getPlatformModel('kefu_pay_order',$platform_list[$platform_id]['suffix']);
            $where = [];
            $where['pay_time'] = ['>=',strtotime(date('Y-m-d'))];
            if(is_array($uid)){
                $where['uid'] = ['in',$uid];
            }else{
                $where['uid'] = ['=',$uid];
            }
            $sum = $model->where($where)->sum('amount');
        }
        return $sum;
    }

    /**
     *
     * @param $data
     * @return array
     * @throws \think\Exception
     */
    public static function getUserOtherDayPayData($data)
    {
        $result = $dayPayCountArr = $thirtyDayPayCountArr = [];

        $model = new EveryDayOrderCount();

        $where = [];
        $where['p_u'] = ['in',$data];
        $where['date'] = date("Y-m-d",strtotime("-1 day"));

        $dayPayCount = $model->field('sum(amount_count) as today_pay,p_u as uid_platform')->where($where)->group('platform_id,uid')->select();

        if (!empty($dayPayCount)){
            $dayPayCountArr = $dayPayCount->toArray();
            foreach ($dayPayCountArr as $v) {
                $result[$v['uid_platform']]['today_pay'] = $v['today_pay'];
            }
        }
        unset($dayPayCount,$dayPayCountArr);

        $where = [];
        $where['p_u'] = ['in',$data];
        $where['date'] = ['>=',date("Y-m-d",strtotime("-30 day"))];
        $thirtyDayPayCount = $model->field('sum(amount_count) as last_thirty_day_pay_count,p_u as uid_platform,game_id')
            ->where($where)
            ->group('platform_id,uid,game_id')
            ->select();

        if (!empty($thirtyDayPayCount)){
            $thirtyDayPayCountArr = $thirtyDayPayCount ->toArray();

            foreach ($thirtyDayPayCountArr as $v) {
                if (isset($result[$v['uid_platform']]['tmp_pay_count'])) {
                    if ($v['last_thirty_day_pay_count'] > $result[$v['uid_platform']]['tmp_pay_count']) {
                        $result[$v['uid_platform']]['tmp_pay_count'] = $v['last_thirty_day_pay_count'];
                        $result[$v['uid_platform']]['last_thirty_day_highest_pay_game'] = $v['game_id'];
                    }
                }else {
                    $result[$v['uid_platform']]['tmp_pay_count'] = $v['last_thirty_day_pay_count'];
                    $result[$v['uid_platform']]['last_thirty_day_highest_pay_game'] = $v['game_id'];
                    $result[$v['uid_platform']]['last_thirty_day_pay_count'] =$v['last_thirty_day_pay_count'];
                }

                $result[$v['uid_platform']]['last_thirty_day_pay_count'] +=$v['last_thirty_day_pay_count'];
            }
        }
        if($result){
            foreach ($result as &$v) {
                unset($v['tmp_pay_count']);
            }
        }

        return $result;
    }

    public static function kfDayStatisticList($param){

        $page = getArrVal($param,'page',1);
        $limit = getArrVal($param,'limit',20);

        $model = new VipKfDayStatistic();
        $SellWorkOrder = new SellWorkOrder();

        $where =[
            ['add_user_count','>',0],
        ];


        if(self::$user_data['is_admin'] == 0){
            if(self::$user_data['platform_id']){
                $where[] =['platform_id','in',self::$user_data['platform_id']];
            }else{
                $where[] =['platform_id','=',0];
            }
        }
        $where = self::vipKfDayStatisticWhere($param,$where);

        $start_time = 0;
        $end_time = 0;

        if(isset($param['day_start']) && $param['day_start']){
            $start_time = strtotime($param['day_start']);
        }

        if(isset($param['day_end']) && $param['day_end'] ){
            $end_time = strtotime($param['day_end']);
        }

        $count = $model->where(setWhereSql($where,''))->count();

        if(!$count) return [[],0];

        $list = $model->where(setWhereSql($where,''));

        if($limit && $page){
            $list = $list->page($page,$limit);
        }

        $list = $list->order('day desc,product_id desc,game_id desc')
            ->select()->toArray();

        if($list){

            $admin_list = SysServer::getAdminListCache();

            $platform_list = SysServer::getPlatformList();

            $PlatformGameInfo = new PlatformGameInfo();

            foreach ($list as $k => &$v) {

                $v['action'] = [];
                $v['admin_id_str'] = isset($admin_list[$v['admin_id']])?$admin_list[$v['admin_id']]['name']:'未知';

                $v['day_str'] = $v['day']?date('Y-m-d',$v['day']):'';
                $v['p_g'] = $v['platform_id'].'_'.$v['game_id'];
                $v['game_id_str'] = '';
                $v['product_id_str'] = '';
                $v['add_user_maintenance'] = 0;
                $v['add_user_maintenance_str'] = '';
                $v['add_user_amount_month'] = 0;

                if($v['add_user_count']>0){
                    $p_u_arr = [];

                    $this_add_user_arr = explode(',',$v['add_user_str']);

                    foreach ($this_add_user_arr as $taua_v){
                        $p_u_arr[] = $v['platform_id'].'_'.$taua_v;
                    }

                    $where = [];
                    $where[] = ['p_u','in',$p_u_arr];
                    $where[] = ['status','>=',0];
                    $start_time && $where[] = ['add_time','>=',$start_time];
                    $end_time && $where[] = ['add_time','<',$end_time];

                    $field = '
                       concat(platform_id,"_",game_id) as p_g
                       ,count(distinct(p_u)) as c
                       ,sum(sell_amount) as sell_amount
                    ';

                    $sell_work_count = $SellWorkOrder->where(setWhereSql($where,''))->field($field)->find();

                    if($sell_work_count){
                        $sell_work_count = $sell_work_count->toArray();
                        $v['add_user_maintenance'] = $sell_work_count['c'];
                        $v['add_user_maintenance_str'] = countPresent($sell_work_count['c'],$v['add_user_count']).'%';
                        $v['add_user_amount_month'] = $sell_work_count['sell_amount'];
                    }
                }

                $this_info = $PlatformGameInfo->where(['platform_id'=>$v['platform_id'],'game_id'=>$v['game_id']])->find();

                if($this_info){
                    $v['game_id_str'] = $this_info->game_name;
                    $this_platform_info = getArrVal($platform_list,$v['platform_id'],[]);
                    $v['product_id_str'] = $this_info->product_name.'('.($this_platform_info?$this_platform_info['name']:'').')';
                }
            }
        }

        return [$list,$count];
    }

    public static function kfDayStatisticListConfig(){

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
                'p_data'=>['group_type'=>[QcConfig::USER_GROUP_VIP],'is_active'=>1],
                'name'=>'admin_id',
            ],
        ];

        $time_arr = timeCondition('month');

        $form = [];

        $form['day_start'] = date('Y-m-d',$time_arr['starttime']);

        return compact('search_config','form');
    }

    /**
     * 每日新增用户分析-统计
     * @param array $param
     * @return array
     */
    public static function getVipKfDayStatistic(array $param){


        $model = new VipKfDayStatistic();
        $SellWorkOrder = new SellWorkOrder();
        $where =[
            ['add_user_count','>',0],
        ];

        if(self::$user_data['is_admin'] == 0){
            if(self::$user_data['platform_id']){
                $where[] =['platform_id','in',self::$user_data['platform_id']];
            }else{
                $where[] =['platform_id','=',0];
            }
        }
        $where = self::vipKfDayStatisticWhere($param,$where);

        $start_time = 0;
        $end_time = 0;

        if(isset($param['day_start']) && $param['day_start']){
            $start_time = strtotime($param['day_start']);
        }

        if(isset($param['day_end']) && $param['day_end'] ){
            $end_time = strtotime($param['day_end']);
        }

        $res = [
            'add_user_count'=>0,
            'add_user_maintenance'=>0,
            'add_user_maintenance_str'=>'',
            'add_user_amount_month'=>0,
        ];

        $field = '
            platform_id
            ,day
            ,add_user_count
            ,add_user_str
        ';
        $list = $model->field($field)->where(setWhereSql($where,''))->select()->toArray();

        if($list){
            $p_u_data = [];
            $p_u_arr = [];
            foreach ($list as $v){
                $res['add_user_count'] += $v['add_user_count'];

                $this_time = timeCondition('month',$v['day']);
                if(!isset($p_u_data[$this_time['starttime']])){
                    $p_u_data[$this_time['starttime']]['time'] = $this_time;
                    $p_u_data[$this_time['starttime']]['p_u'] = [];
                }
                $this_str = $v['add_user_str'];
                $this_str = str_replace(',',",$v[platform_id]_",$this_str);
                $this_str = "$v[platform_id]_".$this_str;
                $this_p_u_arr = explode(',',$this_str);
                $p_u_data[$this_time['starttime']]['p_u'] = array_merge($p_u_data[$this_time['starttime']]['p_u'],$this_p_u_arr);
                $p_u_arr = array_merge($p_u_arr,$this_p_u_arr);
            }

            if($p_u_arr){

                $where = [];
                $where[] = ['p_u','in',$p_u_arr];
                $where[] = ['status','>=',0];
                $start_time && $where[] = ['add_time','>=',$start_time];
                $end_time && $where[] = ['add_time','<',$end_time];
                $this_count = $SellWorkOrder->where(setWhereSql($where,''))->field('count(distinct(p_u)) as c,sum(sell_amount) as sell_amount')->find();
                if($this_count){
                    $this_count = $this_count->toArray();
                    $res['add_user_maintenance'] += $this_count['c'];
                    $res['add_user_amount_month'] += $this_count['sell_amount'];
                }
            }
        }

        if($res['add_user_maintenance']){
            $res['add_user_maintenance_str'] = countPresent($res['add_user_maintenance'],$res['add_user_count']).'%';
        }




        return $res;
    }

    protected static function vipKfDayStatisticWhere($param,$def_where = []){

        $where = [];
        if($def_where){
            $where = $def_where;
        }

        if(isset($param['group_id']) && $param['group_id']){
            $kf_ids = SysServer::getAdminListByGroupIds($param['group_id'],2);

            if($kf_ids){
                $where[] = ['admin_id','in',$kf_ids];
            }else{
                $where[] =['admin_id','=',-1];
            }
        }

        if(isset($param['platform_id']) && $param['platform_id']){
            if(is_array($param['platform_id'])){
                $this_info = $param['platform_id'];
            }else{
                $this_info = explode(',',$param['platform_id']);
            }
            if(count($this_info) == 1){
                $where['platform_id'] = $this_info[0];
            }else{
                $where[] = ['platform_id','in',$this_info];
            }
        }
        if(isset($param['admin_id']) && $param['admin_id']){
            if(is_array($param['admin_id'])){
                $this_info = $param['admin_id'];
            }else{
                $this_info = explode(',',$param['admin_id']);
            }
            if(count($this_info) == 1){
                $where['admin_id'] = $this_info[0];
            }else{
                $where[] = ['admin_id','in',$this_info];
            }
        }

        if(isset($param['p_p']) && $param['p_p']){
            if(is_array($param['p_p'])){
                $this_info = $param['p_p'];
            }else{
                $this_info = explode(',',$param['p_p']);
            }
            if(count($this_info) == 1){
                $where['p_p'] = $this_info[0];
            }else{
                $where[] = ['p_p','in',$this_info];
            }
        }

        if(isset($param['p_g']) && $param['p_g']){
            if(is_array($param['p_g'])){
                $this_info = $param['p_g'];
            }else{
                $this_info = explode(',',$param['p_g']);
            }
            if(count($this_info) == 1){
                $where['p_g'] = $this_info[0];
            }else{
                $where[] = ['p_g','in',$this_info];
            }
        }

        if(isset($param['day_start'])){
            if($param['day_start']){
                $where[] = ["day",'>=',strtotime($param['day_start'])];
            }
        }

        if(isset($param['day_end'])){
            if($param['day_end']){
                $where[] = ["day",'<',strtotime($param['day_end'])];
            }
        }

        if(isset($param['month']) && $param['month']){
            $time_arr = timeCondition('month',strtotime($param['month']));
            $yesterday = strtotime('yesterday');
            if($time_arr['endtime'] > $yesterday){
                $day_time = $yesterday;
            }else{
                $day_time = $time_arr['endtime']+1-3600*24;
            }
            $where[] = ["day",'=',$day_time];
        }

        return $where;
    }

    public static function userRechargeList($params){
        $code = [
            0=>'success',1=>'未知用户类型'
        ];

        $count = 0;
        $page = getArrVal($params,'page',1);
        $limit = getArrVal($params,'limit',20);
        $is_excel = getArrVal($params,'is_excel',0);

        $sql = "SELECT
                    B.id,
                    A.platform_id,
                    A.date,
                    A.game_id,
                    A.game_name,
                    B.user_name,
                    B.uid,
                    A.server_id,
                    A.server_name,
                    A.role_id,
                    A.role_name,
                    B.ascription_vip,
                    SUM(A.amount_count) AS sumamount,
                    SUM(A.order_count) AS order_count,
                    B.last_record_time
                FROM
                    every_day_order_count AS A
                INNER JOIN vip_user_info AS B ON A.platform_id = B.platform_id
                AND A.uid = B.uid ";
        $where = ' where 1=1 ';

        if(self::$user_data['is_admin'] == 0){
            $this_where = [];
            $this_where[] = getWhereDataArr(self::$user_data['platform_id'],'B.platform_id');
            $where.= setWhereSql($this_where,' AND ');
        }

        if(isset($params['vip_commissioner']) && $params['vip_commissioner']){
            $where .= " and B.ascription_vip in (".$params['vip_commissioner'].")";
        }

        if(isset($params['platform_id']) && $params['platform_id']){
            $where .= " and B.platform_id in (".$params['platform_id'].")";
        }
        $where_rp = [];
        if(isset($params['rp_examine_status']) && $params['rp_examine_status']>-1){
            $where_rp['examine_status'] = $params['rp_examine_status'];
        }

        if(isset($params['rp_apply_status']) && $params['rp_apply_status']){
            $where_rp['apply_status'] = $params['rp_apply_status'];
        }

        if ($params['start_time']) {
            $where .= " and A.date >= '".date('Y-m-d', strtotime($params['start_time']))."'";

            $where_rp && $where_rp[] = ['date','>=',date('Y-m-d', strtotime($params['start_time']))];
        }
        if ($params['end_time']) {
            $where .= " and A.date <= '".date('Y-m-d', strtotime($params['end_time']))."'";
            $where_rp && $where_rp[] = ['date','<=',date('Y-m-d', strtotime($params['end_time']))];
        }

        if($where_rp){
            $VipUserRebateProp = new VipUserRebateProp();
            $rp_list = $VipUserRebateProp->field('platform_id,uid')->where(setWhereSql($where_rp,''))->select()->toArray();
            if($rp_list){
                $this_pu_arr = [];
                foreach ($rp_list as $pbl_k =>$pbl_v){
                    $this_pu_arr[] = $pbl_v['platform_id'].'_'.$pbl_v['uid'];
                }
                $where .= " and A.p_u in('".implode('\',\'',$this_pu_arr)."')";
            }else{
                $where .= " and A.p_u = 0 ";
            }
        }
        //处理区服
        $serverIds = [];
        if ((int)$params['server_id_max'] >= (int)$params['server_id_min'] && (int)$params['server_id_max'] >0) {
            $i = (int)$params['server_id_min'];
            for ($i; $i<= (int)$params['server_id_max']; $i++) {
                $serverIds[] = trim($params['server_prefix'].$i.$params['server_suffix'], '');
            }
        }

        if(!empty($params['p_g'])){
            $where .= " and B.p_l_g in ('".str_replace(',','\',\'',$params['p_g'])."')";
        }elseif (!empty($params['p_p'])) {
            $PlatformGameInfo = new PlatformGameInfo();

            $this_where = [];
            $this_where['static'] = 1;
            $this_where[] = getWhereDataArr($params['p_p'],"concat(platform_id,'_',product_id)");

            $p_g_list = $PlatformGameInfo->field("concat(platform_id,'_',game_id) as p_g",2)->where(setWhereSql($this_where,''))->select()->toArray();

            if(!$p_g_list){
                $where.=' AND B.p_l_g = 0';
            }else{
                $p_g_arr = [];

                foreach ($p_g_list as $item){
                    $p_g_arr[] = $item['p_g'];
                }
                if($p_g_arr){
                    $where .= " and B.p_l_g in ('".implode('\',\'',$p_g_arr)."')";
                }else{
                    $where.=' AND B.p_l_g = 0';
                }

            }
        }

        //高管
        if (self::$user_data['position_grade'] > 3) {


        }elseif (in_array(self::$user_data['position_grade'],[2,3])) {
            $ids = SysServer::getAdminListByGroupIds(self::$user_data['group_id'],2);
            if($ids){
                $where .= " and B.ascription_vip in (".implode(',',$ids).")";
            }else{
                $where .= " and B.ascription_vip = 0";
            }

        }elseif (self::$user_data['position_grade'] == 1) {
            $where .= " and B.ascription_vip = ".self::$user_data['id'];
        }else{
            return ['code'=>1,'msg'=>$code[1]];
        }

        if (!empty($serverIds) && is_array($serverIds)) {
            $serverIdStr = "'".trim(implode("','",$serverIds), ',')."'";
            $where .= " and B.server_id in (".$serverIdStr.") ";
        }

        if ($params['uid']) {
            $where .= " and A.uid = ".$params['uid'];
            $where .= " and B.uid = ".$params['uid'];
        }
        if ($params['user_name']) {
            $where .= " and B.user_name = '".$params['user_name']."'";
        }
        if ($params['role_id']) {
            $where .= " and A.role_id = ".$params['role_id'];
        }
        if ($params['role_name']) {
            $where .= " and A.role_name = '".$params['role_name']."'";
        }
        if ($params['is_record'] == 1) {
            $where .= " and B.last_record_time > 0";
        }elseif ($params['is_record'] == 2) {
            $where .= " and B.last_record_time = 0";
        }

        $groupBy = " GROUP BY A.date,A.game_id,A.platform_id,A.uid";

        $having = '';
        if ($params['pay_min'] >0) {
            $having .= " HAVING sumamount >= ".$params['pay_min'];
            if ($params['pay_max'] > 0) {
                $having .= " and sumamount < ".$params['pay_max'];
            }
        }else {
            if ($params['pay_max'] > 0) {
                $having .= " HAVING sumamount < ".$params['pay_max'];
            }
        }
        $orderBy = ' ORDER BY sumamount DESC ';
        $res_count = [
            'people_count'=>0,
            'sum_pay'=>0,
        ];

        $everyDayPayModel = new EveryDayOrderCount();
        if (!$is_excel) {
            $limitSql = $sql.$where.$groupBy.$having.$orderBy." limit ".($page - 1) * $limit.",".$limit;

            $list = $everyDayPayModel->query($limitSql);

            $sqlCount = "SELECT COUNT(1) as total,COUNT(DISTINCT C.platform_id,C.uid) as people_count,SUM(C.sumamount) as sum_pay  FROM (
                                SELECT
                                    A.platform_id,
                                    A.uid,
                                    SUM(A.amount_count) AS sumamount
                                FROM
                                    every_day_order_count AS A
                                INNER JOIN vip_user_info AS B ON A.platform_id = B.platform_id
                                AND A.uid = B.uid ";
            $sqlCount = $sqlCount.$where.$groupBy.$having." ) as C";

            $vipCount = $everyDayPayModel->query($sqlCount);

            $count = (int)$vipCount[0]['total'];
            $res_count = $vipCount[0];
        }else{
            $limitSql = $sql.$where.$groupBy.$having.$orderBy;
            $list = $everyDayPayModel->query($limitSql);
        }
        $rebatePropInfo = [];
        if($is_excel){
            $rebatePropInfo = WelfareServer::getRebatePropInfoByDate($params['start_time'], $params['end_time'], $params['platform_id']);
        }

        if($list){
            $list = self::userRechargeListDo($list,$is_excel,$rebatePropInfo);
        }

        return ['code'=>0,'data'=>$list,'count'=>$count,'res_count'=>$res_count];
    }

    public static function userRechargeListDo($data = array(),$is_excel = false,$rebate_prop_info = array())
    {
        if (empty($data)) return  array();

        $adminUserList = SysServer::getAdminListCache();
        $platformGameList = SysServer::getPlatformGameInfoCache();
        foreach ($data as &$v) {
            $this_admin = getArrVal($adminUserList,$v['ascription_vip'],[]);
            $this_admin && $v['ascription_vip_name'] = $this_admin['realname'];
            $this_product = getArrVal($platformGameList,$v['platform_id'].'_'.$v['game_id'],[]);
            $this_product && $v['product_name'] = $this_product['product_name'];
            $v['last_record_time'] = !empty($v['last_record_time']) ? date('Y-m-d H:i:s', $v['last_record_time']) : '';
            if ($is_excel) {
                $this_r_p_info = getArrVal($rebate_prop_info,$v['date'].'_'.$v['platform_id'].'_'.$v['uid'].'_'.$v['game_id'],[]);
                if ($this_r_p_info) {
                    $v['examine'] = $this_r_p_info[0]['examine'];
                    $v['apply_status'] = $this_r_p_info[0]['apply_status'];
                    $v['add_admin_name'] = $this_r_p_info[0]['add_admin_name'];
                    $v['content'] = '';
                    foreach ($this_r_p_info as $val) {
                        if (empty($v['content'])) {
                            $v['content'] .= "{$val['title']} {$val['content']} {$val['prop_id']}";
                        }else {
                            $v['content'] .= "#{$val['title']} {$val['content']} {$val['prop_id']}";
                        }
                    }
                }else {
                    $v['examine'] = '';
                    $v['apply_status'] = '';
                    $v['add_admin_name'] = '';
                    $v['content'] = '';
                }
                unset($v['ascription_vip'],$v['id'],$v['platform_id'],$v['game_id'],$v['examine_status']);
            }else {
                $rebatePropInfo = WelfareServer::isExelUserRebateProp($v);

                if (count($rebatePropInfo) >0) {
                    $v['action'][] = 'details';
                    if ($rebatePropInfo[0]['examine_status'] == 0){
                        $v['action'][] = 'rebate';
                    }else {
                        $v['action'][] = 'disabledRebate';
                    }
                }else{
                    $v['action'][] = 'rebate';
                }
            }
        }
        return $data;
    }

    public static function userRechargeListConfig(){
        $search_config = [
            'product'=>[
                'status'=>1,
            ],
            'game'=>[
                'status'=>1
            ],
            'admin'=>[
                'status'=>1,
                'p_data'=>['group_type'=>[QcConfig::USER_GROUP_VIP]],
                'name'=>'vip_commissioner',
            ],
        ];

        $config = compact('search_config');
        $config['examine_status_arr'] = VipUserRebateProp::$examine_status_arr;
        $config['apply_status_arr'] = VipUserRebateProp::$apply_status_arr;

        $config['form_data'] = [
            'start_time'=>date('Y-m-d',strtotime('-7 day')),
            'end_time'=>date('Y-m-d')
        ];



        return $config;
    }

    public static function kfMonthProductStatisticListConfig(){
        $time_arr = timeCondition('month');
        $config = [];
        $config['form_data']['month'] = date('Y-m',$time_arr['starttime']);
        $config['search_config'] = [
            'product'=>[
                'status'=>1,
            ],
            'admin'=>[
                'status'=>1,
                'p_data'=>[
                    'group_type'=>[QcConfig::USER_GROUP_VIP],
                    'not_group_type'=>[QcConfig::USER_GROUP_QC],
                ]
            ]
        ];
        return $config;
    }

    /**
     * 月产品Vip用户统计-列表
     * @param array $param
     * @param int $page
     * @param int $limit
     * @return array
     */
    public static function getVipKfMonthProductStatisticList(array $param){

        $def_res = $count_res = [
            'add_user_count_all'=>0,
            'maintain_user_all'=>0,
            'maintain_user_all_present'=>'',
            'add_user_count_month'=>0,
            'maintain_user_count_month'=>0,
            'maintain_user_count_month_present'=>'',
            'washed_away_all'=>0,
            'washed_away_all_present'=>'',
            'login_lost_all'=>0,
            'login_lost_all_present'=>'',
            'add_washed_away_month'=>0,
            'add_washed_away_month_present'=>'',
            'add_login_lost_month'=>0,
            'add_login_lost_month_present'=>'',
        ];

        $model = new VipKfDayStatistic();
        $SellWorkOrder = new SellWorkOrder();

        $where = self::vipKfDayStatisticWhere($param);

        if(self::$user_data['is_admin'] == 0){
            if(self::$user_data['platform_id']){
                $where[] =['platform_id','in',self::$user_data['platform_id']];
            }else{
                $where[] =['platform_id','=',0];
            }
        }

        $list = $model->where(setWhereSql($where,''))->select()->toArray();

        if(!$list){
            return [[],$count_res];
        }

        $new_list = [];
        $p_u_arr = [];
        foreach ($list as $item){
            if($item['add_user_count_month']){
                $this_str = str_replace(',',",$item[platform_id]_",$item['add_user_str_month']);
                $this_str = "$item[platform_id]_".$this_str;
                $p_u_arr = array_merge($p_u_arr,explode(',',$this_str));
            }

            if(!isset($new_list[$item['p_p']])){
                $new_list[$item['p_p']] = $def_res;
                $new_list[$item['p_p']]['p_p'] = $item['p_p'];
                $new_list[$item['p_p']]['day'] = $item['day'];
                $new_list[$item['p_p']]['platform_id'] = $item['platform_id'];
                $new_list[$item['p_p']]['product_id'] = $item['product_id'];
            }
            $new_list[$item['p_p']]['add_user_count_all'] += $item['add_user_count_all'];
            $new_list[$item['p_p']]['add_user_count_month'] += $item['add_user_count_month'];
            $new_list[$item['p_p']]['add_washed_away_month'] += $item['add_washed_away_month'];
            $new_list[$item['p_p']]['add_login_lost_month'] += $item['add_login_lost_month'];
            $new_list[$item['p_p']]['washed_away_all'] += $item['washed_away_all'];
            $new_list[$item['p_p']]['login_lost_all'] += $item['login_lost_all'];
            $new_list[$item['p_p']]['maintain_user_all'] += $item['maintain_user_all'];

            $count_res['add_user_count_all'] += $item['add_user_count_all'];
            $count_res['add_user_count_month'] += $item['add_user_count_month'];
            $count_res['add_washed_away_month'] += $item['add_washed_away_month'];
            $count_res['add_login_lost_month'] += $item['add_login_lost_month'];
            $count_res['washed_away_all'] += $item['washed_away_all'];
            $count_res['login_lost_all'] += $item['login_lost_all'];
            $count_res['maintain_user_all'] += $item['maintain_user_all'];
        }
        $maintain_user_info = [];
        if($p_u_arr){
            $where = [];
            $where[] = ['p_u','in',$p_u_arr];
            $where[] = ['status','>=',0];

            if(isset($param['month']) && $param['month']){
                $time_arr = timeCondition('month',strtotime($param['month']));
                $where[] = ["add_time",'>=',$time_arr['starttime']];
                $where[] = ["add_time",'<',$time_arr['endtime']];
            }

            $sell_work_order_info = $SellWorkOrder
                ->where(setWhereSql($where,''))
                ->field('p_p,count(distinct(p_u)) as c')
                ->group('p_p')
                ->select()
                ->toArray();
            if($sell_work_order_info){
                $maintain_user_info = arrReSet($sell_work_order_info,'p_p');
            }
        }

        $game_list = SysServer::getGameProductCache();//游戏列表
        $new_game_list = arrReSet($game_list,'id_str');

        foreach ($new_list as &$v){
            $v['day_str'] = date('Y-m',$v['day']);
            $this_p_p_info = getArrVal($new_game_list,$v['p_p'],[]);
            $v['p_p_str'] =$this_p_p_info?$this_p_p_info['name']:'';
            if($maintain_user_info){
                $this_info = getArrVal($maintain_user_info,$v['p_p'],[]);
                $v['maintain_user_count_month'] =$this_info?$this_info['c']:0;
                $count_res['maintain_user_count_month'] += $v['maintain_user_count_month'];
            }

            $v = array_merge($v,self::statisticVipKfMonthProductChild($v));
        }

        $new_list = array_values($new_list);
        $count_res = array_merge($count_res,self::statisticVipKfMonthProductChild($count_res));

        return [$new_list,$count_res];
    }

    public static function statisticVipKfMonthProductChild($v){

        $v['maintain_user_all_present'] = countPresent($v['maintain_user_all'],$v['add_user_count_all']).'%';
        $v['maintain_user_count_month_present'] = countPresent($v['maintain_user_count_month'],$v['add_user_count_month']).'%';
        $v['washed_away_all_present'] = countPresent($v['washed_away_all'],$v['add_user_count_all']).'%';
        $v['login_lost_all_present'] = countPresent($v['login_lost_all'],$v['add_user_count_all']).'%';
        $v['add_washed_away_month_present'] = countPresent($v['add_washed_away_month'],($v['add_user_count_all']-$v['washed_away_all']+$v['add_washed_away_month'])).'%';
        $v['add_login_lost_month_present'] = countPresent($v['add_login_lost_month'],($v['add_user_count_all']-$v['login_lost_all']+$v['add_login_lost_month'])).'%';

        return $v;
    }

    /**
     * 每日新增用户分析-列表
     * @param array $param
     * @param int $page
     * @param int $limit
     * @return array
     */
    public static function getVipKfDayStatisticList(array $param){


        $page = getArrVal($param,'page',1);
        $limit = getArrVal($param,'limit',20);

        $model = new VipKfDayStatistic();
        $SellWorkOrder = new SellWorkOrder();

        $where =[
            ['add_user_count','>',0],
        ];

        if(self::$user_data['is_admin'] == 0){
            if(self::$user_data['platform_id']){
                $where[] =['platform_id','in',self::$user_data['platform_id']];
            }else{
                $where[] =['platform_id','=',0];
            }
        }
        $where = self::vipKfDayStatisticWhere($param,$where);

        $start_time = 0;
        $end_time = 0;

        if(isset($param['day_start']) && $param['day_start']){
            $start_time = strtotime($param['day_start']);
        }

        if(isset($param['day_end']) && $param['day_end'] ){
            $end_time = strtotime($param['day_end']);
        }

        $count = $model->where(setWhereSql($where,''))->count();

        if(!$count) return [[],0];

        $list = $model->where(setWhereSql($where,''));

        if($limit && $page){
            $list = $list->page($page,$limit);
        }

        $list = $list->order('day desc,product_id desc,game_id desc')
            ->select()->toArray();

        if($list){

            $admin_list = SysServer::getAdminListCache();

            $platform_list = SysServer::getPlatformList();

            $PlatformGameInfo = new PlatformGameInfo();

            foreach ($list as $k => &$v) {

                $v['action'] = [];
                $v['admin_id_str'] = isset($admin_list[$v['admin_id']])?$admin_list[$v['admin_id']]['name']:'未知';

                $v['day_str'] = $v['day']?date('Y-m-d',$v['day']):'';
                $v['p_g'] = $v['platform_id'].'_'.$v['game_id'];
                $v['game_id_str'] = '';
                $v['product_id_str'] = '';
                $v['add_user_maintenance'] = 0;
                $v['add_user_maintenance_str'] = '';
                $v['add_user_amount_month'] = 0;

                if($v['add_user_count']>0){
                    $p_u_arr = [];

                    $this_add_user_arr = explode(',',$v['add_user_str']);

                    foreach ($this_add_user_arr as $taua_v){
                        $p_u_arr[] = $v['platform_id'].'_'.$taua_v;
                    }

                    $where = [];
                    $where[] = ['p_u','in',$p_u_arr];
                    $where[] = ['status','>=',0];
                    $start_time && $where[] = ['add_time','>=',$start_time];
                    $end_time && $where[] = ['add_time','<',$end_time];

                    $field = '
                       concat(platform_id,"_",game_id) as p_g
                       ,count(distinct(p_u)) as c
                       ,sum(sell_amount) as sell_amount
                    ';

                    $sell_work_count = $SellWorkOrder->where(setWhereSql($where,''))->$field($field)->find();

                    if($sell_work_count){
                        $sell_work_count = $sell_work_count->toArray();
                        $v['add_user_maintenance'] = $sell_work_count['c'];
                        $v['add_user_maintenance_str'] = countPresent($sell_work_count['c'],$v['add_user_count']).'%';
                        $v['add_user_amount_month'] = $sell_work_count['sell_amount'];
                    }
                }

                $this_info = $PlatformGameInfo->where(['platform_id'=>$v['platform_id'],'game_id'=>$v['game_id']])->find();

                if($this_info){
                    $v['game_id_str'] = $this_info->game_name;
                    $this_platform_info = getArrVal($platform_list,$v['platform_id'],[]);
                    $v['product_id_str'] = $this_info->product_name.'('.($this_platform_info?$this_platform_info['name']:'').')';
                }
            }
        }

        return [$list,$count];
    }

    /**
     * 营销单笔统计
     * @param array $param
     * @param int $page
     * @param int $limit
     * @param array $amount_area
     * @return array
     */
    public static function statisticProductAmountAreaMonth(array $param){

        $SellWorkOrder = new SellWorkOrder();//工单表
        /****************** 数据准备 ************************/
        $page = getArrVal($param,'page',1);
        $limit = getArrVal($param,'limit',20);
        $amount_area = self::$amount_area;

        $big_order_limit = self::$big_order_limit_def;//大单条件
        if(isset($param['big_order_limit']) && $param['big_order_limit']){
            $big_order_limit = $param['big_order_limit'];
        }

        if(self::$user_data['is_admin'] == 0){
            $param['admin_platform_id'] =self::$user_data['platform_id']?self::$user_data['platform_id']:[0];
        }

        /**************** 数据准备 end ******************/

        /*************** 营销数据 ****************/
        //列表
        $where = [];
        $where[] = ['status','>',0];
        $where['type'] = SellWorkOrder::SELL_ORDER;



        if(!empty($param['pay_time_start'])){
            $where[] = ['pay_time','>=',strtotime($param['pay_time_start'])];
        }else{
            $where[] = ['pay_time','>=',strtotime(date('Y-m-01'))];
        }

        if(!empty($param['pay_time_end'])){
            $where[] = ['pay_time','<=',strtotime($param['pay_time_end'])];
        }
        if(!empty($param['p_p'])){
            $where[] = getWhereDataArr($param['p_p'],'p_p');
        }
        if(!empty($param['platform_id'])){
            $where[] = getWhereDataArr($param['platform_id'],'platform_id');
        }

        if(isset($param['admin_platform_id']) && $param['admin_platform_id'] ){
            $where[] = ['platform_id','in',$param['admin_platform_id']];
        }

        $columns = "FROM_UNIXTIME(pay_time,'%Y-%m') as month,p_p";

        $group = "p_p,month";

//        //查询符合条件销售工单统计数据
        $sql = 'SELECT '.$columns.' FROM sell_work_order '.setWhereSql($where).' GROUP BY '.$group.' ORDER BY id desc';
        $count_list = $SellWorkOrder->query($sql);

        $count = count($count_list);

        if(!$count){
            return [[],0];
        }

        $columns = "p_p";
        $columns .= ",FROM_UNIXTIME(pay_time,'%Y-%m') as month";
        $columns .= ",pay_time";
        $columns .= ',sum(sell_amount) AS sell_amount_sum';
        $columns .= ',count(id) AS count_num';
        $columns .= ",SUM(CASE WHEN sell_amount > $big_order_limit THEN sell_amount ELSE 0 END ) AS big_order_sum";
        $columns .= ",SUM(CASE WHEN sell_amount > $big_order_limit THEN 1 ELSE 0 END ) AS big_order_count";
        if($amount_area){
            foreach ($amount_area as $k => $v){
                if($v['start'] && $v['end']){
                    $columns .= ",SUM(CASE WHEN sell_amount >= ".$v['start']." AND sell_amount <= ".$v['end']." THEN sell_amount ELSE 0 END ) AS order".$k."_sum";
                    $columns .= ",SUM(CASE WHEN sell_amount >= ".$v['start']." AND sell_amount <= ".$v['end']." THEN 1 ELSE 0 END ) AS order".$k."_count";
                }elseif($v['start']){
                    $columns .= ",SUM(CASE WHEN sell_amount >= ".$v['start']." THEN sell_amount ELSE 0 END ) AS order".$k."_sum";
                    $columns .= ",SUM(CASE WHEN sell_amount >= ".$v['start']." THEN 1 ELSE 0 END ) AS order".$k."_count";
                }elseif($v['end']){
                    $columns .= ",SUM(CASE WHEN sell_amount <= ".$v['end']." THEN sell_amount ELSE 0 END ) AS order".$k."_sum";
                    $columns .= ",SUM(CASE WHEN sell_amount <= ".$v['end']." THEN 1 ELSE 0 END ) AS order".$k."_count";
                }
            }
        }

        //获取kpi列表
        $sql = 'SELECT '.$columns.' FROM sell_work_order '.setWhereSql($where).' GROUP BY '.$group.' ORDER BY pay_time desc,platform_id asc,product_id desc';
        if($limit){
            $sql.= " limit ".($page - 1) * $limit.",$limit";
        }

        $list = $SellWorkOrder->query($sql);

        $game_list = SysServer::getGameProductCache();//游戏列表
        $game_list_new = arrReSet($game_list,'id_str');

        foreach ($list as $k =>&$v){
            $v['big_order_present_str'] = '';
            if($v['count_num']){
                $v['big_order_present_str'] = countPresent($v['big_order_sum'],$v['sell_amount_sum']).'%'.'('.$v['big_order_sum'].'/'.$v['sell_amount_sum'].')';
            }
            $v['product_id_str'] = $game_list_new[$v['p_p']]['name'];
            foreach ($amount_area as $aa_k => $aa_v){
                $v["order".$aa_k."_str"] = $v["order".$aa_k."_sum"].'/'.$v["order".$aa_k."_count"];
            }
        }

        return [$list,$count];
    }

    public static function statisticSwoProductAmountAreaConfig(){
        $config = [];
        $config['amount_area'] = self::$amount_area;
        $config['form_data']['big_order_limit'] = self::$big_order_limit_def;
        $config['search_config'] = [
            'product'=>[
                'status'=>1,
            ],
        ];
        return $config;
    }

    /**
     * @param array $params
     * @param false $type
     * @return array
     */
    public static function getMarketingStageReport($params = array())
    {
        $type = getArrVal($params,'type',0);
        $platform_id = getArrVal($params,'platform_id',0);

        $result = [];
        $pay_time_start = $pay_time_end = 0;
        if (!empty($params['pay_time_start'])) $pay_time_start = strtotime($params['pay_time_start']);
        if (!empty($params['pay_time_end'])) $pay_time_end = strtotime($params['pay_time_end']);

        if (!empty($pay_time_end) && $pay_time_end < $pay_time_start) return $result;
        if (!$platform_id) return $result;

        $dayCountSql = "SELECT
                            A.date,B.ascription_vip,
                        SUM(A.amount_count) as amount_count,
                        SUM(A.order_count) AS order_count,
                        COUNT(DISTINCT A.p_u) AS people_count
                        FROM
                            every_day_order_count AS A
                        INNER JOIN vip_user_info AS B ON A.platform_id = B.platform_id
                        AND A.uid = B.uid
                        WHERE
                            A.date >= FROM_UNIXTIME(
                            B.first_distribute_time,
                            '%Y-%m-%d'
                           ) AND A.platform_id = $platform_id
                           ";

        $sellSql = "SELECT
                        A.kf_id as ascription_vip,
                        SUM(A.sell_amount) AS sell_amount,
                        COUNT(1) as  sell_count,
                        FROM_UNIXTIME(A.pay_time, '%Y-%m-%d') AS date
                    FROM
                        `sell_work_order` AS A
                    INNER JOIN vip_user_info AS B ON A.platform_id = B.platform_id
                    AND A.uid = B.uid
                    WHERE
                        A.pay_time >= B.first_distribute_time
                        and  A.type = ".SellWorkOrder::SELL_ORDER."
                    AND A.STATUS > 0 AND A.platform_id = $platform_id";

        if(self::$user_data['is_admin'] == 0){
            $where = [];
            $where[] = getWhereDataArr(self::$user_data['platform_id'],'B.platform_id');
            $dayCountSql.= setWhereSql($where,' AND ');
            $sellSql.= setWhereSql($where,' AND ');
        }
        if (!empty($pay_time_start)) {
            $dayCountSql .= " and A.date >='".$params['pay_time_start']."'";
            $sellSql .= " and A.pay_time >= ".$pay_time_start;
        }
        if (!empty($params['pay_time_end'])) {
            $dayCountSql .= " and A.date <'".$params['pay_time_end']."'";
            $sellSql .= " and A.pay_time < ".$pay_time_end;
        }
        if (!empty($params['ascription_vip'])) {
            $dayCountSql .= " and B.ascription_vip =".$params['ascription_vip'];
            $sellSql .= " and A.kf_id = ".$params['ascription_vip'];
        }else if (!empty($params['group_id'])) {
            //找到该组下的专员
            $adminList = SysServer::getAdminListByGroupIds($params['group_id'],2);
            $adminIds = array_column($adminList,'id');
            if (!empty($adminIds) && is_array($adminIds)) {
                $adminIdStr = "'".implode("','",$adminIds)."'";
                $dayCountSql .= " and B.ascription_vip in($adminIdStr)";
                $sellSql .= " and A.kf_id in($adminIdStr) B.ascription_vip in ($adminIdStr)";
            }else {
                $dayCountSql .= " and B.ascription_vip = -1";
                $sellSql .= " and A.kf_id = -1";
            }
        }else {
            $dayCountSql .= " and B.ascription_vip >0";
        }
        if ($params['p_p']) {
            $sellSql .= " and A.p_p in ('".str_replace(',',"','",$params['p_p'])."')";
            $PlatformGameInfo = new PlatformGameInfo();

            $this_where = [];
            $this_where['static'] = 1;
            $this_where[] = getWhereDataArr($params['p_p'],"concat(platform_id,'_',product_id)");

            $game_list = $PlatformGameInfo->field("platform_id,game_id")->where(setWhereSql($this_where,''))->select()->toArray();

            if (!empty($game_list)) {
                $tag = false;
                if (!empty($params['server_id_min'])) $tag = true;
                $tmpStr = '';
                foreach ($game_list as $v) {
                    if ($tag) {
                        $sellSql .= " and A.server_id = '".$params['server_id']."'";
                        $tmpStr.=$v['platform_id'].'_'.$v['game_id'].'_'.$params['server_id_min']."','";
                    }else{
                        $tmpStr.=$v['platform_id'].'_'.$v['game_id']."','";
                    }
                }
                $tmpStr = "'".trim($tmpStr,"','")."'";
                if ($tag) {
                    $dayCountSql .= " and A.p_g_s in($tmpStr)";
                }else {
                    $dayCountSql .= " and A.p_g in($tmpStr)";
                }
            }
        }

        $dayCountSql .= " GROUP BY A.date,B.ascription_vip ORDER BY A.date desc";
        $sellSql .= " GROUP BY date,A.kf_id ORDER BY date DESC";

        $sellModel = new SellWorkOrder();
        $sellDate = $sellModel->query($sellSql);

        $everyModel = new EveryDayOrderCount();
        $everyData = $everyModel->query($dayCountSql);

        $result = self::handleMarketingStageReport($everyData,$sellDate,$type);
        return $result;
    }

    public static function marketingStageReportConfig(){

        $config = [];
        $config['form_data']['pay_time_start'] = date('Y-m-01',time());
        $config['search_config'] = [
            'product'=>[
                'status'=>1,
                //'radio'=>1,
                'is_init'=>0,
            ],
            'platform'=>[
                'radio'=>1,
            ]
        ];

        return $config;
    }

    /**
     * @param array $everyData
     * @param array $sellDate
     * @param false $type
     * @return array
     */
    public static function handleMarketingStageReport($everyData = array(), $sellDate = array(), $type = false)
    {
        $data = $result = $res = [];
        if($everyData) foreach ($everyData as $val) {
            if (!empty($data[$val['date']][$val['ascription_vip']])) {
                $data[$val['date']][$val['ascription_vip']]['amount_count'] += $val['amount_count'];
                $data[$val['date']][$val['ascription_vip']]['order_count'] += $val['order_count'];
                $data[$val['date']][$val['ascription_vip']]['people_count'] += $val['people_count'];
            }else {
                $data[$val['date']][$val['ascription_vip']]['amount_count'] = $val['amount_count'];
                $data[$val['date']][$val['ascription_vip']]['order_count'] = $val['order_count'];
                $data[$val['date']][$val['ascription_vip']]['people_count'] = $val['people_count'];
                $data[$val['date']][$val['ascription_vip']]['sell_amount'] = 0;
                $data[$val['date']][$val['ascription_vip']]['sell_count'] = 0;
            }
        }

        if($sellDate) foreach ($sellDate as $val) {
            if (!empty($data[$val['date']][$val['ascription_vip']])) {
                $data[$val['date']][$val['ascription_vip']]['sell_amount'] += $val['sell_amount'];
                $data[$val['date']][$val['ascription_vip']]['sell_count'] += $val['sell_count'];
            }else {
                $data[$val['date']][$val['ascription_vip']]['amount_count'] = isset($data[$val['date']][$val['ascription_vip']]['amount_count']) ? $data[$val['date']][$val['ascription_vip']]['amount_count'] : 0;
                $data[$val['date']][$val['ascription_vip']]['order_count'] = isset($data[$val['date']][$val['ascription_vip']]['order_count']) ? $data[$val['date']][$val['ascription_vip']]['order_count'] : 0;
                $data[$val['date']][$val['ascription_vip']]['people_count'] = isset($data[$val['date']][$val['ascription_vip']]['people_count']) ? $data[$val['date']][$val['ascription_vip']]['people_count'] : 0;
                $data[$val['date']][$val['ascription_vip']]['sell_amount'] = $val['sell_amount'];
                $data[$val['date']][$val['ascription_vip']]['sell_count'] = $val['sell_count'];
            }
        }

        //获取后台用户列表
        $adminList = SysServer::getAdminListCache();

        $userGroupArr = SysServer::getAdminGroupList();

        if(!$data){
            return [];
        }

        if (empty($type)) {
            foreach ($data as $key=>$value) {
                foreach ($value as $ke=>$val) {
                    $tmpKey = '';
                    $tmpArr = explode(',',$adminList[$ke]['group_id']);
                    if (!empty($tmpArr)) {
                        foreach ($tmpArr as $k=>$v) {
                            if (!empty($userGroupArr[$v]) && $userGroupArr[$v]['type'] == QcConfig::USER_GROUP_VIP) {
                                $tmpKey =  $userGroupArr[$v]['name'];
                                break;
                            }
                        }
                        if (empty($tmpKey)) {
                            $tmpKey = !empty($userGroupArr[$tmpArr[0]]['name']) ? $userGroupArr[$tmpArr[0]]['name'] : '未知分组';
                        }
                    }
                    if (isset($res[$key][$tmpKey])) {
                        $res[$key][$tmpKey]['amount_count'] += $val['amount_count'];
                        $res[$key][$tmpKey]['order_count'] += $val['order_count'];
                        $res[$key][$tmpKey]['people_count'] += $val['people_count'];
                        $res[$key][$tmpKey]['sell_amount'] += $val['sell_amount'];
                        $res[$key][$tmpKey]['sell_count'] += $val['sell_count'];
                    }else {
                        $res[$key][$tmpKey]['amount_count'] = isset($val['amount_count']) ? $val['amount_count'] : 0;
                        $res[$key][$tmpKey]['order_count'] = isset($val['order_count']) ? $val['order_count'] : 0;
                        $res[$key][$tmpKey]['people_count'] = isset($val['people_count']) ? $val['people_count'] : 0;
                        $res[$key][$tmpKey]['sell_amount'] = isset($val['sell_amount']) ? $val['sell_amount'] : 0;
                        $res[$key][$tmpKey]['sell_count'] = isset($val['sell_count']) ? $val['sell_count'] : 0;
                    }

                }
            }
            $tmpCount = [
                'date'=>'汇总',
                'group_name'=>'--',
                'amount_count'=>0,
                'order_count'=>0,
                'people_count'=>0,
                'sell_amount'=>0,
                'ltv'=>0,
                'sell_amount_proportion'=>0,
                'not_sell_amount'=>0,
                'sell_count'=>0,
            ];

            foreach ($res as $key=>$val) {
                foreach ($val as $k=>$v) {
                    $tmpArray = [];
                    $tmpArray['date']= $key;
                    $tmpArray['group_name'] = $k;
                    $tmpArray['amount_count'] = $v['amount_count'];
                    $tmpArray['order_count'] = $v['order_count'];
                    $tmpArray['people_count'] = $v['people_count'];
                    $tmpArray['sell_amount'] = $v['sell_amount'];
                    $tmpArray['sell_count'] = $v['sell_count'];
                    $tmpArray['ltv'] = !empty($v['people_count']) ? sprintf("%.2f",$v['amount_count']/$v['people_count']) : 0;
                    $tmpArray['sell_amount_proportion'] = sprintf("%.4f",$v['sell_amount']/$v['amount_count'])*100;
                    $tmpArray['sell_amount_proportion'] .= "%";
                    $tmpArray['not_sell_amount'] = $v['amount_count'] - $v['sell_amount'];
                    $result[] = $tmpArray;

                    $tmpCount['amount_count'] += $v['amount_count'];
                    $tmpCount['order_count'] += $v['order_count'];
                    $tmpCount['people_count'] += $v['people_count'];
                    $tmpCount['sell_amount'] += $v['sell_amount'];
                    $tmpCount['sell_count'] += $v['sell_count'];
                }
            }
            $tmpCount['not_sell_amount'] = $tmpCount['amount_count'] - $tmpCount['sell_amount'];
            $tmpCount['ltv'] = !empty($tmpCount['people_count']) ? sprintf("%.2f",$tmpCount['amount_count']/$tmpCount['people_count']) : 0;

            $tmpCount['sell_amount_proportion'] = sprintf("%.4f",$tmpCount['sell_amount']/$tmpCount['amount_count'])*100;
            $tmpCount['sell_amount_proportion'] .= "%";
            array_push($result,$tmpCount);
        }else {
            $tmpCount = [
                'date'=>'汇总',
                'group_name'=>'--',
                'admin_name'=>'--',
                'amount_count'=>0,
                'order_count'=>0,
                'people_count'=>0,
                'sell_amount'=>0,
                'ltv'=>0,
                'sell_amount_proportion'=>0,
                'not_sell_amount'=>0,
                'sell_count'=>0,
            ];
            foreach ($data as $key=>$value) {
                foreach ($value as $k=>$v) {
                    $tmpArray = [];
                    $tmpArray['date']= $key;
                    $tmpArray['admin_name'] = $adminList[$k]['name'];
                    $tmpArray['amount_count'] = $v['amount_count'];
                    $tmpArray['order_count'] = $v['order_count'];
                    $tmpArray['people_count'] = $v['people_count'];
                    $tmpArray['sell_amount'] = $v['sell_amount'];
                    $tmpArray['sell_count'] = $v['sell_count'];
                    $tmpArray['ltv'] = !empty($v['people_count']) ? sprintf("%.2f",$v['amount_count']/$v['people_count']) : 0;
                    $tmpArray['sell_amount_proportion'] = sprintf("%.4f",$v['sell_amount']/$v['amount_count'])*100;
                    $tmpArray['sell_amount_proportion'] .= "%";
                    $tmpArray['not_sell_amount'] = $v['amount_count'] - $v['sell_amount'];

                    $tmpKey = '';
                    $tmpArr = explode(',',$adminList[$k]['group_id']);
                    if (!empty($tmpArr)) {
                        foreach ($tmpArr as $ke=>$val) {
                            if (!empty($userGroupArr[$val]) && $userGroupArr[$val]['type'] == QcConfig::USER_GROUP_VIP) {
                                $tmpKey =  $userGroupArr[$val]['name'];
                                break;
                            }
                        }
                        if (empty($tmpKey)) {
                            $tmpKey = !empty($userGroupArr[$tmpArr[0]]['name']) ? $userGroupArr[$tmpArr[0]]['name'] : '未知分组';
                        }
                    }
                    $tmpArray['group_name'] =$tmpKey;
                    $result[] = $tmpArray;

                    $tmpCount['amount_count'] += $v['amount_count'];
                    $tmpCount['order_count'] += $v['order_count'];
                    $tmpCount['people_count'] += $v['people_count'];
                    $tmpCount['sell_amount'] += $v['sell_amount'];
                    $tmpCount['sell_count'] += $v['sell_count'];
                }
            }
            $tmpCount['not_sell_amount'] = $tmpCount['amount_count'] - $tmpCount['sell_amount'];
            $tmpCount['ltv'] = !empty($tmpCount['people_count']) ? sprintf("%.2f",$tmpCount['amount_count']/$tmpCount['people_count']) : 0;
            $tmpCount['sell_amount_proportion'] = sprintf("%.4f",$tmpCount['sell_amount']/$tmpCount['amount_count'])*100;
            $tmpCount['sell_amount_proportion'] .= "%";
            array_push($result,$tmpCount);
        }

        return $result;
    }

    public static function marketingStageReportPersonalConfig(){
        $config = [];
        $config['form_data']['pay_time_start'] = date('Y-m-01',time());
        $config['search_config'] = [
            'product'=>[
                'status'=>1,
                //'radio'=>1,
                'is_init'=>0,
            ],
            'platform'=>[
                'radio'=>1,
            ],
            'admin'=>[
                'status'=>1,
                'radio'=>1,
                'name'=>'ascription_vip',
                'p_data'=>[
                    'group_type'=>[QcConfig::USER_GROUP_VIP],
                    'is_active'=>1,
                    'not_group_type'=>[QcConfig::USER_GROUP_QC],
                ],
            ]
        ];

        return $config;
    }

    /**
     * @param array $params
     * @param false $type
     * @return array
     */
    public static function getMarketingStageReportRecharge($params = array())
    {
        $result = [];
        $type = getArrVal($params,'type',0);
        $platform_id = getArrVal($params,'platform_id',0);
        $pay_time_start = $pay_time_end = 0;
        if (!empty($params['pay_time_start'])) $pay_time_start = strtotime($params['pay_time_start']);
        if (!empty($params['pay_time_end'])) $pay_time_end = strtotime($params['pay_time_end']);

        if (!empty($pay_time_end) && $pay_time_end < $pay_time_start) return $result;
        if (empty($platform_id)) return $result;

        $dayCountSql = "SELECT
                            C.ascription_vip,
                            C.amount_range,
                            SUM(C.amount_count) amount_count,
                            COUNT(DISTINCT C.p_u) AS people_count,
                            COUNT(1) AS order_count
                        FROM
                            (
                                SELECT
                                    B.ascription_vip,
                                    A.p_g,
                                    A.p_u,
                                    SUM(A.amount_count) AS amount_count,
                                    CASE
                                WHEN 0 <= SUM(A.amount_count)
                                AND SUM(A.amount_count) < 100 THEN
                                    1
                                WHEN 100 <= SUM(A.amount_count)
                                AND SUM(A.amount_count) < 500 THEN
                                    2
                                WHEN 500 <= SUM(A.amount_count)
                                AND SUM(A.amount_count) < 1000 THEN
                                    3
                                WHEN 1000 <= SUM(A.amount_count)
                                AND SUM(A.amount_count) < 2000 THEN
                                    4
                                WHEN 2000 <= SUM(A.amount_count)
                                AND SUM(A.amount_count) < 5000 THEN
                                    5
                                WHEN 5000 <= SUM(A.amount_count)
                                AND SUM(A.amount_count) < 10000 THEN
                                    6
                                WHEN 10000 <= SUM(A.amount_count)
                                AND SUM(A.amount_count) < 50000 THEN
                                    7
                                WHEN 50000 <= SUM(A.amount_count)
                                AND SUM(A.amount_count) < 100000 THEN
                                    8
                                WHEN SUM(A.amount_count) > 100000 THEN
                                    9
                                END AS amount_range
                                FROM
                                    every_day_order_count AS A
                                INNER JOIN vip_user_info AS B ON A.platform_id = B.platform_id
                                AND A.uid = B.uid
                                WHERE
                                    A.date >= FROM_UNIXTIME(
                                        B.first_distribute_time,
                                        '%Y-%m-%d'
                                    ) AND A.platform_id = $platform_id";

        $sellSql = "SELECT
                        SUM(sell_amount) as sell_amount,
                        COUNT(1) AS order_count,
                        COUNT(DISTINCT p_u) AS people_count
                    FROM
                        `sell_work_order` AS A
                    INNER JOIN vip_user_info AS B ON A.platform_id = B.platform_id
                    AND A.uid = B.uid
                    WHERE
                        A.pay_time >= B.first_distribute_time
                        AND A.sell_amount >=1000 
                        and  A.type = ".SellWorkOrder::SELL_ORDER."
                    AND A.STATUS > 0 AND A.platform_id = $platform_id";

        if(self::$user_data['is_admin'] == 0){
            $where = [];
            $where[] = getWhereDataArr(self::$user_data['platform_id'],'B.platform_id');
            $dayCountSql.= setWhereSql($where,' AND ');
            $sellSql.= setWhereSql($where,' AND ');
        }

        if (!empty($pay_time_start)) {
            $dayCountSql .= " and A.date >='".$params['pay_time_start']."'";
            $sellSql .= " and A.pay_time >= ".$pay_time_start;
        }
        if (!empty($params['pay_time_end'])) {
            $dayCountSql .= " and A.date <'".$params['pay_time_end']."'";
            $sellSql .= " and A.pay_time < ".$pay_time_end;
        }
        if (!empty($params['ascription_vip'])) {
            $dayCountSql .= " and B.ascription_vip =".$params['ascription_vip'];
            $sellSql .= " and A.kf_id = ".$params['ascription_vip'];
        }else if (!empty($params['group_id'])) {
            //找到该组下的专员
            $adminList = SysServer::getAdminListByGroupIds($params['group_id'],2);
            $adminIds = array_column($adminList,'id');
            if (!empty($adminIds) && is_array($adminIds)) {
                $adminIdStr = "'".implode("','",$adminIds)."'";
                $dayCountSql .= " and B.ascription_vip in($adminIdStr)";
                $sellSql .= " and A.kf_id in($adminIdStr) and B.ascription_vip in ($adminIdStr)";
            }else {
                $dayCountSql .= " and B.ascription_vip = -1";
                $sellSql .= " and A.kf_id = -1";
            }
        }else {
            $dayCountSql .= " and B.ascription_vip >0";
        }
        if ($params['p_p']) {
            $sellSql .= " and p_p in ('".str_replace(',',"','",$params['p_p'])."')";

            $PlatformGameInfo = new PlatformGameInfo();

            $this_where = [];
            $this_where['static'] = 1;
            $this_where[] = getWhereDataArr($params['p_p'],"concat(platform_id,'_',product_id)");

            $game_list = $PlatformGameInfo->field("platform_id,game_id")->where(setWhereSql($this_where,''))->select()->toArray();

            if (!empty($game_list)) {
                $tag = false;
                if (!empty($params['server_id_min'])) $tag = true;
                $tmpStr = '';
                foreach ($game_list as $v) {
                    if ($tag) {
                        $sellSql .= " and A.server_id = '".$params['server_id']."'";
                        $tmpStr.=$v['platform_id'].'_'.$v['game_id'].'_'.$params['server_id_min']."','";
                    }else{
                        $tmpStr.=$v['platform_id'].'_'.$v['game_id']."','";
                    }
                }
                $tmpStr = "'".trim($tmpStr,"','")."'";
                if ($tag) {
                    $dayCountSql .= " and A.p_g_s in($tmpStr)";
                }else {
                    $dayCountSql .= " and A.p_g in($tmpStr)";
                }
            }else{
                $dayCountSql .= " and A.p_g_s =-1";
            }
        }

        $dayCountSql .= " GROUP BY
                                    A.date,
                                    A.p_u
                            ) AS C
                        GROUP BY
                            C.ascription_vip,
                            C.amount_range
                        ORDER BY
                            C.amount_range ASC";

        $sellModel = new SellWorkOrder();
        $sellDate = $sellModel->query($sellSql);

        $everyModel = new EveryDayOrderCount();
        $everyData = $everyModel->query($dayCountSql);

        $result = self::handleMarketingStageReportRecharge($everyData,$sellDate,$type);
        return $result;
    }

    /**
     * @param array $everyData
     * @param array $sellDate
     * @param false $type
     * @return array
     */
    public static function handleMarketingStageReportRecharge($everyData = array(), $sellDate = array(), $type = false)
    {
        $data = $result = [];

        if(!$everyData) return $result;

        $amountSum = 0;
        $adminList = SysServer::getAdminListCache();

        $userGroupArr = SysServer::getAdminGroupList();

        $ltOneCount = $gtOneCount = $vipGtOneCount = $totalCount = [
            'group_name'=>'汇总',
            //                'product_name'=>'__',
            'amount_range'=>'_',
            'amount_count'=>0,
            'order_count'=>0,
            'people_count'=>0,
            'amount_proportion'=>0
        ];
        $ltOneCount['group_name'] = '汇总(小于1K)';
        $gtOneCount['group_name'] = '汇总(1K以上)';
        $vipGtOneCount['group_name'] = '汇总(营销1K以上)';

        if (empty($type)) {
            if($everyData) foreach ($everyData as $val) {
                $amountSum += $val['amount_count'];
                $totalCount['amount_count'] += $val['amount_count'];
                $totalCount['order_count'] += $val['order_count'];
                $totalCount['people_count'] += $val['people_count'];
                if ($val['amount_range'] >= 4) {
                    $gtOneCount['amount_count'] += $val['amount_count'];
                    $gtOneCount['order_count'] += $val['order_count'];
                    $gtOneCount['people_count'] += $val['people_count'];
                }else {
                    $ltOneCount['amount_count'] += $val['amount_count'];
                    $ltOneCount['order_count'] += $val['order_count'];
                    $ltOneCount['people_count'] += $val['people_count'];
                }

                $tmpKey = '';
                $tmpArr = explode(',',$adminList[$val['ascription_vip']]['group_id']);
                if (!empty($tmpArr)) {
                    foreach ($tmpArr as $k=>$v) {
                        if (!empty($userGroupArr[$v]) && $userGroupArr[$v]['type'] == QcConfig::USER_GROUP_VIP) {
                            $tmpKey =  $userGroupArr[$v]['name'];
                            break;
                        }
                    }
                    if (empty($tmpKey)) {
                        $tmpKey = !empty($userGroupArr[$tmpArr[0]]['name']) ? $userGroupArr[$tmpArr[0]]['name'] : '未知分组';
                    }
                }

                if (!empty($data[$val['amount_range']][$tmpKey])) {
                    $data[$val['amount_range']][$tmpKey]['amount_count'] += $val['amount_count'];
                    $data[$val['amount_range']][$tmpKey]['order_count'] += $val['order_count'];
                    $data[$val['amount_range']][$tmpKey]['people_count'] += $val['people_count'];
                }else {
                    $data[$val['amount_range']][$tmpKey]['amount_count'] = $val['amount_count'];
                    $data[$val['amount_range']][$tmpKey]['order_count'] = $val['order_count'];
                    $data[$val['amount_range']][$tmpKey]['people_count'] = $val['people_count'];
                }
            }

            foreach ($data as $key=>$value) {
                foreach ($value as $k=>$v) {
                    $tmpArray = [
                        'group_name'=>$k,
                        //                    'product_name'=>'__',
                        'amount_range'=>self::$recharge_range[$key],
                        'amount_count'=>$v['amount_count'],
                        'order_count'=>$v['order_count'],
                        'people_count'=>$v['people_count'],
                        'amount_proportion'=>sprintf("%.4f",$v['amount_count']/$amountSum)*100
                    ];
                    $tmpArray['amount_proportion'] .= "%";
                    $result[] = $tmpArray;
                }
            }

            $ltOneCount['amount_proportion'] = sprintf("%.4f",$ltOneCount['amount_count']/$amountSum)*100;
            $ltOneCount['amount_proportion'] .= "%";
            array_push($result,$ltOneCount);
            $gtOneCount['amount_proportion'] = sprintf("%.4f",$gtOneCount['amount_count']/$amountSum)*100;
            $gtOneCount['amount_proportion'] .= "%";
            array_push($result,$gtOneCount);
            $vipGtOneCount['amount_count'] = $sellDate[0]['sell_amount'];
            $vipGtOneCount['order_count'] = $sellDate[0]['order_count'];
            $vipGtOneCount['people_count'] = $sellDate[0]['people_count'];
            $vipGtOneCount['amount_proportion'] = sprintf("%.4f",$sellDate[0]['sell_amount']/$amountSum)*100;
            $vipGtOneCount['amount_proportion'] .= "%";
            array_push($result,$vipGtOneCount);
            $totalCount['amount_proportion'] = sprintf("%.4f",$totalCount['amount_count']/$amountSum)*100;
            $totalCount['amount_proportion'] .= "%";
            array_push($result,$totalCount);
        }else {
            $ltOneCount['admin_name'] = '-';
            $gtOneCount['admin_name'] = '-';
            $vipGtOneCount['admin_name'] = '-';
            $totalCount['admin_name'] = '-';

            foreach ($everyData as $val) {
                $amountSum += $val['amount_count'];
                $totalCount['amount_count'] += $val['amount_count'];
                $totalCount['order_count'] += $val['order_count'];
                $totalCount['people_count'] += $val['people_count'];
                if ($val['amount_range'] >= 4) {
                    $gtOneCount['amount_count'] += $val['amount_count'];
                    $gtOneCount['order_count'] += $val['order_count'];
                    $gtOneCount['people_count'] += $val['people_count'];
                } else {
                    $ltOneCount['amount_count'] += $val['amount_count'];
                    $ltOneCount['order_count'] += $val['order_count'];
                    $ltOneCount['people_count'] += $val['people_count'];
                }
            }
            foreach ($everyData as $val) {

                $val['admin_name'] =$adminList[$val['ascription_vip']]['name'];

                $tmpKey = '';
                $tmpArr = explode(',',$adminList[$val['ascription_vip']]['group_id']);
                if (!empty($tmpArr)) {
                    foreach ($tmpArr as $k=>$v) {
                        if (!empty($userGroupArr[$v]) && $userGroupArr[$v]['type'] == QcConfig::USER_GROUP_VIP) {
                            $tmpKey =  $userGroupArr[$v]['name'];
                            break;
                        }
                    }
                    if (empty($tmpKey)) {
                        $tmpKey = !empty($userGroupArr[$tmpArr[0]]['name']) ? $userGroupArr[$tmpArr[0]]['name'] : '未知分组';
                    }
                }

                $val['amount_range'] = self::$recharge_range[$val['amount_range']];
                $val['group_name'] = $tmpKey;
                $val['amount_proportion'] = sprintf("%.4f",$val['amount_count']/$amountSum)*100;
                $val['amount_proportion'] .= "%";
                $result[] = $val;
            }

            $ltOneCount['amount_proportion'] = sprintf("%.4f",$ltOneCount['amount_count']/$amountSum)*100;
            $ltOneCount['amount_proportion'] .= "%";
            array_push($result,$ltOneCount);
            $gtOneCount['amount_proportion'] = sprintf("%.4f",$gtOneCount['amount_count']/$amountSum)*100;
            $gtOneCount['amount_proportion'] .= "%";
            array_push($result,$gtOneCount);
            $vipGtOneCount['amount_count'] = $sellDate[0]['sell_amount'];
            $vipGtOneCount['order_count'] = $sellDate[0]['order_count'];
            $vipGtOneCount['people_count'] = $sellDate[0]['people_count'];
            $vipGtOneCount['amount_proportion'] = sprintf("%.4f",$sellDate[0]['sell_amount']/$amountSum)*100;
            $vipGtOneCount['amount_proportion'] .= "%";
            array_push($result,$vipGtOneCount);
            $totalCount['amount_proportion'] = sprintf("%.4f",$totalCount['amount_count']/$amountSum)*100;
            $totalCount['amount_proportion'] .= "%";
            array_push($result,$totalCount);
        }
        return $result;
    }

    public static function marketingStageReportRechargeConfig(){
        $config = [];
        $config['form_data']['pay_time_start'] = date('Y-m-01',time());
        $config['search_config'] = [
            'product'=>[
                'status'=>1,
                //'radio'=>1,
                'is_init'=>0,
            ],
            'platform'=>[
                'radio'=>1,
            ],
//            'admin'=>[
//                'status'=>1,
//                'radio'=>1,
//                'name'=>'ascription_vip',
//                'p_data'=>[
//                    'group_type'=>[QcConfig::USER_GROUP_VIP],
//                    'is_active'=>1,
//                    'not_group_type'=>[QcConfig::USER_GROUP_QC],
//                ],
//            ]
        ];

        return $config;
    }

    public static function marketingStageReportRechargePersonalConfig(){
        $config = [];
        $config['form_data']['pay_time_start'] = date('Y-m-01',time());
        $config['search_config'] = [
            'product'=>[
                'status'=>1,
                //'radio'=>1,
                'is_init'=>0,
            ],
            'platform'=>[
                'radio'=>1,
            ],
            'admin'=>[
                'status'=>1,
                'radio'=>1,
                'name'=>'ascription_vip',
                'p_data'=>[
                    'group_type'=>[QcConfig::USER_GROUP_VIP],
                    'is_active'=>1,
                    'not_group_type'=>[QcConfig::USER_GROUP_QC],
                ],
            ]
        ];

        return $config;
    }

    /**
     * @param array $params
     * @param false $type
     * @return array
     */
    public static function getMarketingStageReportTime($params = array())
    {
        $result = ['data'=>[],'msg'=>''];
        $type = getArrVal($params,'type',0);
        $pay_time_start = $pay_time_end = 0;
        if (!empty($params['pay_time_start'])) $pay_time_start = strtotime($params['pay_time_start']);
        if (!empty($params['pay_time_end'])) $pay_time_end = strtotime($params['pay_time_end']);

        if (!empty($pay_time_end) && $pay_time_end < $pay_time_start) return $result;
        $tmpTime = !empty($pay_time_end) ? $pay_time_end : time();
        if (($tmpTime - $pay_time_start) > 86400*31) {
            $result['msg'] = '请选择时间范围不超过30天！！';
            return $result;
        }
        $platform_id = !empty($params['platform_id']) ? $params['platform_id'] : 0;
        if (empty($platform_id)) return $result;
        //获取平台id对应的平台标识
        $platform_list = SysServer::getPlatformList();

        $platformInfo = getArrVal($platform_list,$platform_id,[]);

        if (empty($platformInfo)) return $result;
        $suffix = $platformInfo['suffix'];

        $dbNameA = "db_customer_".$suffix;
        //判断库A是否存在
        $exitDbSql = "show databases like '$dbNameA'";
        $vipUserModel = new VipUserInfo();
        if (empty($vipUserModel->query($exitDbSql))) return $result;

        $tableA = "db_statistic.vip_user_info";
        $tableB = $dbNameA.".kefu_pay_order";



        $timeCountSql = "SELECT
                            FROM_UNIXTIME(B.pay_time, '%H') AS hours,
                            SUM(B.amount) AS amount_count,
                            COUNT(1) AS order_count,
                            COUNT(DISTINCT B.uid) AS people_count,
                            A.ascription_vip
                        FROM
                        $tableA AS A
                        INNER JOIN $tableB AS B ON A.uid = B.uid
                        WHERE 
                        A.ascription_vip > 0 AND A.platform_id = $platform_id";

        $sellSql = "SELECT
                        A.kf_id as ascription_vip,
                        SUM(A.sell_amount) AS sell_amount,
                        COUNT(1) as  sell_count,
                        FROM_UNIXTIME(A.pay_time, '%H') AS hours
                    FROM
                        `sell_work_order` AS A
                    INNER JOIN vip_user_info AS B ON A.platform_id = B.platform_id
                    AND A.uid = B.uid
                    WHERE
                        A.pay_time >= B.first_distribute_time
                        and  A.type = ".SellWorkOrder::SELL_ORDER."
                    AND A.STATUS > 0 AND A.platform_id = $platform_id";

        if(self::$user_data['is_admin'] == 0){
            $where = [];
            $where[] = getWhereDataArr(self::$user_data['platform_id'],'B.platform_id');
            $sellSql.= str_replace('WHERE','AND',setWhereSql($where));
        }

        if (!empty($pay_time_start)) {
            $timeCountSql .= " and B.pay_time >=".$pay_time_start;
            $sellSql .= " and A.pay_time >= ".$pay_time_start;
        }
        if (!empty($params['pay_time_end'])) {
            $timeCountSql .= " and B.pay_time <".$pay_time_end;
            $sellSql .= " and A.pay_time < ".$pay_time_end;
        }
        if (!empty($params['ascription_vip'])) {
            $timeCountSql .= " and A.ascription_vip =".$params['ascription_vip'];
            $sellSql .= " and A.kf_id = ".$params['ascription_vip'];
        }else if (!empty($params['group_id'])) {
            //找到该组下的专员
            $adminList = SysServer::getAdminListByGroupIds($params['group_id'],2);
            $adminIds = array_column($adminList,'id');
            if (!empty($adminIds) && is_array($adminIds)) {
                $adminIdStr = "'".implode("','",$adminIds)."'";
                $timeCountSql .= " and A.ascription_vip in($adminIdStr)";
                $sellSql .= " and A.kf_id in($adminIdStr) and B.ascription_vip in ($adminIdStr) ";
            }else {
                $timeCountSql .= " and A.ascription_vip = -1";
                $sellSql .= " and A.kf_id = -1";
            }
        }else {
            $timeCountSql .= " and A.ascription_vip >0";
        }
        if ($params['p_p']) {
            //$sellSql .= " and A.p_p = '".$params['p_p']."'";
            $sellSql .= " and A.p_p in ('".str_replace(',',"','",$params['p_p'])."')";
            $timeCountSql .= " and A.platform_id = ".$platform_id;
            $PlatformGameInfo = new PlatformGameInfo();

            $this_where = [];
            $this_where['static'] = 1;
            $this_where[] = getWhereDataArr($params['p_p'],"concat(platform_id,'_',product_id)");

            $game_list = $PlatformGameInfo->field("platform_id,game_id")->where(setWhereSql($this_where,''))->select()->toArray();

            $gameArr = [];
            if (!empty($game_list)) {
                $gameArr = array_column($game_list,'game_id');
            }
            if (empty($gameArr) || !is_array($gameArr)) return $result;
            $gameStr = implode(',',$gameArr);
            $timeCountSql .= " and B.gid in ($gameStr)";
        }

        if (!empty($params['server_id_min'])) {
            $sellSql .= " and A.server_id = '".$params['server_id']."'";
            $timeCountSql .= " and B.server_id = ".$params['server_id_min'];
        }

        $timeCountSql .= " GROUP BY hours,A.ascription_vip ORDER BY hours ASC";
        $sellSql .= " GROUP BY hours,A.kf_id ORDER BY hours ASC";

        $sellModel = new SellWorkOrder();
        $sellDate = $sellModel->query($sellSql);

        $vipUserModel = new VipUserInfo();
        $everyTime = $vipUserModel->query($timeCountSql);

        $result['data'] = self::handleMarketingStageReportTime($everyTime,$sellDate,$type);
        return $result;
    }

    /**
     * @param array $everyTime
     * @param array $sellDate
     * @param false $type
     * @return array
     */
    public static function handleMarketingStageReportTime($everyTime = array(), $sellDate = array(), $type = false)
    {
        $data = $result = $res = [];

        if($everyTime) foreach ($everyTime as $val) {
            if (isset($data[$val['hours']][$val['ascription_vip']])) {
                $data[$val['hours']][$val['ascription_vip']]['amount_count'] += $val['amount_count'];
                $data[$val['hours']][$val['ascription_vip']]['order_count'] += $val['order_count'];
                $data[$val['hours']][$val['ascription_vip']]['people_count'] += $val['people_count'];
            }else {
                $data[$val['hours']][$val['ascription_vip']]['amount_count'] = $val['amount_count'];
                $data[$val['hours']][$val['ascription_vip']]['order_count'] = $val['order_count'];
                $data[$val['hours']][$val['ascription_vip']]['people_count'] = $val['people_count'];
                $data[$val['hours']][$val['ascription_vip']]['sell_amount'] = 0;
                $data[$val['hours']][$val['ascription_vip']]['sell_count'] = 0;
            }
        }

        if($sellDate) foreach ($sellDate as $val) {
            if (isset($data[$val['hours']][$val['ascription_vip']])) {
                $data[$val['hours']][$val['ascription_vip']]['sell_amount'] += $val['sell_amount'];
                $data[$val['hours']][$val['ascription_vip']]['sell_count'] += $val['sell_count'];
            }else {
                $data[$val['hours']][$val['ascription_vip']]['amount_count'] = isset($data[$val['date']][$val['ascription_vip']]['amount_count']) ? $data[$val['date']][$val['ascription_vip']]['amount_count'] : 0;
                $data[$val['hours']][$val['ascription_vip']]['order_count'] = isset($data[$val['date']][$val['ascription_vip']]['order_count']) ? $data[$val['date']][$val['ascription_vip']]['order_count'] : 0;
                $data[$val['hours']][$val['ascription_vip']]['people_count'] = isset($data[$val['date']][$val['ascription_vip']]['people_count']) ? $data[$val['date']][$val['ascription_vip']]['people_count'] : 0;
                $data[$val['hours']][$val['ascription_vip']]['sell_amount'] = $val['sell_amount'];
                $data[$val['hours']][$val['ascription_vip']]['sell_count'] = $val['sell_count'];
            }
        }

        if(!$data){
            return [];
        }

        //获取后台用户列表
        $adminList = SysServer::getAdminListCache();
        $userGroupArr = SysServer::getAdminGroupList();

        if (empty($type)) {
            foreach ($data as $key=>$value) {
                foreach ($value as $ke=>$val) {
                    $tmpKey = '';
                    $tmpArr = explode(',',$adminList[$ke]['group_id']);
                    if (!empty($tmpArr)) {
                        foreach ($tmpArr as $k=>$v) {
                            if (!empty($userGroupArr[$v]) && $userGroupArr[$v]['type'] == QcConfig::USER_GROUP_VIP) {
                                $tmpKey =  $userGroupArr[$v]['name'];
                                break;
                            }
                        }
                        if (empty($tmpKey)) {
                            $tmpKey = !empty($userGroupArr[$tmpArr[0]]['name']) ? $userGroupArr[$tmpArr[0]]['name'] : '未知分组';
                        }
                    }
                    if (isset($res[$key][$tmpKey])) {
                        $res[$key][$tmpKey]['amount_count'] += $val['amount_count'];
                        $res[$key][$tmpKey]['order_count'] += $val['order_count'];
                        $res[$key][$tmpKey]['people_count'] += $val['people_count'];
                        $res[$key][$tmpKey]['sell_amount'] += $val['sell_amount'];
                        $res[$key][$tmpKey]['sell_count'] += $val['sell_count'];
                    }else {
                        $res[$key][$tmpKey]['amount_count'] = isset($val['amount_count']) ? $val['amount_count'] : 0;
                        $res[$key][$tmpKey]['order_count'] = isset($val['order_count']) ? $val['order_count'] : 0;
                        $res[$key][$tmpKey]['people_count'] = isset($val['people_count']) ? $val['people_count'] : 0;
                        $res[$key][$tmpKey]['sell_amount'] = isset($val['sell_amount']) ? $val['sell_amount'] : 0;
                        $res[$key][$tmpKey]['sell_count'] = isset($val['sell_count']) ? $val['sell_count'] : 0;
                    }

                }
            }
            $tmpCount = [
                'hours'=>'汇总',
                'group_name'=>'--',
                'amount_count'=>0,
                'order_count'=>0,
                'people_count'=>0,
                'sell_amount'=>0,
                'ltv'=>0,
                'sell_amount_proportion'=>0,
                'not_sell_amount'=>0,
                'sell_count'=>0,
            ];
            foreach ($res as $key=>$val) {
                foreach ($val as $k=>$v) {
                    $tmpArray = [];
                    $tmpArray['hours']= self::$hour_phase[$key];
                    $tmpArray['group_name'] = $k;
                    $tmpArray['amount_count'] = $v['amount_count'];
                    $tmpArray['order_count'] = $v['order_count'];
                    $tmpArray['people_count'] = $v['people_count'];
                    $tmpArray['sell_amount'] = $v['sell_amount'];
                    $tmpArray['sell_count'] = $v['sell_count'];
                    $tmpArray['ltv'] = !empty($v['people_count']) ? sprintf("%.2f",$v['amount_count']/$v['people_count']) : 0;
                    $tmpArray['sell_amount_proportion'] = sprintf("%.4f",$v['sell_amount']/$v['amount_count'])*100;
                    $tmpArray['sell_amount_proportion'] .= "%";
                    $tmpArray['not_sell_amount'] = $v['amount_count'] - $v['sell_amount'];
                    $result[] = $tmpArray;

                    $tmpCount['amount_count'] += $v['amount_count'];
                    $tmpCount['order_count'] += $v['order_count'];
                    $tmpCount['people_count'] += $v['people_count'];
                    $tmpCount['sell_amount'] += $v['sell_amount'];
                    $tmpCount['sell_count'] += $v['sell_count'];
                }
            }
            $tmpCount['not_sell_amount'] = $tmpCount['amount_count'] - $tmpCount['sell_amount'];
            $tmpCount['ltv'] = !empty($tmpCount['people_count']) ? sprintf("%.2f",$tmpCount['amount_count']/$tmpCount['people_count']) : 0;
            $tmpCount['sell_amount_proportion'] = sprintf("%.4f",$tmpCount['sell_amount']/$tmpCount['amount_count'])*100;
            $tmpCount['sell_amount_proportion'] .= "%";
            array_push($result,$tmpCount);
        }else {
            $tmpCount = [
                'hours'=>'汇总',
                'group_name'=>'--',
                'admin_name'=>'--',
                'amount_count'=>0,
                'order_count'=>0,
                'people_count'=>0,
                'sell_amount'=>0,
                'ltv'=>0,
                'sell_amount_proportion'=>0,
                'not_sell_amount'=>0,
                'sell_count'=>0,
            ];
            foreach ($data as $key=>$value) {
                foreach ($value as $k=>$v) {
                    $tmpArray = [];
                    $tmpArray['hours']= self::$hour_phase[$key];
                    $tmpArray['admin_name'] = $adminList[$k]['name'];
                    $tmpArray['amount_count'] = $v['amount_count'];
                    $tmpArray['order_count'] = $v['order_count'];
                    $tmpArray['people_count'] = $v['people_count'];
                    $tmpArray['sell_amount'] = $v['sell_amount'];
                    $tmpArray['sell_count'] = $v['sell_count'];
                    $tmpArray['ltv'] = !empty($v['people_count']) ? sprintf("%.2f",$v['amount_count']/$v['people_count']) : 0;
                    $tmpArray['sell_amount_proportion'] = sprintf("%.4f",$v['sell_amount']/$v['amount_count'])*100;
                    $tmpArray['sell_amount_proportion'] .= "%";
                    $tmpArray['not_sell_amount'] = $v['amount_count'] - $v['sell_amount'];

                    $tmpKey = '';
                    $tmpArr = explode(',',$adminList[$k]['group_id']);
                    if (!empty($tmpArr)) {
                        foreach ($tmpArr as $ke=>$val) {
                            if (!empty($userGroupArr[$val]) && $userGroupArr[$val]['type'] == QcConfig::USER_GROUP_VIP) {
                                $tmpKey =  $userGroupArr[$val]['name'];
                                break;
                            }
                        }
                        if (empty($tmpKey)) {
                            $tmpKey = !empty($userGroupArr[$tmpArr[0]]['name']) ? $userGroupArr[$tmpArr[0]]['name'] : '未知分组';
                        }
                    }
                    $tmpArray['group_name'] =$tmpKey;
                    $result[] = $tmpArray;

                    $tmpCount['amount_count'] += $v['amount_count'];
                    $tmpCount['order_count'] += $v['order_count'];
                    $tmpCount['people_count'] += $v['people_count'];
                    $tmpCount['sell_amount'] += $v['sell_amount'];
                    $tmpCount['sell_count'] += $v['sell_count'];
                }
            }
            $tmpCount['not_sell_amount'] = $tmpCount['amount_count'] - $tmpCount['sell_amount'];
            $tmpCount['ltv'] = !empty($tmpCount['people_count']) ? sprintf("%.2f",$tmpCount['amount_count']/$tmpCount['people_count']) : 0;
            $tmpCount['sell_amount_proportion'] = sprintf("%.4f",$tmpCount['sell_amount']/$tmpCount['amount_count'])*100;
            $tmpCount['sell_amount_proportion'] .= "%";
            array_push($result,$tmpCount);
        }

        return $result;
    }

    public static function marketingStageReportTimeConfig(){
        $config = [];
        $config['form_data']['pay_time_start'] = date('Y-m-01',time());
        $config['search_config'] = [
            'product'=>[
                'status'=>1,
//                'radio'=>1,
                'is_init'=>0,
            ],
            'platform'=>[
                'radio'=>1,
            ],
//            'admin'=>[
//                'status'=>1,
//                'radio'=>1,
//                'name'=>'ascription_vip',
//                'p_data'=>[
//                    'group_type'=>[QcConfig::USER_GROUP_VIP],
//                    'is_active'=>1,
//                    'not_group_type'=>[QcConfig::USER_GROUP_QC],
//                ],
//            ]
        ];

        return $config;
    }

    public static function marketingStageReportTimePersonalConfig(){
        $config = [];
        $config['form_data']['pay_time_start'] = date('Y-m-01',time());
        $config['search_config'] = [
            'product'=>[
                'status'=>1,
//                'radio'=>1,
                'is_init'=>0,
            ],
            'platform'=>[
                'radio'=>1,
            ],
            'admin'=>[
                'status'=>1,
                'radio'=>1,
                'name'=>'ascription_vip',
                'p_data'=>[
                    'group_type'=>[QcConfig::USER_GROUP_VIP],
                    'is_active'=>1,
                    'not_group_type'=>[QcConfig::USER_GROUP_QC],
                ],
            ]
        ];

        return $config;
    }
}
