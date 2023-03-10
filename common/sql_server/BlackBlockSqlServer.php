<?php
/**
 * Created by PhpStorm.
 * User: crosstime
 * Date: 2018/8/14
 * Time: 下午5:31
 */
namespace common\sql_server;

use common\model\gr_chat\BlackBlock;
use common\server\CustomerPlatform\CommonServer;

class BlackBlockSqlServer extends BaseSqlServer
{

    public static function getList($where,$page=1,$limit=100000,$order = 'id desc')
    {
        $offset = ($page-1)*$limit;
        $model = new BlackBlock();
        if(isset($offset) && isset($limit)){
           if(isset($where['phone'])){
               $where = "a.phone = '{$where['phone']}'";
           }
            $data = $model->field('a.*  ,b.realname as block_admin,c.realname as unblock_admin')->alias('a')->join('gr_admin b', ' a.block_admin_id=b.id','left')->join('gr_admin c', ' a.unblock_admin_id=c.id','left')->where($where)->limit($offset,$limit)->order($order)->select();

        }else{

            $data = $model->where($where)->order($order)->select();
        }


        $data = isset($data) ? $data->toArray() : [];

       return $data;
    }

    public static function getBlackList($platform,$where,$page=1,$limit=100000,$order = 'id asc')
    {

        $offset = ($page-1)*$limit;
        $model = CommonServer::getPlatformModel('KefuCommonMember',$platform);
        if(!empty($where['mobile'])){
            $group = 'mobile';
        }
        if(!empty($where['id_card'])){
            $group = 'id_card';
        }
        if(!empty($where['udid'])){
            $group = 'udid';
        }
        $data = $model->table("db_customer_{$platform}.kefu_common_member")->field("mobile,udid,id_card")->where($where)->limit($offset,$limit)->order($order)->group($group)->select();

        $data = isset($data) ? $data->toArray() : [];

        return $data;
    }

    public static function getBlackListCount($platform,$where)
    {

        $model = CommonServer::getPlatformModel('KefuCommonMember',$platform);
        $data = $model->where($where)->count();

        return $data;
    }




    public static function getCount($where)
    {
        $model = new BlackBlock();

        $data = $model->where($where)->count();

        return $data;
    }

//    public static function getOne($id)
//    {
//        $model = new BlockWaring();
//
//        $data = $model->where("id = {$id}")->find();
//
//        $data = isset($data) ? $data->toArray() : '';
//        return $data;
//    }



    public static function add($data)
    {

        $model = new BlackBlock();
        $res = $model->saveAll($data);
        return $res;
    }

    public static function edit($data)
    {
        $model = new BlackBlock();

        $data = $model->update($data);

        return $data;

    }

    public static function delete($where)
    {

        $model = new BlackBlock();

        $res = $model->where($where)->delete();

        return $res;

    }

}