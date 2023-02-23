<?php
/**
 * Created by PhpStorm.
 * User: crosstime
 * Date: 2018/8/14
 * Time: 下午5:31
 */
namespace common\sql_server;

use common\server\CustomerPlatform\CommonServer;
use common\sql_server\BaseSqlServer;
class KefuCommonMember extends BaseSqlServer
{
    public static $updateStaticByWhereIn = [
        1 => 'uid',
        2 => 'login_ip',
        3 => 'imei',
        4 => 'user_name'
    ];

    public static $platform;



    /**
     * @param array $ids
     * @return array
     */
    public static function getUnameByUid($ids=[],$tkey){
        if( empty($ids) ) return [];
        $model = CommonServer::getPlatformModel('KefuCommonMember',$tkey);

        $ids = implode(',',$ids);
        $userinfo = $model->where("uid in ({$ids}) ")->select();

        $ret = [];
        foreach( $userinfo->toArray() as $v ){
            $ret[$v['uid']] = $v['user_name'];
        }

        return $ret;
    }


//    /**
//     * @param array $uids
//     * @return array
//     */
//    public static function getStatusByUid($uids=[],$tkey){
//        if( empty($uids) ) return [];
//        $model = CommonServer::getPlatformModel('KefuCommonMember',$tkey);
//        $userinfo = $model->where("uid in {$uids} ")->select();
//
//        $ret = [];
//        foreach( $userinfo->toArray() as $v ){
//            $ret[$v['uid']] = $v['status'];
//        }
//        return $ret;
//    }
//    /**
//     * @param array $accounts
//     * @param $type
//     * @return array|ResultSet
//     */
//    public static function getUnameByAccount($accounts=[],$type,$tkey){
//        if( empty($accounts) ) return [];
//        $model = CommonServer::getPlatformModel('KefuCommonMember',$tkey);
//        $sql = "select * from kefu_common_member where {$type} in ('{$accounts}')";
//
//        $res = $model->query($sql);
//
//        return $res;
//    }


//    /**
//     * @param $data
//     * @return ResultSet
//     */
//    public static function getWaring($data){
//        $having = '1 ';
//        $where = '1=1 ';
//
//        if($data['s_time']){
//            $s_time = strtotime($data['s_time']);
//            $where .=" and reg_date>={$s_time}";
//        }
//
//        if($data['e_time']){
//            $e_time = strtotime($data['e_time']);
//            $where .=" and reg_date<={$e_time}";
//        }
//
//        if($data['gid']){
//
//            $where .=" and reg_gid={$data['gid']}";
//        }
//
//        if($data['type'] == 0){
//            $table = 'gr_ban_ip_log';
//            $group_field = 'login_ip';
//            $field = 'ip';
//            if($data['account'])$having .= "and login_ip like '{$data['account']}%' ";
//        }else{
//            $table = 'gr_ban_imei_log';
//            $group_field = 'imei';
//            $field = 'imei';
//            if($data['account'])$having .= "and imei like '{$data['account']}%'";
//
//        }
//
//        $having .= " and num>={$data['limit_num']} and {$group_field} !='' limit {$data['offset']},{$data['limit']}" ;
//        $sql = "select a.*,b.type,b.dateline from (select {$group_field} as {$field},user_name,uid,count(uid)as num from kefu_common_member where {$where} group by {$group_field} having ".$having.") a
//        left join (select {$field},type,dateline from(select {$field},type,dateline from gr_chat.{$table} ORDER BY dateline desc limit 100000000)c GROUP BY {$field} ) b ON a.{$field} = b.{$field} ";
//        $model = new KefuCommonMemberModel();
//        $res = $model->query($sql);
//
//        return $res;
//    }

    public static function getWaringCount($data){
        $having = '1 ';
        if($data['type'] == 0){
            $table = 'gr_ban_ip_log';
            $group_field = 'login_ip';
            $field = 'ip';
            if($data['account'])$having .= "login_ip like '{$data['account']}%'";

        }else{
            $table = 'gr_ban_imei_log';
            $group_field = 'imei';
            $field = 'imei';
            if($data['account'])$having .= "imei like '{$data['account']}%'";

        }

        $having = '1 ';
        if($data['ip'])$having .= "login_ip = {$data['ip']}";
        $having .= " and num>={$data['limit_num']} and {$group_field} !='' " ;
        $sql = "select count(*)as total_num from(select {$group_field},count(uid)as num from kefu_common_member group by {$group_field} having ".$having.")t";
        $model = new KefuCommonMemberModel();
        $res = self::excuteSQL($sql);
        return $res;
    }

