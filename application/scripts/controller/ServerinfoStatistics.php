<?php


namespace app\scripts\controller;



use common\server\Statistic\ServerinfoStatisticsServer;

class ServerinfoStatistics extends Base
{


    protected $func_arr = [
        'server_info'    =>['func'=>'insertIntoServerInfo','param'=>[],'delay_time'=>0,'runtime'=>86400,'limit'=>0,'is_single'=>1],

    ];

    public function run()
    {
//        $index = $this->request->get('action');
        $params = $this->request->get('p/a',[]);
        $index = $params['action'] ?: '';


        $this->func_arr[$index]['param'] = isset($params) && !empty($params) ? $params : '';

        $res = $this->apiRunOne($this->func_arr[$index]);//调用$func_arr里面配置方法
        echo json_encode($res);
    }



    //统计每个角色每个区服的注册充值情况
    public function insertIntoServerInfo($params)
    {

        ini_set('memory_limit', '2048M');
        $tmpTime = time();
        $obj = new ServerinfoStatisticsServer();
        $obj->dealParams($params);
        $res = $obj->insertIntoServerInfo();

        return ['code'=>$res,'data'=>[]];
    }

    //自动按产品分配区服给个人
    public function autoDistributeServer(){

        ini_set('memory_limit','3072M');
        ini_set("display_errors", "On");
        error_reporting(E_ALL | E_STRICT);

        $obj = new ServerinfoStatisticsServer();
        $res = $obj->autoDistributeServer();

        return ['code'=>$res,'data'=>[]];
    }




    //自动按游戏分配区服给个人
    public function autoDistributeGameServer(){

        ini_set('memory_limit','3072M');
        ini_set("display_errors", "On");
        error_reporting(E_ALL | E_STRICT);
        $obj = new ServerinfoStatisticsServer();
        $res = $obj->autoDistributeGameServer();
        return ['code'=>$res,'data'=>[]];

    }



}


