<?php
/**
 * Created by PhpStorm.
 * User: crosstime
 * Date: 2018/8/14
 * Time: 下午5:31
 */
namespace common\sql_server;


use common\server\Vip\LossUserServer;
use common\model\db_customer\{RecallLink, RecallPlan, RecallPlanLog, RecallPlanPeopleLog};


class RecallPlanPeopleLogSqlServer extends BaseSqlServer
{

    public static function insertInfo($plan_log_id,$user_infos)
    {

        $time = time();
        $today = date('Y-m-d');
        $model = new recallPlanPeopleLog();
        $sql = "insert ignore into recall_plan_people_log (`exec_date`,`plan_log_id`,`uid`,`platform_id`,`phone`,`account_money`,`product_money`,`level`,`last_login_time`,`add_time`) values";
        foreach($user_infos as $k=>$v){
            $sql .= "('{$today}',{$plan_log_id},{$v['uid']},{$v['platform_id']},'{$v['phone']}','{$v['account_money']}','{$v['product_money']}',{$v['level']},{$v['last_login_time']},{$time}),";
        }

        $sql = trim($sql,',');

        $res = $model->execute($sql);
        return $res;
    }


    public static function update($data)
    {
        $model = new RecallPlanPeopleLog();
        $res = $model->saveAll($data);
        return $res;
    }

    public static function getInfo($where,$limit = 1000)
    {
        $model = new RecallPlanPeopleLog();
        $res = $model->where($where)->limit($limit)->select();
        $res = isset($res) ? $res->toArray() : [];
        return $res;
    }

    public static function checkRecall($data,$platform_id)
    {
        $people_model = new RecallPlanPeopleLog();

        if(empty($data['mobile'])){
            $phone = LossUserServer::getPhone($platform_id,['uid'=>$data['uid']]);
            if(!empty($phone)){
                $data['mobile'] = $phone;
            }
        }

        $sql = "select a.id,a.uid,a.phone,b.recall_ver_id from recall_plan_people_log a 
                LEFT JOIN 
                recall_plan_log b on a.plan_log_id=b.id 
                where a.platform_id = {$platform_id} and phone = '{$data['mobile']}' and FIND_IN_SET({$data['login_channel']},recall_ver_id)>0 ORDER BY a.id desc";

        $is_exist = $people_model->query($sql);

        if(!empty($is_exist[0])){
            $link_model = new RecallLink();
            $ver_up_id = $link_model->field('up_id')->where("platform_id = {$platform_id} and ver_id = {$data['login_channel']}")->find();

            $people_model->isUpdate(1)->save(['reback'=>1,'reback_time'=>time(),'reback_ver_id'=>$data['login_channel'],'reback_up_id'=>$ver_up_id['up_id']],['id'=>$is_exist[0]['id']]);


        }

    }

    public static function getSendCount($plan_log_id)
    {
        $people_model = new RecallPlanPeopleLog();
        $sql = "SELECT
                    sum(
                    CASE
                    WHEN `status` = 1 THEN
                        1
                    ELSE
                        0
                    END 
                    )as have_send,
                        
                     count(*) AS should_be_send 
                    
                    FROM
                        recall_plan_people_log
                    WHERE
                        plan_log_id = {$plan_log_id}
                    GROUP BY
                        plan_log_id";

        $res = $people_model->query($sql);
        return $res;
    }

    public static function getReBackCount($plan_log_id)
    {
        $people_model = new RecallPlanPeopleLog();
        $res = $people_model->where( "plan_log_id = {$plan_log_id} and reback=1")->count();
        return $res;
    }




}