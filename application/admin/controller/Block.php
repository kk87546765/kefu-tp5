<?php
/**
 * Created by PhpStorm.
 * User: crosstime
 * Date: 2017/10/12
 * Time: 上午11:57
 */
namespace app\admin\controller;


use Cassandra\Varint;
use common\libraries\{Common,IP4datx,ElasticSearch};

use common\server\ElasticSearchChat\ElasticSearchChatServer;
use common\server\Role\RoleServer;
use common\server\Platform\BanServer;

use common\server\Block\BlockServer;
use common\server\Sdk\UserServer;
use common\sql_server\BlockSqlServer;


class Block extends Oauth
{

//    const JIU_ZHOU_GAME = 'jzxjz';

    public $types = [
        1  => '私聊',
        2  => '喇叭',
        3  => '邮件',
        4  => '世界',
        5  => '国家',
        6  => '工会/帮会',
        7  => '队伍',
        8  => '附近',
        9  => '其他',
        10 => '跨服',
    ];


    /**
     * 封禁用户
     */
    public function blockUser()
    {

        $data['ids']    = (array)$this->request->post('a_ids', '');
        $data['b_ids'] = (array)$this->request->post('b_ids/s', '');
        if( empty($ids) ) $data['ids'] = $data['b_ids'];
        $data['blockid'] = (array)$this->request->post('blockid/d',0);
        $data['status'] = $this->request->post('type/s',  BlockServer::STATUS_BLOCK);

        $data['block_time'] = $this->request->post('block_time/d',  self::BLOCK_TIME);//默认封禁1年
        $data['admin_user'] = $this->user_data['username'];
        $data['op_ip']      =  $this->user_data["last_ip"];

        $res = BlockServer::block($data);

        return return_json($res);
    }



    /**
     * 禁言
     * @api
     */
    public function blockChat()
    {

        $data['ids'] = $this->request->post('a_ids/a');
        $data['b_ids'] = $this->request->post('b_ids/a');
        if( empty($data['ids']) ) $data['ids'] = $data['b_ids'];

        $data['blockid'] = $this->request->post('blockid');
        $data['status'] = $this->request->post('type', BlockServer::STATUS_BLOCK);

        $data['block_time'] = $this->request->post('block_time/d',BlockServer::CHAT_TIME);//默认封禁1年
        $data['admin_user'] = $this->user_data['username'];
        $data['op_ip']      = $this->user_data["last_ip"];

        $res = BlockServer::blockChat($data);

        return return_json($res);

    }
    /**
     * 一键解封
     * @api
     */
    public function unblockMixed()
    {
        $data['blockid'] = $this->request->post('blockid');
        $data['admin_user'] = $this->user_data['username'];
        $data['op_ip']      = $this->user_data["last_ip"];
        $res = BlockServer::unblockMixed($data);
        return return_json($res);
    }



    /**
     * 封禁列表-新UI
     */
    public function index()
    {

        $data['page']            = $this->request->post('page/d',1);
        $data['limit']           = $this->request->post('limit/d',20);
        $data['game']            = $this->request->post('game/s','');
        $data['rolename']        = $this->request->post('rolename/s','');
        $data['ip']              = $this->request->post('ip/s','');
        $data['imei']            = $this->request->post('imei/s','');
        $data['sid']             = $this->request->post('sid/s','');
        $data['uid']             = $this->request->post('uid/d',0);
        $data['type']            = $this->request->post('type/s','');
        $data['op_type']         = $this->request->post('op_type/s','');
        $data['admin']           = $this->request->post('admin/s','');
        $data['dateStart']       = $this->request->request('sdate', date('Y-m-d 00:00:00'));
        $data['dateEnd']         = $this->request->request('edate', date('Y-m-d 23:59:59'));
        $data['reg_channel_id']  = $this->request->post('reg_channel_id/d', 0);
        $data['platform_id']     = $this->request->post('platform_id/d', 0);
        $data['status']          = $this->request->post('status');
        $data['order']           = $this->request->post('order/s','');

        $res = BlockServer::getList($data);
        $count = BlockServer::getCount($data);

        if($res['code'] == 0){
            $return['code'] = 0;
            $return['msg'] = '获取成功';
            $return['data'] = $res['data'];
            $return['count'] = $count;
        }else{
            $return['code'] = $res['code'];
            $return['msg'] = $res['msg'];
        }

        return return_json($return);

    }




