<?php

namespace common\model\db_customer;


use common\base\BasicModel;

class QcQuestion extends BasicModel
{
    protected $connection = 'database.db_customer';
    protected $failException = true; //是否验证抛出异常
    protected $resultSetType = 'collection';
    protected $pk = 'id';
    protected $autoWriteTimestamp = false;

    public function saveQcNum($is_define = 1){

        if($this->qc_num){
            return $this;
        }

        $str = date('Ymd').$this->id;

        $this->qc_num = $str;

        if($is_define = 1){
            $this->text_id = 'define'.$str;
        }

        return $this->save();
    }
}
