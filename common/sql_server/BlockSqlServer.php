<?php
/**
 * Created by PhpStorm.
 * User: crosstime
 * Date: 2018/8/14
 * Time: 下午5:31
 */
namespace common\sql_server;

use common\model\gr_chat\Block;

class BlockSqlServer extends BaseSqlServer
{

//    public function beforeValidationOnCreate()
//    {
//        $this->addtime = time();
//
//        if (empty($this->status)) {
//            $this->status = BlockSqlServer::STATUS_BLOCK;
//        }
//        if (empty($this->type)) {
//            $this->type = BlockSqlServer::TYPE_CHAT;
//        }
//
//        if (empty($this->op_admin_id)) {
//            $this->op_admin_id = $this->getDI()->get('session')->get('username');
//        }
//
//        if (empty($this->op_ip)) {
//            $this->op_ip = $this->getDI()->get('request')->getClientAddress();
//        }
//    }

    /**
     * @param array $info
     * @param string $type
     * @return mixed
     */
    public static function insertBlock($info=[],$type="USER"){
        $sql = "insert into gr_block(`gkey`,`sid`,`uid`,`platform_uid`,`reg_channel_id`,`uname`,`roleid`,`rolename`,`role_level`,`count_money`,`ip`,`tkey`,`platform_tkey`,`addtime`,`expect_unblock_time`,`op_admin_id`,`status`,`type`,`op_ip`,`imei`,`blocktime`,`ext`,`reason`,`ban_type`,`openid`,`keyword`) values";
        foreach( $info as $v ){
            $sql .= "('{$v['gkey']}',".
                    "'{$v['sid']}',".
                    "{$v['uid']},".
                    "{$v['platform_uid']},".
                    "{$v['reg_channel_id']},".
                    "'{$v['uname']}',".
                    "'{$v['roleid']}',".
                    "'{$v['rolename']}',".
                    "{$v['role_level']},".
                    "{$v['count_money']},".
                    "'{$v['ip']}',".
                    "'{$v['tkey']}',".
                    "'{$v['platform_tkey']}',".
                    "{$v['addtime']},".
                    "{$v['expect_unblock_time']},".
                    "'{$v['op_admin_id']}',".
                    "1,".
                    "'{$type}',".
                    "'{$v['op_ip']}',".
                    "'{$v['imei']}',".
                    "'{$v['blocktime']}',".
                    "'{$v['ext']}',".
                    "'{$v['reason']}',".
                    "{$v['ban_type']},".
                    "'{$v['openid']}',";
            if (isset($v['keyword'])) {
                $sql .= "'{$v['keyword']}'),";
            }else{
                $sql .= "''),";
            }
        }


        $sql = trim($sql,",");


        $model = new Block();
        $ret = $model->execute($sql);
        return $ret;
    }

    /**
     * @param array $ids
     * @return array
     */
    public static function getBlockById($ids=[]){
        if( empty($ids) ) return [];

        $model = new Block();

        $blocked = $model->where("id in ({$ids})")->select();
        $ret = [];
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
        $model = new Block();
        $blocked =  $model->where("uid in ({$uids}) and roleid in ({$role_id}) and status=1")->select();

        $ret = [];
        foreach( $blocked as $v ){
            if($v['type'] == 'CHAT' || $v['type'] == 'AUTOCHAT' || $v['ACTIONCHAT']){
                $ret[$v['uid']] = 2;
            }elseif($v['type'] == 'USER' || $v['type'] == 'AUTO' || $v['type'] == 'ACTION'){
                $ret[$v['uid']] = 1;
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

        $model = new Block();

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

        $model = new Block();

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

        $model = new Block();

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

        $model = new Block();

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

        $model = new Block();

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

        $model = new Block();

        $blocked =  $model->where("uid in ({$uid}) and roleid in ({$roleid}) and sid in ({$sid}) and status=1 and type='USER'")->select();
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
    public static function updateBlockStatus($blockid=[],$unblock_admin='auto'){
        if( empty($blockid) ) return false;
        $blockid = implode(",",$blockid);

        $unblock_time = time();
        $sql   = "update gr_block set status=0,unblock_time={$unblock_time},unblock_admin='{$unblock_admin}' where id in({$blockid})";
        $model = new Block();
        $ret   = $model->execute($sql);
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
        $model = new Block();
        $ret   = $model->execute($sql);
        return $ret;
    }

    /**
     * @param string $where
     * @return mixed
     */
    public static function deleteBlockByWhere($where=''){
        $sql   = "delete from gr_block where 1 and $where";
        $model = new Block();
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

        $model = new Block();

        $blocked =  $model->where("uid= {$uid} and gkey={$gkey} and status = 1 ")->select();

        $info = !empty($blocked) ?  $blocked : [];

        return $info;

    }


    public static function getList($where='',$page=1,$limit=20,$order='id desc')
    {
        $offset = ($page-1)*$limit;
        $model = new Block();
        $blocks = $model->where($where)->limit($offset.','.$limit)->order($order)->select();

        $blocks = isset($blocks) ? $blocks->toArray() : [];
        return $blocks;
    }

    public static function getCount($where = '')
    {
        $model = new Block();

        $count = $model->where($where)->count();

        $count = isset($count) ? $count : 0;
        return $count;

    }


}