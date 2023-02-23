<?php

namespace common\model\gr_chat;


use common\base\BasicModel;

class RoleList extends BasicModel
{
    protected $failException = true; //是否验证抛出异常
    protected $resultSetType = 'collection';
    protected $pk = 'id';
    protected $autoWriteTimestamp = false;
}
