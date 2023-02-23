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
    'sms'=>[

        'tencent'=>[
            'name'=>'腾讯云',
            'code'=>'tencent',
            'template'=>[
                1342131=>[
                    'type'=>'业务警告',
                    'content'=>"后台预警 预警类型：{0} 预警时间：{1} 预警内容：{2} 五分钟内有效。请勿将验证码告知他人！任何索要验证码都是骗子！",
                    ],
                1342129=>[
                    'type'=>'验证码验证',
                    'content'=>"验证码是：{0}",
                ]
            ],
            'sign'=>['流连互娱网络']
        ],

        'nuoer'=>[
            'name'=>'诺尔代发',
            'code'=>'nuoer',
            'template'=>[
                0=>[
                    'type'=>'测试',
                    'content'=>"{0}",
                ],
            ],
            'sign'=>['【茂宏】','【茂宏网络】','【掌玩平台】','【冰雪科技】','【新辰风源】','【阿斯加德游戏】','【流连互娱】','【掌玩网络】']
        ]


    ]


];
