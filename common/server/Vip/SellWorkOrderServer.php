<?php
/**
 * 系统
 */
namespace common\server\Vip;

use common\base\BasicServer;
use common\model\db_customer\QcConfig;
use common\model\db_statistic\SellerKpiConfig;
use common\model\db_statistic\SellWorkOrder;
use common\model\db_statistic\SellWorkOrderPayOrder;
use common\model\db_statistic\SellWorkOrderStatisticMonth;
use common\model\db_statistic\VipKfDayStatistic;
use common\model\db_statistic\VipUserInfo;
use common\server\AdminServer;
use common\server\CustomerPlatform\CommonServer;
use common\server\ListActionServer;
use common\server\Statistic\GameProductServer;
use common\server\SysServer;
use common\model\db_statistic\PlatformGameInfo;


class SellWorkOrderServer extends BasicServer
{

    public static function sellWorkOrderList($param){

        $result = self::getSellWorkOrderListData($param);

        $list = $result['data'];

        if($list){

            $type_arr = SellWorkOrder::$type_arr;// 工单类型
            $sell_type_arr = SellWorkOrder::$sell_type_arr;// 销售类型
            $status_arr = SellWorkOrder::$status_arr;// 审核状态

            $game_list = SysServer::getGameProductCache();
            $game_list_new  = [];

            foreach ($game_list as $k => $v) {
                $game_list_new[$v['id_str']] = $v['name'];
            }
            $admin_list = SysServer::getAdminListCache();

            $user_id_arr = [];
            foreach ($list as $k => &$v) {

                $v['action'] = ListActionServer::checkSellWorkOrderAction($v);

                $v['type_str'] = getArrVal($type_arr,$v['type'],'未知类型');
                $v['sell_type_str'] = getArrVal($sell_type_arr,$v['sell_type'],'无');
                $v['kf_id_str'] = isset($admin_list[$v['kf_id']])?$admin_list[$v['kf_id']]['name']:'未知';
                $v['status_str'] = getArrVal($status_arr,$v['status'],'未知');

                $v['qc_first_admin_id_str'] = isset($admin_list[$v['qc_first_admin_id']])?$admin_list[$v['qc_first_admin_id']]['name']:'';
                $v['qc_second_admin_id_str'] = isset($admin_list[$v['qc_second_admin_id']])?$admin_list[$v['qc_second_admin_id']]['name']:'';

                $this_game_str = $v['platform_id'].'_'.$v['product_id'];

                $v['product_id_str'] = isset($game_list_new[$this_game_str])?$game_list_new[$this_game_str]:'未知';

                $v['qc_first_time_str'] = $v['qc_first_time']?date('Y-m-d H:i',$v['qc_first_time']):'';
                $v['qc_second_time_str'] = $v['qc_second_time']?date('Y-m-d H:i',$v['qc_second_time']):'';
                $v['pay_time_str'] = $v['pay_time']?date('Y-m-d H:i',$v['pay_time']):'';

                $this_pu_str = $v['platform_id'].'_'.$v['uid'];

                $v['pu_str'] = $this_pu_str;

                $user_id_arr[$this_pu_str] = $this_pu_str;

            }

            if($user_id_arr){
                $VipUserInfo = new VipUserInfo();

                $where = [];
                $where['_string'] = 'CONCAT(platform_id,"_",uid) IN("'.implode('","', $user_id_arr).'")';

                $user_info = $VipUserInfo
                    ->where(setWhereSql($where,''))
                    ->field('CONCAT(platform_id,"_",uid) AS pu_str,user_name,total_pay')
                    ->select();

                $user_info = arrReSet($user_info,'pu_str');

                if($user_info){
                    foreach ($list as $k => &$v) {
                        $this_user_info = getArrVal($user_info,$v['pu_str'],[]);
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

            $result['data'] = $list;
        }

        return $result;
    }

    public static function sellWorkOrderListConfig(){

        $time_arr = timeCondition('month',time());

        $def_add_time_str = date('Y-m-d H:i:s',$time_arr['starttime']);

        $config = [];

        $config['type_arr'] = SellWorkOrder::$type_arr;// 工单类型
        $config['sell_type_arr'] = SellWorkOrder::$sell_type_arr;// 销售类型
        $config['status_arr'] = SellWorkOrder::$status_arr;// 审核状态

        $config['search_config'] = [
            'product'=>[
                'status'=>1
            ],
            'admin'=>[
                'status'=>1,
                'name'=>'kf_id',
                'radio'=>true,
                'p_data'=>['group_type'=>[QcConfig::USER_GROUP_VIP]]
            ],
        ];

        $form_data['add_time_start'] = $def_add_time_str;

        return compact('config','form_data');
    }

    public static function getSellWorkOrderListData(array $param){

        $res = [
            'data'=>[],
            'count'=>0,
            'count_res'=>[],
        ];

        $page = getArrVal($param,'page',1);
        $limit = getArrVal($param,'limit',20);

        $model = new SellWorkOrder();

        $where = self::getSellWorkOrderListWhere($param);

        $sort_field = !empty($param['sort_field']) ? $param['sort_field'] : '';
        $sort = !empty($param['sort']) ? $param['sort'] : '';

        $count = $model->where($where)->count();

        if(!$count) return $res;

        $count_info = $model->where($where)->field('sum(sell_amount) as sell_amount,status,uid')->group('status,uid')->select()->toArray();

        $count_res = [
            'count'=>$count,//总条数
            'sum_sell_amount'=>0,//总销售
            'pass_sell_amount'=>0,//已审核销售
            'no_sell_amount'=>0,//未审核销售
            'count_uid'=>0,//客服_人数
            'uid'=>[],//客服id
        ];

        if($count_info){

            foreach ($count_info as $v){
                if($v['status'] == 0){
                    $count_res['no_sell_amount']+=$v['sell_amount'];
                }
                if($v['status'] >= 1){
                    $count_res['pass_sell_amount']+=$v['sell_amount'];
                }
                if($v['status'] >= 0){
                    $count_res['sum_sell_amount']+=$v['sell_amount'];
                }
                $count_res['uid'][$v['uid']] = $v['uid'];
            }
            $count_res['count_uid'] = count($count_res['uid']);
        }

        $list = $model->where($where);
        if($limit && $page){
            $list = $list->page($page,$limit);
        }

        if (!empty($sort_field)) {
            $order_str = "$sort_field $sort";
        }else {
            $order_str = 'add_time asc';
        }

        $list = $list->order($order_str)->select()->toArray();

        return [
            'data'=>$list,
            'count'=>$count,
            'count_res'=>$count_res
        ];
    }

    /**
     * @param $param
     * @return false|string
     */
    public static function getSellWorkOrderListWhere($param){

        $VipUserInfo = new VipUserInfo();

        $where = getDataByField($param,['kf_id','type','sell_type','id','qc_first_admin_id','qc_second_admin_id','uid'],true);

        $admin_info = self::$user_data;

        if(!$admin_info['is_admin']){
            $where[] = getWhereDataArr($admin_info['platform_id'],'platform_id');

            if(!empty($param['is_all']) && !empty($param['uid']) ){

            }else{
                if(in_array(QcConfig::USER_GROUP_QC,$admin_info['user_group_type_arr']) || $admin_info['position_grade'] >=3){
                    //质检 & 职位等级管理以上 查看所有
                }elseif($admin_info['position_grade'] == 2 && $admin_info['group_id'] > 0){//组长并且分配分组
                    if(!empty($param['group_id'])){
                        $group_id_info = arrMID($admin_info['group_id'],[$param['group_id']]);

                        if($group_id_info['ai_com']){
                            $param['group_id'] = $group_id_info['ai_com'];
                        }else{
                            $param['group_id'] = -1;
                        }
                    }else{
                        $param['group_id'] = $admin_info['group_id'];//查看改分组客服
                    }

                }else{
                    $where[] =['kf_id','=', $admin_info['id']];//只看自己
                }
            }
        }

        if(!empty($param['platform_id'])){
            $where[] = getWhereDataArr($param['platform_id'],'platform_id');
        }

        if(isset($param['username'])){
            if($param['username']){
                $where_recharge_user = [];
                // $where_recharge_user[] = ['user_name','like',$param['username']];
                $where_recharge_user['user_name'] = $param['username'];

                $recharge_user = $VipUserInfo->where($where_recharge_user)->field('uid,platform_id')->order('id desc')->select()->toArray();

                if($recharge_user){
                    $this_pu_arr = [];
                    foreach ($recharge_user as $v){
                        $this_pu_arr[] = $v['platform_id'].'_'.$v['uid'];
                    }
                    $where[] = ['p_u','in',$this_pu_arr];
                }else{
                    $where['uid'] = 0;
                }
            }
        }
        if(isset($param['p_p']) && $param['p_p'] ){
            $where[] = getWhereDataArr($param['p_p'],'p_p');
        }

        if(!empty($param['status'])){

            $where[] = getWhereDataArr($param['status'],'status');
        }

        if(isset($param['group_id']) && $param['group_id']){
            $kf_ids = SysServer::getAdminListByGroupIds($param['group_id'],2);

            if($kf_ids){
                $where[] = getWhereDataArr($kf_ids,'kf_id');
            }else{
                $where[] =['kf_id','=',-1];
            }
        }

        if(isset($param['add_time_start']) && $param['add_time_start']){
            $where[] = ['add_time','>=',strtotime($param['add_time_start'])];
        }

        if(isset($param['add_time_end'])&& $param['add_time_end']){
            $where[] = ['add_time','<',strtotime($param['add_time_end'])];
        }

        if(isset($param['qc_first_time_start'])&&$param['qc_first_time_start']){
            $where[] = ['qc_first_time','>=',strtotime($param['qc_first_time_start'])];
        }

        if(isset($param['qc_first_time_end']) && $param['qc_first_time_end']){
            $where[] = ['qc_first_time','<',strtotime($param['qc_first_time_end'])];
        }

        if(isset($param['qc_second_time_start']) && $param['qc_second_time_start']){
            $where[] = ['qc_second_time','>=',strtotime($param['qc_second_time_start'])];
        }

        if(isset($param['qc_second_time_end']) && $param['qc_second_time_end']){
            $where[] = ['qc_second_time','<',strtotime($param['qc_second_time_end'])];
        }

        if(isset($param['pay_time_start']) && $param['pay_time_start']){
            $where[] = ['pay_time','>=',strtotime($param['pay_time_start'])];
        }

        if(isset($param['pay_time_end']) && $param['pay_time_end']){
            $where[] = ['pay_time','<',strtotime($param['pay_time_end'])];
        }

        if(isset($param['sell_amount_start']) && $param['sell_amount_start']){
            $where[] = ['sell_amount','>=',$param['sell_amount_start']];
        }

        if(isset($param['sell_amount_end']) && $param['sell_amount_end']){
            $where[] = ['sell_amount','<',$param['sell_amount_end']];
        }

        if(isset($param['server_id_start']) && $param['server_id_start']){
            $where[] = ['server_id','>=',$param['server_id_start']];
        }

        if(isset($param['server_id_end']) && $param['server_id_end']){
            $where[] = ['server_id','<',$param['server_id_end']];
        }

        if(isset($param['not_search_id']) && $param['not_search_id']){
            $where[] = getWhereDataArr($param['not_search_id'],'id',false);
        }

        return setWhereSql($where,'');
    }

    public static function sellWorkOrderDetail($param){
        $code = [
            1=>'未知工单'
        ];
        //参数获取
        $type = getArrVal($param,'type',1);
        $id = getArrVal($param,'id',0);
        $user_recharge_info_id = getArrVal($param,'user_recharge_info_id',1);

        //参数初始化
        $info = compact('type','id');

        //查询
        $admin_info = self::$user_data;//查询当前账号信息

        $model = new SellWorkOrder();

        if($id){//详情

            $where = compact('id');

            $info = $model->where($where)->find();

            if(!$info){
                return ['code'=>1,'msg'=>$code[1]];
            }

            $info = $info->toArray();

            //$res['recall_time_str'] = date('Y-m-d H:i:s',$res['recall_time']);
            $info['recall_time'] = date('Y-m-d H:i:s',$info['recall_time']);

            $info['status_str'] = SellWorkOrder::$status_arr[$info['status']];

            if($info['type'] == 2){//销售工单数据处理
                // 整理订单时间区间、工单提交订单只能选择同一天的订单 20210308
                if(in_array($info['sell_type'],[2,3])){
                    $info['order_time_end'] = date('Y-m-d 00:00:00',$info['pay_time_last_day']);
                    $info['order_time_start'] = date('Y-m-d 00:00:00',$info['pay_time_first_day']);
                }else{
                    $info['order_time_end'] = date('Y-m-d 00:00:00',$info['pay_time']+3600*24);
                    $info['order_time_start'] = date('Y-m-d 00:00:00',$info['pay_time']);
                }
            }
            //查询vip充值用户信息
            $where_uri['uid'] = $info['uid'];
            $where_uri['platform_id'] = $info['platform_id'];
            $uri_info = self::getUserRechargeInfoByWhere($where_uri);

        }else{//新增
            //查询vip充值用户信息
            $uri_info = self::getUserRechargeInfoByWhere(['id'=>$user_recharge_info_id]);

            if($uri_info){
                $info['uid'] = $uri_info['uid'];
                $info['game_id'] = $uri_info['last_pay_game_id'];
                $info['product_id'] = $uri_info['product_id'];
                $where = [];
                $where['uid'] = $uri_info['uid'];
                //查询该vip用户是第一次提交工单
                $info['is_first'] = $model->where($where)->count();
            }

            if($type == 2){//销售工单数据处理
                // 整理订单时间区间、工单提交订单只能选择同一天的订单 20210308
                $info['order_time_start'] = date('Y-m-d 00:00:00');
                $info['order_time_end'] = date('Y-m-d 00:00:00',time()+3600*24);
            }

            //查询上一条工单信息
            $where = [];
            $where['uid'] = $uri_info['uid'];
            $where['platform_id'] = $uri_info['platform_id'];

            $last_info = $model->where($where)->order('add_time DESC,id DESC')->find();

            if($last_info){//上一条工单存在初始化部分信息
                $info['product_id'] = $last_info->product_id;
            }

            $info['kf_id'] = $admin_info['id'];//客服专员默认选择当前账号
            $info['sell_amount'] = 0;//工单销售总额
            $info['status'] = 0;

        }
        $action = [];
        $action_info = ListActionServer::checkSellWorkOrderAction($info);

        foreach ($action_info as $v){
            $action[] = 'Vip-'.$v;
        }
        if(in_array('Vip-sellWorkOrderQcFirst',$action) || in_array('Vip-sellWorkOrderQcSecond',$action) ){
            self::lockSellWorkId($id);
        }

        //vip用户充值信息初始化
        if($uri_info){
            $info['user_name'] = $uri_info['user_name'];
            $info['platform_id'] = $uri_info['platform_id'];
        }

        //获取配置信息
        $config = [];
        $config['kf_list'] = SysServer::getUserListByAdminInfo(self::$user_data,['group_type'=>[QcConfig::USER_GROUP_VIP,'is_active'=>1]]);
        $config['sell_type_arr'] = SellWorkOrder::$sell_type_arr;// 销售类型
        $config['is_first_arr'] = SellWorkOrder::$is_first_arr;// 销售次数
        $config['game_list'] = SysServer::getGameProductCache($info['platform_id'],1);
        $config['contact_type_arr'] = SellWorkOrder::$contact_type_arr;// 联系方式类型

        return compact('action','config','info','uri_info');
    }

    /**
     * 查询vip充值用户信息
     * @param $where
     * @return array
     */
    public static function getUserRechargeInfoByWhere($where){

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
            ,last_pay_game_id
        ';

        $info = $VipUserInfo->field($columns)->where($where)->find();

        if(!$info){
            return [];
        }

        $res = $info->toArray();

        $res['product_id'] = 0;
        $PlatformGameInfo = new PlatformGameInfo();

        if($res['last_pay_game_id'] && $res['platform_id']){
            $where = [];
            $where['platform_id'] = $res['platform_id'];
            $where['game_id'] = $res['last_pay_game_id'];
            $product_info = $PlatformGameInfo->where($where)->find();
            if($product_info){
                $res['product_id'] = $product_info->product_id;
            }
        }

        return $res;
    }

    #获取未审核工单
    public static function lockSellWorkId($id){

        $cache_key = 'getSellWorkNextId';

        /**
         * [id=>time]
         */
        $id_arr = cache($cache_key);
        $model = new SellWorkOrder();

        if($id_arr){
            //检查池子id情况
            foreach ($id_arr as $k=> $v){
                if(time()-$v>=60*10){
                    unset($id_arr[$k]);
                    continue;
                }
                $where = [];
                $where['id'] = $k;
                $where[] = ['status','>',0];
                $this_info = $model->where(setWhereSql($where,''))->count();
                if($this_info){
                    unset($id_arr[$k]);
                    continue;
                }
            }
        }
        $id_arr[$id] = time();

        cache($cache_key,$id_arr,60*20);

        return true;
    }

    /*
    * 工单关联-订单列表
    */
    public static function getKefuPayOrderList($param){

        $code = [
            1=>'平台参数错误',2=>'未知平台数据',
        ];

        $page = getArrVal($param,'page',1);
        $limit = getArrVal($param,'limit',20);

        if(!$param['platform_id']){
            return ['code'=>1,'msg'=>$code[1]];
        }

        $platform_id = $param['platform_id'];

        $platform_list = SysServer::getPlatformList();

        $platform_info = getArrVal($platform_list,$platform_id,[]);

        if(!$platform_info){
            return ['code'=>2,'msg'=>$code[2]];
        }

        $model = CommonServer::getPlatformModel('KefuPayOrder',$platform_info['suffix']);

        $SellWorkOrder = new SellWorkOrder();


        $sell_work_order_info = [];

        $sell_work_order_sell_type = isset($param['sell_type'])?$param['sell_type']:1;

        $where_kpo_id = getDataByField($param,['platform_id'],true);//已选中订单id条件 kefu_pay_order

        $where = getDataByField($param,['server_id'],true);

        if(isset($param['sell_work_order_id'])){

            $sell_work_order_id = $param['sell_work_order_id'];

            if($sell_work_order_id){
                $sell_work_order_info = $SellWorkOrder->where(['id'=>$sell_work_order_id])->find();
                if($sell_work_order_info){
                    $sell_work_order_info = $sell_work_order_info->toArray();
                    $sell_work_order_info['info_select_ids'] = explode(',',$sell_work_order_info['order_id_str']);
                }
            }
        }

        if(!empty($param['uid'])){
            $where[] = ['uid','=',$param['uid']];
        }else{
            $where['uid'] = -1;
        }

        if(!empty($param['gid'])){
            $where['gid'] = $param['gid'];
        }elseif(!empty($param['product_id'])){

            $PlatformGameInfoModel = new PlatformGameInfo();

            $game_list = $PlatformGameInfoModel
                ->where([
                    'platform_id'=>$param['platform_id'],
                    'product_id'=>$param['product_id']
                ])
                ->select();
            $game_ids = [];
            foreach ($game_list as $k => $v) {
                $game_ids[] = $v->game_id;
            }

            if($game_ids){
                $where[] = ['gid','in',$game_ids];
            }else{
                $where['gid'] = 0;
            }
        }
        $start_time = 0;
        if(isset($param['order_time_start'])){
            if($param['order_time_start']){
                $start_time = $this_time = strtotime($param['order_time_start']);
                $where[] = $where_kpo_id[] = ['pay_time','>=',$this_time];
                if($sell_work_order_sell_type == 1){
                    $this_time = strtotime(date('Y-m-d',($this_time+3600*24)));
                    $where[] = $where_kpo_id[] = ['pay_time','<',$this_time];
                }
            }
        }

        if(isset($param['order_time_end'])){
            if($param['order_time_end'] && $sell_work_order_sell_type != 1){
                $end_time = strtotime($param['order_time_end']);
                if($start_time){
                    $this_time = timeCondition('month',$start_time);//返回本月第一天最后一天
                    $where[] = $where_kpo_id[] = ['pay_time','>=',$start_time];
                    if($this_time['endtime']<=$end_time){
                        $where[] = $where_kpo_id[] = ['pay_time','<',$this_time['endtime']];
                    }else{
                        $where[] = $where_kpo_id[] = ['pay_time','<',$end_time];
                    }
                }else{
                    $this_time = timeCondition('month',$end_time);
                    $where[] = $where_kpo_id[] = ['pay_time','>=',$this_time['starttime']];
                    $where[] = $where_kpo_id[] = ['pay_time','<',$end_time];
                }
            }
        }

        //获取已选择订单id
        $ids_info = self::getSWOPOKpoId($where_kpo_id,$sell_work_order_info);

        if($ids_info['ids']){
            $where[] = ['order_id','not in',$ids_info['ids']];
        }

        $count = $model->where(setWhereSql($where,''))->count();

        if(!$count) return ['count'=>0];

        $model_param = $model->where(setWhereSql($where,''));


        if($limit && $page){
            $model_param = $model_param->page($page,$limit);
        }

        $list = $model_param->order('pay_time desc,id desc')->select();

        if($list){

            $list = $list->toArray();
            $payment_info = SysServer::getPaymentInfoByPlatformId($platform_id);

            foreach ($list as $k => &$v) {
                $v['LAY_CHECKED'] = false;
                $v['LAY_DISABLE'] = false;
                if(in_array($v['order_id'], $ids_info['info_select_ids'])){
                    $v['LAY_CHECKED'] = true;
                }
                if(in_array($v['order_id'], $ids_info['disable_select_ids'])){
                    $v['LAY_DISABLE'] = true;
                }

                $v['pay_time_str'] = $v['pay_time']?date('Y-m-d H:i',$v['pay_time']):'';

                $v['payment_str'] = getArrVal($payment_info,$v['payment'],'');

            }
        }

        return ['data'=>$list,'count'=>$count];
    }

    /**
     * 获取已选择订单id
     * @param $where
     * @param int $id 当前工单id 新建 0
     * @return array
     */
    public static function getSWOPOKpoId($where,$sell_work_order_info=[]){

        $SellWorkOrderPayOrder = new SellWorkOrderPayOrder();

        //获取符合条件已关联订单数据
        $list = $SellWorkOrderPayOrder->where(setWhereSql($where,''))->select()->toArray();

        $ids = [];//其他工单已关联ids
        $disable_select_ids = [];//当前工单已关联ids
        $info_select_ids = [];
        if($sell_work_order_info){
            $info_select_ids = $sell_work_order_info['info_select_ids'];
        }

        if($list){
            foreach ($list as $k => $v) {
                if($sell_work_order_info){
                    if($sell_work_order_info['status'] < 0){//拒审情况
                        if( in_array($v['kpo_id'],$info_select_ids) ){
                            $disable_select_ids[] = $v['kpo_id'];
                            continue;
                        }
                    }else{//普通数据

                        if(in_array($v['kpo_id'],$info_select_ids)){
                            continue;
                        }
                    }

                }
                $ids[] = $v['kpo_id'];
            }
        }


        return compact('ids','info_select_ids','disable_select_ids');
    }

    /**
     * 保存工单信息
     * @return array
     */
    public static function sWOSave($p_data){

        $code = [
            0=>'success',1=>'操作失败',2=>'信息不存在',3=>'审核状态错误',4=>'用户信息有误，请刷新页面'
        ];

        $id = isset($p_data['id'])?$p_data['id']:0;

        $model = new SellWorkOrder();

        $common_field = [
            'type'
            ,'sell_type'
            ,'is_first'
            ,'recall_account'
            ,'recall_time'
            ,'product_id'
            ,'game_id'
            ,'server_id'
            ,'uid'
            ,'platform_id'
            ,'contact_type'
            ,'content'
            ,'kf_id'
            ,'sell_amount'
            ,'pay_time'
            ,'pay_time_first_day'
            ,'pay_time_last_day'
            ,'p_u'
            ,'p_p'
            ,'chum_reason'
        ];
        $check_user_where = getDataByField($p_data,['uid','platform_id']);

        if(self::$user_data['is_admin'] == 0){

            if(!in_array(QcConfig::USER_GROUP_QC,self::$user_data['user_group_type_arr'])){
                //非质检
                if(self::$user_data['position_grade'] == QcConfig::POSITION_GRADE_NORMAL){

                    $check_user_where['ascription_vip'] = self::$user_data['id'];

                    $VipUserInfoModel = new VipUserInfo();

                    $check_user_res = $VipUserInfoModel->where($check_user_where)->count();

                    if(!$check_user_res){
                        return ['code'=>4,'msg'=>$code[4]];
                    }
                }elseif(self::$user_data['position_grade'] == QcConfig::POSITION_GRADE_LEADER){
                    $ids = AdminServer::getAdminIdsByGroupId(self::$user_data['group_id']);
                    if(!$ids){
                        $ids = [0];
                    }
                    $check_user_where[] = ['ascription_vip','in',$ids];

                    $VipUserInfoModel = new VipUserInfo();

                    $check_user_res = $VipUserInfoModel->where(setWhereSql($check_user_where,''))->count();

                    if(!$check_user_res){
                        return ['code'=>4,'msg'=>$code[4]];
                    }
                }else{

                    if(!in_array(getArrVal($p_data,'platform_id',0),self::$user_data['platform_id'])){
                        return ['code'=>4,'msg'=>$code[4]];
                    }
                }
            }else{
                //质检

            }
        }
        if(isset($p_data['recall_time']) && $p_data['recall_time']){
            $p_data['recall_time'] = strtotime($p_data['recall_time']);
        }

        if($p_data['type'] == SellWorkOrder::SELL_ORDER){
            $check_res = self::checkOrderIds($p_data,$id);

            if($check_res['code']){
                return $check_res;
            }

            $p_data['sell_amount'] = $check_res['data']['amount'];
            $p_data['pay_time'] = $check_res['data']['pay_time'];
            $p_data['server_id'] = $check_res['data']['server_id'];
            $p_data['pay_time_first_day'] = $check_res['data']['pay_time_first_day'];
            $p_data['pay_time_last_day'] = $check_res['data']['pay_time_last_day'];
        }

        if(isset($p_data['platform_id']) && $p_data['platform_id'] && isset($p_data['uid']) && $p_data['uid']){
            $p_data['p_u'] = $p_data['platform_id'].'_'.$p_data['uid'];
        }

        if(isset($p_data['platform_id']) && $p_data['platform_id'] && isset($p_data['product_id']) && $p_data['product_id']){
            $p_data['p_p'] = $p_data['platform_id'].'_'.$p_data['product_id'];
        }

        // game_id

        if($id){

            $this_info = $model->where(['id'=>$id])->find();

            if(!$this_info){
                return ['code'=>2,'msg'=>$code[2]];
            }

            if($this_info->status > 0){
                switch ($this_info->status) {
                    case 0:
                        $msg = '审核中';
                        break;
                    case 1:
                    case 2:
                        $msg = '已通过，不可修改';
                        break;

                    default:
                        $msg = $code[3];
                        break;
                }
                return ['code'=>3,'msg'=>$msg];
            }

            //筛选要更新数据
            $save_data = getDataByField($p_data,$common_field);
            $save_data['status'] = 0;
            $save_data['update_admin_id'] = self::$user_data['id'];

            $res = $this_info->save($save_data);

            if(!$res) return ['code'=>1,'msg'=>$code[1]];
        }else{

            $add_data = getDataByField($p_data,$common_field);

            $add_data['update_admin_id'] = self::$user_data['id'];
            $add_data['update_time'] = $add_data['add_time'] = time();

            $res = $model->insertGetId($add_data);

            if(!$res) return ['code'=>1,'msg'=>$code[1]];

            $id = $res;

            //修改vip用户的最后录单时间
            $vipUserInfoModel = new VipUserInfo();
            $whereData = getDataByField($p_data,['platform_id','uid']);
            $objInfo = $vipUserInfoModel->where($whereData)->find();
            if ($objInfo) {
                $updateData = ['last_record_time'=>time()];
                //更新操作
                $objInfo->save($updateData);
            }
        }

        if($p_data['type'] == SellWorkOrder::SELL_ORDER){//销售工单
            $res = self::sWOPOAdd($id,$p_data['order_ids'],$p_data['platform_id']);

            $res = $model->where('id',$id)->update(['order_id_str'=>implode(',',$res['order_o_ids'])]);

        }

        return ['code'=>0,'msg'=>$code[0]];

    }

    //检查订单是否绑定其他工单
    public static function checkOrderIds($p_data,$id){

        $code = [0=>'success',101=>'部分订单已提工单',102=>'未知平台'];

        $model = new SellWorkOrderPayOrder();

        $where = [];

        $where[] = ['work_order_id','!=',$id];

        $where[] = ['kpo_id','in',$p_data['order_ids']];

        $count = $model->where(setWhereSql($where,''))->count();

        if($count){
            return ['code'=>101,'msg'=>$code[101]];
        }

        $platform_list = SysServer::getPlatformList();

        $platform_info = getArrVal($platform_list,$p_data['platform_id'],[]);

        if(!$platform_info){
            return ['code'=>101,'msg'=>$code[101]];
        }

        $KefuPayOrder = CommonServer::getPlatformModel('KefuPayOrder',$platform_info['suffix']);

        $where = [];
        $where[] = ['id','in',$p_data['order_ids']];

        $info = $KefuPayOrder->where(setWhereSql($where,''))->field('sum(amount) AS amount,max(pay_time) AS pay_time')->find();

        $last_info = [];
        $first_info = [];
        $all_info = $KefuPayOrder->where(setWhereSql($where,''))->order('pay_time ASC')->select()->toArray();

        if($all_info){
            $last_info = array_pop($all_info);
            $first_info = array_shift($all_info);
        }

        $res = [
            'amount'=>0,
            'pay_time'=>0,
            'server_id'=>0,
            'pay_time_first_day'=>0,
            'pay_time_last_day'=>0,
        ];

        if($info){
            $res['amount'] = $info->amount;
            $res['pay_time'] = $info->pay_time;
            if($last_info){
                $res['server_id'] = $last_info['server_id'];
                $res['pay_time_last_day'] = strtotime(date('Y-m-d',$last_info['pay_time']+3600*24));//明天凌晨
            }
            if($first_info){
                $res['pay_time_first_day'] = strtotime(date('Y-m-d',$first_info['pay_time']));//今天天凌晨
            }

        }

        return ['code'=>0,'data'=>$res];
    }

    /**
     * 工单绑定关联订单数据
     * @param $id
     * @param $order_ids
     * @param $platform_id
     */
    public static function sWOPOAdd($id,$order_ids,$platform_id){

        $platform_list = SysServer::getPlatformList();

        $platform_info = getArrVal($platform_list,$platform_id,[]);

        if(!$platform_info){
            return false;
        }

        $KefuPayOrder = CommonServer::getPlatformModel('KefuPayOrder',$platform_info['suffix']);

        $where = [];
        $where[] = ['id','in',$order_ids];

        $order_list = $KefuPayOrder->where(setWhereSql($where,''))->select()->toArray();

        if(!$order_list){
            return false;
        }
        $order_o_ids = [];
        $order_list_n = [];
        foreach ($order_list as $k => $v){
            $order_list_n[$v['order_id']] = $v;
            $order_o_ids[] = $v['order_id'];
        }

        $model = new SellWorkOrderPayOrder();

        $where = [];
        $where['work_order_id'] = $id;

        $list = $model->where($where)->column('kpo_id');

        $del_ids = [];

        if($list){
            $old_order_o_ids = [];
            foreach ($list as $k => $v) {
                $old_order_o_ids[] = $v['kpo_id'];
            }

            $data = arrMID($old_order_o_ids,$order_o_ids);

            $add_ids = $data['ad_add'];

            $del_ids = $data['ad_del'];

        }else{
            $add_ids = $order_o_ids;
        }

        $now_time = time();

        //订单关联数据添加
        if($add_ids){
            $add_list = [];
            foreach ($add_ids as $k => $v) {
                $add_list[] = [
                    'platform_id'=>$platform_id,
                    'work_order_id'=>$id,
                    'kpo_id'=>$v,
                    'add_time'=>$now_time,
                    'pay_time'=>$order_list_n[$v]['pay_time'],
                    'amount'=>$order_list_n[$v]['amount'],
                    'game_id'=>$order_list_n[$v]['gid'],
                ];
            }

            $res_add = $model->insertAll($add_list);
        }
        //订单关联数据删除
        if($del_ids){
            $where = [];
            $where['work_order_id'] = $id;
            $where[] = ['kpo_id','in',$del_ids];
            $res_del = $model->where(setWhereSql($where,''))->delete();
        }

        return ['order_o_ids'=>$order_o_ids];
    }

    /**
     * 工单审核
     * @param $p_data
     * @return array
     */
    public static function sWOQc($p_data){

        $code = [
            0=>'success',1=>'操作失败',2=>'信息不存在',3=>'审核状态错误'
        ];

        $model = new SellWorkOrder();
        //查询工单数据
        $info = $model->where(['id'=>$p_data['id']])->find();

        if(!$info){
            return ['code'=>2,'msg'=>$code[2]];
        }

        if( !in_array($info->status, [0,1])){
            return ['code'=>3,'msg'=>$code[3]];
        }

        if($info->status == 1){//二审
            $info->qc_second_admin_id = self::$user_data['id'];
            $info->qc_second_time = time();
        }elseif($info->status == 0){//一审
            $info->qc_first_admin_id = self::$user_data['id'];
            $info->qc_first_time = time();
        }else{//修改
            $info->update_admin_id = self::$user_data['id'];
            $info->update_time = time();
        }

        $info->status = $p_data['status'];

        if($p_data['status'] < 0){//状态小于0为审核不通，保存不通过理由
            $info->reason = $p_data['reason'];
        }
        //保存 return bool
        $res = $info->save();

        if(!$res){
            return ['code'=>1,'msg'=>$code[1]];
        }

        if($info->type == SellWorkOrder::SELL_ORDER && $info->status < 0){
            $SellWorkOrderPayOrder = new SellWorkOrderPayOrder();
            $where = [];
            $where['work_order_id'] = $info->id;
            $SellWorkOrderPayOrder->where($where)->delete();
        }

        if($info->type == SellWorkOrder::REMARK_ORDER){
            $user_info = [];
            if($info->status == 1){
                $user_info['remark_pay'] = 0;
                $user_info['remark_time'] = $info->add_time;
            }elseif ($info->status < 0){
                $user_info['remark_pay'] = 0;

                $where = [
                    'platform_id'=>$info->platform_id,
                    'uid'=>$info->uid,
                    'type'=>SellWorkOrder::REMARK_ORDER,
                    'status'=>['>',0],
                ];


                $old_work_order_info = $model->field('add_time')
                    ->where($where)
                    ->order('add_time DESC')
                    ->find();

                if($old_work_order_info){
                    $user_info['remark_time'] = $old_work_order_info->add_time;
                }else{
                    $user_info['remark_time'] = 0;
                }
            }
            if($user_info){
                $VipUserInfoModel = new VipUserInfo();
                $res = $VipUserInfoModel->where(['platform_id'=>$info->platform_id,'uid'=>$info->uid])->update($user_info);
            }
        }

        return ['code'=>0,'msg'=>$code[0]];
    }

    public static function getSWOMonth($param){

        $page = getArrVal($param,'page',1);
        $limit = getArrVal($param,'limit',20);

        $SellerKpiConfig = new SellerKpiConfig();//kpi配置表

        $SellWorkOrderStatisticMonth = new SellWorkOrderStatisticMonth();
        $SellWorkOrder = new SellWorkOrder();
        /****************** 数据准备 ************************/

        $data_type = 1;
        if(isset($param['data_type'])){
            $data_type = $param['data_type']?$param['data_type']:$data_type;//
        }

        if(!isset($param['month']) || !$param['month']){
            $param['month'] = strtotime(date('Y-m'));
        }

        $time_arr = timeCondition('month',$param['month']);
        $param['kpi_date'] =$time_arr['starttime'];
        /**************** 数据准备 end ******************/

        /*************** kpi & 营销数据 ****************/
        //获取kpi列表
        $where_kpi = getDataByField($param,['group_id','kpi_date'],true);

        if(self::$user_data['is_admin'] == 0){
            $p_data['admin_platform_id'] =self::$user_data['platform_id']?self::$user_data['platform_id']:[0];
            if(self::$user_data['position_grade'] == QcConfig::POSITION_GRADE_NORMAL){
                $p_data['admin_id'] = self::$user_data['id'];
            }elseif(self::$user_data['position_grade'] == QcConfig::POSITION_GRADE_LEADER){
                $p_data['admin_group_id'] =self::$user_data['group_id_arr']?self::$user_data['group_id_arr']:[0];
            }
        }

        if(isset($param['admin_id']) && $param['admin_id'] ){
            $where_kpi[] = getWhereDataArr($param['admin_id'],'admin_id');
        }

        if(isset($param['platform_id']) && $param['platform_id'] ){
            $where_kpi[] = getWhereDataArr($param['platform_id'],'platform_id');
        }

        if(isset($param['p_p']) && $param['p_p'] ){
            $where_kpi['_string'] = "concat(platform_id,'_',product_id) in ('".str_replace(',','\',\'',$param['p_p'])."')";
        }

        $kpi_list_count = $SellerKpiConfig->where(setWhereSql($where_kpi,''))->count('distinct admin_id');

        if(!$kpi_list_count){
            return [[],0];
        }

        //获取kpi列表
        $kpi_list = $SellerKpiConfig
            ->field('group_id,admin_id,kpi_date,sum(kpi_value) AS kpi_value')
            ->where(setWhereSql($where_kpi,''))
            ->order('kpi_date DESC,id DESC')
            ->group('group_id,admin_id')
            ->page($page,$limit)
            ->select()->toArray();

        // 初始化统计 & 收集kf_id
        $kf_id = [];//客服id
        $product_id = [];//产品id
        $p_p_arr = [];//产品p_p
        $admin_p_p_arr = [];
        $admin_list = SysServer::getAdminListCache();
        foreach ($kpi_list as $k => &$kl_v){
            $kl_v['kpi_date_str'] = date('Y-m',$kl_v['kpi_date']);
            $this_admin_info = getArrVal($admin_list,$kl_v['admin_id'],[]);
            $kl_v['admin_id_str'] = $this_admin_info?$this_admin_info['name']:'未知';

            //查询产品名称
            $this_where =[];// getDataByField($param,['product_id','platform_id']);
            if(isset($param['platform_id']) && $param['platform_id']){
                $this_where[] = getWhereDataArr($param['platform_id'],'platform_id');
            }

            if(isset($param['p_p']) && $param['p_p']){
                $this_where['_string'] = "concat(platform_id,'_',product_id) in ('".str_replace(',','\',\'',$param['p_p'])."')";
            }

            $this_where['admin_id'] = $kl_v['admin_id'];
            $this_where['group_id'] = $kl_v['group_id'];
            $this_where['kpi_date'] = $kl_v['kpi_date'];
            $this_kpi_product_info = ConfigServer::getGameProductNames($this_where);
            $kl_v = array_merge($kl_v,getDataByField($this_kpi_product_info,['p_p','id']));
            if($this_kpi_product_info['p_p']){
                $p_p_arr = array_values(array_unique(array_merge($p_p_arr,$this_kpi_product_info['p_p'])));
                $admin_p_p_arr[]= "(admin_id=$kl_v[admin_id] AND p_p in ('".implode("','",$kl_v['p_p'])."'))";
            }
            if($this_kpi_product_info['id']){
                $product_id = array_values(array_unique(array_merge($product_id,$this_kpi_product_info['id'])));
            }
            $kl_v['product_id_str'] =$this_kpi_product_info['name'];

            $kf_id[] = $kl_v['admin_id'];

            self::initStatisticSWOKpiVal($kl_v);

        }

        // 以用户作维度重排kpi列表数据
        $kpi_list = arrReSet($kpi_list,'admin_id');

        $field_arr =[
            'big_order_count',
            'big_order_sum',
            'maintenance_all',
            'maintenance_count',
            //'month_user_all',
            //'month_user_count',
            'order_count',
            'order_sum',
            //'remark_all',
            //'remark_count',
            //'sell_commission',
            'sum_kpi_value',
        ];
        if($data_type == 1){
            $field_arr[] = 'remark_all';
            $field_arr[] = 'remark_count';
        }elseif ($data_type == 2){
            $field_arr[] = 'month_user_all';
            $field_arr[] = 'month_user_count';
            $field_arr[] = 'sell_commission';
        }
        $field_arr_new = [
            'admin_id'
        ];

        foreach ($field_arr as $v){
            $field_arr_new[] = "sum($v) as $v";
        }

        $where = getDataByField($param,['group_id','kpi_date'],1);

        if(isset($param['platform_id']) && $param['platform_id'] ){
            $where[] = getWhereDataArr($param['platform_id'],'platform_id');
        }

        if(isset($param['p_p']) && $param['p_p'] ){
            $where[] = getWhereDataArr($param['p_p'],'p_p');
        }

        if(isset($param['admin_id']) && $param['admin_id'] ){
            $where[] = getWhereDataArr($param['admin_id'],'admin_id');
        }
        $where['type'] = 2;

        $month_info = $SellWorkOrderStatisticMonth->field(implode(',',$field_arr_new))->where(setWhereSql($where,''))->group('admin_id')->select()->toArray();

        if($month_info){
            foreach ($month_info as $v){
                if(!isset($kpi_list[$v['admin_id']])){
                    continue;
                }
                $kpi_list[$v['admin_id']] = array_merge($kpi_list[$v['admin_id']],$v);
            }
        }

        if(isset($param['big_order_limit']) && $param['big_order_limit'] != StatisticServer::$big_order_limit_def){
            $where = getDataByField($param,['group_id'],true);
            $where['_string'] = str_replace('admin_id','kf_id',"(".implode(' OR ',$admin_p_p_arr).")");

            $time_arr = timeCondition('month',$param['kpi_date']);

            $where[] = ['pay_time','>=',$time_arr['starttime']];
            $where[] = ['pay_time','<',$time_arr['endtime']];

            if(isset($param['admin_id']) && $param['admin_id'] ){
                $where[] = getWhereDataArr($param['admin_id'],'kf_id');
            }

            if(isset($param['admin_platform_id']) && $param['admin_platform_id'] ){
                $where[] = ['platform_id','in',$param['admin_platform_id']];
            }
            if(isset($param['admin_group_id']) && $param['admin_group_id'] ){
                $where[] = getWhereDataArr($param['admin_group_id'],'group_id');
            }
            if(isset($param['platform_id']) && $param['platform_id'] ){
                $where[] = getWhereDataArr($param['platform_id'],'platform_id');
            }

            if(isset($param['p_p']) && $param['p_p'] ){
                $where[] = getWhereDataArr($param['p_p'],'p_p');
            }
            $where[] = ['sell_amount','>=',$param['big_order_limit']];

            $sell_big_order_info = $SellWorkOrder
                ->field('kf_id,sum(sell_amount) as big_order_sum,count(id) as big_order_count')
                ->where(setWhereSql($where,''))
                ->group('kf_id')
                ->select()
                ->toArray();

            foreach ($kpi_list as $k => $v){
                $kpi_list[$k]['big_order_sum'] = $kpi_list[$k]['big_order_sum'] = 0;
            }
            if($sell_big_order_info){
                foreach ($sell_big_order_info as $v){
                    if(!isset($kpi_list[$v['kf_id']])){
                        continue;
                    }
                    $kpi_list[$v['kf_id']] = array_merge($kpi_list[$v['kf_id']],$v);
                }
            }
        }

        // 最后相关统计计算
        foreach ($kpi_list as $k => &$kl_v){
            $this_param = getDataByField($kl_v,['kpi_date','admin_id']);
            if(isset($param['platform_id']) && $param['platform_id'] ){
                $this_param['platform_id'] = $param['platform_id'];
            }
            $kl_v = array_merge($kl_v,self::getMonthKfVipUserStatistic($this_param));

            if($kl_v['sum_kpi_value']>0){
                $kl_v['order_kip_present'] = countPresent($kl_v['order_sum'],$kl_v['sum_kpi_value']);
            }
            if($kl_v['order_sum']>0){
                $kl_v['big_order_sum_present'] = countPresent($kl_v['big_order_sum'],$kl_v['order_sum']);
            }
            if($kl_v['remark_all']>0){
                $kl_v['remark_present'] = countPresent($kl_v['remark_count'],$kl_v['remark_all']);
                $kl_v['remark_present_str'] = $kl_v['remark_present'].'%('.$kl_v['remark_count'].'/'.$kl_v['remark_all'].')';
            }
            //活跃覆盖率
            if($kl_v['maintenance_all']>0){
                $kl_v['maintenance_present'] = countPresent($kl_v['maintenance_count'],$kl_v['maintenance_all']);
                $kl_v['maintenance_present_str'] = $kl_v['maintenance_present'].'%('.$kl_v['maintenance_count'].'/'.$kl_v['maintenance_all'].')';
            }
            if($kl_v['add_washed_away']){
                $kl_v['washed_away_present'] = countPresent($kl_v['add_washed_away'],($kl_v['add_user_count_all']-$kl_v['washed_away_all']+$kl_v['add_washed_away']));
                $kl_v['washed_away_present_str'] = $kl_v['washed_away_present'].'%('.$kl_v['add_washed_away'].'/'.($kl_v['add_user_count_all']-$kl_v['washed_away_all']+$kl_v['add_washed_away']).')';
            }
            if($kl_v['add_login_lost_count']){
                $kl_v['login_lost_present'] = countPresent($kl_v['add_login_lost_count'],($kl_v['add_user_count_all']-$kl_v['login_lost_all']+$kl_v['add_login_lost_count']));
                $kl_v['login_lost_present_str'] = $kl_v['login_lost_present'].'%('.$kl_v['add_login_lost_count'].'/'.($kl_v['add_user_count_all']-$kl_v['login_lost_all']+$kl_v['add_login_lost_count']).')';
            }

        }

        //重排键值
        $kpi_list = array_values($kpi_list);

        return [$kpi_list,$kpi_list_count];
    }

    public static function sellStatisticMonthListConfig(){
        $search_config = [
            'product'=>[
                'status'=>1,
            ],
            'admin'=>[
                'status'=>1,
                'p_data'=>[
                    'group_type'=>[QcConfig::USER_GROUP_VIP],
                    'not_group_type'=>[QcConfig::USER_GROUP_QC],
                ],
                'name'=>'admin_id',
            ],
        ];

        //$group_list = SysServer::getGroupListByAdminInfo(QcConfig::USER_GROUP_VIP);

        return compact(
            'search_config'
        //,'group_list'
        );
    }

    /**
     * 月营销报表全部数据统计
     * @param array $param
     * @return array
     */
    public static function statisticSWOMonth(array $param){

        session_write_close();

        $SellWorkOrderStatisticMonth = new SellWorkOrderStatisticMonth();
        $SellerKpiConfig = new SellerKpiConfig();
        $SellWorkOrder = new SellWorkOrder();
        /**************** 数据准备 *********************/
        $statistic_info = [];

        self::initStatisticSWOKpiVal($statistic_info);

        $big_order_limit = getArrVal($param,'big_order_limit',StatisticServer::$big_order_limit_def);//大单条件

        $data_type = getArrVal($param,'data_type',1);

        if(!isset($param['month']) || !$param['month']){
            $param['month'] = strtotime(date('Y-m'));
        }

        $time_arr = timeCondition('month',$param['month']);

        $param['kpi_date'] = $time_arr['starttime'];

        /***************** 数据准备 end ****************/

        /****************** kpi & 营销数据 ******************/
        $where_kpi_product = $where = getDataByField($param,['group_id','kpi_date'],true);

        if(self::$user_data['is_admin'] == 0){
            $p_data['admin_platform_id'] =self::$user_data['platform_id']?self::$user_data['platform_id']:[0];
            if(self::$user_data['position_grade'] == QcConfig::POSITION_GRADE_NORMAL){
                $p_data['admin_id'] = self::$user_data['id'];
            }elseif(self::$user_data['position_grade'] == QcConfig::POSITION_GRADE_LEADER){
                $p_data['admin_group_id'] =self::$user_data['group_id_arr']?self::$user_data['group_id_arr']:[0];
            }
        }

        if(isset($param['admin_id']) && $param['admin_id'] ){
            $where[] = getWhereDataArr($param['admin_id'],'admin_id');
        }

        if(isset($param['platform_id']) && $param['platform_id'] ){
            $where[] = getWhereDataArr($param['platform_id'],'platform_id');
            $where_kpi_product[] = getWhereDataArr($param['platform_id'],'platform_id');
        }

        if(isset($param['p_p']) && $param['p_p'] ){
            $where[] = getWhereDataArr($param['p_p'],'p_p');
            $where_kpi_product['_string'] = "concat(platform_id,'_',product_id) in ('".str_replace(',','\',\'',$param['p_p'])."')";
        }

        if(isset($param['admin_platform_id']) && $param['admin_platform_id'] ){
            $where[] = ['platform_id','in',$param['admin_platform_id']];
        }
        if(isset($param['admin_group_id']) && $param['admin_group_id'] ){
            $where[] = ['group_id','in',$param['admin_group_id']];
        }

        //查询客服设置营销产品配置
        $kpi_product_info = $SellerKpiConfig->where(setWhereSql($where_kpi_product,''))->select()->toArray();

        $kpi_product_info_new = [];
        $admin_p_p_arr = [];
        if($kpi_product_info){
            foreach ( $kpi_product_info as $item){
                if(!isset($kpi_product_info_new[$item['admin_id']])){
                    $kpi_product_info_new[$item['admin_id']]=['admin_id' => $item['admin_id'],'p_p'=>[]];
                }

                $kpi_product_info_new[$item['admin_id']]['p_p'][]= $item['platform_id'].'_'.$item['product_id'];
            }
        }
        if($kpi_product_info_new){
            foreach ($kpi_product_info_new as $item){
                $admin_p_p_arr[]= "(admin_id=$item[admin_id] AND p_p in ('".implode("','",$item['p_p'])."'))";
            }
        }

        $where['_string'] = ' 1= 1';
        if($admin_p_p_arr){
            $admin_p_p_str = "(".implode(' OR ',$admin_p_p_arr).")";
            setWhereString($admin_p_p_str,$where);
        }else{
            $admin_p_p_str = '';
        }


        $field_arr =[
            'big_order_count',
            'big_order_sum',
            'maintenance_all',
            'maintenance_count',
            //'month_user_all',
            //'month_user_count',
            'order_count',
            'order_sum',
            //'remark_all',
            //'remark_count',
            //'sell_commission',
            'sum_kpi_value',
        ];
        if($data_type == 1){
            $field_arr[] = 'remark_all';
            $field_arr[] = 'remark_count';
        }elseif ($data_type == 2){
            $field_arr[] = 'month_user_all';
            $field_arr[] = 'month_user_count';
            $field_arr[] = 'sell_commission';
        }
        $field_arr_new = [

        ];

        foreach ($field_arr as $v){
            $field_arr_new[] = "sum($v) as $v";
        }

        $where['type'] = 2;

        $count = $SellWorkOrderStatisticMonth->where(setWhereSql($where,''))->count();
        if($count){
            $month_info = $SellWorkOrderStatisticMonth
                ->field(implode(',',$field_arr_new))
                ->where(setWhereSql($where,''))
                ->find();
            $statistic_info = array_merge($statistic_info,$month_info->toArray());
        }

        //获取kpi列表

        $this_param = [];
        $this_param['kpi_date'] = getArrVal($param,'month','');
        $this_param['platform_id'] = getArrVal($param,'platform_id',0);
        $this_param['p_p'] = getArrVal($param,'p_p_id','');
        $this_param['group_id'] = getArrVal($param,'group_id',0);
        $this_param['admin_id'] = getArrVal($param,'admin_id','');
        $this_param['admin_platform_id'] = getArrVal($param,'admin_platform_id',[]);

        if(isset($param['admin_group_id']) && $param['admin_group_id'] ){
            $this_param[] = ['group_id','in',$param['admin_group_id']];
        }


        $statistic_info = array_merge($statistic_info,self::getMonthKfVipUserStatistic($this_param));

        if($big_order_limit != StatisticServer::$big_order_limit_def){
            $where = getDataByField($param,['group_id'],true);
            if($admin_p_p_str){
                $where['_string'] = str_replace('admin_id','kf_id',$admin_p_p_str);
            }

            $time_arr = timeCondition('month',$param['kpi_date']);

            $where[] = ['pay_time','>=',$time_arr['starttime']];
            $where[] = ['pay_time','<',$time_arr['endtime']];

            if(isset($param['admin_id']) && $param['admin_id'] ){
                $where[] = getWhereDataArr($param['admin_id'],'kf_id');
            }

            if(isset($param['admin_platform_id']) && $param['admin_platform_id'] ){
                $where[] = ['platform_id','in',$param['admin_platform_id']];
            }
            if(isset($param['admin_group_id']) && $param['admin_group_id'] ){
                $where[] = ['group_id','in',$param['admin_group_id']];
            }
            if(isset($param['platform_id']) && $param['platform_id'] ){
                $where[] = getWhereDataArr($param['platform_id'],'platform_id');
            }

            if(isset($param['p_p']) && $param['p_p'] ){
                $where[] = getWhereDataArr($param['p_p'],'p_p');
            }
            $where[] = ['sell_amount','>=',$param['big_order_limit']];

            $sell_big_order_info = $SellWorkOrder->field('sum(sell_amount) as big_order_sum,count(id) as big_order_count')->where(setWhereSql($where,''))->find()->toArray();

            if($sell_big_order_info){
                $statistic_info = array_merge($statistic_info,$sell_big_order_info);
            }
        }

        /*************** 最后相关统计计算 begin ***************/
        // kpi
        if($statistic_info['sum_kpi_value']>0){
            $statistic_info['order_kip_present'] = countPresent($statistic_info['order_sum'],$statistic_info['sum_kpi_value']).'%';
        }
        // 大单占比率
        if($statistic_info['order_sum']>0){
            $statistic_info['big_order_sum_present'] = countPresent($statistic_info['big_order_sum'],$statistic_info['order_sum']).'%';
            $statistic_info['big_order_sum_present_str'] = $statistic_info['big_order_sum_present'].'%('.$statistic_info['big_order_sum'].'/'.$statistic_info['order_sum'].')';
        }
        // 流失
        if($statistic_info['add_washed_away']){
            $statistic_info['washed_away_present'] = countPresent($statistic_info['add_washed_away'],($statistic_info['add_user_count_all']-$statistic_info['washed_away_all']+$statistic_info['add_washed_away']));
            $statistic_info['washed_away_present_str'] = $statistic_info['washed_away_present'].'%('.$statistic_info['add_washed_away'].'/'.($statistic_info['add_user_count_all']-$statistic_info['washed_away_all']+$statistic_info['add_washed_away']).')';
        }
        if($statistic_info['add_login_lost_count']){
            $statistic_info['login_lost_present'] = countPresent($statistic_info['add_login_lost_count'],($statistic_info['add_user_count_all']-$statistic_info['login_lost_all']+$statistic_info['add_login_lost_count']));
            $statistic_info['login_lost_present_str'] = $statistic_info['login_lost_present'].'%('.$statistic_info['add_login_lost_count'].'/'.($statistic_info['add_user_count_all']-$statistic_info['login_lost_all']+$statistic_info['add_login_lost_count']).')';
        }
        // 活跃
        if($statistic_info['maintenance_all']>0){
            $statistic_info['maintenance_present'] = countPresent($statistic_info['maintenance_count'],$statistic_info['maintenance_all']);
            $statistic_info['maintenance_present_str'] = $statistic_info['maintenance_present'].'%('.$statistic_info['maintenance_count'].'/'.$statistic_info['maintenance_all'].')';
        }
        // 登记
        if($statistic_info['remark_all']>0){
            $statistic_info['remark_present'] = countPresent($statistic_info['remark_count'],$statistic_info['remark_all']);
            $statistic_info['remark_present_str'] = $statistic_info['remark_present'].'%('.$statistic_info['remark_count'].'/'.$statistic_info['remark_all'].')';
        }
        // 维护
        if($statistic_info['month_user_all']>0){
            $statistic_info['month_user_present'] = countPresent($statistic_info['month_user_count'],$statistic_info['month_user_all']);
            $statistic_info['month_user_present_str'] = $statistic_info['month_user_present'].'%('.$statistic_info['month_user_count'].'/'.$statistic_info['month_user_all'].')';
        }
        /*************** 最后相关统计计算 end ***************/
        return $statistic_info;
    }

    /**
     * 工单月营销报表个人
     * @param array $param
     * @return array
     */
    public static function getSWOMonthByAdmin(array $param){

        $page = getArrVal($param,'page',1);
        $limit = getArrVal($param,'limit',20);

        $SellerKpiConfig = new SellerKpiConfig();//kpi配置表

        $SellWorkOrderStatisticMonth = new SellWorkOrderStatisticMonth();
        /*数据准备*/
        $game_list = SysServer::getGameProductCache();//游戏列表
        $game_list_new = arrReSet($game_list,'id_str');

        $data_type = getArrVal($param,'data_type',1);

        if(!$param['month']){
            $param['month'] = strtotime(date('Y-m'));
        }

        $time_arr = timeCondition('month',$param['month']);
        $param['kpi_date'] = $time_arr['starttime'];
        /*数据准备 end*/

        /*kpi & 营销数据*/
        //获取kpi列表
        $where_kpi = getDataByField($param,['kpi_date','admin_id','group_id'],true);

        if(isset($param['platform_id']) && $param['platform_id'] ){
            $where_kpi[] = getWhereDataArr($param['platform_id'],'platform_id');
        }

        if(isset($param['p_p']) && $param['p_p'] ){
            $where_kpi['_string'] = "concat(platform_id,'_',product_id) in ('".str_replace(',','\',\'',$param['p_p'])."')";
        }

        $kpi_list_count = $SellerKpiConfig->where(setWhereSql($where_kpi,''))->count();

        if(!$kpi_list_count){
            return [[],0];
        }

        //获取kpi列表
        $kpi_list = $SellerKpiConfig->where(setWhereSql($where_kpi,''))->page($page,$limit)->order('id DESC')->select()->toArray();

        // 初始化统计 & 收集游戏id
        $p_p = [];//游戏id
        foreach ($kpi_list as $k => &$v){
            $v['kpi_date_str'] = date('Y-m',$v['kpi_date']);
            $this_p_p = $v['platform_id'].'_'.$v['product_id'];
            $v["p_p"] = $this_p_p;
            $p_p[] = $this_p_p;
            $v['product_id_str'] = $game_list_new[$this_p_p]['name'];
            //查询产品名称
            self::initStatisticSWOKpiVal($v);
        }
        // 以用户作维度重排kpi列表数据
        $kpi_list = arrReSet($kpi_list,'p_p');

        $field_arr =[
            'big_order_count',
            'big_order_sum',
            'maintenance_all',
            'maintenance_count',
            //'month_user_all',
            //'month_user_count',
            'order_count',
            'order_sum',
            //'remark_all',
            //'remark_count',
            //'sell_commission',
            'sum_kpi_value',
        ];
        if($data_type == 1){
//            $field_arr[] = 'remark_all';
            $field_arr[] = 'remark_count';
        }elseif ($data_type == 2){
//            $field_arr[] = 'month_user_all';
            $field_arr[] = 'month_user_count';
            $field_arr[] = 'sell_commission';
        }
        $field_arr_new = [
            'p_p'
        ];

        foreach ($field_arr as $v){
            $field_arr_new[] = $v;
        }

        $where = getDataByField($param,['group_id','admin_id','kpi_date'],1);

        if(isset($param['platform_id']) && $param['platform_id'] ){
            $where[] = getWhereDataArr($param['platform_id'],'platform_id');
        }

        if(isset($param['p_p']) && $param['p_p'] ){
            $where[] = getWhereDataArr($param['p_p'],'p_p');
        }
        $where['type'] = 2;

        $month_info = $SellWorkOrderStatisticMonth
            ->field(implode(',',$field_arr_new))
            ->where(setWhereSql($where,''))
            ->select()->toArray();

        if($month_info){
            foreach ($month_info as $v){
                if(!isset($kpi_list[$v['p_p']])){
                    continue;
                }

                $kpi_list[$v['p_p']] = array_merge($kpi_list[$v['p_p']],$v);
            }
        }

        // 最后相关统计计算
        foreach ($kpi_list as $k => &$v){

            $this_param = getDataByField($v,['kpi_date','platform_id','p_p','group_id','admin_id']);

            $v = array_merge($v,self::getMonthKfVipUserStatistic($this_param));

            if($v['sum_kpi_value']>0){
                $v['order_kip_present'] = countPresent($v['order_sum'],$v['sum_kpi_value']);
            }
            if($v['order_sum']>0){
                $v['big_order_sum_present'] = countPresent($v['big_order_sum'],$v['order_sum']);
            }
            if($v['add_washed_away']>0){
                $v['washed_away_present'] = countPresent($v['add_washed_away'],($v['add_user_count_all']-$v['washed_away_all']+$v['add_washed_away']));
                $v['washed_away_present_str'] = $v['washed_away_present'].'%('.$v['add_washed_away'].'/'.($v['add_user_count_all']-$v['washed_away_all']+$v['add_washed_away']).')';
            }

            if($v['maintenance_all']>0){
                $v['maintenance_present'] = countPresent($v['maintenance_count'],$v['maintenance_all']);
                $v['maintenance_present_str'] = $v['maintenance_present'].'%('.$v['maintenance_count'].'/'.$v['maintenance_all'].')';
            }

            if($v['remark_all']>0){
                $v['remark_present'] = countPresent($v['remark_count'],$v['remark_all']);
                $v['remark_present_str'] = $v['remark_present'].'%('.$v['remark_count'].'/'.$v['remark_all'].')';
            }
        }

        //重排键值
        $kpi_list = array_values($kpi_list);

        return [$kpi_list,$kpi_list_count];
    }

    /**
     * 工单月营销报表个人
     * @param array $param
     * @param int $page
     * @param int $limit
     * @return array
     */
    public static function statisticSWOMonthByAdmin(array $param,int $page,int $limit){

        $SellerKpiConfig = new SellerKpiConfig();//kpi配置表
        $SellWorkOrder = new SellWorkOrder();//工单表
        $VipUserInfo = new VipUserInfo();

        /*数据准备*/
        $admin_id = getArrVal($param,'admin_id',0);
        $common_data = getDataByField($param,['group_id','admin_id','kpi_date','game_product_id']);

        if(!$admin_id) return [];

        $big_order_limit = StatisticServer::$big_order_limit_def;//大单条件
        if(isset($param['big_order_limit']) && $param['big_order_limit']){
            $big_order_limit = $param['big_order_limit'];
        }
        unset($param['big_order_limit']);

        $active_user_recharge_limit = 1000;//充值活跃条件

        $time_arr = timeCondition('month',$param['kpi_date']);
        $param['kpi_date'] = $time_arr['starttime'];

        /*数据准备 end*/

        /*kpi & 营销数据*/
        //获取kpi列表
        $where_kpi_list = getDataByField($param,['kpi_date','admin_id'],true);

        $kpi_list_count = $SellerKpiConfig->where($where_kpi_list)->count();

        if(!$kpi_list_count){
            return [];
        }

        //获取kpi列表
        $kpi_list = $SellerKpiConfig->where($where_kpi_list)->order('id asc')->page($page,$limit)->select()->toArray();

        // 初始化统计 & 收集游戏id
        $p_p = [];//游戏id
        foreach ($kpi_list as $k => &$kl_v){
            $this_p_p = $kl_v['platform_id'].'_'.$kl_v['product_id'];
            $kl_v["p_p"] = $this_p_p;
            $p_p[] = $this_p_p;
            self::initStatisticSWOKpiVal($kl_v);
        }

        //查询游戏关联子游戏信息
        $PlatformGameInfo = new PlatformGameInfo();
        $where = [];
        $where['_string'] = "concat(platform_id,'_',product_id) in('".implode('\',\'',$p_p)."')";
        $platform_game_info = $PlatformGameInfo->where(setWhereSql($where,''))->select()->toArray();
        $new_platform_game_info = [];
        foreach ($platform_game_info as $v){
            $new_platform_game_info[$v['platform_id'].'_'.$v['game_id']] = $v['platform_id'].'_'.$v['product_id'];
        }

        // 以用户作维度重排kpi列表数据
        $kpi_list = arrReSet($kpi_list,'p_p');
        $where_sell_work_order = [];
        $where_sell_work_order[] = ['pay_time','>=',$time_arr['starttime']];
        $where_sell_work_order[] = ['pay_time','<',$time_arr['endtime']];
        $where_sell_work_order[] = ['kf_id','=',$param['admin_id']];
        $where_sell_work_order[] = ['p_p','in',$p_p];
        $where_sell_work_order[] = ['status','>',0];
        $where_sell_work_order['type'] = SellWorkOrder::SELL_ORDER;

        $field = 'kf_id,type,sell_amount,p_p,p_u,platform_id,product_id';

        $sell_work_order_statistic = $SellWorkOrder->field($field)->where(setWhereSql($where_sell_work_order,''))->select()->toArray();
//        dd($sell_work_order_statistic);
        if($sell_work_order_statistic){
            foreach ($sell_work_order_statistic AS $k => $v){
                $kpi_list[$v['p_p']]['order_sum']+= $v['sell_amount'];
                $kpi_list[$v['p_p']]['order_count']+= 1;
                if($v['sell_amount'] >= $big_order_limit){
                    $kpi_list[$v['p_p']]['big_order_sum']+= $v['sell_amount'];
                    $kpi_list[$v['p_p']]['big_order_count']+= 1;
                }
                #提成
                $where_commission = [];
                $where_commission[] = ['start_time','<=',$time_arr['starttime']];
                $where_commission[] = ['end_time','>=',$time_arr['starttime']];
                $kpi_list[$v['p_p']]['sell_commission']+= GameProductServer::getProductCommissionByPlatformIdProductId($v['platform_id'],$v['product_id'],$v['sell_amount'],$where_commission);
                $kpi_list[$v['p_p']]['sell_commission']= round($kpi_list[$v['p_p']]['sell_commission'],2);

            }
        }
        /*kpi & 营销数据 end*/

        /*该月分配用户查询 begin*/
        $where_user_list = [];
        $where_user_list[] = ['ascription_vip','=',$param['admin_id']];
        $where_user_list[] = ['first_distribute_time','between',[$time_arr['starttime'],$time_arr['endtime']]];
        $user_list = $VipUserInfo->field('ascription_vip,platform_id,uid,p_l_g')->where(setWhereSql($where_user_list,''))->select()->toArray();

        $user_list_info = [];//[客服id][uid/p_u_id] 对应客服分配用户数组

        $user_list_info_p_u = [];//p_us

        $other = [];

        foreach ($user_list as $k => $v){
            if(!isset($new_platform_game_info[$v['p_l_g']])){
                $other[] = $v;
                continue;
            }

            $this_p_p = $new_platform_game_info[$v['p_l_g']];

            $user_list_info[$this_p_p][] = $v['platform_id'].'_'.$v['uid'];//预留后面平台不分混合查询

            $user_list_info_p_u[] = $v['platform_id'].'_'.$v['uid'];

        }
        /*该月分配用户查询 end*/

        if($user_list_info){//没有分配用户数据不作处理
            /**
             * 月联系
             * 该客服-该月-新增分配用户-对应登记工单数
             */
            $where = [];
            $where[] = ['status','>',0];
            //该月
            $where[] = ['add_time','>=',$time_arr['starttime']];
            $where[] = ['add_time','<=',$time_arr['endtime']];
            //新增分配用户
            $where[] = ['p_u','in',$user_list_info_p_u];
            $where[] = ['kf_id','=',$admin_id];
            $where[] = ['type','=',SellWorkOrder::REMARK_ORDER];

            $this_info = $SellWorkOrder->field('p_p,count(distinct(p_u)) AS count_num')->where(setWhereSql($where,''))->group('p_p')->select()->toArray();

            if($this_info){
                foreach ($this_info AS $k =>$v){
                    if(!isset($kpi_list[$v['p_p']])){
                        $this_p_p_arr = explode('_',$v['p_p']);

                        if(count($this_p_p_arr) != 2){
                            continue;
                        }
                        $kpi_list[$v['p_p']] = $common_data;
                        $kpi_list[$v['p_p']]['kpi_value'] = 0;
                        $kpi_list[$v['p_p']]['platform_id'] = $this_p_p_arr[0];
                        $kpi_list[$v['p_p']]['product_id'] = $this_p_p_arr[1];
                        $kpi_list[$v['p_p']]["p_p"] = $v['p_p'];
                        self::initStatisticSWOKpiVal($kpi_list[$v['p_p']]);
                    }

                    if(isset($user_list_info[$v['p_p']])){
                        $kpi_list[$v['p_p']]['remark_all'] = count($user_list_info[$v['p_p']]);
                    }
                    $kpi_list[$v['p_p']]['remark_count']=$v['count_num'];
                    $kpi_list[$v['p_p']]['remark_present']= countPresent($v['count_num'],$kpi_list[$v['p_p']]['remark_all']);
                }
            }

            /**
             * 月维护
             * 该客服-该月-新增分配用户-有工单用户数
             */
            $where = [];
            $where[] = ['status','>',0];
            //该月
            $where[] = ['add_time','>=',$time_arr['starttime']];
            $where[] = ['add_time','<=',$time_arr['endtime']];
            //新增分配用户
            $where[] = ['p_u','in',$user_list_info_p_u];
            $where[] = ['kf_id','=',$admin_id];

            $this_info = $SellWorkOrder->field('p_p,count(distinct(p_u)) AS count_num')->where(setWhereSql($where,''))->group('p_p')->select()->toArray();
//            dd($this_info,1);
            if($this_info){
                foreach ($this_info AS $k =>$v){

                    if(!isset($kpi_list[$v['p_p']])){
                        $this_p_p_arr = explode('_',$v['p_p']);

                        if(count($this_p_p_arr) != 2){
                            continue;
                        }
                        $kpi_list[$v['p_p']] = $common_data;
                        $kpi_list[$v['p_p']]['kpi_value'] = 0;
                        $kpi_list[$v['p_p']]['platform_id'] = $this_p_p_arr[0];
                        $kpi_list[$v['p_p']]['product_id'] = $this_p_p_arr[1];
                        $kpi_list[$v['p_p']]["p_p"] = $v['p_p'];
                        self::initStatisticSWOKpiVal($kpi_list[$v['p_p']]);
                    }

                    if(isset($user_list_info[$v['p_p']])){
                        $kpi_list[$v['p_p']]['month_user_all'] = count($user_list_info[$v['p_p']]);
                    }
                    $kpi_list[$v['p_p']]['month_user_count']=$v['count_num'];
                    $kpi_list[$v['p_p']]['month_user_present']= countPresent($v['count_num'],$kpi_list[$v['p_p']]['month_user_all']);

                }
            }
        }

        // 最后相关统计计算
        foreach ($kpi_list as $k => &$kl_v){
            if($kl_v['sum_kpi_value']>0){
                $kl_v['order_kip_present'] = countPresent($kl_v['order_sum'],$kl_v['sum_kpi_value']);
            }
            if($kl_v['order_sum']>0){
                $kl_v['big_order_sum_present'] = countPresent($kl_v['big_order_sum'],$kl_v['order_sum']);
            }
            if($kl_v['washed_away_all']>0){
                $kl_v['washed_away_present'] = countPresent(($kl_v['washed_away_all']-$kl_v['no_washed_away_count']),$kl_v['washed_away_all']);
                $kl_v['washed_away_present_str'] = $kl_v['washed_away_present'].'%('.($kl_v['washed_away_all']-$kl_v['no_washed_away_count']).'/'.$kl_v['washed_away_all'].')';
            }

            if($kl_v['maintenance_all']>0){
                $kl_v['maintenance_present'] = countPresent($kl_v['maintenance_count'],$kl_v['maintenance_all']);
                $kl_v['maintenance_present_str'] = $kl_v['maintenance_present'].'%('.$kl_v['maintenance_count'].'/'.$kl_v['maintenance_all'].')';
            }

            if($kl_v['remark_all']>0){
                $kl_v['remark_present'] = countPresent($kl_v['remark_count'],$kl_v['remark_all']);
                $kl_v['remark_present_str'] = $kl_v['remark_present'].'%('.$kl_v['remark_count'].'/'.$kl_v['remark_all'].')';
            }
        }

        return $kpi_list;
    }

    protected static function getMonthKfVipUserStatistic($param){

        $time_arr = timeCondition('month',$param['kpi_date']);

        $where = getDataByField($param,['platform_id','group_id'],1);

        if(isset($param['admin_platform_id']) && $param['admin_platform_id'] ){
            $where[] = getWhereDataArr($param['admin_platform_id'],'platform_id');
        }

        if(isset($param['admin_id']) && $param['admin_id'] ){
            $where[] = getWhereDataArr($param['admin_id'],'admin_id');
        }
        if(isset($param['p_p']) && $param['p_p']){
            $where[] = getWhereDataArr($param['p_p'],'p_p');
        }

        $last_day = $time_arr['endtime']>time()?strtotime(date('Y-m-d 23:59:59'))-3600*24:$time_arr['endtime'];
        $where[] = ['day','>=',$last_day+1-(3600*24)];
        $where[] = ['day','<',$last_day+1];
        // 查询当月所有
//        $where[] = ['day','>=',$time_arr['starttime']];
//        $where[] = ['day','<',$time_arr['endtime']];

        $VipKfDayStatistic = new VipKfDayStatistic();
        $SellWorkOrder = new SellWorkOrder();

        $res = [
            'maintenance_count'=>0,//当月分配活跃用户填写工单数
            'maintenance_all'=>0,//当月分配活跃用户
            'add_user_count_all'=>0,//所有分配用户
            'add_washed_away'=>0,//新增充值流失用户
            'washed_away_all'=>0,//历史充值流失用户
            'add_login_lost_count'=>0,//新增登录流失用户
            'login_lost_all'=>0,//历史登录流失用户
            'month_user_all'=>0,//月新增分配
            'remark_all'=>0,//月新增分配
            'remark_count'=>0,//月新增分配
        ];


        $field = '
            max(day) as day
            ,p_g
            ,admin_id
        ';

        $max_day_list = $VipKfDayStatistic->field($field)->where(setWhereSql($where,''))->group('p_g,admin_id')->select()->toArray();

        if(!$max_day_list){
            return $res;
        }
//        dd($max_day_list);
        $new_max_day_list = [];
        $where_or = [];
        foreach ( $max_day_list as $item) {
            $new_max_day_list[$item['admin_id']][$item['day']][] = $item['p_g'];
//            $res['add_washed_away'] += $item['add_washed_away'];
//            $res['add_login_lost_count'] += $item['add_login_lost_count'];
        }
        foreach ($new_max_day_list as $k => $v){

            $this_sql = '(admin_id = '.$k.' AND ';

            $this_sql_arr = [];
            foreach ($v as $v1_k => $v1_v){
                $this_sql_arr[]= '(day = '.$v1_k.' AND p_g in("'.implode('","',$v1_v).'"))';
            }
            $this_sql .= '('.implode(' OR ',$this_sql_arr).')';
            $this_sql .= ')';
            $where_or[] = $this_sql;
        }

        $where = getDataByField($param,['platform_id'],true);

        if(isset($param['admin_id']) && $param['admin_id'] ){
            $where[] = getWhereDataArr($param['admin_id'],'admin_id');
        }

        $where['_string'] = '('.implode(' OR ',$where_or).')';

        $list = $VipKfDayStatistic->where(setWhereSql($where,''))->select()->toArray();

        if(!$list){
            return $res;
        }

        $active_count_all_arr = [];
        $add_user_count_month_arr = [];
        foreach ($list as $v){
            if($v['active_count_all']>0){
                $res['maintenance_all'] += $v['active_count_all'];
                $this_str = $v['active_user_str_all'];
                $active_count_all_arr = array_merge($active_count_all_arr,explode(',',$this_str));
            }
            $res['add_user_count_all'] += $v['add_user_count_all'];
//            $res['add_washed_away'] += $v['add_washed_away'];
            $res['add_washed_away'] += $v['add_washed_away_month'];
            $res['washed_away_all'] += $v['washed_away_all'];
//            $res['add_login_lost_count'] += $v['add_login_lost_count'];
            $res['add_login_lost_count'] += $v['add_login_lost_month'];
            $res['login_lost_all'] += $v['login_lost_all'];
            $res['month_user_all'] += $v['add_user_count_month'];

            if($v['add_user_count_month']>0){
                $res['remark_all'] += $v['add_user_count_month'];
                $this_str = $v['add_user_str_month'];
                $this_str_pre = $v['platform_id'].'_';

                $this_str = $this_str_pre.str_replace(',',','.$this_str_pre,$this_str);
                $add_user_count_month_arr = array_merge($add_user_count_month_arr,explode(',',$this_str));
            }
        }

        if($active_count_all_arr){
            $where = [];

            $where[] = ['add_time','>=',$time_arr['starttime']];
            $where[] = ['add_time','<',$time_arr['endtime']+1];
            $where[] = ['p_u','in',$active_count_all_arr];
            $res['maintenance_count'] = $SellWorkOrder->where(setWhereSql($where,''))->count('distinct p_u');
        }

        if($add_user_count_month_arr){
            $where = [];
            $where[] = ['add_time','>=',$time_arr['starttime']];
            $where[] = ['add_time','<',$time_arr['endtime']+1];
            $where[] = ['p_u','in',$add_user_count_month_arr];
            $where[] = ['status','>',0];
            $where[] = ['type','=',SellWorkOrder::REMARK_ORDER];
            $res['remark_count'] = $SellWorkOrder->where(setWhereSql($where,''))->count('distinct p_u');
        }

        return $res;
    }

    protected static function initStatisticSWOKpiVal(&$val){

        //查询产品名称
        $val['order_sum'] = 0;//订单总额
        $val['order_count'] = 0;//订单数
        $val['sum_kpi_value'] = getArrVal($val,'kpi_value',0);//月总kpi
        $val['order_kip_present'] = 0;//kpi完成百分百

        $val['big_order_sum'] = 0;//大单总额
        $val['big_order_count'] = 0;//大单总数
        $val['big_order_sum_present'] = 0;//大单占比
        $val['big_order_sum_present_str'] = 0;//大单占比

        $val['remark_count'] = 0;//当月分配用户登记工单数
        $val['remark_all'] = 0;//当月分配用户总数数
        $val['remark_present'] = 0;//月联系率
        $val['remark_present_str'] = 0;//月联系率

        $val['maintenance_count'] = 0;//活跃用户维护工单数
        $val['maintenance_all'] = 0;//活跃人数
        $val['maintenance_present'] = 0;//活跃覆盖率
        $val['maintenance_present_str'] = 0;//活跃覆盖率

        $val['washed_away_count'] = 0;//历史分配用户总数
        $val['washed_away_all'] = 0;//该月流失用户数量
        $val['no_washed_away_count'] = 0;//该月未流失用户数量
        $val['washed_away_present'] = 0;//活跃覆盖率
        $val['washed_away_present_str'] = 0;//活跃覆盖率

        $val['month_user_count'] = 0;//维护用户数
        $val['month_user_all'] = 0;//需维护人数
        $val['month_user_present'] = 0;//维护覆盖率
        $val['month_user_present_str'] = 0;//维护覆盖率

        $val['login_lost_count'] = 0;//历史分配用户总数
        $val['login_lost_all'] = 0;//该月流失用户数量
        $val['no_login_lost_count'] = 0;//该月未流失用户数量
        $val['login_lost_present'] = 0;//活跃覆盖率
        $val['login_lost_present_str'] = 0;//活跃覆盖率

        $val['sell_commission'] = 0;//提成
    }
}
