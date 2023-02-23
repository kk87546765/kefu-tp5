<?php
/**
 * Created by PhpStorm.
 * User: crosstime
 * Date: 2017/10/12
 * Time: 上午11:57
 */
namespace app\admin\controller;

use common\server\AccountSearch\AccountSearchServer;
use common\sql_server\KefuCommonMember;
use common\libraries\Common;
use common\libraries\Ipip\IP4datx;
use common\server\CustomerPlatform\CommonServer;
class AccountSearch extends Oauth
{

    protected $no_oauth = ['getType'];
    public $type = [
        ['name'=>'账号','value'=>1],
        ['name'=>'UID','value'=>2],
        ['name'=>'手机','value'=>3],
        ['name'=>'渠道OpenId','value'=>4],
        ['name'=>'角色ID','value'=>5]
    ];

    public $change_type = [
        '1' => ['type'=>'修改密码'],
        '2' => ['type'=>'换绑手机'],
        '3' => ['type'=>'平台账号封禁']
    ];





    //新版UI
    public function index()
    {

            $account = $this->request->post('account', '' );
            $type = $this->request->post('type/d', 0);


            $res = AccountSearchServer::getUserInfoByAllPlatform($account,$type);

            $res = AccountSearchServer::getUserTotalPay($res);

            $res = AccountSearchServer::dealData($res);


            $this->rs['code'] = 0;
            $this->rs['msg'] = '获取成功';
            $this->rs['data'] = $res;
            $this->rs['count'] = 2;
            return return_json($this->rs);







//        var_dumP($res);exit;
//        $this->gamelist['autoforbid']="自动封禁";
//        $this->view->setVar('gamelist', $this->gamelist);


//        $this->view->setVar('type_name', $this->type);
    }


    //修改密码或换绑手机
    public function changePassword(){

        $platform_id = $this->request->post('platform_id/d', 0);
        $uid = $this->request->post('uid/d', 0);



        $type = $this->request->post('type/d',  0);
        $new_password = $this->request->post('new_password/s',  '');
        $new_phone = $this->request->post('new_phone/s', '');
        $remarks = $this->request->post('remarks/s', '');


        $platform_info = Common::getPlatformInfoByPlatformIdAndCache($platform_id);
        $user_info = $this->user_data->toArray();
        $data = [
            'uid'=>$uid,
            'platform_id'     => $platform_id,
            'platform_suffix' => $platform_info['platform_suffix'],
            'type'            => $type,
            'new_password'    => $new_password,
            'new_phone'       => $new_phone,
            'remarks'         => $remarks,
            'admin_id'        => $user_info['id'],
            'admin_name'      => $user_info['username'],
        ];

        $res = AccountSearchServer::changePasswordOrMobile($data);


        $this->rs['code'] = 0;
        $this->rs['msg'] = '成功';
        $this->rs['data'] = $res;
        $this->rs['count'] = 0;
        return return_json($this->rs);




//        $start_time = mktime(0,0,0,date('m'),date('d')-30,date('Y'));
//        $end_time = mktime(0,0,0,date('m'),date('d'),date('Y'));
//        $res = AccountSearchLogics::getAccountSeacrchLogByUid($uid,$platform_id,$start_time,$end_time,1);
//        $seven_count = 0;
//        foreach($res as $k=>$v){
//            if($v['add_time'] >=  mktime(0,0,0,date('m'),date('d')-7,date('Y')) && $v['add_time'] <= mktime(0,0,0,date('m'),date('d'),date('Y'))){
//                $seven_count++;
//            }
//        }
//
//
//
//        $this->view->setVar('platform_id', $platform_id);
//        $this->view->setVar('uid', $uid);
//        $this->view->setVar('seven_count', $seven_count);
//        $this->view->setVar('thirty_count', count($res));
    }

    //角色列表
    public function roleList(){

        $uid = $this->request->post('uid', 'int', 0);

        $platform_id = $this->request->post('platform_id', 'int', 0);

        $platform_info = Common::getPlatformInfoByPlatformIdAndCache($platform_id);

        $role_list = AccountSearchserver::getRoleLost($uid,$platform_info);

        foreach($role_list as $k=>&$v){
            $v['date'] = empty($v['reg_time']) ? '' : date('Y-m-d H:i:s',$v['reg_time']);
            $v['count_money'] = empty($v['count_money']) ? 0 : $v['count_money'];
        }
        $this->rs['code'] = 0;
        $this->rs['msg'] = '成功';
        $this->rs['data'] = $role_list;
        $this->rs['count'] = count($role_list);
        return return_json($this->rs);

    }



