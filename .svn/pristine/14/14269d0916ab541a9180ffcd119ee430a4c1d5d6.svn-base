<?php
/**
 * @author ambi
 * @date 2019/4/17
 */

namespace common\server\Statistic;

use common\base\BasicServer;
use common\sql_server\DistributionalGameServerSqlServer;
use common\libraries\Common;
use common\sql_server\LtvServerStatisticSqlServer;
class LtvServerStatisticServer extends BasicServer
{

    public static function statisticForUpdate($params)
    {
        $start_time= isset($params['start_time']) ? strtotime($params['start_time']) : strtotime(date('Y-m-d'))-86400;

        $platform = Common::getPlatformList();

        $add_data = [];

        foreach($platform as $k=>$v){

            if($v['platform_suffix'] == 'asjd') continue;
            $tmp_data = [
                'platform_id' => $v['platform_id'],
                'platform_suffix' => $v['platform_suffix'],
                'now_time' => $start_time
            ];

            $info[$v['platform_suffix']] = self::getTodayPayPeople($tmp_data);
            $new_info[$v['platform_suffix']] = self::getTodayPayNewPeople($tmp_data);

        }

        self::addData($info);
        self::updateData($new_info);

    }

    public static function statisticLTV($now_time)
    {
        $server_info = self::getDistributionalGameServer($now_time);

        $platform = Common::getPlatformList();

        $add_data = [];
        foreach($server_info as $k=>$v){
            $v['platform_suffix'] = $platform[$v['platform_id']]['platform_suffix'];
            $add_data[$v['platform_id'].'_'.$v['game_id'].'_'.$v['server_id']] = self::getRegPeoplePay($v);
        }

        $res = self::addData($add_data);
        return $res;

    }

    public function getDistributionalGameServer($time)
    {
        //获取当天开服的区服信息
        $server_info = DistributionalGameServerSqlServer::getOneByWhere("open_time = {$time}");

        return $server_info;
    }

    //获取时间段注册的用户每天的充值情况
    public function getRegPeoplePay($data)
    {
        $info = LtvServerStatisticSqlServer::getRegPeoplePay($data);
        return $info;
    }

    //添加统计数据
    public static function addData($data)
    {
        foreach($data as $k=>$v){

            LtvServerStatisticSqlServer::addData($v);

        }

    }

    //更新统计数据
    public static function updateData($data)
    {
        foreach($data as $k=>$v){

            LtvServerStatisticSqlServer::updateData($v);

        }

    }

    //获取当天充值用户，按角色归纳数据
    public static function getTodayPayPeople($data)
    {
        $info = LtvServerStatisticSqlServer::getTodayPayPeople($data);
        return $info;
    }

    //获取当天充值用户，按角色归纳数据
    public static function getTodayPayNewPeople($data)
    {
        $info = LtvServerStatisticSqlServer::getTodayPayNewPeople($data);
        return $info;
    }
}