<?php
/**
 * vip统计
 */
namespace app\admin\controller;

use common\server\Vip\SellWorkOrderServer;
use common\server\Vip\StatisticServer AS thisServer;
class VipStatistic extends Oauth
{
    protected $no_oauth = [
        'kfDayStatisticListConfig',
        'getVipKfDayStatistic',
        'userRechargeListConfig',
        'sellStatisticMonthListConfig',
        'sellStatisticMonthInfo',
        'kfMonthProductStatisticListConfig',
        'statisticSwoProductAmountAreaConfig',
        'marketingStageReportConfig',
        'marketingStageReportPersonalConfig',
        'marketingStageReportRechargeConfig',
        'marketingStageReportRechargePersonalConfig',
        'marketingStageReportTimeConfig',
        'marketingStageReportTimePersonalConfig',
    ];

    public function kfDayStatisticList(){

        $time_arr = timeCondition('month');

        $p_data = $this->getPost([
            ['page','int',1],
            ['limit','int',20],
            ['day_start','trim',date('Y-m-d',$time_arr['starttime'])],//other
            ['day_end','trim',''],
            ['platform_id','trim',''],
            ['p_p','trim',''],
            ['p_g','trim',''],
            ['group_id','int',0],
            ['admin_id','trim',''],
        ]);

        list($list,$count) = thisServer::kfDayStatisticList($p_data);

        $this->rs['data'] = $list;
        $this->rs['count'] = $count;

        return return_json($this->rs);
    }

    public function kfDayStatisticListConfig(){

        $this->rs['data'] = thisServer::kfDayStatisticListConfig();

        return return_json($this->rs);
    }

    #vip用户每日-统计
    public function getVipKfDayStatistic(){

        $time_arr = timeCondition('month');

        $p_data = $this->getPost([
            ['day_start','trim',date('Y-m-d',$time_arr['starttime'])],//other
            ['day_end','trim',''],
            ['platform_id','trim',''],
            ['p_p','trim',''],
            ['p_g','trim',''],
            ['group_id','int',0],
            ['admin_id','trim',''],
        ]);

        $this->rs['data'] = thisServer::getVipKfDayStatistic($p_data);

        return return_json($this->rs);

    }

    #vip用户充值-列表
    public function userRechargeList(){

        $params = $this->getPost([
            ['page','int',1],
            ['limit','int',20],
            ['pay_min','int',0],
            ['pay_max','int',0],
            ['is_record','int',0],
            ['rp_examine_status','int',-1],
            ['rp_apply_status','int',0],
            ['uid','int',0],
            ['server_id_min','int',0],
            ['server_id_max','int',0],
            ['is_excel','int',0],
            ['p_p','trim',''],
            ['platform_id','trim',''],
            ['user_name','trim',''],
            ['server_prefix','trim',''],
            ['server_suffix','trim',''],
            ['vip_commissioner','trim',''],
            ['role_id','trim',''],
            ['role_name','trim',''],
            ['p_g','trim',''],
            ['start_time','trim',date('Y-m-d',strtotime('-7 day'))],
            ['end_time','trim',date('Y-m-d')],
        ]);

//        $params['start_time'] = '2021-05-26';
//        $params['end_time'] = '2021-05-26';

        $this->rs = array_merge($this->rs,thisServer::userRechargeList($params));

        return return_json($this->rs);
    }
    #vip用户充值-配置
    public function userRechargeListConfig(){
        $this->rs['data'] = thisServer::userRechargeListConfig();

        return return_json($this->rs);
    }
    #vip用户充值-导出
    public function userRechargeListExcel(){
        ini_set('memory_limit', '2048M');
        $this->rs['name'] = 'vip充值列表'.date('YmdHis');
        return $this->userRechargeList();
    }

    /*营销月统计-列表*/
    public function sellStatisticMonthList(){
        ini_set('memory_limit', '2048M');

        $p_data = $this->getPost([
            ['page','int',1],
            ['limit','int',20],
            ['month','trim',''],
            ['p_p','trim',''],
            ['group_id','int',0],
            ['platform_id','trim',''],
            ['admin_id','trim',''],
            ['big_order_limit','int',5000],
            ['data_type','int',1],
        ]);

        if($p_data['month']){
            $p_data['month'] = strtotime($p_data['month']);
        }else{
            $p_data['month'] = strtotime(date('Y-m'));
        }
        list($list,$count) = SellWorkOrderServer::getSWOMonth($p_data);

        $this->rs['data'] = $list;
        $this->rs['count'] = $count;

        return return_json($this->rs);
    }

    public function sellStatisticMonthListExcel(){
        ini_set('memory_limit', '2048M');
        return $this->sellStatisticMonthList();
    }

    /*营销月统计-列表配置*/
    public function sellStatisticMonthListConfig(){

        $this->rs['data'] = SellWorkOrderServer::sellStatisticMonthListConfig();//游戏列表

        return return_json($this->rs);
    }

    /*营销月统计-全部数据统计*/
    public function sellStatisticMonthInfo(){

        ini_set('memory_limit', '2048M');

        $p_data = $this->getPost([
            ['month','trim',''],
            ['p_p','trim',''],
            ['platform_id','trim',''],
            ['group_id','int',0],
            ['admin_id','trim',''],
            ['big_order_limit','int',5000],
            ['data_type','int',1],
        ]);

        if($p_data['month']){
            $p_data['month'] = strtotime($p_data['month']);
        }else{
            $p_data['month'] = strtotime(date('Y-m'));
        }

        $this->rs['data'] = SellWorkOrderServer::statisticSWOMonth($p_data);

        return return_json($this->rs);

    }

