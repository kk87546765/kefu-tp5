<?php

namespace app\admin\controller;


use common\sql_server\BanImeiLog;
use common\sql_server\BanIpLog;
use common\sql_server\BanUserLog;
use common\sql_server\Log;

class Player extends Oauth
{
    private $banTypeData = [
        1=>'封禁',
        2=>'解封'
    ];

    public function ban_ip_list()
    {
        $fieldArr = [
            'type'=>['name'=>'处理类型','type'=>'int'],
            'ip'=>['type'=>'string','name'=>'IP'],
            'dateline'=>['type'=>'time','name'=>'处理时间'],
            'admin_user'=>['type'=>'string','name'=>'处理人'],
            'ban_time'=>['type'=>'int','name'=>'封禁时长'],
            'reason'=>['type'=>'string','name'=>'说明']
        ];

        $postData = $this->request->post();
        $limit = $this->request->get('limit/d', 20);
        $page = $this->request->get('page/d', 1);

        $where='1=1';
        foreach ($fieldArr as $k => $v){
            if($v['type']!='time' && !empty($postData[$k])){
                $where .=" and {$k} = '{$postData[$k]}'";
            }
        }
        $postData['sdate'] = $postData['sdate']??date('Y-m-d');
        if( !empty($postData['sdate']) ){
            $where  .=  " and dateline>=".strtotime($postData['sdate']);
        }

        if( !empty($postData['edate']) ){
            $where  .=  " and dateline<=".strtotime($postData['edate']);
        }

        $log = BanIpLog::getList($where,($page - 1) * $limit,$limit,'dateline desc');


        $total = BanIpLog::getCount($where);

        foreach( $log as &$v ){
            $v['type']      = $this->banTypeData[$v['type']];
//            $v['game_sign'] = $this->game_sign[$v['game_sign']];
            $v['dateline']      = date("Y-m-d H:i:s",$v['dateline']);
            $v['ban_time']  = $v['ban_time']?:'永久';
        }


        $this->rs['code'] = 0;
        $this->rs['msg'] = '获取成功';
        $this->rs['data'] = $log;
        $this->rs['count'] = $total;
        return return_json($this->rs);

    }

    public function ban_imei_list()
    {
        $fieldArr = [
            'type'=>['name'=>'处理类型','type'=>'int'],
            'imei'=>['type'=>'string','name'=>'设备号'],
            'dateline'=>['type'=>'time','name'=>'处理时间'],
            'admin_user'=>['type'=>'string','name'=>'处理人'],
            'ban_time'=>['type'=>'int','name'=>'封禁时长'],
            'reason'=>['type'=>'string','name'=>'说明']
        ];


        $postData = $this->request->post();
        $limit = $this->request->get('limit/d', 20);
        $page = $this->request->get('page/d', 1);

        $where='1=1';
        foreach ($fieldArr as $k => $v){
            if($v['type']!='time' && !empty($postData[$k])){
                $where .=" and {$k} = '{$postData[$k]}'";
            }
        }
        $postData['sdate'] = $postData['sdate']??date('Y-m-d');
        if( !empty($postData['sdate']) ){
            $where  .=  " and dateline>=".strtotime($postData['sdate']);
        }

        if( !empty($postData['edate']) ){
            $where  .=  " and dateline<=".strtotime($postData['edate']);
        }


        $log = BanImeiLog::getList($where, ($page - 1) * $limit,$limit,  'dateline desc');

        $total = BanImeiLog::getCount($where);

        foreach( $log as &$v ){
            $v['type']      = $this->banTypeData[$v['type']];
//            $v['game_sign'] = $this->game_sign[$v['game_sign']];
            $v['dateline']      = date("Y-m-d H:i:s",$v['dateline']);
            $v['ban_time']  = $v['ban_time']?:'永久';
        }


        $this->rs['code'] = 0;
        $this->rs['msg'] = '获取成功';
        $this->rs['data'] = $log;
        $this->rs['count'] = $total;
        return return_json($this->rs);
    }

    public function ban_user_list()
    {
        $fieldArr = [
            'uid'=>['name'=>'平台id','type'=>'string'],
            'type'=>['name'=>'处理类型','type'=>'int'],
            'user_name'=>['type'=>'string','name'=>'账号'],
            'dateline'=>['type'=>'time','name'=>'处理时间'],
            'admin_user'=>['type'=>'string','name'=>'处理人'],
            'ban_time'=>['type'=>'int','name'=>'封禁时长'],
            'reason'=>['type'=>'string','name'=>'说明']
        ];


        $postData = $this->request->post();
        $limit = $this->request->get('limit/d', 20);
        $page = $this->request->get('page/d', 1);

        $where='1=1';
        foreach ($fieldArr as $k => $v){
            if($v['type']!='time' && !empty($postData[$k])){
                $where .=" and {$k} = '{$postData[$k]}'";
            }
        }
        $postData['sdate'] = $postData['sdate']??date('Y-m-d');
        if( !empty($postData['sdate']) ){
            $where  .=  " and dateline>=".strtotime($postData['sdate']);
        }

        if( !empty($postData['edate']) ){
            $where  .=  " and dateline<=".strtotime($postData['edate']);
        }

        $log = BanUserLog::getList($where, ($page - 1) * $limit,$limit,  'dateline desc');

        $total = BanUserLog::getCount($where);

        foreach( $log as &$v ){
            $v['type']      = $this->banTypeData[$v['type']];
//            $v['game_sign'] = $this->game_sign[$v['game_sign']];
            $v['dateline']      = date("Y-m-d H:i:s",$v['dateline']);
            $v['ban_time']  = $v['ban_time']?:'永久';
        }

        $this->rs['code'] = 0;
        $this->rs['msg'] = '获取成功';
        $this->rs['data'] = $log;
        $this->rs['count'] = $total;
        return return_json($this->rs);

    }
}