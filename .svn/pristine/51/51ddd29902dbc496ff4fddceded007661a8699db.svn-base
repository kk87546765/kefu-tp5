<?php
/**
 * Created by PhpStorm.
 * User: crosstime
 * Date: 2017/10/12
 * Time: 上午11:57
 */
namespace common\server\Sms;


use common\base\BasicServer;

use common\libraries\Common;
use common\sql_server\PlatformList;
use extend\ApiSms;
use think\Config;
use common\sql_server\SmsSqlServer;

class SmsServer extends BasicServer
{

    public static function getList()
    {

    }

    public static function send($data)
    {

        $return = ['code'=>-1 ,'msg'=>'发送失败','data'=>[]];
//        $chunk_result = array_chunk($data['phone'],200);

        $list = Common::getConfig('sms');
        $content = $list[$data['sms_type']]['template'][$data['template_id']]['content'] ?? '';
        preg_match_all("/\{[0-9]\}+/",$content,$match);


        if(count($match[0]) != count($data['params'])){
            $return['msg'] = '变量数与模板不符合';
            return $return;
        }

        $objSms = ApiSms::init($data['sms_type']);

        $success = [];
        $fail = [];
        $time = time();

        foreach($data['phone'] as $k=>$v){

            $rs = $objSms::sendSms([$v], $data['params'],$data['template_id'],$data['sign']);
//            $rs = false;
            if($rs){
                $success[] = $v;
            }else{
                $fail[] = $v;
            }

            $add_data = [];
            $add_data['phone'] = $v;
            $add_data['add_time'] = $time;
            $add_data['platform'] = self::$common_data['def_platform'];
            $add_data['sms_type'] = $data['sms_type'];
            $add_data['template_id'] = $data['template_id'];
            $add_data['admin_id'] = self::$user_data['id'];
            $add_data['admin'] = self::$user_data['username'];
            $add_data['content'] = self::$user_data['username'];
            $add_data['res'] = $rs == true ? 1 : 0;

            SmsSqlServer::add($add_data);
        }
        $return['code'] = 0;
        $return['msg'] = '失败的有：'.implode(',',$fail).','.'成功的有：'.implode(',',$success);
        return $return;
    }


    public static function getSmsType()
    {
        $return = ['code' => 1, 'msg'=>'获取失败','data'=>[]];
        $sms = Common::getConfig('sms');

        $platform = self::$common_data['def_platform'];
        $platform_info = PlatformList::getPlatformList(['platform_suffix'=>$platform]);

//        $platform_info = Common::getPlatformInfoBySuffixAndCache($platform);
        if(!isset($platform_info[0]['config']['sms'])) return return_json($return);

        $platform_sms_info = $sms[$platform_info[0]['config']['sms']];


        if($platform_sms_info){
            $return['code'] = 0;
            $return['msg'] = '获取成功';
            $return['data'] = [$platform_sms_info];
        }

        return $return;

    }



}