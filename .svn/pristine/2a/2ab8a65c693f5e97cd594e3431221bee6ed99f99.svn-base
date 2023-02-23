<?php
/**
 * Created by PhpStorm.
 * User: crosstime
 * Date: 2017/10/12
 * Time: 上午11:57
 */
namespace common\server\RoleSearch;

use common\base\BasicServer;

use common\sql_server\RoleSearch;


class RoleSearchServer extends BasicServer
{

    public static $return = ['status'=>false,'msg'=>''];



    public static function getList($platform_info,$conditions,$page,$limit){

        $condition_data = self::dealWhere($conditions);

        $res = RoleSearch::getList($platform_info['platform_id'],$platform_info['platform_suffix'],$condition_data['where'],$condition_data['having'],$page,$limit);
        foreach($res as $k=>$v){
            $res[$k]['login_date'] = empty($v['login_date']) ? '' : date('Y-m-d H:i:s',$v['login_date']);
            $res[$k]['role_reg_time'] = empty($v['role_reg_time']) ? '' : date('Y-m-d H:i:s',$v['role_reg_time']);
            $res[$k]['account_reg_time'] = empty($v['account_reg_time']) ? '' : date('Y-m-d H:i:s',$v['account_reg_time']);
            $res[$k]['account_reg_time'] = empty($v['account_reg_time']) ? '' : date('Y-m-d H:i:s',$v['account_reg_time']);
            $res[$k]['uname'] = isphone($res[$k]['uname']) ? substr_replace($res[$k]['uname'],'****',4,4) : $res[$k]['uname'];
        }
        return $res;

    }

    public static function getCount($platform_info,$conditions){

        $condition_data = self::dealWhere($conditions);

        $res = RoleSearch::getCount($platform_info['platform_id'],$platform_info['platform_suffix'],$condition_data['where'],$condition_data['having']);

        return $res;

    }

    public static function dealWhere($conditions){

        $where = '1=1 ';
        $having = '1=1 ';
        if(isset($conditions['product_id']) && $conditions['product_id']){
            $where .= " AND product_id = {$conditions['product_id']}";
        }

        if($conditions['user_name']){
            $where .= " AND r.uname = '{$conditions['user_name']}'";
        }

        if($conditions['uid']){
            $where .= " AND r.uid = {$conditions['uid']}";
        }

        if($conditions['reg_gid']){


            $reg_gid = implode(',',$conditions['reg_gid']);
            $where .= " AND r.reg_gid in ({$reg_gid})";
        }

        if($conditions['login_gid']){
            $where .= " AND login_gid = {$conditions['login_gid']}";
        }

        if($conditions['server_id']){
            $where .= " AND r.server_id = '{$conditions['server_id']}'";
        }

        if($conditions['role_name']){
            $where .= " AND r.role_name = '{$conditions['role_name']}'";
        }

        if($conditions['role_id']){
            $where .= " AND r.role_id = {$conditions['role_id']}";
        }

        if($conditions['trans_level']){
            $where .= " AND r.trans_level = {$conditions['trans_level']}";
        }

        if($conditions['role_level']){
            $where .= " AND r.role_level = {$conditions['role_level']}";
        }

        if($conditions['s_reg_time'] && empty($conditions['s_reg_role_days'])){
            $where .= " AND r.reg_time >= {$conditions['s_reg_time']}";
        }

        if($conditions['e_reg_time'] &&  empty($conditions['e_reg_role_days'])){
            $where .= " AND r.reg_time <= {$conditions['e_reg_time']}";
        }

        if($conditions['s_money']){
            $having .= " AND total_amount >= {$conditions['s_money']}";
        }

        if($conditions['e_money']){
            $having .= " AND total_amount <= {$conditions['e_money']}";
        }


        if($conditions['s_reg_days']){
            $time = mktime(0,0,0,date('m'),date('d')-$conditions['s_reg_days'],date('Y'));
            $where .= " AND m.reg_date <= {$time}";
        }

        if($conditions['e_reg_days']){
            $time = mktime(0,0,0,date('m'),date('d')-$conditions['e_reg_days'],date('Y'));
            $where .= " AND m.reg_date >= {$time}";
        }

        if($conditions['s_reg_role_days']){

            if(!empty($conditions['s_reg_time'])){
                $tmp_time = $conditions['s_reg_time'];
            }else{
                $tmp_time = time();
            }
            $time = mktime(0,0,0,date('m',$tmp_time),date('d',$tmp_time)-$conditions['s_reg_days'],date('Y',$tmp_time));
            $where .= " AND r.reg_time <= {$time}";
        }

        if($conditions['e_reg_role_days']){
            if(!empty($conditions['e_reg_time'])){
                $tmp_time = $conditions['e_reg_time'];
            }else{
                $tmp_time = time();
            }
            $time = mktime(0,0,0,date('m',$tmp_time),date('d',$tmp_time)-$conditions['e_reg_days'],date('Y',$tmp_time));
            $where .= " AND r.reg_time >= {$time}";
        }
        $data['where'] = $where;
        $data['having'] = $having;

        return $data;

    }

    public static function changeUname($platform_info,$uid)
    {
        $where = "uid={$uid}";
        $res = RoleSearch::getList($platform_info['platform_id'],$platform_info['platform_suffix'],$where,'',1,1);
        var_duMP($res);exit;
    }


}