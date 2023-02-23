<?php

namespace common\sql_server;


use common\model\gr_chat\Keyword;
use common\model\db_customer_platform\KefuCommonMember;

class WaringSqlServer extends BaseSqlServer
{

    public static function getList($data)
    {
        $having = '1 ';
        $where = '1=1 ';


        if($data['s_time']){
            $s_time = strtotime($data['s_time']);
            $where .=" and reg_date>={$s_time}";
        }

        if($data['e_time']){
            $e_time = strtotime($data['e_time']);
            $where .=" and reg_date<={$e_time}";
        }

        if($data['gid']){

//            $where .=" and reg_gid={$data['gid']}";
            $where .=" and reg_gid=73";
        }

        if($data['type'] == 0){
            $table = 'gr_ban_ip_log';
            $group_field = 'login_ip';
            $field = 'ip';
            if($data['account'])$having .= "and login_ip like '{$data['account']}%' ";
//            $having .= " and num>={$data['limit_num']} and reg_ip !='' limit {$data['offset']},{$data['limit']}" ;
        }else{
            $table = 'gr_ban_imei_log';
            $group_field = 'imei';
            $field = 'imei';
            if($data['account'])$having .= "and imei like '{$data['account']}%'";
//            $having .= " and num>={$data['limit_num']} and imei !='' limit {$data['offset']},{$data['limit']}" ;
        }
//        if($data['ip'])$having .= "reg_ip = {$data['ip']}";
        $having .= " and num>={$data['limit_num']} and {$group_field} !='' limit {$data['offset']},{$data['limit']}" ;


        $sql = "select a.*,b.type,b.dateline from 
                    (
                        select {$group_field} as {$field},user_name,uid,count(uid)as num from 
                        db_customer_{$data['platform']}.kefu_common_member 
                        where {$where} group by {$group_field} 
                        having ".$having."
                    ) a 
                     left join 
                     (
                        select {$field},type,dateline from
                         (
                            select {$field},type,dateline from 
                                gr_chat.{$table} ORDER BY dateline desc limit 100000000
                                )c 
                     GROUP BY {$field} 
                     )b 
                     ON a.{$field} = b.{$field} ";

        $model = new KefuCommonMember();

        $tmp_data = $model->query($sql);
        $tmp_data = isset($tmp_data) ? $tmp_data : [];
        return $tmp_data;
    }


    public static function getCount($data)
    {

        $having = '1 ';
        if($data['type'] == 0){
            $table = 'gr_ban_ip_log';
            $group_field = 'login_ip';
            $field = 'ip';
            if($data['account'])$having .= "login_ip like '{$data['account']}%'";

        }else{
            $table = 'gr_ban_imei_log';
            $group_field = 'imei';
            $field = 'imei';
            if($data['account'])$having .= "imei like '{$data['account']}%'";

        }

        $having = '1 ';
        if(isset($data['ip'])){
            $having .= "login_ip = {$data['ip']}";
        }
        $having .= " and num>={$data['limit_num']} and {$group_field} !='' " ;
        $sql = "select count(*)as total_num from(select {$group_field},count(uid)as num from db_customer_{$data['platform']}.kefu_common_member group by {$group_field} having ".$having.")t";

        $model = new KefuCommonMember();

        $res = $model->query($sql);
        $res = isset($res) ? $res : 0;
        return $res;
    }

    public static function getWaringUser($data,$data2)
    {

        if(empty($data['account'])) return false;
        $where = ' 1 ';
        $having = ' having 1 ';
        if($data['type'] == 0){
            $where .= " and login_ip = '{$data['account']}'";
        }else{
            $where .= " and imei = '{$data['account']}'";
        }

        if($data2['check_type'] == 1){
            $having .= " and a.user_name = '{$data2['check_value']}'";
        }elseif($data2['check_type'] == 2){
            $having .= " and o.role_name = '{$data2['check_value']}'";
        }elseif($data2['check_type'] == 3){
            $having .= " and money = {$data2['check_value']}";
        }
//        $where = "";
        $sql = "SELECT
                    a.reg_date,
                    a.login_date,
                    a.reg_gid as gid,
                    a.uid,
                    a.user_name,
                    o.role_name,
                    o.server_id,
                    o.server_name,
                    sum(o.amount) AS money,
                    a.`status`
                    FROM
                     (
                     SELECT
                        *
                        FROM
                        db_customer_{$data['platform']}.kefu_common_member
                        where {$where}
                        ) a  
                     left join db_customer_{$data['platform']}.kefu_pay_order as o 
                     on a.user_name = o.user_name 
                     group by a.user_name,o.gid,o.role_id ".$having
            ." limit {$data['offset']},{$data['limit']}";


        $model = new KefuCommonMember();

        $res = $model->query($sql);
        $res = isset($res) ? $res : [];

        return $res;
    }


    public static function getWaringUserCount($data,$data2)
    {
        $where = ' 1 ';
        $having = ' having 1 ';
        if($data['type'] == 0){
            $where .= " and login_ip = '{$data['account']}'";
        }else{
            $where .= " and imei = '{$data['account']}'";
        }

        if($data2['check_type'] == 1){
            $having .= " and user_name = '{$data2['check_value']}'";
        }elseif($data2['check_type'] == 2){
            $having .= " and role_name = '{$data2['check_value']}'";
        }elseif($data2['check_type'] == 3){
            $having .= " and money = {$data2['check_value']}";
        }


//        $where = "";
        $sql = "select count(uid)as total_num,money,user_name,uid,role_name from(SELECT
                    a.reg_date,
                    a.login_date,
                    o.gid,
                    a.uid,
                    a.user_name,
                    o.role_name,
                    o.server_id,
                    o.server_name,
                    sum(o.amount) AS money,
                    a.`status`
                    FROM
                     (
                     SELECT
                        *
                        FROM
                        db_customer_{$data['platform']}.kefu_common_member
                        where {$where}
                        ) a  
                     left join db_customer_{$data['platform']}.kefu_pay_order as o 
                     on a.user_name = o.user_name 
                     group by a.user_name,o.gid,o.role_id)b"
                            .$having;

        $model = new KefuCommonMember();

        $res = $model->query($sql);

        $res = isset($res) ? $res[0]['total_num'] : 0;

        return $res;
    }

}