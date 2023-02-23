<?php

namespace common\model\db_customer;


use common\base\BasicModel;

class WorkSheet extends BasicModel
{
    protected $connection = 'database.db_customer';
    protected $failException = true; //是否验证抛出异常
    protected $resultSetType = 'collection';
    protected $pk = 'id';
    protected $autoWriteTimestamp = true;
    protected $createTime = 'add_time';
    protected $updateTime = 'edit_time';

    public static $status_arr = [
        0 =>'待处理',
        1 =>'审核通过',
        2 =>'信息有误',
        3 =>'已处理待回复',
        4 =>'已结单',
    ];

    public static $contact_type_arr = [
        1 =>'微信',
        2 =>'QQ',
        3 =>'手机',
        4 =>'在线',
        5 =>'论坛',
        6 =>'电话',
        7 =>'其他',
    ];

    public static $user_source_arr =[
        1=>'智齿',
        2=>'企点',
        3=>'QQ',
        4=>'微信',
        5=>'电话',
        6=>'论坛',
        7=>'其他',
    ];
}
