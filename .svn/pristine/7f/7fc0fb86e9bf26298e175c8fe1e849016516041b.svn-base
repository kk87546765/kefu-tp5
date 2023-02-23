<?php

namespace common\model\db_customer;


use common\base\BasicModel;

class Gamekey extends BasicModel
{
    protected $connection = 'database.db_customer';
    protected $table = 'gamekey';
    protected $failException = true; //是否验证抛出异常
    protected $resultSetType = 'collection';
    protected $pk = 'id';
    protected $autoWriteTimestamp = true;
    // 定义时间戳字段名
    protected $createTime = 'add_time';
    protected $updateTime = 'edit_time';

    public static $status = [
        1=>'开启',
        0=>'关闭',
    ];

    public function getConfigAttr($value,$data){

        return json_decode($value,true);
    }
}
