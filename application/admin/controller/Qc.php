<?php
namespace app\admin\controller;

use common\server\Qc\QcQuestionServer;
use PhpOffice\PhpSpreadsheet\Reader\Csv;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
class Qc extends Oauth
{
    protected $no_oauth = [
        'config','getSourceConfig','logListConfig','getScoreConfig','appealLogConfig','checkKfInfo',
        'getConversationOtherInfo','contactList',
    ];

    #质检池-列表
    public function list()
    {

        $p_data = $this->getPost([
            ['page','int',0],
            ['limit','int',0],
            ['type','int',0],
            ['server_long_start','int',0],
            ['server_long_end','int',0],
            ['set_admin_status','int',0],
            ['set_qc_status','int',0],
            ['admin_id','int',0],
            ['kf_platform','trim',''],
            ['kf_source','trim',''],
            ['question_type','trim',''],
            ['game_type','trim',''],
            ['server_start','trim',''],
            ['server_end','trim',''],
            ['text_id','trim',''],
            ['partnerid','trim',''],
            ['text','trim',''],
            ['kf_id','trim',''],
            ['kf_score','trim',''],
        ]);

        $res = QcQuestionServer::list($p_data);

        $this->rs = array_merge($this->rs,$res);

        return return_json($this->rs);
    }
    #质检池-配置（无权限）
    public function config(){

        $res = QcQuestionServer::config();

        $this->rs['data'] = $res;

        return return_json($this->rs);
    }
    #质检池-获取会话来源相关质检配置（无权限）
    public function getSourceConfig(){
        $p_data = $this->getPost([
            ['kf_source','int',0],
            ['keys','trim',''],
        ]);

        $list = QcQuestionServer::getSourceConfig($p_data);

        if(!$list){
            $this->rs['msg'] = '没有数据';
            $this->rs['code'] = 1;
            return return_json($this->rs);
        }
        $this->rs['data'] = $list;
        return return_json($this->rs);
    }
    #质检池-详情
    public function detail(){

        $id = $this->req->post('id/d',0);

        $res = QcQuestionServer::detail($id);

        $this->rs = array_merge($this->rs,$res);

        return return_json($this->rs);
    }
    #质检池-详情-相关会话列表（无权限）
    public function contactList(){
        $id      = $this->req->post('id/d', 0);

        $res = QcQuestionServer::contactList($id);

        $this->rs = array_merge($this->rs,$res);

        return return_json($this->rs);
    }
    #质检池-详情-会话相关信息（无权限）
    public function getConversationOtherInfo(){

        $p_data = $this->getPost([
            ['kf_source','int',0],
            ['text_id','trim',''],
        ]);

        $info = QcQuestionServer::conversationOtherInfo($p_data);

        if($info){
            $this->rs['data'] = $info;

            return return_json($this->rs);
        }else{
            $this->rs['code'] = 1;
            $this->rs['msg'] = 'no info';
            return return_json($this->rs);
        }
    }
    #质检池-分配
    public function addAdminIds(){
        $p_data = $this->getPost([
            ['ids','array',[]],
            ['admin_id','int',0],
        ]);

        $res = QcQuestionServer::addAdminId($p_data['ids'],$p_data['admin_id']);

        if(!$res){
            $this->rs['code'] = 1;
            $this->rs['msg'] = '失败';
        }

        return return_json($this->rs);
    }
    #质检池-取消分配
    public function delAdminIds(){
        $id = $this->req->post('id/a',[]);

        if(!$id){
            $this->rs['code'] = 1;
            $this->rs['msg'] = '缺少参数';
            return return_json($this->rs);
        }
        $flag = 0;
        foreach ($id as $item){
            $res = QcQuestionServer::delAdminId($item);
            if($res['code']==0){
                $flag ++;
            }
        }

        if(!$flag){
            $this->rs['msg'] = '操作失败';
            $this->rs['code'] = 2;
        }

        return return_json($this->rs);
    }
    #质检池-设置会话更新
    public function setConversationNeedUpdate(){
        $p_data = $this->getPost([
            ['kf_source','int',0],
            ['text_id','trim',''],
        ]);

        $res = QcQuestionServer::setConversationNeedUpdate($p_data);

        $this->rs = array_merge($this->rs,$res);

        return return_json($this->rs);
    }

