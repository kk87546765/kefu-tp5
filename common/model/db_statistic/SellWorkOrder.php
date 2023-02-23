<?php

namespace common\model\db_statistic;


use common\base\BasicModel;

class SellWorkOrder extends BasicModel
{
    protected $connection = 'database.db_statistic';
    protected $failException = true; //是否验证抛出异常
    protected $resultSetType = 'collection';
    protected $pk = 'id';
    protected $autoWriteTimestamp = true;
    protected $createTime = 'add_time';
    protected $updateTime = 'update_time';

    const REMARK_ORDER = 1;//登记

    const SELL_ORDER = 2;//销售

    const MAINTENANCE_ORDER = 3;//维护

    const RECALL_ORDER = 4;//流失召回

    const CHUM_ORDER = 5;//流失召回

    // 工单类型
    static public $type_arr = [
        self::REMARK_ORDER=>'登记',
        self::SELL_ORDER=>'销售',
        self::MAINTENANCE_ORDER=>'维护',
        self::RECALL_ORDER=>'流失召回',
        self::CHUM_ORDER=>'流失回访',
    ];
    // 销售类型
    static public $sell_type_arr = [
        1=>'普通',
        2=>'召回',
        3=>'特殊',
    ];
    // 审核状态
    static public $status_arr = [
        -2=>'二审拒绝',
        -1=>'初审拒绝',
        0=>'待审核',
        1=>'初审通过',
        2=>'完成',
    ];
    // 联系方式类型
    static public $contact_type_arr = [
        1=>'QQ',
        2=>'电话',
        2=>'微信',
    ];

    // 销售次数
    static public $is_first_arr = [
        1=>'首次',
        0=>'多次',
    ];
}
