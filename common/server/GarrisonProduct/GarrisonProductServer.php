<?php
/**
 * Created by PhpStorm.
 * User: crosstime
 * Date: 2018/8/14
 * Time: 下午5:31
 */
namespace common\server\GarrisonProduct;


use common\base\BasicServer;
use common\sql_server\GarrisonProductSqlServer;
use common\sql_server\GameProductSqlServer;


class GarrisonProductServer extends BasicServer
{


    public static function getGarrisonProductList($limit = 50,$page = 1,$order = 'id desc'){

        $condition = '1=1 ';

        $garrison_product_list = GarrisonProductSqlServer::getGarrisonProductList($condition,$limit,$page,$order);

        foreach ($garrison_product_list as &$v) {

            $v['add_time']   = date("Y-m-d H:i:s",$v['add_time']);
            $v['status']   = $v['status']?'开启':'关闭';

        }

        return $garrison_product_list;
    }


    public static function getGarrisonProductCount(){

        $condition = '1=1 ';

        $garrison_product_count = GarrisonProductSqlServer::getGarrisonProductCount($condition);

        $ret = $garrison_product_count;

        return $ret;
    }


    public static function add($data)
    {
        $res = GameProductSqlServer::getOne($data['product_id'],$data['platform_id']);
        $time = time();
        $add_data =   [
            "product_id"      => $data['product_id'],
            "product_name"    => $res['product_name'],
            "platform_id"     => $data['platform_id'],
            "admin_id"        => $data['admin_user'],
            "admin_name"      => $data['admin_name'],
            "update_time"     => $time,
            "add_time"        => $time,
            "garrison_period" => $data['garrison_period'],
            "status"          => $data['status']

        ];
        $res = GarrisonProductSqlServer::add($add_data);

        return $res;

    }
    public static function edit($data)
    {
        $res = GarrisonProductSqlServer::getOne($data['id']);
        $game_info = GameProductSqlServer::getOne($data['product_id'],$data['platform_id']);


        $update_data['id'] = $data['id'];
        $update_data['product_id'] =  empty($data['product_id']) ? $res['product_id'] : $game_info['product_id'];
        $update_data['product_name'] = empty($data['product_id']) ? $res['product_name'] : $game_info['product_name'];
        $update_data['platform_id'] = empty($data['platform_id']) ? $res['platform_id'] : $data['platform_id'];
        $update_data['admin_id'] = $data['admin_user'];
        $update_data['admin_name'] = $data['admin_name'];
        $update_data['update_time'] = time();
        $update_data['garrison_period'] = empty($data['garrison_period']) ? $res['garrison_period'] : $data['garrison_period'];
        $update_data['status'] = $data['status'] == $res['status'] ? $res['status'] : $data['status'];

        $res = GarrisonProductSqlServer::edit($update_data);

        return $res;
    }


    public static function delete($id)
    {
        $res = GarrisonProductSqlServer::delete($id);

        return $res;
    }

    public static function getOne($id)
    {
        $res = GarrisonProductSqlServer::getOne($id);
        return $res;
    }

    public static function changeStatus($id,$status)
    {
        $update_data['id'] = $id;
        $update_data['status'] = $status;
        $res = GarrisonProductSqlServer::edit($update_data);
        return $res;
    }

    public static function getGarrisonProductServer($platform_list = []){
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
            $res = self::excuteSQL($sql);


            $res = empty($res) ? [] : $res->toArray();
            $data[$k][] = $res;
        }


        return $data;
//        echo '<pre>';
//        var_dump($data);exit;



//

    }


    public static function getGarrisonGameServer($platform_list = []){
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
            $res = self::excuteSQL($sql);


            $res = empty($res) ? [] : $res->toArray();
            $data[$k][] = $res;
        }


        return $data;
//        echo '<pre>';
//        var_dump($data);exit;



//

    }



}