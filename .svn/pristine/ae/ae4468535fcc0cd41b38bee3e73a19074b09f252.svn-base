<?php

namespace common\model\db_customer;


use common\base\BasicModel;

class RecallPlan extends BasicModel
{
    protected $connection = 'database.db_customer';
    protected $table = 'recall_plan';
    protected $failException = true; //是否验证抛出异常
    protected $resultSetType = 'collection';
    protected $pk = 'id';
    // 定义时间戳字段名
    protected $autoWriteTimestamp = true;
    protected $createTime = 'add_time';
    protected $updateTime = false;

    const STATUS_OPEN = 1;
    const STATUS_CLOSE = 0;

    const EXECUTE_TYPE_AUTO = 1;
    const EXECUTE_TYPE_ARTIFICIAL = 2;

    public static $status_arr = [
        self::STATUS_CLOSE=>'关闭',
        self::STATUS_OPEN=>'开启',
    ];

    public static $execute_type_arr = [
        self::EXECUTE_TYPE_AUTO=>'自动',
        self::EXECUTE_TYPE_ARTIFICIAL=>'人工',
    ];


}
