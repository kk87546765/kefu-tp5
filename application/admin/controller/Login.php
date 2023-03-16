<?php
namespace app\admin\controller;
use common\model\gr_chat\Admin;
use common\server\SysServer;
use think\Db;
use common\base\BasicController;
use think\Env;

/* *
 *
 * 登陆模块：用户登陆、获取验证码、判断是否登陆了
 * */
class Login extends BasicController
{
    /**
     * 用户登陆
     *
     * username 用户名
     * password 密码
     * code 验证码
     */
    public function index()
    {
        $param = $this->req->param();

        $msg = is_empty($param,['username','password']);
        if($msg)
        {
            $this->rs['code'] = 4;
            $this->rs['msg'] = '非法操作';
            return return_json($this->rs);
        }

        //核实username规则
        if(!check_validate(['username'=>'require|max:20|alphaNum'],['username'=>$param['username']]))
        {
            $this->rs['code'] = 5;
            $this->rs['msg'] = '用户名或密码不对';
            return return_json($this->rs);
        }

        //核实password规则
        if(!check_validate(['password'=>'require|max:100'],['password'=>$param['password']]))
        {
            $this->rs['code'] = 6;
            $this->rs['msg'] = '用户名或密码不对';
            return return_json($this->rs);
        }
        $model = new Admin();
        //查询用户
        $user_data = $model->where('username',$param['username'])->find();
        if(empty($user_data))
        {
            $this->rs['code'] = 9;
            $this->rs['msg'] = '用户名或密码不对';
            return return_json($this->rs);
        }

        //核实密码
        if(pwd_method($param['password']) != $user_data['password'])
        {
            $this->rs['code'] = 10;
            $this->rs['msg'] = '用户名或密码不对';
            return return_json($this->rs);
        }

        //核实用户状态
        if ($user_data['status'] != 1)
        {
            $this->rs['code'] = 11;
            $this->rs['msg'] = '该账户已被冻结';
            return return_json($this->rs);
        }

        $token = $user_data->getToken();

        $redis = get_redis();
        $session_code = md5(config('sign_md5').$user_data['id']);//token存储key

        $redis->set($session_code,$token,Env::get('login_expiration')*3600*24);

        //登陆记录新增一次
        $user_data->setInc('login_times');
        $save_data = [];
        $save_data['last_login_time'] = time();//最后登录时间
        $save_data['last_ip'] = $this->req->ip();//登录ip

        $user_data->save($save_data);

        $this->rs['data']['token'] = $token;
        $this->rs['data']['user'] = $user_data;
        $this->rs['data']['platform_list'] = array_values(SysServer::getPlatformListByAdminInfo($user_data->toArray()));
        $this->rs['msg'] = '登录成功';

        return return_json($this->rs);
    }
}
