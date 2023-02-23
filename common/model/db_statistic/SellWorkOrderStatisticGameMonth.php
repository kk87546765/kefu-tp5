<?php

namespace common\model\db_statistic;


use common\base\BasicModel;

class SellWorkOrderStatisticGameMonth extends BasicModel
{
    protected $connection = 'database.db_statistic';
    protected $table = 'sell_work_order_statistic_game_month';
    protected $failException = true; //是否验证抛出异常
    protected $resultSetType = 'collection';
    protected $pk = 'id';


}
