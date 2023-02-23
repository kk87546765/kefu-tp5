<?php

namespace common\model\gr_chat;


use common\base\BasicModel;

class Menu extends BasicModel
{
//    protected $connection = 'all_database.adv_system';
    protected $table = 'gr_menu_new';
    protected $failException = true; //是否验证抛出异常
    protected $pk = 'menu_id';
    protected $resultSetType = 'collection';// 设置返回数据集的对象名
    protected $autoWriteTimestamp = false;

    public static $type_arr = [
        1=>'菜单',
        2=>'接口',
    ];
    public static $menu_type_arr = [
        1=>'一级菜单',
        2=>'二级菜单',
        3=>'三级菜单',
    ];

}
