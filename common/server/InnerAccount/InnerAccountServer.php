<?php
/**
 * Created by PhpStorm.
 * User: crosstime
 * Date: 2017/10/12
 * Time: 上午11:57
 */
namespace common\server\InnerAccount;

use common\base\BasicServer;
use common\server\Platform\{BanServer,Platform};
use common\sql_server\{InnerAccountSqlServer,KefuCommonMember};
use common\libraries\{ApiUserInfoSecurity, Common, Dingding, Ipip\IP, Ipip\IP4datx};
//use common\model\db_statistic\AccountSearchLog;
use common\sql_server\{AccountSearchLog,KefuLoginLog,KefuPayOrder};



class InnerAccountServer extends BasicServer
{

    public static $return = ['code'=>-1,'msg'=>'','where'=>'1 = 1','data'=>[]];

    public static $filter_platform = ['asjd'];


    public static $waring_num = 5;

    public static function getList($data)
    {
        self::dealData($data);

        $res = InnerAccountSqlServer::getList(self::$return['where'],$data['page'],$data['limit']);

        return $res;
    }

    public static function count($data)
    {
        self::dealData($data);
        $res = InnerAccountSqlServer::count(self::$return['where']);
        return $res;
    }

    public static function getOne($data)
    {
        $res = InnerAccountSqlServer::getList(['a.id'=>$data['id']],1,1);
        return $res;
    }

    public static function add($data)
    {
        $return = ['code'=>-1,'msg'=>'失败'];
        if(empty($data['admin_id'])){
            $return['msg'] = '员工不能为空';
            return $return;
        }
        if(empty($data['platform_id'])){
            $return['msg'] = '平台不能为空';
            return $return;
        }


        if($data['id']){
            $res = InnerAccountSqlServer::add($data,1);
        }else{
            $res = InnerAccountSqlServer::add($data,0);
        }

        if($res){
            $return['msg'] = '操作成功';
            $return['code'] = 0;
        }

        return $return;
    }


    public static function del($data)
    {
        if($data['id']){

            $res = InnerAccountSqlServer::add($data,1);
        }else{
            $res = false;
        }


        return $res;
    }



    public static function dealData($res)
    {
        if(!empty($res['uid'])){
            self::$return['where'] .= " AND a.`uid` = '{$res['uid']}'";
        }

        if(!empty($res['admin_id'])){
            self::$return['where'] .= " AND a.`admin_id` = {$res['admin_id']}";
        }

        self::$return['where'] .= " AND a.`status` = {$res['status']}";



        return $res;
    }


    //更新内部账号信息
    public static function dealInnerAccount($data)
    {
        $platform_list = Common::getPlatform();

        foreach($platform_list as $k=>$v){
            if(!in_array($v['field'],self::$filter_platform)){
                self::dealInnerAccountData($data,$v['field']);
            }

        }

        return true;

//        InnerAccountSqlServer::
    }


    //获取需要更新的信息，从登录日志里获取今天有登录的用户
    public static function dealInnerAccountData($data,$platform_suffix)
    {

//        $platform_suffix = 'youyu';
        $new_data = $data;
        $new_data['platform_suffix'] = $platform_suffix;
        $new_data['end_time'] = time();
        $new_data['start_time'] = $new_data['end_time'] - 120;
//        $new_data['start_time'] = 1665221200;


        $login_infos = InnerAccountSqlServer::getLoginLog($new_data);

        $tmp_arr = [];
        $tmp_uids_arr = [];
        $i = 0;
        foreach($login_infos as $k=>$v){
            $i++;
            if($i % 500 == 0 ){
                if(!empty($tmp_uids_arr)){
                    $uid_infos = InnerAccountSqlServer::getInnerAccount($tmp_uids_arr);

                    if(!empty($uid_infos)){

                        InnerAccountSqlServer::updateInnerAccount($uid_infos,$tmp_arr);

                    }
                }

            }else{
                $tmp_uids_arr[$v['uid']]['uid'] = $v['uid'];
                $tmp_arr[$v['uid']][] = $v;

            }
        }

        if(!empty($tmp_uids_arr)){
            $uid_infos = InnerAccountSqlServer::getInnerAccount($tmp_uids_arr);
            if(!empty($uid_infos)){
                InnerAccountSqlServer::updateInnerAccount($uid_infos,$tmp_arr);
            }
        }


        if(!empty($uid_infos) && !empty($tmp_arr)){
            $uid_infos = InnerAccountSqlServer::getInnerAccount($tmp_uids_arr);
            self::checkWaring($platform_suffix,$uid_infos,$tmp_arr);
        }


        return true;
    }


    private static function checkWaring($platform,$uid_infos,$login_infos)
    {

        if(empty($uid_infos)){
            return false;
        }
//     dd($uid_infos);

        foreach($uid_infos as $k=>$v){
            $often_login_equipment = !empty($v['often_login_equipment']) ? $v['often_login_equipment'] : '';

            if(!empty($often_login_equipment) && !strpos($often_login_equipment,$login_infos[$v['uid']][0]['login_udid']) && substr_count($often_login_equipment,',') > self::$waring_num){
                $waring_uids[] = $v['uid'];
            }

        }

        foreach($uid_infos as $k=>$v){
//            $often_login_ip = !empty($v['often_login_ip']) ? $v['often_login_ip'] : '';
            $tmp_ip = $login_infos[$v['uid']][0]['login_ip'] ?: '';
            $ip_address = IP4datx::find($tmp_ip);
            if(empty($ip_address) || $ip_address[2] != '广州' ){
                $waring_ips[] = $v['uid'];
            }

        }

//        $waring_uids = implode(',',$waring_uids);
//        $waring_ips = implode(',',$waring_ips);

        if(!empty($waring_uids)){

            DingDing::notOftenLoginUdidDingding($waring_uids,$platform);

        }

        if(!empty($waring_ips)){

            DingDing::notOftenLoginIpDingding($waring_ips,$platform);

        }


    }


}