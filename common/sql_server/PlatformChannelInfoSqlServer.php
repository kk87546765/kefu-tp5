<?php


namespace Quan\Common\Models;


use \Quan\System\Mvc\Model;
use Phalcon\Mvc\Model\Resultset\Simple as ResultSet;

class PlatformChannelInfo extends Model
{


    public function initialize()
    {
        $this->setReadConnectionService('db_db_statistic');
        $this->setWriteConnectionService('db_db_statistic');

        parent::initialize();
    }



    /**
     * @param string $sql
     * @param false $write
     * @return ResultSet
     */
    public function excuteSQL($sql = '', $write = false)
    {
        $sql = trim($sql);
        $write = in_array(strtolower(substr($sql, 0, 6)), array('insert', 'update', 'delete'));
        $model = new static();
        if ($write == true) {
            $query = $model->getWriteConnection()->execute($sql);
            return $query;
        } else {
            return new ResultSet(null, $model, $model->getReadConnection()->query($sql));
        }
    }

    /**
     * @param array $data
     * @return array
     */
    public static function getList($data=array())
    {
        $conditions = '1 ';
        $bind = $result = [];
        foreach ($data as $k=>$v) {
            $conditions .= "and $k = :$k: ";
            $bind[$k] = $v;
        }
        if (empty($bind)) {
            $res = self::find();
        }else {
            $res = self::find([
                'conditions' =>$conditions,
                'bind' => $bind,
            ]);
        }

        if (!empty($res)) $result = $res->toArray();
        return $result;
    }

    public static function add($data)
    {
        $model = new self;

        foreach ($data as $key => $item) {
            $model->$key = $item;
        }

        $res = $model->create();

        if(!$res){
            return false;
        }
        return $model;
    }


    public static function insertPlatformChannelInfo($data){
        $field = [
            'platform_id',
            'channel_id',
            'channel_name',
            'channel_ver',
            'game_id',
            'game_name',
            'product_id',
            'product_name',
            'edit_time',
            'status',
        ];
        $sql = setIntoSql('platform_channel_info',$field,$data);
        $sql .= ' ON DUPLICATE KEY UPDATE 
        channel_name = values(channel_name),
        channel_ver = values(channel_ver),
        game_id = values(game_id),
        game_name = values(game_name),
        product_id = values(product_id),
        product_name = values(product_name),
        status = values(status),
        edit_time = values(edit_time)';

        self::excuteSQL($sql);

    }


}