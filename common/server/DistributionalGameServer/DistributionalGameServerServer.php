<?php
/**
 * Created by PhpStorm.
 * User: crosstime
 * Date: 2018/8/14
 * Time: 下午5:31
 */
namespace common\server\DistributionalGameServer;

use app\admin\controller\DistributionalServer;
use common\base\BasicServer;
use common\libraries\Common;
use common\server\AdminServer;
use common\server\SysServer;
use common\sql_server\DistributionalGameServerSqlServer;
use common\sql_server\KefuUserRoleSqlServer;
use common\sql_server\GameServerStatisticSqlServer;



class DistributionalGameServerServer extends BasicServer
{

    public static $return = ['code'=>-1,'msg'=>'err','data'=>[]];

    /***
     *获取数据列表
     */
    public static function getList($data)
    {

        $return = self::dealData($data);


        $list = DistributionalGameServerSqlServer::getList(['where'=>$return['where'],'page'=>$return['page'],'limit'=>$return['limit']]);

        if($data['init']){
            return [];
        }

        foreach($list as $k=>&$v){

            $server_total_info = GameServerStatisticSqlServer::getTimeRegAndMoney($v['game_id'],$v['platform_id'],$v['server_id'],$v['open_time'],120);


            if($server_total_info){

                $pay_money = 0;

                $date1 = date_create(date('Y-m-d',$v['open_time']));
                $date2 = date_create(date('Y-m-d'));
                $diff=date_diff($date1,$date2);
                $days = $diff->days;

                for($j = 0 ;$j < $days ;$j++){
                    if(@$server_total_info[$j]['days'] != $j){
                        $v['LTV-'.($j+1)] = bcdiv($pay_money,$server_total_info[0]['reg_num'],2);
                    }else{
                        $pay_money += $server_total_info[$j]['pay_money'];
                        if($server_total_info[0]['reg_num']){
                            $v['LTV-'.($server_total_info[$j]['days']+1)] = bcdiv($pay_money,$server_total_info[0]['reg_num'],2);
                        }else{
                            $v['LTV-'.($server_total_info[$j]['days']+1)] = 0;
                        }
                        $v['LTV-'.($server_total_info[$j]['days']+1)] = bcdiv($pay_money,$server_total_info[0]['reg_num'],2);
                    }

                }

            }


            $login_time = DistributionalGameServerSqlServer::getUserRoleLastLoginTime($v);

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
        }

        if($data['is_excel']){
                $tmp_list = [];
            foreach($list as $k1=>$v1){
                $tmp_list[$k1]['product_name'] = $v1['product_name'] ?? '';
                $tmp_list[$k1]['server_id'] = $v1['server_id']  ?? '';
                $tmp_list[$k1]['server_name'] = $v1['server_name']  ?? '';
                $tmp_list[$k1]['open_time'] = $v1['open_time']  ?? '';
                $tmp_list[$k1]['reg_num'] = $v1['reg_num']  ?? 0;
                $tmp_list[$k1]['role_id'] = $v1['role_id']  ?? '';
                $tmp_list[$k1]['role_name'] = $v1['role_name']  ?? '';
                $tmp_list[$k1]['LTV_1'] = $v1['LTV-1']  ?? '';
                $tmp_list[$k1]['LTV_3'] = $v1['LTV-3']  ?? '';
                $tmp_list[$k1]['LTV_7'] = $v1['LTV-7']  ?? '';
                $tmp_list[$k1]['LTV_10'] = $v1['LTV-10']  ?? '';
                $tmp_list[$k1]['LTV_15'] = $v1['LTV-10']  ?? '';
                $tmp_list[$k1]['LTV_20'] = $v1['LTV-20']  ?? '';
                $tmp_list[$k1]['LTV_30'] = $v1['LTV-30']  ?? '';
                $tmp_list[$k1]['login_time'] = $v1['login_time']  ?? '';
                $tmp_list[$k1]['is_end'] = $v1['is_end']  ?? '';
                $tmp_list[$k1]['status'] = $v1['status']  ?? '';
                $tmp_list[$k1]['start_time'] = $v1['start_time']  ?? '';
                $tmp_list[$k1]['end_time'] = $v1['end_time'] ?? '';
                $tmp_list[$k1]['garrison_period'] = $v1['garrison_period'] ?? '';
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

        $count = DistributionalGameServerSqlServer::getCount(['where'=>$return['where']]);

        return $count;
    }


    /***
     *获取数据列表
     */
    public static function getOne($id){


        $res = DistributionalGameServerSqlServer::getOne($id);

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
            $where .= " and distributional_game_server.server_id={$data['server_id']}";
        }

        if($data['only_admin']){
            $where .= " AND distributional_game_server.admin_id !=0";
        }

        if($data['admin_name']){
            $where .= " and distributional_game_server.admin_name='{$data['admin_name']}'";
        }

        if($data['status'] !== ''){
            $where .= " and distributional_game_server.status={$data['status']}";
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

        if($data['platform_id'] && $data['product_id']){
            $where .= " and distributional_game_server.platform_id = {$data['platform_id']} and distributional_game_server.product_id = {$data['product_id']} ";
        }

        if($data['platform_id'] && $data['game_id']){

            $where .= " and distributional_game_server.platform_id = {$data['platform_id']} and distributional_game_server.game_id in( {$data['game_id']})";
        }

        $where .= ' order by distributional_game_server.server_id*1 desc';

        $return['where'] = $where;
        return $return;
    }





    public static function getUserRoleLastLoginTime($data){
        if(empty($data['role_id']) || empty($data['server_id'])){
            return false;
        }
        $platform = Common::getPlatformList();

        $platform_suffix = '';
        foreach($platform as $k=>$v){
            if($v['platform_id'] == $data['platform_id']){
                $platform_suffix = $v['platform_suffix'];
            }
        }
        if(!$platform_suffix){
            return false;
        }

        //通过角色id和区服id获取信息，有可能不是唯一，所以通过游戏id核对产品id
        $res = KefuUserRoleSqlServer::getRoleInfo($platform_suffix,$data['server_id'],$data['role_id']);

        if(!empty($res)){
            return $res[0]['login_date'];
        }else{
            return [];
        }

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

        $res = DistributionalGameServerSqlServer::updateData($id,$update_data);
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

        $res = DistributionalGameServerSqlServer::updateData($id,$update_data);

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
        $res = DistributionalGameServerSqlServer::updateData($id,$update_data);
        if($res){
            return[
                'code' => 0,
                'msg' => "修改成功",
            ];
        }
    }

}