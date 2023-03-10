<?php
/**
 * Created by PhpStorm.
 * User: crosstime
 * Date: 2018/8/14
 * Time: 下午5:31
 */
namespace common\sql_server;

use common\model\gr_chat\RoleNameBlockWaring;
class RoleNameBlockWaringSqlServer extends BaseSqlServer
{

    public static function getList($where,$offset=0,$limit=100000,$order = 'id asc',$field = '*')
    {
        $model = new RoleNameBlockWaring();
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
        $model = new RoleNameBlockWaring();

        $data = $model->where($where)->count();

        return $data;
    }

    public static function getOne($id)
    {
        $model = new RoleNameBlockWaring();

        $data = $model->where("id = {$id}")->find();

        $data = isset($data) ? $data->toArray() : '';
        return $data;
    }

    public static function insert($data)
    {

        $model = new RoleNameBlockWaring();

        $res = $model->where(['uid'=>$data['uid'],'role_id'=>$data['role_id'],'keyword'=>$data['tmp_keyword'],'status'=>0])->find();
       if(isset($res)){
           return false;
       }

        $sql = "insert into gr_role_name_block_waring(`gkey`,`platform_id`,`platform`,`uname`,`reg_gid`,`reg_channel_id`,`server_id`,`uid`,`role_name`,`role_id`,`role_level`,`keyword_id`,`keyword`,
                `block_type`,`count_money`,
                `check_type`,
                `time`
                )values";
        $sql .=
            "('{$data['gkey']}',".
            "'{$data['platform_id']}',".
            "'{$data['platform']}',".
            "'{$data['uname']}',".
            "'{$data['reg_gid']}',".
            "'{$data['reg_channel']}',".
            "'{$data['server_id']}',".
            "'{$data['uid']}',".
            "'{$data['role_name']}',".
            "'{$data['role_id']}',".
            "'{$data['role_level']}',".
            "{$data['hit_keyword_id']},".
            "'{$data['tmp_keyword']}',".
            "'{$data['block_type']}',".
            "'{$data['count_money']}',".
            "{$data['check_type']},".
//            "'{$data['ext']}',".
            "{$data['time']})";


        $ret = $model->execute($sql);
        return $ret;
    }

    public static function edit($data)
    {
        $model = new RoleNameBlockWaring();

        $data = $model->update($data);

        return $data;

    }

    public static function delete($where)
    {

        $model = new RoleNameBlockWaring();

        $res = $model->where($where)->delete();

        return $res;

    }

}