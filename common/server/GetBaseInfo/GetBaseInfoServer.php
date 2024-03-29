<?php
/**
 * Created by PhpStorm.
 * User: crosstime
 * Date: 2018/8/14
 * Time: 下午5:31
 */
namespace common\server\GetBaseInfo;
use common\base\BasicServer;
use common\libraries\Common;
use common\libraries\ApiUserInfoSecurity;
use common\server\SysServer;
use common\sql_server\GetBaseInfoSqlServer;
use common\sql_server\KefuCommonMember;
use common\sql_server\BanUserLog;
use think\Db;
use app\scripts\controller\GetBaseInfo;

class GetBaseInfoServer extends BasicServer
{
    public  $model = '';
    public  $key,$time,$num,$platform,$start_time,$end_time,$type,$action_type,
        $action_start_time,$action_end_time,$new_end_time,$new_start_time,
        $role_type,$platform_list,$platform_arr,$interface_url,$type_arr;
    public $return = ['code'=>-1, 'data'=>'err'];
    /**
     * @param $params
     */
    public function dealParams($params)
    {

        $platform_list = SysServer::getPlatformList();

        foreach ($platform_list as $v){
            if($v['suffix'] != 'asjd'){
                $this->platform_list[$v['suffix']] = $v['platform_id'];
                $this->platform_arr[] = $v['suffix'];
            }
        }

        $this->interface_url = Common::getPlatform();

        $this->time = time();

        $this->platform = isset($params['platform']) ? $params['platform'] : '';

        $this->end_time = isset($params['end_time']) ? strtotime($params['end_time']) : $this->time;

        $this->start_time = isset($params['start_time']) ? strtotime($params['start_time']) : $this->time-300;

        $this->new_end_time = isset($params['new_end_time']) ? strtotime($params['new_end_time']) : $this->time;

        $this->new_start_time= isset($params['new_start_time']) ? strtotime($params['new_start_time']) : $this->time-300;

        $this->num = isset($params['num']) ? $params['num'] : 500;

        $this->type = isset($params['type']) ? $params['type'] : '';

        $this->role_type = isset($params['role_type']) ? $params['role_type'] : 2; //判断角色接口是否用最后登录时间还是创角时间

        $this->type_arr = [1,2,3];

        $this->action_end_time = isset($params['action_end_time']) ? strtotime($params['action_end_time']) : 0;//补缺接口使用
        $this->action_start_time= isset($params['action_start_time']) ? strtotime($params['action_start_time']) : 0;//补缺接口使用
        $this->type = isset($params['type']) ? $params['type'] : '';//补缺接口使用
        $this->action_type = isset($params['action_type']) ? $params['action_type'] : '';//补缺接口使用

        $this->key = $this->interface_url[$this->platform]['url_key'];

        if(empty($this->platform) || !in_array($this->platform,$this->platform_arr) ) {

            $return['data'] = 'platform params error';

            return $return;

        }
        if ($this->start_time >= $this->end_time){

            $this->return['data'] = 'start_time must be less than the end_time';

            return $this->return;

        }

        if (!ctype_digit($this->start_time)){

            $this->return['data'] = 'start_time must be effective timestamp';

            return $this->return;


        }
        if (!ctype_digit($this->end_time)){

            $this->return['data'] = 'end_time must be effective timestamp';

            return $this->return;

        }
        $this->return['code'] = 1;
        $this->return['data'] = 'ok';
        return $this->return;
    }

    public function getUserRegister($params)
    {

        $time = $params->time;
        $num = $params->num;
        $start_time = $params->start_time;
        $end_time = $params->end_time;


        $key = $params->key;
        $sign = md5("start_time={$start_time}end_time={$end_time}time={$time}key={$key}");
        $dataArr = [
            'start_time' => $start_time,
            'end_time' => $end_time,
            'time' => $time,
            'page' => 1,
            'num' => $num,
            'sign' => $sign
        ];
        $url = $params->interface_url[$params->platform]['user_register'];

        $this->model = new GetBaseInfoSqlServer($params->platform);

        $res = $this->register_log($url,$dataArr,$params->platform);

        return $res;
    }

