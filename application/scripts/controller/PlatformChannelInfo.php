<?php


namespace app\scripts\controller;


use common\Libraries\Common;

//use common\Models\Statistic\EveryDayOrderCount;
//use common\Models\PlatformChannelInfo;
use common\server\Platform\ChannelInfoServer;
use common\server\SysServer;

class PlatformChannelInfo extends Base
{

    protected $start_time,$end_time,$num,$platform,$time;
    protected $platform_arr = array('ll','mh','asjd','zw','xll','youyu');

    protected $interface_url = [];



    protected $func_arr = [
        'platform_channel_info'    =>['func'=>'platformChannelInfo','param'=>[],'delay_time'=>0,'runtime'=>86400,'limit'=>0,'is_single'=>1],

    ];

    public function run()
    {
        ini_set('memory_limit', '2048M');

        $params = $this->request->get('p/a',[]);

        $index = $params['action'] ?? '';
        $this->func_arr[$index]['param'] = isset($params) && !empty($params) ? $params : '';

        $res = $this->apiRunOne($this->func_arr[$index]);//调用$func_arr里面配置方法

        echo json_encode($res);
    }





    /**
     * 收集平台子游戏，从各个平台接口获取，每天凌晨执行更新数据
     * 1、传平台标识跑单个平台数据，没有轮询所有的平台
     * 2、可单传开始时间也可开始时间和结束时间都不传，不传开始时间就是昨天的凌晨时间节点、结束时间就是今天的凌晨的时间节点（时间为日期格式）
     * @param array $params
     */
    public function platformChannelInfo(array $params)
    {
        $obj = new ChannelInfoServer();
        $res = $obj->platformChannelInfo($params);
        return ['code'=>$res,'data'=>[]];

    }




}