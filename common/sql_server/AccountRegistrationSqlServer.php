<?php
/**
 * Created by PhpStorm.
 * User: crosstime
 * Date: 2018/8/14
 * Time: 下午5:31
 */
namespace common\sql_server;

use common\model\db_statistic\AccountRegistration;

class AccountRegistrationSqlServer extends BaseSqlServer
{
    public static $return=[];


    public static function add($data)
    {

        $model = new AccountRegistration();

        $res = $model->insert($data);

        return $res;
    }

    public static function edit($data)
    {
        $model = new AccountRegistration();
        $res = $model->isUpdate(true)->save($data,['id'=>$data['id']]);
        return $res;
    }

    public static function getList($data){
        $data['offset'] = $data['page'] ? ($data['page']-1)*$data['limit'] : 0;
        $sql = "SELECT
                a.*, d.product_name as garrison_product_name,
                b.platform_id,
                b.server_name,
                b.open_time,
                b.start_time,
                b.end_time,
                b.admin_name,
                b.is_end,
                b.`status` as server_status,
              	c.username,
	            c.realname,
	            d.platform_id
            FROM
                db_statistic.account_registration a 
            LEFT JOIN db_statistic.garrison_product d ON a.garrison_product_id = d.id  
            LEFT JOIN db_statistic.distributional_server b ON a.garrison_product_id = b.garrison_product_id
            AND a.server_id = b.server_id
            LEFT JOIN gr_chat.gr_admin c ON a.admin_id = c.id
            
            where {$data['where']}
            order by {$data['order']}
            limit {$data['offset']},{$data['limit']}
";

        $model = new AccountRegistration();
        $res = $model->query($sql);
        return $res;


    }

    public static function getOne($id)
    {
        $model = new AccountRegistration();
        $info = $model->where("id={$id}")->find();
        $info = isset($info) ? $info->toArray() : [];

        return $info;
    }


    public static function getCount($data){
        $sql = "SELECT
               count(*)as `count`
            FROM
               (SELECT
                a.*, d.product_name as garrison_product_name,
              
                b.server_name,
                b.open_time,
                b.start_time,
                b.end_time,
                b.admin_name,
                b.is_end,
                b.`status` as server_status,
              	c.username,
	            c.realname,
	            d.platform_id
            FROM
                db_statistic.account_registration a 
            LEFT JOIN db_statistic.garrison_product d ON a.garrison_product_id = d.id  
            LEFT JOIN db_statistic.distributional_server b ON a.garrison_product_id = b.garrison_product_id
            AND a.server_id = b.server_id
            LEFT JOIN gr_chat.gr_admin c ON a.admin_id = c.id
            
            where {$data['where']})z
";

        $model = new AccountRegistration();
        $count = $model->query($sql);

        return $count[0]['count'];
    }


    public static function del($data){

        $model = new AccountRegistration();

        $res = $model->where("id in ({$data['id']})")->delete();

        return $res;

    }


}