    #质检记录-列表
    public function logList(){

        $p_data = $this->getPost([
            ['kf_id','int',0],
            ['group_id','int',0],
            ['status','trim',''],
            ['admin_id','int',0],
            ['qc_score_start','int',0],
            ['qc_score_end','int',0],
            ['kf_is_solve','int',-1],
            ['question_is_solve','int',-1],
            ['kf_source','trim',''],
            ['platform_id','trim',''],
            ['start','trim',''],
            ['end','trim',''],
            ['question_type','trim',''],
            ['game_type','trim',''],
            ['qc_num','trim',''],
            ['server_date','trim',''],
            ['qc_level','trim',''],
            ['qc_type','trim',''],
            ['text','trim',''],
            ['is_example','int',-1],
            ['page','int',1],
            ['limit','int',20],
        ]);

        $res = QcQuestionServer::logList($p_data);

        $this->rs = array_merge($this->rs,$res);

        return return_json($this->rs);

    }
    #质检记录-列表配置（无权限）
    public function logListConfig(){
        $res = QcQuestionServer::logListConfig();

        $this->rs['data'] = $res;

        return return_json($this->rs);
    }
    #质检记录-详情
    public function logDetail(){

        $p_data = $this->getPost([
            ['id','int',0],
            ['qc_question_id','int',0],
        ]);

        $res = QcQuestionServer::logDetail($p_data['id'],$p_data['qc_question_id']);

        $this->rs = array_merge($this->rs,$res);

        return return_json($this->rs);
    }
    #质检记录-详情-检查客服信息（无权限）
    public function checkKfInfo(){

        $p_data = $this->getPost([
            ['kf_id','int',0],
            ['kf_platform','trim',''],
            ['kf_group','int',0],
        ]);

        $info = QcQuestionServer::checkKfInfo($p_data);

        $this->rs['data'] = $info;

        return return_json($this->rs);
    }
    #质检记录-添加
    public function logAdd(){
        $p_data = $this->getPost([
            ['qc_question_id','int',0],
            ['kf_id','int',0],
            ['kf_platform','trim',''],
            ['kf_source','int',0],
            ['kf_score','int',0],
            ['question_type','trim',''],
            ['game_type','trim',''],
            ['text','trim',''],
            ['qc_type','trim',''],
            ['qc_level','trim',''],
            ['qc_score','int',0],
            ['qc_comments','trim',''],
            ['kf_adventage','trim',''],
            ['kf_is_solve','int',0],
            ['kf_nosolve_res','trim',''],
            ['question_is_solve','int',0],
            ['question_nosolve_res','trim',''],
            ['is_example','int',0],
            ['qc_score_inc','array',[]],
            ['qc_score_dec','array',[]],
        ]);

        $res = QcQuestionServer::logSave($p_data);

        if(!$res){
            $this->rs['code'] = 1;
            $this->rs['msg'] = 'error';
        }

        return return_json($this->rs);
    }
    #质检记录-修改
    public function logEdit(){
        $p_data = $this->getPost([
            ['id','int',0],
            ['qc_question_id','int',0],
            ['kf_id','int',0],
            ['kf_platform','trim',''],
            ['kf_source','int',0],
            ['kf_score','int',0],
            ['question_type','trim',''],
            ['game_type','trim',''],
            ['text','trim',''],
            ['qc_type','trim',''],
            ['qc_level','trim',''],
            ['qc_score','int',0],
            ['qc_comments','trim',''],
            ['kf_adventage','trim',''],
            ['kf_is_solve','int',0],
            ['kf_nosolve_res','trim',''],
            ['question_is_solve','int',0],
            ['question_nosolve_res','trim',''],
            ['is_example','int',0],
            ['qc_score_inc','array',[]],
            ['qc_score_dec','array',[]],
        ]);

        $res = QcQuestionServer::logSave($p_data);

        if(!$res){
            $this->rs['code'] = 1;
            $this->rs['msg'] = 'error';
        }

        return return_json($this->rs);
    }
    #质检记录-删除
    public function logDel(){

        $id = $this->request->post('id/d',0);

        if(!$id){
            $this->rs['code'] = 1;
            $this->rs['msg'] = '操作失败';
            return return_json($this->rs);
        }

        $res = QcQuestionServer::logDel($id);

        if($res){
            $this->rs['msg'] = '删除成功';
        }else{
            $this->rs['code'] = 2;
            $this->rs['msg'] = '删除失败';
        }

        return return_json($this->rs);
    }
    #质检详情-客服申述
    public function appeal(){
        $p_data = $this->getPost([
            ['reason','trim',''],
            ['id','int',0],
        ]);

        if(!$p_data['id']){
            $this->rs['code'] = 1;
            $this->rs['msg'] = '没有记录id';
            return return_json($this->rs);
        }

        $p_data['status'] = 1;

        $res = QcQuestionServer::doAppeal($p_data['id'],$p_data);

        if($res){
            $this->rs['msg'] = '操作成功';
        }else{
            $this->rs['code'] = 2;
            $this->rs['msg'] = '操作失败';
        }

        return return_json($this->rs);
    }
    #质检详情-申诉拒绝（一审）
    public function doAppealFirst(){

        $p_data = $this->getPost([
            ['reason','trim',''],
            ['id','int',0],
            ['val','int',0],
        ]);

        if(!$p_data['id']){
            $this->rs['code'] = 1;
            $this->rs['msg'] = '没有记录id';
            return return_json($this->rs);
        }

        if($p_data['val']){
            $p_data['status'] = 2;
        }else{
            $p_data['status'] = -1;
        }

        $res = QcQuestionServer::doAppeal($p_data['id'],$p_data);

        if($res){
            $this->rs['msg'] = '操作成功';
        }else{
            $this->rs['code'] = 2;
            $this->rs['msg'] = '操作失败';
        }

        return return_json($this->rs);
    }
    #质检详情-申诉拒绝（二审）
    public function doAppealSecond(){

        $p_data = $this->getPost([
            ['reason','trim',''],
            ['id','int',0],
            ['val','int',0],
        ]);

        if(!$p_data['id']){
            $this->rs['code'] = 1;
            $this->rs['msg'] = '没有记录id';
            return return_json($this->rs);
        }

        if($p_data['val']){
            $p_data['status'] = 3;
        }else{
            $p_data['status'] = -1;
        }

        $res = QcQuestionServer::doAppeal($p_data['id'],$p_data);

        if($res){
            $this->rs['msg'] = '操作成功';
        }else{
            $this->rs['code'] = 2;
            $this->rs['msg'] = '操作失败';
        }

        return return_json($this->rs);
    }

