<?php

namespace common\model\db_statistic;


use common\base\BasicModel;

class SellWorkOrderStatisticMonth extends BasicModel
{
    protected $connection = 'database.db_statistic';
    protected $failException = true; //是否验证抛出异常
    protected $resultSetType = 'collection';
    protected $pk = 'id';

    const TYPE_ALL = 1;//人月总统计
    const TYPE_GAME = 2;//人、产品月总统计

    // 工单类型
    static public $type_arr = [
        self::TYPE_ALL=>'总',
        self::TYPE_GAME=>'产品',
    ];

}
