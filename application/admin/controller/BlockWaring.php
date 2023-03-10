<?php
/**
 * Created by PhpStorm.
 * User: crosstime
 * Date: 2017/10/12
 * Time: 上午11:57
 */
namespace app\admin\controller;


use common\libraries\Common;
use common\libraries\ElasticSearch;
use common\server\Block\BlockWaringServer;


class BlockWaring extends Oauth
{
    public $query = '';
    public $filter = '';
    public $es = '';





    //gte：大于等于
    //gt：大于
    //lte：小于等于
    //lt：小于

    //新UI
    public function index()
    {

        $data['page']       = $this->request->post('page/d', 1);
        $data['limit']      = $this->request->post('limit/d', 20);
        $data['game']       = $this->request->post('game/s', '');
        $data['type']       = $this->request->post('type/d', 0);
        $data['sid']        = $this->request->post('sid/s',  '');
        $data['uid']        = $this->request->post('uid/s', '');
        $data['ip']         = $this->request->post('ip/s',  '');
        $data['imei']       = $this->request->post('imei/s', '');
        $data['uname']      = $this->request->post('uname/s', '');
        $data['dateStart']  = $this->request->post('sdate/s', date('Y-m-d 00:00:00'));
        $data['dateEnd']    = $this->request->post('edate/s',date('Y-m-d 23:59:59'));
        $data['sid_min']    = $this->request->post('sid_min/s', '');
        $data['sid_max']    = $this->request->post('sid_max/s', '');
        $data['content']    = $this->request->post('content/s', '');
        $data['tkey']       = $this->request->post('tkey/s', '');
        $data['level_min']  = $this->request->post('level_min/s', '');
        $data['level_max']  = $this->request->post('level_max/s', '');
        $data['money_min']  = $this->request->post('money_min/s', '');
        $data['money_max']  = $this->request->post('money_max/s', '');
//        $data['platform_id']= $this->request->post('platform_id/d', 0);
        $data['ext']        = $this->request->post('ext/s', '');
        $data['order']      = $this->request->post('order/s', 'id desc');

//        $platform_key = $this->session->get('platform_key');
        $res = BlockWaringServer::getList($data);
        $count = BlockWaringServer::getCount($data);

        $this->rs['code'] = $res['code'];
        $this->rs['msg'] = $res['msg'];
        $this->rs['data'] = $res['data'];
        $this->rs['count'] = $count;
        return return_json($this->rs);


    }

    public function getTypes()
    {
        $res = ElasticSearchChatServer::$types;
        $this->rs['code'] = 0;
        $this->rs['msg'] = '获取成功';
        $this->rs['data'] = $res;
        return return_json($this->rs);

    }

    public function update()
    {
        $data['ids'] = $this->request->post('a_ids/a',[]);
        $res = BlockWaringServer::updateStatus($data['ids']);
        $this->rs['code'] = 1;
        $this->rs['msg'] = '成功';
        $this->rs['data'] = $res;
        return return_json($this->rs);
    }

    public function del()
    {
        $data['ids'] = $this->request->post('a_ids/a',[]);
        $res = BlockWaringServer::del($data['ids']);
        $this->rs['code'] = $res;
        return return_json($this->rs);
    }

    public function check_chat4()
    {
        $data['uname']  = urldecode($this->request->get('uname/s', ''));
        $data['uid']    = $this->request->get('uid/d', 0);
        $data['gkey']   = $this->request->get('gkey/s', '');
        $data['date']   = $this->request->get('date/s', '');
        $data['s_date'] = strtotime(date('Y-m-d', $data['date']));
        $data['e_date'] = strtotime(date('Y-m-d', $data['date'])) + 86400;

        $res = ElasticSearchChatServer::getCheckChat($data);

        $this->rs['code'] = 0;
        $this->rs['msg'] = '获取成功';
        $this->rs['data'] = $res;
        return return_json($this->rs);
    }

}