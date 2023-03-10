<?php
/**
 * vip
 */
namespace app\admin\controller;

use common\server\Vip\RecallServer;

class Recall extends Oauth
{
    protected $no_oauth = [
        'codeListConfig',
        'codeGroupListConfig',
        'linkListConfig',
        'linkGroupListConfig',
        'planListConfig',
        'planLogListConfig',
    ];

    #礼包码-列表
    public function codeList()
    {
        $data = $this->getPost([
            ['platform_id', 'int', 0],
            ['admin_id', 'int', 0],
            ['status', 'int', -1],
            ['p_up', 'trim', ''],
            ['up_id', 'int', 0],
            ['group_id','int',0],
            ['code', 'trim', ''],
        ]);

        $this->rs = array_merge($this->rs,RecallServer::codeList($data));

        return return_json($this->rs);
    }
    #礼包码-列表配置
    public function codeListConfig(){

        $this->rs['data'] = RecallServer::codeConfig();

        return return_json($this->rs);
    }
    #礼包码-详情
    public function codeDetail(){
        $id = $this->req->post('id/d',0);

        $this->rs = array_merge($this->rs,RecallServer::codeDetail($id));

        return return_json($this->rs);
    }
    #礼包码-添加
    public function codeAdd(){
        $p_data = $this->getPost([
            ['up_id', 'int', 0],
            ['code', 'trim', ''],
            ['status', 'int', 0],
            ['platform_id', 'int', 0],
        ]);
        $res = RecallServer::codeSave($p_data);
        if($res){
            $this->rs['msg'] = '操作成功';
        }else{
            $this->rs['code'] = 1;
            $this->rs['msg'] = '操作失败';
        }

        return return_json($this->rs);
    }
    #礼包码-修改
    public function codeEdit(){
        $p_data = $this->getPost([
            ['id', 'int', 0],
            ['up_id', 'int', 0],
            ['code', 'trim', ''],
            ['status', 'int', 0],
            ['platform_id', 'int', 0],
        ]);
        $res = RecallServer::codeSave($p_data);
        if($res){
            $this->rs['msg'] = '操作成功';
        }else{
            $this->rs['code'] = 1;
            $this->rs['msg'] = '操作失败';
        }

        return return_json($this->rs);
    }

    #礼包码-分组-列表
    public function codeGroupList()
    {
        $data = $this->getPost([
            ['page', 'int', 1],
            ['limit', 'int', 0],
            ['platform_id', 'int', 0],
            ['admin_id', 'int', 0],
            ['up_id', 'int', 0],
            ['status', 'int', -1],
            ['p_up', 'trim', ''],
            ['title', 'trim', ''],
        ]);

        $this->rs = array_merge($this->rs,RecallServer::codeGroupList($data));

        return return_json($this->rs);
    }
    #礼包码-分组-列表配置
    public function codeGroupListConfig(){

        $this->rs['data'] = RecallServer::codeGroupConfig();

        return return_json($this->rs);
    }
    #礼包码-分组-详情
    public function codeGroupDetail(){
        $id = $this->req->post('id/d',0);

        $this->rs = array_merge($this->rs,RecallServer::codeGroupDetail($id));

        return return_json($this->rs);
    }
    #礼包码-分组-添加
    public function codeGroupAdd(){
        $p_data = $this->getPost([
            ['up_id', 'int', 0],
            ['status', 'int', 0],
            ['type', 'int', 0],
            ['platform_id', 'int', 0],
            ['title', 'trim', ''],
            ['code', 'trim', ''],
        ]);

        $res = RecallServer::codeGroupSave($p_data);
        if($res){
            $this->rs['msg'] = '操作成功';
        }else{
            $this->rs['code'] = 1;
            $this->rs['msg'] = '操作失败';
        }

        return return_json($this->rs);
    }
    #礼包码-分组-修改
    public function codeGroupEdit(){
        $p_data = $this->getPost([
            ['id','int',0],
            ['up_id', 'int', 0],
            ['status', 'int', 0],
            ['type', 'int', 0],
            ['platform_id', 'int', 0],
            ['title', 'trim', ''],
            ['code', 'trim', ''],
        ]);
        $res = RecallServer::codeGroupSave($p_data);
        if($res){
            $this->rs['msg'] = '操作成功';
        }else{
            $this->rs['code'] = 1;
            $this->rs['msg'] = '操作失败';
        }

        return return_json($this->rs);
    }

