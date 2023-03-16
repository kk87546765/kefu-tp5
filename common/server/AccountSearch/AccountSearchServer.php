<?php
/**
 * Created by PhpStorm.
 * User: crosstime
 * Date: 2017/10/12
 * Time: 上午11:57
 */
namespace common\server\AccountSearch;

use common\base\BasicServer;
use common\server\Platform\{BanServer,Platform};
use common\sql_server\{AccountSearch,KefuCommonMember};
use common\libraries\{ApiUserInfoSecurity,Common};
//use common\model\db_statistic\AccountSearchLog;
use common\sql_server\{AccountSearchLog,KefuLoginLog,KefuPayOrder};



class AccountSearchServer extends BasicServer
{

    public static $return = ['status'=>false,'msg'=>''];

    public static $change_type = [
        '1' => ['id'=>1,'type'=>'修改密码'],
        '2' => ['id'=>2,'type'=>'换绑手机'],
        '3' => ['id'=>3,'type'=>'封禁']
    ];
    /***
     *从所有平台获取数据
    */
    public static function getUserInfoByAllPlatform($account,$type,$platform_id = []){


        if(empty($account) || empty($type)){
            return [];
        }

        $where = ' 1 ';
        if($type == 1){
            $where .= " and c.user_name='{$account}'";
        }elseif($type == 2){
            $where .= " and c.uid={$account}";
        }elseif($type == 3){
            $account = ApiUserInfoSecurity::encrypt($account);
            $where .= " and c.mobile='{$account}'";
        }elseif($type == 5){
            $where .= " and r.role_id='{$account}'";
        }

        $platform_list = Common::getPlatformList();
//


        $new_res = [];
        foreach($platform_list as $k=>$v){
            if($v['platform_suffix'] == 'asjd'){
                continue;
            }
            $res = AccountSearch::getUserInfo($where,$v);
            if(!empty($res)){
                $new_res[] = $res;
            }

        }

        return $new_res;
    }




    public static function getUserTotalPay($data){

        foreach($data as $k=>$v){
            foreach($v as $k1=>$v1){
                if(empty($v1['uid']) || empty($v1['platform_id'])){
                    continue;
                }

                $tmp_data = AccountSearch::getUserTotalPay($v1['uid'],$v1['platform_id']);
                $data[$k][$k1]['total_pay'] = !empty($tmp_data['total_pay']) ? $tmp_data['total_pay'] : 0;
                $data[$k][$k1]['day_hign_pay'] = !empty($tmp_data['day_hign_pay']) ? $tmp_data['day_hign_pay'] : 0;
            }

        }

        return $data;

    }

    public static function getRoleLost($uid,$platform_info){

        if(empty($uid) || empty($platform_info)){
            return [];
        }

        $res = AccountSearch::getRoleLost($uid,$platform_info);

        return $res;

    }

    public static function changePasswordOrMobile($data){


        $return = [
            'code' => -1,
            'msg' => '',
            'data' => '',
        ];
        if(empty($data['uid']) || empty($data['platform_id']) || empty($data['type'])){
            return false;
        }

        //修改密码
        if($data['type'] == 1){
            if(empty($data['new_password'])){
                $return['msg'] = '新密码不能为空';
                return $return;
            }
            $res = self::updatePassword(['uid'=>$data['uid'],'platform_suffix'=>$data['platform_suffix'],'password'=>$data['new_password']]);

        }


        //换绑手机
        if($data['type'] == 2){
            if(empty($data['new_phone'])){
                $return['msg'] = '新手机不能为空';
                return $return;
            }

            $user_info = KefuCommonMember::getFieldInfoByUidAndSuffix([$data['uid']],$data['platform_suffix']);
            if(empty($user_info[0]['mobile'])){
                $return['msg'] = '旧手机不能为空';
                return $return;
            }
            $res = self::updateMobile(['uid'=>$data['uid'],'platform_suffix'=>$data['platform_suffix'],'new_phone'=>$data['new_phone'],'old_phone'=>ApiUserInfoSecurity::decrypt($user_info[0]['mobile'])]);

            //平台换绑成功后台修改客服中心对应的用户信息
//            if($res['code'] == 1){
            if(true){
                KefuCommonMember::updateCommonMemberMobile(ApiUserInfoSecurity::encrypt($data['new_phone']),[$data['uid']],1,$data['platform_suffix']);
            }

        }

//        if(isset($res['code']) && $res['code'] == 1){
        if(true){

            $return['code'] = 0;
            $data_log = [];
            $data_log['uid'] = $data['uid'];
            $data_log['platform_id'] = $data['platform_id'];
            $data_log['type'] = $data['type'];
            $data_log['admin_id'] = $data['admin_id'];
            $data_log['admin_name'] = $data['admin_name'];
            $data_log['change_content'] = $data['type'] == 1 ? $data['new_password'] : $data['new_phone'];
            $data_log['remarks'] = $data['remarks'];
            $data_log['add_time'] = time();

            AccountSearchLog::addChangeLog([$data_log]);


            $return['msg'] = '修改成功';
        }else{
            $return['msg'] = '修改失败';
        }



        return $return;
    }


