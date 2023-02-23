<?php

namespace common\model\db_statistic;


use common\base\BasicModel;

class DistributionalServer extends BasicModel
{
    protected $connection = 'database.db_statistic';
    protected $table = 'distributional_server';
    protected $failException = true; //是否验证抛出异常
    protected $resultSetType = 'collection';
    protected $pk = 'id';
}
