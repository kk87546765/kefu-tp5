<?php
/**
 * Created by PhpStorm.
 * User: crosstime
 * Date: 2018/8/11
 * Time: 下午5:43
 */
namespace app\admin\controller;

use app\admin\controller\Oauth;
use common\libraries\Common;
use common\server\CountBlock\CountBlockServer;


//use common\sql_server\QcConfig;


class CountBlock extends Oauth
{



    public function index()
    {
        $this->gamelist = Common::getProductList(2);

        $data['page'] = $this->request->post('page/d', 1);
        $data['limit'] = $this->request->post('limit/d', 20);
        $data['game'] = $this->request->post('game/s', '');
        $data['op_admin_id'] = $this->request->post('op_admin_id/s', '');
        $data['s_time'] = $this->request->post('s_time/s', date('Y-m-d', strtotime(date('Y-m-d'))));
        $data['e_time'] = $this->request->post('e_time/s', date('Y-m-d', strtotime(date('Y-m-d', time()))) . ' 23:59:59');

        $block_list = CountBlockServer::getList($data);

        $count = CountBlockServer::getCount($data);

        $return = [];
        if($block_list){
            $return['code'] = 0;
            $return['msg'] = '获取成功';
            $return['data'] = $block_list;
            $return['count'] = $count;
        }

        return return_json($return);


//        $search_config = [
//            'platform'=>[
//                'status'=> 0,
//
//            ],
//            'admin'=>[
//                'title'=>'封禁人',
//                'status'=>1,
//                'name'=>'op_admin_id',
//            ],
//        ];
//
//
//
//        $this->view->setVar('search_config', $search_config);
//        $this->view->setVar('gamelist', $this->gamelist);


    }

}