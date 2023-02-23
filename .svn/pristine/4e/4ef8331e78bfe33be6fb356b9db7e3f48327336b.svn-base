<?php

namespace common\model\db_statistic;


use common\base\BasicModel;

class VipUserInfo extends BasicModel
{
    protected $connection = 'database.db_statistic';
    protected $failException = true; //是否验证抛出异常
    protected $resultSetType = 'collection';
    protected $pk = 'id';

    const INSERT_NUMBER = 2000;

    /**
     * @param int $ascription_vip
     * @param int $platform_id
     * @param array $platformGameIds
     * @param array $serverIds
     * @return bool
     */
    public function findVipUserByGameAndAscription($ascription_vip = 0, $platform_id = 0, $platformGameIds = [], $serverIds = [])
    {
        $result = false;
        if (empty($ascription_vip) || empty($platform_id) || empty($platformGameIds) || empty($serverIds)) return $result;
        $lastPayGameIdStr = "'".implode("','", $platformGameIds)."'";
        $serverIdStr = "'".implode("','", $serverIds)."'";
        $sql = "SELECT COUNT(1) as cou FROM vip_user_info 
                WHERE platform_id = {$platform_id} and ascription_vip = {$ascription_vip} and last_pay_game_id in({$lastPayGameIdStr}) AND server_id in ({$serverIdStr})";
        $res = $this->query($sql);
        if ($res[0]['cou'] >0) $result = true;

        return $result;
    }

    /**
     * 已分配用户更新到新客服
     * @param int $ascription_vip
     * @param int $admin_user_id
     * @param int $platform_id
     * @param array $platformGameIds
     * @param array $serverIds
     * @return false|ResultSet
     */
    public function batchTransferAscription($ascription_vip = 0, $admin_user_id = 0, $platform_id = 0, $platformGameIds = [], $serverIds = [])
    {
        $result = false;
        if (empty($ascription_vip) || empty($admin_user_id) || empty($platform_id) || empty($platformGameIds) || empty($serverIds)) return $result;

        $tmpTime = time();
        $lastPayGameIdStr = "'".implode("','", $platformGameIds)."'";
        $serverIdStr = "'".implode("','", $serverIds)."'";
        $sql = "UPDATE vip_user_info SET ascription_vip = {$ascription_vip},last_distribute_time={$tmpTime},update_time={$tmpTime} 
                WHERE platform_id = {$platform_id} and ascription_vip = {$admin_user_id} and last_pay_game_id in({$lastPayGameIdStr}) AND server_id in ({$serverIdStr})";

        $result = $this->execute($sql);

        return $result;
    }

    /**
     * @param $data
     * @return ResultSet
     */
    public function insertOrUpdate($data)
    {
        $nowTime = time();
        $sql = "insert into vip_user_info (`platform_id`,`uid`,`user_name`,`mobile`,`game_id`,`server_id`,`server_name`,`last_pay_game_id`,`role_id`,`role_name`,`reg_ip`,`reg_time`,`day_hign_pay`,`single_hign_pay`,`total_pay`,`last_day_pay`,`thirty_day_pay`,`month_pay`,`p_l_g`,`p_l_g_s`,`last_pay_time`,`total_time`,`update_time`,`vip_time`,`add_time`,`last_login_time`) values";
        foreach( $data as $v ){
            $sql .= "('{$v['platform_id']}',".
                "'{$v['uid']}',".
                "'".addslashes($v['user_name'])."',".
                "'".addslashes($v['mobile'])."',".
                "'{$v['game_id']}',".
                "'{$v['server_id']}',".
                "'".addslashes($v['server_name'])."',".
                "'{$v['last_pay_game_id']}',".
                "'{$v['role_id']}',".
                "'".addslashes($v['role_name'])."',".
                "'{$v['reg_ip']}',".
                "'{$v['reg_time']}',".
                "{$v['day_hign_pay']},".
                "{$v['single_hign_pay']},".
                "{$v['total_pay']},".
                "{$v['last_day_pay']},".
                "{$v['thirty_day_pay']},".
                "{$v['month_pay']},".
                "'{$v['platform_id']}_{$v['last_pay_game_id']}',".
                "'{$v['platform_id']}_{$v['last_pay_game_id']}_{$v['server_id']}',".
                "{$v['last_pay_time']},".
                "{$v['total_time']},".
                "".strtotime($v['update_time']).",".
                "{$v['vip_time']},".
                "$nowTime,".
                "{$v['last_login_time']}),";
        }
        $sql = trim($sql,",");
        $sql .= " ON DUPLICATE KEY UPDATE 
                 server_id = VALUES(server_id),
                 server_name = VALUES(server_name),
                 last_pay_game_id = VALUES(last_pay_game_id),
                 role_id = VALUES(role_id),
                 role_name = VALUES(role_name),
                 day_hign_pay = VALUES(day_hign_pay),
                 single_hign_pay = VALUES(single_hign_pay),
                 total_pay = VALUES(total_pay),
                 last_day_pay = VALUES(last_day_pay),
                 thirty_day_pay = VALUES(thirty_day_pay),
                 month_pay = VALUES(month_pay),
                 p_l_g = VALUES(p_l_g),
                 p_l_g_s = VALUES(p_l_g_s),
                 last_pay_time = VALUES(last_pay_time),
                 total_time = VALUES(total_time),
                 update_time = VALUES(update_time),
                 vip_time = VALUES(vip_time),
                 last_login_time = VALUES(last_login_time)";
        return $this->execute($sql);
    }

    /**
     * @param int $ascription_vip
     * @param array $infoArr
     * @return false|ResultSet
     */
    public function userDistributeVipAscription($ascription_vip = 0,$infoArr = [])
    {
        $result = false;
        if (empty($infoArr) || empty($infoArr['p_g_s'])) return $result;
        $t = time();
        $p_g_s = $infoArr['p_g_s'];
        if (!empty($p_g_s)) {
            for ($i=0;count($p_g_s) >$i*self::INSERT_NUMBER;$i++)
            {
                $start = empty($i*self::INSERT_NUMBER) ? 0 : $i*self::INSERT_NUMBER;
                $tmpArr = array_slice($p_g_s, $start, self::INSERT_NUMBER);
                $str = "'".implode("','",$tmpArr)."'";

                $sql = "UPDATE vip_user_info SET ascription_vip = $ascription_vip,first_distribute_time=$t,last_distribute_time=$t,update_time=$t WHERE first_distribute_time = 0 AND (total_pay >= {$infoArr['total_pay']} OR day_hign_pay >= {$infoArr['day_pay']} OR thirty_day_pay >= {$infoArr['thirty_day_pay']}) AND p_l_g_s in($str)";
                $result = $this->execute($sql);
            }
        }
        return $result;

    }
}
