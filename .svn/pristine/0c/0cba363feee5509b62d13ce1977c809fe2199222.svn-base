<?php
/**
 * 系统
 */
namespace common\server\Qc;


use common\base\BasicServer;
use common\model\db_customer\SobotConversation;
use common\model\db_customer\SobotMsg;
use common\model\db_customer\SobotSummary;
use common\model\db_customer\SobotUsers;

class SobotServer extends BasicServer
{
    //会话列表
    public function conversation($content){

        $p_arr = [
            'companyid',
            'cid',
            'start_time',
            'end_time',
            'first_response_time',
            'conversation_duration',
            'staff_name',
            'lastgroupid',
            'lastgroup_name',
            'invite_evaluation_flags',
            'add_time',
            'session_human_duration',
            'robot_name',
            'city_name',
            'offline_type',
            'partnerid',
            'staff_reply_msg_count',
            'consult_robot_msg_count',
            'robot_reply_msg_count',
            'consult_staff_msg_count',
            'transfer_tohuman_time',
            'sources',
            'os',
            'visitorid',
        ];


        $add_data = [];

        $SobotConversation = new SobotConversation();

        foreach ($content as $v){

            $v['sources'] = $v['source'];
            $this_data = getDataByField($v,$p_arr);

            if(!$this_data){
                continue;
            }
            $this_data['os'] = intval($this_data['os']);

            $where = [];
            $where['cid'] = $this_data['cid'];

            $info = $SobotConversation->where($where)->count();

            if($info){
                continue;
            }
            $this_data['add_time'] = time();
            $add_data[] = $this_data;

        }

        if(!$add_data){
            return false;
        }

        $sql = setIntoSql('sobot_conversation',$p_arr,$add_data);

        $res = $SobotConversation->query($sql);

        return $res;
    }
    //对话信息
    public function msg($content){
        $p_arr = [
            'cid',
            'companyid',
            'timems',
            'senderid',
            'sender_name',
            'receiverid',
            'receiver_name',
            'msg',
            'doc_name',
            'sender_type',
            'receiver_type',
            'msg_offline',
            'add_time',
        ];
        $add_data = [];
        $update_cid = [];

        $model = new SobotMsg();

        foreach ($content as $v){

            $this_data = getDataByField($v,$p_arr);

            if(!$this_data){
                continue;
            }

            $this_data['msg'] = sqlChangeStr($this_data['msg']);
            $this_data['msg'] = emoji_encode($this_data['msg']);

            $where = [];
            $where['cid'] = $this_data['cid'];
            $where['timems'] = $this_data['timems'];

            $info = $model->where($where)->find();

            if($info){
                continue;
            }
            $this_data['add_time'] = time();
            $add_data[] = $this_data;
            $update_cid[] = $this_data['cid'];

        }

        if(!$add_data){
            return false;
        }

        $sql = setIntoSql('sobot_msg',$p_arr,$add_data);

        $res = $model->query($sql);

        if($update_cid){
            $where = [];
            $where['cid'] = ['in',$update_cid];
            $where['is_do'] = 1;

            $update_data = [];
            $update_data['need_update'] = 1;
            $update_data['update_time'] = time();
            $SobotConversation = new SobotConversation();
            $SobotConversation->where($where)->update($update_data);
        }

        return $res;
    }
    //用户评价
    public function evaluation($content){


        $p_arr = [
            'cid',
            'companyid',
//            'is_robot',
            'score',
            'solved',
            'evaluation_remark',
            'evaluation_date_time',
        ];

        $model = new SobotConversation();
//        $VipStatisticServer = new VipStatisticServer();

//        $add_scroe_list = [];

        foreach ($content as $v){

            if(!$v){//|| $v['is_robot']
                continue;
            }

            $v['evaluation_remark'] = $v['remark'];
            if($v['date_time']){
                $v['evaluation_date_time'] = substr($v['date_time'],0,10);
            }

            $this_data = getDataByField($v,$p_arr);

            $where = [];
            $where['cid'] = $this_data['cid'];

            $info = $model->where($where)->find();

            if(!$info){
//                $add_scroe_list[$this_data['cid']] = $this_data;
                continue;
            }
            $this_data['update_time'] = time();
            $info->save($this_data);

        }

//        if($add_scroe_list){
//            $VipStatisticServer->cache($cache_name);
//
//        }

        return true;
    }

    //会话总结
    public function summary($content){

        $p_arr = [
            'cid',
            'companyid',
            'operation_name',
            'req_type_name',
            'summary_description',
            'add_time',
        ];

        $model = new SobotSummary();

        $add_data = [];

        foreach ($content as $v){

            if(!$v){
                continue;
            }

            $this_data = getDataByField($v,$p_arr);

            $where = [];
            $where['cid'] = $this_data['cid'];
            $where['companyid'] = $this_data['companyid'];

            $info = $model->where($where)->find();

            if($info){
                continue;
            }
            $this_data['add_time'] = time();

            $add_data[] = $this_data;

        }

        if($add_data){
            $sql = setIntoSql('sobot_summary',$p_arr,$add_data);

            $model->query($sql);
        }

        return true;
    }

    //在线用户
    public function user($content){

        $p_arr = [
            'companyid',
            'userid',
            'partnerid',
            'remark',
            'summary_params',
            'add_time',
        ];

        $this->doUser($content,$p_arr);

        return true;
    }

    //在线用户详情
    public function userinfo($content){

        $p_arr = [
            'companyid',
            'userid',
            'nick',
            'sources',
            'visitorids',
            'add_time',
        ];

        $this->doUser($content,$p_arr);

        return true;
    }
    protected function doUser($content,$p_arr){
        $model = new SobotUsers();

        $add_data = [];

        foreach ($content as $v){

            if(!$v){
                continue;
            }

            $this_data = getDataByField($v,$p_arr);

            $where = [];
            $where['userid'] = $this_data['userid'];
            $where['companyid'] = $this_data['companyid'];

            $info = $model->where($where)->find();

            if($info){
                $this_update_data = [];
                foreach ($this_data as $k => $v){
                    if($v != $info->$k){
                        $this_update_data[$k] = $v;
                    }
                }
                if(!$this_update_data){
                    continue;
                }
                $this_update_data['add_time'] = time();
                $res = $info->save($this_update_data);
                $model = new SobotUsers();
            }else{
                $this_data['add_time'] = time();

                $add_data[] = $this_data;
            }

        }

        if($add_data){
            $sql = setIntoSql('sobot_users',$p_arr,$add_data);

            $model->query($sql);
        }
        return true;
    }
}
