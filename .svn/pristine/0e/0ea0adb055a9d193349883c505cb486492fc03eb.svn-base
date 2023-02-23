<?php
/**
 * Created by PhpStorm.
 * User: crosstime
 * Date: 2018/8/14
 * Time: 下午5:31
 */
namespace common\sql_server;
use common\model\db_statistic\GarrisonProduct;


class GarrisonProductSqlServer extends BaseSqlServer
{


    public static function getGarrisonProductList($where = '',$limit = 50,$page = 1,$order = 'id desc'){
        $model = new GarrisonProduct();
        $garrison_product = $model->where($where)->limit(($page - 1) * $limit.','.$limit)->order($order)->select();
        $ret = empty($garrison_product) ? [] : $garrison_product->toArray();

        return $ret;
    }

    public static function getGarrisonProductCount($where = ''){
        $model = new GarrisonProduct();
        $count = $model->where($where)->count();

        $ret = empty($count) ? 0 : $count;

        return $ret;
    }


    public static function getOne($id)
    {
        $model = new GarrisonProduct();
        $info = $model->where("id = {$id}")->find();
        $ret = empty($info) ? [] : $info->toArray();
        return $ret;
    }

    public static function add($data)
    {
        $model = new GarrisonProduct;

        $res = $model->insert($data);

        return $res;
    }

    public static function edit($data)
    {
        $model = new GarrisonProduct();

        $res = $model->isUpdate(true)->save($data);

        return $res;
    }

    public static function delete($id)
    {
        $model = new GarrisonProduct();

        $res = $model->where("id={$id}")->delete();

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