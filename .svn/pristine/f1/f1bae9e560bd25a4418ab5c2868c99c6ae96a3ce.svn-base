<?php
/**
 * Created by PhpStorm.
 * User: crosstime
 * Date: 2017/10/12
 * Time: 上午11:57
 */

namespace app\admin\controller;


use common\libraries\Common;
use common\server\ServerTurnover\ServerTurnoverServer;
use common\server\GarrisonProduct\GarrisonProductServer;
use common\sql_server\ServerStatisticSqlServer;

//use common\Logics\AdminServer;
//use common\Models\GarrisonProduct;
//use common\Models\DistributionalServer;
//use common\Models\KefuUserRole;
//use common\Models\ServerStatistic;
//use common\Models\Admin;
//

class ServerTurnover extends Oauth
{


    public function index()
    {

        $data['garrison_product_id']  = $this->request->post('garrison_product_id/s', '');
        $data['server_id'] = $this->request->post('server_id/s', '');
        $data['admin_name'] = $this->request->post('admin_name/s', '');
        $data['status'] = $this->request->post('status/s', '');
        $data['open_time_1'] = $this->request->request('s_open_time/s', '');
        $data['open_time_2'] = $this->request->request('e_open_time/s', '');
        $data['end_time_1'] = $this->request->request('s_end_time/s', '');
        $data['end_time_2'] = $this->request->request('e_end_time/s', '');
        $data['start_time'] = $this->request->post('start_time/s', '');
        $data['is_excel'] = $this->request->post('is_excel/d', 0);

        $data['page']       = $this->request->post('page',1);
        $data['limit']      = $this->request->post('limit', 20);

//        $list = ServerTurnoverServer::getList($data);
        $list = [];
//        $count = ServerTurnoverServer::getCount($data);


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


    public function getProductList()
    {
        $product_list = GarrisonProductServer::getGarrisonProductList();
        $res['code'] = 0;
        $res['msg'] = '获取成功';
        $res['data'] = $product_list;
        return return_json($res);
    }


    public function getCount(){

        $data['garrison_product_id']  = $this->request->post('garrison_product_id/s', '');
        $data['server_id'] = $this->request->post('server_id/s', '');
        $data['admin_name'] = $this->request->post('admin_name/s', '');
        $data['status'] = $this->request->post('status/s', '');
        $data['open_time_1'] = $this->request->post('s_open_time/s', '');
        $data['open_time_2'] = $this->request->post('e_open_time/s', '');
        $data['end_time_1'] = $this->request->post('s_end_time/s',  '');
        $data['end_time_2'] = $this->request->post('e_end_time/s',  '');


        $data['page']       = $this->request->post('page/d',  1);
        $data['limit']      = $this->request->post('limit/d', 20);


        $count = ServerTurnoverServer::getMoneyCount($data);

        $res['code'] = 0;
        $res['msg'] = '获取成功';
        $res['data'] = $count;
        return return_json($res);
    }



    public function check_pay_history(){
        $server_id       = $this->request->get('server_id', 'int');
        $product_id      = $this->request->get('product_id', 'int');
        $platform_id      = $this->request->get('platform_id', 'int');
        $open_time      = $this->request->get('open_time', 'trim');

        if(empty($server_id) || empty($product_id) || empty($platform_id) || empty($open_time)){
            return false;
        }
        $open_time = strtotime($open_time);


        $server_money = ServerStatisticSqlServer::getTimeRegAndMoney($product_id,$platform_id,$server_id,$open_time,10);


        $this->view->setVar('result',$server_money);
    }






}