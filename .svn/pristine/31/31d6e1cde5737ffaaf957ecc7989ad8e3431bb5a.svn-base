<?php
namespace common\libraries;


class Dingding
{

    public static $url = 'https://oapi.dingtalk.com/robot/send?access_token=263971d43c8db34cf453d735ace8aba46fc41072e4198a73b5778936606ed0e8';



    //检测大金额用户自动封禁警告
    public static function checkBlockWaringDingding($data)
    {
        $dingding_config = common::getConfig('dingding');


        if(isset($dingding_config[__FUNCTION__]) && $dingding_config[__FUNCTION__]['open'] == 1  && !empty($dingding_config[__FUNCTION__]['mobile'])){

            $msg = "{$dingding_config[__FUNCTION__]['title']}有大额充值用户被自动封禁识别，平台为:".$data['tkey'].',游戏为：'.$data['gkey'].',uid为：'.$data['uid'].',关键词为：'.$data['tmp_keyword'];

            $url = $dingding_config[__FUNCTION__]['url'] ?? self::$url;

            self::sendMsg($url,$msg,$dingding_config[__FUNCTION__]['mobile']);

        }

    }


    //非常登陆设备警告
    public static function notOftenLoginUdidDingding($waring_uids,$platform)
    {
        $dingding_config = common::getConfig('dingding');

        if(isset($dingding_config[__FUNCTION__]) && $dingding_config[__FUNCTION__]['open'] == 1  && !empty($dingding_config[__FUNCTION__]['mobile'])){

            $msg = $dingding_config[__FUNCTION__]['title'];

            foreach($waring_uids as $k3=>$v3){
                $msg .= "平台".$platform." 账号：".$v3 .PHP_EOL;
            }

            $msg .= '有非常用设备登录';

            $url = $dingding_config[__FUNCTION__]['url'] ?? self::$url;

            self::sendMsg($url,$msg,$dingding_config[__FUNCTION__]['mobile']);
        }

    }

    //非常登陆IP警告
    public static function notOftenLoginIpDingding($waring_ips,$platform)
    {
        $dingding_config = common::getConfig('dingding');

        if(isset($dingding_config[__FUNCTION__]) && $dingding_config[__FUNCTION__]['open'] == 1  && !empty($dingding_config[__FUNCTION__]['mobile'])){

            $msg = $dingding_config[__FUNCTION__]['title'];

            foreach($waring_ips as $k4=>$v4){
                $msg .= "平台".$platform." 账号：".$v4 .PHP_EOL;
            }

            $msg .= '有非广州IP登录';

            $url = $dingding_config[__FUNCTION__]['url'] ?? self::$url;

            self::sendMsg($url,$msg,$dingding_config[__FUNCTION__]['mobile']);
        }

    }

    private static function sendMsg($url,$msg,$mobile = [])
    {

       $webhook = !empty($url) ? $url : self::$url;
       $message = $msg;
       $data = array ('at'=>['atMobiles'=>$mobile],'msgtype' => 'text','text' => array ('content' => $message));
       $data_string = json_encode($data);

       $result = self::request_by_curl($webhook, $data_string);

       echo $result;
   }


    private static function request_by_curl($remote_server, $post_string) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $remote_server);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array ('Content-Type: application/json;charset=utf-8'));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        // 线下环境不用开启curl证书验证, 未调通情况可尝试添加该代码
        // curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, 0);
        // curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, 0);
        $data = curl_exec($ch);
        curl_close($ch);
        return $data;
    }
}