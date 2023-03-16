<?php
/**
 * Created by PhpStorm.
 * User: crosstime
 * Date: 2017/10/12
 * Time: 上午11:57
 */
namespace app\admin\controller;


use common\libraries\Common;

use common\server\BlockServer;
use common\Logics\Sdk\UserLogics as sdkUserLogics;
use common\server\ActionBlock\ActionBlockServer;



class ActionBlock extends Oauth
{

    public $types = [
        1  => '私聊',
        2  => '喇叭',
        3  => '邮件',
        4  => '世界',
        5  => '国家',
        6  => '工会/帮会',
        7  => '队伍',
        8  => '附近',
        9  => '其他',
        10 => '跨服',
    ];


    /**
     *首页
     */
    public function index()
    {

        $data['page']       = $this->request->get('page/d', 1);
        $data['limit']      = $this->request->get('limit/d', 20);

        $list = ActionBlockServer::getList($data);
        $total = ActionBlockServer::getCount();


        $this->rs['code'] = 0;
        $this->rs['msg'] = '获取成功';
        $this->rs['data'] = $list;
        $this->rs['count'] = $total;
        return return_json($this->rs);

    }



    public function add(){

        $data['name']         =  $this->request->post('name/s','');
        $data['type']         =  $this->request->post('type/d',0);
        $data['ban_time']     =  $this->request->post('ban_time/d',0);
        $data['ban_object']   =  $this->request->post('ban_object/d',0);
        $data['ban_reason']   =  $this->request->post('ban_reason/s','');
        $data['product']      =  $this->request->post('product/d',0);
        $data['min_level']    =  $this->request->post('min_level/d', 0);
        $data['max_level']    =  $this->request->post('max_level/d', 0);
        $data['min_money']    =  $this->request->post('min_money/d', 0);
        $data['max_money']    =  $this->request->post('max_money/d', 0);
        $data['limit_time']   =  $this->request->post('limit_time/d',0);
        $data['check_type']   =  $this->request->post('check_type/d',1);
        $data['private_chat_num']    =  $this->request->post('private_chat_num/s', '');
        $data['repeat_msg_num']      =  $this->request->post('repeat_msg_num/s', '');
        $data['figure_num']          =  $this->request->post('figure_num/s', '');
        $data['limit_figure_length'] =  $this->request->post('limit_figure_length/s', '');
        $data['limit_char_length']   =  $this->request->post('limit_char_length/s', '');
        $data['add_time'] = time();


        $return = ActionBlockServer::add($data);
        $this->rs['code'] = $return['status'];
        $this->rs['msg'] = $return['msg'];

        return return_json($this->rs);

//        $this->view->setVar('product_list',$product_list);
    }


    public function edit(){

        $product_list = Common::getProductList();

        $data['id']       =  $this->request->post('id/d',0);
        $data['name']       =  $this->request->post('name/s', '');
        $data['type']       =  $this->request->post('type/d',0);
        $data['ban_time']   =  empty($this->request->post('ban_time')) ? 0 : $this->request->post('ban_time');
        $data['ban_object'] =  $this->request->post('ban_object/d',0);
        $data['ban_reason'] =  $this->request->post('ban_reason/s','');
        $data['min_level']  =  $this->request->post('min_level/d', 0);
        $data['max_level']  =  $this->request->post('max_level/d', 0);
        $data['min_money']  =  $this->request->post('min_money/d', 0);
        $data['max_money']  =  $this->request->post('max_money/d', 0);
        $data['limit_time'] =  $this->request->post('limit_time/d',0);
        $data['product']    =  $this->request->post('product/d',0);
        $data['product_name']        =  empty($product_list[$data['product']]['code']) ? '' : $product_list[$data['product']]['code'];
        $data['private_chat_num']    =  $this->request->post('private_chat_num/d', 0);
        $data['repeat_msg_num']      =  $this->request->post('repeat_msg_num/d', 0);
        $data['figure_num']          =  $this->request->post('figure_num/d', 0);
        $data['limit_figure_length'] =  $this->request->post('limit_figure_length/d', 0);
        $data['limit_char_length']   =  $this->request->post('limit_char_length/d', 0);

        $return = ActionBlockServer::edit($data);

        return return_json($return);
        }




    public function del(){

        $id = $this->request->post('ids');

        $return = ActionBlockServer::del($id);

        return return_json($return);

    }

    //关闭封禁规则
    public function close(){

        $return = ActionBlockServer::updateStatus(0);

        return return_json($return);
    }

    //重启配置
    public function restart(){

        $return = ActionBlockServer::updateStatus(1);

        return return_json($return);
    }


  public function getGameProduct()
  {
      $res = Common::getProductList();
      $this->rs['code'] = 0;
      $this->rs['msg'] = '获取成功';
      $this->rs['data'] = $res;

      return return_json($this->rs);
  }

  public function getInfo()
  {
      $id = $this->request->post('id/d', '');

      $info = ActionBlockServer::getById($id);
      if(!empty($info)){
          $this->rs['code'] = 0;
          $this->rs['msg'] = '获取成功';
          $this->rs['data'] = $info;
      }else{
          $this->rs['code'] = -1;
          $this->rs['msg'] = '获取失败';
      }


      return return_json($this->rs);

  }



}