<?php

namespace common\model\gr_chat;


use common\base\BasicModel;
use traits\model\JwtAuthModelTrait;

class Admin extends BasicModel
{
    use JwtAuthModelTrait;
    protected $connection = 'database.gr_chat';
    protected $table = 'gr_admin';
    protected $failException = true; //是否验证抛出异常
    protected $resultSetType = 'collection';
    protected $pk = 'id';
    protected $autoWriteTimestamp = true;
    protected $createTime = 'add_time';
    protected $updateTime = false;

    public static $status_arr = [
        1=>"启用",
        2=>"停用",
    ];
}
