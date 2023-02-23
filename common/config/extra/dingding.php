<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: yunwuxin <448901948@qq.com>
// +----------------------------------------------------------------------

return [
    'dingding'=>[

        //检测大金额用户自动封禁警告
        'checkBlockWaringDingding'=>[
            'open'=>0,
            'title'=>'【业务警告】',
            'url' => 'https://oapi.dingtalk.com/robot/send?access_token=263971d43c8db34cf453d735ace8aba46fc41072e4198a73b5778936606ed0e8',
            'mobile'=>['15625055357'],
        ],

        //非常登陆设备警告
        'notOftenLoginUdidDingding'=>[
            'open'=>0,
            'title'=>'【业务警告】',
            'url' => 'https://oapi.dingtalk.com/robot/send?access_token=263971d43c8db34cf453d735ace8aba46fc41072e4198a73b5778936606ed0e8',
            'mobile'=>['15625055357'],
        ],

        //非常登陆IP警告
        'notOftenLoginIpDingding'=>[
            'open'=>0,
            'title'=>'【业务警告】',
            'url' => 'https://oapi.dingtalk.com/robot/send?access_token=263971d43c8db34cf453d735ace8aba46fc41072e4198a73b5778936606ed0e8',
            'mobile'=>['15625055357'],
        ]
    ]


];
