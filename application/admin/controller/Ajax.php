<?php
namespace app\admin\controller;

use common\libraries\Common;
use common\server\GarrisonProduct\GarrisonProductServer;
use common\server\SysServer;
use common\server\Vip\ConfigServer;
use common\server\Vip\VipServer;
use common\server\Vip\WelfareServer;
use common\sql_server\PlatformGameInfo;
use common\server\Statistic\GameProductServer;

class Ajax extends Oauth
{
    protected $no_oauth = ['a'];

    public function index()
    {
        return return_json(['code'=>0,'msg'=>'ok']);
    }

    public function getMenu(){

        $this->rs['data'] = $this->role_data['menu'];

        return return_json($this->rs);
    }

    public function getMenuList()
    {
        $p_id = $this->req->post('p_id','int');

        $data = $this->role_data['all'];

        $new_data = [];

        foreach ($data as $k => $v){
            if($v['p_id'] == $p_id && $v['type'] == 1){
                $new_data[] = $v;
            }
        }

        $this->rs['msg'] = '获取成功';
        $this->rs['data'] = $new_data;
        return return_json($this->rs);
    }

    #根据平台获取用户列表
    public function getUserListByPlatformId(){

        $p_data = $this->req->post();

        $res = SysServer::getUserListByAdminInfo($this->user_data,$p_data);

        if($res){
            $this->rs['data'] = $res;
            $this->rs['msg'] = 'ok';
        }else{
            $this->rs['msg'] = 'no';
        }

        return return_json($this->rs);
    }

    #获取当前账号分配平台(有权限)
    public function getPlatformList(){

        $list = SysServer::getPlatformListByAdminInfo($this->user_data);

        $this->rs['msg'] = '获取成功';
        $this->rs['data'] = $list;
        return return_json($this->rs);

    }
    #获取子游戏
    public function getGameList(){
        $p_data = $this->request->post();

        $where = [];
        if(isset($p_data['platform_id']) && $p_data['platform_id']){
            $where[] = getWhereDataArr($p_data['platform_id'],'platform_id');
        }

        if(isset($p_data['p_p']) && $p_data['p_p']){
            if(is_array($p_data['p_p'])){
                $this_info = $p_data['p_p'];
            }else{
                $this_info = explode(',',$p_data['p_p']);
            }

            if(count($this_info) == 1){
                $where['_string'] = 'concat(platform_id,"_",product_id) = "'.$this_info[0].'"';
            }else{
                $where['_string'] = 'concat(platform_id,"_",product_id) in("'.implode('","',$this_info).'")';
            }
        }

        if(isset($p_data['product_id']) && $p_data['product_id']){
            $where[] = getWhereDataArr($p_data['product_id'],'product_id');
        }

        $field = '
            platform_id
            ,product_id
            ,game_id
            ,concat(platform_id,"_",product_id) as p_p
            ,concat(platform_id,"_",game_id) as p_g
            ,game_name
        ';
        $order = 'platform_id desc,game_id desc';


        $list = PlatformGameInfo::getGameList(setWhereSql($where,''),$order,$field);


        $this->rs['msg'] = '获取成功';

        if(!$list){

            $this->rs['msg'] = '获取失败';
        }

        $this->rs['data'] = $list;
        return return_json($this->rs);


    }
    #获取所有产品
    public function getProductList(){

        $p_data = $this->req->post();
        $p_data['data_type'] = $this->req->post('data_type/d',0);

        $where = [];
        if(isset($p_data['platform_id']) && $p_data['platform_id']){
            $where[] = getWhereDataArr($p_data['platform_id'],'platform_id');
        }

        if($this->user_data['is_admin'] == 0){
            $where[] = getWhereDataArr($this->user_data['platform_id'],'platform_id');
        }


        $list = GameProductServer::getProductList(setWhereSql($where,''));

        if(!$list){
            $this->rs['msg'] = '获取失败';
            $this->rs['data'] = [];
            return return_json($this->rs);

        }

        $platform_list = SysServer::getPlatformList();
        if($p_data['data_type'] == 0){
            foreach ($list as $k =>$v){
                $this_info = getArrVal($platform_list,$v['platform_id'],[]);
                if($this_info){
                    $list[$k]['product_name'] .= "($this_info[name])";
                }
            }
        }elseif($p_data['data_type'] == 2){
            $new_list = [];
            foreach ($list as $k =>$v){
                if(isset($platform_list[$v['platform_id']])){
                    if(!isset($new_list[$v['platform_id']])){
                        $new_list[$v['platform_id']] = $platform_list[$v['platform_id']];
                    }
                    $new_list[$v['platform_id']]['data'][] = $v;
                }
            }
            $list = $new_list;
        }

        $this->rs['msg'] = '获取成功';
        $this->rs['data'] = $list;
        return return_json($this->rs);

    }
    /**
     * 获取子游戏列表
     */
    public function getPlatformGameByPlatformAndProduct()
    {
        $this->rs['msg'] = '获取失败';
        $this->rs['data'] = [];
        $this->rs['code'] = -1;

        $p_product_id = $this->request->get('p_product_id/s', '');
        $where = [];
        $p_product_arr  = explode('_', $p_product_id);
        $platform_id = (int)$p_product_arr[0] ?: 0;
        $product_id = (int)$p_product_arr[1] ?: 0;
        if (!empty($platform_id)) $where['platform_id'] = $platform_id;
        if (!empty($product_id)) $where['product_id'] = $product_id;
        $res = PlatformGameInfo::getPlatformGameInfo($where);
        foreach ($res as &$val) {
            $val['value'] = $val['platform_id'].'_'.$val['game_id'];
            $val['name'] = $val['game_name'];
        }
        if (!empty($res)) {
            $this->rs['msg'] = '获取成功';
            $this->rs['code'] = 0;
            $this->rs['data'] = $res;

        }


        return return_json($this->rs);

    }

    public function rebatePropContentByTitle(){

        $title_md5 = $this->request->post('title_md5/s','');
        if (empty($title_md5)) return return_json($this->rs);
        $res = WelfareServer::getRebatePropContent($title_md5);

        $this->rs['data'] = $res;
        return return_json($this->rs);
    }

    #获取子游戏-根据产品表id
    public function getGameListByGPId(){

        $id = $this->request->post('id/d',0);

        $this->rs = array_merge($this->rs,GameProductServer::getGameListByGPId($id));

        return return_json($this->rs);
    }

    #执行区服配置
    public function adminGameServerDo(){

        $ags_id = $this->request->post('ags_id');

        $this->rs = array_merge($this->rs,ConfigServer::adminUserGameServerSave($ags_id));

        return return_json($this->rs);
    }


    public function getGameProduct()
    {
        $return = ['code'=>-1,'msg'=>'获取失败','data'=>[]];
        $res = Common::getProductList();

        if($res){
            $return['code'] = 0;
            $return['msg'] = '获取成功';
            $return['data'] = $res;
        }

        return return_json($return);
    }

    public function getGarrisonProductList()
    {
        $product_list = GarrisonProductServer::getGarrisonProductList();
        foreach($product_list as $k=>$v){
            $product_list[$k]['p_p'] = $v['platform_id'].'_'.$v['product_id'];
        }
        $res['code'] = 0;
        $res['msg'] = '获取成功';
        $res['data'] = $product_list;
        return return_json($res);
    }

}