    /**
     * @param $data
     * @param $data2
     * @return false|ResultSet
     */
    public static function getWaringUser($data,$data2){
        if(empty($data['account'])) return false;
        $where = ' 1 ';
        $having = ' having 1 ';
        if($data['type'] == 0){
            $where .= " and login_ip = '{$data['account']}'";
        }else{
            $where .= " and imei = '{$data['account']}'";
        }

        if($data2['check_type'] == 1){
            $having .= " and a.user_name = '{$data2['check_value']}'";
        }elseif($data2['check_type'] == 2){
            $having .= " and o.role_name = '{$data2['check_value']}'";
        }elseif($data2['check_type'] == 3){
            $having .= " and money = {$data2['check_value']}";
        }
//        $where = "";
        $sql = "SELECT
	a.reg_date,
	a.login_date,
	a.reg_gid as gid,
	a.uid,
	a.user_name,
	o.role_name,
	o.server_id,
	o.server_name,
	sum(o.amount) AS money,
	a.`status`
    FROM
     (
     SELECT
        *
        FROM
        kefu_common_member
        where {$where}
        ) a  
	 left join kefu_pay_order as o 
	 on a.user_name = o.user_name 
	 group by a.user_name,o.gid,o.role_id ".$having
        ." limit {$data['offset']},{$data['limit']}";

        $res = self::excuteSQL($sql);
        return $res;
    }

