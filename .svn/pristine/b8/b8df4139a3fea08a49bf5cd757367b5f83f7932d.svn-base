<?php

namespace common\model\db_statistic;


use common\base\BasicModel;

class VipUserRebateProp extends BasicModel
{
    protected $connection = 'database.db_statistic';
    protected $failException = true; //是否验证抛出异常
    protected $resultSetType = 'collection';
    protected $pk = 'id';
    protected $autoWriteTimestamp = true;
    protected $createTime = 'add_time';
    protected $updateTime = false;

    public static $apply_status_arr = [
        1=>'未申请',
        2=>'已申请',
    ];

    public static $examine_status_arr = [
        0=>'未审核',
        1=>'通过',
        2=>'拒审',
    ];
}
