<?php
/**
 * Created by PhpStorm.
 * User: crosstime
 * Date: 2018/8/14
 * Time: 下午5:31
 */
namespace common\sql_server;

use common\model\gr_chat\BlockWaring;
class BlockWaringSqlServer extends BaseSqlServer
{

    public static function getList($where,$offset=0,$limit=100000,$order = 'id asc')
    {
        $model = new BlockWaring();
        if(isset($offset) && isset($limit)){

            $data = $model->where($where)->limit($offset,$limit)->order($order)->select();

        }else{

            $data = $model->where($where)->order($order)->select();
        }


        $data = isset($data) ? $data->toArray() : [];

       return $data;
    }

    public static function getCount($where)
    {
        $model = new BlockWaring();

        $data = $model->where($where)->count();

        return $data;
    }

    public static function getOne($id)
    {
        $model = new BlockWaring();

        $data = $model->where("id = {$id}")->find();

        $data = isset($data) ? $data->toArray() : '';
        return $data;
    }

    public static function insert($data)
    {

        $model = new BlockWaring();

        $res = $model->where(['es_id'=>$data['id'],'uid'=>$data['uid']])->find();
       if(isset($res)){
           return false;
       }

        $sql = "insert into gr_block_waring(`es_id`,`gkey`,`tkey`,`sid`,`uid`,`uname`,`roleid`,`type`,`block_type`
                ,`content`,`keyword`,`keyword_id`,`time`,`ip`,`to_uid`,`to_uname`,`role_level`,`imei`,`count_money`,`reg_channel_id`,`ext`
                ,`openid`,`is_sensitive`,`sensitive_keyword`,`request_time`
                )values";
        $sql .= "('{$data['id']}',".
            "'{$data['gkey']}',".
            "'{$data['tkey']}',".
            "'{$data['sid']}',".
            "'{$data['uid']}',".
            "'{$data['uname']}',".
            "'{$data['roleid']}',".
            "{$data['chat_type']},".
            "{$data['block_type']},".
            "'{$data['content']}',".
            "'{$data['tmp_keyword']}',".
            "{$data['hit_keyword_id']},".
            "{$data['time']},".
            "'{$data['ip']}',".
            "'{$data['to_uid']}',".
            "'{$data['to_uname']}',".
            "'{$data['role_level']}',".
            "'{$data['imei']}',".
            "'{$data['count_money']}',".
            "{$data['reg_channel_id']},".
            "'{$data['ext']}',".
            "'{$data['openid']}',".
            "{$data['is_sensitive']},".
            "'{$data['sensitive_keyword']}',".
            "{$data['request_time']})";


        $ret = $model->execute($sql);
        return $ret;
    }

    public static function edit($data)
    {
        $model = new BlockWaring();

        $data = $model->update($data);

        return $data;

    }

    public static function delete($where)
    {

        $model = new BlockWaring();

        $res = $model->where($where)->delete();

        return $res;

    }

}