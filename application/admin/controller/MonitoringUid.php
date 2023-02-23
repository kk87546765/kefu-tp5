<?php
/**
 * Created by PhpStorm.
 * User: crosstime
 * Date: 2017/10/12
 * Time: 上午11:57
 */
namespace app\admin\controller;

use common\libraries\Common;
use common\server\MonitoringUid\MonitoringUidServer;
use think\Config;


class MonitoringUid extends Oauth
{

    //新版UI
    public function index()
    {


        $data['page']    = $this->request->request('page',  1);
        $data['limit']   = $this->request->request('limit',  20);
        $data['uid']    = $this->request->post('uid',  '');
        $data['platform'] = $this->request->post('platform','');


        $list = MonitoringUidServer::getList($data);
        $total = MonitoringUidServer::getCount($data);


        $this->rs['code'] = 0;
        $this->rs['msg'] = '获取成功';
        $this->rs['data'] = $list;
        $this->rs['count'] = $total;
        return return_json($this->rs);
    }



    public function del()
    {
        $this->rs['code'] = -1;
        $this->rs['msg'] = '操作失败';

        $ids = $this->request->post('ids/a');

        if ($ids) {
            $infos = MonitoringUidServer::getMore($ids);

            $res = MonitoringUidServer::delete($infos);
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

        $post_data['platform'] = $this->request->post('platform',  '');
        $post_data['uid'] = $this->request->post('uid');
        $post_data['status'] = $this->request->post('status/d', 0);
        $post_data['ban_time'] = $this->request->post('ban_time/d', 0);

        if(empty($post_data['platform'])){
            $this->rs['code'] = -1;
            $this->rs['msg'] = '平台不能为空';
            return return_json($this->rs);
        }


        $post_data['admin_user'] = $this->user_data['username'];


        $res = MonitoringUidServer::add($post_data);

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
        $post_data['platform'] = $this->request->post('platform',  '');
        $post_data['uid'] = $this->request->post('uid');
        $post_data['status'] = $this->request->post('status/d', 0);
        $post_data['ban_time'] = $this->request->post('ban_time/d', 0);

        $post_data['admin_user'] = $this->user_data['username'];

        if(empty($post_data['platform'])){
            $this->rs['code'] = -1;
            $this->rs['msg'] = '平台不能为空';
            return return_json($this->rs);
        }

        $res = MonitoringUidServer::edit($post_data);

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

        $res = MonitoringUidServer::getOne($id);

        $res['ban_time'] = round($res['ban_time']/3600,1);

        $this->rs['code'] = 0;
        $this->rs['msg'] = '获取成功';
        $this->rs['data'] = $res;

        return return_json($this->rs);
    }



}