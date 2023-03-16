<?php

namespace common\sql_server;

use common\model\gr_chat\Sensitive;

class SensitiveSqlServer extends BaseSqlServer
{
    public static function getList($data)
    {
        $model = new Sensitive();

        $info = $model->where($data['where'])->order($data['order'])->limit($data['offset'].','.$data['limit'])->select();

        return isset($info) ? $info->toArray() : [];

    }

    public static function getOne($where)
    {
        $model = new Sensitive();
        $new_where = '1=1 ';
        if(is_array($where)){
            foreach($where as $k=>$v){
                $new_where .= "and ".$k."="."'{$v}'";
            }
        }{
        $new_where = $where;
    }

        $info = $model->where($new_where)->find();

        return $info;
    }

    public static function add($data)
    {
        $model = new Sensitive();

        $res = $model->insert($data);

        if($res){
            return true;
        }else{
            return false;
        }
    }

    public static function edit($data)
    {
        $model = new Sensitive();
        $id = $data['id'];

        unset($data['id']);
        $res = $model->isUpdate(true)->save($data,['id'=>$id]);
        if($res !== false){
            return true;
        }else{
            return false;
        }
    }

    public static function del($ids)
    {
        $model = new Sensitive();

        $res = $model->where("id in ({$ids})")->delete();

        return $res;
    }
}