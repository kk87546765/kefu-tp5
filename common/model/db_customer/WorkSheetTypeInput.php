<?php

namespace common\model\db_customer;


use common\base\BasicModel;

class WorkSheetTypeInput extends BasicModel
{
    protected $connection = 'database.db_customer';
    protected $failException = true; //是否验证抛出异常
    protected $resultSetType = 'collection';
    protected $pk = 'id';
    protected $autoWriteTimestamp = false;

    public static $status_arr = [
        1=>'显示',
        2=>'隐藏',
    ];

    public static $upload_type_arr = [
        1=>'图片',
        2=>'单视频',
        3=>'单文件',
    ];

}
