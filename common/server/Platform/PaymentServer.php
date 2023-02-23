<?php
/**
 * Created by PhpStorm.
 * User: crosstime
 * Date: 2017/10/12
 * Time: 上午11:57
 */
namespace common\server\Platform;


use common\base\BasicServer;
use common\Libraries\Common;
use common\libraries\Curl;
use think\Config;
use common\sql_server\PaymentSqlServer;

class PaymentServer extends BasicServer
{

    public static function getPayment($params)
    {

        //获取平台列表
        if(isset($params['platform']) && !empty($params['platform'])){
            $platformList[0] = common::getPlatformInfoBySuffixAndCache($params['platform']);
        }else{
            $platformList = common::getPlatformList();
        }

        $time = time();
        foreach ($platformList as $k=>$v) {

            if(empty($v['config']['get_payment'])){
                continue;
            }

            $key = $v['config']['url_key'];
            $sign = md5("time={$time}key={$key}");
            $url = $v['config']['get_payment'];

            if (empty($key) || empty($url)) {
                continue;
            }
            $data = [
                'time' => $time,
                'sign' => $sign
            ];

            $return_res = self::getData($url,$data);
            $return_res['platform_id'] = $v['platform_id'];
            self::insertPaymentData($return_res,$v['platform_id']);

        }
    }


    public static function insertPaymentData($data,$platform_id){
        if(empty($data) || empty($platform_id)){
            return false;
        }
        $res = false;

        foreach($data as $k=>$v){

            if(empty($v['id'])  || empty($v['name'])){

                continue;
            }
            $new_data = [
                'payment_id' => $v['id'],
                'payment_name' => $v['name'],
                'platform_id' => $platform_id,
                'add_time' => time(),
            ];
            if(PaymentSqlServer::getList(['payment_id'=>$v['id'],'platform_id'=>$platform_id])){
                continue;

            }else{
                $res = PaymentSqlServer::add($new_data);
            }
        }
        return $res;
    }


    /**
     * @param $url
     * @param array $opt
     * @return array|mixed
     */
    public static function getData($url,$opt=[])
    {
        $res = Curl::post($url,$opt);
        $data = json_decode($res,1);


        if($data['state']['code']==1){
            return $data['data'];
        } else {
            //需要记录错误信息
            return $data['state']['msg'];
        }

        return false;
    }




}