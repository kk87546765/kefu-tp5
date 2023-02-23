<?php
/**
 * Created by PhpStorm.
 * User: crosstime
 * Date: 2017/10/12
 * Time: 上午11:57
 */
namespace app\admin\controller;

use common\libraries\Common;
use common\server\Keyword\KeywordServer;
use think\Config;


class Keyword extends Oauth
{

    public $block_keyword_key,$common_keyword_forbid,$block_keyword_forbid,$block_resemble_key,$white_keyword_key = '';


    public function keyword_list(){
        $redis = get_redis();
        $product_list = Common::getProductList(1);

        $keyword_key = Config::get('keyword_key')['keyword_key'];

        foreach($product_list as $k=>$v){

            if(!empty($v['code'])){
                echo $keyword_key['block_keyword_key'].'_'.$v['code'].'::';
                var_duMP( $redis->SMEMBERS($keyword_key['block_keyword_key'].'_'.$v['code'] ));

            }

        }
    }



    public function block_resemble_key(){
        $redis = get_redis();
        $product_list = Common::getProductList(1);

        $keyword_key = Config::get('keyword_key')['keyword_key'];

        foreach($product_list as $k=>$v){

            if(!empty($v['code'])){
                echo $keyword_key['block_resemble_key'].'_'.$v['code'].'::';
                var_duMP($redis->SMEMBERS($keyword_key['block_resemble_key'].'_'.$v['code'] ));

            }

        }
    }



    public function block_list(){
        $redis = get_redis();
        $res = $redis->LRANGE("block_keyword_forbid",0,-1);
        var_dump($res);
    }



    public function modiftKeywordList()
    {

        KeywordServer::modiftKeywordList();

    }


    public function block_key(){
        $uuid   = $_GET['uuid'];
        $ip     = $_GET['ip'];
        $uname  = $_GET['uname'];

        if( !empty($uuid) ) $baninfo[]='block_imei_'.$uuid;
        if( !empty($ip) ) $baninfo[]='block_ip_'.$ip;
        if( !empty($uname) ) $baninfo[]='block_uname_'.$uname;

        $redis = $this->struct->get();
        $ret = [];
        foreach($baninfo as $v){
            $ret[] = $redis->get($v);
        }
        var_dump($ret);
    }


    //新版UI
    public function index()
    {


        $data['page']    = $this->request->request('page',  1);
        $data['limit']   = $this->request->request('limit',  20);
        $data['game']    = $this->request->post('game',  '');
        $data['keyword'] = $this->request->post('keyword','');
        $data['id']      = $this->request->post('id','');


        $keywords = KeywordServer::getList($data);
        $total = KeywordServer::getCount($data);

        $this->gamelist = Common::getProductList(2);

        $this->gamelist['autoforbid']['name']="自动封禁";

        foreach ($keywords as &$keyword) {

            $keyword['game_name'] = $this->gamelist[$keyword['game']]['name'] ?? '';
            $keyword['addtime']   = date("Y-m-d H:i:s",$keyword['addtime']);
            $keyword['status']   = $keyword['status']?'开启':'关闭';
        }


        $this->rs['code'] = 0;
        $this->rs['msg'] = '获取成功';
        $this->rs['data'] = $keywords;
        $this->rs['count'] = $total;
        return return_json($this->rs);
    }



    public function del()
    {
        $this->rs['code'] = -1;
        $this->rs['msg'] = '操作失败';

        $ids = $this->request->post('ids/a');

        if ($ids) {
            $infos = KeywordServer::getMore($ids);

            $res = KeywordServer::delete($infos);
            if ($res) {
                $this->rs['code'] = 0;
                $this->rs['msg'] = '操作成功';
            }
        }
        return return_json($this->rs);
    }


    //添加
    public function add()
    {

        $post_data['game'] = $this->request->post('game',  '');
        $post_data['keywords'] = $this->request->post('keywords');
        $post_data['num'] = $this->request->post('num/d',0);
        $post_data['status'] = $this->request->post('status/d', 0);
        $post_data['level_min'] = $this->request->post('level_min/d',  0);
        $post_data['level_max'] = $this->request->post('level_max/d',  0);
        $post_data['money_min'] = $this->request->post('money_min/d',  0);
        $post_data['money_max'] = $this->request->post('money_max/d',  0);
        $post_data['resemble_status'] = $this->request->post('resemble_status/d', 0);
        $post_data['type'] = $this->request->post('type/d', 1);
        $post_data['block_time'] = $this->request->post('block_time/d', 0);
        $post_data['ban_time'] = $this->request->post('ban_time/d', 0);

        if(empty($post_data['game'])){
            $this->rs['code'] = -1;
            $this->rs['msg'] = '游戏不能为空';
            return return_json($this->rs);
        }


        $post_data['admin_user'] = $this->user_data['username'];


        $res = KeywordServer::add($post_data);

        if($res){
            $this->rs['code'] = 0;
            $this->rs['msg'] = '添加成功';
        }else{
            $this->rs['code'] = -1;
            $this->rs['msg'] = '添加失败';
        }

        return return_json($this->rs);
    }


    //编辑
    public function edit()
    {
        $post_data['id'] = $this->request->post('id/d',  0);
        $post_data['game'] = $this->request->post('game',  '');
        $post_data['keywords'] = $this->request->post('keywords');
        $post_data['num'] = $this->request->post('num/d',0);
        $post_data['status'] = $this->request->post('status/d', 0);
        $post_data['level_min'] = $this->request->post('level_min/d',  0);
        $post_data['level_max'] = $this->request->post('level_max/d',  0);
        $post_data['money_min'] = $this->request->post('money_min/d',  0);
        $post_data['money_max'] = $this->request->post('money_max/d',  0);
        $post_data['resemble_status'] = $this->request->post('resemble_status/d', 0);
        $post_data['type'] = $this->request->post('type/d', 1);
        $post_data['block_time'] = $this->request->post('block_time/d', 0);
        $post_data['ban_time'] = $this->request->post('ban_time/d', 0);

        $post_data['admin_user'] = $this->user_data['username'];

        if(empty($post_data['game'])){
            $this->rs['code'] = -1;
            $this->rs['msg'] = '游戏不能为空';
            return return_json($this->rs);
        }

        $res = KeywordServer::edit($post_data);

        if($res){
            $this->rs['code'] = 0;
            $this->rs['msg'] = '编辑成功';
        }else{
            $this->rs['code'] = -1;
            $this->rs['msg'] = '编辑失败';
        }

        return return_json($this->rs);

    }

    public function getEditInfo()
    {
        $id = $this->request->post('id', 0);

        $res = KeywordServer::getOne($id);

        $res['block_time'] = round($res['block_time']/3600,1);
        $res['ban_time'] = round($res['ban_time']/3600,1);

        $this->rs['code'] = 0;
        $this->rs['msg'] = '获取成功';
        $this->rs['data'] = $res;

        return return_json($this->rs);
    }

    public function getProductList()
    {
        $info = Common::getProductList(2);
        $info[999]['name'] = "自动封禁";
        $info[999]['code'] = "autoforbid";
        $this->rs['code'] = 0;
        $this->rs['msg'] = '获取成功';
        $this->rs['data'] = $info;
        $this->rs['count'] = 0;
        return return_json($this->rs);

    }


}