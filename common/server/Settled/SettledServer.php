<?php
/**
 * 系统
 */
namespace common\server\Settled;

use common\base\BasicServer;
use common\sql_server\SettledSqlServer;

class SettledServer extends BasicServer
{
    public static function getList($data)
    {
        $where = self::dealData($data);

        $infos = SettledSqlServer::getList($where);

        foreach($infos as $k=>$v){
            if($v['total_server'] == 0){

                $infos[$k]['have_in_rate'] = '0%';

                $infos[$k]['no_in_rate'] = '0%';

            }elseif($v['have_in'] == 0){

                $infos[$k]['have_pao_rate'] = '0%';
            }

            $infos[$k]['have_in_rate'] = isset($infos[$k]['have_in_rate']) ? $infos[$k]['have_in_rate'] : round($v['have_in']/$v['total_server'],4) *100 .'%';
            $infos[$k]['have_pao_rate'] = isset($infos[$k]['have_pao_rate']) ?  $infos[$k]['have_pao_rate'] : round($v['have_pao']/$v['have_in'],4)*100 .'%';
            $infos[$k]['no_in_rate'] = isset( $infos[$k]['no_in_rate']) ? $infos[$k]['no_in_rate'] : round($v['no_in']/$v['total_server'],4)*100 .'%';

            if($data['excel'] == 1){
                unset($infos[$k]['no_in']);
                unset($infos[$k]['have_tui']);
                unset($infos[$k]['garrison_product_id']);
            }

        }


        return $infos;
    }

    public static function getCount($data)
    {
        $where = self::dealData($data);

        $count = SettledSqlServer::getCount($where);

        return $count;
    }


    public static function getLTVList($data)
    {
        $old_s_open_time = strtotime($data['s_open_time']);

        //判断相差天数
        $date1 = date_create($data['s_open_time']);
        $date2 = date_create($data['e_open_time']);


        $diff = date_diff($date1,$date2);

        if($diff->days>31) {
            return ['msg' => '时间范围不能超过一个月'];
        }

        $where = self::dealData($data);

        $infos = SettledSqlServer::getLTVList($where);

        $s_open_time = strtotime($data['s_open_time']);
        $e_open_time = strtotime($data['e_open_time']);

        $arrange_info = [];
        for($i=0;$i<$diff->days+31;$i++){
            $date = mktime(0,0,0,date('m',$s_open_time),date('d',$s_open_time)+$i,date('Y',$s_open_time));

            if($date < $e_open_time){

                $arrange_info[date('Y-m-d',$date)] = self::arrangeData($date,$infos);

            }
        }

        $arrange_info_new = [];
        foreach($arrange_info as $k1=>$v1){


            if(strtotime($k1) < $old_s_open_time || strtotime($k1) > $e_open_time || empty($v1)){
                continue;
            }

            $arrange_info_new[$k1]['date'] = $k1;

            $arrange_info_new[$k1]['ltv_in_server_7'] = $v1['server_in_7']['reg_num'] == 0 ? 0 : round($v1['server_in_7']['money'] / $v1['server_in_7']['reg_num'],2);
            $arrange_info_new[$k1]['ltv_no_server_7'] = $v1['server_no_7']['reg_num'] == 0 ? 0 : round($v1['server_no_7']['money'] / $v1['server_no_7']['reg_num'],2);
            $arrange_info_new[$k1]['performance_in_server_7'] = $arrange_info_new[$k1]['ltv_no_server_7'] == 0 ? 0 : round($arrange_info_new[$k1]['ltv_in_server_7']/$arrange_info_new[$k1]['ltv_no_server_7'],4);

            $arrange_info_new[$k1]['ltv_in_server_14'] = $v1['server_in_14']['reg_num'] == 0 ? 0 : round($v1['server_in_14']['money'] / $v1['server_in_14']['reg_num'],2);
            $arrange_info_new[$k1]['ltv_no_server_14'] = $v1['server_no_14']['reg_num'] == 0 ? 0 : round($v1['server_no_14']['money'] / $v1['server_no_14']['reg_num'],2);
            $arrange_info_new[$k1]['performance_in_server_14'] = $arrange_info_new[$k1]['ltv_no_server_14'] == 0 ? 0 :  round($arrange_info_new[$k1]['ltv_in_server_14']/$arrange_info_new[$k1]['ltv_no_server_14'],4);

            $arrange_info_new[$k1]['ltv_in_server_30'] = $v1['server_in_30']['reg_num'] == 0 ? 0 : round($v1['server_in_30']['money'] / $v1['server_in_30']['reg_num'],2);
            $arrange_info_new[$k1]['ltv_no_server_30'] = $v1['server_no_30']['reg_num'] == 0 ? 0 : round($v1['server_no_30']['money'] / $v1['server_no_30']['reg_num'],2);
            $arrange_info_new[$k1]['performance_in_server_30'] = $arrange_info_new[$k1]['ltv_no_server_30'] == 0 ? 0 : round($arrange_info_new[$k1]['ltv_in_server_30']/$arrange_info_new[$k1]['ltv_no_server_30'],4);

        }

        return $arrange_info_new;
    }

