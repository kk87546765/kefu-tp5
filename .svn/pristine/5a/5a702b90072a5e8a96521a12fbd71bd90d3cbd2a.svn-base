<?php
namespace app\scripts\controller;

use common\server\Statistic\GameProductServer;

class GameProduct extends Base
{
//    private $start_time,$end_time,$num,$platform,$time;
//    protected $interface_url = [];

    protected $func_arr = [
        ['func'=>'GameProduct','param'=>[0=>''],'delay_time'=>60*5,'runtime'=>60*5,'limit'=>0,'is_single'=>1],//拉取产品
        ['func'=>'GameList','param'=>[0=>''],'delay_time'=>60*5,'runtime'=>60*5,'limit'=>0,'is_single'=>1],//拉取游戏
    ];
    #脚本调用
    public function run()
    {
        $this->apiRun();//循环调用$func_arr里面配置方法
    }
    #检查脚本锁情况、执行次数
    public function check()
    {
        dd($this->apiCheckFuncList());
    }
    #清除脚本锁缓存
    public function clean()
    {
        $func = $this->req->get('func/s','all');
        dd($this->apiClean($func));
    }
    public function test(){
        $func = $this->request->get('func/s','');

        $this->apiRunOne(getArrVal($this->func_arr,$func,[]));
    }

    public function test1(){

        $this->GameProduct([0=>'platform=ll']);

    }
    /**
     * 收集平台子游戏，从各个平台接口获取，每天凌晨执行更新数据
     * 1、传平台标识跑单个平台数据，没有轮询所有的平台
     * 2、可单传开始时间也可开始时间和结束时间都不传，不传开始时间就是昨天的凌晨时间节点、结束时间就是今天的凌晨的时间节点（时间为日期格式）
     * @param array $params
     */
    protected function GameProduct(array $params)
    {
        ini_set('memory_limit', '2048M');

        parse_str($params[0],$param);

        $result = ['code'=>1, 'data'=>['msg'=>'end']];

        set_time_limit(0);

        $param['end_time'] = isset($param['end_time']) ? strtotime(date('Y-m-d', strtotime($param['end_time']))) : time();
        $param['start_time']= isset($param['start_time']) ? strtotime(date('Y-m-d', strtotime($param['start_time']))) : (time() - 10*60);

        //获取平台列表

        $platformList = [];
        if ( !empty($param['platform']) ){
            $platform_id = getArrVal($this->config['platform_suffix'],$param['platform'],0);
            if(!$platform_id){
                $result['data']['msg'] = 'platform error';
                return $result;
            }
            $platformList[] = $this->config['platform_list'][$platform_id];

        }else {
            $platformList = $this->config['platform_list'];
        }

        if(!$platformList){
            $result['data']['msg'] = 'no platform';
            return $result;
        }

        foreach ($platformList as $k=>$v) {
            if (empty($v['config'])) continue;
            $result['data'][] = GameProductServer::updateProductInfo($v,$param);

        }

        return $result;
    }

    /**
     * 收集平台子游戏，从各个平台接口获取，每天凌晨执行更新数据
     * 1、传平台标识跑单个平台数据，没有轮询所有的平台
     * 2、可单传开始时间也可开始时间和结束时间都不传，不传开始时间就是昨天的凌晨时间节点、结束时间就是今天的凌晨的时间节点（时间为日期格式）
     * @param array $params
     */
    protected function GameList(array $params)
    {
        ini_set('memory_limit', '2048M');

        parse_str($params[0],$param);

        $result = ['code'=>1, 'data'=>['msg'=>'end']];

        set_time_limit(0);

        $param['end_time'] = isset($param['end_time']) ? strtotime(date('Y-m-d', strtotime($param['end_time']))) : time();
        $param['start_time']= isset($param['start_time']) ? strtotime(date('Y-m-d', strtotime($param['start_time']))) : (time() - 10*60);

        //获取平台列表

        $platformList = [];
        if ( !empty($param['platform']) ){
            $platform_id = getArrVal($this->config['platform_suffix'],$param['platform'],0);
            if(!$platform_id){
                $result['data']['msg'] = 'platform error';
                return $result;
            }
            $platformList[] = $this->config['platform_list'][$platform_id];

        }else {
            $platformList = $this->config['platform_list'];
        }

        if(!$platformList){
            $result['data']['msg'] = 'no platform';
            return $result;
        }

        foreach ($platformList as $k=>$v) {
            if (empty($v['config'])) continue;
            $result['data'][] = GameProductServer::updateGameInfo($v,$param);

        }

        return $result;
    }

}
