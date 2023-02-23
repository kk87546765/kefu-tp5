<?php

namespace common\model\db_customer;


use common\base\BasicModel;

class WorkSheetType extends BasicModel
{
    protected $connection = 'database.db_customer';
    protected $failException = true; //是否验证抛出异常
    protected $resultSetType = 'collection';
    protected $pk = 'id';
    protected $autoWriteTimestamp = true;
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';

    public static $status_arr = [
        1=>'显示',
        2=>'隐藏',
    ];

    public static $field_title_arr = [
        'sheet_type'=>'工单类型',
        'sheet_item_type'=>'操作类型',
        'sheet_item_type_sub'=>'子类型',
        'role_id'=>'角色ID',
        'user_account'=>'账号',
        'platform_id'=>'平台',
        'product_id'=>'产品',
        'game_id'=>'游戏',
        'server_id'=>'区服',
        'uid'=>'玩家UID',
        'pay_money_total'=>'总充值金额',
        'user_source'=>'用户来源',
        'visitor_id'=>'访客ID',
        'contact_type'=>'联系方式类型',
        'contact'=>'联系方式',
        'status'=>'状态',
//        'is_top'=>'是否置顶',
        'record_user'=>'记录人',
        'follow_user'=>'跟进人',
        'apply_time'=>'申请时间',
        'handle_time'=>'处理时间',
        'content'=>'问题描述',
    ];
}
