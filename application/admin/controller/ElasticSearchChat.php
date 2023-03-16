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
use common\server\ElasticSearchChat\ElasticSearchChatServer;


class ElasticSearchChat extends Oauth
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
        $data['dateEnd']      = $this->request->post('edate/s');
        $data['sid_min']    = $this->request->post('sid_min/s', '');
        $data['sid_max']    = $this->request->post('sid_max/s', '');
        $data['keyword']    = $this->request->post('keyword/s', '');
        $data['tkey']       = $this->request->post('tkey/s', '');
        $data['level_min']  = $this->request->post('level_min/s', '');
        $data['level_max']  = $this->request->post('level_max/s', '');
        $data['money_min']  = $this->request->post('money_min/s', '');
        $data['money_max']  = $this->request->post('money_max/s', '');
        $data['platform_id']  = $this->request->post('platform_id/d', 0);

//        $platform_key = $this->session->get('platform_key');
        $res = ElasticSearchChatServer::getList($data);

        $this->rs['code'] = $res['code'];
        $this->rs['msg'] = $res['msg'];
        $this->rs['data'] = $res['data'];
        $this->rs['count'] = isset($res['total'])? $res['total'] : 0;
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

    public function check_chat4(){
        $uid       = $this->request->get('uid','trim', '');
        $date      = $this->request->get('date','trim', '');
        $s_date = strtotime(date('Y-m-d',strtotime($date)));
        $e_date = strtotime(date('Y-m-d',strtotime($date)))+86400;

        $query = Common::composePlatform();
        $this->es = new ElasticSearch();
        $limit = 10000;
        $page = 1;

        $bool['bool']['filter']['bool']['should'][]['match']['uid'] = $uid;
        $bool['bool']['filter']['bool']['should'][]['match']['to_uid'] = $uid;
        $bool['bool']['filter']['bool']['minimum_should_match'] = 1;

        if($s_date){
            $range0['range']['time']['gte']  = $s_date;

        }

        if($e_date){
            $range0['range']['time']['lte']  = $e_date;

        }

        if($range0){
            $bool['bool']['filter']['bool']['must'][] = $range0;
        }


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

}