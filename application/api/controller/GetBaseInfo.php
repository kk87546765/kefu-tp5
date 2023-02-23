<?php
namespace app\api\controller;


use common\libraries\ApiVerification;
use common\server\GetBaseInfo\GetBaseInfoServer;
use common\server\Statistic\UserPrivacyServer;

class GetBaseInfo extends Base
{
    public function _initialize(){
        parent::_initialize();

        $this->checkIp();
    }

    public function userRegister(){

        set_time_limit(0);

        $params = $this->request->get();

        $obj = new GetBaseInfoServer();

        $return = $obj->dealParams($params);

        if($return['code'] != 1){
            $this->rs = array_merge($this->rs,$return);
            return return_json($this->rs);
        }

        $res = $obj->getUserRegister($obj);

        $this->rs['code'] = $res;
        return return_json($this->rs);
    }

    /**
     * 用户登录日志接口
     */
    public function userLogin()
    {

        set_time_limit(0);

        $params = $this->request->get();

        $obj = new GetBaseInfoServer();

        $return = $obj->dealParams($params);

        if($return['code'] != 1){
            $this->rs = array_merge($this->rs,$return);
            return return_json($this->rs);
        }

        $res = $obj->getuserLogin($obj);

        $this->rs['code'] = $res;
        return return_json($this->rs);
    }

    /**
     * 用户订单接口
     */
    public function userPay()
    {
        set_time_limit(0);

        $params = $this->request->get();

        $obj = new GetBaseInfoServer();

        $return = $obj->dealParams($params);

        if($return['code'] != 1){
            $this->rs = array_merge($this->rs,$return);
            return return_json($this->rs);
        }

        $res = $obj->getUserPay($obj);

        $this->rs['code'] = $res;
        return return_json($this->rs);

    }

    /**
     * 用户角色信息接口
     */
    public function userRole()
    {

        ini_set('memory_limit', '2048M');

        set_time_limit(0);

        $params = $this->request->get();

        $obj = new GetBaseInfoServer();

        $return = $obj->dealParams($params);

        if($return['code'] != 1){
            $this->rs = array_merge($this->rs,$return);
            return return_json($this->rs);
        }

        $res = $obj->getUserRole($obj);

        $this->rs['code'] = $res;
        return return_json($this->rs);

    }


}
