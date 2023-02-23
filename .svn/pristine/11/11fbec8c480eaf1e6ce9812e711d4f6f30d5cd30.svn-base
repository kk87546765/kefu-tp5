<?php

namespace common\model\db_statistic;


use common\base\BasicModel;

class BecomeVipStandard extends BasicModel
{
    protected $connection = 'database.db_statistic';
    protected $failException = true; //是否验证抛出异常
    protected $resultSetType = 'collection';
    protected $pk = 'id';
    protected $autoWriteTimestamp = true;
    protected $createTime = 'add_time';
    protected $updateTime = 'edit_time';

    /**
     * @return array
     */
    public function getConfigInfo($where = ['A.static'=>1,'A.game_id'=>0])
    {
        $result = [];
//       $sql = "select A.day_pay,A.thirty_day_pay,A.total_pay,B.platform_id,B.platform_game_list,B.id FROM become_vip_standard as A INNER JOIN vip_game_product as B ON A.vip_game_product_id = B.id WHERE A.static = 1"; //旧的注释掉
        $sql = "select A.day_pay,A.thirty_day_pay,A.total_pay,A.game_id,B.platform_id,B.product_id,B.id FROM become_vip_standard as A INNER JOIN game_product as B ON A.vip_game_product_id = B.id ".setWhereSql($where);
        return $this->query($sql);
    }
}
