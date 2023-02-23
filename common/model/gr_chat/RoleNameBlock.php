<?php
namespace common\model\gr_chat;


use common\base\BasicModel;

class RoleNameBlock extends BasicModel
{
    protected $connection = 'database.gr_chat';
    protected $table = 'gr_role_name_block';
    protected $failException = true; //是否验证抛出异常
    protected $resultSetType = 'collection';
    protected $pk = 'id';
    protected $autoWriteTimestamp = false;
}