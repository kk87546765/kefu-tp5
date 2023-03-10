<?php


namespace app\scripts\controller;


use common\libraries\Common;
use common\server\InnerAccount\InnerAccountServer;

//use common\Models\PlatformPaymentInfo;

class InnerAccount extends Base
{

    protected $func_arr = [
        'deal_inner_account'    =>['func'=>'dealInnerAccount','param'=>[],'delay_time'=>0,'runtime'=>60,'limit'=>0,'is_single'=>1],

    ];


    public function run()
    {
        ini_set('memory_limit', '4096M');
        $params = $this->request->get('p/a',[]);

        $index = $params['action'] ?? '';

        $no_cache = $params['no_cache'] ?? 0;
        if($no_cache){
            if(isset($this->func_arr[$index]['func'])){
                $this->clean($this->func_arr[$index]['func']);
            }
        }

        $this->func_arr[$index]['param'] = isset($params) && !empty($params) ? $params : [];

        $res = $this->apiRunOne($this->func_arr[$index]);//调用$func_arr里面配置方法

        echo json_encode($res);
    }


    public function dealInnerAccount($params)
    {

        $res = InnerAccountServer::dealInnerAccount($params);

        return $res;
    }


    public function clean($name)
    {
        $this->apiClean($name);
    }




}