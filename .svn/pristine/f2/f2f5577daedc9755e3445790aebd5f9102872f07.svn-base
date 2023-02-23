<?php


namespace common\server\CountBlock;

use common\base\BasicServer;
use common\libraries\Common;
use common\sql_server\CountBlockSqlServer;



class CountBlockServer extends BasicServer
{

    /***
     *获取数据列表
     */
    public static function getList($data){
        $gamelist = Common::getProductList(2);

        $tmp_data = self::dealData($data);

        $blocks = CountBlockSqlServer::getList($tmp_data);

        foreach ($blocks as $k=>&$v){
            $v['game_name'] = $gamelist[$v['gkey']]['name'];
        }

        return $blocks;
    }

    /***
     *获取总数
     */
    public static function getCount($data){

        $tmp_data = self::dealData($data);

        $count = CountBlockSqlServer::getCount($tmp_data);

        return $count;
    }

    private static function dealData($data){
        $condition = '1=1 ';
        $tmp_data = [];
        if($data['game']){
            $condition.= " and gkey = '{$data['game']}'";
        }
        if($data['op_admin_id']){
            $AdminServerModel = new AdminServer();
            $user_info = $AdminServerModel->getAdminInfoByWhere(['id'=>$data['op_admin_id']]);
            $condition .= " and op_admin_id = '{$user_info['username']}'";
        }
        if($data['s_time']){
            $data['s_time'] = strtotime($data['s_time']);


            $condition.= " and addtime >= {$data['s_time']}";
        }
        if($data['e_time']){
            $data['e_time'] = strtotime($data['e_time']);
            $condition.= " and addtime <= {$data['e_time']}";
        }
        $tmp_data['group'] = ' op_admin_id,gkey';
        $tmp_data['condition'] = $condition;

        $offset = ($data['page'] - 1) * $data['limit'] ;
        $tmp_data['offset'] = $offset;
        $tmp_data['limit'] = $data['limit'];

        return $tmp_data;
    }



}