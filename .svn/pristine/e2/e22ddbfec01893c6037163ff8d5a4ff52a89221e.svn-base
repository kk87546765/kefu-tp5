<?php

namespace app\admin\controller;

use common\libraries\Common;
use common\server\WhiteId\WhiteIdServer;
//use common\Models\GarrisonProduct;
//use common\Models\WhiteId;
class WhiteId extends Oauth
{



    public function index()
    {

        $data['page']       = $this->request->get('page/d',  1);
        $data['limit']      = $this->request->get('limit/d', 20);

        $list = WhiteIdServer::getList($data);
        $total = WhiteIdServer::getCount(['status'=>1]);

        $this->rs['code'] = 0;
        $this->rs['msg'] = $list['msg'];
        $this->rs['data'] = $list['data'];
        $this->rs['count'] = $total;

        return return_json($this->rs);
    }


    public function add()
    {
        $data['uids'] = $this->request->post('uid/s','');
        $data['game'] = $this->request->post('game/s', '');

        $res = WhiteIdServer::add($data);

        return return_json($res);
    }

    public function del()
    {
        $id  = $this->request->post('id/d',  0);

        $res = WhiteIdServer::del($id);
        return return_json($res);
    }

    public function edit()
    {

        $data['id'] = $this->request->post('id/d', 0);
        $data['uid'] = $this->request->post('uid/d', 0);
        $data['game'] = $this->request->post('game/s', '');

        $res = WhiteIdServer::edit($data);
        return return_json($res);
    }

    public function getEdit()
    {
        $id = $this->request->get('id/s',0);
        $res = WhiteIdServer::getOne($id);
        return return_json($res);
    }


   public function reset()
   {
       $res = WhiteIdServer::reset();
       return return_json($res);
   }

   public function show()
   {

       $res = WhiteIdServer::show();

       return return_json($res);

   }

}