    public function userLoginLog(){

        $params['page'] = $this->request->post('page/d', 1 );
        $params['limit'] = $this->request->post('limit/d', 20);
        $params['platform_id'] = $this->request->post('platform_id/d', 0);
        $params['uid'] = $this->request->post('uid/d', 0);
        $params['login_date'] = $this->request->post('login_date/s', '');

        $platform_info = Common::getPlatformInfoByPlatformIdAndCache($params['platform_id']);

        $res = AccountSearchServer::getLoginLogNew($params,$platform_info);

        if(!$res['list']){
            $this->rs['code'] = -1;
            $this->rs['msg'] = '没有数据';
            $this->rs['data'] = [];
            return return_json($this->rs);
        }


        $log = $res['list'];

        $tmp_imei_count = [0];
        $tmp_idfa_count = [0];

        foreach($log as $k=>&$v){

            $v['login_date'] = date('Y-m-d H:i:s',$v['login_date']);

            $v['ip_address'] = implode('',IP4datx::find($v['login_ip']));

            $v['type'] = !empty('imei') ? '安卓' : (!empty('idfa') ? '苹果': '未知' ) ;

            if(!empty($v['imei'])){
                if(isset($tmp_imei_count[$v['imei']]['num'])){
                    $tmp_imei_count[$v['imei']]['num'] += 1;
                }else{
                    $tmp_imei_count[$v['imei']]['num'] = 0;
                }

            }

            if(!empty($v['idfa'])){
                if(isset($tmp_imei_count[$v['idfa']]['num'])){
                    $tmp_imei_count[$v['idfa']]['num'] += 1;
                }else{
                    $tmp_imei_count[$v['idfa']]['num'] = 0;
                }

            }

        }


        if(max($tmp_imei_count) >= max($tmp_idfa_count)){
            $max= max($tmp_imei_count);
            $max_name = array_search(max($tmp_imei_count),$tmp_imei_count);

        }else{
            $max= max($tmp_idfa_count);
            $max_name = array_search(max($tmp_idfa_count),$tmp_idfa_count);
        }


        $this->rs['code'] = 0;
        $this->rs['msg'] = 'ok';
        $this->rs['data'] = $log;
        $this->rs['count'] = $res['count'];
        $this->rs['max'] = $max['num'];
        $this->rs['max_name'] = $max_name;

        return return_json($this->rs);

//        $this->s_json('ok',$log,['count'=>$res['count'],'max'=>$max,'max_name'=>$max_name]);


//        }else{
//            $default_data['uid'] = $this->request->get('uid', 'int', 0);
//
//            $default_data['platform_id'] = $this->request->get('platform_id', 'int', 0);
//            $this->view->setVar('default_data', $default_data);
//        }





    }


    public function payLog()
    {


        $platform_id = $this->request->post('platform_id/d', 0);
        $uid = $this->request->post('uid/d', 0);

        $platform_info = Common::getPlatformInfoByPlatformIdAndCache($platform_id);

        $start_time = mktime(0,0,0,date('m'),date('d'),date('Y')-2);

        $end_time = mktime(0,0,0,date('m'),date('d'),date('Y'));

        $log = AccountSearchServer::getPayLog($uid,$platform_info,$start_time,$end_time);


        foreach($log as $k=>&$v){

            $v['pay_time'] = date('Y-m-d H:i:s',$v['pay_time']);
            $v['platform_name'] = $platform_info['platform_name'];


        }

        $this->rs['code'] = 0;
        $this->rs['msg'] = 'ok';
        $this->rs['data'] = $log;
        $this->rs['count'] = count($log);

        return return_json($this->rs);
    }


    public function kefuLog(){

        $platform_id = $this->request->post('platform_id/d', 0);
        $uid = $this->request->post('uid/d', 0);

        $platform_info = Common::getPlatformInfoByPlatformIdAndCache($platform_id);

//        $start_time = mktime(0,0,0,date('m')-1,date('d'),date('Y'));

//        $end_time = mktime(0,0,0,date('m'),date('d'),date('Y'));

        $log = AccountSearchServer::getkefuLog($uid,$platform_info);

        foreach($log as $k=>&$v){
            $v['type'] = $this->change_type[$v['type']]['type'];
            $v['add_time'] = date('Y-m-d H:i:s',$v['add_time']);
            $v['platform_name'] = $platform_info['platform_name'];

        }

        $this->rs['code'] = 0;
        $this->rs['msg'] = 'ok';
        $this->rs['data'] = $log;
        $this->rs['count'] = count($log);

        return return_json($this->rs);
//        $this->view->setVar('log_list', $log);
    }





    public function banUser(){

        $platform_id = $this->request->post('platform_id/d', 0);
        $uid = $this->request->post('uid/d', 0);

        $platform_info = Common::getPlatformInfoByPlatformIdAndCache($platform_id);
//        $model = CommonServer::getPlatformModel('KefuCommonMember',$platform_info['platform_suffix']);
        $user_info = KefuCommonMember::getFieldInfoByUidAndSuffix([$uid],$platform_info['platform_suffix'],'user_name');


        $data = [
            'uid'               => $uid,
            'user_name'         => $user_info[0]['user_name'],
            'platform_id'       => $platform_info['platform_id'],
            'platform_suffix'   => $platform_info['platform_suffix'],
            'admin_id'          => $this->session->get("admin_user_id"),
            'admin_name'        => $this->session->get("username")
        ];

        $res = AccountSearchserver::ban_user($data,$platform_info);


        $this->json($res);

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