<?php
/**
 * 系统
 */
namespace common\server\WhiteId;

use common\base\BasicServer;
use common\sql_server\WhiteIdSqlServer;
use common\libraries\Common;


class WhiteIdServer extends BasicServer
{

    public static $white_id = 'white_id';
    public static $return = ['code'=>1,'msg'=>'','data'=>[]];

    public static function getList($data)
    {

        $offset = ($data['page']-1)*$data['limit'];
        $white_id_list = WhiteIdSqlServer::getList(['status'=>1],$offset,$data['limit']);

        $list = Common::getProductList(2);

        foreach($white_id_list as &$v){
            $v['add_time'] = date('Y-m-d H:i:s' ,$v['add_time']);
            $v['game'] = $list[$v['game']]['name'];
        }

        self::$return['code'] = 0;
        self::$return['data']= $white_id_list;
        self::$return['msg']= '获取成功';

        return  self::$return;

    }



    public static function getCount()
    {
        $count = WhiteIdSqlServer::getCount(['status'=>1]);

        return $count;
    }


    public static function add($data)
    {

        if( !$data['uids'] ){
            self::$return['msg'] = '请输入用户uid！';
            return self::$return;
        }

        if( !$data['game'] ){
            self::$return['msg'] = '请输入游戏！';
            return self::$return;
        }

        $uid_arr = explode("\n",$data['uids']);

        $fail = [];
        $res = false;
        $redis = get_redis();
        foreach($uid_arr as $v){
            $data = [
                'uid' => $v,
                'game' => $data['game'],
                'add_time' => time(),
                'add_user' => self::$user_data['username'],
                'status' => 1,

            ];

            $res = WhiteIdSqlServer::add($data);
            if($res){

                $redis->SADD(self::$white_id.'_'.$data['game'],$v);
            }else{
                $fail[] = $v;
            }

        }

        self::$return['code'] = $res == true ? 0 : 1;
        self::$return['msg']= $res == true ? '添加成功' : '添加失败的有：'.implode(','.$fail);

        return  self::$return;
    }


    public static function edit($data)
    {
        if( !$data['uid'] ){
            self::$return['msg'] = '请输入用户uid！';
            return self::$return;
        }
        if( !$data['game'] ){
            self::$return['msg'] = '请输入游戏！';
            return self::$return;
        }

        $info = WhiteIdSqlServer::getOne(['id' => $data['id']]);

        $res = WhiteIdSqlServer::edit($data);
        if($res !== false && !empty($info)){
            $redis = get_redis();
            $exist_res = $redis->sismember(self::$white_id.'_'.$info['game'],$info['uid']);
            if($exist_res){
                $redis->srem(self::$white_id.'_'.$info['game'],$info['uid']);
                $redis->sadd(self::$white_id.'_'.$data['game'],$data['uid']);
            }
        }


        self::$return['code'] = $res !== false ? 0 : 1;
        self::$return['msg'] = $res !== false ? '修改成功' : '修改失败：';
        return self::$return;
    }

    public static function del($id)
    {
        if (empty($id)) {
            self::$return['msg'] = '参数错误！';
            return self::$return;
        }

        $info = WhiteIdSqlServer::getOne(['id' => $id]);

        $data['id'] = $id;
        $data['status'] = 0;
        $res = WhiteIdSqlServer::edit($data);

        if ($res !== false  && !empty($info)) {
            $redis = get_redis();
            $exist_res = $redis->sismember(self::$white_id . '_' . $info['game'], $info['uid']);
            if ($exist_res) {
                $redis->srem(self::$white_id . '_' . $info['game'], $info['uid']);
            }

        }

        self::$return['code'] = $res !== false ? 0 : 1;
        self::$return['msg'] = $res !== false ? '删除成功' : '删除失败：';
        return self::$return;
    }


    public static function getOne($id)
    {
        $info = WhiteIdSqlServer::getOne(['id' => $id]);
        self::$return['code'] = 0;
        self::$return['data'] = $info;
        self::$return['msg'] = '获取成功';
        return self::$return;
    }

    public static function reset()
    {
        $product_list = common::getProductList();
        $redis = get_redis();
        foreach($product_list as $k=>$v){

            //白名单
            $redis->del(self::$white_id.'_'.$v['code']);

        }

        $info = WhiteIdSqlServer::getList([ 'status' => 1],0,20000);

        foreach($info as $k=>$v){
            $redis->sadd(self::$white_id.'_'.$v['game'],$v['uid']);
        }

        self::$return['code'] = 0;
        self::$return['msg'] = '成功';
        return self::$return;

    }

    public static function show()
    {
        $product_list = common::getProductList();
        $redis = get_redis();
        foreach($product_list as $k=>$v){
            //白名单
            $list[$v['code']] = $redis->SMEMBERS(self::$white_id.'_'.$v['code']);

        }

        self::$return['code'] = 0;
        self::$return['msg'] = '成功';
        self::$return['data'] = $list;
        return self::$return;
    }


}