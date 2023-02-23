<?php

namespace common\model\db_statistic;


use common\base\BasicModel;

class KefuUserRecharge extends BasicModel
{
    protected $connection = 'database.db_statistic';
    protected $failException = true; //是否验证抛出异常
    protected $resultSetType = 'collection';
    protected $pk = 'id';

    const is_vip_total_pay = 500;//20221005 1000修改成500
    const is_vip_day_hign_pay = 500;

    /**
     * @param $data
     * @return mixed
     */
    public function insertIgnoreInfo($data)
    {
        $sql = "insert ignore into kefu_user_recharge(`platform_id`,`uid`,`user_name`,`mobile`,`game_id`,`reg_ip`,`last_pay_game_id`,`reg_time`,`add_time`) values";
        foreach( $data as $v ){
            $sql .= "('{$v['platform_id']}',".
                "'{$v['uid']}',".
                "'{$v['user_name']}',".
                "'{$v['mobile']}',".
                "'{$v['game_id']}',".
                "'{$v['reg_ip']}',".
                "'{$v['game_id']}',".
                "{$v['reg_time']},".
                "{$v['add_time']}),";
        }
        $sql = trim($sql,",");
        $res = $this->execute($sql);
        return $res;
    }

    /**
     * @param $data
     * @return array|false
     */
    public function rechargeUserIfExist($data)
    {
        $result = false;
        if (empty($data) || !is_array($data)) return $result;
        $inStr = implode("','", $data);
        $inStr = "'".$inStr."'";
        $sql = "SELECT CONCAT(platform_id,'_',uid) as pustr
                FROM kefu_user_recharge 
                WHERE CONCAT(platform_id,'_',uid) in($inStr)";
        $res = $this->query($sql);
        if (!empty($res)) {
            $result = array_column($res, 'pustr');
            return $result;
        }

        return $result;
    }

    /**
     * @param $data
     * @return ResultSet
     */
    public function updateUserRechargeLastLoginTime($data)
    {
        $nowTime = time();
        $sql = "insert into kefu_user_recharge(`platform_id`,`uid`,`last_login_time`,`total_time`) values";
        foreach ($data as $v) {
            $sql .= "('{$v['platform_id']}'," .
                "'{$v['uid']}'," .
                "'{$v['last_login_time']}'," .
                "$nowTime),";
        }
        $sql = trim($sql,",");
        $sql .= " ON DUPLICATE KEY UPDATE 
                  last_login_time = CASE 
                    WHEN VALUES(last_login_time) > last_login_time THEN VALUES(last_login_time)
                    ELSE last_login_time
                    END,
                    total_time = VALUES(total_time)";
        $res = $this->execute($sql);
        return $res;
    }

    /**
     * @param $data
     * @return ResultSet
     */
    public function updateUserOtherFieldNew($data)
    {

        $field = [
            'platform_id'
            ,'uid'
            ,'server_id'
            ,'server_name'
            ,'last_pay_game_id'
            ,'role_id'
            ,'role_name'
            ,'day_hign_pay'
            ,'single_hign_pay'
            ,'last_day_pay'
            ,'last_pay_time'
            ,'total_time'
            ,'update_time'
        ];
        $sql = setIntoSql('kefu_user_recharge',$field,$data);

        $sql .= " ON DUPLICATE KEY UPDATE 
                 server_id = CASE server_id 
                    WHEN VALUES(server_id) THEN server_id
                    ELSE VALUES(server_id)
                    END,
                    server_name = CASE server_name
                    WHEN VALUES(server_name) THEN server_name 
                    ELSE VALUES(server_name)
                    END,
                    role_id = CASE role_id
                    WHEN VALUES(role_id) THEN role_id 
                    ELSE VALUES(role_id)
                    END,
                    role_name = CASE role_name
                    WHEN VALUES(role_name) THEN role_name 
                    ELSE VALUES(role_name)
                    END,
                    day_hign_pay = CASE 
                    WHEN day_hign_pay < VALUES(day_hign_pay)
                    THEN VALUES(day_hign_pay)
                    ELSE day_hign_pay
                    END,
                    single_hign_pay = CASE 
                    WHEN single_hign_pay < VALUES(single_hign_pay)
                    THEN VALUES(single_hign_pay)
                    ELSE single_hign_pay
                    END,
                    last_pay_game_id = VALUES(last_pay_game_id),
                    last_day_pay = VALUES(total_pay),
                    total_time = VALUES(total_time),
                    last_pay_time = CASE 
                    WHEN last_pay_time < VALUES(last_pay_time)
                    THEN VALUES(last_pay_time)
                    ELSE last_pay_time
                    END,
                    update_time = VALUES(update_time)";
        return $this->execute($sql);
    }

