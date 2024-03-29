<?php
/**
 * Created by PhpStorm.
 * User: crosstime
 * Date: 2018/8/14
 * Time: 下午5:31
 */
namespace common\server\DistributionalServer;

use app\admin\controller\DistributionalServer;
use common\base\BasicServer;
use common\libraries\Common;
use common\server\AdminServer;
use common\server\SysServer;
use common\sql_server\DistributionalServerSqlServer;
use common\sql_server\KefuUserRoleSqlServer;
use common\sql_server\ServerStatisticSqlServer;



class DistributionalServerServer extends BasicServer
{



    /**
     * @param $data
     * @return mixed
     */
    public static function insertInfo($data)
    {
        foreach($data as $k1=>$v1){
            $res = self::count(['conditions'=>"garrison_product_id={$v1['id']} and server_id ='{$v1['server_id']}'"]);
            if($res){
               unset($data[$k1]);
            }
        }
        if(empty($data)){
            return true;
        }

        $time = time();
        $sql = "insert into distributional_server(`garrison_product_id`,`product_id`,`garrison_product_name`,`platform_id`,`server_id`,`server_name`,`role_id`,`role_name`,`admin_id`,`admin_name`,`add_time`,`start_time`,`end_time`,`open_time`,`is_end`,`status`) values";
        foreach( $data as $v ){

            $sql .= "({$v['id']},".
                "{$v['product_id']},".
                "'{$v['product_name']}',".
                "{$v['platform_id']},".
                "'{$v['server_id']}',".
                "'{$v['server_name']}',".
                "'{$v['role_id']}',".
                "'".addslashes($v['role_name'])."',".
                "'',".
                "'',".
                "{$time},".
                "'',".
                "'',".
                "'{$v['open_time']}',".
                "0,".
                "0),";

        }


        $sql = trim($sql,",");

        $res = self::excuteSQL($sql);
        return $res;
    }


    /***
     *获取数据列表
     */
    public static function getList($data)
    {

        $return = self::dealData($data);


        $list = DistributionalServerSqlServer::getList($return['where'],$return['limit'],$return['page']);

        foreach($list as $k=>&$v){
            $v["money"] = $v["ltv_1_money"] = $v["ltv_3_money"]= $v["ltv_7_money"] = $v["ltv_10_money"] = $v["ltv_15_money"] = $v["ltv_20_money"] = $v["ltv_30_money"] = 0;
            $v['reg_num'] = $v["ltv_1_reg_num"] = $v["ltv_3_reg_num"]= $v["ltv_7_reg_num"] = $v["ltv_10_reg_num"] = $v["ltv_15_reg_num"] = $v["ltv_20_reg_num"] = $v["ltv_30_reg_num"] = 0;

            $server_total_info = ServerStatisticSqlServer::getTimeRegAndMoney($v['product_id'],$v['platform_id'],$v['server_id'],$v['open_time'],30);

            if($server_total_info){

                //判断相差天数
                $date1 = date_create($server_total_info[0]['date']);
                $date2 = date_create($server_total_info[count($server_total_info)-1]['date']);
                $diff = date_diff($date1,$date2);

                foreach($server_total_info as $k1=>$v1){

                    for($i=0;$i<=30;$i++){
                        if(strtotime($v1['date']) == mktime(0,0,0,date('m',$v['open_time']),date('d',$v['open_time'])+$i,date('Y',$v['open_time']))){

                                $v["money"] += $v1['money'];
                                $v["reg_num"] += $v1['reg_num'];

                            if($i+1==1){
                                $v["ltv_1_money"] += $v1['money'];
                                $v["ltv_1_reg_num"] += $v1['reg_num'];
                            }

                            if($i+1<=3){
                                $v["ltv_3_money"] += $v1['money'];
                                $v["ltv_3_reg_num"] += $v1['reg_num'];
                            }

                            if($i+1<=7){
                                $v["ltv_7_money"] += $v1['money'];
                                $v["ltv_7_reg_num"] += $v1['reg_num'];
                            }

                            if($i+1<=10){
                                $v["ltv_10_money"] += $v1['money'];
                                $v["ltv_10_reg_num"] += $v1['reg_num'];
                            }

                            if($i+1<=15){
                                $v["ltv_15_money"] += $v1['money'];
                                $v["ltv_15_reg_num"] += $v1['reg_num'];
                            }

                            if($i+1<=20){
                                $v["ltv_20_money"] += $v1['money'];
                                $v["ltv_20_reg_num"] += $v1['reg_num'];
                            }

                            if($i+1<=30){

                                $v["ltv_30_money"] += $v1['money'];
                                $v["ltv_30_reg_num"] += $v1['reg_num'];
                            }


                        }
                    }

                }
            }


            $login_time = DistributionalServerServer::getUserRoleLastLoginTime($v);

            if(!empty($login_time)){
                $v['login_time'] = date('Y-m-d',$login_time);
            }


            $v['start_time'] = !empty($v['start_time']) ? date('Y-m-d',$v['start_time']): '';
            $v['end_time'] = !empty($v['end_time']) ? date('Y-m-d',$v['end_time']): '';



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

            $v["LTV-1"] = !empty($v['ltv_1_reg_num']) ? round($v['ltv_1_money']/$v['ltv_1_reg_num'],2) : 0;
            $v["LTV-3"] = !empty($v['ltv_3_reg_num']) ? round($v['ltv_3_money']/$v['ltv_3_reg_num'],2) : 0;
            $v["LTV-7"] = !empty($v['ltv_7_reg_num']) ? round($v['ltv_7_money']/$v['ltv_7_reg_num'],2) : 0;
            $v["LTV-10"] = !empty($v['ltv_10_reg_num']) ? round($v['ltv_10_money']/$v['ltv_10_reg_num'],2) : 0;
            $v["LTV-15"] = !empty($v['ltv_15_reg_num']) ? round($v['ltv_15_money']/$v['ltv_15_reg_num'],2) : 0;
            $v["LTV-20"] = !empty($v['ltv_20_reg_num']) ? round($v['ltv_20_money']/$v['ltv_20_reg_num'],2) : 0;
            $v["LTV-30"] = !empty($v['ltv_30_reg_num']) ? round($v['ltv_30_money']/$v['ltv_30_reg_num'],2) : 0;

        }


        if($data['is_excel']){
            $tmp_list = [];
            foreach($list as $k1=>$v1){

                $tmp_list[$k1]['product_name'] = $v1['product_name'];
                $tmp_list[$k1]['server_id'] = $v1['server_id'];
                $tmp_list[$k1]['server_name'] = $v1['server_name'];
                $tmp_list[$k1]['open_time'] = $v1['open_time'];
                $tmp_list[$k1]['reg_num'] = $v1['reg_num'];
                $tmp_list[$k1]['role_id'] = $v1['role_id'];
                $tmp_list[$k1]['role_name'] = $v1['role_name'];
                $tmp_list[$k1]['LTV_1'] = $v1['LTV-1'];
                $tmp_list[$k1]['LTV_3'] = $v1['LTV-3'];
                $tmp_list[$k1]['LTV_7'] = $v1['LTV-7'];
                $tmp_list[$k1]['LTV_10'] = $v1['LTV-10'];
                $tmp_list[$k1]['LTV_15'] = $v1['LTV-10'];
                $tmp_list[$k1]['LTV_20'] = $v1['LTV-20'];
                $tmp_list[$k1]['LTV_30'] = $v1['LTV-30'];
                $tmp_list[$k1]['login_time'] = $v1['login_time'];
                $tmp_list[$k1]['is_end'] = $v1['is_end'];
                $tmp_list[$k1]['status'] = $v1['status'];
                $tmp_list[$k1]['start_time'] = $v1['start_time'];
                $tmp_list[$k1]['end_time'] = $v1['end_time'];
                $tmp_list[$k1]['garrison_period'] = $v1['garrison_period'];
            }
            $list = $tmp_list;
            if(empty($list)){
                $list = [];
            }
        }

        return $list;
    }