    #广告位链接-列表
    public function linkList()
    {
        $data = $this->getPost([
            ['page', 'int', 1],
            ['limit', 'int', 0],
            ['platform_id', 'int', 0],
            ['admin_id', 'int', 0],
            ['status', 'int', -1],
            ['ver_id', 'trim', ''],
            ['up_id', 'int', 0],
            ['group_id', 'int', 0],
            ['p_up', 'trim', ''],
            ['link', 'trim', ''],
            ['ver_title', 'trim', ''],
            ['link_ids','array',[]],
            ['link_ids_str','trim',''],
        ]);

        $this->rs = array_merge($this->rs,RecallServer::linkList($data));

        return return_json($this->rs);
    }
    #广告位链接-列表配置
    public function linkListConfig(){

        $this->rs['data'] = RecallServer::linkConfig();

        return return_json($this->rs);
    }
    #广告位链接-详情
    public function linkDetail(){
        $id = $this->req->post('id/d',0);

        $this->rs = array_merge($this->rs,RecallServer::linkDetail($id));

        return return_json($this->rs);
    }
    #广告位链接-添加
    public function linkAdd(){
        $p_data = $this->getPost([
            ['up_id', 'int', 0],
            ['link', 'trim', ''],
            ['status', 'int', 0],
            ['platform_id', 'int', 0],
            ['ver_id', 'int', 0],
            ['ver_title', 'trim', ''],
        ]);
        $res = RecallServer::linkSave($p_data);
        if($res){
            $this->rs['msg'] = '操作成功';
        }else{
            $this->rs['code'] = 1;
            $this->rs['msg'] = '操作失败';
        }

        return return_json($this->rs);
    }
    #广告位链接-修改
    public function linkEdit(){
        $p_data = $this->getPost([
            ['id', 'int', 0],
            ['up_id', 'int', 0],
            ['link', 'trim', ''],
            ['status', 'int', 0],
            ['platform_id', 'int', 0],
            ['ver_id', 'int', 0],
            ['ver_title', 'trim', ''],
        ]);
        $res = RecallServer::linkSave($p_data);
        if($res){
            $this->rs['msg'] = '操作成功';
        }else{
            $this->rs['code'] = 1;
            $this->rs['msg'] = '操作失败';
        }

        return return_json($this->rs);
    }

    #广告位链接-分组-列表
    public function linkGroupList()
    {
        $data = $this->getPost([
            ['page', 'int', 1],
            ['limit', 'int', 0],
            ['platform_id', 'int', 0],
            ['admin_id', 'int', 0],
            ['status', 'int', -1],
            ['up_id', 'int', 0],
            ['p_up', 'trim', ''],
            ['title', 'trim', ''],
        ]);

        $this->rs = array_merge($this->rs,RecallServer::linkGroupList($data));

        return return_json($this->rs);
    }
    #广告位链接-分组-列表配置
    public function linkGroupListConfig(){

        $this->rs['data'] = RecallServer::linkGroupConfig();

        return return_json($this->rs);
    }
    #广告位链接-分组-详情
    public function linkGroupDetail(){
        $id = $this->req->post('id/d',0);

        $this->rs = array_merge($this->rs,RecallServer::linkGroupDetail($id));

        return return_json($this->rs);
    }
    #广告位链接-分组-添加
    public function linkGroupAdd(){
        $p_data = $this->getPost([
            ['up_id', 'int', 0],
            ['status', 'int', 0],
            ['platform_id', 'int', 0],
            ['title', 'trim', ''],
            ['link', 'trim', ''],
        ]);

        $res = RecallServer::linkGroupSave($p_data);
        if($res){
            $this->rs['msg'] = '操作成功';
        }else{
            $this->rs['code'] = 1;
            $this->rs['msg'] = '操作失败';
        }

        return return_json($this->rs);
    }
    #广告位链接-分组-修改
    public function linkGroupEdit(){
        $p_data = $this->getPost([
            ['id','int',0],
            ['up_id', 'int', 0],
            ['status', 'int', 0],
            ['platform_id', 'int', 0],
            ['title', 'trim', ''],
            ['link', 'trim', ''],
        ]);
        $res = RecallServer::linkGroupSave($p_data);
        if($res){
            $this->rs['msg'] = '操作成功';
        }else{
            $this->rs['code'] = 1;
            $this->rs['msg'] = '操作失败';
        }

        return return_json($this->rs);
    }