    /**
     * 添加用户注册数据
     * @param $url
     * @param $dataArr
     * @param $platform
     * @return bool
     */
    private function register_log($url,$dataArr,$platform)
    {

        $return_res = $this->getDataS($url,$dataArr);

        if (!empty($return_res)){
            $dataArray = [];
            foreach ($return_res as $k => $v) {
                if(empty($v['uid']))continue;
                //判断数据是否存在

                $checkData = $this->model->checkUserExists($v['uid']);

                if($checkData)continue;
                $reg_arr['uid'] = $v['uid'];
//                $reg_arr['user_name'] = empty($v['uname']) ? '' : $v['uname'];
                $reg_arr['user_name'] = empty($v['uname']) ? '' : addslashes($v['uname']);
                $reg_arr['reg_date'] = empty($v['action_time']) ? 0 : $v['action_time'];
                $reg_arr['reg_channel'] =  empty($v['reg_channel']) ? '' : $v['reg_channel'];
                $reg_arr['mobile'] =  empty($v['mobile']) ? '' : ApiUserInfoSecurity::encrypt($v['mobile']);
                $reg_arr['reg_gid'] =  empty($v['gid']) ? 0 : $v['gid'];
                $reg_arr['imei'] =  empty($v['imei']) ? '' : $v['imei'];
                $reg_arr['idfa'] =  empty($v['idfa']) ? '' : $v['idfa'];
                $reg_arr['reg_ip'] =  empty($v['reg_ip']) ? '' : $v['reg_ip'];
                $reg_arr['login_ip'] =  empty($v['reg_ip']) ? '' : $v['reg_ip'];
                $reg_arr['login_date'] =  empty($v['login_date']) ? 0 : $v['login_date'];
                $reg_arr['status'] = 1;
                $reg_arr['real_name'] =  empty($v['real_name']) ? '' : $v['real_name'];
                $reg_arr['id_card'] =  empty($v['id_card']) ? '' : $v['id_card'];
                $reg_arr['real_name_time'] =  empty($v['login_date']) ? 0 : $v['login_date'];
                $reg_arr['user_type'] =  empty($v['user_type']) ? 0 : $v['user_type'];
                array_push($dataArray, $reg_arr);
            }

            if (!empty($dataArray)) {
                $res = $this->model->insertReg($dataArray);
                unset($dataArray);
            }
            if ($this->num == count($return_res)){
                $start_time = $dataArr['start_time'];
                $end_time = $dataArr['end_time'];
                $time = $dataArr['time'];
                $num = $dataArr['num'];
                $sign = $dataArr['sign'];
                $dataArr = [
                    'start_time' => $start_time,
                    'end_time' => $end_time,
                    'time' => $time,
                    'page' => $dataArr['page'] + 1,
                    'num' => $num,
                    'sign' => $sign
                ];
                sleep(1);
                $this->return = $this->register_log($url, $dataArr, $platform);
            }else{
                if (!empty($res)) {
                    $this->return['code'] = 1;
                    $this->return['data'] = 'success';

                } else {
                    $this->return['code'] = -1;
                }
            }
        }else{
            $this->return['code'] = -1;
        }

        return  $this->return;
    }


    public function getUserLogin($params)
    {
        $time = $params->time;
        $num = $params->num;
        $start_time = $params->start_time;
        $end_time = $params->end_time;


        $key = $params->key;
        $sign = md5("start_time={$start_time}end_time={$end_time}time={$time}key={$key}");
        $dataArr = [
            'start_time' => $start_time,
            'end_time' => $end_time,
            'time' => $time,
            'page' => 1,
            'num' => $num,
            'sign' => $sign
        ];
        $url = $params->interface_url[$params->platform]['user_login'];

        $this->model = new GetBaseInfoSqlServer($params->platform);

        $res = $this->login_log($url,$dataArr,$params->platform);

        return $res;
    }


