<?php
/**
 * Created by PhpStorm.
 * User: crosstime
 * Date: 2018/8/14
 * Time: 下午5:31
 */
namespace common\server\AccountRegistration;

use common\base\BasicServer;
use common\libraries\Common;
use common\libraries\Ipip\IP4datx;
use common\sql_server\AccountRegistrationSqlServer;
use common\sql_server\KefuCommonMember;

class AccountRegistrationServer extends BasicServer
{
    public static $return=[];

    public static $redis_set_name = 'account_registration';

    public static $redis = '';
    public static $admin_department = [

        1=>['id'=>1,'name'=>'游娱-客服部-GS1组'],
        2=>['id'=>2,'name'=>'游娱-客服部-GS2组'],
        3=>['id'=>3,'name'=>'游娱-客服部-在线'],
        4=>['id'=>4,'name'=>'游娱-客服部-质检'],
        5=>['id'=>5,'name'=>'游娱-客服部-营销'],
        6=>['id'=>6,'name'=>'游娱-运营'],
        7=>['id'=>7,'name'=>'游娱-市场'],
        8=>['id'=>8,'name'=>'掌玩-客服部-GS1组'],
        9=>['id'=>9,'name'=>'掌玩-客服部-GS2组'],
    ];


    public static function getList($data)
    {
        $where = self::dealData($data);
        $infos = AccountRegistrationSqlServer::getList(['where'=>$where,'order'=>'id desc','limit'=>$data['limit'],'page'=>$data['page']]);

        $platform_list = Common::getPlatformList();

        $admin_user_id = (self::$user_data)['id'];

        $admin_role_id = (self::$user_data)['role_id'];

        $admin_list = [23,24,25,0]; //gs经理、gs主管、gs组长、超级管理员

        foreach ($infos as $k=>$info) {
            $account_info = [];
//            $account_info = KefuCommonMember::getFieldInfoByNameAndSuffix([$info['account']],$platform_list[$info['platform_id']]['platform_suffix']);
            $infos[$k]['password']      = $admin_user_id == $info['admin_id'] || in_array($admin_role_id,$admin_list) ? $info['password'] : '******';
            $infos[$k]['ip_client']     =  !empty($account_info[0]['login_date']) ? implode('',IP4datx::find($account_info[0]['login_ip'])) : '';
            $infos[$k]['login_time']    = empty($account_info[0]['login_date']) ? '' :  date("Y-m-d H:i:s",$account_info[0]['login_date']);
            $infos[$k]['ip']            =  empty($account_info[0]['login_ip']) ? '' : $account_info[0]['login_ip'];
            $infos[$k]['open_time']     = empty($info['open_time']) ? '' : date("Y-m-d H:i:s",$info['open_time']);
            $infos[$k]['add_time']      = empty($info['add_time']) ? '' :  date("Y-m-d H:i:s",$info['add_time']);
            $infos[$k]['start_time']    = empty($info['add_time']) ? '' :  date("Y-m-d H:i:s",$info['add_time']);
            $infos[$k]['end_time']      = empty($info['end_time']) ? '' :  date("Y-m-d H:i:s",$info['end_time']);
            @$infos[$k]['admin_department_name'] = (self::$admin_department)[$info['admin_department_id']]['name'];

        }

        return $infos;

    }


    public static function getOne($id)
    {
        $info = AccountRegistrationSqlServer::getOne($id);

        return $info;
    }

    public static function getCount($data)
    {
        $where = self::dealData($data);

        $count = AccountRegistrationSqlServer::getCount(['where'=>$where]);

        return $count;
    }


    public static function add($data)
    {
        $res = AccountRegistrationSqlServer::add($data);

        $redis = get_redis();
        self::$redis = $redis;
        if($redis->SCARD(self::$redis_set_name) == 0){
            self::initSmembers();
        }

        if($res) {
            $a = $redis->Sismember(self::$redis_set_name,$data['server_id'] . '_' . $data['role_id'] . '_' . $data['role_name']);

            if(!$a){
                if(!empty($data['server_id']) && !empty($data['role_id']) && !empty($data['role_name'])) {
                    $redis->sadd(self::$redis_set_name, $data['server_id'] . '_' . $data['role_id'] . '_' . $data['role_name']);
                }
            }
            $res = true;
        }else{
            $res = false;
        }


        return $res;
    }


    public static function edit($data)
    {
        $res = AccountRegistrationSqlServer::edit($data);
        return $res;
    }

    public static function del($data)
    {
        $res = AccountRegistrationSqlServer::del($data);

        return $res;
    }

    public static function dealData($data)
    {
        $where = ' 1=1 ';
        if ( isset($data['garrison_product_id']) && !empty($data['garrison_product_id']) ) {
            $where .= " and a.garrison_product_id = '{$data['garrison_product_id']}'";
        }

        if (  isset($data['sid']) && !empty($data['sid'])) {
            $where .= " and a.sid= '{$data['sid']}'";
        }

        if ( isset($data['status']) && $data['status'] !='' ) {
            $where .= " and a.status = {$data['status']}";
        }

        if (  isset($data['account']) && !empty($data['account'])) {
            $where .= " and a.account = '{$data['account']}'";
        }

        if (  isset($data['admin_name']) && !empty($data['admin_name'])) {
            $where .= " and c.realname = '{$data['admin_name']}'";
        }

        if (  isset($data['admin_department_id']) && !empty($data['admin_department_id'])) {
            $where .= " and a.admin_department_id = {$data['admin_department_id']}";
        }

        if (  isset($data['s_open_time']) && !empty($data['s_open_time'])) {
            $where .= " and a.add_time >= {$data['s_open_time']}";
        }

        if ( isset( $data['e_open_time']) && !empty($data['e_open_time'])) {
            $where .= " and a.add_time <= {$data['e_open_time']}";
        }

        if (  isset($data['s_end_time']) && !empty($data['s_end_time'])) {
            $where .= " and b.end_time >= {$data['s_end_time']}";
        }

        if (  isset($data['e_end_time']) && !empty($data['e_end_time'])) {
            $where .= " and b.end_time <= {$data['e_end_time']}";
        }

        return $where;

    }


    public static function initSmembers()
    {

        self::$redis->del(self::$redis_set_name);
        $list = AccountRegistrationServer::getList(['limit'  => 200000, 'page' => 1]);
        foreach($list as $k=>$v){
            if(!empty($v['server_id']) && !empty($v['role_id']) && !empty($v['role_name'])){
                self::$redis->sadd(self::$redis_set_name,$v['server_id'].'_'.$v['role_id'].'_'.$v['role_name']);
            }

        }
    }

}