<?php
/**
 * Created by PhpStorm.
 * User: crosstime
 * Date: 2017/10/12
 * Time: 上午11:57
 */

namespace app\admin\controller;

use common\libraries\{Common,Ipip\IP4datx};
use common\server\AccountRegistration\AccountRegistrationServer;
use common\server\AccountRegistrationLog\AccountRegistrationLogServer;
use common\server\GarrisonProduct\GarrisonProductServer;
use common\server\SysServer;

//
//use common\Models\Admin;
//use common\Models\GarrisonProduct;
//use common\Models\AccountRegistration;
//use common\Models\AccountRegistrationLog;
//use common\Models\KefuCommonMember;
//use common\Models\KefuUserRole;
//use common\Models\Statistic\GameProduct;


class AccountRegistration extends Oauth
{



    //新版UI
    public function index()
    {

        $data['page']       = $this->request->post('page/d',  1);
        $data['limit']      = $this->request->post('limit/d', 20);
        $data['garrison_product_id']  = $this->request->post('garrison_product_id/s','');
        $data['sid'] = $this->request->post('sid/s', '');
        $data['status'] = $this->request->post('status/d', 0);
        $data['account'] = $this->request->post('account/s','');
        $data['admin_name'] = $this->request->post('admin_name/s', '');
        $data['s_open_time'] = strtotime($this->request->post('s_open_time/s', ''));
        $data['e_open_time'] = strtotime($this->request->post('e_open_time/s',  ''));
        $data['s_end_time'] = strtotime($this->request->post('s_end_time/s',  ''));
        $data['e_end_time'] = strtotime($this->request->post('e_end_time/s',  ''));
        $data['admin_department_id'] = $this->request->post('admin_department_id/s', 0);

        $infos = AccountRegistrationServer::getList($data);

        $total = AccountRegistrationServer::getCount($data);

        return return_json([
            'code' => 0,
            'msg' => "ok",
            'count' => $total,
            'data' => $infos
        ]);

    }

    public function getAdminDepartment()
    {
        $admin_department = AccountRegistrationServer::$admin_department;
        $return['msg'] = '获取成功';
        $return['code'] = 0;
        $return['data'] = $admin_department;

        return return_json($return);
    }

    public function getGarrisonProductList()
    {
        $garrison_product_list = GarrisonProductServer::getGarrisonProductList();
        return return_json([
            'code' => 0,
            'msg' => "ok",
            'data' => $garrison_product_list
        ]);
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
    //添加
    public function add()
    {

        $data['garrison_product_id'] = $this->request->post('garrison_product_id/d', 0 );
        $data['server_id'] = $this->request->post('server_id/d', 0);
        $data['role_id'] = $this->request->post('role_id/d', '');
        $data['role_name'] = $this->request->post('role_name/s','');
        $data['admin_department_id'] = $this->request->post('admin_department_id/d', 0);
        $data['admin_id'] = $this->request->post('admin_id/d', 0);
        $data['account'] = $this->request->post('account/s', '');
        $data['password'] = $this->request->post('password/s', '');
        $data['status'] = $this->request->post('status/d', 0);
//        $data['admin_user'] = $this->user_data['username'];
        $data['add_time'] = time();


        $res = AccountRegistrationServer::add($data);

        if($res){
            $return['code'] = 0;
            $return['msg'] = '添加成功';
        }else{
            $return['code'] = -1;
            $return['msg'] = '添加失败';
        }

        return return_json($return);

    }


    public function getOne()
    {
        $id = $this->request->post('id/d', 0 );
        $info = AccountRegistrationServer::getOne($id);

        $return['code'] = 0;
        $return['msg'] = '获取成功';
        $return['data'] = $info;
        return return_json($return);

    }




    //添加
    public function edit()
    {

        $data['id'] = $this->request->post('id', 'int', 0 );
        $data['garrison_product_id'] = $this->request->post('garrison_product_id', 'int', 0 );
        $data['server_id'] = $this->request->post('server_id', 'int', 0);
        $data['role_id'] = $this->request->post('role_id', 'trim', '');
        $data['role_name'] = $this->request->post('role_name', 'trim','');
        $data['admin_department_id'] = $this->request->post('admin_department_id', 'int', 0);
        $data['admin_id'] = $this->request->post('admin_id', 'int', 0);
        $data['account'] = $this->request->post('account', 'trim', '');
        $data['password'] = $this->request->post('password', 'trim', '');
        $data['status'] = $this->request->post('status', 'int', 0);
        $data['admin_id'] = $this->user_data['id'];


        $res = AccountRegistrationServer::edit($data);

        $res2 = false;
        if($res){
            $log_data = $data;
            unset($log_data['id']);
            $log_data['log_id'] = $data['id'];
            $log_data['operator_id'] = $data['admin_id'];
            $log_data['operator_name'] = $this->user_data['username'];
            $log_data['add_time'] = time();

            $res2 = AccountRegistrationLogServer::add($log_data);
        }

        if($res2){
           $return['code'] = 0;
           $return['msg'] = '修改成功';
        }else{
            $return['code'] = -1;
            $return['msg'] = '修改失败';
        }

        return return_json($return);
    }

    public function del(){
        $data['id'] = $this->request->get('ids/d', 0 );

        $res = AccountRegistrationServer::del($data);


        if ($res) {
            $return['code'] = 0;
            $return['msg'] = '删除成功';
        } else {
            $return['code'] = -1;
            $return['msg'] = '删除失败';
        }

        return return_json($return);

    }

    static function get_client_city($ip){

        if($ip){
            $response = file_get_contents("https://restapi.amap.com/v3/ip?ip={$ip}&output=json&key=bd5dfbd12e4966759753bb9d620d6e92", 'GET');
            //print_r($response);
            if ($response)
            {
                $response = json_decode($response);
                if($response->status=="1"){
                    return $response->city;
                }
            }
        }
    }







}