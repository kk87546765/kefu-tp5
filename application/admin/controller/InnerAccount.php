<?php
/**
 * Created by PhpStorm.
 * User: crosstime
 * Date: 2017/10/12
 * Time: 上午11:57
 */
namespace app\admin\controller;

use common\server\InnerAccount\InnerAccountServer;
use common\server\SysServer;
use common\sql_server\KefuCommonMember;
use common\libraries\Common;
use common\libraries\Ipip\IP4datx;
use common\server\CustomerPlatform\CommonServer;
class InnerAccount extends Oauth
{

    protected $no_oauth = ['getType'];

    public $admin_department = [
        1=>['id'=>1,'name'=>'游娱-客服部-GS1组'],
        2=>['id'=>2,'name'=>'游娱-客服部-GS2组']
    ];


    //新版UI
    public function index()
    {

        $data['uid'] = $this->request->post('uid/s', '' );
        $data['admin_id'] = $this->request->post('admin_id/d', 0);
        $data['page'] = $this->request->request('page/d', 1);
        $data['limit'] = $this->request->request('limit/d', 20);
        $data['status'] = 1;
        $res = InnerAccountServer::getList($data);

        foreach($res as $k=>&$v){
            $v['admin_department'] = $this->admin_department[$v['admin_department_id']]['name'];
        }

        $count = InnerAccountServer::count($data);
        $this->rs['code'] = 0;
        $this->rs['msg'] = '获取成功';
        $this->rs['data'] = $res;
        $this->rs['count'] = $count;
        return return_json($this->rs);

    }

    public function edit()
    {
        $data['id'] = $this->request->post('id/d', 0);
        $data['platform_id'] = $this->request->post('platform_id/d', 0);
        $data['admin_department_id'] = $this->request->post('admin_department_id/d', 0);
        $data['admin_id'] = $this->request->post('admin_id/d', 0);
        $data['uid'] = $this->request->post('uid/s', '');
        $data['often_login_equipment'] = $this->request->post('often_login_equipment/s', '');
        $data['often_login_ip'] = $this->request->post('often_login_ip/s', '');
        $data['admin_department_id'] = isset($this->admin_department[$data['admin_department_id']]['id']) ? $this->admin_department[$data['admin_department_id']]['id'] : '';

        $res = InnerAccountServer::add($data);

        $this->rs['code'] = $res['code'];
        $this->rs['msg'] = $res['msg'];

        return return_json($this->rs);
    }

    public function add()
    {
        $data['id'] = 0;
        $data['platform_id'] = $this->request->post('platform_id/d', 0);
        $data['admin_department_id'] = $this->request->post('admin_department_id/d', 0);
        $data['admin_id'] = $this->request->post('admin_id/d', 0);
        $data['uid'] = $this->request->post('uid/s', '');
        $data['often_login_equipment'] = $this->request->post('often_login_equipment/s', '');
        $data['often_login_ip'] = $this->request->post('often_login_ip/s', '');
        $data['admin_department_id'] = isset($this->admin_department[$data['admin_department_id']]['id']) ? $this->admin_department[$data['admin_department_id']]['id'] : '';

        $res = InnerAccountServer::add($data);

        $this->rs['code'] = $res == true ? 0 : -1;
        $this->rs['msg'] = '操作成功';

        return return_json($this->rs);
    }


    public function del(){
        $data['id'] = $this->request->get('ids/d', 0 );
        $data['status'] = 0;
        $res = InnerAccountServer::del($data);


        if ($res) {
            $return['code'] = 0;
            $return['msg'] = '删除成功';
        } else {
            $return['code'] = -1;
            $return['msg'] = '删除失败';
        }

        return return_json($return);

    }

    public function getOne()
    {
        $data['id'] = $this->request->request('id/d', 0);
        $res = InnerAccountServer::getOne($data);

        $this->rs['code'] = 0;
        $this->rs['msg'] = '获取成功';
        $this->rs['data'] = $res;
        return return_json($this->rs);

    }


    public function getAdminDepartment()
    {
        $admin_department = $this->admin_department;
        $return['msg'] = '获取成功';
        $return['code'] = 0;
        $return['data'] = $admin_department;

        return return_json($return);
    }


    public function getAdmin()
    {
        $platform_id = $this->request->post('platform_id/d', 0);
        $p_data['role_id'] = '23,24,25,26';
        $p_data['is_active'] = 1;
        $p_data['platform_id'] = $platform_id;

        $admin_list = SysServer::getUserListByAdminInfo($this->user_data,$p_data);

        $res['code'] = 0;
        $res['msg'] = '成功';
        $res['data'] = $admin_list;

        return return_json($res);

    }



    public function getType()
    {
        $this->rs['code'] = 0;
        $this->rs['msg'] = '获取成功';
        $this->rs['data'] = [
            'type'=>$this->type
        ];
        return return_json($this->rs);
    }
}