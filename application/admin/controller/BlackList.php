<?php
/**
 * Created by PhpStorm.
 * User: crosstime
 * Date: 2017/10/12
 * Time: 上午11:57
 */
namespace app\admin\controller;

use common\server\BlackBlock\BlackBlockServer;
use common\server\SysServer;
use common\sql_server\KefuCommonMember;
use common\libraries\Common;
use common\libraries\Ipip\IP4datx;
use common\server\CustomerPlatform\CommonServer;
class BlackList extends Oauth
{

    protected $no_oauth = ['getType'];




    //新版UI
    public function index()
    {
        $data['account'] = $this->request->request('account/s', '');
        $data['type'] = $this->request->request('type/d', 1);
        $data['page'] = $this->request->request('page/d', 1);
        $data['limit'] = $this->request->request('limit/d', 20);
//        $data['status'] = 1;
        $res = BlackBlockServer::getList($data);

        foreach($res as $k=>&$v){
            $v['block_time'] = date('Y-m-d H:i:s',$v['block_time']);
        }

        $count = BlackBlockServer::getCount($data);
        $this->rs['code'] = 0;
        $this->rs['msg'] = '获取成功';
        $this->rs['data'] = $res;
        $this->rs['count'] = $count;
        return return_json($this->rs);

    }


    public function add()
    {
//        $data['platform_id'] = $this->request->post('platform_id/d', 0);
        $data['admin_id'] = $this->request->post('admin_id/d', 0);
        $data['type'] = $this->request->post('type/d', 0);
        $data['check_val'] = $this->request->post('check_val/s', '');
        $data['reason_type'] = $this->request->post('reason_type/s', '');
        $data['reason'] = $this->request->post('reason/s', '');
        $data['phone_val'] = $this->request->post('phone_val/s', '');
        $data['id_card_val'] = $this->request->post('id_card_val/s', '');
        $data['udid_val'] = $this->request->post('udid_val/s', '');
        $data['ip'] = $this->request->ip();

        $data['user_info'] = $this->user_data;
        $platform_suffix = $this->common_data['def_platform'];
        $data['platform_id'] = Common::getPlatformInfoBySuffixAndCache($platform_suffix)['platform_id'];


        $allow_platform = BlackBlockServer::$all_platform;

        if(!in_array($data['platform_id'],$allow_platform)){
            $this->rs['code'] =  -1;
            $this->rs['msg'] = '平台暂未开放';

            return return_json($this->rs);
        }

//        if(empty($data['phone_val']) && empty($data['id_card_val']) && empty($data['udid_val'])){
//            $this->rs['code'] =  -1;
//            $this->rs['msg'] = '没有需要提交的值';
//
//            return return_json($this->rs);
//        }


        $res = BlackBlockServer::add($data);

        $this->rs['code'] = $res == true ? 0 : -1;
        $this->rs['msg'] = '操作成功';

        return return_json($this->rs);
    }


    public function unblock(){
        $data['ids'] = $this->request->get('ids/a', [] );
        $data['unban_reason'] = $this->request->get('unban_reason/s', '' );
        $data['ip'] = $this->request->ip();

        $data['status'] = 0;

        $data['user_info'] = $this->user_data;
        $res = BlackBlockServer::unblock($data);

        $this->rs['code'] = $res == true ? 0 : -1;
        $this->rs['msg'] = $res == true ? '操作成功' : '操作失败';

        return return_json($this->rs);
    }


    public function del(){
        $data['id'] = $this->request->get('ids/d', 0 );
        $data['status'] = 0;
        $res = BlackBlockServer::del($data);


        if ($res) {
            $return['code'] = 0;
            $return['msg'] = '删除成功';
        } else {
            $return['code'] = -1;
            $return['msg'] = '删除失败';
        }

        return return_json($return);

    }
    public function getBlackList(){
        $data['type'] = $this->request->get('type/d', 0);
        $data['value'] = $this->request->get('val/s', '');
//        $data['platform_id'] = $this->request->get('platform_id/d', 0);
        $data['need_type'] = $this->request->get('need_type/d', 0);
        $data['ip'] = $this->request->ip();

        $res = BlackBlockServer::getBlackList($data);

//        $count = BlackBlockServer::getBlackListCount($data);
        $count = count($res);
        $this->rs['code'] = 0;
        $this->rs['msg'] = '获取成功';
        $this->rs['data'] = $res;
        $this->rs['count'] = $count;
        return return_json($this->rs);
    }





    public function getAdminDepartment()
    {
        $admin_department = $this->admin_department;
        $return['msg'] = '获取成功';
        $return['code'] = 0;
        $return['data'] = $admin_department;

        return return_json($return);
    }


    public function getAdmin()
    {
        $platform_id = $this->request->post('platform_id/d', 0);
        $p_data['role_id'] = '23,24,25,26';
        $p_data['is_active'] = 1;
        $p_data['platform_id'] = $platform_id;

        $admin_list = SysServer::getUserListByAdminInfo($this->user_data,$p_data);

        $res['code'] = 0;
        $res['msg'] = '成功';
        $res['data'] = $admin_list;

        return return_json($res);

    }



    public function getType()
    {
        $this->rs['code'] = 0;
        $this->rs['msg'] = '获取成功';
        $this->rs['data'] = [
            'type'=>$this->type
        ];
        return return_json($this->rs);
    }
}