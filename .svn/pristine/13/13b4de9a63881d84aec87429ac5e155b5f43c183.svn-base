<?php
/**
 * Created by PhpStorm.
 * User: crosstime
 * Date: 2017/10/12
 * Time: 上午11:57
 */

namespace app\admin\controller;

use common\server\PlatformProduct\PlatformProductServer;
//use Quan\Common\Models\Statistic\GameProduct;


class PlatformProduct extends Oauth
{

//    public $block_keyword_key,$common_keyword_forbid,$block_keyword_forbid,$block_resemble_key,$white_keyword_key = '';


    public function index()
    {

        $page       = $this->request->request('page/d', 1);
        $limit      = $this->request->request('limit/d', 20);
//        $game       = $this->request->post('game/s', '');
//        $keyword    = $this->request->post('keyword/s', '');


        $game_product_list = PlatformProductServer::getGameProductList($limit,$page);

        $total = PlatformProductServer::getGameProductCount();


        if($game_product_list){
            $this->rs['code'] = 0;
            $this->rs['msg'] = '获取成功';
            $this->rs['data'] = $game_product_list;
            $this->rs['count'] = $total;
        }else{
            $this->rs['code'] = -1;
            $this->rs['msg'] = '获取失败';
        }

        return return_json($this->rs);

    }



    //添加
    public function edit()
    {

        $data['id'] = $this->request->post('id', 'int',0);
        $data['product_code'] = $this->request->post('product_code/s','');


        $res = PlatformProductServer::edit($data);

        if($res!== false){
            $this->rs['code'] = 0;
            $this->rs['msg'] = '编辑成功';
        }else{
            $this->rs['code'] = -1;
            $this->rs['msg'] = '编辑失败';
        }

        return return_json($this->rs);
    }

    public function getInfo()
    {
        $id = $this->request->get('id/d',0);
        $res = PlatformProductServer::getOne($id);
        if($res){
            $this->rs['code'] = 0;
            $this->rs['msg'] = '获取成功';
            $this->rs['data'] = $res;
        }else{
            $this->rs['code'] = -1;
            $this->rs['msg'] = '获取失败';
        }

        if($res!== false){
            $this->rs['code'] = 0;
            $this->rs['msg'] = '编辑成功';
        }else{
            $this->rs['code'] = -1;
            $this->rs['msg'] = '编辑失败';
        }

        return return_json($this->rs);
    }







}