    public static function getCount($data){

        $return = self::dealData($data);

        $count = DistributionalServerSqlServer::getCount($return['where']);

        return $count;
    }


    /***
     *获取数据列表
     */
    public static function getOne($id){


        $res = DistributionalServerSqlServer::getOne($id);

        return $res;
    }



    public static function dealData($data)
    {

        $where = '1=1 ';

        $return['page'] = $data['page'];
        $return['limit'] = $data['limit'];

        if($data['is_excel']){
            $return['page'] = 1;
            $return['limit'] = 100000000;
        }

        if($data['server_id']){
            $where .= " and distributional_server.server_id={$data['server_id']}";
        }

        if($data['admin_name']){
            $where .= " and distributional_server.admin_name='{$data['admin_name']}'";
        }

        if($data['status'] !== ''){
            $where .= " and distributional_server.status={$data['status']}";
        }

        if($data['open_time_1']){
            $open_time_1 = strtotime($data['open_time_1']);
           $where .= " and open_time>={$open_time_1}";
        }

        if($data['open_time_2']){
            $open_time_2 = strtotime($data['open_time_2']);
           $where .= " and open_time<={$open_time_2}";
        }

        if($data['end_time_1']){
            $end_time_1 = strtotime($data['end_time_1']);
           $where .= " and end_time>={$end_time_1}";
        }

        if($data['end_time_2']){
            $end_time_2 = strtotime($data['end_time_2']);
           $where .= " and end_time<={$end_time_2}";
        }

        if($data['garrison_product_id']){
           $where .= " and garrison_product_id={$data['garrison_product_id']} order by distributional_server.server_id*1 desc";
        }

        $return['where'] = $where;
        return $return;
    }



    public static function updateOpenTime($id,$opentime)
    {
        if(empty($opentime) ){
            return[
                'code' => -1,
                'msg' => "修改失败",
                'data' => "不能缺少开服时间",
            ];
        }

        $update_data = ['open_time'=>strtotime($opentime)];

        $res = DistributionalServerSqlServer::updateData($id,$update_data);
        if($res){
            return[
                'code' => 0,
                'msg' => "修改成功",
            ];
        }
    }

    public static function updateInfo($id,$data)
    {
        $admin = AdminServer::userDetail($data['admin_id']);

        $update_data['admin_id'] = $data['admin_id'];
        $update_data['admin_name'] = $admin['admin']['realname'];
        $update_data['role_id'] = $data['role_id'];
        $update_data['role_name'] = $data['role_name'];
        $update_data['status'] = 1;
        $update_data['start_time'] = strtotime($data['start_time']);

        $res = DistributionalServerSqlServer::updateData($id,$update_data);

        if($res){
            return[
                'code' => 0,
                'msg' => "修改成功",
            ];
        }else{
            return[
                'code' => 0,
                'msg' => "修改失败"
                ];
        }
    }

    public static function changeStatus($id,$data)
    {
        $update_data = [];
        if($data['change_status'] == 3){
            $update_data['is_end'] = 1;
            $update_data['end_time'] = time();
        }
        $update_data['status'] = $data['change_status'];
        $res = DistributionalServerSqlServer::updateData($id,$update_data);
        if($res){
            return[
                'code' => 0,
                'msg' => "修改成功",
            ];
        }
    }

}