    #计划-列表
    public function planList()
    {
        $data = $this->getPost([
            ['page', 'int', 1],
            ['limit', 'int', 0],
            ['platform_id', 'int', 0],
            ['admin_id', 'int', 0],
            ['status', 'int', -1],
            ['execute_type', 'int', 0],
            ['recall_up_id', 'int', 0],
            ['loss_up_id', 'int', 0],
            ['p_recall_up', 'trim', ''],
            ['p_loss_up', 'trim', ''],
            ['title', 'trim', ''],
            ['plan_ids','array',[]],
        ]);

        $this->rs = array_merge($this->rs,RecallServer::planList($data));

        return return_json($this->rs);
    }
    #计划-列表配置
    public function planListConfig(){

        $this->rs['data'] = RecallServer::planConfig();

        return return_json($this->rs);
    }
    #计划-详情
    public function planDetail(){
        $id = $this->req->post('id/d',0);

        $this->rs = array_merge($this->rs,RecallServer::planDetail($id));

        return return_json($this->rs);
    }
    #计划-添加
    public function planAdd(){

        $p_data = $this->getPost([
            ['loss_up_id', 'trim', ''],
            ['recall_up_id', 'int', 0],
//            ['status', 'int', 0],
            ['platform_id', 'int', 0],
            ['min_account_money', 'int', 0],
            ['max_account_money', 'int', 0],
            ['min_loss_up_money', 'int', 0],
            ['max_loss_up_money', 'int', 0],
            ['min_loss_day', 'int', 0],
            ['max_loss_day', 'int', 0],
            ['min_level', 'int', 0],
            ['max_level', 'int', 0],
            ['send_num', 'int', 0],
            ['interval_day', 'int', 0],
            ['recall_ver_group_id', 'int', 0],
            ['recall_code_group_id', 'int', 0],
            ['execute_type', 'int', 0],
            ['limit_num', 'int', 0],
            ['title', 'trim', ''],
        ]);

        $res = RecallServer::planSave($p_data);
        if($res){
            $this->rs['msg'] = '操作成功';
        }else{
            $this->rs['code'] = 1;
            $this->rs['msg'] = '操作失败';
        }

        return return_json($this->rs);
    }
    #计划-修改
    public function planEdit(){
        $p_data = $this->getPost([
            ['id','int',0],
            ['loss_up_id', 'trim', ''],
            ['recall_up_id', 'int', 0],
//            ['status', 'int', 0],
            ['platform_id', 'int', 0],
            ['min_account_money', 'int', 0],
            ['max_account_money', 'int', 0],
            ['min_loss_up_money', 'int', 0],
            ['max_loss_up_money', 'int', 0],
            ['min_loss_day', 'int', 0],
            ['max_loss_day', 'int', 0],
            ['min_level', 'int', 0],
            ['max_level', 'int', 0],
            ['send_num', 'int', 0],
            ['interval_day', 'int', 0],
            ['recall_ver_group_id', 'int', 0],
            ['recall_code_group_id', 'int', 0],
            ['execute_type', 'int', 0],
            ['limit_num', 'int', 0],
            ['title', 'trim', ''],
        ]);
        $res = RecallServer::planSave($p_data);
        if($res){
            $this->rs['msg'] = '操作成功';
        }else{
            $this->rs['code'] = 1;
            $this->rs['msg'] = '操作失败';
        }

        return return_json($this->rs);
    }
    #计划-状态修改
    public function planChangeStatus(){

        $ids = $this->request->post('ids');
        $status = $this->request->post('status/d',0);

        $res = RecallServer::planChangeStatus($ids,$status);

        if($res){
            $this->rs['msg'] = '操作成功';
        }else{
            $this->rs['code'] = 1;
            $this->rs['msg'] = '操作失败';
        }

        return return_json($this->rs);
    }

    #计划日志-列表
    public function planLogList()
    {
        $data = $this->getPost([
            ['page', 'int', 1],
            ['limit', 'int', 0],
            ['platform_id', 'int', 0],
            ['admin_id', 'int', 0],
            ['status', 'int', 0],
            ['execute_type', 'int', 0],
            ['recall_up_id', 'int', 0],
            ['loss_up_id', 'int', 0],
            ['p_recall_up', 'trim', ''],
            ['p_loss_up', 'trim', ''],
            ['title', 'trim', ''],
            ['add_time','trim',''],
            ['plan_ids','array',[]],
        ]);

        $this->rs = array_merge($this->rs,RecallServer::planLogList($data));

        return return_json($this->rs);
    }
    #计划日志-列表配置
    public function planLogListConfig(){

        $this->rs['data'] = RecallServer::planConfig();

        return return_json($this->rs);
    }
}
