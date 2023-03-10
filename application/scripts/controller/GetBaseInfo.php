<?php
namespace app\scripts\controller;

use common\libraries\Logger;
use common\server\GetBaseInfo\GetBaseInfoServer;

use common\libraries\ApiUserInfoSecurity;



/**
 * 用户注册登录支付行为接口类
 *先跑注册接口（user_register）,再跑登录日志接口（user_login）,不然注册表里面的手机号，最后登录时间无法更新到
 * @author tomson
 */
class GetBaseInfo extends Base
{

    protected $func_arr = [
        'register_ll'    =>['func'=>'userRegisterll','param'=>'','delay_time'=>0,'runtime'=>60*2,'limit'=>0,'is_single'=>1],
        'register_xll'   =>['func'=>'userRegisterxll','param'=>'','delay_time'=>0,'runtime'=>60*2,'limit'=>0,'is_single'=>1],
        'register_mh'    =>['func'=>'userRegistermh','param'=>'','delay_time'=>0,'runtime'=>60*2,'limit'=>0,'is_single'=>1],
        'register_youyu' =>['func'=>'userRegisteryouyu','param'=>'','delay_time'=>0,'runtime'=>60*2,'limit'=>0,'is_single'=>1],
        'register_zw'    =>['func'=>'userRegisterzw','param'=>'','delay_time'=>0,'runtime'=>60*2,'limit'=>0,'is_single'=>1],
        'register_bx'    =>['func'=>'userRegisterbx','param'=>'','delay_time'=>0,'runtime'=>60*2,'limit'=>0,'is_single'=>1],

        'login_ll'       =>['func'=>'userLoginll','param'=>'','delay_time'=>0,'runtime'=>60*2,'limit'=>0,'is_single'=>1],
        'login_xll'      =>['func'=>'userLoginxll','param'=>'','delay_time'=>0,'runtime'=>60*2,'limit'=>0,'is_single'=>1],
        'login_mh'       =>['func'=>'userLoginmh','param'=>'','delay_time'=>0,'runtime'=>60*2,'limit'=>0,'is_single'=>1],
        'login_youyu'    =>['func'=>'userLoginyouyu','param'=>'','delay_time'=>0,'runtime'=>60*2,'limit'=>0,'is_single'=>1],
        'login_zw'       =>['func'=>'userLoginzw','param'=>'','delay_time'=>0,'runtime'=>60*2,'limit'=>0,'is_single'=>1],
        'login_bx'       =>['func'=>'userLoginbx','param'=>'','delay_time'=>0,'runtime'=>60*2,'limit'=>0,'is_single'=>1],

        'pay_ll'       =>['func'=>'userPayll','param'=>'','delay_time'=>0,'runtime'=>60*2,'limit'=>0,'is_single'=>1],
        'pay_xll'      =>['func'=>'userPayxll','param'=>'','delay_time'=>0,'runtime'=>60*2,'limit'=>0,'is_single'=>1],
        'pay_mh'       =>['func'=>'userPaymh','param'=>'','delay_time'=>0,'runtime'=>60*2,'limit'=>0,'is_single'=>1],
        'pay_youyu'    =>['func'=>'userPayyouyu','param'=>'','delay_time'=>0,'runtime'=>60*2,'limit'=>0,'is_single'=>1],
        'pay_zw'       =>['func'=>'userPayzw','param'=>'','delay_time'=>0,'runtime'=>60*2,'limit'=>0,'is_single'=>1],
        'pay_bx'       =>['func'=>'userPaybx','param'=>'','delay_time'=>0,'runtime'=>60*2,'limit'=>0,'is_single'=>1],

        'role_ll'       =>['func'=>'userRolell','param'=>'','delay_time'=>0,'runtime'=>60*2,'limit'=>0,'is_single'=>1],
        'role_xll'      =>['func'=>'userRolexll','param'=>'','delay_time'=>0,'runtime'=>60*2,'limit'=>0,'is_single'=>1],
        'role_mh'       =>['func'=>'userRolemh','param'=>'','delay_time'=>0,'runtime'=>60*2,'limit'=>0,'is_single'=>1],
        'role_youyu'    =>['func'=>'userRoleyouyu','param'=>'','delay_time'=>0,'runtime'=>60*2,'limit'=>0,'is_single'=>1],
        'role_zw'       =>['func'=>'userRolezw','param'=>'','delay_time'=>0,'runtime'=>60*2,'limit'=>0,'is_single'=>1],
        'role_bx'       =>['func'=>'userRolebx','param'=>'','delay_time'=>0,'runtime'=>60*2,'limit'=>0,'is_single'=>1],

        'role_login_ll'       =>['func'=>'userRoleLoginll','param'=>'','delay_time'=>0,'runtime'=>60*2,'limit'=>0,'is_single'=>1],
        'role_login_xll'      =>['func'=>'userRoleLoginxll','param'=>'','delay_time'=>0,'runtime'=>60*2,'limit'=>0,'is_single'=>1],
        'role_login_mh'       =>['func'=>'userRoleLoginmh','param'=>'','delay_time'=>0,'runtime'=>60*2,'limit'=>0,'is_single'=>1],
        'role_login_youyu'    =>['func'=>'userRoleLoginyouyu','param'=>'','delay_time'=>0,'runtime'=>60*2,'limit'=>0,'is_single'=>1],
        'role_login_zw'       =>['func'=>'userRoleLoginzw','param'=>'','delay_time'=>0,'runtime'=>60*2,'limit'=>0,'is_single'=>1],
        'role_login_bx'       =>['func'=>'userRoleLoginbx','param'=>'','delay_time'=>0,'runtime'=>60*2,'limit'=>0,'is_single'=>1],

//        'role'        =>['func'=>'userRole','param'=>'','delay_time'=>0,'runtime'=>60*2,'limit'=>0,'is_single'=>1],
        'action'      =>['func'=>'userAction','param'=>'','delay_time'=>0,'runtime'=>86400,'limit'=>0,'is_single'=>1],
        'repair_role' =>['func'=>'repairRole','param'=>'','delay_time'=>1*60*60,'runtime'=>86400,'limit'=>0,'is_single'=>1],
        'udid' =>['func'=>'updateUdid','param'=>'','delay_time'=>1*60*60,'runtime'=>86400,'limit'=>99999,'is_single'=>1],

    ];