    /**
     * @param $url
     * @param $dataArr
     * @param $platform
     * @return array
     */
    private function login_log($url,$dataArr,$platform)
    {

        $return_res = $this->getDataS($url,$dataArr);

        if (!empty($return_res)){
            $dataArray = [];
            foreach ($return_res as $k => $v) {
                //检测登录用户日志[同一用户在同一个游戏下同一时间登录的记录]是否存在
                $checkArr = [
                    'uid'=>$v['uid'],
                    'gid'=>$v['gid'],
                    'login_date'=>$v['action_time'],
                ];

                $checkData = $this->model->checkLoginUserExists($checkArr);
                if($checkData)continue;

                //更新用户封禁状态
                $this->checkLoginUserBanExists($v);


                if ($v['action_time']) $month = date('m',$v['action_time']);
                else $month = '';
                $add_data['uid'] = !empty($v['uid']) ? $v['uid'] : '';
//                $add_data['uname'] = !empty($v['uname']) ? $v['uname'] : '';
                $add_data['uname'] = !empty($v['uname']) ? addslashes($v['uname']) : '';
                $add_data['gid'] = !empty($v['gid']) ? $v['gid'] : 0;
//                $add_data['game_name'] = !empty($v['game_name']) ? $v['game_name'] : '';
                $add_data['game_name'] = !empty($v['game_name']) ? addslashes($v['game_name']) : '';
                $add_data['login_date'] = !empty($v['action_time']) ? $v['action_time'] : '';
                $add_data['login_channel'] = !empty($v['login_channel']) ? $v['login_channel'] : '';
                $add_data['imei'] = !empty($v['imei']) ? $v['imei'] : '';
                $add_data['idfa'] = !empty($v['idfa']) ? $v['idfa'] : '';
                $add_data['login_ip'] = !empty($v['login_ip']) ? $v['login_ip'] : '';
                $add_data['month'] = $month;
                $add_data['mobile'] =  empty($v['mobile']) ? '' : ApiUserInfoSecurity::encrypt($v['mobile']);
                $add_data['real_name'] =  empty($v['real_name']) ? '' : $v['real_name'];
                $add_data['id_card'] =  empty($v['id_card']) ? '' : $v['id_card'];
                $add_data['real_name_time'] =  empty($v['real_name_time']) ? 0 : $v['real_name_time'];

                array_push($dataArray, $add_data);


                if($v['uid']){

                    $login_date = $this->model->getLastLoginTime($v['uid']);

                    if($login_date !== false){
                        $update_data = [];
                        if ($login_date < $add_data['login_date']){
                            //注册表里面的登录时间 < 登录日志里面的登录时间 则更新注册表里面的登录时间
                            $update_data['login_date'] = !empty($add_data['login_date']) ? $add_data['login_date'] : '';
                            $update_data['login_ip'] = !empty($add_data['login_ip']) ? $add_data['login_ip'] : '';
                        }

                        $update_data['mobile'] = !empty($v['mobile']) ? ApiUserInfoSecurity::encrypt($v['mobile']) : '';
                        $update_data['real_name'] = !empty($add_data['real_name']) ? $add_data['real_name'] : '';
                        $update_data['id_card'] = !empty($add_data['id_card']) ? $add_data['id_card'] : '';
                        $update_data['real_name_time'] = empty($v['real_name_time']) ? 0 : $v['real_name_time'];
                        $update_data['uid'] = $v['uid'];

                        if(!empty($update_data['mobile']) || !empty($update_data['login_date']) || !empty($update_data['login_ip'])){

                            $this->model->updateUserLoginTime($update_data);

                        }
                    }
                }
            }
            if (!empty($dataArray)) {
                $res = $this->model->insertLogin($dataArray);
                unset($dataArray);
            }

            if ($this->num == count($return_res)){
                $start_time = $dataArr['start_time'];
                $end_time = $dataArr['end_time'];
                $time = $dataArr['time'];
                $num = $dataArr['num'];
                $sign = $dataArr['sign'];
                $dataArr = [
                    'start_time' => $start_time,
                    'end_time' => $end_time,
                    'time' => $time,
                    'page' => $dataArr['page'] + 1,
                    'num' => $num,
                    'sign' => $sign
                ];
//                sleep(1);
                $this->login_log($url, $dataArr, $platform);
            }else{
                if (!empty($res)) {
                    $this->return['code'] = 1;
                } else {
                    $this->return['code'] = -1;
                }
            }
        }else{
            $this->return['code'] = -1;
        }

        return $this->return;
    }


