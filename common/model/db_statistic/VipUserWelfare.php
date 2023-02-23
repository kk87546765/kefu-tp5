<?php

namespace common\model\db_statistic;


use common\base\BasicModel;

class VipUserWelfare extends BasicModel
{
    protected $connection = 'database.db_statistic';
    protected $failException = true; //是否验证抛出异常
    protected $resultSetType = 'collection';
    protected $pk = 'id';
    protected $autoWriteTimestamp = true;
    protected $createTime = 'add_time';
    protected $updateTime = false;

    public static $is_send_arr = [
        0=>'未发放',
        1=>'已发放',
    ];

}
