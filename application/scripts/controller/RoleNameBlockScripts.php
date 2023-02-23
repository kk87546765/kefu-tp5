<?php


namespace app\scripts\controller;

//use common\server\UpdateBlockStatus\UpdateBlockStatusServer;


use common\server\RoleNameKeyword\RoleNameKeywordServer;
use common\server\Scripts\RoleNameBlockScriptsServer;

class RoleNameBlockScripts extends Base
{

    protected $func_arr = [
        'checkRoleName'    =>['func'=>'checkRoleName','param'=>[],'delay_time'=>0,'runtime'=>1,'limit'=>0,'is_single'=>1],

    ];

    public function run()
    {
        $params = $this->request->get('p/a',[]);

        $index = $params['action'] ?: '';
        $this->func_arr[$index]['param'] = isset($params) && !empty($params) ? $params : '';

        $res = $this->apiRun($this->func_arr[$index]);//调用$func_arr里面配置方法
        echo json_encode($res);
    }


    public function checkRoleName($params)
    {
//        $obj = new RoleNameKeywordServer();
        $res = RoleNameBlockScriptsServer::run($params);

        return ['code'=>$res['code'],'data'=>$res['data']];
    }

    public function clean()
    {
        $func = !empty($_GET['func']) ? $_GET['func'] : 'all';

        $this->apiClean($func);
    }




}