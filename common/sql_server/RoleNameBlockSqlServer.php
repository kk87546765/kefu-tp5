<?php
/**
 * Created by PhpStorm.
 * User: crosstime
 * Date: 2018/8/14
 * Time: 下午5:31
 */
namespace common\sql_server;


use common\model\gr_chat\RoleNameBlock;

class RoleNameBlockSqlServer extends BaseSqlServer
{


    /**
     * @param array $info
     * @param string $type
     * @return mixed
     */
    public static function insertBlock($info=[]){
        $sql = "insert into gr_role_name_block(`gkey`,`sid`,`uid`,`uname`,`reg_gid`,`reg_channel_id`,`roleid`,`rolename`,`role_level`,`count_money`,`tkey`,`addtime`,`expect_unblock_time`,`op_admin_id`,`status`,`type`,`check_type`,`op_ip`,`blocktime`,`ext`,`reason`,`ban_type`,`openid`,`hit_keyword_id`,`keyword`) values";
        foreach( $info as $v ){
            $sql .= "('{$v['gkey']}',".
                    "'{$v['server_id']}',".
                    "{$v['uid']},".
                    "'{$v['uname']}',".
                    "{$v['reg_gid']},".
                    "{$v['reg_channel_id']},".
                    "'{$v['role_id']}',".
                    "'{$v['role_name']}',".
                    "{$v['role_level']},".
                    "{$v['count_money']},".
                    "'{$v['tkey']}',".
                    "{$v['addtime']},".
                    "{$v['expect_unblock_time']},".
                    "'{$v['op_admin_id']}',".
                    "1,".
                    "'{$v['type']}',".
                    "{$v['check_type']},".
                    "'{$v['op_ip']}',".
                    "'{$v['blocktime']}',".
                    "'{$v['ext']}',".
                    "'{$v['reason']}',".
                    "{$v['ban_type']},".
                    "'{$v['openid']}',".
                    "{$v['hit_keyword_id']},";
            if (isset($v['keyword'])) {

                $sql .= "'{$v['keyword']}'),";
            }else{
                $sql .= "''),";
            }
        }


        $sql = trim($sql,",");


        $model = new RoleNameBlock();
        $ret = $model->execute($sql);
        return $ret;
    }

    /**
     * @param array $ids
     * @return array
     */
    public static function getBlockById($ids=[]){
        if( empty($ids) ) return [];
        $ret = [];
        $ids = is_array($ids) ? implode(',',$ids) : $ids;
        $model = new RoleNameBlock();

        $blocked = $model->where("id in ({$ids})")->select();
        $blocked = isset($blocked) ? $blocked->toArray() : [];

        foreach( $blocked as $v ){

            $ret[$v["id"]] = $v;
        }
        return $ret;
    }

    /**
     * @param array $uids
     * @return array
     */
    public static function getBlockByUid($uids=[],$role_id=[]){

        if( empty($uids) ) return [];
        $uids = implode(',',$uids);
        $role_id = implode(',',$role_id);
        $model = new RoleNameBlock();
        $blocked =  $model->where("uid in ({$uids}) and roleid in ({$role_id}) and status=1")->select();

        $ret = [];
        foreach( $blocked as $v ){
            if($v['type'] == 'USER' || $v['type'] == 'AUTO' || $v['type'] == 'ACTION'){
                $ret[$v['uid']] = 1;
            }
            elseif($v['type'] == 'CHAT' || $v['type'] == 'AUTOCHAT' || $v['type'] == 'ACTIONCHAT'){
                $ret[$v['uid']] = 2;
            }else{
                $ret[$v['uid']] = 0;
            }

        }
        return $ret;
    }

    /**
     * @param array $uids
     * @return array
     */
    public static function getAllBlockByUid($uids=[]){


        if( empty($uids) ) return [];

        $model = new RoleNameBlock();

        $blocked =  $model->where("uid in ({$uids})")->select();
        $ret = [];
        foreach( $blocked->toArray() as $v ){
            $ret[$v['uid']] = $v;
        }
        return $ret;
    }

    /**
     * @param array $unames
     * @return array
     */
    public static function getBlockByUname($unames=[]){
        if( empty($unames) ) return [];

        $model = new RoleNameBlock();

        $blocked =  $model->where("uname in ({$unames}) and type='USER'")->select();
        $ret = [];
        foreach( $blocked as $v ){
            $ret[$v['uname']] = 1;
        }
        return $ret;
    }

