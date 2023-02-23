<?php
/**
 * Created by PhpStorm.
 * User: crosstime
 * Date: 2017/10/12
 * Time: 上午11:57
 */
namespace common\server\ActionBlock;

use common\base\BasicServer;
use common\sql_server\ActionBlockSqlServer;


class ActionBlockServer extends BasicServer
{
    public static $return = ['status'=>false,'msg'=>''];

    /***
     *获取数据列表
    */
    public static function getList($data){

        $action_blocks = ActionBlockSqlServer::getList([
            'conditions'=>isset($data['conditions']) ? $data['conditions'] : '',
            'limit'  => $data['limit'] ,
            'offset' => ($data['page'] - 1) * $data['limit'] ,
            'order'  => 'id desc'
        ]);

        return $action_blocks;
    }

    /***
     *获取总数
     */
    public static function getCount(){

        $count = ActionBlockSqlServer::getCount();

        return $count;
    }


    public static function add($data){
        $res = ActionBlockSqlServer::add($data);
        self::$return['status'] = $res ? 0 : -1;
        if($res === false){
            self::$return['msg'] = '添加失败';
        }else{
            self::$return['msg'] = '添加成功';
        }
        return self::$return;
    }

    public static function edit($data){


        if (empty($data['id'])) {
            self::$return['status'] = false;
            self::$return['msg'] = '操作失败';
            return self::$return;
        }


        if($data['id']) {
            //使用返回的模型对象调用 update() 函数执行更新操作

            $updateData = [];
            foreach ($data as $k=>$v) {
                $updateData[$k] = $v;
            }

            $result = ActionBlockSqlServer::update($updateData);

            if($result){
                self::$return['code'] = 0;
                self::$return['msg'] = '操作成功';
            }else{
                self::$return['code'] = false;
                self::$return['msg'] = '操作失败';
            }
        }

        return self::$return;
    }

    public static function getById($id){
        $info = ActionBlockSqlServer::getOne(['id'=>$id]);
        return $info;
    }

    public static function del($ids){

        $res = ActionBlockSqlServer::deleteBlock($ids);
        if($res){
            self::$return['code'] = 0;
        }else{
            self::$return['code'] = -1;
        }

        if ($res === false) {
            self::$return['msg'] = '操作失败';
        } else {
            self::$return['msg'] = '操作成功';
        }

        return self::$return;

    }

    public static function updateStatus($status){

        $res = ActionBlockSqlServer::UpdateActionBlockStatus($status);

        if ($res === false) {
            self::$return['code'] = -1;
            self::$return['msg'] = '操作失败';
        } else {
            self::$return['code'] = 0;
            self::$return['msg'] = '操作成功';
        }

        return self::$return;

    }

    public static function getOneByWhere($where)
    {

       $data = ActionBlockSqlServer::getOne($where);

        return $data;
    }

    public static function getAllByWhere($where)
    {

        $data = ActionBlockSqlServer::getList(['conditions'=>$where]);

        return $data;
    }



}