    //线上
    protected $interface_url = [];

    public function run()
    {
//        $index = $this->request->get('action');
        $params = $this->request->get('p/a',[]);
        $index = $params['action'] ?? '';

        $no_cache = $params['no_cache'] ?? 0;

        if($no_cache){
            if(isset($this->func_arr[$index]['func'])){
                $this->clean($this->func_arr[$index]['func']);
            }
        }

        $this->func_arr[$index]['param'] = isset($params) && !empty($params) ? $params : '';

        $res = $this->apiRunOne($this->func_arr[$index]);//调用$func_arr里面配置方法
        echo json_encode($res);
    }

    public function userRegisterll($params){
        $params['platform'] = 'll';
        $res = $this->userRegister($params);
        return $res;
    }

    public function userRegisterxll($params){
        $params['platform'] = 'xll';
        $res = $this->userRegister($params);
        return $res;
    }

    public function userRegisterzw($params){
        $params['platform'] = 'zw';
        $res = $this->userRegister($params);
        return $res;
    }

    public function userRegisteryouyu($params){
        $params['platform'] = 'youyu';
        $res = $this->userRegister($params);
        return $res;
    }

    public function userRegisterbx($params){
        $params['platform'] = 'bx';
        $res = $this->userRegister($params);
        return $res;
    }

    public function userRegistermh($params){
        $params['platform'] = 'mh';
        $res = $this->userRegister($params);
        return $res;
    }



    public function userLoginll($params){
        $params['platform'] = 'll';
        $res = $this->userLogin($params);
        return $res;
    }

    public function userLoginxll($params){
        $params['platform'] = 'xll';
        $res = $this->userLogin($params);
        return $res;
    }

    public function userLoginzw($params){
        $params['platform'] = 'zw';
        $res = $this->userLogin($params);
        return $res;
    }

    public function userLoginyouyu($params){
        $params['platform'] = 'youyu';
        $res = $this->userLogin($params);
        return $res;
    }

    public function userLoginbx($params){
        $params['platform'] = 'bx';
        $res = $this->userLogin($params);
        return $res;
    }

    public function userLoginmh($params){
        $params['platform'] = 'mh';
        $res = $this->userLogin($params);
        return $res;
    }




    public function userPayll($params){
        $params['platform'] = 'll';
        $res = $this->userPay($params);
        return $res;
    }

    public function userPayxll($params){
        $params['platform'] = 'xll';
        $res = $this->userPay($params);
        return $res;
    }

    public function userPayzw($params){
        $params['platform'] = 'zw';
        $res = $this->userPay($params);
        return $res;
    }

    public function userPayyouyu($params){
        $params['platform'] = 'youyu';
        $res = $this->userPay($params);
        return $res;
    }

    public function userPaybx($params){
        $params['platform'] = 'bx';
        $res = $this->userPay($params);
        return $res;
    }

    public function userPaymh($params){
        $params['platform'] = 'mh';
        $res = $this->userPay($params);
        return $res;
    }


    public function userRolell($params){
        $params['platform'] = 'll';
        $res = $this->userRole($params);
        return $res;
    }

    public function userRolexll($params){
        $params['platform'] = 'xll';
        $res = $this->userRole($params);
        return $res;
    }

    public function userRolezw($params){
        $params['platform'] = 'zw';
        $res = $this->userRole($params);

        return $res;
    }

