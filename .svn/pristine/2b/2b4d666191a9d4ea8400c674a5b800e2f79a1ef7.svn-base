<?php
/**
 * Created by PhpStorm.
 * User: crosstime
 * Date: 2017/10/12
 * Time: 上午11:57
 */
namespace app\admin\controller;

#gs用户管理

use common\server\Chat\Admin;
use common\server\Gsuser\GsuserServer;
//use common\Models\GameCompany;

class Gsuser extends Oauth
{


    public function user(){

        $data['realname']   = $this->request->post('realname','');
        $data['username']   = $this->request->post('username','');

        $data['page']       = $this->request->post('page', 1);
        $data['limit']      = $this->request->post('limit', 20);

        $users = GsuserServer::getUser($data);

        $count = GsuserServer::getCount($data);

        if($users){
            $this->rs['code'] = 0;
            $this->rs['msg'] = '获取成功';
            $this->rs['data'] = $users;
            $this->rs['total'] = $count;
        }else{
            $this->rs['code'] = -1;
            $this->rs['msg'] = '获取失败';
        }

        return return_json($this->rs);

    }
    #添加用户
    public function add_user(){

        $username = $this->request->post('username/s',  '');
        $password = $this->request->post('password/s', '');
        $realname = $this->request->post('realname/s', '');
        $status   = $this->request->post('status/s', 'off');

        if( strlen($password)<6 || strlen($password)>20 ){
            $this->json(['status'=>0,'msg'=>'密码长度6至20位']);
        }
        $admin = new Admin();

        $data = array(
            'username' => $username,
            'password' => md5($password),
            'realname' => $realname,
            'add_time' => time(),
            'role_id'  => $this->gs_role_id,
            'status'   => $status=="on"?1:2
        );
        $ret = $admin->add($data);

        if( $ret ){
            $this->json(['status'=>1,'msg'=>'添加成功']);
        }
        $this->json(['status'=>0,'msg'=>'网络繁忙']);

    }

    #修改用户
    public function update_user(){

        if( $this->request->isAjax() ){
            $id       = $this->request->getPost('id', 'int', 0);
            $password = $this->request->getPost('password', 'trim', '');
            $realname = $this->request->getPost('realname', 'trim', '');
            $status   = $this->request->getPost('status', 'trim', 'off');

            $data = array(
                'realname' => $realname,
                'status'   => $status=="on"?1:2,
                'id'       => $id
            );

            if ($password) {
                if( strlen($password)<6 || strlen($password)>20 ){
                    $this->json(['status'=>0,'msg'=>'密码长度6至20位']);
                }
                $data['password'] = md5($password);
            }
            $admin = new Admin();
            $ret = $admin->edit($data);
            if( $ret ){
                $this->json(['status'=>1,'msg'=>'修改成功']);
            }
            $this->json(['status'=>0,'msg'=>'网络繁忙']);
        }

        $id = $_GET['id'];
        $admin = Admin::findFirst(['id=?0', 'bind' => [$id]]);
        $this->view->setVar('admin', $admin->toArray());
    }
}