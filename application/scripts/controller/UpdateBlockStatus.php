<?php


namespace app\scripts\controller;

use common\server\UpdateBlockStatus\UpdateBlockStatusServer;


class UpdateBlockStatus extends Base
{

    protected $func_arr = [
        'update'    =>['func'=>'updateBlockStatus','param'=>[],'delay_time'=>0,'runtime'=>60,'limit'=>0,'is_single'=>1],
        'update2'    =>['func'=>'updateRoleNameBlockStatus','param'=>[],'delay_time'=>0,'runtime'=>60,'limit'=>0,'is_single'=>1],

    ];

    public function run()
    {
        $params = $this->request->get('p/a',[]);
        $index = $params['action'] ?: '';
        $this->func_arr[$index]['param'] = isset($params) && !empty($params) ? $params : '';

        $res = $this->apiRun($this->func_arr[$index]);//调用$func_arr里面配置方法
        echo json_encode($res);
    }

    public function updateBlockStatus()
    {
        $obj = new UpdateBlockStatusServer();
        $res = $obj->updateBlockStatus();
        return ['code'=>$res['code'],'data'=>$res['data']];
    }


    public function updateRoleNameBlockStatus()
    {
        $obj = new UpdateBlockStatusServer();
        $res = $obj->updateRoleNameBlockStatus();
        return ['code'=>$res['code'],'data'=>$res['data']];
    }


    public function clean()
    {
        $func = !empty($_GET['func']) ? $_GET['func'] : 'all';

        $this->apiClean($func);
    }


}