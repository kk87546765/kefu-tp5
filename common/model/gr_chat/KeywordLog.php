<?php
namespace common\model\gr_chat;


use common\base\BasicModel;

class KeywordLog extends BasicModel
{
    protected $connection = 'database.gr_chat';
    protected $table = 'gr_keyword_log';
    protected $failException = true; //是否验证抛出异常
    protected $resultSetType = 'collection';
    protected $pk = 'id';
    protected $autoWriteTimestamp = false;
}