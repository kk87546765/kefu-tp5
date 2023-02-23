<?php


namespace app\scripts\controller;


use common\libraries\Common;
use common\server\Platform\PaymentServer;

//use common\Models\PlatformPaymentInfo;

class Payment extends Base
{

    protected $func_arr = [
        'get_payment'    =>['func'=>'getPayment','param'=>[],'delay_time'=>0,'runtime'=>86400,'limit'=>0,'is_single'=>1],

    ];


    public function run()
    {

        $params = $this->request->get('p/a',[]);

        $index = $params['action'] ?? '';
        $this->func_arr[$index]['param'] = isset($params) && !empty($params) ? $params : '';

        $res = $this->apiRunOne($this->func_arr[$index]);//调用$func_arr里面配置方法

        echo json_encode($res);
    }


    public function getPayment($params)
    {

        PaymentServer::getPayment($params);
    }



}