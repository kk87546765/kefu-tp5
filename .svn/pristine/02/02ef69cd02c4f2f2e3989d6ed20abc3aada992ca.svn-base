<?php
namespace app\admin\controller;
use common\server\AdminServer;
use common\server\SysServer;
use think\Db;
use think\Env;

/* *
 *
 * 用户模块（继承用户入口模块）：获取用户信息、获取权限模块、修改密码、获取用户登陆信息、清除缓存、退出登陆
 * */
class User extends Oauth
{
    protected $no_oauth = ['editPassword','index','model','clear','logout','listConfig'];

    /**
     * 获取用户信息
     *
     * @return string
     */
	public function index()
	{

	    $this->rs['code'] = 0;
        $this->rs['msg'] = '获取成功';
        $this->rs['data'] = [
            'login_time'=>date('Y-m-d H:i:s',$this->user_data['last_login_time']),
            'username'=>substr($this->user_data['username'],0,1),
            'nickname'=>$this->user_data['realname'],
            'email'=>'',
            'phone'=>'',
            'count'=>$this->user_data['login_times'],
            'qq'=>'',
            'role_name'=>'',
            'login_ip'=>$this->user_data['last_ip'],
            'header_url'=>'',
            'login_address'=>'',
        ];
        return return_json($this->rs);
	}

    /**
     * 获取权限模块
     *
     * @return string
     */
    public function model()
    {
        $data['menu'] = getArrVal($this->role_data,'menu',[]);
        $data['role'] = getArrVal($this->role_data,'action',[]);
        $platform_list = array_values(SysServer::getPlatformListByAdminInfo($this->user_data));
        foreach ($platform_list as &$item){
            if($item['suffix'] == $this->common_data['def_platform']){
                $item['selected'] = 1;
            }else{
                $item['selected'] = 0;
            }
        }
        $data['platform'] =$platform_list;
        $this->rs['data'] = $data;
        $this->rs['msg'] = '获取成功';
        return return_json($this->rs);
    }

    /**
     * 退出登陆
     *
     * @return string
     */
    public function logout()
    {
        AdminServer::Logout();
        $this->rs['code'] = 0;
        $this->rs['msg'] = '退出成功';
        return return_json($this->rs);
    }

    /**
     * 清空-待定
     * @return string
     */
    public function clear()
    {
        $this->rs['code'] = 0;
        $this->rs['msg'] = '成功';
        return return_json($this->rs);
    }

    #用户-修改密码
    public function editPassword(){

        $param = $this->req->param();

        $msg = is_empty($param,['password','new_pass','confirm_pass']);

        if($msg)
        {
            $this->rs['code'] = 4;
            $this->rs['msg'] = '非法操作';
            return return_json($this->rs);
        }

        //新密码和确认密码是否相同
        if($param['new_pass'] != $param['confirm_pass'])
        {
            $this->rs['code'] = 11;
            $this->rs['msg'] = '新密码和确认密码不一致';
            return return_json($this->rs);
        }

        if(!check_validate(['new_pass'=>'require|length:6,16'],['new_pass'=>$param['new_pass']]) or preg_match("/\s/", $param['new_pass']))
        {
            $this->rs['code'] = 12;
            $this->rs['msg'] = '密码必须为6到16位字符';
            return return_json($this->rs);
        }

        //核实密码
        if(pwd_method($param['password']) != $this->user_data['password'])
        {
            $this->rs['code'] = 9;
            $this->rs['msg'] = '原密码不对';
            return return_json($this->rs);
        }
        if($param['password'] == $param['new_pass'])
        {
            $this->rs['code'] = 17;
            $this->rs['msg'] = '原密码相同';
            return return_json($this->rs);
        }

        $res = $this->user_data->save(['password'=>pwd_method($param['new_pass'])]);

        if(!$res)
        {
            $this->rs['code'] = 2;
            $this->rs['msg'] = '修改失败';
            return return_json($this->rs);
        }

        //退出登陆
        AdminServer::Logout();

        $this->rs['msg'] = '修改成功';

        return return_json($this->rs);
    }
    #用户-列表
    public function list(){

        $page       = $this->req->post('page/d', 1);
        $limit      = $this->req->post('limit/d', 20);

        $p_data['username'] = $this->req->post('username/s','');
        $p_data['realname'] = $this->req->post('realname/s','');
        $p_data['role_id'] = $this->req->post('role_id/d',0);
        $p_data['status'] = $this->req->post('status/d',0);



        list($list,$count) = AdminServer::userList($p_data, $page,$limit);

        $this->rs['data'] = $list;
        $this->rs['count'] = $count;

        return return_json($this->rs);
    }
    #用户-配置
    public function listConfig(){
        $this->rs['data'] = AdminServer::userListConfig();

        return return_json($this->rs);
    }
    #用户-详情
    public function detail(){

        $id = $this->req->post('id/d',0);

        $this->rs['data'] = AdminServer::userDetail($id);

        return return_json($this->rs);
    }
    #用户-添加
    public function add(){

        $p_data = $this->req->post();

        $ret = AdminServer::userSave($p_data);

        $this->rs = array_merge($this->rs,$ret);

        return return_json($this->rs);
    }
    #用户-修改
    public function edit(){

        $p_data = $this->req->post();

        $ret = AdminServer::userSave($p_data);

        $this->rs = array_merge($this->rs,$ret);

        return return_json($this->rs);
    }
}
