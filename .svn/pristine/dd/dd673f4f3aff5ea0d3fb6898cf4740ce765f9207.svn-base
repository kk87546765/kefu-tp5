<?php


namespace common\server\Game;


use common\base\BasicServer;
use common\libraries\{Logger,Curl,Common};


class RoleServer extends BasicServer
{

    const GAME_SIGN_PATH = EXTEND_PATH."/GamekeySign";
    const GAME_CONFIG_KEY = 'gamekey';

    /**
     * @param array $data
     * @return bool|string
     */
    public static function roleLoginOut($data = [])
    {
        Logger::init([
            'path' => RUNTIME_PATH.'admin/'.__FUNCTION__,
            'filename' => date('Y-m-d', time()) ]);
        $result = -1;
        if (empty($data)) return $result;
        $gameConfigList = Common::getGameKey();
//        $gameConfigList= Common::getConfig(self::GAME_CONFIG_KEY);
        $sign = self::createSign($data);
        $loginOutUrl = $gameConfigList[$data['gkey']]['loginout_url'];


        $param              = [
            'gkey'=>$data['gkey'],
            'tkey'=>$data['tkey'],
            'uid'=> $data['uid'], //如果存在聚合的openid，则使用openid，因为cp只有聚合的openid
            'sid'=>$data['sid'],
            'uname'=>$data['rolename'],
            'roleid'=>$data['roleid'],
            'ext'   => $data['ext'],
            'type'=>1,
            'time'=>$data['addtime'],
            'sign'=> $sign ];

        $curl   = new Curl();
        if(isset($data['action_request_type']) && $data['action_request_type'] == 'post'){
            $url = $loginOutUrl;
            $res = $curl->post($url,$param);

        }else{

            if(strpos($loginOutUrl,'?')){
                $needle = '&';
            }else{
                $needle = '?';
            }
            $url = $loginOutUrl.$needle.http_build_query($param);
            $res = $curl->get($url);
        }


        Logger::write([
            'tag' => 'postRes',
            'url' => $url,
            'msg' => $res,
        ]);
        if (isset(json_decode($res,1)['state']['code']) && json_decode($res,1)['state']['code']== 1 ||$res == 1) $result = 1;
        return $result;
    }

    /**
     * @param array $data
     * @return bool|string
     */
    public static function roleChat($data = [],$deal_type = 1)
    {
        Logger::init([
            'path' => RUNTIME_PATH.'admin/'.__FUNCTION__,
            'filename' => date('Y-m-d', time()) ]);
        $result = -1;

        $gkey = $data['gkey'] ?? '';

        if (empty($data)) return $result;


        //走cp统一封禁接口
        if(isset($data['need_cp_deal']) && $data['need_cp_deal'] == 1){
            $result = self::gameRoleChat($data,$gkey,$deal_type);
            return $result;
        }


        $gameConfigList = Common::getGameKey();

//        $gameConfigList= Common::getConfig(self::GAME_CONFIG_KEY);
        $sign = self::createSign($data);

        $chatUrl = $gameConfigList[$gkey]['chat_url'] ?? '';

        $param              = [
            'gkey'  => $gkey,
            'tkey'  => $data['tkey'] ?? '', //暂时不清楚这个值的定义
            'uid'   => $data['uid'] ?? '',
            'sid'   => $data['sid'] ?? '',
            'uname' => $data['rolename'] ?? '',
            'roleid'=> $data['roleid'] ?? '',
            'ext'   => $data['ext'] ?? '',
            'type'  => $deal_type,
            'time'  => $data['addtime'] ?? '',
            'ban_time'=> $data['ban_time'] ?? '',
            'sign'  => $sign
        ];

        $curl   = new Curl();
        if(isset($data['action_request_type']) && $data['action_request_type'] == 'post'){
            $url = $chatUrl;
            $res = $curl->post($url,$param);

        }else{
            if(strpos($chatUrl,'?')){
                $needle = '&';
            }else{
                $needle = '?';
            }
            $url = $chatUrl.$needle.http_build_query($param);

            $res = $curl->get($url);

        }


        Logger::write([
            'tag' => 'postRes',
            'url' => $url,
            'data'=>json_encode($param),
            'msg' => $res,
        ]);

        if (isset(json_decode($res,1)['state']['code']) && json_decode($res,1)['state']['code']== 1 ||$res == 1) $result = 1;
        return $result;
    }


