<?php
/**
 * Created by PhpStorm.
 * User: crosstime
 * Date: 2018/8/14
 * Time: 下午5:31
 */
namespace common\model\db_statistic;

use common\base\BasicModel;


class AccountSearchLog extends BasicModel
{

    protected $connection = 'database.db_statistic';
    protected $table = 'account_search_log';
    protected $failException = true; //是否验证抛出异常
    protected $resultSetType = 'collection';
    protected $pk = 'id';
    protected $autoWriteTimestamp = false;

}