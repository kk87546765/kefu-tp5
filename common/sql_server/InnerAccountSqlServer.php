<?php
/**
 * Created by PhpStorm.
 * User: crosstime
 * Date: 2018/8/14
 * Time: 下午5:31
 */
namespace common\sql_server;

use common\model\db_statistic\InnerAccount;
use common\server\InnerAccount\InnerAccountServer;
use common\sql_server\BaseSqlServer;
use common\model\db_customer\KefuCommonMember;
use common\model\db_statistic\KefuRecharge;
use common\server\CustomerPlatform\CommonServer;

class InnerAccountSqlServer extends BaseSqlServer
{
    public static $return=[];




    public static function getList($where='',$page,$limit)
    {
        $offset = ($page-1)*$limit;
        $model = new InnerAccount();
        
        $res = $model->field('a.*,b.username,b.realname')->table('inner_account a')->join('gr_chat.gr_admin b','a.admin_id=b.id')->where($where)->limit($offset,$limit)->select();

        $res = isset($res) ? $res->toArray() : [];

        return $res;
    }

    public static function count($where='')
    {
        $model = new InnerAccount();
        $count = $model->alias('a')->where($where)->count();
        return $count;
    }



    public static function getUserInfo($where,$platform_info){

        $platform_suffix = empty($platform_info['platform_suffix']) ? '' :'_'.$platform_info['platform_suffix'];

        $model = CommonServer::getPlatformModel('KefuCommonMember',$platform_info['platform_suffix']);

        $sql = "select c.*,{$platform_info['platform_id']} as platform_id,'{$platform_info['platform_name']}' as platform_name 
        from 
        db_customer{$platform_suffix}.kefu_common_member c
        left join db_customer{$platform_suffix}.kefu_user_role r
        on c.uid = r.uid
        where {$where}
        group by uid
        ";

        $res = $model->query($sql);
        return $res;

    }

//    public static function getUserTotalPay($uid,$platform_id){
//
//        $model = new KefuRecharge();
//
//        $sql = "select total_pay,day_hign_pay from db_statistic.kefu_user_recharge where uid='{$uid}' and platform_id={$platform_id}";
//
//        $res = $model->query($sql);
//
//        return $res;
//
//    }


    public static function getRoleLost($uid,$platform_info){

        $platform_suffix = empty($platform_info['platform_suffix']) ? '' :'_'.$platform_info['platform_suffix'];
        $model = CommonServer::getPlatformModel('KefuUserRole',$platform_info['platform_suffix']);
        $sql = "select a.*,b.product_name,b.game_name,sum(c.amount) as count_money
                from db_customer{$platform_suffix}.kefu_user_role a 
                left join db_statistic.platform_game_info b 
                on 
                a.reg_gid = b.game_id and b.platform_id = {$platform_info['platform_id']}
                left join db_statistic.every_day_role_amount_statistics c 
                on 
                a.role_id = c.role_id and a.reg_gid = c.game_id and c.platform_id = {$platform_info['platform_id']}
                 where a.uid = {$uid}
                  group by a.role_id
                 ";

        $res = $model->query($sql);

        return $res;

    }


    public static function add($data,$is_update = 0)
    {

        $model = new InnerAccount();
        $res = $model->isUpdate($is_update)->save($data);
        return $res;
    }





    public static function addChangeLog($data){

    }





    //获取登录日志
    public static function getLoginLog($data = [])
    {
        $model = CommonServer::getPlatformModel('KefuLoginLog',$data['platform_suffix']);
        $sql = "SELECT
                 b.reg_ip,
                 b.imei AS reg_imei,
                 b.idfa AS reg_idfa,
                 b.udid as reg_udid,
                 a.uid,
                 a.login_ip,
                 a.imei AS login_imei,
                 a.idfa AS login_idfa,
                 a.udid AS login_udid,
                 a.login_date
                FROM
                    db_customer_{$data['platform_suffix']}.kefu_login_log a 
                LEFT JOIN db_customer_{$data['platform_suffix']}.kefu_common_member b 
                ON a.uid =b.uid
                WHERE
                    a.login_date >= {$data['start_time']}
                AND a.login_date <= {$data['end_time']}
                ORDER BY
                    a.login_date asc
                "

                ;

        $login_infos = $model->query($sql);

        return $login_infos;
    }


