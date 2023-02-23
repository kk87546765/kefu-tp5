<?php
/**
 * Created by PhpStorm.
 * User: crosstime
 * Date: 2017/10/12
 * Time: 上午11:57
 */
namespace common\server\RoleNameKeyword;


use common\base\BasicServer;
use common\server\Platform\{BanServer,Platform};
use common\libraries\Common;
use common\libraries\Dingding;
use common\libraries\PinYin;
use common\server\Game\BlockServer;
use common\server\RoleNameBlock\RoleNameBlockServer;

use common\server\Sdk\UserServer as sdkUserServer;
use common\server\SysServer;
use common\sql_server\{RoleNameBlockWaringSqlServer, RoleNameKeywordSqlServer};
use think\Config;


class RoleNameKeywordServer extends BasicServer
{

    public static function getList($data)
    {

        $data = self::dealData($data);
        $info = RoleNameKeywordSqlServer::getList($data['where'],$data['offset'],$data['limit'],$data['order']);

        return $info;
    }


    public static function getCount($data)
    {
        $data = self::dealData($data);

        return RoleNameKeywordSqlServer::getCount($data['where']);
    }

    public static function add($data)
    {
        if ($data['keywords']) {
            $keywords = explode("\n", $data['keywords']);

            $pinyin = new Pinyin();

            $resemble_word_arr = [];

            foreach ($keywords as $keyword) {

                $add_data['game']       = $data['game'];
                $add_data['keyword']    = trim( $keyword);
                $add_data['addtime']    = time();
                $add_data['add_user']   =  $data['admin_user'];
                $add_data['level_min']  = $data['level_min'];
                $add_data['level_max']  = $data['level_max'];
                $add_data['money_min']  = $data['money_min'];
                $add_data['money_max']  = $data['money_max'];
                $add_data['num']        = $data['num'];
                $add_data['check_type'] = !empty($data['check_type']) ? $data['check_type'] : 2;
                $add_data['status']     = $data['status'] == 1 ? 1 : 0;

                $add_data['type']            = empty($data['type']) ? 1 : $data['type'];
                $add_data['block_time']      = !empty($data['block_time']) ? $data['block_time']*3600 : 86400*365;
                $add_data['ban_time']        = !empty($data['ban_time']) ? $data['ban_time']*3600 : 86400*10;

                $res = RoleNameKeywordSqlServer::add($add_data);

                if( $res ) {
                    $keyword_key = Config::get('keyword_key')['keyword_key'];

                    $t_keyword = urldecode(str_replace(array(".","+"), array("",""), urlencode($keyword)));
                    $redis = get_redis();

                    $redis->sadd($keyword_key['rolename_block_keyword_key'].'_'.$data['game'].'_'.$data['check_type'],$t_keyword );


                }
            }
//            $msg2 = '';
//            if($data['resemble_status'] == 1)$msg2 = '生成的谐音为：'.implode(',',$resemble_word_arr);


            return true;
        } else {

            return false;
        }

        return false;
    }

    public static function edit($data)
    {
        if ($data['id']) {

            if ($data['keywords']) {
                $keywords = explode("\n", $data['keywords']);


                $resemble_word_arr = [];

                foreach ($keywords as $keyword) {
                    $old_info = RoleNameKeywordSqlServer::getOne($data['id']);

                    $edit_data['game'] = $data['game'];
                    $edit_data['id']         = $data['id'];
                    $edit_data['keyword']    = trim($keyword);
                    $edit_data['addtime']    = time();
                    $edit_data['add_user']   = $data['admin_user'];
                    $edit_data['level_min']  = $data['level_min'];
                    $edit_data['level_max']  = $data['level_max'];
                    $edit_data['money_min']  = $data['money_min'];
                    $edit_data['money_max']  = $data['money_max'];
                    $edit_data['num']        = $data['num'];
                    $edit_data['check_type']  = !empty($data['check_type']) ? $data['check_type'] : 2;

                    $edit_data['status'] = $data['status'] == 1 ? 1 : 0;

                    $edit_data['type'] = empty($data['type']) ? 1 : $data['type'];
                    $edit_data['block_time'] = !empty($data['block_time']) ? $data['block_time']*3600 : 86400*365;
                    $edit_data['ban_time'] = !empty($data['ban_time']) ? $data['ban_time']*3600 : 86400*10;

                    $res = RoleNameKeywordSqlServer::edit($edit_data);

                    if ($res !== false) {
                        $keyword_key = Config::get('keyword_key')['keyword_key'];

                        $redis = get_redis();
                        $t_keyword = urldecode(str_replace(array(".", "+"), array("", ""), urlencode($old_info['keyword'])));
                        $redis->srem($keyword_key['rolename_block_keyword_key'].'_'.$data['game'].'_'.$old_info['check_type'],$t_keyword);

                        $t_keyword = urldecode(str_replace(array(".", "+"), array("", ""), urlencode($keyword)));
                        $redis->sadd($keyword_key['rolename_block_keyword_key'] . '_' . $data['game'].'_'.$data['check_type'], $t_keyword);



                    }
                }
                return true;
            } else {

                return false;
            }
        }
    }

