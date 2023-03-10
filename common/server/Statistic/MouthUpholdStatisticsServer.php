<?php
/**
 * 系统
 */
namespace common\server\Statistic;


use common\base\BasicServer;
use common\sql_server\MouthUpholdStatisticsSqlServer;

use think\Db;
use common\libraries\Common;
use common\sql_server\GarrisonProductSqlServer;

class MouthUpholdStatisticsServer extends BasicServer
{

    const INSERT_NUMBER = 2000;

    public $time,$num,$platform,$start_time,$end_time,$server_info_statistics_model;


    private static function dealParams($params)
    {
        $return = ['code'=>0,'msg'=>'','data'=>[]];

        $where = '1';
        $tmp_where = [];

        if(!empty($params['date'])){

            $tmp_where[] = getWhereDataArr(strtotime($params['date']),'date');
        }


        if(!empty($params['platform_id'])){

            $tmp_where[] = getWhereDataArr($params['platform_id'],'platform_id');
        }

        if(!empty($params['p_p'])){

            $tmp_where[] = getWhereDataArr($params['p_p'],'p_p');
        }

        if(!empty($params['p_g'])){

            $tmp_where[] = getWhereDataArr($params['p_g'],'p_g');
        }

        if(!empty($params['admin_id'])){

            $where .=  " and admin_id in({$params['admin_id']} )";
        }

        if(count($tmp_where) >= 1){
            $where .= ' and ';
        }

        $where.= setWhereSql($tmp_where,'');

        $where.= ' and ( month_uphold_user_amount != 0 or  all_uphold_user_count != 0 or
             month_new_contact_count !=0 or  month_new_contact_amount != 0 or month_new_distribution_contact_count != 0 
             or month_new_distribution_contact_amount != 0)';

        $return['data']['where'] = $where;

        return $return;

    }


    public static function getList($params)
    {
        $return  = self::dealParams($params);
        $res = MouthUpholdStatisticsSqlServer::getList($return['data']['where'],$params['limit'],$params['page']);

        foreach($res as $k=>$v){
            $res[$k]['date'] = date('Y-m',$v['date']);
        }
        return $res;
    }

    public static function getCount($params)
    {
        $return  = self::dealParams($params);
        $res = MouthUpholdStatisticsSqlServer::getCount($return['data']['where']);
        return $res;
    }

    public static function getStatistic($params)
    {
        $return  = self::dealParams($params);
        $res = MouthUpholdStatisticsSqlServer::getStatistic($return['data']['where']);
        return $res;
    }





}
