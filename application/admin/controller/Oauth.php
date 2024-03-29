<?php
namespace app\admin\controller;
/* *
 *
 * 权限验证模块（继承用户入口模块）：初始化验证请求是否有权限
 * */

use common\base\BasicController;
use common\base\BasicServer;
use common\model\gr_chat\Admin;
use common\model\gr_chat\AdminLogN;
use common\server\AdminServer;
use common\server\SysServer;
use think\Db;

class Oauth extends BasicController
{
    public $user_data;//用户数据
    public $role_data;//权限数据
    public $common_data;//公共数据

    protected $no_oauth = ['test'];//不需权限控制方法，controller下自定义

    public function _initialize()
    {
        parent::_initialize();

        $this->checkUser();//用户数据初始化

        $this->checkPower();//权限控制

        $this->saveLog();//接口日志记录

    }

    protected function checkUser(){
        /**
         * 判断是否登陆了
         *
         * @return string
         */
        $token = $this->req->header('token');

        if(empty($token))
        {
            $this->rs['code'] = 1001;
            $this->rs['msg'] = 'token not exit';
            return return_json($this->rs,false);
        }

        list($user_data) = Admin::parseToken($token);

        if(!$user_data){
            $this->rs['code'] = 1001;
            $this->rs['msg'] = 'token error';
            return return_json($this->rs,false);
        }

        if($user_data['status'] != 1){
            $this->rs['code'] = 1001;
            $this->rs['msg'] = 'forbid user';
            return return_json($this->rs,false);
        }

        $redis = get_redis();

        $session_code = md5(config('sign_md5').$user_data['id']);//token存储key

        if($redis->get($session_code) != $token){
            $this->rs['code'] = 1001;
            $this->rs['msg'] = 'token expiration';
            return return_json($this->rs,false);
        }

        $user_data = $user_data->toArray();
        SysServer::dealAdminInfo($user_data);

        $def_platform = $this->req->header('platform');

        if(!$user_data['is_admin'] && !in_array($def_platform,explode(',',$user_data['platform']))){
            $this->rs['code'] = 1001;
            $this->rs['msg'] = $this->error_code[1003];
            return return_json($this->rs,false);
        }

        $this->common_data['def_platform'] = $def_platform;

        BasicServer::setData([
            'common_data'=>$this->common_data,
            'user_data'=>$user_data,
        ]);
        $this->user_data = $user_data;
    }

    protected function checkPower(){
        $menu = AdminServer::getMenu();
        $this->role_data = $menu;
        BasicServer::setRole($menu);
        $controller = camelize($this->req->controller(),2);
        $action = $this->req->action();

        if(in_array($controller,['Ajax'])){
            return true;
        }

        if($this->no_oauth){
            foreach ($this->no_oauth as $v){
                if($action == strtolower($v)){
                    return true;
                }
            }
        }

        $controller_info = isset($menu['role'][$controller] )?$menu['role'][$controller]:[];

        if($controller_info){
            foreach ($controller_info as $v){
                if($action == strtolower($v)){
                    return true;
                }
            }
        }
        return true;//开发阶段直接返回
        $this->rs['code'] = 1002;
        $this->rs['msg'] = $this->error_code[1002];
        return return_json($this->rs,false);
    }

    protected function saveLog(){
        //不需记录接口[controller=>action_arr]
        $no_check_arr = [
            'Admin'=>[
                'adminLogList',
                'adminLogStatistic',
            ],
            'Ajax'=>'all'
        ];

        $dispatch = $this->req->dispatch();

        $controllerName = camelize($dispatch['module'][1],2);
        $actionName = $dispatch['module'][2];

        $no_oauth = arr_to_lower($this->no_oauth);

        $no_check_arr = arr_to_lower($no_check_arr);

        if(in_array(strtolower($actionName),$no_oauth)){
            return false;
        }

        if(isset($no_check_arr[strtolower($controllerName)])){
            $this_action_arr = $no_check_arr[strtolower($controllerName)];

            if(is_array($this_action_arr) && !empty($this_action_arr)){
                if(in_array(strtolower($actionName),$this_action_arr)){
                    return false;
                }
            }elseif($this_action_arr == 'all'){
                return false;
            }
        }

        $data = json_encode($_REQUEST);

        $log_data = array(
            'ip' => $this->req->ip(),
            'admin_id' => $this->user_data['id'],
            'url' => $this->req->url(),
            'data' => json_encode($data),
            'controller' => $controllerName,
            'action' => $actionName,
        );

        $model = new AdminLogN();

        return $model->create($log_data);

    }

    public function getPost($param){
        $data = [];

        foreach ($param as $k => $v){
            $this_key = $v[0];
            if(isset($v[1])){
                switch ($v[1]){
                    case 'int':
                        $this_key.='/d';
                        break;
                    case 'trim':
                        $this_key.='/s';
                        break;
                    case 'array':
                        $this_key.='/a';
                        break;
                    default:
                        break;
                }
            }
            if(isset($v[2])){
                $data[$v[0]] = $this->req->post($this_key,$v[2]);
            }else{
                $data[$v[0]] = $this->req->post($this_key);
            }
        }

        return $data;
    }

    /**
     * 组装返回json格式的信息
     * @param string $msg
     * @param array $data
     * @param array $extra
     */
    protected function s_json($msg = 'ok',$data = [],$extra = []){

        if(!$data && $msg && is_array($msg)){
            $this->rs['msg'] = 'ok';
            $this->rs['data'] = $msg;
        }else{
            $this->rs = array_merge($this->rs,compact('msg','data'));
        }

        if($extra && is_array($extra)){
            $this->rs = array_merge($this->rs,$extra);
        }

        return_json($this->rs,false);
    }

    /**
     * 组装返回json格式信息
     * @param string $msg
     * @param array $data
     * @param int $code
     */
    protected function f_json($msg='error',$data=[],$code=200){

        if(!$data && $msg && is_array($msg)){
            $this->rs['msg'] = 'error';
            $this->rs['data'] = $msg;
            $this->rs['code'] = $code;
        }else{
            $this->rs = array_merge($this->rs,compact('msg','data','code'));
        }

        return_json($this->rs,false);
    }
}
