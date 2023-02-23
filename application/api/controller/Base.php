<?php
namespace app\api\controller;
use common\base\BasicController;
use common\libraries\ApiVerification;
use common\server\SysServer;

class Base extends BasicController
{
    protected $base_config = [];

    public function _initialize()
    {
        parent::_initialize();

        $this->base_config = SysServer::getAllConfigByCache();
    }

    /**
     * 校验ip
     * @return bool
     */
    protected function checkIp(){
        $api_ip_write_open = getArrVal($this->base_config,'api_ip_write_open',0);
        $api_ip_black_open = getArrVal($this->base_config,'api_ip_black_open',0);

        $ip = $this->request->ip();

        if($api_ip_write_open){
            $api_ip_write = getArrVal($this->base_config,'api_ip_write',[]);

            if(!in_array($ip,$api_ip_write)){
                $this->rs['code'] = 2001;
                $this->rs['msg'] = $this->error_code[2001];
                return_json($this->rs,false);
            }
        }

        if($api_ip_black_open){
            $api_ip_black = getArrVal($this->base_config,'api_ip_black',[]);

            if(in_array($ip,$api_ip_black)){
                $this->rs['code'] = 2002;
                $this->rs['msg'] = $this->error_code[2002];
                return_json($this->rs,false);
            }
        }
        return true;
    }

    /**
     * 验签
     * @param $param
     * @return array|bool
     */
    protected function checkSing($param){
        $config = [
            'key'=>config('api_key'),
            'time'=>$this->time,
        ];
        $server = new ApiVerification($config);

        $res = $server->checkSign($param);

        if($res['code'] != ApiVerification::API_CODE_SUCCESS){
            $this->rs = array_merge($this->rs,$res);
            return return_json($this->rs,false);
        }
        return true;
    }

    /**
     * 校验必须参数
     * @param $param
     * @param $verify_key
     * @return array|bool
     */
    protected function checkParam($param,$verify_key){

        $res = ApiVerification::checkParam($param,$verify_key);

        if($res['code'] != ApiVerification::API_CODE_SUCCESS){
            $this->rs = array_merge($this->rs,$res);
            return return_json($this->rs,false);
        }
        return true;

    }
}
