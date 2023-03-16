<?php
/**
 * Created by PhpStorm.
 * User: crosstime
 * Date: 2018/8/14
 * Time: 下午5:31
 */
namespace common\sql_server;
use common\model\db_statistic\DistributionalServer;


class DistributionalServerSqlServer extends BaseSqlServer
{


    public static function getList($where = '',$limit = 50,$page = 1,$order = 'id desc')
    {

        $model = new DistributionalServer();
        $offset = ($page- 1) * $limit;

        $sql = "select distributional_server.*,b.product_name,b.platform_id,b.garrison_period 
                from distributional_server  
                left join garrison_product b 
                on distributional_server.garrison_product_id = b.id 
                where {$where}  limit {$offset},{$limit} ";
        $res = $model->query($sql);

        return $res;
    }

    public static function getCount($where)
    {
        $model = new DistributionalServer();

        $sql = "select count(*)as `count` from distributional_server  left join garrison_product b on distributional_server.garrison_product_id = b.id where {$where}  ";

        $count = $model->query($sql);

        $ret = empty($count) ? 0 : $count[0]['count'];

        return $ret;
    }


    public static function getOne($id)
    {

        $sql = "select a.*,b.product_name,b.platform_id,b.garrison_period from distributional_server a left join garrison_product b on a.garrison_product_id = b.id where a.id = {$id} limit 1";
        $model = new DistributionalServer();

        $info = $model->query($sql);

        return $info[0];
    }


    public static function getGarrisonProductServer($platform_list = [])
    {

        $model = new DistributionalServer();

        $data = [];
        //按平台分配
        foreach($platform_list as $k=>$v){
            if($k == 'asjd' ){
                continue;
            }

            $sql = "select a.*,c.server_id,c.server_name,c.open_time,c.gid,{$v['id']} as platform_id,b.product_id
                    from 
                    db_statistic.garrison_product a 
                    left join 
                    db_statistic.platform_game_info b on a.product_id = b.product_id 
                    and b.platform_id = a.platform_id
                    and b.platform_id={$v['id']}
                    left join 
                    db_customer_{$k}.kefu_server_list c on b.game_id = c.gid  
                    and c.platform_id = {$v['id']}                  
                    where
                    c.server_id IS NOT NULL
                    and
                    a.status = 1
                    group by 
                    b.product_id,c.server_id";
            $res = $model->execute($sql);


            $res = empty($res) ? [] : $res->toArray();
            $data[$k][] = $res;
        }


        return $data;
//        echo '<pre>';
//        var_dump($data);exit;



//

    }


    public static function getGarrisonGameServer($platform_list = []){
        $model = new DistributionalServer();
        $data = [];
        //按平台分配
        foreach($platform_list as $k=>$v){
            if($k == 'asjd' ){
                continue;
            }

            $sql = "select a.*,c.server_id,c.server_name,c.open_time,c.gid,{$v['id']} as platform_id,b.product_id
                    from 
                    db_statistic.garrison_product a 
                    left join 
                    db_statistic.platform_game_info b on a.product_id = b.product_id 
                    and b.platform_id = a.platform_id
                    and b.platform_id={$v['id']}
                    left join 
                    db_customer_{$k}.kefu_server_list c on b.game_id = c.gid  
                    and c.platform_id = {$v['id']}                  
                    where
                    c.server_id IS NOT NULL
                    and
                    a.status = 1
                    group by 
                    b.product_id,c.gid,c.server_id";
            $res = $model->execute($sql);


            $res = empty($res) ? [] : $res->toArray();
            $data[$k][] = $res;
        }

        return $data;

    }


    public static function updateData($id,$update_data)
    {


        $model = new DistributionalServer();
        $res = $model->isUpdate(true)->save($update_data,['id'=>$id]);
        if($res !== false){
            return true;
        }else{
            return false;
        }
    }


    /**
     * @param $data
     * @return mixed
     */
    public static function insertInfo($data)
    {
        $model = new DistributionalServerSqlServer();
        foreach($data as $k1=>$v1){
            $res = $model->where("garrison_product_id={$v1['id']} and server_id ='{$v1['server_id']}'")->count();
//            $res = self::count(['conditions'=>"garrison_product_id={$v1['id']} and server_id ='{$v1['server_id']}'"]);
            if($res){
                unset($data[$k1]);
            }
        }
        if(empty($data)){
            return true;
        }

        $time = time();
        $sql = "insert into distributional_server(`garrison_product_id`,`product_id`,`garrison_product_name`,`platform_id`,`server_id`,`server_name`,`role_id`,`role_name`,`admin_id`,`admin_name`,`add_time`,`start_time`,`end_time`,`open_time`,`is_end`,`status`) values";
        foreach( $data as $v ){

            $sql .= "({$v['id']},".
                "{$v['product_id']},".
                "'{$v['product_name']}',".
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





}