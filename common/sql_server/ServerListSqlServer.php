<?php
/**
 * Created by PhpStorm.
 * User: crosstime
 * Date: 2018/8/14
 * Time: 下午5:31
 */
namespace common\sql_server;

use common\server\CustomerPlatform\CommonServer;
use common\model\db_customer_platform\KefuServerList;
use common\model\db_statistic\PlatformGameInfo;
use common\model\db_statistic\DistributionalGameServer;
class ServerListSqlServer extends BaseSqlServer
{


    public static function updateOpenTime($data)
    {
        //更新每个平台的server_list列表数据
        self::updateServerListOpenTime($data);

        //更新统计数据的区服管理列表数据
//        self::updateDistributionalServerOpenTime($data);
    }

    private static function updateServerListOpenTime($data)
    {

        $platform_game_info_model = new PlatformGameInfo();
        $sql = "select * from db_statistic.platform_game_info where platform_id = {$data['platform_id']} and product_id = {$data['product_id']}";

        $info = $platform_game_info_model->query($sql);

        if($info){
            $game_id = array_column($info,'game_id');
        }else{
            return false;
        }

        $game_id = implode(',',$game_id);

        $platform_model = CommonServer::getPlatformModel('KefuServerList',$data['platform_suffix']);
        foreach ($data['server_list'] as $k => $v) {
            $open_time = strtotime(date('Y-m-d',strtotime($v['open_time'])));

            $sql = "UPDATE db_customer_{$data['platform_suffix']}.kefu_server_list set open_time = {$open_time} where `server_id` ='{$v['sid']}' and `server_name` = '{$v['server_name']}' ";
            $res = $platform_model->execute($sql);
        }
        return true;

    }

    private static function updateDistributionalServerOpenTime($data)
    {
        $model = new DistributionalGameServer();
        foreach ($data['server_list'] as $k1 => $v1) {
            $open_time = strtotime(date('Y-m-d',strtotime($v1['open_time'])));
            $sql = "UPDATE db_statistic.distributional_game_server set open_time = {$open_time} where `server_id` = '{$v1['sid']}' and `server_name` = '{$v1['server_name']}' and product_id = {$data['product_id']} and platform_id = {$data['platform_id']}";
            $res = $model->execute($sql);

        }

        return true;
    }

//open_time = {$data['open_time']} where game_id in ({$game_id}) and platform_id = {$data['platform_id']} ";





}