    public function checkLoginUserBanExists($checkArr){
        $flag = false;
        $platform = $this->model->platform;
        $redis = get_redis();
        $re1 = $redis->get($platform.'_'.'block_uname_'.$checkArr['uname']);
        $re2 = $redis->get($platform.'_'.'block_ip_'.$checkArr['login_ip']);
        $re3 = $redis->get($platform.'_'.'block_imei_'.$checkArr['imei']);
        $re4 = $redis->get($platform.'_'.'block_uid_'.$checkArr['uid']);

        if($re1){
            $flag = $redis->delete($platform.'_'.'block_uname_'.$checkArr['uname'],1);
            $account = $checkArr['uname'];
            $data_type = 5;

        }
        if($re2){
            $flag = $redis->delete($platform.'_'.'block_ip_'.$checkArr['login_ip'],1);
            $account = $checkArr['login_ip'];
            $data_type = 6;

        }
        if($re3){
            $flag = $redis->delete($platform.'_'.'block_imei_'.$checkArr['imei'],1);
            $account = $checkArr['imei'];
            $data_type = 7;

        }

        if($re4){
            $flag = $redis->delete($platform.'_'.'block_uid_'.$checkArr['uid'],1);
            $account = $checkArr['uname'];
            $data_type = 5;

        }

        if($flag){
            KefuCommonMember::UpdateMemberStatus($account,$data_type,1);

            if($data_type == 5){
                BanUserLog::insertLog(['user_name'=>$account,'admin_user'=>'test','time'=>time(),'type'=>2,'ban_time'=>0,'reason'=>'系统自动解封'],1,$checkArr['tkey']);
            }

        }
        return $flag;
    }



    //用户充值日志接口
    public function getUserPay($params){

        $time = $params->time;
        $num = $params->num;
        $start_time = $params->start_time;
        $end_time = $params->end_time;


        $key = $params->key;
        $sign = md5("start_time={$start_time}end_time={$end_time}time={$time}key={$key}");
        $dataArr = [
            'start_time' => $start_time,
            'end_time' => $end_time,
            'time' => $time,
            'page' => 1,
            'num' => $num,
            'sign' => $sign
        ];
        $url = $params->interface_url[$params->platform]['user_pay'];

        $this->model = new GetBaseInfoSqlServer($params->platform);

        $res = $this->recharge_log($url,$dataArr,$params->platform);

        return $res;
    }

    /**
     * @param $url
     * @param $dataArr
     * @param $platform
     * @return bool
     */
    private function recharge_log($url,$dataArr,$platform)
    {
        $return_res = $this->getDataS($url,$dataArr,8);

        if (!empty($return_res)){
            $dataArray = [];
            foreach ($return_res as $k => $v) {
                if(empty($v['order_id']))continue;
                //判断数据是否存在
                $checkData = $this->model->checkOrdersExists($v['order_id']);
                if($checkData)continue;
                $pay_data['order_id'] = $v['order_id'];
                $pay_data['uid'] = !empty($v['uid']) ? $v['uid'] : 0;
//                $pay_data['user_name'] = !empty($v['uname']) ? $v['uname'] : '';
                $pay_data['user_name'] = !empty($v['uname']) ? addslashes($v['uname']) : '';
                $pay_data['amount'] = !empty($v['amount']) ? $v['amount'] : 0;
                $pay_data['gid'] = !empty($v['gid']) ? $v['gid'] : 0;
//                $pay_data['game_name'] = !empty($v['game_name']) ? $v['game_name'] : '';
                $pay_data['game_name'] = !empty($v['game_name']) ? addslashes($v['game_name']) : '';
                $pay_data['server_id'] = !empty($v['server_id']) ? $v['server_id'] : 0;
//                $pay_data['server_name'] = !empty($v['server_name']) ? $v['server_name'] : '';
                $pay_data['server_name'] = !empty($v['server_name']) ? addslashes($v['server_name']) : '';
                $pay_data['role_id'] = !empty($v['role_id']) ? $v['role_id'] : 0;
//                $pay_data['role_name'] = !empty($v['role_name']) ? $v['role_name'] : '';
                $pay_data['role_name'] = !empty($v['role_name']) ? addslashes($v['role_name']) : '';
                $pay_data['pay_channel'] = !empty($v['pay_channel']) ? $v['pay_channel'] : '';
                $pay_data['imei'] = !empty($v['imei']) ? $v['imei'] : '';
                $pay_data['idfa'] = !empty($v['idfa']) ? $v['idfa'] : '';
//                $pay_data['pay_time'] = !empty($v['action_time']) ? strtotime($v['action_time']) : 0;

                $pay_data['pay_time'] = !empty($v['action_time']) ? $v['action_time'] : 0;

                $pay_data['reg_channel'] = !empty($v['reg_channel']) ? $v['reg_channel'] : '';
                $pay_data['payment'] = !empty($v['payment']) ? $v['payment'] : '';
                $pay_data['yuanbao_status'] = !empty($v['yuanbao_status']) ? $v['yuanbao_status'] : 1;
                $pay_data['first_login_game_id'] = !empty($v['first_login_game_id']) ? $v['first_login_game_id'] : 0;
                $pay_data['first_login_game_time'] = !empty($v['first_login_game_time']) ? $v['first_login_game_time'] : 0;
                $pay_data['third_party_order_id'] = !empty($v['third_party_order_id']) ? $v['third_party_order_id'] : '';

                $pay_data['dateline'] = time();
                array_push($dataArray, $pay_data);
            }

            if (!empty($dataArray)) {
                $res = $this->model->insertOrders($dataArray);
                unset($dataArray);
            }
            if ($this->num == count($return_res)){
                $start_time = $dataArr['start_time'];
                $end_time = $dataArr['end_time'];
                $time = $dataArr['time'];
                $num = $dataArr['num'];
                $sign = $dataArr['sign'];
                $dataArr = [
                    'start_time' => $start_time,
                    'end_time' => $end_time,
                    'time' => $time,
                    'page' => $dataArr['page'] + 1,
                    'num' => $num,
                    'sign' => $sign
                ];
                sleep(1);
                $this->recharge_log($url, $dataArr, $platform);
            }else{
                if (!empty($res)) {
                   $this->return['code'] = 1;
                } else {
                    $this->return['code'] = -1;

                }
            }
        }else{
            $this->return['code'] = -1;
        }
        return $this->return;
    }



