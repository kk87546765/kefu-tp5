<?php
/**
 * Created by PhpStorm.
 * User: crosstime
 * Date: 2017/10/12
 * Time: 上午11:57
 */

namespace app\admin\controller;


//use common\Models\Admin;
//use common\Models\GarrisonProduct;
//use common\Models\AccountRegistration;
use common\server\Settled\SettledServer;


class Settled extends Oauth
{

    public $admin_department = [
        1=>['id'=>1,'name'=>'1组'],
        2=>['id'=>2,'name'=>'2组'],
        3=>['id'=>3,'name'=>'3组'],
        4=>['id'=>4,'name'=>'4组'],
        5=>['id'=>5,'name'=>'5组'],
    ];




    //新版UI
    public function index()
    {


        $data['page']   = $this->request->post('page', 1);
        $data['limit']  = $this->request->post('limit', 20);
        $data['garrison_product_id'] = $this->request->post('garrison_product_id', '');
        $data['s_open_time'] = $this->request->request('s_open_time', '');
        $data['e_open_time'] = $this->request->request('e_open_time', '');
        $data['excel'] = $this->request->post('excel', 0);


        $infos = SettledServer::getList($data);

        $total = SettledServer::getCount($data);

        $res['code'] = 0;
        $res['msg'] = '获取成功';
        $res['data'] = $infos;
        $res['count'] = $total;
        return return_json($res);


    }



    //新版UI
    public function ltvIndex()
    {

        $data['page']   = $this->request->post('page', 1);
        $data['limit']  = $this->request->post('limit', 20);
        $data['garrison_product_id'] = $this->request->post('garrison_product_id', '');
        $data['s_open_time'] = $this->request->request('s_open_time', '');
        $data['e_open_time'] = $this->request->request('e_open_time', '');
        $data['excel'] = $this->request->post('excel', 0);

        $infos = SettledServer::getLTVList($data);

        $res['code'] = 0;
        $res['msg'] = '获取成功';
        $res['data'] = $infos;

        return return_json($res);


    }



}