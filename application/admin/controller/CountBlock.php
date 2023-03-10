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
use common\server\SysServer;


//use common\sql_server\QcConfig;


class CountBlock extends Oauth
{



    public function index()
    {
        $this->gamelist = Common::getProductList(2);

        $data['page'] = $this->request->request('page/d', 1);
        $data['limit'] = $this->request->request('limit/d', 20);
        $data['game'] = $this->request->post('game/s', '');
        $data['op_admin_id'] = $this->request->post('op_admin_id/s', '');

        $data['s_time'] = $this->request->post('s_time/s', date('Y-m-d', strtotime(date('Y-m-d'))));
        $data['e_time'] = $this->request->post('e_time/s', date('Y-m-d', strtotime(date('Y-m-d', time()))) . ' 23:59:59');
        $data['is_excel'] = $this->request->post('is_excel/d',0);

        $block_list = CountBlockServer::getList($data);

        $count = CountBlockServer::getCount($data);

        $return = [];

        $return['code'] = 0;
        $return['msg'] = '获取成功';
        $return['data'] = $block_list;
        $return['count'] = $count;


        return return_json($return);

    }


    public function getAdmin()
    {
        $p_data = ['role_id'=>'14,15,16,17,18,23,24,25,26'];
        $admin_list = SysServer::getUserListByAdminInfo($this->user_data,$p_data);


        $res['code'] = 0;
        $res['msg'] = '成功';
        $res['data'] = $admin_list;

        return return_json($res);

    }

    public function getGame()
    {
        $list = Common::getProductList();

        $res['code'] = 0;
        $res['msg'] = '成功';
        $res['data'] = $list;

        return return_json($res);

    }



}