    //用户充值日志接口
    public function getUserRole($params){

        $time = $params->time;
        $num = $params->num;
        $start_time = $params->start_time;
        $end_time = $params->end_time;
        $key = $params->key;
        $role_type = $this->role_type;

        $sign = md5("start_time={$start_time}end_time={$end_time}time={$time}key={$key}");
        $url = $this->interface_url[$this->platform]['get_user_role'];
        $dataArr = [
            'start_time' => $start_time,
            'end_time' => $end_time,
            'time' => $time,
            'page' => 1,
            'num' => $num,
            'role_type'=>$role_type,
            'sign' => $sign
        ];

        $this->model = new GetBaseInfoSqlServer($params->platform);

        $res = $this->role_log($url,$dataArr,$params->platform);

        return $res;
    }

    /**
     * @param $url
     * @param $dataArr
     * @param $platform
     * @return bool
     */
    private function role_log($url,$dataArr,$platform)
    {

        $return_res = $this->getDataS($url,$dataArr);

        $count = count($return_res);
        if (!empty($return_res)){
            $dataArray = [];
            $serverArray = [];
            foreach ($return_res as $k => $v) {

                $add_data['uid'] = !empty($v['uid']) ? $v['uid'] : '';
                $add_data['uname'] = !empty($v['uname']) ? $v['uname'] : '';
                $add_data['role_id'] = !empty($v['role_id']) ? $v['role_id'] : 0;
                $add_data['role_name'] = !empty($v['role_name']) ? $v['role_name'] : '';
                $add_data['role_level'] = !empty($v['role_level']) ? $v['role_level'] : 0;
                $add_data['reg_gid'] = !empty($v['reg_gid']) ? $v['reg_gid'] : 0;
                $add_data['reg_channel'] = !empty($v['reg_channel']) ? $v['reg_channel'] : 0;
                $add_data['reg_time'] = !empty($v['reg_time']) ? $v['reg_time'] : 0;
                $add_data['server_id'] = !empty($v['server_id']) ? $v['server_id'] : 0;
                $add_data['server_name'] = !empty($v['server_name']) ? $v['server_name'] : '';
                $add_data['login_date'] = !empty($v['login_date']) ? $v['login_date'] : 0;

                $add_data['trans_level'] = !empty($v['trans_level']) ? (int)$v['trans_level'] : 0;

                $server_data['gid'] = $add_data['reg_gid'];
                $server_data['server_id'] = $add_data['server_id'];
                $server_data['server_name'] = $add_data['server_name'];
                $server_data['open_time'] = strtotime(date('Y-m-d',$add_data['login_date']));

                array_push($serverArray, $server_data);

                if($v['uid'] && $v['role_id']){

                    $user_info = $this->model->getLastUserRoleLoginTime($v['uid'],$v['role_id']);

                    //如果已经存在用户，则不插入只更新
                    if(empty($user_info['uid'])){
                        array_push($dataArray, $add_data);
                    }

                    $login_date = $user_info['login_date'];

                    if($login_date !== false){
                        $update_data = [];
                        if ($login_date < $add_data['login_date']){
                            //注册表里面的登录时间 < 登录日志里面的登录时间 则更新注册表里面的登录时间
                            $update_data['login_date'] = !empty($add_data['login_date']) ? $add_data['login_date'] : '';
                            $update_data['role_level'] = !empty($add_data['role_level']) ? $add_data['role_level'] : 0;
                            $update_data['trans_level'] = !empty($add_data['trans_level']) ? (int)$add_data['trans_level'] : 0;
                        }

                        $update_data['uid'] = $v['uid'];
                        $update_data['role_id'] = $v['role_id'];

                        if((!empty($update_data['login_date']) || !empty($update_data['role_level'])) && $dataArr['role_type'] == 1){

                            $res = $this->model->updateUserRoleLoginTime($update_data);

                        }
                    }
                }
            }


            unset($add_data);
            unset($server_data);
            unset($update_data);
            unset($return_res);

            //插入角色信息
            if (!empty($dataArray) && $dataArr['role_type'] == 2 ) {

                //每2000条数据插入一次
                for ($i=0;count($dataArray) >$i*2000;$i++)
                {
                    Db::startTrans();
                    $start = empty($i*2000) ? 0 : $i*2000;
                    $insertData = array_slice($dataArray, $start, 20000);

                    $res = $this->model->insertRole($insertData);

                    if($res){
                        Db::commit();
                    }else{
                        Db::rollback();
                    }

                }

                unset($dataArray);
            }

            //插入区服信息
            if (!empty($serverArray) && $dataArr['role_type'] == 2) {

                $res = $this->model->insertServer($serverArray,$platform);

                unset($serverArray);
            }

            if ($this->num == $count){
                $start_time = $dataArr['start_time'];
                $end_time = $dataArr['end_time'];
                $time = time();
                $role_type = $dataArr['role_type'];
                $num = $dataArr['num'];
                $sign = md5("start_time={$start_time}end_time={$end_time}time={$time}key={$this->key}");
                $dataArr = [
                    'start_time' => $start_time,
                    'end_time' => $end_time,
                    'time' => $time,
                    'page' => $dataArr['page'] + 1,
                    'num' => $num,
                    'role_type' => $role_type,
                    'sign' => $sign
                ];
//                sleep(1);
                $this->role_log($url, $dataArr, $platform);
            }else{
                if (!empty($res)) {
                    return true;
                } else {
                    return false;
                }
            }
        }else{
            return false;
        }

    }





