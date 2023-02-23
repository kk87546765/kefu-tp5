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
use think\Env;
return [
    'polymeriza_list'=>[
        'getPlatformUrl'         => Env::get('configKey.getPlatformUrl',''),
        'getPlatformKey'         => Env::get('configKey.getPlatformKey',''),
        'platformSuffix'         =>[
            75=>'bx',  #冰雪
            76=>'bx',  #
            43=>'mh',  #茂宏
            72=>'mh',
            32=>'mh',
            25=>'ll',  #流连
            69=>'xll', #新流连
            44=>'ll',
            73=>'ll',
            65=>'zw',  #掌玩
            66=>'zw',
            100=>'youyu', #游娱
            108=>'youyu' #游娱
        ]
    ]


];