    /**
     * @param array $ips
     * @return array
     */
    public static function getBlockByIps($ips=[]){
        if( empty($ips) ) return [];

        $model = new RoleNameBlock();

        $blocked =  $model->where("ip in ({ips:array}) and type='IP'")->select();
        $ret = [];
        foreach( $blocked as $v ){
            $ret[$v['ip']] = 1;
        }
        return $ret;
    }

    /**
     * @param array $imeis
     * @return array
     */
    public static function getBlockByImeis($imeis=[]){
        if( empty($imeis) ) return [];

        $model = new RoleNameBlock();

        $blocked =  $model->where("imei in ({imeis:array}) and type='IMEI'")->select();
        $ret = [];
        foreach( $blocked as $v ){
            $ret[$v['imei']] = 1;
        }
        return $ret;
    }

    /**
     * @param array $uid
     * @param array $roleid
     * @param array $sid
     * @return array
     */
    public static function getBlockByChat($uid=[],$roleid=[],$sid=[]){

        $model = new RoleNameBlock();

        $blocked =  $model->where("uid in ({$uid}) and roleid in ({$roleid}) and sid in ({$sid}) and type='CHAT'")->select();
        $ret = [];
        foreach( $blocked as $v ){
            $t_key  =  $v['uid'].$v['roleid'].$v['sid'];
            $ret[ $t_key ] = 1;
        }
        return $ret;
    }

    /**
     * @param array $uid
     * @param array $roleid
     * @param array $sid
     * @return array
     */
    public static function getBlockByUserInfo($uid=[],$roleid=[],$sid=[]){

        $model = new RoleNameBlock();
        $roleid = is_array($roleid) ? implode(',',$roleid) : $roleid;
        $uid = is_array($uid) ? implode(',',$uid) : $uid;
        $sid = is_array($sid) ? implode(',',$sid) : $sid;

        $time = mktime(0,0,0,date('m')-1,date('d'),date('Y'));
        $blocked =  $model->where("uid in ({$uid}) and roleid in ('{$roleid}') and sid in ('{$sid}') and status=1 and (type='USER' or type='AUTO') and addtime > {$time}")->select();

        $blocked = isset($blocked) ? $blocked->toArray() : [];
        $ret = [];
        foreach( $blocked as $v ){
            $t_key  =  $v['uid'].$v['roleid'].$v['sid'];
            $ret[ $t_key ]['keyword'] = $v['keyword'];
        }
        return $ret;
    }


    /**
     * @param array $blockid
     * @return false
     */
    public static function deleteBlock($blockid=[]){
        if( empty($blockid) ) return false;
        $blockid = implode(",",$blockid);
        $sql   = "delete from gr_block where id in({$blockid})";
        $model = new RoleNameBlock();
        $ret   = $model->execute($sql);
        return $ret;
    }

    /**
     * @param string $where
     * @return mixed
     */
    public static function deleteBlockByWhere($where=''){
        $sql   = "delete from gr_block where 1 and $where";
        $model = new RoleNameBlock();
        $ret   = $model->execute($sql);
        return $ret;
    }



    /**
     *
     * @param int $uid
     * @param string $gkey
     * @return mixed
     */
    public static function getAllChatBlockByUidAndGkey($uid,$gkey){

        $model = new RoleNameBlock();

        $blocked =  $model->where("uid= {$uid} and gkey={$gkey} and status = 1 ")->select();

        $info = !empty($blocked) ?  $blocked : [];

        return $info;

    }


    public static function getList($where='',$page=1,$limit=20,$order='id desc')
    {
        $offset = ($page-1)*$limit;
        $model = new RoleNameBlock();
        $blocks = $model->where($where)->limit($offset.','.$limit)->order($order)->select();

        $blocks = isset($blocks) ? $blocks->toArray() : [];

        return $blocks;
    }

    public static function getCount($where = '')
    {
        $model = new RoleNameBlock();

        $count = $model->where($where)->count();

        $count = isset($count) ? $count : 0;
        return $count;

    }

    /**
     * @param array $blockid
     * @return false
     */
    public static function updateRoleNameBlockStatus($blockid = [],$unblock_admin='auto'){
        if( empty($blockid) ) return false;
        $blockid = is_array($blockid) ? implode(",",$blockid) : $blockid;

        $unblock_time = time();
        $sql   = "update gr_role_name_block set status=0,unblock_time={$unblock_time},unblock_admin='{$unblock_admin}' where id in({$blockid})";
        $model = new RoleNameBlock();
        $ret   = $model->execute($sql);
        return $ret;
    }


}