    /**
     * 行为数据查询接口
     * @param array $params
     */
    public function userAction($params){

        $return = ['code'=>-1,'msg'=>'error'];
        $end_time = empty(($params->action_end_time)) ? mktime(0,0,0,date('m'),date('d'),date('Y')) : $params->action_end_time;
        $start_time = empty(($params->action_start_time)) ? mktime(0,0,0,date('m'),date('d')-1,date('Y')) : $params->action_start_time;
        $time = $params->time;

        $key = $params->key;
        $action_type = $params->action_type;//行为类型(1-注册，2-登录，3-订单)
        $type = $params->type;//类型(1-按小时统计，2-按天统计，3-按月统计)

        if (!in_array($action_type,$params->type_arr)){
            $return['msg'] = "action_type error";
            return $return;
        }
        if (!in_array($type,$params->type_arr)){
            $return['msg'] = "type error";
            return $return;
        }
        if ($type == 1){

            //按小时统计，开始时间，结束时间必须为同一天
            if (date('d',$start_time) != date('d',$end_time -1)){
                $return['msg'] = "Start time end time must be within the same day";
                return $return;
            }
            $start_time = empty($start_time) ? mktime(0,0,0,date('m'),date('d')-1,date('Y')) : $start_time;
            $end_time = empty($end_time) ? mktime(0,0,0,date('m'),date('d'),date('Y')) : $end_time;
        }elseif ($type == 2){
            //按天统计，开始时间，结束时间必须为同一月内的
            if (date('m',$start_time) != date('m',$end_time -1)){
                $return['msg'] = "Start time end time must be within the same month";
                return $return;
            }
        }else{
            //todo
        }

        $sign = md5("start_time={$start_time}end_time={$end_time}time={$time}key={$key}");
        $dataArr = [
            'start_time' => $start_time,
            'end_time' => $end_time,
            'action_type' => $action_type,
            'type' => $type,
            'time' => $time,
            'sign' => $sign
        ];
        $url = $this->interface_url[$this->platform]['action'];

        $this->model = new GetBaseInfoSqlServer($params->platform);

        $res = $this->action_log($url,$dataArr,$action_type,$type,$params);
        return $res;
    }

