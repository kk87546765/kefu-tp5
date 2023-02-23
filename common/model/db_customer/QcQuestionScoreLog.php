<?php

namespace common\model\db_customer;


use common\base\BasicModel;

class QcQuestionScoreLog extends BasicModel
{
    protected $connection = 'database.db_customer';
    protected $failException = true; //是否验证抛出异常
    protected $resultSetType = 'collection';
    protected $pk = 'id';
    protected $autoWriteTimestamp = false;

    public function saveInfoByQLId($ql_id , $data = []){

        $old_list = $this->getListByQlId($ql_id)->toArray();

        if($old_list){
            //清除数据
            $this->delNotInIdsByQlId($ql_id);
        }

        if($data){

            foreach ($data as $k => $v) {
                $data[$k]['qc_question_log_id'] = $ql_id;
            }

            $this->insertAll($data);
        }

        return true;

    }

    public function getListByQlId($ql_id){
        return $this->where('qc_question_log_id',$ql_id)->select();
    }


    public function delNotInIdsByQlId($ql_id,$ids=array()){

        $where = [];
        $where['qc_question_log_id'] = $ql_id;

        if($ids){
            $where['id'] = ['not in',$ids];
        }

        return $this->where($where)->delete();

    }
}
