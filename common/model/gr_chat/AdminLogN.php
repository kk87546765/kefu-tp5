<?php

namespace common\model\gr_chat;


use common\base\BasicModel;

class AdminLogN extends BasicModel
{
    protected $failException = true; //是否验证抛出异常
    protected $resultSetType = 'collection';
    protected $pk = 'id';
    protected $autoWriteTimestamp = true;
    protected $createTime = 'add_time';
    protected $updateTime = false;
}
