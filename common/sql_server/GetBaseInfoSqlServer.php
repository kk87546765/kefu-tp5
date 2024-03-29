<?php
/**
 * Created by PhpStorm.
 * User: crosstime
 * Date: 2018/8/14
 * Time: 下午5:31
 */
namespace common\sql_server;


use common\libraries\Common;
use common\model\db_customer_platform\KefuCommonMember;
use common\model\db_customer_platform\KefuPayOrder;
use common\model\db_customer_platform\KefuUserRole;
use common\model\db_customer_platform\KefuLoginLog;
use common\model\db_customer_platform\KefuServerList;
use common\server\CustomerPlatform\CommonServer;
use think\Db;

class GetBaseInfoSqlServer extends BaseSqlServer
{
    public $block_uname = "block_uname_";
    public $block_ip = "block_ip_";
    public $block_imei = "block_imei_";
    public $platform = '';
    public function __construct($platform)
    {
        $this->platform = $platform;
    }


    public function insertReg($info){
        $sql = "insert into db_customer_{$this->platform}.kefu_common_member(`uid`,`user_name`,`reg_date`,`login_date`,`reg_channel`,`mobile`,`reg_gid`,`status`,`imei`,`idfa`,`reg_ip`) values";
        foreach( $info as $v ){
            $sql .= "('{$v['uid']}',".
                "'{$v['user_name']}',".
                "'{$v['reg_date']}',".
                "{$v['login_date']},".
                "'{$v['reg_channel']}',".
                "'{$v['mobile']}',".
                "{$v['reg_gid']},".
                "{$v['status']},".
                "'{$v['imei']}',".
                "'{$v['idfa']}',".
                "'{$v['reg_ip']}'";

            $sql .= "),";
        }
        $sql = trim($sql,",");

        $model = new KefuCommonMember();
        $ret = $model->execute($sql);

        return $ret;
    }

