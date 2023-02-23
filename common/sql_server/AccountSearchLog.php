<?php
/**
 * Created by PhpStorm.
 * User: crosstime
 * Date: 2018/8/14
 * Time: 下午5:31
 */
namespace common\sql_server;

use common\sql_server\BaseSqlServer;
use common\model\db_customer\KefuCommonMember;
use common\model\db_statistic\AccountSearchLog as AccountSearchLogModel;
class AccountSearchLog extends BaseSqlServer
{



    public static function addChangeLog($data){


        $sql = "insert into account_search_log(`uid`,`platform_id`,`type`,`admin_id`,`admin_name`,`change_content`,`remarks`,`add_time`) values";

        foreach( $data as $v ){
            $sql .= "({$v['uid']},".
                "{$v['platform_id']},".
                "{$v['type']},".
                "{$v['admin_id']},".
                "'{$v['admin_name']}',".
                "'{$v['change_content']}',".
                "'{$v['remarks']}',".
                "'{$v['add_time']}'),";
        }

        $sql = trim($sql,",");
        $model = new AccountSearchLogModel();
        $res = $model->execute($sql);
        return $res;
    }


    public static function getKefuLogByUid($uid,$platform_id){
        $result = [];
        $model = new AccountSearchLogModel();

        $res = $model->where("platform_id = {$platform_id} and uid = {$uid}")->order('id desc')->select();

        if (!empty($res)) $result = $res->toArray();

        return $result;
    }

    public static function getChangeLog($uid,$platform_id,$start_time,$end_time,$type){
        $result = [];
        $model = new AccountSearchLogModel();
        $res = $model->where("platform_id = {$platform_id} and uid = {$uid} and add_time>= {$start_time} and add_time<={$end_time} and type={$type}")->order( 'add_time desc')->select();

        if (!empty($res)) $result = $res->toArray();

        return $result;

}


}