<?php
namespace app\scripts\controller;


use common\Libraries\{Common,CUtf8_PY,GameApi,Search,Suggest};

use common\server\ActionBlock\ActionBlockServer;
use common\server\Game\BlockServer;
use common\server\keyword\KeywordServer;
use common\server\Platform\BanServer;
use common\server\Scripts\ScriptsServer;
use common\server\Sdk\UserLogics as sdkUserLogics;
use common\Models\ActionBlock;
use ccripts\Controllers\ControllerBase;
use common\Libraries\Curl;

use common\Libraries\Logger;
use common\Models\BanUserLog;
use common\Models\Block;
use common\Models\Keyword;
use common\Models\IpKeyword;
use common\Models\KeywordLog;
use common\Models\GetBaseInfo as GetBaseInfoModel;
use common\Models\Log;
use common\Models\KefuCommonMember;
use common\Models\KefuPayOrder;
use common\Libraries\Unicode;

/**
 * 用户注册登录支付行为接口类
 *先跑注册接口（user_register）,再跑登录日志接口（user_login）,不然注册表里面的手机号，最后登录时间无法更新到
 * @author tomson
 */
class Scripts extends Base
{

    protected $last_check_keyword_time = RUNTIME_PATH.'last_check_keyword_time.txt';


    public function test(){
        $time = time();
        $start_time = $start_time??$time-60;
        $end_time = $time;

        //超过半小时延迟，放弃之前的时段
        if($time - $start_time > 1800){
            $start_time = $time-60;
            $end_time = $time;
        }
        $ScriptsLogics = new ScriptsLogics();
        $chat_info = $ScriptsLogics->getElasticSearchSuggestInfo($start_time,$end_time);
        echo "<pre>";
        var_dumP($chat_info);exit;
    }

    //处理聊天信息功能
    public function run(){

        $ScriptsServer = new ScriptsServer();
        $start_time = file_exists($this->last_check_keyword_time) ? file_get_contents($this->last_check_keyword_time) : time();
        $time = time();
        $start_time = $start_time??$time-60;
        $end_time = $time;

        //超过半小时延迟，放弃之前的时段
        if($time - $start_time > 1800){
            $start_time = $time-60;
            $end_time = $time;
        }
        //获取时间段内的聊天信息
//        $chat_info = $ScriptsLogics->getOpenSearchSuggestInfo($start_time,$end_time);

        //使用elasticsearch获取聊天信息
//        $chat_info = $ScriptsServer->getElasticSearchSuggestInfo($start_time,$end_time);


        $chat_info = '{
    "id": "2108252232397298",
    "gkey": "shenqiios",
    "tkey": "zw",
    "sid": "S515",
    "uid": "17302133",
    "uname": "海宁",
    "roleid": "1629269285000862700",
    "type": "6",
    "content": "哈",
    "content2": "**你也来",
    "time": "1629901955",
    "ip": "112.17.247.250",
    "ip_id": 0,
    "to_uid": "",
    "to_uname": "",
    "role_level": "151",
    "imei": "",
    "count_money": 0,
    "reg_channel_id": "145222",
    "ext": "",
    "openid": "",
    "request_time": 1629901959
  }';

        $chat_info2 = '{
    "id": "2108252232397298",
    "gkey": "shenqiios",
    "tkey": "zw",
    "sid": "S515",
    "uid": "17302133",
    "uname": "海宁",
    "roleid": "1629269285000862700",
    "type": "6",
    "content": "哈",
    "content2": "**你也来",
    "time": "1629901955",
    "ip": "112.17.247.250",
    "ip_id": 0,
    "to_uid": "",
    "to_uname": "",
    "role_level": "151",
    "imei": "",
    "count_money": 0,
    "reg_channel_id": "145222",
    "ext": "",
    "openid": "",
    "request_time": 1629901959
  }';

        $empty[0] = json_decode($chat_info,1);
        $empty[1] = json_decode($chat_info2,1);
        $chat_info = $empty;


        //使用火山引擎处理聊天信息(暂停)
//        $ScriptsLogics->checkChatTest($chat_info);

        //对内容进行过滤，包括去除白名单、去除特殊字符、转换中文为数字
        $chat_info = $ScriptsServer->filterContent($chat_info);

        //重置开始时间
        file_put_contents($this->last_check_keyword_time,$end_time);

        //处理关键词
