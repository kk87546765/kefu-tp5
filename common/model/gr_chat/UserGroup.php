<?php

namespace common\model\gr_chat;


use common\base\BasicModel;

class UserGroup extends BasicModel
{
    protected $failException = true; //是否验证抛出异常
    protected $resultSetType = 'collection';
    protected $pk = 'id';
    protected $autoWriteTimestamp = true;
    // 定义时间戳字段名
    protected $createTime = 'add_time';
    protected $updateTime = 'edit_time';

    public static $status_arr = [
        1=>'启用',
        2=>'停用',
    ];
}
