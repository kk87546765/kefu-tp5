<?php
/**
 * 系统
 */
namespace common\server\Statistic;


use common\base\BasicServer;
use common\sql_server\PlatformList;
use common\sql_server\ServerStatisticSqlServer;
use common\sql_server\EveryDayRoleAmountStatisticsSqlServer;
use think\Db;
use common\libraries\Common;
use common\sql_server\GarrisonProductSqlServer;

class EveryDayRoleAmountStatisticsServer extends BasicServer
{

    const INSERT_NUMBER = 2000;

    public $time,$num,$platform,$start_time,$end_time,$server_info_statistics_model;


    public function dealParams($params)
    {

        $this->time = time();
        $this->platform = isset($params['platform']) ? $params['platform'] : '';
        $this->end_time = isset($params['end_time']) ? date('Y-m-d', strtotime($params['end_time'])) : date("Y-m-d", $this->time);

        $this->start_time= isset($params['start_time']) ? date('Y-m-d', strtotime($params['start_time'])) : date("Y-m-d",strtotime("-1 day"));
        $this->num = isset($num) ? $num : 1000;
    }


    public function roleAmountStatistics()
    {

        $start_time = strtotime($this->start_time);
        $end_time = strtotime($this->end_time);

        $server_statistic_model = new ServerStatisticSqlServer();

        if($this->platform){

            $platform_id = PlatformList::getOneBySuffix($this->platform);

            //如果指定平台则统计该平台，否则统计全部平台
            $data = $server_statistic_model->getRoleAmount($start_time,$end_time,$this->platform,$platform_id);
        }else{
            $data = $server_statistic_model->getRoleAmount($start_time,$end_time);
        }

        $j = 1;
        foreach($data as $k=>$v){

            $i = 0;
            Db::startTrans();
            $new_arr = [];
            foreach($v as $k1=>$v1){
                $i++;
                array_push($new_arr,$v1);
                if( $i % 1000 == 0){
                    EveryDayRoleAmountStatisticsSqlServer::insertInfo($new_arr);
                    $new_arr = [];
                    Db::commit();
                    $j++;
                    Db::startTrans();
                }
            }
            EveryDayRoleAmountStatisticsSqlServer::insertInfo($new_arr);
            Db::commit();

        }

        return true;
    }


    public function repairServerStatic($params)
    {
        $start_time = $this->start_time;
        $end_time = $this->end_time;

        $date1 = date_create($start_time);
        $date2 = date_create($end_time);
        $date_obj = date_diff($date1,$date2);
        $days = (int)$date_obj->format('%a');

        $tmp_start_time = strtotime($this->start_time);
        $tmp_end_time = strtotime($this->end_time);

        $obj = new ServerinfoStatisticsServer();

        for($i = 1;$i<=$days;$i++){

            $params['start_time'] = date('Y-m-d',mktime(0,0,0,date('m',$tmp_start_time),date('d',$tmp_start_time)+$i-1,date('Y',$tmp_start_time)));
            $params['end_time'] = date('Y-m-d',mktime(0,0,0,date('m',$tmp_start_time),date('d',$tmp_start_time)+$i,date('Y',$tmp_start_time)));

            $obj->insertIntoServerInfo($params);
        }
        return true;
    }



}
