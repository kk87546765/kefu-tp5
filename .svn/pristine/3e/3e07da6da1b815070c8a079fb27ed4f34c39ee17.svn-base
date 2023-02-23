<?php
/**
 * Created by PhpStorm.
 * User: crosstime
 * Date: 2018/8/14
 * Time: 下午5:31
 */
namespace Quan\Common\Models;
use \Quan\System\Mvc\Model;
use Phalcon\Mvc\Model\Resultset\Simple as ResultSet;
use Quan\System\Mvc\ModelLxx;

class KefuUserRole extends Model
{
    use ModelLxx;

    /**
     * @param $platform
     */
    public function setPlatform($platform){
        $platform = empty($platform) ?'' : '_'.$platform;

        $this->setReadConnectionService('db_db_customer'.$platform);
        $this->setWriteConnectionService('db_db_customer'.$platform);
    }

    public static function getRoleInfo($platform,$server_id,$role_id){

        if(empty($platform) || empty($server_id) || empty($role_id)){
            return false;
        }

        $sql = "select * from db_customer_{$platform}.kefu_user_role where role_id='{$role_id}' and server_id = '{$server_id}'";

        $res = self::excuteSQL($sql);
        $res = isset($res) ? $res->toArray() : '';
        return $res;
    }




}