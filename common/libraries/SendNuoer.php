<?php
namespace common\libraries;

use common\libraries\Logger;




class SendNuoer
{

    protected static $send_plan_url = 'http://api.nuoer.vip/v1/recall/task/commit';
    protected static $send_plan_people_url = 'http://api.nuoer.vip/v1/recall/taskDetail/commit';
    private static  $key = 'dOW3Kjc2rGWsB1vkSKszuckEAwXuCTwR';
    private static $aes_key = 'PNlPzm28enDSuk8a';



    public static function sendPhone($data)
    {
        Logger::init([
            'path' => RUNTIME_PATH.'admin/'.__FUNCTION__,
            'filename' => date('Y-m-d', time()) ]);

        if(empty($data)){
            return false;
        }

        $res = false;
        $time = time();
        $post_data['plan_log_id'] = $data[0]['plan_log_id'];
        $arr_phone = array_column($data,'phone');

        $post_data['phone'] = self::encrypt(json_encode($arr_phone));

        $post_data['time'] = $time;
        $post_data['sign'] = md5($post_data['plan_log_id'].$post_data['time'].self::$key);

        $json_post_data = json_encode($post_data);

        $result = self::request_by_curl(self::$send_plan_people_url, $json_post_data );

        $result = json_decode($result,1);

        Logger::write([
            'tag' => 'postRes',
            'url' => self::$send_plan_people_url,
            'msg' =>  $json_post_data.'||'.json_encode($result),
        ]);

        if($result['state']['code'] == 1){
            $res = true;
        }
        return $res;
   }

   public static function sendPlan($data)
   {

       Logger::init([
           'path' => RUNTIME_PATH.'admin/'.__FUNCTION__,
           'filename' => date('Y-m-d', time()) ]);

       if(empty($data)){
           return false;
       }

       $res = false;
       $post_data = $data;
       $time = time();

       $post_data['time'] = $time;
       $post_data['sign'] = md5($data['platform'].$post_data['time'].self::$key);

       $json_post_data = json_encode($post_data);

       $result = self::request_by_curl(self::$send_plan_url, $json_post_data);

       $result = json_decode($result,1);

       Logger::write([
           'tag' => 'postRes',
           'url' => self::$send_plan_url,
           'msg' => $json_post_data.'||'.json_encode($result),
       ]);

       if($result['state']['code'] == 1){
           $res = true;
       }
       return $res;
   }


   private static function decrypt($content)
   {
       $decrypt = openssl_decrypt($content, 'AES-128-ECB', self::$aes_key, 0);
       return $decrypt;
   }

    private static function encrypt($content)
    {
        // 加密数据 'AES-128-ECB' 可以通过openssl_get_cipher_methods()获取
        $encrypt = openssl_encrypt($content, 'AES-128-ECB', self::$aes_key, 0);
        return $encrypt;

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