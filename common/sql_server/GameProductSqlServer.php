<?php
/**
 * @author ambi
 * @date 2019/4/17
 */

namespace common\sql_server;

use common\model\db_statistic\GameProduct;
class GameProductSqlServer extends BaseSqlServer
{
    public static function getOne($product_id,$platform_id)
    {
        $model = new GameProduct();
        $res = $model->where("product_id = {$product_id} and platform_id = {$platform_id}")->find();
        $res = isset($res) ? $res->toArray() : [];
        return $res;
    }


    /**
     * @param $data
     * @return mixed
     */
    public function add($data)
    {
        $model = new GameProduct();
        $res = $model->insert($data);

        return $res;
    }

    /**
     * @param $id
     * @return mixed
     */
    public function del($id)
    {
        $model = new GameProduct();
        $res = $model->where("id = {$id}")->delete();

        return $res;
    }

    /**
     * @param $data
     * @return mixed
     */
    public function edit($data)
    {
        $model = new GameProduct();
        $res = $model->isUpdate(true)->save($data,['id'=>$data['id']]);
        return $res;
    }

    /**
     * @param array $data
     * @return array
     */
    public static function getGameProductList($data=array())
    {
        $model = new GameProduct();
        $where = '1 ';
        $result = [];
        foreach ($data as $k=>$v) {
            if (is_array($v) && !empty($v)) {
                $where .= "and $k in ({" . $v . ":array}) ";
            } elseif (is_array($v) && empty($v)) {
                continue;
            } else {
                if (is_string($v)) {
                    $where .= "and $k = '{$v}' ";
                } else {
                    $where .= "and $k = {$v} ";
                }


            }
        }

        $res = $model->where($where)->find();

        if (!empty($res)) $result = $res->toArray();
        return $result;
    }

    /**
     * @param $data
     * @return false|ResultSet
     */
    public function insertGameProductInfo($data)
    {
        if (empty($data)) return false;
        $tmpTime = time();

        $sql = "insert into game_product (`platform_id`,`product_id`,`product_name`,`add_admin_id`,`add_time`,`edit_admin_id`,`edit_time`,`static`) values";
        foreach( $data as $v ){
            $sql .= "({$v['platform_id']},".
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
                 product_name = VALUES(product_name),
                 static = VALUES(static),
                 edit_time = VALUES(add_time)";
        $model = new GameProduct();
        $res =$model->execute($sql);
        return $res;
    }

    /**
     * @param int $id
     * @return array
     */
    public static function getPlatformGameListById($id = 0)
    {
        $result = [];
        $sql = "SELECT B.platform_id,B.product_id,B.game_id FROM `game_product` as A INNER JOIN `platform_game_info` as B ON A.platform_id = B.platform_id AND A.product_id = B.product_id ";
        if (!empty((int)$id)) {
            $sql .= " WHERE A.id = $id";
        }
        $model = new GameProduct();
        $res = $model->query($sql);
        return $res;
    }



}