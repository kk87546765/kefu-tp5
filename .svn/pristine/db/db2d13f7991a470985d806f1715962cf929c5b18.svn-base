<?php


namespace common\sql_server;
use common\model\db_statistic\PlatformChannelInfo;

class ChannelInfoSqlServer extends BaseSqlServer
{


    /**
     * @param array $data
     * @return array
     */
    public static function getList($data=array())
    {
        $model = new PlatformChannelInfo();
        $res = $model->where($data)->select();

        if (!empty($res)) $result = $res->toArray();
        return $result;
    }

    public static function add($data)
    {
        $model = new PlatformChannelInfo();

        $res = $model->insert($data);

        return $res;
    }


    public static function insertPlatformChannelInfo($data){

        $model = new PlatformChannelInfo();
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

        $res = $model->execute($sql);
        return $res;
    }


}