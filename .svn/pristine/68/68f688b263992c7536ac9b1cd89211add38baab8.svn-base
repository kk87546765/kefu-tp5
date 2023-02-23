<?php
namespace app\scripts\controller;

use common\model\db_customer\QcQuestion;
use common\model\db_customer\SobotConversation;
use common\model\db_customer\SobotMsg;
use common\server\Qc\QcQuestionServer;
use common\server\SysServer;

class Sobot extends Base
{
    protected $sobot_config = [];

    protected $func_arr = [
        ['func'=>'updateQcQuestion','param'=>[0=>''],'delay_time'=>0,'runtime'=>120,'limit'=>0],
        ['func'=>'creatQcQuestion','param'=>[0=>''],'delay_time'=>0,'runtime'=>60,'limit'=>0],
    ];

    public function _initialize()
    {
        parent::_initialize();

        /*获取配置-begin*/
        $lxx = getArrVal($_GET,'lxx',0);

        $bast_config = SysServer::getAllConfigByCache();

        if(!$bast_config || !isset($bast_config['sobot']['script_open']) || !$bast_config['sobot']['script_open'] ){
            if(!$lxx){
                $this->fail('stop');
            }
        }

        $this->sobot_config = $bast_config['sobot'];
        /*获取配置-end*/
    }

    /*
    * run 缩减脚本命令
    * 函数约定
    * 1.未执行/执行失败 return
    * 2.执行成功 die
    */
    public function run(){

        $this->apiRun();

    }

    public function clean(){

        $name = getArrVal($_GET,'func','all');

        $this->apiClean($name);
        $this->s_json($name);
    }

    public function checkFuncList(){
        $res = $this->apiCheckFuncList();
        dd($res);
    }

    public function creatQcQuestion(){

        $SobotConversation = new SobotConversation();
        $where = [];
        $where['is_do'] = 0;
        $where['_string'] = '(consult_robot_msg_count > 0 OR robot_reply_msg_count > 0 OR consult_staff_msg_count > 0)';
        $where[] = ['add_time','<=',time()-1800];
        $where[] = ['add_time','>=',time()-12*3600*2*4];

        $c_list = $SobotConversation->where(setWhereSql($where,''))->order('add_time desc')->page(1,500)->select()->toArray();

        if(!$c_list){
            return true;
        }

        $source_arr = [];
        foreach ($this->sobot_config['company_list'] as $v){
            $source_arr[$v['company_id']] = $v['source_id'];
        }
        $c_list_new = [];
        foreach ($c_list as $k => $v){
            if(!isset($c_list_new[$v['companyid']])){
                $c_list_new[$v['companyid']]['companyid'] = $v['companyid'];
                $c_list_new[$v['companyid']]['kf_source'] = getArrVal($source_arr,$v['companyid'],0);
            }
            $c_list_new[$v['companyid']]['data'][] = $v;
        }

        if(!$c_list_new){
            return true;
        }
        $flag = 1;
        foreach ($c_list_new as $k =>$v){
            $res = $this->doByCompanyId($v['data'],$v['companyid'],$v['kf_source']);
            if($res){
                $flag = 0;
            }
        }

        return $flag;
    }

    public function updateQcQuestion(){

        $SobotConversation = new SobotConversation();
        $where = [];
        $where['is_do'] = 1;
        $where['need_update'] = 1;

        $c_list = $SobotConversation->where($where)->select()->toArray();

        if(!$c_list){
            return true;
        }

        $source_arr = [];
        foreach ($this->sobot_config['company_list'] as $v){
            $source_arr[$v['company_id']] = $v['source_id'];
        }
        $c_list_new = [];
        foreach ($c_list as $k => $v){
            if(!isset($c_list_new[$v['companyid']])){
                $c_list_new[$v['companyid']]['companyid'] = $v['companyid'];
                $c_list_new[$v['companyid']]['kf_source'] = getArrVal($source_arr,$v['companyid'],0);
            }
            $c_list_new[$v['companyid']]['data'][] = $v;
        }

        if(!$c_list_new){
            return true;
        }

        foreach ($c_list_new as $k =>$v){
            $this->updateByCompanyId($v['data'],$v['companyid'],$v['kf_source']);
        }

        return false;
    }