    /**
     * 更新用户的累充和30天充值
     * @param $data
     * @return ResultSet
     */
    public function updateUserRechargeTotalPayAndThirtyDayPayField($data)
    {

        $nowTime = time();
        $sql = "insert into kefu_user_recharge(`platform_id`,`uid`,`total_pay`,`thirty_day_pay`,`month_pay`,`total_time`) values";
        foreach ($data as $v) {
            $sql .= "('{$v['platform_id']}'," .
                "'{$v['uid']}'," .
                "'{$v['total_pay']}'," .
                "'{$v['thirty_day_pay']}'," .
                "'{$v['month_pay']}'," .
                "$nowTime),";
        }
        $sql = trim($sql,",");
        $sql .= " ON DUPLICATE KEY UPDATE 
                  total_pay = VALUES(total_pay),
                  thirty_day_pay = VALUES(thirty_day_pay),
                  month_pay = VALUES(month_pay),
                  total_time = VALUES(total_time)";

        return $this->execute($sql);
    }

    /**
     * @param int $start_time
     * @param int $end_time
     * @param bool $is_today
     * @return ResultSet
     */
    public function updateNoGameConfigIsVipField($start_time=0,$end_time=0,$is_today = true)
    {
        $t = time();
        $sql = "UPDATE `kefu_user_recharge` SET is_vip = 1,vip_time=last_pay_time,total_time={$t} WHERE is_vip = 0 AND (total_pay >= ".self::is_vip_total_pay." OR day_hign_pay >=".self::is_vip_day_hign_pay.") ";

        if ($is_today) {
            //不是全部更新
            if (!empty($start_time)) {
                if (empty($end_time)) $end_time = strtotime(date('Y-m-d', time()));
                $sql .= " and last_pay_time >=".$start_time." and last_pay_time<".($end_time + 86400);
            }else {
                $todayTime = strtotime(date('Y-m-d', strtotime('-1 day')));
                $sql .= " and last_pay_time >=".$todayTime." and last_pay_time<".($todayTime + 86400);
            }

        }else {
            return false;
            $sql1 = "UPDATE `kefu_user_recharge` SET is_vip = 0,vip_time=0 ";

            $this->execute($sql1);
        }
        return $this->execute($sql);
    }

    /**
     * @param $data
     * @return ResultSet
     */
    public function updateUserRechargeThirtyDayPayField($data)
    {
        $nowTime = time();
        $sql = "insert into kefu_user_recharge(`platform_id`,`uid`,`thirty_day_pay`,`total_time`) values";
        foreach ($data as $v) {
            $sql .= "('{$v['platform_id']}'," .
                "'{$v['uid']}'," .
                "'{$v['thirty_day_pay']}'," .
                "$nowTime),";
        }
        $sql = trim($sql,",");
        $sql .= " ON DUPLICATE KEY UPDATE 
                  thirty_day_pay = CASE  
                    WHEN thirty_day_pay >= VALUES(thirty_day_pay) THEN thirty_day_pay - VALUES(thirty_day_pay) 
                    ELSE 0
                    END,
                    total_time = VALUES(total_time)";
        $res = $this->execute($sql);
        return $res;
    }
}
