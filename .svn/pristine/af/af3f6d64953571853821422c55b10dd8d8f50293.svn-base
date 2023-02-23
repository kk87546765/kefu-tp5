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
class PlatformGameInfo extends BaseSqlServer
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

}