    /**
     * @param array $data
     * @param int  $deal_type 1:封禁，2:解除封禁
     * @return bool|string
     */
    public static function roleBlock($data = [],$deal_type = 1)
    {
        Logger::init([
            'path' => RUNTIME_PATH.'admin/'.__FUNCTION__,
            'filename' => date('Y-m-d', time()) ]);

        $result = -1;
        $gkey = $data['gkey'] ?? '';

        if (empty($data)) return $result;


        //走cp统一封禁接口
        if(isset($data['need_cp_deal']) && $data['need_cp_deal'] == 1){

            $result = self::gameRoleBlock($data,$gkey,$deal_type);
            return $result;
        }


        $gameConfigList = Common::getGameKey();
//        $gameConfigList= Common::getConfig(self::GAME_CONFIG_KEY);

        //是否支持批量角色封禁
        if( $gameConfigList[$data['gkey']]['batch_block'] == 1){
            $sign = self::createBatchRoleBlockSign($data);
            $blockUrl = $gameConfigList[$data['gkey']]['role_block_url'];
        }else{
            $sign = self::createSign($data);
            $blockUrl = $gameConfigList[$data['gkey']]['block_url'];
        }

        $param              = [
            'gkey'=>$data['gkey'],
            'tkey'=>$data['tkey'],
            'uid'=> $data['uid'],//如果存在聚合的openid，则使用openid，因为cp只有聚合的openid
            'sid'=>$data['sid'],
            'uname'=>$data['rolename'],
            'roleid'=>$data['roleid'],
            'type'=>$deal_type, //判断是否封禁 1封禁 2解禁
            'time'=>$data['addtime'],
            'ban_time'=>$data['ban_time'],
            'ext'   => $data['ext'],
            'sign'=> $sign
        ];

        $curl   = new Curl();

        if(isset($data['action_request_type']) && $data['action_request_type'] == 'post'){
            $url = $blockUrl;
            $res = $curl->post($url,$param);

        }else{
            if(strpos($blockUrl,'?')){
                $needle = '&';
            }else{
                $needle = '?';
            }

            $url = $blockUrl.$needle.http_build_query($param);
            $res = $curl->get($url);

        }


        Logger::write([
            'tag' => 'postRes',
            'url' => $url,
            'data'=>json_encode($param),
            'msg' => $res,
        ]);
        if (isset(json_decode($res,1)['state']['code']) && json_decode($res,1)['state']['code']== 1 ||$res == 1) $result = 1;
        return $result;
    }


    /**
     * @param array $data
     * @return bool|string
     */
    public static function BatchRoleBlock($data = [],$deal_type = 1)
    {
        Logger::init([
            'path' => RUNTIME_PATH.'admin/'.__FUNCTION__,
            'filename' => date('Y-m-d', time()) ]);
        $result = -1;


        if (empty($data)) return $result;

        //走cp统一封禁接口
        if(isset($data['need_cp_deal']) && $data['need_cp_deal'] == 1){

            $result = self::gameRoleBlock($data,$data['gkey'],$deal_type);
            return $result;
        }

        $gameConfigList = Common::getGameKey();
//        $gameConfigList= Common::getConfig(self::GAME_CONFIG_KEY);
        $sign = self::createBatchRoleBlockSign($data);
        $blockUrl = $gameConfigList[$data['gkey']]['role_block_url'];

        if(empty($blockUrl)){
            return -2;
        }


        $param              = [
            'gkey'=>$data['gkey'],
            'sid'=>$data['sid'],
            'roleid'=>$data['roleid'],
            'type'=>$deal_type, //判断是否封禁 1封禁 2解禁
            'time'=>$data['addtime'],
            'ban_time'=>$data['ban_time'],
            'sign'=> $sign
        ];

        $curl   = new Curl();

        if(isset($data['action_request_type']) && $data['action_request_type'] == 'post'){
            $url = $blockUrl;
            $res = $curl->post($url,$param);

        }else {
            if (strpos($blockUrl, '?')) {
                $needle = '&';
            } else {
                $needle = '?';
            }
            $url = $blockUrl . $needle . http_build_query($param);
            $res = $curl->get($url);
        }



        Logger::write([
            'tag' => 'postRes',
            'url' => $url,
            'data'=>json_encode($param),
            'msg' => $res,
        ]);
        if (isset(json_decode($res,1)['state']['code']) && json_decode($res,1)['state']['code']== 1 ||$res == 1) $result = 1;
        return $result;
    }


    /**
     * @param array $data
     * @return string
     */
    public static function createSign($data=[])
    {
        $gameConfigList = Common::getGameKey();
//        $gameConfigList= Common::getConfig(self::GAME_CONFIG_KEY);
        $gkey = $data['gkey'] ?? '';
        $sid = $data['sid'] ?? '';
        $uid = $data['uid'] ?? '';
        $addtime = $data['addtime'] ?? '';
        $key = $gameConfigList[$gkey]['key'] ?? '';
        $signStr = $gkey.$sid.$uid.$addtime.$key;
        $sign = md5($signStr);
        return $sign;
    }

    /**
     * @param array $data
     * @return string
     */
    public static function createBatchRoleBlockSign($data=[])
    {
        $gameConfigList = Common::getGameKey();
//        $gameConfigList= Common::getConfig(self::GAME_CONFIG_KEY);
        $signStr = $data['gkey'].$data['sid'].$data['roleid'].$data['addtime'].$gameConfigList[$data['gkey']]['key'];
        $sign = md5($signStr);
        return $sign;
    }




    //对接cp的禁言
    public static function gameRoleChat($chat_info,$gkey,$deal_type)
    {
        $res = false;
        $obj = '';
        if(isset($gkey) && file_exists(self::GAME_SIGN_PATH."/{$gkey}.php")){
            include_once( self::GAME_SIGN_PATH."/{$gkey}.php");

            if(class_exists($gkey)) {

                $obj = new $gkey;
            }
        }


        if(method_exists($obj,'roleChat') && in_array('roleChat',get_class_methods($obj)) ){

            $res = $obj->roleChat($chat_info,$deal_type);

        }

        return $res;
    }

    //对接cp的封禁
    public static function gameRoleBlock($chat_info,$gkey,$deal_type)
    {
        $res = false;
        $obj = '';
        if(isset($gkey) && file_exists(self::GAME_SIGN_PATH."/{$gkey}.php")){
            include_once( self::GAME_SIGN_PATH."/{$gkey}.php");

            if(class_exists($gkey)) {

                $obj = new $gkey;
            }
        }

        if(method_exists($obj,'roleBlock') && in_array('roleBlock',get_class_methods($obj)) ){

            $res = $obj->roleBlock($chat_info,$deal_type);

        }

        return $res;
    }

}