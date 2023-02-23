<?php
/**
 * Created by PhpStorm.
 * User: crosstime
 * Date: 2018/8/14
 * Time: 下午5:31
 */
namespace common\sql_server;

use common\libraries\Common;
use common\model\db_statistic\DistributionalGameServer;
use think\Db;

class DistributionalGameServerSqlServer extends BaseSqlServer
{


    /**
     * @param $data
     * @return mixed
     */
    public static function insertInfo($data)
    {
        $model = new DistributionalGameServer();
        foreach($data as $k1=>$v1){
            $res = $model->where("garrison_product_id={$v1['id']} and  game_id = {$v1['gid']} and server_id ='{$v1['server_id']}'")->count();
            if($res){
               unset($data[$k1]);
            }
        }
        if(empty($data)){
            return true;
        }

        $time = time();
        $sql = "insert into distributional_game_server(`garrison_product_id`,`product_id`,`garrison_product_name`,`game_id`,`platform_id`,`server_id`,`server_name`,`role_id`,`role_name`,`admin_id`,`admin_name`,`add_time`,`start_time`,`end_time`,`open_time`,`is_end`,`status`) values";
        foreach( $data as $v ){

            $sql .= "({$v['id']},".
                "{$v['product_id']},".
                "'{$v['product_name']}',".
                "'{$v['gid']}',".
                "{$v['platform_id']},".
                "'{$v['server_id']}',".
                "'{$v['server_name']}',".
                "'{$v['role_id']}',".
                "'".addslashes($v['role_name'])."',".
                "'',".
                "'',".
                "{$time},".
                "'',".
                "'',".
                "'{$v['open_time']}',".
                "0,".
                "0),";

        }


        $sql = trim($sql,",");

        $res = $model->execute($sql);
        return $res;
    }


    /***
     *获取数据列表
     */
    public static function getList($data){

        $model = new DistributionalGameServer();
        $offset = ($data['page'] - 1) * $data['limit'];
        $limit = $data['limit'];

        $sql = "select distributional_game_server.*,b.product_name,c.platform_id,b.garrison_period,c.game_name       
                from distributional_game_server  
                left join garrison_product b 
                 on distributional_game_server.garrison_product_id = b.id 
                left join platform_game_info c 
                on distributional_game_server.game_id = c.game_id and distributional_game_server.platform_id = c.platform_id
                where {$data['where']}  limit {$offset},{$limit} ";

        $server_info_list = $model->query($sql);

        return $server_info_list;
    }


    public static function getCount($data){

        $model = new DistributionalGameServer();
        $sql = "select count(*)as `count` from distributional_game_server 
        left join garrison_product b on distributional_game_server.garrison_product_id = b.id where {$data['where']}  ";

        $count = $model->query($sql);

        return $count[0]['count'];
    }


    /***
     *获取数据列表
     */
    public static function getOne($id){

        $model = new DistributionalGameServer();
        $sql = "select a.*,b.product_name,b.platform_id,b.garrison_period from distributional_game_server a left join garrison_product b on a.garrison_product_id = b.id where a.id = {$id} limit 1";

        $server_info_list = $model->query($sql);

        return $server_info_list[0];
    }

    /***
     *获取数据列表
     */
    public static function getOneByWhere($where){

        $model = new DistributionalGameServer();
        $sql = "select a.*,b.product_name,b.platform_id,b.garrison_period from distributional_game_server a left join garrison_product b on a.garrison_product_id = b.id where {$where} limit 1";

        $server_info_list = $model->query($sql);
        $server_info_list = isset($server_info_list) ? $server_info_list->toArray() : [];

        return $server_info_list[0];
    }



    public static function getUserRoleLastLoginTime($data){


        if(empty($data['role_id']) || empty($data['server_id'])){
            return false;
        }
        $platform = Common::getPlatformList();

        $platform_suffix = '';
        foreach($platform as $k=>$v){
            if($v['platform_id'] == $data['platform_id']){
                $platform_suffix = $v['platform_suffix'];
            }
        }
        if(!$platform_suffix){
            return false;
        }

        //通过角色id和区服id获取信息，有可能不是唯一，所以通过游戏id核对产品id
        $sql = "select login_date,role_name,server_id,reg_gid from db_customer_{$platform_suffix}.kefu_user_role where role_id = '{$data['role_id']}' and server_id = '{$data['server_id']}'";

        $res = Db::query($sql);
        if(isset($res[0]['login_date'])){
            $res = $res[0]['login_date'];
        }else{
            $res = [];
        }
        return $res;
    }

    public static function updateData($id,$update_data)
    {


        $model = new DistributionalGameServer();
        $res = $model->isUpdate(true)->save($update_data,['id'=>$id]);
        if($res !== false){
            return true;
        }else{
            return false;
        }
    }

}