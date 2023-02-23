<?php
/**
 * Created by PhpStorm.
 * User: crosstime
 * Date: 2017/10/12
 * Time: 上午11:57
 */
namespace common\server\PayOrderSearch;


use common\base\BasicServer;
use common\sql_server\PayOrderSearch;


class PayOrderSearchServer extends BasicServer
{

    public static $return = ['status'=>false,'msg'=>''];



    public static function getList($platform_info,$conditions,$order,$order_type,$page,$limit){

        $condition_data = self::dealWhere($conditions);

        $res = PayOrderSearch::getList($platform_info,$condition_data['where'],$order,$order_type,$page,$limit);

        foreach($res as $k=>$v){
            $res[$k]['yuanbao_status'] = $v['yuanbao_status'] == 1 ? '已发放' : '未发放';
            $res[$k]['pay_time'] = empty($v['pay_time']) ? '' : date('Y-m-d H:i:s',$v['pay_time']);
            $res[$k]['first_login_game_time'] = empty($v['first_login_game_time']) ? '' : date('Y-m-d H:i:s',$v['first_login_game_time']);
        }
        return $res;

    }

    public static function getCount($platform_info,$conditions){

        $condition_data = self::dealWhere($conditions);

        $res = PayOrderSearch::getCount($platform_info,$condition_data['where']);

        return $res;

    }

    public static function dealWhere($conditions){
        $where = '1=1 ';

        if($conditions['product_id']){
            $where .= " AND pgi.product_id = {$conditions['product_id']}";
        }

        if($conditions['user_name']){
            $where .= " AND po.user_name = '{$conditions['user_name']}'";
        }

        if($conditions['uid']){
            $where .= " AND po.uid = {$conditions['uid']}";
        }

        if($conditions['order_id']){
            $where .= " AND po.order_id = '{$conditions['order_id']}'";
        }

        if($conditions['pay_game_id']){
            $pay_game_id = implode(',',$conditions['pay_game_id']);
            $where .= " AND po.gid in ({$pay_game_id})";
        }

        if($conditions['yuanbao_status'] >= 0){
            $where .= " AND po.yuanbao_status = '{$conditions['yuanbao_status']}'";
        }

        if($conditions['role_name']){
            $where .= " AND po.role_name = '{$conditions['role_name']}'";
        }

        if($conditions['role_id']){
            $where .= " AND po.role_id = {$conditions['role_id']}";
        }

        if($conditions['third_party_order_id']){
            $where .= " AND po.third_party_order_id = '{$conditions['third_party_order_id']}'";
        }

        if($conditions['reg_channel']){
            $where .= " AND po.reg_channel = {$conditions['reg_channel']}";
        }

        if($conditions['pay_channel']){
            $where .= " AND po.pay_channel = {$conditions['pay_channel']}";
        }

//        if($conditions['first_login_game_id']){
//            $first_login_game_id = implode(',',$conditions['first_login_game_id']);
//            $where .= " AND po.first_login_game_id in ({$first_login_game_id})";
//        }
//
//        if($conditions['s_first_login_game_time'] && empty($conditions['order_id'])){
//            $where .= " AND po.first_login_game_time >= {$conditions['s_first_login_game_time']}";
//        }
//
//        if($conditions['e_first_login_game_time'] && empty($conditions['order_id'])){
//            $where .= " AND po.first_login_game_time < {$conditions['e_first_login_game_time']}";
//        }

        if($conditions['s_pay_time'] && empty($conditions['order_id'])){
            $where .= " AND po.pay_time >= {$conditions['s_pay_time']}";
        }

        if($conditions['e_pay_time'] && empty($conditions['order_id'])){
            $where .= " AND po.pay_time < {$conditions['e_pay_time']}";
        }


//        if($conditions['s_reg_date'] && empty($conditions['order_id'])){
//            $where .= " AND r.reg_time >= {$conditions['s_reg_date']}";
//        }
//
//        if($conditions['e_reg_date'] && empty($conditions['order_id'])){
//            $where .= " AND po.pay_time < {$conditions['e_reg_date']}";
//        }


        $data['where'] = $where;

        return $data;

    }


}