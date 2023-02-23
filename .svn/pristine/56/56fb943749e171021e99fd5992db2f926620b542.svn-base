<?php
/**
 * Created by PhpStorm.
 * User: crosstime
 * Date: 2017/10/12
 * Time: 上午11:57
 */

namespace app\admin\controller;

use common\base\BasicServer;

use common\server\Statistic\MouthUpholdStatisticsServer;
use common\server\SysServer;


class MouthUpholdStatistics extends Oauth
{



    public function index()
    {

        $data['p_p']          = $this->request->post('p_p/s', '');
        $data['platform_id']  = $this->request->post('platform_id/s', '');
        $data['p_g']          = $this->request->post('p_g/s', '');
        $data['date']         = $this->request->post('date/s', '');
        $data['admin_id']     = $this->request->post('admin_id/s', '');
        $data['page']         = $this->request->post('page/d', 1);
        $data['limit']        = $this->request->post('limit/d', 20);

        $list = MouthUpholdStatisticsServer::getList($data);

        $count = MouthUpholdStatisticsServer::getCount($data);

        $return['code'] = 0;
        $return['msg'] = '获取成功';

        if($list){
            $return['data'] = $list;
            $return['count'] = $count;
        }else{
            $return['msg'] = '暂无数据';
            $return['data'] = [];
        }

        return return_json($return);

    }

    public function getStatistic()
    {
        $data['p_p']          = $this->request->post('p_p/s', '');
        $data['platform_id']  = $this->request->post('platform_id/s', '');
        $data['p_g']          = $this->request->post('p_g/s', '');
        $data['date']     = $this->request->post('date/s', '');
        $data['admin_id']     = $this->request->post('admin_id/s', '');

        $res = MouthUpholdStatisticsServer::getStatistic($data);

        $return = [];
        $return['code'] = 0;
        $return['msg'] = '获取成功';
        if($res){
            $return['data'] = $res[0];
        }else{
            $return['msg'] = '暂无数据';
            $return['data'] = [];
        }

        return return_json($return);
    }




}