    /**
     * @param $data
     * @param $data2
     * @return ResultSet
     */
    public static function getWaringUserCount($data,$data2){
        $where = ' 1 ';
        $having = ' having 1 ';
        if($data['type'] == 0){
            $where .= " and login_ip = '{$data['account']}'";
        }else{
            $where .= " and imei = '{$data['account']}'";
        }

        if($data2['check_type'] == 1){
            $having .= " and user_name = '{$data2['check_value']}'";
        }elseif($data2['check_type'] == 2){
            $having .= " and role_name = '{$data2['check_value']}'";
        }elseif($data2['check_type'] == 3){
            $having .= " and money = {$data2['check_value']}";
        }


//        $where = "";
        $sql = "select count(uid)as total_num,money,user_name,uid,role_name from(SELECT
	a.reg_date,
	a.login_date,
	o.gid,
	a.uid,
	a.user_name,
	o.role_name,
	o.server_id,
	o.server_name,
	sum(o.amount) AS money,
	a.`status`
    FROM
     (
     SELECT
        *
        FROM
        kefu_common_member
        where {$where}
        ) a  
	 left join kefu_pay_order as o 
	 on a.user_name = o.user_name 
	 group by a.user_name,o.gid,o.role_id)b"
        .$having;
        $res = self::excuteSQL($sql);
        return $res;
    }


    /**
     * @param $users
     * @param $data_type
     * @param $type
     * @return false|ResultSet
     */
    public static function UpdateMemberStatus($users,$data_type,$type){
        $where = '';
        if($data_type == 1 || $data_type == 5){
            $where .= "user_name ='{$users}'";
        }elseif($data_type == 2 || $data_type == 6){
            $where .= "login_ip ='{$users}'";
        }elseif($data_type == 3 || $data_type == 7){
            $where .= "imei ='{$users}'";
        }

        $exists = self::findFirst(['conditions' => $where]);
        if (!$exists)  return false;

        $sql = "update kefu_common_member set status={$type} where {$where} ";
        $res = self::excuteSQL($sql);

        return $res;
    }



    /**
     * @param array $userNames
     * @return array
     */
    public static function getUidByUserName($userNames = [])
    {
        if( empty($userNames) ) return [];
        $userinfo =  self::find([
            'user_name in ({user_name:array})',
            'bind' => ['user_name' => $userNames]
        ]);
        $ret = [];
        foreach( $userinfo->toArray() as $v ){
            $ret[$v['uid']] = $v['user_name'];
        }
        return $ret;
    }

    /**
     * @param int $fieldValue
     * @param array $inArr
     * @param int $type
     * @return false
     */
    public static function updateCommonMemberStatus($fieldValue = 1, $inArr = [], $type = 1, $platformSuffix='')
    {
        if( empty($inArr) ) return false;
        $inStr = implode("','",$inArr);
        $inStr = "'".$inStr."'";
        $sql     = "update kefu_common_member set status=$fieldValue where ";
        $sql .= self::$updateStaticByWhereIn[$type]." in({$inStr})";
        $model = new static();
        if (!empty($platformSuffix)) {
            $model->setWriteConnectionService('db_db_customer_'.$platformSuffix);
        }
        $ret = $model->getWriteConnection()->query($sql);
        return $ret;
    }

    /**
     * @param array $uids
     * @param string $suffix
     * @param string $field
     * @return array|ResultSet
     */
    public static function getFieldInfoByUidAndSuffix($uids=[], $suffix='', $field='*')
    {


        $res = [];
        if (empty($uids) || !is_array($uids)) return $res;
        if (empty($suffix)){
            $suffix = $_SESSION['platform_key'];
        }
        $model = CommonServer::getPlatformModel('KefuCommonMember',$suffix);

        $suffix = '_'.$suffix;

        $sql = "select ";
        if ($field === '*' || empty($field)){
            $sql .= "*";
        }elseif (is_array($field)){
            $fieldStr = implode(',', $field);
            $sql .= "$fieldStr";
        }else{
            $sql .= "$field";
        }
        $sql .= " from db_customer{$suffix}.kefu_common_member where uid ";

        if (is_array($uids)){
            $inStr = implode("','",$uids);
            $inStr = "'".$inStr."'";
            $sql .= "in({$inStr})";
        }else{
            //兼容传字符串
            $sql .= "in({$uids})";
        }
        $res = $model->query($sql);

        return $res;
    }

    /**
     * @param int $startTime
     * @param int $endTime
     * @param array $otherWhere
     * @param string $field
     * @param string $platformSuffix
     * @return array
     */
    public function getUserInfoByRegTime($startTime = 0, $endTime = 0, $otherWhere = array(), $field = '*', $platformSuffix = '')
    {
        $res = [];
        if (empty($platformSuffix)) return $res;

        $model = CommonServer::getPlatformModel('KefuCommonMember',$platformSuffix);
        $sql = "select ";
        if ($field === '*' || empty($field)){
            $sql .= "*";
        }elseif (is_array($field)){
            $fieldStr = implode(',', $field);
            $sql .= "$fieldStr";
        }else{
            $sql .= "$field";
        }
        $sql .= " from kefu_common_member where 1 ";
        if (!empty($startTime)) $sql .= "and reg_date >= ".(int)$startTime." ";
        if (!empty($endTime)) $sql .= "and reg_date < ".(int)$endTime." ";

        foreach ($otherWhere as $key=>$value){
            $sql .= " and `".$key."` = '".$value."' ";
        }
        $res = $model->query($sql);
        $res = $res->toArray();
        return $res;

    }

    /**
     * @param string $platform
     * @param string $order
     * @param int $starTime
     * @param int $endTime
     * @return array
     */
    public static function getRegTime($suffix = '', $order = 'asc', $starTime = 0, $endTime = 0)
    {
        $result = [];
        if (!empty($suffix)) {
            $model = CommonServer::getPlatformModel('KefuCommonMember',$suffix);
        }
            $res = $model->field('reg_date')->where('reg_date','>',$starTime)->where('reg_date','<',$endTime)->order('reg_date '.$order)->find();
        if (!empty($res)) $result = $res->toArray();
        return $result;
    }


    /**
     * @param array $uids
     * @param string $suffix
     * @param string $field
     * @return array|ResultSet
     */
    public static function getFieldInfoByNameAndSuffix($user_name=[], $suffix='', $field='*')
    {

        $res = [];
        if (empty($user_name) || !is_array($user_name)) return $res;
        if (empty($suffix)){
            return $res;
        }
        $model = CommonServer::getPlatformModel('kefu_common_member',$suffix);
        $sql = "select ";
        if ($field === '*' || empty($field)){
            $sql .= "*";
        }elseif (is_array($field)){
            $fieldStr = implode(',', $field);
            $sql .= "$fieldStr";
        }else{
            $sql .= "$field";
        }
        $sql .= " from db_customer_{$suffix}.kefu_common_member where user_name ";

        if (is_array($user_name)){
            $inStr = implode("','",$user_name);
            $inStr = "'".$inStr."'";
            $sql .= "in({$inStr})";
        }else{
            //兼容传字符串
            $sql .= "in({$user_name})";
        }

        $res = $model->query($sql);

        return $res;
    }


    /**
     * @param string $new_mobile
     * @param array $inArr
     * @param int $type
     * @return false
     */
    public static function updateCommonMemberMobile($new_mobile = '', $inArr = [], $type = 1, $platformSuffix='')
    {
        if( empty($new_mobile) ) return false;
        $inStr = implode("','",$inArr);
        $inStr = "'".$inStr."'";
        $model = CommonServer::getPlatformModel('KefuCommonMember',$platformSuffix);

        $sql     = "update db_customer_{$platformSuffix}.kefu_common_member set mobile='{$new_mobile}' where ";
        $sql .= self::$updateStaticByWhereIn[$type]." in({$inStr})";

        $ret = $model->execute($sql);

        return $ret;
    }


    /**
     * @param array $uids
     * @param string $suffix
     * @param string $field
     * @return array|ResultSet
     */
    public static function getRegChannelByUid($uids=[], $platform=[],$field='*')
    {


        $res = [];
        if (empty($uids) || !is_array($uids)) return $res;

        $sql = "select ";
        if ($field === '*' || empty($field)){
            $sql .= "*";
        }elseif (is_array($field)){
            $fieldStr = implode(',', $field);
            $sql .= "$fieldStr";
        }else{
            $sql .= "$field";
        }
        $sql .= " from db_customer_{$platform['platform_suffix']}.kefu_common_member a  join db_statistic.platform_channel_info b on a.reg_channel=b.channel_id and b.platform_id = {$platform['platform_id']}  where uid ";

        if (is_array($uids)){
            $inStr = implode("','",$uids);
            $inStr = "'".$inStr."'";
            $sql .= "in({$inStr})";
        }else{
            //兼容传字符串
            $sql .= "in({$uids})";
        }
        $model = CommonServer::getPlatformModel('KefuCommonMember',$platform['platform_suffix']);
        $res = $model->query($sql);

        $res = !empty($res) ? $res : [];

        return $res;
    }

}