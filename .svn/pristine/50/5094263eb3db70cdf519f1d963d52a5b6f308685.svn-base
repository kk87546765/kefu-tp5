<?php
/**
 * Created by PhpStorm.
 * User: crosstime
 * Date: 2018/8/14
 * Time: 下午5:31
 */
namespace common\sql_server;

use common\libraries\Adb;
use common\sql_server\BaseSqlServer;

class AdbSqlServer extends BaseSqlServer
{
    public $model = '';

    public function __construct($field = 'adb')
    {
        $this->model = new Adb($field);

    }

    public function getChat($field = [],$where = '')
    {
        $str_field = implode(',',$field);
        $sql = "select {$str_field} from chat_message where {$where} limit 0,100";
        $res = $this->query($sql);
        return $res;
    }

    private function query($sql)
    {
        $res = $this->model->query($sql);
        return $res;
    }

}