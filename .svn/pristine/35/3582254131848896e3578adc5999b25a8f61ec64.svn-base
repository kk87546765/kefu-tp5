<?php

namespace common\model\db_customer;


use common\base\BasicModel;

class PlatformList extends BasicModel
{
    protected $connection = 'database.db_customer';
    protected $table = 'platform_list';
    protected $failException = true; //是否验证抛出异常
    protected $resultSetType = 'collection';
    protected $pk = 'id';
    protected $autoWriteTimestamp = true;
    // 定义时间戳字段名
    protected $createTime = 'add_time';
    protected $updateTime = 'edit_time';

    public static $static_arr = [
        1=>'开启',
        2=>'关闭',
    ];

    public function getConfigAttr($value,$data){

        return json_decode($value,true);
    }
}