    public function check_chat(){
        $uid       = $this->request->get('uid','trim', '');
        $query = Common::composePlatform();
        $filter = '';
        $filter .= 'uid='. $uid. '';

        $result = $this->search->query($query, $filter, 10, 1);
        $result = json_decode($result->result, true);
        $messages = [];
        foreach ($result['result']['items'] as $v) {
            $item = $v['fields'];
            $item['date'] = date('Y-m-d H:i:s', $v['fields']['time']);
            $item['typename'] = $this->types[$v['fields']['type']];
            $item['gkey'] = $this->gamelist[$v['fields']['gkey']];
            $item['attribution'] = implode('', IP4datx::find($v['fields']['ip']));
            $messages[] = $item;
        }

        $this->view->setVar('result',$messages);
    }


    public function check_chat2(){
        $uid       = $this->request->get('uid','trim', '');
        $query = Common::composePlatform();
        $filter = '';

//        $dateStart = date('Y-m-d 00:00:00',time()-86400*14);
//        $dateEnd = date('Y-m-d 23:59:59', strtotime($dateStart." +1 day") );


//        $edate = empty($dateEnd)?date("Y-m-d H:i:s"):$dateEnd;
//        $filter .= sprintf("time>%s AND time<%s", strtotime($dateStart), strtotime($edate));

        $filter .= ' uid='. $uid. '';

        $result = $this->search->query($query, $filter, 500, 1);
        $result = json_decode($result->result, true);

        $messages = [];
        foreach ($result['result']['items'] as $v) {
            $item = $v['fields'];
            $item['date'] = date('Y-m-d H:i:s', $v['fields']['time']);
            $item['typename'] = $this->types[$v['fields']['type']];
            $item['gkey'] = $this->gamelist[$v['fields']['gkey']];
            $item['attribution'] = implode('', IP4datx::find($v['fields']['ip']));
            $messages[] = $item;
        }


        $this->view->setVar('result',$messages);
    }


    public function check_chat3(){
        $uid       = $this->request->get('uid', '');
        $tkey      = $this->request->get('tkey', '');
        $this->es = new ElasticSearch();
        $limit = 10000;
        $page = 1;

        $bool['bool']['filter']['bool']['must'][]['term']['uid'] = $uid;
        $bool['bool']['filter']['bool']['must'][]['term']['tkey'] = $tkey;


        $last_month = Common::GetMonth(1);
        $now_month = date('Ym');
        $next_month = Common::GetMonth(0);

        $result = $this->es->search(
            [
                $this->es->index_name.'-'.$last_month,
                $this->es->index_name.'-'.$now_month,
                $this->es->index_name.'-'.$next_month
            ],
            $bool,
            '',
            ['time'=>['order'=>'desc']],
            $page,
            $limit
        );

        foreach ($result['data'] as &$v) {
            $v['date'] = date('Y-m-d H:i:s', $v['time']);
            $v['typename'] = $this->types[$v['type']];
            $v['gkey'] = $this->gamelist[$v['gkey']]['name'];
            $v['attribution'] = implode('', IP4datx::find($v['ip']));
            $v['ip'] = $v['ip']."[{$v['attribution']}]";
        }


        $this->view->setVar('result',$result['data']);
    }

    public function getTypes()
    {

        $res = ElasticSearchChatServer::$types;

        $this->rs['code'] = 0;
        $this->rs['msg'] = '获取成功';
        $this->rs['data'] = $res;
        return return_json($this->rs);

    }



}