    //获取内部账号信息
    public static function getInnerAccount($uid_infos)
    {

        $where['uid'] = ['in',array_column($uid_infos,'uid')];

        $res = self::getList($where,1,999999);

        return $res;
    }

    //更新内部号登录信息
    public static function updateInnerAccount($uid_infos,$login_infos)
    {
        $tmp_data = [];
        if(empty($uid_infos)){
            return false;
        }

        $time = time();

        foreach($uid_infos as $k=>$v){


            $all_login_equmpment = array_column($login_infos[$v['uid']],'login_udid');
            $all_login_equmpment = implode(',',$all_login_equmpment);
            $often_login_equmpment = !empty($v['often_login_equipment']) ? $v['often_login_equipment'] : '';
            $often_login_equmpment = $often_login_equmpment . ','. $all_login_equmpment;


            $all_login_ip = array_column($login_infos[$v['uid']],'login_ip');
            $all_login_ip = implode(',',$all_login_ip);
            $often_login_ip = !empty($v['often_login_ip']) ? $v['often_login_ip'] : '';
            $often_login_ip = $often_login_ip . ','. $all_login_ip;

            $arr_often_login_equmpment = explode(',',$often_login_equmpment);
            $arr_often_login_equmpment = array_unique($arr_often_login_equmpment);
            array_splice($arr_often_login_equmpment,InnerAccountServer::$waring_num);
            $often_login_equmpment = implode(',',$arr_often_login_equmpment);

            $arr_often_login_ip = explode(',',$often_login_ip);
            $arr_often_login_ip = array_unique($arr_often_login_ip);
            array_splice($arr_often_login_ip,InnerAccountServer::$waring_num);
            $often_login_ip = implode(',',$arr_often_login_ip);

//            $often_login_equmpment_count = !empty($often_login_equmpment) ? substr_count($often_login_equmpment,',') : 0;
//            $often_login_equmpment = $often_login_equmpment_count <= 4
//                ? $often_login_equmpment.','.$login_infos[$v['uid']][0]['login_udid']
//                : $often_login_equmpment;
//
//            $often_login_ip_count = !empty($often_login_ip) ? substr_count($often_login_ip,',') : 0;
//            $often_login_ip = $often_login_ip_count <= 4
//                ? $often_login_ip.','.$login_infos[$v['uid']][0]['login_ip']
//                : $often_login_ip;

            $tmp_data[$k]['id']                      = $v['id'];
            $tmp_data[$k]['login_ip']                = $login_infos[$v['uid']][count($login_infos[$v['uid']])-1]['login_ip'];
            $tmp_data[$k]['login_equipment']         = $login_infos[$v['uid']][count($login_infos[$v['uid']])-1]['login_udid'];
            $tmp_data[$k]['often_login_equipment']   = trim($often_login_equmpment,',');
            $tmp_data[$k]['often_login_ip']          = trim($often_login_ip,',');
            $tmp_data[$k]['login_time']              = $login_infos[$v['uid']][count($login_infos[$v['uid']])-1]['login_date'];
            $tmp_data[$k]['reg_ip']                  = $login_infos[$v['uid']][count($login_infos[$v['uid']])-1]['reg_ip'];
            $tmp_data[$k]['reg_equipment']           = $login_infos[$v['uid']][count($login_infos[$v['uid']])-1]['reg_udid'];
            $tmp_data[$k]['add_time']                = $time;


        }

        self::update($tmp_data);


    }





    private static function update($tmp_data)
    {

        $model = new InnerAccount();
        $res = $model->saveAll($tmp_data);

    }



}