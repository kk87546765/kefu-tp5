<?php

namespace common\model\db_statistic;


use common\base\BasicModel;

class AdminUserGameServer extends BasicModel
{
    protected $connection = 'database.db_statistic';
    protected $failException = true; //是否验证抛出异常
    protected $resultSetType = 'collection';
    protected $pk = 'id';

    public function updateAdminIdByAscId($admin_user_id = 0, $ascription_game_server_id = 0)
    {
        $result = false;
        if (empty($admin_user_id) || empty($ascription_game_server_id)) return $result;
        $sql = "UPDATE admin_user_game_server SET admin_user_id = {$admin_user_id} WHERE ascription_game_server_id = {$ascription_game_server_id}";
        $result = $this->execute($sql);
        return $result;
    }
}
