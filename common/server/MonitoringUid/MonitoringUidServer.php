<?php
/**
 * Created by PhpStorm.
 * User: crosstime
 * Date: 2017/10/12
 * Time: 上午11:57
 */
namespace common\server\MonitoringUid;


use common\base\BasicServer;
use common\libraries\Common;
use common\sql_server\{MonitoringUidSqlServer};
use think\Config;


class MonitoringUidServer extends BasicServer
{

    public static function getList($data)
    {

        $data = self::dealData($data);
        $info = MonitoringUidSqlServer::getList($data['where'],$data['offset'],$data['limit'],$data['order']);

        $platform_list = Common::getPlatform();

        foreach($info as $k=>&$v){
            $v['platform'] = $platform_list[$v['platform']]['name'];
            $v['addtime']  = !empty($v['addtime']) ? date('Y-m-d H:i:s',$v['addtime']) : '';
            $v['status']   = $v['status']?'开启':'关闭';
            $v['ban_time']   = $v['ban_time'] ? $v['ban_time']/3600 : 0;
        }

        return $info;
    }


    public static function getCount($data)
    {
        $data = self::dealData($data);

        return MonitoringUidSqlServer::getCount($data['where']);
    }

    public static function add($data)
    {
        if ($data['uid']) {
            $uids = explode("\n", $data['uid']);

            $redis = get_redis();

            $keyword_key = Config::get('keyword_key')['keyword_key'];

            foreach ($uids as $v) {

                $add_data['platform']  = $data['platform'];
                $add_data['uid']       = trim( $v);
                $add_data['addtime']   = time();
                $add_data['add_user']  =  $data['admin_user'];
                $add_data['status']    =  $data['status'] == 1 ? 1 : 0;
                $add_data['ban_time']  = !empty($data['ban_time']) ? $data['ban_time']*3600 : 86400*10;

                $res = MonitoringUidSqlServer::add($add_data);
                if($res){

                    $redis->sadd($keyword_key['platform_monitoring_uid'].'_'.$data['platform'],$add_data['uid'] );
                }

            }
            return true;
        } else {

            return false;
        }

        return false;
    }

    public static function edit($data)
    {
        if ($data['id']) {

            if ($data['uid']) {
                $uids = explode("\n", $data['uid']);

                $redis = get_redis();

                $keyword_key = Config::get('keyword_key')['keyword_key'];

                foreach ($uids as $v) {
                    $edit_data['platform'] = $data['platform'];
                    $edit_data['id'] = $data['id'];
                    $edit_data['uid'] = trim($v);
                    $edit_data['addtime'] = time();
                    $edit_data['add_user'] = $data['admin_user'];

                    $edit_data['status'] = $data['status'] == 1 ? 1 : 0;
                    $edit_data['ban_time'] = !empty($data['ban_time']) ? $data['ban_time']*3600 : 86400*10;

                    $res = MonitoringUidSqlServer::edit($edit_data);
                    if($res !== false){
                        $redis->srem($keyword_key['platform_monitoring_uid'].'_'.$data['platform'],$edit_data['uid'] );

                        $redis->sadd($keyword_key['platform_monitoring_uid'].'_'.$data['platform'],$edit_data['uid'] );
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
        $keywords = MonitoringUidSqlServer::getOne($id);

        return $keywords;
    }

    public static function getMore($ids)
    {
        $ids = is_array($ids) ? implode(',',$ids) : '';
        $keywords = MonitoringUidSqlServer::getMore($ids);

        return $keywords;
    }

    public static function getOneByWhere($where)
    {

        $keywords = MonitoringUidSqlServer::getOneByWhere($where);

        return $keywords;
    }

    public static function delete($infos)
    {
        $redis = get_redis();
        $keyword_key = Config::get('keyword_key')['keyword_key'];

        $delete_ids = [];

        foreach( $infos as $v ){

            $redis->srem($keyword_key['platform_monitoring_uid'].'_'.$v['platform'],$v['uid']);

            $delete_ids[] = $v['id'];

        }

        if(!empty($delete_ids)){

            $str_ids = implode(',',$delete_ids);

            $delete_where =  "id in ({$str_ids})";

           $res = MonitoringUidSqlServer::delete($delete_where);
           return $res;
        }

    }

    private static function dealData($data)
    {
        $where = '1=1 ';
        if ( $data['uid'] ) {
            $where .= " and uid = {$data['uid']}";
        }

        if ( $data['platform'] ) {
            $where .= " and platform = '{$data['platform']}'";
        }
        $data['offset'] = ($data['page']-1)*$data['limit'];
        $new_data['where']  = $where;
        $new_data['offset'] = $data['offset'] ?? 0;
        $new_data['limit']  = $data['limit'] ?? 20;
        $new_data['order']  = $data['order'] ?? 'id desc';

        return $new_data;
    }

    public static function modiftKeywordList()
    {
        $res = MonitoringUidSqlServer::getList('1=1');

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
        $res1 = MonitoringUidSqlServer::getList('resemble_status = 1');

        foreach( $res1 as $v ){
            $pinyin = new PinYin();
            $redis->sadd($keyword_key['block_resemble_key'].'_'.$v['game'],str_replace(array("."," "), array("", ""),$pinyin->main($v['keyword'],true,true)) );
        }
    }




}