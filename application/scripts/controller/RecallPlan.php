<?php
namespace app\scripts\controller;

use common\server\RecallPlan\RecallPlanServer;
use common\server\Vip\LossUserServer;


class RecallPlan extends Base
{

    public $scripts_params = [];

    protected $func_arr = [
        'executeRecallPlan'       => ['func'=>'executeRecallPlan','param'=>'','delay_time'=>1000,'runtime'=>86400,'limit'=>0,'is_single'=>1],
        'excuteRecallPlanLog'     => ['func'=>'excuteRecallPlanLog','param'=>'','delay_time'=>1500,'runtime'=>86400,'limit'=>0,'is_single'=>1],
        'sendRecallPlanLog'       => ['func'=>'sendRecallPlanLog','param'=>'','delay_time'=>2000,'runtime'=>86400,'limit'=>0,'is_single'=>1],
        'sendRecallPlanPeopleLog' => ['func'=>'sendRecallPlanPeopleLog','param'=>'','delay_time'=>2300,'runtime'=>110,'limit'=>0,'is_single'=>1],
        'updatePlanLogSendNum'    => ['func'=>'updatePlanLogSendNum','param'=>'','delay_time'=>3000,'runtime'=>7200,'limit'=>0,'is_single'=>1],


    ];

    public function run()
    {
        set_time_limit(0);
        ini_set('memory_limit', '4096M');
        $this->apiRun();
    }

    //计划表插入到计划记录表
    public function executeRecallPlan($params)
    {
        $is_friday = $this->isFriday();
        if(!$is_friday){
            $result = ['code'=>1, 'data'=>[]];
            return $result;
        }

        $result = RecallPlanServer::executeLossPlan($params);

        return $result;
    }

    //计划记录表插入到计划记录明细表
    public function excuteRecallPlanLog($params)
    {
        $is_friday = $this->isFriday();
        if(!$is_friday){
            $result = ['code'=>1, 'data'=>[]];
            return $result;
        }
        $result = RecallPlanServer::executeLossPlanLog($params);

        return $result;
    }


    //发送计划记录信息
    public function sendRecallPlanLog($params)
    {
        $is_friday = $this->isFriday();
        if(!$is_friday){
            $result = ['code'=>1, 'data'=>[]];
            return $result;
        }
        $result = RecallPlanServer::sendRecallPlanLog($params);

        return $result;
    }


    //发送计划记录明细信息
    public function sendRecallPlanPeopleLog($params)
    {

        $is_friday = $this->isFriday();
        if(!$is_friday){
            $result = ['code'=>1, 'data'=>[]];
            return $result;
        }
        $result = RecallPlanServer::sendRecallPlanPeopleLog($params);

        return $result;
    }

    //更新发送次数
    public function updatePlanLogSendNum($params)
    {
        $result = RecallPlanServer::updatePlanLogSendNum($params);

        return $result;
    }


    //手动推送某计划
    public function manualSendRecallPlanLog()
    {
        $params = $this->request->get('p/a',[]);
        //$params['plan_log_id'] = 1;
        $result = RecallPlanServer::sendRecallPlanLog($params);
        return $result;
    }


    //手动推送某计划的全部用户
    public function manualSendRecallPlanPeopleLog()
    {
        ini_set('memory_limit', '4096M');
        $params = $this->request->get('p/a',[]);
        //$params['plan_log_id'] = 1;
        $result = RecallPlanServer::sendRecallPlanPeopleLog($params);
        self::updatePlanLogSendNum([]);

    }

    public function diyExec()
    {
        $params = $this->request->get('p/a',[]);
        $func = $params['func'];

        $this->$func([]);
    }

    public function clean()
    {
        $func = !empty($_GET['func']) ? $_GET['func'] : 'all';
        $cache = !empty($_GET['cache']) ? $_GET['cache'] : '';

        if($cache){
            cache($cache,null);
        }

        $this->apiClean($func);
    }

    private function isFriday()
    {
        $ga = date("w");
        if($ga == 5){
            return true;
        }else{
            return false;
        }
    }


}
