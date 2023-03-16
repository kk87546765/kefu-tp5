<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
use think\Env;

return [
    // 数据库类型
    'type'            => 'mysql',
    // 服务器地址
    'hostname'        => Env::get('def_database.hostname','127.0.0.1'),
    // 数据库名
    'database'        => Env::get('def_database.database',''),
    // 用户名
    'username'        => Env::get('def_database.username',''),
    // 密码
    'password'        => Env::get('def_database.password',''),
    // 端口
    'hostport'        => Env::get('def_database.hostport','3306'),
    // 连接dsn
    'dsn'             => '',
    // 数据库连接参数
    'params'          => [],
    // 数据库编码默认采用utf8
    'charset'         => 'utf8',
    // 数据库表前缀
    'prefix'          => Env::get('def_database.prefix',''),
    // 数据库调试模式
    'debug'           => true,
    // 数据库部署方式:0 集中式(单一服务器),1 分布式(主从服务器)
    'deploy'          => 0,
    // 数据库读写是否分离 主从式有效
    'rw_separate'     => false,
    // 读写分离后 主服务器数量
    'master_num'      => 1,
    // 指定从服务器序号
    'slave_no'        => '',
    // 自动读取主库数据
    'read_master'     => false,
    // 是否严格检查字段是否存在
    'fields_strict'   => true,
    // 数据集返回类型
    'resultset_type'  => 'array',
    // 自动写入时间戳字段
    'auto_timestamp'  => false,
    // 时间字段取出后的默认时间格式
    'datetime_format' => 'Y-m-d H:i:s',
    // 是否需要进行SQL性能分析
    'sql_explain'     => false,

    'db_slave'=>[
        [
            "weight"=>1,
            'db'=>[
                // 数据库类型
                'type'        => 'mysql',
                // 数据库连接DSN配置
                'dsn'         => '',
                // 服务器地址
                'hostname'    => 'rr-wz91y447r3r49quz2.mysql.rds.aliyuncs.com',
                // 数据库名
                'database'    => 'adv_system',
                // 数据库用户名
                'username'    => 'dbllk',
                // 数据库密码
                'password'    => 'sVb2eKkjI1eQoZbEJD',
                // 数据库连接端口
                'hostport'    => '',
                // 数据库连接参数
                'params'      => [],
                // 数据库编码默认采用utf8
                'charset'     => 'utf8',
                // 数据库表前缀
                'prefix'      => 'tbl_',
            ]
        ],
        //后面如果新增了服务器，可以往下添加
        [
            "weight"=>5,
            'db'=>[
                // 数据库类型
                'type'        => 'mysql',
                // 数据库连接DSN配置
                'dsn'         => '',
                // 服务器地址
                'hostname'    => 'rm-wz9631921u10159mt.mysql.rds.aliyuncs.com',
                // 数据库名
                'database'    => 'adv_system',
                // 数据库用户名
                'username'    => 'dbllk',
                // 数据库密码
                'password'    => 'sVb2eKkjI1eQoZbEJD',
                // 数据库连接端口
                'hostport'    => '',
                // 数据库连接参数
                'params'      => [],
                // 数据库编码默认采用utf8
                'charset'     => 'utf8',
                // 数据库表前缀
                'prefix'      => 'tbl_',
            ]
        ],
    ],

    'gr_chat' => [
        // 数据库类型
        'type'        => 'mysql',
        // 数据库连接DSN配置
        'dsn'         => '',
        // 服务器地址
        'hostname'    => Env::get('def_database.hostname','127.0.0.1'),
        // 数据库名
        'database'    => 'gr_chat',
        // 数据库用户名
        'username'    => Env::get('def_database.username',''),
        // 数据库密码
        'password'    => Env::get('def_database.password',''),
        // 数据库连接端口
        'hostport'    => Env::get('def_database.hostport',''),
        // 数据库连接参数
        'params'      => [],
        // 数据库编码默认采用utf8
        'charset'     => 'utf8',
        // 数据库表前缀
        'prefix'      => 'gr_',
    ],
    'db_statistic' => [
        // 数据库类型
        'type'        => 'mysql',
        // 数据库连接DSN配置
        'dsn'         => '',
        // 服务器地址
        'hostname'    => Env::get('def_database.hostname','127.0.0.1'),
        // 数据库名
        'database'    => 'db_statistic',
        // 数据库用户名
        'username'    => Env::get('def_database.username',''),
        // 数据库密码
        'password'    => Env::get('def_database.password',''),
        // 数据库连接端口
        'hostport'    => Env::get('def_database.hostport',''),
        // 数据库连接参数
        'params'      => [],
        // 数据库编码默认采用utf8
        'charset'     => 'utf8',
        // 数据库表前缀
        'prefix'      => '',
    ],
    'db_customer' => [
        // 数据库类型
        'type'        => 'mysql',
        // 数据库连接DSN配置
        'dsn'         => '',
        // 服务器地址
        'hostname'    => Env::get('def_database.hostname','127.0.0.1'),
        // 数据库名
        'database'    => 'db_customer',
        // 数据库用户名
        'username'    => Env::get('def_database.username',''),
        // 数据库密码
        'password'    => Env::get('def_database.password',''),
        // 数据库连接端口
        'hostport'    => Env::get('def_database.hostport',''),
        // 数据库连接参数
        'params'      => [],
        // 数据库编码默认采用utf8
        'charset'     => 'utf8',
        // 数据库表前缀
        'prefix'      => '',
    ],
    'db_customer_ll' => [
        // 数据库类型
        'type'        => 'mysql',
        // 数据库连接DSN配置
        'dsn'         => '',
        // 服务器地址
        'hostname'    => Env::get('def_database.hostname','127.0.0.1'),
        // 数据库名
        'database'    => 'db_customer_ll',
        // 数据库用户名
        'username'    => Env::get('def_database.username',''),
        // 数据库密码
        'password'    => Env::get('def_database.password',''),
        // 数据库连接端口
        'hostport'    => Env::get('def_database.hostport',''),
        // 数据库连接参数
        'params'      => [],
        // 数据库编码默认采用utf8
        'charset'     => 'utf8',
        // 数据库表前缀
        'prefix'      => '',
    ],
    'db_customer_mh' => [
        // 数据库类型
        'type'        => 'mysql',
        // 数据库连接DSN配置
        'dsn'         => '',
        // 服务器地址
        'hostname'    => Env::get('def_database.hostname','127.0.0.1'),
        // 数据库名
        'database'    => 'db_customer_mh',
        // 数据库用户名
        'username'    => Env::get('def_database.username',''),
        // 数据库密码
        'password'    => Env::get('def_database.password',''),
        // 数据库连接端口
        'hostport'    => Env::get('def_database.hostport',''),
        // 数据库连接参数
        'params'      => [],
        // 数据库编码默认采用utf8
        'charset'     => 'utf8',
        // 数据库表前缀
        'prefix'      => '',
    ],
    'db_customer_xll' => [
        // 数据库类型
        'type'        => 'mysql',
        // 数据库连接DSN配置
        'dsn'         => '',
        // 服务器地址
        'hostname'    => Env::get('def_database.hostname','127.0.0.1'),
        // 数据库名
        'database'    => 'db_customer_xll',
        // 数据库用户名
        'username'    => Env::get('def_database.username',''),
        // 数据库密码
        'password'    => Env::get('def_database.password',''),
        // 数据库连接端口
        'hostport'    => Env::get('def_database.hostport',''),
        // 数据库连接参数
        'params'      => [],
        // 数据库编码默认采用utf8
        'charset'     => 'utf8',
        // 数据库表前缀
        'prefix'      => '',
    ],
    'db_customer_zw' => [
        // 数据库类型
        'type'        => 'mysql',
        // 数据库连接DSN配置
        'dsn'         => '',
        // 服务器地址
        'hostname'    => Env::get('def_database.hostname','127.0.0.1'),
        // 数据库名
        'database'    => 'db_customer_zw',
        // 数据库用户名
        'username'    => Env::get('def_database.username',''),
        // 数据库密码
        'password'    => Env::get('def_database.password',''),
        // 数据库连接端口
        'hostport'    => Env::get('def_database.hostport',''),
        // 数据库连接参数
        'params'      => [],
        // 数据库编码默认采用utf8
        'charset'     => 'utf8',
        // 数据库表前缀
        'prefix'      => '',
    ],
    'db_customer_youyu' => [
        // 数据库类型
        'type'        => 'mysql',
        // 数据库连接DSN配置
        'dsn'         => '',
        // 服务器地址
        'hostname'    => Env::get('def_database.hostname','127.0.0.1'),
        // 数据库名
        'database'    => 'db_customer_youyu',
        // 数据库用户名
        'username'    => Env::get('def_database.username',''),
        // 数据库密码
        'password'    => Env::get('def_database.password',''),
        // 数据库连接端口
        'hostport'    => Env::get('def_database.hostport',''),
        // 数据库连接参数
        'params'      => [],
        // 数据库编码默认采用utf8
        'charset'     => 'utf8',
        // 数据库表前缀
        'prefix'      => '',
    ],
    'db_customer_bx' => [
        // 数据库类型
        'type'        => 'mysql',
        // 数据库连接DSN配置
        'dsn'         => '',
        // 服务器地址
        'hostname'    => Env::get('def_database.hostname','127.0.0.1'),
        // 数据库名
        'database'    => 'db_customer_bx',
        // 数据库用户名
        'username'    => Env::get('def_database.username',''),
        // 数据库密码
        'password'    => Env::get('def_database.password',''),
        // 数据库连接端口
        'hostport'    => Env::get('def_database.hostport',''),
        // 数据库连接参数
        'params'      => [],
        // 数据库编码默认采用utf8
        'charset'     => 'utf8',
        // 数据库表前缀
        'prefix'      => '',
    ],

];