    public static function updatePassword($data){

        $platform_info = Common::getPlatform();

        $res = Platform::updatePassword($data,$platform_info[$data['platform_suffix']]);

        return $res;

    }

    //换绑手机
    public static function updateMobile($data){

        $platform_info = Common::getPlatform();

        $res = Platform::updateMobile($data,$platform_info[$data['platform_suffix']]);

        return $res;
    }

    //获取登录日志
    public static function getLoginLog($uid,$platform_info,$start_time,$end_time){

        $res = KefuLoginLog::getLoginLogByUid($uid,$platform_info['platform_suffix'],'desc',$start_time,$end_time);

        return $res;
    }

    //获取登录日志
    public static function getLoginLogNew($param,$platform_info){

        $page = getArrVal($param,'page',1);
        $limit = getArrVal($param,'limit',20);

        $where = getDataByField($param,['uid']);

        if (!empty($param['login_date'])) {
            $timeArr = explode(' - ', $param['login_date']);
            if (!empty($timeArr) && count($timeArr) > 1) {
                $where[] = ['login_date','between', [strtotime($timeArr[0]), strtotime($timeArr[1].'23:59:59')]];
            }
        }

        $return = ['list' => [],'count' => 0];

        $model = new KefuLoginLog();
        $total = $model->getCount($where,$platform_info['platform_suffix']);

        if(!$total){
            return $return;
        }

        $return['count'] = $total;

        $return['list'] = $model->getList($where,$page,$limit,'login_date desc',$platform_info['platform_suffix']);

        return $return;
    }

    //获取支付日志
    public static function getPayLog($uid,$platform_info,$start_time,$end_time){
        $res = KefuPayOrder::getPayLogByUid($uid,$platform_info['platform_suffix'],'desc',$start_time,$end_time);

        return $res;
    }

    //获取客服操作记录
    public static function getkefuLog($uid,$platform_info){
        $res = AccountSearchLog::getKefuLogByUid($uid,$platform_info['platform_id']);

        return $res;
    }

    public static function ban_user($data,$platform_info){

        $info[$platform_info['platform_suffix']][0]['uid'] = $data['uid'];
        $info[$platform_info['platform_suffix']][0]['sdkUid'] = $data['uid'];
        $info[$platform_info['platform_suffix']][0]['user_name'] = $data['user_name'];


        //平台封禁操作
        $BanLogicModel = new BanServer();


        $res = $BanLogicModel->ban(
            $info,
            1,
            1,
            365*86400,//默认封禁1年
            '中心账号封禁');



        if(!empty($res['succ'])){
            $return['code'] = 0;
            $return['msg'] = '封禁成功';
            $return['data'] = '';
        }else{
            $return['code'] =  -1 ;
            $return['msg'] = '封禁失败';
            $return['data'] = '';
        }

        $data_log = [];
        $data_log['uid'] = $data['uid'];
        $data_log['platform_id'] = $data['platform_id'];
        $data_log['type'] = 3; //封禁
        $data_log['admin_id'] = $data['admin_id'];
        $data_log['admin_name'] = $data['admin_name'];
        $data_log['change_content'] = '';
        $data_log['remarks'] = '中心账号封禁';
        $data_log['add_time'] = time();

        AccountSearch::addChangeLog([$data_log]);

        return $return;

    }

    public function getAccountSeacrchLogByUid($uid,$platform,$start_time,$end_time,$type){
        if(empty($uid) || empty($platform)){
            return false;
        }

        $res = AccountSearchLog::getChangeLog($uid,$platform,$start_time,$end_time,$type);
        return $res;
    }

    public static function dealData($res){

        foreach($res as $k=>$v){
            foreach($v as $k1=>$v1){
                $res[$k][$k1]['mobile'] = empty($v1['mobile']) ? '' : substr_replace(ApiUserInfoSecurity::decrypt($v1['mobile']),'****',3,4);
                $res[$k][$k1]['reg_date'] = empty($v1['reg_date']) ? '' : date('Y-m-d H:i:s',$v1['reg_date']);
                $res[$k][$k1]['login_date'] = empty($v1['login_date']) ? '' : date('Y-m-d H:i:s',$v1['login_date']);
                $res[$k][$k1]['real_name_time'] = empty($v1['real_name_time']) ? '' : date('Y-m-d H:i:s',$v1['real_name_time']);
                $res[$k][$k1]['id_card'] = empty($v1['id_card']) ? '' : substr_replace($v1['id_card'],'****',14,4);
            }

        }

        return $res;
    }


}