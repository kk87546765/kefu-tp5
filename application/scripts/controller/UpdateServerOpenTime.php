<?php
namespace app\scripts\controller;

use common\server\UpdateServerOpenTime\UpdateServerOpenTimeServer;
/**
 * 用户注册登录支付行为接口类
 *先跑注册接口（user_register）,再跑登录日志接口（user_login）,不然注册表里面的手机号，最后登录时间无法更新到
 * @author tomson
 */
class UpdateServerOpenTime extends Base
{

    protected $func_arr = [
        'uppate'    =>['func'=>'updateServerOpenTime','param'=>[],'delay_time'=>0,'runtime'=>86400,'limit'=>0,'is_single'=>1],
    ];

    public function run()
    {
        $params = $this->request->get('p/a',[]);
        $index = $params['action'] ?: '';
        $this->func_arr[$index]['param'] = isset($params) && !empty($params) ? $params : '';

        $res = $this->apiRun($this->func_arr[$index]);//调用$func_arr里面配置方法
        echo json_encode($res);
    }

    //更新开服时间
    public function updateServerOpenTime($params)
    {
        $obj = new UpdateServerOpenTimeServer();

        $res = $obj->updateServerOpenTime($params);

        return ['code'=>$res['code'],'data'=>$res['data']];


    }

}
