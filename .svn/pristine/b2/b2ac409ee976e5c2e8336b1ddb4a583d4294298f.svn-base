<?php
/**
 * Created by PhpStorm.
 * User: crosstime
 * Date: 2017/10/12
 * Time: 上午11:57
 */
namespace common\server\Waring;


use common\base\BasicServer;
use common\server\Platform\{BanServer,Platform};
use common\libraries\Common;
use common\libraries\PinYin;
use common\sql_server\{WaringSqlServer};
use common\libraries\Ipip\IP4datx;
use think\Config;


class WaringServer extends BasicServer
{


    public static function getList($data)
    {

        $res = self::dealData($data);

        if($res['code'] !== 0){
            return $res;
        }

        $info = WaringSqlServer::getList($res['data']);

        foreach ($info as $k=>$v) {

            $info[$k]['status']   = $v['type'] == 1?'已封禁':'正常';
            $info[$k]['account']  = $data['type'] == 1 ? $v['imei'] : $v['ip'];
            $info[$k]['area']     = $data['type'] == 0 ?implode('', IP4datx::find($v['ip'])) : '';
            $info[$k]['type']     = $data['type'] == 1 ? 'imei' : 'ip';
            $info[$k]['platform_id']     = $data['platform_id'];

        }

        return ['code'=>0,'data'=>$info,'msg'=>'获取成功'];
    }


    public static function getCount($data)
    {
        $res = self::dealData($data);

        if($res['code'] !== 0){
            return $res;
        }

        return WaringSqlServer::getCount($res['data']);
    }



    public static function getOne($id)
    {

        $keywords = WaringSqlServer::getOne($id);

        return $keywords;
    }



    private static function dealData($data)
    {

        if($data['type'] == 0){
            $account = $data['ip'];
        }else{
            $account = $data['imei'];
        }

        if(empty($data['platform_id'])){

            return ['data'=>[],'code'=>-1,'msg'=>'平台不能为空'];
        }

        if($data['p_g']){
            @list($data['platform_key'],$data['gid']) = explode('_',$data['p_g']);
        }
        $platform_info = Common::getPlatformInfoByPlatformIdAndCache($data['platform_id']);
        $data['platform_key'] = $platform_info['platform_suffix'];

        if(empty($data['gid'])){
            return ['data'=>[],'code'=>-1,'msg'=>'游戏不能为空'];
        }

        $where = [

            'account'=>$account,
            's_time'=>$data['s_time'],
            'e_time'=>$data['e_time'],
            'gid'=>$data['gid'],
            'limit_num'=>$data['limit_num'],
            'limit'  => $data['limit'],
            'offset' => ($data['page'] - 1) * $data['limit'],
            'order'  => 'id desc',
            'platform'=>$data['platform_key'],
            'type'=>$data['type']
        ];
        $new_data['where'] = $where;

        return ['code'=>0,'data'=>$new_data['where'],'msg'=>''];
    }

    public static function modiftKeywordList()
    {
        $res = WaringSqlServer::getList('1=1');

        $product_list = Common::getProductList(1);

        $keyword_key = Config::get('keyword_key')['keyword_key'];
        $redis = get_redis();
        foreach($product_list as $k=>$v){


            $redis->delete( $keyword_key['block_keyword_key'].'_'.$v['code'] );
            $redis->delete( $keyword_key['block_resemble_key'].'_'.$v['code'] );
        }

        foreach( $res->toArray() as $v ){

            $t_content = urldecode(str_replace(array(".","+"), array("",""), urlencode($v['keyword'])));
            $redis->sadd($keyword_key['block_keyword_key'].'_'.$v['game'],$t_content );

        }
        $res1 = WaringSqlServer::getList('resemble_status = 1');

        foreach( $res1->toArray() as $v ){
            $pinyin = new PinYin();
            $redis->sadd($keyword_key['block_resemble_key'].'_'.$v['game'],str_replace(array("."," "), array("", ""),$pinyin->main($v['keyword'],true,true)) );
        }
    }



    public static function getWaringUser($post_data)
    {

        $platform_info = Common::getPlatformInfoByPlatformIdAndCache($post_data['platform_id']);

        $where = [
            'account'=>$post_data['account'],
            'limit'  => $post_data['limit'],
            'offset' => ($post_data['page'] - 1) * $post_data['limit'],
            'platform'=>$platform_info['platform_suffix'],
            'type'=>$post_data['type']
        ];

        $having = [];
        if(isset($post_data['check_value']) && isset($post_data['check_type'])){
            $having = [
                'check_value'=>$post_data['check_value'],
                'check_type'=>$post_data['check_type']
            ];
        }

        $res = WaringSqlServer::getWaringUser($where,$having);

        return $res;
    }



    public static function getWaringUserCount($post_data)
    {
        $platform_info = Common::getPlatformInfoByPlatformIdAndCache($post_data['platform_id']);

        $where = [
            'account'=>$post_data['account'],
            'limit'  => $post_data['limit'],
            'offset' => ($post_data['page'] - 1) * $post_data['limit'],
            'platform'=>$platform_info['platform_suffix'],
            'type'=>$post_data['type']
        ];

        $having = [];
        if(isset($post_data['check_value']) && isset($post_data['check_type'])){
            $having = [
                'check_value'=>$post_data['check_value'],
                'check_type'=>$post_data['check_type']
            ];
        }

        $res = WaringSqlServer::getWaringUserCount($where,$having);

        return $res;
    }



}