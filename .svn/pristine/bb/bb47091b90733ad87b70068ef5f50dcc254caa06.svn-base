<?php
/**
 * Created by PhpStorm.
 * User: crosstime
 * Date: 2018/8/14
 * Time: 下午5:31
 */
namespace common\server\AccountRegistrationLog;

use common\base\BasicServer;

use common\server\AccountRegistration\AccountRegistrationServer;
use common\sql_server\AccountRegistrationLogSqlServer;


class AccountRegistrationLogServer extends BasicServer
{
    public static $return=[];

    public static $redis_set_name = 'account_registration';

    public static $redis = '';



    public static function add($data)
    {
        $res = AccountRegistrationLogSqlServer::add($data);

        $redis = get_redis();
        self::$redis = $redis;
        if($redis->SCARD(self::$redis_set_name) == 0){
            self::initSmembers();
        }

        if($res) {
            $a = $redis->Sismember(self::$redis_set_name,$data['server_id'] . '_' . $data['role_id'] . '_' . $data['role_name']);

            if(!$a){
                if(!empty($data['server_id']) && !empty($data['role_id']) && !empty($data['role_name'])) {
                    $redis->sadd(self::$redis_set_name, $data['server_id'] . '_' . $data['role_id'] . '_' . $data['role_name']);
                }
            }
            $res = true;
        }else{
            $res = false;
        }


        return $res;
    }

    public static function initSmembers()
    {

        self::$redis->del(self::$redis_set_name);
        $list = AccountRegistrationServer::getList(['limit'  => 200000, 'page' => 1]);
        foreach($list as $k=>$v){
            if(!empty($v['server_id']) && !empty($v['role_id']) && !empty($v['role_name'])){
                self::$redis->sadd(self::$redis_set_name,$v['server_id'].'_'.$v['role_id'].'_'.$v['role_name']);
            }

        }
    }


}