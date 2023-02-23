<?php
/**
 * Created by PhpStorm.
 * User: crosstime
 * Date: 2017/10/12
 * Time: 上午11:57
 */
namespace app\admin\controller;

use common\libraries\Common;
use common\server\Game\GameLtvServer;

class GameLtv extends Oauth
{


    public function index()
    {

        $data['start_sid'] = $this->request->post('start_sid/d', 1);
        $data['end_sid'] = $this->request->post('end_sid/d', 10);
        $data['start_time'] =  $this->request->post('start_time/s', '');
        $data['end_time'] = $this->request->post('end_time/s','');
        $data['type'] = $this->request->post('type/d',1);

        $res = GameLtvServer::getList($data);
        $return = [];
        $return['code'] = 0;
        $return['msg'] = '';
        $return['data'] = $res;
        $return['count'] = count($res);



        return return_json($return);

    }



}