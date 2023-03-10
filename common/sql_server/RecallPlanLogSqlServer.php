<?php
/**
 * Created by PhpStorm.
 * User: crosstime
 * Date: 2018/8/14
 * Time: 下午5:31
 */
namespace common\sql_server;


use common\model\db_customer\RecallPlanLog;
use common\model\db_customer\RecallPlanPeopleLog;
use think\Db;


class RecallPlanLogSqlServer extends BaseSqlServer
{



    public static function insertInfo($data)
    {

        $time = time();
        $model = new RecallPlanLog();
        $is_exist = $model->where(['exec_date'=>date('Y-m-d'),'plan_id'=>$data['id']])->find();
        $is_exist = isset($is_exist) ? $is_exist->toArray() : [];
        if(empty($is_exist)){
            $res = $model->allowField($model->allow_field)->save($data);
        }else{
            $res = false;
        }

        return $res;
    }



    public static function getInfo($where)
    {
        $model = new RecallPlanLog();
        $res = $model->where($where)->select();

        $res = isset($res) ? $res->toArray() : [];
        return $res;
    }


    public static function updateSendNum()
    {
        $model = new RecallPlanLog();

        $res = $model->where("have_send < should_be_send or should_be_send = 0")->select();

        $res = isset($res) ? $res->toArray() : [];

        foreach($res as $k=>$v){
            $res = RecallPlanPeopleLogSqlServer::getSendCount($v['id']);

            if(!empty($res)){
                $model->isUpdate(1)->update(['should_be_send'=>$res[0]['should_be_send'],'have_send'=>$res[0]['have_send']],['id'=>$v['id']]);
            }

        }

    }

    public static function updateReBackNum()
    {
        $model = new RecallPlanLog();

        $res = $model->select();

        $res = isset($res) ? $res->toArray() : [];

        foreach($res as $k=>$v){

            $res = RecallPlanPeopleLogSqlServer::getReBackCount($v['id']);

            if(!empty($res)){
                $model->isUpdate(1)->update(['recall_num'=>$res],['id'=>$v['id']]);
            }

        }

    }


    public static function updateSendStatus()
    {
        $model = new RecallPlanLog();

        $res = $model->where("have_send = should_be_send and status = 1 and have_send != 0 and should_be_send != 0")->select();

        $res = isset($res) ? $res->toArray() : [];

        foreach($res as $k=>$v){

            $model->isUpdate(1)->update(['status'=>2],['id'=>$v['id']]);

        }


    }




}