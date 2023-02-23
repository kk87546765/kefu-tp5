<?php
/**
 * Created by PhpStorm.
 * User: crosstime
 * Date: 2018/8/14
 * Time: 下午5:31
 */
namespace common\server\PlatformProduct;


use common\base\BasicServer;
//use common\sql_server\GameProductSqlServer;
use common\libraries\Common;
use common\sql_server\GameProductSqlServer;


class PlatformProductServer extends BasicServer
{


    public static function getGameProductList($limit = 50,$page = 1,$order = 'id desc'){

        $condition = '1=1 ';
        $platform_list = Common::getPlatformList();
        $garrison_product_list = GameProductSqlServer::getPlatformProductList($condition,$limit,$page,$order);

        foreach ($garrison_product_list as &$v) {

            $v['platform'] = $platform_list[$v['platform_id']]['platform_name'];
            $v['static']   = $v['static']?'开启':'关闭';

        }

        return $garrison_product_list;
    }


    public static function getGameProductCount(){

        $condition = '1=1 ';

        $game_product_count = GameProductSqlServer::getPlatformProductCount($condition);

        $ret = $game_product_count;

        return $ret;
    }



    public static function edit($data)
    {

        $update_data['id'] = $data['id'];
        $update_data['customer_product_code'] = $data['product_code'];

        $res = GameProductSqlServer::edit($update_data);

        return $res;
    }






}