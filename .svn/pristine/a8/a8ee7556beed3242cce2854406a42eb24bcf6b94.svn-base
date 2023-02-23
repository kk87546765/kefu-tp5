<?php


namespace common\sql_server;

use common\model\db_statistic\PlatformPaymentInfo;

class PaymentSqlServer extends BaseSqlServer
{


    /**
     * @param array $data
     * @return array
     */
    public static function getList($data=array())
    {
        $model = new PlatformPaymentInfo();
        $res = $model->where($data)->select();

        if (!empty($res)) $result = $res->toArray();
        return $result;
    }

    public static function add($data)
    {
        $model = new PlatformPaymentInfo();

        $res = $model->insert($data);

        if(!$res){
            return false;
        }
        return $model;
    }

    public static function del($id)
    {
        $model = new PlatformPaymentInfo();
        $res = $model->where("id={$id}")->delete();
        return $res;
    }

    public static function edit($data)
    {
        $model = new PlatformPaymentInfo();
        $res = false;
        if($data['id']){
            $id = $data['id'];
            unset($data['id']);
            $res = $model->isUpdate(true)->save($data,$id);
        }

        return $res;
    }

}