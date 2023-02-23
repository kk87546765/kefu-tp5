<?php

namespace common\model\db_customer;


use common\base\BasicModel;

class QcQuestionLog extends BasicModel
{
    protected $connection = 'database.db_customer';
    protected $failException = true; //是否验证抛出异常
    protected $resultSetType = 'collection';
    protected $pk = 'id';
    protected $autoWriteTimestamp = false;
    protected $dateFormat = false;

    public static $status_arr = [
        -1=>'申诉驳回',
        0=>'正常',
        1=>'申诉中',
        2=>'一审成功',
        3=>'申诉完成',
    ];

    public function checkAppealById($id,$status = 1){
        $info = $this->where('id',$id)->find();

        if(!$info){
            return false;
        }

        if($info->status == $status){
            return false;
        }

        return $info;
    }
}
