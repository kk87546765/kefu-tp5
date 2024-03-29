<?php


namespace common\server\Game;


use common\base\BasicServer;
use common\libraries\{Logger,Curl,Common};


class RoleServer extends BasicServer
{


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
        $gameConfigList= Common::getConfig(self::GAME_CONFIG_KEY);
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
        Logger::write([
            'tag' => 'actionDataJson',
            'msg' => json_encode($param)
        ]);
        if(strpos($loginOutUrl,'?')){
            $needle = '&';
        }else{
            $needle = '?';
        }
        $url = $loginOutUrl.$needle.http_build_query($param);

        $curl   = new Curl();
        $res = $curl->get($url);

        Logger::write([
            'tag' => 'postRes',
            'url' => $url,
            'msg' => $res,
        ]);
        if (json_decode($res,1)['state'] == 1 ||$res === 1) $result = 1;
        return $result;
    }

    /**
     * @param array $data
     * @return bool|string
     */
    public static function roleChat($data = [])
    {
        Logger::init([
            'path' => RUNTIME_PATH.'admin/'.__FUNCTION__,
            'filename' => date('Y-m-d', time()) ]);
        $result = -1;
        if (empty($data)) return $result;
        $gameConfigList= Common::getConfig(self::GAME_CONFIG_KEY);
        $sign = self::createSign($data);
        $chatUrl = $gameConfigList[$data['gkey']]['chat_url'];

        $param              = [
            'gkey'  => $data['gkey'],
            'tkey'  => $data['tkey'], //暂时不清楚这个值的定义
            'uid'   => $data['uid'],
            'sid'   => $data['sid'],
            'uname' => $data['rolename'],
            'roleid'=> $data['roleid'],
            'ext'   => $data['ext'],
            'type'  => $data['type'],
            'time'  => $data['addtime'],
            'ban_time'=> $data['ban_time'],
            'sign'  => $sign
        ];
        if(strpos($chatUrl,'?')){
            $needle = '&';
        }else{
            $needle = '?';
        }
        $url = $chatUrl.$needle.http_build_query($param);

        $curl   = new Curl();
        $res = $curl->get($url);

        Logger::write([
            'tag' => 'postRes',
            'url' => $url,
            'msg' => $res,
        ]);

        if (json_decode($res,1)['state'] == 1 ||$res === 1) $result = 1;
        return $result;
    }


    /**
     * @param array $data
     * @return bool|string
     */
    public static function roleBlock($data = [])
    {
        Logger::init([
            'path' => RUNTIME_PATH.'admin/'.__FUNCTION__,
            'filename' => date('Y-m-d', time()) ]);
        $result = -1;
        if (empty($data)) return $result;
        $gameConfigList= Common::getConfig(self::GAME_CONFIG_KEY);
        $sign = self::createSign($data);
        $blockUrl = $gameConfigList[$data['gkey']]['block_url'];

        $param              = [
            'gkey'=>$data['gkey'],
            'tkey'=>$data['tkey'],
            'uid'=> $data['uid'],//如果存在聚合的openid，则使用openid，因为cp只有聚合的openid
            'sid'=>$data['sid'],
            'uname'=>$data['rolename'],
            'roleid'=>$data['roleid'],
            'type'=>empty($data['is_block']) ? 1 : $data['is_block'], //判断是否封禁 1封禁 2解禁
            'time'=>$data['addtime'],
            'ban_time'=>$data['ban_time'],
            'ext'   => $data['ext'],
            'sign'=> $sign
        ];

        if(strpos($blockUrl,'?')){
            $needle = '&';
        }else{
            $needle = '?';
        }
        $url = $blockUrl.$needle.http_build_query($param);

        $curl   = new Curl();
        $res = $curl->get($url);

        Logger::write([
            'tag' => 'postRes',
            'url' => $url,
            'msg' => $res,
        ]);
        if (json_decode($res,1)['state'] == 1 ||$res === 1) $result = 1;
        return $result;
    }


    /**
     * @param array $data
     * @return bool|string
     */
    public static function BatchRoleBlock($data = [])
    {
        Logger::init([
            'path' => RUNTIME_PATH.'admin/'.__FUNCTION__,
            'filename' => date('Y-m-d', time()) ]);
        $result = -1;
        if (empty($data)) return $result;
        $gameConfigList= Common::getConfig(self::GAME_CONFIG_KEY);
        $sign = self::createBatchRoleBlockSign($data);
        $blockUrl = $gameConfigList[$data['gkey']]['role_block_url'];

        if(empty($blockUrl)){
            return -2;
        }

        $param              = [
            'gkey'=>$data['gkey'],
            'sid'=>$data['sid'],
            'roleid'=>$data['roleid'],
            'type'=>empty($data['is_block']) ? 1 : $data['is_block'], //判断是否封禁 1封禁 2解禁
            'time'=>$data['addtime'],
            'ban_time'=>$data['ban_time'],
            'sign'=> $sign
        ];

        if(strpos($blockUrl,'?')){
            $needle = '&';
        }else{
            $needle = '?';
        }
        $url = $blockUrl.$needle.http_build_query($param);

        $curl   = new Curl();
        $res = $curl->get($url);

        Logger::write([
            'tag' => 'postRes',
            'url' => $url,
            'msg' => $res,
        ]);
        if (json_decode($res,1)['state']['code'] == 1 ||$res === 1) $result = 1;
        return $result;
    }


    /**
     * @param array $data
     * @return string
     */
    public static function createSign($data=[])
    {
        $gameConfigList= Common::getConfig(self::GAME_CONFIG_KEY);
        $signStr = $data['gkey'].$data['sid'].$data['uid'].$data['addtime'].$gameConfigList[$data['gkey']]['key'];
        $sign = md5($signStr);
        return $sign;
    }

    /**
     * @param array $data
     * @return string
     */
    public static function createBatchRoleBlockSign($data=[])
    {
        $gameConfigList= Common::getConfig(self::GAME_CONFIG_KEY);
        $signStr = $data['gkey'].$data['sid'].$data['roleid'].$data['addtime'].$gameConfigList[$data['gkey']]['key'];
        $sign = md5($signStr);
        return $sign;
    }
}