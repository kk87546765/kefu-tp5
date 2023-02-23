<?php

namespace common\model\db_customer;


use common\base\BasicModel;

class RecallPlanLog extends BasicModel
{
    protected $connection = 'database.db_customer';
    protected $table = 'recall_plan_log';
    protected $failException = true; //是否验证抛出异常
    protected $resultSetType = 'collection';
    protected $pk = 'id';
    // 定义时间戳字段名
    protected $createTime = 'add_time';
    protected $updateTime = false;

    const STATUS_RUN = 1;
    const STATUS_END = 2;

    public $allow_field = [
        'exec_date','plan_id','platform_id','title','loss_up_id','recall_up_id',
        'min_account_money','max_account_money','min_loss_up_money','max_loss_up_money','min_loss_day','max_loss_day','min_level','max_level',
        'send_num','interval_day','recall_ver_id','recall_code_id','execute_type','should_be_send',
        'have_send','recall_num','add_time','status',
    ];

    public static $status_arr = [
        self::STATUS_RUN=>'执行中',
        self::STATUS_END=>'完成',
    ];
}
