<?php


namespace app\scripts\controller;

use common\libraries\Common;


use common\server\Statistic\EveryDayRoleAmountStatisticsServer;



class EveryDayRoleAmountStatistics extends Base
{


    protected $func_arr = [
        'role_amount'    =>['func'=>'roleAmountStatistics','param'=>[],'delay_time'=>0,'runtime'=>86400,'limit'=>0,'is_single'=>1],

    ];

    public function run()
    {
        $params = $this->request->get('p/a',[]);
        $index = $params['action'] ?: '';
        $this->func_arr[$index]['param'] = isset($params) && !empty($params) ? $params : '';

        $res = $this->apiRunOne($this->func_arr[$index]);//调用$func_arr里面配置方法
        echo json_encode($res);
    }


    //根据角色统计每日充值
    public function roleAmountStatistics($params)
    {
        ini_set('memory_limit', '4000M');
        set_time_limit(300);

        $obj = new EveryDayRoleAmountStatisticsServer();
        $obj->dealParams($params);
        $res = $obj->roleAmountStatistics();
        return ['code'=>$res,'data'=>[]];
    }


    //修复区服统计数据
    public function repairServerStatic($params)
    {
        $obj = new EveryDayRoleAmountStatisticsServer();
        $obj->dealParams($params);
        $res = $obj->repairServerStatic($params);
        return ['code'=>$res,'data'=>[]];
    }


}


