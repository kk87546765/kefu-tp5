<?php
/**
 * Created by PhpStorm.
 * 会话质检统计
 * User: crosstime
 * Date: 2017/10/12
 * Time: 上午11:57
 */

namespace app\admin\controller;

use common\server\Vip\WorkSheetServer as thisServer;

class WorkSheet extends Oauth
{
    protected $no_oauth = [
        'listConfig',
        'getSheetTypeConfig',
        'getInfoByRoleId',
        'typeListConfig',
    ];

    #工单统计
    public function index()
    {
        //列表操作权限
        $param = $this->getPost([
            ['platform_id','int',0],
            ['add_time','trim',''],
        ]);

        $this->rs = array_merge($this->rs,thisServer::indexWorkSheetInfo($param));
        return return_json($this->rs);
    }
    #工单-列表
    public function list()
    {
        $param = $this->getPost([
            ['page','int',1],
            ['limit','int',20],
            ['sheet_type','int',0],
            ['sheet_item_type','int',0],
            ['is_tips','int',0],
            ['status','int',-1],
            ['record_user','trim',''],
            ['follow_user','trim',''],
            ['platform_id','int',0],
            ['keyword','trim',''],
            ['static_type','trim',''],
            ['add_time','trim',''],
            ['showMyWorkSheet','int',0],
        ]);

        $res = thisServer::getWorkSheetList($param);

        $this->rs = array_merge($this->rs,$res);
        return return_json($this->rs);
    }

    public function listConfig(){
        $this->rs['data'] = thisServer::listConfig();
        return return_json($this->rs);
    }

    #工单-详情
    public function detail()
    {
        $id = $this->request->post('id/d',0);
        $info['id'] = $id;
        if($id){
            $res = thisServer::getWorkSheetDetail(compact('id'));
            if($res['code'] == 0){
                $info = $res['data'];
            }
        }
        $config = thisServer::getConfig();

        $this->rs['data'] = compact('config','info');

        return return_json($this->rs);
    }
    #工单-获取工单类型配置
    public function getSheetTypeConfig(){
        $id = $this->request->post('id/d',0);

        if(!$id){
            $this->rs['code'] = 1;
            $this->rs['msg'] = '参数不全';
            return return_json($this->rs);
        }

        $work_sheet_id = $this->request->post('work_sheet_id/d',0);

        $this->rs = array_merge($this->rs,thisServer::getSheetTypeConfig($id,$work_sheet_id));

        return return_json($this->rs);
    }
    public function config(){
        $this->s_json(thisServer::getConfig());
    }

    #工单-详情
    public function add()
    {

        $param = $this->getPost([
            ['id','int',0],
            ['sheet_type','int',0],
            ['sheet_item_type_sub','int',0],
            ['role_id','int',0],
            ['uid','int',0],
            ['platform_id','int',0],
            ['product_id','int',0],
            ['game_id','int',0],
            ['user_source','int',0],
            ['pay_money_total','int',0],
            ['status','int',0],
            ['record_user','int',0],
            ['follow_user','int',0],
            ['contact_type','int',0],
            ['sheet_item_type','trim',''],
            ['user_account','trim',''],
            ['server_id','trim',''],
            ['visitor_id','trim',''],
            ['real_name','trim',''],
            ['contact','trim',''],
            ['apply_time','trim',''],
            ['handle_time','trim',''],
            ['content','trim','1'],
            ['order_screen_capture','array',[]],
            ['other_info','array',[]]
        ]);

        $this->rs = array_merge($this->rs,thisServer::saveWorkSheet($param));

        return return_json($this->rs);
    }

    #工单-详情
    public function edit()
    {
        return $this->add();
    }

    #工单-顶置
    public function top(){
        $p_data = $this->getPost([
            ['id','int',0],
            ['is_top','int',0],
        ]);

        $this->rs = array_merge($this->rs,thisServer::sheetDataSave($p_data));
        return return_json($this->rs);
    }

    /**
     * 根据角色获取信息
     * @return false|string
     */
    public function getInfoByRoleId()
    {
        $params = $this->request->post();

        $this->rs = array_merge($this->rs,thisServer::getInfoByRoleId($params));
        return return_json($this->rs);
    }

    #工单类型-列表
    public function typeList()
    {
        //列表操作权限

        $param = $this->getPost([
            ['page','int',1],
            ['limit','int',20],
            ['status','int',0],
            ['pid','int',-1],
            ['title','trim',''],
        ]);

        $res = thisServer::getWorkSheetTypeList($param);

        if($res['code']){
            $this->rs = array_merge($this->rs,$res);
        }else{
            $this->rs['data'] = $res['data']['list'];
            $this->rs['count'] = $res['data']['count'];
        }

        return return_json($this->rs);
    }

    #工单类型-列表配置
    public function typeListConfig()
    {

        $this->rs['data'] = thisServer::getTypeListConfig();
        return return_json($this->rs);

    }

