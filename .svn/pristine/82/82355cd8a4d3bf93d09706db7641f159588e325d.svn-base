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
use common\model\db_statistic\PlatformGameInfo as PlatformGameInfoModel;
class PlatformGameInfoSqlServer extends BaseSqlServer
{
    public static function getGameList($where,$order,$field = '*')
    {
        $model = new PlatformGameInfoModel();
        $res = $model->field($field)->where($where)->order($order)->select();
        $res = isset($res) ? $res->toArray() : [];

        return $res;
    }

    /**
     * @param array $data
     * @return array
     */
    public static function getPlatformGameInfo($data=array())
    {
        $conditions = '1 ';
        $bind = $result = [];
        foreach ($data as $k=>$v) {
            if (is_array($v) && !empty($v)) {
                $conditions .= "and $k in ({".$k.":array}) ";
                $bind[$k] = $v;
            }elseif (is_array($v) && empty($v)) {
                continue;
            }else {
                $conditions .= "and $k = :$k: ";
                $bind[$k] = $v;
            }
        }

        if (empty($bind)) {
            $res =PlatformGameInfoModel::all();
        }else {
            $res = PlatformGameInfoModel::all([
                'conditions' =>$conditions,
                'bind' => $bind,
            ]);
        }

        if (!empty($res)) $result = $res->toArray();
        return $result;
    }



    /**
     * @param $data
     * @return false|ResultSet
     */
    public function insertPlatformGameInfo($data)
    {
        if (empty($data)) return false;
        $tmpTime = time();
        $model = new PlatformGameInfoModel();
        $sql = "insert into platform_game_info (`platform_id`,`game_id`,`game_name`,`product_id`,`product_name`,`add_admin_id`,`add_time`,`edit_admin_id`,`edit_time`,`static`) values";
        foreach( $data as $v ){
            $sql .= "({$v['platform_id']},".
                "'{$v['game_id']}',".
                "'{$v['game_name']}',".
                "'{$v['product_id']}',".
                "'{$v['product_name']}',".
                "0,".
                "{$tmpTime},".
                "0,".
                "0,".
                "{$v['static']}),";
        }
        $sql = trim($sql,",");
        $sql .= " ON DUPLICATE KEY UPDATE 
                 game_name = VALUES(game_name),
                 product_id = VALUES(product_id),
                 product_name = VALUES(product_name),
                 static = VALUES(static),
                 edit_time = VALUES(add_time)";
        $res = $model->execute($sql);
        return $res;
    }

    /**
     * @param array $game_product_ids
     * @return array
     */
    public static function getPlatformGameList($game_product_ids = array())
    {
        $model = new PlatformGameInfoModel();
        $result = [];
        if (empty($game_product_ids)) return $result;
        $inStr = implode("','",$game_product_ids);
        $inStr = "'".$inStr."'";
        $sql = "SELECT CONCAT(A.platform_id,'_',A.game_id) as pg,B.id FROM `platform_game_info` AS A INNER JOIN game_product AS B ON A.platform_id = B.platform_id AND A.product_id = B.product_id WHERE B.id in($inStr)";

        $res = $model->execute($sql);
        if (!empty($res)) $result = $res->toArray();
        return $result;
    }

    /**
     * @param array $concat_p_p
     * @return array
     */
    public static function getPlatformGameListByConcatPlatformProduct($concat_p_p = array())
    {
        $model = new PlatformGameInfoModel();

        $result = [];
        if (empty($concat_p_p)) return $result;
        $inStr = implode("','",$concat_p_p);
        $inStr = "'".$inStr."'";
        $sql = "SELECT * FROM `platform_game_info` WHERE CONCAT(platform_id,'_',product_id) in($inStr)";

        $res = $model->execute($sql);
        if (!empty($res)) $result = $res->toArray();
        return $result;
    }

}