//        $ScriptsServer->dealChatKeyword($chat_info);

        //处理上下文关键词
        $ScriptsServer->dealMergeChatKeyword($chat_info);


    }


    //行为封禁脚本
    public function dealActionBlock(){
        $start_time = time()-1200;
        $ScriptsServer = new ScriptsServer();
        $end_time = time();

        $info = $ScriptsServer->getElasticSearchSuggestInfo($start_time,$end_time);
        $redis = get_redis();
//        //剔除已操作的信息
        foreach($info as $k10=>$v10){
            if($redis->get('action_block_1_'.$v10['uid']) || $redis->get('action_block_2_'.$v10['ip']) || $redis->get('action_block_2_'.$v10['imei'])){
                unset($info[$k10]);
            }
        }

        //对内容进行过滤，包括去除白名单、去除特殊字符、转换中文为数字
        $info = $ScriptsServer->filterContent($info);


        //整理聊天信息
        $new_arr = [];
        foreach($info as $k=>$v){
            $new_arr[$v['tkey'].'_'.$v['gkey'].'_'.$v['uid']][] = $v;
        }

        $new_arr1 = [];

        if(empty($new_arr)){
            echo 'nothing to deal';exit;
        }

        //对合并的数据进行排序
        foreach($new_arr as $k1=>$v1){
            $new_arr1[$k1] =  Common::arraySort($new_arr[$k1],'time',SORT_ASC);
        }

        //获取行为列表
        $actions = ActionBlockServer::getOneByWhere(['status'=>1]);

        $limit_time = [];
        //获取需要计算的时间
        foreach($actions as $k=>$v){
            $limit_time[$v['limit_time']] = $v['limit_time'];
        }
        $info_time_arr = [];


        //按照时间区间[用户uid][聊天信息]重组数组
        foreach($limit_time as $k3=>$v3){
            //根据规则处理信息
            foreach($new_arr1 as $k2=>$v2){

                foreach($v2 as $k4=>$v4){

                    //按用户区分，将用户按规则的时间重组
                    if($v4['time'] <= $new_arr1[$k2][0]['time'] + ($v3*60)){

                        $info_time_arr[$v3][$v4['uid']][] = $v4;
                    }
                }

            }

        }

        //对时间区间内的聊天信息进行规则匹配
        foreach($actions as $k5=>$v5){
            foreach($info_time_arr[$v5['limit_time']] as $k6=>$v6){


                //剔除已经处理过的信息  action_block_1_uid、action_block_2_ip、action_block_3_imei
                if($redis->get('action_block_1_'.$v6[0]['uid']) || $redis->get('action_block_2_'.$v6[0]['ip']) || $redis->get('action_block_2_'.$v6[0]['imei'])){
                    continue;
                }

                //处理是否达到私聊人数
                if($v5['check_type'] == 1){
                    $ScriptsServer->dealPrivateChat($v6,$v5['limit_time'],$v5);
                }

//                //处理是否重复信息
                if($v5['check_type'] == 2){
                    $ScriptsServer->dealRepeatMsg($v6,$v5['limit_time'],$v5);
                }

                //处理是否多位数字达到多次
                if($v5['check_type'] == 3){
                    $ScriptsServer->dealFigureNum($v6,$v5['limit_time'],$v5);
                }

                //处理累计字符是否超过长度
                if($v5['check_type'] == 4){
                    $ScriptsServer->dealLimitCharLength($v6,$v5['limit_time'],$v5);
                }
            }

        }

    }


    /**
     * 模拟post提交函数
     * @param $post_url
     * @param $post_arr
     * @param int $timeOut
     * @param bool $cookie
     * @param bool $header
     * @return array
     */
    protected function post_curl($post_url, $post_arr, $timeOut = 0, $cookie = false, $header = false)
    {
        $timeOut = $timeOut ?: VALUE_TIMEOUT;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $post_url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_arr);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        if (strpos($post_url, 'https') !== false) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        }
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeOut);
        $header && curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        $cookie && curl_setopt($ch, CURLOPT_COOKIE, $cookie);
        $response = curl_exec($ch);    //这个是读到的值
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        unset($ch);
        return array($httpCode, $response);
    }



    public function checkChatTest(){

    }

}