    protected function doByCompanyId($c_list,$companyid,$kf_source){
        $SobotMsg = new SobotMsg();

        $QcQuestion = new QcQuestion();
        $SobotConversation = new SobotConversation();

        $c_id_arr = [];
        foreach ($c_list as $v){
            $c_id_arr[] = $v['cid'];
        }

        $where = [];
        $where[] = ['cid','in',$c_id_arr];
        $where[] = ['companyid','=',$companyid];
        $msg_list = $SobotMsg->where(setWhereSql($where,''))->order('timems ASC,id ASC')->select()->toArray();

        if(!$msg_list){
            return false;
        }

        /*[lxxjsondata]
        "type": "1",
        "name": "测试玩家",
        "time": "",
        "text": "sad撒撒多所"*/
        $msg_info = [];
        foreach ($msg_list as $v){
            $this_info = [];
            $this_info['type'] = $v['sender_type'] == 0?1:2;
            $this_info['name'] = $v['sender_name'];
            $this_info['time'] = substr($v['timems'],0,10);
//            $this_info['text'] = htmlspecialchars(addslashes($v['msg']));
            $this_info['text'] = addslashes(htmlspecialchars($v['msg']));
            $msg_info[$v['cid']][] =$this_info;
        }

        $add_data = [];
        /*
            server_start
            server_end
            server_long
            text
            text_id
            kf_platform
            kf_source
            kf_score

            question_type
            game_type
        */
        $c_id_arr_update = [];

        foreach ( $c_list as $k =>$v) {
            if(!isset($msg_info[$v['cid']])){
                continue;
            }
            $where_qc_question = [];
            $where_qc_question['text_id'] = $v['cid'];
            $qc_question_count = $QcQuestion->where($where_qc_question)->count();
            $c_id_arr_update[] = $v['cid'];
            if($qc_question_count){
                continue;
            }

            $this_data = [];
            $this_data['server_start'] = substr($v['start_time'],0,10);
            $this_data['server_end'] = substr($v['end_time'],0,10);
            $this_data['server_long'] = ceil($v['conversation_duration']/60000);
            $this_data['text'] = '[lxxjsondata]'.serialize($msg_info[$v['cid']]);
            $this_data['text_id'] = $v['cid'];
            $this_data['kf_platform'] = QcQuestionServer::pregPlatform($v['lastgroup_name'],$kf_source);
            $this_data['kf_source'] = $kf_source;
            $this_data['kf_score'] = $v['score'];
            $this_data['partnerid'] = $v['partnerid'];
            $this_data['text_user_id'] = $v['visitorid'];
            $this_data['kf_id'] = 0;

            if($v['staff_name'] == 'robot'){//机器人
                $this_admin_info = QcQuestionServer::searchAdminByName($v['robot_name'],'all');

                if($this_admin_info){
                    $this_data['kf_id'] = $this_admin_info['id'];
                    $this_data['kf_platform'] = $this_admin_info['platform_id'];
                }
            }else{
                //客服
//                if($this_data['kf_platform']){
//                    $this_admin_info = QcQuestionServer::searchAdminByName($v['staff_name'],$this_data['kf_platform']);
//                }else{
                $this_admin_info = QcQuestionServer::searchAdminByName($v['staff_name'],'all');
//                }

                if($this_admin_info){
                    $this_data['kf_id'] = $this_admin_info['id'];
                    !$this_data['kf_platform'] && $this_data['kf_platform'] = $this_admin_info['platform_id'];
                }
            }

            $this_data['create_time'] = time();

            $add_data[] = $this_data;
        }

        if($add_data){

            $QcQuestion = new QcQuestion();

            $res = $QcQuestion->insertAll($add_data);

            if(!$res){
                return false;
            }
        }

        $where = [];
        $where[] = ['text_id','in',$c_id_arr_update];
        $where[] = ['kf_source','=',$kf_source];

        $sql = 'UPDATE qc_question SET qc_num = CONCAT('.date('Ymd').',id)'.setWhereSql($where);

        $res = $QcQuestion->execute($sql);

        $where = [];
        $where[] = ['cid','in',$c_id_arr_update];
        $where['companyid'] = $companyid;

        $sql = setUpdateSql('sobot_conversation',$where,['is_do'=>1],0);

        $res = $SobotConversation->execute($sql);

        return $res;
    }


    protected function updateByCompanyId($c_list,$companyid,$kf_source){

        $SobotMsg = new SobotMsg();

        $QcQuestion = new QcQuestion();
        $SobotConversation = new SobotConversation();

        $c_id_arr = [];
        foreach ($c_list as $v){
            $c_id_arr[] = $v['cid'];
        }

        $where = [];
        $where[] = ['cid','in',$c_id_arr];
        $where[] = ['companyid','=',$companyid];

        $msg_list = $SobotMsg->where(setWhereSql($where,''))->order('timems ASC,id ASC')->select()->toArray();

        if(!$msg_list){
            return false;
        }

        /*[lxxjsondata]
        "type": "1",
        "name": "测试玩家",
        "time": "",
        "text": "sad撒撒多所"*/
        $msg_info = [];
        foreach ($msg_list as $v){
            $this_info = [];
            $this_info['type'] = $v['sender_type'] == 0?1:2;
            $this_info['name'] = $v['sender_name'];
            $this_info['time'] = substr($v['timems'],0,10);
//            $this_info['text'] = htmlspecialchars(addslashes($v['msg']));
            $this_info['text'] = addslashes(htmlspecialchars($v['msg']));
            $msg_info[$v['cid']][] =$this_info;
        }

        $c_id_arr_update = [];

        foreach ( $c_list as $k =>$v) {
            if(!isset($msg_info[$v['cid']])){
                continue;
            }
            $where_qc_question = [];
            $where_qc_question['kf_source'] = $kf_source;
            $where_qc_question['text_id'] = $v['cid'];
            $qc_question_info = $QcQuestion->where($where_qc_question)->find();
            $c_id_arr_update[] = $v['cid'];
            if(!$qc_question_info){
                continue;
            }

            $save_data = [];
            $save_data['text'] = '[lxxjsondata]'.serialize($msg_info[$v['cid']]);

            $res = $qc_question_info->save($save_data);

            if($res){
                $c_id_arr_update[] = $v['cid'];
            }

        }

        $where = [];
        $where[] = ['cid','in',$c_id_arr_update];
        $where['companyid'] = $companyid;

        $sql = setUpdateSql('sobot_conversation',$where,['need_update'=>2],0);

        $res = $SobotConversation->execute($sql);

        return $res;
    }


}