    public function userRoleyouyu($params){
        $params['platform'] = 'youyu';
        $res = $this->userRole($params);
        return $res;
    }

    public function userRolebx($params){
        $params['platform'] = 'bx';
        $res = $this->userRole($params);
        return $res;
    }

    public function userRolemh($params){
        $params['platform'] = 'mh';
        $res = $this->userRole($params);
        return $res;
    }

    public function userRoleLoginll($params){
        $params['platform'] = 'll';
        $res = $this->userRole($params);
        return $res;
    }

    public function userRoleLoginxll($params){
        $params['platform'] = 'xll';
        $res = $this->userRole($params);
        return $res;
    }

    public function userRoleLoginzw($params){
        try {
            $params['platform'] = 'zw';
            $res = $this->userRole($params);
        }catch (\Exception $e) {
            Logger::init([
                'path' => RUNTIME_PATH.'admin/'.__FUNCTION__,
                'filename' => date('Y-m-d', time()) ]);
            Logger::write( $e->getMessage());
        }
        
        return $res;
    }

    public function userRoleLoginyouyu($params){
        $params['platform'] = 'youyu';
        $res = $this->userRole($params);
        return $res;
    }

    public function userRoleLoginbx($params){
        $params['platform'] = 'bx';
        $res = $this->userRole($params);
        return $res;
    }

    public function userRoleLoginmh($params){
        $params['platform'] = 'mh';
        $res = $this->userRole($params);
        return $res;
    }


    /**
     * 用户注册日志接口
     * @param array $params
     */
    public function userRegister($params)
    {

        $obj = new GetBaseInfoServer();

        $return = $obj->dealParams($params);

        if($return['code'] != 1){
            return $return;
        }

        $res = $obj->getUserRegister($obj);

        return ['code'=>$res,'data'=>[]];
    }

    /**
     * 用户登录日志接口
     * @param array $params
     */
    public function userLogin(array $params)
    {

        $obj = new GetBaseInfoServer();

        $return = $obj->dealParams($params);

        if($return['code'] != 1){
            return $return;
        }

        $res = $obj->getuserLogin($obj);

        return ['code'=>$res,'data'=>[]];

    }

    /**
     * 用户订单接口
     * @param array $params
     */
    public function userPay(array $params)
    {

        $obj = new GetBaseInfoServer();

        $return = $obj->dealParams($params);

        if($return['code'] != 1){
            return $return;
        }

        $res = $obj->getUserPay($obj);

        return ['code'=>$res,'data'=>[]];

    }



    /**
     * 用户角色信息接口
     * @param array $params
     */
    public function userRole(array $params)
    {

        ini_set('memory_limit', '2048M');

        $obj = new GetBaseInfoServer();

        $return = $obj->dealParams($params);

        if($return['code'] != 1){
            return $return;
        }

        $res = $obj->getUserRole($obj);

        return ['code'=>$res,'data'=>[]];

    }

    /**
     * 行为数据查询接口
     * @param array $params
     */
    public function userAction(array $params)
    {
        ini_set('memory_limit', '2048M');

        $obj = new GetBaseInfoServer();

        $return = $obj->dealParams($params);

        if($return['code'] != 1){
            return $return;
        }

        $res = $obj->userAction($obj);

        return ['code'=>$res,'data'=>[]];
    }




    public function repairRole($params){

        $obj = new GetBaseInfoServer();

        $return = $obj->dealParams($params);

        if($return['code'] != 1){
            return $return;
        }

        $res = $obj->repairRole($obj);

        return ['code'=>$res,'data'=>[]];


    }

    /**
     * 每12小时执行一次，更新角色的最后登录时间
     *
     * @param $interface_name
     */
    //
    public function repairRoleLoginLog($params){

        $obj = new GetBaseInfoServer();

        $return = $obj->dealParams($params);

        if($return['code'] != 1){
            return $return;
        }

        $time = time();
        if($time <= mktime(12,0,0,date('m'),date('d'),date('Y'))){
            $params['start_time'] = mktime(12,0,0,date('m'),date('d')-1,date('Y'));
            $params['end_time'] = mktime(0,0,0,date('m'),date('d'),date('Y'));
        } else{
            $params['start_time'] = mktime(0,0,0,date('m'),date('d'),date('Y'));
            $params['end_time'] = mktime(12,0,0,date('m'),date('d'),date('Y'));
        }
        $res = $this->userRole($params);

        return ['code'=>$res,'data'=>[]];

    }



    public function clean($name)
    {
        $this->apiClean($name);
    }



    public function updateUdid($params)
    {
        ini_set('memory_limit', '2048M');
        set_time_limit(200);
        $obj = new GetBaseInfoServer();
        $return = $obj->dealParams($params);

        if($return['code'] != 1){
            return $return;
        }



        $obj->updateUdid($obj);

    }







}
