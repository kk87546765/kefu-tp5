<?php
namespace app\scripts\controller;

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
        'register'    =>['func'=>'userRegister','param'=>'','delay_time'=>0,'runtime'=>60*2,'limit'=>0,'is_single'=>1],
        'login'       =>['func'=>'userLogin','param'=>'','delay_time'=>0,'runtime'=>60*2,'limit'=>0,'is_single'=>1],
        'role'        =>['func'=>'userRole','param'=>'','delay_time'=>0,'runtime'=>60*2,'limit'=>0,'is_single'=>1],
        'action'      =>['func'=>'userAction','param'=>'','delay_time'=>0,'runtime'=>86400,'limit'=>0,'is_single'=>1],
        'repair_role' =>['func'=>'repairRole','param'=>'','delay_time'=>1*60*60,'runtime'=>86400,'limit'=>0,'is_single'=>1],

    ];

    //线上
    protected $interface_url = [];

    public function run()
    {
//        $index = $this->request->get('action');
        $params = $this->request->get('p/a',[]);
        $index = $params['action'] ?: '';


        $this->func_arr[$index]['param'] = isset($params) && !empty($params) ? $params : '';

        $res = $this->apiRunOne($this->func_arr[$index]);//调用$func_arr里面配置方法
        echo json_encode($res);
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

        if($return['code'] != 0){
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










}
