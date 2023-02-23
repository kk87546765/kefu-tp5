<?php
/**
 * Created by PhpStorm.
 * User: crosstime
 * Date: 2018/8/14
 * Time: 下午5:31
 */
namespace common\sql_server;
use common\model\gr_chat\WhiteId;

class WhiteIdSqlServer extends BaseSqlServer
{

    public static function add($data)
    {
        $model = new WhiteId();

        $res = $model->insert($data);

        return $res;
    }


    public static function edit($data)
    {
        $model = new WhiteId();
        $id = $data['id'] ?: '';
        $res = false;
        if($id){
            unset($data['id']);
            $res = $model->isUpdate(true)->save($data,['id'=>$id]);
        }

        return $res;
    }


    public static function del($id)
    {
        $model = new WhiteId();

        $res = $model->where("id = {$id}")->delete();

        return $res;
    }

    public static function getList($where = [],$offset = 0 ,$limit = 20,$order = 'id desc')
    {
        $model = new WhiteId();
        $white_id_list = $model->where($where)->limit($offset.",".$limit)->order($order)->select();

        $white_id_list = isset($white_id_list) ? $white_id_list->toArray() : [];

        return $white_id_list;
    }

    public static function getCount($where)
    {
        $model = new WhiteId();
        $count = $model->where($where)->count();
        return $count;
    }

    public static function getOne($where)
    {
        $model = new WhiteId();
        $info = $model->where($where)->find();
        return $info;
    }

}