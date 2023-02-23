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


    /**
     * @param int $fieldValue
     * @param array $inArr
     * @param int $type
     * @return false
     */
    public static function updateCommonMemberStatus($fieldValue = 1, $inArr = [], $type = 1, $platformSuffix='')
    {
        if( empty($inArr) ) return false;

        $model = CommonServer::getPlatformModel('KefuCommonMember',$platformSuffix);
        $inStr = implode("','",$inArr);
        $inStr = "'".$inStr."'";
        $sql     = "update db_customer_{$platformSuffix}.kefu_common_member set status=$fieldValue where ";
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
    public static function getFieldInfoByUidAndSuffix($uids=[], $suffix='', $field='*')
    {


        $res = [];
        if (empty($uids) || !is_array($uids)) return $res;

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