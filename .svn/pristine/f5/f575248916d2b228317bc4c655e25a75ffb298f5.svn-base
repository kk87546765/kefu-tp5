<?php
/**
 * Created by PhpStorm.
 * User: crosstime
 * Date: 2017/10/12
 * Time: 上午11:57
 */

namespace app\admin\controller;

use common\base\BasicServer;

use common\server\DistributionalServer\DistributionalServerServer;
use common\server\SysServer;


class DistributionalServer extends Oauth
{



    public function index()
    {

        $data['garrison_product_id']  = $this->request->post('garrison_product_id/s', '');
        $data['server_id']            = $this->request->post('server_id/s', '');
        $data['admin_name']           = $this->request->post('admin_name/s', '');
        $data['status']               = $this->request->post('status/s', '');
        $data['open_time_1']          = $this->request->request('s_open_time/s', '');
        $data['open_time_2']          = $this->request->request('e_open_time/s','');
        $data['end_time_1']           = $this->request->request('s_end_time/s', '');
        $data['end_time_2']           = $this->request->request('e_end_time/s', '');
        $data['start_time']           = $this->request->post('start_time/s', '');
        $data['is_excel']             = $this->request->post('is_excel/d', 0);
        $data['page']                 = $this->request->post('page/d', 1);
        $data['limit']                = $this->request->post('limit/d', 20);


//            $model = new DistributionalServer();


        $list = DistributionalServerServer::getList($data);

        $count = DistributionalServerServer::getCount($data);

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

    //添加
    public function edit()
    {

        $id = $this->request->post('id/d', 0);
        $garrison_product_id = $this->request->post('garrison_product_id/d', 0);
        $data['role_id'] = $this->request->post('role_id/d',0);
        $data['role_name'] = $this->request->post('role_name/s','');

        $data['admin_id'] = $this->request->post('admin_id/d', 0);
        $data['start_time'] = $this->request->post('start_time/s', '');

        $res = DistributionalServerServer::updateInfo($id,$data);

        return return_json($res);



    }



    public function getOne()
    {
        $id = $this->request->post('id/d',  0);
        $info = DistributionalServerServer::getOne($id);

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

        $res = DistributionalServerServer::updateOpenTime($id,$open_time);

        return return_json($res);
    }




    //归档
    public function changeServerStatus()
    {
        $id = $this->request->post('id/d',0);
        $data['change_status'] = $this->request->post('change_status/d',0);
        $res = DistributionalServerServer::changeStatus($id,$data);

        return return_json($res);
    }


}