<?php
/**
 * Created by PhpStorm.
 * User: crosstime
 * Date: 2017/10/12
 * Time: 上午11:57
 */
namespace common\server\RecallPlan;


use common\base\BasicServer;

use common\server\Statistic\GameProductServer;
use common\server\Vip\LossUserServer;
use common\server\Platform\{Platform};
use common\server\Vip\RecallServer;
use common\libraries\{ApiUserInfoSecurity, Common, SendNuoer};

use common\sql_server\{LossUserSqlServer, RecallPlanLogSqlServer, RecallPlanPeopleLogSqlServer, RecallPlanSqlServer};
use think\Config;


class RecallPlanServer extends BasicServer
{

    //根据计划表写入计划记录表
    public static function executeLossPlan($params)
    {
        $result = ['code'=>1, 'data'=>[]];

        //
        $where = ['status'=>1,'execute_type'=>1];
        $res = RecallPlanSqlServer::getInfo($where);
        $res = isset($res) ? $res->toArray() : [];
        $return_arr = [];

        if($res){
            foreach($res as $k=>$v){

                $tmp_data = self::dealParams($v);

                $res = RecallPlanLogSqlServer::insertInfo($tmp_data);
                $return_arr['data'][$v['id']] = $res;
            }
        }


        $result['data'] = $return_arr;
        return $result;

    }


    //根据计划记录表写入计划记录明细表
    public static function executeLossPlanLog($params)
    {
        $result = ['code'=>1, 'data'=>[]];

        $where = ['status'=>1,'execute_type'=>1];
        $plan_logs = RecallPlanLogSqlServer::getInfo($where);

        $ins_res = false;
        foreach($plan_logs as $k=>$v)
        {
            //筛选符合计划的用户
            $res = LossUserServer::screenUids($v);

            if(!empty($res)){

                RecallPlanPeopleLogSqlServer::insertInfo($v['id'],$res);

            }

        }

        return $result;

    }


    public static function dealParams($data)
    {
        $tmp_data = $data;
        $code_ids = RecallServer::getCodeByGroupId($data['recall_code_group_id']);
        $ver_ids = RecallServer::getLinkByGroupId($data['recall_ver_group_id'],'ver_id');

        $tmp_data['recall_code_id'] = implode(',',$code_ids);
        $tmp_data['recall_ver_id'] = implode(',',$ver_ids);
        $tmp_data['exec_date'] = date('Y-m-d');
        $tmp_data['add_time'] = time();
        $tmp_data['plan_id'] = $data['id'];
//        $tmp_data['platform_id'] = $data['id'];
        return $tmp_data;
    }

    public static function dealParams2($data)
    {
        $tmp_data = $data;

        $tmp_data['plan_log_id'] = $data['id'];
//        $tmp_data['last_login_time'] = $data['last_login_time'];
        $tmp_data['add_time'] = time();
//        $tmp_data['plan_id'] = $data['id'];
//        $tmp_data['platform_id'] = $data['id'];
        return $tmp_data;
    }