    /*营销月统计-详情*/
    public function sellStatisticMonthDetail(){

        $p_data = $this->getPost([
            ['month','trim',''],
            ['p_p','trim',''],
            ['platform_id','trim',''],
            ['admin_id','int',0],
            ['big_order_limit','int',0],
            ['active_user_recharge_limit','int',0],
        ]);
        if($p_data['month']){
            $p_data['month'] = strtotime($p_data['month']);
        }else{
            $p_data['month'] = strtotime(date('Y-m'));
        }

        list($list,$count) = SellWorkOrderServer::getSWOMonthByAdmin($p_data,0,0);

        $this->rs['data'] = $list;
        $this->rs['count'] = $count;

        return return_json($this->rs);
    }

    #vip用户-月产品统计-列表
    public function kfMonthProductStatisticList(){

        $time_arr = timeCondition('month');
        $p_data = $this->getPost([
            ['page','int',1],
            ['limit','int',20],
            ['month','trim',date('Y-m',$time_arr['starttime'])],//other
            ['platform_id','trim',''],
            ['p_p','trim',''],
            ['p_g','trim',''],
            ['group_id','int',0],
            ['admin_id','trim',''],
        ]);

        list($list,$count_res) = thisServer::getVipKfMonthProductStatisticList($p_data);

        $this->rs['data'] = $list;
        $this->rs['count'] = count($list);
        $this->rs['count_res'] = $count_res;

        return return_json($this->rs);
    }

    #vip用户-月产品统计-列表配置
    public function kfMonthProductStatisticListConfig(){

        $this->rs['data'] = thisServer::kfMonthProductStatisticListConfig();

        return return_json($this->rs);
    }

    #营销单笔报表-工单-游戏、月份为维度，统计 每个工单 不同工单金额范围内 工单数据
    public function statisticSwoProductAmountArea(){

        $p_data = $this->getPost([
            ['page','int',1],
            ['limit','int',20],
            ['pay_time_start','trim',''],
            ['pay_time_end','trim',''],
            ['p_p','trim',''],
            ['platform_id','trim',''],
            ['big_order_limit','int',thisServer::$big_order_limit_def],
        ]);

        list($list,$count) = thisServer::statisticProductAmountAreaMonth($p_data);

        $this->rs['data'] = $list;
        $this->rs['count'] = $count;

        return return_json($this->rs);
    }

    #营销单笔报表-工单-游戏、月份为维度，统计 每个工单 不同工单金额范围内 工单数据
    public function statisticSwoProductAmountAreaConfig(){
        $this->rs['data'] = thisServer::statisticSwoProductAmountAreaConfig();
        return return_json($this->rs);
    }

    public function marketingStageReport()
    {
        ini_set('memory_limit', '2048M');
        $params = $this->getPost([
            ['pay_time_start','trim',''],
            ['pay_time_end','trim',''],
            ['p_p','trim',''],
            ['platform_id','int',0],
            ['group_id','int',0],
            ['server_id_min','int',0],
        ]);

        $this->rs['data'] = thisServer::getMarketingStageReport($params);

        return return_json($this->rs);
    }

    public function marketingStageReportConfig()
    {

        $this->rs['data'] = thisServer::marketingStageReportConfig();

        return return_json($this->rs);
    }

    public function marketingStageReportPersonal(){
        ini_set('memory_limit', '2048M');

        $params = $this->getPost([
            ['pay_time_start','trim',''],
            ['pay_time_end','trim',''],
            ['p_p','trim',''],
            ['platform_id','int',0],
            ['group_id','int',0],
            ['server_id_min','int',0],
            ['ascription_vip','int',0],
            ['type','int',0],
        ]);

        $this->rs['data'] = thisServer::getMarketingStageReport($params);

        return return_json($this->rs);
    }

    public function marketingStageReportPersonalConfig(){
        $this->rs['data'] = thisServer::marketingStageReportPersonalConfig();

        return return_json($this->rs);
    }

    public function marketingStageReportRecharge()
    {
        ini_set('memory_limit', '2048M');
        $params = $this->getPost([
            ['pay_time_start','trim',''],
            ['pay_time_end','trim',''],
            ['p_p','trim',''],
            ['platform_id','int',0],
            ['group_id','int',0],
            ['server_id_min','int',0],
            ['ascription_vip','int',0],
            ['type','int',0],
        ]);

        $this->rs['data'] = thisServer::getMarketingStageReportRecharge($params);

        return return_json($this->rs);
    }

    public function marketingStageReportRechargeConfig()
    {
        $this->rs['data'] = thisServer::marketingStageReportRechargeConfig();

        return return_json($this->rs);
    }

    public function marketingStageReportRechargePersonal()
    {
        return $this->marketingStageReportRecharge();
    }

    public function marketingStageReportRechargePersonalConfig()
    {
        $this->rs['data'] = thisServer::marketingStageReportRechargePersonalConfig();

        return return_json($this->rs);
    }

    public function marketingStageReportTime()
    {
        ini_set('memory_limit', '2048M');
        $params = $this->getPost([
            ['pay_time_start','trim',''],
            ['pay_time_end','trim',''],
            ['p_p','trim',''],
            ['platform_id','int',0],
            ['group_id','int',0],
            ['server_id_min','int',0],
            ['ascription_vip','int',0],
            ['type','int',0],
        ]);

        $this->rs = array_merge($this->rs,thisServer::getMarketingStageReportTime($params));

        return return_json($this->rs);
    }

    public function marketingStageReportTimeConfig()
    {
        $this->rs['data'] = thisServer::marketingStageReportTimeConfig();

        return return_json($this->rs);
    }

    public function marketingStageReportTimePersonal()
    {
        return $this->marketingStageReportTime();
    }

    public function marketingStageReportTimePersonalConfig()
    {
        $this->rs['data'] = thisServer::marketingStageReportTimePersonalConfig();

        return return_json($this->rs);
    }
}
