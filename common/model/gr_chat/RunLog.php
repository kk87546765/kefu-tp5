<?php

namespace common\model\gr_chat;


use common\base\BasicModel;

class RunLog extends BasicModel
{
    protected $failException = true; //是否验证抛出异常
    protected $resultSetType = 'collection';
    protected $pk = 'id';
    protected $autoWriteTimestamp = true;
    // 定义时间戳字段名
    protected $createTime = 'add_time';
    protected $updateTime = 'update_time';

    const STATUS_ARR = [
        0=>'执行中',
        1=>'完成',
    ];

    protected $type = [
        'input_param'      =>  'json',
        'out_param'        =>  'json',
    ];
}
