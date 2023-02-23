<?php
/**
 * Created by PhpStorm.
 * User: crosstime
 * Date: 2017/10/12
 * Time: 上午11:57
 */

namespace app\admin\controller;


use common\server\Waring\WaringServer;


class Waring extends  Oauth
{
    protected $block_keyword_key = 'block_keyword_set';
    protected $common_keyword_forbid = 'common_keyword_set';
    protected $common_keyword_list = "common_keyword_list";


    public function index()
    {


        $deal_data['page']        = $this->request->post('page/d', 1);
        $deal_data['limit']       = $this->request->post('limit/d',  20);
        $deal_data['ip']          = $this->request->post('ip/s','');
        $deal_data['imei']        = $this->request->post('imei/s',  '');
        $deal_data['limit_num']   = $this->request->post('limit_num/d', 20);
        $deal_data['type']        = $this->request->post('type/d', 0);

        $deal_data['s_time']      = $this->request->post('s_time/s', '');
        $deal_data['e_time']      = $this->request->post('e_time/s', '');
        $deal_data['e_time']      = $this->request->post('e_time/s', '');

        $deal_data['p_g']         = $this->request->post('p_g/s', '');

        $deal_data['platform_id'] = $this->request->post('platform_id/d', 0);
        $limit_num   = empty($limit_num)? 1 : $limit_num;


        $list = WaringServer::getList($deal_data);

        $total = WaringServer::getCount($deal_data);

        $this->rs['code'] = 0;
        $this->rs['msg'] = $list['msg'];
        $this->rs['data'] = $list['data'];
        $this->rs['count'] = $total;
        return return_json($this->rs);

    }


    public function check_user(){

        $post_data['type'] = $this->request->post('type/d', 0);
        $post_data['account'] = $this->request->post('account/s',  '');
        $post_data['platform_id'] = $this->request->post('platform_id/d',  '');


        $post_data['page']  = $this->request->post('page/d', 1);
        $post_data['limit'] = $this->request->post('limit/d', 20);
//
        $post_data['type'] = $this->request->post('type/d', 0);

        $post_data['check_value'] = $this->request->post('check_value/s', '');
        $post_data['check_type'] = $this->request->post('check_type/s','');





//        $game_list = $this->getGameList();


        $list = WaringServer::getWaringUser($post_data);

        $total = WaringServer::getWaringUserCount($post_data);




//        $this->gamelist['autoforbid']="自动封禁";

        foreach ($list as $k=>$v) {
            $list[$k]['reg_date']   = date('Y-m-d H:i:s',$v['reg_date']);
            $list[$k]['login_date']   = empty($v['login_date']) ? '' : date('Y-m-d H:i:s',$v['login_date']);
            $list[$k]['status']   = $v['status'] == 1?'正常':'已封禁';


        }

        $this->rs['code'] = 0;
        $this->rs['msg'] = '获取成功';
        $this->rs['data'] = $list;

        $this->rs['count'] = $total;
        return return_json($this->rs);


//
//        $this->gamelist['autoforbid']="自动封禁";
//
//        if($type == 0){
//            $type_name = 'IP';
//        }else{
//            $type_name = 'imei';
//        }
    }



}