<?php

namespace common\model\db_customer;


use common\base\BasicModel;

class RecallLinkGroup extends BasicModel
{
    protected $connection = 'database.db_customer';
    protected $failException = true; //是否验证抛出异常
    protected $resultSetType = 'collection';
    protected $pk = 'id';
    protected $autoWriteTimestamp = true;
    // 定义时间戳字段名
    protected $createTime = 'add_time';
    protected $updateTime = 'update_time';

    const STATUS_OPEN = 1;
    const STATUS_CLOSE = 0;

    public static $status_arr = [
        self::STATUS_CLOSE=>'关闭',
        self::STATUS_OPEN=>'开启',
    ];

}
