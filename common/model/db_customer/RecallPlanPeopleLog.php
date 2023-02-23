<?php

namespace common\model\db_customer;


use common\base\BasicModel;

class RecallPlanPeopleLog extends BasicModel
{
    protected $connection = 'database.db_customer';
    protected $table = 'recall_plan_people_log';
    protected $failException = true; //是否验证抛出异常
    protected $resultSetType = 'collection';
    protected $pk = 'id';
    // 定义时间戳字段名
    protected $createTime = 'add_time';
    protected $updateTime = false;


}
