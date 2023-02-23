<?php
/**
 * 系统
 */
namespace common\server\ServerTurnover;


use common\base\BasicServer;

use common\libraries\Common;
use common\model\db_statistic\PlatformGameInfo;
use common\model\db_statistic\GameProduct;
use common\model\db_statistic\ServerStatistic;
use common\sql_server\KefuUserRoleSqlServer;
use common\sql_server\ServerStatisticSqlServer;
use common\sql_server\ServerTurnoverSqlServer;


class ServerTurnoverServer extends BasicServer
{
    /**
     * 根据子类game_id获取游戏产品信息
     * @param $game_id
     * @param $platform_id
     * @return object
     */
    public static function getList($data)
    {
        $where = self::dealData($data);
        $list = ServerTurnoverSqlServer::getList($where,$data['page'],$data['limit']);

        foreach($list as $k=>&$v){
            $v["reg_num_total"] = $v["total_money_60"] = $v["total_money_90"] = $v["total_money_120"] = 0;

            $login_time = '';


            $server_total_info = ServerStatisticSqlServer::getTimeRegAndMoney($v['product_id'],$v['platform_id'],$v['server_id'],$v['open_time']);
            $platform = Common::getPlatformInfoByPlatformIdAndCache($v['platform_id']);
            if($platform['platform_suffix']){
                $role_info = KefuUserRoleSqlServer::getRoleInfo($platform['platform_suffix'],$v['server_id'],$v['role_id']);
                $login_time = $role_info[0]['login_date'];
            }

            if($server_total_info){

                //判断相差天数
                $date1 = date_create($server_total_info[0]['date']);
                $date2 = date_create($server_total_info[count($server_total_info)-1]['date']);
                $diff=date_diff($date1,$date2);


                foreach($server_total_info as $k1=>$v1){


                    for($i=0;$i<=$diff->days;$i++){
                        if(strtotime($v1['date']) == mktime(0,0,0,date('m',$v['open_time']),date('d',$v['open_time'])+$i,date('Y',$v['open_time']))){

                            $v["money_".($i+1)] = $v1['money'];
                            $v["reg_num_".($i+1)] = $v1['reg_num'];

                            if($i+1 <= 2){
                                $v["reg_num_total"] += $v1['reg_num'];
                            }

                            //统计
                            if($i+1<60){
                                $v["total_money_60"] += $v1['money'];

                            }
                            if($i+1<90){
                                $v["total_money_90"] += $v1['money'];
                            }
                            if($i+1<120){
                                $v["total_money_120"] += $v1['money'];
                            }


                        }
                    }

                }
            }

            if(!empty($login_time)){
                $v['login_time'] = date('Y-m-d',$login_time);
            }

            if(!empty($v['start_time'])){
                $v['start_time'] = date('Y-m-d',$v['start_time']);
            }

            $v['open_time'] = date('Y-m-d',$v['open_time']);
            $v['is_end'] = $v['is_end'] == 1 ? '是' : '否';
            if($v['status'] == 0){
                $v['status'] = '未进驻';
            }elseif($v['status'] == 1){
                $v['status'] = '进驻中';
            }elseif($v['status'] == 2){
                $v['status'] = '已抛服';
            }elseif($v['status'] == 3){
                $v['status'] = '已退服';
            }

            $v['ltv'] = !empty($v['reg_num']) ? round($v['money']/$v['reg_num'],2) : 0;
        }

        if($data['is_excel']){
            //过滤不需要的参数
            unset($v['date'],$v['platform_id'],$v['product_id'],$v['admin_id'],$v['add_time'],$v['garrison_product_id']);
        }

        return $list;
    }


    public static function getCount($data)
    {
        $where = self::dealData($data);
        $count = ServerTurnoverSqlServer::getCount($where);
        return $count;
    }

    public static function getMoneyCount($data)
    {
        $where = self::dealData($data);
        $count = ServerTurnoverSqlServer::getMoneyCount($where);
        foreach($count as $k=>&$v){
            $v['open_time'] = date('Y-m-d',$v['open_time']);
        }
        return $count;
    }

    public static function dealData($data)
    {
        $where = '1=1 ';


        if($data['garrison_product_id']){
            $where .= " and b.garrison_product_id={$data['garrison_product_id']}";
        }

        if($data['server_id']){
            $where .= " and b.server_id={$data['server_id']}";
        }

        if($data['admin_name']){
            $where .= " and b.admin_name='{$data['admin_name']}'";
        }

        if(isset($data['status']) && $data['status'] !== ''){
            $where .= " and b.status={$data['status']}";
        }

        if($data['open_time_1']){
            $open_time_1 = strtotime($data['open_time_1']);
            $where .= " and b.open_time>={$open_time_1}";
        }

        if($data['open_time_2']){
            $open_time_2 = strtotime($data['open_time_2']);
            $where .= " and b.open_time<={$open_time_2}";
        }

        if($data['end_time_1']){
            $end_time_1 = strtotime($data['end_time_1']);
            $where .= " and b.end_time>={$end_time_1}";
        }

        if($data['end_time_2']){
            $end_time_2 = strtotime($data['end_time_2']);
            $where .= " and b.end_time<={$end_time_2}";
        }

        return $where;
    }




}