    /**
     * 获取数据
     * @param $url
     * @param array $opt
     * @param int $timeOut
     * @return array|mixed
     */
    protected  function getDataS($url,$opt=[],$timeOut = 10)
    {
        list($res,$return_str) = $this->post_curl($url,urldecode(http_build_query($opt)),$timeOut);

        if($res==200){
            $data = json_decode($return_str,1);
            if($data['state']['code']==1){
                return $data['data'];
            } else {
                //需要记录错误信息
                //return $data['state']['msg'];
            }
        } else {
            //说明请求网络有问题，
            return [];
        }
    }




    /**
     * @param $url
     * @param $dataArr
     * @param $action_type
     * @param $type
     * @return bool|string
     */
    private function action_log($url,$dataArr,$action_type,$type,$params){
        $db_name = 'db_customer_'.$this->platform;//平台对应的数据库
        // action_type 行为类型(1-注册，2-登录，3-订单);
        // type 类型(1-按小时统计，2-按天统计，3-按月统计);
        $start_time = $dataArr['start_time'];
        $end_time = $dataArr['end_time'];
        $res = $result = $dataArray = $diff_res = [];
        $return_res = $this->getDataS($url,$dataArr,10);


        ksort($return_res);
        if (!empty($return_res)){
            switch ($action_type){
                case 1:
                    //type = 1 返回 该日期 0-23 每一个小时的数据量
                    if ($type == 1){

                        $res = $this->model->getTotalCount($type,'kefu_common_member','reg_date',$start_time,$end_time);
                        $different_time = $this->hourDifferent('userRegister',$return_res,$res,$start_time,$end_time);

                    }elseif ($type == 2){

                        $res = $this->model->getTotalCount($type,'kefu_common_member','reg_date');
                        $different_time = $this->dayDifferent('userRegister',$return_res,$res);

                    }
                    break;
                case 2:
                    if ($type == 1){

                        $res = $this->model->getTotalCount($type,'kefu_login_log','login_date',$start_time,$end_time);
                        $different_time = $this->hourDifferent('userLogin',$return_res,$res,$start_time,$end_time);

                    }elseif ($type == 2){
                        //返回数据格式 data": {"2020-08-01":23,"2020-08-02":45........,"2020-08-30":20}
                        $res = $this->model->getTotalCount($type,'kefu_login_log','login_date');
                        $different_time = $this->dayDifferent('userLogin',$return_res,$res);

                    }
                    break;
                case 3:
                    if ($type == 1){

                        $res = $this->model->getTotalCount($type,'kefu_pay_order','pay_time',$start_time,$end_time);
                        $different_time = $this->hourDifferent('userPay',$return_res,$res);

                    }elseif ($type == 2){

                        $res = $this->model->getTotalCount($type,'kefu_pay_order','pay_time');
                        $different_time = $this->dayDifferent('userPay',$return_res,$res);
                    }
                    break;
            }

            $con = new GetBaseInfo();

            $params = get_object_vars($params);
            foreach($different_time['different_time'] as $k=>$v){
                $params['start_time'] = $v['start_time'];
                $params['end_time'] = $v['end_time'];
                $con->{$different_time['interface_name']}($params);

            }

            return true;
        }else{
            return false;
        }
    }



