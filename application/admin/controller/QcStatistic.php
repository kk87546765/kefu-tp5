<?php
namespace app\admin\controller;

use common\server\Qc\QcStatisticServer AS thisServer;
class QcStatistic extends Oauth
{
    protected $no_oauth = [
        'conversationTypeConfig','qcScoreDecConfig','qcKfConfig'
    ];

    #会话类型统计-列表
    public function conversationType(){

        $p_data = $this->getPost([
            ['kf_id','int',0],
            ['page','int',1],
            ['limit','int',20],
            ['kf_is_solve','int',-1],
            ['question_is_solve','int',-1],
            ['kf_source','trim',''],
            ['platform_id','trim',''],
            ['create_time_start','trim',''],
            ['create_time_end','trim',''],
            ['question_type','trim',''],
            ['game_type','trim',''],
            ['server_start_start','trim',''],
            ['server_start_end','trim',''],
            ['qc_type','trim',''],
        ]);

        list($list,$count) = thisServer::getConversationTypeList($p_data);

        $this->rs['data'] = $list;
        $this->rs['count'] = $count;

        return return_json($this->rs);
    }
    #会话类型统计-配置（无权限）
    public function conversationTypeConfig(){

        $time_arr = timeCondition('month');
        $this->rs['data']['create_time_start'] = date('Y-m-d H:i:s',$time_arr['starttime']);
        $this->rs['config'] = thisServer::getConversationTypeConfig();

        return return_json($this->rs);
    }

    #会话扣分原因统计-列表
    public function qcScoreDec(){

        $p_data = $this->getPost([
            ['kf_id','int',0],
            ['page','int',1],
            ['limit','int',20],
            ['group_id','int',0],
            ['admin_id','int',0],
            ['kf_source','trim',''],
            ['platform_id','trim',''],
            ['create_time_start','trim',''],
            ['create_time_end','trim',''],
            ['question_type','trim',''],
            ['game_type','trim',''],
            ['server_start_start','trim',''],
            ['server_start_end','trim',''],
            ['qc_level','trim',''],
            ['qc_type','trim',''],
        ]);

        list($list,$count) = thisServer::getQcScoreDecList($p_data);

        $this->rs['data'] = $list;
        $this->rs['count'] = $count;

        return return_json($this->rs);
    }
    #会话扣分原因统计-配置（无权限）
    public function qcScoreDecConfig(){

        $time_arr = timeCondition('month');
        $this->rs['data']['create_time_start'] = date('Y-m-d H:i:s',$time_arr['starttime']);
        $this->rs['config'] = thisServer::getQcScoreDecConfig();

        return return_json($this->rs);
    }

    #质检统计(客服)-列表
    public function qcKf(){

        $p_data = $this->getPost([
            ['page','int',1],
            ['limit','int',20],
            ['kf_id','int',0],
            ['kf_source','trim',''],
            ['platform_id','trim',''],
            ['create_time_start','trim',''],
            ['create_time_end','trim',''],
            ['server_start_start','trim',''],
            ['server_start_end','trim',''],
            ['qc_level','trim',''],
            ['qc_type','trim',''],
        ]);

        $p_data['search_type'] = 'kf';

        list($list,$count) = thisServer::getQcKfList($p_data);

        $this->rs['data'] = $list;
        $this->rs['count'] = $count;

        return return_json($this->rs);
    }
    #质检统计(质检员)-列表
    public function qcAdmin(){

        $p_data = $this->getPost([
            ['page','int',1],
            ['limit','int',20],
            ['admin_id','int',0],
            ['kf_source','trim',''],
            ['platform_id','trim',''],
            ['create_time_start','trim',''],
            ['create_time_end','trim',''],
            ['server_start_start','trim',''],
            ['server_start_end','trim',''],
            ['qc_level','trim',''],
            ['qc_type','trim',''],
        ]);

        $p_data['search_type'] = 'admin';

        list($list,$count) = thisServer::getQcKfList($p_data);

        $this->rs['data'] = $list;
        $this->rs['count'] = $count;

        return return_json($this->rs);
    }
    #质检统计-配置（无权限）
    public function qcKfConfig(){

        $this->rs = array_merge($this->rs,thisServer::getQcKfConfig());

        return return_json($this->rs);
    }
}
