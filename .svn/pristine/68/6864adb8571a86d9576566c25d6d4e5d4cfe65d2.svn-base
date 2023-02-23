<?php
/**
 * Created by PhpStorm.
 * User: crosstime
 * Date: 2018/8/14
 * Time: 下午5:31
 */
namespace common\sql_server;
use common\model\gr_chat\ActionBlock;
use common\sql_server\BaseSqlServer;

class ActionBlockSqlServer extends BaseSqlServer
{
    public $id;
    public $gkey;
    public $sid;
    public $uid;
    public $uanme;
    public $roleid;
    public $ip;
    public $tkey;
    public $addtime;
    public $op_admin_id;
    public $op_ip;
    public $status;
    public $type;

    const STATUS_BLOCK = 1;
    const STATUS_NORMAL = 2;


    const TYPE_IP = 'IP';
    const TYPE_USER = 'USER';
    const TYPE_CHAT = 'CHAT';
    const TYPE_IMEI = "IMEI";



    public static function add($data){
        $model = new ActionBlock();

        $res = $model->insert($data);
        return $res;
    }

    public function beforeValidationOnCreate()
    {
        $this->addtime = time();

        if (empty($this->status)) {
            $this->status = self::STATUS_BLOCK;
        }
        if (empty($this->type)) {
            $this->type = self::TYPE_CHAT;
        }

        if (empty($this->op_admin_id)) {
            $this->op_admin_id = $this->getDI()->get('session')->get('username');
        }

        if (empty($this->op_ip)) {
            $this->op_ip = $this->getDI()->get('request')->getClientAddress();
        }
    }

    /**
     * @param array $info
     * @param string $type
     * @return mixed
     */
    public static function insertBlock($info=[],$type="USER"){
        $sql = "insert into gr_block(`gkey`,`sid`,`uid`,`uname`,`roleid`,`rolename`,`role_level`,`count_money`,`ip`,`tkey`,`addtime`,`op_admin_id`,`status`,`type`,`op_ip`,`imei`,`blocktime`,`ext`,`keyword`) values";
        foreach( $info as $v ){
            $sql .= "('{$v['gkey']}',".
                    "'{$v['sid']}',".
                    "'{$v['uid']}',".
                    "'{$v['uname']}',".
                    "'{$v['roleid']}',".
                    "'{$v['rolename']}',".
                    "{$v['role_level']},".
                    "{$v['count_money']},".
                    "'{$v['ip']}',".
                    "'{$v['tkey']}',".
                    "{$v['addtime']},".
                    "'{$v['op_admin_id']}',".
                    "1,".
                    "'{$type}',".
                    "'{$v['op_ip']}',".
                    "'{$v['imei']}',".
                    "'{$v['blocktime']}',".
                    "'{$v['ext']}',";
            if (isset($v['keyword'])) {
                $sql .= "'{$v['keyword']}'),";
            }else{
                $sql .= "''),";
            }
        }
        $sql = trim($sql,",");


        $model = new static();
        $ret = $model->getWriteConnection()->execute($sql);
        return $ret;
    }

    /**
     * @param array $ids
     * @return array
     */
    public static function getBlockById($ids=[]){
        if( empty($ids) ) return [];
        $blocked =  self::find(['id in ({id:array})', 'bind' => ['id' => $ids]]);
        $ret = [];
        foreach( $blocked->toArray() as $v ){
            $ret[$v["id"]] = $v;
        }
        return $ret;
    }

    /**
     * @param array $uids
     * @return array
     */
    public static function getBlockByUid($uids=[]){
        if( empty($uids) ) return [];
        $blocked =  self::find(['uid in ({uid:array}) and type="USER"', 'bind' => ['uid' => $uids]]);
        $ret = [];
        foreach( $blocked->toArray() as $v ){
            $ret[$v['uid']] = 1;
        }
        return $ret;
    }

    /**
     * @param array $unames
     * @return array
     */
    public static function getBlockByUname($unames=[]){
        if( empty($unames) ) return [];
        $blocked =  self::find(['uname in ({uname:array}) and type="USER"', 'bind' => ['uname' => $unames]]);
        $ret = [];
        foreach( $blocked->toArray() as $v ){
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
        $blocked =  self::find(['ip in ({ips:array}) and type="IP"', 'bind' => ['ips' => $ips]]);
        $ret = [];
        foreach( $blocked->toArray() as $v ){
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
        $blocked =  self::find(['imei in ({imeis:array}) and type="IMEI"', 'bind' => ['imeis' => $imeis]]);
        $ret = [];
        foreach( $blocked->toArray() as $v ){
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
        $blocked =  self::find(['uid in ({uid:array}) and roleid in ({roleid:array}) and sid in ({sid:array}) and type="CHAT"', 'bind' => [ 'uid' => $uid,'roleid'=>$roleid,'sid'=>$sid ]]);
        $ret = [];
        foreach( $blocked->toArray() as $v ){
            $t_key  =  $v['uid'].$v['roleid'].$v['sid'];
            $ret[ $t_key ] = 1;
        }
        return $ret;
    }

    /**
     * @param array $blockid
     * @return false
     */
    public static function updateBlockStatus($blockid=[]){
        if( empty($blockid) ) return false;
        $blockid = implode(",",$blockid);
        $sql     = "update gr_block set status=0 where id in({$blockid})";
        $model = new ActionBlock();
        $ret = $model->execute($sql);
        return $ret;
    }

    /**
     * @param array $blockid
     * @return false
     */
    public static function UpdateActionBlockStatus($status){
        $sql     = "update gr_action_block set status={$status} ";
        $model = new ActionBlock();
        $ret = $model->execute($sql);

        return $ret;
    }


    /**
     * @param array $blockid
     * @return false
     */
    public static function deleteBlock($blockid=[]){
        if( empty($blockid) ) return false;
        $blockid = is_array($blockid) ? implode(",",$blockid) : $blockid;
        $sql     = "delete from gr_action_block where id in({$blockid})";
        $model = new ActionBlock();

        $ret = $model->execute($sql);
        return $ret;
    }

    /**
     * @param string $where
     * @return mixed
     */
    public static function deleteByWhere($where=''){
        $sql   = "delete from gr_action_block where 1 and {$where}";
        $model = new ActionBlock();
        $ret = $model->execute($sql);
        return $ret;
    }


    public static function getList($data)
    {
        $data['limit'] = isset($data['limit']) ? $data['limit'] : 0;
        $data['offset'] = isset($data['offset']) ? $data['offset'] : 10000;
        $model = new ActionBlock();
        $res = $model->where($data['conditions'])->order(isset($data['order']) ? $data['order'] : '')->limit($data['limit'].','.$data['offset'])->select();
        $res = isset($res) ? $res->toArray() : [];
        return $res;
    }

    public static function getCount()
    {
        $model = new ActionBlock();
        $res = $model->count();
        $res = isset($res) ? $res : 0;
        return $res;
    }

    public static function getOne($data)
    {
        $model = new ActionBlock();
        $res = $model->where($data)->find();
        $res = isset($res) ? $res->toArray() : [];
        return $res;
    }

    public static function update($data)
    {
        $model = new ActionBlock();
        $res = $model->update($data);
        if($res !== false){
            return true;
        }
        return false;

    }

}