    public static function sendRecallPlanPeopleLog($data)
    {

        $result = ['code'=>1, 'data'=>[]];

        $cache_name = 'sendRecallPlanPeopleLog_last_id';
        $limit = 5000;

        $exec_date = date('Y-m-d');
        $time = time();
        $where = ['status'=>1,'execute_type'=>1,'exec_date'=>$exec_date];

        if(isset($data['plan_log_id'])){
            $where['id'] = $data['plan_log_id'];
            unset($where['exec_date']);
            cache($cache_name.$data['plan_log_id'],null);
            $limit = 99999999;
        }

        $plan_logs = RecallPlanLogSqlServer::getInfo($where);



        foreach($plan_logs as $k=>$v){
            $cache_name_plan = $cache_name.$v['id'];

            $last_id = cache($cache_name_plan);

            if(!isset($last_id)){

                cache($cache_name_plan,0,14400);
            }

            $tmp_where['plan_log_id'] = $v['id'];
            $tmp_where['platform_id'] = $v['platform_id'];
            $tmp_where['id'] = ['>',$last_id];
//            $tmp_where['status'] = 0;
            $infos = RecallPlanPeopleLogSqlServer::getInfo($tmp_where,$limit);

            if(!empty($infos)){
                cache($cache_name_plan,$infos[count($infos)-1]['id']);
            }


            $arr_phone_infos = array_chunk($infos,1000);

            foreach($arr_phone_infos as $k2=>$v2){

                $tmp_arr = [];

                foreach($v2 as $k3=>$v3){

                    $tmp_arr[$k3]['status'] = 1;
                    $tmp_arr[$k3]['id'] = $v3['id'];
                    $tmp_arr[$k3]['exec_date'] = $v3['exec_date'];
                    $tmp_arr[$k3]['plan_log_id'] = $v3['plan_log_id'];
                    $tmp_arr[$k3]['platform_id'] = $v3['platform_id'];
                    $tmp_arr[$k3]['phone'] = $v3['phone'];

                    $v2[$k3]['plan_log_id'] = $v3['plan_log_id'];
                    $v2[$k3]['phone'] = ApiUserInfoSecurity::decrypt($v3['phone']);

                }

                //todo
                $send_res = SendNuoer::sendPhone($v2);
//                $send_res = true;
                $result['data'][] = $v2;
                if($send_res == true && !empty($tmp_arr)){

                    RecallPlanPeopleLogSqlServer::update($tmp_arr);

                    foreach($tmp_arr as $k4=>$v4){

                        LossUserSqlServer::updateSendStatus(['last_send_time'=>$time,'send_num'=>['inc','1']],['phone'=>$v4['phone'],'platform_id'=>$v4['platform_id']]);
                    }


                }
            }
        }

        return $result;

    }




    public static function sendRecallPlanLog($data)
    {

        $result = ['code'=>1, 'data'=>[]];

        $exec_date = date('Y-m-d');

        $where = ['status'=>1,'execute_type'=>1,'exec_date'=>$exec_date];

        if(isset($data['plan_log_id'])){
            $where['id'] = $data['plan_log_id'];
            unset($where['exec_date']);
        }

        $plan_logs = RecallPlanLogSqlServer::getInfo($where);

        foreach($plan_logs as $k=>$v)
        {

            $platform = Common::getPlatformInfoByPlatformIdAndCache($v['platform_id']);

            $tmp_data['exec_date']                   = $v['exec_date'];
            $tmp_data['plan_log_id']            = $v['id'];
            $tmp_data['platform']               = $platform['platform_suffix'];
            $tmp_data['title']                  = $v['title'];
            $tmp_data['loss_product_name']      = GameProductServer::getUpProductById($v['platform_id'],$v['loss_up_id']);
            $tmp_data['recall_product_name']    = GameProductServer::getUpProductById($v['platform_id'],$v['recall_up_id']);
            $tmp_data['min_account_money']      = !empty($v['min_account_money']) ? $v['min_account_money'] : 0;
            $tmp_data['max_account_money']      = !empty($v['max_account_money']) ? $v['max_account_money'] : 0;
            $tmp_data['min_loss_product_money'] = !empty($v['min_loss_up_money']) ? $v['min_loss_up_money'] : 0;
            $tmp_data['max_loss_product_money'] = !empty($v['max_loss_up_money']) ? $v['max_loss_up_money'] : 0;
            $tmp_data['min_level']              = $v['min_level'];
            $tmp_data['max_level']              = $v['max_level'];
            $tmp_data['send_num']               = $v['send_num'];
            $tmp_data['recall_ver']             = !empty($v['recall_ver_id']) ? implode(',',RecallServer::getLinkByVerId($v['platform_id'],$v['recall_ver_id'])) : '';
            $tmp_data['recall_code']            = $v['recall_code_id'];
            $tmp_data['interval_day']           = $v['interval_day'];

//            $res = true;
            $res = SendNuoer::sendPlan($tmp_data);

            if($res){
                $result['data'][$v['id']] = $tmp_data;
            }
        }

        return $result;
    }


    public static function updatePlanLogSendNum($data)
    {
        $result = ['code'=>1, 'data'=>[]];

        //更新发送数量
        RecallPlanLogSqlServer::updateSendNum($data);
        //更新召回数量
        RecallPlanLogSqlServer::updateReBackNum($data);
        //更新发送状态
        RecallPlanLogSqlServer::updateSendStatus();

        return $result;
    }

}