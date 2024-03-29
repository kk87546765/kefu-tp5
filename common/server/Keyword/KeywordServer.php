<?php
/**
 * Created by PhpStorm.
 * User: crosstime
 * Date: 2017/10/12
 * Time: 上午11:57
 */
namespace common\server\Keyword;


use common\base\BasicServer;
use common\server\Platform\{BanServer,Platform};
use common\Libraries\Common;
use common\libraries\PinYin;
use common\sql_server\{KeywordSqlServer};
use think\Config;


class KeywordServer extends BasicServer
{

    public static function getList($data)
    {

        $data = self::dealData($data);
        $info = KeywordSqlServer::getList($data['where'],$data['offset'],$data['limit'],$data['order']);

        return $info;
    }


    public static function getCount($data)
    {
        $data = self::dealData($data);

        return KeywordSqlServer::getCount($data['where']);
    }

    public static function add($data)
    {
        if ($data['keywords']) {
            $keywords = explode("\n", $data['keywords']);

            $pinyin = new Pinyin();

            $resemble_word_arr = [];

            foreach ($keywords as $keyword) {

                $add_data['game']     = $data['game'];
                $add_data['keyword']  = trim( $keyword);
                $add_data['addtime']  = time();
                $add_data['add_user'] =  $data['admin_user'];
                $add_data['level_min'] = $data['level_min'];
                $add_data['level_max'] = $data['level_max'];
                $add_data['money_min'] = $data['money_min'];
                $add_data['money_max'] = $data['money_max'];
                $add_data['num'] =  $data['num'];
                $add_data['status'] =  $data['status'] == 1 ? 1 : 0;
                $add_data['resemble_status'] = empty( $data['resemble_status']) ? 0 :  $data['resemble_status'];
                $add_data['type'] = empty($type) ? 1 : $type;

                $res = KeywordSqlServer::add($add_data);

                if( $res ) {
                    $keyword_key = Config::get('keyword_key')['keyword_key'];

                    $t_keyword = urldecode(str_replace(array(".","+"), array("",""), urlencode($keyword)));
                    $redis = get_redis();

                    $redis->sadd($keyword_key['block_keyword_key'].'_'.$data['game'],$t_keyword );

                    //加入谐音集合
                    if($data['resemble_status'] == 1){
                        $resemble_word = str_replace(array("."," "), array("", ""),$pinyin->main($keyword,true,true));
                        $redis->sadd( $keyword_key['block_resemble_key'].'_'.$data['game'],$resemble_word );
                        $resemble_word_arr[] = $resemble_word;
                    }

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

                $pinyin = new Pinyin();

                $resemble_word_arr = [];

                foreach ($keywords as $keyword) {
                    $old_info = KeywordServer::getOne($data['id']);

                    $edit_data['game'] = $data['game'];
                    $edit_data['id'] = $data['id'];
                    $edit_data['keyword'] = trim($keyword);
                    $edit_data['addtime'] = time();
                    $edit_data['add_user'] = $data['admin_user'];
                    $edit_data['level_min'] = $data['level_min'];
                    $edit_data['level_max'] = $data['level_max'];
                    $edit_data['money_min'] = $data['money_min'];
                    $edit_data['money_max'] = $data['money_max'];
                    $edit_data['num'] = $data['num'];
                    $edit_data['status'] = $data['status'] == 1 ? 1 : 0;
                    $edit_data['resemble_status'] = empty($data['resemble_status']) ? 0 : $data['resemble_status'];
                    $edit_data['type'] = empty($type) ? 1 : $type;

                    $res = KeywordSqlServer::edit($edit_data);

                    if ($res !== false) {
                        $keyword_key = Config::get('keyword_key')['keyword_key'];

                        $redis = get_redis();
                        $t_keyword = urldecode(str_replace(array(".", "+"), array("", ""), urlencode($old_info['keyword'])));
                        $redis->srem($keyword_key['block_keyword_key'].'_'.$data['game'],$t_keyword);

                        $t_keyword = urldecode(str_replace(array(".", "+"), array("", ""), urlencode($keyword)));
                        $redis->sadd($keyword_key['block_keyword_key'] . '_' . $data['game'], $t_keyword);

                        //加入谐音集合
                        if ($data['resemble_status'] == 1) {

                            $redis->srem( $keyword_key['block_resemble_key'].'_'.$data['game'],str_replace(array("."," "), array("", ""),$pinyin->main($old_info['keyword'],true,true)));

                            $resemble_word = str_replace(array(".", " "), array("", ""), $pinyin->main($keyword, true, true));
                            $redis->sadd($keyword_key['block_resemble_key'] . '_' . $data['game'], $resemble_word);
                            $resemble_word_arr[] = $resemble_word;
                        }

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
        $keywords = KeywordSqlServer::getOne($id);

        return $keywords;
    }

    public static function getMore($ids)
    {
        $ids = is_array($ids) ? implode(',',$ids) : '';
        $keywords = KeywordSqlServer::getMore($ids);

        return $keywords;
    }

    public static function getOneByWhere($where)
    {

        $keywords = KeywordSqlServer::getOneByWhere($where);

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

            $redis->srem($keyword_key['block_keyword_key'].'_'.$v['game'],$t_keyword);

            $redis->srem( $keyword_key['block_resemble_key'].'_'.$v['game'],str_replace(array("."," "), array("", ""),$pinyin->main($v['keyword'],true,true)));

            $delete_ids[] = $v['id'];

        }

        if(!empty($delete_ids)){

            $str_ids = implode(',',$delete_ids);

            $delete_where =  "id in ({$str_ids})";

           $res = KeywordSqlServer::delete($delete_where);
           return $res;
        }

    }

    private static function dealData($data)
    {
        $where = '1=1 ';
        if ( $data['game'] ) {
            $where .= " and game = '{$data['game']}'";
        }

        if ( $data['keyword'] ) {
            $where .= " and keyword like '{$data['keyword']}%'";
        }

        $new_data['where']  = $where;
        $new_data['offset'] = $data['offset'] ?? 0;
        $new_data['limit']  = $data['limit'] ?? 20;
        $new_data['order']  = $data['order'] ?? 'id desc';

        return $new_data;
    }

    public static function modiftKeywordList()
    {
        $res = KeywordSqlServer::getList('1=1');

        $product_list = Common::getProductList(1);

        $keyword_key = Config::get('keyword_key')['keyword_key'];
        $redis = get_redis();
        foreach($product_list as $k=>$v){

            if(!empty($v['code'])){
                $redis->del( $keyword_key['block_keyword_key'].'_'.$v['code'] );
                $redis->del( $keyword_key['block_resemble_key'].'_'.$v['code'] );
            }

        }

        foreach( $res as $v ){

            if(!empty($v['game'])){
                $t_content = urldecode(str_replace(array(".","+"), array("",""), urlencode($v['keyword'])));
                $redis->sadd($keyword_key['block_keyword_key'].'_'.$v['game'],$t_content );
            }

        }
        $res1 = KeywordSqlServer::getList('resemble_status = 1');

        foreach( $res1 as $v ){
            $pinyin = new PinYin();
            $redis->sadd($keyword_key['block_resemble_key'].'_'.$v['game'],str_replace(array("."," "), array("", ""),$pinyin->main($v['keyword'],true,true)) );
        }
    }




}