    public static function dealData($data)
    {
        $where = '1=1 ';
        if ( $data['garrison_product_id'] ) {
            $where .= " and garrison_product_id in( {$data['garrison_product_id']})";
        }

        if ( $data['s_open_time'] ) {
            $s_open_time = strtotime($data['s_open_time']);
            $where .= " and open_time >= {$s_open_time}";
        }

        if ( $data['e_open_time'] ) {
            $e_open_time = strtotime($data['e_open_time']);
            $where .= " and open_time < {$e_open_time}";
        }

        return $where;
    }

    public static function arrangeData($date,$data)
    {


        if (empty($data) || empty($date)) {
            return [];
        }
        $total = [];

//        $date = strtotime('2021-05-10');
        foreach ($data as $k => $v) {
            //7天入驻服数据
            if (strtotime($v['date']) <= $date && $date - strtotime($v['date']) < 7 * 86400 && $v['status'] == 1) {

                $total['server_in_7']['reg_num'] += $v['reg_num'];
                $total['server_in_7']['money'] += $v['money'];
                $total['server_in_7']['date'] = date('Y-m-d', $date);

            }

            //7天空服数据
            if (strtotime($v['date']) <= $date && $date - strtotime($v['date']) < 7 * 86400 && $v['status'] == 0) {
                $total['server_no_7']['reg_num'] += $v['reg_num'];
                $total['server_no_7']['money'] += $v['money'];
                $total['server_no_7']['date'] = date('Y-m-d', $date);
            }

            //14天入驻服数据
            if (strtotime($v['date']) <= $date && $date - strtotime($v['date']) < 14 * 86400 && $v['status'] == 1) {
                $total['server_in_14']['reg_num'] += $v['reg_num'];
                $total['server_in_14']['money'] += $v['money'];
                $total['server_in_14']['date'] = date('Y-m-d', $date);
            }

            //14天空服数据
            if (strtotime($v['date']) <= $date && $date - strtotime($v['date']) < 14 * 86400 && $v['status'] == 0) {
                $total['server_no_14']['reg_num'] += $v['reg_num'];
                $total['server_no_14']['money'] += $v['money'];
                $total['server_no_14']['date'] = date('Y-m-d', $date);
            }

            //30天入驻服数据
            if (strtotime($v['date']) <= $date && $date - strtotime($v['date']) < 30 * 86400 && $v['status'] == 1) {
                $total['server_in_30']['reg_num'] += $v['reg_num'];
                $total['server_in_30']['money'] += $v['money'];
                $total['server_in_30']['date'] = date('Y-m-d', $date);
            }

            //30天空服数据
            if (strtotime($v['date']) <= $date && $date - strtotime($v['date']) < 30 * 86400 && $v['status'] == 0) {

                $total['server_no_30']['reg_num'] += $v['reg_num'];
                $total['server_no_30']['money'] += $v['money'];
                $total['server_no_30']['date'] = date('Y-m-d', $date);
            }
        }
        return $total;
    }
}