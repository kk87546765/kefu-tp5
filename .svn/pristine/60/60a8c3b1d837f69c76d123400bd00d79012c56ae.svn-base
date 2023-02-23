<?php
/**
 * Created by PhpStorm.
 * User: crosstime
 * Date: 2017/10/12
 * Time: 上午11:57
 */

namespace app\admin\controller;

use common\base\BasicServer;

use common\server\DistributionalGameServer\DistributionalGameServerServer;
use common\server\SysServer;


class DistributionalGameServer extends Oauth
{



    public function index()
    {
        $data['game_id'] = explode(',',$this->request->post('p_game_id/s',  ''));

        foreach($data['game_id'] as $k=>$v){
            @list($platform_id,$tmp_game_id[]) = explode('_',$v);
        }
        if(isset($tmp_game_id)){
            $data['game_id'] = implode(',',$tmp_game_id);
        }

        @list($data['platform_id'],$data['product_id']) = explode('_',$this->request->post('garrison_product_id/s',  ''));

        $data['server_id']            = $this->request->post('server_id/s', '');
        $data['admin_name']           = $this->request->post('admin_name/s', '');
        $data['status']               = $this->request->post('status/s', '');
        $data['open_time_1']          = $this->request->request('s_open_time/s', '');
        $data['open_time_2']          = $this->request->request('e_open_time/s','');
        $data['end_time_1']           = $this->request->request('s_end_time/s', '');
        $data['end_time_2']           = $this->request->request('e_end_time/s', '');
        $data['start_time']           = $this->request->post('start_time/s', '');
        $data['is_excel']             = $this->request->post('is_excel/d', 0);
        $data['only_admin']           = $this->request->post('only_admin/d', 0);
        $data['page']                 = $this->request->get('page/d', 1);
        $data['limit']                = $this->request->get('limit/d', 20);
        $data['init']                 = $this->request->get('init/d', 0);


        $list = DistributionalGameServerServer::getList($data);

        $count = DistributionalGameServerServer::getCount($data);

        $return['code'] = 0;
        $return['msg'] = '获取成功';

        if($list){
            $return['data'] = $list;
            $return['count'] = $count;
        }else{
            $return['msg'] = '暂无数据';
            $return['data'] = [];
        }

        return return_json($return);



    }


    public function edit()
    {

        $id = $this->request->post('id/d', 0);
        $garrison_product_id = $this->request->post('garrison_product_id/d', 0);
        $data['role_id'] = $this->request->post('role_id/d',0);
        $data['role_name'] = $this->request->post('role_name/s','');

        $data['admin_id'] = $this->request->post('admin_id/d', 0);
        $data['start_time'] = $this->request->post('start_time/s', '');

        $res = DistributionalGameServerServer::updateInfo($id,$data);

        return return_json($res);



    }



    public function getOne()
    {
        $id = $this->request->post('id/d',  0);
        $info = DistributionalGameServerServer::getOne($id);

        $res['code'] = 0;
        $res['msg'] = '成功';
        $res['data'] = $info;

        return return_json($res);
    }

    public function getAdmin()
    {
        $p_data = ['role_id'=>'23,24,25,26'];
        $admin_list = SysServer::getUserListByAdminInfo($this->user_data,$p_data);

        $res['code'] = 0;
        $res['msg'] = '成功';
        $res['data'] = $admin_list;

        return return_json($res);

    }



    //修改开服时间
    public function openTimeEdit()
    {
        $id = $this->request->post('id/d', 0);
        $open_time = $this->request->post('open_time/s', '');

        $res = DistributionalGameServerServer::updateOpenTime($id,$open_time);

        return return_json($res);
    }




    //归档
    public function changeServerStatus()
    {
        $id = $this->request->post('id/d',0);
        $data['change_status'] = $this->request->post('change_status/d',0);
        $res = DistributionalGameServerServer::changeStatus($id,$data);

        return return_json($res);
    }













}