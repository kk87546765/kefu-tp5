<?php
/**
 * Created by PhpStorm.
 * User: crosstime
 * Date: 2018/8/14
 * Time: 下午5:31
 */
namespace common\sql_server;

use common\model\gr_chat\MonitoringUid;
class MonitoringUidSqlServer extends BaseSqlServer
{

    public static function getList($where,$offset=0,$limit=100000,$order = 'id asc')
    {
        $model = new MonitoringUid();
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
        $model = new MonitoringUid();

        $data = $model->where($where)->count();

        return $data;
    }

    public static function getOne($id)
    {
        $model = new MonitoringUid();

        $data = $model->where("id = {$id}")->find();

        $data = isset($data) ? $data->toArray() : '';
        return $data;
    }

    public static function getMore($ids)
    {
        $model = new MonitoringUid();

        $data = $model->where("id in( {$ids})")->select();

        $data = isset($data) ? $data->toArray() : '';
        return $data;
    }

    public static function add($data)
    {
        $model = new MonitoringUid();

        $data = $model->insert($data);

        return $data;
    }

    public static function edit($data)
    {
        $model = new MonitoringUid();

        $data = $model->update($data);

        return $data;

    }
    public static function delete($where)
    {
        $model = new MonitoringUid();

        $res = $model->where($where)->delete();

        return $res;
    }

    public static function getOneByWhere($where)
    {
        $model = new MonitoringUid();

        $data = $model->where($where)->find();

        $data = isset($data) ? $data->toArray() : '';

        return $data;
    }
}