<?php
/**
 * Created by PhpStorm.
 * User: crosstime
 * Date: 2017/10/12
 * Time: 上午11:57
 */

namespace app\admin\controller;


use common\libraries\Common;
use common\server\Sensitive\SensitiveServer;
class  Sensitive extends Oauth
{

    public $sensitive_keyword_key = 'sensitive';
    protected $gamelist;

    public function _initialize()
    {
        parent::_initialize();
        $this->gamelist = Common::getProductList(2);
        $this->gamelist['common']['name'] ="公共";

    }


    public function keyword_list(){
        $key = $this->request->get('key', 'trim', '');
        $res = $this->struct->get()->SMEMBERS($this->sensitive_keyword_key.'_'.$key);
        var_dump($res);
    }


    public function modift_keyword_list(){
        $res = Sensitive::find();
        $product_list = Common::getProductList(1);

        foreach($product_list as $k=>$v){
            $this->struct->get()->delete( $this->sensitive_keyword_key.'_'.$v['code'] );
        }
        $this->struct->get()->delete( $this->sensitive_keyword_key.'_common' );

        foreach( $res->toArray() as $v ){

            $t_content = str_replace(array(".","+"), array("",""), $v['keyword']);
            $this->struct->get()->sadd($this->sensitive_keyword_key.'_'.$v['game'],$t_content );

        }

    }


    //新版UI
    public function index()
    {

        $data['page']       = $this->request->post('page/d', 1);
        $data['limit']      = $this->request->post('limit/d',  20);
        $data['game']       = $this->request->post('game/s', '');
        $data['keyword']    = $this->request->post('keyword/s', '');
        $data['gamelist']   = $this->gamelist;

        $keywords = SensitiveServer::getList($data);

        if($keywords){
            $this->rs['code'] = 0;
            $this->rs['msg'] = '获取成功';
            $this->rs['data'] = $keywords;
            $this->rs['total'] = 0;
        }else{
            $this->rs['code'] = -1;
            $this->rs['msg'] = '获取失败';
        }

        return return_json($this->rs);


//        $this->gamelist['common']['name'] ="公共";
//
//        $this->view->setVar('gamelist', $this->gamelist);
    }
    
    
    //添加
    public function add()
    {

        $data['game'] = $this->request->post('game/s', 'common');
        $data['keywords'] = $this->request->post('keywords/s','');
        $data['num'] = $this->request->post('num/d', 0);
        $data['status'] = $this->request->post('status/d',  0);
        $data['level_min'] = $this->request->post('level_min/d', 0);
        $data['level_max'] = $this->request->post('level_max/d', 0);
        $data['money_min'] = $this->request->post('money_min/d', 0);
        $data['money_max'] = $this->request->post('money_max/d', 0);
        $data['admin_user'] = $this->user_data['username'];

        $res = SensitiveServer::add($data);

        $this->rs['code'] = $res['code'];
        $this->rs['msg'] = $res['msg'];


        return return_json($this->rs);

    }


    public function getOne()
    {
        $id= $this->request->get('id/d', 0);
        $res = SensitiveServer::getOne(['id'=>$id]);

        $this->rs['code'] = 0;
        $this->rs['msg'] = '获取成功';
        $this->rs['data'] = $res;


        return return_json($this->rs);
    }


    public function getGameList()
    {

        $this->rs['data'] = $this->gamelist;
        $this->rs['code'] = 0;
        $this->rs['msg'] = '获取成功';


        return return_json($this->rs);
    }

    //编辑
    public function edit()
    {


        $data['id'] = $this->request->post('id/d', 0);
        $data['game'] = $this->request->post('game/s', 'common');
        $data['keyword'] = $this->request->post('keyword/s','');
        $data['num'] = $this->request->post('num', 'int', 0);
        $data['status'] = $this->request->post('status', 'int', 0);
        $data['level_min'] = $this->request->post('level_min/d',  0);
        $data['level_max'] = $this->request->post('level_max/d',  0);
        $data['money_min'] = $this->request->post('money_min/d',  0);
        $data['money_max'] = $this->request->post('money_max/d',  0);

        $res = SensitiveServer::edit($data);

        $this->rs['code'] = $res['code'];
        $this->rs['msg'] = $res['msg'];


        return return_json($this->rs);

    }

    //删除-新
    public function del()
    {
        $ids = $this->request->post('ids',  $this->request->get('ids'));

        $res = SensitiveServer::del($ids);
        return return_json($res);

    }


}