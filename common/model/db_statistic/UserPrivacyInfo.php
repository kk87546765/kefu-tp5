<?php

namespace common\model\db_statistic;


use common\base\BasicModel;

class UserPrivacyInfo extends BasicModel
{
    protected $connection = 'database.db_statistic';
    protected $failException = true; //是否验证抛出异常
    protected $resultSetType = 'collection';
    protected $pk = 'id';
    protected $autoWriteTimestamp = true;
    protected $createTime = 'add_time';
    protected $updateTime = false;

    public static $status_arr = [
        0=>'未处理',
        1=>'已添加待用户通过',
        2=>'添加成功',
        3=>'添加失败',
    ];
}