    #质检会话导入
    public function questionLeadingIn(){
        $code = [
            0=>'success',1=>'请选择来源',2=>'没有上传文件',3=>'不符合类型',4=>'数据不足',5=>'导入格式有误',6=>'没有符合插入的数据'
        ];

        $this_file = $this->request->file('file');;
        $source = $this->request->post('source/d',1);

        if($source<1){
            $this->rs['code'] = 1;
            $this->rs['msg'] = $code[1];
            return return_json($this->rs);
        }

        if(!$this_file){
            $this->rs['code'] = 2;
            $this->rs['msg'] = $code[2];
            return return_json($this->rs);
        }

        $file_mimes = array('text/x-comma-separated-values', 'text/comma-separated-values', 'application/octet-stream', 'application/vnd.ms-excel', 'application/x-csv', 'text/x-csv', 'text/csv', 'application/csv', 'application/excel', 'application/vnd.msexcel', 'text/plain', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $file_info = $this_file->getInfo();

        if(!in_array($file_info['type'], $file_mimes)) {
            $this->rs['code'] = 3;
            $this->rs['msg'] = $code[3];
            return return_json($this->rs);
        }

        $file_path_info = pathinfo($file_info['name']);

        if('csv' == $file_path_info['extension']) {
            $this->rs['code'] = 3.1;
            $this->rs['msg'] = $code[3];
            return return_json($this->rs);
            $reader = new Csv();
        } else {
            $reader = new Xlsx();
        }

        $spreadsheet = $reader->load( $file_info['tmp_name'] );

        $sheetData = $spreadsheet->getActiveSheet()->ToArray();

        if(count($sheetData)<2){
            $this->rs['code'] = 4;
            $this->rs['msg'] = $code[4];
            return return_json($this->rs);
        }

        $res = QcQuestionServer::addQuestion($sheetData,$source);

        if($res['code']==0){
            $this->rs['msg'] = $res['msg'];
        }else{
            $this->rs['code'] = $res['code'];
            $this->rs['msg'] = $code[$res['code']];
        }

        return return_json($this->rs);
    }

    #质检配置-详情
    public function qcConfigList()
    {

        $res = QcQuestionServer::qcConfigList();

        $this->rs = array_merge($this->rs,$res);

        return return_json($this->rs);
    }
    #质检配置-扣或加分配置详细（无权限）
    public function getScoreConfig(){

        $p_data = $this->getPost([
            ['kf_source','int',0],
            ['key','trim',''],
        ]);

        $list = QcQuestionServer::getScoreConfig($p_data);

        if(!$list){
            $this->rs['code'] = 1;
            $this->rs['msg'] = '没有数据';
        }

        $this->rs['data'] = $list;

        return return_json($this->rs);
    }
    #质检配置-保存
    public function configSave()
    {
        $p_data = $this->request->post();

        $res = QcQuestionServer::configSave($p_data);

        return return_json($this->rs);
    }

    #质检申诉记录-列表
    public function appealLogList(){

        $p_data = $this->getPost([
            ['page','int',1],
            ['limit','int',20],
            ['kf_id','int',0],
            ['group_id','int',0],
            ['status','trim',''],
            ['admin_id','int',0],
            ['qc_score_start','int',0],
            ['qc_score_end','int',0],
            ['kf_is_solve','int',-1],
            ['question_is_solve','int',-1],
            ['kf_source','trim',''],
            ['platform_id','trim',''],
            ['start','trim',''],
            ['end','trim',''],
            ['question_type','trim',''],
            ['game_type','trim',''],
            ['qc_num','trim',''],
            ['server_date','trim',''],
            ['qc_level','trim',''],
            ['qc_type','trim',''],
            ['text','trim',''],
        ]);

        $p_data['list_type'] = 'appeal';
        list($list,$count) = QcQuestionServer::AppealLogList($p_data);

        $this->rs['data'] = $list;
        $this->rs['count'] = $count;

        return return_json($this->rs);
    }
    #质检申诉记录-配置（无权限）
    public function appealLogConfig(){

        $this->rs['data'] = QcQuestionServer::appealLogConfig();

        return return_json($this->rs);
    }
}
