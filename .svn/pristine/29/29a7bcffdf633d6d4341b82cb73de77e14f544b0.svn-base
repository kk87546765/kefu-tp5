<?php
use \think\Env;

return [
    'connector'=>'redis',
    'expire'=>60,
    'default'=>'default_queue',
    'host'=>Env::get('redis.host','127.0.0.1'),
    'port'=>Env::get('redis.port','6379'),
    'password'=>Env::get('redis.password',''),
    'select'=>8,
    'timeout'=>Env::get('redis.timeout',0),
    'persistent'=>false,
];
