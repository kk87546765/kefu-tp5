<?php

namespace app\admin\controller;

use common\libraries\Common;
//use common\server\Game\ChangeCpUidServer;

class CustomerProductChangeGame extends Oauth
{

    public $rs = ['code' => 1,'msg'=>'','data'=>[],'count'=>0];


    public function index()
    {

        $data['page']       = $this->request->get('page/d',  1);
        $data['limit']      = $this->request->get('limit/d', 20);

        $list = CustomerProductChangeGame::getList($data);
        $total = CustomerProductChangeGame::getCount(['status'=>1]);

        $this->rs['code'] = 0;
        $this->rs['msg'] = $list['msg'];
        $this->rs['data'] = $list['data'];
        $this->rs['count'] = $total;

        return return_json($this->rs);
    }


    public function getProduct()
    {

        $res = [
            ['code'=>'nbcq','name'=>'牛逼传奇'],
            ['code'=>'dxcq2','name'=>'大侠传奇2'],
            ['code'=>'yscqios','name'=>'原始传奇ios'],
            ['code'=>'yscqyy','name'=>'原始传奇-游娱'],
        ];
        $this->rs['code'] = 0;
        $this->rs['data'] = $res;

        return return_json($this->rs);
    }

    public function change()
    {
        $data['type']       = $this->request->post('type/d',  1);
        $data['product']    = $this->request->post('product/s', '');
        $data['in_uid']     = $this->request->post('in_uid/s', '');

        $id = CustomerProductChangeGameServer::change($data);

        $this->rs['code'] = 0;
        $this->rs['data'] = json_encode($id);
        return return_json($this->rs);


    }

}