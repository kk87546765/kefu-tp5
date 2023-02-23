<?php
/**
 * Created by PhpStorm.
 * User: crosstime
 * Date: 2017/10/12
 * Time: 上午11:57
 */

namespace app\admin\controller;


use common\libraries\Common;
use common\server\PayOrderSearch\PayOrderSearchServer;


class PayOrderSearch extends Oauth
{



    //新版UI
    public function index()
    {


        $page       = $this->request->get('page/d',  1);
        $limit      = $this->request->get('limit/d', 20);

        $platform_id  = $this->request->post('platform_id/s',  '');

        if(empty($platform_id)){

            $this->rs['code'] = 0;
            $this->rs['msg'] = '获取失败';
            $this->rs['data'] = [];
            $this->rs['count'] = 0;
            return return_json($this->rs);

        }
        $product_id = $this->request->post('p_product_id/s', '');
        if($product_id){
            list($tmp_platform_id,$product_id)  = explode('_',$product_id);
        }
        $p_game_id = $this->request->post('p_game_id/s', '');

        $uid  = $this->request->post('uid/d', 0);
        $user_name  = $this->request->post('user_name/s', '');

        $role_name  = $this->request->post('role_name/s', '');
        $role_id  = $this->request->post('role_id/d', 0);
        $reg_channel = $this->request->post('reg_channel/d', 0);
        $pay_channel = $this->request->post('pay_channel/d', 0);
        $order_id = $this->request->post('order_id/s', '');
        $third_party_order_id = $this->request->post('third_party_order_id/s','');
        $payment = $this->request->post('payment/d', 0);
        $yuanbao_status = $this->request->post('yuanbao_status/d', -1);
        $first_login_game_id = $this->request->post('first_login_game_id/s', '');
        $s_first_login_game_time = $this->request->post('s_first_login_game_time/s', '');
        $e_first_login_game_time = $this->request->post('e_first_login_game_time/s', '');
        $s_pay_time = $this->request->post('s_pay_time/s',  '');
        $e_pay_time = $this->request->post('e_pay_time/s',  '');
        $s_reg_date = $this->request->post('s_reg_date/s',  '');
        $e_reg_date = $this->request->post('e_reg_date/s',  '');


        $order = $this->request->post('order/s', 'pay_time');
        $order_type = $this->request->post('order_type/d',  2);
        $order_type = $order_type == 1 ? 'asc' : 'desc';
        $game_arr = explode(',',$p_game_id);
        $game_arr2 = explode(',',$first_login_game_id);
        $tmp_game_arr2 = $tmp_game_arr = [];
        if($game_arr){
            foreach($game_arr as $k=>$v){
                if(strpos($v,'_')){
                    list($tmp_platform_id,$tmp_game_arr[]) = explode('_',$v);
                }
            }
        }
        if($game_arr2){
            foreach($game_arr2 as $k1=>$v1){
                if(strpos($v1,'_')){
                    list($tmp_platform_id,$tmp_game_arr2[]) = explode('_',$v1);
                }
            }
        }

        $platform_info = Common::getPlatformInfoByPlatformIdAndCache($platform_id);


        $conditions = [
            'product_id' => $product_id,
            'platform_id' => $platform_info['platform_id'],
            'user_name' => $user_name,
            'uid' => $uid,
            'pay_game_id' => $tmp_game_arr,
            'reg_channel' => $reg_channel,
            'pay_channel' => $pay_channel,
            'role_name' => $role_name,
            'role_id' => $role_id,
            'payment' => $payment,
            'yuanbao_status' => $yuanbao_status,
            'order_id' => $order_id,
            'third_party_order_id' => $third_party_order_id,
            'first_login_game_id' => $tmp_game_arr2,
            's_first_login_game_time' => strtotime($s_first_login_game_time),
            'e_first_login_game_time' => strtotime($e_first_login_game_time),
            's_pay_time' => strtotime($s_pay_time),
            'e_pay_time' => strtotime($e_pay_time),
            's_reg_date' => strtotime($s_reg_date),
            'e_reg_date' => strtotime($e_reg_date),

        ];

        $res = PayOrderSearchServer::getList($platform_info,$conditions,$order,$order_type,$page,$limit);

        $count = PayOrderSearchServer::getCount($platform_info,$conditions);


        $this->rs['code'] = 0;
        $this->rs['msg'] = '获取成功';
        $this->rs['data'] = $res;
        $this->rs['count'] = $count;
        return return_json($this->rs);

    }



}