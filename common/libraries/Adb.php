<?php
namespace common\libraries;


use think\Db;

class Adb
{

    public $adb_model = '';
    public function __construct($field = 'adb')
    {

        $this->adb_model = Db::connect(config('database.'.$field));

        $this->adb_model->execute('set names utf8');

    }

    public function query($sql)
    {
        $res = $this->adb_model->query($sql);

        return $res;
    }


}