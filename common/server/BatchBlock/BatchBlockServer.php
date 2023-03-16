<?php


namespace common\server\BatchBlock;

use common\base\BasicServer;
use common\libraries\Common;
use common\sql_server\BatchBlockSqlServer;
//use common\server\Block\BlockServer;
use common\server\Game\BlockServer;



class BatchBlockServer extends BasicServer
{

    /***
     *获取数据列表
     */
    public static function getList($data){
        $gamelist = Common::getProductList(2);

        $where = self::dealData($data);

        $blocks = BatchBlockSqlServer::getList($where,$data['page'],$data['limit']);


        foreach ($blocks as &$block) {

            $block['game_name']            = $gamelist[$block['gkey']]['name'];
            $block['add_time']             = date('Y-m-d H:i:s',$block['add_time']);
            $block['expect_unblock_time']  = date('Y-m-d H:i:s',$block['expect_unblock_time']);
//            $block['unblock_time']         = $block['unblock_time'] == 0?'':date('Y-m-d H:i:s',$block['unblock_time']);

        }

        return $blocks;
    }

    /***
     *获取总数
     */
    public static function getCount($data){

        $tmp_data = self::dealData($data);

        $count = BatchBlockSqlServer::getCount($tmp_data);

        return $count;
    }

    private static function dealData($data){
        $condition = '1=1 ';
        $tmp_data = [];

        if($data['dateStart']){
            $data['dateStart'] = strtotime($data['dateStart']);
            $condition .= " AND add_time >= {$data['dateStart']}";
        }

        if($data['dateEnd']){
            $data['dateEnd'] = strtotime($data['dateEnd']);
            $condition .= " AND add_time < {$data['dateEnd']}";
        }

        if ( $data['game'] && $data['game']!='common' ) {
            $condition .= " AND gkey = '{$data['game']}'";
        }

        if ($data['server_id']) {
            $condition .=  " AND  server_id = '{$data['server_id']}'";
        }

        if ($data['role_id']) {
            $condition .= " AND role_id = '{$data['role_id']}'";
        }

        if ($data['type']) {
            $condition .= " AND type = {$data['type']}";
        }

        if ($data['op_admin']) {
            $condition .= " AND op_admin = '{$data['op_admin']}'";
        }

        return $condition;
    }


    public static function block($data)
    {
        $return = ['code'=>-1,'msg'=>'封禁失败'];
        $res = Common::getConfig('gamekey');

        if(!isset($res[$data['game']]['batch_block']) || $res[$data['game']]['batch_block'] != 1) {
            $return['msg'] = '该游戏未开通手动角色封禁';
            return $return;
        }

        $fail = [];
        $succ = [];
        $accounts = explode("\n",$data['account']);

        foreach($accounts as $v){
            @list($data['roleid'],$data['sid']) = explode('||',$v);

            if(empty($data['roleid'])){
                $return['msg'] = '请输入角色id信息';
                return $return;
            }

            if(empty($data['sid'])){
                $return['msg'] = '请输入区服id信息';
                return $return;
            }

            $res = BlockServer::BathBlock($data);

            if($res !== true){
                $fail[] = $v;
            }else{
                $succ[] = $v;
            }
        }
        $return['code'] = 0;
        $return['msg'] = '操作成功';
        $return['data']['fail'] = $fail;
        $return['data']['succ'] = $succ;
        return $return;
    }



}