    /**
     * @param $info
     * @return ResultSet
     */
    public function insertIgnoreIntoReg($info){
        $sql = "INSERT IGNORE INTO db_customer_{$this->platform}.kefu_common_member(
                    `uid`
                    ,`user_name`
                    ,`reg_date`
                    ,`login_date`
                    ,`reg_channel`
                    ,`mobile`
                    ,`reg_gid`
                    ,`status`
                    ,`imei`
                    ,`idfa`
                    ,`reg_ip`
                    ) values";
        foreach( $info as $v ){
            $sql .= "('{$v['uid']}',".
                "'{$v['user_name']}',".
                "'{$v['reg_date']}',".
                "{$v['login_date']},".
                "'{$v['reg_channel']}',".
                "'{$v['mobile']}',".
                "{$v['reg_gid']},".
                "{$v['status']},".
                "'{$v['imei']}',".
                "'{$v['idfa']}',".
                "'{$v['reg_ip']}'";
            $sql .= "),";
        }
        $sql = trim($sql,",");
        $model = new KefuCommonMember();
        $res = $model->execute($sql);
        return $res;
    }

    public  function insertLogin($info){
        $sql = "insert into db_customer_{$this->platform}.kefu_login_log(`uid`,`month`,`uname`,`gid`,`game_name`,`login_date`,`login_channel`,`imei`,`idfa`,`mobile`,`real_name`,`id_card`,`real_name_time`,`login_ip`) values";
        foreach( $info as $v ){
            $sql .= "('{$v['uid']}',".
                "{$v['month']},".
                "'{$v['uname']}',".
                "'{$v['gid']}',".
                "'{$v['game_name']}',".
                "'{$v['login_date']}',".
                "'{$v['login_channel']}',".
                "'{$v['imei']}',".
                "'{$v['idfa']}',".
                "'{$v['mobile']}',".
                "'{$v['real_name']}',".
                "'{$v['id_card']}',".
                "{$v['real_name_time']},".
                "'{$v['login_ip']}'";

            $sql .= "),";
        }
        $sql = trim($sql,",");

        $model = new KefuLoginLog();
        $ret = $model->execute($sql);

        return $ret;
    }


    public function insertOrders($info){
        $sql = "INSERT INTO db_customer_{$this->platform}.kefu_pay_order(`order_id`,`uid`,`user_name`,`amount`,`role_id`,`role_name`,`gid`,`game_name`,`server_id`,`server_name`,`pay_channel`,`imei`,`idfa`,`dateline`,`third_party_order_id`,`reg_channel`,`payment`,`yuanbao_status`,`first_login_game_id`,`first_login_game_time`,`pay_time`) values";
        foreach( $info as $v ){
            $sql .= "('{$v['order_id']}',".
                "{$v['uid']},".
                "'{$v['user_name']}',".
                "{$v['amount']},".
                "'{$v['role_id']}',".
                "'".addslashes($v['role_name'])."',".
                "'{$v['gid']}',".
                "'{$v['game_name']}',".
                "'{$v['server_id']}',".
                "'{$v['server_name']}',".
                "'{$v['pay_channel']}',".
                "'{$v['imei']}',".
                "'{$v['idfa']}',".
                "'{$v['dateline']}',".

                "'{$v['third_party_order_id']}',".
                "'{$v['reg_channel']}',".
                "'{$v['payment']}',".
                "{$v['yuanbao_status']},".
                "{$v['first_login_game_id']},".
                "{$v['first_login_game_time']},".

                "{$v['pay_time']}";

            $sql .= "),";
        }
        $sql = trim($sql,",");

        $model = new KefuPayOrder();
        $ret = $model->execute($sql);

        return $ret;
    }


    /**
     * @param $uid
     * @param $role_id
     * @return int
     */
    public function checkRoleExists($uid,$role_id)
    {

        $num = 0;
        $sql = "select count(uid)as num from db_customer_{$this->platform}.kefu_user_role where uid = {$uid} and role_id = '{$role_id}' limit 1";

        $model = new KefuUserRole();
        $ret = $model->query($sql);

        $ret = isset($ret) ? $ret->toArray() : 0;

        $num = empty($ret)? 0 : $ret[0]['num'];

        return $num;

    }


    /**
     * @param $uid
     * @param $role_id
     * @return int
     */
    public function checkServerExists($gid,$server_id)
    {

        $num = 0;
        $sql = "select count(uid)as num from db_customer_{$this->platform}.kefu_server_list where gid = {$gid} and server_id = '{$server_id}'  limit 1";

        $model = new KefuServerList();
        $ret = $model->query($sql);
        $ret = isset($ret) ? $ret->toArray() : 0;
        $num = empty($ret)? 0 : $ret[0]['num'];

        return $num;

    }

    /**
     * @param string $order
     * @param int $starTime
     * @param int $endTime
     * @return int[]
     */
    public function getPayTime($order = 'asc', $starTime = 0, $endTime = 0)
    {
        $result = ['pay_time' =>0];
        $sql = "select pay_time from db_customer_{$this->platform}.kefu_pay_order where pay_time > {$starTime} and pay_time <{$endTime} order by pay_time ".$order." limit 1";

        $model = new KefuPayOrder();
        $ret = $model->query($sql);
        $ret = isset($ret) ? $ret->toArray() : 0;
        $result['pay_time'] = empty($ret[0]['pay_time'])? 0 : $ret[0]['pay_time'];

        return $result;
    }

    /**
     * 检测用户是否存在
     * @param $alias
     * @param $uid
     * @param $order_id
     * @return mixed
     */
    public function checkUserExists($uid)
    {
        $sql = "select count(uid)as num from db_customer_{$this->platform}.kefu_common_member where uid = {$uid} limit 1";

        $model = new KefuCommonMember();
        $ret = $model->query($sql);
        $num = empty($ret)? 0 : $ret[0]['num'];

        return $num;

    }

    /**
     * 检测用户是否存在
     * @param $alias
     * @param $uid
     * @param $order_id
     * @return mixed
     */
    public function checkLoginUserExists($checkArr)
    {

        $sql = "select count(uid)as num from db_customer_{$this->platform}.kefu_login_log where uid = {$checkArr['uid']} and gid = {$checkArr['gid']} and login_date = {$checkArr['login_date']}  limit 1";

        $model = new KefuLoginLog();

        $ret = $model->execute($sql);

        $num = empty($ret)? 0 : $ret[0]['num'];

        return $num;

    }

    /**
     * 检测用户是否存在
     * @param $alias
     * @param $uid
     * @param $order_id
     * @return mixed
     */
    public function checkOrdersExists($order_id)
    {

        $sql = "select count(uid)as num from db_customer_{$this->platform}.kefu_pay_order where order_id = '{$order_id}' limit 1";

        $model = new KefuPayOrder();

        $ret = $model->query($sql);

        $ret = isset($ret) ? $ret->toArray() : 0;

        $num = empty($ret)? 0 : $ret[0]['num'];

        return $num;

    }

    public function getLastLoginTime($uid)
    {

        $sql = "select uid,login_date from db_customer_{$this->platform}.kefu_common_member where uid = {$uid}";

        $model = new KefuCommonMember();

        $ret = $model->query($sql);

        $login_date = empty($ret[0]['uid'])? false : $ret[0]['login_date'];

        return $login_date;

    }


    public function updateUserLoginTime($update_data)
    {

        $sql1 = $sql2 = $sql3 = $sql4 = $sql5 = $sql6 = '';
        if(!empty(($update_data['mobile']))){
            $sql1 = ",mobile = '{$update_data['mobile']}'";
        }
        if(!empty(($update_data['login_date']))){
            $sql2 = ",login_date = {$update_data['login_date']}";
        }
        if(!empty(($update_data['login_ip']))){
            $sql3 = ",login_ip = '{$update_data['login_ip']}'";
        }
        if(!empty(($update_data['real_name']))){
            $sql4 = ",real_name = '{$update_data['real_name']}'";
        }
        if(!empty(($update_data['id_card']))){
            $sql5 = ",id_card = '{$update_data['id_card']}'";
        }
        if(!empty(($update_data['real_name_time']))){
            $sql6 = ",real_name_time = '{$update_data['real_name_time']}'";
        }

        if(isset($sql1) || isset($sql2)|| isset($sql3) || isset($sql4) || isset($sql5) || isset($sql6)){
            $new_sql = trim($sql1.$sql2.$sql3.$sql4.$sql5.$sql6,',');

            $sql = "update db_customer_{$this->platform}.kefu_common_member set {$new_sql}  where uid = {$update_data['uid']}";

            $model = new KefuCommonMember();

            $ret = $model->execute($sql);

            return $ret;
        }else{
            return false;
        }

    }



    public function getLastUserRoleLoginTime($uid,$role_id)
    {

        $sql = "select uid,role_id,login_date from db_customer_{$this->platform}.kefu_user_role where uid = {$uid} and role_id= '{$role_id}'";

        $model = new KefuCommonMember();

        $ret = $model->query($sql);

        $user_info = empty($ret[0]['uid'])? false : $ret[0];

        return $user_info;

    }


    public function updateUserRoleLoginTime($update_data)
    {


        if(!empty(($update_data['mobile']))){
            $sql1 = ",role_level = '{$update_data['role_level']}'";
        }
        if(!empty(($update_data['login_date']))){
            $sql2 = ",login_date = {$update_data['login_date']}";
        }

        if(isset($sql1) || isset($sql2)){
            $sql4 = trim($sql1.$sql2,',');

            $sql = "update db_customer_{$this->platform}.kefu_user_role set {$sql4}  where uid = {$update_data['uid']} and role_id = '{$update_data['role_id']}'";

            $model = new KefuUserRole();

            $ret = $model->execute($sql);

            return $ret;
        }else{
            return false;
        }

    }


    public function insertRole($info)
    {
        $sql = "insert ignore into db_customer_{$this->platform}.kefu_user_role(`uid`,`uname`,`role_id`,`role_name`,`trans_level`,`role_level`,`reg_gid`,`reg_channel`,`reg_time`,`server_id`,`server_name`,`login_date`) values";

        foreach( $info as $v ){
            $sql .= "({$v['uid']},".
                "'{$v['uname']}',".
                "'{$v['role_id']}',".
                "'".addslashes($v['role_name'])."',".
                "{$v['trans_level']},".
                "{$v['role_level']},".
                "{$v['reg_gid']},".
                "{$v['reg_channel']},".
                "{$v['reg_time']},".
                "'{$v['server_id']}',".
                "'{$v['server_name']}',".
                "{$v['login_date']}";

            $sql .= "),";

        }
        $sql = trim($sql,",");

        $model = new KefuUserRole();

        $ret = $model->execute($sql);

        return $ret;
    }


    public function insertServer($info,$platform){
        foreach($info as $k=>$v){

            $kefu_server_list_model = CommonServer::getPlatformModel('KefuServerList',$platform);
            $res = $kefu_server_list_model->where("gid = {$v['gid']} and server_id = '{$v['server_id']}'")->order("open_time asc")->find();

            if($res){
                $data = $res->toArray();
                $open_time = $data['open_time'];

                //如果对应游戏的区服已存在则直接更新开服时间，
                if(empty($data['open_time'])|| strtotime(date('Y-m-d',$data['open_time'])) > strtotime(date('Y-m-d',$v['open_time']))){

                    unset($info[$k]);

                    $open_time = strtotime(date('Y-m-d',$v['open_time']));

                }

                $kefu_server_list_model->isUpdate(1)->save(['open_time'=>$open_time],['id'=>$res['id']]);
            }

        }


        $ret = [];
        if($info){

            $res = Common::getPlatformList(['platform_suffix'=>$platform]);

            foreach($res as $k=>$v){
                $platform_id = $v['platform_id'];
                break;
            }


            $sql = "insert ignore into db_customer_{$this->platform}.kefu_server_list(`gid`,`server_id`,`server_name`,`platform_id`,`open_time`) values";

            foreach( $info as $v ){
                $sql .="({$v['gid']},".
                    "'{$v['server_id']}',".
                    "'{$v['server_name']}',".
                    "{$platform_id},".
                    "'{$v['open_time']}'";

                $sql .= "),";

            }

            $sql = trim($sql,",");
            $ret = $kefu_server_list_model->execute($sql);

        }


        return $ret;
    }



    public function getTotalCount($type,$table,$select_field,$start_time,$end_time)
    {
        $tmp_db = Common::camelize($table);
        switch($type)
        {
            case 1:
                $sql = "SELECT
                    (
                        FROM_UNIXTIME('{$select_field}', '%H')
                    ) AS h,
                    count(*) as totalCount
                FROM
                    db_customer_{$this->platform}.{$table}
                WHERE
                    {$select_field} >= {$start_time}
                AND {$select_field} < {$end_time}
                GROUP BY
                    h";
                break;
            case 2:
                $sql = "SELECT 
                    FROM_UNIXTIME(reg_date,'%Y-%m-%d') TotalDay,
                    COUNT(id) totalCount
                FROM
                    db_customer_{$this->platform}.{$table}
                WHERE
                    {$select_field} >= {$start_time}
                AND {$select_field} < }$end_time}
                GROUP BY
                    TotalDay
                ORDER BY
                    {$select_field}";
                break;
            case 3:
                break;
        }

        $res = Db::query($sql);

        return $res;
    }











}