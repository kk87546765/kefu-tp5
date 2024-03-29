<?php
namespace app\admin\controller;
use common\server\AdminServer;
use think\Db;
use common\libraries\Common;
/* *
 *
 * 用户模块（继承用户入口模块）：获取用户信息、获取权限模块、修改密码、获取用户登陆信息、清除缓存、退出登陆
 * */
class Admin extends Oauth
{
    protected $no_oauth = [
        'adminLogConfig',
        'adminLogStatisticConfig',
        'runLogListConfig',
        'runLogStatisticConfig'
    ];

    /**
     * 获取用户信息
     *
     * @return string
     */
	public function index()
	{
        $this->rs['code'] = 1;
        $this->rs['msg'] = '获取成功';
        $this->rs['data'] = [
            'login_time'=>date('Y-m-d H:i:s',$this->user_data['login_time']),
            'username'=>substr($this->user_data['username'],0,1),
            'nickname'=>$this->user_data['nickname'],
            'email'=>$this->user_data['email'],
            'phone'=>$this->user_data['phone'],
            'count'=>$this->user_data['count'],
            'qq'=>$this->user_data['qq'],
            'role_name'=>$this->user_data['role_name'],
            'login_ip'=>$this->user_data['login_ip'],
            'header_url'=>empty($this->user_data['header_url']) ? '' : str_replace('\\','/',config('all_common.cdn_url').config('all_common.images_path') . $this->user_data['header_url']),
            'login_address'=>$this->user_data['login_address'],
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
        $this->rs['code'] = 1;
        $this->rs['data'] = $this->role_data;
        $this->rs['msg'] = '获取成功';
        return return_json($this->rs);
    }

    /**
     * 修改密码
     *
     * password 原密码
     * new_pass 新密码
     * confirm_pass 确认密码
     * @return string
     */
    public function edit_password()
    {
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
        if(!password_verify($param['password'] . $this->user_data['salt'],$this->user_data['password']))
        {
            $this->rs['code'] = 9;
            $this->rs['msg'] = '密码不对';
            return return_json($this->rs);
        }
        if($param['password'] == md5($param['new_pass']))
        {
            $this->rs['code'] = 17;
            $this->rs['msg'] = '原密码相同';
            return return_json($this->rs);
        }
        if(!Db::connect(db_master())->name('user')->where(['id'=>$this->user_data['id']])->setField('password',password_hash(md5($param['new_pass']) . $this->user_data['salt'],PASSWORD_BCRYPT)))
        {
            $this->rs['code'] = 2;
            $this->rs['msg'] = '修改失败';
            return return_json($this->rs);
        }

        //退出登陆
        session(null);
        session_destroy();

        $this->rs['code'] = 1;
        $this->rs['msg'] = '修改成功';
        return return_json($this->rs);
    }


    /**
     * 获取用户登陆信息
     * @return string
     */
    public function login_msg()
    {
        $param = $this->req->param();
        //这里需要验证权限
        $where = [];

        if(!in_array('node_user/get_all_user',$this->role_data['role']))
        {
            $where['user_id'] = intval($this->user_data['id']);
        }
        else
        {
            if(isset($param['user_id']) and !empty($param['user_id']))
            {
                $where['user_id'] = intval($param['user_id']);
            }
        }


        //如果没有设置
        if(!isset($param['stime']) or empty($param['stime']) or !check_validate(['stime'=>'date'],['stime'=>$param['stime']]))
        {
            $stime = strtotime(date('Y-m-d',strtotime('-7 day')));
        }
        else
        {
            $stime = strtotime($param['stime']);
            $ctime = strtotime(date('Y-m-d',strtotime('-60 day')));//只允许查询2个月
            if($stime < $ctime)
            {
                $stime = $ctime;
            }
        }
        if(!isset($param['etime']) or empty($param['etime']) or !check_validate(['etime'=>'date'],['etime'=>$param['etime']]))
        {
            $etime = strtotime(date('Y-m-d').' 23:59:59');
        }
        else
        {
            $etime = strtotime($param['etime'].' 23:59:59');
        }

        $data = Db::connect(db_slave())->name('login_log')->where($where)->whereTime('time','between',[$stime,$etime])->order('time desc')->field('user_id,ip,address,time')->select();
        $this->rs['data']['list'] = [];
        $this->rs['data']['count'] = 0;
        $this->rs['data']['cols'] = [[
            ['type'=>'checkbox','fixed'=>'left'],
            ['field'=>'nickname','title'=>'昵称','tips'=>'1.这是测试1。&#13;2.这是测试2。'],
            ['field'=>'address','title'=>'登录地址'],
            ['field'=>'ip','title'=>'登录IP'],
            ['field'=>'time','title'=>'登录时间','sort'=>true],
        ]];
        if(!empty($data))
        {
            $u_data = $this->get_user();
            foreach($data as $key=>$val)
            {
                $this->rs['data']['list'][$key] = [
                    'nickname'=>$u_data[$val['user_id']],
                    'ip'=>$val['ip'],
                    'address'=>$val['address'],
                    'time'=>date('Y-m-d H:i:s',$val['time']),
                ];
            }
            $this->rs['data']['count'] = count($data);
        }
        $this->rs['code'] = 1;
        $this->rs['msg'] = '获取成功';
        return return_json($this->rs);
    }

    /**
     * 上传头像
     *
     * string $file 文件地址和名称，格式：a/b.jpg
     * string $md5 文件的md5验证
     *
     * @return string
     */
    public function upload()
    {
        $param = $this->req->param();
        $msg = is_empty($param,['file','md5']);
        if($msg)
        {
            $this->rs['code'] = 4;
            $this->rs['msg'] = '非法操作';
            return return_json($this->rs);
        }
        $new_path = to_upload($param['file'],$param['md5']);
        if(!$new_path)
        {
            $this->rs['code'] = 13;
            $this->rs['msg'] = '验证非法';
            return return_json($this->rs);
        }

        if(!Db::connect(db_master())->name('user')->where('id',$this->user_data['id'])->update(['header_url'=>$param['file']]))
        {
            @unlink($new_path);
            $this->rs['code'] = 2;
            $this->rs['msg'] = '获取失败';
            return return_json($this->rs);
        }
        $this->rs['data']['src'] = str_replace('\\','/',config('all_common.cdn_url').config('all_common.images_path') . $param['file']);
        $this->sync_user(['id'=>$this->user_data['id']]);
        $this->rs['code'] = 1;
        $this->rs['msg'] = '获取成功';
        return return_json($this->rs);
    }



    /**
     * 清除缓存
     *
     * @return string
     */
    public function clear()
    {
        $redis = get_redis('bloc');
        $cache = config('bloc_common.cache_bloc_model').$this->user_data['role_id'];
        $redis->del(config('bloc_common.cache_bloc_node'));
        $redis->del($cache);
        $this->rs['code'] = 1;
        $this->rs['msg'] = '清除成功';
        return return_json($this->rs);
    }

    /**
     * 退出登陆
     *
     * @return string
     */
    public function logout()
    {
        $this->quit($this->req->param('session_code'),$this->user_data['role_id'].'_'.$this->user_data['id']);
        $this->rs['code'] = 1;
        $this->rs['msg'] = '退出成功';
        return return_json($this->rs);
    }


    /**
     * 监测阿斯加德uid
     *
     * @return mixed
     */
    public function checkId(){

        $ids=  $this->request->post('id');

        if(empty($ids)){
            $this->rs['code'] = -1;
            $this->rs['msg'] = '请输入信息';
            return return_json($this->rs);
        }

        $ids = explode("\n",$ids);

        $res = Common::getAsjdCacheUserInfo($ids);

        $platform_list = Common::getPlatform();

        $new_res = [];
        foreach($res as $k=>$v){
            $v['tkey'] = $platform_list[$v['tkey']]['name'];
            array_push($new_res,$v);
        }

        if(!empty($res)){
            $this->rs['code'] = 0;
            $this->rs['msg'] = '成功';
            $this->rs['data'] = $new_res;
            return return_json($this->rs);
        }else{
            $this->rs['code'] = -1;
            $this->rs['msg'] = '失败！';
            return return_json($this->rs);
        }




    }

    /**
     * 平台账号反查阿斯加德账号
     *
     * @return mixed
     */
    //
    public function checkSDKId(){


        $ids = $this->request->post('id');

        if(empty($ids)){
            $this->rs['code'] = -1;
            $this->rs['msg'] = '请输入信息！';
            return return_json($this->rs);
        }

        $ids = explode("\n",$ids);

        $res = Common::getSDKUserInfo($ids);

        $platform_list = Common::getPlatform();

        $new_res = [];
        foreach($res as $k=>$v){
            $v['tkey'] = $platform_list[$v['tkey']]['name'];
            array_push($new_res,$v);
        }

        if(!empty($res)){
            $this->rs['code'] = 0;
            $this->rs['msg'] = '成功！';
            $this->rs['data'] = $new_res;
            return return_json($this->rs);
        }else{
            $this->rs['code'] = -1;
            $this->rs['msg'] = '失败！';
            return return_json($this->rs);
        }
    }

    #用户操作记录-列表
    public function adminLogList(){

        $p_data = $this->getPost([
            ['page','int',1],
            ['limit','int',20],
            ['admin_id','int',0],
            ['ca','trim',''],
            ['start_time','trim',''],
            ['end_time','trim',''],
        ]);

        list($list,$count) = AdminServer::getAdminLogList($p_data);

        $this->rs['data'] = $list;
        $this->rs['count'] = $count;

        return return_json($this->rs);
    }
    #用户操作记录-列表配置
    public function adminLogConfig(){

        $this->rs['data'] = AdminServer::adminLogConfig();

        return return_json($this->rs);
    }
    #用户操作记录-统计
    public function adminLogStatistic(){

        $p_data = $this->getPost([
            ['admin_id','int',0],
            ['ca','trim',''],
            ['start_time','trim',''],
            ['end_time','trim',''],
            ['controller','trim',''],
            ['action','trim',''],
        ]);

        $this->rs['data'] = AdminServer::getAdminLogStatistic($p_data);

        return return_json($this->rs);
    }
    #用户操作记录-统计配置
    public function adminLogStatisticConfig(){

        $this->rs['data'] = AdminServer::getAdminLogStatisticConfig();

        return return_json($this->rs);
    }

    #脚本记录-列表
    public function runLogList(){

        $p_data = $this->getPost([
            ['page','int',1],
            ['limit','int',20],
            ['controller','trim',''],
            ['action','trim',''],
            ['status','int',0],
            ['start_time','trim',''],
            ['end_time','trim',''],
        ]);

        list($list,$count) = AdminServer::getRunLogList($p_data);

        $this->rs['data'] = $list;
        $this->rs['count'] = $count;

        return return_json($this->rs);
    }

    #用户操作记录-统计配置
    public function runLogListConfig(){

        $this->rs['data'] = AdminServer::getRunLogListConfig();

        return return_json($this->rs);
    }
    #脚本记录-统计
    public function runLogStatistic(){

        $p_data = $this->getPost([
            ['start_time','trim',date('Y-m-d 00:00:00')],
            ['end_time','trim',''],
            ['controller','trim',''],
            ['action','trim',''],
            ['status','int',0],
            ['type','int',1],
        ]);

        $this->rs['data'] = AdminServer::getRunLogStatistic($p_data);

        return return_json($this->rs);
    }

    #用户操作记录-统计配置
    public function runLogStatisticConfig(){

//        $this->rs['data'] = AdminServer::getAdminLogStatisticConfig();

        return return_json($this->rs);
    }

}
