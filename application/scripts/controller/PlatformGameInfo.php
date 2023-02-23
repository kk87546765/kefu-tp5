<?php

namespace app\scripts\controller;


use common\libraries\Common;
//
//use common\Models\Statistic\EveryDayOrderCount;
use common\server\Platform\GameInfoServer;
//use common\sql_server\PlatformGameInfo;

class PlatformGameInfo extends Base
{
    protected $func_arr = [
        'platform_game_list'    =>['func'=>'platformGameList','param'=>[],'delay_time'=>0,'runtime'=>86400,'limit'=>0,'is_single'=>1],

    ];

    public function run()
    {
        $params = $this->request->get('p/a',[]);

        $index = $params['action'] ?? '';
        $this->func_arr[$index]['param'] = isset($params) && !empty($params) ? $params : '';

        $res = $this->apiRunOne($this->func_arr[$index]);//调用$func_arr里面配置方法

        echo json_encode($res);
    }


    /**
     * 收集平台子游戏，是从充值数据sql统计获取，时效性不强先以弃用
     * 1、可单传开始时间也可开始时间和结束时间都不传，不传开始时间就是昨天的凌晨时间节点、结束时间就是今天的凌晨的时间节点（时间为日期格式）
     * @param array $params
     */
//    public function insertPlatformGame(array $params)
//    {
//        $model = new ChannelGameServer();
//        $res = $model->insertPlatformGame($params);
//        return ['code'=>$res['code'],'data'=>$res['data']];
//    }



    /**
     * 收集平台子游戏，从各个平台接口获取，每天凌晨执行更新数据
     * 1、传平台标识跑单个平台数据，没有轮询所有的平台
     * 2、可单传开始时间也可开始时间和结束时间都不传，不传开始时间就是昨天的凌晨时间节点、结束时间就是今天的凌晨的时间节点（时间为日期格式）
     * @param array $params
     */
    public function platformGameList(array $params)
    {
        ini_set('memory_limit', '2048M');
        $model = new GameInfoServer();
        $res = $model->platformGameList($params);
        return ['code'=>$res['code'],'data'=>$res['data']];
    }


}