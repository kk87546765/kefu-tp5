<?php
namespace app\scripts\controller;

use common\server\Statistic\LtvServerStatisticServer;


/**
 * 用户注册登录支付行为接口类
 *先跑注册接口（user_register）,再跑登录日志接口（user_login）,不然注册表里面的手机号，最后登录时间无法更新到
 * @author tomson
 */
class LtvServerStatistic extends Base
{
    protected $reg_data = [],$type_arr=[];
    protected $reg_model = '',$recharge_model = '';
    protected $platform_arr = array('ll','mh','asjd','zw','xll','youyu');


    //线上
    protected $interface_url = [];
    private $start_time;


    protected $func_arr = [
        'statistic'    =>['func'=>'statistic','param'=>[],'delay_time'=>0,'runtime'=>86400,'limit'=>0,'is_single'=>1],

    ];

    public function run()
    {
        $params = $this->request->get('p/a',[]);
        $index = $params['action'] ?: '';
        $this->func_arr[$index]['param'] = isset($params) && !empty($params) ? $params : '';

        $res = $this->apiRunOne($this->func_arr[$index]);//调用$func_arr里面配置方法
        echo json_encode($res);
    }


    //跟踪统计区服充值情况，计算ltv
    public function statistic()
    {
        $now_time = strtotime(date('Y-m-d'))-86400;
        $res = LtvServerStatisticServer::statisticLTV($now_time);
        return ['code'=>$res,'data'=>[]];
    }


    //跟踪统计区服充值情况，计算ltv更新
    public function statisticForUpdate($params)
    {
        $res = LtvServerStatisticServer::statisticForUpdate($params);
        return ['code'=>true,'data'=>[]];
    }

}