    public static function getOne($id)
    {
        $keywords = RoleNameKeywordSqlServer::getOne($id);

        return $keywords;
    }

    public static function getMore($ids)
    {
        $ids = is_array($ids) ? implode(',',$ids) : '';
        $keywords = RoleNameKeywordSqlServer::getMore($ids);

        return $keywords;
    }

    public static function getOneByWhere($where)
    {

        $keywords = RoleNameKeywordSqlServer::getOneByWhere($where);

        return $keywords;
    }

    public static function delete($keywords)
    {
        $redis = get_redis();
        $keyword_key = Config::get('keyword_key')['keyword_key'];
        $pinyin = new PinYin();
        $delete_ids = [];

        foreach( $keywords as $v ){

            $t_keyword = urldecode(str_replace(array(".","+"), array("",""), urlencode($v['keyword'])));

            $redis->srem($keyword_key['rolename_block_keyword_key'].'_'.$v['game'].'_1',$t_keyword);
            $redis->srem($keyword_key['rolename_block_keyword_key'].'_'.$v['game'].'_2',$t_keyword);


            $delete_ids[] = $v['id'];

        }

        if(!empty($delete_ids)){

            $str_ids = implode(',',$delete_ids);

            $delete_where =  "id in ({$str_ids})";

           $res = RoleNameKeywordSqlServer::delete($delete_where);
           return $res;
        }

    }

    private static function dealData($data)
    {

        $where = '1=1 ';
        if ( !empty($data['game']) ) {
            $where .= " and game = '{$data['game']}'";
        }

        if ( !empty($data['keyword']) ) {
            $where .= " and keyword like '{$data['keyword']}%'";
        }

        if (!empty( $data['id']) ) {
            $where .= " and id = {$data['id']}";
        }


        $data['offset'] =  $data['limit'] * ($data['page']-1);
        $new_data['where']  = $where;
        $new_data['offset'] = $data['offset'] ?? 0;
        $new_data['limit']  = $data['limit'] ?? 20;
        $new_data['order']  = $data['order'] ?? 'id desc';

        return $new_data;
    }



    public static function modiftKeywordList()
    {
        $res = RoleNameKeywordSqlServer::getList('status= 1',0,100000,'block_time DESC,id desc');

        $product_list = Common::getProductList(1);

        $keyword_key = Config::get('keyword_key')['keyword_key'];
        $redis = get_redis();
        foreach($product_list as $k=>$v){

            if(!empty($v['code'])){
                $redis->del( $keyword_key['rolename_block_keyword_key'].'_'.$v['code'].'_1' );
                $redis->del( $keyword_key['rolename_block_keyword_key'].'_'.$v['code'].'_2' );
//                $redis->del( $keyword_key['rolename_block_resemble_key'].'_'.$v['code'] );
            }

        }

        foreach( $res as $v ){

            if(!empty($v['game'])){
                $t_content = urldecode(str_replace(array(".","+"), array("",""), urlencode($v['keyword'])));
                $redis->sadd($keyword_key['rolename_block_keyword_key'].'_'.$v['game'].'_'.$v['check_type'],$t_content );
            }

        }

    }








}