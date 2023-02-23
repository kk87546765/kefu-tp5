<?php


namespace app\scripts\controller;

use common\server\Statistic\GameServerinfoStatisticsServer;

class GameServerinfoStatistics extends Base
{

    protected $func_arr = [
        'server_info'    =>['func'=>'insertIntoServerInfo','param'=>[],'delay_time'=>0,'runtime'=>86400,'limit'=>0,'is_single'=>1],

    ];

    public function run()
    {
//        $index = $this->request->get('action');
        $params = $this->request->get('p/a',[]);
        $index = $params['action'] ?: '';
        $no_cache = $params['no_cache'] ?? 0;
        if($no_cache){
            if(isset($this->func_arr[$index]['func'])){
                $this->clean($this->func_arr[$index]['func']);
            }
        }

        $this->func_arr[$index]['param'] = isset($params) && !empty($params) ? $params : '';

        $res = $this->apiRunOne($this->func_arr[$index]);//调用$func_arr里面配置方法
        echo json_encode($res);
    }






    //统计每个角色每个区服的注册充值情况(子游戏维度)
    public function insertIntoServerInfo($params)
    {

        ini_set('memory_limit', '5000M');
        ini_set("display_errors", "On");
        error_reporting(E_ALL | E_STRICT);
        $tmpTime = time();

        $obj = new GameServerinfoStatisticsServer();
        $obj->dealParams($params);
        $res = $obj->insertIntoServerInfo();
        return ['code'=>$res,'data'=>[]];

    }


    //自动按游戏分配区服给个人
    public function autoDistributeGameServer(){

        ini_set('memory_limit','3072M');
        ini_set("display_errors", "On");
        error_reporting(E_ALL | E_STRICT);
        $obj = new GameServerinfoStatisticsServer();
        $res = $obj->autoDistributeGameServer();
        return ['code'=>$res,'data'=>[]];

    }

    public function clean($name)
    {
        $this->apiClean($name);
    }


}


