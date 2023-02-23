<?php
/**
 * Created by PhpStorm.
 * User: crosstime
 * Date: 2017/10/12
 * Time: 上午11:57
 */

namespace app\admin\controller;

use common\server\GarrisonProduct\GarrisonProductServer;
//use Quan\Common\Models\Statistic\GameProduct;


class GarrisonProduct extends Oauth
{

    public $block_keyword_key,$common_keyword_forbid,$block_keyword_forbid,$block_resemble_key,$white_keyword_key = '';


    public function index()
    {

        $page       = $this->request->post('page/d', 1);
        $limit      = $this->request->post('limit/d', 20);
        $game       = $this->request->post('game/s', '');
        $keyword    = $this->request->post('keyword/s', '');


        $garrison_product_list = GarrisonProductServer::getGarrisonProductList($limit,$page);

        $total = GarrisonProductServer::getGarrisonProductCount();


        if($garrison_product_list){
            $this->rs['code'] = 0;
            $this->rs['msg'] = '获取成功';
            $this->rs['data'] = $garrison_product_list;
            $this->rs['total'] = $total;
        }else{
            $this->rs['code'] = -1;
            $this->rs['msg'] = '获取失败';
        }

        return return_json($this->rs);

    }


    //添加
    public function add()
    {

        $data['garrison_period'] = $this->request->post('garrison_period/d',0);
        $data['product_id'] = $this->request->post('product_id/d',0);
        $data['status'] = $this->request->post('status/d', 0);
        $data['platform_id'] = $this->request->post('platform_id/d', 0);

        $data['admin_user'] = $this->user_data['id'];
        $data['admin_name'] = $this->user_data['username'];

        $res = GarrisonProductServer::add($data);

        if($res){
            $this->rs['code'] = 0;
            $this->rs['msg'] = '添加成功';
        }else{
            $this->rs['code'] = -1;
            $this->rs['msg'] = '添加失败';
        }

        return return_json($this->rs);

    }


    //添加
    public function edit()
    {

        $data['id'] = $this->request->post('id', 'int',0);

        $data['garrison_period'] = $this->request->post('garrison_period/d',0);
        $data['product_id'] = $this->request->post('product_id/d',0);
        $data['status'] = $this->request->post('status/d', 0);
        $data['platform_id'] = $this->request->post('platform_id/d', 0);

        $data['admin_user'] = $this->user_data['id'];
        $data['admin_name'] = $this->user_data['username'];

        $res = GarrisonProductServer::edit($data);

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
        $res = GarrisonProductServer::getOne($id);
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



    //归档
    public function changeStatus()
    {
        $id = $this->request->post('ids', 'int',0);
        $res = GarrisonProductServer::changeStatus($id,0);

        if($res!== false){
            $this->rs['code'] = 0;
            $this->rs['msg'] = '归档成功';
        }else{
            $this->rs['code'] = -1;
            $this->rs['msg'] = '归档失败';
        }

        return return_json($this->rs);
    }


    //删除
    public function del()
    {
        $id = $this->request->post('ids', 'int', 0);
        $res = GarrisonProductServer::delete($id);

        if ($res) {
            $this->rs['code'] = 0;
            $this->rs['msg'] = '删除成功';
        } else {
            $this->rs['code'] = -1;
            $this->rs['msg'] = '删除失败';
        }

        return return_json($this->rs);

    }



}