    private function hourDifferent($interface_name,$return_res,$res,$start_time,$end_time)
    {
        $dataArray = [];
        foreach ($res as $k=>$v){
            if (in_array($this->platform,['mh'])){
                $dataArray[intval($v['h'])] = $v['totalCount'];
            }else {
                $dataArray[$v['h']] = $v['totalCount'];
            }

        }

        $diff_res = array_diff_assoc($return_res,$dataArray);//比较差集，返回不同的


        if ($diff_res){
            ksort($diff_res);
            //返回的差集不是一个空数组，说明某几个时间段的数据与我们数据库里面的数据不一致，需要把那几个时间段的补漏
            foreach ($diff_res as $k=>$v){
                $start_time = mktime($k,0,0,date('m',$start_time),date('d',$start_time),date('Y',$start_time));
                $end_time = mktime($k+1,0,0,date('m',$start_time),date('d',$start_time),date('Y',$start_time));

                $return['different_time'][$k]['start_time'] = date('Y-m-d H:i:s',$start_time);
                $return['different_time'][$k]['end_time'] = date('Y-m-d H:i:s',$end_time);
            }
            $return['interface_name'] = $interface_name;
            return $return;

        }else{
            return true;
        }
    }



    private function dayDifferent($interface_name,$return_res,$res)
    {
        $return = [];
        $dataArray = [];
        foreach ($res as $k=>$v){
            $dataArray[$v['registerDay']] = $v['registerCount'];
        }

        $diff_res = array_diff_assoc($return_res,$dataArray);
        if ($diff_res){
            ksort($diff_res);
            //返回的差集不是一个空数组，说明某几天时间段的数据与我们数据库里面的数据不一致，需要把那几天时间段的补漏
            foreach ($diff_res as $k=>$v) {
                $start_time = $k . ' 00:00:00';
                $start_time = $start_time;
                $end_time = date('Y-m-d H:i:s',strtotime($start_time) + 86400);
                $return['start_time'] = $start_time;
                $return['end_time'] = $end_time;
                $return['interface_name'] = $interface_name;

                return $return;
            }
        }else{
            return false;
        }
    }



    public function repairRole($obj)
    {
        $start_time = $obj->start_time;
        $end_time = $obj->end_time;

        $date1 = date_create(date("Y-m-d",$start_time));
        $date2 = date_create(date("Y-m-d",$end_time));
        $date_obj = date_diff($date1,$date2);
        $days = (int)$date_obj->format('%a');

        $tmp_start_time = $start_time;
        $tmp_end_time = $end_time;
        $con = new GetBaseInfo();
        if($obj->type == 2){
            for($i = 1;$i<=$days;$i++){

                $start_time = mktime(0,0,0,date('m',$tmp_start_time),date('d',$tmp_start_time)+$i-1,date('Y',$tmp_start_time));
                $end_time = mktime(0,0,0,date('m',$tmp_start_time),date('d',$tmp_start_time)+$i,date('Y',$tmp_start_time));

                $params = get_object_vars($obj);
                $params['start_time'] = $start_time;
                $params['end_time'] = $end_time;

                $con->userRole($params);
            }


        }else{
            $start_time = mktime(0,0,0,date('m',$tmp_start_time),date('d',$tmp_start_time)-1,date('Y',$tmp_start_time));
            $end_time = mktime(0,0,0,date('m',$tmp_end_time),date('d',$tmp_end_time),date('Y',$tmp_end_time));

            $params = get_object_vars($obj);
            $params['start_time'] = date('Y-m-d',$start_time);
            $params['end_time'] =  date('Y-m-d',$end_time);

            $con->userRole($params);
        }

        return true;
    }


    /**
     * 模拟post提交函数
     * @param $post_url
     * @param $post_arr
     * @param int $timeOut
     * @param bool $cookie
     * @param bool $header
     * @return array
     */
    protected function post_curl($post_url, $post_arr, $timeOut = 0, $cookie = false, $header = false)
    {
        $timeOut = $timeOut ?: VALUE_TIMEOUT;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $post_url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_arr);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        if (strpos($post_url, 'https') !== false) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        }
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeOut);
        $header && curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        $cookie && curl_setopt($ch, CURLOPT_COOKIE, $cookie);
        $response = curl_exec($ch);    //这个是读到的值
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        unset($ch);
        return array($httpCode, $response);
    }
}