    #工单类型-详情
    public function typeDetail()
    {
        $id = $this->request->post('id/d',0);
        $info['id'] = $id;
        $info['status'] = 1;

        $config = thisServer::getTypeDetailConfig();

        if($id){
            $res = thisServer::getWorkSheetTypeDetail(compact('id'));

            if($res['code'] == 0){
                $info = $res['data'];
                foreach ($info['field_config'] as $k => $v){
                    $config['field_title_arr'][$k] = $v;
                }
            }
        }

        $this->rs['data'] = compact('config','info');

        return return_json($this->rs);
    }
    #工单类型-详情
    public function typeAdd()
    {
        $param = $this->getPost([
            ['id','int',0],
            ['status','int',0],
            ['pid','int',0],
            ['title','trim',''],
            ['children_title','trim',''],
            ['children_val','trim',''],
            ['status_val','trim',''],
            ['field_config','array',[]],
        ]);

        $this->rs = array_merge($this->rs,thisServer::saveWorkSheetType($param));
        return return_json($this->rs);
    }

    #工单类型-详情
    public function typeEdit()
    {
        return $this->typeAdd();
    }

    #工单类型配置-列表
    public function typeInputList()
    {
        $param = $this->getPost([
            ['page','int',1],
            ['limit','int',20],
            ['work_sheet_type_id','int',0]
        ]);

        $res = thisServer::getWorkSheetTypeInputList($param);

        if($res['code']){
            $this->rs = array_merge($this->rs,$res);
        }else{
            $this->rs['data'] = $res['data']['list'];
            $this->rs['count'] = $res['data']['count'];
        }

        return return_json($this->rs);
    }

    #工单类型配置-列表
    public function typeInputListConfig()
    {

        $this->rs['data'] = thisServer::getTypeInputConfig();
        return return_json($this->rs);

    }

    #工单类型配置-详情
    public function typeInputDetail()
    {
        $id = $this->request->post('id/d',0);
        $work_sheet_type_id = $this->request->post('work_sheet_type_id/d',0);

        $info['id'] = $id;
        $info['work_sheet_type_id'] = $work_sheet_type_id;
        if($id){
            $res = thisServer::getWorkSheetTypeInputDetail(compact('id'));
            if($res['code'] == 0){
                $info = $res['data'];
            }
        }

        $this->rs['data'] = $info;

        return return_json($this->rs);
    }
    #工单类型配置-添加
    public function typeInputAdd()
    {

        $data = $this->getPost([
            ['id','int',0],
            ['work_sheet_type_id','int',0],
            ['upload_type','int',0],
            ['width','int',0],
            ['high','int',0],
            ['sort','int',0],
            ['status','int',1],
            ['menu_name','trim',''],
            ['type','trim',''],
            ['input_type','trim',''],
            ['parameter','trim',''],
            ['required','trim',''],
            ['value','trim',''],
            ['info','trim',''],
            ['desc','trim',''],
        ]);

        if (!$data['info']) return $this->f_json('请输入配置名称');
        if (!$data['menu_name']) return $this->f_json('请输入字段名称');
        if (!preg_match('/^[a-z][a-z0-9_]*$/',$data['menu_name'])) return $this->f_json('字段名称必须小写字母开头，字母数字下划线组成');

        if ($data['sort'] < 0) {
            $data['sort'] = 0;
        }

        if ($data['type'] == 'radio' || $data['type'] == 'checkbox') {
            if (!$data['parameter']) return $this->f_json('请输入配置参数');
            $res = thisServer::valiDateRadioAndCheckbox($data);

            if($res['code']){
                $this->f_json($res['msg']);
            }
        }

        $data['value'] = json_encode($data['value']);

        $res = thisServer::saveWorkSheetTypeInput($data);

        if($res['code']){
            $this->f_json($res['msg']);
        }else{
            $this->s_json($res['msg']);
        }
    }

    #工单类型配置-修改
    public function typeInputEdit()
    {
        return $this->typeInputAdd();
    }

    #跟进记录-列表
    public function logList()
    {
        $param = $this->getPost([
            ['page','int',1],
            ['limit','int',20],
            ['work_sheet_id','int',0]
        ]);

        $res = thisServer::getWorkSheetLogList($param);

        if($res['code']){
            $this->f_json($res['msg']);
        }else{
            $this->s_json(0,$res['data']['list'],['count'=>$res['data']['count']]);
        }
    }

    #跟进记录-详情
    public function logDetail()
    {
        $info = $this->getPost([
            ['id','int',0],
            ['work_sheet_id','int',0],
        ]);

        $res = thisServer::getWorkSheetLogDetail($info);

        if($res){
            $info = $res;
        }

        $this->s_json($info);
    }
    #跟进记录-添加
    public function logAdd()
    {
        $data = $this->getPost([
            ['id','int',0],
            ['work_sheet_id','int',0],
            ['operation_type','int',0],
            ['content','trim',''],
            ['file_data','array',[]],
        ]);

        if(empty($data['content'])){
            $this->f_json('请填写内容');
        }

        if(empty($data['work_sheet_id'])){
            $this->f_json('参数错误');
        }

        $res = thisServer::saveWorkSheetLog($data);

        if($res['code']){
            $this->f_json($res['msg']);
        }else{
            $this->s_json($res['msg']);
        }
    }

    #跟进记录-修改
    public function logEdit()
    {
        return $this->logAdd();
    }


}